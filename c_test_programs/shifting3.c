#include <stdio.h>
#include <linux/types.h>

int main()
{
    __u8 one = 1;
    __u16 x = (__u16)one << 8;

    printf("%u\n", x);

    return 0;
}