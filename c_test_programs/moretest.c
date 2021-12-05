#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <inttypes.h>

char *uint8tob( uint8_t value ) {
  static uint8_t base = 2;
  static char buffer[8] = {0};

  int i = 8;
  for( ; i ; --i, value /= base ) {
    buffer[i] = "01"[value % base];
  }

  return &buffer[i+1];
}

char *convert_bytes_to_binary_string( uint8_t *bytes, size_t count ) {
  if ( count < 1 ) {
    return NULL;
  }

  size_t buffer_size = 8 * count + 1;
  char *buffer = calloc( 1, buffer_size );
  if ( buffer == NULL ) {
    return NULL;
  }

  char *output = buffer;
  for ( int i = 0 ; i < count ; i++ ) {
    memcpy( output, uint8tob( bytes[i] ), 8 );
    output += 8;
  }

  return buffer;
};


int main()
{
    uint8_t *data = (uint8_t *)872419136;
    char *stuff = convert_bytes_to_binary_string(data, 4);

    printf("%s", stuff);

    return 0;
}