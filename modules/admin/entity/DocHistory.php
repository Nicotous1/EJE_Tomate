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
				$num = (int) substr($name, $pos + 1);
				if ($num < 1) {
					throw new Exception("Erreur du DocHistory pour la référence '$name' : Le numéro de la référence doit être supérieur à 1 et être un nombre et pas '$type_var'.", 1);
				}

				$docs = $this->etude->getDocTypes($type_var); // index by n
				$docs = array_values($docs); //order by n (without index -> only values)

				$n = count($docs);
				if ($n < 1) {
					throw new Exception("Erreur du DocHistory pour la référence '$name' : L'étude ne contient aucun documents du type '$type_var', êtes vous sur que le type '$type_var' existe ?", 1);
				}
				if ($n < $num) { // strict est important !
					throw new Exception("Erreur du DocHistory pour la référence '$name' : L'étude ne contient que $n documents du type '$type_var', le document n°$num n'existe donc pas !", 1);
				}
				return $docs[$n - $num];	
			}
		}
	}
?>