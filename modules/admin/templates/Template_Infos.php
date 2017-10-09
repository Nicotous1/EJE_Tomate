<?php require("templates/Template_Header.php"); ?>

  <div ng-controller="InfosController">
      <div class="md-whiteframe-z2" style="padding: 0; margin-bottom: 20px;">
        <md-toolbar>
          <div class="md-toolbar-tools">
            <span>Dernières infos</span>
          </div>
        </md-toolbar>
        <md-content>
          <md-list>      
            <md-list-item ng-if="dynamicItems.getLength() == '0'">
              <p>On dirait qu'il ne se passe rien dans cette JE.</p>
            </md-list-item>                
            <md-virtual-repeat-container style="height: 70%;" md-auto-shrink> 
              <md-list-item class="md-3-line" md-virtual-repeat="info in dynamicItems" md-on-demand ng-click="redirect_com(info)" style="border-bottom: solid 1px rgb(220,220,220);">
                <div class="md-list-item-text" layout="column">
                  <p style="color: black;"><span style="font-weight: bold;">#{{info.etude.numero}}</span> : {{info.author.prenom}} {{info.author.nom}} {{info.type.str_action}}.</p>
                  <p style="padding-left: 15px; white-space: pre-line;" ng-if="info.details">{{info.details}}</p>
                  <p style="text-align: right;">{{info.date | date:'à HH:mm le dd/MM/yyyy'}}</p>
                  <div class="md-secondary" style="padding-left: 15px;"><md-icon>{{info.type.icon}}</md-icon></div>
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
  app.controller("InfosController", function($scope, $timeout, $http) {

    $scope.redirect_com = function(info) {
      location.href = "<?php echo $routeur->getUrlFor("AdminEdit", array("id" => 1515)); ?>".replace("1515", info.etude.id);
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
      return {etude : {numero : " Loading..."}};
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
      var url = "<?php echo $routeur->getUrlFor("AdminAjaxLastInfos") ?>";
      var c = this;
      this.full_loaded = true;
      var resHandler = handle_response({
        success : function(data, msg) {
                    c.numItems = data.n;
                    if (data.n != c.numItems) {
                      angular.element(window).triggerHandler('resize');
                      c.loadedPages.length = 0; // Empty array while keeping reference
                    }

                    c.loadedPages[pageNumber] = data.infos;
                  },
        failed : function(data, msg) {
          //c.full_loaded = true;
          console.log(msg);
        }
      });
      $http.post(url, {page : pageNumber, page_size : this.PAGE_SIZE}).then(resHandler, resHandler);
    };

    $scope.dynamicItems = new DynamicItems(); 
  });
</script>
<?php require("templates/Template_Footer_2.php"); ?>