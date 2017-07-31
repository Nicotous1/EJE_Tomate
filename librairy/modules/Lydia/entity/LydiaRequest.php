<?php
	class LydiaRequest {
		protected $url;
		protected $params;
		protected $result;
		protected $signature;

		const vendor_token = "584976a3c969c851596416";
		const private_token = "584976a3cb830108940202";
		const recipient_email = "ntoussai29@gmail.com";

		public function __construct($url, $params) {
			$this->url = $url;
			$this->params = $params;
			$this->result = -1;
		}

		protected function exec() {
			$fields_string = "";
			foreach($this->params as $key=>$value) {
				$fields_string .= urlencode($key).'='.urlencode($value).'&';
			}
			rtrim($fields_string, '&');

			//open connection
			$ch = curl_init();

			//set the url, number of POST vars, POST data
			curl_setopt($ch,CURLOPT_URL, $this->url.".json");
			curl_setopt($ch,CURLOPT_POST, count($this->params));
			curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			//execute post
			$result = curl_exec($ch);

			//close connection
			curl_close($ch);
			return json_decode($result, true);
		}

		public function getResult() {
			if ($this->result == -1) {$this->result = $this->exec();}
			return $this->result;
		}

		public function hasError() {
			return (isset($this->getResult()["error"]) && $this->getResult()["error"] != "0");
		}
	}
?>