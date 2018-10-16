<?php require("templates/Template_Header.php"); ?>

    <div  class="md-whiteframe-z2" style="padding: 0;" ng-controller="EditController">
      <md-toolbar>
        <div class="md-toolbar-tools">
          <span>
            <span ng-if="etude.numero">#{{etude.numero}}</span>
            <span ng-if="etude.pseudo"> : {{etude.pseudo}}</span>
            <span ng-if="etude.pseudo || etude.numero"> ({{formEtude.statuts[etude.statut].name}})</span>
          </span>
          <span ng-if="!etude.id && !etude.pseudo && !etude.numero">Nouvelle étude</span>
          <span flex></span>
          <div ng-if="etude.id">   
            <md-button class="md-icon-button" ng-if="parent != null" ng-click="redirect(parent.link)">
              <md-icon>arrow_back</md-icon>
              <md-tooltip md-direction="down">Voir la sauvegarde.</md-tooltip>
            </md-button>

            <md-button class="md-icon-button" ng-if="etude.child != null" ng-click="redirect(child.link)">
              <md-icon>arrow_forward</md-icon>
              <md-tooltip md-direction="down">Voir l'avenant.</md-tooltip>
            </md-button>

            <md-button class="md-icon-button" ng-if="!etude.child" ng-click="copy($event)">
              <md-icon>content_copy</md-icon>
              <md-tooltip md-direction="down">Faire un avenant.</md-tooltip>
            </md-button> 
<!--
            <md-button class="md-icon-button" ng-if="!etude.locked" ng-click="lockEtude($event)">
              <md-icon>lock_open</md-icon>
              <md-tooltip md-direction="down">Vérouiller l'étude.</md-tooltip>
            </md-button>
