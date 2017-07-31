<?php
	class StringPos {
		protected $str;

		protected $cursor;
		protected $charStr;
		protected $charPosArray;
		protected $size;


		public function __construct($str = null, $cursor = 0, $charStr = '', $charPosArray = array()) {
			$this->str = $str;
			$this->size = strlen($str);
			$this->cursor = ($cursor > 0) ? $cursor : 0;
			$this->charStr = $charStr;
			$this->charPosArray = $charPosArray;
		}

		public function readToNextChar() {
			if ($this->isFinished()) {return $this;}
			$offset = $this->cursor;
			preg_match("#^(<.*?>)*#", substr($this->str, $offset), $matches, PREG_OFFSET_CAPTURE);
			$pos = (empty($matches)) ? $offset : $matches[0][1] + $offset + strlen($matches[0][0]);
			$c = substr($this->str, $pos,1);

			if ($c !== false) {
				$this->charStr .= $c;
				$this->charPosArray[] = $pos;
			}
			$this->cursor = $pos + 1; 
			return $this;
		}

		public function readTo($pos) { //Inclus -> le curseur va jusqu'à dépasser $pos
			while ($this->cursor < $pos + 1 && !$this->isFinished()) {
				$this->readToNextChar();
			}
			return $this;
		}

		//Lit $n caractères
		public function readN($n = 1) {
			for ($i=0; $i < $n; $i++) { 
				$this->readToNextChar();
				if ($this->isFinished()) {break;}
			}
			return $this;
		}

		public function isFinished() {
			return ($this->size <= $this->cursor); 
		}

		public function offsetOut($offset) {
			return ($this->size <= $offset);
		}

		public function content() {
			$this->readAll();
			return trim($this->charStr);
		}

		//Cherche quand même si en dehors c'est nécessaire le test doit être fait avant !
		public function convertOffset($offset, $search = true) {
			if ($search) {$this->readTo($offset);} //Si demandé on lit jusqu'à l'offset pour avoir valeur exacte
			if ($offset < $this->cursor) {
				//Marche car suite croissante
				foreach ($this->charPosArray as $i => $pos) {
					if ($offset <= $pos) {return $i;} 
				}
			}

			return count($this->charPosArray);
		}

		public function strpos($needle, $offset = 0) {
			if ($this->offsetOut($offset)) {return false;} //En dehors de la string pas la peine

			$offset_i = $this->convertOffset($offset);
			while(true) // Je sais mais pas de panique ;)TO DO -> IMPLEMENTER OFFSET DYNAMIQUE
			{
				$i = strpos($this->charStr, $needle, $offset_i);
				if ($i !== false) { //On a trouvé !!
					return array($this->charPosArray[$i], $this->charPosArray[$i + strlen($needle) - 1] + 1);
				}
				if ($this->isFinished()) {return false;} //PREVIENT LA BOUCLE INFINI !!
				$this->readToNextChar(); //Sinon on lit le caractère suivant (rappel le cursor est après l'offset)
			}
			return false;
		}
		
		public function extract($offset = 0, $n = 40) {
			if ($this->offsetOut($offset)) {return false;} //En dehors de la string !

			$i_offset = $this->convertOffset($offset);
			$toDo = ($i_offset + $n) - count($this->charPosArray);
			$this->readN($toDo); //Si négatif ne fait rien ;)
			return substr($this->charStr, $i_offset, $n);
		}

		public function source() {
			return $this->str;
		}

		public function substr($start = 0, $length = null) {
			if ($this->offsetOut($start)) {return new StringPos();}
			$i_start = $this->convertOffset($start, false);
			if ($length === null) {
				$new_charStr = substr($this->charStr, $i_start);
				$new_charPosArray = array_slice($this->charPosArray, $i_start);	
			} else {			
				$end = $start + $length;
				$i_end = $this->convertOffset($end, false);
				$new_charStr = substr($this->charStr, $i_start, $i_end-$i_start);
				$new_charPosArray = array_slice($this->charPosArray, $i_start, $i_end-$i_start);	
			}

			$new_str = ($length === null) ? substr($this->str, $start) : substr($this->str, $start, $length);
			
			$new_cursor = $this->cursor - $start;

			//Décale les indices de position
			if ($start > 0) {
				foreach ($new_charPosArray as $i => $pos) {
					$new_charPosArray[$i] -= $start;
				}
			}

			return new StringPos($new_str, $new_cursor, $new_charStr, $new_charPosArray);
		}

		public function substr_pos($start = 0, $end = null) {
			return ($end === null) ? $this->substr($start) : $this->substr($start, $end-$start);
		}

		public function readAll() {
			while (!$this->isFinished()) {
				$this->readToNextChar();
			}
			return $this;
		}

		public function explode($key) {
			$offset = 0;
			$res = array();
			$pos = true;
			while ($pos !== false) {
				$pos = $this->strpos($key, $offset);
				$res[] = $this->substr_pos($offset, $pos[0]);
				$offset = $pos[1];
			}
			return $res;
		}
/*
		public function replace($patterns = array(), $replace = null) {
			if (!is_array()) {$patterns = array($patterns);}

			foreach ($patterns as $pattern) {
				$this->strpos($pattern);
			}
		}
*/
		public function size() {
			return $this->size;
		}

		public function cursor() {
			return min($this->size, $this->cursor);
		}

		public function char_str() {
			return $this->charStr;
		}

		public function char_pos_array() {
			return $this->charPosArray;
		}

		public function add(StringPos $b) {
			//Cas particulier
			if ($b->cursor() != 0) {
				$this->readAll();
			}

			$this->str .= $b->source();
			$this->charStr .= $b->char_str();

			//Ajoute en décalant les positions
			$n = count($this->charPosArray);
			foreach ($b->char_pos_array() as $i => $pos) {
				$this->charPosArray[$n + $i] = $pos + $this->size();
			}

			$this->cursor = $this->cursor() + $b->cursor(); //EN PREMIER SINON INTERFERENCES !!
			$this->size += $b->size();
			return $this;
		}

	}
?>