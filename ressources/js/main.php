<?php
  use Core\Routeur;
?>

<script type="text/javascript">

    /**
     * You must include the dependency on 'ngMaterial' 
     */
    var app = angular.module('BlankApp', ['ngMaterial','angularFileUpload']);

    app.controller('MainController', function($scope, $mdSidenav, $mdDialog) {
      $scope.openLeftMenu = function() {
        $mdSidenav('left').toggle();
      };

      $scope.getId = function(array, id) {
        if (array == null) {return null;}
        var res = null;
        array.forEach(function(e) {
          if (res === null && e.id == id) {res = e;}
        });
        return res;
      }

      $scope.redirect = function(url, blank = false) {
        if (blank) {
            window.open(url);
        } else {
          location.href = url;
        }
      }

      $scope.confirmDialog = function(ev, content, title="Êtes-vous sur ?") {
        return $mdDialog.confirm()
                .title(title)
                .textContent(content)
                .ariaLabel(title)
                .targetEvent(ev)
                .ok("Je suis prêt")
                .cancel("Je préfére demander au Prez !");
      }

      $scope.signOut = function() {
        window.location.href = "<?php echo $firewall->getUrlFor("logout_road"); ?>";
      };

      $scope.modal_edit_handler = function(p) {
        return function(ev, e = null) {
          if (p.initHandler !== undefined) {p.initHandler(e);}
          if (e === null && p.e_default !== undefined) {e = p.e_default;} else { e = p.handle_e(e);}
          var e = jQuery.extend({}, e);
           var parentEl = angular.element(document.body);
           $mdDialog.show({
             parent: parentEl,
             targetEvent: ev,
             templateUrl: p.templateUrl,
             controller: EditModalController,
             locals : {p : p, e : e, main : p.scope},
          }).then(function(e = null) {
            if (e != null && e.id > 0) {
              p.resultHandler(e);
            }
          });

          function EditModalController(p, e, main, $scope, $mdDialog, $http) {
            $scope.p = p.locals;
            $scope.e = e;
            $scope.main = main;

            $scope.close = function() {
              $mdDialog.hide();
            }

            $scope.save = function() {  
              console.log("posting entity....");
              console.log($scope.e);
              return p.saveHandler($scope);
            }
          }
        }
      }
    });

  function handle_response(p) {


    return function(response) {
      success = p.success;
      failed = p.failed;
      data = response.data;
      console.log("Response :");
      console.log(data);
      if (data.res === true) {
        var msg = "La requête s'est bien éxécutée.";
        if (data.msg !== undefined) {msg = data.msg;}
        if (success === undefined) {success = function(data, msg) {alert(msg); return true;}}

        if (p.all !== undefined) {p.all(data, msg);}
        return success(data, msg);
      } else {
        var msg = "Une erreur est arrivée. Rafraîchissez et réessayez ou contactez la JE !";
        if (data.msg !== undefined) {msg = data.msg;}
        if (failed === undefined) {failed = function(data, msg) {alert(msg); return false;};}

        if (p.all !== undefined) {p.all(data, msg);}
        return failed(data, msg);
      }
    }
  }

  function handle_date(obj) {
    if (obj.constructor === Array) {
      angular.forEach(obj, function(x,i) {
        obj[i] = handle_date(x);
      });
    } else {
      for (var property in obj) {
        if (obj.hasOwnProperty(property)) {
          if (property.substring(0,4) == "date") {
            obj[property] = new Date(obj[property]);
          }
        }
      }
    }
    return obj;
  }  

  function add_entity(e, a) {
    if (e == null || !(e.id > 0)) {return false;}
    var done = false;
    angular.forEach(a, function(x,i) {
      if (!done && x.id == e.id) {done = true; a[i] = e;}
    });
    if (!done) {a.push(e);}
    return true;
  }

  function remove_entity(e, a) {
    var done = false;
    angular.forEach(a, function(x,i) {
      if (done) {return;}
      if (x === e || x.id == e.id) {
        a.splice(i, 1)
        done = true; return;
      }
    });    
  }




  app.config(function($mdDateLocaleProvider) {

    // Example of a French localization.
    $mdDateLocaleProvider.months = ['Janvier', "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];
    $mdDateLocaleProvider.shortMonths = ['Janv', "Févr", "Mars", "Avril", "Mai", "Juin", "Juil", "Août", "Sept", "Oct", "Nov", "Déc"];
    $mdDateLocaleProvider.days = ['Dimanche', "Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi", "Dimanche"];
    $mdDateLocaleProvider.shortDays = ['Dim', "Lun", "Mar", "Mer", "Jeu", "Ven", "Sam", "Dim"];

    // Can change week display to start on Monday.
    $mdDateLocaleProvider.firstDayOfWeek = 1;

    // Example uses moment.js to parse and format dates.
    $mdDateLocaleProvider.parseDate = function(dateString) {
      var m = moment(dateString, 'DD/MM/YYYY', true);
      return m.isValid() ? m.toDate() : new Date(NaN);
    };

    $mdDateLocaleProvider.formatDate = function(date) {
      var m = moment(date);
      return m.isValid() ? m.format('DD/MM/YYYY') : '';
    };

    $mdDateLocaleProvider.weekNumberFormatter = function(weekNumber) {
      return 'Semaine ' + weekNumber;
    };

    $mdDateLocaleProvider.msgCalendar = 'Calendrier';
    $mdDateLocaleProvider.msgOpenCalendar = 'Ouvrir le calendrier';
  });

  app.filter('cut', function () {
        return function (str, lim = 200) {
            if (str == undefined) {return "";}
            if (str.length > lim) {
              return str.substring(0, lim - 3) + "...";
            }
            return str;
        };
    });



  app.filter('time_past', function () {
        return function (d) {
            var d = new Date(d);
            var diff = Math.abs(new Date() - d.getTime())/1000;
            var res = 'un moment';

            if (diff < 60) {
              res =  "quelques secondes";
            }
            else if (diff < 3600) {
              var i = Math.round(diff/60);
              res = String(i) + " minute" + ((i > 1) ? "s" : '');
            }
            else if (diff < 86400) {
              var i = Math.round(diff/3600);
              res = String(i) + " heure" + ((i > 1) ? "s" : '');
            }
            else if (diff < 604800) {
              var i = Math.round(diff/86400);
              res = String(i) + " jour" + ((i > 1) ? "s" : '');
            }
            else if (diff < 18144000) {
              var i = Math.round(diff/604800);
              res = String(i) + " semaine" + ((i > 1) ? "s" : '');
            }
            else if (diff < 217728000) {
              var i = Math.round(diff/18144000);
              res = String(i) + " mois";
            } else {
              var i = Math.round(diff/217728000);
              res = String(i) + " ans";
            }

            return "il y a " + res + "";
        };
    });




/* SEARCH BAR */
    app.controller('SearchBar', function($scope, $q, $http) {
      $scope.querySearch = function(search) {
        deferred = $q.defer();

        var resHandler = handle_response({
          success : function(data, msg) {
            deferred.resolve(data.items);
          },
          failed : function(data, msg) {
            deferred.resolve([]);
          }
        });
        var url = "<?php echo Routeur::getInstance()->getUrlFor("AdminAjaxSearch") ?>";
        $http.post(url, {search : search}).then(resHandler, resHandler);
 
        return deferred.promise;
      }

      $scope.load = function(item) {
        location.href = "<?php echo Routeur::getInstance()->getUrlFor("AdminEdit", array("id" => 1515)); ?>".replace("1515", item.id);;
      }
    });


</script>