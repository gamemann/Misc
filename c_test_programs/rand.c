#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>
#include <math.h>
#include <inttypes.h>

uint16_t randNum(uint16_t min, uint16_t max, unsigned int seed)
{
    return (rand_r(&seed) % (max - min + 1)) + min;
}

int main()
{
    unsigned int seed = 1;

    uint16_t rand2 = randNum(0, 20, seed);
    uint16_t rand3 = randNum(0, 20, seed + 1);
    uint16_t rand4 = randNum(0, 20, seed + 2);
    uint16_t rand5 = randNum(0, 20, seed);


    fprintf(stdout, "%d/%d/%d/%d\n", rand2, rand3, rand4, rand5);

    exit(1);
}