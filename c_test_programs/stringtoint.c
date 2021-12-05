#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <errno.h>
#include <linux/types.h>

__u32 strToIntValue(char *str)
{
    char *ptr;
    __u32 val;

    val = strtol(str, &ptr, 10);

    return val;
}

int main()
{
    char lol[] = "803905723;27015;16843009;443";

    char *ptr = strtok(lol, ";");

    if (ptr == NULL)
    {
        printf("Bad split.");

        return 1;
    }

    __u32 srcip = strToIntValue(ptr);

    ptr = strtok(NULL, ";");

    if (ptr == NULL)
    {
        printf("Source port fail.");

        return 1;
    }

    __u16 srcport = atoi(ptr);

    ptr = strtok(NULL, ";");

    if (ptr == NULL)
    {
        printf("Destination IP fail.");

        return 1;
    }

    __u32 dstip = strToIntValue(ptr);

    ptr = strtok(NULL, ";");

    if (ptr == NULL)
    {
        printf("Destination port fail.");

        return 1;
    }

    __u16 dstport = atoi(ptr);

    printf("%lu:%d => %lu:%d", srcip, srcport, dstip, dstport);

    return 0;
}