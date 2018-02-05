<?php
	namespace WordTemplate;
	use \Exception;
	
	class TagC extends Tag {

		protected $openTag = "{c{";
		protected $closeTag = "}c}";
		protected $midleTag = "}c{";

		public function compile(Scope $scope) {
			$start = $this->getOpenTagPos()[1];
			$end = $this->getCloseTagPos()[0];

			$inside = $this->getInside();

			$parts = $this->explode($this->midleTag);

			if (count($parts) != 5) {$this->error("Wohh ! On dirait que vous avez oublié un sexe :O {c{ variable }c{ masculin }c{ feminin }c{ masculinS }c{ femininS }c}.");}

			$var_name = $parts[0]->content();
			$value = $scope->get($var_name);

			$sexe = $this->getSexe($value, $var_name);

			return $this->render($parts[$sexe], $scope);
		}

		private function getSexe($entities, $var_name) {
			if (!is_array($entities)) {$entities = array($entities);}

			$n = count($entities);
			if ($n == 0) {$this->error("'$var_name' est un tableau vide, il n'a donc pas de sexe !");}
			$n_gars = 0; $n_fille = 0;
			foreach ($entities as $e) {
				if (is_a($e, "Core\PDO\Entity\Entity") && !$e->getEntitySQL()->exist_att("sexe")) {
					switch ($e->get("sexe")) {
						case 1:
							return ($n == 1) ? 1 : 3; // masculin : masculinS

						case 2:
							continue;
						
						default:
							$this->error("Certains élements de '$var_name' n'ont pas de sexe défini !");
					}
				}
			}
			return ($n == 1) ? 2 : 4; // feminin : femininS
		}
	}
?>