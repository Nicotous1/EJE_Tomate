<?php
	ini_set('display_errors',1);
	ini_set('html_errors', true);
	include("librairy/loader.php");
    $GA = new Core\Application();
    $GA->RunAndDie();
?>a