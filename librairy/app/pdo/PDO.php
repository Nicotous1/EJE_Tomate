<?php 
	namespace Core\PDO;

	/**
	* 
	*/
	class MyPDO extends \PDO
	{
		const PARAM_DATE = 1515;

		public static $n = 0;
		public static $time = 0;

		public function __construct($host, $name, $user, $mdp, $driver_options=array())
		{
	        $driver_options[PDO::ATTR_STATEMENT_CLASS] = array('MyPDOStatement');
			parent::__construct('mysql:host='.$host.';dbname='.$name, $user, $mdp, $driver_options);
		}

		public function prepare($statement, $driver_options = array()) {
			return parent::prepare($statement, $driver_options);
		}

		public function stats() {
			echo "<br><br>";
			echo "<b>Stats SQL :</b><br>";
			echo "&nbsp;&nbsp;&nbsp;&nbsp;Nombre de requête -> " . $this::$n . "<br>";
			echo "&nbsp;&nbsp;&nbsp;&nbsp;Temps des requêtes -> " . round($this::$time*1000,3) . "ms<br>";
			echo "<br><br>";
			return $this;
		}
	}

	class MyPDOStatement extends \PDOStatement {

		public function execute($params = null) {
			MyPDO::$n += 1;
			$t = microtime();
			$res = parent::execute($params);
			MyPDO::$time += microtime() - $t;
			if (!$res && ($_SERVER['HTTP_HOST'] == "localhost")) {$this->debugDumpParams();print_r($this->errorInfo());}
			return $res;
		}

		public function bindValue($id, $x, $type = null) {
			if ($type == MyPDO::PARAM_DATE && $x != null) {
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