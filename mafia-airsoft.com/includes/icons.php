<?php
/**
 * Fonction pour afficher une icône
 * Si le fichier PNG existe dans images/icons/, l'affiche
 * Sinon, n'affiche rien (emoji retiré)
 */
function icon($name, $alt = '', $class = 'icon') {
    // Déterminer le chemin de base selon le dossier actuel
    $base_path = '';
    if (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) {
        $base_path = '../';
    } elseif (strpos($_SERVER['PHP_SELF'], '/player/') !== false) {
        $base_path = '../';
    } elseif (strpos($_SERVER['PHP_SELF'], '/qr-code/') !== false) {
        $base_path = '../';
    }
    
    $icon_path = $base_path . 'images/icons/' . $name . '.png';
    
    // Utiliser le chemin absolu du serveur pour vérifier l'existence
    $absolute_path = $_SERVER['DOCUMENT_ROOT'] . dirname($_SERVER['SCRIPT_NAME']) . '/' . $icon_path;
    
    // Vérifier si le fichier existe
    if (file_exists($absolute_path)) {
        $alt_text = $alt ?: ucfirst($name);
        return '<img src="' . $icon_path . '" alt="' . htmlspecialchars($alt_text) . '" class="' . htmlspecialchars($class) . '" width="24" height="24">';
    }
    
    // Si l'icône n'existe pas, retourner rien
    return '';
}
