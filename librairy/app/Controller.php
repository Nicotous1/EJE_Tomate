<?php
	namespace Core;

	use Core\PDO\EntityPDO;

	abstract class Controller	{
		protected $SC;
		protected $httpRequest;
		protected $httpResponse;

		public function __construct(httpRequest $httpRequest, httpResponse $httpResponse, Firewall $firewall) {
			$this->SC = new ServiceController();
			
			$this->httpRequest = $httpRequest;
			$this->httpResponse = $httpResponse;
			$this->firewall = $firewall;
			$this->user = $firewall->getuser();
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