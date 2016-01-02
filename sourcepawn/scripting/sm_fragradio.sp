/***************************************************************************
	
	FragRadio SourceMod Plugin
	Copyright (c) 2011 JokerIce <http://forums.jokerice.co.uk/>

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program. If not, see <http://www.gnu.org/licenses/>.
	
****************************************************************************/

#define PLUGINVERSION "1.4.7"

#pragma semicolon 1
#include <sourcemod>
#include <socket>
#include <base64>
#include <colors>
#include <clientprefs>

new Handle:g_hCvarPluginStatus = INVALID_HANDLE;
new Handle:g_hCvarInteract = INVALID_HANDLE;
new Handle:g_hCvarRating = INVALID_HANDLE;
new Handle:g_hCvarWelcomeAdvert = INVALID_HANDLE;
new Handle:g_hCvarAdverts = INVALID_HANDLE;
new Handle:g_hCvarAdvertsInterval = INVALID_HANDLE;
new Handle:g_hCvarGetStreamInfo = INVALID_HANDLE;
new Handle:g_hCvarStreamInterval = INVALID_HANDLE;
new Handle:g_hCvarStreamHost = INVALID_HANDLE;
new Handle:g_hCvarStreamPort = INVALID_HANDLE;
new Handle:g_hCvarStreamGamePath = INVALID_HANDLE;
new Handle:g_hCvarStreamUpdatePath = INVALID_HANDLE;
new Handle:g_hCvarStreamStatsPath = INVALID_HANDLE;
new Handle:g_hCvarWebPlayerScript = INVALID_HANDLE;
new Handle:g_hCvarServerUpdateInterval = INVALID_HANDLE;
new Handle:g_hCvarDefaultVolume = INVALID_HANDLE;
new Handle:g_hCvarTriggerShow = INVALID_HANDLE;

new Handle:g_hAdvertsTimer = INVALID_HANDLE;
new Handle:g_hStreamTimer = INVALID_HANDLE;
new Handle:g_hServerTimer = INVALID_HANDLE;

new String:g_sDataReceived[5120];
new String:g_sDJ[512];
new String:g_sSong[512];

// Cookies
new Handle:g_ClientVolume = INVALID_HANDLE;

new bool:PluginEnabled;
new bool:InteractEnabled;
new bool:RatingEnabled;
new bool:WelcomeAdvertsEnabled;
new bool:AdvertsEnabled;
new Float:AdvertsInterval;
new bool:GetStreamInfo;
new Float:StreamInterval;
new String:StreamHost[512];
new StreamPort;
new String:StreamGamePath[512];
new String:StreamUpdatePath[512];
new String:StreamStatsPath[512];
new String:WebPlayerScript[512];
new Float:ServerUpdateInterval;
new String:DefaultVolume[512];
new bool:isTuned[100];
new bool:first_time[100];
new tunedVol[100];
new AdsIndex = 0;
new bool:bTriggerShow;

new String:Adverts[10][2048] = {
	"In-Game Radio by FragRadio",
	"Type !poon to show us you dont like the current song!",
	"Type !dj to see what dj is currently onair!",
	"Type !joke to say something funny to the dj currently on air!",
	"Want this plugin for your server? Visit fragradio.com/plugins to download it!",
	"Type !request <song> to request a song!",
	"Type !choon to show us you like the current song!",
	"Type !shoutout <message> to ask the dj to say something on air!",
	"Type !song to see what song is currently playing!",
	"Not hearing any music from the radio? Make sure you have flash installed and your MOTD enabled"
};

public Plugin:myinfo = {
	name = "FragRadio SourceMod Plugin",
	author = "BomBom - Dunceantix",
	description = "FragRadio SourceMod Plugin",
	version = PLUGINVERSION,
	url = "http://www.fragradio.com/"
}

