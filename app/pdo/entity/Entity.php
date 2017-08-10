<?php
namespace Core\PDO\Entity;
use \Exception;

	abstract class Entity implements \JsonSerializable 
	{
		protected $id;

	    final public static function getEntitySQL()
	    {
	        static $entity_sqls = array();

	        $calledClass = get_called_class();

	        if (!isset($entity_sqls[$calledClass]))
	        {
	        	$params = $calledClass::get_array_EntitySQL();
	        	$params["class"] = $calledClass;
	            $entity_sqls[$calledClass] = new EntitySQL($params);
	        }

	        return $entity_sqls[$calledClass];
	    }

	    abstract static protected function get_array_EntitySQL();

		public function __construct($params = null)
		{
			//Particulary handle of id because we need to know after if it has one or no
			if ((isset($params["id"]))) {
				$this->set("id",$params["id"]);
				unset($params["id"]);
			}
			
			//Set to default all attribute SQL to put to default value (ex : array and not null ;))
			$defaults = array();
			foreach (self::getEntitySQL()->getAtts() as $attSQL) {
				$defaults[$attSQL->getAtt()] = $attSQL->getDefault($this); 
			}
			unset($defaults["id"]); //VERY IMPORTANT !
			$defaults = array_replace($defaults, $this->var_defaults());

			$params = ($params != null) ? array_replace($defaults, $params) : $defaults;
			$this->set_Array($params);
			

			//$this->set_Indirects(); //Défini des references si id non null
		}

		public function set_Array($data) {
			if (is_array($data)) { // Cas du null
				foreach ($data as $att => $x) {
					$this->set($att, $x);
				}
			}
			return $this;
		}

		public function set($att, $x) {
			if (is_a($att, "Core\PDO\Entity\AttSQL")) {$att = $att->getAtt();}
			//Check override
			$name_method = "set".ucfirst($att);
			if (method_exists($this, $name_method)) {return $this->$name_method($x);}
			
			$attSQL = self::getEntitySQL()->getAtt($att); //Premier traitement pour vérifier les droits -> si interdit retourne erreur

			$class = $attSQL->getClass();

			switch ($attSQL->getType()) {
				case AttSQL::TYPE_INT:
					$val = ($x === null) ? null : (int) $x; break;

				case AttSQL::TYPE_ARRAY:
					$id = null;
					if (is_array($x) && isset($x["id"]) && is_numeric($x["id"])) { $id = (int) $x["id"];}
					elseif (is_numeric($x)) { $id = (int) $x;}
					$val = (isset($attSQL->getList()[$x])) ? $attSQL->getList()[$x] : null; break;
				
				case AttSQL::TYPE_STR:
					$val = (string) $x; break;
				
				case AttSQL::TYPE_FLOAT:
					$val = ($x === null) ? null : (float) $x; break;
				
				case AttSQL::TYPE_BOOL:
					$val = ($x === null) ? null : (bool) $x; break;
				
				case AttSQL::TYPE_DATE:
					if ($x === null) {$val = null; break;}
					$val = (is_a($x, $class)) ? $x : new $class($x); break;					

				case AttSQL::TYPE_DREF:
				case AttSQL::TYPE_USER:
					if (empty($x)) { $val = null; break;}
					if (is_a($x, $class)) { $val = $x; break;}
					if (is_a($x, "Core\PDO\Entity\RefSQL")) { $val = $x; break;}
					if (is_array($x)) { $val = new $class($x); break;}
					if (is_numeric($x) && $x > 0) { $val = new RefSQL($attSQL, $x); break;}
					if (is_numeric($x) && $x <= 0) { $val = null; break;}
					throw new Exception("The attribut '".$att."' is a reference ! It can only be set with an entity '".$class."', an array or an id !", 1);

				case AttSQL::TYPE_IREF:
				case AttSQL::TYPE_MREF:
					if (empty($x)) { $val = array(); break;}
					if (is_numeric($x)) { $val = new RefSQL($attSQL,array($x)); break;}
					if (is_a($x, $class)) {
						$x->set($attSQL->getAttRef()->getAtt(), $this);
						$val = array($x); break;
					}
					if (is_a($x, "Core\PDO\Entity\RefSQL")) { $val = $x; break;}
					if (is_array($x)) {
						$val = $x; //Hypothese tableau de class -> cas parfait
						// TRES IMPORTANT
						foreach ($val as $i => $item) {
							if ($item == null) { continue;} //Tableau vide => Class default
							if (is_array($item)) {$t = new $class($item); $t->set($attSQL->getAttRef()->getAtt(), $this); $val[$i] = $t; continue;} //Convertie les tableaux de paramètres
							if (!is_a($item, $class)) { $val = new RefSQL($attSQL, $x); break;} //REF DETECTEE !! -> ON ARRETE ET ON RETOURNE UNE REF DE TOUS LES PARAMETRES -> LA REF S'OCCUPERA DU TRI !!
						}
						break;
					}
					throw new Exception("The attribut '".$att."' is an array of reference ! It can only be set with a '".$class."', an array, an id OR an array of class or id or params!", 1);

				default:
					throw new Exception("The type of attribut '".$att."' is not handle in the set function of Entity ! You must define it !", 1);
			}
			$this->$att = $val;
			return $this;
		}

		public function get($att) {
			if (is_a($att, "Core\PDO\Entity\AttSQL")) {$att = $att->getAtt();}

			//Check override
			$name_method = "get".ucfirst($att);
			if (method_exists($this, $name_method)) {
				return $this->$name_method();
/*				$method = new ReflectionMethod(get_class($this), $name_method);
				return ($p !== null && count($method->getParameters()) == 1) ? $this->$name_method($p) : $this->$name_method();*/
			}

			if (!self::getEntitySQL()->exist_att($att)) {
				throw new Exception("$att n'est pas un attribut SQL ! Vous devez définir la fonction get" . ucfirst($att) . "() !", 1);
			}

			$x = $this->$att;
			if (is_a($x,"Core\PDO\Entity\RefSQL")) {
				$this->$att = $x->convert();
			}
			return $this->$att;
		}
		
		/*	
			Retourne les ids ou l'id de la ref evite une requete suplémentaire pour rechercher la ref complete
			ONLY FOR REF
		*/	
		public function get_Ids($att) {
			$attSQL = self::getEntitySQL()->getAtt($att);
			$x = $this->$att;

			switch ($attSQL->getType()) {
				case AttSQL::TYPE_IREF:
				case AttSQL::TYPE_MREF:
					if ($x == null) {return array();}
					if (is_a($x,"Core\PDO\Entity\RefSQL")) {
						return $x->getIds();
					}
					else {
						$ids = array();
						foreach ($this->get($att) as $e) {
							$id = $e->getId();
							if ($id != null) {$ids[] = $id;}
						}
						return $ids;
					}

				case AttSQL::TYPE_DREF:
				case AttSQL::TYPE_USER:
					if ($x == null) {return null;}	
					return $x->getId();
				
				default:
					throw new Exception("get_Ids can only be used with object with getId method !", 1);
			}
		}

		public function get_NotInBDD($att) {
			$attSQL = (is_a($att, "Core\PDO\Entity\AttSQL")) ? $att : self::getEntitySQL()->getAtt($att);
			$att = $attSQL->getAtt();
			$x = $this->$att;

			switch ($attSQL->getType()) {
				case AttSQL::TYPE_IREF:
				case AttSQL::TYPE_MREF:
					if ($x == null) {return array();}
					if (is_a($x,"Core\PDO\Entity\RefSQL")) {
						return $x->getUnSaved();
					}
					else {
						$ids = array();
						foreach ($x as $e) {
							if (!$e->inBDD()) {$ids[] = $e;}
						}
						return $ids;
					}
					break;

				case AttSQL::TYPE_DREF:
				case AttSQL::TYPE_USER:
					if ($x == null) {return null;}	
					return ($x->inBDD()) ? null : $x;
				
				default:
					throw new Exception("get_NotInBDD can only be used with REF !", 1);
			}
		}

		public function get_InBDD_ClassAndId($att) {
			$attSQL = (is_a($att, "Core\PDO\Entity\AttSQL")) ? $att : self::getEntitySQL()->getAtt($att);
			$att = $attSQL->getAtt();
			$x = $this->$att;

			switch ($attSQL->getType()) {
				case AttSQL::TYPE_IREF:
				case AttSQL::TYPE_MREF:
					if ($x == null) {return array();}
					if (is_a($x,"Core\PDO\Entity\RefSQL")) {
						return $x->getInBDD_ClassAndId();
					}
					else {
						$ids = array();
						foreach ($x as $e) {
							if ($e->inBDD()) {$ids[] = $e;}
						}
						return $ids;
					}
					break;

				case AttSQL::TYPE_DREF:
				case AttSQL::TYPE_USER:
					if ($x == null) {return null;}	
					return ($x->inBDD()) ? $x : null;
				
				default:
					throw new Exception("get_NotInBDD can only be used with REF !", 1);
			}
		}

		// Only here to be overflow ;)
		public function var_defaults() {
			return array();
		}

		// Deprecated !
		public function getStruct() {
			return self::getEntitySQL();
		}

		private function set_Indirects() {
			if ($this->id <= 0) {return $this;}
			foreach (self::getEntitySQL()->getIAtts() as $attSQL) {
				$att = $attSQL->getAtt();
				$this->$att = new RefSQL($attSQL, $this);
			}
			return $this;	
		}

		public function getId() {
			return $this->id;
		}

		public function setId($id) {
			if ($id !== null && $this->id !== null && $id != $this->id) {throw new Exception("An entity can not change of id during its lifecycle ! ( ".$this->id." -> ".$id." ) ", 1);} //Incompatible with a cache gestion
			$this->id = ($id === null || $id < 1) ? null : (int) $id;
			return $this;
		}

		public function toArray() {
			$r = array();
			foreach (self::getEntitySQL()->getDAtts() as $attSQL) {
				switch ($attSQL->getType()) {
					case AttSQL::TYPE_ARRAY:
						$r[$attSQL->getAtt()] = $this->get($attSQL->getAtt())["id"];
						break;
					case AttSQL::TYPE_INT:
					case AttSQL::TYPE_STR:
					case AttSQL::TYPE_FLOAT:
					case AttSQL::TYPE_BOOL:
						$r[$attSQL->getAtt()] = $this->get($attSQL->getAtt());
						break;
					
					case AttSQL::TYPE_DATE:
						$val = $this->get($attSQL->getAtt());
						$r[$attSQL->getAtt()] = ($val == null) ? null : $val->getTimestamp();
						break;
					case AttSQL::TYPE_DREF:
						$r[$attSQL->getAtt()] = $this->get_Ids($attSQL->getAtt());
						break;
				};
			}
			return $r;
		}
		
		public function jsonSerialize() {
			return $this->toArray();
		}

		public function inBDD() {
			return ($this->id > 0);
		}

		public function isValid() {
			return true;
		}

		public function exist_att($att) {
			return self::getEntitySQL()->exist_att($att) || method_exists($this, "get".ucfirst($att));
		}
	}
?>