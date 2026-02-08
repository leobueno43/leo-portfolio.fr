<?php
require_once __DIR__ . '/config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    die('Non connecté');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $CLOUD_STORAGE_DIR = __DIR__ . '/storage/';
    $folderName = trim($_POST['folder_name'] ?? '');
    $currentPath = trim($_POST['current_path'] ?? '', '/');
    
    echo "<h2>🔍 Debug Création de Dossier</h2>";
    echo "<pre>";
    
    echo "1. Données reçues:\n";
    echo "   - Nom du dossier: '" . htmlspecialchars($folderName) . "'\n";
    echo "   - Chemin actuel: '" . htmlspecialchars($currentPath) . "'\n\n";
    
    $safeName = preg_replace('/[^a-zA-Z0-9_\-\s]/', '_', $folderName);
    $safeName = trim($safeName);
    echo "2. Nom nettoyé: '" . htmlspecialchars($safeName) . "'\n\n";
    
    if (empty($currentPath)) {
        $currentFullPath = $CLOUD_STORAGE_DIR;
    } else {
        $currentFullPath = $CLOUD_STORAGE_DIR . $currentPath;
    }
    
    echo "3. Chemins:\n";
    echo "   - CLOUD_STORAGE_DIR: " . $CLOUD_STORAGE_DIR . "\n";
    echo "   - currentFullPath: " . $currentFullPath . "\n";
    echo "   - Dossier existe? " . (is_dir($currentFullPath) ? 'OUI' : 'NON') . "\n\n";
    
    if (!is_dir($currentFullPath)) {
        echo "4. Création du dossier parent...\n";
        if (mkdir($currentFullPath, 0755, true)) {
            echo "   ✓ Créé avec succès\n\n";
        } else {
            echo "   ✗ ÉCHEC\n\n";
        }
    }
    
    $realCurrentPath = realpath($currentFullPath);
    $realStorageDir = realpath($CLOUD_STORAGE_DIR);
    
    echo "5. Chemins réels (realpath):\n";
    echo "   - realCurrentPath: " . ($realCurrentPath ?: 'FALSE') . "\n";
    echo "   - realStorageDir: " . ($realStorageDir ?: 'FALSE') . "\n\n";
    
    if ($realCurrentPath !== false && $realStorageDir !== false) {
        echo "6. Vérification sécurité:\n";
        echo "   - realCurrentPath commence par realStorageDir? ";
        if (strpos($realCurrentPath, $realStorageDir) === 0) {
            echo "OUI ✓\n\n";
            
            $targetDir = $realCurrentPath . '/' . $safeName;
            echo "7. Dossier cible:\n";
            echo "   - Chemin: " . $targetDir . "\n";
            echo "   - Existe déjà? " . (file_exists($targetDir) ? 'OUI' : 'NON') . "\n\n";
            
            if (!file_exists($targetDir)) {
                echo "8. Tentative de création...\n";
                if (mkdir($targetDir, 0755, true)) {
                    echo "   ✓✓✓ SUCCÈS ! Dossier créé ✓✓✓\n";
                    echo "\n<a href='panel.php?path=" . urlencode($currentPath) . "'>→ Retour au panel</a>";
                } else {
                    echo "   ✗✗✗ ÉCHEC ! Impossible de créer\n";
                    echo "   Erreur PHP: " . error_get_last()['message'] ?? 'Aucune';
                }
            } else {
                echo "   ⚠ Le dossier existe déjà\n";
            }
        } else {
            echo "NON ✗ (PROBLÈME DE SÉCURITÉ)\n";
            echo "   realCurrentPath: $realCurrentPath\n";
            echo "   realStorageDir: $realStorageDir\n";
        }
    } else {
        echo "6. ✗ Erreur: Un des chemins réels est FALSE\n";
    }
    
    echo "</pre>";
} else {
    echo "<h2>Formulaire de test</h2>";
    echo "<form method='post'>";
    echo "Nom du dossier: <input type='text' name='folder_name' value='test_folder'><br>";
    echo "Chemin actuel: <input type='text' name='current_path' value=''><br>";
    echo "<button type='submit'>Créer (Mode Debug)</button>";
    echo "</form>";
}
?>