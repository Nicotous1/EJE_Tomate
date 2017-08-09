<?php
	namespace Admin\Entity;
	use Core\PDO\Entity\Entity;
	use Core\PDO\Entity\AttSQL;

	use Core\Routeur;
	use Core\PDO\PDO;
	use \Datetime;

	class Etude extends Entity {
		private $dochistory;
		private $etudiants;

		//WARNING ON ID FOR JAVASCRIPT MUST START AT 0 -> COHERENT 
		public static $provenanceArray = array(
			array("id" => 1, "name" => "Email", "long" => "Email_long"),
			array("id" => 2, "name" => "Prospection", "long" => "prosp_long"),
		);

		public static $statutArray = array(
			array("id" => 0, "name" => "Brouillon"),
			array("id" => 1, "name" => "En attente"),
			array("id" => 2, "name" => "Recrutement"),
			array("id" => 3, "name" => "Selection"),
			array("id" => 4, "name" => "En cours"),
			array("id" => 5, "name" => "Cloturée"),
			array("id" => 6, "name" => "Morte"),
		);

		public static $lieuArray = array(
			array("id" => 1, "short" => "A l'ENSAE", "long" => "en_long"),
			array("id" => 2, "short" => "Chez le client", "long" => "client_long"),
		);

		protected static function get_array_EntitySQL() {
			return array(
				"atts" => array(
					array("att" => "nom", "type" => AttSQL::TYPE_STR),
					array("att" => "numero", "type" => AttSQL::TYPE_INT),
					array("att" => "p_jeh", "type" => AttSQL::TYPE_INT),
					array("att" => "pseudo", "type" => AttSQL::TYPE_STR),
					array("att" => "bdd", "type" => AttSQL::TYPE_STR),
					array("att" => "specifications", "type" => AttSQL::TYPE_STR),
					array("att" => "but", "type" => AttSQL::TYPE_STR),
					array("att" => "competences", "type" => AttSQL::TYPE_STR),
					array("att" => "context", "type" => AttSQL::TYPE_STR),
					array("att" => "domaines", "type" => AttSQL::TYPE_STR),
					array("att" => "pub", "type" => AttSQL::TYPE_STR),
					array("att" => "pub_titre", "type" => AttSQL::TYPE_STR),
					array("att" => "date_created", "type" => AttSQL::TYPE_DATE),
					array("att" => "date_modified", "type" => AttSQL::TYPE_DATE),
					array("att" => "date_end_recrute", "type" => AttSQL::TYPE_DATE),
					array("att" => "locked", "type" => AttSQL::TYPE_BOOL),

					array("att" => "fee", "type" => AttSQL::TYPE_FLOAT),
					array("att" => "break_jeh", "type" => AttSQL::TYPE_INT),
					array("att" => "break_fee", "type" => AttSQL::TYPE_FLOAT),
					
					array("att" => "provenance", "type" => AttSQL::TYPE_ARRAY, "list" => self::$provenanceArray),
					array("att" => "statut", "type" => AttSQL::TYPE_ARRAY, "list" => self::$statutArray),
					array("att" => "lieu", "type" => AttSQL::TYPE_ARRAY, "list" => self::$lieuArray),

					array("att" => "client", "type" => AttSQL::TYPE_DREF, "class" => "Client"),
					array("att" => "facturation", "type" => AttSQL::TYPE_DREF, "class" => "Client"),
					array("att" => "signataire", "type" => AttSQL::TYPE_DREF, "class" => "Client"),
					array("att" => "entreprise", "type" => AttSQL::TYPE_DREF, "class" => "Entreprise"),
					array("att" => "child", "type" => AttSQL::TYPE_DREF, "class" => "Etude"),
					
					array("att" => "admins", "type" => AttSQL::TYPE_MREF, "class" => "Auth\Entity\User", "table" => "admin_etude", "ref_col" => "admin_id", "col" => "etude_id"),

					array("att" => "work_requests", "type" => AttSQL::TYPE_IREF, "class" => "WorkRequest", "att_ref" => "etude"),
					array("att" => "docs", "type" => AttSQL::TYPE_IREF, "class" => "DocEtude", "att_ref" => "etude"),
					array("att" => "etapes", "type" => AttSQL::TYPE_IREF, "class" => "Etape", "att_ref" => "etude"),
					array("att" => "parent", "type" => AttSQL::TYPE_IREF, "class" => "Etude", "att_ref" => "child"),
				),
			);
		}

		public function var_defaults() {
			return array(
				"date_created" => new DateTime(),
				"date_modified" => new DateTime(),
				"fee" => 0,
				"break_fee" => 0,
				"break_jeh" => 0,
				"statut" => 0,
				"locked" => false,
				"p_jeh" => 340,
			);
		}

		public function toArray() {
			$r = parent::toArray();
			if ($this->get("id") > 0) {
				$r["link"] = Routeur::getInstance()->getUrlFor("AdminEdit", array("id" => $this->get("id")));
			}
			$r["admins"] = $this->get_Ids("admins");
			return $r;
		}

		public function generateNum() {
			if ($this->get("numero") <= 0) {
				$pdo = PDO::getInstance();
				$strct = $this->getStruct();
				$r = $pdo->prepare("SELECT MAX(numero) FROM " . $strct->getTable());
				$r->execute();
				$res = $r->fetch(PDO::FETCH_NUM);
				$maxBDD = (empty($res)) ? 0 : $res[0];
				$this->numero = $maxBDD + 1;
			}
			return $this;
		}

		public function infant() {
			$child = clone $this;
			$child->set("id", null)
				  ->set("child", null)
				  ->set("locked", false)
				  ->set("admins", $this->get_Ids("admins"))
				  ->set("parent", array($this))
			;

			//Export et freeze des work_requests
			$ws = $this->get("work_requests");
			$child->set("work_requests", $ws);
			$ws_new = array();
			foreach ($ws as $w) {
				$w = clone $w;
				$w->set("id", null);
				$ws_new[] = $w;
			}
			$this->set("work_requests", $ws_new);

			//Copie des documents
			$docs = $this->get("docs");
			foreach ($docs as $i => $doc) {
				$doc = clone $doc;
				$doc->set("id", null)->set("etude", $child);
				$docs[$i] = $doc;
 			}
 			$child->set("docs", $docs);

 			//Copie des etapes
			$etapes = $this->get("etapes");
			foreach ($etapes as $i => $e) {
				$ses = $e->get("sEtapes");
				$e = clone $e;
				$e->set("id", null)->set("etude", $child);

				//Copie des sEtapes
				foreach ($ses as $j => $se) {
					$se = clone $se;
					$se->set("id", null)->set("etape", $e);
					$ses[$j] = $se;
				}
				$e->set("sEtapes", $ses);

				$etapes[$i] = $e;
 			}
 			$child->set("etapes", $etapes);

 			$this->set("child", $child)->set("locked", true);
 			return $child;
		}

		public function getLocked() {
			return ($this->locked || !empty($this->get_Ids("child")));
		}

		public function getP_jeh() {
			if ($this->p_jeh < 180) {$this->p_jeh = 180;}
			if ($this->p_jeh > 340) {$this->p_jeh = 340;}
			return $this->p_jeh;
		}

		public function isRequestable() {
			return ($this->get("statut")["id"] == 2);
			$end = $this->get("date_end_recrute");
			$now = new DateTime();
			return ($now < $end);
		}

		public function isSelectable() {
			return ($this->get("statut")["id"] == 3);
		}

		//CUSTOM FOR QUALITE
		public function getN_etape() {
			return count($this->get_Ids("etapes"));
		}

		public function getN_jeh() {
			$t = 0;
			foreach ($this->get("etapes") as $e) {
				$t += $e->get("n_jeh");
			}
			return $t;
		}

		public function getPrix_ht() {
			$p = 0;
			foreach ($this->get("etapes") as $etape) {
				$p += $etape->getN_jeh()*$this->get("p_jeh");
			}
			return $p;
		}

		public function getPrix_tot() {
			return $this->get("prix_ht") + $this->get("fee");
		}

		public function getPrix_tva() {
			return $this->get("prix_tot")*0.2;
		}

		public function getPrix_ttc() {
			return $this->get("prix_tot")*1.2;
		}

		public function getDate_start() {
			$date = null;
			foreach ($this->get("etapes") as $etape) {
				if ($date == null || ($etape->get("date_start") < $date)) {
					$date = $etape->get("date_start");
				}
			}
			return $date;
		}

		public function getDate_end() {
			$date = null;
			foreach ($this->get("etapes") as $etape) {
				if ($date == null || ($etape->get("date_end") > $date)) {
					$date = $etape->get("date_end");
				}
			}
			return $date;
		}

		public function getN_week() {
			$start = $this->get("date_start");
			$end = $this->get("date_end");
			if ($start == null || $end == null) {return null;}
			$interval = $this->get("date_start")->diff($this->get("date_end"));

			return ceil(($interval->days) / 7);			
		}



		public function getPrix_ht_break() {
			return $etape->get("break_jeh")*340;
		}

		public function getPrix_tot_break() {
			return $this->get("prix_ht_break") + $this->get("break_fee");
		}

		public function getPrix_tva_break() {
			return $this->get("prix_tot_break")*0.2;
		}

		public function getPrix_ttc_break() {
			return $this->get("prix_tot_break")*1.2;
		}

		public function getDocHistory() {
			if ($this->dochistory == null) {
				$this->dochistory = new DocHistory($this);
			}
			return $this->dochistory;
		}

		public function getEtudiants() {
			$id = $this->get("id");
			if (!($id > 0)) {return array();}

			if ($this->etudiants === null) {
				$w_s = $this->get("work_requests");
				$eds = array();
				foreach ($w_s as $w) {
					if ($w->isAccepted()) {$eds[] = $w->get_Ids("etudiant");}
				}
				$pdo = new EntityPDO();
				$eds = $pdo->get("User", array("id IN :", $eds), false);
				$this->etudiants = $eds;
			}
			return $this->etudiants;
		}
	}
?>