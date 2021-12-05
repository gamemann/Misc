#include <stdio.h>
#include <arpa/inet.h>
#include <inttypes.h>

int main()
{
    uint32_t lol = inet_addr("xxx.xxx.xxx.xxx");

    printf("%" PRIu32 "\n", lol);

    uint16_t port = htons(27005);
    printf("%" PRIu16 "\n", port);

    return 0;
}