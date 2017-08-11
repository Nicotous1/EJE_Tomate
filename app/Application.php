<?php
	namespace Core;
	use \Exception;

	class Application extends Singleton {


		protected $SC;
		protected $httpRequest;
		protected $httpResponse;
		protected $route;
		protected $firewall;

		protected function __construct() {
			ob_start();
			$this->httpRequest = httpRequest::getInstance();
			$this->httpResponse = httpResponse::getInstance();
			$this->firewall = Firewall::getInstance();
			$this->route = null;
		}

		public function RunAndDie() {
			$this->runRouteur()
			     ->runFirewall()
			     ->runMethod()
			     ->end();
		}

		/*
			TROUVE LA ROUTE DEMANDER SINON RENVOIE 404
		*/
		private function runRouteur() {
			$this->route = Routeur::getInstance()->getRoute();
			if($this->route == null) {$this->httpResponse->setCode(404); $this->end();}
			return $this;
		}

		/*
			VERIFIE AUTORISATION 
		*/
		private function runFirewall() {
			$this->firewall->getInstance()->run();
			return $this;
		}

		/*
			EXECUTE LA METHOD ASSOCIER A LA ROUTE
		*/
		private function runMethod() {
			
			$controllerName = $this->route->getController();
			$methodName = $this->route->getMethod();

			$controller = new $controllerName();
			$res = $controller->$methodName();

			//Gestion du retour du controlleur
			if(is_a($res, 'Core\Page')) { //Une page -> standard
				$this->httpResponse->setPage($res);
			}
			elseif(is_array($res)) { //Un tableau -> on l'encode en json
				$this->httpResponse->getPage()->addContent(json_encode($res));
			}
			elseif (is_int($res)) { //Un entier -> on retourne un code d'erreur
				$this->httpResponse->setCode($res);
			}
			elseif(is_bool($res)) {
				$code  = ($res) ? 200 : 400;
				$this->httpResponse->setCode($code);
			}
			elseif(is_string($res)) {
				$this->httpResponse->getPage()->addContent($res);
			}
			elseif ($res !== null) {
				throw new Exception("The method '$controllerName'->'$methodName' dit not return any correct value. You can only return Page, array, int, bool or string. It returned '".gettype($res)."'", 1);
			}
			
			return $this;
		}

		/*
			ROUTINE DE FIN
			ENVOI DE LA REPONSE
		*/
		public function end() {
			// This is done to prevent any output before the header are sent (necesary for cookie)
			$pre_render_contents = ob_get_contents();
			ob_end_clean();
			$this->httpResponse->getPage()->addContent($pre_render_contents);

		    $this->httpResponse->send();
		    
		    exit(); // important -> end must be a exit of any process
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