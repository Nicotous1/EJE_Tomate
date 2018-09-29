<?php require("templates/Template_Header.php"); ?>

  <div ng-controller="DashboardController">
      <div class="md-whiteframe-z2" style="padding: 0; margin-bottom: 20px;">
        <md-toolbar>
          <div class="md-toolbar-tools">
            <span><?php echo $HeaderTitre; ?></span>
          </div>
        </md-toolbar>
        <md-content>
          <md-list>
              <md-list-item ng-href="<?php echo $routeur->getUrlFor("AdminNew"); ?>"><p style="text-align: center;">Ajouter une étude</p></md-list-item>
              <md-divider></md-divider>  

            <md-virtual-repeat-container style="height: 600px; max-height: 600px;" md-auto-shrink>                      
              <md-list-item class="md-2-line" md-virtual-repeat="etude in dynamicItems" md-on-demand ng-href="{{edit(etude)}}">
                <div class="md-list-item-text" layout="column">
                  <h4><span style="font-weight: bold;">#{{etude.numero}}</span> : {{etude.pseudo}} ({{etude_statuts[etude.statut].name}})</h4>
                  <p style="white-space: pre-line;">{{etude.but | cut}}</p>
                  <md-button class="md-icon-button md-secondary"><i class="material-icons">{{etude_statuts[etude.statut].icon}}</i></md-button>
                </div>
              </md-list-item>
            </md-virtual-repeat-container>  
          </md-list> 
        </md-content>
      </div>
  </div>



<?php require("templates/Template_Footer_1.php"); ?>
<?php
  use Admin\Entity\Etude;
?>

<script type="text/javascript">
  app.controller("DashboardController", function($scope, $timeout, $http) {
    $scope.etude_statuts = <?php echo json_encode(Etude::$statutArray); ?>;

    $scope.new = function() {
      location.href = "<?php echo $routeur->getUrlFor("AdminNew"); ?>";
    };

    $scope.edit = function(etude) {
      var url = "<?php echo $routeur->getUrlFor("AdminEdit", array("id" => 1515)); ?>".replace("1515", etude.id);
      return url;
    }


    // In this example, we set up our model using a class.
    // Using a plain object works too. All that matters
    // is that we implement getItemAtIndex and getLength.
    var DynamicItems = function() {
      this.loadedPages = {};

      this.numItems = 1;

      this.PAGE_SIZE = 20;

      this.fetchPage_(0);
    };

    // Required.
    DynamicItems.prototype.getItemAtIndex = function(index) {
      var pageNumber = Math.floor(index / this.PAGE_SIZE);
      var page = this.loadedPages[pageNumber];

      if (page) {
        return page[index % this.PAGE_SIZE];
      } else if (page !== null) {
        this.fetchPage_(pageNumber);
      }
      return {numero : "Loading..."};
    };

    // Required.
    DynamicItems.prototype.getLength = function() {
      return this.numItems;
    };

    DynamicItems.prototype.fetchPage_ = function(pageNumber) {
      console.log("fetching page "+pageNumber);
      // Set the page to null so we know it is already being fetched.
      this.loadedPages[pageNumber] = null;

      // For demo purposes, we simulate loading more items with a timed
      // promise. In real code, this function would likely contain an
      // $http request.
      console.log("fetchMoreItems_...");
      var url = "<?php echo $routeur->getUrlFor("AdminAjaxLastEtudes") ?>";
      var c = this;
      this.full_loaded = true;
      var resHandler = handle_response({
        success : function(data, msg) {
                    c.numItems = data.n;
                    if (data.n != c.numItems) {
                      angular.element(window).triggerHandler('resize');  
                      c.loadedPages.length = 0; // Empty array while keeping reference
                    }

                    c.loadedPages[pageNumber] = data.etudes;
                  },
        failed : function(data, msg) {
          //c.full_loaded = true;
          console.log(msg);
        }
      });
      $http.post(url, {page : pageNumber, page_size : this.PAGE_SIZE, archived : <?php echo ($archived) ? 'true' : 'false' ?> }).then(resHandler, resHandler);
    };

    $scope.dynamicItems = new DynamicItems(); 
  });
</script>
<?php require("templates/Template_Footer_2.php"); ?>