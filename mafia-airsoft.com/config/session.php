<?php
// Démarrage et configuration de la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fonction pour vérifier si l'utilisateur est connecté
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Fonction pour vérifier si l'utilisateur est admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Fonction pour obtenir le chemin relatif vers la racine
function getBasePath() {
    $path = $_SERVER['PHP_SELF'];
    if (strpos($path, '/admin/') !== false || strpos($path, '/player/') !== false) {
        return '../';
    }
    return '';
}

// Fonction pour rediriger si non connecté
function requireLogin() {
    if (!isLoggedIn()) {
        $basePath = getBasePath();
        header('Location: ' . $basePath . 'login.php');
        exit;
    }
}

// Fonction pour rediriger si non admin
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        $basePath = getBasePath();
        header('Location: ' . $basePath . 'index.php');
        exit;
    }
}

// Fonction pour se déconnecter
function logout() {
    session_unset();
    session_destroy();
    $basePath = getBasePath();
    header('Location: ' . $basePath . 'index.php');
    exit;
}

// Inclure les fonctions d'icônes
require_once __DIR__ . '/../includes/icons.php';
?>
