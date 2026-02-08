<?php
// admin/settings/payment.php - Configuration des paiements
session_start();
require_once '../../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

$success = '';
$error = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings = [
        'stripe_public_key' => trim($_POST['stripe_public_key'] ?? ''),
        'stripe_secret_key' => trim($_POST['stripe_secret_key'] ?? ''),
        'stripe_enabled' => isset($_POST['stripe_enabled']) ? '1' : '0',
        'paypal_client_id' => trim($_POST['paypal_client_id'] ?? ''),
        'paypal_secret' => trim($_POST['paypal_secret'] ?? ''),
        'paypal_mode' => $_POST['paypal_mode'] ?? 'sandbox',
        'paypal_enabled' => isset($_POST['paypal_enabled']) ? '1' : '0'
    ];
    
    try {
        foreach ($settings as $key => $value) {
            $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value, updated_at) 
                                   VALUES (?, ?, NOW()) 
                                   ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = NOW()");
            $stmt->execute([$key, $value, $value]);
        }
        
        $success = 'Configuration enregistrée avec succès !';
    } catch (PDOException $e) {
        $error = 'Erreur lors de l\'enregistrement : ' . $e->getMessage();
    }
}

// Récupérer les paramètres actuels
$stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings 
                     WHERE setting_key IN ('stripe_public_key', 'stripe_secret_key', 'stripe_enabled', 
                                           'paypal_client_id', 'paypal_secret', 'paypal_mode', 'paypal_enabled')");
