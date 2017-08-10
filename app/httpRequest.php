<?php
	namespace Core;

	class httpRequest extends Singleton {

		protected function __construct() {
			// Try to decode json -> angular
			if (empty($_POST)) {
				$res = json_decode(file_get_contents("php://input"), true);
				if (json_last_error() == JSON_ERROR_NONE) {
					$_POST = $res;
				}
			}
		}

		private function getter($var, $names, $default) {
			if (!is_array($names)) {$names = array($names);}
			$res = array();
			foreach ($names as $name) {
				$res[$name] = (isset($var[$name])) ? $var[$name] : $default;
			}
			return (count($res) > 1) ? $res : $res[$names[0]];
		}

		public function get($name, $default = null) {
			return $this->getter($_GET, $name, $default);
		}

		public function post($name, $default = null) {
			return $this->getter($_POST, $name, $default);
		}

		public function cookie($name, $default = null) {
			return $this->getter($_COOKIE, $name, $default);
		}
	}
?>