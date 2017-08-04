<?php
	//OBLIGATOIRE !
	$prefix_name = "Ajax";  # Ajouté au debut de chaque nom de route
	$prefix_url = "Ajax/"; # Ajouté au debut de chaque pattern et constructeur de route
	$level = 2;

	//ROUTES  DES PAGES
	$ControllerRoutes = array(
		array(
			'name' => 'SaveEtude',
		),

		array(
			'name' => 'SaveTemplate',
		),

		array(
			'name' => 'SaveDocType',
		),

		array(
			'name' => 'SaveClient',
		),

		array(
			'name' => 'SaveVar',
		),


		array(
			'name' => 'SaveEntreprise',
		),

		array(
			'name' => 'HandleWorkRequest',
		),

		array(
			'name' => 'SaveEtapes',
		),

		array(
			'name' => 'GenCustomTemplate',
		),

		array(
			'name' => 'AddDocEtude',
		),

		array(
			'name' => 'GenDocTemplate',
		),

		array(
			'name' => 'CopyEtude',
		),

		array(
			'name' => 'DownloadDocEtude',
			"pattern" => 'DownloadDocEtude/([0-9]{1,})/?',
			"constructor" => 'DownloadDocEtude/$1/',
			'method' => 'DownloadDocEtude',
			'vars' => 'id'
		),

		array(
			'name' => 'DownloadDoc',
			"pattern" => 'DownloadDoc/([0-9]{1,})/?',
			"constructor" => 'DownloadDoc/$1/',
			'method' => 'DownloadDoc',
			'vars' => 'id'
		),

		array(
			'name' => 'TemplateGet',
			"pattern" => 'TemplateGet/([0-9]{1,})/?',
			"constructor" => 'TemplateGet/$1/',
			'method' => 'TemplateGet',
			'vars' => 'id'
		),

		array(
			'name' => 'DownloadWorkRequest',
			"pattern" => 'DownloadWorkRequest/([0-9]{1,})/?',
			"constructor" => 'DownloadWorkRequest/$1/',
			'method' => 'DownloadWorkRequest',
			'vars' => 'id'
		),

		array(
			'name' => 'SaveUser',
			'level' => 1,
		),

		array(
			'name' => 'WorkRequest',
			'level' => 1,
		),
	);
?>