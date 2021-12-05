#include <stdio.h>
#include <linux/types.h>

int main()
{
    __u16 x = 65535;

    printf("%u\n", x);

    __u16 y = x >> 16;
    
    printf("%u\n", y);

    return 0;
}