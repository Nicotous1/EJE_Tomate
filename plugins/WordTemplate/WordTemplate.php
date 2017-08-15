<?php
	namespace WordTemplate;
	use \Exception;
	use \ZipArchive;
	
	class WordTemplate {
		
		private $template;
		private $scope;

		public function __construct($template, Scope $scope) {
			$this->template = $template;
			$this->scope = $scope;
		}

		public function compile($path) {
			//Création d'un fichier temporaire
			$temp = tempnam(sys_get_temp_dir(), 'TMP_');
			
			if (!file_exists($this->template)) {throw new Exception("Le fichier du template n'existe pas !\n Le fichier doit être uploadé dans le pôle qualité.", 1);}
			file_put_contents($temp, file_get_contents($this->template));			

			$zip = new ZipArchive();
			if ($zip->open($temp, ZipArchive::CREATE)!==TRUE) {  echo "Cannot open $temp :( "; die; }
			$n = $zip->numFiles;
			for ($i = 0; $i < $n ; $i++) {
			    $doc = $zip->getNameIndex($i);
			    if (!preg_match('#(document|header|footer)\d*.xml$#', $doc)) {continue;}
				$str = $zip->getFromName($doc);
				$t = new TomateTemplate($str, $this->scope);
				$t->compile();
				if (!$zip->addFromString($doc, $t->getContent())) { echo 'File not written.  Go back and add write permissions to this folder!l'; die(); }
			}
			$zip->close();	
			copy($temp, $path);
			return $this;
		}
	}
?>