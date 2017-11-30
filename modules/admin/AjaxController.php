<?php
	namespace Admin;
	
	use Core\Controller;
	use Core\PDO\Request;

	use \Exception;	

	use Admin\Entity\Etude;
	use Admin\Entity\Com;
	use Admin\Entity\DocEtude;
	use Admin\Entity\Info;
	use Admin\Entity\SearchEngine;

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

		public function LastInfos() {
			$page = (int) $this->httpRequest->post("page");
			$page_size = (int) $this->httpRequest->post("page_size");

			$offset = $page*$page_size; // pas de +1 car commence à zero

			$date_lim = date("Y-m-d", strtotime("-2 weeks"));

			$res = $this->pdo->get("Admin\Entity\Info", array(
				"#s.date > :2 AND #s.author != :3.id ORDER BY #s.date DESC LIMIT :0 OFFSET :1",
				array($page_size, $offset, $date_lim, $this->user)
			), false);

			$r = new Request("SELECT COUNT(*) AS n FROM #^ WHERE #date > '$date_lim'", Info::getEntitySQL());
			$n = $r->fetch()["n"];

			return $this->success(array("infos" => $res, "n" => $n));
		}


		public function SaveCom() {
			$content = $this->httpRequest->post("content");
			$etude_id = (int) $this->httpRequest->post("etude_id");


			$e = $this->pdo->get("Admin\Entity\Etude", $etude_id);
			if ($e == null) {return $this->error("L'étude que vous souhaitez commenter a été supprimée !");}

			$com = new Com(array("content" => $content, "etude" => $e));
			$res = $this->pdo->save($com);
			if (!$res) {$this->error("Une erreur s'est produite lors de la sauvegarde votre commentaire.");}

			//Info modification
			$this->pdo->save(new Info(array("etude" => $e, "type" => 2, "com" => $com)));

			return $this->success(array("com" => $com));
		}

/*
	Fonctions DocEtude
*/

		public function DeleteDocEtude() {
			$id = (int) $this->httpRequest->post("id");
			$d = new DocEtude(array("id" => $id));
			$res = $this->pdo->remove($d);
			return ($res) ? $this->success() : $this->error();

		}


		public function Search() {
			$search = $this->httpRequest->post("search");
			$s = new SearchEngine();
			$res = $s->search($search);
			return $this->success(array("items" => $res));
		}

	}
?>