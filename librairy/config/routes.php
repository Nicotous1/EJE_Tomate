<?php
/*
	EXEMPLE :
		array(
			'name' => '_name_',
			"pattern" => 'Facture/-?([0-9]{1,})/?',
			"constructor" => 'Facture/$1/',
			'method' => 'facture',
			'vars' => 'id',
			'level' => '3',
		),
*/

	//INDEX GLOBALES DES ROUTES
	$GLOBALS['params']['routes'] = array();

	##Loading modules routes
	foreach ($params['modules'] as $module) {
		$controller = $module."Controller";
		$prefix_name = "";
		$prefix_url = "";
		$level = 0;
		include("librairy/modules/".$module."/config/routes.php");
		foreach ($ControllerRoutes as $route) {
			addRouteToIndex($route, $controller, $prefix_name, $prefix_url, $level);
		}		
	};

	function addRouteToIndex(array $route, $controller, $prefix_name, $prefix_url, $level = 0) {
		//CHECK ONLY COMPULSORY VARIABLE -> NAME, CONTROLLER
		if ($controller == null) {throw new Exception('A road has no controller !');};
		if (!isset($route["name"]) || $route["name"] == null) {throw new Exception('A road is not named for '. $controller);};

		//Mis a name si null
		$route["method"] = (isset($route["method"])) ? $route["method"] : $route["name"];

		$route["pattern"] = $prefix_url . ((isset($route["pattern"])) ? $route["pattern"] : $route["name"]);

		//FORMATAGE ROUTE
		$route["name"] = $prefix_name . $route["name"];

		$route["constructor"] = (isset($route["constructor"])) ? $prefix_url . $route["constructor"] : $route["pattern"];

		$route["level"] = (isset($route["level"])) ?$route["level"] : $level;
		
		$route["controller"] = $controller;
		
		
		//Ajout route à l'index
		$GLOBALS['params']['routes'][] = $route;
	};
?>