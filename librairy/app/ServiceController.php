<?php
;

	class ServiceController {
		public function getPDO() {
			$PDO = $this->getSaveOf('PDO');

			// AUCUNE SAVE DISPONIBLE
			if ($PDO == null) {
				$PDO = new MyPDO($this->getParam('hostBDD'), $this->getParam('nameBDD'), $this->getParam('userBDD'), $this->getParam('mdpBDD'));
				$this->saveService('PDO' ,$PDO);
			}
			
			return $PDO;			
		}

		public function getRouteur() {
			$Routeur = $this->getSaveOf('Routeur');

			// AUCUNE SAVE DISPONIBLE
			if ($Routeur == null) {
				$Routeur = new Routeur($this->getParam('routes'));
				$this->saveService('Routeur' ,$Routeur);
			}

			return $Routeur;
		}

		public function getFirewall() {
			$Firewall = $this->getSaveOf('Firewall');

			// AUCUNE SAVE DISPONIBLE
			if ($Firewall == null) {
				$Firewall = new Firewall();
				$this->saveService('Firewall' ,$Firewall);
			}

			return $Firewall;
		}

		public function getCookies() {
			$Cookies = $this->getSaveOf('Cookies');

			// AUCUNE SAVE DISPONIBLE
			if ($Cookies == null) {
				$Cookies = new CookieController();
				$this->saveService('Cookies' ,$Cookies);
			}

			return $Cookies;
		}

		public function getApp() {
			if(!isset($GLOBALS['app_reference'])) {throw new Exception("No application objet has been saved in the ServiceController !"); return null;}
			return $GLOBALS['app_reference'];
		}

		public function setApp(Application $app) {
			$GLOBALS['app_reference'] = $app;
			return $this;
		}

		public function getParam($name) {
			return $GLOBALS['params'][$name];
		}

		public function setParam($name, $val) {
			return $GLOBALS['params'][$name] = $val;
		}

		private function getSaveOf($name) {
			return (isset($GLOBALS['service'][$name])) ? $GLOBALS['service'][$name] : null;
		}

		private function saveService($name, $service) {
			$GLOBALS['service'][$name] = $service;
			return $this;
		}
	}
?>