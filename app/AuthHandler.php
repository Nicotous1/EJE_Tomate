<?php 
	namespace Core;

	/*
		This is a very basic auth system -> it does not auth anyone always return the same AuthUser
		If you want a more complex Auth system you should do your module.
		However there is already an Auth module available.
	*/
	
	class AuthHandler extends Singleton {
		protected $user;

		protected function __construct() {
			$this->user = new AuthUser();
		}

		public function getUser() {
			return $this->user;
		}
	}
?>