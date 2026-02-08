<!-- admin/orders/update_status.php - Mise à jour statut commande -->
<!-- ============================================ -->
<?php
session_start();
require_once '../../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = (int)($_POST['order_id'] ?? 0);
    $status = $_POST['status'] ?? '';
    
    $allowed_statuses = ['pending', 'processing', 'shipped', 'completed', 'cancelled', 'refunded'];
    
    if (!in_array($status, $allowed_statuses)) {
        echo json_encode(['success' => false, 'message' => 'Statut invalide']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
        
        if ($stmt->execute([$status, $order_id])) {
            // Mettre à jour les dates spécifiques selon le statut
            if ($status === 'shipped') {
                $pdo->prepare("UPDATE orders SET shipped_at = NOW() WHERE id = ?")->execute([$order_id]);
            } elseif ($status === 'completed') {
                $pdo->prepare("UPDATE orders SET completed_at = NOW() WHERE id = ?")->execute([$order_id]);
            }
            
            echo json_encode(['success' => true, 'message' => 'Statut mis à jour']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur de mise à jour']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}
?>