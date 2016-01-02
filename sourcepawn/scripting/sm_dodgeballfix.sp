#include <sourcemod>
#include <sdktools>

public Plugin myinfo =
{
	name = "DodgeBall Model Fix",
	author = "Roy (Christian Deacon)",
	description = "Changes the human models to something Valve didn't break.",
	version = "1.0",
	url = ""
};

public void OnPluginStart()
{
	HookEvent("player_spawn", Event_PlayerSpawn);
}

public void OnMapStart()
{
	// Precache models.
	PrecacheModel("models/player/custom_player/legacy/prisioner/prisioner.mdl");
	PrecacheModel("models/player/custom_player/legacy/security/security.mdl");
	
	// Add downloads.
	AddFileToDownloadsTable("models/player/custom_player/legacy/prisioner/prisioner.mdl");
	AddFileToDownloadsTable("models/player/custom_player/legacy/prisioner/prisioner.dx90.vtx");
	AddFileToDownloadsTable("models/player/custom_player/legacy/prisioner/prisioner.phy");
	AddFileToDownloadsTable("models/player/custom_player/legacy/prisioner/prisioner.vvd");
	 
	 
	AddFileToDownloadsTable("materials/models/player/custom/prisioner/DiffOpmk00.vmt");
	AddFileToDownloadsTable("materials/models/player/custom/prisioner/DiffOpmk00.vtf");
	AddFileToDownloadsTable("materials/models/player/custom/prisioner/Norm00.vtf");

	AddFileToDownloadsTable("models/player/custom_player/legacy/security/security.mdl");
	AddFileToDownloadsTable("models/player/custom_player/legacy/security/security.dx90.vtx");
	AddFileToDownloadsTable("models/player/custom_player/legacy/security/security.phy");
	AddFileToDownloadsTable("models/player/custom_player/legacy/security/security.vvd");
	 
	AddFileToDownloadsTable("materials/models/player/custom/security/Diff00_2.vmt");
	AddFileToDownloadsTable("materials/models/player/custom/security/Diff00_2.vtf");
	AddFileToDownloadsTable("materials/models/player/custom/security/Norm00_2.vtf");
}

public Action Event_PlayerSpawn(Event eEvent, const char[] sName, bool bDontBroadcast)
{
	new iClient = GetClientOfUserId(GetEventInt(eEvent, "userid"));
	
	if (IsClientInGame(iClient) && IsPlayerAlive(iClient))
	{
		int iTeam = GetClientTeam(iClient);
		
		if (iTeam == 2)
		{
			// Terrorist (Prisoner).
			SetEntityModel(iClient, "models/player/custom_player/legacy/prisioner/prisioner.mdl");
		}
		else if (iTeam == 3)
		{
			// Counter-Terrorist (Security).
			SetEntityModel(iClient, "models/player/custom_player/legacy/security/security.mdl");
		}	
	}
}