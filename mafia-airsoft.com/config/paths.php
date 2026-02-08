<?php
/**
 * Configuration des chemins relatifs
 * Ce fichier gère automatiquement les chemins selon le dossier actuel
 */

// Déterminer le niveau de profondeur dans l'arborescence
$scriptPath = $_SERVER['PHP_SELF'];

// Calculer le chemin de base
if (strpos($scriptPath, '/admin/') !== false) {
    define('BASE_PATH', '../');
} elseif (strpos($scriptPath, '/player/') !== false) {
    define('BASE_PATH', '../');
} else {
    define('BASE_PATH', '');
}

// Définir les chemins vers les différents dossiers
define('CSS_PATH', BASE_PATH . 'css/');
define('JS_PATH', BASE_PATH . 'js/');
define('INCLUDES_PATH', BASE_PATH . 'includes/');
define('ADMIN_PATH', BASE_PATH . 'admin/');
define('PLAYER_PATH', BASE_PATH . 'player/');

// Fonction helper pour obtenir le chemin complet d'une ressource
function asset($path) {
    return BASE_PATH . $path;
}

// Fonction helper pour obtenir l'URL d'une page
function url($page) {
    return BASE_PATH . $page;
}
?>
