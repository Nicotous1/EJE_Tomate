<?php
	function loadClass($class) {
		foreach ($GLOBALS["params"]["modules"] as $module) {
			$file = "librairy/modules/".$module."/entity/".$class.".php";
			if (file_exists ($file)) {
    			include $file;
    			return true;
    		}
		}
		return false;
	}

	//Dynamique entity loader
	spl_autoload_register(function($s) {
		$e = explode("\\", $s);
		$class = array_pop($e);
		loadClass($class);
	});
?>