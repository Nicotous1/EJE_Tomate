<?php
	namespace Core;
	use \Exception;

	class Firewall extends Singleton {

		private $authHandler;
		private $params;

		protected function __construct() {
			$this->params = ConfigHandler::getInstance()->get(".firewall");

			$class = isset($this->params["AuthHandler"]) ? $this->params["AuthHandler"] : "Core\AuthHandler";
			$this->authHandler = $class::getInstance();
		}

		public function run() {
			if ($this->authHandler->getUser()->getId() == 3) {
				sleep(5);
			}
			
			if(!$this->isAllowed(Routeur::getInstance()->getRoute())) { //ACCES REFUSER	
				if ($this->isConnected()) {
					httpResponse::getInstance()->setCode(403); //ERROR 403 -> Not allowed
				} else { // Non connecté
					$signIn = $this->getUrlFor("login_road");
					httpResponse::getInstance()->redirect($signIn); //REDIRECTION
				}

				$sessions = new SessionController();
				$sessions->set("firewall_last_refused_url", Routeur::getInstance()->getUrl());
				Application::getInstance()->end();
			}

			return $this;			
		}

		public function isAllowed(Route $route = null) {
			$route = Routeur::getInstance()->getRoute();

			$levelRequire = $route->getLevel();		                         

			$user = $this->authHandler->getUser();

			return $user->isAllowed($levelRequire);		                         	
		}

		public function isConnected() {
			return ($this->authHandler->getUser()->getId() > 0) ? true : false; 
		}	

		public function getUser() {
			return $this->authHandler->getUser();
		}

		public function getUrlFor($name) {
			return Routeur::getInstance()->getUrlFor($this->params[$name]);
		}		
	}
?>