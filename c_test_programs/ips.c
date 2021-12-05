#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <linux/types.h>

#include <arpa/inet.h>

int main()
{
    char range[32] = "10.0.0.0/24";
    char *ptr = NULL;

    ptr = strtok(range, "/");

    if (ptr == NULL)
    {
        fprintf(stderr, "PTR NULL 1\n");

        return 1;
    }

    char net[32];
    strcpy(net, ptr);

    ptr = strtok(NULL, "/");

    if (ptr == NULL)
    {
        fprintf(stderr, "PTR NULL 2\n");

        return 1;
    }

    char cidr[4];
    strcpy(cidr, ptr);

    __u8 c = atoi(cidr);

    struct in_addr addr = {0};
    inet_pton(AF_INET, net, &addr);

    fprintf(stdout, "Net => %s. CIDR => %s. Net IP => %llu.\n", net, cidr, addr.s_addr);

    

    return 0;
}