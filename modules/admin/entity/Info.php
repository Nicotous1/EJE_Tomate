<?php
	namespace Admin\Entity;
	use Core\PDO\Entity\Entity;
	use Core\PDO\Entity\AttSQL;
	use DateTime;

	class Info extends Entity {


		public static $typeArray = array(
			array("id" => 0, "str_action" => "a modifé l'étude", "icon" => "mode_edit"),
			array("id" => 1, "str_action" => "a crée l'étude", "icon" => "mode_edit"),
			array("id" => 2, "str_action" => "a commenté l'étude", "icon" => "insert_comment"),
			array("id" => 3, "str_action" => "a modifié les étapes de l'étude", "icon" => "mode_edit"),
			array("id" => 4, "str_action" => "a copié l'étude", "icon" => "content_copy"),
			array("id" => 5, "str_action" => "a modifié le client de l'étude", "icon" => "mode_edit"),
			array("id" => 6, "str_action" => "a ajouté un document à l'étude", "icon" => "picture_as_pdf"),
		);

		protected static function get_array_EntitySQL() {
			return array(
				"atts" => array(
					array("att" => "type", "type" => AttSQL::TYPE_ARRAY, "list" => self::$typeArray),
					array("att" => "etude", "type" => AttSQL::TYPE_DREF, "class"=>"Etude", "null" => false),
					array("att" => "com", "type" => AttSQL::TYPE_DREF, "class"=>"Com"),
					array("att" => "doc", "type" => AttSQL::TYPE_DREF, "class"=>"DocEtude"),
					array("att" => "author", "type" => AttSQL::TYPE_USER),
					array("att" => "date", "type" => AttSQL::TYPE_DATE),
				),
			);
		}


		public function var_defaults() {
			return array("date" => new DateTime());
		}

		public function toArray() {
			$a = parent::toArray();
			$a["author"] = $this->get("author")->toArray();
			$a["etude"] = $this->get("etude")->toArray();
			$a["type"] = $this->get("type");
			$a["details"] = $this->get("details");
			return $a;
		}

		public function getDetails() {
			switch ($this->get("type")["id"]) {
				case 2:
					return $this->get("com")->get("content");
				
				default:
					return null;
			}
		}
	}
?>