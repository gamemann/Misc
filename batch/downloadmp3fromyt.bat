@echo off

set url=%1=%2

echo Downloading %url%

if "%url%"=="" (
    echo No URL specified
    exit /b 1
)

.\yt-dlp.exe --ffmpeg-location .\bin -x --audio-format mp3 --audio-quality 0 -P ".\downloads" %url%
