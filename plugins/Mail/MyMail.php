<?php 
	class MyMail
	{

		private $receivers;
		private $page;
		private $title;
		
		public function __construct($receivers, $title, $page)
		{
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
		    $headers .= "From: Smooedis Facturation <compte@smooedis.com> \r\n";

			$content = $this->getPage()->getRender();

			if ( $_SERVER['HTTP_HOST'] != "localhost" ) {
				return mail($receiversString, $title, $content, $headers);
			} else {
				$file = 'last_mail.txt';
				$content = $headers . "\n\n\n" . $title . "\n\n" . $content . "\n\n";
				file_put_contents($file, $content);
				return true;	
			}

		}

		private function convertUserToHeader($user) {
			if (is_array($user) && count($user) >= 2) {
				$str =  $user[1] . "dzdzddz <" . $user[0] . ">";
				return $str;
			} else {
				return $user;
			}
			return false;
		}


	}
?>