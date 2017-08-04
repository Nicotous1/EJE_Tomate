<?php
	class DocEtudeController {

		public function __construct() {
			$this->SC = new ServiceController();
			$this->pdo = $this->SC->getPDO();
			$handler = new EntitySQLHandler();
			$this->strct = $handler->get("DocEtude");
		}

		public function countOfTypeWithIds($e_id, $t_id) {
			$type_col = $this->strct->getAtt("type")->getCol();
			$etude_col = $this->strct->getAtt("etude")->getCol();
			$table = $this->strct->getTable();
			
			$r = $this->SC->getPDO()->prepare("SELECT COUNT(*) FROM $table WHERE $type_col = :type AND $etude_col = :etude");
			$r->bindValue(':type', $t_id, PDO::PARAM_INT);
			$r->bindValue(':etude', $e_id, PDO::PARAM_INT);
			$res = $r->execute();
			return (int) $r->fetch(PDO::FETCH_NUM)[0];
		}

	}
?>