$current_settings = [];
while ($row = $stmt->fetch()) {
    $current_settings[$row['setting_key']] = $row['setting_value'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuration des paiements - Admin KIND WOLF</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/style.css">
</head>
<body class="admin-body">
    <div class="admin-container">
        <?php include '../admin_sidebar.php'; ?>
        
        <main class="admin-main">
            <div class="admin-header">
                <h1>💳 Configuration des paiements</h1>
                <div style="display: flex; gap: 1rem;">
                    <button type="submit" form="payment-form" class="btn-primary">💾 Enregistrer</button>
                    <a href="site.php" class="btn-secondary">⚙️ Paramètres</a>
                </div>
            </div>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form id="payment-form" method="POST">
                
                <!-- SECTION 1: STRIPE -->
                <div class="settings-card">
                    <div class="settings-card-header">
                        <h2>💳 Stripe</h2>
                        <p>Paiement par carte bancaire</p>
                    </div>
                    <div class="settings-card-body">
                        <div class="form-checkboxes" style="margin-bottom: 1.5rem;">
                            <label>
                                <input type="checkbox" name="stripe_enabled" 
                                       <?php echo ($current_settings['stripe_enabled'] ?? '0') === '1' ? 'checked' : ''; ?>>
                                <span>Activer les paiements Stripe</span>
                            </label>
                        </div>
                        
                        <div class="smtp-info-box">
                            <strong>📝 Comment obtenir vos clés Stripe :</strong>
                            <ol style="margin: 0.5rem 0 0 1.5rem; line-height: 1.8;">
                                <li>Créez un compte sur <a href="https://dashboard.stripe.com/register" target="_blank">stripe.com</a></li>
                                <li>Allez dans <strong>Développeurs → Clés API</strong></li>
                                <li>Copiez vos clés publique et secrète</li>
                            </ol>
                        </div>
                        
                        <div class="form-group">
                            <label for="stripe_public_key">Clé publique (Publishable key)</label>
                            <input type="text" id="stripe_public_key" name="stripe_public_key" 
                                   value="<?php echo htmlspecialchars($current_settings['stripe_public_key'] ?? ''); ?>"
                                   placeholder="pk_test_...">
                            <small>Commence par pk_test_ (test) ou pk_live_ (production)</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="stripe_secret_key">Clé secrète (Secret key)</label>
                            <input type="password" id="stripe_secret_key" name="stripe_secret_key" 
                                   value="<?php echo htmlspecialchars($current_settings['stripe_secret_key'] ?? ''); ?>"
                                   placeholder="sk_test_...">
                            <small>⚠️ Ne partagez JAMAIS cette clé ! Commence par sk_test_ ou sk_live_</small>
                        </div>
                    </div>
                </div>
                
                <!-- SECTION 2: PAYPAL -->
                <div class="settings-card">
                    <div class="settings-card-header">
                        <h2>🔵 PayPal</h2>
                        <p>Paiement via compte PayPal</p>
                    </div>
                    <div class="settings-card-body">
                        <div class="form-checkboxes" style="margin-bottom: 1.5rem;">
                            <label>
                                <input type="checkbox" name="paypal_enabled" 
                                       <?php echo ($current_settings['paypal_enabled'] ?? '0') === '1' ? 'checked' : ''; ?>>
                                <span>Activer les paiements PayPal</span>
                            </label>
                        </div>
                        
                        <div class="smtp-info-box">
                            <strong>📝 Comment obtenir vos identifiants PayPal :</strong>
                            <ol style="margin: 0.5rem 0 0 1.5rem; line-height: 1.8;">
                                <li>Créez un compte sur <a href="https://developer.paypal.com" target="_blank">developer.paypal.com</a></li>
                                <li>Allez dans <strong>My Apps & Credentials</strong></li>
                                <li>Créez une nouvelle app REST API</li>
                                <li>Copiez votre Client ID et Secret</li>
                            </ol>
                        </div>
                        
                        <div class="form-group">
                            <label for="paypal_mode">Mode</label>
                            <select id="paypal_mode" name="paypal_mode">
                                <option value="sandbox" <?php echo ($current_settings['paypal_mode'] ?? 'sandbox') === 'sandbox' ? 'selected' : ''; ?>>
                                    Sandbox (Test)
                                </option>
                                <option value="live" <?php echo ($current_settings['paypal_mode'] ?? 'sandbox') === 'live' ? 'selected' : ''; ?>>
                                    Live (Production)
                                </option>
                            </select>
                            <small>Utilisez Sandbox pour tester avant de passer en Live</small>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="paypal_client_id">Client ID</label>
                                <input type="text" id="paypal_client_id" name="paypal_client_id" 
                                       value="<?php echo htmlspecialchars($current_settings['paypal_client_id'] ?? ''); ?>"
                                       placeholder="AYSq3RDGsmBLJE-otTkB...">
                            </div>
                            
                            <div class="form-group">
                                <label for="paypal_secret">Secret</label>
                                <input type="password" id="paypal_secret" name="paypal_secret" 
                                       value="<?php echo htmlspecialchars($current_settings['paypal_secret'] ?? ''); ?>"
                                       placeholder="EGnHDxD_qRPdaLdZz8iCr...">
                                <small>⚠️ Ne partagez JAMAIS ce secret !</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- SECTION 3: AVERTISSEMENTS -->
                <div class="settings-card">
                    <div class="settings-card-header">
                        <h2>⚠️ Sécurité et bonnes pratiques</h2>
                        <p>Consignes importantes pour la configuration</p>
                    </div>
                    <div class="settings-card-body" style="padding: 1.5rem;">
                        <ul style="margin: 0; padding-left: 1.5rem; line-height: 2;">
                            <li>Testez toujours en mode <strong>Test/Sandbox</strong> avant de passer en production</li>
                            <li>Vérifiez que votre site utilise <strong>HTTPS</strong> (certificat SSL)</li>
                            <li>Ne partagez <strong>jamais</strong> vos clés secrètes</li>
                            <li>Consultez la documentation Stripe et PayPal pour configurer les <strong>webhooks</strong></li>
                            <li>Conservez vos clés API dans un endroit sécurisé</li>
                        </ul>
                    </div>
                </div>
                
            </form>
            
            <!-- SECTION 4: DOCUMENTATION -->
            <div class="settings-card">
                <div class="settings-card-header">
                    <h2>📚 Ressources utiles</h2>
                    <p>Documentation et outils de test</p>
                </div>
                <div class="settings-card-body" style="padding: 1.5rem;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div>
                            <h4 style="color: var(--forest-green); margin-bottom: 0.5rem;">Stripe</h4>
                            <ul style="margin: 0; padding-left: 1.5rem; line-height: 1.8;">
                                <li><a href="https://stripe.com/docs" target="_blank">Documentation Stripe</a></li>
                                <li><a href="https://stripe.com/docs/testing" target="_blank">Cartes de test</a></li>
                            </ul>
                        </div>
                        <div>
                            <h4 style="color: var(--forest-green); margin-bottom: 0.5rem;">PayPal</h4>
                            <ul style="margin: 0; padding-left: 1.5rem; line-height: 1.8;">
                                <li><a href="https://developer.paypal.com/docs" target="_blank">Documentation PayPal</a></li>
                                <li><a href="https://developer.paypal.com/tools/sandbox/" target="_blank">Sandbox PayPal</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script src="<?php echo BASE_URL; ?>/script.js"></script>
</body>
</html>