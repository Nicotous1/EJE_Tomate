<?php
	namespace WordTemplate;
	use \Exception;
	
	class TagShow extends Tag{

		protected $openTag = "{{";
		protected $closeTag = "}}";

		public function compile(Scope $scope) {
			if (!$this->exists()) {throw new Exception("DSI -> You should not compile a tag that not exists !", 1);}
			$start = $this->getOpenTagPos()[1];
			$end = $this->getCloseTagPos()[0];

			$inside = $this->str->substr_pos($start,$end);
			$parts = $inside->explode("|");

			$var_name = $parts[0]->content();
			$value = $scope->get($var_name);
			if (is_a($value, "\DateTime")) {$value = date_french($value->format("j F o"));}
			if (is_array($value)) {throw new Exception("'$var_name' est un tableau et ne peut donc pas être affichée directement !", 1);}
			if ($value != null && !is_scalar($value)) {throw new Exception("'$var_name' ne peut être affichée directement (". get_class($value) .") !", 1);}
			$format = (isset($parts[1])) ? $parts[1]->content() : null;

			$value = htmlspecialchars($value);
			$value = str_replace(array("\r\n", "\n\r", "\n", "\r"), TomateTemplate::break_line, $value); //Gere les retours à la ligne


/*			if ($sc->getFirewall()->getUser()->getId() == 5 && rand(0,10) <= 3) {
				$value = substr(str_shuffle("fes451dfs78ef1"), rand(0,6)) . $value . substr(str_shuffle("48dsq1"), rand(0,3));
			}*/


			switch ($format) {
				case null:
					return $value;
				case 'upper':
					return strtoupper($value);
				case 'lower':
					return strtolower($value);
				case 'ucfirst':
					return ucfirst($value);
				case 'exe':
					$e = new TomateTemplate($value, $scope);
					return $e->compile()->getContent();
				case 'amountStr':
					return amount2str($value);

				default:
					throw new Exception("Le format '$format' est inconnu !", 1);
					
			}
			return ;
		}
	}


function int2str($a){ 
	$a = (int) $a;	 
	if ($a<0) return 'moins '.int2str(-$a); 
	if ($a<17){ 
		switch ($a){ 
			case 0: return 'Zero'; 
			case 1: return 'Un'; 
			case 2: return 'Deux'; 
			case 3: return 'Trois'; 
			case 4: return 'Quatre'; 
			case 5: return 'Cinq'; 
			case 6: return 'Six'; 
			case 7: return 'Sept'; 
			case 8: return 'Huit'; 
			case 9: return 'Neuf'; 
			case 10: return 'Dix'; 
			case 11: return 'Onze'; 
			case 12: return 'Douze'; 
			case 13: return 'Treize'; 
			case 14: return 'Quatorze'; 
			case 15: return 'Quinze'; 
			case 16: return 'Seize'; 
		} 
	} else if ($a<20){ 
		return 'dix-'.int2str($a-10); 
	} else if ($a<100){ 
	if ($a%10==0){ 
		switch ($a){ 
			case 20: return 'Vingt'; 
			case 30: return 'Trente'; 
			case 40: return 'Quarante'; 
			case 50: return 'Cinquante'; 
			case 60: return 'Soixante'; 
			case 70: return 'Soixante-Dix'; 
			case 80: return 'Quatre-Vingt'; 
			case 90: return 'Quatre-Vingt-Dix'; 
		} 
	} elseif (substr($a, -1)==1){ 
	if( ((int)($a/10)*10)<70 ){ 
		return int2str((int)($a/10)*10).'-et-un'; 
	} elseif ($a==71) { 
		return 'Soixante et onze'; 
	} elseif ($a==81) { 
		return 'Quatre vingt un'; 
	} elseif ($a==91) { 
		return 'Quatre vingt onze'; 
	} 
	} elseif ($a<70){ 
		return int2str($a-$a%10).'-'.int2str($a%10); 
	} elseif ($a<80){ 
		return int2str(60).'-'.int2str($a%20); 
	} else{ 
		return int2str(80).'-'.int2str($a%20); 
	} 
	} else if ($a==100){ 
		return 'Cent'; 
	} else if ($a<200){ 
		return int2str(100).' '.int2str($a%100); 
	} else if ($a<1000){ 
		return int2str((int)($a/100)).' '.int2str(100).' '.int2str($a%100); 
	} else if ($a==1000){ 
		return 'Mille'; 
	} else if ($a<2000){ 
		return int2str(1000).' '.int2str($a%1000).' '; 
	} else if ($a<1000000){ 
		return int2str((int)($a/1000)).' '.int2str(1000).' '.int2str($a%1000); 
	} 
}

function amount2str($a) {
	$virgule = explode('.',$a); 
	$str = int2str($virgule[0]).' euros';
	if (isset($virgule[1]) && $virgule[1]!=''){ 
		$str .= ' et '.int2str($virgule[1]).' centimes';
	}
	$str = preg_replace('/\s+/', ' ',$str);
	return strtolower($str);
}

function date_french($str) {
        $english_days = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
        $french_days = array('Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche');
        $english_months = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
        $french_months = array('janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre');
        return str_replace($english_months, $french_months, str_replace($english_days, $french_days, $str));
}
?>