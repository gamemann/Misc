#include <sourcemod>
#include <sdktools>

#define MAXENTITIES 2048

public Plugin:myinfo = 
{
	name = "Edicts Counter",
	description = "blkalba",
	author = "Roy",
	version = "1",
	url = ""
};

public OnPluginStart()
{
	RegConsoleCmd("sm_edicts", Command_Edicts);
}

public Action:Command_Edicts(client, args)
{
	new j = 0;
	for (new i = 0; i < MAXENTITIES; i++)
	{
		if (IsValidEdict(i))
		{
			j++;
		}
	}
	
	ReplyToCommand(client, "There are %i/%i entities.", j, MAXENTITIES);
	
	return Plugin_Handled;
}