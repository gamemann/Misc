#include <sourcemod>
#include <sdktools>

public Plugin:myinfo = {
	name = "TF2Ware Time Limit",
	author = "Gamemann",
	description = "Time Limits for TF2Ware.",
	version = "1",
	url = "GFLClan.com"
};

new Handle:g_limit = INVALID_HANDLE;
new Handle:g_enabled = INVALID_HANDLE;
new Handle:g_debug = INVALID_HANDLE;

new maptime;

public OnPluginStart() {
	g_enabled = CreateConVar("TF2Ware_limit_enabled", "0", "Enable this plugin on TF2Ware gamemode only!");
	g_debug = CreateConVar("TF2Ware_limit_debug", "0", "Enable debugging for this plugin?");
	g_limit = FindConVar("mp_timelimit");
}

public OnMapStart() {
	if (g_enabled) {
		maptime = GetConVarInt(g_limit);
		CreateTimer(maptime * 60, ShutDown);
		if (g_debug) {
			LogMessage("[LIMIT]Time created.");
			LogMessage("[LIMIT]Value of timer limit is %d", GetConVarInt(g_limit));
		}
	}
}

public Action:ShutDown(Handle:timer) {
	//Code to end the bloody round.
	if (g_debug) {
		LogMessage("[LIMIT]Timer up");
	}
	//Best way to end the game.
	new iGameEnd  = FindEntityByClassname(-1, "game_end");
	if (iGameEnd == -1 && (iGameEnd = CreateEntityByName("game_end")) == -1)  {     
		LogError("Unable to create entity \"game_end\"!");
	} else {     
		AcceptEntityInput(iGameEnd, "EndGame");
	}
	new String:newmap[65];
	GetNextMap(newmap, sizeof(newmap));
	ServerCommand("sm_map %s", newmap);
}