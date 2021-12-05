#include <stdio.h>
#include <stdlib.h>
#include <math.h>

struct opts
{
    int hi;
};

struct lol
{
    int lol;
    struct opts b[256];
} xd;

int main()
{
    for (int i = 0; i < 200; i ++)
    {
        xd.b[i].hi = rand() % 255;
    }

    for (int i = 190; i < 210; i++)
    {
        if (&xd.b[i] != NULL)
        {
            fprintf(stdout, "Item #%d is valid. %d is value\n", i, xd.b[i].hi);
        }
        else
        {
            fprintf(stdout, "Item %d is invalid.\n", i);
        }
        
    }

    

    exit(0);
}