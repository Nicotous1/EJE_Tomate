<?php
	//Création du log
	$log = new Log(array("name" => "EtudeSave"));
	//Sauvegarde du log à l'aide la librairie
	$pdo = new EntityPDO()
	$pdo->save($log);
	




	$handler = new EntitySQLHandler();

	$handler->add((array(
			"class" => "Log",
			"atts" => array(
				array("att" => "name", "type" => AttSQL::TYPE_STR),
				array("att" => "param", "type" => AttSQL::TYPE_STR),
				array("att" => "user", "type" => AttSQL::TYPE_USER),
				array("att" => "time", "type" => AttSQL::TYPE_DATETIME),
		))))
	;	

	class Log extends Entity {}
?>