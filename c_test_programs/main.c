#include <stdio.h>
#include <float.h>

#include "main.h"

//#define MAX_NAMES 50
#define MAX_NAME_LENGTH 255

void myFunc();

int main() 
{
   for (int i = 1; i <= 5; i++)
   {
      //myFunc();
   }
   
   myFunc();

   return 0;
}

void myFunc()
{
   name userNames[10];

   strcpy(userNames[0].name, "Hailey");
   strcpy(userNames[1].name, "Baylee");
   strcpy(userNames[2].name, "Discover");

   for (int i = 0; i < 3; i++)
   {
      if (userNames[i].name == NULL)
      {
         break;
      }

      printf("%s\n", userNames[i].name);
   }
}