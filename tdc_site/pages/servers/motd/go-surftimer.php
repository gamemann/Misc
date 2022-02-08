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
						<!-- Server List -->
						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
							<h1 class="block-title">CS:GO Surf Timer MOTD</h1>
							
							<div class="block-content">
								<p>Welcome to TDC's CS:GO Surf Timer server! Rules, etc are listed below.</p>
								
								<ul>
									<li>Do not <span class="colored">excessively</span> mic spam.</li>
									<li>Do not <span class="colored">over-use</span> racism, etc.</li>
									<li>Do not <span class="colored">hack</span>, script, etc.</li>
								</ul>
							</div>
						</div>
					</div>
					
					<!-- SideBar -->
					<div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
						<!-- Server Details -->
						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
							<h1 class="block-title">Link Your Steam Account!</h1>
							
							<div class="block-content">
								<p>Link your Steam account to acess more features!</p>
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