#include <stdio.h>
#include <linux/types.h>

int main()
{
    char buffer[] = "lolz";

    void *pckt = (void *)buffer;

    __u64 addr = (__u64)pckt;

    printf("%llu address.\n", addr);

    char *ptr = (char *)addr;

    if (ptr == NULL)
    {
        printf("what?\n");
    }

    printf("Sup => %s.\n", ptr);

    return 0;
}
