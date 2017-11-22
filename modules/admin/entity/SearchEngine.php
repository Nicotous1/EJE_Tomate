<?php
	namespace Admin\Entity;
	use Core\PDO\Entity\Entity;
	use Core\PDO\EntityPDO;
	use Core\PDO\Entity\AttSQL;
	use Core\PDO\Request;
	use DateTime;

	class SearchEngine {

		public function search($txt, $n_res = 6) {
			$txt = strtolower(trim($txt));
			$es = explode(" ", $txt);
			$sum_up = array();
			foreach ($es as $e) {
				if ($e == "") {continue;}
				$e = "%" . $e . "%";
				$r = new Request("SELECT id FROM #0.^ WHERE #0.child IS NULL AND (#0.numero LIKE :1 OR LOWER(#0.nom) LIKE :1 OR LOWER(#0.pseudo) LIKE :1)", array(Etude::getEntitySQL(), $e));
				foreach ($r->fetchAll() as $res) {
					$id = $res["id"];
					if (!isset($sum_up[$id])) {$sum_up[$id] = 0;}
					$sum_up[$id] += strlen($e) - 2;
				}
			}
			$ids = $this->ids_from_sum_up($sum_up);
			$pdo = new EntityPDO();
			$res = array();
			foreach ($ids as $id) {
				$res[] = $pdo->get("Admin\Entity\Etude", $id);
			}
			return $res;
		}

		private function ids_from_sum_up($sum_up) {
			$n = count($sum_up);
			asort($sum_up);
			$keys = array_keys($sum_up);
			$ids = array();
			for ($i=$n - 1; $i >= max(0, $n-6) ; $i--) { 
				$ids[] = $keys[$i];
			}
			return $ids;
		}

	}
?>
