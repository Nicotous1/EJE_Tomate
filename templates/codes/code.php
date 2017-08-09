<?php
	require('templates/Template_Header.php');
?>
	<section>
		<div>
			<h1>La page a retournée <?php echo $code; ?></h1>
			<button type="button" onClick="javascript:window.history.go(-1)">Retour</button>
		</div>
	</section>
<?php
	include('templates/Template_Footer.php');
?>