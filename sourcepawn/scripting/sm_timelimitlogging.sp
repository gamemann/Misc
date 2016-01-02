#include <sourcemod>

public Plugin:myinfo =
{
	name = "TimeLimit Logging",
	author = "Roy (Christian Deacon)",
	description = "Logs when the time limit is changed.",
	version = "1.0",
	url = "GFLClan.com & TheDevelopingCommunity.com"
};

// ConVars
new Handle:g_hThreshold = INVALID_HANDLE;

// Values
new g_iThreshold;

public OnPluginStart()
{
	g_hThreshold = CreateConVar("sm_tll_threshold", "1", "When the time limit is increased equal to or over this number, logging begins");
	HookConVarChange(g_hThreshold, funcConVarChanged);
	
	AddCommandListener(funcCommandCalled, "mp_timelimit");
}

public funcConVarChanged(Handle:hEvent, const String:sOldV[], const String:sNewV[])
{
	OnConfigsExecuted();
}

public OnConfigsExecuted()
{
	g_iThreshold = GetConVarInt(g_hThreshold);
}

public Action:funcCommandCalled(iClient, const String:sName[], iArgc)
{
	new iValue;
	decl String:sValue[32];
	
	GetCmdArg(1, sValue, sizeof(sValue));
	iValue = StringToInt(sValue);
	
	if (iValue >= g_iThreshold)
	{
		decl String:sDate[MAX_NAME_LENGTH];
		FormatTime(sDate, sizeof(sDate), "%m-%d-%y", GetTime());
		
		new String:sPath[PLATFORM_MAX_PATH];
		BuildPath(Path_SM, sPath, sizeof(sPath), "logs/GFL/tll_%s.log", sDate);
		
		new Handle:hFile = OpenFile(sPath, "a");
		
		if (hFile != INVALID_HANDLE)
		{
			decl String:sFullMsg[256], String:sFullDate[256], String:sAdminName[MAX_NAME_LENGTH], String:sSteamID[64], String:sMapName[MAX_NAME_LENGTH];
			FormatTime(sFullDate, sizeof(sFullDate), "%c", GetTime());
			GetClientName(iClient, sAdminName, sizeof(sAdminName));
			GetClientAuthId(iClient, AuthId_Steam2, sSteamID, sizeof(sSteamID));
			GetCurrentMap(sMapName, sizeof(sMapName));
			
			Format(sFullMsg, sizeof(sFullMsg), "%s :: %s (%s) has executed \"mp_timelimit\" and changed the value to %i on map %s", sFullDate, sAdminName, sSteamID, iValue, sMapName);
			
			CloseHandle(hFile);
		}
	}
	
	return Plugin_Handled;
}