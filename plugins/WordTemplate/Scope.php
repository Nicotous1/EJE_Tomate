<?php
	namespace WordTemplate;
	use \Exception;

	class ScopeVoid {}

	class Scope {
		private $piles;
		private $level;

		public function __construct($data) {
			$this->piles = array($data);
			$this->level = 0;

			$this->add("true", true);
			$this->add("false", false);
		}

		public function setContext($context) {
			$context = $this->clean_str($context);
			$lines = explode(";", $context);
			foreach ($lines as $line) {
				if ($line == '') {continue;}
				$ps = explode("=", $line);
				if (count($ps) != 2) {throw new Exception("Le contexte contient la ligne '$line' qui n'est pas au format '#VAR_NAME# = #VALUE#'");}
				$this->add($ps[0], $this->get($ps[1]));
			}
		}

		public function getlevel() {
			return $this->level;
		}

		public function get($ref, $for_func = false) {
			// Remove space and invisible character
			$ref = $this->clean_str($ref);
			$ref = strtolower($ref);

			$parts = $this->split_comma($ref);

			if (count($parts) > 1) {
				$res = array();
				foreach ($parts as $part) {
					$res[] = $this->get($part);
				}
				return $res;		
			} else {
				$res = $this->get_pure($ref);
				return ($for_func) ? array($res) : $res;
			}

		}

		private function get_pure($ref) {
			$cursor = 0;
			$val = new ScopeVoid();
			while ($cursor < strlen($ref)) {
				//echo ("<br><br>Start c = $cursor : ||" . substr($ref, 0, $cursor) . "||->||" . substr($ref, $cursor) . "||<br>");

				$ref_val = substr($ref, 0, $cursor - 1);

				// WARNING -> CAN BE NULL
				$first_match = $this->first_match($ref, array("(", "."), $cursor);
				$first_match_c = $first_match[0];
				$first_match_pos = $first_match[1];




				if ($first_match_c == "." || $first_match_c === false) {
					$name_var = substr($ref, $cursor, $first_match_pos - $cursor);
					$val = $this->next($val, $name_var, $ref_val);
					$cursor = $first_match_pos + 1;
					continue;
				}



				//First is open parenthesis (DONE)
				if ($first_match_c == "(") {
					$pos_open = $first_match_pos;
					$pos_close = $this->find_close_bracket($ref, $pos_open);

					$sub_content = substr($ref, $pos_open + 1, $pos_close - $pos_open - 1); //Contenu de la parenthèse.
					$block = substr($ref, $cursor, $pos_close); 
					

					$function_name = substr($ref, $cursor, $pos_open - $cursor);
					if ($function_name == '') {
						$sub_val = $this->get($sub_content, false);
						if (is_a($val, "WordTemplate\ScopeVoid")) {
							$val = $sub_val;
						} else {
							if (!is_string($sub_val)) {throw new Exception("La parenthese '$block' doit retourner une string pas un type '".gettype($sub_val)."' dans '$ref' !");}
							$val = $this->next($val, $sub_val, $ref_val);
						}
					} else {
						// Il ne peut y avoir de fonction dès le root
						if ($cursor == 0) {throw new Exception("'$ref', vous ne pouvez pas appeler de fonction dans le vide. La fonction doit appartenir à une classe ! Peut être vouliez vous dire 'etude.$ref'.");}
						
						// Une fonction doit appartenir à une classe
						if (!is_object($val)) {throw new Exception("'$ref_val' n'est pas un objet mais est de type '" . gettype($val) . "'. Il n'a donc pas de méthode nommée '$function_name' dans '$ref'.");}
						
						// La fonction doit exister
						if (!method_exists($val, $function_name)) {throw new Exception("'$function_name' n'est pas une méthode de l'objet '$ref_val' qui est de type '".gettype($val)."' dans '$ref'.");}

						// Paramètres de la fonctions
						$params = $this->get($sub_content, true);

						// Execution de la fonction
						try {
							$val = call_user_func_array(array($val, $function_name), $params);
						} catch (Exception $e) {
							throw new Exception("'$block', la fonction '$function_name' de '$ref_val' a retournée une erreur : " . $e->getMessage());
						}	
					}

					$cursor = $pos_close + 2;
					continue;
				}


			}
	
			return (is_a($val, "WordTemplate\ScopeVoid")) ? null : $val;
		}

		private function split_comma($ref) {
			$level = 0;
			$parts = array();
			$cursor = 0;
			for ($i=0; $i < strlen($ref); $i++) { 
				$c = substr($ref, $i, 1);
				if ($c == "(") {$level++;}
				if ($c == ")") {$level--;}
				if ($c == "," && $level == 0) {
					$parts[] = substr($ref, $cursor, $i - $cursor);
					$cursor = $i + 1;
				}
			}
			if (strlen($ref) != $cursor) {$parts[] = substr($ref, $cursor, strlen($ref) - $cursor);}
			if ($level != 0) {throw new Exception("Une parenthèse n'est pas refermée dans '$ref'.");}
			return $parts;
		}

		private function find_close_bracket($ref, $pos_open) {
			$level = 1;
			for ($i=$pos_open + 1; $i < strlen($ref); $i++) { 
				$c = substr($ref, $i, 1);
				if ($c == "(") {$level++;}
				if ($c == ")") {$level--;}

				if ($level == 0) {return $i;}
			}
			throw new Exception("Une parenthèse n'est pas refermée dans '$ref'.");
		}

		private function next_root($id) {
			for ($i=$this->level; $i >= 0  ; $i--) { 
				if (isset($this->piles[$i][$id])) {return $this->piles[$i][$id];}
			}
			//var_dump($this->piles);
			throw new Exception("'". $id . "' n'a pu être trouvé !\nVous utilisez peut-être une variable d'une boucle terminée.");
		}

		private function next($val, $name_var, $ref_val) {
			if (is_a($val, "WordTemplate\ScopeArray")) {$val = $val->toArray();}

			if (is_a($val, "WordTemplate\ScopeVoid")) {
				return $this->next_root($name_var);
			}

			if (is_array($val)) {
				if (isset($val[$name_var])) {
					return $val[$name_var];
				} else {
					throw new Exception("Le tableau '" . $ref_val . "' ne possede pas d'attribut '".$name_var."' !");
				}
			}

			if (is_a($val, "Core\PDO\Entity\Entity")) {
				if ($val->exist_att($name_var)) {
					return $val->get($name_var);
				} else {
					throw new Exception("L'objet '" . $ref_val . "' de type '".get_class($val)."' ne possede pas d'attribut '".$name_var."' !");
				}
			}

			if (is_a($val, "Admin\Entity\DocHistory")) {
				return $val->get($name_var);
			}

			if ($val == null) {
				throw new Exception("La variable '" . $ref_val . "' est vide ! Elle ne possede donc pas d'attribut '".$name_var."' !" );
			}

			throw new Exception("La variable '" . $ref_val . "' n'est pas un tableau ou un objet ou un DocHistory mais est de type '".gettype($val)."' ! Elle ne possede donc pas d'attribut '".$name_var."' !");			
		}



		private function first_match($str, $chars, $cursor) {
			$min = false; $min_c = false;
			foreach ($chars as $c) {
				$p = strpos($str, $c, $cursor);

				if ($p !== false && ($min === false || $p < $min)) {
					$min = $p;
					$min_c = $c;
				}
			}

			if ($min === false) { $min = strlen($str);}
			return array($min_c, $min);
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

		private function clean_str($str) {
			$str = str_replace(' ', '', $str);
			$str = preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', $str); //Invisible
			return $str;
		}
	}
?>