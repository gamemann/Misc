#include <sourcemod>
#include <sdktools>

public Plugin:myinfo = {
	name = "Loads timer [CKSurf]",
	description = "Loads the timer on course maps",
	author = "Roy",
	version = "1.0",
	url = "TheDevelopingCommunity.com"
};

#define MAX_PLUGINS 1

// All the plugins to unload and load 

new Handle:g_loadtimer = INVALID_HANDLE;
new Handle:g_debug = INVALID_HANDLE;
new Handle:g_array;

public OnPluginStart() {
	// All the plugins!
	g_array = CreateArray(MAX_PLUGINS+1);
	PushArrayString(g_array, "disabled/cksurf");
	
	
	g_loadtimer = CreateConVar("GFL_load_timer", "0", "Load the timer?");
	g_debug = CreateConVar("GFL_load_timer_debug", "0", "Debug the Load timer plugin?");
}

public OnMapStart() {
	CreateTimer(0.1, LoadTimer);
}

public Action:LoadTimer(Handle:timer) {
	if (GetConVarBool(g_loadtimer)) {
		// It is enabled!
		for (new i=0; i < MAX_PLUGINS; i++) {
				
			new String:plugin[64];
			GetArrayString(g_array, i, plugin, sizeof(plugin));
			ServerCommand("sm plugins load %s.smx", plugin);
			
			if (GetConVarBool(g_debug))
				LogMessage("[TIMER]Loaded %s", plugin);
		}
	} else {
		new Handle:g_timerenabled = FindConVar("timer_version");
		
		if (g_timerenabled != INVALID_HANDLE) {
			if (GetConVarBool(g_debug))
				LogMessage("Timer de-activating");
		
			for (new i=0; i < MAX_PLUGINS; i++) {
					
				new String:plugin[64];
				GetArrayString(g_array, i, plugin, sizeof(plugin));
				ServerCommand("sm plugins unload %s.smx", plugin);
				
				if (GetConVarBool(g_debug))
					LogMessage("[TIMER]UnLoaded %s", plugin);
			}
		}
	}
}