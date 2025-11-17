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
        if (!mkdir($CLOUD_STORAGE_DIR, 0755, true)) {
            header('Location: panel.php?error=storage_creation_failed');
            exit;
        }
        // Créer le fichier .htaccess pour protéger le dossier
        file_put_contents($CLOUD_STORAGE_DIR . '.htaccess', "Deny from all\n");
    }
    
    // Construire le chemin de destination
    if (empty($currentPath)) {
        $uploadFullPath = $CLOUD_STORAGE_DIR;
    } else {
        $uploadFullPath = $CLOUD_STORAGE_DIR . $currentPath;
    }
    
    // Créer le sous-dossier s'il n'existe pas
    if (!is_dir($uploadFullPath)) {
        if (!mkdir($uploadFullPath, 0755, true)) {
            header('Location: panel.php?path=' . urlencode($currentPath) . '&error=folder_creation_failed');
            exit;
        }
    }
    
    // Vérifier que le chemin est valide (après création)
    $realUploadPath = realpath($uploadFullPath);
    if ($realUploadPath === false || strpos($realUploadPath, realpath($CLOUD_STORAGE_DIR)) !== 0) {
        header('Location: panel.php?path=' . urlencode($currentPath) . '&error=invalid_path');
        exit;
    }
    
    $originalName = $_FILES['file']['name'];
    $tmpName = $_FILES['file']['tmp_name'];
    $fileError = $_FILES['file']['error'];
    $fileSize = $_FILES['file']['size'];
    
    // Vérifier qu'il n'y a pas d'erreur
    if ($fileError !== UPLOAD_ERR_OK) {
        $errorMsg = 'upload';
        switch ($fileError) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $errorMsg = 'file_too_large';
                break;
            case UPLOAD_ERR_NO_FILE:
                $errorMsg = 'no_file';
                break;
        }
        header('Location: panel.php?path=' . urlencode($currentPath) . '&error=' . $errorMsg);
        exit;
    }
    
    // Limite de taille (100 MB par fichier)
    $maxSize = 100 * 1024 * 1024;
    if ($fileSize > $maxSize) {
        header('Location: panel.php?path=' . urlencode($currentPath) . '&error=file_too_large');
        exit;
    }
    
    // Nettoyer le nom du fichier
    $safeName = preg_replace('/[^a-zA-Z0-9._\-\s]/', '_', $originalName);
    $safeName = preg_replace('/\s+/', '_', $safeName); // Remplacer espaces multiples par _
    $targetPath = $realUploadPath . '/' . $safeName;
    
    // Vérifier si le fichier existe déjà et ajouter un suffixe si nécessaire
    if (file_exists($targetPath)) {
        $counter = 1;
        $pathInfo = pathinfo($safeName);
        $baseName = $pathInfo['filename'];
        $extension = isset($pathInfo['extension']) ? '.' . $pathInfo['extension'] : '';
        
        while (file_exists($targetPath)) {
            $safeName = $baseName . '_' . $counter . $extension;
            $targetPath = $realUploadPath . '/' . $safeName;
            $counter++;
            
            // Sécurité : éviter une boucle infinie
            if ($counter > 999) {
                header('Location: panel.php?path=' . urlencode($currentPath) . '&error=too_many_duplicates');
                exit;
            }
        }
    }
    
    // Déplacer le fichier uploadé
    if (move_uploaded_file($tmpName, $targetPath)) {
        // Définir les bonnes permissions
        chmod($targetPath, 0644);
        header('Location: panel.php?path=' . urlencode($currentPath) . '&success=upload');
        exit;
    } else {
        header('Location: panel.php?path=' . urlencode($currentPath) . '&error=move_failed');
        exit;
    }
} else {
    header('Location: panel.php?error=no_file');
    exit;
}
?>