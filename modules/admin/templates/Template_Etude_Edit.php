<?php include("templates/Template_Header.php"); ?>


    <div  class="md-whiteframe-z2" style="padding: 0;" ng-controller="EditController">
      <md-toolbar>
        <div class="md-toolbar-tools">
          <span><span ng-if="etude.numero">#{{etude.numero}} : </span><span ng-if="etude.pseudo">{{etude.pseudo}}</span></span>
          <span ng-if="!etude.nom && !etude.pseudo">Nouvelle étude</span>
          <span flex></span>

          <md-button class="md-icon-button" ng-if="parent != null" ng-click="redirect(parent.link)">
            <md-icon>arrow_back</md-icon>
          </md-button>

          <md-button class="md-icon-button" ng-if="child != null" ng-click="redirect(child.link)">
            <md-icon>arrow_forward</md-icon>
          </md-button>

          <md-button class="md-icon-button" ng-if="etude.locked && child == null" ng-click="copy($event)">
            <md-icon>content_copy</md-icon>
          </md-button> 

          <md-button class="md-icon-button" ng-if="!etude.locked && etude.id != null" ng-click="lockEtude($event)">
            <md-icon>lock_open</md-icon>
          </md-button>

          <md-button class="md-icon-button" ng-if="etude.locked">
            <md-icon>lock_close</md-icon>
          </md-button>
        </div>
      </md-toolbar>

      <md-content>

        <md-tabs md-dynamic-height md-border-bottom >

          <div ng-controller="PropEditController">
            <md-tab label="Propriétés">
              <md-tabs md-dynamic-height md-border-bottom >
                <md-tab label="Général">
                  <md-tab-body>
                    <div class="layout-padding">
                      <div layout="row">
                        <md-input-container class="md-block flex">
                          <label>Nom</label>
                          <input type="text" ng-model="etude.nom" md-maxlength="130" ng-disabled="etude.locked">
                        </md-input-container>
                      </div>  

                      <div layout="row">

                        <md-input-container class="md-block flex">
                          <label>Nom Interne</label>
                          <input type="text" ng-model="etude.pseudo" md-maxlength="50" ng-disabled="etude.locked">
                        </md-input-container> 

                        <md-input-container class="md-block flex-20" ng-disabled="etude.locked">
                          <label>Numero</label>
                          <input type="number" ng-model="etude.numero" step="1" min="2000" ng-disabled="etude.locked">
                        </md-input-container>  

                        <md-input-container class="md-block flex-20">
                          <label>Statut</label>
                          <md-select ng-model="etude.statut" placeholder="Statut" ng-disabled="etude.locked">  
                            <md-option ng-value="statut.id" ng-repeat="statut in formEtude.statuts track by statut.id">{{statut.name}}</md-option>
                          </md-select>
                        </md-input-container>
                      </div>

                      <div layout="row">  
                        <md-input-container class="md-block flex">
                          <label>Administrateurs</label>
                          <md-select ng-model="etude.admins" placeholder="Administrateurs" multiple ng-disabled="etude.locked">  
                            <md-option ng-value="{{admin.id}}" ng-repeat="admin in admins | orderBy:'nom' track by admin.id">{{admin.nom}} {{admin.prenom}}</md-option>
                          </md-select>
                        </md-input-container>      
                        <md-input-container class="md-block flex-20">
                          <label>Provenance</label>
                          <md-select  ng-model="etude.provenance" placeholder="Provenance" ng-disabled="etude.locked">  
                            <md-option ng-value="prov.id" ng-repeat="prov in formEtude.provs track by prov.id">{{prov.name}}</md-option>
                          </md-select>
                        </md-input-container>
                        <md-input-container class="md-block flex-20">
                          <label>Lieu</label>
                          <md-select ng-model="etude.lieu" placeholder="Lieu" ng-disabled="etude.locked">  
                            <md-option ng-value="lieu.id" ng-repeat="lieu in formEtude.lieux track by lieu.id">{{lieu.short}}</md-option>
                          </md-select>
                        </md-input-container>
                      </div>

                      <div layout="row">
                        <md-input-container  class="md-block flex">
                          <label>Domaines</label>
                          <input ng-model="etude.domaines"  md-maxlength="120" ng-disabled="etude.locked"></textarea>
                        </md-input-container>
                      </div>
                    </div>
                  </md-tab-body>
                </md-tab>



                <md-tab label="Contacts">
                  <md-tab-body>
                    <div class="layout-padding">
                      <div layout="row" layout-align="left center" class="flex">
                        <md-input-container class="md-block flex">
                          <label>Entreprise</label>
                          <md-select  ng-model="etude.entreprise" placeholder="Entreprise" ng-disabled="etude.locked" ng-change="coherentClient()">  
                            <md-option ng-value="-1" ng-click="editEntreprise($event)">Ajouter une entreprise</md-option>
                            <md-option ng-value="{{entreprise.id}}" ng-repeat="entreprise in formEtude.entreprises | orderBy:'nom' track by entreprise.id">{{entreprise.nom}}</md-option>
                          </md-select>
                        </md-input-container>
                        <md-button class="md-icon-button" ng-if="etude.entreprise" ng-click="editEntreprise($event, etude.entreprise)" style="margin-right: 15px;">
                          <md-icon>mode_edit</md-icon>
                        </md-button>
                      </div>

                      <div ng-if="etude.entreprise > 0" class="md-block flex" layout="row">

                        <div layout="row" layout-align="left center" class="flex">
                          <md-input-container class="md-block flex">
                            <label>Client</label>
                            <md-select  ng-model="etude.client" placeholder="Client" ng-disabled="etude.locked">  
                              <md-option ng-value="-1" ng-click="editClient($event)">Ajouter un client</md-option>
                              <md-option ng-value="{{client.id}}" ng-repeat="client in formEtude.clients | filter:{entreprise : etude.entreprise} | orderBy:'nom' track by client.id" onclick="return false;">{{client.nom}} {{client.prenom}}</md-option>
                            </md-select>
                          </md-input-container>
                          <md-button class="md-icon-button" ng-if="etude.client > 0" ng-click="editClient($event, etude.client)" style="margin-right: 15px;">
                            <md-icon>mode_edit</md-icon>
                          </md-button>
                        </div>
                      
                        <div layout="row" layout-align="left center" class="flex">
                          <md-input-container class="md-block flex">
                            <label>Facturation</label>
                            <md-select  ng-model="etude.facturation" placeholder="Facturation"  ng-disabled="etude.locked"> 
                              <md-option ng-value="-1" ng-click="editFacturation($event)">Ajouter un client</md-option>
                              <md-option ng-value="{{client.id}}" ng-repeat="client in formEtude.clients | filter:{entreprise : etude.entreprise} track by client.id ">{{client.nom}} {{client.prenom}}</md-option>
                            </md-select>
                          </md-input-container>
                          <md-button class="md-icon-button" ng-click="editFacturation($event, etude.facturation)" ng-if="etude.facturation > 0" style="margin-right: 15px;">
                            <md-icon>mode_edit</md-icon>
                          </md-button>
                        </div>
                      
                        <div layout="row" layout-align="left center" class="flex">
                          <md-input-container class="md-block flex">
                            <label>Signataire</label>
                            <md-select  ng-model="etude.signataire" placeholder="Signataire"  ng-disabled="etude.locked">  
                              <md-option ng-value="-1" ng-click="editSignataire($event)">Ajouter un client</md-option>
                              <md-option ng-value="{{client.id}}" ng-repeat="client in formEtude.clients  | filter:{entreprise : etude.entreprise} track by client.id">{{client.nom}} {{client.prenom}}</md-option>
                            </md-select>
                          </md-input-container>
                          <md-button class="md-icon-button" ng-click="editSignataire($event, etude.signataire)" ng-if="etude.signataire > 0">
                            <md-icon>mode_edit</md-icon>
                          </md-button>
                        </div>

                      </div>
                    </div>
                  </md-tab-body>
                </md-tab>



                <md-tab label="Comptable">
                  <md-tab-body>
                    <div class="layout-padding" layout="row">
                        <md-input-container class="md-block flex">
                          <label>Frais</label>
                          <input type="number" ng-model="etude.fee" ng-disabled="etude.locked" step="0.01">
                        </md-input-container>

                        <md-input-container  class="md-block flex">
                          <label>Prix de la JEH</label>
                          <input type="number" min="80" max="340" ng-model="etude.p_jeh" step="10" ng-disabled="etude.locked"></textarea>
                        </md-input-container>    

                        <md-input-container class="md-block flex" ng-disabled="etude.locked">
                          <label>Nombre JEH rupture</label>
                          <input type="number" ng-model="etude.break_jeh" step="1" ng-disabled="etude.locked">
                        </md-input-container>  

                        <md-input-container class="md-block flex">
                          <label>Frais rupture</label>
                          <input type="number" ng-model="etude.break_fee" ng-disabled="etude.locked" step="0.01">
                        </md-input-container>
                    </div>
                  </md-tab-body>
                </md-tab>



                <md-tab label="Détails">
                  <md-tab-body>
                    <div class="layout-padding">

                      <md-input-container  class="md-block flex-gt-sm">
                        <label>But</label>
                        <textarea ng-model="etude.but"  rows="4" ng-disabled="etude.locked"></textarea>
                      </md-input-container>
                      <md-input-container  class="md-block flex-gt-sm">
                        <label>Contexte</label>
                        <textarea ng-model="etude.context"  rows="4" ng-disabled="etude.locked"></textarea>
                      </md-input-container>
                      <md-input-container  class="md-block flex-gt-sm">
                        <label>Compétences</label>
                        <textarea ng-model="etude.competences"  rows="4" ng-disabled="etude.locked"></textarea>
                      </md-input-container>
                      <md-input-container  class="md-block flex-gt-sm">
                        <label>Cahier des charges</label>
                        <textarea ng-model="etude.specifications"  rows="4" ng-disabled="etude.locked"></textarea>
                      </md-input-container>
                      <md-input-container  class="md-block flex-gt-sm">
                        <label>Base de données</label>
                        <textarea ng-model="etude.bdd"  rows="4" ng-disabled="etude.locked"></textarea>
                      </md-input-container>
                    
                    </div>
                  </md-tab-body>
                </md-tab>



                <md-tab label="Recrutement">
                  <md-tab-body>
                    <div class="layout-padding">

                      <md-input-container class="md-block flex">
                        <label>Nom</label>
                        <input type="text" ng-model="etude.pub_titre" md-maxlength="50" ng-disabled="etude.locked">
                      </md-input-container>
                      <md-input-container  class="md-block flex-gt-sm">
                        <label>Pub</label>
                        <textarea ng-model="etude.pub"  rows="4"  md-maxlength="200" ng-disabled="etude.locked"></textarea>
                      </md-input-container>
                    
                      <p>Attention ces informations sont publics et seront accesibles par les étudiants !</p>
                    </div>
                  </md-tab-body>
                </md-tab>



              </md-tabs>
              <div layout="row" layout-align="center" style="margin-bottom: 20px;">
                <md-button ng-click="save()" class="md-accent md-raised" ng-if="!etude.locked" ng-disabled="sending">{{sending ? 'Sauvegarde en cours...' : 'Sauvegarder'}}</md-button>   
              </div>
            </md-tab>
          </div>


          <div ng-controller="EtapeEditController">          
            <md-tab label="Etapes ({{etapes.length}})" ng-disabled="!($parent.etude.id > 0)">
              <md-tab-body>
                
                <div layout="row" layout-align="center" style="margin-bottom: 20px;" ng-if="etapes.length == '0'">
                  <p >Aucune étape n'a été enregistrée.</p>
                </div>

                <md-tabs md-dynamic-height md-border-bottom  md-autoselect md-selected="vm.selectedIndex" ng-if="etapes.length != '0'">
                  <md-tab label="Etape n°{{etape.n}}" ng-repeat="etape in etapes | orderBy:'n' track by etape.n">
                    <md-tab-body>
                      <div class="layout-padding">

                        <div layout="row">
                          <md-input-container class="md-block flex">
                            <label>Nom</label>
                            <input type="text" ng-model="etape.nom" required md-maxlength="130" ng-disabled="etude.locked" >
                          </md-input-container>
                          <md-input-container class="md-block">
                            <label>Date de début</label>
                            <md-datepicker ng-model="etape.date_start" md-placeholder="Entrez une date :" md-open-on-focus ng-disabled="etude.locked"></md-datepicker>
                          </md-input-container>
                          <md-input-container class="md-block">
                            <label>Date de fin</label>
                            <md-datepicker ng-model="etape.date_end" md-placeholder="Entrez une date :" md-open-on-focus ng-disabled="etude.locked"></md-datepicker>
                          </md-input-container>
                        </div>

                        <md-input-container  class="md-block flex-gt-sm" style="margin-bottom: 0;">
                          <label>Détails</label>
                          <textarea ng-model="etape.details" rows="4" ng-disabled="etude.locked"></textarea>
                        </md-input-container>     


                        <md-subheader>Division des {{getTotJEH(etape)}} JEH :</md-subheader>             
                        <md-list>
                          <md-list-item class="md-1-line" ng-repeat="sEtape in etape.sEtapes track by sEtape.id">

                            <md-select ng-model="sEtape.etudiant" placeholder="Choisir un intervenant" ng-disabled="etude.locked">  
                              <md-option ng-value="e.id" ng-repeat="e in etude_etudiants track by e.id">{{e.prenom}} {{e.nom}}</md-option>
                            </md-select>

                            <div class="md-list-item-text" style="padding: 0 15px 0 15px;">effectue</div>

                            <md-input-container style="padding: 0 15px 0 15px;" ng-disabled="etude.locked">
                              <input type="number" ng-model="sEtape.jeh" required style="width: 50px;" min="1" ng-disabled="etude.locked">
                            </md-input-container>

                            <div class="md-list-item-text">JEH.</div>

                            <md-button class="md-secondary md-icon-button" ng-click="removeS(etape, sEtape)" ng-if="!etude.locked"><i class="material-icons">clear</i></md-button>

                          </md-list-item>                  
                        </md-list> 

                        <div layout="row" layout-align="center">
                            <md-button ng-click="new_sEtape(etape)" class="md-icon-button" ng-if="!etude.locked"><md-tooltip md-direction="top">Ajouter un intervenant</md-tooltip><i class="material-icons">add</i></md-button>
                            <md-button ng-click="up(etape)" class="md-icon-button" ng-if="!$first && !etude.locked"><md-tooltip md-direction="top">Inverser avec l'étape n°{{etape.n-1}}</md-tooltip><i class="material-icons">keyboard_arrow_left</i></md-button>
                            <md-button ng-click="down(etape)" class="md-icon-button" ng-if="!$last && !etude.locked"><md-tooltip md-direction="top">Inverser avec l'étape n°{{etape.n+1}}</md-tooltip><i class="material-icons">keyboard_arrow_right</i></md-button>
                            <md-button ng-click="clear(etape, $event)" class="md-icon-button" ng-if="!etude.locked"><md-tooltip md-direction="top">Supprimer l'étape n°{{etape.n}}</md-tooltip><i class="material-icons">clear</i></md-button>
                        </div>


                      </div>
                    </md-tab-body>
                  </md-tab>
                </md-tabs>

                <div layout="row" layout-align="center" style="margin-bottom: 20px;">
                  <md-button class="md-accent md-raised" ng-click="new()" ng-disabled="sending || etude.locked">Ajouter une étape</md-button>
                  <md-button class="md-accent md-raised" ng-click="save()" ng-disabled="sending || etude.locked">{{sending ? 'Sauvegarde en cours...' : 'Sauvegarder'}}</md-button>
                </div>

              </md-tab-body>
            </md-tab>
          </div>











          <div ng-controller="TemplateController">
            <md-tab label="Documents ({{docs.length}})" ng-disabled="!($parent.etude.id > 0)">
              <md-tab-body class="md-padding">
                <md-subheader ng-if="docs.length > 0">Documents enregistrés</md-subheader>
                <md-list>
                  <md-list-item ng-if="docs.length == '0'">
                    <p>Aucun document enregistré.</p>
                  </md-list-item>
                  <md-list-item ng-repeat="d in docs" ng-click="redirect(d.link)">
                    <div>{{d.nom}} <small>({{d.ref}})</small></div>         
                    <md-button class="md-icon-button md-secondary" ng-click="redirect(d.link)"><i class="material-icons">file_download</i></md-button>
                  </md-list-item>
                </md-list>
                <p style="color:red; text-align: center;">{{error}}</p>
                <md-progress-linear ng-if="custom_uploader.isUploading" md-mode="determinate" value="{{custom_uploader.progress}}"></md-progress-linear>
                <div layout="row" ng-if="child == null">
                    <md-button flex ng-click="add()">Ajouter un document</md-button>
                    <md-button flex ng-click="generate()">Générer</md-button>
                    <md-button flex ng-click="custom()">Custom</md-button>
                </div>
              </md-tab-body>
            </md-tab>
          </div>     


          <div ng-controller="EtudiantController">
            <md-tab label="Intervenants ({{(work_requests).length}})"  ng-disabled="!($parent.etude.id > 0)" ng-if="etude.statut >= 2">
              <md-tab-body>
                <md-list-item ng-if="work_requests.length == '0'">
                  <p>Aucune candidature reçu pour le moment.</p>
                </md-list-item>

                <section ng-if="(work_requests | filter:{statut : 0}).length > 0" >
                  <md-subheader>Candidature en attente :</md-subsubheader>
                  <md-list>
                    <md-list-item ng-repeat="w in work_requests | filter:{statut : 0}" class="md-padding">
                      <div>{{w.etudiant.prenom}} {{w.etudiant.nom}} ({{getId(formEtude.annees,w.etudiant.annee).name}})</div>
                      <div class="md-secondary">                
                        <md-button class="md-icon-button" ng-click="openZipUrl(w)" ng-disabled="sending"><md-tooltip md-direction="top">Télécharger la candidature</md-tooltip><i class="material-icons">file_download</i></md-button>
                        <md-button class="md-icon-button" ng-click="refuse(w, $event)" ng-disabled="sending" ng-if="!etude.locked"><md-tooltip md-direction="top">Refuser la candidature</md-tooltip><i class="material-icons">assignment_late</i></md-button>
                        <md-button class="md-icon-button" ng-click="accept(w, $event)" ng-disabled="sending" ng-if="!etude.locked"><md-tooltip md-direction="top">Accepter la candidature</md-tooltip><i class="material-icons">assignment_turned_in</i></md-button>
                      </div>
                    </md-list-item>
                  </md-list>
                <md-divider></md-divider>
                </section>

                <section ng-if="(work_requests | filter:{statut : 2}).length > 0">
                  <md-subheader>Candidature acceptée :</md-subheader>
                  <md-list>
                    <md-list-item ng-repeat="w in work_requests | filter:{statut : 2}" class="md-padding">
                      <div>{{w.etudiant.prenom}} {{w.etudiant.nom}} ({{getId(formEtude.annees,w.etudiant.annee).name}})</div>
                      <div class="md-secondary">                
                        <md-button class="md-icon-button" ng-click="openZipUrl(w)" ng-disabled="sending"><md-tooltip md-direction="top">Télécharger la candidature</md-tooltip><i class="material-icons">file_download</i></md-button>
                        <md-button class="md-icon-button" ng-click="refuse(w, $event)" ng-disabled="sending" ng-if="!etude.locked"><md-tooltip md-direction="top">Supprimer l'intervenant</md-tooltip><i class="material-icons">clear</i></md-button>
                      </div>
                    </md-list-item>
                  </md-list>
                <md-divider></md-divider>
                </section>

                <section ng-if="(work_requests | filter:{statut : 1}).length > 0">
                  <md-subheader>Candidature refusée :</md-subheader>
                  <md-list>
                    <md-list-item ng-repeat="w in work_requests | filter:{statut : 1}" class="md-padding">
                      <div>{{w.etudiant.prenom}} {{w.etudiant.nom}} ({{getId(formEtude.annees,w.etudiant.annee).name}})</div>
                      <div class="md-secondary">                
                        <md-button class="md-icon-button" ng-click="openZipUrl(w)" ng-disabled="sending"><md-tooltip md-direction="top">Télécharger la candidature</md-tooltip><i class="material-icons">file_download</i></md-button>
                        <md-button class="md-icon-button" ng-click="accept(w, $event)" ng-disabled="sending" ng-if="!etude.locked"><md-tooltip md-direction="top">Accepter la candidature</md-tooltip><i class="material-icons">assignment_turned_in</i></md-button>
                      </div>
                    </md-list-item>
                  </md-list>
                <md-divider></md-divider>
                </section>

              </md-tab-body>
            </md-tab>
          </div>

        </md-tabs>    
        <md-divider></md-divider>  
      </md-content>
    </div>




<?php
  include("templates/Template_Footer_1.php"); ?>

<?php
  $routeur->baliseJS("etude_edit", true, $vars);
?>

<?php include("templates/Template_Footer_2.php"); ?>