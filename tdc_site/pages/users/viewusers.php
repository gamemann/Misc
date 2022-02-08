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
						<!-- Server Bans -->
						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
							<h1 class="block-title">Users</h1>
							
							<div class="block-content">
								<table id="userstable">
									<thead>
										<tr>
											<th>Steam Name</th>
											<th>Steam ID</th>
											<th>Group</th>
											<th>Last Logged In</th>
										</tr>
									</thead>
									
									<tbody>
										<?php
											$query = $db->query("SELECT * FROM `users` ORDER BY `timeadded`");
											
											if ($query)
											{
												while ($row = $query->fetch_assoc())
												{
													$user = json_decode($row['sinfo'], true);
													
													// Get the group name.
													$group = 'Unknown';
													$q2 = $db->query("SELECT * FROM `usergroups` WHERE `id`=" . $row['groupid']);
													
													
													if ($q2)
													{
														while ($r2 = $q2->fetch_assoc())
														{
															$group = '<span style="color: ' . $r2['color'] . '">' . $r2['display'] . '</span>';
														}
													}
													
													$theDate = date('n-j-y g:i A T', $row['lastupdated']);
													
													echo '<tr>';
														echo '<td>' . $M['users']->formatUser($row['id']) . '</td>';
														echo '<td><a href="http://steamid.co/player.php?input=' . $user['steamid'] . '" target="_blank">' . $user['steamid'] . '</a></td>';
														echo '<td>' . $group . '</td>';
														echo '<td>' . $theDate . '</td>';
													echo '</tr>';
												}
											}
										?>
									</tbody>
								</table>
								
								<script>
									initUsersTable();
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