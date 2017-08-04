<?php
	namespace Auth;
	use Core\Controller;
	use \Exception;
	
	class AuthController extends Controller {
		/*
			Handle Register and SignIn
		*/
		public function Home() {
			//SI DEJA CONNECTER REDIRECTION
			if($this->firewall->isConnected()) {
				$this->httpResponse->redirect($this->firewall->getUrlFor("success_road"));
				return;
			}

			//AFFICHAGE
			$page = new Page();
		    $page->addFile("Auth/templates/Template_SignIn.php")
		    	 ->addVar("HeaderTitre", "Sign in");
		    return $page;
		}

		public function Register() {
			//Handle POST Data
			//Important to rewrite for security
			$params = array(
				"mail" => $this->httpRequest->post("mail"),
				"password" => $this->httpRequest->post("password"),
				"titre" => $this->httpRequest->post("titre"),
				"nom" => $this->httpRequest->post("nom"),
				"prenom" => $this->httpRequest->post("prenom"),
				"annee" => $this->httpRequest->post("annee"),
			);

			$user = new User($params);
			if(!$user->isValid()) {return $this->error("Le formulaire n'est pas valide !");}
			
			$pdo = new EntityPDO();
			$exist = $pdo->exist("User", array("#s.mail = :", $user->get("mail")));
			if ($exist) {return $this->error("Cet email est déjà utilisé !");}

			$user->set("level", 1);
			$res = $pdo->save($user);
			if (!$res) {return $this->error();}

			$this->firewall->signIn($user);

			return $this->success();
	
		}

		public function SignIn() {
			//Handle POST Data
			//Important to rewrite for security
			$params = array(
				"mail" => $this->httpRequest->post("mail"),
				"password" => $this->httpRequest->post("password"),
			);

			$userPOST = new User($params);
			
			$pdo = new EntityPDO();
			$userBDD = $pdo->get("User", array("#mail~", $userPOST), true);
			if ($userBDD == null ) {return $this->error("Les identifiants sont incorrects !");}

			$userBDD->set("password", $userPOST->get("password"));
			if (!$userBDD->check()) {return $this->error("Les identifiants sont incorrects !");}

			$this->firewall->signIn($userBDD, false);

			return $this->success();

		}

		public function SignOut() {
			$this->firewall->signOut();
			$this->httpResponse->redirect($this->firewall->getUrlFor("login_road"));
		}

		protected function success($url = null) {
			$url = ($url == null) ? $this->firewall->getUrlFor("success_road") : $url;
			return array("url" => $url, "res" => true);
		}

		protected function error($msg = "Une erreur s'est produite !") {
			return array("res" => false, "msg" => $msg);
		}

	}
?>