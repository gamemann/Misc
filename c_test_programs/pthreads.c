#include <stdio.h>
#include <stdlib.h>
#include <pthread.h>
#include <inttypes.h>

uint64_t count;

void *threadhdnl(void *data)
{
    while(1)
    {
        if (__sync_add_and_fetch(&count, 1) > 10000)
        {
            fprintf(stdout, "Thread count reached on %lu\n", pthread_self());

            break;
        }
        else
        {
            fprintf(stdout, "Count %" PRIu64 "/10000\n", count);
        }
    }

    pthread_exit(NULL);
}

int main()
{
    for (int i = 0; i < 300; i++)
    {
        pthread_t pid;

        pthread_create(&pid, NULL, threadhdnl, NULL);
    }

    return 0;
}