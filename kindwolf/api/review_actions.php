<?php
// api/review_actions.php - Gestion des avis produits
// ============================================

session_start();
require_once '../config.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'add_review':
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Connexion requise']);
            exit;
        }
        
        $product_id = (int)($_POST['product_id'] ?? 0);
        $rating = (int)($_POST['rating'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $comment = trim($_POST['comment'] ?? '');
        $user_id = $_SESSION['user_id'];
        
        if ($product_id <= 0 || $rating < 1 || $rating > 5 || empty($title)) {
            echo json_encode(['success' => false, 'message' => 'Données invalides']);
            exit;
        }
        
        // Vérifier que l'utilisateur a acheté le produit
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM order_items oi 
                               JOIN orders o ON oi.order_id = o.id 
                               WHERE o.user_id = ? AND oi.product_id = ? AND o.payment_status = 'succeeded'");
        $stmt->execute([$user_id, $product_id]);
        $has_purchased = $stmt->fetchColumn() > 0;
        
        // Vérifier si l'utilisateur a déjà laissé un avis
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'message' => 'Vous avez déjà laissé un avis pour ce produit']);
            exit;
        }
        
        try {
            $stmt = $pdo->prepare("INSERT INTO reviews (product_id, user_id, rating, title, comment, verified_purchase) 
                                   VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$product_id, $user_id, $rating, $title, $comment, $has_purchased ? 1 : 0]);
            
            // Mettre à jour la note moyenne du produit
            updateProductRating($pdo, $product_id);
            
            echo json_encode(['success' => true, 'message' => 'Avis ajouté avec succès']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'ajout']);
        }
        break;
        
    case 'get_reviews':
        $product_id = (int)($_GET['product_id'] ?? 0);
        $page = max(1, (int)($_GET['page'] ?? 1));
        $per_page = 10;
        $offset = ($page - 1) * $per_page;
        
        if ($product_id <= 0) {
            echo json_encode(['success' => false, 'reviews' => []]);
            exit;
        }
        
        $stmt = $pdo->prepare("SELECT r.*, u.name as user_name 
                               FROM reviews r 
                               JOIN users u ON r.user_id = u.id 
                               WHERE r.product_id = ? AND r.approved = 1 
                               ORDER BY r.created_at DESC 
                               LIMIT ? OFFSET ?");
        $stmt->execute([$product_id, $per_page, $offset]);
        $reviews = $stmt->fetchAll();
        
        echo json_encode(['success' => true, 'reviews' => $reviews]);
        break;
    
    case 'submit_review':
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Connexion requise']);
            exit;
        }
        
        $product_id = (int)($_POST['product_id'] ?? 0);
        $rating = (int)($_POST['rating'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $comment = trim($_POST['comment'] ?? '');
        $user_id = $_SESSION['user_id'];
        
        if ($product_id <= 0 || $rating < 1 || $rating > 5 || empty($title)) {
            echo json_encode(['success' => false, 'message' => 'Données invalides']);
            exit;
        }
        
        // Vérifier que l'utilisateur a acheté le produit
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM order_items oi 
                               JOIN orders o ON oi.order_id = o.id 
                               WHERE o.user_id = ? AND oi.product_id = ? AND o.status = 'completed'");
        $stmt->execute([$user_id, $product_id]);
        $has_purchased = $stmt->fetchColumn() > 0;
        
        // Vérifier si l'utilisateur a déjà laissé un avis
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'message' => 'Vous avez déjà laissé un avis pour ce produit']);
            exit;
        }
        
        try {
            $stmt = $pdo->prepare("INSERT INTO reviews (product_id, user_id, rating, title, comment, verified_purchase, created_at) 
                                   VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$product_id, $user_id, $rating, $title, $comment, $has_purchased ? 1 : 0]);
            
            // Mettre à jour la note moyenne du produit
            updateProductRating($pdo, $product_id);
            
            echo json_encode(['success' => true, 'message' => 'Votre avis a été soumis et sera visible après modération.']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'ajout']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Action invalide']);
}

function updateProductRating($pdo, $product_id) {
    $stmt = $pdo->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as review_count 
                           FROM reviews WHERE product_id = ? AND approved = 1");
    $stmt->execute([$product_id]);
    $result = $stmt->fetch();
    
    $pdo->prepare("UPDATE products SET rating = ?, review_count = ? WHERE id = ?")
        ->execute([$result['avg_rating'], $result['review_count'], $product_id]);
}
exit;