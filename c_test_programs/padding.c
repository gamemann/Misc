#include <stdio.h>
#include <stdint.h>

struct test_one {
    uint32_t field1;
    uint16_t field2;
} typedef TestOne;

struct test_two {
    TestOne field1;
    uint8_t field2;
} typedef TestTwo;

int main() {
    TestOne testOne = {
        .field1 = 1234567,
        .field2 = 1234
    };

    TestTwo testTwo = {
        .field1 = testOne,
        .field2 = 123
    };

    // Output is 12 bytes, 8 bytes, and 1 byte sizes.
    printf("%lu bytes is sizeof testTwo. %lu bytes is sizeof testOne. %lu bytes is sizeof testTwo.field2.\n", sizeof(testTwo), sizeof(testOne), sizeof(testTwo.field2));

    return 0;
}
