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
			
			if ($type == 'game')
			{
				if ($M['permissions']->havePermission($userInfo, 'server-addgame'))
				{
					$name = $db->real_escape_string($_POST['game-name']);
					$shortName = $db->real_escape_string($_POST['game-shortname']);
					$codeName = $db->real_escape_string($_POST['game-codename']);
					$imagePath = $db->real_escape_string($_POST['game-image']);
					
					// Do a check. Make sure the game code doesn't exist.
					$query = $db->query("SELECT * FROM `server-games` WHERE `codename`='" . $codeName . "'");
					
					if ($query && $query->num_rows < 1)
					{
						// Now for the fun part!
						$query = $db->query("INSERT INTO `server-games` (`display`, `short`, `codename`, `image`) VALUES ('" . $name . "', '" . $shortName . "', '" . $codeName . "', '" . $imagePath . "');");
						
						if ($query)
						{
							$insertID = $db->insert_id;
							$success = true;
							$message = 'Game successfully created!';
						}
						else
						{
							$success = false;
							$message = 'An error has occurred. Please report this! Error: ' . $db->error;
						}
					}
					else
					{
						$message = 'That code name already exists. SQL Error (if you know it doesn\'t exist): ' . $db->error;
					}
				}
				else
				{
					$message = 'You do not have permission to add games.';
				}
			}
			elseif ($type == 'server')
			{
				if ($M['permissions']->havePermission($userInfo, 'server-addserver'))
				{
					$gameID = $_POST['server-game'];
					$locationID = $_POST['server-location'];
					$ip = $db->real_escape_string($_POST['server-ip']);
					$publicIP = $db->real_escape_string($_POST['server-publicip']);
					$port = $db->real_escape_string($_POST['server-port']);
					
					// Do a check. Make sure the game code doesn't exist.
					$query = $db->query("SELECT * FROM `gameservers` WHERE `ip`='" . $ip . "' AND `port`=" . $port);
					
					if ($query && $query->num_rows < 1)
					{
						// Now for the fun part!
						$query = $db->query("INSERT INTO `gameservers` (`ip`, `publicip`, `port`, `gameid`, `locationid`) VALUES ('" . $ip . "', '" . $publicIP . "', " . $port . ", " . $gameID . ", " . $locationID . ");");
						
						if ($query)
						{
							$insertID = $db->insert_id;
							$success = true;
							$message = 'Game Server successfully created! You can view it <a href="/pages/servers/viewserver.php?id=' . $insertID . '">here</a>.';
						}
						else
						{
							$success = false;
							$message = 'An error has occurred. Please report this! Error: ' . $db->error;
						}
					}
					else
					{
						$message = 'Server already exists. SQL Error (if you know it doesn\'t exist): ' . $db->error;
					}
				}
				else
				{
					$message = 'You do not have permission to add servers.';
				}
			}
			elseif ($type == 'changelog')
			{
				if ($M['permissions']->havePermission($userInfo, 'server-addchangelog'))
				{
					$serverID = $_POST['serverID'];
					$items = json_decode($_POST['items'], true);
					
					print_r($items, false);
					
					// Do a check. Make sure the game code doesn't exist.
					$query = $db->query("SELECT * FROM `server-games` WHERE `codename`='" . $codeName . "'");
					
					if ($query && $query->num_rows < 1)
					{
						// Now for the fun part!
						$query = $db->query("INSERT INTO `server-games` (`display`, `short`, `codename`, `image`) VALUES ('" . $name . "', '" . $shortName . "', '" . $codeName . "', '" . $imagePath . "');");
						
						if ($query)
						{
							$insertID = $db->insert_id;
							$success = true;
							$message = 'Game successfully created!';
						}
						else
						{
							$success = false;
							$message = 'An error has occurred. Please report this! Error: ' . $db->error;
						}
					}
					else
					{
						$message = 'That code name already exists. SQL Error (if you know it doesn\'t exist): ' . $db->error;
					}
				}
				else
				{
					$message = 'You do not have permission to add games.';
				}
			
			}
			elseif ($type == 'updateserver')
			{
			
			}
			elseif ($type == 'location')
			{
				if ($M['permissions']->havePermission($userInfo, 'server-addlocation'))
				{
					$name = $db->real_escape_string($_POST['location-name']);
					$shortName = $db->real_escape_string($_POST['location-shortname']);
					$codeName = $db->real_escape_string($_POST['location-codename']);
					$imagePath = $db->real_escape_string($_POST['location-image']);
					
					// Do a check. Make sure the game code doesn't exist.
					$query = $db->query("SELECT * FROM `server-locations` WHERE `codename`='" . $codeName . "'");
					
					if ($query && $query->num_rows < 1)
					{
						// Now for the fun part!
						$query = $db->query("INSERT INTO `server-locations` (`display`, `short`, `codename`, `image`) VALUES ('" . $name . "', '" . $shortName . "', '" . $codeName . "', '" . $imagePath . "');");
						
						if ($query)
						{
							$insertID = $db->insert_id;
							$success = true;
							$message = 'Location successfully created!';
						}
						else
						{
							$success = false;
							$message = 'An error has occurred. Please report this! Error: ' . $db->error;
						}
					}
					else
					{
						$message = 'That code name already exists. SQL Error (if you know it doesn\'t exist): ' . $db->error;
					}
				}
				else
				{
					$message = 'You do not have permission to add locations.';
				}
			}
			else
			{
				$message = 'Nothing set?';
			}
		}
		else
		{
			$message = 'Nothing prepared.';
		}
	}
	else
	{
		$message = 'You do not have permission to this page.';
	}
	
	
	
?>

<html>
	<head>
		<?php
			$M['main']->loadJS();
			$M['main']->loadCSS();
		?>
		
		<!-- BootStrap stuff -->
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
	</head>
	
	<body>
		<div class="container-fluid" id="main">
			<?php
				$M['main']->loadLogo();
				$M['main']->loadNavBar(__FILE__, $userInfo);
			?>
			
			<!-- Page Specific Content -->
			<div id="page-content">
				<div style="display: none;">
					<?php
						echo steamlogin();
					?>
				</div>
				
				<?php
					// Before the page specific information, we have the UserBar!
					$M['main']->loadUserBar($userInfo);
				?>
				<div class="row">
					<!-- Main Content -->
					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
						<!-- Forums -->
						<?php
							echo '<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">';
								echo '<h1 class="block-title">Submittion</h1>';
								
								echo '<div class="block-content text-center">';
									echo '<p>' . $message . '</p>';
								echo '</div>';
							echo '</div>';
						?>
					</div>
				</div>
			</div>
			
			<?php
				$M['main']->loadFooter();
			?>
		</div>
	</body>
</html>