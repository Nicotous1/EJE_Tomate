<?php
	namespace Core;

	use Core\PDO\EntityPDO;

	abstract class Controller	{
		protected $httpRequest;
		protected $httpResponse;
		protected $firewall;
		protected $user;
		protected $pdo;

		public function __construct() {
			## Shortcut
			$this->httpRequest = httpRequest::getInstance();
			$this->httpResponse = httpResponse::getInstance();
			$this->firewall = Firewall::getInstance();
			$this->user = $this->firewall->getuser();
			$this->pdo = new EntityPDO();
		}

		protected function success($a = null) {
			if ($a == null) { $a = array();}
			return array_merge(array("res" => true), $a);
		}

		protected function error($msg = "Une erreur s'est produite !") {
			return (array("res" => false, "msg" => $msg));
		}
	}
?>