#include <stdio.h>

struct unoptimized
{
    int x;
    int y;
} unopt;

struct optimized
{
    unsigned int x : 3;
    unsigned int y : 3;
} opt;

int main()
{
    opt.x = 8;
    opt.y = 7;

    unopt.x = 3;
    unopt.y = 2;

    printf("%d is the sizeof unoptimized struct.\n", sizeof(unopt));
    printf("%d is the sizeof opt\n", sizeof(opt));

    printf("%d is the value of opt.x and %d is the value of opt.y\n", opt.x, opt.y);

    return 0;
}