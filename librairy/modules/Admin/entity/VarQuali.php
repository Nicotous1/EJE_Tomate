<?php

	$handler = new EntitySQLHandler();

	$handler->add((array(
			"class" => "VarQuali",
			"atts" => array(
				array("att" => "nom", "type" => AttSQL::TYPE_STR),
				array("att" => "var_name", "type" => AttSQL::TYPE_STR),
				array("att" => "content", "type" => AttSQL::TYPE_STR),
		))))
	;	

	class VarQuali extends Entity {
		
	}
?>