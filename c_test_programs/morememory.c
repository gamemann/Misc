#include <stdio.h>
#include <stdlib.h>

#define TRUE 1
#define FALSE 0

int main()
{
    // Allocate memory.
    int *main = (int*)malloc(1 * sizeof(int));

    main[0] = 5;

    if (main[0] == 5)
    {
        // We must reallocate memory and add more.
        main = realloc(main, 25 * sizeof(int));

        if  (main == NULL)
        {
            return 0;
        }

        for (int i = 1; i < 25; i++)
        {
            main[i] = (i + 1) * 3;
        }
    }

    for (int i = 0; i < 25; i++)
    {
        printf("%d, ", main[i]);
    }

    return 0;
}