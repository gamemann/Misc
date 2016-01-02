#include <sourcemod>
#include <sdktools>

public Plugin:myinfo = 
{
	name = "Dynamic Slots",
	author = "Bottiger | Gamemann",
	description = "Expands slots to 24,26,28,30",
	version = "1.0",
	url = "http://tf.skial.com"
};

new Handle:hThreshold;
new Handle:hMiddle
new Handle:hMiddle1
new Handle:hMiddle2
new Handle:hLow
new Handle:hMaxPlayers

public OnPluginStart() {
    hThreshold = CreateConVar("dynamicslots_threshold", "24", "When you have this many players, expand slots to dynamicslots_high. Dropping below will set to dynamicslots_low");
	hMiddle = CreateConVar("dynamicslots_middle", "26", "Middle 1");
	hMiddle1 = CreateConVar("dynamicslots_middle1", "28", "Middle 2");
	hMiddle2 = CreateConVar("dynamicslots_middle2", "30", "Middle 3");   
	hLow = CreateConVar("dynamicslots_low", "24", "Lowest value for slots.");
	hMaxPlayers = FindConVar("sv_visiblemaxplayers");
    ExecuteLogic();
}

public OnClientConnected(client) {
    ExecuteLogic();
}

public OnClientDisconnect() {
    ExecuteLogic();
}

public ExecuteLogic() {
    new clients = GetRealClientCount(false);
    new threshold = GetConVarInt(hThreshold); 
    if(clients < threshold) 
	{
        PrintToServer("[GFL]Slots below 24. Setting to 24!");
		LogMessage("[GFL DynamicSlots]Slots below 24. Changing slot count to 24!");
        SetConVarInt(hMaxPlayers, GetConVarInt(hLow));
    }
	else if(clients == 24)
	{
        PrintToServer("[GFL]Slots above 24, changing to 26!");
		LogMessage("[GFL DynamicSlots]Reached 24. Changing slot count to 26!");
        SetConVarInt(hMaxPlayers, GetConVarInt(hMiddle));
	}
	else if(clients == 26)
	{
        PrintToServer("[GFL]Slots above 26, changing to 28!");
		LogMessage("[GFL DynamicSlots]Reached 26. Changing slot count to 28!");
        SetConVarInt(hMaxPlayers, GetConVarInt(hMiddle1));
	}
	else if(clients == 28)
	{
        PrintToServer("[GFL]Slots above 28, changing to 30!");
		LogMessage("[GFL DynamicSlots]Reached 28. Changing slot count to 30!");
        SetConVarInt(hMaxPlayers, GetConVarInt(hMiddle2));
	}
}

GetRealClientCount(bool:inGameOnly=true) {
    new clients = 0;
    for(new i=1;i<=MaxClients; i++ ) {
        if((inGameOnly ? IsClientInGame( i ): IsClientConnected(i)) && !IsFakeClient(i))
            clients++;
    }
    return clients;
}