#include <stdint.h>
#include <stdio.h>
#include <arpa/inet.h>

int main() {
    uint16_t port = 36895;

    printf("%d => %d.\n", port, ntohs(port));

    return 0;
}