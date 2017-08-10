<?php
	namespace Admin\Entity;
	use Core\PDO\PDO;

	class DocEtudeController {

		public function __construct() {
			$this->pdo = PDO::getInstance();
			$this->strct = DocEtude::getEntitySQL();
		}

		public function countOfTypeWithIds($e_id, $t_id) {
			$type_col = $this->strct->getAtt("type")->getCol();
			$etude_col = $this->strct->getAtt("etude")->getCol();
			$table = $this->strct->getTable();
			
			$r = $this->pdo->prepare("SELECT COUNT(*) FROM $table WHERE $type_col = :type AND $etude_col = :etude");
			$r->bindValue(':type', $t_id, PDO::PARAM_INT);
			$r->bindValue(':etude', $e_id, PDO::PARAM_INT);
			$res = $r->execute();
			return (int) $r->fetch(PDO::FETCH_NUM)[0];
		}

	}
?>