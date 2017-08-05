<?php include("library/templates/Template_Header.php"); ?>

  <div ng-controller="DashboardController">

    <div  class="md-whiteframe-z2" style="padding: 0; margin-bottom: 20px;">
      <md-toolbar>
        <div class="md-toolbar-tools">
          <span>Dernières études</span>
        </div>
      </md-toolbar>
      <md-content>
        <md-list>
            <md-list-item ng-click="new()"><p style="text-align: center;">Ajouter une étude</p></md-list-item>
            <md-divider></md-divider>
            <md-list-item class="md-2-line" ng-repeat="etude in etudes | orderBy:'numero':true" ng-click="edit(etude)">
              <div class="md-list-item-text" layout="column">
                <h4>#{{etude.numero}} : {{etude.pseudo}} ({{etude_statuts[etude.statut].name}})</h4>
                <p style="white-space: pre-line;">{{etude.but | cut}}</p>
                <md-button class="md-icon-button md-secondary"><i class="material-icons">mode_edit</i></md-button>
              </div>
            </md-list-item>
        </md-list> 
      </md-content>
    </div>

  </div>



<?php
  include("library/templates/Template_Footer_1.php"); ?>

<script type="text/javascript">
var url = "";
  app.controller("DashboardController", function($scope) {
    $scope.etudes = <?php echo json_encode($etudes); ?>;
    $scope.etude_statuts = <?php echo json_encode(Etude::$statutArray); ?>;

    $scope.new = function() {
      location.href = "<?php echo $routeur->getUrlFor("AdminNew"); ?>";
    };

    $scope.edit = function(etude) {
      location.href = "<?php echo $routeur->getUrlFor("AdminEdit", array("id" => 1515)); ?>".replace("1515", etude.id);;
    }
  });
</script>
<?php include("library/templates/Template_Footer_2.php"); ?>