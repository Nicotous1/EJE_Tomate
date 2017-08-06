<?php
	use \Exception;
	use Core\Routeur;
	require_once "plugins/Random/random.php";

	$handler = new EntitySQLHandler();

	$handler->add((array(
			"class" => "Document",
			"atts" => array(
				array("att" => "nom", "type" => AttSQL::TYPE_STR),
				array("att" => "path", "type" => AttSQL::TYPE_STR),
				array("att" => "owner", "type" => AttSQL::TYPE_USER),
				array("att" => "date_created", "type" => AttSQL::TYPE_DATE),
		))))
	;	

	class Document extends Entity {

		protected $tmp_name;
		protected $type;

		const pathStorage = "uploadedFiles/";

		public function __construct($params = null, $conv = false) {
			//COMES FROM $_FILES
			if (isset($params["tmp_name"])) {
				$this->tmp_name = $params["tmp_name"];

				//Suppresion de l'extension du nom
				$nom = $params["name"];
				$pos = strrpos($nom, ".");
				$nom = ($pos === false) ? $nom : substr($nom, 0, $pos);

				$params = array(
					"nom" => $nom,
				);
			}

			unset($params["type"]); //TYPE IS ALVAYS DETERMINED BY THE FILE DIRECTLY (kind of more secured)

			parent::__construct($params, $conv);
			
			if (!file_exists($this->get("path"))) {
				throw new Exception("Le fichier n'existe pas sur le disque !", 1);
			}

		}

		public function toArray() {
			$r = parent::toArray();
			$r["link"] = $this->get("link");
			$r["fullName"] = $this->get("fullName");
			return $r;
		}

		public function var_defaults() {
			return array(
				"date_created" => new DateTime(),
				"path" => -1
			);
		}

		public function move() {
			if ($this->tmp_name == null) {return true;}
			for ($i=0; $i < 5; $i++) { 
				$randomName = bin2hex(random_bytes(10));
				$randomPath = $this::pathStorage . $randomName . "." . $this->get("ext");
				if (!file_exists($randomPath)) {
					$res = move_uploaded_file($this->tmp_name, $randomPath);
					if ($res) {
						$this->path = $randomPath;
						return true;
					} else {return false;}
				}
			}
			return false;

			//move_uploaded_file(filename, destination)
			//file_exists(filename)

		}

		public function getPath() {
			return ($this->path == -1) ? $this->tmp_name : $this->path;
		}

		public function remove() {
			$path = $this->path;
			if (is_file($path)) {
				unlink($this->path);
			}
			
			$pdo = new EntityPDO();
			$pdo->remove($this);
			return $this;
		}

		public function save() {
			if ($this->move()) {
				$pdo = new EntityPDO();
				return $pdo->save($this);
			}
			return false;
		}

		//Revérifie à chaque fois !
		public function getType() {
			switch (mime_content_type($this->get("path"))) {
				case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
					return "word";

				case 'application/pdf':
					return "pdf";
				
				default:
					return -1;
			}
		}

		public function getExt() {
			if ($this->get("type") == "word") {
				return "docx";
			} else {
				return "pdf";
			}
		}

		public function getLink() {
			return Routeur::getInstance()->getUrlFor("AjaxDownloadDoc", array("id" => $this->get("id")));			
		}

		public function isPDF() {
			return ($this->get("type") == "pdf");
		}

		public function getFullName() {
			return $this->get("nom") . "." . $this->get("ext");
		}

	}
?>