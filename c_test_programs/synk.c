#include <linux/bpf.h>
#include "../xdp-stats/libbpf/src/bpf_helpers.h"
#include <linux/in.h>
#include <linux/if_ether.h>
#include <linux/if_packet.h>
#include <linux/ipv6.h>
#include <linux/udp.h>
#include <linux/tcp.h>
#include <linux/ip.h>
#include <linux/icmpv6.h>
#include <stdint.h>
#include <stdatomic.h>

#ifndef htons
#define htons(x) ((__be16)___constant_swab16((x)))
#endif


SEC("xdp")
int xdp_filter(struct xdp_md *ctx) {
    void *data_end = (void *)(long)ctx->data_end;
    void *data = (void *)(long)ctx->data;
    struct ethhdr *eth = data;
    if (eth + 1 > (struct ethhdr *)data_end) {
        return XDP_DROP;
    }

    if (eth->h_proto != htons(ETH_P_IP)) {
        return XDP_PASS;
    }


    if(eth->h_proto == htons(ETH_P_IP)) {
        struct iphdr *iph = data + sizeof(struct ethhdr);
        if (iph + 1 > (struct iphdr *)data_end) {
            return XDP_DROP;
        }
        if(iph->protocol == IPPROTO_UDP) {
            struct udphdr *udph = data + sizeof(struct ethhdr) + (iph->ihl * 4);
            if (udph + 1 > (struct udphdr *)data_end) {
                return XDP_DROP;
            }
            if(udph->dest > htons(26999) && udph->dest < htons(21000)) {
                return XDP_DROP;
            }
        }
    }
    return XDP_PASS;    
}