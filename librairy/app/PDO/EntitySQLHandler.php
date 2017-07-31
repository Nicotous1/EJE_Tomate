<?php
	class EntitySQLHandler {
		const Key = "EntitySQLStore";

		public function __construct() {
			if (!isset($GLOBALS[$this::Key])) {$GLOBALS[$this::Key] = array();}
		}		

		public function add($t) {
			$e = new EntitySQL();
			$GLOBALS[$this::Key][$t["class"]] = $e;
			$e->load($t); //Very important to be after assign array (because IREF can be recursif !)
			return $this;
		}

		public function get($e) {
			if (!is_string($e)) {$e = get_class($e);}
			if (!isset($GLOBALS[$this::Key][$e])) {loadClass($e);} //Load class if not already loaded
			return $GLOBALS[$this::Key][$e];
		}
	}
?>