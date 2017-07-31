<?php
	ini_set('display_errors',1);
	include("librairy/loader.php");
    $GA = new Core\Application();
    $GA->RunAndDie();
?>