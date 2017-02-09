#!/bin/bash
FILENAME="$1.mp3"
FILENAME_BOOSTED="$1_$3.mp3"
COMPLETEDIR=/home/gamemannn/music

# Download from YouTube using youtube-dl (along with additional options).
echo "Downloading $1 from URL $2"
youtube-dl -x --audio-format "mp3" -o "$1.%(ext)s" $2

# Raise the volume by using FFMPEG
echo "Raising the volume!"
ffmpeg -i "$FILENAME" -af "volume=$3" "$FILENAME_BOOSTED"

# Move the file to the correct directory.
echo "Moving file!"
mv -f "$FILENAME_BOOSTED" $COMPLETEDIR

# Delete the useless file.
echo "Deleting useless files."
rm -f "$FILENAME"

