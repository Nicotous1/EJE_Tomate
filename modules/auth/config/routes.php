<?php
	//OBLIGATOIRE !
	$prefix_name = "Auth";  # Ajouté au debut de chaque nom de route
	$prefix_url = ""; # Ajouté au debut de chaque pattern et constructeur de route
	$controller = "AuthController";
	$level = 0;

	//ROUTES  DES PAGES
	$ControllerRoutes = array(
		array(
			'name' => 'Home',
			"pattern" => 'SignIn/',
		),

		array(
			'name' => 'Register',
			"pattern" => 'Auth/AJAX/Register/',
		),

		array(
			'name' => 'SignIn',
			"pattern" => 'Auth/AJAX/SignIn/',
		),

		array(
			"name" => "SignOut",
		),


	);
?>