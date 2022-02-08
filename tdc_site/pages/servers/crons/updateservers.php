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
	
	// Source Query Class
	require_once($_SERVER['DOCUMENT_ROOT'] . '/SourceQuery/bootstrap.php');
	
	use xPaw\SourceQuery\SourceQuery
	
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
						<!-- Update Servers -->
						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
							<h1 class="block-title">Servers Updating...</h1>
							
							<div class="block-content">
								<?php
									$query = $db->query("SELECT * FROM `gameservers`");
									
									if ($query)
									{
										while ($row = $query->fetch_assoc())
										{
											$serverInfo = Array();
											$sQuery = new SourceQuery();
											
											try
											{
												$sQuery->Connect($row['ip'], $row['port'], 1);
												
												$serverInfo = $sQuery->GetInfo();
											}
											catch (Exception $e)
											{
												
											}
											
											$online = 0;
											if (isset($serverInfo['MaxPlayers']))
											{
												$online = 1;
											}

											$sQuery->Disconnect();
											
											$db->query("UPDATE `gameservers` SET `sinfo`='" . addcslashes($M['main']->json_readable_encode($serverInfo), '"') . "', `online`=" . $online . " WHERE `id`=" . $row['id']);
											
											echo 'Successfully updated: ' . $row['ip'] . ':' . $row['port'] . '<br />';
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