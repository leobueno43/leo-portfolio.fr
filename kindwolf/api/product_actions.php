<?php
// api/product_actions.php - Actions produits
// ============================================

session_start();
require_once '../config.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'increment_view':
        $product_id = (int)($_POST['product_id'] ?? 0);
        
        if ($product_id > 0) {
            $stmt = $pdo->prepare("UPDATE products SET views = views + 1 WHERE id = ?");
            $stmt->execute([$product_id]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
        break;
        
    case 'add_to_wishlist':
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Connexion requise']);
            exit;
        }
        
        $product_id = (int)($_POST['product_id'] ?? 0);
        $user_id = $_SESSION['user_id'];
        
        if ($product_id > 0) {
            try {
                $stmt = $pdo->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
                $stmt->execute([$user_id, $product_id]);
                echo json_encode(['success' => true, 'message' => 'Ajouté à la liste de souhaits']);
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) { // Duplicate entry
                    echo json_encode(['success' => false, 'message' => 'Déjà dans la liste']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
                }
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Produit invalide']);
        }
        break;
        
    case 'remove_from_wishlist':
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Connexion requise']);
            exit;
        }
        
        $product_id = (int)($_POST['product_id'] ?? 0);
        $user_id = $_SESSION['user_id'];
        
        if ($product_id > 0) {
            $stmt = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$user_id, $product_id]);
            echo json_encode(['success' => true, 'message' => 'Retiré de la liste']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Produit invalide']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Action invalide']);
}
exit;
?>