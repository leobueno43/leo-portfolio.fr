<!-- admin/products/delete.php - Supprimer produit -->
<!-- ============================================ -->
<?php
session_start();
require_once '../../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../../login.php');
    exit;
}

$product_id = (int)($_GET['id'] ?? 0);

if ($product_id > 0) {
    // Suppression logique : marquer le produit comme inactif au lieu de le supprimer
    // Cela préserve l'intégrité des commandes existantes qui référencent ce produit
    $stmt = $pdo->prepare("UPDATE products SET active = 0, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$product_id]);
    
    $_SESSION['success_message'] = "Produit supprimé avec succès";
}

header('Location: list.php');
exit;
?>