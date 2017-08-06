<?php
	include('templates/Template_Header.php');
?>
	<section>
		<div>
<?php
	if($ErrorCode != null) {
?>
			<h1>Accès Refusé</h1>
<?php } ?>			
			<p>Cher <?php echo $user->getPseudo(); ?>,<br>Nous sommes désolez mais l'accès vous est refusé !</p>
			<button type="button" onClick="javascript:window.history.go(-1)">Retour</button>
		</div>
	</section>
<?php
	include('templates/Template_Footer.php');
?>