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
					<div class="col-xs-12 col-sm-8 col-md-8 col-lg-8">
						<!-- Create Game -->
						<div class="colxs-12 col-sm-12 col-md-12 col-lg-12">
							<h1 class="block-title">Create Game Server!</h1>
							
							<div class="block-content">
								<?php
									if ($M['permissions']->havePermission($userInfo, 'server-addserver'))
									{
										echo '<form action="/pages/admin/servers/submit.php" method="POST">';
										
											// Game.
											echo '<div class="form-group">';
												echo '<label for="server-game">Game</label>';
												echo '<select class="form-control custom-control" name="server-game">';
													$query = $db->query("SELECT * FROM `server-games`");
													
													if ($query)
													{
														while ($row = $query->fetch_assoc())
														{
															echo '<option value="' . $row['id'] . '"><span class="colored">' . $row['codename'] . '</span> - ' . $row['display'] . '</option>';
														}
													}
												echo '</select>';
											echo '</div>';		
											
											// Location.
											echo '<div class="form-group">';
												echo '<label for="server-location">Location</label>';
												echo '<select class="form-control custom-control" name="server-location">';
													$query = $db->query("SELECT * FROM `server-locations`");
													
													if ($query)
													{
														while ($row = $query->fetch_assoc())
														{
															echo '<option value="' . $row['id'] . '"><span class="colored">' . $row['codename'] . '</span> - ' . $row['display'] . '</option>';
														}
													}
												echo '</select>';
											echo '</div>';										
											
											// IP.
											echo '<div class="form-group">';
												echo '<label for="server-ip">IP</label>';
												echo '<input type="text" class="form-control custom-control" name="server-ip" placeholder="82.12.212.213..." />';
											echo '</div>';											
											
											// Public IP.
											echo '<div class="form-group">';
												echo '<label for="server-publicip">Public IP</label>';
												echo '<input type="text" class="form-control custom-control" name="server-publicip" placeholder="surf.thedevelopingcommunity.com...." />';
											echo '</div>';									
											
											// Port.
											echo '<div class="form-group">';
												echo '<label for="server-port">Port</label>';
												echo '<input type="text" class="form-control custom-control" name="server-port" placeholder="27015..." />';
											echo '</div>';
											
											// Secret stuff (submittion process).
											echo '<input type="hidden" name="type" value="server" />';
											
											echo '<div class="text-center"><button type="submit" class="btn btn-tdc">Add Server!</button></div>';
										echo '</form>';
									}
									else
									{
										echo '<p class="text-center">You do not have access to this page.</p>';
									}
								?>
							</div>
						</div>
					</div>
					
					<!-- SideBar -->
					<div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
						<!-- Current Games -->
						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
							<h1 class="block-title">Current Servers</h1>
							
							<div class="block-content">
								<?php
									$query = $db->query("SELECT * FROM `gameservers`");
									
									if ($query && $query->num_rows > 0)
									{
										echo '<ul>';
											while ($row = $query->fetch_assoc())
											{
												$sInfo = json_decode($row['sinfo'], true);
												echo '<li><span class="colored">' . $row['ip'] . ':' . $row['port'] . '</span> - ' . $sInfo['Players'] . '/ ' . $sInfo['MaxPlayers'] . '</li>';
											}
										echo '</ul>';
									}
									else
									{
										echo '<p class="text-center">No games found.</p>';
									}
								?>
							</div>
						</div>
						
					</div>
				</div>
			</div>
			
			<?php
				$M['main']->loadFooter();
			?>
		</div>
	</body>
</html>