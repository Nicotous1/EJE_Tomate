<?php
	namespace WordTemplate;
	use \Exception;
	
	class TomateTemplate {
		
		private $str;
		private $scope;

		const white_space = "<w:t xml:space=\"preserve\"> </w:t>";
		const break_line = "<w:br/>";

		public function __construct($str, Scope $scope) {
			$this->str = (is_a($str, "WordTemplate\StringPos")) ? $str : new StringPos($str);
			$this->tags = array("TagShow", "TagRepeat", "TagExtend", "TagIf");
			$this->scope = $scope;
		}

		public function compile() {
			$this->str = $this->compile_rec($this->str);
			return $this;
		}

		protected function compile_rec(StringPos $str) {
			//Find first tag (position d'ouverture minimum)
			$firstTag = null;
			foreach ($this->tags as $tag) {
				$class = "WordTemplate\\" . $tag;
				$tag = new $class($str);
				$start =  $tag->getOpenTagPos();
				if ($start !== false && ($firstTag === null || $start[0] < $firstTag->getOpenTagPos()[0])) {
					$firstTag = $tag;
				}
			}

			if ($firstTag === null) {return $str->source();}

			$WZ = $firstTag->getWorkZone();

			//$b = $str->substr($WZ[0], $WZ[1]-$WZ[0]);  -> ne surtout pas prendre le milieu -> le tag est basé sur la str de départ ;)
			//var_dump($a->size() + $b->size() + $c->size() - $this->str->size());
			$a = $str->substr_pos(0, $WZ[0]);
			$b = $str->substr_pos($WZ[0], $WZ[1]);
			$c = $str->substr_pos($WZ[1]);

			return ($a->source()).($firstTag->compile($this->scope)).($this->compile_rec($c));
		}

		public function getContent() {
			return $this->str;
		}

	}
?>