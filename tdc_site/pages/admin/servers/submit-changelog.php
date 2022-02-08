<?php
	session_start();
	
	// Include the main file. Let's start!
	require_once($_SERVER['DOCUMENT_ROOT'] . '/init.php');
	
	// Steam Logout (custom-made).
	if (isset($_REQUEST['logout']))
	{
		unset($_SESSION['steamid']);
	}
	
	// Steam Auth.
	require_once($_SERVER['DOCUMENT_ROOT'] . '/steamauth/steamauth.php');
	$userInfo = $M['users']->getUserInfo();
	
	// Get the DataBase.
	$db = $M['mysql']->receiveDataBase();
	
	// Set the time-zone to New York (for myself).
	date_default_timezone_set('America/New_York');
	
	// Now, we can set up the page!
	
	// Most of page stuff will be under here.
	$success = false;
	$insertID = 0;
	$message = '';
	
	// First, check if the user is even logged in.
	if ($userInfo)
	{
		// Now, get the information.
		if (isset($_POST['type']))
		{
			$type = $_POST['type'];
			
			if ($type == 'changelog')
			{
				if ($M['permissions']->havePermission($userInfo, 'server-addchangelog'))
				{
					$serverID = $_POST['serverID'];
					$items = json_decode($_POST['items'], true);
					
					//print_r($items, false);
					
					$newArray = array();
					
					foreach ($items as $item)
					{
						if (is_array($item))
						{
							$details = array();
							foreach ($item['Details'] as $detail)
							{
								array_push($details, $detail);
							}
							
							$newArray[$item['Title']] = $details;
						}
						else
						{
							array_push($newArray, $item);
						}
					}
					
					$toJson = addcslashes($M['main']->json_readable_encode($newArray), '"');
					

					// Now for the fun part!
					$query = $db->query("INSERT INTO `server-changelog` (`userid`, `sid`, `details`, `timeadded`) VALUES (" . $userInfo['id'] . ", " . $serverID . ", '" . $toJson . "', " . time() . ");");
					
					if ($query)
					{
						$insertID = $db->insert_id;
						$success = true;
					}
					else
					{
						$success = false;
						$message = 'An error has occurred. Please report this! Error: ' . $db->error;
					}
				}
				else
				{
					$message = 'You do not have permission to add games.';
				}
			}
		}
	}
	
	if ($success)
	{
		echo 1;
	}
	else
	{
		echo 'An error has occurred. (Message: ' . $message . ')';
	}
?>