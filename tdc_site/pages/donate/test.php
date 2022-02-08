<?php
	$custom = array
	(
		'userID' => 5,
		'userIP' => $_SERVER['REMOTE_ADDR'] 
	);
	
	echo serialize($custom);
	
	echo '<br />----<br />';
	
	$t = $_POST['custom'];
	
	$t = unserialize($t);
	
	foreach($_POST as $key => $value)
	{
		echo $key . ': ' . $value . '<br />';
	}
?>