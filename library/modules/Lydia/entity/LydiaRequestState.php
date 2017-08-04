<?php
	class LydiaRequestState extends LydiaRequest
	{
		protected $p;

		function __construct(LydiaPaiement $p)
		{
			$url = 'https://homologation.lydia-app.com/api/request/state';
			$params = array(
			    "request_uuid" => $p->get("lydia_uuid"),
			    "vendor_token"=> LydiaRequest::vendor_token,
			);
			$this->p = $p;
			parent::__construct($url, $params);
		}

		public function checkSignature() {
			return (isset($this->getResult()["signature"]) && $this->getResult()["signature"] == $this->getSignature());
		}

		public function getSignature() {
			if ($this->signature == null) {	
				$param = array(
			        'amount' => number_format($this->p->get("amount"), 2),
			    	"request_uuid" => $this->p->get("lydia_uuid"),
			    );
			    $str = "";

			    foreach ($param as $key => $val) {
			        $str .= urlencode($key).'='.urlencode($val) . "&";
			    }

			    $this->signature =  md5($str.LydiaRequest::private_token);
			}
			return $this->signature;
		}

		public function hasError() {
			return (parent::hasError() || !$this->checkSignature());
		}
	}
?>