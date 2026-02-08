<?php
// admin/settings/site.php - Paramètres du site CORRIGÉ
// ============================================

session_start();
require_once '../../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

$success = '';
$error = '';

// Traiter la sauvegarde
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings = [
        'site_name' => trim($_POST['site_name'] ?? ''),
        'site_email' => trim($_POST['site_email'] ?? ''),
        'site_phone' => trim($_POST['site_phone'] ?? ''),
        'site_address' => trim($_POST['site_address'] ?? ''),
        'free_shipping_threshold' => (float)($_POST['free_shipping_threshold'] ?? 0),
        'shipping_cost' => (float)($_POST['shipping_cost'] ?? 0),
        'maintenance_mode' => isset($_POST['maintenance_mode']) ? 1 : 0,
        // Paramètres SMTP pour newsletter
        'email_smtp_host' => trim($_POST['email_smtp_host'] ?? ''),
        'email_smtp_port' => trim($_POST['email_smtp_port'] ?? '587'),
        'email_smtp_username' => trim($_POST['email_smtp_username'] ?? ''),
        'email_from' => trim($_POST['email_from'] ?? ''),
        'email_from_name' => trim($_POST['email_from_name'] ?? '')
    ];
    
    // Ne mettre à jour le mot de passe que s'il est fourni
    $new_password = trim($_POST['email_smtp_password'] ?? '');
    if (!empty($new_password)) {
        $settings['email_smtp_password'] = $new_password;
    }
    
    try {
        foreach ($settings as $key => $value) {
            $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) 
                                   VALUES (?, ?) 
                                   ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->execute([$key, $value, $value]);
        }
        $success = 'Paramètres enregistrés avec succès';
    } catch (PDOException $e) {
        $error = 'Erreur lors de l\'enregistrement : ' . $e->getMessage();
    }
}

