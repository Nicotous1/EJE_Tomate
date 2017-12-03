<?php
	namespace Admin\Entity;
	use Core\PDO\Entity\Entity;
	use Core\PDO\Entity\AttSQL;

	use \DateTime;

	class Entreprise extends Entity {

		public static $typesArray = array(
			array("id" => 0, "name" => "Grand groupe"),
			array("id" => 1, "name" => "Institution publique"),
			array("id" => 2, "name" => "Association"),
			array("id" => 3, "name" => "PME"),
		);

		public static $secteursArray = array(
			array("id" => 0, "name" => "Banque, finance, Assurance"),
			array("id" => 1, "name" => "Audit / Conseil"),
			array("id" => 2, "name" => "Industrie"),
			array("id" => 3, "name" => "Énergie"),
			array("id" => 4, "name" => "Construction"),
			array("id" => 5, "name" => "Transport & logistique"),
			array("id" => 6, "name" => "Services"),
			array("id" => 7, "name" => "Informatique & Télécommunication"),
		);

		protected static function get_array_EntitySQL() {
			return array(
				"atts" => array(
					array("att" => "nom", "type" => AttSQL::TYPE_STR),
					array("att" => "type", "type" => AttSQL::TYPE_ARRAY, "list" => self::$typesArray),
					array("att" => "secteur", "type" => AttSQL::TYPE_ARRAY, "list" => self::$secteursArray),
					array("att" => "presentation", "type" => AttSQL::TYPE_STR),
					array("att" => "date_created", "type" => AttSQL::TYPE_DATE),
					array("att" => "date_modified", "type" => AttSQL::TYPE_DATE),
			));
		}

		public function var_defaults() {
			return array(
				"date_created" => new DateTime(),
				"date_modified" => new DateTime()
			);
		}
	}
?>