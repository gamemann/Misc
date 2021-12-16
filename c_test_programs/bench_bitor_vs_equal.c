#include <stdio.h>
#include <time.h>

#define MAX_FOR_LOOP 100000

#define FLAG_ONE (1 << 0)
#define FLAG_TWO (1 << 0)
#define FLAGS (FLAG_ONE | FLAG_TWO)

int eq = FLAGS;

/**
 * @note - Compiling with Clang with no optimization via `clang -O0 -o bench_bitor_vs_equal bench_bitor_vs_equal.c`.
 * @note - Can compile project into Assembly code via `clang -O0 -S -o bench_bitor_vs_equal.s bench_bitor_vs_equal.c`.
**/

int main()
{
    unsigned int i;

    clock_t start;
    clock_t end;
    clock_t ela;

    // Bitwise AND/OR (One Flag).
    start = clock();

    for (i = 0; i < MAX_FOR_LOOP; i++)
    {
        if (i & FLAG_ONE)
        {

        }
    }

    end = clock();
    ela = end - start;

    printf("Bitwise AND/OR (1F) => %lu.\n", ela);


    // Bitwise AND/OR (Two Flags).
    start = clock();

    for (i = 0; i < MAX_FOR_LOOP; i++)
    {
        if (i & (FLAG_ONE | FLAG_TWO))
        {

        }
    }

    end = clock();
    ela = end - start;

    printf("Bitwise AND/OR (2F) => %lu.\n", ela);

    // Equal.
    start = clock();

    for (i = 0; i < MAX_FOR_LOOP; i++)
    {
        if (i == FLAGS)
        {

        }
    }

    end = clock();
    ela = end - start;

    printf("Equal => %lu.\n", ela);

    return 0;
}