#include <stdio.h>
#include <sys/time.h>
#include <unistd.h>
#include <time.h>

void customSleep(float seconds)
{
    struct timeval tv = {0, ((seconds * 1000) * 1000000L)};

    nanosleep(&tv, NULL);
}

int main()
{
    printf("Hi!\n");
    customSleep(5.5);
    printf("Hola!");

    return 1;
}