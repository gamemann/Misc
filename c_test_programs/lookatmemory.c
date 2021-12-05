#include <stdio.h>
#include <stdlib.h>
#include <time.h>

int *ptr;
int size;

void DoStuff()
{
    if (size > 300)
    {
        free(ptr);
        size = 1;

        // Now let's assignz it.
        ptr = (int *)malloc(size * sizeof(int));
        
        printf("Freeing PTR and setting size back to 1.\n");
    }
    else
    {
        ptr = realloc(ptr, size * sizeof(int));
        ptr[size - 1] = size * 3;
        printf("Allocating more space :) Size is now %d and %d is the value of ptr[%d]\n", size, ptr[size - 1], (size - 1));
    }
    
}

int main()
{
    size = 1;
    ptr = (int *)malloc(size * sizeof(int));

    if (ptr == NULL)
    {
        exit(0);
    }

    // Continuously run.
    while (1)
    {
        DoStuff();
        size++;

        sleep(3);
    }

    return 0;
}