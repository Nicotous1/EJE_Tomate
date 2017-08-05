<?php
	namespace Core;
	use \Exception;

	class ErrorPage extends Page{

		private $errorCode;

		public function __construct($errorCode = null) {
			parent::__construct();
			$this->errorCode = $errorCode;
			$this->addVar('HeaderTitre', 'Erreur ' . $this->errorCode);
			$this->addVar('ErrorCode', $this->errorCode);
		}

		private function getErrorFile() {
			$file = "library/templates/error/error_" . $this->errorCode . ".php";
			if (!file_exists($file)) { $file = "library/templates/error/error.php"; };
			if (!file_exists($file)) { throw new Exception("No error template find at '" . $file . "' !"); };
			return $file;
		}

		public function render() {
			extract($this->getVars());
			include($this->getErrorFile());
		}
	}
?>