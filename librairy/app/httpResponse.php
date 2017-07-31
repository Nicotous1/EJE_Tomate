<?php
;

	class httpResponse {
		private $page;
		private $header;
		private $no_render;
		private $file;

		public function __construct() {
			$this->headers = array();
			$this->page = new Page();
			$this->file = null;
			$this->no_render = false; //si true => pas bessoin de render
		}

		public function addHeader($string = null) {
			if($string != null) {$this->headers[] = $string;}
			return $this;
		}

		public function redirect($url) {
			$this->headers[] = "Location: " . $url;
			$this->no_render = true;
			return $this;
		}

		public function setCode($int = null) {
			$header = null;
			if($int == 404) {$header = "HTTP/1.0 404 Not Found";}
			$this->addHeader($header);
			$this->page = new ErrorPage($int);
			return $this;
		}

		public function setPage($page) {
			$this->page = $page;
			return $this;
		}

		public function getPage() {
			return $this->page;
		}

		public function setFile($name, $ext, $path, $delete = false) {
			if (!file_exists($path)) {return false;}
			$this->no_render = true;
		    $this->addHeader('Content-Description: File Transfer')
		    	 ->addHeader('Content-Type: application/octet-stream')
		    	 ->addHeader('Content-Disposition: attachment; filename="'. $name . "." . $ext .'"')
		    	 ->addHeader('Expires: 0')
		    	 ->addHeader('Cache-Control: must-revalidate')
		    	 ->addHeader('Pragma: public')
		    	 ->addHeader('Content-Length: ' . filesize($path))
		    ;
		   	$this->file = array($path, $delete);
		}

		private function flushHeader() {
			foreach ($this->headers as $header) {
				header($header);
			}
			return $this;
		}

		public function send() {
			$sc = new ServiceController();
			$sc->getCookies()->flushCookies();

			$this->flushHeader();

			if ($this->file !== null) {
		    	readfile($this->file[0]);
		    	if ($this->file[1]) {unlink($path);}
		    	return $this;
			}

			if (!$this->no_render) {$this->page->render();} //Si non redirigé => render
			
			return $this;
		}
	}
?>