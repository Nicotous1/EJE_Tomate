<?php
	namespace Core;

	class CookieController
	{

		private static $cookies = null;

		public function __construct()
		{
			if (self::$cookies === null) {
				self::$cookies = array();
			}
		}

		public function set($id, $value, $days) {
			self::$cookies[$id] = array(
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
			self::$cookies[$id] = array("value" => null, "time" => time());
			if (isset($_COOKIE[$id])) {
				unset($_COOKIE[$id]);
			}
			return $this;
		}

		public function flushCookies() {
			foreach(self::$cookies as $id => $cookie) {
				setcookie($id, $cookie['value'], $cookie['time']);
			}
			return $this;
		}
	}
?>