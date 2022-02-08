<?php
	// This file loads the CSS files.
	require_once($_SERVER['DOCUMENT_ROOT'] . '/init.php');
	
	$db = $m['mysql']->receiveDatabase();
	
	// Add files outside the JavaScript folder here.
	echo '<link rel="stylesheet" type="text/css" href="' . $_SERVER['DOCUMENT_ROOT'] . '/DataTables/datatables.min.css" />';
	
	// Default Files (declared in the database).
	$query = $db->query("SELECT * FROM `loadfiles` WHERE `type`=1 ORDER BY `listorder` ASC");
	
	if ($query)
	{
		while ($row = $query->fetch_assoc())
		{
			echo '<link rel="stylesheet" type="text/css" href="' . $_SERVER['DOCUMENT_ROOT'] . '/css/' . $row['file'] . '" />';
		}
	}
?>