# Auto Mount Samba Shares (Linux)
This small Bash script checks the contents of the `mount -t cifs` command every `$INTERVAL` seconds (default 5). If the contents of `mount -t cifs` doesn't contain `$PATTERN`, which will typically be the host of the Samba share you want always mounted, it will execute the `mount -a` command to remount drives, including Samba shares, from `/etc/fstab`.

For months now, I've been experiencing issues related to my Samba shares not remounting when the network drops/reconnects or on boot up on my Ubuntu 22.04 laptop. My guess as to why it didn't work on bootup regardless of being in `/etc/fstab` is due to my WiFi adapter/driver taking time to load.

After switching my laptop's Linux distro to Fedora 39, I decided it was time to find a solution. I wasn't able to find any suitable solutions using Google, so I ended up making this Bash script.

## Systemd Service
You can move the `auto_mnts_smbs.service` file to `/etc/systemd/system/auto_mnts_smbs.service`. Afterwards, you can enable it so that it starts on boot along with start it at the same time.

```bash
# Move service file to correct Systemd directory.
cp -f auto_mnt_smbs.service /etc/systemd/system/auto_mnt_smbs.service

# Enable the service on boot and start.
systemctl enable --now auto_mnt_smbs

# Start service.
systemctl start auto_mnt_smbs

# Stop service.
systemctl stop auto_mnt_smbs

# Disable service (remove from startup).
systemctl disable auto_mnt_smbs
```

## Utilize `/etc/fstab`
For this script to run properly, you will want to mount your Samba shares in `/etc/fstab`. Read [this](https://timlehr.com/2018/01/auto-mount-samba-cifs-shares-via-fstab-on-linux/index.html) guide for more information!
