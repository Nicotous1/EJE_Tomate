<?php
	namespace Admin\Entity;
	use Core\PDO\PDO;
	use Core\PDO\Request;


	class DocEtudeController {

		public function __construct() {
			$this->pdo = PDO::getInstance();
			$this->strct = DocEtude::getEntitySQL();
		}

		public function nextN(Docetude $d) {
			$r = new Request("SELECT MAX(#n) AS n FROM #^ WHERE #type~ AND #etude~", $d);
			$res = $r->fetch();
			$n = (int) $res["n"];
			return $n + 1;
		}

	}
?>