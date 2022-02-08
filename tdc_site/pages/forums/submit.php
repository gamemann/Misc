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
	
	// Most of page stuff will be under here.
	$success = false;
	$insertID = 0;
	$message = '';
	
	// First, check if the user is even logged in.
	if ($userInfo)
	{
		// Now, get the information.
		if (isset($_POST['type']))
		{
			$type = $_POST['type'];
			
			if ($type == 'topic')
			{
				$title = $db->real_escape_string($_POST['topic-title']);
				$content = $db->real_escape_string($_POST['topic-content']);
				$forumID = $_POST['forumid'];
				
				// Now for the fun part!
				$query = $db->query("INSERT INTO `forum-threads` (`userid`, `forumid`, `title`, `content`, `timeadded`, `lastupdated`) VALUES (" . $userInfo['id'] . ", " . $forumID . ", '" . $title . "', '" . $content . "', " . time() . ", " . time() . ");");
				
				if ($query)
				{
					$insertID = $db->insert_id;
					$success = true;
					$message = 'Successfully created topic! You can view it <a href="/pages/forums/viewtopic.php?id=' . $insertID . '">here</a>.';
				}
				else
				{
					$success = false;
					$message = 'An error has occurred. Please report this! Error: ' . $db->error;
				}
			}
			elseif ($type == 'reply')
			{
				$content = $db->real_escape_string($_POST['reply-content']);
				$topicID = $_POST['topicid'];
				
				$query = $db->query("INSERT INTO `forum-replies` (`userid`, `topicid`, `content`, `timeadded`, `lastupdated`) VALUES (" . $userInfo['id'] . ", " . $topicID . ", '" . $content . "', " . time() . ", " . time() . ");");
				
				if ($query)
				{
					$insertID = $db->insert_id;
					$success = true;
					$message = 'Successfully replied! You can view it on the topic <a href="/pages/forums/viewtopic.php?id=' . $topicID . '">here</a>.';
				}
				else
				{
					$success = false;
					$message = 'An error has occurred. Please report this! Error: ' . $db->error;
				}
			}
			else
			{
				$message = 'Wrong type?';
			}
		}
		else
		{
			$message = 'Nothing prepared.';
		}
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
					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
						<!-- Forums -->
						<?php
							echo '<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">';
								echo '<h1 class="block-title">Submittion</h1>';
								
								echo '<div class="block-content text-center">';
									echo '<p>' . $message . '</p>';
								echo '</div>';
							echo '</div>';
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