public OnPluginStart() {
	g_hCvarPluginStatus = CreateConVar("fr_enabled", "1", "Enables or disables the plugin.", FCVAR_PLUGIN, true, 0.0, true, 1.0);
	HookConVarChange(g_hCvarPluginStatus, Cvar_Change_Enabled);
	
	g_hCvarInteract = CreateConVar("fr_interact", "1", "Enables or disables the request and shoutout system.", FCVAR_PLUGIN, true, 0.0, true, 1.0);
	HookConVarChange(g_hCvarInteract, Cvar_Changed);
	
	g_hCvarRating = CreateConVar("fr_rating", "1", "Enables or disables the song and dj rating system.", FCVAR_PLUGIN, true, 0.0, true, 1.0);
	HookConVarChange(g_hCvarRating, Cvar_Changed);
	
	g_hCvarWelcomeAdvert = CreateConVar("fr_welcomeadvert", "1", "Enables or disables the welcome advert you see upon joining the server.", FCVAR_PLUGIN, true, 0.0, true, 1.0);
	HookConVarChange(g_hCvarWelcomeAdvert, Cvar_Changed);
	
	g_hCvarAdverts = CreateConVar("fr_adverts", "1", "Enables or disables chat adverts that display after a time limit.", FCVAR_PLUGIN, true, 0.0, true, 1.0);
	HookConVarChange(g_hCvarAdverts, Cvar_Change_Adverts);
	
	g_hCvarAdvertsInterval = CreateConVar("fr_adverts_interval", "150.0", "Sets the delay between adverts shown in chat.", FCVAR_PLUGIN);
	HookConVarChange(g_hCvarAdvertsInterval, Cvar_Changed);
	
	g_hCvarGetStreamInfo = CreateConVar("fr_streaminfo", "1", "Enables or disables stream information from being gathered.", FCVAR_PLUGIN, true, 0.0, true, 1.0);
	HookConVarChange(g_hCvarGetStreamInfo, Cvar_Change_StreamInfo);
	
	g_hCvarStreamInterval = CreateConVar("fr_streaminfo_interval", "15.0", "DO NOT CHANGE OR YOUR SERVER IP WILL BE BANNED! Sets the time between stream info updates.", FCVAR_PLUGIN);
	HookConVarChange(g_hCvarStreamInterval, Cvar_Changed);

	g_hCvarStreamHost = CreateConVar("fr_stream_host", "fragradio.com", "Sets the website hostname used to send and retrieve info.", FCVAR_PLUGIN);
	HookConVarChange(g_hCvarStreamHost, Cvar_Changed);
	
	g_hCvarStreamPort = CreateConVar("fr_stream_port", "80", "Sets the website port used to send and retrieve info.", FCVAR_PLUGIN);
	HookConVarChange(g_hCvarStreamPort, Cvar_Changed);
	
	g_hCvarStreamGamePath = CreateConVar("fr_stream_game_path", "/resources/plugins/sourcemod/smreq.php", "Website path used for rating and interaction.", FCVAR_PLUGIN);
	HookConVarChange(g_hCvarStreamGamePath, Cvar_Changed);
	
	g_hCvarStreamUpdatePath = CreateConVar("fr_stream_update_path", "/resources/plugins/sourcemod/smsinfo.php", "Website path used for sending server info.", FCVAR_PLUGIN);
	HookConVarChange(g_hCvarStreamUpdatePath, Cvar_Changed);
	
	g_hCvarStreamStatsPath = CreateConVar("fr_stream_stats_path", "/resources/plugins/sourcemod/smsong.php", "Website path used for getting stream info.", FCVAR_PLUGIN);
	HookConVarChange(g_hCvarStreamStatsPath, Cvar_Changed);
	
	g_hCvarWebPlayerScript = CreateConVar("fr_webplayer_script", "/resources/plugins/sourcemod/player.php", "Website script used for the web player.", FCVAR_PLUGIN);
	HookConVarChange(g_hCvarWebPlayerScript, Cvar_Changed);
	
	g_hCvarServerUpdateInterval = CreateConVar("fr_server_update_interval", "600.0", "DO NOT CHANGE OR YOUR SERVER IP WILL BE BANNED! Set at 10min for enabledservers info.", FCVAR_PLUGIN);
	HookConVarChange(g_hCvarServerUpdateInterval, Cvar_Changed);
	
	g_hCvarDefaultVolume = CreateConVar("fr_default_volume", "20.0", "The default volume for users once they join the server!");
	HookConVarChange(g_hCvarDefaultVolume, Cvar_Changed);	
	
	g_hCvarTriggerShow = CreateConVar("fr_trigger_show", "0", "Use PrintToChatAll for all FragRadio commands?");
	HookConVarChange(g_hCvarTriggerShow, Cvar_Changed);
	
	
	RegConsoleCmd("sm_dj", Cmd_ShowDJ);
	RegConsoleCmd("sm_song", Cmd_ShowSong);
	RegConsoleCmd("sm_radio", Cmd_RadioMenu);
	RegConsoleCmd("sm_req", Cmd_Request);
	RegConsoleCmd("sm_request", Cmd_Request);
	RegConsoleCmd("sm_r", Cmd_Request);
	RegConsoleCmd("sm_shoutout", Cmd_Shoutout);
	RegConsoleCmd("sm_s", Cmd_Shoutout);
	RegConsoleCmd("sm_choon", Cmd_Choon);
	RegConsoleCmd("sm_ch", Cmd_Choon);
	RegConsoleCmd("sm_poon", Cmd_Poon);
	RegConsoleCmd("sm_p", Cmd_Poon);
	RegConsoleCmd("sm_djftw", Cmd_djFTW);
	RegConsoleCmd("sm_djftl", Cmd_djFTL);
	RegConsoleCmd("sm_djsftw", Cmd_djsFTW);
	RegConsoleCmd("sm_djsftl", Cmd_djsFTL);
	RegConsoleCmd("sm_competition", Cmd_Competition);
	RegConsoleCmd("sm_comp", Cmd_Competition);
	RegConsoleCmd("sm_c", Cmd_Competition);
	RegConsoleCmd("sm_joke", Cmd_Joke);
	RegConsoleCmd("sm_j", Cmd_Joke);
	RegConsoleCmd("sm_other", Cmd_Other);
	RegConsoleCmd("sm_o", Cmd_Other);
	
	// Cookies
	g_ClientVolume = RegClientCookie("fr_volume", "Frag Radio Volume.", CookieAccess_Private);
	for (new i = MaxClients; i > 0; --i)
	{
		if (!AreClientCookiesCached(i))
		{
			continue;
		}
		OnClientCookiesCached(i);
	}
	
	AutoExecConfig(true, "sm_fragradio");

}

// Caching
public OnClientCookiesCached(client)
{
	if (IsFakeClient(client))
		return;
	
	decl String:sValue[8];
	GetClientCookie(client, g_ClientVolume, sValue, sizeof(sValue));
	new myvalue = StringToInt(sValue);
	
	if (myvalue == 0) {
		SetClientCookie(client, g_ClientVolume, DefaultVolume);
		tunedVol[client] = StringToInt(DefaultVolume);
		if (tunedVol[client] > 0)
		{
			isTuned[client] = true;
		}
		else
		{
			isTuned[client] = false;
		}
		first_time[client] = true;
	} else if(myvalue > 0) {
		first_time[client] = false;
		isTuned[client] = true;
		tunedVol[client] = myvalue;
	} else {
		tunedVol[client] = 0;
		isTuned[client] = false;
		first_time[client] = false;
	}
}

public OnClientPutInServer(client) {
	if (WelcomeAdvertsEnabled) {
		WelcomeAdvert(client);
	}
}

