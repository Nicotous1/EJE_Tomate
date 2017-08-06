<?php
	namespace WordTemplate;
	use \Exception;
	
/*
	On ne stocke pas $str. Mais pour garder cohérence elle doit toujorus être appeler avec la même
*/
	abstract class Tag {
		protected $openTag;
		protected $openTagPos;
		protected $closeTag;
		protected $closeTagPos;

		protected $str;

		//abstract public function getCloseTagPos();
		abstract public function compile(Scope $scope);

		public function __construct(StringPos $str) {
			$this->str = $str;
		}

		public function getWorkZone() {
			return array($this->getOpenTagPos()[0], $this->getCloseTagPos()[1]);
		}

		public function exists() {
			return ($this->getCloseTagPos() !== false);
		}

		public function getOpenTagPos() {
			if ($this->openTagPos === null) {
				$this->openTagPos = $this->str->strpos($this->openTag);
			}

			return $this->openTagPos;
		}

		public function getStrPos() {
			return $this->str;
		}

		//ADVANCED
		protected function handle_rec_tag($offset = 0) {
			$class = get_class($this);
			//TAG DE FERMETURE
			$firstClosePos = $this->str->strpos($this->closeTag, $offset);
			if ($firstClosePos === false) {throw new Exception("Un $class n'est pas fermé !\nIl manque '$this->closeTag' près de '" . $this->str->extract($start[0]) . "' !", 1);}

			$nextTag = new $class($this->str->substr($offset));
			$nextTagStart = $nextTag->getOpenTagPos();
			while ($nextTagStart !== false && $nextTagStart[0] + $offset < $firstClosePos[0]) { //Boucle pour la largeur
				$nextTagClose = $nextTag->getCloseTagPos(); //Recursivité pour la profondeur
				$offset += $nextTagClose[1]; //On se place après cette fermeture très important de rajouter offset ! -> TRES TRES IMPORTANT

				//Largeur
				$strPos = $nextTag->getStrPos()->substr($nextTagClose[1]); // -> plus rapide que $this->str->substr($offset) mais identique !
				$nextTag = new $class($strPos);
				$nextTagStart = $nextTag->getOpenTagPos(); //On reprend la stringpos du next tag

				$firstClosePos = $this->str->strpos($this->closeTag, $offset);
				if ($firstClosePos === false) {throw new Exception("Un $class n'est pas fermé !\nIl manque '$this->closeTag' près de '" . $this->str->extract($offset) . "' !", 1);}
			}
			$this->closeTagPos = $firstClosePos;
		}	

		public function getCloseTagPos() {
			if ($this->closeTagPos === null) {
				$start = $this->getOpenTagPos();
				$offset = $start[1];

				$class = get_class($this);
				//TAG DE FERMETURE
				$firstClosePos = $this->str->strpos($this->closeTag, $offset);
				if ($firstClosePos === false) {throw new Exception("Un $class n'est pas fermé !\nIl manque '$this->closeTag' près de '" . $this->str->extract($start[0]) . "' !", 1);}

				$nextTag = new $class($this->str->substr($offset));
				$nextTagStart = $nextTag->getOpenTagPos();
				while ($nextTagStart !== false && $nextTagStart[0] + $offset < $firstClosePos[0]) { //Boucle pour la largeur
					$nextTagClose = $nextTag->getCloseTagPos(); //Recursivité pour la profondeur
					$offset += $nextTagClose[1]; //On se place après cette fermeture très important de rajouter offset ! -> TRES TRES IMPORTANT

					//Largeur
					$strPos = $nextTag->getStrPos()->substr($nextTagClose[1]); // -> plus rapide que $this->str->substr($offset) mais identique !
					$nextTag = new $class($strPos);
					$nextTagStart = $nextTag->getOpenTagPos(); //On reprend la stringpos du next tag

					$firstClosePos = $this->str->strpos($this->closeTag, $offset);
					if ($firstClosePos === false) {throw new Exception("Un $class n'est pas fermé !\nIl manque '$this->closeTag' près de '" . $this->str->extract($offset) . "' !", 1);}
				}
				$this->closeTagPos = $firstClosePos;				
			}
			return ($this->closeTagPos === false) ? false : $this->closeTagPos;
		}	
	}
?>