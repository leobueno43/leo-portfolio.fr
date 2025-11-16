<?php
require_once __DIR__ . '/config.php';

// Vérification de la connexion admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$CLOUD_STORAGE_DIR = __DIR__ . '/storage/';
$filePath = $_GET['file'] ?? '';
$isPreview = isset($_GET['preview']);

// Sécuriser le chemin
$fullPath = realpath($CLOUD_STORAGE_DIR . $filePath);

if ($fullPath === false || strpos($fullPath, $CLOUD_STORAGE_DIR) !== 0 || !is_file($fullPath)) {
    http_response_code(404);
    die('Fichier non trouvé');
}

// Obtenir les informations du fichier
$fileName = basename($fullPath);
$fileSize = filesize($fullPath);
$mimeType = mime_content_type($fullPath);

// Si c'est un aperçu d'image, afficher directement
if ($isPreview && strpos($mimeType, 'image/') === 0) {
    header('Content-Type: ' . $mimeType);
    header('Content-Length: ' . $fileSize);
    header('Cache-Control: public, max-age=3600');
    readfile($fullPath);
    exit;
}

// Sinon, forcer le téléchargement
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Content-Length: ' . $fileSize);
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: public');

// Lecture du fichier par morceaux pour économiser la mémoire
$handle = fopen($fullPath, 'rb');
while (!feof($handle)) {
    echo fread($handle, 8192);
    flush();
}
fclose($handle);
exit;
?>