<?php
	namespace Auth\Entity;

	use \DateTime;
	use \DateInterval;
	use Core\PDO\Entity\AttSQL;
	use Core\PDO\Request;
	use Core\PDO\PDO;
	use Core\PDO\Entity\Entity;

	require_once "plugins/Random/random.php";
	
	class Token extends Entity {

		protected static function get_array_EntitySQL() {
			return array(
				"atts" => array(
					array("att" => "selector", "type" => AttSQL::TYPE_STR),
					array("att" => "hash", "type" => AttSQL::TYPE_STR),
					array("att" => "user", "type" => AttSQL::TYPE_DREF, "class" => "User"),
					array("att" => "type", "type" => AttSQL::TYPE_INT),
					array("att" => "date_created", "type" => AttSQL::TYPE_DATE),
					array("att" => "expires", "type" => AttSQL::TYPE_DATE),
					array("att" => "activated", "type" => AttSQL::TYPE_BOOL),
				),
			);
		}

		protected $id;
		protected $selector;
		protected $validator;
		protected $hash;
		protected $type;
		protected $user;
		protected $date_created;
		protected $expires;
		protected $activated;

		const SIZE_SELECTOR = 12;
		const SIZE_VALIDATOR = 40;
		const LIFE_DAYS = 3;

		public function var_defaults() {
			$now = new DateTime();
			$expires = $now->add(new DateInterval('P'. self::LIFE_DAYS	 .'D'));
			return array(
				"date_created" => new DateTime(),
				"activated" => true,
				"hash" => "-1",
				"validator" => $this->getRandom(Token::SIZE_VALIDATOR),
				"expires" => $expires,
			);
		}

		public function create_selector($try = 3) {
			if (strlen($this->selector) == TOKEN::SIZE_SELECTOR) {return $this;} //Already done
			$success = False;
			for ($i=0; $i < $try; $i++) { 
				$this->selector = $this->getRandom(TOKEN::SIZE_SELECTOR);
				$r = new Request("SELECT COUNT(*) AS n FROM #^ WHERE #selector~", $this);
				$r->execute();
				$res = $r->fetch(PDO::FETCH_ASSOC);
				if($res["n"] == 0) {
					$success = True;
					break;
				}
			}
			if (!$success) {throw new Exception("Token did not succeed to generate a selector ! Did you exhaust the infinite ?", 1);}
			return $this;
		}

		public function check() {
			return  !$this->isExpired() &&
				   	$this->hash_equals($this->hash,$this->hashOf($this->validator))
			;
		}

		public function isExpired() {
			return ($this->expires < $now);
		}

		private function hashOf($str) {
			return hash("sha256", $str);
		}

		public function getHash() {
			if ($this->hash == "-1") {
				$this->hash = $this->hashOf($this->validator);
			}
			return $this->hash;
		}

		public function __toString() {
			return $this->get("selector").$this->get("validator");
		}

		public function setValidator($validator) {
			$this->validator = $validator;
			return $this;
		}

		public function getValidator() {
			return $this->validator;
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

		private function getRandom($length) {
			if ($length % 2 != 0) {throw new Exception("random_bytes give twice the length asked. So you need a 2 multiple for the length !");}
			return bin2hex(random_bytes($length/2));
		}
		
	}

?>