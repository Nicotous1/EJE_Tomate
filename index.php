<?php

/*	
	To set depending on dev or prod
*/
	error_reporting(E_ALL);
	ini_set('display_errors',1);
	ini_set('html_errors', true);



function exception_error_handler($errno, $errstr, $errfile, $errline ) {
    throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
}
set_error_handler("exception_error_handler");


/*
	Custom loading for class -> mainly based on namespace
*/
	function loadClass($s) {
		$e = explode("\\", $s);
		$n = count($e);
		if ($n < 2) {return false;}

		$path = ($e[0] == "Core") ? "app" : "modules/" . strtolower($e[0]);

		foreach ($e as $i => $file) {
			if ($i == 0) {continue;}
			$path .= "/";
			$path .= ($i + 1 == $n) ? $file . ".php" : strtolower($file);
		}

		if (file_exists ($path)) {
			require_once $path;
			return true;
		}
	}

	//Dynamique CLass loader
	spl_autoload_register(function($s) {return loadClass($s);});

	// Run the application
	use Core\Application;
    $GA = Application::getInstance();
    $GA->RunAndDie();
?>a