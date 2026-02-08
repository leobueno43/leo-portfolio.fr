<!-- admin/settings/shipping.php - Paramètres de livraison -->
<!-- ============================================ -->
<?php
session_start();
require_once '../../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../../login.php');
    exit;
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $settings = [
            'free_shipping_threshold' => $_POST['free_shipping_threshold'] ?? '50.00',
            'default_shipping_cost' => $_POST['default_shipping_cost'] ?? '5.99',
            'express_shipping_cost' => $_POST['express_shipping_cost'] ?? '12.99',
            'international_shipping_cost' => $_POST['international_shipping_cost'] ?? '19.99'
        ];
        
        foreach ($settings as $key => $value) {
            $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) 
                                   VALUES (?, ?) 
                                   ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->execute([$key, $value, $value]);
        }
        
        $success = 'Paramètres de livraison mis à jour avec succès';
    } catch (PDOException $e) {
        $error = 'Erreur lors de la mise à jour: ' . $e->getMessage();
    }
}

// Récupérer les paramètres actuels
$stmt = $pdo->query("SELECT * FROM site_settings WHERE setting_key LIKE '%shipping%'");
$settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paramètres Livraison - KIND WOLF Admin</title>
    <link rel="stylesheet" href="../../style.css">
</head>
<body>
    <div class="admin-container">
        <?php include '../admin_sidebar.php'; ?>
        
        <div class="admin-main">
            <div class="admin-header">
                <h1>Paramètres de Livraison</h1>
                <a href="site.php" class="btn-outline">Paramètres généraux</a>
            </div>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <div class="admin-section">
                <form method="POST">
                    <h2>Frais de livraison</h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="free_shipping_threshold">Seuil livraison gratuite (€)</label>
                            <input type="number" step="0.01" id="free_shipping_threshold" 
                                   name="free_shipping_threshold" 
                                   value="<?php echo htmlspecialchars($settings['free_shipping_threshold'] ?? '50.00'); ?>" 
                                   required>
                            <small>Montant minimum pour bénéficier de la livraison gratuite</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="default_shipping_cost">Livraison standard (€)</label>
                            <input type="number" step="0.01" id="default_shipping_cost" 
                                   name="default_shipping_cost" 
                                   value="<?php echo htmlspecialchars($settings['default_shipping_cost'] ?? '5.99'); ?>" 
                                   required>
                            <small>Frais de livraison standard (3-5 jours ouvrés)</small>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="express_shipping_cost">Livraison express (€)</label>
                            <input type="number" step="0.01" id="express_shipping_cost" 
                                   name="express_shipping_cost" 
                                   value="<?php echo htmlspecialchars($settings['express_shipping_cost'] ?? '12.99'); ?>" 
                                   required>
                            <small>Frais de livraison express (24-48h)</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="international_shipping_cost">Livraison internationale (€)</label>
                            <input type="number" step="0.01" id="international_shipping_cost" 
                                   name="international_shipping_cost" 
                                   value="<?php echo htmlspecialchars($settings['international_shipping_cost'] ?? '19.99'); ?>" 
                                   required>
                            <small>Frais de livraison hors France métropolitaine</small>
                        </div>
                    </div>
                    
                    <h2>Options de livraison</h2>
                    
                    <div class="form-checkboxes">
                        <label>
                            <input type="checkbox" name="enable_express_shipping" value="1" 
                                   <?php echo ($settings['enable_express_shipping'] ?? '1') == '1' ? 'checked' : ''; ?>>
                            Activer la livraison express
                        </label>
                        <label>
                            <input type="checkbox" name="enable_international_shipping" value="1" 
                                   <?php echo ($settings['enable_international_shipping'] ?? '1') == '1' ? 'checked' : ''; ?>>
                            Activer la livraison internationale
                        </label>
                        <label>
                            <input type="checkbox" name="enable_pickup" value="1" 
                                   <?php echo ($settings['enable_pickup'] ?? '0') == '1' ? 'checked' : ''; ?>>
                            Activer le retrait en point relais
                        </label>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">Enregistrer les paramètres</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>