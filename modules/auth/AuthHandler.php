<?php
	namespace Auth;
	use Core\ConfigHandler;
	use Core\CookieController;
	use Core\SessionController;
	use Core\PDO\EntityPDO;
	use Core\AuthHandler as ApiAuth;
	use \Exception;

	use Auth\Entity\TokenController;
	use Auth\Entity\Token;
	use Auth\Entity\User;

	class AuthHandler extends ApiAuth {

		protected $user;
		protected $cookies;

		protected function __construct() {
			$this->cookies = new CookieController();
		}

		/*
			Défini l'utilisateur courant dans le firewall
				-> Enregistrement dans une session
				-> Si remenber : Persistence avec token dans un cookie
		*/
		public function setUser(User $user, $remenber = false) {
			if ($user->isVisiteur()) {throw new Exception("The AuthHandler can't persist a user without an id !");}

			$this->killOldUserWithNoTrace(); //Some Cleaning before hard work !

			$sessions = new SessionController();
			$sessions->set("current_user-id",$user->getId());

			if ($remenber) {
				$tokenController = new TokenController();
				$token = $tokenController->generate($user); //Genere et sauvegarde en BDD ou autre lui seul sait !
				if ($token != null) {
					$this->cookies->set("auth_token", $token->serialize(), Token::TOKEN_LIFE);
				}
			}


			$this->user = $user; //Sauvegarde dans le firewall
			return $this;
		}

		/*
			Défini l'utilisateur dans le firewall en utilisant :
				-> la session en cours si l'utilisateur a été authentifié
				-> un token en cookie si l'utilisateur a utilisé remenber
				Sinon renvoie visiteur (Aucune information)
		*/
		private function setUserFromData() {
			//Session
			$sessions = new SessionController();
			$userId = $sessions->get('current_user-id');
			if ($userId != null) {
				$pdo = new EntityPDO();
				$user = $pdo->get("Auth\Entity\User", $userId);
				if ($user != null) {
					$this->user = $user;
					return $this;
				}
			}


			// No token
			$this->user = new User();
			return $this;

			// not user -> because no token
			//Recuperation par Token (remenber)
			$tokenController = new TokenController();
			$token = $tokenController->getOfRaw($this->cookies->get("auth_token"));
			if ($token != null) {
				if ($token->check()) {
					$userId = $token->getUserId();
					$pdo = new EntityPDO();
					$user = $pdo->get("Auth\Entity\User", $userId);
					if ($user != null) {
						$sessions->set('current_user-id', $user->getId()); //RESTORE SESSION
						$tokenController->update($token);
						$this->cookies->set("auth_token", $token->serialize(), Token::TOKEN_LIFE);

						$this->user = $user;
						return $this;
					}
				} else {
					$tokenController->remove($token);
				}
			}


			//Visiteur
			$this->killOldUserWithNoTrace();
			return $this;
		}

		public function getUser() {
			if($this->user == null) {$this->setUserFromData();}
			return $this->user;
		}

		public function signIn(User $user, $remenber = false) {
			$this->setUser($user, $remenber);
			return $this;
		}

		public function signOut() {
			return $this->killOldUserWithNoTrace();
		}		

		private function removeCookie() {
			$tokenController = new TokenController();
			$tokenController->removeOfRaw($this->cookies->get("auth_token"));
			$this->cookies->remove('auth_token');
			return $this;
		}

		private function removeSession() {
			$sessions = new SessionController();
			$sessions->remove('current_user-id');
			return $this;
		}

		private function killOldUserWithNoTrace() {
			$this->removeCookie()->removeSession(); //Removing Trace
			$this->user = new User();
			return $this;
		}	
		
	}
?>