// Récupérer les paramètres - REQUÊTE CORRIGÉE
try {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
    $settings = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (PDOException $e) {
    $settings = [];
    $error = "Erreur de chargement des paramètres : " . $e->getMessage();
}

// Valeurs par défaut
$defaults = [
    'site_name' => 'KIND WOLF',
    'site_email' => 'contact@kindwolf.com',
    'site_phone' => '+33 1 23 45 67 89',
    'site_address' => '123 Rue de la Mode, 75001 Paris',
    'free_shipping_threshold' => 50,
    'shipping_cost' => 5.99,
    'maintenance_mode' => 0,
    // SMTP par défaut
    'email_smtp_host' => 'smtp.gmail.com',
    'email_smtp_port' => '587',
    'email_smtp_username' => '',
    'email_smtp_password' => '',
    'email_from' => 'noreply@kindwolf.com',
    'email_from_name' => 'KIND WOLF'
];

foreach ($defaults as $key => $value) {
    if (!isset($settings[$key])) {
        $settings[$key] = $value;
    }
}

// Forcer le seuil de livraison gratuite à minimum 50 si c'est 0
if (!isset($settings['free_shipping_threshold']) || $settings['free_shipping_threshold'] <= 0) {
    $settings['free_shipping_threshold'] = 50;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paramètres du site - KIND WOLF Admin</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/style.css">
</head>
<body class="admin-body">
    <div class="admin-container">
        <?php include '../admin_sidebar.php'; ?>
        
        <main class="admin-main">
            <div class="admin-header">
                <h1>⚙️ Paramètres du site</h1>
                <div style="display: flex; gap: 1rem;">
                    <button type="submit" form="settings-form" class="btn-primary">💾 Enregistrer</button>
                    <a href="payment.php" class="btn-secondary">💳 Paiement</a>
                </div>
            </div>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form id="settings-form" method="POST">
                
                <!-- SECTION 1: Informations générales -->
                <div class="settings-card">
                    <div class="settings-card-header">
                        <h2>🏢 Informations générales</h2>
                        <p>Informations de base de votre boutique</p>
                    </div>
                    <div class="settings-card-body">
                        <div class="form-group">
                            <label for="site_name">Nom du site</label>
                            <input type="text" id="site_name" name="site_name" 
                                   value="<?php echo htmlspecialchars($settings['site_name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="site_email">Email de contact</label>
                            <input type="email" id="site_email" name="site_email" 
                                   value="<?php echo htmlspecialchars($settings['site_email']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="site_phone">Téléphone</label>
                            <input type="tel" id="site_phone" name="site_phone" 
                                   value="<?php echo htmlspecialchars($settings['site_phone']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="site_address">Adresse</label>
                            <textarea id="site_address" name="site_address" rows="3"><?php echo htmlspecialchars($settings['site_address']); ?></textarea>
                        </div>
                    </div>
                </div>
                
                <!-- SECTION 2: Livraison -->
                <div class="settings-card">
                    <div class="settings-card-header">
                        <h2>🚚 Paramètres de livraison</h2>
                        <p>Configurez les frais et seuils de livraison</p>
                    </div>
                    <div class="settings-card-body">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="shipping_cost">Frais de livraison (€)</label>
                                <input type="number" id="shipping_cost" name="shipping_cost" 
                                       step="0.01" min="0" 
                                       value="<?php echo htmlspecialchars($settings['shipping_cost']); ?>" required>
                                <small>Coût de livraison standard</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="free_shipping_threshold">Seuil livraison gratuite (€)</label>
                                <input type="number" id="free_shipping_threshold" name="free_shipping_threshold" 
                                       step="0.01" min="50" 
                                       value="<?php echo htmlspecialchars($settings['free_shipping_threshold']); ?>" required>
                                <small>Minimum : 50€</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- SECTION 3: Configuration Email SMTP -->
                <div class="settings-card">
                    <div class="settings-card-header">
                        <h2>📧 Configuration Email (SMTP)</h2>
                        <p>Paramètres pour l'envoi d'emails (newsletter, notifications)</p>
                    </div>
                    <div class="settings-card-body">
                        <p class="help-text">
                            Configurez les paramètres SMTP pour envoyer des emails (newsletter, notifications, etc.).<br>
                            <strong>Pour Gmail :</strong> Activez la validation en 2 étapes puis générez un "mot de passe d'application" 
                            <a href="https://myaccount.google.com/apppasswords" target="_blank">ici</a>
                        </p>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="email_smtp_host">Serveur SMTP</label>
                                <input type="text" id="email_smtp_host" name="email_smtp_host" 
                                       value="<?php echo htmlspecialchars($settings['email_smtp_host']); ?>"
                                       placeholder="smtp.gmail.com">
                                <small>Ex: smtp.gmail.com, smtp.office365.com, smtp.mailtrap.io</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="email_smtp_port">Port SMTP</label>
                                <input type="number" id="email_smtp_port" name="email_smtp_port" 
                                       value="<?php echo htmlspecialchars($settings['email_smtp_port']); ?>"
                                       placeholder="587">
                                <small>587 (TLS) ou 465 (SSL)</small>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="email_smtp_username">Nom d'utilisateur SMTP</label>
                                <input type="text" id="email_smtp_username" name="email_smtp_username" 
                                       value="<?php echo htmlspecialchars($settings['email_smtp_username']); ?>"
                                       placeholder="votre-email@gmail.com">
                                <small>Votre adresse email complète</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="email_smtp_password">Mot de passe SMTP</label>
                                <div style="position: relative;">
                                    <input type="text" id="email_smtp_password" name="email_smtp_password" 
                                           value="<?php echo htmlspecialchars($settings['email_smtp_password']); ?>"
                                           placeholder="Mot de passe d'application Gmail (16 caractères)"
                                           autocomplete="off">
                                    <button type="button" onclick="togglePasswordVisibility()" 
                                            style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); 
                                                   background: none; border: none; cursor: pointer; font-size: 1.2rem;"
                                            title="Masquer/Afficher">
                                        👁️
                                    </button>
                                </div>
                                <small>Pour Gmail : mot de passe d'application (16 caractères). La valeur reste affichée après sauvegarde.</small>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="email_from">Email expéditeur</label>
                                <input type="email" id="email_from" name="email_from" 
                                       value="<?php echo htmlspecialchars($settings['email_from']); ?>"
                                       placeholder="noreply@kindwolf.com">
                                <small>Adresse email qui apparaîtra comme expéditeur</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="email_from_name">Nom expéditeur</label>
                                <input type="text" id="email_from_name" name="email_from_name" 
                                       value="<?php echo htmlspecialchars($settings['email_from_name']); ?>"
                                       placeholder="KIND WOLF">
                                <small>Nom qui apparaîtra comme expéditeur</small>
                            </div>
                        </div>
                        
                        <?php if (!empty($settings['email_smtp_username'])): ?>
                        <div class="smtp-status">
                            <span class="status-badge success">✅ SMTP configuré</span>
                            <span>Utilisateur : <?php echo htmlspecialchars($settings['email_smtp_username']); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- SECTION 4: Maintenance -->
                <div class="settings-card">
                    <div class="settings-card-header">
                        <h2>🔧 Mode maintenance</h2>
                        <p>Activer une page de maintenance pour les visiteurs</p>
                    </div>
                    <div class="settings-card-body" style="padding: 1.5rem;">
                        <div class="form-checkboxes">
                            <label>
                                <input type="checkbox" name="maintenance_mode" 
                                       <?php echo $settings['maintenance_mode'] ? 'checked' : ''; ?>>
                                <span>Activer le mode maintenance</span>
                            </label>
                        </div>
                        <small style="display: block; margin-top: 0.5rem; color: #666; padding-left: 0.5rem;">Le site affichera une page de maintenance aux visiteurs (sauf administrateurs)</small>
                    </div>
                </div>
                
            </form>
        </main>
    </div>
    
    <script src="<?php echo BASE_URL; ?>/script.js"></script>
    <script>
    function togglePasswordVisibility() {
        const input = document.getElementById('email_smtp_password');
        const button = event.target;
        
        if (input.type === 'text') {
            input.type = 'password';
            button.textContent = '👁️';
            button.title = 'Afficher';
        } else {
            input.type = 'text';
            button.textContent = '🙈';
            button.title = 'Masquer';
        }
    }
    </script>
</body>
</html>