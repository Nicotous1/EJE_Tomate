<?php
	namespace Admin;
	
	use Core\Controller;
	use Core\PDO\Request;

	use \Exception;	

	use Admin\Entity\Etude;

	class AjaxController extends Controller {

/*

	Fonctions Etude
	Toutes les fonctions de sauvegarde liées à une étude

*/
		public function LastEtudes() {
			$page = (int) $this->httpRequest->post("page");
			$page_size = (int) $this->httpRequest->post("page_size");

			$offset = $page*$page_size; // pas de +1 car commence à zero

			$res = $this->pdo->get("Admin\Entity\Etude", array(
				"#s.child IS NULL ORDER BY #s.numero DESC LIMIT :0 OFFSET :1",
				array($page_size, $offset)
			), false);

			$r = new Request("SELECT COUNT(*) AS n FROM #^ WHERE #child IS NULL", Etude::getEntitySQL());
			$n = $r->fetch()["n"];

			return $this->success(array("etudes" => $res, "n" => $n));
		}

	}
?>