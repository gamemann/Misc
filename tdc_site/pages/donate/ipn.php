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
				$req .= "&$key=$value";
			}
			
			$M['debug']->logMessage($req, 'purchase');
			
			// Header.
			$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
			$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
			$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
			
			$fp = curl_init('https://www.paypal.com/cgi-bin/webscr');
			
			if (!$fp)
			{
				$content = 'Error communicating with PayPal\'s CURL socket.';
			}
			else
			{
				curl_setopt($fp, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
				curl_setopt($fp, CURLOPT_POST, 1);
				curl_setopt($fp, CURLOPT_RETURNTRANSFER,1);
				curl_setopt($fp, CURLOPT_POSTFIELDS, $req);
				curl_setopt($fp, CURLOPT_SSL_VERIFYPEER, 1);
				curl_setopt($fp, CURLOPT_SSL_VERIFYHOST, 2);
				curl_setopt($fp, CURLOPT_FORBID_REUSE, 1);
				
				curl_setopt($fp, CURLOPT_CONNECTTIMEOUT, 30);
				curl_setopt($fp, CURLOPT_HTTPHEADER, array('Connection: Close'));

				$res = curl_exec($fp);
				$moveForward = true;
				
				if (curl_errno($fp) != 0)
				{
					$content = 'CURL error. Error: ' . curl_error($fp) . '.';
					curl_close($fp);
					$moveForward = false;
				}
				
				if ($moveForward)
				{
					$tokens = explode("\r\n\r\n", trim($res));
					$res = trim(end($tokens));
					
					if (strcmp($res, "VERIFIED") == 0)
					{
						// Payment succuessful. Enter them in the database?
						if (isset($_POST['mc_gross']) && isset($_POST['custom']))
						{
							$custom = unserialize($_POST['custom']);
							
							// Required.
							$amount = stripslashes($_POST['mc_gross']);
							$txn_id = stripslashes($_POST['txn_id']);
							
							if (isset($custom['userID']))
							{
								$userID = $custom['userID'];
							}
							else
							{
								$userID = -1;
							}
							
							if ($userID > 0 && $amount > 4)
							{
								// Optional
								$userIP = '';
								$email = '';
								
								if (isset($custom['userIP']))
								{
									$userIP = $custom['userIP'];
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
								$query = $db->query("INSERT INTO `purchases` (`product`, `userid`, `email`, `ip`, `timeadded`, `amountadded`, `amount`, `txn_id`) VALUES (1, " . $userID . ", '" . $db->real_escape_string($email) . "', '" . $db->real_escape_string($userIP) . "', " . time() . ", " . $addedTime . ", '" . $amount . "', '" . $txn_id . "')");
								
								if ($query)
								{
									// Success...
								}
								else
								{
									$content = 'Unable to insert into the database. (IP: ' . $userIP . ', UserID: ' . $userID . ', Email: ' . $email . ', Added Time: ' . $addedTime . ', Amount: ' . $amount . ') (Error: ' . $db->error . ')';
								}
								
								// Now, simply update expire time and group ID in the database.
								if ($userID > 0)
								{
									// Retrieve the User's information.
									$uInfo = $M['users']->getUserInfo($userID);
									
									// We must get the current expiration date (time).
									$expireTime = time();
									
									$temp = $db->query("SELECT * FROM `users` WHERE `id`=" . $userID);
									
									if ($temp)
									{
										while ($row = $temp->fetch_assoc())
										{
											$expireTime = $row['expiredate'];
										}
									}
									
									$totalTime = $expireTime + $addedTime;
									
									$query = $db->query("UPDATE `users` SET `expiredate`=" . $totalTime . " WHERE `id`=" . $userID);
									
									if ($query)
									{
										// VIP group has the power of two. Therefore, > 2 = VIP + 1
										if (!$M['users']->checkLevel($uInfo, 3))
										{
											$query = $db->query("UPDATE `users` SET `groupid`=4 WHERE `id`=" . $userID);
											
											if ($query)
											{
												// Success...
											}
											else
											{
												$content = 'Unable to update the user\'s group ID in the database. (IP: ' . $userIP . ', UserID: ' . $userID . ', Email: ' . $email . ', Added Time: ' . $addedTime . ', Amount: ' . $amount . ') (Error: ' . $db->error . ')';
											}
										}
									}
									else
									{
										$content = 'Unable to update the user\'s expiration time in the database. (IP: ' . $userIP . ', UserID: ' . $userID . ', Email: ' . $email . ', Added Time: ' . $addedTime . ', Amount: ' . $amount . ') (Error: ' . $db->error . ')';
									}

				
								}
							}
							else
							{
								$content = 'Amount or UserID is less than 1-4 (Amount: ' . $amount . ', UserID: ' . $userID . ')';
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
					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
						<!-- PayPal IPN -->
						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
							<h1 class="block-title">PayPal IPN</h1>
							
							<div class="block-content">
								<p class="text-center"><?php echo $content; ?></p>
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