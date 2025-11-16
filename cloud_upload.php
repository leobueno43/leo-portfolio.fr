<?php
require_once __DIR__ . '/config.php';

// Vérification de la connexion admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $CLOUD_STORAGE_DIR = __DIR__ . '/storage/';
    $currentPath = trim($_POST['current_path'] ?? '', '/');
    
    // Créer le dossier storage s'il n'existe pas
    if (!is_dir($CLOUD_STORAGE_DIR)) {
        mkdir($CLOUD_STORAGE_DIR, 0755, true);
        file_put_contents($CLOUD_STORAGE_DIR . '.htaccess', "Deny from all\n");
    }
    
    // Construire le chemin de destination
    $uploadFullPath = $CLOUD_STORAGE_DIR . $currentPath;
    
    // Vérifier que le chemin est valide
    $uploadFullPath = realpath($uploadFullPath);
    if ($uploadFullPath === false || strpos($uploadFullPath, $CLOUD_STORAGE_DIR) !== 0) {
        $uploadFullPath = $CLOUD_STORAGE_DIR;
        $currentPath = '';
    }
    
    // Créer le dossier s'il n'existe pas
    if (!is_dir($uploadFullPath)) {
        mkdir($uploadFullPath, 0755, true);
    }
    
    $originalName = $_FILES['file']['name'];
    $tmpName = $_FILES['file']['tmp_name'];
    $fileError = $_FILES['file']['error'];
    $fileSize = $_FILES['file']['size'];
    
    // Vérifier qu'il n'y a pas d'erreur
    if ($fileError !== UPLOAD_ERR_OK) {
        header('Location: panel.php?path=' . urlencode($currentPath) . '&error=upload');
        exit;
    }
    
    // Limite de taille (100 MB par fichier)
    $maxSize = 100 * 1024 * 1024;
    if ($fileSize > $maxSize) {
        header('Location: panel.php?path=' . urlencode($currentPath) . '&error=file_too_large');
        exit;
    }
    
    // Nettoyer le nom du fichier
    $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
    $targetPath = $uploadFullPath . '/' . $safeName;
    
    // Vérifier si le fichier existe déjà et ajouter un suffixe si nécessaire
    $counter = 1;
    $pathInfo = pathinfo($safeName);
    $baseName = $pathInfo['filename'];
    $extension = isset($pathInfo['extension']) ? '.' . $pathInfo['extension'] : '';
    
    while (file_exists($targetPath)) {
        $safeName = $baseName . '_' . $counter . $extension;
        $targetPath = $uploadFullPath . '/' . $safeName;
        $counter++;
    }
    
    if (move_uploaded_file($tmpName, $targetPath)) {
        header('Location: panel.php?path=' . urlencode($currentPath) . '&success=upload');
        exit;
    } else {
        header('Location: panel.php?path=' . urlencode($currentPath) . '&error=upload');
        exit;
    }
} else {
    header('Location: panel.php');
    exit;
}