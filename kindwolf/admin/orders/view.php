<!-- admin/orders/view.php - Voir détail commande -->
<!-- ============================================ -->
<?php
session_start();
require_once '../../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../../login.php');
    exit;
}

$order_id = (int)($_GET['id'] ?? 0);

// Récupérer la commande
$stmt = $pdo->prepare("SELECT o.*, u.name as customer_name, u.email as customer_email 
                       FROM orders o 
                       JOIN users u ON o.user_id = u.id 
                       WHERE o.id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: list.php');
    exit;
}

// Récupérer les articles
$stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();

// Décoder l'adresse
$shipping_address = json_decode($order['shipping_address'], true);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commande #<?php echo $order['order_number']; ?> - KIND WOLF Admin</title>
    <link rel="stylesheet" href="../../style.css">
</head>
<body>
    <div class="admin-container">
        <?php include '../admin_sidebar.php'; ?>
        
        <div class="admin-main">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php 
                    echo htmlspecialchars($_SESSION['success']); 
                    unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <?php 
                    echo htmlspecialchars($_SESSION['error']); 
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>
            
            <div class="admin-header">
                <h1>Commande #<?php echo htmlspecialchars($order['order_number']); ?></h1>
                <div class="header-actions">
                    <a href="invoice.php?id=<?php echo $order['id']; ?>" class="btn-secondary" target="_blank">📄 Facture</a>
                    <?php if (!in_array($order['status'], ['cancelled', 'completed'])): ?>
                        <a href="cancel.php?id=<?php echo $order['id']; ?>" class="btn-outline">❌ Annuler</a>
                    <?php endif; ?>
                    <a href="list.php" class="btn-outline">← Retour</a>
                </div>
            </div>

            <div class="order-detail-grid">
                <div class="admin-section">
                    <h2>Informations générales</h2>
                    <table class="info-table">
                        <tr>
                            <th>N° Commande:</th>
                            <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                        </tr>
                        <tr>
                            <th>Date:</th>
                            <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                        </tr>
                        <tr>
                            <th>Client:</th>
                            <td>
                                <?php echo htmlspecialchars($order['customer_name']); ?><br>
                                <small><?php echo htmlspecialchars($order['customer_email']); ?></small>
                            </td>
                        </tr>
                        <tr>
                            <th>Statut:</th>
                            <td>
                                <select onchange="updateOrderStatus(<?php echo $order['id']; ?>, this.value)" 
                                        class="status-select status-<?php echo $order['status']; ?>">
                                    <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>En attente</option>
                                    <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>En cours</option>
                                    <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Expédié</option>
                                    <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Terminé</option>
                                    <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Annulé</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th>Paiement:</th>
                            <td>
                                <?php echo htmlspecialchars($order['payment_method']); ?><br>
                                <span class="payment-status payment-<?php echo $order['payment_status']; ?>">
                                    <?php echo ucfirst($order['payment_status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php if ($order['tracking_number']): ?>
                        <tr>
                            <th>N° de suivi:</th>
                            <td><?php echo htmlspecialchars($order['tracking_number']); ?></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>

                <div class="admin-section">
                    <h2>Adresse de livraison</h2>
                    <?php if ($shipping_address): ?>
                    <address>
                        <strong><?php echo htmlspecialchars($shipping_address['firstname'] . ' ' . $shipping_address['lastname']); ?></strong><br>
                        <?php echo htmlspecialchars($shipping_address['address_line1']); ?><br>
                        <?php if ($shipping_address['address_line2']): ?>
                            <?php echo htmlspecialchars($shipping_address['address_line2']); ?><br>
                        <?php endif; ?>
                        <?php echo htmlspecialchars($shipping_address['postal_code'] . ' ' . $shipping_address['city']); ?><br>
                        <?php echo htmlspecialchars($shipping_address['country']); ?><br>
                        Tél: <?php echo htmlspecialchars($shipping_address['phone']); ?>
                    </address>
                    <?php endif; ?>
                </div>
            </div>

            <div class="admin-section">
                <h2>Articles commandés</h2>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Produit</th>
                            <th>SKU</th>
                            <th>Prix unitaire</th>
                            <th>Quantité</th>
                            <th>Sous-total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                            <td><?php echo htmlspecialchars($item['product_sku']); ?></td>
                            <td><?php echo number_format($item['price'], 2); ?> €</td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td><?php echo number_format($item['subtotal'], 2); ?> €</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4">Sous-total</th>
                            <th><?php echo number_format($order['subtotal'], 2); ?> €</th>
                        </tr>
                        <tr>
                            <th colspan="4">Livraison</th>
                            <th><?php echo number_format($order['shipping_cost'], 2); ?> €</th>
                        </tr>
                        <?php if ($order['discount_amount'] > 0): ?>
                        <tr>
                            <th colspan="4">Réduction<?php echo $order['promo_code'] ? ' (' . htmlspecialchars($order['promo_code']) . ')' : ''; ?></th>
                            <th>-<?php echo number_format($order['discount_amount'], 2); ?> €</th>
                        </tr>
                        <?php endif; ?>
                        <tr class="total-row">
                            <th colspan="4">TOTAL</th>
                            <th><?php echo number_format($order['total'], 2); ?> €</th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <?php if ($order['customer_notes']): ?>
            <div class="admin-section">
                <h2>Notes du client</h2>
                <p><?php echo nl2br(htmlspecialchars($order['customer_notes'])); ?></p>
            </div>
            <?php endif; ?>

            <div class="admin-section">
                <h2>Notes internes</h2>
                <form method="POST" action="update_notes.php">
                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                    <textarea name="admin_notes" rows="4" class="admin-notes"><?php echo htmlspecialchars($order['admin_notes'] ?? ''); ?></textarea>
                    <button type="submit" class="btn-primary">Enregistrer les notes</button>
                </form>
            </div>
        </div>
    </div>

    <script>
    function updateOrderStatus(orderId, status) {
        if (confirm('Changer le statut de cette commande ?')) {
            fetch('../../api/order_actions.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=update_status&order_id=${orderId}&status=${status}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Statut mis à jour', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    alert('Erreur: ' + (data.message || 'Échec'));
                    location.reload();
                }
            });
        } else {
            location.reload();
        }
    }
    
    function showNotification(message, type = 'info') {
        const existingNotifications = document.querySelectorAll('.notification');
        existingNotifications.forEach(notif => notif.remove());
        
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <span>${message}</span>
            <button onclick="this.parentElement.remove()">×</button>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.classList.add('show');
        }, 10);
        
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 3000);
    }
    </script>
</body>
</html>
