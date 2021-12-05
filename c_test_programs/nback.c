#include <stdio.h>
#include <stdbool.h>

#include "nback.h"

int iStacks;

bool isStack()
{
    if (iStacks >= 1)
    {
        return true;
    }

    return false;
}

void setStack(int iAmount)
{
    iStacks = iAmount;
}