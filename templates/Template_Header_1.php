<?php
    //Exemple Utile
    // $routeur->getUrlFor('cat', array("cat"=>2));

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
      
<?php $ressources->icon(); ?>

      <!-- Angular Material style sheet -->
<?php $ressources->css("/angular-material.min")->css("/icon")->css("/lf-ng-md-file-input.min"); ?>

    <title><?php if (isset($HeaderTitre)) { echo $HeaderTitre . " | "; }?>Ensae Junior Études | Lucas est un BG</title>

<?php
    if (!isset($sheetsCSS)) {$sheetsCSS = array();}
    foreach ($sheetsCSS as $sheet) {
        $ressources->css($sheet);
    }
?>