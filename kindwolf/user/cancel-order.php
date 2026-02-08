<?php
// user/cancel-order.php - Annuler une commande
// ============================================

session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

$order_id = (int)($_GET['id'] ?? 0);
$user_id = $_SESSION['user_id'];

// Vérifier que la commande existe et appartient à l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    $_SESSION['error'] = 'Commande introuvable';
    header('Location: ' . BASE_URL . '/user/commandes.php');
    exit;
}

// Vérifier que la commande peut être annulée (uniquement si pending)
if ($order['status'] !== 'pending') {
    $_SESSION['error'] = 'Cette commande ne peut plus être annulée. Contactez le support.';
    header('Location: ' . BASE_URL . '/user/order-detail.php?id=' . $order_id);
    exit;
}

try {
    // Commencer une transaction
    $pdo->beginTransaction();
    
    // Annuler la commande
    $stmt = $pdo->prepare("UPDATE orders SET status = 'cancelled', updated_at = NOW() WHERE id = ?");
    $stmt->execute([$order_id]);
    
    // Restaurer le stock des produits
    $stmt = $pdo->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $items = $stmt->fetchAll();
    
    foreach ($items as $item) {
        $stmt = $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
        $stmt->execute([$item['quantity'], $item['product_id']]);
    }
    
    // Si un code promo a été utilisé, décrémenter son usage
    if ($order['promo_code']) {
        $stmt = $pdo->prepare("UPDATE promo_codes SET usage_count = usage_count - 1 WHERE code = ?");
        $stmt->execute([$order['promo_code']]);
        
        // Supprimer l'usage de l'utilisateur
        $stmt = $pdo->prepare("DELETE FROM promo_usage WHERE order_id = ?");
        $stmt->execute([$order_id]);
    }
    
    // Valider la transaction
    $pdo->commit();
    
    $_SESSION['success'] = 'Votre commande a été annulée avec succès.';
    header('Location: ' . BASE_URL . '/user/commandes.php');
    exit;
    
} catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['error'] = 'Erreur lors de l\'annulation de la commande';
    header('Location: ' . BASE_URL . '/user/order-detail.php?id=' . $order_id);
    exit;
}
?>
