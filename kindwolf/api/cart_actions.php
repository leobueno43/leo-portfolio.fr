<?php
// api/cart_actions.php - Actions panier AJAX
// ============================================

session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$action = isset($_POST['action']) ? $_POST['action'] : '';
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

switch ($action) {
    case 'add':
        if ($product_id > 0) {
            // Vérifier que le produit existe et est actif
            $stmt = $pdo->prepare("SELECT id, stock FROM products WHERE id = ? AND active = 1");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();
            
            if ($product) {
                // Vérifier le stock
                $current_qty = isset($_SESSION['cart'][$product_id]) ? $_SESSION['cart'][$product_id] : 0;
                $new_qty = $current_qty + $quantity;
                
                if ($new_qty > $product['stock']) {
                    echo json_encode([
                        'success' => false, 
                        'message' => 'Stock insuffisant'
                    ]);
                } else {
                    $_SESSION['cart'][$product_id] = $new_qty;
                    echo json_encode([
                        'success' => true, 
                        'count' => array_sum($_SESSION['cart'])
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Produit non disponible'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Produit invalide'
            ]);
        }
        break;
        
    case 'update':
        if ($product_id > 0) {
            if ($quantity > 0) {
                // Vérifier le stock
                $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ? AND active = 1");
                $stmt->execute([$product_id]);
                $product = $stmt->fetch();
                
                if ($product && $quantity <= $product['stock']) {
                    $_SESSION['cart'][$product_id] = $quantity;
                    echo json_encode([
                        'success' => true, 
                        'count' => array_sum($_SESSION['cart'])
                    ]);
                } else {
                    echo json_encode([
                        'success' => false, 
                        'message' => 'Stock insuffisant'
                    ]);
                }
            } else {
                unset($_SESSION['cart'][$product_id]);
                echo json_encode([
                    'success' => true, 
                    'count' => array_sum($_SESSION['cart'])
                ]);
            }
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Produit invalide'
            ]);
        }
        break;
        
    case 'remove':
        if ($product_id > 0) {
            unset($_SESSION['cart'][$product_id]);
            echo json_encode([
                'success' => true, 
                'count' => array_sum($_SESSION['cart'])
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Produit invalide'
            ]);
        }
        break;
        
    case 'get_count':
        echo json_encode([
            'count' => array_sum($_SESSION['cart'])
        ]);
        break;
        
    case 'clear':
        $_SESSION['cart'] = [];
        echo json_encode([
            'success' => true, 
            'count' => 0
        ]);
        break;
        
    default:
        echo json_encode([
            'success' => false, 
            'message' => 'Action invalide'
        ]);
}
exit;
?>