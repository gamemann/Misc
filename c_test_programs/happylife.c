#include <stdio.h>
#include <stdbool.h>

#include "happylife.h"

#define XML "XML-Path"

const static int b = 5;
const static int a = 3;

bool hasFlag(int* iFlags, int iFlag)
{
    return (*iFlags & (1 << iFlag)) ? true : false;
}

void setFlag(int* iFlags, int iFlag)
{
    *iFlags = *iFlags | (1 << iFlag);
}

int main()
{
    int f = b % a;

   //printf("%d is the remainder", f);

    int MyFlags = 0;

    setFlag(&MyFlags, FLAG_MTR);
    setFlag(&MyFlags, FLAG_INSECURE);

    printf("%d is flags", MyFlags);

    if (hasFlag(&MyFlags, FLAG_MTR))
    {
        printf("Have MTR flag");
    }
    
    if (hasFlag(&MyFlags, FLAG_INSECURE))
    {
        printf("Have insecure flag");
    }

    return 0;
}