#include <stdio.h>
#include <stdlib.h>
#include <linux/types.h>
#include <string.h>
#include <sys/time.h>

#define ARCH_X86_64

static inline __u32 read_time(void)
{
    __u32 a = 0;
    #if defined(__GNUC__) && (defined(ARCH_X86) || defined(ARCH_X86_64))
    asm volatile( "rdtsc" :"=a"(a) ::"edx" );
    #elif defined(ARCH_PPC)
    asm volatile( "mftb %0" : "=r" (a) );
    #elif defined(ARCH_ARM)     // ARMv7 only
    asm volatile( "mrc p15, 0, %0, c9, c13, 0" : "=r"(a) );
    #endif

    return a;
}

int main(int argc, char *argv[])
{
    unsigned int times = 1000000;

    if (argc > 1)
    {
        times = atoi(argv[1]);        
    }

    struct timeval t1, t2;
    __u32 tm1 = read_time();
    gettimeofday(&t1, NULL);

    unsigned int i;

    for (i = 0; i < times; i++)
    {
        char lol[256];        
        char *data = lol;
        __u32 *xd = NULL;
    }

    __u32 tm2 = read_time();
    gettimeofday(&t2, NULL);

    fprintf(stdout, "%u calls in %.2g seconds (%u)\n", i, t2.tv_sec - t1.tv_sec + 1E-6 * (t2.tv_usec - t1.tv_usec), tm2 - tm1);

    return 0;
}