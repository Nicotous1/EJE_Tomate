<?php
	class EtudiantController extends Controller {

		public function Home() {
			$level = $this->user->get("level");
			if ($level < 2) {
				return $this->Candidater();
			} else {
				return $this->httpResponse->redirect($this->SC->getRouteur()->getUrlFor("AdminHome"));
			}
		}


		public function Candidater() {			
			$pdo = new EntityPDO();
			$etudePostulable = $pdo->get("Etude", array("#s.statut IN : AND #s.child IS NULL", array(2,3)), false);

			//AFFICHAGE
			$page = new Page();
		    $page->addFile("Etudiant/templates/Template_Home.php")
		    	 ->addVar("HeaderTitre", "Candiater")
		    	 ->addVar("etudes", $etudePostulable) //REND TOUT PUBLIC A MODIFIER PLUS TARD
		   	;
		    return $page;
		}

		public function Edit() {
			//AFFICHAGE
			$page = new Page();
		    $page->addFile("Etudiant/templates/Template_User_Edit.php")
		    	 ->addVar("HeaderTitre", "Votre profil")
		   	;
		    return $page;

		}
	}
?>