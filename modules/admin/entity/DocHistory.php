<?php 
	namespace Admin\Entity;
	use \Exception;
	use Core\PDO\EntityPDO;
	
	class DocHistory {
		private $etude;

		public function __construct(Etude $e) {
			$this->etude = $e;
		}

		public function get($name) {
			$pos = strrpos($name,"_");
			if ($pos === false) {
				return $this->etude->getDocTypes($name);
			} else {
				$type_var = substr($name, 0, $pos);
				$pdo = new EntityPDO();
				$type = $pdo->get("Admin\Entity\DocType", array(
					"#s.var_name = :", $type_var), 1);
				if ($type === null) {throw new Exception("Erreur du DocHistory pour la référence '$name' : Le type '$type_var' n'existe pas !", 1);}


				$num = (int) substr($name, $pos + 1);
				if ($num < 1) {
					throw new Exception("Erreur du DocHistory pour la référence '$name' : Le numéro de la référence doit être supérieur à 1 et être un nombre et pas '$type_var'.", 1);
				}

				$docs = $this->etude->getDocTypes($type_var); // index by n
				$docs = array_values($docs); //order by n (without index -> only values)

				$n = count($docs);
				if (isset($docs[$n - $num])) {
					return $docs[$n - $num];
				} else {
					return new DocEtude(array("type" => $type, "etude" => $this->etude, "n" => 1));
				}
			}
		}
	}
?>