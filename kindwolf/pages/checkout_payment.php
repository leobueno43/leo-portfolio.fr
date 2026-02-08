<?php
// pages/checkout_payment.php - Page de paiement avec Stripe et PayPal
session_start();
require_once '../config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/auth/login.php?redirect=checkout');
    exit;
}

// Vérifier si le panier existe et n'est pas vide
if (empty($_SESSION['cart'])) {
    header('Location: ' . BASE_URL . '/pages/panier.php');
    exit;
}

// Récupérer l'ID de commande (passé depuis checkout.php)
$order_id = $_SESSION['current_order_id'] ?? null;

if (!$order_id) {
    header('Location: ' . BASE_URL . '/pages/checkout.php');
    exit;
}

// Récupérer les détails de la commande
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: ' . BASE_URL . '/pages/checkout.php');
    exit;
}

// Récupérer les articles de la commande
$stmt = $pdo->prepare("SELECT oi.*, p.name, p.image 
                       FROM order_items oi 
                       JOIN products p ON oi.product_id = p.id 
                       WHERE oi.order_id = ?");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();

// Récupérer les clés API depuis les paramètres (à configurer dans l'admin)
$stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings WHERE setting_key IN ('stripe_public_key', 'stripe_secret_key', 'paypal_client_id', 'paypal_mode')");
$settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

$stripe_public_key = $settings['stripe_public_key'] ?? '';
$paypal_client_id = $settings['paypal_client_id'] ?? '';
$paypal_mode = $settings['paypal_mode'] ?? 'sandbox'; // sandbox ou live
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement - KIND WOLF</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/style.css">
    
    <!-- Stripe JS -->
    <script src="https://js.stripe.com/v3/"></script>
    
    <!-- PayPal JS -->
    <script src="https://www.paypal.com/sdk/js?client-id=<?php echo htmlspecialchars($paypal_client_id); ?>&currency=EUR"></script>
</head>
<body>
    <?php include '../header.php'; ?>
    
    <div class="container" style="padding: 3rem 0;">
        <div style="max-width: 800px; margin: 0 auto;">
            <!-- En-tête -->
            <div style="text-align: center; margin-bottom: 3rem;">
                <h1 style="font-family: var(--font-display); margin-bottom: 1rem;">Paiement sécurisé</h1>
                <p style="color: var(--charcoal); opacity: 0.7;">Commande #<?php echo $order['id']; ?></p>
            </div>
            
            <!-- Récapitulatif commande -->
            <div style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 2rem;">
                <h2 style="font-size: 1.25rem; margin-bottom: 1.5rem;">📦 Récapitulatif</h2>
                
                <?php foreach ($items as $item): ?>
                <div style="display: flex; gap: 1rem; padding: 1rem 0; border-bottom: 1px solid #f0f0f0;">
                    <img src="<?php echo BASE_URL . '/' . htmlspecialchars($item['image']); ?>" 
                         alt="<?php echo htmlspecialchars($item['name']); ?>"
                         style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px;">
                    <div style="flex: 1;">
                        <div style="font-weight: 600;"><?php echo htmlspecialchars($item['name']); ?></div>
                        <div style="color: #666; font-size: 0.9rem;">Quantité : <?php echo $item['quantity']; ?></div>
                    </div>
                    <div style="font-weight: 600;"><?php echo number_format($item['subtotal'], 2); ?> €</div>
                </div>
                <?php endforeach; ?>
                
                <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 2px solid #f0f0f0;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span>Sous-total</span>
                        <span><?php echo number_format($order['subtotal'], 2); ?> €</span>
                    </div>
                    <?php if ($order['discount'] > 0): ?>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; color: var(--deep-red);">
                        <span>Réduction</span>
                        <span>- <?php echo number_format($order['discount'], 2); ?> €</span>
                    </div>
                    <?php endif; ?>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span>Livraison</span>
                        <span><?php echo $order['shipping_cost'] > 0 ? number_format($order['shipping_cost'], 2) . ' €' : 'Gratuite'; ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 1.25rem; font-weight: 700; margin-top: 1rem; color: var(--forest-green);">
                        <span>Total</span>
                        <span><?php echo number_format($order['total'], 2); ?> €</span>
                    </div>
                </div>
            </div>
            
            <!-- Messages -->
            <div id="payment-message" style="display: none; padding: 1rem; border-radius: 5px; margin-bottom: 2rem;"></div>
            
            <!-- Choix du mode de paiement -->
            <div style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                <h2 style="font-size: 1.25rem; margin-bottom: 1.5rem;">💳 Choisissez votre mode de paiement</h2>
                
                <!-- Onglets -->
                <div style="display: flex; gap: 1rem; margin-bottom: 2rem; border-bottom: 2px solid #f0f0f0;">
                    <button class="payment-tab active" data-method="card" 
                            style="padding: 1rem 2rem; border: none; background: none; cursor: pointer; border-bottom: 3px solid var(--forest-green); font-weight: 600; color: var(--forest-green);">
                        💳 Carte bancaire
                    </button>
                    <button class="payment-tab" data-method="paypal" 
                            style="padding: 1rem 2rem; border: none; background: none; cursor: pointer; border-bottom: 3px solid transparent; font-weight: 600; color: #666;">
                        🔵 PayPal
                    </button>
                </div>
                
                <!-- Formulaire Carte bancaire (Stripe) -->
                <div id="card-payment" class="payment-method-content">
                    <form id="payment-form">
                        <div style="margin-bottom: 1.5rem;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Informations de carte</label>
                            <div id="card-element" style="padding: 1rem; border: 1px solid #ddd; border-radius: 5px; background: white;"></div>
                            <div id="card-errors" style="color: var(--deep-red); margin-top: 0.5rem; font-size: 0.9rem;"></div>
                        </div>
                        
                        <button type="submit" id="submit-button" class="btn btn-primary btn-block" style="width: 100%;">
                            <span id="button-text">Payer <?php echo number_format($order['total'], 2); ?> €</span>
                            <span id="spinner" style="display: none;">⏳ Traitement...</span>
                        </button>
                    </form>
                    
                    <div style="text-align: center; margin-top: 1rem; font-size: 0.85rem; color: #666;">
                        🔒 Paiement 100% sécurisé par Stripe
                    </div>
                </div>
                
                <!-- PayPal -->
                <div id="paypal-payment" class="payment-method-content" style="display: none;">
                    <div id="paypal-button-container"></div>
                    
                    <div style="text-align: center; margin-top: 1rem; font-size: 0.85rem; color: #666;">
                        🔒 Paiement sécurisé par PayPal
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../footer.php'; ?>
    
    <script>
    // ============================================
    // STRIPE - Paiement par carte
    // ============================================
    <?php if (!empty($stripe_public_key)): ?>
    const stripe = Stripe('<?php echo $stripe_public_key; ?>');
    const elements = stripe.elements();
    
    // Style du champ carte
    const style = {
        base: {
            fontSize: '16px',
            color: '#1A1A1A',
            '::placeholder': {
                color: '#aab7c4'
            }
        },
        invalid: {
            color: '#8B2C2C',
            iconColor: '#8B2C2C'
        }
    };
    
    const cardElement = elements.create('card', {style: style});
    cardElement.mount('#card-element');
    
    // Gérer les erreurs
    cardElement.on('change', function(event) {
        const displayError = document.getElementById('card-errors');
        if (event.error) {
            displayError.textContent = event.error.message;
        } else {
            displayError.textContent = '';
        }
    });
    
    // Soumettre le paiement
    const form = document.getElementById('payment-form');
    form.addEventListener('submit', async function(event) {
        event.preventDefault();
        
        const submitButton = document.getElementById('submit-button');
        const buttonText = document.getElementById('button-text');
        const spinner = document.getElementById('spinner');
        
        // Désactiver le bouton
        submitButton.disabled = true;
        buttonText.style.display = 'none';
        spinner.style.display = 'inline';
        
        // Créer le PaymentIntent côté serveur
        const response = await fetch('<?php echo BASE_URL; ?>/api/create_payment_intent.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                order_id: <?php echo $order_id; ?>,
                amount: <?php echo $order['total']; ?>
            })
        });
        
        const {clientSecret} = await response.json();
        
        // Confirmer le paiement
        const {error, paymentIntent} = await stripe.confirmCardPayment(clientSecret, {
            payment_method: {
                card: cardElement
            }
        });
        
        if (error) {
            showMessage(error.message, 'error');
            submitButton.disabled = false;
            buttonText.style.display = 'inline';
            spinner.style.display = 'none';
        } else {
            if (paymentIntent.status === 'succeeded') {
                // Paiement réussi
                window.location.href = '<?php echo BASE_URL; ?>/api/payment_success.php?order_id=<?php echo $order_id; ?>&method=stripe&payment_id=' + paymentIntent.id;
            }
        }
    });
    <?php endif; ?>
    
    // ============================================
    // PAYPAL
    // ============================================
    <?php if (!empty($paypal_client_id)): ?>
    paypal.Buttons({
        createOrder: function(data, actions) {
            return actions.order.create({
                purchase_units: [{
                    amount: {
                        value: '<?php echo number_format($order['total'], 2, '.', ''); ?>'
                    },
                    description: 'Commande KIND WOLF #<?php echo $order_id; ?>'
                }]
            });
        },
        onApprove: function(data, actions) {
            return actions.order.capture().then(function(details) {
                // Paiement réussi
                window.location.href = '<?php echo BASE_URL; ?>/api/payment_success.php?order_id=<?php echo $order_id; ?>&method=paypal&payment_id=' + details.id;
            });
        },
        onError: function(err) {
            showMessage('Erreur PayPal : ' + err, 'error');
        }
    }).render('#paypal-button-container');
    <?php endif; ?>
    
    // ============================================
    // GESTION DES ONGLETS
    // ============================================
    document.querySelectorAll('.payment-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            const method = this.dataset.method;
            
            // Changer les onglets actifs
            document.querySelectorAll('.payment-tab').forEach(t => {
                t.classList.remove('active');
                t.style.borderBottom = '3px solid transparent';
                t.style.color = '#666';
            });
            this.classList.add('active');
            this.style.borderBottom = '3px solid var(--forest-green)';
            this.style.color = 'var(--forest-green)';
            
            // Afficher le bon contenu
            document.querySelectorAll('.payment-method-content').forEach(content => {
                content.style.display = 'none';
            });
            
            if (method === 'card') {
                document.getElementById('card-payment').style.display = 'block';
            } else if (method === 'paypal') {
                document.getElementById('paypal-payment').style.display = 'block';
            }
        });
    });
    
    // ============================================
    // AFFICHER LES MESSAGES
    // ============================================
    function showMessage(text, type) {
        const messageDiv = document.getElementById('payment-message');
        messageDiv.textContent = text;
        messageDiv.style.display = 'block';
        
        if (type === 'error') {
            messageDiv.style.background = '#FEE2E2';
            messageDiv.style.color = '#991B1B';
            messageDiv.style.border = '1px solid #FCA5A5';
        } else {
            messageDiv.style.background = '#D1FAE5';
            messageDiv.style.color = '#065F46';
            messageDiv.style.border = '1px solid #6EE7B7';
        }
        
        // Scroll vers le message
        messageDiv.scrollIntoView({behavior: 'smooth', block: 'center'});
    }
    </script>
</body>
</html>