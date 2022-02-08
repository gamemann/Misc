<?php
	$M = array();
	$docRoot = $_SERVER['DOCUMENT_ROOT'];
	
	// Loads all the modules.
	
	// Config for executing first.
	$executeFirst = array
	(
		'mysql'
	);
	
	foreach ($executeFirst as $value)
	{
		require_once($docRoot . '/modules/' . $value . '.php');
		$M[$value] = new $value;
	}
	
	// Now load the rest.
	$modules = scandir($docRoot . '/modules/');
	
	foreach ($modules as $value)
	{
		if (!strstr($value, '.php'))
		{
			continue;
		}
		
		$die = false;
		foreach ($executeFirst as $value2)
		{
			if ($value == $value2)
			{
				$die = true;
			}
		}
		
		if ($die === FALSE)
		{
			require_once($docRoot . '/modules/' . $value);
			$className = basename($value, '.php');
			$M[$className] = new $className;
		}
	}
	
	// Initialize them all.
	foreach ($executeFirst as $value)
	{
		$className = basename($value, '.php');
		
		if (isset($M[$className]))
		{
			$M[$className]->init();
		}
	}
	
	foreach ($modules as $value)
	{
		$className = basename($value, '.php');
		
		if (isset($M[$className]))
		{
			$M[$className]->init();
		}
	}
	
?>