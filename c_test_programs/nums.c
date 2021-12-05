#include <stdio.h>
#include <inttypes.h>

int main()
{
    uint64_t now = 1422284000000000;
    uint64_t val = 6247520740825643776;
    uint64_t diff = (now - val) / 1000000000;

    fprintf(stdout, "Diff => %" PRIu64 ".\n", diff);

    return 0;
}