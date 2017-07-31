<?php
	class Application {	
		protected $SC;
		protected $httpRequest;
		protected $httpResponse;
		protected $route;
		protected $firewall;

		public function __construct() {
			$this->SC = new ServiceController();
			$this->SC->setApp($this);
			
			$this->httpRequest = new httpRequest();
			$this->httpResponse = new httpResponse();
			$this->firewall = $this->SC->getFirewall();
			$this->route = null;
		}

		public function RunAndDie() {
			ob_start();
			$this->runRouteur()
			     ->runFirewall()
			     ->runMethod()
			     ->end();
		}

		/*
			TROUVE LA ROUTE DEMANDER SINON RENVOIE 404
		*/
		private function runRouteur() {
			$this->route = $this->SC->getRouteur()->getRoute();
			if($this->route == null) {$this->httpResponse->setCode(404); $this->end();}
			return $this;
		}

		/*
			VERIFIE AUTORISATION 
		*/
		private function runFirewall() {
			if(!$this->firewall->isAllowed($this->route)) { //ACCES REFUSER
				$url = $this->firewall->getRedirection(); //CHERCHE UNE REDIRECTION
				
				if($url == null) { //SI AUCUNE REDIRECTION POSSIBLE
					$this->httpResponse->setCode(403); //ERROR 403
				} else {
					$this->httpResponse->redirect($url); //REDIRECTION
				}

				$this->end();
			}
			return $this;
		}

		/*
			EXECUTE LA METHOD ASSOCIER A LA ROUTE
		*/
		private function runMethod() {
			
			$controllerName = $this->route->getController();
			$methodName = $this->route->getMethod();
			$clearedName = substr($controllerName, 0, -10);			

			include("librairy/modules/".$clearedName."/".$controllerName.".php");
			$controller = new $controllerName($this->httpRequest, $this->httpResponse, $this->firewall);

			//Gestion du retour du controlleur
			$res = $controller->$methodName();
			if(is_a($res, 'Page')) { //Une page -> standard
				$this->httpResponse->setPage($res);
			}
			if(is_array($res)) { //Un tableau -> on l'encode en json
				$this->httpResponse->setPage(new Page(json_encode($res)));
			}
			if (is_int($res)) { //Un entier -> on retourne un code d'erreur
				$this->httpResponse->setCode($res);
			}
			
			return $this;
		}

		/*
			ROUTINE DE FIN
			ENVOI DE LA REPONSE
		*/
		private function end() {
		    $this->httpResponse->send();
			$c = ob_get_contents();
			ob_end_clean();
			echo $c;
			//$this->SC->getPDO()->stats();
		    exit();
		}


		/*
			FONCTIONS D'INTERFACE
		*/

		public function getHttpResponse() {
			return $this->httpResponse;
		}
			
		public function getHttpRequest() {
			return $this->httpRequest;
		}
			
		public function getFirewall() {
			return $this->firewall;
		}
	}
?>