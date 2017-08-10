<?php
	namespace Core;
	use \Exception;

	class Route {

		private $name;
		private $method;
		private $pattern;
		private $constructor;
		private $vars;
		private $level;
		private $controller;

		public function __construct(array $params) {
			//CHECK ONLY COMPULSORY VARIABLE -> NAME, MODULE
			if (!isset($params["module"]) || $params["module"] == null) {throw new Exception('A road has no module set !');};
			if (!isset($params["name"]) || $params["name"] == null) {throw new Exception('A road has no name in module : '. $params["module"]);};

			$defaults = array(
				"prefix_url" => "",
				"prefix_name" => ucfirst($params["module"]),
				"method" => $params["name"],
				"pattern" => $params["name"],
				"level" => 0,
				"controller" => ucfirst($params["module"]),
			);
			$params = array_merge($defaults, $params);

			// Set constructor after (can't be set in default)
			
			//FORMATAGE params
			$params["pattern"] = $params["prefix_url"] . $params["pattern"];
			$params["name"] = $params["prefix_name"] . $params["name"];
			$params["constructor"] = (isset($params["constructor"])) ? $params["prefix_url"] . $params["constructor"] : $params["pattern"];


			//OBLIGATOIRE
			$this->name = $params['name'];
			$this->pattern = "#^" . $params['pattern'] . "$#i";
			$this->method = $params['method'];
			$this->constructor = $params['constructor'];
			$this->controller = $params["module"] . "\\" . $params['controller'] . "Controller";
			
			//FACULTATIF
			$this->vars = (isset($params['vars'])) ? explode(',', $params['vars']) : array();
			$this->level = (isset($params['level'])) ? $params['level'] : 0;
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