!-->
            <md-button class="md-icon-button" ng-if="etude.locked">
              <md-icon>lock_close</md-icon>
              <md-tooltip md-direction="down">L'étude est vérouillée pour cause d'avenant.</md-tooltip>
            </md-button>
          </div>
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

                        <md-input-container class="md-block flex-20" ng-if="etude.compt_statut">
                          <label>Statut comptable</label>
                          <md-select ng-model="etude.compt_statut" placeholder="Statut" ng-disabled="etude.locked">  
                            <md-option ng-value="statut.id" ng-repeat="statut in formEtude.statuts_compt track by statut.id">{{statut.name}}</md-option>
                          </md-select>
                        </md-input-container>
                      </div>

                      <div layout="row">  
                        <md-input-container class="md-block flex">
                          <label>Administrateurs</label>
                          <md-select ng-model="etude.admins" placeholder="Administrateurs" multiple ng-disabled="etude.locked">  
                            <md-option ng-value="{{admin.id}}" ng-repeat="admin in admins | orderBy:'prenom' track by admin.id">{{admin.prenom}} {{admin.nom}}</md-option>
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
                          <md-select ng-model="etude.domaines" multiple ng-disabled="etude.locked">  
                            <md-option ng-value="{{domaine.id}}" ng-repeat="domaine in formEtude.domaines | orderBy:'long'  track by domaine.id">{{domaine.long}}</md-option>
                          </md-select>                        
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

                        <md-input-container class="md-block flex">
                          <label>Pourcentage de rémunération</label>
                          <input type="number" ng-model="etude.per_rem" ng-disabled="etude.locked" step="1" min="0" max="100">
                        </md-input-container>

                        <md-input-container  class="md-block flex">
                          <label>Prix de la JEH</label>
                          <input type="number" min="80" max="400" ng-model="etude.p_jeh" step="10" ng-disabled="etude.locked">
                        </md-input-container>    

                        <md-input-container class="md-block flex" ng-disabled="etude.locked">
                          <label>Nombre JEH rupture</label>
                          <input type="number" ng-model="etude.break_jeh" step="1" ng-disabled="etude.locked">
                        </md-input-container>  

                        <md-input-container class="md-block flex">
                          <label>Frais rupture</label>
                          <input type="number" ng-model="etude.break_fee" ng-disabled="etude.locked" step="0.01">
                        </md-input-container>

                        <md-input-container class="md-block flex">
                          <label>Statut</label>
                          <md-select ng-model="etude.compt_statut" placeholder="Statut" ng-disabled="etude.locked">  
                            <md-option ng-value="statut.id" ng-repeat="statut in formEtude.statuts_compt track by statut.id">{{statut.name}}</md-option>
                          </md-select>
                        </md-input-container>
                    </div>
                  </md-tab-body>
                </md-tab>



                <md-tab label="Détails" md-active="parent != null && !etude.avn_motif">
                  <md-tab-body>
                    <div class="layout-padding">

                      <md-input-container  class="md-block flex-gt-sm" ng-if="parent != null">
                        <label>Raison de l'avenant</label>
                        <textarea ng-model="etude.avn_motif"  rows="4" ng-disabled="etude.locked"></textarea>
                      </md-input-container>

                      <md-input-container  class="md-block flex-gt-sm">
                        <label>Contexte</label>
                        <textarea ng-model="etude.context"  rows="4" ng-disabled="etude.locked"></textarea>
                      </md-input-container>

                      <md-input-container  class="md-block flex-gt-sm">
                        <label>But</label>
                        <textarea ng-model="etude.but"  rows="4" ng-disabled="etude.locked"></textarea>
                      </md-input-container>

                      <md-input-container  class="md-block flex-gt-sm">
                        <label>(Cahier des charges ) EJE fournira au client les prestations suivantes :</label>
                        <textarea ng-model="etude.specifications"  rows="4" ng-disabled="etude.locked"></textarea>
                      </md-input-container>
                      
                      <md-input-container  class="md-block flex-gt-sm">
                        <label>Base de données</label>
                        <textarea ng-model="etude.bdd"  rows="4" ng-disabled="etude.locked"></textarea>
                      </md-input-container>

                      <md-input-container  class="md-block flex-gt-sm">
                        <label>Compétences</label>
                        <textarea ng-model="etude.competences"  rows="4" ng-disabled="etude.locked"></textarea>
                      </md-input-container>

                      <md-input-container  class="md-block flex-gt-sm">
                        <label>L'étude consiste en</label>
                        <textarea ng-model="etude.but_short"  rows="4" ng-disabled="etude.locked" md-maxlength="400"></textarea>
                      </md-input-container>
                    
                    </div>
                  </md-tab-body>
                </md-tab>



                <md-tab label="Recrutement" ng-disabled="!etude.numero">
                  <md-tab-body>
                    <div class="layout-padding">

                      <md-input-container class="md-block flex">
                        <label>Titre</label>
                        <input type="text" ng-model="etude.pub_titre" md-maxlength="50" ng-disabled="etude.locked">
                      </md-input-container>
                      <md-input-container  class="md-block flex-gt-sm">
                        <label>Description</label>
                        <textarea ng-model="etude.pub"  rows="4"  md-maxlength="200" ng-disabled="etude.locked"></textarea>
                      </md-input-container>
                    
                      <p style="margin-bottom: 0;">Aperçu de l'offre pour les étudiants :</p>

                      <md-list>
                          <md-list-item class="md-2-line">
                            <div class="md-list-item-text" layout="column"  style="margin-top: 0;">
                              <h4>#{{etude.numero}} : {{etude.pub_titre}}</h4>
                              <p style="white-space: pre-line;">{{etude.pub | cut}}</p>
                              <p style="color: red;">Aucune candidature enregistrée.</p>
                            </div>
                            <md-divider></md-divider>
                          </md-list-item>
                      </md-list>         
                    </div>
                  </md-tab-body>
                </md-tab>



                <md-tab label="Notes" ng-disabled="">
                  <md-tab-body>
                    <div class="layout-padding">

                      <md-input-container  class="md-block flex-gt-sm">
                        <label>Notes diverses</label>
                        <textarea ng-model="etude.notes"  rows="15" ng-disabled="$parent.etude.child"></textarea>
                      </md-input-container>

                    </div>
                  </md-tab-body>
                </md-tab>



              </md-tabs>
              <div layout="row" layout-align="center" style="margin-bottom: 20px;" ng-if="$parent.etude.child == null">
                <md-button ng-click="save()" class="md-accent md-raised" ng-disabled="sending">{{sending ? 'Sauvegarde en cours...' : 'Sauvegarder'}}</md-button>   
              </div>
            </md-tab>
          </div>


          <div ng-controller="EtapeEditController">          
            <md-tab label="étapes ({{etapes.length}})" ng-disabled="!($parent.etude.id > 0)">
              <md-tab-body>
                
                <div layout="row" layout-align="center" style="margin-bottom: 20px;" ng-if="etapes.length == '0'">
                  <p >Aucune étape n'a été enregistrée.</p>
                </div>

                <md-tabs md-dynamic-height md-border-bottom  md-autoselect md-selected="vm.selectedIndex" ng-if="etapes.length != '0'">
                  <md-tab label="étape n°{{etape.n}}" ng-repeat="etape in etapes | orderBy:'n' track by etape.n">
                    <md-tab-body>
                      <div class="layout-padding">

                        <div layout="row">
                          <md-input-container class="md-block flex">
                            <label>Nom</label>
                            <input type="text" ng-model="etape.nom" required md-maxlength="130" ng-disabled="etude.locked" >
                          </md-input-container>
                          <md-input-container class="md-block">
                            <label>Date de début</label>
                            <md-datepicker ng-model="etape.date_start" ng-change="updateDate(etape)" md-placeholder="Entrez une date :" md-open-on-focus ng-disabled="etude.locked"></md-datepicker>
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
                          <md-list-item class="md-1-line" ng-repeat="sEtape in etape.sEtapes">

                            <md-select ng-model="sEtape.etudiant" placeholder="Choisir un intervenant" ng-disabled="etude.locked" ng-change="pushIntervenant(sEtape.etudiant)">  
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

                  <md-tab label="Planning">

                    <md-tab-body>
                      <div class="layout-padding">

                        <div layout="row" ng-repeat="etape in etapes | orderBy:'n' track by etape.n">
                          <md-input-container class="md-block">
                            <label>Date de début</label>
                            <md-datepicker ng-model="etape.date_start" ng-change="updateDate(etape)" md-placeholder="Entrez une date :" md-open-on-focus ng-disabled="etude.locked"></md-datepicker>
                          </md-input-container>
                          <md-input-container class="md-block">
                            <label>Date de fin</label>
                            <md-datepicker ng-model="etape.date_end" md-placeholder="Entrez une date :" md-open-on-focus ng-disabled="etude.locked"></md-datepicker>
                          </md-input-container>
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
                  <md-list-item ng-repeat="d in docs">
                    <div>{{d.nom}}<small ng-if="d.ref != false"> ({{d.ref}})</small><small ng-if="d.archived"> - Archivé</small></div>      
                    <md-button class="md-icon-button md-secondary" ng-click="archive($event, d)">
                      <i class="material-icons">{{d.archived ? 'clear' : 'archive'}}</i>
                      <md-tooltip md-direction="left">{{d.archived ? 'Déclarer comme perdu' : 'Déclarer comme archivé'}}</md-tooltip>
                    </md-button>       
                    <md-button class="md-icon-button md-secondary" ng-click="redirect(d.link)">
                      <i class="material-icons">file_download</i>
                    </md-button>   
