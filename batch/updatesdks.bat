:: File Name: 		update.bat
:: File Author: 	Roy (Christian Deacon)
:: File Date:		6-22-16

@echo off 

:: Variables and such.
SET MAINPATH=E:\Coding\Dependencies\sdks
SET PREFIX=hl2sdk_
SET VERIFY=1
SET SOURCEMODFOLDER=sourcemod-sdk
SET METAMODFOLDER=metamod-sdk

:: HL2SDKs
SET SDKS[0]=csgo
SET SDKS[1]=gmod
SET SDKS[2]=orangebox
SET SDKS[3]=bgt
SET SDKS[4]=episode1
SET SDKS[5]=darkm
SET SDKS[6]=l4d
SET SDKS[7]=l4d2
SET SDKS[8]=eye
SET SDKS[9]=contagion
SET SDKS[10]=swarm
SET SDKS[11]=css
SET SDKS[12]=dods
SET SDKS[13]=hl2dm
SET SDKS[14]=insurgency
SET SDKS[15]=tf2
SET SDKS[16]=nucleardawn
SET SDKS[17]=bms
SET SDKS[18]=blade
SET SDKS[19]=dota
SET SDKS[20]=sdk2013
SET SDKS[21]=portal2

:: Update the HL2SDKs.
echo Updating HL2SDKs...
echo.
timeout /t 1 >nul 2>&1

for /F "tokens=2 delims==" %%s in ('set SDKS[') do (
	cd %MAINPATH%
	
	:: Check if the folder exist or not.
	if exist %MAINPATH%\%PREFIX%%%s (
		:: Verify and Git Pull.
		cd %MAINPATH%\%PREFIX%%%s
		
		if %VERIFY%==1 (
			git checkout .
		)
		
		git pull
	) else (
		:: Make directory + git clone.
		mkdir %MAINPATH%\%PREFIX%%%s >nul 2>&1
		cd %MAINPATH%\%PREFIX%%%s
		git clone -b %%s https://github.com/alliedmodders/hl2sdk.git .
	)
	
	echo %PREFIX%%%s Updated...
	echo.

	timeout /t 1 >nul 2>&1
)

timeout /t 1 >nul 2>&1
echo HL2SDKs updated...

:: Update the SourceMod SDK.
echo Updating SourceMod SDK...
echo.
timeout /t 1 >nul 2>&1

cd %MAINPATH%

:: Check if the folder exist or not.
if exist %MAINPATH%\%SOURCEMODFOLDER% (
	:: Verify and Git Pull.
	cd %MAINPATH%\%SOURCEMODFOLDER%
	
	if %VERIFY%==1 (
		git checkout .
	)
	
	git pull
) else (
	:: Make directory + git clone.
	mkdir %MAINPATH%\%SOURCEMODFOLDER% >nul 2>&1
	cd %MAINPATH%\%SOURCEMODFOLDER%
	git clone -b master --recursive https://github.com/alliedmodders/sourcemod.git .
)

echo SourceMod SDK Updated...
echo.

timeout /t 1 >nul 2>&1

:: Update the MetaMod SDK.
echo Updating MetaMod SDK...
echo.
timeout /t 1 >nul 2>&1

cd %MAINPATH%

:: Check if the folder exist or not.
if exist %MAINPATH%\%METAMODFOLDER% (
	:: Verify and Git Pull.
	cd %MAINPATH%\%METAMODFOLDER%
	
	if %VERIFY%==1 (
		git checkout .
	)
	
	git pull
) else (
	:: Make directory + git clone.
	mkdir %MAINPATH%\%METAMODFOLDER% >nul 2>&1
	cd %MAINPATH%\%METAMODFOLDER%
	git clone -b master --recursive https://github.com/alliedmodders/metamod-source.git .
)

echo MetaMod SDK Updated...
echo.

timeout /t 1 >nul 2>&1

echo Updating Complete!
timeout /t 5 >nul 2>&1
exit