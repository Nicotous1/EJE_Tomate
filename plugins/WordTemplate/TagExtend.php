<?php
	namespace WordTemplate;
	use \Exception;
	
	class TagExtend extends Tag {

		protected $openTag = "[[for";
		protected $closeTag ="]]";
		protected $openExtTag = "<w:tr "; //Espace important car il existe des w:tr*** qui parasiteraient
		protected $closeExtTag = "</w:tr>";

		protected $openExtTagPos;
		protected $closeExtTagPos;


		//Add the handle of array before -> must return error when other line before detected else scope error will happen !
		public function getOpenTagPos() {
			if ($this->openExtTagPos === null) {
				$openTagPos1 = $this->str->strpos($this->openTag);
				$this->openTagPos = $openTagPos1;

				if ($openTagPos1 !== false ) {
					$offset = $openTagPos1[0] - $this->str->size();
					$openExtPos = strrpos($this->str->source(), $this->openExtTag , $offset); //On utilise pas $this->strPos car on recherche une balise Word qui serait ignorée !
					if ($openExtPos === false) {
						throw new Exception("Le TagExtend près de '". $this->str->extract($openTagPos1[0]) ."' doit être placé à l'interieur d'un tableau !", 1);
					}
					$this->openExtTagPos = array($openExtPos, $openExtPos + strlen($this->openExtTag));
				} else  {
					$this->openExtTagPos = false;
				}
			}
			return $this->openExtTagPos;
		}

		public function findArrayEnd($start) {
			if ($start === false || $this->str->size() <= $start) {return false;}
			$end = false;
			$offset = $start + strlen($this->openExtTag);
			while($offset < $this->str->size()) {
				$firstClose = strpos($this->str->source(), $this->closeExtTag, $offset);
				if ($firstClose === false) {throw new Exception("DSI -> Erreur dans la lecture du Word !", 1);}
				
				$nextOpen = strpos($this->str->source(), $this->openExtTag, $offset);
				if ($nextOpen !== false && $nextOpen < $firstClose) {
					$nextClose = $this->findArrayEnd($nextOpen);
					if ($offset === false) {throw new Exception("DSI -> Erreur dans la lecture du Word !", 1);}
					$offset = $nextClose;
					continue;
				} else {
					return $firstClose + strlen($this->closeExtTag);
				}
			}
		}

		// Attention donne la position de fermeture -> donc la fin du tag de fermeture et pas le début !
		//On utilise pas $this->strpos car ce sont des tags WORD !
		public function getCloseTagPos() {
			if ($this->closeExtTagPos === null) {
				$openExt = $this->getOpenTagPos()[0];
				$closeExt = $this->findArrayEnd($openExt);
				$openTagPos = $this->openTagPos;
				if ($closeExt < $openTagPos[0]) {throw new Exception("Le TagExtend près de '". $this->str->extract($openTagPos1[0]) ."' doit être placé à l'interieur d'un tableau !", 1);}
				$this->closeExtTagPos = array($closeExt - strlen($this->closeExtTag),$closeExt);
			}
			return $this->closeExtTagPos;
		}

		public function exists() {
			return ($this->getOpenTagPos() !== false && $this->getCloseTagPos() !== false && $this->openTagPos !== false);
		}

		public function compile(Scope $scope) {
			if (!$this->exists()) {throw new Exception("DSI -> You should not compile a tag that not exists !", 1);}
			//echo "coucou<br>";
			$start = $this->getOpenTagPos()[0];
			$end = $this->getCloseTagPos()[1];
			$openTagPos = $this->openTagPos;
			$closeTagPos = $this->str->strpos($this->closeTag, $openTagPos[1]);
			if ($closeTagPos === false) {throw new Exception("Un TagExtend n'est pas fermé !\nIl manque '$this->closeTag' près de '" . $this->str->extract($openTagPos[0]) . "' !", 1);}

			$a = $this->str->substr_pos($start, $openTagPos[0]);
			$b = $this->str->substr_pos($openTagPos[1], $closeTagPos[0]);
			$c = $this->str->substr_pos($closeTagPos[1], $end);
/*			var_dump($a);
			var_dump($b);
			var_dump($c);*/

			$a->add($c);
			$template = $a;
			//var_dump($template);

			$param = $b->content();
			$param = explode(" in ", $param);
			if (count($param) != 2) { throw new Exception("Un tag de répétition doit être de la forme '$this->openTag item in array $this->closeTag' !\nVérifier que vous avez bien écrit 'as' en minuscule et l'avait séparé d'espace.", 1);}

			$res = null;
			$scope->addLevel();
			$i = 0;
			foreach ($scope->get($param[1]) as $index => $x) {
				$i++;
				$scope->add("i", $i)
					  ->add("index", $index)
					  ->add($param[0], $x);
				$tomateTemplate = new TomateTemplate($template, $scope);
				$res .= $tomateTemplate->compile()->getContent();
			}
			$scope->dropLevel();
			return $res;
		}

	}
?>