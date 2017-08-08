<?php
	namespace Auth\Entity;
	use \Exception;
	use Core\PDO;
	use \Datetime;

	require_once "plugins/Random/random.php";
	
	class TokenController
	{


		public function generate(User $user) {

			if ($user->isVisiteur()) {throw new Exception("TokenController can't make a token for a visiteur. User must have an id !");}

			$expires = new DateTime();
			$expires->add(new DateInterval('P'. Token::TOKEN_LIFE .'D'));

			$token = new Token(array(
				"selector" => $this->getRandom(Token::SIZE_SELECTOR),
				"validator" => $this->getRandom(Token::SIZE_VALIDATOR),
				"expires" => $expires,
				"user" => $user->getId(),
			));

			$request = PDO::getInstance()->prepare("INSERT INTO token (selector, hash, user, expires) VALUES (:selector, :hash, :user, :expires)");

			for ($i=0; $i < 3; $i++) { 
				$res = $request->execute(array(
						":selector" => $token->getSelector(),
						":hash" => $token->getHash(),
						":user" => $token->getUserId(),
						":expires" => $token->getExpires()->format("Y-m-d H:i:s"),
				));
				if ($res) {break;} //SUCCESS
				if (!$res && $request->errorInfo()[1] != 1062) {return null;} //UNCKNOWN ERROR
				$token->setSelector($this->getRandom(Token::SIZE_SELECTOR)); //Rare mais clé déja utilisé donc regeneration
			 }

			return $token;

		}

		public function update(Token $token) {

			$expires = new DateTime();
			$expires->add(new DateInterval('P'. Token::TOKEN_LIFE .'D'));
			$token->setExpires($expires);
			
			$token->setValidator($this->getRandom(Token::SIZE_VALIDATOR));

			$request = PDO::getInstance()->prepare("UPDATE token SET expires = :expires, hash = :hash WHERE selector = :selector");
			$res = $request->execute(array(
					":selector" => $token->getSelector(),
					":hash" => $token->getHash(),
					":expires" => $token->getExpires()->format("Y-m-d H:i:s"),
			));		 

			return $res;		
		}

		public function get(Token $token) {
			if ($token->getSelector() == null) {return null;}

			$request = PDO::getInstance()->prepare("SELECT hash, user, expires FROM token WHERE selector = :selector");
			$res = $request->execute(array(":selector" => $token->getSelector()));
			
			$tokenArray = $request->fetch();
			if($tokenArray != null) {
				return $token->setHash($tokenArray["hash"])
					         ->setUserId($tokenArray["user"])
					         ->setExpires(new DateTime($tokenArray["expires"]));
			}
			return null;
		}

		public function getOfRaw($str) {
			$token = $this->extract($str);
			if ($token == null) {return null;}
			return $this->get($token);
		}

		public function removeOfRaw($str) {
			$token = $this->extract($str);
			if ($token == null) {return null;}
			return $this->remove($token);
		}

		public function remove(Token $token) {
			if ($token->getSelector() == null || $token->getHash() == null) {return false;}

			$request = PDO::getInstance()->prepare("DELETE FROM token WHERE selector = :selector");
			$res = $request->execute(array(
				":selector" => $token->getSelector(),
			));

			return $res;
		}

		public function extract($str) {
			if ($str == null or strlen($str) != Token::SIZE_TOKEN) {return null;}
			return new Token(array(
				"selector" => substr($str, 0,Token::SIZE_SELECTOR),
				"validator" => substr($str,-(Token::SIZE_VALIDATOR)),
			));
		}

		private function getRandom($length) {
			if ($length % 2 != 0) {throw new Exception("random_bytes give twice the length asked. So you need a 2 multiple for the length !");}
			return bin2hex(random_bytes($length/2));
		}
	}
?>