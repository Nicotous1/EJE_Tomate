<?php
namespace Core\PDO\Entity;
	use \Exception;

	class AttSQL
	{
		// <10 => direct : on main table of entity
		// New type must be handle in AttSQL::__construct, Entity::set, EntityPDO::bindvalue
		
		//Direct
		const TYPE_INT = 0;
		const TYPE_STR = 1; 
		const TYPE_FLOAT = 2;
		const TYPE_BOOL = 3;
		const TYPE_DATE = 4;
		const TYPE_ARRAY = 5;

		//Direct mais ref
		const TYPE_USER = 10;
		const TYPE_DREF = 11;

		//Indirect  >= 20
		const TYPE_IREF = 20;
		const TYPE_MREF = 21;

		private $att;
		private $col;
		private $type;
		private $class;
		private $parent;

		private $ref_col;
		private $att_ref;
		private $null;
		private $table;
		private $list;


		public function __construct($params)
		{
			$this->att = $params["att"];
			$this->type = $params["type"];
			$this->col = (isset($params["col"])) ? $params["col"] : $this->att;
			$this->parent = $params["parent"]; //EntitySQL
			$this->table = $this->parent->getTable();

			//Gestion types
			switch ($this->type) {
				case AttSQL::TYPE_USER:
					$this->class = "User"; break;
				case AttSQL::TYPE_DATE:
					$this->class = "DateTime"; break;
				case AttSQL::TYPE_DREF:
					if (!isset($params["class"])) {throw new Exception("A reference attribut need the CLASS of the reference to be handle !", 1);}
					$this->null = (isset($params["null"])) ? (bool) $params["null"] : true;
					$this->class =  $params["class"]; break;
				case AttSQL::TYPE_MREF:
					if (!isset($params["class"])) {throw new Exception("A reference attribut need the CLASS of the reference to be handle !", 1);}
					if (!isset($params["table"])) {throw new Exception("A multiple reference attribut need the ref table to be handle !", 1);}
					if (!isset($params["ref_col"])) {throw new Exception("A multiple reference attribut need the COLUMN OF THE REF in the ref table to be handle !", 1);}
					$this->class =  $params["class"];
					$this->table =  $params["table"];
					$this->ref_col =  $params["ref_col"];
					break;
				case AttSQL::TYPE_IREF:
					if (!isset($params["class"])) {throw new Exception("A reference attribut need the CLASS of the reference to be handle !", 1);}
					if (!isset($params["att_ref"])) {throw new Exception("A reference attribut need the att_ref of the reference to be handle !", 1);}

					$this->class =  $params["class"];
					$this->att_ref =  ($this->class)::getEntitySQL()->getAtt($params["att_ref"]);
					$this->table = $this->att_ref->getTable();
					$this->col = $this->att_ref->getCol();
					break;
				case AttSQL::TYPE_ARRAY:
					if (!isset($params["list"])) {throw new Exception("A AttSQL of TYPE_ARRAY must have a list parameter ! (where it fetch the array with an id)", 1);}
					$res = array();
					foreach ($params["list"] as $e) {
						$res[$e["id"]] = $e;
					}
					$this->list = $res;
					break;

			}
		}

		public function getDefault(Entity $e = null) {
			switch ($this->getType()) {
				case AttSQL::TYPE_USER:
					return Firewall::getInstance()->getUser();

				case AttSQL::TYPE_IREF:
				case AttSQL::TYPE_MREF:
					return ($e->inBDD()) ? new RefSQL($this, $e) : array();

				case AttSQL::TYPE_DREF:
					return ($e->inBDD()) ? new RefSQL($this, $e) : null;

				default:
					return null;
			}
		}

		public function isUnique() {
			return ($this->att == "id");
		}

		public function isRef() {
			return ($this->type >= 10);
		}

		public function isDirect() {
			return ($this->type < 20);
		}

		public function getAtt() {
			return $this->att;
		}

		public function getAttRef() {
			return $this->att_ref;
		}

		public function getList() {
			return $this->list;
		}



		public function getCol() {
			return $this->col;
		}

		public function getType() {
			return $this->type;
		}

		public function getKey() {
			return ":" . $this->getAtt();
		}

		public function getClass() {
			return $this->class;
		}

		public function getTable() {
			return $this->table;
		}

		public function getRefCol() {
			return $this->ref_col;
		}

		public function getWhereKey() {
			return ":w_".$this->getCol();
		}

		public function getParent() {
			return $this->parent;
		}

		public function canBeNull() {
			return $this->null;
		}

		public function getClassSQL() {
			return ($this->class)::getEntitySQL();
		}
	}
?>