<?php
	namespace Core\PDO;

	class PDOStatement extends \PDOStatement {

		public function execute($params = null) {
			PDO::$n += 1;
			$t = microtime();
			$res = parent::execute($params);
			PDO::$time += microtime() - $t;
			if (!$res && ($_SERVER['HTTP_HOST'] == "localhost")) {$this->debugDumpParams();print_r($this->errorInfo());}
			return $res;
		}

		public function bindValue($id, $x, $type = null) {
			if ($type == PDO::PARAM_DATE && $x != null) {
				$x = $x->format("Y-m-d H:i:s");
				$type = PDO::PARAM_STR;
			}
			return parent::bindValue($id, $x, $type);
		}

		public function fetch ( $fetch_style = PDO::FETCH_ASSOC , $cursor_orientation = PDO::FETCH_ORI_NEXT , $cursor_offset = 0  ) {
			return parent::fetch($fetch_style, $cursor_orientation, $cursor_offset);
		}

	}
?>