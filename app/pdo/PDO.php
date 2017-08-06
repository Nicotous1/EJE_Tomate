<?php
	namespace Core\PDO;
	use Core\ConfigHandler;

	class PDO extends \PDO
	{
		const PARAM_DATE = 1515;

		public static $n = 0;
		public static $time = 0;

		protected static $_instance;

		static public function getInstance() {
			if (self::$_instance === null) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		public function __construct()
		{
	        $driver_options[PDO::ATTR_STATEMENT_CLASS] = array('Core\PDO\PDOStatement');
			$config = ConfigHandler::getInstance()->get(".database_private")->getData();
			parent::__construct('mysql:host='.$config["host"].';dbname='.$config["name"], $config["user"], $config["password"], $driver_options);
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
?>