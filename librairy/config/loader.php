<?php
	$params = array();

	//PARAM LOADER
	include('modules.php');
	include('routes.php'); //Must be after modules
	include('firewall.php'); //Must be after modules
	include('sql.php'); //Must be after modules
	include('entity.php'); //Must be after modules
?>