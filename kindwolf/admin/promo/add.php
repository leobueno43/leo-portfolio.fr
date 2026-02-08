<?php
// admin/promo/add.php
session_start();
require_once '../../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = strtoupper(trim($_POST['code'] ?? ''));
    $discount_type = $_POST['discount_type'] ?? 'percentage';
    $discount_percent = (float)($_POST['discount_percent'] ?? 0);
    $discount_amount = (float)($_POST['discount_amount'] ?? 0);
    $minimum_amount = (float)($_POST['minimum_amount'] ?? 0);
    $maximum_discount = (float)($_POST['maximum_discount'] ?? 0) ?: null;
    $usage_limit = (int)($_POST['usage_limit'] ?? 0) ?: null;
    $user_limit = (int)($_POST['user_limit'] ?? 1);
    $expires_at = $_POST['expires_at'] ?: null;
    $active = isset($_POST['active']) ? 1 : 0;
    
    if (empty($code)) {
        $error = "Le code est requis";
    } else {
        // Vérifier que le code n'existe pas déjà
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM promo_codes WHERE code = ?");
        $stmt->execute([$code]);
        
        if ($stmt->fetchColumn() > 0) {
            $error = "Ce code existe déjà";
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO promo_codes (
                    code, discount_type, discount_percent, discount_amount, 
                    minimum_amount, maximum_discount, usage_limit, user_limit, 
                    expires_at, active, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                
                $stmt->execute([
                    $code, $discount_type, $discount_percent, $discount_amount,
                    $minimum_amount, $maximum_discount, $usage_limit, $user_limit,
                    $expires_at, $active
                ]);
                
                header('Location: list.php?success=' . urlencode('Code promo créé avec succès'));
                exit;
            } catch (PDOException $e) {
                $error = "Erreur lors de la création";
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
    <title>Nouveau code promo - Admin KIND WOLF</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/style.css">
</head>
<body>
    <div class="admin-container">
        <?php include '../admin_sidebar.php'; ?>
        
        <main class="admin-main">
            <div class="admin-section">
                <h1>+ Nouveau code promo</h1>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" class="admin-form">
                    <div class="form-group">
                        <label for="code">Code * (lettres et chiffres uniquement)</label>
                        <input type="text" id="code" name="code" required 
                               pattern="[A-Z0-9]+" maxlength="20" 
                               style="text-transform: uppercase;">
                    </div>
                    
                    <div class="form-group">
                        <label for="discount_type">Type de réduction *</label>
                        <select id="discount_type" name="discount_type" required onchange="toggleDiscountFields()">
                            <option value="percentage">Pourcentage</option>
                            <option value="fixed">Montant fixe</option>
                        </select>
                    </div>
                    
                    <div class="form-group" id="percent_field">
                        <label for="discount_percent">Pourcentage de réduction (%)</label>
                        <input type="number" id="discount_percent" name="discount_percent" 
                               min="0" max="100" step="0.01">
                    </div>
                    
                    <div class="form-group" id="amount_field" style="display: none;">
                        <label for="discount_amount">Montant de réduction (€)</label>
                        <input type="number" id="discount_amount" name="discount_amount" 
                               min="0" step="0.01">
                    </div>
                    
                    <div class="form-group">
                        <label for="minimum_amount">Montant minimum du panier (€)</label>
                        <input type="number" id="minimum_amount" name="minimum_amount" 
                               value="0" min="0" step="0.01">
                    </div>
                    
                    <div class="form-group">
                        <label for="maximum_discount">Réduction maximum (€) - optionnel</label>
                        <input type="number" id="maximum_discount" name="maximum_discount" 
                               min="0" step="0.01" placeholder="Illimité">
                    </div>
                    
                    <div class="form-group">
                        <label for="usage_limit">Nombre d'utilisations maximum - optionnel</label>
                        <input type="number" id="usage_limit" name="usage_limit" 
                               min="1" placeholder="Illimité">
                    </div>
                    
                    <div class="form-group">
                        <label for="user_limit">Nombre d'utilisations par utilisateur</label>
                        <input type="number" id="user_limit" name="user_limit" 
                               value="1" min="1">
                    </div>
                    
                    <div class="form-group">
                        <label for="expires_at">Date d'expiration - optionnel</label>
                        <input type="datetime-local" id="expires_at" name="expires_at">
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="active" checked>
                            Code actif
                        </label>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">Créer le code promo</button>
                        <a href="list.php" class="btn-outline">Annuler</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
    
    <script>
        function toggleDiscountFields() {
            const type = document.getElementById('discount_type').value;
            document.getElementById('percent_field').style.display = type === 'percentage' ? 'block' : 'none';
            document.getElementById('amount_field').style.display = type === 'fixed' ? 'block' : 'none';
        }
    </script>
    <script src="<?php echo BASE_URL; ?>/script.js"></script>
</body>
</html>
