#include <stdio.h>
#include <string.h>

int main()
{
    unsigned char sLol[12] = "Hello!";
    unsigned char *ptr = sLol;

    for (int i = 0; i < 3; i++)
    {
        fprintf(stdout, "%c\n", *(ptr + i));
    }

    fprintf(stdout, "\n\n%c", *(ptr + 1));

    return 0;
}