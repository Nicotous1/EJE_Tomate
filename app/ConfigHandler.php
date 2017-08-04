<?php 
	namespace Core;

	class ConfigHandler extends Singleton
	{
		// Without "." return main config else go to modules
		public function get($name) {
			$path = "library/";
			$ext = ".config";

			$params = split(".", $name);
			$n = count($params;
			switch ($n) {
				case 1:
					$path .= "config/" . $name;
					break;
				case 2:
					$path .= "modules/" . $params[0] . "/config/" . $params[1];
					break;
				default:
					throw new Exception("'$name' does not match any pattern :\n 'name' or 'module.name'", 1);
			}
			$path .= $ext
			if (!file_exists($path)) {throw new Exception("'$name' does not match any file ! ('$path')", 1);}

			return require($path);
		}
	}