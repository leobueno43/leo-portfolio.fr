<?php
session_start();

// Vérification de la connexion admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$adminUsername = isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : 'admin';

// IMPORTANT : Dossier de stockage cloud (indépendant du site)
$CLOUD_STORAGE_DIR = __DIR__ . '/storage/';

// Créer le dossier storage s'il n'existe pas
if (!is_dir($CLOUD_STORAGE_DIR)) {
    mkdir($CLOUD_STORAGE_DIR, 0755, true);
    // Créer un fichier .htaccess pour protéger le dossier
    file_put_contents($CLOUD_STORAGE_DIR . '.htaccess', "Deny from all\n");
}

// Calcul de l'espace disque du dossier storage
function getDirSize($dir) {
    $size = 0;
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)) as $file) {
        $size += $file->getSize();
    }
    return $size;
}

$usedSpace = getDirSize($CLOUD_STORAGE_DIR);
$totalSpace = disk_total_space(__DIR__);
$freeSpace = disk_free_space(__DIR__);
$usedSpacePercent = $totalSpace > 0 ? ($usedSpace / $totalSpace) * 100 : 0;

// Gestion de la navigation dans les dossiers
$currentPath = isset($_GET['path']) ? $_GET['path'] : '';
$currentPath = trim($currentPath, '/');

// Sécurité : empêcher de sortir du dossier storage
$fullPath = realpath($CLOUD_STORAGE_DIR . $currentPath);
if ($fullPath === false || strpos($fullPath, $CLOUD_STORAGE_DIR) !== 0) {
    $currentPath = '';
    $fullPath = $CLOUD_STORAGE_DIR;
}

// Messages de succès/erreur
$message = '';
$messageType = '';
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'upload':
            $message = 'Fichier uploadé avec succès !';
            $messageType = 'success';
            break;
        case 'deleted':
            $message = 'Élément supprimé avec succès !';
            $messageType = 'success';
            break;
        case 'renamed':
            $message = 'Fichier renommé avec succès !';
            $messageType = 'success';
            break;
        case 'copied':
            $message = 'Fichier(s) copié(s) avec succès !';
            $messageType = 'success';
            break;
        case 'moved':
            $message = 'Fichier(s) déplacé(s) avec succès !';
            $messageType = 'success';
            break;
        case 'folder_created':
            $message = 'Dossier créé avec succès !';
            $messageType = 'success';
            break;
        case 'compressed':
            $message = 'Archive ZIP créée avec succès !';
            $messageType = 'success';
            break;
        case 'extracted':
            $message = 'Archive extraite avec succès !';
            $messageType = 'success';
            break;
    }
}
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'upload':
            $message = 'Erreur lors de l\'upload du fichier.';
            $messageType = 'error';
            break;
        case 'invalid_path':
            $message = 'Chemin invalide.';
            $messageType = 'error';
            break;
        case 'no_selection':
            $message = 'Aucun fichier sélectionné.';
            $messageType = 'error';
            break;
        case 'file_too_large':
            $message = 'Fichier trop volumineux. Limite : 100 MB par fichier.';
            $messageType = 'error';
            break;
        case 'delete_failed':
            $message = 'Impossible de supprimer certains fichiers.';
            $messageType = 'error';
            break;
        case 'exists':
            $message = 'Un fichier ou dossier avec ce nom existe déjà.';
            $messageType = 'error';
            break;
    }
}
if (isset($_GET['warning'])) {
    switch ($_GET['warning']) {
        case 'space_low':
            $message = 'Attention : Espace disque faible (moins de 10% disponible).';
            $messageType = 'warning';
            break;
        case 'large_file':
            $message = 'Fichier volumineux détecté. L\'upload peut prendre du temps.';
            $messageType = 'warning';
            break;
    }
}
if (isset($_GET['info'])) {
    switch ($_GET['info']) {
        case 'first_visit':
            $message = 'Bienvenue sur votre cloud personnel ! Commencez par uploader vos fichiers.';
            $messageType = 'info';
            break;
        case 'backup_reminder':
            $message = 'N\'oubliez pas de sauvegarder régulièrement vos fichiers importants.';
            $messageType = 'info';
            break;
    }
}

// Vérification espace disque faible (avertissement automatique)
if ($freeSpace < ($totalSpace * 0.1) && empty($message)) {
    $message = 'Attention : Espace disque faible (' . formatSize($freeSpace) . ' restants).';
    $messageType = 'warning';
}

// Récupération des fichiers et dossiers
$items = [];
if (is_dir($fullPath)) {
    $scanResult = array_diff(scandir($fullPath), ['.', '..', '.htaccess']);
    foreach ($scanResult as $item) {
        $itemPath = $fullPath . '/' . $item;
        $relativePath = $currentPath ? $currentPath . '/' . $item : $item;
        
        $items[] = [
            'name' => $item,
            'path' => $relativePath,
            'is_dir' => is_dir($itemPath),
            'size' => is_file($itemPath) ? filesize($itemPath) : 0,
            'modified' => filemtime($itemPath),
            'type' => is_dir($itemPath) ? 'httpd/unix-directory' : mime_content_type($itemPath),
            'permissions' => substr(sprintf('%o', fileperms($itemPath)), -4)
        ];
    }
    
    // Trier : dossiers d'abord, puis fichiers
    usort($items, function($a, $b) {
        if ($a['is_dir'] && !$b['is_dir']) return -1;
        if (!$a['is_dir'] && $b['is_dir']) return 1;
        return strcasecmp($a['name'], $b['name']);
    });
}

