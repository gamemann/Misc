#include <stdio.h>
#include <stdint.h>
#include <arpa/inet.h>

// Function to check if the source IP is within the network IP/CIDR range
int isIPInRange(uint32_t sourceIp, uint32_t networkIp, uint8_t cidr) {
    // Calculate the subnet mask
    return !((sourceIp ^ networkIp) & htonl(0xFFFFFFFFu << (32 - cidr)));
}

int main() {
    const char *sourceIpStr = "192.168.1.3";
    const char *networkIpStr = "192.168.2.0";
    uint8_t cidr = 24;

    struct in_addr sourceIp;

    if (inet_pton(AF_INET, sourceIpStr, &sourceIp) <= 0) {
        perror("inet_pton");

        return 1;
    }

    struct in_addr networkIp;

    if (inet_pton(AF_INET, networkIpStr, &networkIp) <= 0) {
        perror("inet_pton");

        return 1;
    }

    if (isIPInRange(sourceIp.s_addr, networkIp.s_addr, cidr))
        printf("%s is in range of %s/%d!\n", sourceIpStr, networkIpStr, cidr);
    else
        printf("%s is NOT in range of %s/%d!\n", sourceIpStr, networkIpStr, cidr);

    return 0;
}