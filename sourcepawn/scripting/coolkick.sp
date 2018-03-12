#include <sourcemod>

public Plugin myinfo =
{
	name = "Coolio Kick",
	author = "Roy (Christian Deacon)",
	description = "Cool.",
	version = "1.0.0",
	url = "GFLClan.com"
};

KeyValues g_hKV = null;

public void OnPluginStart()
{
	char sFile[PLATFORM_MAX_PATH];
	
	BuildPath(Path_SM, sFile, sizeof(sFile), "configs/whitelist.cfg");
	
	g_hKV = new KeyValues("Whitelist");
	g_hKV.ImportFromFile(sFile);
}

public void OnPluginEnd()
{
	if (g_hKV != null)
	{
		delete g_hKV;
	}
}

public void OnClientAuthorized(int iClient, const char[] sAuthID)
{
	bool bFound = false;
	
	if (g_hKV != null)
	{
		if (g_hKV.JumpToKey(sAuthID, false))
		{
			bFound = true;
		}
	}
	
	g_hKV.Rewind();
	
	if (!bFound)
	{
		KickClient(iClient, "YOU'RE NOT ALLOWED.. REEEEEEE");
	}
}