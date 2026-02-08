<?php
// api/create_payment_intent.php - Créer un PaymentIntent Stripe
header('Content-Type: application/json');
session_start();
require_once '../config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}

// Récupérer les données
$data = json_decode(file_get_contents('php://input'), true);
$order_id = $data['order_id'] ?? null;
$amount = $data['amount'] ?? null;

if (!$order_id || !$amount) {
    http_response_code(400);
    echo json_encode(['error' => 'Données manquantes']);
    exit;
}

// Vérifier que la commande appartient à l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    http_response_code(404);
    echo json_encode(['error' => 'Commande introuvable']);
    exit;
}

// Récupérer la clé secrète Stripe
$stmt = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'stripe_secret_key'");
$stmt->execute();
$stripe_secret = $stmt->fetchColumn();

if (!$stripe_secret) {
    http_response_code(500);
    echo json_encode(['error' => 'Configuration Stripe manquante']);
    exit;
}

// Installer Stripe via Composer : composer require stripe/stripe-php
// Ou utiliser l'API REST directement
try {
    // Créer le PaymentIntent via l'API Stripe
    $amount_cents = (int)($amount * 100); // Convertir en centimes
    
    $ch = curl_init('https://api.stripe.com/v1/payment_intents');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_USERPWD, $stripe_secret . ':');
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'amount' => $amount_cents,
        'currency' => 'eur',
        'description' => 'Commande KIND WOLF #' . $order_id,
        'metadata' => [
            'order_id' => $order_id,
            'user_id' => $_SESSION['user_id']
        ]
    ]));
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        throw new Exception('Erreur Stripe : ' . $response);
    }
    
    $paymentIntent = json_decode($response, true);
    
    // Enregistrer le PaymentIntent ID dans la commande
    $stmt = $pdo->prepare("UPDATE orders SET payment_intent_id = ? WHERE id = ?");
    $stmt->execute([$paymentIntent['id'], $order_id]);
    
    echo json_encode([
        'clientSecret' => $paymentIntent['client_secret']
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>