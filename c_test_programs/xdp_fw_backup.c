#include <linux/if_ether.h>
#include <linux/udp.h>
#include <linux/tcp.h>
#include <linux/icmp.h>
#include <linux/ip.h>
#include <linux/ipv6.h>
#include <linux/in.h>
#include <inttypes.h>
#include <stdint.h>
#include <stdatomic.h>

#include <linux/bpf.h>
#include <linux/bpf_common.h>

#include "../libbpf/src/bpf_helpers.h"

#include "include/xdpfw.h"

//#define DEBUG
#define DOSTATSONBLOCKMAP   // Feel free to comment this out if you don't want the `blocked` entry on the stats map to be incremented every single time a packet is dropped from the source IP being on the blocked map. Commenting this line out should increase performance when blocking malicious traffic.

#ifdef DEBUG

#define bpf_printk(fmt, ...)					\
({								\
	       char ____fmt[] = fmt;				\
	       bpf_trace_printk(____fmt, sizeof(____fmt),	\
				##__VA_ARGS__);			\
})

#endif

#define likely(x) __builtin_expect(!!(x), 1)
#define unlikely(x) __builtin_expect(!!(x), 0)
#if __BYTE_ORDER__ == __ORDER_LITTLE_ENDIAN__
#define htons(x) ((__be16)___constant_swab16((x)))
#define ntohs(x) ((__be16)___constant_swab16((x)))
#define htonl(x) ((__be32)___constant_swab32((x)))
#define ntohl(x) ((__be32)___constant_swab32((x)))
#elif __BYTE_ORDER__ == __ORDER_BIG_ENDIAN__
#define htons(x) (x)
#define ntohs(X) (x)
#define htonl(x) (x)
#define ntohl(x) (x)
#endif

struct bpf_map_def SEC("maps") filters_map = 
{
    .type = BPF_MAP_TYPE_ARRAY,
    .key_size = sizeof(uint32_t),
    .value_size = sizeof(struct filter),
    .max_entries = MAX_FILTERS
};

struct bpf_map_def SEC("maps") stats_map =
{
    .type = BPF_MAP_TYPE_ARRAY,
    .key_size = sizeof(uint32_t),
    .value_size = sizeof(struct xdpfw_stats),
    .max_entries = 1
};

struct bpf_map_def SEC("maps") ip_stats_map =
{
    .type = BPF_MAP_TYPE_LRU_HASH,
    .key_size = sizeof(__uint128_t),
    .value_size = sizeof(struct xdpfw_ip_stats),
    .max_entries = MAX_TRACK_IPS
};

struct bpf_map_def SEC("maps") ip_blacklist_map =
{
    .type = BPF_MAP_TYPE_LRU_HASH,
    .key_size = sizeof(__uint128_t),
    .value_size = sizeof(uint64_t),
    .max_entries = MAX_TRACK_IPS
};

SEC("xdp_prog")
int xdp_prog_main(struct xdp_md *ctx)
{    
    // Initialize data.
    void *data_end = (void *)(long)ctx->data_end;
    void *data = (void *)(long)ctx->data;

    // Scan ethernet header.
    struct ethhdr *ethhdr = data;

    // Check if the ethernet header is valid.
    if (ethhdr + 1 > (struct ethhdr *)data_end)
    {
        return XDP_DROP;
    }

    // Check Ethernet protocol.
    if (unlikely(ethhdr->h_proto != htons(ETH_P_IP) && ethhdr->h_proto != htons(ETH_P_IPV6)))
    {
        return XDP_PASS;
    }

    uint8_t matched = 0;
    uint8_t action = 0;
    uint64_t blocktime = 1;

    uint8_t ipv6 = 0;

    // Initialize IP headers.
    struct iphdr *iph;
    struct ipv6hdr *iph6;

    // Check if we have an IPv6 packet.
    if (ethhdr->h_proto == htons(ETH_P_IPV6))
    {
        ipv6 = 1;
    }

    uint8_t l3hdrlen = 0;
    uint8_t protocol = 0;
    uint16_t len = 0;
    uint8_t ttl = 0;
    __uint128_t saddr = 0;

    // Set IPv4 and IPv6 common variables.
    if (ipv6)
    {
        iph6 = (data + sizeof(struct ethhdr));

        if (unlikely(iph6 + 1 > (struct ipv6hdr *)data_end))
        {
            return XDP_DROP;
        }
        
        protocol = iph6->nexthdr;
        l3hdrlen = sizeof(struct ipv6hdr);
        len = ntohs(iph6->payload_len);
        ttl = iph6->hop_limit;
        saddr = 0;
    }
    else
    {
        iph = (data + sizeof(struct ethhdr));

        if (unlikely(iph + 1 > (struct iphdr *)data_end))
        {
            return XDP_DROP;
        }

        protocol = iph->protocol;
        l3hdrlen = (iph->ihl * 4);
        len = ntohs(iph->tot_len);
        ttl = iph->ttl;
        saddr = iph->saddr;
    }
    
    // Check IP header protocols.
    if (unlikely(protocol != IPPROTO_UDP && protocol != IPPROTO_TCP && protocol != IPPROTO_ICMP))
    {
        return XDP_DROP;
    }

    // Get stats map.
    uint32_t key = 0;
    struct xdpfw_stats *stats;

    stats = bpf_map_lookup_elem(&stats_map, &key);

    uint64_t now = bpf_ktime_get_ns();

    // Check blacklist map.
    uint64_t *blocked = bpf_map_lookup_elem(&ip_blacklist_map, &saddr);
    
    if (blocked != NULL && *blocked > 0)
    {
        #ifdef DEBUG
            bpf_printk("Checking for blocked packet... Block time %" PRIu64 "\n", *blocked);
        #endif

        if (now > *blocked)
        {
            // Remove element from map.
            bpf_map_delete_elem(&ip_blacklist_map, &saddr);
        }
        else
        {
            #ifdef DOSTATSONBLOCKMAP
                // Increase blocked stats entry.
                if (stats)
                {
                    __sync_fetch_and_add(&stats->blocked, 1);
                }
            #endif

            // They're still blocked. Drop the packet.
            return XDP_DROP;
        }
    }

    // Update IP stats (PPS/BPS).
    uint64_t pps = 0;
    uint64_t bps = 0;

    struct xdpfw_ip_stats *ip_stats = bpf_map_lookup_elem(&ip_stats_map, &saddr);

    if (ip_stats)
    {
        // Check for reset.
        if ((now - ip_stats->tracking) > 1000000000)
        {
            ip_stats->pps = 0;
            ip_stats->bps = 0;
            ip_stats->tracking = now;
        }

        // Increment PPS and BPS using built-in functions.
        __sync_fetch_and_add(&ip_stats->pps, 1);
        __sync_fetch_and_add(&ip_stats->bps, ctx->data_end - ctx->data);
        
        pps = ip_stats->pps;
        bps = ip_stats->bps;
    }
    else
    {
        // Create new entry.
        struct xdpfw_ip_stats new;

        new.pps = 1;
        new.bps = ctx->data_end - ctx->data;
        new.tracking = now;

        pps = new.pps;
        bps = new.bps;

        bpf_map_update_elem(&ip_stats_map, &saddr, &new, BPF_ANY);
    }

    // Let's get the filters we need.
    struct filter *filter[MAX_FILTERS];

    for (uint8_t i = 0; i < MAX_FILTERS; i++)
    {
        uint32_t key = i;
        
        filter[i] = bpf_map_lookup_elem(&filters_map, &key);
    }

    struct tcphdr *tcph;
    struct udphdr *udph;
    struct icmphdr *icmph;
    
    uint16_t l4headerLen = 0;

    // Check protocol.
    switch (protocol)
    {
        case IPPROTO_TCP:
            // Scan TCP header.
            tcph = (data + sizeof(struct ethhdr) + l3hdrlen);

            // Check TCP header.
            if (tcph + 1 > (struct tcphdr *)data_end)
            {
                return XDP_PASS;
            }

            // Set L4 Header length.
            l4headerLen = sizeof(struct tcphdr);

            break;

        case IPPROTO_UDP:
            // Scan UDP header.
            udph = (data + sizeof(struct ethhdr) + l3hdrlen);

            // Check TCP header.
            if (udph + 1 > (struct udphdr *)data_end)
            {
                return XDP_PASS;
            }

            // Set L4 Header length.
            l4headerLen = sizeof(struct udphdr);

            break;

        case IPPROTO_ICMP:
            // Scan ICMP header.
            icmph = (data + sizeof(struct ethhdr) + l3hdrlen);

            // Check TCP header.
            if (icmph + 1 > (struct icmphdr *)data_end)
            {
                return XDP_PASS;
            }

            // Set L4 Header length.
            l4headerLen = sizeof(struct icmphdr);

            break;
    }
    
    for (uint8_t i = 0; i < MAX_FILTERS; i++)
    {
        // Check if ID is above 0 (if 0, it's an invalid rule).
        if (!filter[i] || filter[i]->id < 1)
        {
            break;
        }

        // Check if the rule is enabled.
        if (!filter[i]->enabled)
        {
            continue;
        }

        // Do specific IPv6.
        if (ipv6)
        {
            // Source address.
            if (filter[i]->srcIP6 != 0 && saddr != filter[i]->srcIP6)
            {
                continue;
            }

            // Destination address.
            if (filter[i]->dstIP6 != 0 && saddr != filter[i]->dstIP6)
            {
                continue;
            }
        }
        else
        {
            // Source address.
            if (filter[i]->srcIP != 0 && iph->saddr != filter[i]->srcIP)
            {
                continue;
            }

            // Destination address.
            if (filter[i]->dstIP != 0 && iph->daddr != filter[i]->dstIP)
            {
                continue;
            }

            // TOS.
            if (filter[i]->do_tos && filter[i]->tos != iph->tos)
            {
                continue;
            }
        }
        

        // Max TTL length.
        if (filter[i]->do_max_ttl && filter[i]->max_ttl > ttl)
        {
            continue;
        }

        // Min TTL length.
        if (filter[i]->do_min_ttl && filter[i]->min_ttl < ttl)
        {
            continue;
        }

        // Max packet length.
        if (filter[i]->do_max_len && filter[i]->max_len > (len + sizeof(struct ethhdr)))
        {
            continue;
        }

        // Min packet length.
        if (filter[i]->do_min_len && filter[i]->min_len < (len + sizeof(struct ethhdr)))
        {
            continue;
        }

        // PPS.
        if (filter[i]->do_pps &&  pps <= filter[i]->pps)
        {
            continue;
        }

        // BPS.
        if (filter[i]->do_bps && bps <= filter[i]->bps)
        {
            continue;
        }
        
        // Do TCP options.
        if (protocol == IPPROTO_TCP && filter[i]->tcpopts.enabled)
        {
            // Source port.
            if (filter[i]->tcpopts.do_sport && htons(filter[i]->tcpopts.sport) != tcph->source)
            {
                continue;
            }

            // Destination port.
            if (filter[i]->tcpopts.do_dport && htons(filter[i]->tcpopts.dport) != tcph->dest)
            {
                continue;
            }

            // URG flag.
            if (filter[i]->tcpopts.do_urg && filter[i]->tcpopts.urg != tcph->urg)
            {
                continue;
            }

            // ACK flag.
            if (filter[i]->tcpopts.do_ack && filter[i]->tcpopts.ack != tcph->ack)
            {
                continue;
            }

            // RST flag.
            if (filter[i]->tcpopts.do_rst && filter[i]->tcpopts.rst != tcph->rst)
            {
                continue;
            }

            // PSH flag.
            if (filter[i]->tcpopts.do_psh && filter[i]->tcpopts.psh != tcph->psh)
            {
                continue;
            }

            // SYN flag.
            if (filter[i]->tcpopts.do_syn && filter[i]->tcpopts.syn != tcph->syn)
            {
                continue;
            }

            // FIN flag.
            if (filter[i]->tcpopts.do_fin && filter[i]->tcpopts.fin != tcph->fin)
            {
                continue;
            }
        }
        else if (protocol == IPPROTO_UDP && filter[i]->udpopts.enabled)
        {
            // Source port.
            if (filter[i]->udpopts.do_sport && htons(filter[i]->udpopts.sport) != udph->source)
            {
                continue;
            }

            // Destination port.
            if (filter[i]->udpopts.do_dport && htons(filter[i]->udpopts.dport) != udph->dest)
            {
                continue;
            }
        }
        else if (protocol == IPPROTO_ICMP && filter[i]->icmpopts.enabled)
        {
            // Code.
            if (filter[i]->icmpopts.do_code && filter[i]->icmpopts.code != icmph->code)
            {
                continue;
            }

            // Type.
            if (filter[i]->icmpopts.do_type && filter[i]->icmpopts.type != icmph->type)
            {
                continue;
            }
        }
        
        // Matched.
        #ifdef DEBUG
            bpf_printk("Matched rule ID #%" PRIu8 ".\n", filter[i]->id);
        #endif

        matched = 1;
        action = filter[i]->action;
        blocktime = filter[i]->blockTime;

        break;
    }

    if (matched)
    {
        // Increase allowed or blocked entries on stats map.
        if (stats)
        {
            // Update stats map.
            if (action == 0)
            {
                __sync_fetch_and_add(&stats->blocked, 1);
            }
            else
            {
                __sync_fetch_and_add(&stats->allowed, 1);
            }
        }

        #ifdef DEBUG
            //bpf_printk("Matched with protocol %" PRIu8 " and sAddr %" PRIu32 ".\n", iph->protocol, iph->saddr);
        #endif
    }

    if ((matched) && action == 0)
    {
        // Before dropping, update the blacklist map.
        if (blocktime > 0)
        {
            uint64_t newTime = now + (blocktime * 1000000000);

            bpf_map_update_elem(&ip_blacklist_map, &saddr, &newTime, BPF_ANY);
        }

        return XDP_DROP;
    }

    return XDP_PASS;
}

char _license[] SEC("license") = "GPL";