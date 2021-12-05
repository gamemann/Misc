#include <stdio.h>

int main()
{
    int i[] = {12, 50, 90, 20, 30, 40, 45};

    printf("%d is the sizeof i and %d is the sizeof i[0]", (int) sizeof(i), (int) sizeof(i[0]));

    return 0;
}