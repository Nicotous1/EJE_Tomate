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

		public function getCloseTagPos() {
			if ($this->closeTagPos === null) {
				$this->closeTagPos = $this->find($this->closeTag);
				if ($this->closeTagPos === False) {
					throw new Exception("Le Tag '" . get_class($this) . "' n'est pas refermé. '".$this->closeTag."' n'a pu être trouvée ! " . $this->getNearStr(), 1);
				}
			}

			return $this->closeTagPos;
		}	

		public function getInside() {
			return $this->str->substr_pos($this->getOpenTagPos()[1],$this->getCloseTagPos()[0]);
		}	

		protected function getNearStr($nearTag = null) {
			if ($nearTag === null) {$nearTag = $this->getOpenTagPos();}
			return "Près de '" . $this->str->extract($nearTag[0]) . "' !";
		}

		protected function error($str, $nearTag = null) {
			$str = get_class($this) . " : " . $str . "\n" . $this->getNearStr($nearTag);
			throw new Exception($str, 1);
		}

		public function getStrPos() {
			return $this->str;
		}

		protected function find($balise, $offset = null) {
			if ($offset === null) {			
				$openPos = $this->getOpenTagPos();
				$offset = $openPos[1];
			}
			$level = 0;

			while (!$this->str->offsetOut($offset)) {
				$balisePos = $this->str->strpos($balise, $offset);
				if ($balisePos === False) {return False;} // No balise found anywhere

				$openPos = $this->str->strpos($this->openTag, $offset);
				if ($openPos === False) {
					if ($level == 0) {
						return $balisePos;
					} else {
						$closePos = $this->str->strpos($this->closeTag, $offset);
						if ($closePos === False) {
							return False;
						} else {
							$level--;
							$offset = $closePos[1];
						}
					}
				} else {
					//Both not false
					if ($level == 0 && $balisePos[0] <= $openPos[0]) { //Found at good level
						return $balisePos;
					} else {
						$closePos = $this->str->strpos($this->closeTag, $offset);
						if ($closePos[0] < $openPos[0])	{ // New close
							$level--;
							$offset = $closePos[1];
						} else { // New Open
							$level ++;
							$offset = $openPos[1];
						}
					}					
				}


				if ($level < 0) {return False;}
			}
			return False;
		}


		protected function explode($balise) {
			$found = null;
			$offset = $this->getOpenTagPos()[1];
			$parts = array();
			while ($found !== False) {
				$found = $this->find($balise, $offset);
				if ($found !== False) {
					$parts[] = $this->str->substr_pos($offset, $found[0]);
					$offset = $found[1];
				}
			}
			$parts[] = $this->str->substr_pos($offset, $this->getCloseTagPos()[0]);
			return $parts;
		}

		protected function render($str, Scope $scope) {
			$tomateTemplate = new TomateTemplate($str, $scope);
			return $tomateTemplate->compile()->getContent();
		}
	}
?>