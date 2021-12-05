#include <stdio.h>
#include <inttypes.h>
#include <stdlib.h>
#include <unistd.h>
#include <linux/if_ether.h>
#include <linux/ipv6.h>
#include <linux/udp.h>
#include <arpa/inet.h>
#include <sys/ioctl.h>
#include <net/if.h>
#include <linux/if_packet.h>

#include <sys/types.h>
#include <sys/socket.h>

#include <assert.h>
#include <errno.h>
#include <string.h>

int main()
{
    int sock;

    assert((sock = socket(AF_PACKET, SOCK_RAW, IPPROTO_RAW)) > 0);

    char buffer[65535];

    struct ethhdr *eth = (struct ethhdr *)buffer;

    eth->h_proto = htons(ETH_P_IPV6);

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

    struct ipv6hdr *iph6 = (struct ipv6hdr *)(buffer + sizeof(struct ethhdr));

    const char *source = "fe80::18c4:dfff:fe70:d8a6";
    const char *dest = "fe80::ac21:14ff:fe4b:3a6d";

    struct in6_addr sin;
    inet_pton(AF_INET6, source, &sin);

    struct in6_addr din;
    inet_pton(AF_INET6, dest, &din);

    iph6->version = 6;
    iph6->saddr = sin;
    iph6->daddr = din;
    iph6->hop_limit = 64;
    iph6->nexthdr = IPPROTO_UDP;
    iph6->flow_lbl[0] = 0;
    iph6->flow_lbl[1] = 0;
    iph6->flow_lbl[2] = 0;
    iph6->priority = 0;
    iph6->payload_len = htons(sizeof(struct udphdr));

    struct udphdr *udph = (struct udphdr *)(buffer + sizeof(struct ethhdr) + sizeof(struct ipv6hdr));

    udph->source = htons(27000);
    udph->dest = htons(8088);
    udph->len = htons(sizeof(struct udphdr));
    udph->check = 0;

    struct sockaddr_in6 ssin;

    ssin.sin6_family = AF_INET6;
    ssin.sin6_addr = sin;
    ssin.sin6_port = htons(8088);
    //ssin.sin6_scope_id = if_nametoindex("ens18");

    //assert(bind(sock, (struct sockaddr *)&ssin, sizeof(ssin)) == 0);

    struct sockaddr_in6 ddin;
    ddin.sin6_family = AF_INET6;
    ddin.sin6_addr = din;
    ddin.sin6_port = htons(8088);

    struct sockaddr_ll dinll;

    memcpy(&dinll.sll_addr, &eth->h_source, ETH_ALEN);

    dinll.sll_halen = ETH_ALEN;
    dinll.sll_family = AF_PACKET;
    dinll.sll_ifindex = if_nametoindex("ens18");


    while (1)
    {
        if (sendto(sock, buffer, ntohs(iph6->payload_len) + sizeof(struct ethhdr) + sizeof(struct ipv6hdr), 0, (struct sockaddr *)&dinll, sizeof(dinll)) < 1)
        {
            printf("Couldn't send packet :: %s\n", strerror(errno));
        }

        sleep(1);
    }
    close(sock);

    return 0;
}