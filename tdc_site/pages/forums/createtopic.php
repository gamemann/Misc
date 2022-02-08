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
	if (isset($_GET['forumid']))
	{
		$id = $_GET['forumid'];
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
						<!-- Forums -->
						<?php
							if ($M['forums']->isValidForum($id))
							{
								echo '<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">';
									echo '<h1 class="block-title">Create Topic!</h1>';
									
									echo '<div class="block-content text-center">';
										if ($userInfo && !$M['users']->isUserBanned($userInfo))
										{
											echo '<form action="/pages/forums/submit.php" method="POST">';
												// Topic Title
												echo '<div class="form-group">';
													echo '<label for="topic-title">Title</label>';
													echo '<input type="text" class="form-control custom-control" name="topic-title" id="topic-title" placeholder="Topic Title..." />';
												echo '</div>';
												
												// Topic Content
												echo '<div class="form-group">';
													echo '<label for="topic-content">Content</label>';
													echo '<textarea rows="5" class="form-control custom-control" name="topic-content" id="topic-content" placeholder="Topic Content..."></textarea>';
												echo '</div>';
												
												// Secret stuff (submit process).
												echo '<input type="hidden" name="type" value="topic" />';
												echo '<input type="hidden" name="forumid" value="' . $id . '" />';
												
												echo '<button type="submit" class="btn btn-tdc" onClick="return meetTopicLength(' . $M['config']->getValue('forum-topic-titlelength') . ', ' . $M['config']->getValue('forum-topic-contentlength') . ');">Create!</button>';
												
											echo '</form>';
										}
										else
										{
											echo '<p class="text-center">You do not have permission to post. Please make sure you are signed in and not banned.</p>';
										}
									echo '</div>';
								echo '</div>';
							}
							else
							{
								echo '<p class="text-center">This forum doesn\'t exist.</p>';
							}
						?>
					</div>
					
					<!-- SideBar -->
					<div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
						<!-- Options -->
						<?php
							if ($M['forums']->isValidForum($id))
							{
								echo '<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">';
									echo '<h1 class="block-title">Options</h1>';
									
									echo '<div class="block-content">';
										echo '<p class="text-center">There are no avaiable options at the moment.</p>';
									echo '</div>';
								echo '</div>';
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