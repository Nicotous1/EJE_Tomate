<?php
	namespace Auth;
	
	use Core\Controller;
	use Core\Page;
	use Core\PDO\EntityPDO;
	use Core\PDO\Request;
	use Auth\Entity\User;
	use Core\SessionController;
	use Auth\Entity\Token;
	use Core\CodePage;
	use Core\Mail;

	use \Exception;
	require_once "plugins/Random/random.php";

	
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
		    	 ->addVar("HeaderTitre", "Se connecter");
		    return $page;
		}

		public function Register() {
			//Handle POST Data
			//Important to rewrite for security
			$params = $this->httpRequest->post(array(
				"mail", "password", "titre", "nom", "prenom", "annee", "nationality", "has_secu"
			));

			$user = new User($params);
			if(!$user->isValid()) {return $this->error("Le formulaire n'est pas valide !");}
			
			$pdo = new EntityPDO();
			$exist = $pdo->exist("Auth\Entity\User", array("#mail~", $user));
			if ($exist) {return $this->error("Cet email est déjà utilisé !");}

			$user->set("level", 1);
			$res = $pdo->save($user);
			if (!$res) {return $this->error();}

			AuthHandler::getInstance()->setUser($user, false); // No remember

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
			$userBDD = $pdo->get("Auth\Entity\User", array("#mail~", $userPOST), 1);
			if ($userBDD == null ) {return $this->error("Ce compte n'existe pas encore.\nVeuillez vous inscrire.");}

			$userBDD->setPassword($userPOST->get("password"), False); // False disable the update of the hash
			if (!$userBDD->check()) {return $this->error("Le mot de passe est incorrect !");}

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

/*

	Forgot password Interface

*/
		public function ForgotInit() {
			//Handle POST Data
			//Important to rewrite for security
			$params = array(
				"mail" => $this->httpRequest->post("mail"),
			);

			$userPOST = new User($params);
			$userBDD = $this->pdo->get("Auth\Entity\User", array("#mail~", $userPOST), 1);
			if ($userBDD === null) {
				return $this->error("Ce compte n'existe pas encore.\nVeuillez vous inscrire.");
			}

			// Save new Token
			$token = new Token(array("user" => $userBDD, "type" => 1));
			$token->create_selector(); // Create an unique selector
			$res = $this->pdo->save($token);
			if (!$res) {return $this->error();}

			// Disable old token
			$r = new Request("UPDATE #^ SET #activated = FALSE WHERE #user~ AND #type~ AND #selector != :selector"  ,$token);
			$res = $r->execute();
			if (!$res) {return $this->error();}

			// Send mail
			$url = $this->routeur->getUrlFor("AuthForgotSet", array("raw_token" => strval($token)));
			$mail = new Mail(
				$userBDD->get("mail"),
				"Réinitialisation de votre mot de passe",
<<<EOT
	Pour réinitialiser votre mot de passe : <a href="$url">Cliquez ici</a><br><br>
	Si le lien ne fonctionne pas rendez vous ici : "$url"
EOT
			);
			$mail->send();

			return $this->success();
		}

		public function ForgotSet() {
			// Check authenticity of token
			$raw = $this->httpRequest->get("raw_token");
			$selector = substr($raw, 0, Token::SIZE_SELECTOR);
			$token = $this->pdo->get("Auth\Entity\Token", array("#s.selector = :", $selector));
			if ($token === null) {return 404;}

			$password = $this->httpRequest->post("password");
			if (!$token->get("activated")) {
				$msg = "Ce lien n'est plus actif. Le token a expiré !";
				return ($password == null) ? new CodePage(404, $msg) : $this->error($msg);
			}

			$validator = substr($raw, Token::SIZE_SELECTOR, Token::SIZE_VALIDATOR);
			$token->set("validator", $validator);
			if (!$token->check()) {return 404;}


			// Handles POST
			if ($password != null) {
				// Update password
				$user = $token->get("user");
				$user->set("password", $password);
				$res = $this->pdo->save($user);
				if (!$res) {return $this->error();}

				// Disabled token
				$token->set("activated", False);
				$res = $this->pdo->save($token);
				if (!$res) {return $this->error();}

				// Signin user
				AuthHandler::getInstance()->setUser($user, false);


				return $this->success();
			} else {
				//AFFICHAGE
				$page = new Page();
			    $page->addFile(dirname(__FILE__) . "/templates/Template_Forget.php")
			    	 ->addVar("HeaderTitre", "Réinitialiser votre mot de passe");
			    return $page;				
			}	
		}
	}
?>