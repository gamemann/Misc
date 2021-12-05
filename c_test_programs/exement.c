#include <stdio.h>

#include "exement.h"
#include "loop.c"

int main()
{
    printf("%d is iMaps\n\n", iMaps);

    printf("%d is GetMaps();", getMaps());

    setStack(30);

    changeMaps();

    printf("iMaps is now %d\n\n", iMaps);

    printf("iStacks is %d while stack trace is %d\n\n", iStacks, StackTrace());

    return 0;
}