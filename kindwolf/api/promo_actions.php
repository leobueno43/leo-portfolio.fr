<?php
// api/promo_actions.php - Actions codes promo
// ============================================

session_start();
require_once '../config.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'apply':
        $code = strtoupper(trim($_POST['code'] ?? ''));
        
        if (empty($code)) {
            echo json_encode(['success' => false, 'message' => 'Code promo vide']);
            exit;
        }
        
        // Vérifier le code promo
        $stmt = $pdo->prepare("SELECT * FROM promo_codes 
                               WHERE code = ? AND active = 1 
                               AND (expires_at IS NULL OR expires_at > NOW())
                               AND (usage_limit IS NULL OR usage_count < usage_limit)");
        $stmt->execute([$code]);
        $promo = $stmt->fetch();
        
        if (!$promo) {
            echo json_encode(['success' => false, 'message' => 'Code promo invalide ou expiré']);
            exit;
        }
        
        // Vérifier si l'utilisateur a déjà utilisé ce code
        if (isset($_SESSION['user_id'])) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM promo_usage 
                                   WHERE promo_id = ? AND user_id = ?");
            $stmt->execute([$promo['id'], $_SESSION['user_id']]);
            $usage_count = $stmt->fetchColumn();
            
            if ($usage_count >= $promo['user_limit']) {
                echo json_encode(['success' => false, 'message' => 'Vous avez déjà utilisé ce code promo']);
                exit;
            }
        }
        
        // Calculer le montant du panier pour vérifier le minimum
        $cart_total = 0;
        if (!empty($_SESSION['cart'])) {
            $ids = array_keys($_SESSION['cart']);
            $placeholders = str_repeat('?,', count($ids) - 1) . '?';
            $stmt = $pdo->prepare("SELECT id, price FROM products WHERE id IN ($placeholders)");
            $stmt->execute($ids);
            $products = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            foreach ($_SESSION['cart'] as $product_id => $quantity) {
                if (isset($products[$product_id])) {
                    $cart_total += $products[$product_id] * $quantity;
                }
            }
        }
        
        if ($cart_total < $promo['minimum_amount']) {
            echo json_encode([
                'success' => false, 
                'message' => 'Montant minimum de ' . number_format($promo['minimum_amount'], 2) . ' € requis'
            ]);
            exit;
        }
        
        // Appliquer le code promo
        $_SESSION['promo_code'] = $code;
        
        echo json_encode([
            'success' => true, 
            'message' => 'Code promo appliqué avec succès',
            'code' => $code,
            'discount_type' => $promo['discount_type'],
            'discount_value' => $promo['discount_type'] === 'percentage' ? $promo['discount_percent'] : $promo['discount_amount']
        ]);
        break;
        
    case 'remove':
        unset($_SESSION['promo_code']);
        echo json_encode(['success' => true, 'message' => 'Code promo retiré']);
        break;
        
    case 'validate':
        $code = strtoupper(trim($_POST['code'] ?? ''));
        
        $stmt = $pdo->prepare("SELECT * FROM promo_codes 
                               WHERE code = ? AND active = 1 
                               AND (expires_at IS NULL OR expires_at > NOW())
                               AND (usage_limit IS NULL OR usage_count < usage_limit)");
        $stmt->execute([$code]);
        $promo = $stmt->fetch();
        
        if ($promo) {
            echo json_encode([
                'success' => true,
                'valid' => true,
                'discount_type' => $promo['discount_type'],
                'discount_value' => $promo['discount_type'] === 'percentage' ? $promo['discount_percent'] : $promo['discount_amount'],
                'minimum_amount' => $promo['minimum_amount']
            ]);
        } else {
            echo json_encode(['success' => true, 'valid' => false]);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Action invalide']);
}
exit;
?>