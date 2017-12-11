<?php
	namespace Etudiant;
	use Core\Controller;
	use Core\PDO\EntityPDO;
	use Core\Page;
	use \Exception;

	use Admin\Entity\WorkRequest;

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
			$etudePostulable = $pdo->get("Admin\Entity\Etude", array(
				"#s.child IS NULL AND (#s.statut = 2 OR EXISTS (SELECT 1 FROM #0.^ w WHERE w.#0.etudiant = :1.id AND w.#0.etude = etude.id))",
				array(WorkRequest::getEntitySQL(), $this->user) ), false);

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