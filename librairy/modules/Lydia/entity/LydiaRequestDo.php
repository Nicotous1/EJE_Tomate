<?php
	class LydiaRequestDo extends LydiaRequest
	{
		function __construct($amount)
		{
			$url = 'https://homologation.lydia-app.com/api/request/do';
			$params = array(
			    "message" => "Yolo Thug Money",
			    "amount"=> str_replace(",",".",$amount),
			    "currency"=> "EUR",
			    "type"=> "email",
			    "recipient"=> LydiaRequest::recipient_email,
			    "vendor_token"=> LydiaRequest::vendor_token,
			);
			parent::__construct($url, $params);
		}
	}
?>