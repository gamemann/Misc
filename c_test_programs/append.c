#include <stdio.h>

int main()
{
    FILE *fp = fopen("xd.txt", "a");

    if (fp)
    {
        fprintf(fp, "This is a text\n");
        fprintf(stdout, "Wrote to file.\n");

        fclose(fp);
    }

    return 0;
}