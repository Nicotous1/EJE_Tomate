<?php
	namespace Admin\Entity;
	use Core\PDO\Entity\Entity;
	use Core\PDO\Entity\AttSQL;

	class DocType extends Entity {

		protected static function get_array_EntitySQL() {
			return array(
				"atts" => array(
					array("att" => "nom", "type" => AttSQL::TYPE_STR),
					array("att" => "var_name", "type" => AttSQL::TYPE_STR),
			));
		}

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