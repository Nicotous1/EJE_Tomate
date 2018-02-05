<?php 
	namespace WordTemplate;
	use \Exception;
	
	class TagIf extends Tag {

		protected $openTag = "{if[";
		protected $closeTag = "]if}";
		
		protected $thenTag = "]then[";
		protected $elseTag = "]else[";
		
		public function compile(Scope $scope) {
			$start = $this->getOpenTagPos();
			
			$then = $this->find($this->thenTag);
			if ($then === false) {$this->error("Il manque '$this->thenTag' à un TagIf.");}

			// Clean the param string
			$param_str = $this->str->substr_pos($start[1],$then[0])->content();
			$param_str = str_replace( chr( 194 ) . chr( 160 ), ' ', $param_str );
			$param_str = trim($param_str);
			$param_str = preg_replace('/\s+/', ' ',$param_str);

			$bool = $this->eval($param_str, $scope);

			$else = $this->find($this->elseTag);
			$end = $this->getCloseTagPos();
			if ($else === False) {
				$true = $this->str->substr_pos($then[1],$end[0])->content();
				$false = null;
			} else {
				$true = $this->str->substr_pos($then[1],$else[0])->content();
				$false = $this->str->substr_pos($else[1],$end[0])->content();
			}

			$res = ($bool) ? $true : $false;

			return $this->render($res, $scope);
		}

		protected function eval($param_str, Scope $scope) {
			$param_str = str_replace("|", "", $param_str); // Remove old format '|'
			$params = explode(" ", $param_str); // Ca ne marche pas avec les strings -> interdire ?

			$n = count($params);
			if ($n < 1 || 3 < $n) {
				$this->error("La condition n'est pas correcte !");
			}

			$a_str = $params[0];
			$a = (is_numeric($a_str)) ? floatval($a_str) : $scope->get($a_str);

			$b_str = ($n > 2) ? $params[2] : False;

			$cond_str = ($n > 1) ? $params[1] : 'exist';
			$inv = (substr($cond_str, 0, 1) == "!");
			if ($inv) {$cond_str = substr($cond_str, 1);}
			
			if ($b_str === False) {
				switch ($cond_str) {
					case 'empty':
						$res = empty($a); break;
					case 'exist':
						$res = !empty($a); break;
					case 'multiple':
						$res = count($a) > 1; break;
					case 'alone':
						$res = count($a) == 1; break;
					default:
						$this->error("La condition '$cond_str' n'existe pas.");	
				}				
			}
			else {
				$b = (is_numeric($b_str)) ? floatval($b_str) : $scope->get($b_str);
				
				$a = $this->check($a);
				$b = $this->check($b);
				
				switch ($cond_str) {
					case '=':
						$res = ($a == $b); break;
					case '&lt;':
						$res = ($a < $b); break;
					case '&lt;=':
						$res = ($a <= $b); break;
					case '&gt;':
						$res = ($a > $b); break;

					default:
						$this->error("La condition '$cond_str' n'existe pas pour comparer deux éléments.");	
				}
			}


			return ($inv) ? !$res : $res;
		}

		protected function check($a) {
			if (is_numeric($a)) {
				return floatval($a);
			} else {
				$this->error("'$a_str' n'est pas un nombre ('".gettype($a)."'). Vous ne pouvez comparer que des entiers.");
			}
		}
	} 
?>