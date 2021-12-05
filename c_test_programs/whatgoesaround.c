#include <stdio.h>
#include <string.h>

void hi(unsigned char sStr[], int size)
{
    for (int i = 0; i < size; i++)
    {
        printf("%02X ", sStr[i]);
    }

    printf("\n");
}

int main()
{
    unsigned char ecksD[] = {0x10, 0x20, 0x30, 0x40, 0x45};
    unsigned char ecksD1[] = {0};

    printf("First set...\n");
    hi(ecksD, strlen(ecksD));
    hi(ecksD1, strlen(ecksD1));

    printf("\n\n");
    printf("Second set...\n");
    memcpy(ecksD1, ecksD, strlen(ecksD) - 1);
    hi(ecksD, strlen(ecksD));
    hi(ecksD1, strlen(ecksD1));

    return 0;
}