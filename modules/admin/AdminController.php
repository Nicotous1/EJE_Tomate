<?php
	namespace Admin;
	use Core\Controller;
	use Core\PDO\EntityPDO;
	use Core\PDO\Request;
	use Core\PDO\PDO;
	use Core\Page;
	use \Exception;

	use Admin\Entity\Etude;
	use Admin\Entity\Document;
	use Auth\Entity\UserPDO;

	class AdminController extends Controller {

		public function Edit() {
			$id = $this->httpRequest->get("id");
			if ($id != null) {
				$pdo = new EntityPDO();
				$etude = $pdo->get("Admin\Entity\Etude", $id);
				if ($etude == null) {return 404;}
			} else {
				$etude = new Etude();
			}

			$userPDO = new UserPDO();
			$admins = $userPDO->getAdmins();
			$adminsSecured = array();
			foreach ($admins as $admin) {
				$adminsSecured[] = array(
					"id" => $admin->get("id"),
					"nom" => $admin->get("nom"),
					"prenom" => $admin->get("prenom"),
				);
			}

			$pdo = new EntityPDO();
			$es = $pdo->get("Admin\Entity\Entreprise", null, false);
			$cs = $pdo->get("Admin\Entity\Client", null, false);
			$ds = $pdo->get("Admin\Entity\DocType", null, false);
			$ts = $pdo->get("Admin\Entity\DocTemplate", null, false);

			
			//AFFICHAGE
			$title = ($etude->inBDD()) ? "#" . $etude->get("numero") : "Nouvelle étude";
			$page = new Page();
		    $page->addFile(dirname(__FILE__) . "/templates/Template_Etude_Edit.php")
		    	 ->addVar("HeaderTitre", $title)
		    	 ->addVar("etude", $etude)
		    	 ->addVar("admins", $adminsSecured)
		    	 ->addVar("entreprises", $es)
		    	 ->addVar("doc_types", $ds)
		    	 ->addVar("templates", $ts)
		    	 ->addVar("clients", $cs)
		   	;
		    return $page;
		}

		public function home() {
			return $this->LastInfos();
		}

		public function LastInfos() {
			$date_lim = date("Y-m-d", strtotime("-2 weeks"));

			$r = new Request("
					SELECT e.id id, e.numero numero, MAX(c.date) d
					FROM etude e
					LEFT JOIN com c ON c.etude = e.id
					WHERE
						e.child IS NULL
					    AND e.statut < 5
					GROUP BY e.id 
					HAVING d < : 
					ORDER BY e.numero DESC
				",
				$date_lim
			);
			$res = $r->fetchAll();

			$str = null;
			foreach ($res as $i => $r) {
				if ($i > 4) {
					$str = substr($str, 0, -2) . "..  ";
					break;
				}
				$str .= "<a href='". $this->routeur->getUrlFor("AdminEdit", array("id" => $r["id"])) ."'>". $r["numero"] . "</a>, ";
			}
			$n = count($res);
			if ($n > 0) {$str = substr($str, 0, -2);}
			if ($n == 1) {
				$str .= " n'a pas été commentée depuis 2 semaines.";
			} elseif ($n > 1) {
				$str .= " n'ont pas été commentées depuis 2 semaines.";
			}

			// var_dump($str);
			// return;

			//AFFICHAGE
			$page = new Page();
		    $page->addFile(dirname(__FILE__) . "/templates/Template_Infos.php")
		    	 ->addVar("HeaderTitre", "Dernières infos")
		    	 ->addVar("SuiviWarning", $str)
		  	;
		    return $page;
		}

		public function LastEtudes() {
			//AFFICHAGE
			$page = new Page();
		    $page->addFile(dirname(__FILE__) . "/templates/Template_LastEtudes.php")
		    	 ->addVar("HeaderTitre", "Dernières études")
		  	;
		    return $page;
		}

		public function Quali() {
			$pdo = new EntityPDO();
			$ds = $pdo->get("Admin\Entity\DocType", null, false);
			$ts = $pdo->get("Admin\Entity\DocTemplate", null, false);
			$vs = $pdo->get("Admin\Entity\VarQuali", null, false);
			$rs = $pdo->get("Admin\Entity\QualiRequest", null, false);
			$es = $pdo->get("Admin\Entity\Etude", array(
				"#statut = 7",
				Etude::getEntitySQL()
			), false);

			//AFFICHAGE
			$page = new Page();
		    $page->addFile(dirname(__FILE__) . "/templates/Template_Quali_Home.php")
		    	 ->addVar("HeaderTitre", "Pôle Qualité")
		    	 ->addVar("doc_types", $ds)
		    	 ->addVar("templates", $ts)
		    	 ->addVar("vars_quali", $vs)
		    	 ->addVar("etudes", $es)
		    	 ->addVar("requests", $rs)
		  	;
		    return $page;
		}

		public function Clean() {
			// This is not clean you should use alias but i'm lazy...
			$r = new Request("
				DELETE FROM document WHERE
					    NOT EXISTS (SELECT 1 FROM doctemplate dt WHERE dt.doc = document.id)
				    AND NOT EXISTS (SELECT 1 FROM etudiant e WHERE e.cv = document.id)
				    AND NOT EXISTS (SELECT 1 FROM docetude de WHERE de.doc = document.id)
				    AND NOT EXISTS (SELECT 1 FROM work_request w WHERE w.lettre = document.id)
    		");
			$r->execute();

    		$n = $r->rowCount();
    		echo "$n documents non reliés dans la DB ont été supprimés de la DB.<br>";

			$docs = $this->pdo->get("Admin\Entity\Document", null, false);

			$paths_usefull = array();
			$n_deleted = 0;
			foreach ($docs as $i => $doc) {
				if ($doc->exists()) {$paths_usefull[] = $doc->get("path");}
				else {$doc->remove(); $n_deleted++;}
			}
			echo "$n_deleted documents ont été supprimée de la base de données car ils ne sont pas sur le serveur.<br>";

			// var_dump($paths_usefull);

			$files = array();
			$n_deleted = 0;
			foreach (scandir(Document::pathStorage) as $file) {
				if (!in_array(pathinfo($file, PATHINFO_EXTENSION), array("pdf", "docx"))) {continue;}

				if (!in_array(Document::pathStorage . $file, $paths_usefull)) {
					unlink(Document::pathStorage . $file);
					$n_deleted++;
				}
			}
			echo "$n_deleted documents ont été supprimée du disque car ils ne sont pas dans la DB.<br><br>";
			echo count($paths_usefull) . " documents restants.<br>";



		}

		public function test() {
			$e = $this->pdo->get("Etude", 76);
			var_dump($e->get("admins"));
		}

	}
?>