<?php 
	class DocHistory {
		private $etude;

		public function __construct(Etude $e) {
			$this->etude = $e;
		}

		public function get($name) {
			$pos = strrpos($name,"_");
			if ($pos === false) {
				throw new Exception("La référence '$name' n'est pas correcte. Une référence de document doit être de la forme '##TYPE_NAME##_##NUM_DOC##' !", 1);
			}
			$type_var = substr($name, 0, $pos);
			$num = (int) substr($name, $pos + 1);
			
			$pdo = new EntityPDO();
			$type = $pdo->get("DocType", array("#s.var_name = :", $type_var));

			if ($type === null) {
				throw new Exception("'$name' faire référence au DocType '$type_var' qui n'existe pas !", 1);
			}

			$docs = $this->etude->get("docs");
			$n = count($docs);
			if ($n <= $num) {
				throw new Exception("L'étude ne contient que $n documents du type '$type_var', le document n°$num n'existe donc pas !", 1);
			}
			$res = array();
			foreach ($docs as $i => $d) {
				if ($d->get_Ids("type") == $type->getId()) {
					$res[] = $d;
				}
			}

			usort($res,
				function ($a, $b)
				{
					$a = $a->get("date_created"); $b = $b->get("date_created");
				    if ($a == $b) {
				        return 0;
				    }
				    return ($a > $b) ? -1 : 1;
				}
			);
			return $res[$num];
		}
	}
?>