<?php
	include('library/templates/Template_Header.php');
	if(!isset($errorMSG)) { $errorMSG = "Une erreur s'est produite !<br>Veuillez nous excusez !";}
?>
	<section>
		<div>
<?php
	if($ErrorCode != null) {
?>
			<h1>Erreur <?php echo $ErrorCode; ?></h1>
<?php } ?>			
			<p><?php echo $errorMSG; ?></p>
			<button type="button" onClick="javascript:window.history.go(-1)">Retour</button>
		</div>
	</section>
<?php
	include('library/templates/Template_Footer.php');
?>