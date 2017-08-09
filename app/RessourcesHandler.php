<?php
	namespace Core;

	use \Exception;

	class RessourcesHandler {
		
		public function html_url($name) {
			$path = $this->get_path($name, "templates", "html");
			return $this->get_url_path($path)
		}

		public function css($name, $vars = array()) {
			$path = $this->get_path($name, "css");
			$ext = $this->get_ext($path);

			if ($ext == "php") {
				extract($vars);
				require($path);
			} else {
				echo "<link rel='stylesheet' href='" . $this->get_path_url($path) . "'>\n";
			}
			return $this;			
		}

		public function js($name, $vars = array()) {
			$path = $this->get_path($name, "js");
			$ext = $this->get_ext($path);

			if ($ext == "php") {
				extract($vars);
				require($path);
			} else {
				echo "<script type='text/javascript' src='". $this->get_path_url($path) ."'></script>\n";
			}
			return $this;			
		}

		public function get_url($name) {
			$path = $this->get_path($name);
			return $this->get_path_url($path);
		}

		public function get_path($name, $folder = null, $ext = null) {
			$name = str_replace(" ", "", $name);
			$params = explode(".", $name);
			if (count($params) < 2) {
				throw new Exception("The ressource named '$name' should at least contain one '.' !", 1);	
			}

			// Handles folder ressources -> ressources/$folder$/
			if ($folder !== null) {
				array_splice($params, 1, 0, array($folder)); //Add the folder to the params at the good position
			} else {
				$folder = $params[1]; //Save the folder given directly in name
			}

			if ($ext !== null) {
				$params[] = $ext;
			}

			// Find root folder for the ressources
			$module = $params[0]
			$root = ($module != '') ? "modules/" . strtolower($params[0]) . "/" : '';
			$root .= "ressources/";

			// Append folder to the path
			$path_params = array_slice($params, 1, count($params) - 2);
			$path = $root . implode("/", $path_params);


			// Handles the last param meaning possibility
			$last_param = end($params);
			
			// Last param is the extension
			$path .= "." . $last_param;
			if (file_exists($path)) {return $path;}

			if ($ext !== null) { // if ext given last param could only be the extension
				// Last param is the file name and extension can be '.php' or '.$folder_ressource$'
				$path .= "/" . $last_param;
				foreach (array("php", $folder) as $ext) {
					$full_path = $path . "." . $ext;
					if (file_exists($full_path)) {return $full_path;}
				}
			}

			// The name can't be match with anything
			throw new Exception("The name '$name' can't be translated in a existing path !", 1);
		}

		public function icon() {
			$root = Routeur::getInstance()->getRoot() . "ressources/";
			$str = 
<<<EOT
		      <link rel="apple-touch-icon" sizes="57x57" href="icon/apple-icon-57x57.png">
		      <link rel="apple-touch-icon" sizes="60x60" href="icon/apple-icon-60x60.png">
		      <link rel="apple-touch-icon" sizes="72x72" href="icon/apple-icon-72x72.png">
		      <link rel="apple-touch-icon" sizes="76x76" href="icon/apple-icon-76x76.png">
		      <link rel="apple-touch-icon" sizes="114x114" href="icon/apple-icon-114x114.png">
		      <link rel="apple-touch-icon" sizes="120x120" href="icon/apple-icon-120x120.png">
		      <link rel="apple-touch-icon" sizes="144x144" href="icon/apple-icon-144x144.png">
		      <link rel="apple-touch-icon" sizes="152x152" href="icon/apple-icon-152x152.png">
		      <link rel="apple-touch-icon" sizes="180x180" href="icon/apple-icon-180x180.png">
		      <link rel="icon" type="image/png" sizes="192x192"  href="icon/android-icon-192x192.png">
		      <link rel="icon" type="image/png" sizes="32x32" href="icon/favicon-32x32.png">
		      <link rel="icon" type="image/png" sizes="96x96" href="icon/favicon-96x96.png">
		      <link rel="icon" type="image/png" sizes="16x16" href="icon/favicon-16x16.png">
		      <link rel="manifest" href="icon/manifest.json">
		      <meta name="msapplication-TileColor" content="#ffffff">
		      <meta name="msapplication-TileImage" content="icon/ms-icon-144x144.png">
		      <meta name="theme-color" content="#ffffff">
EOT;
			$str = str_replace("href=\"", "href=\"" . $root, $str)
			echo $str;
			return $this;
		}



		protected function get_ext($path) {
			$pos = strrpos($path, ".");
			return ($pos === False) ? null : substr($path, $pos + 1);
		}

		protected function get_path_url($path) {
			return Routeur::getInstance()->getRoot() . $path;
		}
	}
?>