<?php
require_once __DIR__ . '/config.php';

// Vérification de la connexion admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$CLOUD_STORAGE_DIR = __DIR__ . '/storage/';
$currentPath = trim($_POST['current_path'] ?? $_GET['current_path'] ?? '', '/');
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Fonction pour sécuriser les chemins
function securePath($baseDir, $path) {
    $fullPath = realpath($baseDir . $path);
    if ($fullPath === false || strpos($fullPath, $baseDir) !== 0) {
        return false;
    }
    return $fullPath;
}

// Fonction pour copier récursivement
function copyRecursive($source, $dest) {
    if (is_dir($source)) {
        if (!is_dir($dest)) {
            mkdir($dest, 0755, true);
        }
        $files = scandir($source);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                copyRecursive($source . '/' . $file, $dest . '/' . $file);
            }
        }
    } else {
        copy($source, $dest);
    }
}

// Fonction pour supprimer récursivement
function deleteRecursive($path) {
    if (is_dir($path)) {
        $files = array_diff(scandir($path), ['.', '..']);
        foreach ($files as $file) {
            deleteRecursive($path . '/' . $file);
        }
        return rmdir($path);
    } else {
        return unlink($path);
    }
}

// ACTIONS GET (pour les lectures)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    
    // Récupérer le contenu d'un fichier
    if ($action === 'get_content') {
        $filePath = $_GET['file'] ?? '';
        $fullPath = securePath($CLOUD_STORAGE_DIR, $filePath);
        
        if ($fullPath && is_file($fullPath)) {
            header('Content-Type: text/plain; charset=utf-8');
            echo file_get_contents($fullPath);
        } else {
            http_response_code(404);
            echo "Fichier non trouvé";
        }
        exit;
    }
    
    // Afficher un fichier
    if ($action === 'view') {
        $filePath = $_GET['file'] ?? '';
        $fullPath = securePath($CLOUD_STORAGE_DIR, $filePath);
        
        if ($fullPath && is_file($fullPath)) {
            $mimeType = mime_content_type($fullPath);
            
            header('Content-Type: application/json');
            
            if (strpos($mimeType, 'image/') === 0) {
                // Pour les images, on crée un script temporaire de lecture
                echo json_encode([
                    'type' => 'image',
                    'mime' => $mimeType,
                    'url' => 'cloud_download.php?file=' . urlencode($filePath) . '&preview=1'
                ]);
            } elseif (strpos($mimeType, 'text/') === 0 || 
                      in_array($mimeType, ['application/json', 'application/javascript'])) {
                $content = file_get_contents($fullPath);
                // Limiter à 50KB pour l'aperçu
                if (strlen($content) > 51200) {
                    $content = substr($content, 0, 51200) . "\n\n... (fichier tronqué pour l'aperçu)";
                }
                echo json_encode([
                    'type' => 'text',
                    'mime' => $mimeType,
                    'content' => $content
                ]);
            } else {
                echo json_encode([
                    'type' => 'other',
                    'mime' => $mimeType
                ]);
            }
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Fichier non trouvé']);
        }
        exit;
    }
}

