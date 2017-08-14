<?php
	namespace Admin;
	use Core\Controller;
	use Core\PDO\EntityPDO;
	use Core\PDO\Request;
	use Core\PDO\PDO;
	use Core\Page;
	use \Exception;

	use Admin\Entity\Etude;
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
			//$this->SC->getPDO()->stats();

			
			//AFFICHAGE
			$page = new Page();
		    $page->addFile(dirname(__FILE__) . "/templates/Template_Etude_Edit.php")
		    	 ->addVar("HeaderTitre", "Edition")
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
			$pdo = new EntityPDO();
			$es = $pdo->get("Admin\Entity\Etude", "#s.child IS NULL", false);

			//AFFICHAGE
			$page = new Page();
		    $page->addFile(dirname(__FILE__) . "/templates/Template_Dashboard.php")
		    	 ->addVar("HeaderTitre", "Dernières études")
		    	 ->addVar("etudes", $es)
		  	;
		    return $page;
		}

		public function Quali() {
			$pdo = new EntityPDO();
			$ds = $pdo->get("Admin\Entity\DocType", null, false);
			$ts = $pdo->get("Admin\Entity\DocTemplate", null, false);
			$vs = $pdo->get("Admin\Entity\VarQuali", null, false);

			//AFFICHAGE
			$page = new Page();
		    $page->addFile(dirname(__FILE__) . "/templates/Template_Doc_List.php")
		    	 ->addVar("HeaderTitre", "Pôle Qualité")
		    	 ->addVar("doc_types", $ds)
		    	 ->addVar("templates", $ts)
		    	 ->addVar("vars_quali", $vs)
		  	;
		    return $page;
		}

		public function test() {
			$e = $this->pdo->get("Etude", 76);
			var_dump($e->get("admins"));
		}
	}
?>