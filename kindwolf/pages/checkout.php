<?php
// pages/checkout.php - Processus de commande avec PAIEMENT INTÉGRÉ
session_start();
require_once '../config.php';

// Vérifier connexion
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = 'pages/checkout.php';
    header('Location: ../auth/login.php');
    exit;
}

// Vérifier panier non vide
if (empty($_SESSION['cart'])) {
    header('Location: panier.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Calculer le total
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
        
        if ($quantity > $product['stock']) {
            $_SESSION['cart'][$product['id']] = $product['stock'];
            $quantity = $product['stock'];
        }
        
        $cart_items[] = [
            'product' => $product,
            'quantity' => $quantity,
            'subtotal' => $product['price'] * $quantity
        ];
        $subtotal += $product['price'] * $quantity;
    }
}

// Récupérer les adresses
$stmt = $pdo->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC");
$stmt->execute([$user_id]);
$addresses = $stmt->fetchAll();

// Paramètres de livraison
$shipping_cost = 5.99;
$free_shipping_threshold = 50.00;

$settings = $pdo->query("SELECT setting_key, setting_value FROM site_settings")
                ->fetchAll(PDO::FETCH_KEY_PAIR);

if (isset($settings['free_shipping_threshold'])) {
    $free_shipping_threshold = (float)$settings['free_shipping_threshold'];
}
if (isset($settings['shipping_cost'])) {
    $shipping_cost = (float)$settings['shipping_cost'];
}

$final_shipping_cost = $subtotal >= $free_shipping_threshold ? 0 : $shipping_cost;

// Code promo
$promo_discount = 0;
$promo_code = '';
$promo_id = null;

