<?php
namespace Core\PDO\Cache;

	class CachePDO {

		const CACHE_KEY = "&&PDO_Cache&&";

		public function construct() {	
			if (!isset($GLOBALS[CachePDO::CACHE_KEY])) {$GLOBALS[CachePDO::CACHE_KEY] = array();}
		}

		//Warning $e can be null and therefore not an entity !!
		public function save($e, $class = null, $id = null) {
			if ($class === null) {
				$this->getCache($e)->set($e);
			} else {
				$this->getCacheOfRaw($class, $id)->set($e);
			}
			return $this;
		}

		public function saveAll($es) {
			if (!is_array($es)) {$es = array($es);}
			foreach ($es as $e) {
				if (is_a($e, "Core\PDO\Entity\Entity")) {$this->save($e);}
			}
			return $this;
		}

		public function get($class, $id) {
			return $this->getCacheOfRaw($class, $id)->get();
		}

		public function getCache(Entity $e) {
			return $this->getCacheOfRaw(get_class($e), $e->getId());
		}

		public function getCacheOfRaw($class, $id) {
			if (!($id > 0)) {throw new Exception("CachePDO must have an id to handle !", 1);}
			if (!isset($GLOBALS[CachePDO::CACHE_KEY][$class][$id])) {
				$GLOBALS[CachePDO::CACHE_KEY][$class][$id] = new CacheEntity();
			}	
			return $GLOBALS[CachePDO::CACHE_KEY][$class][$id];
		}



		public function removeIds($class, $ids) {
			if (!isset($GLOBALS[CachePDO::CACHE_KEY][$class])) {return $this;}
			if (!is_array($ids)) {$ids = [$ids];}
			foreach ($ids as $id) {
				unset($GLOBALS[CachePDO::CACHE_KEY][$class][$id]);
			}
			return $this;
		}

		public function debug() {
			var_dump($GLOBALS[CachePDO::CACHE_KEY]);
		}

	}
?>