// Fonction pour formater la taille
function formatSize($bytes) {
    if ($bytes < 1024) return $bytes . ' octets';
    if ($bytes < 1048576) return round($bytes / 1024, 2) . ' KB';
    if ($bytes < 1073741824) return round($bytes / 1048576, 2) . ' MB';
    return round($bytes / 1073741824, 2) . ' GB';
}

// Fonction pour formater la date
function formatDate($timestamp) {
    $today = date('Y-m-d');
    $fileDate = date('Y-m-d', $timestamp);
    
    if ($today === $fileDate) {
        return "Aujourd'hui, " . date('H:i', $timestamp);
    }
    
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    if ($yesterday === $fileDate) {
        return "Hier, " . date('H:i', $timestamp);
    }
    
    $months = ['janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 
               'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'];
    $month = $months[date('n', $timestamp) - 1];
    
    return date('j', $timestamp) . ' ' . $month . ' ' . date('Y', $timestamp) . ', ' . date('H:i', $timestamp);
}

// Construire le fil d'ariane
$breadcrumbs = [];
if ($currentPath) {
    $parts = explode('/', $currentPath);
    $accPath = '';
    foreach ($parts as $part) {
        $accPath .= ($accPath ? '/' : '') . $part;
        $breadcrumbs[] = ['name' => $part, 'path' => $accPath];
    }
}

// Liste des dossiers pour copier/déplacer
function getAllFolders($baseDir) {
    $folders = [['path' => '', 'display' => '📁 Mon Cloud (racine)']];
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($baseDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $item) {
        if ($item->isDir() && $item->getFilename() !== '.htaccess') {
            $relativePath = str_replace($baseDir, '', $item->getPathname());
            $relativePath = trim($relativePath, '/');
            $depth = substr_count($relativePath, '/');
            $indent = str_repeat('  ', $depth);
            $folders[] = [
                'path' => $relativePath,
                'display' => $indent . '📁 ' . basename($relativePath)
            ];
        }
    }
    
    return $folders;
}

$allFolders = getAllFolders($CLOUD_STORAGE_DIR);

