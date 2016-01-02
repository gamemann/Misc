#include <sourcemod>
#include <sdktools>
#define VERSION "1.0"
#define TAG "[GFL DS]"

public Plugin:myinfo = {
	name = "[GFL]Dynamic Slots",
	description = "Dynamic slots for GFL.",
	author = "[GFL] Roy",
	version = VERSION,
	url = "GFLClan.com"
};

// Convars
new Handle:g_MinSlots = INVALID_HANDLE;
new Handle:g_MaxSlots = INVALID_HANDLE;
new Handle:g_IncreaseSlots = INVALID_HANDLE;
new Handle:g_BotsInclude = INVALID_HANDLE;
new Handle:g_Reset = INVALID_HANDLE;
new Handle:g_Debug = INVALID_HANDLE;

// Variables
new MinSlots;
new MaxSlots;
new Increase;
new bool:BotsInclude;
new bool:Reset;
new bool:Debug;

public OnPluginStart() {
	// Convars
	g_MinSlots = CreateConVar("GFL_ds_min", "24", "Maximum number of slots");
	g_MaxSlots = CreateConVar("GFL_ds_max", "0", "Maximum number of slots. Use 0 for the maximum server slots.");
	g_IncreaseSlots = CreateConVar("GFL_ds_increase", "2", "How many slots to increase by after the GFL_ds_min is reached.");
	g_BotsInclude = CreateConVar("GFL_ds_bots_include", "0", "Should we include bots in the player amounts?");
	g_Reset = CreateConVar("GFL_ds_reset", "1", "If enabled, when the map is started the plugin will execute \"sv_visiblemaxplayers GFL_ds_max\"");
	g_Debug = CreateConVar("GFL_ds_debug", "0", "Enables debugging (for developer mode only)");
	
	// Now execute the config!
	AutoExecConfig(true, "GFL_dynamic_slots", "sourcemod/GFL");
}


public OnConfigsExecuted() {
	// Set the variables
	MinSlots = GetConVarInt(g_MinSlots);
	MaxSlots = GetConVarInt(g_MaxSlots);
	Increase = GetConVarInt(g_IncreaseSlots);
	BotsInclude = GetConVarBool(g_BotsInclude);
	Reset = GetConVarBool(g_Reset);
	Debug = GetConVarBool(g_Debug);
	
	// Optional
	if (MaxSlots == 0) {
		MaxSlots = MaxClients;
	}
	
	if (Reset) {
		ServerCommand("sv_visiblemaxplayers %d", MinSlots);
	}
	
	if (Debug) {
		LogMessage("%s Configs executed!", TAG);
		LogMessage("MinSlots: %d; MaxSlots: %d; Increase: %d;", MinSlots, MaxSlots, Increase);
	}
	
}


public OnClientPutInServer(client) {
	new clientcount = f_GetClientCount();
	
	if (clientcount >= MinSlots && clientcount < MaxSlots && clientcount) {
		new newmaxplayers = MinSlots;
		
		for (new i=MinSlots; i <= MaxSlots; i++) {
			newmaxplayers += Increase;
			if (newmaxplayers <= clientcount) {
				if (Debug) {
					LogMessage("%s[CONNECT] Adding %d onto the slot count (%d)", TAG, newmaxplayers, MaxSlots);
				}
			} else {
				ServerCommand("sv_visiblemaxplayers %d", newmaxplayers);
				return;
			}
		}
	}
}

public OnClientDisconnect(client) {
	new clientcount = f_GetClientCount();
	if (Debug) {
		LogMessage("%s[DISCONNECT] Client count: %d", TAG, clientcount);
	}
	
	if (clientcount >= MinSlots && clientcount <= MaxSlots && clientcount) {
		new newmaxplayers = MinSlots;
		new Handle:visiblemax = FindConVar("sv_visiblemaxplayers");
		
		for (new i=MinSlots; i <= MaxSlots; i++) {
			newmaxplayers += Increase;
			if (newmaxplayers == clientcount) {
				
				new removeclients = GetConVarInt(visiblemax) - Increase;
				ServerCommand("sv_visiblemaxplayers %d", removeclients);
				
				if (Debug) {
					LogMessage("%s[DISCONNECT] Removing %d from the slot count (%d). Original Client Count: %d", TAG, Increase, GetConVarInt(visiblemax), clientcount);
				}
				return;
			} else {
				newmaxplayers += i;
			}
		}
	}
}

stock f_GetClientCount() {
	new cc;
	for (new i=1; i <= MaxClients; i++) {
		if (IsClientInGame(i) && i) {
			if (IsFakeClient(i)) {
				if (BotsInclude) {
					cc++;
				}
			} else {
				cc++;
			}
		}
	}
	
	return cc;
}

