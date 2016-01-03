#include <sourcemod>
#include <sdktools>
#include <sdkhooks>

public Plugin myinfo = 
{
	name = "Entity Classnames",
	author = "Roy (Christian Deacon)",
	description = "Prints classnames everytime an entity is created",
	version = "1.0",
	url = ""
};

Handle g_hFilter = null;

char g_sFilter[MAX_NAME_LENGTH];

public void OnPluginStart()
{
	g_hFilter = CreateConVar("sm_ec_filter", "", "If you want to filter entities.");
	HookConVarChange(g_hFilter, CVarChanged);
}

public OnConfigsExecuted()
{
	GetConVarString(g_hFilter, g_sFilter, sizeof(g_sFilter));
}

public void CVarChanged(Handle hCVar, const char[] sOldV, const char[] sNewV)
{
	OnConfigsExecuted();
}

public int OnEntityCreated(int iEnt, const char[] sClassName)
{
	if (!IsValidEdict(iEnt) || !IsValidEntity(iEnt))
	{
		return;
	}
	
	// All.
	if (StrEqual(g_sFilter, "", false))
	{
		LogToFile("logs/classnames.log", "%i - %s", iEnt, sClassName);
	}
	else
	{
		if (StrContains(sClassName, g_sFilter, false) != -1)
		{
			char sPath[PLATFORM_MAX_PATH];
			Format(sPath, sizeof(sPath), "logs/%s-classnames.log", sClassName);
			LogToFile(sPath, "%i - %s", iEnt, sClassName);
		}
	}
	
}