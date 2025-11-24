<?php
require_once __DIR__ . '/config.php';

// Vérification de la connexion admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $CLOUD_STORAGE_DIR = __DIR__ . '/storage/';
    
    // Créer le dossier storage s'il n'existe pas
    if (!is_dir($CLOUD_STORAGE_DIR)) {
        mkdir($CLOUD_STORAGE_DIR, 0755, true);
        file_put_contents($CLOUD_STORAGE_DIR . '.htaccess', "Deny from all\n");
    }
    
    $folderName = trim($_POST['folder_name'] ?? '');
    $currentPath = trim($_POST['current_path'] ?? '', '/');
    
    if ($folderName === '') {
        header('Location: panel.php?path=' . urlencode($currentPath) . '&error=invalid_name');
        exit;
    }
    
    // Autoriser uniquement lettres, chiffres, tirets, underscores, espaces
    $safeName = preg_replace('/[^a-zA-Z0-9_\- ]/', '_', $folderName);
    
    if ($safeName === '') {
        header('Location: panel.php?path=' . urlencode($currentPath) . '&error=invalid_name');
        exit;
    }
    
    // Construire le chemin complet en sécurité
    if ($currentPath === '') {
        $currentFullPath = $CLOUD_STORAGE_DIR;
    } else {
        $currentFullPath = $CLOUD_STORAGE_DIR . $currentPath;
    }
    
    // Vérifier que le chemin existe
    if (!is_dir($currentFullPath)) {
        mkdir($currentFullPath, 0755, true);
    }
    
    // Vérifier que le chemin est valide
    $realPath = realpath($currentFullPath);
    if ($realPath === false || strpos($realPath, $CLOUD_STORAGE_DIR) !== 0) {
        header('Location: panel.php?error=invalid_path');
        exit;
    }
    
    $targetDir = $realPath . '/' . $safeName;
    
    // Vérifier si le dossier existe déjà
    if (file_exists($targetDir)) {
        header('Location: panel.php?path=' . urlencode($currentPath) . '&error=exists');
        exit;
    }
    
    // Créer le dossier
    if (mkdir($targetDir, 0755, true)) {
        header('Location: panel.php?path=' . urlencode($currentPath) . '&success=folder_created');
    } else {
        header('Location: panel.php?path=' . urlencode($currentPath) . '&error=create_failed');
    }
    exit;
} else {
    header('Location: panel.php');
    exit;
}
?>