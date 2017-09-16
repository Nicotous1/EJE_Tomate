<?php
	namespace Admin\Entity;
	use Core\PDO\Entity\Entity;
	use Core\PDO\Entity\AttSQL;
	use DateTime;

	class Com extends Entity {

		protected static function get_array_EntitySQL() {
			return array(
				"atts" => array(
					array("att" => "content", "type" => AttSQL::TYPE_STR),
					array("att" => "date", "type" => AttSQL::TYPE_DATE),

					array("att" => "etude", "type" => AttSQL::TYPE_DREF, "class"=>"Etude", "null" => false),
					array("att" => "author", "type" => AttSQL::TYPE_USER),
				),
			);
		}

		public function var_defaults() {
			return array("date" => new DateTime());
		}
	}
?>