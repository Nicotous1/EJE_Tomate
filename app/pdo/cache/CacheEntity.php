<?php
namespace Core\PDO\Cache;

/*
	Pour l'instant pas vraiment utile mais ca peut vite évoluer...
*/
	class CacheEntity {
		private $entity;

		public function __construct() {
			$this->entity = false;
		}

		public function set($e = null) {
			if (!(is_a($e, "Core\PDO\Entity\Entity") || $e === null)) {throw new Exception("A cache entity handles only an Entity or a null value !", 1);}
			$this->entity = $e;
			return $this;
		}

		public function get() {
			return $this->entity;
		}
	}
?>