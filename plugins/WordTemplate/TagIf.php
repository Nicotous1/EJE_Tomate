<?php 
	namespace WordTemplate;
	use \Exception;
	
	class TagIf extends Tag {

		protected $openTag = "{if[";
		protected $closeTag = "]if}";
		
		protected $midTag = "][";
		protected $midTagPos;

		public function getMidTagPos() {
			if ($this->midTagPos === null) {
				$start = $this->getOpenTagPos();
				if ($start === false) {throw new Exception("DSI -> Should not compiled what doesn't exist !", 1);}
				$this->midTagPos = $this->str->strpos($this->midTag, $start[1]);
			}
			return $this->midTagPos;
		}
		
		public function compile(Scope $scope) {
			if (!$this->exists()) {throw new Exception("DSI -> You should not compiled a tag that not exists !", 1);}
			$start = $this->getOpenTagPos();
			$mid = $this->getMidTagPos();
			if ($mid === false) {throw new Exception("Il manque '$this->midTag' à un TagIf près de '" . $this->str->extract($start[0]) . "' !", 1);}
			$end = $this->getCloseTagPos();

			$param = $this->str->substr_pos($start[1],$mid[0])->content();
			$template = $this->str->substr_pos($mid[1],$end[0]);

			$param = str_replace( chr( 194 ) . chr( 160 ), ' ', $param );
			$param = trim($param);
			$p = strpos($param, ' ');
			$var_name = $param; $cond = 'exist'; $value = null; //Default
			if ($p !== false) {
				$var_name = substr($param, 0, $p);
				$param = trim(substr($param, $p));
				$p = strpos($param, ' ');
				if ($p === false) {
					$cond = $param;
				} else {
					$cond = substr($param, 0, $p);
					$param = trim(substr($param, $p));
					$t = preg_match("#\|(.+)\|#", $param, $m);
					if (!$t && !empty($param)) {throw new Exception("La valeur d'un TagIf doit être entourée de '|' -> '|##VALUE##|' !", 1);
					}
					$value = $t ? $m[1] : null;
				}
			}
			$var = $scope->get($var_name);
			switch ( ($cond[0] == '!') ? substr($cond, 1) : $cond ) {
				case 'empty':
					$res = empty($var); break;
				case 'exist':
					$res = !empty($var); break;
				case 'multiple':
					$res = count($var) > 1; break;
				case 'alone':
					$res = count($var) == 1; break;
				case '=':
					$res = ($var == $value); break;

				default:
					throw new Exception("La condition '$cond' n'existe pas !", 1);	
			}
			if ($cond[0] == "!") {$res = !$res;}

			$elsePos = $template->strpos($this->midTag);
			if ($elsePos !== false) {
				$ifTrue = $template->substr(0, $elsePos[0]);
				$ifFalse = $template->substr($elsePos[1]);		
			} else {
				$ifTrue = $template;
				$ifFalse = new StringPos();
			}

			$template = ($res) ? $ifTrue : $ifFalse;

			$tomateTemplate = new TomateTemplate($template, $scope);
			return $tomateTemplate->compile()->getContent();
		}
	} 
?>