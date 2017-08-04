<?php
    //Exemple Utile
    // $routeur->getUrlFor('cat', array("cat"=>2));
    // $routeur->getRessource("ressources/favicon/favicon-96x96.png");

    //FUNCTION D'AFFICHAGE (SECURITY AND CLEAN)
    if (!function_exists('show')) {
        function show($str) {
            echo htmlspecialchars($str);
        }
    }
?>
<html lang="fr" >
  <head>

      <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
      <!-- Meta, title, CSS, favicons, etc. -->
      <meta charset="utf-8">

      <meta name="viewport" content="width=device-width, initial-scale=1">
      
      <link rel="apple-touch-icon" sizes="57x57" href="<?php echo $routeur->getRessource("ressources/icon/apple-icon-57x57.png"); ?>">
      <link rel="apple-touch-icon" sizes="60x60" href="<?php echo $routeur->getRessource("ressources/icon/apple-icon-60x60.png"); ?>">
      <link rel="apple-touch-icon" sizes="72x72" href="<?php echo $routeur->getRessource("ressources/icon/apple-icon-72x72.png"); ?>">
      <link rel="apple-touch-icon" sizes="76x76" href="<?php echo $routeur->getRessource("ressources/icon/apple-icon-76x76.png"); ?>">
      <link rel="apple-touch-icon" sizes="114x114" href="<?php echo $routeur->getRessource("ressources/icon/apple-icon-114x114.png"); ?>">
      <link rel="apple-touch-icon" sizes="120x120" href="<?php echo $routeur->getRessource("ressources/icon/apple-icon-120x120.png"); ?>">
      <link rel="apple-touch-icon" sizes="144x144" href="<?php echo $routeur->getRessource("ressources/icon/apple-icon-144x144.png"); ?>">
      <link rel="apple-touch-icon" sizes="152x152" href="<?php echo $routeur->getRessource("ressources/icon/apple-icon-152x152.png"); ?>">
      <link rel="apple-touch-icon" sizes="180x180" href="<?php echo $routeur->getRessource("ressources/icon/apple-icon-180x180.png"); ?>">
      <link rel="icon" type="image/png" sizes="192x192"  href="<?php echo $routeur->getRessource("ressources/icon/android-icon-192x192.png"); ?>">
      <link rel="icon" type="image/png" sizes="32x32" href="<?php echo $routeur->getRessource("ressources/icon/favicon-32x32.png"); ?>">
      <link rel="icon" type="image/png" sizes="96x96" href="<?php echo $routeur->getRessource("ressources/icon/favicon-96x96.png"); ?>">
      <link rel="icon" type="image/png" sizes="16x16" href="<?php echo $routeur->getRessource("ressources/icon/favicon-16x16.png"); ?>">
      <link rel="manifest" href="<?php echo $routeur->getRessource("ressources/icon/manifest.json"); ?>">
      <meta name="msapplication-TileColor" content="#ffffff">
      <meta name="msapplication-TileImage" content="<?php echo $routeur->getRessource("ressources/icon/ms-icon-144x144.png"); ?>">
      <meta name="theme-color" content="#ffffff">

      <!-- Angular Material style sheet -->
<?php $routeur->baliseCSS("angular-material.min")->baliseCSS("icon")->baliseCSS("lf-ng-md-file-input.min"); ?>

    <title><?php if (isset($HeaderTitre)) { echo $HeaderTitre . " | "; }?>Ensae Junior Etudes</title>

<?php
    if (!isset($sheetsCSS)) {$sheetsCSS = array();}
    foreach ($sheetsCSS as $sheet) {
        $routeur->baliseCSS($sheet);
    }
?>