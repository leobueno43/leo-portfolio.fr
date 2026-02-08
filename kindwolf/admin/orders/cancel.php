<?php
// admin/orders/cancel.php - Annuler une commande (Admin)
// ============================================

session_start();
require_once '../../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

$order_id = (int)($_GET['id'] ?? 0);
$reason = trim($_POST['reason'] ?? '');

// Vérifier que la commande existe
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    $_SESSION['error'] = 'Commande introuvable';
    header('Location: ' . BASE_URL . '/admin/orders/list.php');
    exit;
}

// Afficher le formulaire si pas de soumission
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Annuler la commande - Admin KIND WOLF</title>
        <link rel="stylesheet" href="<?php echo BASE_URL; ?>/style.css">
    </head>
<body>
    <div class="admin-container">
            <?php include '../admin_sidebar.php'; ?>
            
            <main class="admin-main">
                <h1>Annuler la commande #<?php echo htmlspecialchars($order['order_number']); ?></h1>
                
                <div class="admin-section">
                    <p><strong>⚠️ Attention :</strong> L'annulation de cette commande va :</p>
                    <ul>
                        <li>Restaurer le stock des produits</li>
                        <li>Annuler l'utilisation du code promo (si applicable)</li>
                        <li>Notifier le client par email</li>
                    </ul>
                    
                    <form method="POST" style="margin-top: 2rem;">
                        <div class="form-group">
                            <label for="reason">Raison de l'annulation *</label>
                            <textarea id="reason" name="reason" rows="4" required 
                                      placeholder="Ex: Produit en rupture de stock, demande du client..."></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn-primary">Confirmer l'annulation</button>
                            <a href="view.php?id=<?php echo $order_id; ?>" class="btn-outline">Retour</a>
                        </div>
                    </form>
                </div>
            </main>
        </div>
        
        <?php include '../../footer.php'; ?>
    </body>
    </html>
    <?php
    exit;
}

try {
    // Commencer une transaction
    $pdo->beginTransaction();
    
    // Annuler la commande
    $stmt = $pdo->prepare("UPDATE orders SET status = 'cancelled', admin_notes = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$reason, $order_id]);
    
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
    
    $_SESSION['success'] = 'Commande annulée avec succès';
    header('Location: ' . BASE_URL . '/admin/orders/view.php?id=' . $order_id);
    exit;
    
} catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['error'] = 'Erreur lors de l\'annulation de la commande: ' . $e->getMessage();
    header('Location: ' . BASE_URL . '/admin/orders/view.php?id=' . $order_id);
    exit;
}
?>