// ACTIONS POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Copier des fichiers
    if ($action === 'copy') {
        $files = json_decode($_POST['files'] ?? '[]', true);
        $destination = trim($_POST['destination'] ?? '', '/');
        
        if (empty($files)) {
            header('Location: panel.php?path=' . urlencode($currentPath) . '&error=no_selection');
            exit;
        }
        
        $destFullPath = securePath($CLOUD_STORAGE_DIR, $destination);
        if (!$destFullPath) {
            header('Location: panel.php?path=' . urlencode($currentPath) . '&error=invalid_path');
            exit;
        }
        
        foreach ($files as $file) {
            $sourceFullPath = securePath($CLOUD_STORAGE_DIR, $file);
            if ($sourceFullPath) {
                $fileName = basename($sourceFullPath);
                $destFilePath = $destFullPath . '/' . $fileName;
                
                // Gérer les doublons
                $counter = 1;
                $pathInfo = pathinfo($fileName);
                $baseName = $pathInfo['filename'];
                $extension = isset($pathInfo['extension']) ? '.' . $pathInfo['extension'] : '';
                
                while (file_exists($destFilePath)) {
                    $newName = $baseName . '_copie_' . $counter . $extension;
                    $destFilePath = $destFullPath . '/' . $newName;
                    $counter++;
                }
                
                copyRecursive($sourceFullPath, $destFilePath);
            }
        }
        
        header('Location: panel.php?path=' . urlencode($destination) . '&success=copied');
        exit;
    }
    
    // Déplacer des fichiers
    if ($action === 'move') {
        $files = json_decode($_POST['files'] ?? '[]', true);
        $destination = trim($_POST['destination'] ?? '', '/');
        
        if (empty($files)) {
            header('Location: panel.php?path=' . urlencode($currentPath) . '&error=no_selection');
            exit;
        }
        
        $destFullPath = securePath($CLOUD_STORAGE_DIR, $destination);
        if (!$destFullPath) {
            header('Location: panel.php?path=' . urlencode($currentPath) . '&error=invalid_path');
            exit;
        }
        
        foreach ($files as $file) {
            $sourceFullPath = securePath($CLOUD_STORAGE_DIR, $file);
            if ($sourceFullPath) {
                $fileName = basename($sourceFullPath);
                $destFilePath = $destFullPath . '/' . $fileName;
                
                // Gérer les doublons
                $counter = 1;
                $pathInfo = pathinfo($fileName);
                $baseName = $pathInfo['filename'];
                $extension = isset($pathInfo['extension']) ? '.' . $pathInfo['extension'] : '';
                
                while (file_exists($destFilePath)) {
                    $newName = $baseName . '_' . $counter . $extension;
                    $destFilePath = $destFullPath . '/' . $newName;
                    $counter++;
                }
                
                rename($sourceFullPath, $destFilePath);
            }
        }
        
        header('Location: panel.php?path=' . urlencode($destination) . '&success=moved');
        exit;
    }
    
    // Renommer un fichier
    if ($action === 'rename') {
        $file = $_POST['file'] ?? '';
        $newName = trim($_POST['new_name'] ?? '');
        
        if (empty($file) || empty($newName)) {
            header('Location: panel.php?path=' . urlencode($currentPath) . '&error=invalid_name');
            exit;
        }
        
        $sourceFullPath = securePath($CLOUD_STORAGE_DIR, $file);
        if (!$sourceFullPath) {
            header('Location: panel.php?path=' . urlencode($currentPath) . '&error=invalid_path');
            exit;
        }
        
        // Nettoyer le nouveau nom
        $safeName = preg_replace('/[^a-zA-Z0-9._\- ]/', '_', $newName);
        $destFullPath = dirname($sourceFullPath) . '/' . $safeName;
        
        if (file_exists($destFullPath)) {
            header('Location: panel.php?path=' . urlencode($currentPath) . '&error=exists');
            exit;
        }
        
        rename($sourceFullPath, $destFullPath);
        
        header('Location: panel.php?path=' . urlencode($currentPath) . '&success=renamed');
        exit;
    }
    
    // Supprimer plusieurs fichiers
    if ($action === 'delete_multiple') {
        $files = json_decode($_POST['files'] ?? '[]', true);
        
        if (empty($files)) {
            header('Location: panel.php?path=' . urlencode($currentPath) . '&error=no_selection');
            exit;
        }
        
        foreach ($files as $file) {
            $fullPath = securePath($CLOUD_STORAGE_DIR, $file);
            if ($fullPath) {
                deleteRecursive($fullPath);
            }
        }
        
        header('Location: panel.php?path=' . urlencode($currentPath) . '&success=deleted');
        exit;
    }
}

// Redirection par défaut
header('Location: panel.php');
exit;
?>