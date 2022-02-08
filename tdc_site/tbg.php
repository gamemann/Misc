<?php
	$db = new mysqli('localhost', 'xxxxx', 'xxxxx', 'xxxxx');
	$userip = $_SERVER['REMOTE_ADDR'];
	$allowedips = array 
	(
		// ...
	);
	$allowed = false;
	$body = '';
	
	foreach ($allowedips as $ip)
	{
		if ($userip == $ip)
		{
			$allowed = true;
		}
	}
	
	if (isset($_POST['body']) && !empty($_POST['body']))
	{
		$newbody = $db->real_escape_string($_POST['body']);
		$result = $db->query("UPDATE `tbg` SET `body`='" . $newbody . "' WHERE `id`=1");
		
		if ($result)
		{
			header('Location: tbg.php');
		}
	}
	
	$result = $db->query("SELECT * FROM `tbg` WHERE `id`=1");
	while ($t = $result->fetch_assoc())
	{
		$body = $t['body'];
	}
	
	if(isset($_REQUEST['editmode']) && $allowed)
	{
		echo '<form action="tbg.php" method="POST">';
		echo '<textarea name="body" cols="100" rows="40">' . $body . '</textarea><br />';
		echo '<input type="submit" name="submit" value="Submit!" />';
		echo '</form>';
	}
	else
	{
		echo $body;
	}
	
	if ($allowed && !isset($_REQUEST['editmode']))
	{
		echo '<form action="tbg.php" method="GET">';
		echo '<input type="submit" name="editmode" value="Edit Body" />';
		echo '</form>';
	}
?>
