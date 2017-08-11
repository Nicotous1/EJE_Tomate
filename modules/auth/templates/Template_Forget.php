<?php require("templates/Template_Header.php"); ?>



    <div  class="md-whiteframe-z2" style="padding: 0;" ng-controller="EditController">
    <md-toolbar>
      <div class="md-toolbar-tools">
        <span><?php show($HeaderTitre); ?></span>
      </div>
    </md-toolbar>

      <md-content>

		<form method="POST" ng-submit="reset()">
			<md-content class="md-padding">
				<md-input-container class="md-block flex">
				  <label>Votre nouveau mot de passe</label>
				  <input type="password" ng-model="password" required md-maxlength="72"">
				</md-input-container>

				<md-input-container class="md-block flex">
				  <label>Confirmer mot de passe</label>
				  <input type="password" ng-model="password2" required md-maxlength="72">
				</md-input-container>

				<div>
					<md-button type="submit" ng-disabled="sending">{{sending ? 'Réinitialisation en cours...' : 'Réiniatiliser'}}</md-button>	
				</div>
			</md-content>
		</form> 
      </md-content>
      </div>
    </div>

<?php require("templates/Template_Footer_1.php"); ?>

<script type="text/javascript">
	    app.controller('EditController', function($scope, $http, $mdToast) {

	    	$scope.sending = false;

	    	$scope.reset = function (url) {
	    		if ($scope.password != $scope.password2)  {alert("Les mots de passe ne sont pas identiques."); return;}

		        $scope.sending = true;
		        var resHandler = handle_response({
		          success : function(data, msg) {
								window.location = data.url;
		                    },
		           failed : function(data, msg) {
		           		alert(msg);
		           		$scope.sending = false;
		           },
		        });
	    		var url = window.location.href;
	    		console.log({password: $scope.password});
		        $http.post(url, {password: $scope.password}).then(resHandler, resHandler);
	    	}

	    });
</script>

<?php require("templates/Template_Footer_2.php"); ?>