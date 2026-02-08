<?php
/**
 * API de validation des billets scannés
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

// Vérifier que c'est bien un admin qui scanne
if (!isLoggedIn() || !isAdmin()) {
    echo json_encode([
        'success' => false,
        'error' => 'Accès non autorisé. Seuls les administrateurs peuvent scanner les billets.'
    ]);
    exit;
}

// Récupérer le code du billet
$ticketCode = $_GET['code'] ?? $_POST['code'] ?? '';

if (empty($ticketCode)) {
    echo json_encode([
        'success' => false,
        'error' => 'Code du billet manquant'
    ]);
    exit;
}

try {
    // Récupérer les informations du billet
    $stmt = $pdo->prepare("
        SELECT et.*, 
               e.title as event_title, 
               e.event_date,
               e.location as event_location,
               u.pseudo as user_name,
               u.email as user_email,
               scanner.pseudo as scanned_by_name
        FROM event_tickets et
        JOIN events e ON et.event_id = e.id
        JOIN users u ON et.user_id = u.id
        LEFT JOIN users scanner ON et.scanned_by = scanner.id
        WHERE et.ticket_code = ?
    ");
    $stmt->execute([$ticketCode]);
    $ticket = $stmt->fetch();
    
    if (!$ticket) {
        echo json_encode([
            'success' => false,
            'error' => 'Billet invalide ou introuvable',
            'status' => 'invalid'
        ]);
        exit;
    }
    
    // Vérifier si le billet a déjà été scanné
    if ($ticket['is_scanned']) {
        $scannedAt = date('d/m/Y à H:i', strtotime($ticket['scanned_at']));
        echo json_encode([
            'success' => false,
            'error' => 'Ce billet a déjà été scanné',
            'status' => 'already_scanned',
            'ticket' => [
                'code' => $ticket['ticket_code'],
                'event' => $ticket['event_title'],
                'participant' => $ticket['user_name'],
                'scanned_at' => $scannedAt,
                'scanned_by' => $ticket['scanned_by_name']
            ]
        ]);
        exit;
    }
    
    // Vérifier si l'événement est passé
    $eventDate = strtotime($ticket['event_date']);
    $now = time();
    
    if ($eventDate < ($now - 86400)) { // Si l'événement date de plus de 24h
        echo json_encode([
            'success' => false,
            'error' => 'L\'événement est terminé depuis plus de 24h',
            'status' => 'expired',
            'ticket' => [
                'code' => $ticket['ticket_code'],
                'event' => $ticket['event_title'],
                'participant' => $ticket['user_name'],
                'event_date' => date('d/m/Y à H:i', $eventDate)
            ]
        ]);
        exit;
    }
    
    // Scanner le billet (marquer comme scanné)
    $stmt = $pdo->prepare("
        UPDATE event_tickets 
        SET is_scanned = 1, 
            scanned_at = NOW(), 
            scanned_by = ?
        WHERE id = ?
    ");
    $stmt->execute([$_SESSION['user_id'], $ticket['id']]);
    
    // Optionnel : Envoyer un email de confirmation
    // require_once __DIR__ . '/send_ticket_email.php';
    // $emailer = new TicketEmailer();
    // $emailer->sendScanConfirmation($ticket);
    
    echo json_encode([
        'success' => true,
        'status' => 'valid',
        'message' => 'Entrée validée avec succès !',
        'ticket' => [
            'id' => $ticket['id'],
            'code' => $ticket['ticket_code'],
            'event' => $ticket['event_title'],
            'event_date' => date('d/m/Y à H:i', $eventDate),
            'participant' => $ticket['user_name'],
            'email' => $ticket['user_email'],
            'scanned_at' => date('d/m/Y à H:i'),
            'scanned_by' => $_SESSION['pseudo']
        ]
    ]);
    
} catch (PDOException $e) {
    // Erreur de base de données
    error_log("Erreur BD dans validate_ticket.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Erreur de connexion à la base de données',
        'status' => 'error',
        'debug' => $e->getMessage()
    ]);
} catch (Exception $e) {
    // Autre erreur
    error_log("Erreur dans validate_ticket.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Erreur serveur: ' . $e->getMessage(),
        'status' => 'error'
    ]);
}
