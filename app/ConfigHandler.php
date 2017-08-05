<?php 
	namespace Core;
	use \Exception;

	class ConfigHandler
	{
		private static $params;
		private static $files;

		// Singleton interface
		protected static $_instance = null;

		public static function getInstance() {
			if (self::$_instance === null) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		protected function __construct() {
			$this->load_files();
		}

		public function get($name)  {
			if (!isset(self::$files[$name])) {
				throw new Exception("The name '$name' do not match any config files !", 1);
			}
			return self::$files[$name];
		}

		public function get_matches($regex) {
			$res = array();
			foreach (self::$files as $file) {
				if(preg_match("#" . $regex . "#", $file->getId()))
				{
					$res[] = $file;
				}		
			}
			return $res;
		}

		private function get_folders() {
			$folders = array("config/");
			$path = "modules/";
			foreach (scandir($path) as $result) {
			    if ( !($result === '.' or $result === '..') && (is_dir($path . $result)) ) {
			    	$folders[] = $path. $result . "/config/";
			    }			    
			}	
			return $folders;
		}

		private function load_files() {
			self::$files = array();
			foreach ($this->get_folders() as $folder) {
				foreach (scandir($folder) as $file) {
					if(preg_match('/.config$/', $file))
					{
						$path = $folder . $file;
						$configFile = new ConfigFile($path);
						self::$files[$configFile->getId()] = $configFile;
					}
				}
			}
			return $this;
		}
	}