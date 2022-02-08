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
						<!-- Forums -->
						<?php
							$query = $db->query("SELECT * FROM `forum-forums` WHERE `iscat`=1 ORDER BY `listorder` ASC");
							
							if ($query)
							{
								while ($row = $query->fetch_assoc())
								{
									echo '<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">';
										echo '<h1 class="block-title">' . $row['title'] . '</h1>';
										
										echo '<div class="block-content">';
											// Now for the fun stuff! Other forums!
											echo '<table class="forums">';
												echo '<thead>';
												
												echo '</thead>';
												echo '<tbody>';
													$q2 = $db->query("SELECT * FROM `forum-forums` WHERE `parent`=" . $row['id'] . " ORDER BY `listorder` ASC");
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
																echo '<td>';
																	echo '<a href="/pages/forums/viewforum.php?id=' . $r2['id'] . '"><span class="forum-title">' . $r2['title'] . '</span></a>';
																	echo '<br />';
																	echo '<span class="forum-description">' . $r2['description'] . '</span>';
																echo '</td>';
															echo '</tr>';
															
															$i++;
														}
													}
												echo '</tbody>';
											echo '</table>';
										echo '</div>';
									echo '</div>';
								}
							}
						?>
					</div>					
					
					<!-- SideBar -->
					<div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
						<!-- Forum Statistics -->
						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
							<h1 class="block-title">Statistics</h1>
							
							<div class="block-content">
								<?php
									$stats = $M['forums']->getStats();
									
									echo '<ul style="list-style-type: none">';
										echo '<li><span class="colored"><strong>' . $stats['categories'] . '</strong></span> Categories</li>'; 
										echo '<li><span class="colored"><strong>' . $stats['forums'] . '</strong></span> Forums</li>'; 
										echo '<li><span class="colored"><strong>' . $stats['topics'] . '</strong></span> Topics</li>'; 
										echo '<li><span class="colored"><strong>' . $stats['replies'] . '</strong></span> Replies</li>'; 
									echo '</ul>';
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