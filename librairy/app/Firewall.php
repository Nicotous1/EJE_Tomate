<?php
;

	class Firewall {

		private $routeur;
		private $currentUser;
		private $params;

		public function __construct() {
			$sc = new ServiceController();
			$this->currentUser = null;
			$this->cookies = $sc->getCookies();
			$this->routeur = $sc->getRouteur();
			$this->params = $sc->getParam('firewall');
		}

		/*
			Défini l'utilisateur courant dans le firewall
				-> Enregistrement dans une session
				-> Si remenber : Persistence avec token dans un cookie
		*/
		public function setUser(User $user, $remenber = false) {
			if ($user->isVisiteur()) {throw new Exception("Firewall can't accept user not authentificated. User must have an id !");}

			$this->killOldUserWithNoTrace(); //Some Cleaning before hard work !

			$sessions = new SessionController();
			$sessions->set("current_user-id",$user->getId());

			if ($remenber) {
				$tokenController = new TokenController;
				$token = $tokenController->generate($user); //Genere et sauvegarde en BDD ou autre lui seul sait !
				if ($token != null) {
					$this->cookies->set("auth_token", $token->serialize(), Token::TOKEN_LIFE);
				}
			}


			$this->currentUser = $user; //Sauvegarde dans le firewall
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
				$user = $pdo->get("User", $userId);
				if ($user != null) {
					$this->currentUser = $user;
					return $this;
				}
			}

			//Recuperation par Token (remenber)
			$tokenController = new TokenController;
			$token = $tokenController->getOfRaw($this->cookies->get("auth_token"));
			if ($token != null) {
				if ($token->check()) {
					$userId = $token->getUserId();
					$pdo = new EntityPDO();
					$user = $pdo->get("User", $userId);
					if ($user != null) {
						$sessions->set('current_user-id', $user->getId()); //RESTORE SESSION
						$tokenController->update($token);
						$this->cookies->set("auth_token", $token->serialize(), Token::TOKEN_LIFE);

						$this->currentUser = $user;
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
			if($this->currentUser == null) {$this->setUserFromData();}
			return $this->currentUser;
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
			$this->currentUser = new User;
			return $this;
		}



		public function isConnected() {
			return ($this->getUser()->getId() > 0) ? true : false; 
		}

		public function isAllowed(Route $route = null) {
			if($route == null) {
				$route = $this->routeur->getRoute();
			}

			$levelRequire = $route->getLevel();
			if($levelRequire <= 0) {return true;}			                         

			$levelUser = $this->getUser()
			                  ->get("level");

			return ($levelUser >= $levelRequire) ? true : false;			                         	
		}

		public function getRedirection($url = null) {
			return (!$this->isAllowed() && !$this->isConnected()) ? $this->getUrlFor("login_road") : $url;
		}

		public function getUrlFor($name) {
			if(!isset($this->params[$name])) {throw new Exception("No road with the name '". $name ."' has been defined in the firewall config file !"); return null;}
			return $this->routeur->getUrlFor($this->params[$name]);
		}
	}
?>