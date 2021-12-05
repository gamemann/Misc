#include <stdio.h>
#include <linux/types.h>

int main()
{
    __u64 ts = 479416627093127;
    __u32 lol = (ts / 1000000);
    __u64 newts = (__u64)((__u64)lol * 1000000);

    printf("ts => %llu. lol => %u. newts => %llu.\n", ts, lol, newts);

    return 0;
}