#include <stdio.h>
#include <string.h>
#include <linux/types.h>
#include <arpa/inet.h>

int main()
{
    __u32 lol = 3247732006;
    printf("%u\n", htonl(lol));

    return 0;
}