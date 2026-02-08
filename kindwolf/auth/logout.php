<?php
// auth/logout.php - Déconnexion
// ============================================

session_start();
session_destroy();

// Rediriger vers l'accueil
header('Location: /kindwolf/index.php');
exit;