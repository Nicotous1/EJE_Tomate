<?php
	namespace Core\PDO;
	use Core\PDO\Entity\AttSQL;
	use Core\PDO\Entity\Entity;
	use \Exception;

	class Request {
		private $str;
		private $r;
		private $res;
		private $toBind;
		private $overData;
		private $data;

		private static  $start = array("#", ":");
		private static  $end = array(" ", ",", "=", ")", "(", ";");

		public function __construct($str, $data = null, $over = array()) {
			$this->r = null;
			$this->res = null;
			$this->toBind = array();
			$this->overData = $over;
			
			$this->str = $this->preHandle($str, $data);

		}

		public function fetch($type) {
			return  $this->getR()->fetch($type);
		}

		public function addOverData($key, $value) {
			$this->overData[$key] = $value;
			return $this;
		}

		public function append($str, $data = null) {
			if ($str == "") {return $this;}
			$this->str .= " " . $this->preHandle($str, $data);
			return $this;
		}

		public function getR() {
			if ($this->r === null) {
				$this->bindValues();
			}
			return $this->r;
		}

		public function execute() {
			if ($this->res === null) {
				$this->res = (bool) $this->getR()->execute();
			}
			return $this->res;
		}

		public function lastId() {
			return PDO::getInstance()->lastInsertId();
		}

		private function bindValues() {
			$this->r = PDO::getInstance()->prepare($this->str);
			foreach ($this->toBind as $key => $val) {
				$this->bindValue(":".$key, $val[0], $val[1]);
			}

			return $this;
		}

		private function bindValue($key, $val, $attSQL) {
			if (is_a($attSQL, "Core\PDO\Entity\AttSQL")) {
				switch ($attSQL->getType()) {
					case AttSQL::TYPE_USER:
					case AttSQL::TYPE_DREF:
					case AttSQL::TYPE_ARRAY:
					case AttSQL::TYPE_INT:
						$this->r->bindValue($key, $this->toId($val), PDO::PARAM_INT);
						return;
					case AttSQL::TYPE_BOOL:
						$this->r->bindValue($key, $val, PDO::PARAM_BOOL);
						return;
					case AttSQL::TYPE_STR:
					case AttSQL::TYPE_FLOAT:
						$this->r->bindValue($key, $val, PDO::PARAM_STR);
						return;
					case AttSQL::TYPE_DATE:
						$this->r->bindValue($key, $val, PDO::PARAM_DATE);
						return;
				}
			} else {
				if ($val === null) {
					return $this->r->bindValue($key, null, PDO::PARAM_NULL);
				}
				else if (is_a($val, "Core\PDO\Entity\Entity") || is_int($val)) {
					return $this->r->bindValue($key, $this->toId($val), PDO::PARAM_INT);
				}
				else if (is_bool($val)) {
					return $this->r->bindValue($key, $val, PDO::PARAM_BOOL);
				}
				else if (is_a($val, "\DateTime")) {
					return $this->r->bindValue($key, $val, PDO::PARAM_DATE);
				} else {
					return $this->r->bindValue($key, $val, PDO::PARAM_STR); //Default
				}
			}
			throw new Exception("Can't bindvalue to '$key' with this type !", 1);
		}

		public function preHandle($str, $data = null) {	
			$this->data = $data; //Set the contexte for all other method	
			$c = 0;
			while ($c < strlen($str)) {
				//Récupération du tag
				$a = $this->mult_strpos($str, $this::$start, $c);
				if ($a === false) {break;}
				$a_p = (substr($str, $a, 1) == ":") ? ($a-1) : $this->mult_pre($str, $this::$end, $a); //Pas de prefixe pour les variables !
				if ($a_p === false) {$a_p = -1;}

				$b = $this->mult_strpos($str,$this::$end, $a);
				if ($b === false) {$b = strlen($str);}
				$tag = substr($str, $a, $b - $a);
				$prefix = substr($str, $a_p + 1, $a - $a_p - 1);

				$res = $this->convertTag($tag, $prefix);

				$a = $a_p + 1; //On rempalcera aussi le prefixe
				$str = substr_replace($str, $res, $a, $b - $a);
				$c = $a + strlen($res); //accurate pos for cursor

			}
			return $str;
		}

		private function addToBind($data, $att = false) {
			$this->toBind[] = array($data, $att);
			return ":" . (count($this->toBind) - 1);
		}

		private function convertTag($tag, $prefix = null) {
			$type = substr($tag, 0, 1);
			//Gestion du dernier caractère de format
			$format = substr($tag, strlen($tag)-1);
			if (in_array($format, array("^", "~", ">"))) {$ref = substr($tag, 1, -1);} else {$format = null; $ref = substr($tag,1);} //set ref and format


			if ($type == "#") {
				$att = $this->getRefSql($ref);

				if (is_a($att, "Core\PDO\Entity\AttSQL")) {
					if ($format == "^") {return $prefix . $att->getTable();}
					if ($format == ">") {return $prefix . $att->getRefCol();}
					if ($format == "~") {
						$e = $this->getRefValue($ref);
						if (is_a($e, "Core\PDO\Entity\Entity")) {
							return $this->equalAtts($e, $prefix);
						}
						else {
							return $prefix . $att->getCol() . " = " . $this->addToBind($e, $att);
						}
					}
					return $prefix . $att->getCol();
				}

				elseif (is_a($att, "Core\PDO\Entity\Entity") || is_a($att, "Core\PDO\Entity\EntitySQL")) {
					$strct = (is_a($att, "Core\PDO\Entity\EntitySQL")) ? $att : $att::getEntitySQL();
					if ($format == "^") {return $prefix . $strct->getTable();}
					if ($format == "~") {return $this->equalAtts($this->getRefValue($ref), $prefix);}
					//Default
					$res = "";
					foreach ($strct->getDAtts() as $d) {
						$res .= $prefix . $d->getCol() . ",";
					}
					return substr($res, 0, -1);

				} else {
					throw new Exception("La ref '$ref' ne dirige vers aucun AttSQL ou Entity !", 1);
				}					
			}

			if ($type == ":") {
				$val = $this->getRefValue($ref);
				if (is_a($val, "Core\PDO\Entity\Entity")) {
					if ($format == "^") {return $this->addToBind($val->getId());}
					$res = "";
					foreach ($val::getEntitySQL()->getDAtts() as $a) {
						$res .= $this->addToBind($val->get($a), $a) . ",";
					}
					return substr($res, 0, -1);
				}
				$att = $this->getRefSql($ref, false); //Pas d'erreur si on tombe sur false !
				if (is_array($val) && $att === false) {return $this->IdsToSQL($val);}
				return $this->addToBind($val);
			}

			return false;
		}

		private function equalAtts(Entity $e, $prefix = null) {
			$res = "";
			foreach ($e->getEntitySQL()->getDAtts() as $a) {
				if ($a->isUnique()) {continue;} //Unique can't be update (id)
				$res .= $prefix . $a->getCol() . " = " . $this->addToBind($e->get($a), $a) . ",";
			}
			return substr($res, 0, -1);
		}

		private function mult_strpos($str, $needles, $offset = 0) {
			$res = false;
			foreach ($needles as $needle) {
				$pos = strpos($str, $needle, $offset);
				if ($res === false || ($pos !== false && $pos < $res)) {
					$res = $pos;
				}
			}
			return $res;
		}

		private function mult_pre($str, $needles, $offset = 0) {
			$res = false;
			$offset = -(strlen($str) - $offset);
			foreach ($needles as $needle) {
				$pos = strrpos($str, $needle, $offset);
				if ($res === false || ($pos !== false && $pos > $res)) {
					$res = $pos;
				}
			}
			return $res;
		}

		private function getRefSql($ref, $error = true) {
			$p = $this->data ;
			foreach (explode(".", $ref) as $i => $key) {
				if ($i == 0 && isset($this->overData[$key])) {$p = $this->overData[$key]; continue;} //Overwrite
				if ($key == "") {continue;}
				if (is_array($p)) {
					if (!isset($p[$key])) {throw new Exception("La ref '$ref' contient la clé '$key' qui n'existe pas dans le tableau !", 1);}
					$p = $p[$key];
				}
				elseif (is_a($p, "Core\PDO\Entity\Entity")) {
					$p = $p->getStruct()->getAtt($key);
				}
				elseif(is_a($p, "Core\PDO\Entity\AttSQL") && $p->isRef()) {
					$class = $p->getClass();
					$p = $class::getEntitySQL()->getAtt($key);
				}
				elseif(is_a($p, "Core\PDO\Entity\EntitySQL")) {
					$p = $p->getAtt($key);
				} else {
					if (!$error) {return false;}
					throw new Exception("La ref '$ref' contient la clé '$key' sur un attribut qui ne peut être descendu (".get_class($p).") ! (Can't go deeper, it's the end of the path Bob !)", 1);
				}
			}		
			

			//Mise en forme sql
			if (is_a($p, "Core\PDO\Entity\Entity")) {$p = $p->getEntitySQL();}
			return (is_a($p, "Core\PDO\Entity\EntitySQL") || is_a($p, "Core\PDO\Entity\AttSQL")) ? $p : false;		
		}

		private function getRefValue($ref) {
			$p = $this->data;
			$keys = explode(".", $ref);
			foreach ($keys as $i => $key) {		
				if ($i == 0 && isset($this->overData[$key])) {return $this->overData[$key];}
				if ($key == "") {continue;}		
				$last = ($i + 1 == count($keys));
				if (is_array($p)) {
					if (!isset($p[$key])) {throw new Exception("La ref '$ref' contient la clé '$key' qui n'existe pas dans les paramètres !", 1);}
					$p = $p[$key];
				}
				elseif (is_a($p, "Core\PDO\Entity\Entity")) {
					$p = ($last && $p->getStruct()->getAtt($key)->isRef()) ? $p->get_Ids($key) : $p->get($key);
				} else {
					var_dump($this->str);
					throw new Exception("La ref '$ref' contient la clé '$key' sur un attribut qui n'est pas une référence ! (Can't go deeper, it's the end of the path Bob !)", 1);
				}
			}
			return $p;		
		}

		private function IdsToSQL($ids) {
			$str = "(-1,";
			foreach ($ids as $raw) {
				$id = (is_a($raw, "Core\PDO\Entity\Entity")) ? $raw->getId() : (int) $raw;
				if ($id > 0) {$str .= $id . ",";} else {throw new Exception("An array for a request SQL must only be composed of int ! Got '$raw'", 1);
				}
			}
			return substr($str, 0, -1) . ")"; //remove last comma and add parenthese
		}

		private function toId($e) {
			if ($e == null) {return $e;}
			if (is_numeric($e)) {return (int) $e;}
			if (is_array($e)) {return (int) $e["id"];}
			if (is_a($e, "Core\PDO\Entity\Entity")) {return $e->getId();} //ADD CHECK IF NULL AND RAISE EXCEPTION -> SAVE BEFORE
			throw new Exception("Convertion impossible en Id !", 1);
		}

		public function getStr() {
			return $this->str;
		}

		public function __toString() {
			return $this->getStr();
		}

	}


?>