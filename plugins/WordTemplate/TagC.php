<?php
	namespace WordTemplate;
	use \Exception;
	
	class TagC extends Tag {

		protected $openTag = "{c{";
		protected $closeTag = "}c}";

		public function compile(Scope $scope) {
			if (!$this->exists()) {throw new Exception("DSI -> You should not compile a tag that not exists !", 1);}
			$start = $this->getOpenTagPos()[1];
			$end = $this->getCloseTagPos()[0];

			$inside = $this->str->substr_pos($start,$end);
			$parts = $inside->explode("}c{");

			if (count($parts) != 5) {throw new Exception("Wohh ! On dirait que vous avez oublié un sexe :O {c{ variable }c{ masculin }c{ feminin }c{ masculinS }c{ femininS }c}", 1);}

			$var_name = $parts[0]->content();
			$value = $scope->get($var_name);

			$sexe = $this->getSexe($value, $var_name);
			return $parts[$sexe]->source();
		}

		private function getSexe($entities, $var_name) {
			if (!is_array($entities)) {$entities = array($entities);}

			$n = count($entities);
			if ($n == 0) {throw new Exception("'$var_name' est un tableau vide, il n'a donc pas de sexe !", 1);}
			$n_gars = 0; $n_fille = 0;
			foreach ($entities as $e) {
				if (is_a($e, "Core\PDO\Entity\Entity") && !$e->getEntitySQL()->exist_att("sexe")) {
					switch ($e->get("sexe")) {
						case 1:
							return ($n == 1) ? 1 : 3; // masculin : masculinS

						case 2:
							continue;
						
						default:
							throw new Exception("Certains élements de '$var_name' n'ont pas de sexe défini !", 1);
					}
				}
			}
			return ($n == 1) ? 2 : 4; // feminin : femininS
		}
	}
?>