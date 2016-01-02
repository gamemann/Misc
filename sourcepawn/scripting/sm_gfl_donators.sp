#include <sourcemod>
#include <sdktools>

#pragma semicolon 1

#define PLUGIN_VERSION	"0.1"

new gVelocityOffset;


new g_EntList[MAXPLAYERS + 1];
new bool:g_bRoundEnded;

public Plugin:myinfo = 
{
	name = "GFL Donators",
	author = "Gamemann",
	description = "Round End Sprites.",
	version = PLUGIN_VERSION,
	url = "http://GFLClan.com/"
}

public OnPluginStart()
{
	
	HookEventEx("teamplay_round_start", hook_Start, EventHookMode_PostNoCopy);
	HookEventEx("arena_round_start", hook_Start, EventHookMode_PostNoCopy);
	HookEventEx("teamplay_round_win", hook_Win, EventHookMode_PostNoCopy);
	HookEventEx("arena_win_panel", hook_Win, EventHookMode_PostNoCopy);
	HookEventEx("player_death", event_player_death, EventHookMode_Post);

	gVelocityOffset = FindSendPropInfo("CBasePlayer", "m_vecVelocity[0]");
}


public OnMapStart()
{
	PrecacheGeneric("materials/gfl/gfl-supporter.vmt", true);
	AddFileToDownloadsTable("materials/gfl/gfl-supporter.vmt");
	PrecacheGeneric("materials/gfl/gfl-supporter.vtf", true);
	AddFileToDownloadsTable("materials/gfl/gfl-supporter.vtf");
}

public hook_Start(Handle:event, const String:name[], bool:dontBroadcast)
{
	for(new i = 1; i <= MaxClients; i++)
	{
	
		KillSprite(i);
	}
	g_bRoundEnded = false;
}

public hook_Win(Handle:event, const String:name[], bool:dontBroadcast)
{	
	for(new i = 1; i <= MaxClients; i++)
	{
		if (!IsClientInGame(i) || !IsPlayerAlive(i) || IsClientObserver(i)) continue;
		{
			if(CheckCommandAccess(i, "", ADMFLAG_CUSTOM5))
			{
				CreateSprite(i, "materials/gfl/gfl-supporter.vmt", 25.0);
			}
		}
	}
	g_bRoundEnded = true;
}

public Action:event_player_death(Handle:event, const String:name[], bool:dontBroadcast)
{
	if(!g_bRoundEnded) return Plugin_Continue;
	KillSprite(GetClientOfUserId(GetEventInt(event, "userid")));
	return Plugin_Continue;
}

stock KillSprite(iClient)
{
	if (g_EntList[iClient] > 0 && IsValidEntity(g_EntList[iClient]))
	{
		AcceptEntityInput(g_EntList[iClient], "kill");
		g_EntList[iClient] = 0;
	}
}

stock CreateSprite(iClient, String:sprite[], Float:offset)
{
	new String:szTemp[64]; 
	Format(szTemp, sizeof(szTemp), "client%i", iClient);
	DispatchKeyValue(iClient, "targetname", szTemp);

	new Float:vOrigin[3];
	GetClientAbsOrigin(iClient, vOrigin);
	vOrigin[2] += offset;
	new ent = CreateEntityByName("env_sprite_oriented");
	if (ent)
	{
		DispatchKeyValue(ent, "model", sprite);
		DispatchKeyValue(ent, "classname", "env_sprite_oriented");
		DispatchKeyValue(ent, "spawnflags", "1");
		//DispatchKeyValue(ent, "scale", "0.1");
		DispatchKeyValue(ent, "rendermode", "5");
		DispatchKeyValue(ent, "rendercolor", "255 255 255");
		DispatchKeyValue(ent, "targetname", "donator_spr");
		DispatchKeyValue(ent, "parentname", szTemp);
		AcceptEntityInput(ent, "SetParent", iClient, ent);
		DispatchSpawn(ent);
		
		TeleportEntity(ent, vOrigin, NULL_VECTOR, NULL_VECTOR);

		g_EntList[iClient] = ent;
	}
}


/*
public OnGameFrame()
{
	if (!g_bRoundEnded) return;
	new ent, Float:vOrigin[3], Float:vVelocity[3];
	
	for(new i = 1; i <= MaxClients; i++)
	{
		if (!IsClientInGame(i)) continue;
		if ((ent = g_EntList[i]) > 0)
		{
			if (!IsValidEntity(ent))
				g_EntList[i] = 0;
			else
				if ((ent = EntRefToEntIndex(ent)) > 0)
				{
					GetClientEyePosition(i, vOrigin);
					vOrigin[2] += 25.0;
					GetEntDataVector(i, gVelocityOffset, vVelocity);
					TeleportEntity(ent, vOrigin, NULL_VECTOR, vVelocity);
				}
		}
	}
}
*/