<?php
	namespace Admin;
	use Core\Controller;
	use Core\PDO\EntityPDO;
	use Core\Page;
	use \Exception;

	use Admn\Entity\Etude;
	use Auth\Entity\UserPDO;

	class AdminController extends Controller {

		public function Edit() {
			$id = $this->httpRequest->get("id");
			if ($id != null) {
				$pdo = new EntityPDO();
				$etude = $pdo->get("Etude", $id);
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
			$es = $pdo->get("Entreprise", null, false);
			$cs = $pdo->get("Client", null, false);
			$ds = $pdo->get("DocType", null, false);
			$ts = $pdo->get("DocTemplate", null, false);
			//$this->SC->getPDO()->stats();

			
			//AFFICHAGE
			$page = new Page();
		    $page->addFile("Admin/templates/Template_Etude_Edit.php")
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
			$es = $pdo->get("Etude", "#s.child IS NULL", false);

			//AFFICHAGE
			$page = new Page();
		    $page->addFile("Admin/templates/Template_Dashboard.php")
		    	 ->addVar("HeaderTitre", "Dernières études")
		    	 ->addVar("etudes", $es)
		  	;
		    return $page;
		}

		public function Quali() {
			$pdo = new EntityPDO();
			$ds = $pdo->get("DocType", null, false);
			$ts = $pdo->get("DocTemplate", null, false);
			$vs = $pdo->get("VarQuali", null, false);

			//AFFICHAGE
			$page = new Page();
		    $page->addFile("Admin/templates/Template_Doc_List.php")
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