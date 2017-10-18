<?php
	namespace Auth\Entity;

	use Core\PDO\Entity\AttSQL;
	use Core\PDO\Entity\Entity;

	require_once "plugins/Random/random.php";

	class User extends Entity {

		protected static function get_array_EntitySQL() {
			return array(
				"table" => "etudiant",
				"atts" => array(
					array("att" => "mail", "type" => AttSQL::TYPE_STR),
					array("att" => "hash", "type" => AttSQL::TYPE_STR),
					array("att" => "level", "type" => AttSQL::TYPE_INT),
					array("att" => "date_signin", "type" => AttSQL::TYPE_DATE),
					array("att" => "activated", "type" => AttSQL::TYPE_INT),
					array("att" => "activation", "type" => AttSQL::TYPE_STR),
					array("att" => "nom", "type" => AttSQL::TYPE_STR),
					array("att" => "prenom", "type" => AttSQL::TYPE_STR),
					array("att" => "adresse", "type" => AttSQL::TYPE_STR),
					array("att" => "code_postal", "type" => AttSQL::TYPE_STR),
					array("att" => "ville", "type" => AttSQL::TYPE_STR),
					array("att" => "fixe", "type" => AttSQL::TYPE_STR),
					array("att" => "mobile", "type" => AttSQL::TYPE_STR),
					array("att" => "date_birth", "type" => AttSQL::TYPE_DATE),
					array("att" => "nationality", "type" => AttSQL::TYPE_STR),
					array("att" => "secu", "type" => AttSQL::TYPE_STR),

					// Powers
					array("att" => "can_delete_doc", "type" => AttSQL::TYPE_BOOL),

					array("att" => "annee", "type" => AttSQL::TYPE_ARRAY, "list" => User::$anneeArray),
					array("att" => "titre", "type" => AttSQL::TYPE_ARRAY, "list" => User::$titreArray),

					array("att" => "cv", "type" => AttSQL::TYPE_DREF, "class" => "Admin\Entity\Document"),
					
					array("att" => "work_requests", "type" => AttSQL::TYPE_IREF, "class" => "Admin\Entity\WorkRequest", "att_ref" => "etudiant"),
				),
			);
		}

		protected $id;
		protected $mail;
		protected $hash;
		protected $password;
		protected $level;	
		protected $date_signin;
		protected $activated;
		protected $activation;

		protected $titre;
		protected $nom;
		protected $prenom;
		protected $adresse;
		protected $fixe;
		protected $mobile;
		protected $date_birth; //date must be first word for angular handling
		protected $nationalite;
		protected $secu;
		protected $annee;

		protected $cv;



		public static $anneeArray = array(
			array("id" => 1, "name" => "1A"),
			array("id" => 2, "name" => "2A"),
			array("id" => 3, "name" => "2AD"),
			array("id" => 4, "name" => "3A"),
		);

		public static $titreArray = array(
			array("id" => 1, "titre" => "Mr", "long" => "Monsieur"),
			array("id" => 2, "titre" => "Mme", "long" => "Madame"),
		);

		public function getSexe() {
			$titre = $this->get("titre");
			return (is_array($titre)) ? $titre["id"] : 0; //gere le cas du non défini (null)
		}

		public function isAllowed($level) {
			return ($this->level >= $level);
		}
		
		public function var_defaults() {
			return array(
				"date_signin" => new \DateTime(),
				"level" => 0, //LEVEL VISITEUR
				"activated" => true,
				"can_delete_doc" => false,
			);
		}

		private function loadHash() {
			$this->hash = password_hash($this->password, PASSWORD_DEFAULT);
			return $this;
		}

		public function getHash() {
			return $this->hash;
		}

		public function getActivation() {
			if ($this->activation == null) { $this->activation = bin2hex(random_bytes(25)); }
			return $this->activation;
		}

		public function getPseudo() {
			if ($this->mail != null && $this->pseudo == null) {
				$this->pseudo = explode("@", $this->mail)[0];
			}
			return $this->pseudo;
		}

		public function check() {
			return password_verify($this->password, $this->hash);
		}

		public function isValid() {
			return ($this->get("hash") != null && $this->get("mail") != null && filter_var($this->get("mail"), FILTER_VALIDATE_EMAIL) && $this->get("nom") != null && $this->get("prenom") != null);
		}

		public function isSearchable() {
			return ($this->id != null || $this->mail != null);
		}

		public function isVisiteur() {
			return $this->id == null;
		}

		public function setPassword($p, $hash_update = True) {
			$this->password = $p;
			if ($hash_update) {
				$this->loadHash();
			}
			return $this;
		}

		public function getPassword() {
			return $this->password;
		}

		public function setMail($p) {
			$this->mail = (filter_var($p, FILTER_VALIDATE_EMAIL)) ? strtolower(explode("@", $p)[0]) . "@ensae.fr" : null;
			return $this;
		}

		public function isAdmin() {
			return ($this->level >= 2);
		}

		public function toArray() {
			$r = parent::toArray();
			$r["cv"] = $this->get("cv");
			unset($r["hash"]);
			unset($r["activation"]);
			return $r;
		}

/*
	Better looking name
*/
		public function setNom($str) {
			$this->nom = ucwords(strtolower($str), " \t\r\n\f\v-");
			return $this;
		}

		public function setPrenom($str) {
			$this->prenom = ucwords(strtolower($str), " \t\r\n\f\v-");
			return $this;
		}
	}
?>