<?php if ($user->get("quali")) { ?>
                    <md-button class="md-icon-button md-secondary" ng-click="delete($event, d)">
                      <i class="material-icons">delete</i>
                      <md-tooltip md-direction="right">Supprimer ce document</md-tooltip>
                    </md-button>
<?php } ?>                    
                  </md-list-item>
                </md-list>
                <p style="color:red; text-align: center;">{{error}}</p>
                <md-progress-linear ng-if="custom_uploader.isUploading" md-mode="determinate" value="{{custom_uploader.progress}}"></md-progress-linear>

                <md-divider></md-divider>

                <div layout="row" ng-if="$parent.etude.child == null" layout-align="center center">
                    <md-button flex ng-click="generate()">Générer</md-button>
                    <md-button ng-click="ask()" flex>Demander un document</md-button>                  
                    <md-button flex ng-click="add()" ng-if="user.quali">Ajouter un document</md-button>     
                    <md-button flex ng-click="custom()" ng-if="user.quali">Custom</md-button>
                </div>
              </md-tab-body>
            </md-tab>
          </div>     



<!-- C'est archi degeulasse ! -->
          <div ng-controller="EtudiantController">
            <md-tab label="Intervenants ({{(work_requests).length}})"  ng-disabled="!($parent.etude.id > 0)" ng-if="etude.statut >= 2">
              <md-tab-body>
                <md-list-item ng-if="work_requests.length == '0'">
                  <p>Aucune candidature reçu pour le moment.</p>
                </md-list-item>

                <section ng-if="(work_requests | filter:{statut : 0}).length > 0" >
                  <md-subheader>Candidature en attente :</md-subsubheader>
                  <md-list>
                    <md-list-item ng-repeat="w in work_requests | filter:{statut : 0}" class="md-padding" style="padding : 2px 15px;">
                      <div>{{w.etudiant.prenom}} {{w.etudiant.nom}} ({{getId(formEtude.annees,w.etudiant.annee).name}} - {{ w.etudiant.nationality}})</div>
                      <div class="md-secondary">       
                        <md-button class="md-icon-button" ng-click="editUser($event, w.etudiant)" ng-disabled="sending"><md-tooltip md-direction="top">Voir l'étudiant</md-tooltip><i class="material-icons">assignment_ind</i></md-button>         
                        <md-button class="md-icon-button" ng-click="openZipUrl(w)" ng-disabled="sending"><md-tooltip md-direction="top">Télécharger la candidature</md-tooltip><i class="material-icons">file_download</i></md-button>
                        <md-button class="md-icon-button" ng-click="refuse(w, $event)" ng-disabled="sending"><md-tooltip md-direction="top">Refuser la candidature</md-tooltip><i class="material-icons">assignment_late</i></md-button>
                        <md-button class="md-icon-button" ng-click="accept(w, $event)" ng-disabled="sending"><md-tooltip md-direction="top">Accepter la candidature</md-tooltip><i class="material-icons">assignment_turned_in</i></md-button>
                      </div>
                    </md-list-item>
                  </md-list>
                <md-divider></md-divider>
                </section>

                <section ng-if="(work_requests | filter:{statut : 2}).length > 0">
                  <md-subheader>Candidature acceptée :</md-subheader>
                  <md-list>
                    <md-list-item ng-repeat="w in work_requests | filter:{statut : 2}" class="md-padding" style="padding : 2px 15px;">
                      <div>{{w.etudiant.prenom}} {{w.etudiant.nom}} ({{getId(formEtude.annees,w.etudiant.annee).name}} - {{ w.etudiant.nationality}})</div>
                      <div class="md-secondary">                
                        <md-button class="md-icon-button" ng-click="editUser($event, w.etudiant)" ng-disabled="sending"><md-tooltip md-direction="top">Voir l'étudiant</md-tooltip><i class="material-icons">assignment_ind</i></md-button>
                        <md-button class="md-icon-button" ng-click="openZipUrl(w)" ng-disabled="sending"><md-tooltip md-direction="top">Télécharger la candidature</md-tooltip><i class="material-icons">file_download</i></md-button>
                        <md-button class="md-icon-button" ng-click="delete(w, $event)" ng-disabled="sending"><md-tooltip md-direction="top">Supprimer l'intervenant</md-tooltip><i class="material-icons">clear</i></md-button>
                      </div>
                    </md-list-item>
                  </md-list>
                <md-divider></md-divider>
                </section>

                <section ng-if="(work_requests | filter:{statut : 1}).length > 0">
                  <md-subheader>Candidature refusée :</md-subheader>
                  <md-list>
                    <md-list-item ng-repeat="w in work_requests | filter:{statut : 1}" class="md-padding" style="padding : 2px 15px;">
                      <div>{{w.etudiant.prenom}} {{w.etudiant.nom}} ({{getId(formEtude.annees,w.etudiant.annee).name}} - {{ w.etudiant.nationality}})</div>
                      <div class="md-secondary">    
                        <md-button class="md-icon-button" ng-click="editUser($event, w.etudiant)" ng-disabled="sending"><md-tooltip md-direction="top">Voir l'étudiant</md-tooltip><i class="material-icons">assignment_ind</i></md-button>            
                        <md-button class="md-icon-button" ng-click="openZipUrl(w)" ng-disabled="sending"><md-tooltip md-direction="top">Télécharger la candidature</md-tooltip><i class="material-icons">file_download</i></md-button>
                        <md-button class="md-icon-button" ng-click="accept(w, $event)" ng-disabled="sending"><md-tooltip md-direction="top">Accepter la candidature</md-tooltip><i class="material-icons">assignment_turned_in</i></md-button>
                      </div>
                    </md-list-item>
                  </md-list>
                <md-divider></md-divider>
                </section>

              </md-tab-body>
            </md-tab>
          </div>

          <div ng-controller="ComsController" >
            <md-tab label="Coms ({{(coms).length}})" ng-disabled="!($parent.etude.id > 0)">
              <md-tab-body layout="row">

                <form ng-if="!$parent.etude.child" ng-submit="save(com)" layout="row" class="md-padding" style="padding-bottom: 0; margin-bottom: 0;">
                  <md-input-container flex style="margin-bottom: 0;">
                    <label>Que se passe t-il dans votre étude ?</label>
                    <textarea ng-model="com.content"  rows="5" ng-focus="init_coms(com)"></textarea>
                  </md-input-container>
                  <div layout="row" layout-align="center">
                    <md-button class="" ng-disabled="sending" type="submit">{{sending ? 'Sauvegarde en cours...' : 'Poster'}}</md-button>   
                  </div>
                </form>

                <md-divider></md-divider>




                <md-list style="padding:0;"  md-no-ink flex>
                  <md-list-item ng-if="coms.length == 0" style="padding: 10px; border-bottom: solid 1px rgb(220,220,220);">
                    <div>Aucun commentaire</div>
                  </md-list-item>

                  <md-list-item ng-click="c.edit = true; c.temp = c.content" ng-repeat="c in coms | orderBy:'date': true track by c.id" ng-init="c.temp = 'dzdzdzdz'; c.edit = false" style="border-bottom: solid 1px rgb(220,220,220);" layout="row">


                    <div class="md-list-item-text" layout="column" flex>
                      <p style="white-space: pre-line;" flex ng-if="!c.edit">{{c.content}}</p>

                      <md-input-container ng-if="c.edit" flex style="margin-top: 40px;">
                        <label>Edition du commentaire :</label>
                        <textarea ng-model="c.temp"  rows="4" ng-disabled="sending"></textarea>
                      </md-input-container>  

                      <p style="text-align: right; margin: 0; font-size: 15px;" flex >{{c.author.prenom}} {{c.author.nom}}, le {{c.date | date:'dd/MM/yyyy à HH:mm'}}<span ng-if="c.etude != $parent.etude.id"> <i>(sauvegarde)</i></span></p>
                    </div>


                    <div ng-if="c.edit"layout-align="center center" layout="column">
                      <div layout="row">
                        <md-button class="md-icon-button" ng-click="c.edit = false" ng-disabled="sending">
                          <i class="material-icons">clear</i>
                          <md-tooltip md-direction="top">Annuler</md-tooltip>
                        </md-button>           
                        <md-button class="md-icon-button" ng-click="save(c)" ng-disabled="sending">
                          <i class="material-icons">save</i>
                          <md-tooltip md-direction="top">Sauvegarder le commentaire</md-tooltip>
                        </md-button>  
                      </div>             
                      <div layout="row">      
                        <md-button class="md-icon-button" ng-click="delete(c, $event)" ng-disabled="sending">
                          <i class="material-icons">delete</i>
                          <md-tooltip md-direction="down">Supprimer le commentaire</md-tooltip>
                        </md-button>
                      </div>
                    </div>


                  </md-list-item>

                </md-list>
              </md-tab-body>            
            </md-tab>
          </div>

        </md-tabs>    
        <md-divider></md-divider>  
      </md-content>
    </div>




<?php
  require("templates/Template_Footer_1.php"); ?>

<?php
  $ressources->js("admin/etude_edit", $vars);
?>

<?php require("templates/Template_Footer_2.php"); ?>
