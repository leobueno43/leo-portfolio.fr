<?php
// admin/orders/update_notes.php - Mettre à jour les notes internes
// ============================================

session_start();
require_once '../../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = (int)($_POST['order_id'] ?? 0);
    $admin_notes = trim($_POST['admin_notes'] ?? '');
    
    try {
        $stmt = $pdo->prepare("UPDATE orders SET admin_notes = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$admin_notes, $order_id]);
        
        $_SESSION['success'] = 'Notes mises à jour avec succès';
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Erreur lors de la mise à jour';
    }
    
    header('Location: view.php?id=' . $order_id);
    exit;
}

header('Location: list.php');
exit;
?>
