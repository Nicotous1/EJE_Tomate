<?php
	namespace Auth;
	
	use Core\Controller;
	use Core\Page;
	use Core\PDO\EntityPDO;
	use Core\PDO\Request;
	use Auth\Entity\User;
	use Core\SessionController;

	use \Exception;

	
	class AuthController extends Controller {
		/*
			Handle Register and SignIn
		*/
		public function Home() {
			//SI DEJA CONNECTER REDIRECTION
			if($this->firewall->isConnected()) {
				$url = $this->getSuccessUrl();
				$this->httpResponse->redirect($url);
				return;
			}

			//AFFICHAGE
			$page = new Page();
		    $page->addFile(dirname(__FILE__) . "/templates/Template_SignIn.php")
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
			$exist = $pdo->exist("Auth\Entity\User", array("#s.mail = :", $user->get("mail")));
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
			$userBDD = $pdo->get("Auth\Entity\User", array("#mail~", $userPOST), true);
			if ($userBDD == null ) {return $this->error("Les identifiants sont incorrects !");}

			$userBDD->set("password", $userPOST->get("password"));
			if (!$userBDD->check()) {return $this->error("Les identifiants sont incorrects !");}

			AuthHandler::getInstance()->setUser($userBDD, false); // No remember

			return $this->success();

		}

		public function SignOut() {
			AuthHandler::getInstance()->signOut();
			$this->httpResponse->redirect($this->firewall->getUrlFor("login_road"));
		}

		protected function getSuccessUrl() {
			$sessions = new SessionController();
			$url = ($sessions->isset("firewall_last_refused_url")) ? $sessions->get("firewall_last_refused_url") : $this->firewall->getUrlFor("success_road");
			$sessions->remove("firewall_last_refused_url");
			return $url;
		}

		protected function success($url = null) {
			$url = ($url == null) ? $this->getSuccessUrl() : $url;
			return array("url" => $url, "res" => true);
		}

		protected function error($msg = "Une erreur s'est produite !") {
			return array("res" => false, "msg" => $msg);
		}

	}
?>