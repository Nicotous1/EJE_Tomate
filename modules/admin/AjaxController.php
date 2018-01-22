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
	use Admin\Entity\QualiRequest;

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
				"#s.date > :2 ORDER BY #s.date DESC LIMIT :0 OFFSET :1",
				array($page_size, $offset, $date_lim, $this->user)
			), false);

			$r = new Request("SELECT COUNT(*) AS n FROM #0.^ WHERE #0.date > :2",
				array(Info::getEntitySQL(),$this->user, $date_lim)
			);
			$n = $r->fetch()["n"];

			return $this->success(array("infos" => $res, "n" => $n));
		}


		public function SaveCom() {
			$params = $this->httpRequest->post(array(
				"content", "etude", "id"
			));

			$e = $this->pdo->get("Admin\Entity\Etude", $params["etude"]);
			if ($e == null) {return $this->error("L'étude que vous souhaitez commenter a été supprimée !");}

			if ($params["id"] > 0) {
				$update = true;
				$com = $this->pdo->get("Admin\Entity\Com", $params["id"]);
				if ($com === null) {return $this->error("Ce commentaire a été supprimé !");}
				$com->set_Array($params);
			} else {
				$update = false;
				$com = new Com($params);
			}

			$res = $this->pdo->save($com);
			if (!$res) {return $this->error("Une erreur s'est produite lors de la sauvegarde votre commentaire.");}

			//Info modification
			$info = new Info(array("etude" => $e, "type" => 2, "com" => $com));
			if ($update) {				
				$r = new Request("DELETE FROM #^ WHERE #etude~ AND #type~ AND #com~", $info);
				$r->execute();
			}
			$this->pdo->save($info);

			return $this->success(array("com" => $com));
		}

		public function DeleteCom() {
			$id = (int) $this->httpRequest->post("id");
			$res = $this->pdo->remove(new Com(array("id" => $id)));
			return ($res) ? $this->success() : $this->error();
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

		public function ArchiveDocEtude() {
			$id = (int) $this->httpRequest->post("id");
			$archived = (bool) $this->httpRequest->post("archived");

			$d = $this->pdo->get("Admin\Entity\DocEtude", $id);
			if ($d === null) {return $this->error("Ce document n'existe plus !");}

			$d->set("archived", $archived);
			$res = $this->pdo->save($d);
			return ($res) ? $this->success(array("d" => $d)) : $this->error();

		}


		public function Search() {
			$search = $this->httpRequest->post("search");
			$s = new SearchEngine();
			$res = $s->search($search);
			return $this->success(array("items" => $res));
		}



		public function DeleteRequestQuali() {
			$id = (int) $this->httpRequest->post("id");
			$d = new QualiRequest(array("id" => $id));
			$res = $this->pdo->remove($d);
			return ($res) ? $this->success() : $this->error();
		}

		public function MakeRequestQuali() {
			$params = $this->httpRequest->post(array(
				"etude", "doc"
			));

			$r = new QualiRequest($params);
			if ($r->get("etude") === null) {return $this->error("Cette étude n'existe plus !");}
			if ($r->get("doc") === null) {return $this->error("Ce template n'existe plus !");}

			$res = $this->pdo->save($r);

			if ($res) {
				$r = new Request("DELETE FROM #^ WHERE #doc~ AND #etude~ AND id != :id", $r);
				//var_dump($r->getStr());
				$r->execute();
				return $this->success();
			} else {
				return $this->error();
			}
		}

	}
?>