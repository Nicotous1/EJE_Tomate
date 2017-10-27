<?php
	namespace Etudiant;
	use Core\Controller;
	use Core\PDO\EntityPDO;
	use Core\Page;
	use \Exception;

	class EtudiantController extends Controller {

		public function Home() {
			$level = $this->user->get("level");
			if ($level < 2) {
				return $this->Candidater();
			} else {
				$this->httpResponse->redirect($this->routeur->getUrlFor("AdminHome"));
				return True;
			}
		}


		public function Candidater() {			
			$pdo = new EntityPDO();
			$etudePostulable = $pdo->get("Admin\Entity\Etude", array("#s.statut IN : AND #s.child IS NULL", array(2,3)), false);

			//AFFICHAGE
			$page = new Page();
		    $page->addFile(dirname(__FILE__) . "/templates/Template_Home.php")
		    	 ->addVar("HeaderTitre", "Candidater")
		    	 ->addVar("etudes", $etudePostulable) //REND TOUT PUBLIC A MODIFIER PLUS TARD
		   	;
		    return $page;
		}

		public function Edit() {
			//AFFICHAGE
			$page = new Page();
		    $page->addFile(dirname(__FILE__) . "/templates/Template_User_Edit.php")
		    	 ->addVar("HeaderTitre", "Votre profil")
		   	;
		    return $page;

		}
	}
?>