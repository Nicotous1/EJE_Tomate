<?php
  use Admin\Entity\Etude; 
  use Admin\Entity\Client; 
  use Auth\Entity\User; 
?>

<script type="text/javascript">
  app.controller('EditController', function($scope, $mdDialog, $http) {
    $scope.etude = <?php echo json_encode($etude); ?>;
    $scope.etude_etudiants = <?php echo json_encode($etude->get("etudiants")); ?>;
    $scope.etapes = handle_date(<?php echo json_encode($etude->get("etapes")); ?>);

    if (!($scope.etude.id > 0) && $scope.etude.admins.length == 0) {
      $scope.etude.admins = [<?php echo $user->getId(); ?>];
    }
    
    $scope.etudeSaved = !($scope.etude.id > 0);
    
<?php 
  $p = $etude->get("parent");
  $parent = (empty($p)) ? null : $p[0];
?>    
    $scope.parent = <?php echo json_encode($parent); ?>;
    $scope.child = <?php echo json_encode($etude->get("child")); ?>;

    $scope.doctypes = <?php echo json_encode($doc_types); ?>;
    $scope.doctemplates = <?php echo json_encode($templates); ?>;

    $scope.formEtude = {
     provs : <?php echo json_encode(Etude::$provenanceArray); ?>,
     statuts : <?php echo json_encode(Etude::$statutArray); ?>,
     lieux : <?php echo json_encode(Etude::$lieuArray); ?>,
     entreprises : <?php echo json_encode($entreprises); ?>,
     clients : <?php echo json_encode($clients); ?>,
     annees : <?php echo json_encode(User::$anneeArray); ?>,
    };

    $scope.admins = <?php echo json_encode($admins); ?>;  

    $scope.lockEtude = function(ev) {
      var confirm = $scope.confirmDialog(ev, "Cette version sera toujours accesible mais ne sera plus éditable !", "Confimer le vérrouillage de l'étude ?");

      $mdDialog.show(confirm).then(function() {
        $scope.etude.locked = true;
        var url = "<?php echo $routeur->getUrlFor("AjaxSaveEtude") ?>";
        var resHandler = handle_response({
          success : function(data, msg) {
                      $scope.$parent.etude = data.etude;
                    },
        });
        $http.post(url, $scope.etude).then(resHandler, resHandler);
      });
    }; 

    $scope.copy = function(ev) {
      var confirm = $scope.confirmDialog(ev, "Une copie éditable de cette version sera crée.", "Confimer la copie de l'étude ?");
      $mdDialog.show(confirm).then(function() {
        var resHandler = handle_response({
          success : function(data, msg) {
                      document.location = data.link;
                    },
        });
        $http.post("<?php echo $routeur->getUrlFor("AjaxCopyEtude") ?>", $scope.etude).then(resHandler, resHandler)
      });
    };

  });





/*

  PropEditController

*/
  app.controller("PropEditController", function($scope, $http, $mdDialog, $mdToast) {

    $scope.sending = false;

    $scope.save = function() {
      console.log("posting etude :");
      console.log($scope.etude);
      $scope.sending = true;

      var resHandler = handle_response({
        success : function(data, msg) {
                    $scope.$parent.etude = data.etude;
                    $mdToast.show($mdToast.simple().textContent("#" + $scope.etude.numero + ' a été sauvegardée !').position("top right"));
                  },
        all : function(data, msg) {
                $scope.sending = false;
              }, 
      });
      $http.post("<?php echo $routeur->getUrlFor("AjaxSaveEtude") ?>", $scope.etude).then(resHandler, resHandler);
    }; 

    $scope.coherentClient = function() {
      $scope.etude.client = null;
      $scope.etude.facturation = null;
      $scope.etude.signataire = null;
    }

    $scope.editEntreprise = $scope.modal_edit_handler({
      templateUrl : "<?php echo $ressources->html_url("admin.Template_Entreprise_New"); ?>",

      resultHandler : function(e) {
        add_entity(e, $scope.formEtude.entreprises);
        $scope.etude.entreprise = e.id;
      },

      handle_e : function(e) {return $scope.getId($scope.formEtude.entreprises, e);},
      
      saveHandler : function($scope) {
        $scope.sending = true;
        var resHandler = handle_response({
          success : function(data, msg) {
                      $mdDialog.hide(data.entreprise);
                    },
          all : function(data, msg) {$scope.sending = false;}, 
        });
        $http.post("<?php echo $routeur->getUrlFor("AjaxSaveEntreprise") ?>", $scope.e).then(resHandler, resHandler);   
      },
    });



    function genEditClient(var_name) {
      return $scope.modal_edit_handler({
        templateUrl : "<?php echo $ressources->html_url("admin.Template_Client_New.html"); ?>",
        scope : $scope,
        locals : {
          entreprise : $scope.getId($scope.formEtude.entreprises,$scope.etude.entreprise),
          contacts : <?php echo json_encode(Client::$last_contactArray); ?>,
          titres : <?php echo json_encode(User::$titreArray); ?>,
        },

        resultHandler : function(e) {
          add_entity(e, $scope.formEtude.clients);
          $scope.etude[var_name] = e.id;
        },

        handle_e : function(e = null) {
          var c = {};
          if (e != null)
          {
            c = $scope.getId($scope.formEtude.clients, e);
          }
          c.entreprise = $scope.etude.entreprise
          return c
        },

        //Attention $scope de la modal
        saveHandler : function($scope) {
          $scope.sending = true;
          var resHandler = handle_response({
            success : function(data, msg) {
                        $mdDialog.hide(data.client);
                      },
            all : function(data, msg) {$scope.sending = false;}, 
          });
          $http.post("<?php echo $routeur->getUrlFor("AjaxSaveClient") ?>", $scope.e).then(resHandler, resHandler);   
        },
      });
    }
    $scope.editClient = genEditClient("client");
    $scope.editFacturation = genEditClient("facturation");
    $scope.editSignataire = genEditClient("signataire");

  });




