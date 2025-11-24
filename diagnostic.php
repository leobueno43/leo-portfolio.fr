<?php
session_start();

// Vérification de la connexion admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$CLOUD_STORAGE_DIR = __DIR__ . '/storage/';
$errors = [];
$warnings = [];
$success = [];

// Test 1: Vérifier si le dossier storage existe
if (is_dir($CLOUD_STORAGE_DIR)) {
    $success[] = "✓ Le dossier 'storage/' existe";
} else {
    $errors[] = "✗ Le dossier 'storage/' n'existe pas";
    
    // Essayer de le créer
    if (mkdir($CLOUD_STORAGE_DIR, 0755, true)) {
        $success[] = "✓ Le dossier 'storage/' a été créé avec succès";
        file_put_contents($CLOUD_STORAGE_DIR . '.htaccess', "Deny from all\n");
        $success[] = "✓ Fichier .htaccess créé pour la sécurité";
    } else {
        $errors[] = "✗ IMPOSSIBLE de créer le dossier 'storage/'";
    }
}

// Test 2: Vérifier les permissions du dossier storage
if (is_dir($CLOUD_STORAGE_DIR)) {
    $perms = substr(sprintf('%o', fileperms($CLOUD_STORAGE_DIR)), -4);
    if (is_writable($CLOUD_STORAGE_DIR)) {
        $success[] = "✓ Le dossier 'storage/' est accessible en écriture (permissions: $perms)";
    } else {
        $errors[] = "✗ Le dossier 'storage/' n'est PAS accessible en écriture (permissions: $perms)";
        $errors[] = "→ Exécutez: chmod 755 " . $CLOUD_STORAGE_DIR;
    }
}

// Test 3: Vérifier les permissions du dossier parent
$parentDir = __DIR__;
$parentPerms = substr(sprintf('%o', fileperms($parentDir)), -4);
if (is_writable($parentDir)) {
    $success[] = "✓ Le dossier racine est accessible en écriture (permissions: $parentPerms)";
} else {
    $warnings[] = "⚠ Le dossier racine n'est pas accessible en écriture (permissions: $parentPerms)";
}

// Test 4: Vérifier la configuration PHP pour les uploads
$upload_max = ini_get('upload_max_filesize');
$post_max = ini_get('post_max_size');
$memory_limit = ini_get('memory_limit');

$success[] = "✓ Configuration PHP:";
$success[] = "  - upload_max_filesize: $upload_max";
$success[] = "  - post_max_size: $post_max";
$success[] = "  - memory_limit: $memory_limit";

// Convertir en bytes pour comparaison
function convertToBytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    $val = (int)$val;
    switch($last) {
        case 'g': $val *= 1024;
        case 'm': $val *= 1024;
        case 'k': $val *= 1024;
    }
    return $val;
}

$uploadBytes = convertToBytes($upload_max);
$postBytes = convertToBytes($post_max);

if ($uploadBytes < 10485760) { // 10MB
    $warnings[] = "⚠ upload_max_filesize est petit ($upload_max). Recommandé: 100M";
}
if ($postBytes < 10485760) { // 10MB
    $warnings[] = "⚠ post_max_size est petit ($post_max). Recommandé: 100M";
}

// Test 5: Vérifier ZipArchive pour compression
if (class_exists('ZipArchive')) {
    $success[] = "✓ ZipArchive est disponible (compression/extraction fonctionnelle)";
} else {
    $warnings[] = "⚠ ZipArchive n'est pas disponible (compression désactivée)";
}

// Test 6: Tester la création d'un fichier
$testFile = $CLOUD_STORAGE_DIR . '.test_write_' . time() . '.txt';
if (is_dir($CLOUD_STORAGE_DIR)) {
    if (file_put_contents($testFile, 'test') !== false) {
        $success[] = "✓ Test d'écriture réussi";
        unlink($testFile); // Supprimer le fichier de test
    } else {
        $errors[] = "✗ IMPOSSIBLE d'écrire dans le dossier storage/";
    }
}

// Test 7: Vérifier la session
if (isset($_SESSION['admin_logged_in'])) {
    $success[] = "✓ Session admin active";
} else {
    $errors[] = "✗ Session non active";
}

// Test 8: Vérifier les chemins
$success[] = "✓ Chemins système:";
$success[] = "  - __DIR__: " . __DIR__;
$success[] = "  - Storage: " . $CLOUD_STORAGE_DIR;
$success[] = "  - Realpath storage: " . (realpath($CLOUD_STORAGE_DIR) ?: 'N/A');

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>🔧 Diagnostic Cloud</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 20px;
            margin: 0;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            padding: 32px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        h1 {
            margin: 0 0 24px 0;
            color: #2c3e50;
            font-size: 28px;
        }
        .section {
            margin: 24px 0;
            padding: 16px;
            border-radius: 8px;
            border-left: 4px solid;
        }
        .success {
            background: #d4edda;
            border-color: #27ae60;
            color: #155724;
        }
        .warning {
            background: #fff3cd;
            border-color: #f39c12;
            color: #856404;
        }
        .error {
            background: #f8d7da;
            border-color: #e74c3c;
            color: #721c24;
        }
        .section h3 {
            margin: 0 0 12px 0;
            font-size: 18px;
        }
        .section ul {
            margin: 0;
            padding-left: 20px;
        }
        .section li {
            margin: 6px 0;
            line-height: 1.6;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin-top: 24px;
            transition: all 0.3s;
        }
        .btn:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }
        code {
            background: rgba(0,0,0,0.05);
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Diagnostic du Cloud Storage</h1>
        
        <?php if (!empty($errors)): ?>
        <div class="section error">
            <h3>❌ Erreurs Critiques</h3>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($warnings)): ?>
        <div class="section warning">
            <h3>⚠️ Avertissements</h3>
            <ul>
                <?php foreach ($warnings as $warning): ?>
                    <li><?= htmlspecialchars($warning) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
        <div class="section success">
            <h3>✅ Tests Réussis</h3>
            <ul>
                <?php foreach ($success as $item): ?>
                    <li><?= htmlspecialchars($item) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <div style="margin-top: 32px; padding-top: 24px; border-top: 2px solid #e0e0e0;">
            <h3 style="margin-bottom: 16px;">🛠️ Actions Recommandées</h3>
            
            <?php if (!empty($errors)): ?>
                <p style="color: #e74c3c; font-weight: 600;">
                    ⚠️ Des erreurs critiques ont été détectées. Le cloud ne fonctionnera pas correctement.
                </p>
                <p><strong>Solutions :</strong></p>
                <ol>
                    <li>Connectez-vous en SSH à votre serveur</li>
                    <li>Exécutez : <code>chmod 755 <?= htmlspecialchars(__DIR__) ?>/storage</code></li>
                    <li>Ou via cPanel : Gestionnaire de fichiers → storage → Permissions → 755</li>
                </ol>
            <?php elseif (!empty($warnings)): ?>
                <p style="color: #f39c12;">
                    ⚠️ Le cloud fonctionne mais des optimisations sont recommandées.
                </p>
            <?php else: ?>
                <p style="color: #27ae60; font-weight: 600;">
                    ✅ Tout fonctionne parfaitement ! Vous pouvez utiliser le cloud.
                </p>
            <?php endif; ?>
        </div>
        
        <div style="text-align: center;">
            <a href="panel.php" class="btn">← Retour au Cloud</a>
            <a href="?refresh=1" class="btn" style="background: #95a5a6;">🔄 Relancer le diagnostic</a>
        </div>
    </div>
</body>
</html>