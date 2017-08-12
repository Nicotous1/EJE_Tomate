<?php require("templates/Template_Header.php"); ?>

  <div ng-controller="HomeController">

    <div  class="md-whiteframe-z2" style="padding: 0; margin-bottom: 20px;">
      <md-toolbar>
        <div class="md-toolbar-tools">
          <span>Candidater à une étude</span>
        </div>
      </md-toolbar>
      <md-content>
        <md-list>
            <md-list-item ng-if="etudes.length == '0'">
              <p>Aucune étude en cours de recrutement pour le moment.</p>
            </md-list-item>

            <md-list-item class="md-2-line" ng-repeat="etude in etudes | orderBy:'numero':true">
              <div class="md-list-item-text" layout="column">
                <h4>#{{etude.numero}} : {{etude.pub_titre}}</h4>
                <p ng-if="(etude.statut != '2')">Les candidatures sont terminées.</p>
                <p style="white-space: pre-line;" ng-if="(etude.statut == '2')">{{etude.pub | cut}}</p>
                <p ng-if="(etude.w.statut == '1')">Vous allez recevoir un mail avec la réponse de la JE.</p>
                <p ng-if="(etude.w.statut == '2')">Vous avez été accepté pour effectuer cette étude. Félicitations !</p>
                <md-button class="md-secondary" ng-click="postuler($event, etude)" ng-if="!(etude.w.statut > '0') && (etude.statut == 2)">{{ !etude.w  ? 'Candidater' : 'Modifier la candidature'}}</md-button>
              </div>
              <md-divider></md-divider>
            </md-list-item>
        </md-list> 
      </md-content>
    </div>

  </div>



<?php
  require("templates/Template_Footer_1.php"); ?>

<?php
  $ressources->js("etudiant/home", $vars);
?>

<?php require("templates/Template_Footer_2.php"); ?>