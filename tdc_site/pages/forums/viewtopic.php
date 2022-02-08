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
						<!-- Topic -->
						<?php
							if ($M['forums']->isValidTopic($id))
							{
								$query = $db->query("SELECT * FROM `forum-threads` WHERE `id`=" . $id);
								
								if ($query)
								{
									while ($row = $query->fetch_assoc())
									{
										echo '<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">';
											echo '<h1 class="block-title">' . htmlentities($row['title']) . '</h1>';
											
											echo '<div class="block-content">';
												echo '<table id="forum-replies">';
													echo '<tbody>';
														// Check how many replies there are.
														$addClass = '';
														$q2 = $db->query("SELECT * FROM `forum-replies` WHERE `topicid`=" . $id);
														
														if ($q2)
														{
															if ($q2->num_rows > 0)
															{
																$addClass = ' forumborder';
															}
														}
														// First reply (original post).
														echo '<tr class="reply-op' . $addClass . '">';
															// Side (User Information, etc).
															echo '<td class="reply-side">';
																// Get the main information.
																$uInfo = $M['users']->getUserInfo($row['userid']);
																
																// Post Date.
																$theDate = date('n-j-y g:i A T', $row['timeadded']);
																echo '<span class="reply-side-date">' . $theDate . '</span><br />';
																
																// Avatar
																echo '<img src="' . $uInfo['avatarmedium'] . '" alt="Avatar" /><br /><br />';
																
																// UserName
																echo '<span class="reply-side-username">' . $M['users']->formatUser($row['userid']) . '</span><br />';
																
																// Group
																echo '<span class="reply-side-group">' . $M['users']->formatGroup($uInfo['groupid']) . '</span>';

																
															echo '</td>';
															
															//Content.
															echo '<td class="reply-content">';
																echo nl2br(htmlentities($row['content']));
															echo '</td>';
														echo '</tr>';
														
														// All other replies.
														$q2 = $db->query("SELECT * FROM `forum-replies` WHERE `topicid`=" . $id . " ORDER BY `timeadded` ASC");
														
														if ($q2)
														{
															$i = 1;
															$max = $q2->num_rows;
															while ($r2 = $q2->fetch_assoc())
															{
																$addClass = '';
																
																if ($i < $max)
																{
																	$addClass = ' class="forumborder"';
																}
																
																echo '<tr' . $addClass . '>';
																	// Side (User Information, etc).
																	echo '<td class="reply-side">';
																		// Get the main information.
																		$u2Info = $M['users']->getUserInfo($r2['userid']);
																		
																		// Post Date.
																		$theDate = date('n-j-y g:i A T', $r2['timeadded']);
																		echo '<span class="reply-side-date">' . $theDate . '</span><br />';
																		
																		// Avatar
																		echo '<img src="' . $u2Info['avatarmedium'] . '" alt="Avatar" /><br /><br />';
																		
																		// UserName
																		echo '<span class="reply-side-username">' . $M['users']->formatUser($r2['userid']) . '</span><br />';
																		
																		// Group
																		echo '<span class="reply-side-group">' . $M['users']->formatGroup($u2Info['groupid']) . '</span>';
																		
																	echo '</td>';
																	
																	// Content.
																	echo '<td class="reply-content">';
																		echo nl2br(htmlentities($r2['content']));
																	echo '</td>';
																echo '</tr>';
																
																$i++;
															}
														}
													echo '</tbody>';
												echo '</table>';
												
												echo '<div class="reply-area text-center">';
													if ($userInfo)
													{
														echo '<form action="/pages/forums/submit.php" method="POST">';
															echo '<div class="form-group">';
																echo '<label for="reply-content">Reply</label>';
																echo '<textarea rows="3" name="reply-content" id="reply-content" class="form-control custom-control" placeholder="Your Reply..."></textarea>';
															echo '</div>';
															
															// Secret stuff (submit process).
															echo '<input type="hidden" name="type" value="reply" />';
															echo '<input type="hidden" name="topicid" value="' . $id . '" />';
															
															echo '<button type="submit" class="btn btn-tdc" onClick="return meetReplyLength(' . $M['config']->getValue('forum-topic-contentlength') . ');">Reply!</button>';
														echo '</form>';
													}
													else
													{
														echo '<p>You must be logged in to reply to topics.</p>';
													}
												echo '</div>';
											echo '</div>';
										echo '</div>';
									}
								}
							}
							else
							{
								echo '<p class="text-center">This topic doesn\'t exist.</p>';
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