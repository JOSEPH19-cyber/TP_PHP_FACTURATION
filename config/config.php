<?php
// Configuration de l'application

// Taux de TVA (18% comme dans le cahier des charges)
define('TVA', 0.18);

// Chemins des dossiers
define('CHEMIN_DATA', __DIR__ . '/../data/');
define('CHEMIN_MODULES', __DIR__ . '/../modules/');
define('CHEMIN_INCLUDES', __DIR__ . '/../includes/');
define('CHEMIN_ASSETS', __DIR__ . '/../assets/');
define('CHEMIN_RAPPORTS', __DIR__ . '/../rapports/');


if (session_status() === PHP_SESSION_NONE) 
{
    session_start();
}

date_default_timezone_set('Africa/Kinshasa');

?>