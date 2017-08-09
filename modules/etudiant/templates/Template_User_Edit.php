<?php require("templates/Template_Header.php"); ?>

  <div>

    <div  class="md-whiteframe-z2" style="padding: 0; margin-bottom: 20px;" ng-controller="UserEditController">
      <md-toolbar>
        <div class="md-toolbar-tools">
          <span class="flex">Votre profil</span>
        </div>     
      </md-toolbar>
      <md-content class="md-padding">
        <form ng-submit="save()">
            <div layout="row">
              <md-input-container>
                <label>Titre</label>
                <md-select ng-model="user.titre" placeholder="Titre" required>
                  <md-option ng-value="t.id" ng-repeat="t in formEtudiant.titres track by t.id">{{t.titre}}</md-option>
                </md-select>
              </md-input-container>
              <md-input-container class="md-block flex">
                <label>Nom</label>
                <input type="text" ng-model="user.nom" md-maxlength="30"  required>
              </md-input-container>
              <md-input-container class="md-block flex">
                <label>Prénom</label>
                <input type="text" ng-model="user.prenom" md-maxlength="30"  required>
              </md-input-container>

              <md-input-container>
                <md-select ng-model="user.annee" placeholder="Classe" required>  
                  <md-option ng-value="annee.id" ng-repeat="annee in formEtudiant.annees track by annee.id">{{annee.name}}</md-option>
                </md-select>
              </md-input-container> 
            </div> 

            <div layout="row">
              <md-input-container class="md-block flex-50">
                <label>Mail</label>
                <input type="email" ng-model="user.mail" md-maxlength="130"  disabled>
              </md-input-container>
              <md-input-container class="md-block flex-25">
                <label>Mobile</label>
                <input type="text" ng-model="user.mobile" md-maxlength="15">
              </md-input-container>
              <md-input-container class="md-block flex-25">
                <label>Fixe</label>
                <input type="text" ng-model="user.fixe" md-maxlength="15">
              </md-input-container>
            </div> 

            <div layout="row">
              <md-input-container class="md-block flex-40">
                <label>Adresse</label>
                <input type="text" ng-model="user.adresse" md-maxlength="300">
              </md-input-container>
              <md-input-container class="md-block flex-30">
                <label>Code Postal</label>
                <input type="text" ng-model="user.code_postal" md-maxlength="6">
              </md-input-container>
              <md-input-container class="md-block flex-30">
                <label>Ville</label>
                <input type="text" ng-model="user.ville" md-maxlength="30">
              </md-input-container>
            </div> 
            
            <div layout="row">
              <md-input-container class="md-block">
                <label>Date de naissance</label>
                <md-datepicker ng-model="user.date_birth" md-placeholder="Entrez une date :" md-open-on-focus></md-datepicker>
              </md-input-container>
              <md-input-container class="md-block flex">
                <label>Nationalité</label>
                <input type="text" ng-model="user.nationality" md-maxlength="30">
              </md-input-container>
              <md-input-container class="md-block flex-40">
                <label>Numéro de sécurité sociale</label>
                <input type="text" ng-model="user.secu" md-maxlength="15">
              </md-input-container>
            </div>

              <div layout="row" layout-align="center" style="margin-bottom: 20px;">
                <md-button type="submit" class="md-accent md-raised" ng-disabled="sending">{{sending ? 'Sauvegarde en cours...' : 'Sauvegarder'}}</md-button>   
              </div>
        </form>
      </md-content>
    </div>

  </div>



<?php
  require("templates/Template_Footer_1.php"); ?>

<script type="text/javascript">
  app.controller("UserEditController", function($scope, $http, $mdToast) {

    $scope.user = handle_date(<?php echo json_encode($user); ?>);
    $scope.formEtudiant = {
     annees : <?php echo json_encode(User::$anneeArray); ?>,
     titres : <?php echo json_encode(User::$titreArray); ?>,
    };

    $scope.sending = false;

    $scope.save = function() {
      console.log("posting user :");
      console.log($scope.user);
      $scope.sending = true;

      var resHandler = handle_response({
        success : function(data, msg) {
                    $scope.user = handle_date(data.user);
                    $mdToast.show($mdToast.simple().textContent('Votre profil a été sauvegardé !').position("top right"));
                  },
        all : function(data, msg) {
                $scope.sending = false;
              }, 
      });
      $http.post("<?php echo $routeur->getUrlFor("AjaxSaveUser") ?>", $scope.user).then(resHandler, resHandler);
    };
  });
</script>

<?php require("templates/Template_Footer_2.php"); ?>