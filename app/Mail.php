<?php 
	namespace Core;

	class Mail
	{

		private $receivers;
		private $page;
		private $title;

		private static $params = null;
		
		public function __construct($receivers, $title, $page)
		{
			// Set params
			if (self::$params === null) {
				self::$params = ConfigHandler::getInstance()->get(".mail");
			}

			$this->title = $title;

			if (!is_array($receivers)) {
				$receivers = array($receivers);
			}
			$this->receivers = $receivers;
			
			if (!is_a($page, "Core\Page")) {
				$page = new Page($page);
			}
			$this->page = $page;
		}

		public function getTitle() {
			return $this->title;
		}

		public function getReceivers() {
			return $this->receivers;
		}

		public function getPage() {
			return $this->page;
		}
		
		public function send() {

			$title = $this->getTitle();
			
			//Receivers
			$receiversString = '';
			foreach ($this->getReceivers() as $mail) {
				$receiversString .= " " . $mail . " , ";
			}

			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
		    $headers .= "From: ".self::$params["name"]." <".self::$params["mail"]."> \r\n";

			$content = $this->getPage()->getRender();

			return mail($receiversString, $title, $content, $headers);
		}

	}
?>