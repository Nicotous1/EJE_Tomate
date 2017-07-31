<?php 
	class UserPDO extends EntityPDO {

		public function getAdmins() {
			$pdo = new EntityPDO();
			return $pdo->get("User", "#s.level = 2", false);
		}
	}
?>