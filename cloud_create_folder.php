<?php
require_once __DIR__ . '/config.php';

// Vérification de la connexion admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $CLOUD_STORAGE_DIR = __DIR__ . '/storage/';
    $folderName = trim($_POST['folder_name'] ?? '');
    $currentPath = trim($_POST['current_path'] ?? '', '/');
    
    if ($folderName === '') {
        header('Location: panel.php?path=' . urlencode($currentPath));
        exit;
    }
    
    // Autoriser uniquement lettres, chiffres, tirets, underscores, espaces
    $safeName = preg_replace('/[^a-zA-Z0-9_\- ]/', '_', $folderName);
    
    if ($safeName === '') {
        header('Location: panel.php?path=' . urlencode($currentPath));
        exit;
    }
    
    // Construire le chemin complet en sécurité
    $currentFullPath = $CLOUD_STORAGE_DIR . $currentPath;
    
    // Vérifier que le chemin est valide
    $currentFullPath = realpath($currentFullPath);
    if ($currentFullPath === false || strpos($currentFullPath, $CLOUD_STORAGE_DIR) !== 0) {
        header('Location: panel.php');
        exit;
    }
    
    $targetDir = $currentFullPath . '/' . $safeName;
    
    // Créer le dossier s'il n'existe pas
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    
    // Rediriger vers le dossier actuel
    header('Location: panel.php?path=' . urlencode($currentPath) . '&success=folder_created');
    exit;
} else {
    header('Location: panel.php');
    exit;
}
?>