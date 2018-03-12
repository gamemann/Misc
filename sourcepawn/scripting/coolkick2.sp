#include <sourcemod>

public Plugin myinfo =
{
	name = "Coolio Kickv2",
	author = "Roy (Christian Deacon)",
	description = "Coolv2.",
	version = "1.0.0",
	url = "GFLClan.com"
};

public void OnClientPostAdminCheck (int iClient)
{
	if (!HasPermission(iClient, "a") && !IsFakeClient(iClient))
	{
		KickClient(iClient, "Nu To Not Too...");
	}
}

stock bool HasPermission(int iClient, char[] sFlagString) 
{
	if (StrEqual(sFlagString, ""))
	{	
		return true;
	}
	
	AdminId eAdmin = GetUserAdmin(iClient);
	
	if (eAdmin != INVALID_ADMIN_ID)
	{
		int iFlags = ReadFlagString(sFlagString);

		if (CheckAccess(eAdmin, "", iFlags, true))
		{
			return true;
		}
	}

	return false;
}