/*

  EtudiantController

*/
  app.controller("EtudiantController", function($scope, $http, $mdDialog) {
    $scope.work_requests = <?php echo json_encode($etude->get("work_requests")); ?>; //etude_etudiants
    $scope.sending = false;

    function send(c, w, statut) {
      $mdDialog.show(c).then(function() {
        $scope.sending = true;
        var url = "<?php echo $routeur->getUrlFor("AjaxHandleWorkRequest") ?>";
        var resHandler = handle_response({
          success : function(data, msg) {
              console.log($scope.etude_etudiants);
              $scope.etude_etudiants.length = 0;

              [].push.apply($scope.etude_etudiants, data.eds);
              add_entity(data.w, $scope.work_requests);

              if (data.etapes !== undefined) {
                console.log("replace");
                $scope.etapes.length = 0;
                [].push.apply($scope.etapes, handle_date(data.etapes));        
              }
            },
          all : function(data, msg) {$scope.sending = false;}, 
        });
        $http.post(url, {etude : $scope.etude.id, statut : statut, w : w.id}).then(resHandler, resHandler);
      }); 
    }

    $scope.refuse = function(work_request, ev) {
      var confirm = $scope.confirmDialog(ev, "Êtes vous sur de refuser la candidature ?", "Refuser " + work_request.etudiant.nom + "?");
      send(confirm, work_request, 1);
    };

    $scope.accept= function(work_request, ev) {
      var confirm = $scope.confirmDialog(ev, "Êtes vous sur de valider la candidature ?", "Accepter " + work_request.etudiant.nom + "?");
      send(confirm,work_request, 2);
    };

    $scope.openZipUrl = function(w) {
      document.location = w.zip_url;
    }
  });





