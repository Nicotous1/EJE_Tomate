<?php
	namespace Admin\Entity;
	
	use Core\PDO\Entity\EntitySQLHandler;
	use Core\PDO\Entity\AttSQL;
	use Core\PDO\Entity\Entity;

	class WorkRequest extends Entity {

		public static $statutArray = array(
			0 => array("id" => 0, "name" => "En attente"),
			1 => array("id" => 1, "name" => "Refusée"),
			2 => array("id" => 2, "name" => "Acceptée"),
		);

		static protected function get_array_EntitySQL() {
			return array(
				"class" => "Admin\Entity\WorkRequest",
				"table" => "work_request",
				"atts" => array(
					array("att" => "etude", "type" => AttSQL::TYPE_DREF, "class" => "Admin\Entity\Etude"),
					array("att" => "lettre", "type" => AttSQL::TYPE_DREF, "class" => "Admin\Entity\Document"),
					array("att" => "etudiant", "type" => AttSQL::TYPE_DREF, "class" => "Auth\Entity\User"),
					array("att" => "statut", "type" => AttSQL::TYPE_ARRAY, "list" => WorkRequest::$statutArray),
					array("att" => "date_created", "type" => AttSQL::TYPE_DATE),
			));
		}


		public function var_defaults() {
			return array(
				"date_created" => new DateTime(),
				"statut" => 0,
			);
		}

		public function toArray() {
			$r = parent::toArray();
			$r["etudiant"] = $this->get("etudiant");
			$r["lettre"] = $this->get("lettre");
			$r["zip_url"] = $this->get("ZipUrl");
			return $r;
		}

		public function getZipUrl() {
			$sc = new ServiceController();
			return $sc->getRouteur()->getUrlFor("AjaxDownloadWorkRequest", array("id" => $this->get("id")));
		}

		public function isWaiting() {
			return ($this->get("statut")["id"] == 0);
		}

		public function isAccepted() {
			return ($this->get("statut")["id"] == 2);
		}

		public function isRefused() {
			return ($this->get("statut")["id"] == 1);
		}
	}	
?>