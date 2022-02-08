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
						<!-- Removed Expired -->
						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
							<h1 class="block-title">Removed Expired</h1>
							
							<div class="block-content">
								<?php
									$query = $db->query("SELECT * FROM `users` WHERE `expiredate` < " . time());
									
									if ($query)
									{
										$i = 0;
										while ($row = $query->fetch_assoc())
										{
											if ($row['expiredate'] < 1)
											{
												continue;
											}
											
											$uInfo = $M['users']->getUserInfo($row['id']);
											
											if (!$M['users']->checkLevel($uInfo, 3) && $row['groupid'] == 4)
											{
												// Remove them!
												$remove = $db->query("UPDATE `users` SET `groupid`=1 WHERE `id`=" . $row['id']);
												
												if ($remove)
												{
													$i++;
													
													if ($userInfo && $M['users']->checkLevel($userInfo, 5))
													{
														echo 'Removed ' . $M['users']->formatUser($row['id']) . '...<br />';
													}
												}
											}
										}
										
										echo '<br />Demoted <span class="colored">' . $i . '</span> users. (EXPIRED)';
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