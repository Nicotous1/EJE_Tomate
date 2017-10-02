<?php
	namespace Ajax;
	
	use Core\PDO\EntityPDO;
	use Core\PDO\Request;
	use Core\Controller;

	use Admin\Entity\Etude;
	use Admin\Entity\Client;
	use Admin\Entity\Entreprise;
	use Admin\Entity\Document;
	use Admin\Entity\WorkRequest;
	use Admin\Entity\DocEtude;
	use Admin\Entity\DocType;
	use Admin\Entity\DocTemplate;
	use Admin\Entity\VarQuali;
	use Admin\Entity\Info;
	use Admin\Entity\InfoController;

	require_once("plugins/WordTemplate/loader.php");
	use WordTemplate\Scope;
	use WordTemplate\WordTemplate;

	use \Exception;
	use \ZipArchive;
	
	class AjaxController extends Controller {

/*

	Fonctions Etude
	Toutes les fonctions de sauvegarde liées à une étude

*/
		public function SaveEtude() {
			//Handle POST Data
			//Important to rewrite for security
			$params = $this->httpRequest->post(array(
				"id", "nom", "pseudo", "but", "competences", "bdd", "specifications", "p_jeh", "context", "domaines", "provenance",
				"lieu", "statut", "fee", "break_jeh", "break_fee", "locked", "child", "pub", "pub_titre", "client", "facturation",
				"signataire","entreprise","numero", "admins", "per_rem", "but_short",
			));

			$etude = new Etude($params);
			$etude->generateNum();

			$res = $this->pdo->save($etude);
			if(!$res) {return $this->error("Une erreur est arrivée lors de la sauvegarde de l'étude ! Contactez le DSI !");}
			$res = $this->pdo->saveAtt("admins",$etude);
			if(!$res) {return $this->error("Une erreur est arrivée lors de la sauvegarde des administrateurs de l'étude ! Contactez le DSI !");}

			//Info modification
			$info = new Info(array("etude" => $etude, "type" => 0));
			$r = new Request("DELETE FROM #^ WHERE #etude~ AND #type~", $info); //Remove old info of saving -> prevent spam
			$r->execute();
			$this->pdo->save($info);

			return $this->success(array("etude" => $etude));
		}

		
		public function SaveEtapes() {
			//Récupération de l'étude
			$etude_id = $this->httpRequest->post("etude_id");
			if (empty($etude_id)) {$this->error("Veuillez enregistrer votre étude avant d'ajouter des étapes !"); return;}

			$pdo = new EntityPDO();
			$etude = $pdo->get("Admin\Entity\Etude",$etude_id);
			if (empty($etude)) {$this->error("Cette étude n'existe plus !"); return;}

			$etapes = $this->httpRequest->post("etapes");
			$etude->set("etapes", $etapes);
			$pdo->saveAtt("etapes", $etude, array("save" => true));


			//Gestion sEtapes
			foreach ($etude->get("etapes") as $etape) {
				$etape->optimizeSEtapes();
				$pdo->saveAtt("sEtapes", $etape, array("save" => true));
			}

			//Info modification
			$info = new Info(array("etude" => $etude, "type" => 3));
			$r = new Request("DELETE FROM #^ WHERE #etude~ AND #type~", $info); //Remove old info of saving -> prevent spam
			$r->execute();
			$this->pdo->save($info);

			return $this->success(array("etapes" => $etude->get("etapes")));
		}


		public function CopyEtude() {

			$id = $this->httpRequest->post("id");
			$pdo = new EntityPDO();
			$e = $pdo->get("Admin\Entity\Etude", $id);
			$copy = $e->infant();

			$pdo = new EntityPDO();
			$pdo->save($copy);
			$pdo->saveAtt("admins", $copy);
			$pdo->saveAtt("docs", $copy, array("save" => true));
			$pdo->saveAtt("etapes", $copy, array("save" => true));
			$pdo->saveAtt("work_requests", $copy);

			$pdo->saveAtt("work_requests", $e, array("save" => true));

			$etapes = $copy->get("etapes");
			foreach ($etapes as $i => $etape) {
				$pdo->saveAtt("sEtapes", $etape, array("save" => true));
			}
			$pdo->save($e);

			//Info modification
			$this->pdo->save(new Info(array("type" => 4, "etude" => $copy)));

			return $this->success(array("link" => $this->routeur->getUrlFor("AdminEdit",array("id"=>$copy->getId()))));
		}
		

		public function SaveClient() {
			//Handle POST Data
			//Important to rewrite for security
			$params = $this->httpRequest->post(array(
				"id", "nom", "prenom", "mail", "titre", "adresse", "code_postal", "fixe", "mobile", "ville", "last_contact", "entreprise",
			));
			$client = new Client($params);

			$res = $this->pdo->save($client);
			return ($res) ? $this->success(array("client" => $client)) : $this->error();
		}

		public function SaveEntreprise() {
			//Handle POST Data
			//Important to rewrite for security
			$params = $this->httpRequest->post(array(
				"id", "nom", "type", "secteur", "presentation",
			));
			$entreprise = new Entreprise($params);

			$res = $this->pdo->save($entreprise);			
			return ($res) ? $this->success(array("entreprise" => $entreprise)) : $this->error();
		}



/*

	Fonctions User

*/

		public function SaveUser() {
			//Handle POST Data
			//Important to rewrite for security
			$params = $this->httpRequest->post(array(
				"id", "nom", "prenom", "annee", "mobile", "fixe", "adresse", "code_postal", "mobile", "ville", "date_birth", "nationality", "secu",
			));

			$user = $this->user;
			$user->set_Array($params);
			if(!$user->isValid()) {return $this->error("Le formulaire n'est pas valide !");}
			
			$res = $this->pdo->save($user);
			return ($res) ? $this->success(array("user" => $user)) : $this->error();
		}




/*

	Fonctions Work_Request

*/

		public function WorkRequest() {
			$id = $this->httpRequest->post("id");
			$pdo = new EntityPDO();
			$e = $pdo->get("Admin\Entity\Etude", $id);
			if ($e == null || !$e->isRequestable()) {return $this->error("Veuillez nous excuser mais le recrutement pour cette étude est terminée !");}

			//Cas de l'envoi du CV
			if (isset($_FILES["cv"])) {
				$cv = new Document($_FILES["cv"]);
				if (!$cv->isPDF()) {return $this->error("Votre CV doit être au format PDF !");}
				$r = $cv->save();
				if (!$r) {return $this->error("Nous n'avons pas réussi à enregistrer votre CV ! Réessayer plus tard.");}
				
				//Ajout du CV et suppresion de l'ancien
				$u = $this->firewall->getUser();
				$last_cv = $u->get("cv");
				$u->set("cv", $cv);
				$r = $pdo->save($u);
				if (!$r) {$cv->remove(); return $this->error("Nous n'avons pas réussi à enregistrer votre CV ! Réessayer plus tard.");}
				
				if ($last_cv != null) {$last_cv->remove();} // Si tout c'est bien passé on supprime l'ancienne lettre

				return $this->success(array("cv" => $cv));
			}

			//Cas de l'envoi de la lettre de motivation
			if (isset($_FILES["lettre"])) {
				$lettre = new Document($_FILES["lettre"]);
				if (!$lettre->isPDF()) {return $this->error("Votre lettre de motivation doit être au format PDF !");}

				$u = $this->firewall->getUser();

				//Recuperation ancienne requete
				$w = $pdo->get("Admin\Entity\WorkRequest", array("#s.etude = :0.id && #s.etudiant = :1.id", array($e, $u)));

				// Pas d'ancienne requête
				if ($w == null) { 
					$p["lettre"] = $lettre;
					$p["etude"] = $e;
					$p["etudiant"] = $u;
					$w = new WorkRequest($p);

					$r = $lettre->save();
					if (!$r) {return $this->error("Nous n'avons pas réussi à enregistrer votre lettre de motivation ! Réessayer plus tard.");}

					$r = $pdo->save($w);
					if (!$r) {return $lettre->remove(); $this->error("Nous n'avons pas réussi à enregistrer votre lettre de motivation ! Réessayer plus tard.");}

				} 

				//Ancienne candidature existante
				else {
					if (!$w->isWaiting()) {return $this->error("Votre candidature a déjà été traitée. Veuillez rafraîchir votre navigateur.");}
					
					$r = $lettre->save();
					if (!$r) {return $this->error("Nous n'avons pas réussi à enregistrer votre lettre de motivation ! Réessayer plus tard.");}


					$past = $w->get("lettre");
					
					$w->set("lettre", $lettre);
					$r = $pdo->save($w);
					if (!$r) {return $this->error("Nous n'avons pas réussi à enregistrer votre lettre de motivation ! Réessayer plus tard.");}

					if ($past != null) { $past->remove(); } // Si tout c'est bien passé on supprime l'ancienne lettre
				}

				return $this->success(array("w" => $w));
			}

			return $this->error("Aucun document n'a été reçu !");
		}

		public function HandleWorkRequest() {
			$id_etude = $this->httpRequest->post("etude");
			$id_w =  $this->httpRequest->post("w");
			$statut =  $this->httpRequest->post("statut");

			$pdo = new EntityPDO();
			$e = $pdo->get("Admin\Entity\Etude", $id_etude);

			if ($e == null) {return $this->error("Cette etude n'existe plus !");}
			if (!$e->isSelectable()) {return $this->error("Vous ne pouvez accepter un candidat tant que l'étude est en recrutement ! Veuillez changer le statut de l'étude !");}

			$w = $pdo->get("Admin\Entity\WorkRequest", $id_w);
			if ($w == null) {return $this->error("Cette candidature n'existe plus !");}

			$past_accepted = $w->isAccepted(); //Utilisé pour fired

			$w->set("statut", $statut);
			$pdo->save($w);

			$fired = ($past_accepted && !$w->isAccepted());
			
			$res = array("w" => $w, "eds" => $e->get("etudiants"));
			//Supprime l'étudiant des sousEtapes
			if ($fired) {
				foreach ($e->get("etapes") as $etape) {
					foreach ($etape->get("sEtapes") as $s_e) {
						if ($s_e->get_Ids("etudiant") == $w->get_Ids("etudiant")) {
							$s_e->set("etudiant", null);
							$pdo->save($s_e);
						}
					}
				}
			}
			$res["etapes"] = $e->get("etapes");

			return $this->success($res);
		}

/*
	
	Fonctions de Download : Document et Template

*/

		public function DownloadWorkRequest() {
			$id_work_request = $this->httpRequest->get("id");

			$w = $this->pdo->get("Admin\Entity\WorkRequest", $id_work_request);
			if ($w === null) {return 404;}
			$ed = $w->get("etudiant");
			$etude = $w->get("etude");

			$name = $etude->get("numero") . "_" .$ed->get("nom")."_".$ed->get("prenom");
			
			$files = array();
			$cv = $ed->get("cv");
			if ($cv !== null) {$files["cv.pdf"] = $cv->get("path");}
			$lettre = $w->get("lettre");
			if ($lettre !== null) {$files["lettre.pdf"] = $lettre->get("path");}

			return $this->zip($name, $files);
		}

		public function TemplateGet() {
			$n = $this->httpRequest->get("id");
			$path = "plugins/WordTemplate/Generation/Gen_".$n.".docx";
	        if (!file_exists($path)) {return 404;}

			return $this->httpResponse->setFile("Tomate_Gen", "docx", $path, true);
		}

		public function DownloadDocEtude() {
			$id = $this->httpRequest->get("id");
			$pdo = new EntityPDO();
			$de = $pdo->get("Admin\Entity\DocEtude", $id);
			if ($de === null) {return 404;}

			return $this->httpResponse->setFile("#" . $de->get("etude")->get("numero") . "_" . $de->get("nom"), "pdf", $de->get("doc")->get("path"));	
		}

		public function DownloadDoc() {
			$id = $this->httpRequest->get("id");
			$pdo = new EntityPDO();
			$doc = $pdo->get("Admin\Entity\Document", $id);
			if ($doc === null) {return 404;}

			return $this->httpResponse->setFile($doc->get("nom"), $doc->get("ext"), $doc->get("path"));
		}

		private function zip($name, $files) {
			if (!is_array($files)) {$files = array($files);}

			$zip = new ZipArchive();
			$filename = $name . ".zip";
			if (file_exists($filename)) {unlink($filename);}
			if ($zip->open($filename, ZipArchive::CREATE)!==TRUE) {
			    return $this->error("Impossible de crée le zip !");
			}			

			//Ajout des documents
			foreach ($files as $key => $path) {
				$zip->addFile($path, $key);
			}
			$zip->close();
			return $this->httpResponse->setFile($name, "zip", $filename, true);
		}



/*

	Fonctions générations

*/

		public function GenDocTemplate() {
			$etude_id = $this->httpRequest->post("etude");
			$template_id = $this->httpRequest->post("template");
			$pdo = new EntityPDO();
			$dt = $pdo->get("Admin\Entity\DocTemplate", $template_id);
			return $this->DocEtudeGenerate(new DocEtude(array("etude" => $etude_id, "doc" => $dt->get_Ids('doc'), "type" => $dt->get_Ids('type'))));
		}


		public function GenCustomTemplate() {
			$etude_id = $this->httpRequest->post("etude");
			$type = $this->httpRequest->post("type");
			$doc = new Document($_FILES["doc"]);

			return $this->DocEtudeGenerate(new DocEtude(array("etude" => $etude_id, "doc" => $doc, "type" => $type)));
		}


		private function DocEtudeGenerate(DocEtude $de) {
			$pathPlugin = "plugins/WordTemplate/";

			$pdo = new EntityPDO();
			$etude = $de->get("etude");
			if ($etude == null) {return $this->error("Cette étude n'existe plus ou pas !\nAvez vous sauvegardé ?");}

			$temp = $pdo->get("Admin\Entity\VarQuali", null, false); $vars_quali = array();
			foreach ($temp as $v) {
				$vars_quali[$v->get("var_name")] = $v->get("content");
			}
			$scope = new Scope(array("etude" => $etude, "doc" => $de, "quali" => $vars_quali));
			$word = new WordTemplate($de->get("doc")->get("path"), $scope);

			//Lien du fichier
			$history = $pathPlugin.'history.txt';
			$n = file_exists($history) ? file_get_contents($history) : 0;
			$n = ((int) $n + 1) % 20;
			file_put_contents($history, $n);
			$link = $pathPlugin."Generation/Gen_".$n.".docx";

			try {
				$word->compile($link);
			} catch (Exception $e) {
				return $this->error($e->getMessage());
			}

			//FINI
			return $this->success(array("link" => $this->routeur->getUrlFor("AjaxTemplateGet",array("id"=>$n))));
		}



/*

	Fonction Quali

*/
		public function SaveDocType() {
			//Handle POST Data
			//Important to rewrite for security
			$params = $this->httpRequest->post(array(
				"id", "nom", "var_name",
			));
			$DocType = new DocType($params);

			$pdo = new EntityPDO();
			$res = $pdo->save($DocType);
			return ($res) ? $this->success(array("doctype" => $DocType)) : $this->error();
		}


		public function SaveTemplate() {
			$id = $this->httpRequest->post("id");
			$file_uploaded = isset($_FILES["template"]);

			$params = array(
				"nom" => $this->httpRequest->post("nom"),
				"type" => $this->httpRequest->post("type"),
			);

			if ($file_uploaded) { $params["doc"] = $_FILES["template"]; }

			$pdo = new EntityPDO();
			if ($id > 0) {
				$template = $pdo->get("Admin\Entity\DocTemplate", $id);
				$pastDoc = $template->get("doc");
				$template->set_Array($params);
			} else {
				$template = new DocTemplate($params);
				$pastDoc = null;
			}

			if ($file_uploaded) {
				$doc = $template->get("doc");
				$doc->move();
				$pdo->save($doc);
			}

			$pdo->save($template);

			if ($pastDoc !== null && $file_uploaded) {
				$pastDoc->remove();
			}

			return $this->success(array("template" => $template));
		}

/*

	A refaire car ne traite aucune erreur !!

*/
		public function AddDocEtude() {
			$etude_id = $this->httpRequest->post("etude");
			$params = array(
				"type" =>  $this->httpRequest->post("type"),
				"etude" =>  $etude_id,
				"doc" => $_FILES["doc"],
			);

			$docE = new DocEtude($params);
			$doc = $docE->get("doc");
			$doc->move();
			$this->pdo->save($doc);
			$this->pdo->save($docE);

			//Info
			$this->pdo->save(new Info(array("type" => 6, "doc" => $docE, "etude" => $etude_id)));

			return $this->success(array("doc" => $docE));
		}


		public function SaveVar() {
			$params = array(
				"id" =>  $this->httpRequest->post("id"),
				"nom" =>  $this->httpRequest->post("nom"),
				"var_name" =>  $this->httpRequest->post("var_name"),
				"content" =>  $this->httpRequest->post("content"),
			);

			$var = new VarQuali($params);
			$pdo = new EntityPDO();
			$res = $pdo->save($var);
			
			return ($res) ? $this->success(array("var" => $var)) : $this->error();
		}
	}
?>