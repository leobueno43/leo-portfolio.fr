<?php
// api/newsletter_actions.php - Gestion newsletter
// ============================================

session_start();
require_once '../config.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'subscribe':
        $email = trim($_POST['email'] ?? '');
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Email invalide']);
            exit;
        }
        
        // Vérifier si déjà inscrit
        $stmt = $pdo->prepare("SELECT id FROM newsletter_subscribers WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Cet email est déjà inscrit']);
            exit;
        }
        
        try {
            $token = bin2hex(random_bytes(16));
            $stmt = $pdo->prepare("INSERT INTO newsletter_subscribers (email, token, subscribed_at) VALUES (?, ?, NOW())");
            $stmt->execute([$email, $token]);
            
            echo json_encode(['success' => true, 'message' => 'Inscription réussie à la newsletter']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'inscription']);
        }
        break;
        
    case 'unsubscribe':
        $token = trim($_POST['token'] ?? '');
        
        if (empty($token)) {
            echo json_encode(['success' => false, 'message' => 'Token invalide']);
            exit;
        }
        
        $stmt = $pdo->prepare("UPDATE newsletter_subscribers SET active = 0, unsubscribed_at = NOW() WHERE token = ?");
        
        if ($stmt->execute([$token]) && $stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Désinscription réussie']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Token non trouvé']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Action invalide']);
}
exit;