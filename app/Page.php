<?php
	namespace Core;
	use \Exception;

	class Page {

		protected $files;
		protected $vars;
		protected $content;

		public function __construct($content = null) {
			$this->files = array();
			$this->vars = array(
				"routeur" => Routeur::getInstance(), 
				'firewall' => Firewall::getInstance(),
				"user" => Firewall::getInstance()->getUser(),
				"ressources" => new RessourcesHandler(),
			);
			$this->content = $content;
		}

		public function addContent($content) {
			$this->content = $this->content . $content;
			return $this;
		}

		public function addFile($file, $vars = null) {
			if(!is_array($vars)) {$vars = array();} else {throw new Exception("You need to handle the .js with var only for page -> remenber !", 1);}

			$this->files[] = array(
					"file" => $file,
					"vars" => $vars
				);
			return $this;
		}

		public function addVar($name, $value) {
			$this->vars[$name] = $value;
			return $this;
		}

		public function addVars(array $vars) {
			foreach ($vars as $var) {
				$this->addVar($var[0], $var[1]);
			}
			return $this;
		}

		protected function getVars() {
			return $this->vars;
		}

		public function render() {
			extract($this->vars);
			extract(array("vars" => $this->vars));
			echo $this->content;
			foreach ($this->files as $file) {
				extract($file['vars']);
				require $file['file'];
			}
			return $this;
		}

		public function getRender() {
			ob_start();
			$this->render();
			$content = ob_get_contents(); // stockage du tampon dans une chaîne de caractères
			ob_end_clean(); // fermeture de la tamporisation de sortie et effacement du tampon
			return $content;
		}	
	}
?>