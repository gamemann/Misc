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
	$id = 0;
	
	if (isset($_GET['id']))
	{
		$id = $_GET['id'];
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
					<div class="col-xs-12 col-sm-8 col-md-8 col-lg-8">
						<!-- Create Game -->
						<div class="colxs-12 col-sm-12 col-md-12 col-lg-12">
							<?php
								echo '<h1 class="block-title">Update Server #' . $id . '</h1>';
								
								echo '<div class="block-content">';
									if ($M['servers']->isValidServer($id))
									{
										if ($userInfo && ($M['users']->checkLevel($userInfo, 5) || $M['servers']->isManager($userInfo, $id)))
										{
											$query = $db->query("SELECT * FROM `gameservers` WHERE `id`=" . $id);
											
											if ($query)
											{
												while ($row = $query->fetch_assoc())
												{
													// Server IP.
													echo '<div class="form-group">';
														echo '<label for="server-ip">IP</label>';
														echo '<input type="text" class="form-control custom-control" name="server-ip" id="server-ip" value="' . $row['ip'] . '" />';
													echo '</div>';													
													
													// Server Public IP.
													echo '<div class="form-group">';
														echo '<label for="server-publicip">Public IP</label>';
														echo '<input type="text" class="form-control custom-control" name="server-publicip" id="server-publicip" value="' . $row['publicip'] . '" />';
													echo '</div>';	
													
													// Server Port.
													echo '<div class="form-group">';
														echo '<label for="server-port">Port</label>';
														echo '<input type="text" class="form-control custom-control" name="server-port" id="server-port" value="' . $row['port'] . '" />';
													echo '</div>';													
													
													// Game.
													echo '<div class="form-group">';
														echo '<label for="server-game">Game</label>';
														echo '<select id="server-game" name="server-game" class="form-control custom-control">';
															$q2 = $db->query("SELECT * FROM `server-games`");
															
															if ($q2)
															{
																while ($r2 = $q2->fetch_assoc())
																{
																	echo '<option value="' . $r2['id'] . '"';
																	
																	if ($r2['id'] == $row['gameid'])
																	{
																		echo ' selected="selected"';
																	}
																	echo '>' . $r2['codename'] . ' - ' . $r2['display'] . '</option>';
																	
																}
															}
														echo '</select>';
													echo '</div>';													
													
													// Location.
													echo '<div class="form-group">';
														echo '<label for="server-location">Location</label>';
														echo '<select id="server-location" name="server-location" class="form-control custom-control">';
															$q2 = $db->query("SELECT * FROM `server-locations`");
															
															if ($q2)
															{
																while ($r2 = $q2->fetch_assoc())
																{
																	echo '<option value="' . $r2['id'] . '"';
																	
																	if ($r2['id'] == $row['gameid'])
																	{
																		echo ' selected="selected"';
																	}
																	echo '>' . $r2['codename'] . ' - ' . $r2['display'] . '</option>';
																	
																}
															}
														echo '</select>';
													echo '</div>';
													
													echo '<div class="text-center"><button type="button" class="btn btn-tdc" onClick="saveServer(' . $id . ', \'general\');">Save!</button></div>';
												}
											}
										}
										else
										{
											echo '<p class="text-center">You do not have access to edit this server.</p>';
										}
									}
									else
									{
										echo '<p class="text-center">This server doesn\'t exist.</p>';
									}
								echo '</div>';
							?>
						</div>
					</div>
					
					<!-- SideBar -->
					<div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
						<!-- Current Games -->
						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
							<h1 class="block-title">Admins</h1>
							
							<div class="block-content">
								<div id="managementblocks">
									<?php
										if ($M['servers']->isValidServer($id))
										{
											if ($userInfo && ($M['users']->checkLevel($userInfo, 5) || $M['servers']->isManager($userInfo, $id)))
											{
												$query = $db->query("SELECT * FROM `gameservers` WHERE `id`=" . $id);
												
												if ($query)
												{
													while ($row = $query->fetch_assoc())
													{
														$details = json_decode($row['details'], true);
														
														if (is_array($details['admins']))
														{
															foreach ($details['admins'] as $value)
															{
																echo '<div class="managementrow">';
																	echo '<div class="form-group">';
																		echo '<input type="text" class="form-control custom-control userid" size="2" placeholder="User ID..." style="width: 20%; margin: 0 auto;" value="' . $value . '" />';
																	echo '</div>';
																echo '</div>';
															}
														}
													}
												}
												echo '<div class="text-center"><span class="glyphicon glyphicon-plus colored" style="font-size: 120%; cursor: pointer;" onClick="addAdminSlot();"></span></div>';
												echo '<div class="text-center" style="margin-top: 15px;"><button type="button" class="btn btn-tdc" onClick="saveServer(' . $id . ', \'admins\');">Save!</button></div>';
											}
										}
									?>
									
									<?php
									
									?>
								</div>
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