/*

  EtapeEditController  

*/
  app.controller("EtapeEditController", function($scope, $http, $mdToast, $mdDialog) {

    $scope.vm = {
      selectedIndex: 0
    };    

    $scope.new = function() {
      var etape = handle_date(<?php echo json_encode(new Etape()); ?>);
      if ($scope.etapes.length > 0) {
        var d =$scope.etapes[$scope.etapes.length-1].date_end.getDate();
        etape.date_start = new Date();
        etape.date_start.setDate(d+1);
        etape.date_end = new Date();
        etape.date_end.setDate(d+2);
      }

      etape.etude = $scope.$parent.etude.id;
      etape.sEtapes.push(<?php echo json_encode(new sEtape()); ?>);


      //Find better new n -> fille gap if exists and assure unicity !
      var new_n = $scope.etapes.length + 1;
      var ids_taken = [];
      angular.forEach($scope.etapes, function(x) {ids_taken.push(x.n);});
      ids_taken.sort(); done = false;
      angular.forEach(ids_taken, function(x,i) {
        if (done) {return;}
        if (x != (i+1)) { new_n = i+1; done = true;return;}
      });
      etape.n = new_n;

      $scope.etapes.push(etape);
      console.log($scope.etapes);
    };

    $scope.new_sEtape = function(etape) {
      var sEtape = <?php echo json_encode(new sEtape()); ?>;
      sEtape.etape = etape.id;
      etape.sEtapes.push(sEtape);
    }

    $scope.getTotJEH = function(etape) {
      var tot = 0;
      angular.forEach(etape.sEtapes, function(sEtape) {tot += sEtape.jeh;});
      return tot;
    }

    $scope.removeS = function(etape, sEtape) {
        var done = false;
        angular.forEach(etape.sEtapes, function(x,i) {
          if (done) {return;}
          if (x === sEtape) {
            etape.sEtapes.splice(i, 1)
            done = true; return;
          }
        });
      };

    $scope.up = function(etape) {
      var n_pivot = etape.n;
      angular.forEach($scope.etapes, function(x,i) {
        if (x.n == n_pivot - 1) {x.n = x.n + 1; return;}
        if (x.n == n_pivot) { x.n = x.n - 1; return;}
      });
      $scope.vm.selectedIndex -= 1;
    };

    $scope.down = function(etape) {
      var n_pivot = etape.n;
      angular.forEach($scope.etapes, function(x,i) {
        if (x.n == n_pivot + 1) { x.n = x.n - 1; return;}
        if (x.n == n_pivot) { x.n = x.n + 1; return;}
      });
      $scope.vm.selectedIndex += 1;
    };

    $scope.clear = function(etape, ev) {

      var confirm = $scope.confirmDialog(ev, "L'étape sera supprimée lorsque vous enregistrerez les étapes de l'étude.\nSi vous souhaitez récupérer une étape n'enregistrez pas après la suppresion et actualisez la page.", "Confimer la suppresion de l'étape n°" + etape.n + " ?");

      $mdDialog.show(confirm).then(function() {
        var done = false;
        angular.forEach($scope.etapes, function(x,i) {
          if (done) {return;}
          if (x === etape) {
            $scope.etapes.splice(i, 1);;
            //Décale vers le bas les autres indices
            angular.forEach($scope.etapes, function(y,j) {
              if (etape.n <= y.n) {y.n--;}
            });
            done = true; return;
          }
        });
      });
    };

    function sort() {
      function compare(a,b) {
        if (a.n < b.n)
          return -1;
        if (a.n > b.n)
          return 1;
        return 0;
      }

      $scope.etapes.sort(compare); 
    };

    $scope.save = function() {
      console.log("posting etapes :");
      console.log($scope.etapes);
      $scope.sending = true;

      var resHandler = handle_response({
        success : function(data, msg) {
                    $scope.etapes = handle_date(data.etapes);
                    $mdToast.show($mdToast.simple().textContent("Les étapes ont été sauvegardées ").position("top right"));
                  },
        all : function(data, msg) {$scope.sending = false;}, 
      });
      $http.post("<?php echo $routeur->getUrlFor("AjaxSaveEtapes") ?>", {etapes : $scope.etapes, etude_id : $scope.$parent.etude.id}).then(resHandler, resHandler);
    }

  });





