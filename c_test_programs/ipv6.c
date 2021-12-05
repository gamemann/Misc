#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>
#include <linux/ipv6.h>
#include <arpa/inet.h>
#include <inttypes.h>
#include <string.h>
#include <errno.h>

typedef unsigned __int128 uint128_t;

#define P10_UINT64 10000000000000000000ULL
#define E10_UINT64 19

#define STRINGIZER(x)   # x
#define TO_STRING(x)    STRINGIZER(x)

static int print_u128_u(uint128_t u128)
{
    int rc;

    if (u128 > UINT64_MAX)
    {
        uint128_t leading  = u128 / P10_UINT64;
        uint64_t  trailing = u128 % P10_UINT64;
        rc = print_u128_u(leading);
        rc += printf("%." TO_STRING(E10_UINT64) PRIu64, trailing);
    }
    else
    {
        uint64_t u64 = u128;
        rc = printf("%" PRIu64, u64);
    }

    return rc;
}

int main()
{
    const char *ipaddr = "3FFE:0000:0000:0001:0200:F8FF:FE75:50DF";
    struct in6_addr in;
    inet_pton(AF_INET6, ipaddr, &in);

    fprintf(stdout, "First => ");
    print_u128_u(in.in6_u.u6_addr32[0]);
    fprintf(stdout, "\n");

    fprintf(stdout, "Second => ");
    print_u128_u(in.in6_u.u6_addr32[1]);
    fprintf(stdout, "\n");

    fprintf(stdout, "Third => ");
    print_u128_u(in.in6_u.u6_addr32[2]);
    fprintf(stdout, "\n");

    fprintf(stdout, "Fourth => ");
    print_u128_u(in.in6_u.u6_addr32[3]);
    fprintf(stdout, "\n");

    uint128_t myip = in.in6_u.u6_addr32[0] << in.in6_u.u6_addr32[1] << in.in6_u.u6_addr32[2] << in.in6_u.u6_addr32[3];

    uint128_t corip = 85060207136517546229177723905438470367;

    fprintf(stdout, "Correct IP => ");
    print_u128_u(corip);
    fprintf(stdout, "\n");

    fprintf(stdout, "IP => ");
    print_u128_u(myip);
    fprintf(stdout, "\n");

    char *newip = malloc(sizeof(char) * INET6_ADDRSTRLEN);
    
    inet_ntop(AF_INET6, &in, newip, INET6_ADDRSTRLEN);

    if (newip == NULL)
    {
        fprintf(stdout, "Error => %s\n", strerror(errno));
    }

    fprintf(stdout, "New IP => %s\n", newip);

    return 0;
}