<?php 
	namespace Core;
	use \Exception;

	class ConfigHandler extends Singleton
	{
		private static $params;

		protected function __construct() {
			self::$params = array();
		}

		// Without "." return main config else go to modules
		protected function load($name) {
			echo "loading";
			$path = "";
			$ext = ".config";

			$params = explode(".", $name);
			$n = count($params);
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
			$path .= $ext;
			if (!file_exists($path)) {throw new Exception("'$name' does not match any file ! ('$path')", 1);}

			$param =  require($path);

			self::$params[$name] = $param;
			return $this;
		}

		public function get($name)  {
			if (!isset(self::$params[$name])) {
				$this->load($name);
			}
			return self::$params[$name];
		}
	}