public KeepListening(Handle:menu, MenuAction:action, client, choice) {
	if (action == MenuAction_Select) {
		new String:info[32];
		new bool:found = GetMenuItem(menu, choice, info, sizeof(info));
		
		if (found) {
			if (StrEqual(info, "0", false)) {
				PrintToChat(client, "Enjoy the server and the radio! Type !radio if you want to turn off the radio! Apply for Membership at GFLClan.com!");
			} else {
				tunedVol[client] = 0;
				isTuned[client] = false;
				decl String:newvol[8];
				IntToString(tunedVol[client], newvol, sizeof(newvol));
				SetClientCookie(client, g_ClientVolume, "-1");
				
				// Now redo the stream with volume at -1!
				decl String:url[256];
				FormatEx(url, sizeof(url), "http://%s/%s?vol=%s", StreamHost, WebPlayerScript, newvol);
				StreamPanel("You are tuned into FragRadio!", url, client);
			}
		}
	} else if (action == MenuAction_End) {
		CloseHandle(menu);
	}
}

public OnClientPostAdminCheck(client) {
	if(!IsFakeClient(client)) {
		CreateTimer(5.0, Repeatme, client, TIMER_FLAG_NO_MAPCHANGE);
	}
}

public Action:Repeatme(Handle:timer, any:client) {
	if (!IsClientInGame(client) || !IsClientConnected(client)) {
		return;
	}
	new client2 = GetClientFromSerial(GetClientSerial(client));
	
	if (IsClientInGame(client) && IsClientConnected(client) && client && IsClientAuthorized(client) && client2 != 0 && GetClientTeam(client) > 0) {
		if (isTuned[client]) {
			decl String:url[256];
			decl String:info[8];
			IntToString(tunedVol[client], info, sizeof(info));
			FormatEx(url, sizeof(url), "http://%s/%s?vol=%s", StreamHost, WebPlayerScript, info);
			StreamPanel("You are tuned into FragRadio!", url, client);
			
			// Now for the menu part.
			if (first_time[client] && tunedVol[client] > 0) {
				new Handle:menu = CreateMenu(KeepListening);
				SetMenuTitle(menu, "Keep Listening to the radio?");
				
				AddMenuItem(menu, "0", "Yes.");
				AddMenuItem(menu, "1", "No.");
				
				DisplayMenu(menu, client, 0);
			}
		}
	} else {
		if (IsClientInGame(client) && IsClientConnected(client)) {
			CreateTimer(5.0, Repeatme, client, TIMER_FLAG_NO_MAPCHANGE);
		}
	}
}

public OnClientDisconnect(client) {
	isTuned[client] = false;
}

stock ClearTimer(&Handle:timer) {
	if (timer != INVALID_HANDLE) {
		KillTimer(timer);
	}
	
	timer = INVALID_HANDLE;
}

public Action:WelcomeAdvert(any:client) {
	PrintToChat(client, "\x04[FragRadio]\x01 To listen to the radio type !radio in chat");
}

public Action:Advertise(Handle:timer) {
	if (AdsIndex == 9) {
		AdsIndex = 0;
		PrintToChatAll("\x04[FragRadio]\x01 %s", Adverts[AdsIndex]);
		AdsIndex++;
	} else {
		PrintToChatAll("\x04[FragRadio]\x01 %s", Adverts[AdsIndex]);
		AdsIndex++;
	}
}

public OnMapEnd() {
	ClearTimer(g_hAdvertsTimer);
	ClearTimer(g_hStreamTimer);
	ClearTimer(g_hServerTimer);
}

public Cvar_Changed(Handle:convar, const String:oldValue[], const String:newValue[]) {
	OnConfigsExecuted();
}

public Cvar_Change_Enabled(Handle:convar, const String:oldValue[], const String:newValue[]) {
	PluginEnabled = GetConVarBool(g_hCvarPluginStatus);

	if (PluginEnabled) {
		if (AdvertsEnabled) {
			g_hAdvertsTimer = CreateTimer(AdvertsInterval, Advertise, 0, TIMER_REPEAT);
		}
		
		if (GetStreamInfo) {
			Server_Receive();
			g_hStreamTimer = CreateTimer(StreamInterval, UpdateStreamInfo, 0, TIMER_REPEAT);
			
			Server_Send();
			g_hServerTimer = CreateTimer(ServerUpdateInterval, UpdateServerList, 0, TIMER_REPEAT);
		}
		
		for (new i=1; i<= MaxClients; i++) {
			if (IsClientConnected(i) && IsClientInGame(i)) {
				if (isTuned[i]) {
					StreamPanel("Thanks for tuning into FragRadio!", "about:blank", i);
					
					decl String:url[256];
					FormatEx(url, sizeof(url), "http://%s/%s?vol=%s", StreamHost, WebPlayerScript, tunedVol[i]);
					StreamPanel("You are tuned into FragRadio!", url, i);
					
					PrintToChat(i, "\x04[FragRadio]\x01 The FragRadio plugin has been enabled, you have been auto tuned in.");
				}
			}
		}
	} else {
		ClearTimer(g_hAdvertsTimer);
		ClearTimer(g_hStreamTimer);
		ClearTimer(g_hServerTimer);
		
		for (new i=1; i<= MaxClients; i++) {
			if (IsClientConnected(i) && IsClientInGame(i)) {
				if (isTuned[i]) {
					StreamPanel("Thanks for tuning into FragRadio!", "about:blank", i);
					PrintToChat(i, "\x04[FragRadio]\x01 The FragRadio plugin has been disabled, you have been auto tuned out.");
				}
			}
		}
	}
}

