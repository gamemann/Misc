#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>
#include <time.h>
#include <string.h>
#include <inttypes.h>
#include <errno.h>

extern int errno;

// Returns unsigned 64-bit value of stat.
uint64_t getstat(const char *path)
{
    FILE *fp = fopen(path, "r");
    char buff[255];

    if (fp == NULL)
    {
        fprintf(stderr, "Error parsing stat file (%s) :: %s\n", path, strerror(errno));

        return 0;
    }

    fscanf(fp, "%s", buff);

    fclose(fp);

    return (uint64_t) strtoull((const char *)buff, (char **)buff, 0);
}

int main()
{
    char [256];
    const char *rxpackets = "/sys/class/net/ens18/statistics/rx_packets";

    time_t lastupdate = time(NULL);
    uint64_t totpckts = getstat(rxpackets);

    while(1)
    {
        time_t now = time(NULL);

        if ((now - lastupdate) > 1)
        {
            uint64_t pcktsnow = getstat(rxpackets);      
            uint64_t pps = pcktsnow - totpckts;

            fprintf(stdout, "PPS -> %" PRIu64 "\n", pps);     

            totpckts = getstat(rxpackets);
            lastupdate = time(NULL);
        }

        sleep(1);      
    }

    return 0;
}