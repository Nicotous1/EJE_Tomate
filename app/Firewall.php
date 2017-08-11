<?php
	namespace Core;
	use \Exception;

	class Firewall extends Singleton {

		private $authHandler;
		private $params;

		protected function __construct() {
			$this->params = ConfigHandler::getInstance()->get(".firewall");

			$class = isset($this->params["AuthHandler"]) ? $this->params["AuthHandler"] : "Core\AuthHandler";
			$this->authHandler = $class:getInstance();
		}

		public function isAllowed(Route $route = null) {
			$route = Routeur::getInstance()->getRoute();

			$levelRequire = $route->getLevel();		                         

			$user = $this->authHandler->getUser();

			return $user->isAllowed($levelRequire);		                         	
		}

		public function getRedirection($url = null) {
			return (!$this->isAllowed() && !$this->isConnected()) ? $this->getUrlFor("login_road") : $url;
		}

		public function getUrlFor($name) {
			return $this->routeur->getUrlFor($this->params[$name]);
		}
	}
?>