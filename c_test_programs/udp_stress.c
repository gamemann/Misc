#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>
#include <sys/types.h>
#include <sys/socket.h>
#include <sys/sysinfo.h>
#include <netinet/in.h>
#include <net/if.h>
#include <linux/if.h>
#include <linux/if_ether.h>
#include <linux/ip.h>
#include <linux/tcp.h>
#include <linux/udp.h>
#include <linux/icmp.h>
#include <linux/if_packet.h>
#include <arpa/inet.h>
#include <sys/ioctl.h>
#include <inttypes.h>
#include <time.h>
#include <pthread.h>
#include <string.h>
#include <errno.h>
#include <assert.h>
#include <fcntl.h>
#include <sys/resource.h>

#include "/home/dev/pckt-sequence/src/csum.h"

#define DATALEN 61000
#define THREADCOUNT 12

void *threadhndl(void *temp)
{
    int sockfd;
    
    assert((sockfd = socket(AF_PACKET, SOCK_RAW, IPPROTO_RAW)) > -1);

    struct sockaddr_ll sin;

    sin.sll_family = PF_PACKET;
    sin.sll_ifindex = if_nametoindex("ens18");
    sin.sll_protocol = htons(ETH_P_IP);
    sin.sll_halen = ETH_ALEN;

    assert(bind(sockfd, (struct sockaddr *)&sin, sizeof(sin)) == 0);

    //fcntl(sockfd, F_SETFL, O_NONBLOCK);

    char buffer[65535];

    struct ethhdr *eth = (struct ethhdr *)(buffer);

    eth->h_source[0] = 0x1A;
    eth->h_source[1] = 0xC4;
    eth->h_source[2] = 0xDF;
    eth->h_source[3] = 0x70;
    eth->h_source[4] = 0xD8;
    eth->h_source[5] = 0xA6;

    eth->h_dest[0] = 0xAE;
    eth->h_dest[1] = 0x21;
    eth->h_dest[2] = 0x14;
    eth->h_dest[3] = 0x4B;
    eth->h_dest[4] = 0x3A;
    eth->h_dest[5] = 0x6D;

    eth->h_proto = htons(ETH_P_IP);

    struct iphdr *iph = (struct iphdr *)(buffer + 14);
    iph->ihl = 5;
    iph->version = 4;
    iph->id = 0;
    iph->frag_off = 0;
    iph->protocol = IPPROTO_UDP;
    iph->saddr = inet_addr("10.50.0.3");
    iph->daddr = inet_addr("10.50.0.4");
    iph->tos = 0x00;
    iph->ttl = 64;
    iph->tot_len = htons(20 + 8 + DATALEN);
    update_iph_checksum(iph);

    struct udphdr *udph = (struct udphdr *)(buffer + 14 + (iph->ihl * 4));
    udph->source = htons(8000);
    udph->dest = htons(27000);
    udph->len = htons(8 + DATALEN);
    udph->check = 0;

    unsigned char *data = (unsigned char *)(buffer + 14 + (iph->ihl * 4) + 8);

    for (int i = 0; i < DATALEN; i++)
    {
        *(data + i) = 0xFF;
    }

    while (1)
    {
        if (send(sockfd, buffer, ntohs(iph->tot_len) + 14, 0) < 0)
        {
            //fprintf(stdout, "Failed to send packet :: %s (%d)\n", strerror(errno), sockfd);
        }
    }
}

int main()
{
    // Raise RLimit.
    struct rlimit rl = {RLIM_INFINITY, RLIM_INFINITY};

    if (setrlimit(RLIMIT_MEMLOCK, &rl)) 
    {
        fprintf(stderr, "Error setting rlimit.\n");

        return EXIT_FAILURE;
    }

    pthread_t pid[THREADCOUNT];

    for (int i = 0; i < THREADCOUNT; i++)
    {
        pthread_create(&pid[i], NULL, threadhndl, NULL);
    }

    for (int i = 0; i < THREADCOUNT; i++)
    {
        pthread_join(pid[i], NULL);
    }

    return 0;
}