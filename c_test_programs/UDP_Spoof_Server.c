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

#define PCKT_LEN 8192

extern int errno;

volatile sig_atomic_t cont = 1;

void signHdl(int tmp)
{
    cont = 0;
}

int main(int argc, char *argv[])
{
    if (argc < 3)
    {
        fprintf(stdout, "Usage: %s <Bind IP> <Bind Port>", argv[0]);

        exit(0);
    }

    // Initialize variables.
    char buffer[PCKT_LEN];
    int sockfd;
    struct sockaddr_in sin;
    struct sockaddr_in din;
    int one = 1;

    struct timeval tv;
    tv.tv_sec = 3;
    tv.tv_usec = 0;

    int dinSize = sizeof(din);
    int data = 0;

    // Fill out source sockaddr_in.
    sin.sin_family = AF_INET;
    sin.sin_port = htons(atoi(argv[2]));
    sin.sin_addr.s_addr = inet_addr(argv[1]);

    // Create socket.
    sockfd = socket(PF_INET, SOCK_DGRAM, 0);

    // Check for any errors with socket creation.
    if (sockfd <= 0)
    {
        fprintf(stderr, "Socket error - %s\n", strerror(errno));
        perror("socket");

        exit(1);
    }

    // Bind socket and check for errors.
    if (bind(sockfd, (struct sockaddr *)&sin, sizeof(sin)) < 0)
    {
        fprintf(stderr, "Socket Bind error - %s\n", strerror(errno));
        perror("bind");

        exit(1);
    }

    // Set reuse option.
    if (setsockopt(sockfd, SOL_SOCKET, SO_REUSEADDR, &one, sizeof(one)) < 0)
    {
        fprintf(stderr, "Socket option error (reuse) - %s\n", strerror(errno));
        perror("setsockopt");

        exit(1);
    }

    // Set timeout.
    if (setsockopt(sockfd, SOL_SOCKET, SO_RCVTIMEO, (const char *)&tv, sizeof(tv)) < 0)
    {
        fprintf(stderr, "Socket option error (timeout) - %s\n", strerror(errno));
        perror("setsockopt");

        exit(1);   
    }

    // Set signal handle.
    signal(SIGINT, signHdl);

    // Let client know details.
    fprintf(stdout, "Program bound to %s:%u. Waiting for packets...\n", argv[1], atoi(argv[2]));
    
    // Loop.
    while (cont)
    {
        data = recvfrom(sockfd, buffer, PCKT_LEN, 0, (struct sockaddr *)&din, &dinSize);

        // Check data amount.
        if (data < 0)
        {
            continue;
        }

        fprintf(stdout, "Got a packet with size of %d\n", data);
        // Let's send data back.
        if (sendto(sockfd, buffer, data, 0, (struct sockaddr *)&sin, sizeof(sin)) <= 0)
        {
            fprintf(stderr, "Error sending packet back.\n");
        }
    }

    // Close socket.
    close(sockfd);

    // Close program.
    exit(0);
}