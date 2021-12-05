#include <linux/bpf.h>
#include <linux/bpf_common.h>

#include <inttypes.h>
#include <linux/if_ether.h>
#include <linux/ip.h>
#include <linux/icmp.h>
#include <linux/in.h>

#include "/home/dev/XDP-Firewall/libbpf/src/bpf_helpers.h"

#define bpf_printk(fmt, ...)					\
({								\
	       char ____fmt[] = fmt;				\
	       bpf_trace_printk(____fmt, sizeof(____fmt),	\
				##__VA_ARGS__);			\
})

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

struct bpf_map_def SEC("maps") icmpv6_map_test =
{
    .type = BPF_MAP_TYPE_PERCPU_HASH,
    .key_size = sizeof(uint32_t),
    .value_size = sizeof(sizeof(struct icmphdr)),
    .max_entries = 64000
};

struct bpf_map_def SEC("maps") ip_map_test =
{
    .type = BPF_MAP_TYPE_PERCPU_HASH,
    .key_size = sizeof(uint32_t),
    .value_size = sizeof(struct iphdr),
    .max_entries = 64000
};

SEC("xdp_prog")
int xdp_prog_main(struct xdp_md *ctx)
{
    void *data = (void *)(long)ctx->data;
    void *data_end = (void *)(long)ctx->data_end;

    struct ethhdr *eth = (data);

    if (eth + 1 > (struct ethhdr *)data_end)
    {
        return XDP_DROP;
    }

    if (eth->h_proto != htons(ETH_P_IP))
    {
        return XDP_PASS;
    }

    struct iphdr *iph = (data + sizeof(struct ethhdr));

    if (iph + 1 > (struct iphdr *)data_end)
    {
        return XDP_DROP;
    }

    bpf_map_update_elem(&ip_map_test, &iph->saddr, iph, BPF_ANY);

    if (iph->protocol == IPPROTO_ICMP)
    {
        struct icmphdr *icmph = (data + sizeof(struct ethhdr) + (iph->ihl * 4));

        if (icmph + 1 > (struct icmphdr *)data_end)
        {
            return XDP_DROP;
        }

        bpf_map_update_elem(&icmpv6_map_test, &iph->saddr, icmph, BPF_ANY);
    }

    struct icmphdr *info = bpf_map_lookup_elem(&icmpv6_map_test, &iph->saddr);

    if (info)
    {
        bpf_printk("Code = > %" PRIu8 " | Type => %" PRIu8 " | Saddr => %" PRIu32 "\n", info->code, info->type, iph->saddr);

        bpf_map_delete_elem(&icmpv6_map_test, &iph->saddr);
    }

    struct iphdr *ipinfo = bpf_map_lookup_elem(&ip_map_test, &iph->saddr);

    if (ipinfo)
    {
        bpf_printk("TTL = > %" PRIu8 " | ID => %" PRIu16 " | Saddr => %" PRIu32 "\n", ipinfo->ttl, ipinfo->id, iph->saddr);

        bpf_map_delete_elem(&ip_map_test, &iph->saddr);
    }

    return XDP_PASS;
}

char _license[] SEC("license") = "GPL";