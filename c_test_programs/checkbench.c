#include <stdio.h>
#include <stdlib.h>
#include <time.h>

#define MAX_LOOP 1e6

int main(int argc, char **argv)
{
    unsigned int i;
    unsigned int test;
    unsigned int test2;

    printf("Argument Count => %d.\n\n", argc);

    test = (argc > 1) ? atoi(argv[1]) : 1;

    clock_t start;
    clock_t end;
    clock_t ela;

    start = clock();

    for (i = 0; i < MAX_LOOP; i++)
    {
    }

    end = clock();
    ela = end - start;

    printf("Empty #0 => %lu.\n", ela);

    start = clock();

    for (i = 0; i < MAX_LOOP; i++)
    {
        if (test == 3)
        {
        }
    }

    end = clock();
    ela = end - start;

    printf("Check #1 => %lu.\n", ela);

    start = clock();

    for (i = 0; i < MAX_LOOP; i++)
    {
        if (test == 50)
        {
        }

        if (test == 30)
        {
        }

        if (test == 40)
        {
        }

        if (test == 60)
        {
        }

        if (test == 10)
        {
        }
    }

    end = clock();
    ela = end - start;

    printf("Check #2 => %lu.\n", ela);

    start = clock();

    for (i = 0; i < MAX_LOOP; i++)
    {
        if (test == 50 || test == 30 || test == 40 || test == 60 || test == 10)
        {
        }
    }

    end = clock();
    ela = end - start;

    printf("Check #3 => %lu.\n", ela);

    start = clock();

    for (i = 0; i < MAX_LOOP; i++)
    {
        if (test == 1)
        {
            test2 += 2;
        }
    }

    end = clock();
    ela = end - start;

    printf("Check #4 => %lu.\n\n", ela);
}