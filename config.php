<?php
// Démarrer la session sur toutes les pages qui incluent ce fichier
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclure la connexion à la base
require_once __DIR__ . '/db.php';

// Vérifie si l'utilisateur est connecté
function isLoggedIn(): bool {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

// Forcer la connexion pour accéder à une page
function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}
?>