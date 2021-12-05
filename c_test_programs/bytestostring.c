#include <stdlib.h>
#include <stdio.h>

int main(int argc, char **argv)
{
    if (argc < 1)
    {
        printf("Please input a word.");

        return EXIT_FAILURE;
    }

    char* sMsg = argv[1];

    printf("Message - ");

    for (int i = 0; i < sizeof(sMsg); i++)
    {
        if (&sMsg[i] == 0x00)
        {
            break;
        }

        printf("%x ", sMsg[i]);
    }

    return EXIT_SUCCESS;
}