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
						<!-- Forums -->
						<?php
							if ($userInfo && $M['permissions']->havePermission($userInfo, 'articles_addarticle'))
							{
								echo '<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">';
									echo '<h1 class="block-title">Create Article!</h1>';
									
									echo '<div class="block-content text-center">';

											echo '<form action="/pages/admin/articles/submit.php" method="POST">';
												// Article Title
												echo '<div class="form-group">';
													echo '<label for="article-title">Title</label>';
													echo '<input type="text" class="form-control custom-control" name="article-title" placeholder="Article Title..." />';
												echo '</div>';
												
												// Article Content
												echo '<div class="form-group">';
													echo '<label for="article-content">Content</label>';
													echo '<textarea rows="3" class="form-control custom-control" name="article-content" placeholder="Article Content..."></textarea>';
												echo '</div>';
												
												echo '<button type="submit" class="btn btn-tdc">Create!</button>';
											echo '</form>';
									echo '</div>';
								echo '</div>';
							}
							else
							{
								echo '<p class="text-center">You do not have access to this page.</p>';
							}
						?>
					</div>
				</div>
			</div>
			
			<?php
				$M['main']->loadFooter();
			?>
		</div>
	</body>
</html>