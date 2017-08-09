<?php
	namespace Admin\Entity;
	use Core\PDO\Entity\Entity;
	use Core\PDO\Entity\AttSQL;

	class Etape extends Entity {

		protected static function get_array_EntitySQL() {
			return array(
				"atts" => array(
					array("att" => "nom", "type" => AttSQL::TYPE_STR),
					array("att" => "details", "type" => AttSQL::TYPE_STR),
					array("att" => "date_start", "type" => AttSQL::TYPE_DATE),
					array("att" => "date_end", "type" => AttSQL::TYPE_DATE),
					array("att" => "n", "type" => AttSQL::TYPE_INT),

					array("att" => "etude", "type" => AttSQL::TYPE_DREF, "class"=>"Etude", "null" => false),

					array("att" => "sEtapes", "type" => AttSQL::TYPE_IREF, "class"=>"sEtape", "att_ref" => "etape"),
				),
			);
		}

		public function toArray() {
			$r = parent::toArray();
			$r["sEtapes"] = $this->get("sEtapes");
			return $r;
		}

		public function var_defaults() {
			return array("date_start" => new DateTime(), "date_end" => new DateTime());
		}

		//Fusionne les étapes avec même étudiants -> optimise
		public function optimizeSEtapes() {
			$etudiants = array();
			foreach ($this->get("sEtapes") as $sEtape) {
				if (!$sEtape->isValid()) {continue;}
				$id = $sEtape->get_Ids("etudiant");
				if (isset($etudiants[$id])) {
					$p = $etudiants[$id];
					$p->set("jeh", $sEtape->get("jeh") + $p->get("jeh"));
				} else {
					$etudiants[$id] = $sEtape;
				}
			}
			return $this->set("sEtapes", array_values($etudiants));
		}

		//Variable Quali
		public function getN_jeh() {
			$n = 0;
			foreach ($this->get("sEtapes") as $x) {
				$n += $x->get("jeh");
			}
			return $n;
		}

		public function getPrix_ht() {
			return $this->get("n_jeh")*$this->get("etude")->get("p_jeh");
		}

	}
?>