<!-- pages/panier.php - Page panier CORRIGÉE -->
<!-- ============================================ -->
<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$cart_items = [];
$subtotal = 0;

if (!empty($_SESSION['cart'])) {
    $ids = array_keys($_SESSION['cart']);
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders) AND active = 1");
    $stmt->execute($ids);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($products as $product) {
        $quantity = $_SESSION['cart'][$product['id']];
        $item_subtotal = $product['price'] * $quantity;
        $cart_items[] = [
            'product' => $product,
            'quantity' => $quantity,
            'subtotal' => $item_subtotal
        ];
        $subtotal += $item_subtotal;
    }
}

// Récupérer les paramètres de livraison
$shipping_threshold = 50.00;
$shipping_cost = 5.99;

$settings_stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings 
                               WHERE setting_key IN ('free_shipping_threshold', 'shipping_cost')");
$settings = $settings_stmt->fetchAll(PDO::FETCH_KEY_PAIR);

if (isset($settings['free_shipping_threshold'])) {
    $shipping_threshold = (float)$settings['free_shipping_threshold'];
}
if (isset($settings['shipping_cost'])) {
    $shipping_cost = (float)$settings['shipping_cost'];
}

// Calculer les frais de port
$final_shipping_cost = $subtotal >= $shipping_threshold ? 0 : $shipping_cost;

// Gérer le code promo
$promo_discount = 0;
$promo_code = '';
if (isset($_SESSION['promo_code'])) {
    $stmt = $pdo->prepare("SELECT * FROM promo_codes 
                           WHERE code = ? AND active = 1 
                           AND (expires_at IS NULL OR expires_at > NOW())");
    $stmt->execute([$_SESSION['promo_code']]);
    $promo = $stmt->fetch();
    
    if ($promo && $subtotal >= $promo['minimum_amount']) {
        $promo_code = $promo['code'];
        
        if ($promo['discount_type'] === 'percentage') {
            $promo_discount = ($subtotal * $promo['discount_percent']) / 100;
            if ($promo['maximum_discount'] && $promo_discount > $promo['maximum_discount']) {
                $promo_discount = $promo['maximum_discount'];
            }
        } else {
            $promo_discount = $promo['discount_amount'];
        }
    }
}

$total = $subtotal + $final_shipping_cost - $promo_discount;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Panier - KIND WOLF</title>
    <meta name="description" content="Panier d'achat KIND WOLF">
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <?php include '../header.php'; ?>
    
    <div class="page-header">
        <h1>Mon Panier</h1>
        <p><?php echo count($cart_items); ?> article(s)</p>
    </div>

    <div class="cart-container container">
        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
                <div class="empty-cart-icon">🛒</div>
                <h2>Votre panier est vide</h2>
                <p>Découvrez notre collection et ajoutez des produits à votre panier</p>
                <a href="boutique.php" class="btn-primary">Découvrir la boutique</a>
            </div>
        <?php else: ?>
            <div class="cart-items">
                <h2>Articles (<?php echo count($cart_items); ?>)</h2>
                <?php foreach ($cart_items as $item): ?>
                <div class="cart-item" data-product-id="<?php echo $item['product']['id']; ?>">
                    <img src="<?php echo BASE_URL . '/' . htmlspecialchars($item['product']['image']); ?>" 
                         alt="<?php echo htmlspecialchars($item['product']['name']); ?>"
                         onerror="this.src='<?php echo BASE_URL; ?>/images/products/default.jpg'">
                    
                    <div class="cart-item-info">
                        <h3>
                            <a href="produit.php?id=<?php echo $item['product']['id']; ?>">
                                <?php echo htmlspecialchars($item['product']['name']); ?>
                            </a>
                        </h3>
                        <p class="cart-item-category"><?php echo htmlspecialchars($item['product']['category']); ?></p>
                        <p class="cart-item-price"><?php echo number_format($item['product']['price'], 2); ?> € / unité</p>
                        <?php if ($item['product']['stock'] < $item['quantity']): ?>
                            <p class="stock-warning">⚠️ Stock limité: <?php echo $item['product']['stock']; ?> disponible(s)</p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="cart-item-quantity">
                        <button onclick="updateCart(<?php echo $item['product']['id']; ?>, -1)" 
                                <?php echo $item['quantity'] <= 1 ? 'disabled' : ''; ?>>−</button>
                        <span><?php echo $item['quantity']; ?></span>
                        <button onclick="updateCart(<?php echo $item['product']['id']; ?>, 1)"
                                <?php echo $item['quantity'] >= $item['product']['stock'] ? 'disabled' : ''; ?>>+</button>
                    </div>
                    
                    <div class="cart-item-subtotal">
                        <strong><?php echo number_format($item['subtotal'], 2); ?> €</strong>
                    </div>
                    
                    <button class="cart-item-remove" 
                            onclick="removeFromCart(<?php echo $item['product']['id']; ?>)" 
                            title="Supprimer">×</button>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="cart-summary">
                <h2>Récapitulatif</h2>
                
                <!-- Section code promo -->
                <div class="promo-code-section">
                    <h3>Code promo</h3>
                    <?php if ($promo_code): ?>
                        <div class="promo-applied">
                            <span>Code: <strong><?php echo htmlspecialchars($promo_code); ?></strong></span>
                            <button onclick="removePromoCode()" class="btn-remove-promo">×</button>
                        </div>
                    <?php else: ?>
                        <div id="promoForm" class="promo-form">
                            <input type="text" id="promoCodeInput" placeholder="Entrez votre code" maxlength="50">
                            <button onclick="applyPromoCode()" class="btn-secondary">Appliquer</button>
                        </div>
                    <?php endif; ?>
                    <div id="promoMessage" class="promo-message"></div>
                </div>
                
                <div class="cart-summary-line">
                    <span>Sous-total</span>
                    <span><?php echo number_format($subtotal, 2); ?> €</span>
                </div>
                
                <?php if ($promo_discount > 0): ?>
                <div class="cart-summary-line">
                    <span>Réduction (<?php echo htmlspecialchars($promo_code); ?>)</span>
                    <span class="discount-text">-<?php echo number_format($promo_discount, 2); ?> €</span>
                </div>
                <?php endif; ?>
                
                <div class="cart-summary-line">
                    <span>Livraison</span>
                    <?php if ($final_shipping_cost == 0): ?>
                        <span class="free-shipping">Gratuite ✓</span>
                    <?php else: ?>
                        <span><?php echo number_format($final_shipping_cost, 2); ?> €</span>
                    <?php endif; ?>
                </div>
                
                <?php if ($subtotal < $shipping_threshold && $final_shipping_cost > 0): ?>
                <div class="shipping-progress">
                    <p>Plus que <strong><?php echo number_format($shipping_threshold - $subtotal, 2); ?> €</strong> 
                       pour la livraison gratuite !</p>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo min(100, ($subtotal / $shipping_threshold) * 100); ?>%"></div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="cart-summary-total">
                    <span>Total</span>
                    <span><?php echo number_format($total, 2); ?> €</span>
                </div>
                
                <a href="checkout.php" class="btn-primary btn-block">
                    Procéder au paiement
                </a>
                
                <a href="boutique.php" class="btn-outline btn-block">
                    Continuer mes achats
                </a>
                
                <div class="secure-payment">
                    <p>🔒 Paiement 100% sécurisé</p>
                    <div class="payment-icons">
                        <span>💳</span>
                        <span>🅿️</span>
                        <span>🏦</span>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php include '../footer.php'; ?>
    <script src="../script.js"></script>
</body>
</html>