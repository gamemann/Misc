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
	
	$content = '';
?>

<html>
	<head>
		<?php
			$M['main']->loadJS();
			$M['main']->loadCSS();
			
			// Do some of the header stuff for PayPal.
			$req = 'cmd=_notify-validate';
			foreach ($_POST as $key => $value)
			{
				$value = urlencode(stripslashes($value));
				$req .= '&' . $key . '=' . $value;
			}
			
			// Header.
			$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
			$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
			$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
			
			$fp = fsockopen('ssl://www.paypal.com', 443, $errno, $errstr, 3):
			
			if (!$fp)
			{
				$content = 'Error communicating with PayPal\'s SSL socket. Error: ' . $errstr;
			}
			else
			{
				fputs($fp, $header . $req);
				
				while (!feof($fp))
				{
					$res = fgets($fp, 1024);
					
					if (strcmp($res, "VERIFIED") == 0)
					{
						// Payment succuessful. Enter them in the database?
						if (isset($_POST['amount']) && isset($_POST['userid']))
						{
							// Required.
							$amount = urlencode(stripslashes($_POST['amount']));
							$userID = urlencode(stripslashes($_POST['userid']));
							
							// Optional
							$userIP = '';
							$email = '';
							
							if (isset($_POST['userip']))
							{
								$userIP = urlencode(stripslashes($_POST['userip']));
							}
							
							if (isset($_POST['payer_email']))
							{
								$email = $_POST['payer_email'];
							}
							
							// Let's get the added time variable.
							$addedTime = 0;
							
							$factor = $amount / $M['config']->getValue('donate-factor');
							$days = $factor * 30;
							
							// Finally, convert to seconds.
							$addedTime = round($days * 86400, 0, PHP_ROUND_HALF_UP);
							
							// Now we're ready to insert.
							$query = $db->query("INSERT INTO `purchases` (`product`, `userid`, `email`, `ip`, `timeadded`, `amountadded`, `amount`) VALUES (1, " . $userID . ", '" . $email . "', '" . $userIP . "', " . time() . ", " . $addedTime . ", '" . $amount . "')");
							
							if ($query)
							{
								// Success...
							}
							else
							{
								$content = 'Unable to insert into the database. (IP: ' . $userIP . ', UserID: ' . $userID . ', Email: ' . $email . ', Added Time: ' . $addedTime . ', Amount: ' . $amount . ') (Error: ' . $db->error . ')';
							}
							
							// Now, simply update expire time in the database.
							if ($userID > 0)
							{
								$query = $db->query("UPDATE `users` SET `expiredate`=" . time() + $addedTime . " WHERE `id`=" . $userID);
								
								if ($query)
								{
									// Success...
								}
								else
								{
									$content = 'Unable to update the user in the databse. (IP: ' . $userIP . ', UserID: ' . $userID . ', Email: ' . $email . ', Added Time: ' . $addedTime . ', Amount: ' . $amount . ') (Error: ' . $db->error . ')';
								}
							}
						}
						else
						{
							$content = 'Amount or UserID not found. Please report this to an Administrator!';
						}
					}
					elseif (strcmp($res, "INVALID") == 0)
					{
						// Invalid payment.
						$content = 'Invalid payment!';
					}
					
					fclose($fp);
				}
			}
			
			// Debug message.
			if (!empty($content))
			{
				$extraInfo = ' (IP: ' . $_SERVER['REMOTE_ADDR'] . ')';
				$M['debug']->logMessage($content . $extraInfo, 'purchase');
			}
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
											echo '<form action="donate.php" method="POST">';
												echo '<div class="form-group">';
													echo '<label for="amount">Amount</label>';
													
													echo '<div class="input-group amount-box">';
														echo '<div class="input-group-addon custom-addon">$</div>'; 
														echo '<input type="text" id="amount" name="amount" class="form-control amount-text" value="7" onkeypress="return checkIfNumber(event);" />';
														echo '<div class="input-group-addon custom-addon">.00</div>'; 
													echo '</div>';
												echo '</div>';
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
					</div>
				</div>
			</div>
			
			<?php
				$M['main']->loadFooter();
			?>
		</div>
	</body>
</html>