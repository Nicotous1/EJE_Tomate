<?php
	namespace Core;
	use \Exception;

	class Routeur extends Singleton {

		private $askedUrl;
		private $root;
		private $url;
		private $route;
		private $routes;
		
		static private $params = null;

		protected function __construct() {
			self::$params = ConfigHandler::getInstance()->get(".routeur")->getData();

			$this->loadRoutes()
				 ->setUrl($_SERVER['REQUEST_URI'])
			     ->setRoute()
			     ->setGET();

			$this->applySSL();

			//DEBUG -> MAKE BUG HEADER (COOKIE, REDIRECT)
			// var_dump($this); 
			// die();
		}

		protected function applySSL() {
			if ($this->enabledSSL() && !$this->isSSL()) {
				$url_ssl = "https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
				header("Location: " . $url_ssl);
				die();
			}
		}

		/*
			FUNCTION INITIALISATION
		*/

		//RECUPERE LES ROUTES PARAMETREES
		private function loadRoutes() {
			$this->routes = array();
			
			## Get only routes config in modules (not in main) 
			foreach (ConfigHandler::getInstance()->get_matches("^(.+)\.routes") as $config) {
				foreach ($config->getData() as $route) {
					$route["module"] = $config->getModule();
					$this->routes[] = new Route($route);
				}
			}
			return $this;
		}
		//SET ET FILTRE L'URL
		private function setUrl($url) {
			$this->askedUrl = $this->generalFilter($url);
			$this->url = $this->rootFilter($this->askedUrl);
			$this->url = $this->getFilter($this->url);
			return $this;
		}

		//DETERMINE LA ROUTE DESIREE
		private function setRoute() {
			$this->route = null;
			foreach($this->routes as $route) {
				if($route->match($this->url)) { $this->route = $route; return $this;}
			}
			return $this;
		}

		//DETERMINE LES PARAMETRE DE L'URL ET AJOUTE A $_GET
		private function setGET() {
			if($this->route == null) {return $this;}
			$params = $this->route->getParam($this->url);
			$_GET = array_merge($_GET, $params);
			return $this;
		}


		/*
			FUNCTION INTERFACE
		*/

		public function getRoute() {
			return $this->route;
		}

		public function getUrlFor($name, array $params = null) {
			foreach($this->routes as $route) {
				if($route->getName() == $name) { return $this->getRoot() . $route->getUrl($params);}
			}
			throw new Exception("No road has been find with this name : '" . $name . "'"); return null;
		}

		public function getRoot() {
			if($this->root == null) {$this->setRoot();}
			return $this->root;
		}

		public function getUrl() {
			return $this->getRoot() . substr($_SERVER["REQUEST_URI"],1);
		}


		/*
			DETERMINE L'ADRESSE ABSOLU DU ROOT
		*/
		private function setRoot() {
			$url = $this->generalFilter($_SERVER['SCRIPT_NAME']);
			$lastSlashPos = strrpos($url, "/");
			$ssl = ($this->enabledSSL()) ? "https" : "http";
			$server_url =    $ssl . "://" . $_SERVER['SERVER_NAME'] . "/";
			if ($lastSlashPos === false) { // A la racine
				$root = $server_url;
			} else { // Dans un sous dossier
				$root = $server_url . substr($url, 0, $lastSlashPos + 1); // Contient le dernier slash
			}

			$this->root = $root;
			return $this;
		}


		/*
			FILTRE POUR URL DEMANDER SORT UNE URL PROPRE -> folder/folder2/.../folder/
		*/

		private function generalFilter($url) {
			$url = $this->absoluteFilter($url);
			$url = preg_replace("#^/?(.?)#i", "$1", $url); //ENLEVE FIRST SLASH
			return $url;
		}

		private function absoluteFilter($url) {
			return preg_replace("#^https?://[a-zA-Z0-9.]*/?(.?)#i", "$1", $url);
		}

		private function rootFilter($url) {
			return preg_replace('#'. $this->generalFilter($this->getRoot()) . '#i', '', $url);
		}

		private function getFilter($url) {
			if(substr_count($url, "?") <= 0) {return $url;}
			return substr($url, 0, strpos($url, "?"));
		}

		private function enabledSSL() {
			return (self::$params["ssl"]);
		}

		private function isSSL() {
			return (isset($_SERVER['HTTPS']));
		}


		/*
			DEBUG FUNCTION
		*/

		private function isAbsolute($url) {
			return (preg_match("#^https?://[a-zA-Z0-9.]*/?#", $url)) ? true : false ;
		}

	}
?>