#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>
#include <string.h>
#include <inttypes.h>
#include <math.h>

char eh[] = "FF FF FF FF 49";
unsigned char buffer[0XFFFF];

int main()
{
    memset(buffer, 0, 0xFFFF);
    unsigned char *data = (unsigned char *)(buffer);

    char *token = strtok(eh, " ");

    uint16_t i;

    while (token != NULL)
    {
        uint8_t tmp;

        sscanf(token, "%2hhx", &tmp);

        fprintf(stdout, "Byte #%d => %02x\n", i, tmp);

        *data = tmp;
        *data++;

        i++;

        token = strtok(NULL, " ");
    }

    exit(1);
}