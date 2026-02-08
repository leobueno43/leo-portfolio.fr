<?php
// api/payment_success.php - Traiter le paiement réussi
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

$order_id = (int)($_GET['order_id'] ?? 0);
$payment_method = $_GET['method'] ?? ''; // stripe ou paypal
$payment_id = $_GET['payment_id'] ?? '';

if (!$order_id || !$payment_method || !$payment_id) {
    header('Location: ' . BASE_URL . '/pages/panier.php');
    exit;
}

// Vérifier que la commande appartient à l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: ' . BASE_URL . '/pages/panier.php');
    exit;
}

try {
    // Mettre à jour la commande
    $stmt = $pdo->prepare("UPDATE orders 
                           SET payment_status = 'succeeded',
                               status = 'confirmed', 
                               payment_method = ?, 
                               payment_id = ?
                           WHERE id = ?");
    $stmt->execute([$payment_method, $payment_id, $order_id]);
    
    // Décrémenter le stock maintenant que le paiement est confirmé
    $items_stmt = $pdo->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
    $items_stmt->execute([$order_id]);
    $items = $items_stmt->fetchAll();
    
    foreach ($items as $item) {
        $stock_stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        $stock_stmt->execute([$item['quantity'], $item['product_id']]);
    }
    
    // Vider le panier
    unset($_SESSION['cart']);
    unset($_SESSION['current_order_id']);
    
    // Envoyer un email de confirmation (optionnel)
    $user_stmt = $pdo->prepare("SELECT email, name FROM users WHERE id = ?");
    $user_stmt->execute([$_SESSION['user_id']]);
    $user = $user_stmt->fetch();
    
    if ($user) {
        $to = $user['email'];
        $subject = 'Confirmation de commande #' . $order_id . ' - KIND WOLF';
        $message = "Bonjour " . $user['name'] . ",\n\n";
        $message .= "Votre commande #" . $order_id . " a été confirmée et payée avec succès.\n";
        $message .= "Montant total : " . number_format($order['total'], 2) . " €\n\n";
        $message .= "Vous recevrez bientôt un email avec le suivi de livraison.\n\n";
        $message .= "Merci de votre confiance !\n\n";
        $message .= "L'équipe KIND WOLF";
        
        $headers = "From: no-reply@kindwolf.com\r\n";
        $headers .= "Reply-To: contact@kindwolf.com\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        
        // Note: Dans un environnement de production, utilisez un service SMTP (PHPMailer, SendGrid, etc.)
        @mail($to, $subject, $message, $headers);
    }
    
    // Rediriger vers la page de confirmation
    header('Location: ' . BASE_URL . '/pages/order-confirmation.php?order_id=' . $order_id);
    exit;
    
} catch (Exception $e) {
    error_log('Payment success error: ' . $e->getMessage());
    header('Location: ' . BASE_URL . '/pages/checkout.php?error=' . urlencode('Erreur lors du traitement'));
    exit;
}
?>