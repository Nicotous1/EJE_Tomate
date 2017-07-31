<?php
	//OBLIGATOIRE !
	$prefix_name = "Ed";  # Ajouté au debut de chaque nom de route
	$prefix_url = ""; # Ajouté au debut de chaque pattern et constructeur de route
	$level = 1;

	//ROUTES  DES PAGES
	$ControllerRoutes = array(
		array(
			"name" => "Home",
			"pattern" => "",
		),

		array(
			"name" => "Candidater",
			"pattern" => "Candidater/",
		),

		array(
			'name' => 'Edit',
			"pattern" => 'MonProfil/',
		),
	);
?>