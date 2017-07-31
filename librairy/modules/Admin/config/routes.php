<?php
	//OBLIGATOIRE !
	$prefix_name = "Admin";  # Ajouté au debut de chaque nom de route
	$prefix_url = "Tomate/"; # Ajouté au debut de chaque pattern et constructeur de route
	$level = 2;

	//ROUTES  DES PAGES
	$ControllerRoutes = array(
		array(
			"name" => "Home",
			"pattern" => "",
		),

		array(
			'name' => 'Edit',
			"pattern" => 'Suivi/([0-9]{1,})/?',
			"constructor" => 'Suivi/$1/',
			'method' => 'Edit',
			'vars' => 'id'
		),
		
		array(
			'name' => 'New',
			"pattern" => 'New/',
			"method" => "Edit",
		),
		
		array(
			'name' => 'Quali',
		),
		
		array(
			'name' => 'test',
			'level' => 0,
		),
	);
?>