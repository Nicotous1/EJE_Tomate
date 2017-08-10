<?php
	namespace Admin\Entity;
	use Core\PDO\Entity\AttSQL;
	use Core\PDO\Entity\Entity;

	class sEtape extends Entity {

		static protected function get_array_EntitySQL() {
			return array(
				"table" => "jeh_etape",
				"atts" => array(
					array("att" => "jeh", "type" => AttSQL::TYPE_INT),
					array("att" => "etudiant", "type" => AttSQL::TYPE_DREF, "class" => "Auth\Entity\User"),
					array("att" => "etape", "type" => AttSQL::TYPE_DREF, "class" => "Etape", "null" => false),
				),
			);
		}

		public function var_defaults() {
			return array("jeh" => 1);
		}

		public function isValid() {
			return !(empty($this->get_Ids("etape")));
		}
	}
?>