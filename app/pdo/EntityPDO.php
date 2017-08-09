<?php
	namespace Core\PDO;
	use Core\PDO\cache\CachePDO;
	use Core\PDO\Entity\Entity;
	use Core\PDO\Entity\AttSQL;
	use \Exception;

	/*
		Gerer le stock des get null ;)
	*/
	class EntityPDO
	{
		private $cache;

		public function __construct() {
			$this->cache = new CachePDO();
		}

		/*
			FONCTION SAUVEGARDE
		*/

		public function save(Entity $e) {
			if ($e->getId() == null) {
				$res = $this->insert($e);
			} else {
				$res = $this->update($e);
			}
			if ($res) {$this->cache->save($e);} //Ajout au cache
			return $res;
		}

		private function insert(Entity $e) {
			$r = new Request("INSERT INTO #^ (#) VALUES (:)", $e);
			$res = $r->execute();

			$e->setId( ($res) ? $r->lastId() : -1 );
			return $res;
		}

		private function update(Entity $e) {
			$r = new Request("UPDATE #^ SET #~ WHERE id = :id", $e);
			return $r->execute();
		}


		public function remove(Entity $e) {
			$id = $e->getId();
			if (!$e->inBDD()) {return true;}

			$r = new Request("DELETE FROM #^ WHERE id = :id", $e);
			$res = $r->execute();

			if ($res) {$e->setId(null); $this->cache->removeIds(get_class($e), $id);}
			return $res;
		}



		/*
			FONCTIONS DE RECUPERATIONS
		*/

		// REFAIRE GESTION DU CACHE -> INSTABLE AVEC CONDS
		public function get($class, $conds = null, $limit = 1) { //TO DO -> Handle all and conds 
			//Format get($class, $id) -> on convertit
			$search_by_id = (is_numeric($conds));
			if ($search_by_id) {
				$id = (int) $conds;
				$cached = $this->cache->get($class, $id);
				if ($cached !== false) {return clone $cached;}
				$limit = 1;
			}

			if ($limit !== false && $limit < 1) {return null;}

			$conds = $this->handleConds($conds);
			$str_where = $conds[0];
			$params = $conds[1];

			//Selection attribut directe et base
			$r = new Request("SELECT # FROM #^", $class::getEntitySQL());
			
			//Overwrite
			$r->addOverData("s", $class::getEntitySQL());

			//Condition WHERE
			if ($str_where != "") {$r->append("WHERE " . $str_where, $params);};

			//Limite de lignes
			if ($limit !== false) {$r->append("LIMIT :", intval($limit));}
			
			//Execution
			$res = $r->execute();
			if ($res === false) {throw new Exception("An error happened during the execution of the Request !", 1);}
			$e = ($limit == 1) ? $this->dealWithOutput($r, $class) : $this->dealWithOutputs($r, $class);

			//Ajout au cache
			if ($search_by_id) {
				$this->cache->save($e, $class, $id);
			} else {
				$this->cache->saveAll($e);
			}
			return $e;
		}

		private function dealWithOutput($r, $class) {
			$data = (is_array($r)) ?$data :  $r->fetch(PDO::FETCH_ASSOC);
			
			if (is_array($data)) {
				$params = $class::getEntitySQL()->convColtoAtt($data);
				return new $class($params);
			} else {
				return null;
			}
		}

		private function dealWithOutputs($r, $class) {
			$res = array();
			while($data = $r->fetch(PDO::FETCH_ASSOC)){
				$params = $class::getEntitySQL()->convColtoAtt($data);
				$res[] = new $class($params);
			}
			return $res;
		}





		/*
			REFERENCES
		*/

		//More powerfull now
		public function saveAtt($att, Entity $x, $params = null) {
			if (!$x->inBDD()) {throw new Exception("saveAtt need an entity already in the bdd to work !", 1);}

			$id = $x->getId();
			$attSQL = $class::getEntitySQL()->getAtt($att);

			switch ($attSQL->getType()) {
				case AttSQL::TYPE_MREF: return $this->saveMRefsWithIds($attSQL, $id, $x->get_Ids($att));
				case AttSQL::TYPE_IREF: return $this->saveIRefs($attSQL, $x, $params);
				
				default:
					throw new Exception("saveAtt does not handle this type yet !!", 1);
			}
		}


		/*
			Sauvegarde les refs en id et ajoute les nouvelles en sauvegardant l'entité si pas en BDD
		*/
			//MODIFIER LE CACHE -> A FAIRE (DANS TOUT LES OBJETS !)
		private function saveIRefs(AttSQL $attSQL, Entity $e, $params = null) {
			if (!$e->inBDD()) {throw new Exception("PDO can't save IREF if the parent entity doesn't have an id ! You must save it before !!", 1);}

			//Params
			$params = ($params == null) ? array() : $params;
			$defaults = array("delete" => !$attSQL->getAttRef()->canBeNull(), "save" => false);
			$params = array_replace($defaults, $params);

			//Récupération nouveau et ancien que l'on garde
			$NotInBDD = $e->get_NotInBDD($attSQL); //Entity not saved (without id) -> it is entities !
			$InBDD = $e->get_InBDD_ClassAndId($attSQL); //Attention retourne des ids et des classes !!
			$attSQL_Ref = $attSQL->getAttRef(); //Attribut reference -> attribut DREF dans la base lié
			$ids = array(); //A la fin contient tout les ids liés

			//On souvegarde les nouveaux (sans id) et on ajoute leurs nouelles ids
			foreach ($NotInBDD as $item) {
				$item->set($attSQL_Ref, $e);
				$res = $this->save($item);
				if (!$res) {throw new Exception("PDO failed to save a new class ! (IREF)", 1);}
				$ids[] = $item->getId();
			}
			
			//On sauvegarde les anciens si parametre save et on ajoute leurs ids
			foreach ($InBDD as $item) {
				$ids[] = $this->toId($item);
				//PARAM SAVE -> on sauvegarde tous l'item
				if ($params["save"] && is_a($item, "Core\PDO\Entity\Entity")) {
					$item->set($attSQL_Ref, $e);
					$res = $this->save($item);
					if (!$res) {throw new Exception("PDO failed to save an old class ! id = '" . $item->getId() . "' (IREF)", 1);}
				} 
			}

			//Paramètre pour toutes les requêtes
			$params_r = array($attSQL_Ref, $e->getId(), $ids);

			//On lie l'entité aux autres (utile dans le cas où il y a dans InBDD des ids pure (un id seul pas une entité))
			$r = new Request("UPDATE #0^ SET #0 = :1 WHERE id IN :2", $params_r);
			$res = $r->execute();
			if (!$res) {throw new Exception("PDO failed to link ids ! (IREF)", 1);}

			//On délie les autres et on les supprimes si demandé
			$r_str = (($params["delete"]) ? "DELETE FROM #0^" : "UPDATE #0^ SET #0 = NULL") . " WHERE #0 = :1 AND id NOT IN :2";
			$r = new Request($r_str, $params_r);
			$res = $r->execute();
			if (!$res) {throw new Exception("PDO failed to unlink ids ! (IREF)", 1);}

			return true;
		}


		private function saveMRefsWithIds(AttSQL $attSQL, $e_id, $ref_ids) {
			$table = $attSQL->getTable();
			$col = $attSQL->getCol();
			$ref_col = $attSQL->getRefCol();
			$e_id = (int) $e_id;

			//Remove past REFS
			$r = new Request("DELETE FROM #0^ WHERE #0 = :1", array($attSQL, $e_id));
			$res = $r->execute();
			if (!$res) {throw new Exception("PDO failed to delete all link ! (MREF)", 1);}
			
			if (!empty($ref_ids)) {
				$r = new Request("INSERT INTO #^ (#, #>) VALUES", $attSQL);
				foreach ($ref_ids as $i => $id) {
					$glue = ($i + 1 == count($ref_ids)) ? null : ",";
					$r->append("(:0,:1)".$glue, array($e_id, (int) $id));
				}
				$res = $r->execute();
				if (!$res) {throw new Exception("PDO failed to add new links ! (MREF)", 1);}
			}
			return true;
		}		

		//MREF ARE UNIQUE ! -> IT IS NOT INSERT ELSE
		public function addMRefIds($class, $att, $id, $ref) {
			$attSQL = $class::getEntitySQL()->getAtt($att);

			$str = "INSERT INTO #0^ (#0, #0>) (SELECT :1, :2 WHERE NOT EXISTS (SELECT * FROM #0^ WHERE #0 = :1 AND #0> = :2))";
			$p = array($attSQL, (int) $id, (int) $ref);
			$r = Request($str, $p);
			return $r->execute();
		}

		public function getRefs(AttSQL $attSQL, Entity $e) {
			$p = array($attSQL->getClassSQL(), $attSQL, (int) $e->getId());
			switch ($attSQL->getType()) {
				case AttSQL::TYPE_MREF:
					$r = new Request("
						SELECT m.#0 FROM #0^ m 
						JOIN #1^ l ON m.id = l.#1> WHERE l.#1 = :2", $p);
					break;

				case AttSQL::TYPE_IREF:
					$r = new Request("SELECT #0 FROM #0^ WHERE #1 = :2", $p);
					break;
				
				default:
					throw new Exception("getRefs can't handle this type of AttSQL !", 1);
					break;
			}

			$res = $r->execute();
			if (!$res) {throw new Exception("PDO failed to getRefs !", 1);}


			$es = $this->dealWithOutputs($r, $attSQL->getClass());
			$this->cache->saveAll($es);
			return $es;
		}

/*
		Recherche
*/

		public function exist($class, $conds = null) {
			$strct = $class::getEntitySQL();

			$r = new Request("SELECT COUNT(*) FROM #^", $strct);

			//Condition WHERE
			$r->addOverData("s", $strct); //Overwrite
			$conds = $this->handleConds($conds);		
			if ($conds[0] != "") {$r->append("WHERE " . $conds[0], $conds[1]);};

			$res = $r->execute();
			if (!$res) {throw new Exception("An error happened during the exist method !", 1);}
			return ($r->fetch(PDO::FETCH_NUM)[0] > 0);
		}





		/*
			UTILITAIRES
		*/

		private function handleConds($conds) {
			if (empty($conds)) {return array(null, null);}
			if (is_numeric($conds)) {
				return array("id = :", (int) $conds);
			}
			if (is_array($conds)) {
				if (count($conds) != 2) {throw new Exception("An array for a WHERE must have a size of two like [str_where, data] !", 1);}
				return $conds;
			}
			return array($conds, null);
		}

		private function toId($e) {
			if ($e == null) {return $e;}
			if (is_numeric($e)) {return (int) $e;}
			if (is_array($e)) {return (int) $e["id"];}
			if (is_a($e, "Core\PDO\Entity\Entity")) {return $e->getId();} //ADD CHECK IF NULL AND RAISE EXCEPTION -> SAVE BEFORE
			throw new Exception("Convertion impossible en Id !", 1);
		}
	}
?>