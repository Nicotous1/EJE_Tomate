<?php
/*	
	To set depending on dev or prod
*/
	ini_set('display_errors',1);
	ini_set('html_errors', true);


/*
	Custom loading for class -> mainly based on namespace
*/
	function loadClass($s) {
		$e = explode("\\", $s);
		$n = count($e);
		if ($n < 2) {return false;}

		$path = "library/" . (($e[0] == "Core") ? "app" : "modules/" . $e[0]);

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



    $GA = new Core\Application();
    $GA->RunAndDie();
?>a