public Cvar_Change_Adverts(Handle:convar, const String:oldValue[], const String:newValue[]) {
	AdvertsEnabled = GetConVarBool(g_hCvarAdverts);
	
	if (AdvertsEnabled) {
		g_hAdvertsTimer = CreateTimer(AdvertsInterval, Advertise, 0, TIMER_REPEAT);
	} else {
		ClearTimer(g_hAdvertsTimer);
	}
}

public Cvar_Change_StreamInfo(Handle:convar, const String:oldValue[], const String:newValue[]) {
	GetStreamInfo = GetConVarBool(g_hCvarGetStreamInfo);
	
	if (GetStreamInfo) {
		Server_Receive();
		g_hStreamTimer = CreateTimer(StreamInterval, UpdateStreamInfo, 0, TIMER_REPEAT);
		
		Server_Send();
		g_hServerTimer = CreateTimer(ServerUpdateInterval, UpdateServerList, 0, TIMER_REPEAT);
	} else {
		ClearTimer(g_hStreamTimer);
		ClearTimer(g_hServerTimer);
	}
}

public OnConfigsExecuted() {
	PluginEnabled = GetConVarBool(g_hCvarPluginStatus);
	InteractEnabled = GetConVarBool(g_hCvarInteract);
	RatingEnabled = GetConVarBool(g_hCvarRating);
	WelcomeAdvertsEnabled = GetConVarBool(g_hCvarWelcomeAdvert);
	AdvertsEnabled = GetConVarBool(g_hCvarAdverts);
	AdvertsInterval = GetConVarFloat(g_hCvarAdvertsInterval);
	GetStreamInfo = GetConVarBool(g_hCvarGetStreamInfo);
	StreamInterval = GetConVarFloat(g_hCvarStreamInterval);
	GetConVarString(g_hCvarStreamHost, StreamHost, sizeof(StreamHost));
	StreamPort = GetConVarInt(g_hCvarStreamPort);
	GetConVarString(g_hCvarStreamGamePath, StreamGamePath, sizeof(StreamGamePath));
	GetConVarString(g_hCvarStreamUpdatePath, StreamUpdatePath, sizeof(StreamUpdatePath));
	GetConVarString(g_hCvarStreamStatsPath, StreamStatsPath, sizeof(StreamStatsPath));
	GetConVarString(g_hCvarWebPlayerScript, WebPlayerScript, sizeof(WebPlayerScript));
	GetConVarString(g_hCvarDefaultVolume, DefaultVolume, sizeof(DefaultVolume));
	ServerUpdateInterval = GetConVarFloat(g_hCvarServerUpdateInterval);
	bTriggerShow = GetConVarBool(g_hCvarTriggerShow);
	
	
	if (PluginEnabled) {
		if (AdvertsEnabled) {
			g_hAdvertsTimer = CreateTimer(AdvertsInterval, Advertise, 0, TIMER_REPEAT);
		}
		
		if (GetStreamInfo) {
			Server_Receive();
			g_hStreamTimer = CreateTimer(StreamInterval, UpdateStreamInfo, 0, TIMER_REPEAT);
			
			Server_Send();
			g_hServerTimer = CreateTimer(ServerUpdateInterval, UpdateServerList, 0, TIMER_REPEAT);
		}
	}
}

public Action:UpdateStreamInfo(Handle:timer) {
	Server_Receive();
}

public Action:UpdateServerList(Handle:timer) {
	Server_Send();
}

public Cmd_Check(String:type[], client) {
	if (client > 0 && client <= MaxClients && IsClientInGame(client) && !IsFakeClient(client)) {
		if (!PluginEnabled) {
			ReplyToCommand(client, "\x04[FragRadio]\x01 The FragRadio Plugin has been disabled.");
			return false;
		}
		
		if (StrEqual(type, "streaminfo") && !GetStreamInfo) {
			ReplyToCommand(client, "\x04[FragRadio]\x01 Stream info gathering has been disabled.");
			return false;
		} else if (StrEqual(type, "interact") && !InteractEnabled) {
			ReplyToCommand(client, "\x04[FragRadio]\x01 Interaction commands have been disabled.");
			return false;
		} else if (StrEqual(type, "rating") && !RatingEnabled) {
			ReplyToCommand(client, "\x04[FragRadio]\x01 Rating commands have been disabled.");
			return false;
		}
		
		if (!StrEqual(type, "streaminfo") && isTuned[client] != true) {
			ReplyToCommand(client, "\x04[FragRadio]\x01 You are not tuned in, type !radio to tune in");
			return false;
		}
	} else {
		ReplyToCommand(client, "[FragRadio] That command can only be used by clients!");
		return false;
	}
	
	return true;
}

public Action:Cmd_ShowDJ(client, args) {
	if (Cmd_Check("streaminfo", client)) {		
		if (GetStreamInfo && !StrEqual(g_sDJ, "")) {
			ReplyToCommand(client, "\x04[FragRadio] Current DJ:\x01 %s", g_sDJ);
		} else {
			ReplyToCommand(client, "\x04[FragRadio]\x01 No stream info has been received yet!");
		}
	}

	return Plugin_Handled;
}

public Action:Cmd_ShowSong(client, args) {
	if (Cmd_Check("streaminfo", client)) {		
		if (GetStreamInfo && !StrEqual(g_sSong, "")) {
			ReplyToCommand(client, "\x04[FragRadio] Current Song:\x01 %s", g_sSong);
		} else {
			ReplyToCommand(client, "\x04[FragRadio]\x01 No stream info has been received yet!");
		}
	}

	return Plugin_Handled;
}

public StreamPanel(String:title[], String:url[], client) {
	new Handle:Radio = CreateKeyValues("data");
	KvSetString(Radio, "title", title);
	KvSetString(Radio, "type", "2");
	KvSetString(Radio, "msg", url);
	ShowVGUIPanel(client, "info", Radio, false);
	CloseHandle(Radio);
}

