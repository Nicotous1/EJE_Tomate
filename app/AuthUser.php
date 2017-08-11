<?php
	namespace Core;

	class AuthUser {
	
		public function isAllowed($level) {
			return ($level === 0);
		}

		public function getId() {
			return null;
		}
	
	}
?>