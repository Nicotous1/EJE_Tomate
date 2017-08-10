<?php require("templates/Template_Header.php"); ?>

    <div  class="md-whiteframe-z2" style="padding: 0;" ng-controller="DocListController">

      <md-toolbar>
        <div class="md-toolbar-tools">
          <span>Pôle qualité</span>
          <span flex></span>
        </div>
      </md-toolbar>

      <md-content>
        <md-tabs md-dynamic-height md-border-bottom >

          <div ng-controller="TemplatesController" ng-if="doctypes.length > 0">
            <md-tab label="Templates">
              <md-tab-body>
                <md-list style="padding: 0;">
                  <md-list-item ng-click="edit()"><p style="text-align: center;">Ajouter un template</p></md-list-item>
                  <md-divider></md-divider>
                  <md-list-item ng-repeat="t in templates | orderBy:'nom'" ng-click="edit(t);">
                    <span>
                      {{t.nom}} - {{getId(doctypes, t.type).nom}} <small>({{t.doc.fullName}})</small>
                    </span>
                    <md-button class="md-icon-button md-secondary" ng-click="redirect(t.doc.link);"><i class="material-icons">file_download</i></md-button>
                  </md-list-item>
                </md-list>
              </md-tab-body>
            </md-tab> 
          </div>

          <div ng-controller="DocTypesController">
            <md-tab label="Types">
              <md-tab-body>
                <md-list style="padding: 0;">
                  <md-list-item ng-click="edit()"><p style="text-align: center;">Ajouter un type</p></md-list-item>
                  <md-divider></md-divider>
                  <md-list-item ng-repeat="d in doctypes | orderBy:'nom'" ng-click="edit(d)">
                    <span>{{d.nom}} <small>({{d.var_name}})</small></span>
                    <md-button class="md-icon-button md-secondary" ng-click="edit(d)"><i class="material-icons">mode_edit</i></md-button>
                  </md-list-item>
                </md-list>
              </md-tab-body>
            </md-tab> 
          </div>

          <div ng-controller="VarController">
            <md-tab label="Variables">
              <md-tab-body>
                <md-list style="padding: 0;">
                  <md-list-item ng-click="edit()"><p style="text-align: center;">Ajouter une variable</p></md-list-item>
                  <md-divider></md-divider>
                  <md-list-item ng-repeat="d in vars_quali | orderBy:'nom'" ng-click="edit(d)">
                    <span>{{d.nom}} <small>({{d.var_name}})</small></span>
                    <md-button class="md-icon-button md-secondary" ng-click="edit(d)"><i class="material-icons">mode_edit</i></md-button>
                  </md-list-item>
                </md-list>
              </md-tab-body>
            </md-tab> 
          </div>

        </md-tabs>
      </md-content>
    </div>

<?php
  require("templates/Template_Footer_1.php"); ?>

<?php
  $ressources->js("admin/doc_list", $vars);
?>

<?php require("templates/Template_Footer_2.php"); ?>