<?php
	namespace Admin\Entity;
	use Core\PDO\Entity\Entity;
	use Core\PDO\Entity\AttSQL;

	class DocTemplate extends Entity {

		protected static function get_array_EntitySQL() {
			return array(
				"atts" => array(
					array("att" => "nom", "type" => AttSQL::TYPE_STR),
					array("att" => "context", "type" => AttSQL::TYPE_STR),
					array("att" => "type", "type" => AttSQL::TYPE_DREF, "class" => "DocType"),
					array("att" => "doc", "type" => AttSQL::TYPE_DREF, "class" => "Document"),
			));
		}

		public function toArray() {
			$r = parent::toArray();
			$r["type"] = $this->get_Ids("type");
			$r["doc"] = $this->get("doc");
			return $r;
		}

	}
?>