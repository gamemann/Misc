#include <sourcemod>
#include <sdktools>
#include <sdkhooks>

public Plugin myinfo = 
{
	name = "DodgeBall Model",
	author = "Roy (Christian Deacon)",
	description = "Gives Dodgeball a model. CHICKEN!",
	version = "1.0",
	url = ""
};

public void OnMapStart()
{
	PrecacheModel("models/chicken/chicken.mdl");
}

public int OnEntityCreated(int iEnt, const char[] sClassName)
{
	if (!IsValidEdict(iEnt) || !IsValidEntity(iEnt))
	{
		return;
	}
	
	if (StrContains(sClassName, "decoy_projectile", false) != -1)
	{
		CreateTimer(0.01, Chicken, iEnt);
	}
}

public Action Chicken(Handle hTimer, any iEnt)
{
	if (!IsValidEntity(iEnt) || !IsValidEdict(iEnt))
	{
		return Plugin_Stop;
	}
	
	SetEntityModel(iEnt, "models/chicken/chicken.mdl");
	
	return Plugin_Stop;
}

stock bool IsValidClient(int iClient, bool bBots = false)
{
	if (iClient < 1 || iClient > MaxClients || !IsClientInGame(iClient) || (bBots && IsFakeClient(iClient)))
	{
		return false;
	}	
	
	return true;
}