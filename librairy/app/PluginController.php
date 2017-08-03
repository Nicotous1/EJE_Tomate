<?php
	namespace Core;

	class PluginController {
		public function load($name) {
			include_once("librairy/plugins/".$name);
		}
	}
?>