public RadioMenuHandle(Handle:menu, MenuAction:action, client, choice) {
	if (action == MenuAction_Select) {
		new String:info[32];
		new bool:found = GetMenuItem(menu, choice, info, sizeof(info));
		
		if (found) {
			if (StringToInt(info) == -1) {
				isTuned[client] = false;
				tunedVol[client] = 0;
				
				StreamPanel("Thanks for tuning into FragRadio!", "about:blank", client);
				PrintToChat(client, "\x04[FragRadio]\x01 Thanks for tuning into FragRadio!");
			} else {
				if (isTuned[client] != true) {
					decl String:name[128];
					GetClientName(client, name, sizeof(name));
					PrintToChatAll("\x04[FragRadio]\x01 %s has tuned into the radio by typing !radio", name);
				}
				
				StreamPanel("Thanks for tuning into FragRadio!", "about:blank", client);
				
				isTuned[client] = true;
				tunedVol[client] = StringToInt(info);
				
			
				decl String:url[256];			
				FormatEx(url, sizeof(url), "http://%s/%s?vol=%s", StreamHost, WebPlayerScript, info);
				StreamPanel("You are tuned into FragRadio!", url, client);
			}
			
			// Simple, set the cookie
			SetClientCookie(client, g_ClientVolume, info);
		}
	} else if (action == MenuAction_End) {
		CloseHandle(menu);
	}
}

public Action:Cmd_RadioMenu(client, args) {
	if (!PluginEnabled) {
		ReplyToCommand(client, "\x04[FragRadio]\x01 The FragRadio Plugin has been disabled.");
		return Plugin_Handled;
	}
	
	if (args > 1) {
	
	
	return Plugin_Handled;
	}
	
	new Handle:menu = CreateMenu(RadioMenuHandle);
	
	if (GetStreamInfo && !StrEqual(g_sDJ, "") && !StrEqual(g_sSong, "")) {
		SetMenuTitle(menu, "FragRadio Menu\n \nDJ: %s\nSong: %s\n \n", g_sDJ, g_sSong);
	} else {
		SetMenuTitle(menu, "FragRadio Menu\n \n");
	}
	
	AddMenuItem(menu, "100", "100% Volume");
	AddMenuItem(menu, "80", "80% Volume");
	AddMenuItem(menu, "40", "40% Volume");
	AddMenuItem(menu, "20", "20% Volume");
	AddMenuItem(menu, "10", "10% Volume");
	AddMenuItem(menu, "5", "5% Volume");
	
	if (isTuned[client]) {
		AddMenuItem(menu, "-1", "Stop Listening");
	}
	
	SetMenuExitButton(menu, true);
	DisplayMenu(menu, client, 20);
 
	return Plugin_Handled;
}

public Action:Cmd_Request(client, args) {
	if (Cmd_Check("interact", client)) {	
		if (args < 1) {
			ReplyToCommand(client, "\x04[FragRadio]\x01 Usage: !request <song>");
			return Plugin_Handled;
		}
		
		decl String:request[256];
		GetCmdArgString(request, sizeof(request));
		
		if (StrEqual(g_sDJ, "AutoDJ")) {
		ReplyToCommand(client, "\x04[FragRadio]\x01 Requests cannot be made when AutoDJ is onair");
		return Plugin_Handled;
		}
				
		if (strlen(request) < 8) {
			ReplyToCommand(client, "\x04[FragRadio]\x01 Requests must be more than 8 characters.");
			return Plugin_Handled;
		} else if (strlen(request) > 255) {
			ReplyToCommand(client, "\x04[FragRadio]\x01 Requests must be less than 255 characters.");
			return Plugin_Handled;
		}
		
		if (GetStreamInfo && !StrEqual(g_sDJ, "")) {
			ReplyToCommand(client, "\x04[FragRadio]\x01 Your song request has been sent to %s", g_sDJ);
		} else {
			ReplyToCommand(client, "\x04[FragRadio]\x01 Your song request has been sent!");
		}
		
		Client_Send("req", request, client);
	}
	
	return Plugin_Handled;
}

public Action:Cmd_Shoutout(client, args) {
	if (Cmd_Check("interact", client)) {		
		if (args < 1) {
			ReplyToCommand(client, "\x04[FragRadio]\x01 Usage: !shoutout <message>");
			return Plugin_Handled;
		}

		decl String:shoutout[256];
		GetCmdArgString(shoutout, sizeof(shoutout));
		
		if (StrEqual(g_sDJ, "AutoDJ")) {
			ReplyToCommand(client, "\x04[FragRadio]\x01 Shoutouts cannot be made when AutoDJ is onair");
			return Plugin_Handled;
		}
		
		if (strlen(shoutout) < 8) {
			ReplyToCommand(client, "\x04[FragRadio]\x01 Shoutouts must be more than 8 characters.");
			return Plugin_Handled;
		} else if (strlen(shoutout) > 255) {
			ReplyToCommand(client, "\x04[FragRadio]\x01 Shoutouts must be less than 255 characters.");
			return Plugin_Handled;
		}
		
		if (GetStreamInfo && !StrEqual(g_sDJ, "")) {
			ReplyToCommand(client, "\x04[FragRadio]\x01 Your shoutout has been sent to %s", g_sDJ);
		} else {
			ReplyToCommand(client, "\x04[FragRadio]\x01 Your shoutout has been sent!");
		}
		
		Client_Send("shout", shoutout, client);
	}
	
	return Plugin_Handled;
}

