#include <stdio.h>

int main()
{
    char str[] = "Hello, I started this gansta STUFF!";
    char lol[3][32] = {"HELLO!", "THIS IS GANSTA", "STUFF"};

    int i;

    for (i=0; lol[i]; ++i)
    {
        int j;
        for (j = 0; lol[i][j]; ++j)
        {
            printf("%d", j);
        }
    }

    return 0;
}