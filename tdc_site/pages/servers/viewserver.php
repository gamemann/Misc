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
	
	// Source Query Class
	require_once($_SERVER['DOCUMENT_ROOT'] . '/SourceQuery/bootstrap.php');
	
	use xPaw\SourceQuery\SourceQuery;
	
	$serverInfo = Array();
	$playersInfo = Array();
	$sQuery = 0;
	$online = false;
	$details = Array();
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
						<!-- Server List -->
						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
							<?php
								
								echo '<h1 class="block-title">Server #' . $id . '</h1>';
								
								echo '<div class="block-content">';
									if ($M['servers']->isValidServer($id) && $M['servers']->havePermission($userInfo, $id))
									{
										$query = $db->query("SELECT * FROM `gameservers` WHERE `id`=" . $id);
										
										if ($query && $query->num_rows > 0)
										{
											while ($row = $query->fetch_assoc())
											{
												$sQuery = new SourceQuery();
												
												try
												{
													$sQuery->Connect($row['ip'], $row['port']);
													
													$serverInfo = $sQuery->GetInfo();
													$playersInfo = $sQuery->GetPlayers();
												}
												catch (Exception $e)
												{
										
												}
												
												if (isset($serverInfo) && isset($serverInfo['MaxPlayers']) && $serverInfo['MaxPlayers'] > 0)
												{
													$online = true;
													$details = json_decode($row['details'], true);
												}
												
												if ($online)
												{
													echo '<ul class="list-unstyled">';
														echo '<li><strong class="colored">HostName:</strong> ' . $serverInfo['HostName'] . '</li>';
														echo '<li><strong class="colored">Players:</strong> ' . $serverInfo['Players'] . '</li>';
														echo '<li><strong class="colored">Max Players:</strong> ' . $serverInfo['MaxPlayers'] . '</li>';
														echo '<li><strong class="colored">Map:</strong> ' . $serverInfo['Map'] . '</li>';
													echo '</ul>';
												}
												else
												{
													echo '<p class="text-center">This server is offline.</p>';
												}
											}
										}
										else
										{
											echo '<p class="text-center">An error has occurred. Please report this! Error: ' . $db->error . '</p>';
										}
									}
									else
									{
										echo '<p class="text-center">Not a valid server or you don\'t have permission.</p>';
									}
								echo '</div>';
							?>
						</div>
						
						<?php
							// Players block.
							if ($online && is_array($playersInfo) && count($playersInfo) > 0)
							{
								echo '<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">';
									echo '<h1 class="block-title">Players</h1>';
									
									echo '<div class="block-content">';
										echo '<table id="playerstable">';
											echo '<thead>';
												echo '<tr>';
													echo '<th>Name</th>';
													echo '<th>Frags</th>';
													echo '<th>Time</th>';
												echo '</tr>';
											echo '</thead>';
											
											echo '<tbody>';
												foreach ($playersInfo as $player)
												{
													echo '<tr>';
														echo '<td>' . $player['Name'] . '</td>';
														echo '<td>' . $player['Frags'] . '</td>';
														echo '<td>' . $player['TimeF'] . '</td>';
													echo '</tr>';
												}
											echo '</tbody>';
										echo '</table>';
										
										echo '<script>initPlayersTable()</script>';
									echo '</div>';
								echo '</div>';
							}
						?>
					</div>
					
					<!-- SideBar -->
					<div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
						<!-- Server Details -->
						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
							<h1 class="block-title">Server Details</h1>
							
							<div class="block-content">
								<?php
									if ($online && is_array($details))
									{
										if (isset($details['managers']))
										{
											if (count($details['managers']) > 0)
											{
												echo '<h3>Server Managers</h3>';
												echo '<ul>';
													foreach ($details['managers'] as $manager)
													{	
														echo '<li>' . $M['users']->formatUser($manager) . '</li>';
													}
												echo '</ul>';
											}
										}
										
										if (isset($details['admins']))
										{
											if (count($details['admins']) > 0)
											{
												echo '<h3>Server Admins</h3>';
												
												echo '<ul>';
													foreach ($details['admins'] as $admin)
													{	
														echo '<li>' . $M['users']->formatUser($admin) . '</li>';
													}
												echo '</ul>';
											}
										}
										
										if (isset($details['description']))
										{
											echo '<h3>Description</h3>';
											echo '<p>' . $details['description'] . '</p>';
										}
									}
								?>
							</div>
						</div>						
						
						<!-- Server Details -->
						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
							<h1 class="block-title">Change Log</h1>
							
							<div class="block-content">
								<?php
									if ($online)
									{
										$query = $db->query("SELECT * FROM `server-changelog` WHERE `sid`=" . $id . " ORDER BY `timeadded` DESC LIMIT 0, 10");
										
										if ($query)
										{
											while ($row = $query->fetch_assoc())
											{
												// This is going to be a change-log. We're going to use the article-content background for the change-log itself.
												$changeLog = json_decode($row['details'], true);
												$theDate = date('n-j-y g:i A T', $row['timeadded']);
												
												// The Date.
												echo '<h3 class="text-center">' . $theDate . '</h3>';
												
												// The changelog.
												echo '<div class="article-content">';
													if ($changeLog)
													{
														echo '<ul>';
															foreach ($changeLog as $key => $value)
															{
																if (is_array($value))
																{
																	echo '<li>' . $key . '</li>';
																	echo '<ul>';
																		foreach ($value as $value2)
																		{
																			echo '<li>' . $value2 . '</li>';
																		}
																	echo '</ul>';
																}
																else
																{
																	echo '<li>' . $value . '</li>';
																}
															}
														echo '</ul>';
													}
													else
													{
														echo 'Empty';
													}
												echo '</div>';
												
												// The author.
												echo '<p class="text-center">By ' . $M['users']->formatUser($row['userid']) . '</p>';
											}
											
											echo '<p class="text-center"><a href="/pages/servers/viewchangelog.php?id=' . $id . '">View Full Change-Log</a></p>';
											
											if ($M['permissions']->havePermission($userInfo, 'server-addchangelog'))
											{
												echo '<div class="text-center"><a href="/pages/admin/servers/addchangelog.php?id=' . $id . '"><button type="button" class="btn btn-tdc">Add Change-Log!</button></a></div>';
											}
										}
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