public Action:Cmd_Other(client, args) {
	if (Cmd_Check("interact", client)) {		
		if (args < 1) {
			ReplyToCommand(client, "\x04[FragRadio]\x01 Usage: !other <message>");
			return Plugin_Handled;
		}

		decl String:other[256];
		GetCmdArgString(other, sizeof(other));
		
		if (StrEqual(g_sDJ, "AutoDJ")) {
			ReplyToCommand(client, "\x04[FragRadio]\x01 Other content cannot be submitted when AutoDJ is onair");
			return Plugin_Handled;
		}
		
		if (strlen(other) < 8) {
			ReplyToCommand(client, "\x04[FragRadio]\x01 Other content must be more than 8 characters.");
			return Plugin_Handled;
		} else if (strlen(other) > 255) {
			ReplyToCommand(client, "\x04[FragRadio]\x01 Other content must be less than 255 characters.");
			return Plugin_Handled;
		}
		
		if (GetStreamInfo && !StrEqual(g_sDJ, "")) {
			ReplyToCommand(client, "\x04[FragRadio]\x01 Your content has been sent to %s", g_sDJ);
		} else {
			ReplyToCommand(client, "\x04[FragRadio]\x01 Your content has been sent!");
		}
		
		Client_Send("other", other, client);
	}
	
	return Plugin_Handled;
}

public Action:Cmd_Joke(client, args) {
	if (Cmd_Check("interact", client)) {		
		if (args < 1) {
			ReplyToCommand(client, "\x04[FragRadio]\x01 Usage: !joke <message>");
			return Plugin_Handled;
		}

		decl String:joke[256];
		GetCmdArgString(joke, sizeof(joke));
		
		if (StrEqual(g_sDJ, "AutoDJ")) {
			ReplyToCommand(client, "\x04[FragRadio]\x01 Cannot submit jokes when AutoDJ is onair");
			return Plugin_Handled;
		}
		
		if (strlen(joke) <4) {
			ReplyToCommand(client, "\x04[FragRadio]\x01 Jokes must be more than 4 characters.");
			return Plugin_Handled;
		} else if (strlen(joke) > 255) {
			ReplyToCommand(client, "\x04[FragRadio]\x01 Jokes must be less than 255 characters.");
			return Plugin_Handled;
		}
		
		if (GetStreamInfo && !StrEqual(g_sDJ, "")) {
			ReplyToCommand(client, "\x04[FragRadio]\x01 Your Joke has been sent to %s", g_sDJ);
		} else {
			ReplyToCommand(client, "\x04[FragRadio]\x01 Your Joke has been sent!");
		}
		
		Client_Send("Joke", joke, client);
	}
	
	return Plugin_Handled;
}

public Action:Cmd_Competition(client, args) {
	if (Cmd_Check("interact", client)) {		
		if (args < 1) {
			ReplyToCommand(client, "\x04[FragRadio]\x01 Usage: !competition <message>");
			return Plugin_Handled;
		}

		decl String:competition[256];
		GetCmdArgString(competition, sizeof(competition));
		
		if (StrEqual(g_sDJ, "AutoDJ")) {
			ReplyToCommand(client, "\x04[FragRadio]\x01 Cannot enter competition when AutoDJ is onair");
			return Plugin_Handled;
		}
		
		if (strlen(competition) <4) {
			ReplyToCommand(client, "\x04[FragRadio]\x01 Entries must be more than 4 characters.");
			return Plugin_Handled;
		} else if (strlen(competition) > 255) {
			ReplyToCommand(client, "\x04[FragRadio]\x01 Entries must be less than 255 characters.");
			return Plugin_Handled;
		}
		
		if (GetStreamInfo && !StrEqual(g_sDJ, "")) {
			ReplyToCommand(client, "\x04[FragRadio]\x01 Your Competition entry has been sent to %s", g_sDJ);
		} else {
			ReplyToCommand(client, "\x04[FragRadio]\x01 Your Competition entry has been sent!");
		}
		
		Client_Send("Competition", competition, client);
	}
	
	return Plugin_Handled;
}

public Action:Cmd_Choon(client, args) {
	if (Cmd_Check("rating", client)) {		
		decl String:name[128];
		GetClientName(client, name, sizeof(name));
		
		if (GetStreamInfo && !StrEqual(g_sSong, "")) {
			if (bTriggerShow)
			{
				PrintToChatAll("\x04[FragRadio]\x01 %s thinks %s is a CHOON!", name, g_sSong);
			}
			else
			{
				PrintToChat(client, "\x04[FragRadio]\x01 %s thinks %s is a CHOON!", name, g_sSong);
			}
		} else {
			if (bTriggerShow)
			{
				PrintToChatAll("\x04[FragRadio]\x01 %s thinks the current song is a CHOON!", name);
			}
			else
			{
				PrintToChat(client, "\x04[FragRadio]\x01 %s thinks the current song is a CHOON!", name);
			}
		}
		
		Client_Send("song", "ftw", client);
	}
	
	return Plugin_Handled;
}

public Action:Cmd_Poon(client, args) {
	if (Cmd_Check("rating", client)) {		
		decl String:name[128];
		GetClientName(client, name, sizeof(name));
		
		if (GetStreamInfo && !StrEqual(g_sSong, "")) {
			if (bTriggerShow)
			{
				PrintToChatAll("\x04[FragRadio]\x01 %s thinks %s is a POON!", name, g_sSong);
			}
			else
			{
				PrintToChat(client, "\x04[FragRadio]\x01 %s thinks %s is a POON!", name, g_sSong);
			}
		} else {
			if (bTriggerShow)
			{
				PrintToChatAll("\x04[FragRadio]\x01 %s thinks the current song is a POON!", name);
			}
			else
			{
				PrintToChat(client, "\x04[FragRadio]\x01 %s thinks the current song is a POON!", name);
			}
		}
		
		Client_Send("song", "ftl", client);
	}
	
	return Plugin_Handled;
}

