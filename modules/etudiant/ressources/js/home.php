<script type="text/javascript">
  app.controller('HomeController', function($scope, $http, $mdDialog, FileUploader) {
  	$scope.etudes = <?php echo json_encode($etudes); ?>;
  	$scope.user = handle_date(<?php echo json_encode($user); ?>);

    var work_requests = <?php echo json_encode($user->get("work_requests")); ?>;
    $scope.w_statuts =  <?php echo json_encode(WorkRequest::$statutArray); ?>;
    function add_w(w) {
      var e = $scope.getId($scope.etudes, w.etude);
      if (e != null) {e.w = w;}
    }
    work_requests.forEach(function(w) {
      add_w(w);
    });

    $scope.postuler = function($event, etude) {
       var parentEl = angular.element(document.body);
       $mdDialog.show({
         parent: parentEl,
         targetEvent: $event,
         templateUrl: "<?php echo $ressources->html_url("etudiant.Template_WorkRequest_New.html"); ?>",
         controller: DocDialogController,
         locals: {
           etude: etude,
           user : $scope.user
         },
      });

      function DocDialogController(etude, user, $scope) {
        $scope.etude = etude;
        $scope.user = user;

        var data = [{id : $scope.etude.id}];
        var url = "<?php echo $routeur->getUrlFor("AjaxWorkRequest"); ?>";

        $scope.uploaderCV = new FileUploader({
          url : url,
          alias : "cv",
          formData : data,
          
          onAfterAddingFile : function(item) {
            this.queue = [item];
            this.uploadItem(item);
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
            var resHandler = handle_response({
              success : function(data, msg) {
                          $scope.user.cv = data.cv;
                          alert("Votre CV a été enregistré !");
                        },
            });
            resHandler({data : response});
          },
        }); 
        

        $scope.uploaderL = new FileUploader({
          url : url,
          alias : "lettre",
          formData : data,
          
          onAfterAddingFile : function(item) {
            if ($scope.user.cv == undefined) {
              alert("Veuillez d'abord ajouter votre CV !");
            } else {
              this.queue = [item];
              this.uploadItem(item);
            }
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
            var resHandler = handle_response({
              success : function(data, msg) {
                          alert("Votre candidature a été enregistrée !\n Vous pouvez toujours modifier vos documents si vous le souhaitez.");
                          $scope.etude.w = data.w;
                        },
            });
            resHandler({data : response});
          },
        });     


        $scope.close = function() {
          $mdDialog.hide();
        }
      }

    };    
  });

</script>