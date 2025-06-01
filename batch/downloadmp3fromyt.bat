@echo off

if "%1"=="" (
    set /p "url=URL: "
) else (
    set url=%1=%2
)

if "%url%"=="" (
    echo No URL specified
    exit /b 1
)

echo Downloading %url%

yt-dlp --ffmpeg-location .\bin -x --audio-format mp3 --audio-quality 0 -P ".\downloads" %url%

pause
exit