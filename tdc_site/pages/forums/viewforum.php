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
	if (isset($_GET['id']))
	{
		$id = $_GET['id'];
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
						<?php
							if ($userInfo && !$M['users']->isUserBanned($userInfo))
							{
								echo '<div class="text-center">';
					
									echo '<a href="/pages/forums/createtopic.php?forumid=' . $id . '"><button type="button" class="btn btn-tdc">Create Topic!</button></a>';
								
								echo '</div>';
							}
						?>
						<!-- Forums -->
						<?php
							if ($M['forums']->isValidForum($id))
							{
								// Check for sub-forums.
								$query = $db->query("SELECT * FROM `forum-forums` WHERE `parent`=" . $id . " ORDER BY `listorder` ASC");
								
								if ($query && $query->num_rows > 0)
								{
									// Start the sub-forum block.
									echo '<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">';
										echo '<h1 class="block-title">Sub-Forums</h1>';
										
										echo '<div class="block-content">';
											echo '<table class="forums">';
												echo '<thead>';
												
												echo '</thead>';
												echo '<tbody>';
													$i = 1;
													$max = $query->num_rows;
													
													while ($row = $query->fetch_assoc())
													{
														$addClass = '';
														if ($i < $max)
														{
															$addClass = ' class="forumborder"';
														}
														
														echo '<tr' . $addClass . '>';
															echo '<td>';
																echo '<a href="/pages/forums/viewforum.php?id=' . $row['id'] . '"><span class="forum-title">' . $row['title'] . '</span></a>';
																echo '<br />';
																echo '<span class="forum-description">' . $row['description'] . '</span>';
															echo '</td>';
														echo '</tr>';
														
														$i++;
													}
												echo '</tbody>';
											echo '</table>';
										echo '</div>';
									echo '</div>';
								}
								
								// Now check for topics.
								echo '<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">';
									echo '<h1 class="block-title">Topics</h1>';
									
									echo '<div class="block-content">';
										$query = $db->query("SELECT * FROM `forum-threads` WHERE `forumid`=" . $id . " ORDER BY `timeadded` DESC");
										
										if ($query && $query->num_rows > 0)
										{
											echo '<table class="forums">';
												echo '<thead>';
													
												echo '</thead.';
												
												echo '<tbody>';
													$i = 1;
													$max = $query->num_rows;
													
													while ($row = $query->fetch_assoc())
													{
														$addClass = '';
														
														if ($i < $max)
														{
															$addClass = 'class="forumborder"';
														}
														
														echo '<tr' . $addClass . '>';
															// Main Title & Author
															echo '<td class="topic-left">';
																echo '<a href="/pages/forums/viewtopic.php?id=' . $row['id'] . '"><span class="forum-title">' . htmlentities($row['title']) . '</span></a>';
																echo '<br />';
																echo '<span class="forum-description">By ' . $M['users']->formatUser($row['userid']) . '</span>';
															echo '</td>';
															
															echo '<td class="topic-center">';
																// Reply Counts, etc.
																$replyCount = $M['forums']->getReplyCount($row['id']);
																
																if ($replyCount != 1)
																{
																	$phrase = 'replies';
																}
																else
																{
																	$phrase = 'reply';
																}
																echo '<span class="topic-center-replies"><span class="topic-center-reply-count"><strong>' . $replyCount . '</strong></span> ' . $phrase . '</span>';
															echo '</td>';
															
															echo '<td class="topic-right">';
																$theDate = date('n-j-y g:i', $row['timeadded']);
																echo '<span class="topic-right-date">Date Created: ' . $theDate . '</span>';
															echo '</td>';
														echo '</tr>';
													}
												echo '</tbody>';
											echo '</table>';
											
											
										}
										else
										{
											echo '<p class="text-center">No topics found.</p>';
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
				</div>
			</div>
			
			<?php
				$M['main']->loadFooter();
			?>
		</div>
	</body>
</html>