<?php
// admin/reviews/delete.php
session_start();
require_once '../../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

$review_id = (int)($_GET['id'] ?? 0);

if ($review_id > 0) {
    // Récupérer le product_id avant suppression
    $stmt = $pdo->prepare("SELECT product_id FROM reviews WHERE id = ?");
    $stmt->execute([$review_id]);
    $product_id = $stmt->fetchColumn();
    
    // Supprimer l'avis
    $pdo->prepare("DELETE FROM reviews WHERE id = ?")->execute([$review_id]);
    
    // Mettre à jour la note du produit
    if ($product_id) {
        $stmt = $pdo->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as count 
                               FROM reviews WHERE product_id = ? AND approved = 1");
        $stmt->execute([$product_id]);
        $result = $stmt->fetch();
        
        $pdo->prepare("UPDATE products SET rating = ?, review_count = ? WHERE id = ?")
            ->execute([$result['avg_rating'] ?? 0, $result['count'], $product_id]);
    }
}

header('Location: list.php');
exit;