<?php
	namespace Auth\Entity;
	
	class Token {

		private $id;
		private $selector;
		private $validator;
		private $hash;
		private $userid;
		private $expires;

		const SIZE_SELECTOR = 12;
		const SIZE_TOKEN = 64;
		const SIZE_VALIDATOR = 64;
		const TOKEN_LIFE = 20; //En jours

		public function __construct(array $params = null) {
			//DEFAULT VARS
			$defaults = array(
				"token_id" => null,
				"selector" => null,
				"validator" => null,
				"hash" => null,
				"user" => null, 
				"expires" => null
			);
			$params = ($params != null) ? array_replace($defaults,$params) : $defaults;


			//ATTRIBUTION VARIABLES
			$this->id = $params['token_id'];
			$this->selector = $params['selector']; //MUST BE UNIQUE
			$this->validator = $params['validator'];
			$this->hash = $params['hash'];
			$this->userid = $params['user'];
			$this->expires = $params['expires'];	
		}

		private function hashOf($str) {
			return hash("sha256", $str);
		}

		public function check() {
			return  !$this->isExpired() &&
				   	$this->hash_equals($this->hash,$this->hashOf($this->validator))
			;
		}

		public function isExpired() {
			$now = new DateTime();
			return ($this->expires < $now);
		}

		public function getUserId() {
			return $this->userid;
		}

		public function getSelector() {
			return $this->selector;
		}

		public function getValidator() {
			return $this->validator;
		}

		public function getHash() {
			if ($this->hash == null) {
				if ($this->validator == null) {return null;}
				else { $this->hash = $this->hashOf($this->validator);}
			}
			return $this->hash;
		}

		public function getExpires() {
			return $this->expires;
		}

		public function serialize() {
			return $this->getSelector().$this->getValidator();
		}

		public function setHash($hash) {
			$this->hash = $hash;
			return $this;
		}

		public function setUserId($id) {
			$this->userid = $id;
			return $this;
		}

		public function setSelector($selector) {
			$this->selector = $selector;
			return $this;
		}

		public function setValidator($validator) {
			$this->validator = $validator;
			$this->hash = null;
			return $this;
		}

		public function setExpires($expires) {
			$this->expires = $expires;
			return $this;
		}

		private  function hash_equals($str1, $str2) {
			if(strlen($str1) != strlen($str2)) {
				return false;
			} else {
				$res = $str1 ^ $str2;
				$ret = 0;
				for($i = strlen($res) - 1; $i >= 0; $i--) $ret |= ord($res[$i]);
				return !$ret;
			}
		}
		
	}

?>