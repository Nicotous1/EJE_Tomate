<?php

	$handler = new EntitySQLHandler();

	$handler->add((array(
			"class" => "DocTemplate",
			"atts" => array(
				array("att" => "nom", "type" => AttSQL::TYPE_STR),
				array("att" => "type", "type" => AttSQL::TYPE_DREF, "class" => "DocType"),
				array("att" => "doc", "type" => AttSQL::TYPE_DREF, "class" => "Document"),
		))))
	;	

	class DocTemplate extends Entity {

		public function toArray() {
			$r = parent::toArray();
			$r["type"] = $this->get_Ids("type");
			$r["doc"] = $this->get("doc");
			return $r;
		}

	}
?>