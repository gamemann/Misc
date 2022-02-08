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
							<h1 class="block-title">Add Change Log</h1>
							
							<div class="block-content">
								<?php
									if ($M['permissions']->havePermission($userInfo, 'server-addchangelog'))
									{
										
										// The content.
										echo '<label for="changelog-content">ChangeLog</label>';
										
										echo '<div id="changelog-content">';
										
										echo '</div>';
										
										// Server.
										echo '<div class="form-group">';
											echo '<label for="changelog-server">Server</label>';
											
											echo '<select name="changelog-server" id="changelog-server" class="form-control custom-control">';
												$query = $db->query("SELECT * FROM `gameservers`");
												
												if ($query)
												{
													while ($row = $query->fetch_assoc())
													{
														echo '<option value="' . $row['id'] . '">' . $row['ip'] . ':' . $row['port'] . '</option>';
													}
												}
											echo '</select>';
										echo '</div>';
										
										// Secret stuff (submittion process).
										echo '<input type="hidden" name="type" id="changelog-type" value="changelog" />';
										
										echo '<div class="text-center"><button type="button" class="btn btn-tdc" onClick="addChangeLog();">Add ChangeLog!</button></div>';
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
						<!-- Options -->
						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
							<h1 class="block-title">Options</h1>
							
							<div class="block-content">
								<div class="text-center">
									<span class="glyphicon glyphicon-plus-sign" style="color: #34CF0E; font-size: 150%; cursor: pointer; padding-right: 30px;" onClick="addNormChangeLog();"></span>
									
									<span class="glyphicon glyphicon-plus-sign" style="color: #00D1FF; font-size: 150%; cursor: pointer; padding-right: 30px;" onClick="addCategoryChangeLog();"></span>
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