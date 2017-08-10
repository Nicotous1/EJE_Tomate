<?php
	namespace Admin\Entity;
	use Core\PDO\Entity\Entity;
	use Core\PDO\Entity\AttSQL;
	use Core\Routeur;
	use \Datetime;
	
	class DocEtude extends Entity {

		protected static function get_array_EntitySQL() {
			return array(
				"atts" => array(
					array("att" => "n", "type" => AttSQL::TYPE_INT),
					array("att" => "type", "type" => AttSQL::TYPE_DREF, "class" => "DocType"), //CAN BE NULL
					array("att" => "doc", "type" => AttSQL::TYPE_DREF, "class" => "Document"),
					array("att" => "etude", "type" => AttSQL::TYPE_DREF, "class" => "Etude"),
					array("att" => "date_created", "type" => AttSQL::TYPE_DATE),
			));
		}

		public function toArray() {
			$r = parent::toArray();
			$r["type"] = $this->get_Ids("type");
			$r["nom"] = $this->get("nom");
			$r["etude"] = $this->get_Ids("etude");
			$r["doc"] = $this->get("doc");
			$r["link"] = $this->get("link");
			$r["ref"] = $this->get("ref");
			return $r;
		}

		public function var_defaults() {
			return array("date_created" => new DateTime());
		}

		public function getLink() {
			return Routeur::getInstance()->getUrlFor("AjaxDownloadDocEtude", array("id" => $this->get("id")));			
		}

		public function getNom() {
			$type = $this->get("type");
			if ($type === null) {
				return $this->get("doc")->get("nom");
			} else {
				return $type->get("nom") . " n°" . $this->get("n");
			}
			return $this;
		}

		public function getN() {
			if ($this->n < 1 && $this->get("type") !== null) {
				$c = new DocEtudeController();
				$n = $c->countOfTypeWithIds($this->get_Ids("etude"), $this->get_Ids("type"));
				$this->n = $n + 1;
			}
			return $this->n;
		}

		public function getRef() {
			if ($this->get("n") > 0 && $this->get("type") !== null && $this->get("etude") != null) {
				return $this->get("etude")->get("numero") . "-" . $this->get("type")->get("var_name") . "-" . $this->get("n");
			} else {
				return $this->getNom();
			}
		}
	}
?>