<?php
	namespace Core;
	use \Exception;

	class CodePage extends Page{

		private $code;

		public function __construct($code) {
			if (!is_int($code)) {throw new Exception("A CodePage must have an integer as parameter !", 1);}
			$this->code = $code;

			parent::__construct();

			if($this->code == 404) {$header = "HTTP/1.0 404 Not Found";}
			httpResponse::getInstance()->addHeader($header);


			$this->addVar('HeaderTitre', 'Code ' . $this->code);
			$this->addVar('code', $this->code);
			$this->addFile($this->getCodeFile());
		}

		private function getCodeFile() {
			$file = "templates/codes/code_" . $this->code . ".php";
			if (!file_exists($file)) { $file = "templates/code/code.php"; };
			if (!file_exists($file)) { throw new Exception("No code template find at '" . $file . "' !"); };
			return $file;
		}
	}
?>