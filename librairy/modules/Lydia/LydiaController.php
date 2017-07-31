<?php
	class LydiaController extends Controller {

		public function test() {
			$pdo = new EntityPDO();
			$p = $pdo->get("LydiaPaiement", 2);
			$p->stateLydia();
			var_dump($p);
			//$pdo->save($p);
		}
	}
?>