#include <stdio.h>
#include <arpa/inet.h>
#include <linux/types.h>

int main()
{
    __u32 l = 2993910571;

    printf("%u.\n", htonl(l));

    return 0;
}