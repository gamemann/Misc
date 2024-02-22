#!/bin/bash
PATH=/usr/bin
PATTERN="mysharehost"
INTERVAL=5

VERBOSE=1

echo "Starting script to check for reamounts."

while true; do
    if [[ "$VERBOSE" -ge 2 ]]; then
        echo "[SMBAUTOMNT] Checking if we need to remount SMB shares."
    fi

    # Retrieve contents of `mount -t cifs` which lists CIFS and SMB shares.
    contents=$(mount -t cifs)

    if ! echo "$contents" | grep -q $PATTERN; then
        if [[ "$VERBOSE" -ge 2 ]]; then
            echo "[SMBAUTOMNT] Did not find SMB shares. Remounting!"
        fi

        # Remount 
        mount -a
    fi

    sleep $INTERVAL
done
