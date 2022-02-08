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
							<h1 class="block-title">Create Location!</h1>
							
							<div class="block-content">
								<?php
									if ($M['permissions']->havePermission($userInfo, 'server-addlocation'))
									{
										echo '<form action="/pages/admin/servers/submit.php" method="POST">';
											echo '<div class="form-group">';
												echo '<label for="location-name">Name</label>';
												echo '<input type="text" class="form-control custom-control" name="location-name" placeholder="Dallas, United States..." />';
											echo '</div>';											
											
											echo '<div class="form-group">';
												echo '<label for="location-shortname">Short Name</label>';
												echo '<input type="text" class="form-control custom-control" name="location-shortname" placeholder="Dallas..." />';
											echo '</div>';										
											
											echo '<div class="form-group">';
												echo '<label for="location-codename">Code Name</label>';
												echo '<input type="text" class="form-control custom-control" name="location-codename" placeholder="us-dallas..." />';
											echo '</div>';											
											
											echo '<div class="form-group">';
												echo '<label for="location-image">Image Path (From Root)</label>';
												echo '<input type="text" class="form-control custom-control" name="location-image" placeholder="images/path/to/image.png..." />';
											echo '</div>';
											
											// Secret stuff (submittion process).
											echo '<input type="hidden" name="type" value="location" />';
											
											echo '<div class="text-center"><button type="submit" class="btn btn-tdc">Add Location!</button></div>';
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
						<!-- Current Locations -->
						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
							<h1 class="block-title">Current Locations</h1>
							
							<div class="block-content">
								<?php
									$query = $db->query("SELECT * FROM `server-locations`");
									
									if ($query && $query->num_rows > 0)
									{
										echo '<ul>';
											while ($row = $query->fetch_assoc())
											{
												echo '<li><span class="colored">' . $row['codename'] . '</span> - ' . $row['display'] . ' (' . $row['short'] . ')</li>';
											}
										echo '</ul>';
									}
									else
									{
										echo '<p class="text-center">No locations found.</p>';
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