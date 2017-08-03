<?php
	namespace Core;

	class CookieController
	{

		private $cookies;

		public function __construct()
		{
			$this->cookies = array();
		}

		public function set($id, $value, $days) {
			$this->cookies[$id] = array(
					'value' => $value,
					'time' => time() + $days*86400,
				);
			$_COOKIE[$id] = $value;
			return $this;
		}

		public function get($id) {
			return (isset($_COOKIE[$id])) ? $_COOKIE[$id] : null;
		}

		public function remove($id) {
			if (isset($_COOKIE[$id])) {
				$this->cookies[$id] = array("value" => null, "time" => time());
				unset($_COOKIE[$id]);
			}
			return $this;
		}

		public function flushCookies() {
			foreach($this->cookies as $id => $cookie) {
				setcookie($id, $cookie['value'], $cookie['time']);
			}
			return $this;
		}
	}
?>