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
						<!-- Information (Why VIP?) -->
						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
							<h1 class="block-title">Why VIP?</h1>
							
							<div class="block-content">
								<p>Our VIP package offers a long list of benefits. This includes:</p>
								
								<h2>Counter-Strike: Global Offensive</h2>
								
								<ul>
									<li>Full access to the In-Game Store (e.g. More items, etc).</li>
									<li>Receive three times the credits per minute.</li>
									<li>A "VIP" tag on the website and in the game servers</li>
								</ul>
							</div>
						</div>						
						
						<!-- Information (FAQ?) -->
						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
							<h1 class="block-title">F.A.Q.</h1>
							
							<div class="block-content">
								<p>
									<span class="question">Q: What happens if I charge-back?</span>
									<br />
									<span class="answer">A: We will ban you from our game servers.</span>
									
									<br />
									<br />
									
									<span class="question">Q: Do I receive perks automatically?</span>
									<br />
									<span class="answer">A: Yes, after you purchase this package, you will receive the benefits automatically.</span>
								</p>
							</div>
						</div>
					</div>
					
					<!-- SideBar -->
					<div class="col-xs-12 col-sm-4 col-md-4- col-lg-4">
						<!-- Donate -->
						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
							<h1 class="block-title">Donate!</h1>
							
							<div class="block-content">
								<?php
									if ($userInfo)
									{
										echo '<div class="text-center">';
											echo '<div id="daysamount"><span style="color: #647CFF;"><strong>30</strong></span> days of VIP.</div>';
											echo '<form action="https://www.paypal.com/cgi-bin/webscr" method="POST">';	// Action = https://www.paypal.com/cgi-bin/webscr
												echo '<div class="form-group">';
													echo '<label for="amount">Amount</label>';
													
													echo '<div class="input-group amount-box">';
														echo '<div class="input-group-addon custom-addon">$</div>'; 
														echo '<input type="text" id="amount" name="amount" class="form-control amount-text" value="7" onkeypress="return checkIfNumber(event);" />';
														echo '<div class="input-group-addon custom-addon">.00</div>'; 
													echo '</div>';
												echo '</div>';
												
												// PayPal Stuff.
												echo '<input type="hidden" name="cmd" value="_xclick">';
												echo '<input type="hidden" name="business" value="donations@thedevelopingcommunity.com">';
												echo '<input type="hidden" name="item_name" value="Donation">';
												echo '<input type="hidden" name="item_number" value="1">';
												echo '<input type="hidden" name="currency_code" value="USD">';
												echo '<input type="hidden" name="return" value="http://thedevelopingcommunity.com/pages/donate/paypal/success.php">';
												echo '<input type="hidden" name="cancel_return" value="http://thedevelopingcommunity.com/pages/donate/index.php">';
												echo '<input type="hidden" name="notify_url" value="http://thedevelopingcommunity.com/pages/donate/ipn.php">';
												
												$custom = array
												(
													'userID' => $userInfo['id'],
													'userIP' => $_SERVER['REMOTE_ADDR']
												);
												
												// Custom.
												$toStore = serialize($custom);
												echo "<input type='hidden' name='custom' value='$toStore' />";
												
												// Submit button.
												echo '<button type="submit" class="btn btn-tdc" onclick="return isEnough();">Donate!</button>';
											echo '</form>';
										echo '</div>';
									}
									else
									{
										echo '<div class="text-center">Please log in to donate.</div>';
									}
								?>
							</div>
						</div>						
						
						<!-- Information -->
						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
							<h1 class="block-title">Information</h1>
							
							<div class="block-content">
								<?php
									if ($userInfo)
									{
										echo '<ul class="list-unstyled">';
											$query = $db->query("SELECT * FROM `users` WHERE `id`=" . $userInfo['id']);
											
											if ($query)
											{
												while ($row = $query->fetch_assoc())
												{
													if ($row['expiredate'] > 0)
													{
														$expire = $row['expiredate'];
														
														if ($expire <= time())
														{
															$status = '<span style="color: #BF2020;">EXPIRED</span>';
														}
														else
														{
															$status = '<span style="color: #00D615;">ACTIVE</span>';
														}
														
														$theDate = date('n-j-y g:i A T', $expire);
														
														echo '<li>Status: ' . $status . '</li>';
														echo '<li>Expiration Date: <span class="colored">' . $theDate . '</span></li>';
													}
													else
													{
														echo '<li>When you buy VIP, the status and expiration date will show up here.</li>';
													}
												}
											}
										echo '</ul>';
										
										echo '<br /><p class="text-center"><a href="/pages/donate/viewmydonations.php">View My Donations</a></p>';
									}
									else
									{
										echo '<div class="text-center">Please log in to see your information.</div>';
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