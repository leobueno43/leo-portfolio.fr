<?php
/**
 * Fonction d'aide pour intégrer la génération automatique de billets
 * À inclure après une inscription réussie
 */

require_once __DIR__ . '/generate_ticket.php';
require_once __DIR__ . '/send_ticket_email.php';

/**
 * Génère et envoie automatiquement un billet après inscription
 * 
 * @param PDO $pdo - Connexion à la base de données
 * @param int $eventId - ID de l'événement
 * @param int $userId - ID de l'utilisateur inscrit
 * @return array - Résultat de la génération et de l'envoi
 */
function processTicketAfterRegistration($pdo, $eventId, $userId) {
    try {
        // Générer le billet
        $ticketGenerator = new TicketGenerator($pdo);
        $ticketResult = $ticketGenerator->createTicket($eventId, $userId);
        
        if (!$ticketResult['success']) {
            return [
                'success' => false,
                'error' => 'Erreur lors de la génération du billet: ' . $ticketResult['error']
            ];
        }
        
        // Récupérer les détails complets du billet
        $ticket = $ticketGenerator->getTicket($ticketResult['ticket_code']);
        
        if (!$ticket) {
            return [
                'success' => false,
                'error' => 'Billet créé mais impossible de récupérer les détails'
            ];
        }
        
        // Envoyer le billet par email
        $emailer = new TicketEmailer();
        $emailResult = $emailer->sendTicket([
            'ticket_code' => $ticket['ticket_code'],
            'event_title' => $ticket['event_title'],
            'event_date' => $ticket['event_date'],
            'event_location' => $ticket['event_location'],
            'user_name' => $ticket['user_name'],
            'user_email' => $ticket['user_email'],
            'pdf_path' => $ticket['pdf_path']
        ]);
        
        if (!$emailResult['success']) {
            // Le billet est créé mais l'email n'a pas pu être envoyé
            return [
                'success' => true,
                'warning' => 'Billet créé mais email non envoyé: ' . $emailResult['error'],
                'ticket_code' => $ticketResult['ticket_code'],
                'ticket_id' => $ticketResult['ticket_id']
            ];
        }
        
        return [
            'success' => true,
            'message' => $ticketResult['already_exists'] ? 
                'Billet renvoyé par email' : 
                'Billet généré et envoyé par email',
            'ticket_code' => $ticketResult['ticket_code'],
            'ticket_id' => $ticketResult['ticket_id'],
            'email_sent' => true
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => 'Erreur inattendue: ' . $e->getMessage()
        ];
    }
}

/**
 * Supprime le billet lors d'une désinscription
 * 
 * @param PDO $pdo - Connexion à la base de données
 * @param int $eventId - ID de l'événement
 * @param int $userId - ID de l'utilisateur désinscrit
 * @return bool - Succès de la suppression
 */
function deleteTicketAfterUnregistration($pdo, $eventId, $userId) {
    try {
        // Récupérer le billet
        $stmt = $pdo->prepare("
            SELECT * FROM event_tickets 
            WHERE event_id = ? AND user_id = ?
        ");
        $stmt->execute([$eventId, $userId]);
        $ticket = $stmt->fetch();
        
        if (!$ticket) {
            return true; // Pas de billet à supprimer
        }
        
        // Supprimer les fichiers physiques
        if (!empty($ticket['qr_code_path']) && file_exists('../' . $ticket['qr_code_path'])) {
            unlink('../' . $ticket['qr_code_path']);
        }
        
        if (!empty($ticket['pdf_path']) && file_exists('../' . $ticket['pdf_path'])) {
            unlink('../' . $ticket['pdf_path']);
        }
        
        // Supprimer l'entrée de la base de données
        $stmt = $pdo->prepare("DELETE FROM event_tickets WHERE id = ?");
        $stmt->execute([$ticket['id']]);
        
        return true;
        
    } catch (Exception $e) {
        error_log('Erreur lors de la suppression du billet: ' . $e->getMessage());
        return false;
    }
}
