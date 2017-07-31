<?php
	class CacheEntity {
		private $entity;
		private $refs;

		public function __construct($e = null, $refs = array()) {
			$this->entity = $e;
			$this->refs = $refs;
		}

		public function addRef(AttSQL $attSQL, $x) {
			$att = $attSQL->getAtt();
			if (isset($this->refs[$att])) { //Assure that there is only one RefSQL for an attribute while all the execution 
				$this->refs[$att]->setX($x);
			} else {
				$this->refs[$att] = new RefSQL($attSQL, $x);
				$this->setRef($this->refs[$att]);
			}
			return $this;
		}

		public function setEntity($e = null) {
			if ($e==null) { $this->entity = -1; return $this; } //Cette id n'existe pas en BDD
			
			$this->entity = $e;
			foreach ($this->refs as $ref) {
				$this->setRef($ref);
			}
			$this->entity = clone $e; //Prend ton envol !
			return $this;
		}

		public function getEntity() {
			return ($this->entity == null) ? null : clone $this->entity;
		}

		public function getRefs($att) {
			return (isset($this->refs[$att]) && $this->entity != -1) ? $this->refs[$att] : -1;
		}

		private function setRef(RefSQL $ref) {
			if (is_a($this->entity, "Entity")) {$this->entity->set($ref->getAtt(), $ref);}
			return $this;	
		}
	}
?>