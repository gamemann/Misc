#include <stdio.h>
#include <string.h>

#define MAX_NUMBERS 5

int main()
{
    int nums[MAX_NUMBERS];

    char sFormat[MAX_NUMBERS * 5];

    for (int i = 1; i <= MAX_NUMBERS; i++)
    {
        strcat(sFormat, "%d");

        if (i != MAX_NUMBERS)
        {
            strcat(sFormat, " ");
        }
    }

    //printf("%s\n\n", sFormat);

    printf("Enter your code: ");

    scanf(sFormat, &nums);

    for (int i = 0; i < MAX_NUMBERS; i++)
    {
        printf("\n\nNum #%d - %d", (i + 1), nums[i]);
    }

}