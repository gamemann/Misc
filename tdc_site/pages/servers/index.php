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
					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
						<!-- Server List -->
						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
							<h1 class="block-title">Our Servers</h1>
							
							<div class="block-content">
								<table id="serverstable">
									<thead>
										<tr>
											<th>Host Name</th>
											<th>IP</th>
											<th>Port</th>
											<th>Players</th>
											<th>Max Players</th>
											<th>Map</th>
											<th>Connect</th>
										</tr>
									</thead>
									
									<tbody>
										<?php
											$query = $db->query("SELECT * FROM `gameservers` WHERE `online`=1");
											
											if ($query)
											{
												while ($row = $query->fetch_assoc())
												{
													$serverInfo = array();
													if (!$M['servers']->havePermission($userInfo, $row['id']))
													{
														continue;
													}
													
													$serverInfo = json_decode($row['sinfo'], true);

													if($serverInfo && $serverInfo['MaxPlayers'] > 0)
													{
														echo '<tr>';
															echo '<td><a href="/pages/servers/viewserver.php?id=' . $row['id'] . '">' . $serverInfo['HostName'] . '</a></td>';
															echo '<td>' . $row['ip'] . '</td>';
															echo '<td>' . $row['port'] . '</td>';
															echo '<td>' . $serverInfo['Players'] . '</td>';
															echo '<td>' . $serverInfo['MaxPlayers'] . '</td>';
															echo '<td>' . $serverInfo['Map'] . '</td>';
															echo '<td><a href="steam://connect/' . $row['ip'] . ':' . $row['port'] . '"><span class="glyphicon glyphicon-play connect-button"></span></a></td>';
														echo '</tr>';
													}
												}
											}
										?>
									</tbody>
								</table>
								
								<script>
									initServersTable();
								</script>
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