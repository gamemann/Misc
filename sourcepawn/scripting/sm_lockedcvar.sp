#include <sourcemod>

public Plugin:myinfo =
{
  name = "locked cvar lister",
  author = "bl4nk & Roy",
  description = "lists locked cvars",
  version = "1.0.0",
  url = "http://forums.alliedmods.net/"
};

#if !defined FCVAR_DEVELOPMENTONLY
#define FCVAR_DEVELOPMENTONLY    (1<<1)
#endif

new String:sPath[PLATFORM_MAX_PATH];

public OnPluginStart()
{
    RegAdminCmd("sm_llcvars", Command_ListCvars, ADMFLAG_CHEATS);
    RegAdminCmd("sm_printcvars", Command_ListCvars, ADMFLAG_CHEATS);
	BuildPath(Path_SM, sPath, sizeof(sPath), "logs/convars.txt");
}

public Action:Command_ListCvars(client, args)
{
	new Handle:hFile = OpenFile(sPath, "a+");
	
	if (hFile != INVALID_HANDLE)
	{
		decl String:name[64];
		decl String:desc[1024];
		decl String:val[64];
		new Handle:cvar, bool:isCommand, flags;

		cvar = FindFirstConCommand(name, sizeof(name), isCommand, flags, desc, sizeof(desc));
		if (cvar == INVALID_HANDLE)
		{
			PrintToConsole(client, "Could not load cvar list");
			return Plugin_Handled;
		}

		do
		{
			if (isCommand)
			{
				WriteFileLine(hFile, "- %s", name);
				continue;
			}
			
			new Handle:thecvar = FindConVar(name);
			if (thecvar == INVALID_HANDLE) {
					continue;
			}
			GetConVarString(FindConVar(name), val, sizeof(val));

			if (strlen(desc) > 0)
			{
				WriteFileLine(hFile, "%s (Default \"%s\") - %s", name, val, desc);
			}
			else 
			{
				WriteFileLine(hFile, "%s (Default: \"%s\") - *None*", name, val);
			}

		} while (FindNextConCommand(cvar, name, sizeof(name), isCommand, flags, desc, sizeof(desc)));

		CloseHandle(cvar);
		CloseHandle(hFile);
		return Plugin_Handled;
	}
	
	return Plugin_Handled;
}  
