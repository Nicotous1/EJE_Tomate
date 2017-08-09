<?php
	namespace Admin\Entity;
	use Core\PDO\Entity\Entity;
	use Core\PDO\Entity\AttSQL;

	class VarQuali extends Entity {

		protected static function get_array_EntitySQL() {
			return array(
				"atts" => array(
					array("att" => "nom", "type" => AttSQL::TYPE_STR),
					array("att" => "var_name", "type" => AttSQL::TYPE_STR),
					array("att" => "content", "type" => AttSQL::TYPE_STR),
			));
	}
?>