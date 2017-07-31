<?php
	class LydiaRequestCancel extends LydiaRequest
	{
		function __construct(LydiaPaiement $p)
		{
			$url = 'https://homologation.lydia-app.com/api/request/cancel';
			$params = array(
			    "request_id"=> $p->get("lydia_id"),
			);
			parent::__construct($url, $params);
		}
	}
?>