// Statistiques
$totalFiles = 0;
$totalFolders = 0;
foreach ($items as $item) {
    if ($item['is_dir']) {
        $totalFolders++;
    } else {
        $totalFiles++;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>☁️ Mon Cloud - Stockage Personnel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="admin.css">
    <style>
        /* Styles du cloud */
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .cloud-header {
            background: white;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .cloud-title {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 28px;
            font-weight: 700;
            color: #2c3e50;
            margin: 0 0 8px 0;
        }
        
        .cloud-subtitle {
            color: #7f8c8d;
            font-size: 14px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        
        .stat-icon.blue { background: #e3f2fd; }
        .stat-icon.green { background: #e8f5e9; }
        .stat-icon.purple { background: #f3e5f5; }
        .stat-icon.orange { background: #fff3e0; }
        
        .stat-info h3 {
            margin: 0 0 4px 0;
            font-size: 24px;
            font-weight: 700;
            color: #2c3e50;
        }
        
        .stat-info p {
            margin: 0;
            font-size: 12px;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .file-manager-wrapper {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        
        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 16px;
            opacity: 0.5;
        }
        
        .empty-state h3 {
            font-size: 20px;
            color: #2c3e50;
            margin: 0 0 8px 0;
        }
        
        .empty-state p {
            color: #7f8c8d;
            margin: 0 0 24px 0;
        }
        
        .quick-upload {
            display: inline-block;
            padding: 12px 24px;
            background: #3498db;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .quick-upload:hover {
            background: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
        }
        
        /* Reprise des styles précédents nécessaires */
        .fm-toolbar {
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            padding: 12px 20px;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .fm-btn {
            padding: 6px 14px;
            border: 1px solid #ced4da;
            background: white;
            border-radius: 4px;
            font-size: 13px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }
        
        .fm-btn:hover:not(:disabled) {
            background: #e9ecef;
            border-color: #adb5bd;
        }
        
        .fm-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }
        
        .fm-btn-primary {
            background: #3498db;
            color: white;
            border-color: #2980b9;
        }
        
        .fm-btn-primary:hover:not(:disabled) {
            background: #2980b9;
        }
        
        .fm-btn-danger {
            background: #e74c3c;
            color: white;
            border-color: #c0392b;
        }
        
        .fm-btn-danger:hover:not(:disabled) {
            background: #c0392b;
        }
        
        .fm-breadcrumb {
            background: white;
            padding: 10px 20px;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
        }
        
        .fm-breadcrumb a {
            color: #3498db;
            text-decoration: none;
        }
        
        .fm-breadcrumb a:hover {
            text-decoration: underline;
        }
        
        .fm-breadcrumb-separator {
            color: #adb5bd;
        }
        
        .fm-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .fm-table thead {
            background: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }
        
        .fm-table th {
            padding: 10px 20px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            color: #495057;
        }
        
        .fm-table tbody tr {
            border-bottom: 1px solid #f1f3f5;
            transition: background 0.15s;
        }
        
        .fm-table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .fm-table tbody tr.selected {
            background: #e3f2fd;
        }
        
        .fm-table td {
            padding: 10px 20px;
            font-size: 13px;
        }
        
        .fm-checkbox {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        
        .fm-icon {
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .fm-icon::before {
            content: '';
            width: 20px;
            height: 20px;
            display: inline-block;
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }
        
        .fm-icon.folder::before {
            background-image: url('/images/icon/folder.png');
        }
        
        .fm-icon.file::before {
            background-image: url('/images/icon/file.png');
        }
        
        .fm-icon.css::before {
            background-image: url('/images/icon/css.png');
        }
        
        .fm-icon.html::before {
            background-image: url('/images/icon/html.png');
        }
        
        .fm-icon.php::before {
            background-image: url('/images/icon/php.png');
        }
        
        .fm-icon.js::before {
            background-image: url('/images/icon/js.png');
        }
        
        .fm-icon.image::before {
            background-image: url('/images/icon/image.png');
        }
        
        .fm-icon.pdf::before {
            background-image: url('/images/icon/pdf.png');
        }
        
        .fm-icon.zip::before {
            background-image: url('/images/icon/zip.png');
        }
        
        .fm-icon.doc::before {
            background-image: url('/images/icon/doc.png');
        }
        
        .fm-icon.video::before {
            background-image: url('/images/icon/video.png');
        }
        
        .fm-icon.audio::before {
            background-image: url('/images/icon/audio.png');
        }
        
        .fm-name-link {
            color: #212529;
            text-decoration: none;
        }
        
        .fm-name-link:hover {
            color: #3498db;
            text-decoration: underline;
        }
        
        .message-banner {
            padding: 12px 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .message-banner.success {
            border-left: 4px solid #27ae60;
            background: #d4edda;
        }
        
        .message-banner.error {
            border-left: 4px solid #e74c3c;
            background: #f8d7da;
        }
        
        .message-banner.warning {
            border-left: 4px solid #f39c12;
            background: #fff3cd;
        }
        
        .message-banner.info {
            border-left: 4px solid #3498db;
            background: #d1ecf1;
        }
        
        .selection-info {
            padding: 12px 20px;
            background: white;
            border-radius: 8px;
            font-size: 13px;
            color: #1976d2;
            display: none;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        
        .selection-info.active {
            display: block;
        }
        
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            align-items: center;
            justify-content: center;
        }
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background: white;
            padding: 24px;
            border-radius: 12px;
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }
        
        .modal-header {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .modal-footer {
            margin-top: 20px;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        
        textarea.code-editor {
            font-family: 'Courier New', monospace;
            font-size: 13px;
            width: 100%;
            min-height: 300px;
            padding: 12px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            resize: vertical;
            box-sizing: border-box;
        }
        
        .folder-tree {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 8px;
        }
        
        .folder-tree label {
            display: block;
            padding: 6px 8px;
            cursor: pointer;
            border-radius: 4px;
        }
        
        .folder-tree label:hover {
            background: #f8f9fa;
        }
        
        .folder-tree input[type="radio"] {
            margin-right: 8px;
        }
        
        .permissions-badge {
            font-family: monospace;
            font-size: 11px;
            color: #6c757d;
            background: #f8f9fa;
            padding: 2px 6px;
            border-radius: 3px;
        }
    </style>
</head>
<body class="admin-body">

<div class="admin-container">
    
    <!-- Header Cloud -->
    <div class="cloud-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 class="cloud-title">
                    <img src="/images/icon/cloud.png" alt="" style="width: 36px; height: 36px;">
                    Mon Cloud
                </h1>
                <p class="cloud-subtitle">
                    Bienvenue <?= htmlspecialchars($adminUsername) ?> • Stockage personnel sécurisé
                </p>
            </div>
            <div style="text-align:right;">
                <a href="index.html" class="link-muted" style="display:block; margin-bottom:8px;">
                    <img src="/images/icon/home.png" alt="" style="width: 14px; height: 14px; vertical-align: middle; margin-right: 4px;">
                    Retour au site
                </a>
                <a href="logout.php" class="link-muted">
                    <img src="/images/icon/logout.png" alt="" style="width: 14px; height: 14px; vertical-align: middle; margin-right: 4px;">
                    Se déconnecter
                </a>
            </div>
        </div>
    </div>

    <!-- Message de succès/erreur/warning/info -->
    <?php if ($message): ?>
    <div class="message-banner <?= $messageType ?>">
        <?php if ($messageType === 'success'): ?>
            <img src="/images/icon/success.png" alt="" style="width: 20px; height: 20px;">
        <?php elseif ($messageType === 'error'): ?>
            <img src="/images/icon/error.png" alt="" style="width: 20px; height: 20px;">
        <?php elseif ($messageType === 'warning'): ?>
            <img src="/images/icon/warning.png" alt="" style="width: 20px; height: 20px;">
        <?php else: ?>
            <img src="/images/icon/info.png" alt="" style="width: 20px; height: 20px;">
        <?php endif; ?>
        <?= $message ?>
    </div>
    <?php endif; ?>

    <!-- Statistiques -->
    <div class="stats-grid">
        <!-- Graphique circulaire espace disque -->
        <div class="stat-card" style="grid-column: span 2;">
            <div style="display: flex; align-items: center; gap: 24px; width: 100%;">
                <div class="storage-chart-container" style="flex-shrink: 0;">
                    <svg class="storage-chart" viewBox="0 0 200 200" style="width: 120px; height: 120px;">
                        <!-- Cercle de fond -->
                        <circle cx="100" cy="100" r="80" fill="none" stroke="#e5e7eb" stroke-width="20"/>
                        <!-- Cercle de progression -->
                        <circle cx="100" cy="100" r="80" fill="none" 
                                stroke="url(#gradient)" stroke-width="20"
                                stroke-dasharray="502.65" 
                                stroke-dashoffset="<?= 502.65 - (502.65 * ($usedSpacePercent / 100)) ?>"
                                transform="rotate(-90 100 100)"
                                stroke-linecap="round"/>
                        
                        <!-- Gradient -->
                        <defs>
                            <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" style="stop-color:#3498db;stop-opacity:1" />
                                <stop offset="100%" style="stop-color:#9b59b6;stop-opacity:1" />
                            </linearGradient>
                        </defs>
                        
                        <!-- Texte au centre -->
                        <text x="100" y="95" text-anchor="middle" font-size="24" font-weight="700" fill="#2c3e50">
                            <?= round($usedSpacePercent, 1) ?>%
                        </text>
                        <text x="100" y="115" text-anchor="middle" font-size="12" fill="#7f8c8d">
                            utilisé
                        </text>
                    </svg>
                </div>
                
                <div style="flex: 1;">
                    <h3 style="margin: 0 0 12px 0; font-size: 18px; color: #2c3e50;">
                        <img src="/images/icon/storage.png" alt="" style="width: 20px; height: 20px; vertical-align: middle; margin-right: 6px;">
                        Espace de stockage
                    </h3>
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px;">
                        <div>
                            <p style="margin: 0; font-size: 12px; color: #7f8c8d;">Utilisé</p>
                            <p style="margin: 4px 0 0 0; font-size: 16px; font-weight: 700; color: #3498db;">
                                <?= formatSize($usedSpace) ?>
                            </p>
                        </div>
                        <div>
                            <p style="margin: 0; font-size: 12px; color: #7f8c8d;">Disponible</p>
                            <p style="margin: 4px 0 0 0; font-size: 16px; font-weight: 700; color: #27ae60;">
                                <?= formatSize($freeSpace) ?>
                            </p>
                        </div>
                        <div>
                            <p style="margin: 0; font-size: 12px; color: #7f8c8d;">Total</p>
                            <p style="margin: 4px 0 0 0; font-size: 16px; font-weight: 700; color: #2c3e50;">
                                <?= formatSize($totalSpace) ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon green">
                <img src="/images/icon/folder.png" alt="" style="width: 32px; height: 32px;">
            </div>
            <div class="stat-info">
                <h3><?= $totalFolders ?></h3>
                <p>Dossiers</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon purple">
                <img src="/images/icon/file.png" alt="" style="width: 32px; height: 32px;">
            </div>
            <div class="stat-info">
                <h3><?= $totalFiles ?></h3>
                <p>Fichiers</p>
            </div>
        </div>
    </div>

    <!-- Info de sélection -->
    <div id="selectionInfo" class="selection-info">
        <img src="/images/icon/check-all.png" alt="" style="width: 16px; height: 16px; vertical-align: middle; margin-right: 4px;">
        <span id="selectionCount">0</span> élément(s) sélectionné(s)
    </div>

    <!-- Gestionnaire de fichiers -->
    <div class="file-manager-wrapper">
        
        <!-- Toolbar -->
        <div class="fm-toolbar">
            <button class="fm-btn fm-btn-primary" onclick="showUploadModal()">
                <img src="/images/icon/upload.png" alt="" style="width: 14px; height: 14px;">
                Upload
            </button>
            <button class="fm-btn" onclick="showCreateFolder()">
                <img src="/images/icon/plus.png" alt="" style="width: 14px; height: 14px;">
                Nouveau dossier
            </button>
            <button class="fm-btn" id="btnSearch" onclick="showSearchModal()">
                <img src="/images/icon/search.png" alt="" style="width: 14px; height: 14px;">
                Rechercher
            </button>
            <button class="fm-btn" id="btnCopy" onclick="showCopyModal()" disabled>
                <img src="/images/icon/copy.png" alt="" style="width: 14px; height: 14px;">
                Copier
            </button>
            <button class="fm-btn" id="btnMove" onclick="showMoveModal()" disabled>
                <img src="/images/icon/move.png" alt="" style="width: 14px; height: 14px;">
                Déplacer
            </button>
            <button class="fm-btn" id="btnDownload" onclick="downloadSelected()" disabled>
                <img src="/images/icon/download.png" alt="" style="width: 14px; height: 14px;">
                Télécharger
            </button>
            <button class="fm-btn" id="btnCompress" onclick="showCompressModal()" disabled>
                <img src="/images/icon/compress.png" alt="" style="width: 14px; height: 14px;">
                Compresser
            </button>
            <button class="fm-btn" id="btnExtract" onclick="showExtractModal()" disabled>
                <img src="/images/icon/extract.png" alt="" style="width: 14px; height: 14px;">
                Extraire
            </button>
            <button class="fm-btn" id="btnRename" onclick="showRenameModal()" disabled>
                <img src="/images/icon/rename.png" alt="" style="width: 14px; height: 14px;">
                Renommer
            </button>
            <button class="fm-btn" id="btnFavorite" onclick="toggleFavorite()" disabled>
                <img src="/images/icon/favorite.png" alt="" style="width: 14px; height: 14px;">
                Favoris
            </button>
            <button class="fm-btn" id="btnShare" onclick="showShareModal()" disabled>
                <img src="/images/icon/share.png" alt="" style="width: 14px; height: 14px;">
                Partager
            </button>
            <button class="fm-btn" id="btnView" onclick="viewFile()" disabled>
                <img src="/images/icon/view.png" alt="" style="width: 14px; height: 14px;">
                Aperçu
            </button>
            <button class="fm-btn fm-btn-danger" id="btnDelete" onclick="deleteSelected()" disabled>
                <img src="/images/icon/delete.png" alt="" style="width: 14px; height: 14px;">
                Supprimer
            </button>
        </div>

        <!-- Fil d'ariane -->
        <div class="fm-breadcrumb">
            <a href="panel.php">
                <img src="/images/icon/cloud.png" alt="" style="width: 14px; height: 14px; vertical-align: middle; margin-right: 4px;">
                Mon Cloud
            </a>
            <?php foreach ($breadcrumbs as $crumb): ?>
                <span class="fm-breadcrumb-separator">›</span>
                <a href="panel.php?path=<?= urlencode($crumb['path']) ?>">
                    <?= htmlspecialchars($crumb['name']) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Contenu -->
        <?php if (empty($items)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <img src="/images/icon/cloud.png" alt="" style="width: 64px; height: 64px; opacity: 0.5;">
                </div>
                <h3>Votre cloud est vide</h3>
                <p>Commencez par uploader vos premiers fichiers</p>
                <a href="#" class="quick-upload" onclick="showUploadModal(); return false;">
                    <img src="/images/icon/upload.png" alt="" style="width: 16px; height: 16px; vertical-align: middle; margin-right: 6px;">
                    Upload un fichier
                </a>
            </div>
        <?php else: ?>
        <table class="fm-table">
            <thead>
                <tr>
                    <th style="width: 40px;">
                        <input type="checkbox" id="selectAllCheckbox" class="fm-checkbox" onchange="toggleSelectAll(this)">
                    </th>
                    <th>Nom</th>
                    <th>Taille</th>
                    <th>Modifié</th>
                    <th>Type</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr class="file-row" data-path="<?= htmlspecialchars($item['path']) ?>" 
                    data-name="<?= htmlspecialchars($item['name']) ?>"
                    data-is-dir="<?= $item['is_dir'] ? '1' : '0' ?>">
                    <td>
                        <input type="checkbox" class="fm-checkbox file-checkbox" 
                               value="<?= htmlspecialchars($item['path']) ?>"
                               onchange="updateSelection()">
                    </td>
                    <td>
                        <?php if ($item['is_dir']): ?>
                            <a href="panel.php?path=<?= urlencode($item['path']) ?>" class="fm-icon folder fm-name-link">
                                <?= htmlspecialchars($item['name']) ?>
                            </a>
                        <?php else: ?>
                            <?php 
                            $ext = strtolower(pathinfo($item['name'], PATHINFO_EXTENSION));
                            $iconClass = 'file';
                            if (in_array($ext, ['css'])) $iconClass = 'css';
                            elseif (in_array($ext, ['html', 'htm'])) $iconClass = 'html';
                            elseif (in_array($ext, ['php'])) $iconClass = 'php';
                            elseif (in_array($ext, ['js', 'json'])) $iconClass = 'js';
                            elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'bmp', 'ico'])) $iconClass = 'image';
                            elseif (in_array($ext, ['pdf'])) $iconClass = 'pdf';
                            elseif (in_array($ext, ['zip', 'rar', '7z', 'tar', 'gz'])) $iconClass = 'zip';
                            elseif (in_array($ext, ['doc', 'docx', 'odt', 'txt', 'rtf'])) $iconClass = 'doc';
                            elseif (in_array($ext, ['mp4', 'avi', 'mkv', 'mov', 'wmv', 'flv', 'webm'])) $iconClass = 'video';
                            elseif (in_array($ext, ['mp3', 'wav', 'ogg', 'flac', 'aac', 'm4a'])) $iconClass = 'audio';
                            ?>
                            <span class="fm-icon <?= $iconClass ?>">
                                <?= htmlspecialchars($item['name']) ?>
                            </span>
                        <?php endif; ?>
                    </td>
                    <td><?= $item['is_dir'] ? '<img src="/images/icon/folder.png" style="width: 14px; height: 14px; opacity: 0.5;">' : formatSize($item['size']) ?></td>
                    <td><?= formatDate($item['modified']) ?></td>
                    <td style="color: #6c757d; font-size: 12px;">
                        <?= $item['is_dir'] ? 'Dossier' : htmlspecialchars($item['type']) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
        
    </div>

</div>

<!-- Modals -->

<!-- Modal Upload -->
<div id="uploadModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <img src="/images/icon/upload.png" alt="" style="width: 20px; height: 20px;">
            Upload de fichier
        </div>
        <form method="post" action="cloud_upload.php" enctype="multipart/form-data">
            <input type="hidden" name="current_path" value="<?= htmlspecialchars($currentPath) ?>">
            <div class="form-group">
                <label for="file">Sélectionner un fichier</label>
                <input type="file" name="file" id="file" class="form-control" required multiple>
                <p class="helper-text">
                    Les fichiers seront uploadés dans : <strong><?= $currentPath ?: 'Mon Cloud' ?></strong>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="fm-btn" onclick="closeModal('uploadModal')">Annuler</button>
                <button type="submit" class="fm-btn fm-btn-primary">Envoyer</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Copier -->
<div id="copyModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <img src="/images/icon/copy.png" alt="" style="width: 20px; height: 20px;">
            Copier les fichiers
        </div>
        <form method="post" action="cloud_actions.php">
            <input type="hidden" name="action" value="copy">
            <input type="hidden" name="current_path" value="<?= htmlspecialchars($currentPath) ?>">
            <input type="hidden" name="files" id="copyFiles">
            
            <div class="form-group">
                <label>Destination</label>
                <div class="folder-tree">
                    <?php foreach ($allFolders as $folder): ?>
                        <label>
                            <input type="radio" name="destination" value="<?= htmlspecialchars($folder['path']) ?>" 
                                   <?= $folder['path'] === $currentPath ? 'checked' : '' ?>>
                            <?= htmlspecialchars($folder['display']) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="fm-btn" onclick="closeModal('copyModal')">Annuler</button>
                <button type="submit" class="fm-btn fm-btn-primary">Copier</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Déplacer -->
<div id="moveModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <img src="/images/icon/move.png" alt="" style="width: 20px; height: 20px;">
            Déplacer les fichiers
        </div>
        <form method="post" action="cloud_actions.php">
            <input type="hidden" name="action" value="move">
            <input type="hidden" name="current_path" value="<?= htmlspecialchars($currentPath) ?>">
            <input type="hidden" name="files" id="moveFiles">
            
            <div class="form-group">
                <label>Destination</label>
                <div class="folder-tree">
                    <?php foreach ($allFolders as $folder): ?>
                        <label>
                            <input type="radio" name="destination" value="<?= htmlspecialchars($folder['path']) ?>"
                                   <?= $folder['path'] === $currentPath ? 'checked' : '' ?>>
                            <?= htmlspecialchars($folder['display']) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="fm-btn" onclick="closeModal('moveModal')">Annuler</button>
                <button type="submit" class="fm-btn fm-btn-primary">Déplacer</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Renommer -->
<div id="renameModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <img src="/images/icon/rename.png" alt="" style="width: 20px; height: 20px;">
            Renommer
        </div>
        <form method="post" action="cloud_actions.php">
            <input type="hidden" name="action" value="rename">
            <input type="hidden" name="current_path" value="<?= htmlspecialchars($currentPath) ?>">
            <input type="hidden" name="file" id="renameFile">
            
            <div class="form-group">
                <label for="new_name">Nouveau nom</label>
                <input type="text" name="new_name" id="renameNewName" class="form-control" required>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="fm-btn" onclick="closeModal('renameModal')">Annuler</button>
                <button type="submit" class="fm-btn fm-btn-primary">Renommer</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Aperçu -->
<div id="viewModal" class="modal">
    <div class="modal-content" style="max-width: 800px;">
        <div class="modal-header">
            <img src="/images/icon/view.png" alt="" style="width: 20px; height: 20px;">
            Aperçu du fichier
        </div>
        <div id="viewContent" style="padding: 12px; background: #f8f9fa; border-radius: 8px; max-height: 500px; overflow: auto;">
            <!-- Contenu chargé dynamiquement -->
        </div>
        <div class="modal-footer">
            <button type="button" class="fm-btn fm-btn-primary" onclick="closeModal('viewModal')">Fermer</button>
        </div>
    </div>
</div>

<!-- Modal Rechercher -->
<div id="searchModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <img src="/images/icon/search.png" alt="" style="width: 20px; height: 20px;">
            Rechercher des fichiers
        </div>
        <div class="form-group">
            <label for="searchInput">Recherche</label>
            <input type="text" id="searchInput" class="form-control" placeholder="Nom du fichier..." onkeyup="performSearch()">
            <p class="helper-text">Recherche dans tous vos fichiers et dossiers</p>
        </div>
        <div id="searchResults" style="max-height: 300px; overflow-y: auto; margin-top: 16px;">
            <p style="text-align: center; color: #7f8c8d;">Saisissez un nom pour rechercher...</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="fm-btn" onclick="closeModal('searchModal')">Fermer</button>
        </div>
    </div>
</div>

<!-- Modal Compresser -->
<div id="compressModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <img src="/images/icon/compress.png" alt="" style="width: 20px; height: 20px;">
            Créer une archive ZIP
        </div>
        <form method="post" action="cloud_actions.php">
            <input type="hidden" name="action" value="compress">
            <input type="hidden" name="current_path" value="<?= htmlspecialchars($currentPath) ?>">
            <input type="hidden" name="files" id="compressFiles">
            
            <div class="form-group">
                <label for="archive_name">Nom de l'archive</label>
                <input type="text" name="archive_name" id="archiveName" class="form-control" 
                       placeholder="mon-archive.zip" required>
                <p class="helper-text">
                    L'archive sera créée dans le dossier actuel
                </p>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="fm-btn" onclick="closeModal('compressModal')">Annuler</button>
                <button type="submit" class="fm-btn fm-btn-primary">Créer l'archive</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Extraire -->
<div id="extractModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <img src="/images/icon/extract.png" alt="" style="width: 20px; height: 20px;">
            Extraire l'archive
        </div>
        <form method="post" action="cloud_actions.php">
            <input type="hidden" name="action" value="extract">
            <input type="hidden" name="current_path" value="<?= htmlspecialchars($currentPath) ?>">
            <input type="hidden" name="file" id="extractFile">
            
            <div class="form-group">
                <label>Options d'extraction</label>
                <p class="helper-text" id="extractInfo">
                    L'archive sera extraite dans un nouveau dossier
                </p>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="fm-btn" onclick="closeModal('extractModal')">Annuler</button>
                <button type="submit" class="fm-btn fm-btn-primary">Extraire</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Partager -->
<div id="shareModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <img src="/images/icon/share.png" alt="" style="width: 20px; height: 20px;">
            Partager le fichier
        </div>
        <div class="form-group">
            <label>Lien de partage</label>
            <div style="display: flex; gap: 8px;">
                <input type="text" id="shareLink" class="form-control" readonly>
                <button type="button" class="fm-btn fm-btn-primary" onclick="copyShareLink()">
                    <img src="/images/icon/copy.png" alt="" style="width: 14px; height: 14px;">
                    Copier
                </button>
            </div>
            <p class="helper-text">
                Ce lien permet de télécharger le fichier (valide 24h)
            </p>
        </div>
        <div class="modal-footer">
            <button type="button" class="fm-btn" onclick="closeModal('shareModal')">Fermer</button>
        </div>
    </div>
</div>

<script>
let selectedFiles = [];

// Mise à jour de la sélection
function updateSelection() {
    selectedFiles = [];
    const checkboxes = document.querySelectorAll('.file-checkbox:checked');
    checkboxes.forEach(cb => {
        const row = cb.closest('.file-row');
        selectedFiles.push({
            path: cb.value,
            name: row.dataset.name,
            isDir: row.dataset.isDir === '1'
        });
        row.classList.add('selected');
    });
    
    document.querySelectorAll('.file-checkbox:not(:checked)').forEach(cb => {
        cb.closest('.file-row').classList.remove('selected');
    });
    
    const count = selectedFiles.length;
    document.getElementById('selectionCount').textContent = count;
    document.getElementById('selectionInfo').classList.toggle('active', count > 0);
    
    const hasSelection = count > 0;
    const singleSelection = count === 1;
    const singleFileSelection = singleSelection && !selectedFiles[0].isDir;
    const hasZipFile = singleFileSelection && selectedFiles[0].name.match(/\.(zip|rar|7z|tar|gz)$/i);
    
    document.getElementById('btnCopy').disabled = !hasSelection;
    document.getElementById('btnMove').disabled = !hasSelection;
    document.getElementById('btnDownload').disabled = !hasSelection;
    document.getElementById('btnDelete').disabled = !hasSelection;
    document.getElementById('btnRename').disabled = !singleSelection;
    document.getElementById('btnView').disabled = !singleFileSelection;
    document.getElementById('btnCompress').disabled = !hasSelection;
    document.getElementById('btnExtract').disabled = !hasZipFile;
    document.getElementById('btnFavorite').disabled = !hasSelection;
    document.getElementById('btnShare').disabled = !singleFileSelection;
}

function selectAll() {
    document.querySelectorAll('.file-checkbox').forEach(cb => cb.checked = true);
    document.getElementById('selectAllCheckbox').checked = true;
    updateSelection();
}

function deselectAll() {
    document.querySelectorAll('.file-checkbox').forEach(cb => cb.checked = false);
    document.getElementById('selectAllCheckbox').checked = false;
    updateSelection();
}

function toggleSelectAll(checkbox) {
    if (checkbox.checked) {
        selectAll();
    } else {
        deselectAll();
    }
}

function showCreateFolder() {
    const folderName = prompt("Nom du nouveau dossier :");
    if (folderName) {
        const form = document.createElement('form');
        form.method = 'post';
        form.action = 'cloud_create_folder.php';
        
        const pathInput = document.createElement('input');
        pathInput.type = 'hidden';
        pathInput.name = 'current_path';
        pathInput.value = '<?= htmlspecialchars($currentPath) ?>';
        
        const nameInput = document.createElement('input');
        nameInput.type = 'hidden';
        nameInput.name = 'folder_name';
        nameInput.value = folderName;
        
        form.appendChild(pathInput);
        form.appendChild(nameInput);
        document.body.appendChild(form);
        form.submit();
    }
}

function showUploadModal() {
    document.getElementById('uploadModal').classList.add('active');
}

function showCopyModal() {
    if (selectedFiles.length === 0) return;
    document.getElementById('copyFiles').value = JSON.stringify(selectedFiles.map(f => f.path));
    document.getElementById('copyModal').classList.add('active');
}

function showMoveModal() {
    if (selectedFiles.length === 0) return;
    document.getElementById('moveFiles').value = JSON.stringify(selectedFiles.map(f => f.path));
    document.getElementById('moveModal').classList.add('active');
}

function showRenameModal() {
    if (selectedFiles.length !== 1) return;
    const file = selectedFiles[0];
    document.getElementById('renameFile').value = file.path;
    document.getElementById('renameNewName').value = file.name;
    document.getElementById('renameModal').classList.add('active');
}

async function viewFile() {
    if (selectedFiles.length !== 1 || selectedFiles[0].isDir) return;
    const file = selectedFiles[0];
    
    try {
        const response = await fetch('cloud_actions.php?action=view&file=' + encodeURIComponent(file.path));
        const data = await response.json();
        
        const viewContent = document.getElementById('viewContent');
        
        if (data.type === 'image') {
            viewContent.innerHTML = `<img src="${data.url}" style="max-width: 100%; height: auto; border-radius: 8px;">`;
        } else if (data.type === 'text') {
            viewContent.innerHTML = `<pre style="margin: 0; white-space: pre-wrap; word-wrap: break-word; font-size: 13px;">${escapeHtml(data.content)}</pre>`;
        } else {
            viewContent.innerHTML = `<p>Type de fichier : ${data.mime}</p><p>Impossible d'afficher un aperçu de ce type de fichier.</p>`;
        }
        
        document.getElementById('viewModal').classList.add('active');
    } catch (error) {
        alert('Erreur lors du chargement du fichier : ' + error.message);
    }
}

function downloadSelected() {
    if (selectedFiles.length === 0) return;
    
    selectedFiles.forEach(file => {
        if (!file.isDir) {
            window.location.href = 'cloud_download.php?file=' + encodeURIComponent(file.path);
        }
    });
}

function deleteSelected() {
    if (selectedFiles.length === 0) return;
    
    const fileNames = selectedFiles.map(f => f.name).join(', ');
    if (!confirm(`Voulez-vous vraiment supprimer ${selectedFiles.length} élément(s) ?\n\n${fileNames}`)) {
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'post';
    form.action = 'cloud_actions.php';
    
    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    actionInput.value = 'delete_multiple';
    
    const filesInput = document.createElement('input');
    filesInput.type = 'hidden';
    filesInput.name = 'files';
    filesInput.value = JSON.stringify(selectedFiles.map(f => f.path));
    
    const pathInput = document.createElement('input');
    pathInput.type = 'hidden';
    pathInput.name = 'current_path';
    pathInput.value = '<?= htmlspecialchars($currentPath) ?>';
    
    form.appendChild(actionInput);
    form.appendChild(filesInput);
    form.appendChild(pathInput);
    document.body.appendChild(form);
    form.submit();
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
}

document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal(this.id);
        }
    });
});

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Nouvelle fonctionnalité : Recherche
function showSearchModal() {
    document.getElementById('searchModal').classList.add('active');
    document.getElementById('searchInput').focus();
}

let searchTimeout;
function performSearch() {
    clearTimeout(searchTimeout);
    const query = document.getElementById('searchInput').value.trim().toLowerCase();
    
    if (query.length < 2) {
        document.getElementById('searchResults').innerHTML = 
            '<p style="text-align: center; color: #7f8c8d;">Saisissez au moins 2 caractères...</p>';
        return;
    }
    
    searchTimeout = setTimeout(() => {
        const allRows = document.querySelectorAll('.file-row');
        const results = [];
        
        allRows.forEach(row => {
            const name = row.dataset.name.toLowerCase();
            if (name.includes(query)) {
                results.push({
                    name: row.dataset.name,
                    path: row.dataset.path,
                    isDir: row.dataset.isDir === '1'
                });
            }
        });
        
        if (results.length === 0) {
            document.getElementById('searchResults').innerHTML = 
                '<p style="text-align: center; color: #7f8c8d;">Aucun résultat trouvé</p>';
        } else {
            let html = '<div style="border: 1px solid #dee2e6; border-radius: 8px; overflow: hidden;">';
            results.forEach(result => {
                const icon = result.isDir ? 'folder' : 'file';
                html += `
                    <div style="padding: 12px; border-bottom: 1px solid #f1f3f5; cursor: pointer; transition: background 0.2s;"
                         onmouseover="this.style.background='#f8f9fa'" 
                         onmouseout="this.style.background='white'"
                         onclick="navigateToFile('${result.path}', ${result.isDir})">
                        <img src="/images/icon/${icon}.png" style="width: 16px; height: 16px; vertical-align: middle; margin-right: 8px;">
                        <strong>${escapeHtml(result.name)}</strong>
                        <br>
                        <small style="color: #7f8c8d; margin-left: 24px;">${escapeHtml(result.path)}</small>
                    </div>
                `;
            });
            html += '</div>';
            document.getElementById('searchResults').innerHTML = html;
        }
    }, 300);
}

function navigateToFile(path, isDir) {
    if (isDir) {
        window.location.href = 'panel.php?path=' + encodeURIComponent(path);
    } else {
        const dirPath = path.substring(0, path.lastIndexOf('/'));
        window.location.href = 'panel.php?path=' + encodeURIComponent(dirPath);
    }
}

// Compresser des fichiers
function showCompressModal() {
    if (selectedFiles.length === 0) return;
    const defaultName = selectedFiles.length === 1 ? selectedFiles[0].name : 'archive';
    document.getElementById('archiveName').value = defaultName + '.zip';
    document.getElementById('compressFiles').value = JSON.stringify(selectedFiles.map(f => f.path));
    document.getElementById('compressModal').classList.add('active');
}

// Extraire une archive
function showExtractModal() {
    if (selectedFiles.length !== 1) return;
    const file = selectedFiles[0];
    document.getElementById('extractFile').value = file.path;
    document.getElementById('extractInfo').textContent = `L'archive "${file.name}" sera extraite dans un nouveau dossier`;
    document.getElementById('extractModal').classList.add('active');
}

// Ajouter aux favoris
function toggleFavorite() {
    if (selectedFiles.length === 0) return;
    
    // Pour l'instant, simple alerte (à implémenter avec stockage)
    alert('Fonctionnalité "Favoris" : ' + selectedFiles.length + ' fichier(s) ajouté(s) aux favoris !\n\n(Cette fonction nécessite une base de données pour être complètement fonctionnelle)');
}

// Partager un fichier
function showShareModal() {
    if (selectedFiles.length !== 1 || selectedFiles[0].isDir) return;
    const file = selectedFiles[0];
    
    // Générer un lien de partage temporaire
    const shareUrl = window.location.origin + '/cloud_download.php?file=' + encodeURIComponent(file.path) + '&share=' + btoa(file.path);
    document.getElementById('shareLink').value = shareUrl;
    document.getElementById('shareModal').classList.add('active');
}

function copyShareLink() {
    const linkInput = document.getElementById('shareLink');
    linkInput.select();
    document.execCommand('copy');
    alert('Lien copié dans le presse-papiers !');
}

updateSelection();
</script>

</body>
</html>