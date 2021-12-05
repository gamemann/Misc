#include <stdio.h>

int main()
{
    int a = 1;
    int b = 2;
    int c = 2;
    int d = 1;

    if (((a == 1 && b == 2) || (a == 2 && c == 2)) && d)
    {
        printf("Good.\n");
    }

    return 0;
}