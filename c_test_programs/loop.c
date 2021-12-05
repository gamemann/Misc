#include <stdio.h>

#include "exement.h"
#include "nback.c"

int iMaps;

int getMaps()
{
    return 33;
}

void changeMaps()
{
    iMaps = 9;
    
    printf("%d is iMaps", iMaps);
}

int StackTrace()
{
    return iMaps * iStacks;
}