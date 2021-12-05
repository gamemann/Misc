#include <stdio.h>
#include <inttypes.h>

int main()
{
    uint16_t nums[4];
    uint64_t num = 2310051230312123321;
    uint64_t mainnum = 0;

    #if __BYTE_ORDER__ == __ORDER_LITTLE_ENDIAN__
        nums[0] = num >> 0;     // xxxx ---- ---- ----
        nums[1] = num >> 16;    // ---- ---- ---- xxxx
        nums[2] = num >> 32;    // ---- ---- xxxx ----
        nums[3] = num >> 48;    // ---- xxxx ---- ----
    #else
        /*
        nums[0] = num << 0;
        nums[1] = num << 16;
        nums[2] = num << 32;
        nums[3] = num << 48;
        */
    #endif

    printf("Num => %" PRIu64 "\nNum #1 => %" PRIu16 "\nNum #2 => %" PRIu16 "\nNum #3 => %" PRIu16 "\nNum #4 => %" PRIu16 "\n\n", num, nums[0], nums[1], nums[2], nums[3]);

    #if __BYTE_ORDER__ == __ORDER_LITTLE_ENDIAN__
        mainnum |= (uint64_t) nums[0] << 0;
        mainnum |= (uint64_t) nums[1] << 16;
        mainnum |= (uint64_t) nums[2] << 32;
        mainnum |= (uint64_t) nums[3] << 48;
    #else
        /* ... */
    #endif

    uint16_t other[4];

    other[0] = mainnum >> 0;
    other[1] = mainnum >> 16;
    other[2] = mainnum >> 32;
    other[3] = mainnum >> 48;

    printf("Num => %" PRIu64 "\nNum #1 => %" PRIu16 "\nNum #2 => %" PRIu16 "\nNum #3 => %" PRIu16 "\nNum #4 => %" PRIu16 "\n", mainnum, other[0], other[1], other[2], other[3]);

    return 0;
}