#include <stdio.h>
#include <time.h>

int main()
{
    time_t time1 = 1;
    time_t time2 = 128;

    time_t shift1 = time1 >> 6;
    time_t shift2 = time2 >> 6;

    printf("Time #1 => %lu.\n", shift1);
    printf("Time #2 => %lu.\n", shift2);

    return 0;
}