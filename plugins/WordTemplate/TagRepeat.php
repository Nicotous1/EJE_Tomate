<?php 
	namespace WordTemplate;
	use \Exception;
	
	class TagRepeat extends Tag {

		protected $openTag = "{for[";
		protected $closeTag = "]for}";
		
		protected $midTag = "]do[";
		protected $midTagPos;

		public function getMidTagPos() {
			if ($this->midTagPos === null) {
				$start = $this->getOpenTagPos();
				if ($start === false) {throw new Exception("DSI -> Should not compile what doesn't exist !", 1);}
				$this->midTagPos = $this->str->strpos($this->midTag, $start[1]);
			}
			return $this->midTagPos;
		}
		
		public function compile(Scope $scope) {
			if (!$this->exists()) {throw new Exception("DSI -> You should not compile a tag that not exists !", 1);}
			$start = $this->getOpenTagPos();
			$mid = $this->getMidTagPos();
			if ($mid === false) {throw new Exception("Il manque '$this->midTag' à un TagRepeat près de '" . $this->str->extract($start[0]) . "' !", 1);}
			$end = $this->getCloseTagPos();

			$param = $this->str->substr_pos($start[1],$mid[0]);
			$template = $this->str->substr_pos($mid[1],$end[0]);

			$parts = $param->explode("|");

			//Traitement des paramètres de boucle
			$param_boucle = $parts[0]->content();
			$param_boucle = explode(" in ", $param_boucle);
			if (count($param_boucle) != 2) { throw new Exception("Un tag de répétition doit être de la forme '$this->openTag item in array $this->closeTag' !\nVérifier que vous avez bien écrit 'in' en minuscule et l'avait séparé d'espace.", 1);}
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
				$tomateTemplate = new TomateTemplate($template, $scope);
				$res .= $tomateTemplate->compile()->getContent();

				//Traitement des formats
				switch ($format) {
					case 'comma':
						if ($i < $n - 1) {$res .= ", ";}
						if ($i == ($n -1)) {$res .= TomateTemplate::white_space . " et " . TomateTemplate::white_space;}
						break;
				}

				$i++;
			}
			$scope->dropLevel();
			return $res;
		}
	}
?>