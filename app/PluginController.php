<?php
	namespace Core;

	class PluginController {
		public function load($name) {
			include_once("library/plugins/".$name);
		}
	}
?>