// I do not use Javascript often. I plan on learning it though!
var i = 0;
var sResults = "";

function AddMe()
{
	sResults = "";
	i++;
	sResults += i;
	
	document.getElementById("AddMeContent").innerHTML = sResults;
}

function DeleteMe()
{
	sResults = "";
	i--;
	sResults += i;
	
	document.getElementById("AddMeContent").innerHTML = sResults;
}

// Let's Create A Counter Game
var iCounter = 0;
var iStartTime = 0;
var iEndTime = 0;
var iTotalTime = 0;
var iCoolDown = 0;
var bEndGame = false;

function GameAction()
{
	var iCurTime = Math.floor(Date.now() / 1000);
	
	if (iCurTime < iCoolDown)
	{
		document.getElementById("GameButton").innerHTML = (iCoolDown - iCurTime) + " seconds";
		return;
	}
	else
	{
		document.getElementById("GameButton").innerHTML = "Click Me!";
	}
	
	iCounter++;
	
	document.getElementById("GameCounter").innerHTML = iCounter;
	
	if (iCounter == 1)
	{
		// Game just started.
		iStartTime = Math.floor(Date.now() / 1000);
		document.getElementById("GameMessage").innerHTML = "<div class=\"alert-warning\">The game has started! Click as fast as you can to 100!</div>";
	}
	else if (iCounter == 100)
	{
		// Game has ended.
		iEndTime = Math.floor(Date.now() / 1000);
		iTotalTime = iEndTime - iStartTime;
		bEndGame = true;
		
	}
	
	if (bEndGame)
	{
		// Game has ended.
		document.getElementById("GameMessage").innerHTML = "<div class=\"alert-success\">Congratulations! It took you <strong>" +  iTotalTime + "</strong> seconds to get to 100 clicks!</div>";
		
		// Reset Variables
		iCounter = 0;
		iStartTime = 0;
		iEndTime = 0;
		iTotalTime = 0;
		bEndGame = false;
		
		// Set the cooldown (5 seconds)
		iCoolDown = Math.floor(Date.now() / 1000) + 5;	// 5 seconds cool-down.
		
		document.getElementById("GameCounter").innerHTML = "0";
	}
}