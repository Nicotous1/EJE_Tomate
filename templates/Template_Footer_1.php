    </div>

<?php
	$ressources->js(".moment")
			->js(".angular.min")
			->js(".jquery.min")
			->js(".angular-animate.min")
			->js(".angular-aria.min")
			->js(".angular-messages.min")
			->js(".angular-material.min")
			->js(".angular-file-upload.min")
			->js(".main", array("firewall"=> $firewall))
	;
?>
	
<script type="text/javascript">
	app.controller("ToolBarController", function($scope) {
	});
</script>