public Action:Cmd_djFTW(client, args) {
	if (Cmd_Check("rating", client)) {		
		decl String:name[128];
		GetClientName(client, name, sizeof(name));
		
		if (GetStreamInfo && !StrEqual(g_sDJ, "")) {
			if (bTriggerShow)
			{
				PrintToChatAll("\x04[FragRadio]\x01 %s thinks %s is Awesome!", name, g_sDJ);
			}
			else
			{
				PrintToChat(client, "\x04[FragRadio]\x01 %s thinks %s is Awesome!", name, g_sDJ);
			}
		} else {
			if (bTriggerShow)
			{
				PrintToChatAll("\x04[FragRadio]\x01 %s thinks the current dj is Awesome!", name);
			}
			else
			{
				PrintToChat(client, "\x04[FragRadio]\x01 %s thinks the current dj is Awesome!", name);
			}
		}
		
		Client_Send("dj", "ftw", client);
	}
	
	return Plugin_Handled;
}

public Action:Cmd_djFTL(client, args) {
	if (Cmd_Check("rating", client)) {		
		decl String:name[128];
		GetClientName(client, name, sizeof(name));
		
		if (GetStreamInfo && !StrEqual(g_sDJ, "")) {
			if (bTriggerShow)
			{
				PrintToChatAll("\x04[FragRadio]\x01 %s thinks %s FAILS!", name, g_sDJ);
			}
			else
			{
				PrintToChat(client, "\x04[FragRadio]\x01 %s thinks %s FAILS!", name, g_sDJ);
			}
		} else {
			if (bTriggerShow)
			{
				PrintToChatAll("\x04[FragRadio]\x01 %s thinks the current dj FAILS!", name);
			}
			else
			{
				PrintToChat(client, "\x04[FragRadio]\x01 %s thinks the current dj FAILS!", name);
			}
		}
		
		Client_Send("dj", "ftl", client);
	}
	
	return Plugin_Handled;
}

public Action:Cmd_djsFTW(client, args) {
	if (Cmd_Check("rating", client)) {		
		decl String:name[128];
		GetClientName(client, name, sizeof(name));
		
		if (GetStreamInfo && !StrEqual(g_sDJ, "")) {
			if (bTriggerShow)
			{
				PrintToChatAll("\x04[FragRadio]\x01 %s thinks %s are Awesome!", name, g_sDJ);
			}
			else
			{
				PrintToChat(client, "\x04[FragRadio]\x01 %s thinks %s are Awesome!", name, g_sDJ);
			}
		} else {
			if (bTriggerShow)
			{
				PrintToChatAll("\x04[FragRadio]\x01 %s thinks the current dj's are Awesome!", name);
			}
			else
			{
				PrintToChat(client, "\x04[FragRadio]\x01 %s thinks the current dj's are Awesome!", name);
			}
		}
		
		Client_Send("djs", "ftw", client);
	}
	
	return Plugin_Handled;
}

public Action:Cmd_djsFTL(client, args) {
	if (Cmd_Check("rating", client)) {		
		decl String:name[128];
		GetClientName(client, name, sizeof(name));
		
		if (GetStreamInfo && !StrEqual(g_sDJ, "")) {
			if (bTriggerShow)
			{
				PrintToChatAll("\x04[FragRadio]\x01 %s thinks %s FAIL!", name, g_sDJ);
			}
			else
			{
				PrintToChat(client, "\x04[FragRadio]\x01 %s thinks %s FAIL!", name, g_sDJ);
			}
		} else {
			if (bTriggerShow)
			{
				PrintToChatAll("\x04[FragRadio]\x01 %s thinks the current dj's FAIL!", name);
			}
			else
			{
				PrintToChat(client, "\x04[FragRadio]\x01 %s thinks the current dj's FAIL!", name);
			}
		}
		
		Client_Send("djs", "ftl", client);
	}
	
	return Plugin_Handled;
}

public Action:Server_Send() {
	new Handle:dp = CreateDataPack();
	
	WritePackString(dp, "serverinfo");
	
	decl String:serverip[32];
	decl String:serverport[32];
	decl String:serverinfo[64];
		
	GetConVarString(FindConVar("hostip"), serverip, sizeof(serverip));
	new hostip = GetConVarInt(FindConVar("hostip"));	
	FormatEx(serverip, sizeof(serverip), "%u.%u.%u.%u", (hostip >> 24) & 0x000000FF, (hostip >> 16) & 0x000000FF, (hostip >> 8) & 0x000000FF, hostip & 0x000000FF);
	GetConVarString(FindConVar("hostport"), serverport, sizeof(serverport));
	FormatEx(serverinfo, sizeof(serverinfo), "%s:%s", serverip, serverport);
	WritePackString(dp, serverinfo);
	
	new Handle:socket = SocketCreate(SOCKET_TCP, OnSocketError);
	SocketSetArg(socket, dp);
	SocketConnect(socket, OnSocketConnected, OnSocketReceive, OnSocketDisconnected, StreamHost, StreamPort);
}

public Action:Server_Receive() {
	new Handle:dp = CreateDataPack();
	
	WritePackString(dp, "streaminfo");
	
	new Handle:socket = SocketCreate(SOCKET_TCP, OnSocketError);
	SocketSetArg(socket, dp);
	SocketConnect(socket, OnSocketConnected, OnSocketReceive, OnSocketDisconnected, StreamHost, StreamPort);
}

