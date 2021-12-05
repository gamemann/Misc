#include <linux/bpf.h>
#include <linux/bpf_common.h>

#include <inttypes.h>
#include <linux/if_ether.h>
#include <linux/ip.h>
#include <linux/tcp.h>
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

struct connection
{
    uint64_t lastseen;
    uint64_t count;

    uint32_t clientaddr;
    uint16_t srcport;

};

struct bpf_map_def SEC("maps") connection_map =
{
    .type = BPF_MAP_TYPE_LRU_HASH,
    .key_size = sizeof(uint32_t),
    .value_size = sizeof(uint16_t),
    .max_entries = 65535
};

struct bpf_map_def SEC("maps") port_map =
{
    .type = BPF_MAP_TYPE_LRU_HASH,
    .key_size = sizeof(uint16_t),
    .value_size = sizeof(struct connection),
    .max_entries = 65535
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

    if (iph->protocol == IPPROTO_TCP)
    {
        struct tcphdr *tcph = data + sizeof(struct ethhdr) + (iph->ihl * 4);

        if (tcph + 1 > (struct tcphdr *)data_end)
        {
            return XDP_DROP;
        }

        uint64_t now = bpf_ktime_get_ns();

        uint16_t *sport = bpf_map_lookup_elem(&connection_map, &iph->saddr);

        if (sport)
        {
            struct connection *conn = bpf_map_lookup_elem(&port_map, sport);

            if (conn)
            {
                if (conn->clientaddr == iph->saddr)
                {
                    bpf_printk("Using port %" PRIu16 " with %" PRIu32 "\n", *sport, iph->saddr);
                    conn->lastseen = now;

                    return XDP_PASS;
                }
            }

            bpf_map_delete_elem(&connection_map, &iph->saddr);
        }

        // Look for available ports.
        uint16_t port = 0;
        uint64_t smallest = UINT64_MAX;

        for (uint32_t i = 1; i <= 64000; i++)
        {
            uint16_t tmp = (uint16_t)i;

            struct connection *conn = bpf_map_lookup_elem(&port_map, &tmp);

            if (!conn)
            {
                port = tmp;

                break;
            }
            else
            {
                if (conn->lastseen < smallest)
                {
                    smallest = conn->lastseen;
                    port = tmp;
                }
            }
        }

        if (port > 0)
        {
            // New entry.
            bpf_map_update_elem(&connection_map, &iph->saddr, &port, BPF_ANY);

            struct connection conn = {0};
            conn.clientaddr = iph->saddr;
            conn.lastseen = now;
            conn.srcport = port;

            bpf_map_update_elem(&port_map, &port, &conn, BPF_ANY);
        }
        
    }

    return XDP_PASS;
}

char _license[] SEC("license") = "GPL";