if (isset($_SESSION['promo_code'])) {
    $stmt = $pdo->prepare("SELECT * FROM promo_codes 
                           WHERE code = ? AND active = 1 
                           AND (expires_at IS NULL OR expires_at > NOW())");
    $stmt->execute([$_SESSION['promo_code']]);
    $promo = $stmt->fetch();
    
    if ($promo && $subtotal >= $promo['minimum_amount']) {
        $promo_code = $promo['code'];
        $promo_id = $promo['id'];
        
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

// Récupérer clés paiement
$stripe_public_key = $settings['stripe_public_key'] ?? '';
$stripe_enabled = ($settings['stripe_enabled'] ?? '0') === '1';
$paypal_client_id = $settings['paypal_client_id'] ?? '';
$paypal_enabled = ($settings['paypal_enabled'] ?? '0') === '1';

$success = '';
$error = '';
$order_created = null;

// ÉTAPE 1 : Créer la commande (avant le paiement)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_order'])) {
    $shipping_address_id = (int)($_POST['shipping_address'] ?? 0);
    $customer_notes = $_POST['customer_notes'] ?? '';
    $accept_cgv = isset($_POST['accept_cgv']);
    $newsletter_subscribe = isset($_POST['newsletter_subscribe']);
    
    if (!$accept_cgv) {
        $error = 'Vous devez accepter les Conditions Générales de Vente pour continuer';
    } elseif ($shipping_address_id == 0) {
        $error = 'Veuillez sélectionner une adresse de livraison';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM addresses WHERE id = ? AND user_id = ?");
        $stmt->execute([$shipping_address_id, $user_id]);
        $address = $stmt->fetch();
        
        if (!$address) {
            $error = 'Adresse invalide';
        } else {
            try {
                $pdo->beginTransaction();
                
                $shipping_address_json = json_encode($address);
                $order_number = 'KW-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
                
                // Insérer la commande
                $stmt = $pdo->prepare("INSERT INTO orders (
                    user_id, order_number, total, subtotal, shipping_cost, 
                    discount_amount, promo_code, status, payment_method, 
                    payment_status, shipping_address, customer_notes
                ) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', 'card', 'pending', ?, ?)");
                
                $stmt->execute([
                    $user_id, $order_number, $total, $subtotal,
                    $final_shipping_cost, $promo_discount, $promo_code ?: null,
                    $shipping_address_json, $customer_notes
                ]);
                
                $order_id = $pdo->lastInsertId();
                
                // Insérer les articles
                $stmt = $pdo->prepare("INSERT INTO order_items (
                    order_id, product_id, product_name, product_sku, 
                    quantity, price, subtotal
                ) VALUES (?, ?, ?, ?, ?, ?, ?)");
                
                foreach ($cart_items as $item) {
                    $stmt->execute([
                        $order_id,
                        $item['product']['id'],
                        $item['product']['name'],
                        $item['product']['sku'],
                        $item['quantity'],
                        $item['product']['price'],
                        $item['subtotal']
                    ]);
                    
                    // Le stock sera décrémenté uniquement après paiement réussi
                }
                
                if ($promo_id) {
                    $pdo->prepare("INSERT INTO promo_usage (promo_id, user_id, order_id, discount_amount) 
                                   VALUES (?, ?, ?, ?)")
                        ->execute([$promo_id, $user_id, $order_id, $promo_discount]);
                    
                    $pdo->prepare("UPDATE promo_codes SET usage_count = usage_count + 1 WHERE id = ?")
                        ->execute([$promo_id]);
                }
                
                $pdo->commit();
                
                // Gérer l'inscription à la newsletter
                if ($newsletter_subscribe) {
                    // Récupérer l'email de l'utilisateur
                    $stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
                    $stmt->execute([$user_id]);
                    $user_email = $stmt->fetchColumn();
                    
                    if ($user_email) {
                        // Vérifier si déjà inscrit
                        $stmt = $pdo->prepare("SELECT id FROM newsletter_subscribers WHERE email = ?");
                        $stmt->execute([$user_email]);
                        
                        if (!$stmt->fetch()) {
                            // Inscrire à la newsletter
                            $token = bin2hex(random_bytes(16));
                            $pdo->prepare("INSERT INTO newsletter_subscribers (email, token, subscribed_at) VALUES (?, ?, NOW())")
                                ->execute([$user_email, $token]);
                        }
                    }
                }
                
                // Si le total est 0€, valider directement la commande
                if ($total <= 0) {
                    // Décrémenter le stock pour les commandes gratuites
                    foreach ($cart_items as $item) {
                        $stock_stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                        $stock_stmt->execute([$item['quantity'], $item['product']['id']]);
                    }
                    
                    $pdo->prepare("UPDATE orders SET payment_status = 'succeeded', payment_method = 'free', status = 'confirmed' WHERE id = ?")
                        ->execute([$order_id]);
                    
                    // Nettoyer la session
                    unset($_SESSION['cart']);
                    unset($_SESSION['promo_code']);
                    
                    // Rediriger vers la confirmation
                    header("Location: " . BASE_URL . "/pages/order-confirmation.php?order_id=" . $order_id);
                    exit;
                } else {
                    // Sauvegarder l'ID de commande pour le paiement
                    $_SESSION['pending_order_id'] = $order_id;
                    $order_created = $order_id;
                }
                
            } catch (PDOException $e) {
                $pdo->rollBack();
                $error = 'Erreur lors de la création de la commande: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finaliser ma commande - KIND WOLF</title>
    <link rel="stylesheet" href="../style.css">
    
    <!-- Stripe JS -->
    <?php if ($stripe_enabled && !empty($stripe_public_key)): ?>
    <script src="https://js.stripe.com/v3/"></script>
    <?php endif; ?>
    
    <!-- PayPal JS -->
    <?php if ($paypal_enabled && !empty($paypal_client_id)): ?>
    <script src="https://www.paypal.com/sdk/js?client-id=<?php echo htmlspecialchars($paypal_client_id); ?>&currency=EUR"></script>
    <?php endif; ?>
</head>
<body>
    <?php include '../header.php'; ?>
    
    <div class="page-header">
        <h1>Finaliser ma commande</h1>
        <div class="checkout-steps">
            <span class="step completed">1. Panier</span>
            <span class="step <?php echo $order_created ? 'completed' : 'active'; ?>">2. Livraison</span>
            <span class="step <?php echo $order_created ? 'active' : ''; ?>">3. Paiement</span>
        </div>
    </div>

    <div class="container" style="padding-top: 2rem;>
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if (!$order_created): ?>
        <!-- ÉTAPE 1 : Adresse et livraison -->
        <form method="POST" class="checkout-form">
            <div class="checkout-main">
                <section class="checkout-section">
                    <h2>📍 Adresse de livraison</h2>
                    <?php if (empty($addresses)): ?>
                        <div class="no-address">
                            <p>Vous n'avez pas d'adresse enregistrée.</p>
                            <a href="../user/adresses.php?add=1&redirect=checkout" class="btn-primary">
                                + Ajouter une adresse
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="address-list">
                            <?php foreach ($addresses as $addr): ?>
                            <label class="address-card <?php echo $addr['is_default'] ? 'default' : ''; ?>">
                                <input type="radio" name="shipping_address" value="<?php echo $addr['id']; ?>" 
                                       <?php echo $addr['is_default'] ? 'checked' : ''; ?> required>
                                <div class="address-details">
                                    <strong>
                                        <?php echo htmlspecialchars($addr['firstname'] . ' ' . $addr['lastname']); ?>
                                        <?php if ($addr['is_default']): ?>
                                            <span class="default-badge">Par défaut</span>
                                        <?php endif; ?>
                                    </strong>
                                    <p><?php echo htmlspecialchars($addr['address_line1']); ?></p>
                                    <p><?php echo htmlspecialchars($addr['postal_code'] . ' ' . $addr['city']); ?></p>
                                </div>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

                <section class="checkout-section">
                    <h2>📝 Notes (optionnel)</h2>
                    <textarea name="customer_notes" rows="3" placeholder="Instructions de livraison..."></textarea>
                </section>
            </div>

            <aside class="checkout-sidebar">
                <div class="order-summary">
                    <h2>Récapitulatif</h2>
                    
                    <div class="summary-line">
                        <span>Sous-total</span>
                        <span><?php echo number_format($subtotal, 2); ?> €</span>
                    </div>
                    
                    <div class="summary-line">
                        <span>Livraison</span>
                        <?php if ($final_shipping_cost == 0): ?>
                            <span class="free-shipping">Gratuite</span>
                        <?php else: ?>
                            <span><?php echo number_format($final_shipping_cost, 2); ?> €</span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($promo_discount > 0): ?>
                    <div class="summary-line discount">
                        <span>Réduction</span>
                        <span>-<?php echo number_format($promo_discount, 2); ?> €</span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="summary-total">
                        <span>Total</span>
                        <span><?php echo number_format($total, 2); ?> €</span>
                    </div>
                    
                    <!-- Cases à cocher -->
                    <div class="checkout-checkboxes">
                        <label class="checkbox-label required">
                            <input type="checkbox" name="accept_cgv" id="accept_cgv" required>
                            <span>J'accepte les <a href="<?php echo BASE_URL; ?>/pages/cgv.php" target="_blank">Conditions Générales de Vente</a> et les <a href="<?php echo BASE_URL; ?>/pages/cgu.php" target="_blank">Conditions Générales d'Utilisation</a> *</span>
                        </label>
                        
                        <label class="checkbox-label">
                            <input type="checkbox" name="newsletter_subscribe" id="newsletter_subscribe">
                            <span>Je souhaite recevoir la newsletter et les offres promotionnelles</span>
                        </label>
                    </div>
                    
                    <button type="submit" name="create_order" class="btn-primary btn-block">
                        Continuer vers le paiement →
                    </button>
                </div>
            </aside>
        </form>
        
        <?php else: ?>
        <!-- ÉTAPE 2 : Paiement -->
        <div class="payment-section">
            <h2 style="text-align: center; margin-bottom: 2rem;">💳 Choisissez votre mode de paiement</h2>
            
            <div id="payment-message" style="display: none; padding: 1rem; border-radius: 5px; margin-bottom: 2rem;"></div>
            
            <div style="max-width: 600px; margin: 0 auto;">
                <!-- Onglets -->
                <div style="display: flex; gap: 1rem; margin-bottom: 2rem; border-bottom: 2px solid #f0f0f0;">
                    <?php if ($stripe_enabled): ?>
                    <button class="payment-tab active" data-method="card" type="button">
                        💳 Carte bancaire
                    </button>
                    <?php endif; ?>
                    <?php if ($paypal_enabled): ?>
                    <button class="payment-tab" data-method="paypal" type="button">
                        🔵 PayPal
                    </button>
                    <?php endif; ?>
                </div>
                
                <!-- Stripe -->
                <?php if ($stripe_enabled): ?>
                <div id="card-payment" class="payment-method-content">
                    <form id="payment-form">
                        <div id="card-element" style="padding: 1rem; border: 1px solid #ddd; border-radius: 5px; margin-bottom: 1rem;"></div>
                        <div id="card-errors" style="color: var(--deep-red); margin-bottom: 1rem;"></div>
                        <button type="submit" id="submit-button" class="btn-primary btn-block">
                            Payer <?php echo number_format($total, 2); ?> €
                        </button>
                    </form>
                </div>
                <?php endif; ?>
                
                <!-- PayPal -->
                <?php if ($paypal_enabled): ?>
                <div id="paypal-payment" class="payment-method-content" style="display: none;">
                    <div id="paypal-button-container"></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php include '../footer.php'; ?>
    
    <?php if ($order_created): ?>
    <script>
    const orderId = <?php echo $order_created; ?>;
    const totalAmount = <?php echo $total; ?>;
    
    // STRIPE
    <?php if ($stripe_enabled && !empty($stripe_public_key)): ?>
    const stripe = Stripe('<?php echo $stripe_public_key; ?>');
    const elements = stripe.elements();
    const cardElement = elements.create('card');
    cardElement.mount('#card-element');
    
    document.getElementById('payment-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        document.getElementById('submit-button').disabled = true;
        document.getElementById('submit-button').textContent = 'Traitement...';
        
        try {
            const response = await fetch('../api/create_payment_intent.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({order_id: orderId, amount: totalAmount})
            });
            
            const {clientSecret, error} = await response.json();
            
            if (error) {
                throw new Error(error);
            }
            
            const {error: stripeError, paymentIntent} = await stripe.confirmCardPayment(clientSecret, {
                payment_method: {card: cardElement}
            });
            
            if (stripeError) {
                throw new Error(stripeError.message);
            }
            
            if (paymentIntent.status === 'succeeded') {
                window.location.href = `../api/payment_success.php?order_id=${orderId}&method=stripe&payment_id=${paymentIntent.id}`;
            }
        } catch (err) {
            document.getElementById('submit-button').disabled = false;
            document.getElementById('submit-button').textContent = 'Payer <?php echo number_format($total, 2); ?> €';
            showMessage(err.message, 'error');
        }
    });
    <?php endif; ?>
    
    // PAYPAL
    <?php if ($paypal_enabled && !empty($paypal_client_id)): ?>
    paypal.Buttons({
        createOrder: (data, actions) => {
            return actions.order.create({
                purchase_units: [{
                    amount: {value: '<?php echo number_format($total, 2, '.', ''); ?>'}
                }]
            });
        },
        onApprove: (data, actions) => {
            return actions.order.capture().then(details => {
                window.location.href = `../api/payment_success.php?order_id=${orderId}&method=paypal&payment_id=${details.id}`;
            });
        }
    }).render('#paypal-button-container');
    <?php endif; ?>
    
    // Onglets
    document.querySelectorAll('.payment-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.payment-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            document.querySelectorAll('.payment-method-content').forEach(c => c.style.display = 'none');
            document.getElementById(this.dataset.method + '-payment').style.display = 'block';
        });
    });
    
    function showMessage(text, type) {
        const msg = document.getElementById('payment-message');
        msg.textContent = text;
        msg.style.display = 'block';
        msg.style.background = type === 'error' ? '#FEE2E2' : '#D1FAE5';
        msg.style.color = type === 'error' ? '#991B1B' : '#065F46';
    }
    </script>
    <?php endif; ?>
    
    <style>
    .payment-tab {
        padding: 1rem 2rem;
        border: none;
        background: none;
        cursor: pointer;
        border-bottom: 3px solid transparent;
        font-weight: 600;
        color: #666;
    }
    .payment-tab.active {
        border-bottom-color: var(--forest-green);
        color: var(--forest-green);
    }
    </style>
</body>
</html>