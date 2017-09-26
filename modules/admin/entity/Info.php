<?php
	namespace Admin\Entity;
	use Core\PDO\Entity\Entity;
	use Core\PDO\Entity\AttSQL;
	use DateTime;

	class Info extends Entity {

		protected static function get_array_EntitySQL() {
			return array(
				"atts" => array(
					array("att" => "type", "type" => AttSQL::TYPE_INT),
					array("att" => "etude", "type" => AttSQL::TYPE_DREF, "class"=>"Etude", "null" => false),
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
			return $a;
		}
	}
?>