/*

  TemplateController

*/
  app.controller("TemplateController", function($scope, $http, $mdDialog, FileUploader) {
    $scope.error = null;
    $scope.docs = handle_date(<?php echo json_encode($etude->get("docs")); ?>);

    $scope.add = function($event) {
      $scope.error = null;
       var parentEl = angular.element(document.body);
       $mdDialog.show({
         parent: parentEl,
         targetEvent: $event,
         templateUrl: "<?php echo $ressources->html_url("admin.Template_Doc_New"); ?>",
         controller: DocDialogController,
         locals: {
           doctypes: $scope.doctypes,
           etude_id: $scope.etude.id,
         },
      }).then(function(e = null) {
        if (e !== null) {
          $scope.docs.push(e);
        }
      });

      function DocDialogController(etude_id, doctypes, $scope, $mdDialog, $http) {
        $scope.doctypes = doctypes;
        $scope.doc = {etude : etude_id};

        var url = "<?php echo $routeur->getUrlFor("AjaxAddDocEtude"); ?>";
        $scope.uploader = new FileUploader({
          url : url,
          alias : "doc",
          formData : [$scope.doc],
          
          onAfterAddingFile : function(item) {
            this.queue = [item];
          },

          filters: [{
              name: 'pdf',
              // A user-defined filter
              fn: function(item) {
                  var name = item.name;
                  var ext = name.substr(name.lastIndexOf('.') + 1);
                  if (ext != "pdf") {
                    alert("'" + name + "' n'est pas un fichier PDF ! (.pdf)"); return false;
                  }
                  return true;
              }
          }],

          onCompleteItem : function(item, response, status, headers) {
            console.log(response);
            if (response.res === true) {
              $mdDialog.hide(response.doc);
            } else {
              alert("Une erreur s'est produite contactez votre DSI !");
            }
          },
        });   

        $scope.close = function() {
          $mdDialog.hide();
        }

        $scope.add = function() {
          console.log("posting doc....");
          console.log($scope.doc);
          $scope.uploader.uploadAll(); 
        }
      }
    };

    $scope.custom = function($event) {
      $scope.error = null;
       var parentEl = angular.element(document.body);
       $mdDialog.show({
         parent: parentEl,
         targetEvent: $event,
         templateUrl: "<?php echo $ressources->html_url("admin.Template_Doc_Custom"); ?>",
         controller: DocDialogController,
         locals: {
           doctypes: $scope.doctypes,
           etude_id: $scope.etude.id,
         },
      });

      function DocDialogController(etude_id, doctypes, $scope, $mdDialog, $http) {
        $scope.doctypes = doctypes;
        $scope.doc = {etude : etude_id, type : -1};

        var url = "<?php echo $routeur->getUrlFor("AjaxGenCustomTemplate"); ?>";
        $scope.uploader = new FileUploader({
          url : url,
          alias : "doc",
          formData : [$scope.doc],
          
          onAfterAddingFile : function(item) {
            this.queue = [item];
          },

          filters: [{
              name: 'docx',
              // A user-defined filter
              fn: function(item) {
                  var name = item.name;
                  var ext = name.substr(name.lastIndexOf('.') + 1);
                  if (ext != "docx") {
                    alert("'" + name + "' n'est pas un fichier Word ! (.docx)"); return false;
                  }
                  return true;
              }
          }],

          onCompleteItem : function(item, response, status, headers) {
            console.log(response);
            var data = response;
            if (data.res === true) {
              $scope.error = null;
              console.log("redirect -> " + data.link);
              $scope.error = null;
              location.href = data.link;
            } else {
              var msg = "Une erreur complexe est arrivée. Contactez votre DSI !";
              if (data.msg !== undefined) {msg = data.msg;}
              $scope.error = msg;
            }
          },
        });   

        $scope.close = function() {
          $mdDialog.hide();
        }

        $scope.generate = function() {
          console.log("posting doc....");
          console.log($scope.doc);
          $scope.uploader.uploadAll(); 
        }
      }
    };

    $scope.generate = function($event) {
       var parentEl = angular.element(document.body);
        $scope.error = null;
       $mdDialog.show({
         parent: parentEl,
         targetEvent: $event,
         templateUrl: "<?php echo $ressources->html_url("admin.Template_Doc_Generate"); ?>",
         controller: DocDialogController,
         locals: {
           doctemplates: $scope.doctemplates,
           etude_id: $scope.etude.id,
         },
      }).then(function(e = null) {
        if (e !== null) {
          $scope.docs.push(e);
        }
      });

      function DocDialogController(etude_id, doctemplates, $scope, $mdDialog, $http) {
        $scope.doctemplates = doctemplates;
        $scope.doc = {etude : etude_id};
        $scope.sending = false;
        $scope.error = null;

        var url = "<?php echo $routeur->getUrlFor("AjaxGenDocTemplate"); ?>";

        $scope.generate = function() {
          $scope.sending = true;
          $http.post(url, $scope.doc).success(function(data){
            console.log(data);
            $scope.sending = false;
            if (data.res === true) {
              location.href = data.link;
              $mdDialog.hide();
            } else {
              var msg ="Une erreur complexe est arrivée. Contactez votre DSI !";
              if (data.msg !== undefined) {msg = data.msg;}
              console.log("yolo" + msg);
              $scope.error = msg;
            }
          }).error(function() {
            $scope.sending = false;
            $scope.error = "Une erreur complexe est arrivée. Contactez votre DSI !";
          });  
        };

        $scope.close = function() {
          $mdDialog.hide();
        }

        $scope.add = function() {
          console.log("posting doc....");
          console.log($scope.doc);
          $scope.uploader.uploadAll(); 
        }
      }
    };

  });
</script>