    </div>

<?php
	$routeur->baliseJS("moment")
			->baliseJS("angular.min")
			->baliseJS("jquery.min")
			->baliseJS("angular-animate.min")
			->baliseJS("angular-aria.min")
			->baliseJS("angular-messages.min")
			->baliseJS("angular-material.min")
			->baliseJS("angular-file-upload.min")
			->baliseJS("main", true, array("firewall"=> $firewall))
	;
?>
	
<script type="text/javascript">
	app.controller("ToolBarController", function($scope) {
	});
</script>