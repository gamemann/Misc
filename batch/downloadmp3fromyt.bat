@echo off

IF "%1"=="" (
    set /p "url=URL: "
) ELSE (
    set url=%1=%2
)

echo Downloading %url%

if "%url%"=="" (
    echo No URL specified
    exit /b 1
)

yt-dlp --ffmpeg-location .\bin -x --audio-format mp3 --audio-quality 0 -P ".\downloads" %url%

pause
exit