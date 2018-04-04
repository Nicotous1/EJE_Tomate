<?php
	namespace Admin\Entity;
	use Core\PDO\Entity\Entity;
	use Core\PDO\Entity\AttSQL;
	use DateTime;

	class QualiRequest extends Entity {

		protected static function get_array_EntitySQL() {
			return array(
				"atts" => array(
					array("att" => "date", "type" => AttSQL::TYPE_DATE),
					array("att" => "com", "type" => AttSQL::TYPE_STR),

					array("att" => "etude", "type" => AttSQL::TYPE_DREF, "class"=>"Etude", "null" => false),
					array("att" => "template", "type" => AttSQL::TYPE_DREF, "class"=>"DocTemplate", "null" => false),
					array("att" => "author", "type" => AttSQL::TYPE_USER),
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
			return $a;
		}
	}
?>