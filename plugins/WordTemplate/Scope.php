<?php
	namespace WordTemplate;
	use \Exception;

	class Scope {
		private $piles;
		private $level;

		public function __construct($data) {
			$this->piles = array($data);
			$this->level = 0;
		}

		public function getlevel() {
			return $this->level;
		}

		public function get($ref) {
			$ref_str = str_replace(' ', '', $ref); //Clean space

			
			$ref = explode(".", $ref_str);
			$r = $this->getRoot($ref[0]); //Nom mère !
			for ($i=1; $i < sizeof($ref); $i++) { 
				$name_var = $ref[$i];
				if (is_array($r)) {
					if (isset($r[$name_var])) {
						$r = $r[$name_var];
					} else {
						throw new Exception("Le tableau '" . $ref[$i-1] . "' ne possede pas d'attribut '".$name_var."' !", 1);
					}
					continue; //IMPORTANT
				}
				if (is_a($r, "Core\PDO\Entity\Entity")) {
					if ($r->exist_att($name_var)) {
						$r = $r->get($name_var);
					} else {
						throw new Exception("L'objet '" . $ref[$i-1] . "' de type '".get_class($r)."' ne possede pas d'attribut '".$name_var."' !", 1);
					}
					continue; //IMPORTANT
				}
				if (is_a($r, "Admin\Entity\DocHistory")) {
					$r = $r->get($name_var);
					continue; //IMPORTANT
				}
				if ($r == null) {
				throw new Exception("La variable '" . $ref[$i-1] . "' dans '$ref_str' est vide ! Elle ne possede donc pas d'attribut '".$name_var."' !" , 1);
				}
				throw new Exception("La variable '" . $ref[$i-1] . "' dans '$ref_str' n'est pas un tableau ou un objet (".gettype($r).") ! Elle ne possede donc pas d'attribut '".$name_var."' !" , 1);
				
			}

			return $r;
		}

		private function getRoot($id) {
			for ($i=$this->level; $i >= 0  ; $i--) { 
				if (isset($this->piles[$i][$id])) {return $this->piles[$i][$id];}
			}
			//var_dump($this->piles);
			throw new Exception("'". $id . "' n'a pu être trouvé !\nVous utilisez peut-être une variable d'une boucle terminée.", 1);
		}

		public function add($key, $x) {
			$this->piles[$this->level][$key] = $x;
			return $this;
		}

		public function addLevel() {
			$this->level += 1;
			$this->piles[$this->level] = array();
			return $this;
		}

		public function dropLevel() {
			if (!$this->level == 0) {unset($this->piles[$this->level]); $this->level -= 1;}
			return $this;
		}
	}
?>