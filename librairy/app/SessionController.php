<?php
	namespace Core;

	class SessionController {

		private function open() {
			if (!$this->isOpen()) {
			    session_start();
			}
			return $this;
		}

		public function isOpen() {
			if ( php_sapi_name() !== 'cli' ) {
				if ( version_compare(phpversion(), '5.4.0', '>=') ) {
					return session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE;
				} else {
					return session_id() === '' ? FALSE : TRUE;
				}
}			
			return FALSE;		
		}

		public function set($id, $value) {
			$this->open();
			$_SESSION[$id] = $value;
			session_write_close();
			return $this;
		}

		public function get($id) {
			$this->open();
			return (isset($_SESSION[$id])) ? $_SESSION[$id] : null;
			session_write_close();
		}

		public function remove($id) {
			$this->open();
			if (isset($_SESSION[$id])) {unset($_SESSION[$id]);}
			session_write_close();
			return $this;
		}
	}
?>