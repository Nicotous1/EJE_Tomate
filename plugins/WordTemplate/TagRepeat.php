<?php 
	namespace WordTemplate;
	use \Exception;
	
	class TagRepeat extends Tag {

		protected $openTag = "{for[";
		protected $closeTag = "]for}";
		
		protected $midTag = "]do[";
		
		public function compile(Scope $scope) {
			$start = $this->getOpenTagPos();
			$mid = $this->find($this->midTag);
			if ($mid === false) {$this->error("Il manque '$this->midTag'.");}
			$end = $this->getCloseTagPos();

			$param = $this->str->substr_pos($start[1],$mid[0]);
			$template = $this->str->substr_pos($mid[1],$end[0]);

			$parts = $param->explode("|");

			//Traitement des paramètres de boucle
			$param_boucle = $parts[0]->content();
			$param_boucle = explode(" in ", $param_boucle);
			if (count($param_boucle) != 2) { $this->error("N'est pas de la forme : '$this->openTag item in array $this->midTag ... $this->closeTag' !\nVérifier que vous avez bien écrit 'in' en minuscule et l'avait séparé d'espace.", 1);}
			$item_name = $param_boucle[0];
			$array_ref = $param_boucle[1];

			//Traitement du format
			$format = (isset($parts[1])) ? $parts[1]->content() : null;
			

			//Génération
			$scope->addLevel();
			$res = null; $i = 1; $array = $scope->get($array_ref); $n = count($array);
			foreach ($array as $index => $x) {
				$scope->add("i", $i)
					  ->add("index", $index)
					  ->add($item_name, $x);
				$res .= $this->render($template, $scope);

				//Traitement des formats
				switch ($format) {
					case 'comma':
						if ($i < $n - 1) {$res .= ", ";}
						if ($i == ($n -1)) {$res .= " et ";}
						break;
				}

				$i++;
			}
			$scope->dropLevel();
			return $res;
		}
	}
?>