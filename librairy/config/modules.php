<?php
	//MODULES A CHARGER -> AUTOLOAD
	$path = 'librairy/modules/';
	$modules = array();
	foreach (scandir($path) as $result) {
	    if ( !($result === '.' or $result === '..') && (is_dir($path . $result)) ) {
	    	$modules[] = $result;
	    }
	}	
	$params['modules'] = $modules;
?>