<?php
	include('templates/Template_Header.php');
?>
	<section>
		<div>
			<h1>Accès Refusé</h1>
			<p>Cher <?php echo $user->getPseudo(); ?>,<br>Nous sommes désolez mais l'accès vous est refusé !</p>
			<button type="button" onClick="javascript:window.history.go(-1)">Retour</button>
		</div>
	</section>
<?php
	include('templates/Template_Footer.php');
?>