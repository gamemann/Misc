#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>
#include <sys/socket.h>
#include <sys/types.h>
#include <netinet/ip.h>
#include <netinet/udp.h>
#include <arpa/inet.h>
#include <string.h>
#include <error.h>
#include <errno.h>
#include <signal.h>
#include <time.h>
#include <sys/time.h>

#define PCKT_LEN 8192   // Buffer length. Note - This isn't how much data we're actually sending.

extern int errno;

/* Signal action */
int cont = 1;

void signalHndl(int tmp)
{
    cont = 0;
}

/* Checksum function */
unsigned short csum(unsigned short *buf, int nwords)
{
    unsigned long sum;

    for (sum = 0; nwords > 0; nwords--)
    {
        sum += *buf++;
    }

    sum = (sum >> 16) + (sum & 0xffff);
    sum += (sum >> 16);

    return (unsigned short) (~sum);
}

int main(int argc, char *argv[])
{
    // Check argument count.
    if (argc < 5)
    {
        fprintf(stdout, "Usage: %s <Source IP> <Source Port> <Destination IP> <Destination Port> [<Time out> <Data>]", argv[0]);

        exit(1);
    }

    // Initialize variables.
    char buffer[PCKT_LEN];
    char recvBuffer[PCKT_LEN];

    struct iphdr *iphdr = (struct iphdr *) buffer;
    struct udphdr *udphdr = (struct udphdr *) (buffer + sizeof(struct iphdr));
    char *pcktData = (char *) (buffer + sizeof(struct iphdr) + sizeof(udphdr));

    int sockfd;
    struct sockaddr_in sin;
    struct sockaddr_in din;
    int one = 1;
    int addrLen = sizeof(din);
    int data = 0;

    struct timeval tv;

    // Timeout
    int timeout = 1;

    if (argc > 5)
    {
        timeout = atoi(argv[5]);
    }

    // Set timeval structure to timeout.
    tv.tv_sec = timeout;
    tv.tv_usec = 0;

    // Set packet buffer to 0's.
    memset(buffer, 0, PCKT_LEN);

    // Check for data.
    int pcktDataLen = 0;

    if (argc > 6)
    {
        // Adds data to the packet's payload (after IP + UDP headers).
        for (int i = 0; i < strlen(argv[6]); i++)
        {
            *pcktData++ = argv[6][i];

            pcktDataLen++;
        }
    }

    // Fill out source sockaddr in (IPv4, source address, and source port).
    sin.sin_family = AF_INET;
    sin.sin_addr.s_addr = inet_addr(argv[1]);
    sin.sin_port = htons(atoi(argv[2]));
    memset(&sin.sin_zero, 0, sizeof(sin.sin_zero));

    // Let's fill out the IP and UDP headers C:

    // IP Header.
    iphdr->ihl = 5;
    iphdr->version = 4;
    iphdr->tos = 16;
    iphdr->tot_len = sizeof(struct iphdr) + sizeof(struct udphdr) + pcktDataLen;
    iphdr->id = htons(54321);
    iphdr->ttl = 64;
    iphdr->protocol = IPPROTO_UDP;
    iphdr->saddr = inet_addr(argv[1]);
    iphdr->daddr = inet_addr(argv[3]);

    // UDP Header.
    udphdr->uh_sport = htons(atoi(argv[2]));
    udphdr->uh_dport = htons(atoi(argv[4]));
    udphdr->len = htons(sizeof(struct udphdr) + pcktDataLen);

    // IP Checksum.
    iphdr->check = csum((unsigned short *)buffer, sizeof(struct iphdr) + sizeof(struct udphdr));

    // Create the socket.
    sockfd = socket(PF_INET, SOCK_RAW, IPPROTO_UDP);

    // Check for socket error.
    if (sockfd <= 0)
    {
        fprintf(stderr, "Socket Error - %s\n", strerror(errno));
        perror("sockfd");

        exit(1);
    }

    // Set socket timeout.
    if (setsockopt(sockfd, SOL_SOCKET, SO_RCVTIMEO, (const char *)&tv, sizeof(tv)) < 0)
    {
        fprintf(stderr, "Socket Option Error (Timeout) - %s\n", strerror(errno));
        perror("setsockopt");

        exit(1);
    }

    // Let the socket know we want to pass our own headers.
    if (setsockopt(sockfd, IPPROTO_IP, IP_HDRINCL, &one, sizeof(one)) < 0)
    {
        fprintf(stderr, "Socket Option Error (Header Include) - %s\n", strerror(errno));
        perror("setsockopt");

        exit(1);
    }

    // Setup signal.
    signal(SIGINT, signalHndl);

    // Let the client know what we're doing.
    fprintf(stdout, "Sending packets to %s:%u from %s:%u with timeout %d\n\n", argv[3], atoi(argv[4]), argv[1], atoi(argv[2]), timeout);

    // Loop.
    while(cont)
    {
        // Send packet.
        fprintf(stdout, "Sending UDP packet.\n");

        // Check for send error.
        if (sendto(sockfd, buffer, iphdr->tot_len, 0, (struct sockaddr *)&sin, sizeof(sin)) <= 0)
        {
            fprintf(stderr, "Error sending packet...\n\n");
        }

        // Receive data that may be coming back to us.
        data = recvfrom(sockfd, recvBuffer, PCKT_LEN, 0, (struct sockaddr *)&din, &addrLen);

        // Check if the IP we're receiving from matches the destination IP along with data check. Keep in mind if you actually spoof the IP, you won't receive any data from the server since it'll reply to the spoofed IP instead :P
        if (din.sin_addr.s_addr != inet_addr(argv[3]) || data < 0)
        {
            fprintf(stderr, "Error receiving packet...\n\n");
        }

        sleep(1);
    }

    // Close socket.
    close(sockfd);

    // Exit program successfully.
    exit(0);
}