#include <stdio.h>
#include <stdlib.h>
#include <string.h>

int main()
{
    char buffer[256] = "My name is a pianna";

    char *x;
    x = (char *) (buffer + 5);

    fprintf(stdout, "%s is the string.", x);

    exit(1);
}