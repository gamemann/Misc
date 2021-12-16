#include <stdio.h>

#define FLAG_ONE (1 << 0)
#define FLAG_TWO (1 << 1)
#define FLAG_THREE (1 << 2)
#define MIXED (FLAG_ONE | FLAG_TWO | FLAG_THREE)

int main()
{
    int flags = 0;

    flags = ((flags | FLAG_ONE) | (flags | FLAG_TWO) | (flags | FLAG_THREE));

    printf("%d/%d\n", flags, MIXED);
}