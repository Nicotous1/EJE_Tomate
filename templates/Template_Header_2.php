<?php
  use Core\PDO\Request;
  use Admin\Entity\Etude;
?>
  </head>

  <body ng-app="BlankApp" layout="column" ng-cloak style="background-color: rgb(240,240,240);">

  <div layout="row" ng-controller="MainController" style="height: 100%;">


<?php if ($firewall->isConnected()) { ?>
    <md-sidenav md-component-id="left" class="md-sidenav-left md-whiteframe-z2" md-is-locked-open="$mdMedia('min-width: 1000px')">
      <header class="nav-header md-padding" style="text-align: center">
          <a href="<?php echo $routeur->getUrlFor("EdHome") ?>"><img src="<?php echo $ressources->get_url("/images/eje.png"); ?>" alt=""></a>
      </header>
      <md-list>
      <?php if ($user->isAdmin()) { ?>
        <md-divider></md-divider>
        <md-list-item ng-click="redirect('<?php echo $routeur->getUrlFor("AdminNew") ?>')">Nouvelle étude</md-list-item>
        <md-list-item ng-click="redirect('<?php echo $routeur->getUrlFor("AdminLastEtudes") ?>')">Dernières études</md-list-item>
        <md-list-item ng-click="redirect('<?php echo $routeur->getUrlFor("AdminLastInfos") ?>')">Dernières infos</md-list-item>
<?php 
      // Beautifull no ?
      $r = new Request("SELECT e.#0.id id, e.#0.numero numero, e.#0.pseudo pseudo FROM #0.^ e JOIN #0.admins^ l ON l.#0.admins = e.id WHERE l.#0.admins> = :1^ AND #0.child IS NULL AND #0.statut < 5 ORDER BY e.#0.numero DESC LIMIT 5", array(Etude::getEntitySQL(), $user));
      $etudes_shortcut = $r->fetchAll(PDO::FETCH_ASSOC);
      if (count($etudes_shortcut) > 0) {
?>
        <md-divider></md-divider>
<?php
      }
      foreach ($etudes_shortcut as $e) {
?>
        <md-list-item ng-click="redirect('<?php echo $routeur->getUrlFor("AdminEdit", array("id" => $e["id"])); ?>')"><?php show("#" . $e["numero"] . " : " . $e["pseudo"]); ?></md-list-item>
<?php
      }
?>        
        <md-divider></md-divider>
        <md-list-item ng-click="redirect('<?php echo $routeur->getUrlFor("AdminQuali") ?>')">Pôle Qualité</md-list-item>
      <?php } ?>
        <md-divider></md-divider>

        <md-list-item ng-click="redirect('<?php echo $routeur->getUrlFor("EdEdit") ?>')">Votre profil</md-list-item>
        <md-list-item ng-click="redirect('<?php echo $routeur->getUrlFor("EdCandidater") ?>')">Postuler</md-list-item>
        <md-divider></md-divider>
        
        <md-list-item ng-click="signOut()">Se déconnecter</md-list-item>
      </md-list>    
    </md-sidenav>
<?php } ?> 

    <div layout="column" layout-fill>


      <md-toolbar class="md-whiteframe-z2" ng-controller="ToolBarController">
        <div class="md-toolbar-tools"   style="max-width: 1200px;">
<?php if ($firewall->isConnected()) { ?>
          <md-button ng-click="openLeftMenu()">
           <i class="material-icons">menu</i>
          </md-button>
<?php } ?> 
          <a href="<?php echo $routeur->getUrlFor("EdHome"); ?>">Ensae Junior Études</a>
          <span flex></span>
<?php if ($firewall->isConnected()) { ?>
          <md-menu>
           <md-button ng-click="$mdOpenMenu($event)" class="md-icon-button" aria-label="Open sample menu">
            <i class="material-icons">account_circle</i>
           </md-button>
           <md-menu-content>
             <md-menu-item ng-click="signOut()"><md-button>Se déconnecter</md-button></md-menu-item>
           </md-menu-content>
          </md-menu>
<?php } ?>          
        </div>
      </md-toolbar>
      <md-content layout-padding  style="max-width: 1200px; width: 100%; background-color: rgb(240,240,240); <?php if (!$firewall->isConnected()) { ?> margin: 0 auto; <?php } ?>" layout="column">