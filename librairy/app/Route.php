<?php
;

	class Route {

		private $name;
		private $method;
		private $pattern;
		private $constructor;
		private $vars;
		private $level;
		private $controller;

		public function __construct(array $param) {

			//OBLIGATOIRE
			$this->name = $param['name'];
			$this->pattern = "#^" . $param['pattern'] . "$#i";
			$this->method = $param['method'];
			$this->constructor = $param['constructor'];
			$this->controller = $param['controller'];
			
			//FACULTATIF
			$this->vars = (isset($param['vars'])) ? explode(',', $param['vars']) : array();
			$this->level = (isset($param['level'])) ? $param['level'] : 0;
		}

		public function match($url) {
			return preg_match($this->pattern, $url);
		}

		public function getMethod() {
			return $this->method;
		}

		public function getName() {
			return $this->name;
		}

		public function getLevel() {
			return $this->level;
		}

		public function getController() {
			return $this->controller;
		}

		public function getUrl(array $params = null) {
			$paramAsked = substr_count($this->constructor, "$");
			if($paramAsked == 0) { return $this->constructor; }

			if($params == null) { $params = array();}
			if(count($params) < $paramAsked) { throw new Exception("The constructor need " .  $paramAsked . " parameters only " . count($params) . " given !"); return null;}

			$i = 1;
			$url = $this->constructor;
			foreach($params as $param) {
				$url = preg_replace("#\\$" . $i . "#i", $param, $url);
				$i++;
			}

			if(!$this->match($url)) { throw new Exception("Parameters are not valid for this road : '" . $url . "' doesn't match with '" . $this->pattern . "' !"); return null;}
			return $url;
		}

		public function getParam($url) {
			preg_match($this->pattern, $url, $matched);
			array_splice($matched, 0, 1);
			if(count($matched) == count($this->vars) && count($matched) > 0) {
				return array_combine($this->vars, $matched);
			}
			return array();
		}
	}
?>