<?php
	class CachePDO {

		const CACHE_KEY = "&&PDO_Cache&&";

		public function construct() {	
			if (!isset($GLOBALS[CachePDO::CACHE_KEY])) {$GLOBALS[CachePDO::CACHE_KEY] = array();}
		}

		public function save(Entity $e) {
			$this->getCache($e)->setEntity($e);
			return $this;
		}

		public function saveNull($class, $id = null) {
			if ($id == null) {return $this;}
			$this->getCacheOfRaw($class, $id)->setEntity();
			return $this;
		}

		public function get($class, $id) {
			return $this->getCacheOfRaw($class, $id)->getEntity();
		}

		public function saveRefs(AttSQL $attSQL, $id, $ids) {
			$this->getCacheOfRaw($attSQL->getParent()->getClass(), $id)->addRef($attSQL, $ids);
			return $this;
		}



		public function getCache(Entity $e) {
			return $this->getCacheOfRaw(get_class($e), $e->getId());
		}

		public function getCacheOfRaw($class, $id) {
			if ($id == null) {throw new Exception("CachePDO can't cache an entity without id !", 1);}
			if (!isset($GLOBALS[CachePDO::CACHE_KEY][$class][$id])) {
				$GLOBALS[CachePDO::CACHE_KEY][$class][$id] = new CacheEntity();
			}	
			return $GLOBALS[CachePDO::CACHE_KEY][$class][$id];
		}

		public function removeIds($class, $ids) {
			if (!isset($GLOBALS[CachePDO::CACHE_KEY][$class])) {return $this;}
			foreach ($ids as $id) {
				unset($GLOBALS[CachePDO::CACHE_KEY][$class][$id]);
			}
			return $this;
		}

	}
?>