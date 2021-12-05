#include <stdio.h>
#include <string.h>
#include <stdlib.h>
#include <unistd.h>

int randNum(int lower, int higher)
{
    return (rand() % (higher - lower + 1)) + lower;
}

int main()
{
    char *msg;
    int i;
    msg = malloc(sizeof(char) * 1024);

    for (i = 0; i < 1023; i++)
    {
        msg[i] = randNum(1, 255);
    }

    for (i = 0; i < 255; i++)
    {
        fprintf(stdout, "%d ", randNum(1, 255));
    }

    msg[1023] = '\0';

    fprintf(stdout, "\n\n%s", msg);

    exit(0);
}