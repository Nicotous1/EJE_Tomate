<?php
	namespace Admin\Entity;
	use Core\PDO\Entity\Entity;
	use Core\PDO\Entity\AttSQL;

	use Auth\Entity\User;
	use \DateTime;

	class Client extends Entity {

		public static $last_contactArray = array(
			array("id" => 1, "short" => "Email", "long" => "email"),
			array("id" => 2, "short" => "Téléphone", "long" => "téléphone"),
			array("id" => 3, "short" => "Courrier", "long" => "courrier"),
		);

		protected static function get_array_EntitySQL() {
			return array(
				"atts" => array(
					array("att" => "nom", "type" => AttSQL::TYPE_STR),
					array("att" => "prenom", "type" => AttSQL::TYPE_STR),
					array("att" => "adresse", "type" => AttSQL::TYPE_STR),
					array("att" => "mobile", "type" => AttSQL::TYPE_STR),
					array("att" => "fixe", "type" => AttSQL::TYPE_STR),
					array("att" => "mail", "type" => AttSQL::TYPE_STR),
					array("att" => "code_postal", "type" => AttSQL::TYPE_STR),
					array("att" => "ville", "type" => AttSQL::TYPE_STR),
					array("att" => "date_created", "type" => AttSQL::TYPE_DATE),
					array("att" => "date_modified", "type" => AttSQL::TYPE_DATE),
					
					array("att" => "titre", "type" => AttSQL::TYPE_ARRAY, "list" => User::$titreArray),
					array("att" => "last_contact", "type" => AttSQL::TYPE_ARRAY, "list" => self::$last_contactArray),
					
					array("att" => "entreprise", "type" => AttSQL::TYPE_DREF, "class"=>"Entreprise"),
				)
			);
		}

		public function var_defaults() {
			return array(
				"date_created" => new DateTime(),
				"date_modified" => new DateTime()
			);
		}
	}
?>