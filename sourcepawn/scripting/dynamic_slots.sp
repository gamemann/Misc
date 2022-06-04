#include <sourcemod>
#include <sdktools>
#define VERSION "1.0"
#define TAG "[DS]"

public Plugin:myinfo = 
{
	name = "Dynamic Slots",
	description = "Dynamic slots.",
	author = "[LBG] Christian",
	version = VERSION,
	url = "deaconn.net and lbgaming.co"
};

// Convars
ConVar g_MinSlots = null;
ConVar g_MaxSlots = null;
ConVar g_IncreaseSlots = null;
ConVar g_BotsInclude = null;
ConVar g_Reset = null;
ConVar g_Debug = null;

// Variables
int MinSlots;
int MaxSlots;
int Increase;
bool BotsInclude;
bool Reset;
bool Debug;

public void OnPluginStart() 
{
	// Convars
	g_MinSlots = CreateConVar("ds_min", "24", "Minimum number of slots");
	g_MaxSlots = CreateConVar("ds_max", "0", "Maximum number of slots. Use 0 for the maximum server slots.");
	g_IncreaseSlots = CreateConVar("ds_increase", "2", "How many slots to increase by after the ds_min is reached.");
	g_BotsInclude = CreateConVar("ds_bots_include", "0", "Should we include bots in the player amounts?");
	g_Reset = CreateConVar("ds_reset", "1", "If enabled, when the map is started the plugin will execute \"sv_visiblemaxplayers ds_max\"");
	g_Debug = CreateConVar("ds_debug", "0", "Enables debugging (for developer mode only)");
	
	// Now execute the config!
	AutoExecConfig(true, "plugin.dynamicslots");
}

public void OnConfigsExecuted() 
{
	// Set the variables
	MinSlots = GetConVarInt(g_MinSlots);
	MaxSlots = GetConVarInt(g_MaxSlots);
	Increase = GetConVarInt(g_IncreaseSlots);
	BotsInclude = GetConVarBool(g_BotsInclude);
	Reset = GetConVarBool(g_Reset);
	Debug = GetConVarBool(g_Debug);
	
	// Optional
	if (MaxSlots == 0)
	{
		MaxSlots = MaxClients;
	}
	
	if (Reset) 
	{
		ServerCommand("sv_visiblemaxplayers %d", MinSlots);
	}
	
	if (Debug) 
	{
		LogMessage("%s Configs executed!", TAG);
		LogMessage("MinSlots: %d; MaxSlots: %d; Increase: %d;", MinSlots, MaxSlots, Increase);
	}
	
}


public void OnClientPutInServer(client) 
{
	int clientcount = f_GetClientCount();
	
	if (clientcount >= MinSlots && clientcount < MaxSlots && clientcount) 
	{
		int newmaxplayers = MinSlots;
		
		for (int i = MinSlots; i <= MaxSlots; i++) 
		{
			newmaxplayers += Increase;

			if (newmaxplayers <= clientcount) 
			{
				if (Debug) 
				{
					LogMessage("%s[CONNECT] Adding %d onto the slot count (%d)", TAG, newmaxplayers, MaxSlots);
				}
			} 
			else 
			{
				ServerCommand("sv_visiblemaxplayers %d", newmaxplayers);

				return;
			}
		}
	}
}

public void OnClientDisconnect(client) 
{
	int clientcount = f_GetClientCount();

	if (Debug) 
	{
		LogMessage("%s[DISCONNECT] Client count: %d", TAG, clientcount);
	}
	
	if (clientcount >= MinSlots && clientcount <= MaxSlots && clientcount) 
	{
		int newmaxplayers = MinSlots;
		ConVar visiblemax = FindConVar("sv_visiblemaxplayers");
		
		for (int i = MinSlots; i <= MaxSlots; i++) 
		{
			newmaxplayers += Increase;

			if (newmaxplayers == clientcount) 
			{
				
				new removeclients = GetConVarInt(visiblemax) - Increase;
				ServerCommand("sv_visiblemaxplayers %d", removeclients);
				
				if (Debug) 
				{
					LogMessage("%s[DISCONNECT] Removing %d from the slot count (%d). Original Client Count: %d", TAG, Increase, GetConVarInt(visiblemax), clientcount);
				}

				return;
			} 
			else 
			{
				newmaxplayers += i;
			}
		}
	}
}

stock int f_GetClientCount() {
	int cc;
	
	for (int i = 1; i <= MaxClients; i++) 
	{
		if (IsClientInGame(i)) 
		{
			if (IsFakeClient(i)) 
			{
				if (BotsInclude) 
				{
					cc++;
				}
			} 
			else 
			{
				cc++;
			}
		}
	}
	
	return cc;
}