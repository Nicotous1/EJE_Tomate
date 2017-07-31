<?php

	$handler = new EntitySQLHandler();

	$handler->add((array(
			"class" => "sEtape",
			"table" => "jeh_etape",
			"atts" => array(
				array("att" => "jeh", "type" => AttSQL::TYPE_INT),
				array("att" => "etudiant", "type" => AttSQL::TYPE_DREF, "class" => "User"),
				array("att" => "etape", "type" => AttSQL::TYPE_DREF, "class" => "Etape", "null" => false),
			),
		)))
	;

	class sEtape extends Entity {

		public function var_defaults() {
			return array("jeh" => 1);
		}

		public function isValid() {
			return !(empty($this->get_Ids("etape")));
		}
	}
?>