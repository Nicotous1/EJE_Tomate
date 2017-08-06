<?php
namespace Core\PDO\Entity;

	class RefSQLData
	{
		private $e_dones;
		private $e_ids;
		private $owner;
		private $attSQL;
		
		public function __construct(AttSQL $attSQL, $x)
		{
			if (empty($x)) {throw new Exception("A RefSQL data can't be empty -> how could it get in the BDD else ?", 1);}

			$this->attSQL = $attSQL;

			$class = $attSQL->getClass();
			$parent = $attSQL->getParent()->getClass();

			$this->e_dones = array();
			$this->e_ids = array();

			if (is_numeric($x)) {
				$this->e_ids[] = $x;
			}
			if (is_a($x, $parent)) {
				$this->owner = $x;
			}
			
			if (is_array($x)) {
				foreach ($x as $p) {
					if (is_numeric($p)) {
						$this->e_ids[] = $p;
					}
					if (is_a($p, "Core\PDO\Entity\Entity")) {
						$this->e_dones[] = $p;
					}
					if (is_array($p)) {
						$this->e_ids[] = new $class($p);
					}
				}
			}
			//var_dump($this);
		}

		public function getIds() {
			return $this->e_ids;
		}

		public function getId() {
			return (isset($this->e_ids[0])) ? $this->e_ids[0] : null;
		}

		public function getDones() {
			return $this->e_dones;
		}

		public function getOwnerId() {
			return $this->owner->getId();
		}

		public function getOwner() {
			return $this->owner;
		}

		public function isRaw() {
			return (!$this->owner == null);
		}
	}
?>