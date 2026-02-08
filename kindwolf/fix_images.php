<?php
// fix_images.php - Script pour corriger les chemins d'images dans la BDD
// ============================================
// À exécuter UNE SEULE FOIS puis supprimer

session_start();
require_once 'config.php';

echo "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <title>Correction des images - KIND WOLF</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 2rem; max-width: 800px; margin: 0 auto; }
        .success { background: #d4edda; padding: 1rem; margin: 1rem 0; border-radius: 5px; color: #155724; }
        .error { background: #f8d7da; padding: 1rem; margin: 1rem 0; border-radius: 5px; color: #721c24; }
        .info { background: #d1ecf1; padding: 1rem; margin: 1rem 0; border-radius: 5px; color: #0c5460; }
        table { width: 100%; border-collapse: collapse; margin: 1rem 0; }
        th, td { padding: 0.5rem; text-align: left; border: 1px solid #ddd; }
        th { background: #2F5D50; color: white; }
        .btn { padding: 1rem 2rem; background: #2F5D50; color: white; border: none; border-radius: 5px; cursor: pointer; margin: 1rem 0; }
        .btn:hover { background: #3a7362; }
    </style>
</head>
<body>
    <h1>🔧 Correction des chemins d'images</h1>";

// Récupérer tous les produits
$stmt = $pdo->query("SELECT id, name, image FROM products");
$products = $stmt->fetchAll();

echo "<div class='info'><strong>Total produits :</strong> " . count($products) . "</div>";

$corrected = 0;
$errors = 0;

echo "<h2>Analyse des images :</h2>";
echo "<table>
        <tr>
            <th>ID</th>
            <th>Produit</th>
            <th>Image actuelle</th>
            <th>Action</th>
            <th>Résultat</th>
        </tr>";

foreach ($products as $product) {
    $id = $product['id'];
    $name = $product['name'];
    $current_image = $product['image'];
    
    echo "<tr>";
    echo "<td>#{$id}</td>";
    echo "<td>" . htmlspecialchars($name) . "</td>";
    echo "<td>" . htmlspecialchars($current_image ?: '(vide)') . "</td>";
    
    // Vérifier si l'image existe et corriger
    if (!empty($current_image)) {
        // Cas 1 : Image contient déjà "images/products/"
        if (strpos($current_image, 'images/products/') === 0) {
            echo "<td>✅ Déjà correct</td>";
            echo "<td>-</td>";
        }
        // Cas 2 : Image est juste un nom de fichier (ex: 696191fd7c167.webp)
        elseif (!strpos($current_image, '/')) {
            $new_path = 'images/products/' . $current_image;
            
            // Vérifier si le fichier existe
            $file_path = $_SERVER['DOCUMENT_ROOT'] . '/kindwolf/' . $new_path;
            
            if (file_exists($file_path)) {
                // Mettre à jour la BDD
                try {
                    $update = $pdo->prepare("UPDATE products SET image = ? WHERE id = ?");
                    $update->execute([$new_path, $id]);
                    
                    echo "<td>🔧 Corrigé</td>";
                    echo "<td><strong>✅ " . htmlspecialchars($new_path) . "</strong></td>";
                    $corrected++;
                } catch (PDOException $e) {
                    echo "<td>❌ Erreur SQL</td>";
                    echo "<td>" . htmlspecialchars($e->getMessage()) . "</td>";
                    $errors++;
                }
            } else {
                echo "<td>⚠️ Fichier introuvable</td>";
                echo "<td>Fichier n'existe pas : {$new_path}</td>";
                $errors++;
            }
        }
        // Cas 3 : Autre chemin
        else {
            echo "<td>⚠️ Chemin non standard</td>";
            echo "<td>À vérifier manuellement</td>";
        }
    } else {
        echo "<td>⚠️ Vide</td>";
        echo "<td>Pas d'image</td>";
    }
    
    echo "</tr>";
}

echo "</table>";

// Résumé
echo "<h2>📊 Résumé</h2>";

if ($corrected > 0) {
    echo "<div class='success'>
            ✅ <strong>{$corrected} image(s)</strong> corrigée(s) avec succès !
          </div>";
}

if ($errors > 0) {
    echo "<div class='error'>
            ❌ <strong>{$errors} erreur(s)</strong> détectée(s)
          </div>";
}

if ($corrected === 0 && $errors === 0) {
    echo "<div class='success'>
            ✅ Toutes les images sont déjà correctes !
          </div>";
}

// Vérification finale
echo "<h2>🔍 Vérification finale</h2>";
$stmt = $pdo->query("SELECT id, name, image FROM products WHERE image IS NOT NULL AND image != ''");
$all_products = $stmt->fetchAll();

echo "<table>
        <tr>
            <th>ID</th>
            <th>Produit</th>
            <th>Chemin image</th>
            <th>Fichier existe ?</th>
        </tr>";

foreach ($all_products as $product) {
    $file_path = $_SERVER['DOCUMENT_ROOT'] . '/kindwolf/' . $product['image'];
    $exists = file_exists($file_path);
    
    echo "<tr>";
    echo "<td>#{$product['id']}</td>";
    echo "<td>" . htmlspecialchars($product['name']) . "</td>";
    echo "<td>" . htmlspecialchars($product['image']) . "</td>";
    echo "<td>" . ($exists ? "✅ Oui" : "❌ Non") . "</td>";
    echo "</tr>";
}

echo "</table>";

echo "<hr>
      <div class='info'>
        <h3>📝 Instructions :</h3>
        <ol>
            <li>Vérifiez les résultats ci-dessus</li>
            <li>Si tout est OK, retournez sur votre site : <a href='" . BASE_URL . "'>Accueil</a></li>
            <li>Vérifiez que les images s'affichent</li>
            <li><strong>Supprimez ce fichier fix_images.php</strong> par sécurité</li>
        </ol>
      </div>
      
      <a href='" . BASE_URL . "' class='btn'>🏠 Retour à l'accueil</a>
      
</body>
</html>";
?>