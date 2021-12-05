#include <stdio.h>
#include <sys/socket.h>
#include <sys/types.h>
#include <netinet/in.h>
#include <arpa/inet.h>
#include <unistd.h>
#include <string.h>
#include <stdlib.h>
#include <netinet/ip_icmp.h>
#include <error.h>
#include <errno.h>
#include <sys/time.h>
#include <fcntl.h>
#include <signal.h>
#include <time.h>

#define PORT_NUM 0
#define PING_SLEEP 1000000
#define PING_TIMEOUT 1

struct ping_pckt
{
    struct icmphdr hdr;
    char *msg;
};

extern int errno;
int pingloop = 1;

void intHandle(int dummy)
{
    pingloop = 0;
}

// Calcualtes the checksum.
unsigned short csum(void *b, int len)
{
    unsigned short *buf = b;
    unsigned int sum = 0;
    unsigned short result;

    for (sum = 0; len > 1; len -= 2)
    {
        sum += *buf++;
    }

    if (len == 1)
    {
        sum += *(unsigned char*)buf;
    }

    sum = (sum >> 16) + (sum & 0xFFFF);
    sum += (sum >> 16);
    result = ~sum;

    return result;
}

int main(int argc, char *argv[])
{
    struct sockaddr_in destAddr;
    struct sockaddr_in rAddr;

    int sockfd;
    int yes = 1;
    int ttl = 64, count = 0, addr_len, flag = 1, receivedCount = 0;

    struct ping_pckt *pckt;

    struct timespec time_start, time_end, tfs, tfe;
    long double rtt_msec = 0, total_msec = 0;

    struct timeval tv_out;
    tv_out.tv_sec = PING_TIMEOUT;
    tv_out.tv_usec = 0;

    clock_gettime(CLOCK_MONOTONIC, &tfs);

    unsigned long sAddress;
    unsigned long dAddress;
    int payload_size = 64;

    if (argc < 2)
    {
        fprintf(stdout, "Usage: %s <Destination IP> [<Payload Size>]", argv[0]);

        exit(1);
    }
    else if (argc == 3)
    {
        payload_size = atoi(argv[2]);
    }

    // Get size of packet (not including ICMP header).
    int dataSize = payload_size - sizeof(struct icmphdr);

    // Get source and destination addresses.
    dAddress = inet_addr(argv[1]);

    // Fill out destination addr struct.
    destAddr.sin_family = AF_INET;
    destAddr.sin_addr.s_addr = dAddress;
    destAddr.sin_port = htons(PORT_NUM);
    memset(&destAddr.sin_zero, 0, sizeof (destAddr.sin_zero));

    signal(SIGINT, intHandle);

    sockfd = socket(AF_INET, SOCK_RAW, IPPROTO_ICMP);

    if (sockfd < 0)
    {
        fprintf(stdout, "Error setting up socket. Socket Error - %s\n", strerror(errno));
        perror("socket");

        close(sockfd);

        exit(1);
    }

    if (setsockopt(sockfd, SOL_SOCKET, SO_REUSEADDR, &yes, sizeof(yes)) != 0)
    {
        fprintf(stdout, "Error with setsockopt() on reusing address.\n");
        perror("setsockopt");

        close(sockfd);

        exit(1);
    }

    if (setsockopt(sockfd, SOL_IP, IP_TTL, &ttl, sizeof(ttl)) != 0)
    {
        fprintf(stdout, "Error with setsockopt() on setting TTL.\n");
        perror("setsockopt");

        close(sockfd);

        exit(1);
    }

    if (setsockopt(sockfd, SOL_SOCKET, SO_RCVTIMEO, (const char *)&tv_out, sizeof(tv_out)) != 0)
    {
        fprintf(stdout, "Error with setsockopt() on setting receive timeout.");
        perror("setsockopt");

        close(sockfd);

        exit(1);
    }

    while (pingloop)
    {
        flag = 1;

        pckt = malloc(payload_size);
        pckt->msg = malloc(dataSize);

        pckt->hdr.type = ICMP_ECHO;
        pckt->hdr.un.echo.id = getpid();
        pckt->hdr.checksum = 0;

        for (int i = 0; i < dataSize - 1; i++)
        {
            pckt->msg[i] = i + '0';
        }

        // Null terminate dat string :tap: :tap:.
        pckt->msg[dataSize - 1] = '\0';

        pckt->hdr.un.echo.sequence = count++;
        pckt->hdr.checksum = csum(pckt, payload_size);

        usleep(PING_SLEEP);

        // LET'S SEND THIS BITCH.
        clock_gettime(CLOCK_MONOTONIC, &time_start);

        if (sendto(sockfd, pckt, payload_size, 0, (struct sockaddr *)&destAddr, sizeof(destAddr)) <= 0)
        {
            flag = 0;
            fprintf(stdout, "Error sending packet!\n");
        }

        addr_len = sizeof(rAddr);

        if (recvfrom(sockfd, pckt, payload_size, 0, (struct sockaddr *)&rAddr, &addr_len) <= 0 && count > 1)
        {
            fprintf(stdout, "Error receiving packet!\n");
        }
        else
        {
            clock_gettime(CLOCK_MONOTONIC, &time_end);

            double  timeE = ((double)(time_end.tv_nsec - time_start.tv_nsec))/1000000.0;

            rtt_msec = (time_end.tv_sec - time_start.tv_sec) * 1000.0 + timeE;

            if (flag)
            {
                if (!(pckt->hdr.type == 69 && pckt->hdr.code == 0))
                {
                    fprintf(stdout, "Error with packet. Error type is %d and error code is %d\n", pckt->hdr.type, pckt->hdr.code);
                }
                else
                {
                    fprintf(stdout, "%d bytes from %s msg_seq=%d ttl=%d rtt=%Lf ms.\n", payload_size, inet_ntoa(rAddr.sin_addr), count, ttl, rtt_msec);

                    receivedCount++;
                }   
            } 
        }

        //free(pckt);
        //free(pckt->msg);
    }

    fprintf(stdout, "\n\n=== Ping Statistics For %s ===\n", inet_ntoa(destAddr.sin_addr));
    fprintf(stdout, "%d packets sent. %d packets received. %f percent packet loss. Total time %Lf\n", count, receivedCount, ((count - receivedCount)/count) * 100.0, total_msec);

    close(sockfd);

    exit(0);
}