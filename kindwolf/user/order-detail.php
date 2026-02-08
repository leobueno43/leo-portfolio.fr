<?php
// user/order-detail.php - Détails d'une commande
// ============================================

session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

$order_id = (int)($_GET['id'] ?? 0);

// Récupérer la commande (vérifier qu'elle appartient bien à l'utilisateur)
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: ' . BASE_URL . '/user/commandes.php');
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
    <title>Commande #<?php echo htmlspecialchars($order['order_number']); ?> - KIND WOLF</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/style.css">
</head>
<body>
    <?php include '../header.php'; ?>
    
    <div class="page-header">
        <h1>Commande #<?php echo htmlspecialchars($order['order_number']); ?></h1>
        <p>Détails de votre commande</p>
    </div>

    <div class="order-detail-container container">
        <div class="back-link">
            <a href="<?php echo BASE_URL; ?>/user/commandes.php">← Retour à mes commandes</a>
        </div>
        
        <div class="order-detail-grid">
            <div class="order-detail-main">
                <!-- Statut de la commande -->
                <div class="detail-section">
                    <h2>📊 Statut de la commande</h2>
                    <div class="status-timeline">
                        <div class="status-step <?php echo in_array($order['status'], ['pending', 'processing', 'shipped', 'completed']) ? 'active' : ''; ?>">
                            <div class="step-icon">📝</div>
                            <div class="step-label">Confirmée</div>
                        </div>
                        <div class="status-step <?php echo in_array($order['status'], ['processing', 'shipped', 'completed']) ? 'active' : ''; ?>">
                            <div class="step-icon">⚙️</div>
                            <div class="step-label">En préparation</div>
                        </div>
                        <div class="status-step <?php echo in_array($order['status'], ['shipped', 'completed']) ? 'active' : ''; ?>">
                            <div class="step-icon">🚚</div>
                            <div class="step-label">Expédiée</div>
                        </div>
                        <div class="status-step <?php echo $order['status'] === 'completed' ? 'active' : ''; ?>">
                            <div class="step-icon">✅</div>
                            <div class="step-label">Livrée</div>
                        </div>
                    </div>
                    
                    <?php if ($order['tracking_number']): ?>
                    <div class="tracking-info">
                        <strong>📦 Numéro de suivi :</strong> <?php echo htmlspecialchars($order['tracking_number']); ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Articles commandés -->
                <div class="detail-section">
                    <h2>📦 Articles commandés</h2>
                    <table class="items-table">
                        <thead>
                            <tr>
                                <th>Produit</th>
                                <th>Prix unitaire</th>
                                <th>Quantité</th>
                                <th>Sous-total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($item['product_name']); ?></strong><br>
                                    <small>SKU: <?php echo htmlspecialchars($item['product_sku']); ?></small>
                                </td>
                                <td><?php echo number_format($item['price'], 2); ?> €</td>
                                <td>×<?php echo $item['quantity']; ?></td>
                                <td><strong><?php echo number_format($item['subtotal'], 2); ?> €</strong></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3">Sous-total</th>
                                <th><?php echo number_format($order['subtotal'], 2); ?> €</th>
                            </tr>
                            <tr>
                                <th colspan="3">Livraison</th>
                                <th><?php echo $order['shipping_cost'] > 0 ? number_format($order['shipping_cost'], 2) . ' €' : 'Gratuite'; ?></th>
                            </tr>
                            <?php if ($order['discount_amount'] > 0): ?>
                            <tr class="discount-row">
                                <th colspan="3">
                                    Réduction
                                    <?php if ($order['promo_code']): ?>
                                        (<?php echo htmlspecialchars($order['promo_code']); ?>)
                                    <?php endif; ?>
                                </th>
                                <th>-<?php echo number_format($order['discount_amount'], 2); ?> €</th>
                            </tr>
                            <?php endif; ?>
                            <tr class="total-row">
                                <th colspan="3">TOTAL</th>
                                <th><?php echo number_format($order['total'], 2); ?> €</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <?php if ($order['customer_notes']): ?>
                <div class="detail-section">
                    <h2>📝 Vos notes</h2>
                    <p><?php echo nl2br(htmlspecialchars($order['customer_notes'])); ?></p>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="order-detail-sidebar">
                <!-- Informations générales -->
                <div class="detail-section">
                    <h3>ℹ️ Informations</h3>
                    <div class="info-list">
                        <div class="info-item">
                            <span class="info-label">Date</span>
                            <span class="info-value"><?php echo date('d/m/Y à H:i', strtotime($order['created_at'])); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Statut</span>
                            <span class="status status-<?php echo $order['status']; ?>">
                                <?php 
                                $statuses = [
                                    'pending' => 'En attente',
                                    'processing' => 'En cours',
                                    'shipped' => 'Expédiée',
                                    'completed' => 'Terminée',
                                    'cancelled' => 'Annulée'
                                ];
                                echo $statuses[$order['status']] ?? ucfirst($order['status']); 
                                ?>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Paiement</span>
                            <span class="info-value">
                                <?php 
                                $methods = [
                                    'card' => 'Carte bancaire',
                                    'paypal' => 'PayPal',
                                    'bank_transfer' => 'Virement'
                                ];
                                echo $methods[$order['payment_method']] ?? $order['payment_method']; 
                                ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Adresse de livraison -->
                <?php if ($shipping_address): ?>
                <div class="detail-section">
                    <h3>📍 Livraison</h3>
                    <address class="shipping-address">
                        <strong><?php echo htmlspecialchars($shipping_address['firstname'] . ' ' . $shipping_address['lastname']); ?></strong><br>
                        <?php if (!empty($shipping_address['company'])): ?>
                            <?php echo htmlspecialchars($shipping_address['company']); ?><br>
                        <?php endif; ?>
                        <?php echo htmlspecialchars($shipping_address['address_line1']); ?><br>
                        <?php if (!empty($shipping_address['address_line2'])): ?>
                            <?php echo htmlspecialchars($shipping_address['address_line2']); ?><br>
                        <?php endif; ?>
                        <?php echo htmlspecialchars($shipping_address['postal_code'] . ' ' . $shipping_address['city']); ?><br>
                        <?php echo htmlspecialchars($shipping_address['country']); ?><br>
                        📞 <?php echo htmlspecialchars($shipping_address['phone']); ?>
                    </address>
                </div>
                <?php endif; ?>
                
                <!-- Actions -->
                <div class="detail-section">
                    <h3>⚡ Actions</h3>
                    <div class="action-buttons">
                        <?php if (in_array($order['status'], ['completed', 'shipped'])): ?>
                            <a href="<?php echo BASE_URL; ?>/user/order-invoice.php?id=<?php echo $order['id']; ?>" 
                               class="btn-primary btn-block" target="_blank">
                                📄 Télécharger la facture
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($order['status'] === 'pending'): ?>
                            <button onclick="cancelOrder(<?php echo $order['id']; ?>)" 
                                    class="btn-outline btn-block">
                                ❌ Annuler la commande
                            </button>
                        <?php endif; ?>
                        
                        <a href="<?php echo BASE_URL; ?>/pages/contact.php" class="btn-outline btn-block">
                            📧 Contacter le support
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../footer.php'; ?>
    <script src="<?php echo BASE_URL; ?>/script.js"></script>
</body>
</html>