#include <stdio.h>
#include <inttypes.h>

#include <arpa/inet.h>

int main()
{
    struct in_addr sin;
    inet_pton(AF_INET, "xxx.xxx.xxx.xxx", &sin);

    fprintf(stdout, "%" PRIu32 "\n", sin.s_addr);

    return 0;
}