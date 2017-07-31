<?php
	//SQL
	if ( $_SERVER['HTTP_HOST'] == "localhost" )
	{
		//NE MODIFIE PAS CEUX LA C'EST LES MIENS EN LOCAL
		$params['hostBDD'] = "127.0.0.1";
		$params['nameBDD'] = "je";
		$params['userBDD'] = "root";
		$params['mdpBDD'] = "";

		$_SERVER["REMOTE_ADDR"] = "192.0.0.1";
	}
	else 
	{
		//paramsETRES BDD
		$params['hostBDD'] = "ensaejuncqerp.mysql.db";
		$params['nameBDD'] = "ensaejuncqerp";
		$params['userBDD'] = "ensaejuncqerp";
		$params['mdpBDD'] = "ju0yGEaWNxK0";
	}
?>