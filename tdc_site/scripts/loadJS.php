<?php
	// This file loads the JavaScript files.
	require_once($_SERVER['DOCUMENT_ROOT'] . '/init.php');
	
	$db = $m['mysql']->receiveDatabase();
	
	// Add files outside the JavaScript folder here.
	echo '<script src="' . $_SERVER['DOCUMENT_ROOT'] . '/DataTables/datatables.min.js" type="text/javascript"></script>';	// DataTables
	
	// Default Files (declared in the database).
	$query = $db->query("SELECT * FROM `loadfiles` WHERE `type`=2 ORDER BY `listorder` ASC");
	
	if ($query)
	{
		while ($row = $query->fetch_assoc())
		{
			echo '<script src="' . $_SERVER['DOCUMENT_ROOT'] . '/js/' . $row['file'] . '" type="text/javascript"></script>';
		}
	}
?>