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

      $scope.redirect = function(url) {
        location.href = url;
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
      console.log(date);
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
</script>