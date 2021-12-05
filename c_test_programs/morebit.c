#include <stdio.h>
#include <math.h>
#include <inttypes.h>

#define FLAG1 (1U << 0) // 0001
#define FLAG2 (1U << 1) // 0010
#define FLAG3 (1U << 2) // 0100

int main()
{
    fprintf(stdout, "%" PRIu32 " is flag 1, %" PRIu32 " is flag 2, and %" PRIu32 " is flag three.\n", FLAG1, FLAG2, FLAG3);

    // 0011
    uint32_t flags1 = FLAG1 | FLAG2;

    // flags1   = 0011
    // ~FLAG1   = 1110
    // &flags1  = 0010
    // 0010
    uint32_t flags2 = flags1 & ~FLAG1;
    return 0;
}