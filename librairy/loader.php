<?php
	function loadClass($s) {
		$e = explode("\\", $s);
		$n = count($e);
		if ($n < 2) {return false;}
		var_dump($s);

		$path = "librairy/" . (($e[0] == "Core") ? "app" : "modules/" . $e[0]);

		foreach ($e as $i => $file) {
			if ($i == 0) {continue;}
			$path .= "/";
			$path .= ($i + 1 == $n) ? $file . ".php" : strtolower($file);
		}
		var_dump($path);
		if (file_exists ($path)) {
			require_once $path;
			return true;
		}
	}

	//Dynamique CLass loader
	spl_autoload_register(function($s) {return loadClass($s);});

	//GENERAL LOADER
	include('config/loader.php');
?>