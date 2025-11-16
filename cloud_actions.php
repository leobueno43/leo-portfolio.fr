<?php
require_once __DIR__ . '/config.php';

// Vérification de la connexion admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$CLOUD_STORAGE_DIR = __DIR__ . '/storage/';

// Créer le dossier storage s'il n'existe pas
if (!is_dir($CLOUD_STORAGE_DIR)) {
    mkdir($CLOUD_STORAGE_DIR, 0755, true);
    file_put_contents($CLOUD_STORAGE_DIR . '.htaccess', "Deny from all\n");
}

$currentPath = trim($_POST['current_path'] ?? $_GET['current_path'] ?? '', '/');
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Fonction pour sécuriser les chemins
function securePath($baseDir, $path) {
    // Si le chemin est vide, retourner le dossier de base
    if (empty($path)) {
        return $baseDir;
    }
    
    $fullPath = $baseDir . $path;
    $realPath = realpath($fullPath);
    
    // Si realpath retourne false, le fichier n'existe pas encore (création)
    if ($realPath === false) {
        // Vérifier que le chemin parent existe et est valide
        $parentPath = dirname($fullPath);
        $realParentPath = realpath($parentPath);
        
        if ($realParentPath !== false && strpos($realParentPath, $baseDir) === 0) {
            return $fullPath;
        }
        return false;
    }
    
    // Vérifier que le chemin est dans le dossier de base
    if (strpos($realPath, $baseDir) !== 0) {
        return false;
    }
    
    return $realPath;
}

// Fonction pour copier récursivement
function copyRecursive($source, $dest) {
    if (is_dir($source)) {
        if (!is_dir($dest)) {
            mkdir($dest, 0755, true);
        }
        $files = scandir($source);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..' && $file !== '.htaccess') {
                copyRecursive($source . '/' . $file, $dest . '/' . $file);
            }
        }
    } else {
        copy($source, $dest);
    }
}

// Fonction pour supprimer récursivement
function deleteRecursive($path) {
    if (!file_exists($path)) {
        return true;
    }
    
    if (is_dir($path)) {
        $files = array_diff(scandir($path), ['.', '..', '.htaccess']);
        foreach ($files as $file) {
            $filePath = $path . '/' . $file;
            deleteRecursive($filePath);
        }
        return @rmdir($path);
    } else {
        return @unlink($path);
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
            if ($sourceFullPath && file_exists($sourceFullPath)) {
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
            if ($sourceFullPath && file_exists($sourceFullPath)) {
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
        if (!$sourceFullPath || !file_exists($sourceFullPath)) {
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
        
        $deletedCount = 0;
        foreach ($files as $file) {
            $fullPath = securePath($CLOUD_STORAGE_DIR, $file);
            if ($fullPath && file_exists($fullPath)) {
                if (deleteRecursive($fullPath)) {
                    $deletedCount++;
                }
            }
        }
        
        if ($deletedCount > 0) {
            header('Location: panel.php?path=' . urlencode($currentPath) . '&success=deleted');
        } else {
            header('Location: panel.php?path=' . urlencode($currentPath) . '&error=delete_failed');
        }
        exit;
    }
}

// Redirection par défaut si aucune action reconnue
header('Location: panel.php?path=' . urlencode($currentPath));
exit;
?>