<?php
	session_start();
	
	// Include the main file. Let's start!
	require_once($_SERVER['DOCUMENT_ROOT'] . '/init.php');
	
	// Steam Auth.
	require_once($_SERVER['DOCUMENT_ROOT'] . '/steamauth/steamauth.php');
	$userInfo = $M['users']->getUserInfo();
	
	// Get the DataBase.
	$db = $M['mysql']->receiveDataBase();
	
	// Set the time-zone to New York (for myself).
	date_default_timezone_set('America/New_York');
	
	// Now, we can set up the page!
	$success = false;
	$message = '';
	
	if (isset($_POST['type']))
	{
		$type = $_POST['type'];
		
		if ($type == 'admins')
		{
			$id = $_POST['serverID'];
			$admins = json_decode($_POST['admins'], true);
			
			if (is_array($admins))
			{
				$query = $db->query("SELECT * FROM `gameservers` WHERE `id`=" . $id);
				
				if ($query)
				{
					echo $db->error;
					while ($row = $query->fetch_assoc())
					{
						$currentDetails = json_decode($row['details'], true);
						
						$currentDetails['admins'] = array();
						
						foreach ($admins as $admin)
						{
							array_push($currentDetails['admins'], $admin);
						}
						
						$final = $M['main']->json_readable_encode($currentDetails);
						
						$finalQuery = $db->query("UPDATE `gameservers` SET `details`='" . $final . "' WHERE `id`=" . $id);
						
						if ($finalQuery)
						{
							$success = true;
						}
						else
						{
							$message = 'Error: ' . $db->error;
						}
					}
				}
			}
		}
		elseif ($type == 'general')
		{
			$ip = $db->real_escape_string($_POST['ip']);
			$publicIP = $db->real_escape_string($_POST['publicip']);
			$port = $db->real_escape_string($_POST['port']);
			$gameID = $_POST['game'];
			$locationID = $_POST['location'];
			$id = $_POST['serverID'];
			
			$query = $db->query("UPDATE `gameservers` SET `ip`='" . $ip . "', `publicip`='" . $publicIP . "', `port`=" . $port . ", `gameid`=" . $gameID . ", `locationid`=" . $locationID . " WHERE `id`=" . $id);
			
			if ($query)
			{
				$success = true;
			}
			else
			{
				$message = 'Error: ' . $db->error;
			}
		}
		else
		{
			$message = 'Not a valid category?';
		}
	}

	if ($success)
	{
		echo '1';
	}
	else
	{
		echo 'Error! (Message: ' . $message . ')';
	}
?>