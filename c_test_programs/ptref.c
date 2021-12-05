#include <stdio.h>
#include <linux/types.h>

int main()
{
    int i = 5;
    int *b = &i;
    int **c = &b;

    __u32 j = 5;
    __u32 *z = &j;

    printf("%lu is i.\n", (long unsigned int )**c);

    if (j + 1 > (__u32 *)c)
    {
        printf("LOLZ\n");
    }

    __u8 xd[1500];
    xd[0] = 3;
    xd[1] = 5;
    xd[2] = 4;
    xd[3] = 1;
    xd[4] = 89;

    __u64 addr = 0;
    addr = (__u64)xd;

    printf("Address is %llu.\n", addr);

    __u8 *lol = (__u8 *)addr;

    printf("0 = %d. 1 = %d. 2 = %d. 3 = %d.\n", *lol, *(lol + 1), *(lol + 2), *(lol + 3));

    return 0;
}