public Action:Client_Send(String:type[], String:message[], client) {
	new Handle:dp = CreateDataPack();
	
	WritePackString(dp, type);
	WritePackString(dp, message);
	
	decl String:ip[32];
	GetClientIP(client, ip, sizeof(ip), true);
	WritePackString(dp, ip);
	
	decl String:name[128];
	GetClientName(client, name, sizeof(name));
	WritePackString(dp, name);
	
	decl String:steamid[64];
	GetClientAuthString(client, steamid, sizeof(steamid));
	WritePackString(dp, steamid);
	
	decl String:serverip[32];
	decl String:serverport[32];
	decl String:serverinfo[64];
		
	GetConVarString(FindConVar("hostip"), serverip, sizeof(serverip));
	new hostip = GetConVarInt(FindConVar("hostip"));	
	FormatEx(serverip, sizeof(serverip), "%u.%u.%u.%u", (hostip >> 24) & 0x000000FF, (hostip >> 16) & 0x000000FF, (hostip >> 8) & 0x000000FF, hostip & 0x000000FF);
	GetConVarString(FindConVar("hostport"), serverport, sizeof(serverport));
	FormatEx(serverinfo, sizeof(serverinfo), "%s:%s", serverip, serverport);
	
	WritePackString(dp, serverinfo);
	WritePackCell(dp, client);
	
	new Handle:socket = SocketCreate(SOCKET_TCP, OnSocketError);
	SocketSetArg(socket, dp);
	SocketConnect(socket, OnSocketConnected, OnSocketReceive, OnSocketDisconnected, StreamHost, StreamPort);
}

public OnSocketConnected(Handle:socket, any:dp) {	
	ResetPack(dp);
	
	decl String:type[32];
	ReadPackString(dp, type, sizeof(type));
	
	decl String:socketStr[1024];
	
	if (StrEqual(type, "serverinfo")) {
		decl String:ip[64];
		ReadPackString(dp, ip, sizeof(ip));
		decl String:eip[128];
		EncodeBase64(eip, sizeof(eip), ip);
	
		FormatEx(socketStr, sizeof(socketStr), "GET %s?ip=%s HTTP/1.0\r\nHost: %s\r\nConnection: close\r\n\r\n", StreamUpdatePath, eip, StreamHost);
	} else if (StrEqual(type, "streaminfo")) {
		FormatEx(socketStr, sizeof(socketStr), "GET %s HTTP/1.0\r\nHost: %s\r\nConnection: close\r\n\r\n", StreamStatsPath, StreamHost);
	} else {
		decl String:etype[64];
		EncodeBase64(etype, sizeof(etype), type);
	
		decl String:message[256];
		ReadPackString(dp, message, sizeof(message));
		decl String:emessage[512];
		EncodeBase64(emessage, sizeof(emessage), message);
		
		decl String:ip[64];
		ReadPackString(dp, ip, sizeof(ip));
		decl String:eip[128];
		EncodeBase64(eip, sizeof(eip), ip);
		
		decl String:name[128];
		ReadPackString(dp, name, sizeof(name));
		decl String:ename[256];
		EncodeBase64(ename, sizeof(ename), name);
		
		decl String:steamid[64];
		ReadPackString(dp, steamid, sizeof(steamid));
		decl String:esteamid[128];
		EncodeBase64(esteamid, sizeof(esteamid), steamid);
		
		decl String:serverinfo[64];
		ReadPackString(dp, serverinfo, sizeof(serverinfo));
		decl String:eserverinfo[128];
		EncodeBase64(eserverinfo, sizeof(eserverinfo), serverinfo);
		
		FormatEx(socketStr, sizeof(socketStr), "GET %s?type=%s&content=%s&playersip=%s&playersname=%s&playerssteam=%s&serversip=%s HTTP/1.0\r\nHost: %s\r\nConnection: close\r\n\r\n", StreamGamePath, etype, emessage, eip, ename, esteamid, eserverinfo, StreamHost);
	}
	
	SocketSend(socket, socketStr);
}

public OnSocketReceive(Handle:socket, String:receiveData[], const dataSize, any:dp) {
	ResetPack(dp);
	
	decl String:type[32];
	ReadPackString(dp, type, sizeof(type));
	
	if (StrEqual(type, "streaminfo")) {
		strcopy(g_sDataReceived, sizeof(g_sDataReceived), receiveData);
	}
}

public OnSocketDisconnected(Handle:socket, any:dp) {
	ResetPack(dp);
	
	decl String:type[32];
	ReadPackString(dp, type, sizeof(type));
	
	if (StrEqual(type, "streaminfo")) {		
		new pos = StrContains(g_sDataReceived, "INFO");
		
		if (pos > 0) {
			decl String:streaminfo[5120];
			
			strcopy(streaminfo, sizeof(streaminfo), g_sDataReceived[pos - 1]);
			
			new String:file[512];
			BuildPath(Path_SM, file, 512, "configs/fragradio_stream_info.txt");

			new Handle:hFile = OpenFile(file, "wb");
			WriteFileString(hFile, streaminfo, false);
			CloseHandle(hFile);
			
			new Handle:Info = CreateKeyValues("INFO");
			FileToKeyValues(Info, file);
			
			DeleteFile(file);
			
			if (KvJumpToKey(Info, "FragRadio"))	{
				decl String:dj[512];
				decl String:song[512];
				KvGetString(Info, "DJ", dj, sizeof(dj), "Unknown");
				KvGetString(Info, "SONG", song, sizeof(song), "Unknown");
				
				if(!StrEqual(dj, g_sDJ)) {
					g_sDJ = dj;
					PrintToChatAll("\x04[FragRadio] Now Presenting:\x01 %s", g_sDJ);
				}

				if(!StrEqual(song, g_sSong)) {
					g_sSong = song;
					PrintToChatAll("\x04[FragRadio] Now Playing:\x01 %s", g_sSong);
				}
			}
			
			CloseHandle(Info);
		}
	}

	CloseHandle(dp);
	CloseHandle(socket);
}

public OnSocketError(Handle:socket, const errorType, const errorNum, any:dp) {
	LogError("[FragRadio] Socket error %d (errno %d)", errorType, errorNum);
	
	CloseHandle(dp);
	CloseHandle(socket);
}
