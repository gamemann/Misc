#include <stdio.h>
#include <time.h>
#include <stdlib.h>

int main()
{
    time_t t;
    srand((unsigned) time(&t));

    int r = rand() % 4;

    if (r)
    {
        return 1;
    }

    return 0;
}