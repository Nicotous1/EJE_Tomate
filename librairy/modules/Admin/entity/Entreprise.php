<?php

	$handler = new EntitySQLHandler();

	$handler->add((array(
			"class" => "Entreprise",
			"atts" => array(
				array("att" => "nom", "type" => AttSQL::TYPE_STR),
				array("att" => "type", "type" => AttSQL::TYPE_STR),
				array("att" => "secteur", "type" => AttSQL::TYPE_STR),
				array("att" => "presentation", "type" => AttSQL::TYPE_STR),
				array("att" => "date_created", "type" => AttSQL::TYPE_DATE),
				array("att" => "date_modified", "type" => AttSQL::TYPE_DATE),
		))))
	;	

	class Entreprise extends Entity {

		public static $last_contactArray = array(
			array("id" => 1, "name" => "Email"),
			array("id" => 2, "name" => "Téléphone"),
			array("id" => 3, "name" => "Courrier"),
		);

		public function var_defaults() {
			return array(
				"date_created" => new DateTime(),
				"date_modified" => new DateTime()
			);
		}
	}
?>