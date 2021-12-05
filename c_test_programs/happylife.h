#define FLAG_MTR 1
#define FLAG_OPS 2
#define FLAG_INSECURE 3
#define FLAG_PPS 4

extern bool hasFlag(int* iFlags, int iFlag);
extern void setFlag(int* iFlags, int iFlag);