:: File Name: 		update.bat
:: File Author: 	Roy (Christian Deacon)
:: File Date:		6-22-16
:: File Version: 	1.1 (6-26-16)

@echo off 

:: Variables and such.
SET MAINPATH=E:\Coding\Dependencies\sdks
SET VERIFY=1

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

SET SDKSPREFIX=hl2sdk-

:: SourceMod Branches. Note: I only did this for the ones I use.
SET SM[0]=master
SET SM[1]=1.3-dev
SET SM[2]=1.4-dev
SET SM[3]=1.7-dev
SET SM[4]=1.5-dev

SET SMPREFIX=sourcemod-

:: MetaMod Branches. Note: I only did this for the ones I use.
SET MM[0]=master
SET MM[1]=1.7-dev
SET MM[2]=1.8-dev
SET MM[3]=1.9-dev

SET MMPREFIX=metamod-

:: Update the HL2SDKs.
echo Updating HL2SDKs...
echo.
timeout /t 1 >nul 2>&1

for /F "tokens=2 delims==" %%s in ('set SDKS[') do (
	:: Check if the folder exist or not.
	if exist %MAINPATH%\%SDKSPREFIX%%%s (
		:: Verify and Git Pull.
		cd %MAINPATH%\%SDKSPREFIX%%%s
		
		if %VERIFY%==1 (
			git checkout .
		)
		
		git pull
	) else (
		:: Make directory + git clone.
		mkdir %MAINPATH%\%SDKSPREFIX%%%s >nul 2>&1
		cd %MAINPATH%\%SDKSPREFIX%%%s
		git clone -b %%s https://github.com/alliedmodders/hl2sdk.git .
	)
	
	echo %SDKSPREFIX%%%s Updated...
	echo.

	timeout /t 1 >nul 2>&1
)

timeout /t 1 >nul 2>&1
echo HL2SDKs updated...

:: Update the SourceMod SDK.
echo Updating SourceMod SDKs...
echo.
timeout /t 1 >nul 2>&1

for /F "tokens=2 delims==" %%s in ('set SM[') do (
	:: Check if the folder exist or not.
	if exist %MAINPATH%\%SMPREFIX%%%s (
		:: Verify and Git Pull.
		cd %MAINPATH%\%SMPREFIX%%%s
		
		if %VERIFY%==1 (
			git checkout .
		)
		
		git pull
	) else (
		:: Make directory + git clone.
		mkdir %MAINPATH%\%SMPREFIX%%%s >nul 2>&1
		cd %MAINPATH%\%SMPREFIX%%%s
		git clone -b %%s --recursive https://github.com/alliedmodders/sourcemod.git .
	)
	
	echo %SMPREFIX%%%s Updated...
	echo.

	timeout /t 1 >nul 2>&1
)

echo SourceMod SDKs Updated...
echo.
timeout /t 1 >nul 2>&1

:: Update the MetaMod SDK.
echo Updating MetaMod SDKs...
echo.
timeout /t 1 >nul 2>&1

for /F "tokens=2 delims==" %%s in ('set MM[') do (
	:: Check if the folder exist or not.
	if exist %MAINPATH%\%MMPREFIX%%%s (
		:: Verify and Git Pull.
		cd %MAINPATH%\%MMPREFIX%%%s
		
		if %VERIFY%==1 (
			git checkout .
		)
		
		git pull
	) else (
		:: Make directory + git clone.
		mkdir %MAINPATH%\%MMPREFIX%%%s >nul 2>&1
		cd %MAINPATH%\%MMPREFIX%%%s
		git clone -b %%s --recursive https://github.com/alliedmodders/metamod-source.git .
	)
	
	echo %MMPREFIX%%%s Updated...
	echo.

	timeout /t 1 >nul 2>&1
)

echo MetaMod SDKs Updated...
echo.

timeout /t 1 >nul 2>&1

echo Updating Complete!
timeout /t 5 >nul 2>&1
exit