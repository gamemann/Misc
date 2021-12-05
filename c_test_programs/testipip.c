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
#include <pthread.h>
#include <inttypes.h>
#include <linux/if.h>
#include <sys/ioctl.h>

#define MAX_PCKT_LENGTH 65508

int cont = 1;

void sighdl(int tmp)
{
    cont = 0;
}

int main()
{
    int sockfd;
    char buffer[MAX_PCKT_LENGTH];
    struct sockaddr_in sin;
    struct sockaddr_in din;

    sin.sin_family = AF_INET;
    sin.sin_addr.s_addr = inet_addr("10.50.0.21");
    sin.sin_port = htons(27015);
    memset(&sin.sin_zero, 0, sizeof(sin.sin_zero));

    sockfd = socket(PF_INET, SOCK_DGRAM, IPPROTO_UDP);

    if (sockfd <= 0)
    {
        perror("socket");

        exit(1);
    }

    if (bind(sockfd, (struct sockaddr *)&sin, sizeof(sin)) < 0)
    {
        perror("bind");

        exit(1);
    }

    signal(SIGINT, sighdl);

    int len = sizeof(din);

    fprintf(stdout, "Receiving packets on 10.50.0.21...\n\n");

    while (cont)
    {
        int i = recvfrom(sockfd, (char *)buffer, MAX_PCKT_LENGTH, 0, (struct sockaddr *)&din, &len);

        if (i < 0)
        {
            perror("recvfrom");

            continue;
        }

        // Send back.
        int j = sendto(sockfd, (const char *)buffer, i, 0, (struct sockaddr *)&din, sizeof(din));

        if (j < 0)
        {
            perror("sendto");

            continue;
        }

        fprintf(stdout, "Received %d bytes and sent %d bytes.\n", i, j);
    }

    exit(0);
}