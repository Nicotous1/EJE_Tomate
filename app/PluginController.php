<?php
	namespace Core;

	class PluginController {
		public function load($name) {
			require_once("plugins/".$name);
		}
	}
?>