<!-- pages/order-confirmation.php - Confirmation commande CORRIGÉE -->
<!-- ============================================ -->
<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['order_id'])) {
    header('Location: ../index.php');
    exit;
}

$order_id = (int)$_GET['order_id'];
$stmt = $pdo->prepare("SELECT o.*, u.name, u.email 
                       FROM orders o 
                       JOIN users u ON o.user_id = u.id 
                       WHERE o.id = ? AND o.user_id = ?");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: ../index.php');
    exit;
}

// Récupérer les articles
$stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->execute([$order['id']]);
$items = $stmt->fetchAll();

// Décoder l'adresse de livraison
$shipping_address = json_decode($order['shipping_address'], true);

// Vérifier si le paiement a réussi
$payment_success = ($order['payment_status'] === 'succeeded');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $payment_success ? 'Commande confirmée' : 'Erreur de paiement'; ?> - KIND WOLF</title>
    <meta name="description" content="<?php echo $payment_success ? 'Votre commande a été confirmée avec succès' : 'Une erreur s\'est produite lors du paiement'; ?>">
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <?php include '../header.php'; ?>
    
    <div class="confirmation-container container">
        <div class="confirmation-box">
            <?php if ($payment_success): ?>
                <!-- PAIEMENT RÉUSSI -->
                <div class="confirmation-icon success">✓</div>
                <h1>✅ Commande confirmée !</h1>
                <p class="confirmation-message">
                    Merci pour votre commande <strong><?php echo htmlspecialchars($order['name']); ?></strong> ! 
                    Votre paiement a été traité avec succès.
                </p>
                <p class="confirmation-submessage">
                    Un email de confirmation a été envoyé à <strong><?php echo htmlspecialchars($order['email']); ?></strong>
                </p>
            <?php else: ?>
                <!-- PAIEMENT ÉCHOUÉ -->
                <div class="confirmation-icon error">✕</div>
                <h1>❌ Erreur de paiement</h1>
                <p class="confirmation-message error">
                    Le paiement de votre commande n'a pas pu être traité.
                </p>
                <p class="confirmation-submessage">
                    Votre commande est en attente. Vous pouvez réessayer le paiement depuis votre espace client.
                </p>
            <?php endif; ?>
            
            <div class="order-info-box">
                <h2>📦 Récapitulatif de votre commande</h2>
                <table class="info-table">
                    <tr>
                        <th>Numéro de commande:</th>
                        <td><strong><?php echo htmlspecialchars($order['order_number']); ?></strong></td>
                    </tr>
                    <tr>
                        <th>Date:</th>
                        <td><?php echo date('d/m/Y à H:i', strtotime($order['created_at'])); ?></td>
                    </tr>
                    <tr>
                        <th>Montant total:</th>
                        <td><strong class="total-amount"><?php echo number_format($order['total'], 2); ?> €</strong></td>
                    </tr>
                    <tr>
                        <th>Statut de paiement:</th>
                        <td>
                            <?php if ($payment_success): ?>
                                <span class="payment-status paid">✓ Payé</span>
                            <?php else: ?>
                                <span class="payment-status pending">⏳ En attente</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Méthode de paiement:</th>
                        <td>
                            <?php 
                            $payment_methods = [
                                'card' => '💳 Carte bancaire',
                                'paypal' => '🔵 PayPal',
                                'free' => '🎁 Gratuit (code promo)'
                            ];
                            echo $payment_methods[$order['payment_method']] ?? $order['payment_method']; 
                            ?>
                        </td>
                    </tr>
                </table>
            </div>

            <?php if ($shipping_address): ?>
            <div class="order-info-box">
                <h2>📍 Adresse de livraison</h2>
                <div class="address-display">
                    <p><strong><?php echo htmlspecialchars($shipping_address['recipient_name'] ?? $order['name']); ?></strong></p>
                    <p><?php echo htmlspecialchars($shipping_address['street_address'] ?? ''); ?></p>
                    <?php if (!empty($shipping_address['address_line2'])): ?>
                        <p><?php echo htmlspecialchars($shipping_address['address_line2']); ?></p>
                    <?php endif; ?>
                    <p><?php echo htmlspecialchars($shipping_address['postal_code'] ?? ''); ?> <?php echo htmlspecialchars($shipping_address['city'] ?? ''); ?></p>
                    <?php if (!empty($shipping_address['phone'])): ?>
                        <p>📞 <?php echo htmlspecialchars($shipping_address['phone']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="order-items-summary">
                <h3>Articles commandés</h3>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Produit</th>
                            <th>Quantité</th>
                            <th>Prix unitaire</th>
                            <th>Sous-total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($item['product_name']); ?></strong>
                                <br><small>SKU: <?php echo htmlspecialchars($item['product_sku']); ?></small>
                            </td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td><?php echo number_format($item['price'], 2); ?> €</td>
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
            
            <div class="confirmation-next-steps">
                <?php if ($payment_success): ?>
                    <h3>✨ Que se passe-t-il maintenant ?</h3>
                    <div class="steps-grid">
                        <div class="step-card">
                            <div class="step-icon">📧</div>
                            <h4>1. Confirmation</h4>
                            <p>Un email de confirmation vous a été envoyé</p>
                        </div>
                        <div class="step-card">
                            <div class="step-icon">📦</div>
                            <h4>2. Préparation</h4>
                            <p>Votre commande est en cours de préparation</p>
                        </div>
                        <div class="step-card">
                            <div class="step-icon">🚚</div>
                            <h4>3. Expédition</h4>
                            <p>Vous recevrez un numéro de suivi</p>
                        </div>
                        <div class="step-card">
                            <div class="step-icon">✨</div>
                            <h4>4. Livraison</h4>
                            <p>Profitez de vos produits KIND WOLF !</p>
                        </div>
                    </div>
                <?php else: ?>
                    <h3>💡 Comment régler le problème ?</h3>
                    <div class="error-help">
                        <ul>
                            <li>Vérifiez que vous avez des fonds suffisants</li>
                            <li>Vérifiez les informations de votre carte</li>
                            <li>Essayez une autre méthode de paiement</li>
                            <li>Contactez notre service client si le problème persiste</li>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="confirmation-actions">
                <?php if ($payment_success): ?>
                    <a href="../user/commandes.php" class="btn-primary">
                        📋 Voir mes commandes
                    </a>
                    <a href="boutique.php" class="btn-secondary">
                        🛍️ Continuer mes achats
                    </a>
                <?php else: ?>
                    <a href="../user/commandes.php" class="btn-primary">
                        🔄 Réessayer le paiement
                    </a>
                    <a href="contact.php" class="btn-secondary">
                        📞 Contacter le support
                    </a>
                <?php endif; ?>
                <a href="../index.php" class="btn-outline">
                    🏠 Retour à l'accueil
                </a>
            </div>
            
            <div class="confirmation-help">
                <p>Une question ? <a href="../pages/contact.php">Contactez-nous</a></p>
            </div>
        </div>
    </div>

    <?php include '../footer.php'; ?>
    <script src="../script.js"></script>
</body>
</html>