#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>
#include <string.h>
#include <inttypes.h>

int main()
{
    char *lol;

    fprintf(stdout, "Lol is right now %lu is size.\n", sizeof(lol));

    char str[] = "203010401";
    size_t keysz = strlen(str);

    lol = malloc(keysz);

    fprintf(stdout, "Lol is now size %lu (%lu)\n", sizeof(lol), keysz);

    memcpy(lol, str, keysz);

    fprintf(stdout, "Lol is => ");

    for (int i = 0; i < keysz; i++)
    {
        
        fprintf(stdout, "%" PRIu8, atoi(lol + i));
    }

    return 0;
}