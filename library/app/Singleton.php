<?php
	namespace Core;

	class Singleton
	{
		private static $_instance = null;


		## The construct should always be private
		private function __construct() {}


		public static function getInstance() {

		 if(is_null(self::$_instance)) {
		   self::$_instance = $this::loadInstance();  
		 }

		 return self::$_instance;
		}

		private static function loadInstance() {
			return new static();
		}
	}
?>