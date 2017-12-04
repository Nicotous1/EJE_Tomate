<?php 
	namespace Auth\Entity;
	use Core\PDO\EntityPDO;
	
	class UserPDO extends EntityPDO {

		public function getAdmins() {
			$pdo = new EntityPDO();
			return $pdo->get("Auth\Entity\User", "#s.level IN (2,3)", false);
		}
	}
?>