<?php
	$handler = new EntitySQLHandler();

	$handler->add((array(
			"class" => "LydiaPaiement",
			"table" => "lydia_paiement",
			"atts" => array(
				array("att" => "amount", "type" => AttSQL::TYPE_FLOAT),
				array("att" => "state", "type" => AttSQL::TYPE_INT),
				array("att" => "user", "type" => AttSQL::TYPE_USER),

				array("att" => "lydia_id", "type" => AttSQL::TYPE_INT),
				array("att" => "lydia_uuid", "type" => AttSQL::TYPE_STR),
				array("att" => "lydia_url", "type" => AttSQL::TYPE_STR),
			),
		)))
	;

	class LydiaPaiement extends Entity
	{
		//LYDIA
		protected $lydia_id;
		protected $lydia_uuid;
		protected $lydia_url;

		protected $state;
		protected $amount;
		protected $user;

		const STATE_ERROR = -1;
		const STATE_NOTSENDED = 0;
		const STATE_WAITING = 1;
		const STATE_DONE = 2;
		const STATE_CANCELED = 3;

	// public function setAmount() (Maybe necessary !) DONT DO IT -> NEEDED FOR STATE !

		public function var_defaults() {
			return array("state" => $this::STATE_NOTSENDED);
		}

		public function initLydia() {
			$r = new LydiaRequestDo($this->get("amount"));
			if (!$r->hasError()) {
				$datas = $r->getResult();
				$this->set_Array(array(
					"lydia_id" => $datas["request_id"],
					"lydia_uuid" =>  $datas["request_uuid"],
					"lydia_url" =>  $datas["mobile_url"],
				));
				$this->state = $this::STATE_WAITING;
			} else {
				$this->state = $this::STATE_ERROR;
			}
			return $this;
		}

		public function stateLydia() {
			$r = new LydiaRequestState($this);
			if (!$r->hasError()) {
				$datas = $r->getResult();
				switch ((int) $datas["state"]) {
					case 0:
						$this->state = $this::STATE_WAITING; break;
					case 1:
						$this->state = $this::STATE_DONE; break;
					case 5:
					case 6:
						$this->state = $this::STATE_CANCELED; break;
					default:
						$this->state = $this::STATE_ERROR; break;
				}
			} else {
				$this->state = $this::STATE_ERROR;
			}
			return $this;
		}

		public function cancelLydia() {
			$r = new LydiaRequestCancel($this);
			if (!$r->hasError()) {
				$this->state = $this::STATE_CANCELED;
			} else {
				$this->state = $this::STATE_ERROR;
			}
			return $this;
		}
	}

?>