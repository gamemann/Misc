#include <stdio.h>
#include <inttypes.h>
#include <stdlib.h>

int main()
{
    uint64_t main = 1030230411232311;
    uint32_t one = main >> 0;
    uint32_t two = main >> 32;

    uint64_t lol = (uint64_t) two << 32 | one;

    printf("Main => %" PRIu64 "\nOne => %" PRIu32 "\nTwo=> %" PRIu32 "\nLol => %" PRIu64 "\n", main, one, two, lol);

    return 0;
}