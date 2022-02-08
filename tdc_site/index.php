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
						<!-- Latest News -->
						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
							<h1 class="block-title">Latest News</h1>
							
							<div class="block-content">
								<?php
									// Receive all the articles.
									$query = $db->query("SELECT * FROM `articles` ORDER BY `timeadded` DESC LIMIT 0, 10");
									
									if ($query && $query->num_rows > 0)
									{
										while ($row = $query->fetch_assoc())
										{
											// Article title.
											echo '<h2 class="article-title">' . htmlentities($row['title']) . '</h2>';
											
											// Article date.
											$theDate = date('n-j-y g:i A T', $row['timeadded']);
											echo '<p class="text-center"><span class="article-date">' . $theDate . '</span></p>';
											
											// Article content.
											echo '<div class="article-content">';
												echo nl2br(htmlentities($row['content']));
											echo '</div>';
											
											$userID = $M['users']->getUserInfo($row['userid']);
											echo '<div class="text-center">By ' . $M['users']->formatUser($row['userid']) . '</div>';
										}
									}
									else
									{
										echo '<p class="text-center">No articles in the database.</p>';
										echo '<p>' . $db->error . '</p>';
									}
									
									if ($userInfo && $M['permissions']->havePermission($userInfo, 'articles_addarticle'))
									{
										echo '<div class="text-center" style="margin-top: 5px;"><a href="/pages/admin/articles/addarticle.php"><button type="button" class="btn btn-tdc">Add Article!</button></a></div>';
									}
								?>
							</div>
						</div>
					</div>
					
					<!-- SideBar -->
					<div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
						<!-- Top Donors -->
						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
							<h1 class="block-title">Top Donors</h1>
							
							<div class="block-content">
								<?php
									$query = $db->query("SELECT * FROM `purchases`");
									
									if ($query)
									{
										$donors = array();
										
										while ($row = $query->fetch_assoc())
										{
											$donors[$row['userid']] += $row['amount'];
										}
										
										arsort($donors);
										
										$max = 10;
										$i = 1;
										echo '<table id="index-donorstable">';
											echo '<thead>';
												echo '<tr>';
													echo '<th>Place</th>';
													echo '<th>Name</th>';
													echo '<th>Amount</th>';
												echo '</tr>';
											echo '</thead>';
											
											echo '<tbody>';
												foreach ($donors as $key => $value)
												{
													if ($i > $max)
													{
														continue;
													}
													
													echo '<tr>';
														echo '<td>#' . $i . '</td>';
														echo '<td>' . $M['users']->formatUser($key) . '</td>';
														echo '<td>$' . $value . '</td>';
													echo '</tr>';
													
													$i++;
												}
											echo '</tbody>';
										echo '</table>';
										
										echo '<br /><p class="text-center"><a href="/pages/donate/viewdonors.php">View All Donors</a></p>';
									}
								?>
							</div>
						</div>							
						
						<!-- Statistics -->
						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
							<h1 class="block-title">Statistics</h1>
							
							<div class="block-content">
								<?php
									$stats = $M['main']->getStats();
									
									echo '<ul style="list-style-type: none">';
										echo '<li><a href="/pages/users/viewusers.php"><strong>' . $stats['users'] . '</strong></a> Linked Steam Accounts</li>'; 
									echo '</ul>';
								?>
							</div>
						</div>						
						
						<!-- Groups Legend -->
						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
							<h1 class="block-title">Group's Legend</h1>
							
							<div class="block-content">
								<?php
									echo '<ul>';
										$query = $db->query("SELECT * FROM `usergroups` ORDER BY `level` ASC");
										
										if ($query && $query->num_rows > 0)
										{
											while ($row = $query->fetch_assoc())
											{
												echo '<li>' . $M['users']->formatGroup($row['id'], 3) . '</li>';
											}
										}
										else
										{
											echo '<li>No groups found</li>';
										}
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