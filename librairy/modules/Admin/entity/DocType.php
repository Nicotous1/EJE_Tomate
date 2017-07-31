<?php

	$handler = new EntitySQLHandler();

	$handler->add((array(
			"class" => "DocType",
			"atts" => array(
				array("att" => "nom", "type" => AttSQL::TYPE_STR),
				array("att" => "var_name", "type" => AttSQL::TYPE_STR),
		))))
	;	

	class DocType extends Entity {
		public function setVar_name($str) {
			$str = trim($str);
			$str = strtolower($str);
			$str = str_replace(" ", "_", $str);
			$str = preg_replace("/[^a-z0-9_]/", '', $str);
			$this->var_name = $str;
			return $this;
		}
	}
?>