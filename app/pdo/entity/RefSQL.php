<?php
	namespace Core\PDO\Entity;
	use \Exception;
	use Core\PDO\EntityPDO;

	class RefSQL {
		protected $attSQL;
		protected $x; // Can be an id, entity owner, array of entity and id || OR null if convert done

		public function __construct(AttSQL $attSQL, $x) {
			$this->attSQL = $attSQL; //MUST BE FIRST !!
			$this->done = null;
			$this->setX($x);
			//var_dump($this);
		}

		//Must work if x = null !
		//Convert must return a past result
		public function convert() {
				if ($this->x == null) {return $this->done;}

				$pdo = new EntityPDO;
				$class = $this->attSQL->getClass();
				$x = $this->x;

				switch ($this->attSQL->getType()) {
					case AttSQL::TYPE_USER:
					case AttSQL::TYPE_DREF:
						// TO DO -> Add handle of raw x -> like just giving id of entity make alone the jointure
						$this->done = $pdo->get($class, $x->getId()); break;

					case AttSQL::TYPE_MREF:
					case AttSQL::TYPE_IREF:
						if ($x->isRaw()) {
							$this->done = $pdo->getRefs($this->attSQL, $x->getOwner());
						} else {
							$this->done = $x->getDones();
							foreach ($x->getIds() as $id) {
								$e = $pdo->get($class, $id);
								if ($e != null) {$this->done[] = $e;}
							}
						}
						break;
					default:
						throw new Exception("RefSQL can only convert REF !", 1);
				}
				$this->x = null;
				return $this->done;
		}

		public function getIds() {
			//Cas déjà fait
			if ($this->x == null) {
				if ($this->done == null) {return array();}
				$a = (is_array($this->done)) ? $this->done : array($this->done);
				$res = array();
				foreach ($a as $e) {
					$res[] = $e->getId();
				}
				return $res;
			}

			if ($this->x->isRaw()) {
				$ids = array();
				$es = $this->convert();
			} else {
				$ids = $this->x->getIds();
				$es = $this->x->getDones();
			}

			foreach ($es as $e) {
				$id = $e->getId();
				if ($id != null) {$ids[] = $id;}
			}

			return $ids;
		}

		public function getInBDD_ClassAndId() {
			if ($this->x == null) {
				if ($this->done == null) {return array();}
				return (is_array($this->done)) ? $this->done : array($this->done);
			}

			$res = array();
			if (!$this->x->isRaw()) {
				$res = $this->x->getIds();
				foreach ($this->x->getDones() as $e) {
					if ($e->inBDD()) {$res[] = $e;}
				}
			}
			return $res;
		}

		public function getId() {
			if ($this->x == null) {
				return ($this->done != null) ? $this->done->getId() : null;
			}
			return ($this->x->isRaw()) ? $this->convert()->getId() : $this->x->getId();
		}

		public function setX($x) {
			if (empty($x)) {$this->done = $x; $this->x = null;} else {
				$this->x = new RefSQLData($this->attSQL, $x);
			}
			return $this;
		}

		public function getAtt() {
			return $this->attSQL->getAtt();
		}
	}
?>