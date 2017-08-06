<?php
namespace Core\PDO\Entity;
	use \Exception;

	class EntitySQL {

		private $table;
		private $class;
		private $atts;
		private $cols;
		private $atts_d;
		private $atts_i;

		public function __construct(array $p) {
			$this->class = $p["class"];
			$this->table = (isset($p["table"])) ? $p["table"] : strtolower($this->class);
			$this->atts = array(); $this->atts_d = array(); $this->atts_i = array();
			$this->cols = array();
			foreach ($p["atts"] as $a) {
				$a["parent"] = $this;
				$this->add($a);
			}

			//COMPULSORY ID
			$this->add(array( 
					"parent" => $this,
					"att" => "id",
					"type" => AttSQL::TYPE_INT,
				)
			);
		}

		public function add($a) {
			$a = new AttSQL($a);
			$this->atts[$a->getAtt()] = $a;
			if ($a->isDirect()) {
				$this->cols[$a->getCol()] = $a; //Only for Drect att
				$this->atts_d[] = $a;
			} else {
				$this->atts_i[] = $a;
			}
			return $this;
		}

		public function convColToAtt($cols) {
			if (empty($cols)) {return array();}
			$atts = array();
			foreach ($cols as $col => $x) {
				$attSQL = $this->getCol($col);
				$atts[$attSQL->getAtt()] = $x;
			}
			return $atts;
		}

		public function getAtt($n) {
			if (!isset($this->atts[$n])) {throw new Exception("L'attribut '".$n."' n'existe pas sur l'objet '".$this->class."' !", 1);}
			return $this->atts[$n];
		}

		//ONLY FOR DRECT ATT !!!!
		public function getCol($n) {
			return $this->cols[$n];
		}

		public function getClass() {
			return $this->class;
		}	

		public function getAtts() {
			return $this->atts;
		}

		public function getDAtts() {
			return $this->atts_d;
		}

		public function getIAtts() {
			return $this->atts_i;
		}

		public function getTable() {
			return $this->table;
		}

		public function exist_att($att) {
			return isset($this->atts[$att]);
		}

		
	}
?>