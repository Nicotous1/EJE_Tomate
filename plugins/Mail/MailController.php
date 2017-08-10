<?php 
	class MailController
	{
	}
/*
			public function mail_paid($facture) {     // Plusieurs destinataires
		     $to  = 'ntoussai29@gmail.com' . ', '; // notez la virgule

			// message
			$message = "
				<html>
					<head>
					    <meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
					    <meta charset='utf-8'>
						<title>". $facture->getName() ." a été payée !</title>
					</head>
					<body>
						<p>" . $facture->getName() ." a été payée !</p>
						<a href='". $this->SC->getRouteur()->getUrlFor("cat", array("cat" => 3)) ."'>Voir les factures payées</a>
					</body>
				</html>
			";

			// Pour envoyer un mail HTML, l'en-tête Content-type doit être défini

			// Envoi
			return mail($to, $facture->getName() ." a été payée !", $message, $headers);
		}
*/
?>