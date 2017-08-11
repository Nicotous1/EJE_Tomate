<?php
	namespace Core;
	use \Exception;

	class ConfigFile implements ArrayAccess
	{
		
		private $data;
		private $module;
		private $id;

		public function __construct($path)
		{
			if (!file_exists($path)) {
				throw new Exception("There is no config file at '$path'", 1);
			}

			$this->data = require($path);

			## Pattern modules config -> "/modules/$module_name$/config/$file_name$.config"
			preg_match("/modules\/(.*)\/config\/(.*)\.config/", $path, $matches);
			if (count($matches) == 3) {
				$this->id = $matches[1] . "." . $matches[2];
				$this->module = $matches[1];
				return $this;
			}

			## Pattern main config -> /config/$file_name$.config
			preg_match("/^\/?config\/(.*)\.config/", $path, $matches);
			if (count($matches) == 2) {
				$this->id = "." . $matches[1];
				$this->module = false;
				return $this;
			}

			throw new Exception("The path was not recognized by any pattern ! Can't generade the id and detect the module or if it is main ! ('$path')", 1);		
		}

		public function getId() {
			return $this->id;
		}

		public function getData() {
			return $this->data;
		}

		public function getModule() {
			return $this->module;
		}

		/*
			Array methods
		*/
	    public function offsetSet($offset, $value) {
	        if (is_null($offset)) {
	            $this->data[] = $value;
	        } else {
	            $this->data[$offset] = $value;
	        }
	    }

	    public function offsetExists($offset) {
	        return isset($this->data[$offset]);
	    }

	    public function offsetUnset($offset) {
	    	throw new Exception("Yan can't unset a variable from a config file !", 1);
	    }

	    public function offsetGet($offset) {
	    	if (isset($this->data[$offset])) {
	    		return $this->data[$offset];
	    	} else {
	    		throw new Exception("'$offset' index does not exists in the config file : $this->id !", 1);
	    	}
	    }		
	}
?>