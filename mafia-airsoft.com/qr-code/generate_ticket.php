<?php
/**
 * Générateur de billets avec QR code et PDF
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/email_config.php';

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class TicketGenerator {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Génère un code de billet unique
     */
    private function generateTicketCode() {
        return 'TKT-' . strtoupper(bin2hex(random_bytes(8)));
    }
    
    /**
     * Génère un QR code pour le billet
     */
    private function generateQRCode($ticketCode) {
        $options = new QROptions([
            'version'    => 5,
            // Ancienne clé: outputType avec constante (compat serveur)
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            // Ancienne valeur: constante pour niveau ECC (int attendu)
            'eccLevel'   => QRCode::ECC_H,
            'scale'      => 10,
            'imageBase64' => false,
        ]);
        
        $qrcode = new QRCode($options);
        
        // Créer le dossier s'il n'existe pas
        $qrDir = __DIR__ . '/../uploads/qrcodes';
        if (!is_dir($qrDir)) {
            mkdir($qrDir, 0777, true);
        }
        
        $qrCodePath = $qrDir . '/' . $ticketCode . '.png';
        
        // Générer le QR code avec juste le code du billet (plus court)
        // L'URL complète sera construite par le scanner
        $qrcode->render($ticketCode, $qrCodePath);
        
        return 'uploads/qrcodes/' . $ticketCode . '.png';
    }
    
    /**
     * Génère le PDF du billet
     */
    private function generatePDF($ticketData, $qrCodePath) {
        require_once(__DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php');
        
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        
        // Configuration du PDF
        $pdf->SetCreator('MAT Billetterie');
        $pdf->SetAuthor('MAT');
        $pdf->SetTitle('Billet - ' . $ticketData['event_title']);
        $pdf->SetSubject('Billet d\'entrée');
        
        // Supprimer header et footer par défaut
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        $pdf->AddPage();
        
        // Design du billet
        $pdf->SetFont('helvetica', 'B', 24);
        $pdf->SetTextColor(51, 51, 51);
        $pdf->Cell(0, 15, 'BILLET D\'ENTREE', 0, 1, 'C');
        
        // Logo ou image (optionnel)
        $pdf->Ln(5);
        
        // Informations de l'événement
        $pdf->SetFont('helvetica', 'B', 18);
        $pdf->SetTextColor(102, 126, 234);
        $pdf->MultiCell(0, 10, $ticketData['event_title'], 0, 'C');
        
        $pdf->Ln(5);
        
        // Date et lieu
        $pdf->SetFont('helvetica', '', 12);
        $pdf->SetTextColor(85, 85, 85);
        $pdf->Cell(0, 8, 'Date: ' . date('d/m/Y à H:i', strtotime($ticketData['event_date'])), 0, 1, 'C');
        $pdf->Cell(0, 8, 'Lieu: ' . $ticketData['event_location'], 0, 1, 'C');
        
        $pdf->Ln(10);
        
        // Informations du participant
        $pdf->SetFillColor(240, 240, 240);
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 8, 'INFORMATIONS DU PARTICIPANT', 0, 1, 'L', true);
        
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(40, 7, 'Nom:', 0, 0, 'L');
        $pdf->Cell(0, 7, $ticketData['user_name'], 0, 1, 'L');
        $pdf->Cell(40, 7, 'Email:', 0, 0, 'L');
        $pdf->Cell(0, 7, $ticketData['user_email'], 0, 1, 'L');
        
        $pdf->Ln(10);
        
        // Code du billet
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 8, 'CODE DU BILLET', 0, 1, 'L', true);
        $pdf->SetFont('courier', 'B', 14);
        $pdf->SetTextColor(0, 128, 0);
        $pdf->Cell(0, 10, $ticketData['ticket_code'], 0, 1, 'C');
        
        $pdf->Ln(5);
        
        // QR Code
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->SetTextColor(85, 85, 85);
        $pdf->Cell(0, 8, 'SCANNEZ CE CODE A L\'ENTREE', 0, 1, 'C');
        
        // Centrer le QR code
        $qrSize = 60;
        $x = ($pdf->getPageWidth() - $qrSize) / 2;
        $fullQrPath = __DIR__ . '/../' . $qrCodePath;
        
        // Vérifier que le QR code existe
        if (file_exists($fullQrPath)) {
            $pdf->Image($fullQrPath, $x, $pdf->GetY() + 5, $qrSize, $qrSize, 'PNG');
        } else {
            // Si le QR code n'existe pas, afficher un message d'erreur
            $pdf->SetTextColor(255, 0, 0);
            $pdf->Cell(0, 10, 'ERREUR: QR code non trouvé', 0, 1, 'C');
        }
        
        $pdf->Ln($qrSize + 10);
        
        // Instructions
        $pdf->SetFont('helvetica', 'I', 9);
        $pdf->SetTextColor(128, 128, 128);
        $pdf->MultiCell(0, 5, "Présentez ce billet à l'entrée. Le QR code sera scanné pour valider votre accès.\nConservez ce billet jusqu'à la fin de l'événement.", 0, 'C');
        
        $pdf->Ln(5);
        
        // Footer
        $pdf->SetDrawColor(200, 200, 200);
        $pdf->Line(20, $pdf->GetY(), $pdf->getPageWidth() - 20, $pdf->GetY());
        $pdf->Ln(3);
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Cell(0, 5, 'MAT - ' . date('Y') . ' - Tous droits réservés', 0, 1, 'C');
        
        // Créer le dossier s'il n'existe pas
        $ticketDir = __DIR__ . '/../uploads/tickets';
        if (!is_dir($ticketDir)) {
            mkdir($ticketDir, 0777, true);
        }
        
        // Sauvegarder le PDF
        $pdfPath = $ticketDir . '/' . $ticketData['ticket_code'] . '.pdf';
        $pdf->Output($pdfPath, 'F');
        
        return 'uploads/tickets/' . $ticketData['ticket_code'] . '.pdf';
    }
    
    /**
     * Crée un billet complet pour un participant
     */
    public function createTicket($eventId, $userId) {
        try {
            // Récupérer les informations de l'événement
            $stmt = $this->pdo->prepare("
                SELECT * FROM events WHERE id = ?
            ");
            $stmt->execute([$eventId]);
            $event = $stmt->fetch();
            
            if (!$event) {
                throw new Exception("Événement introuvable");
            }
            
            // Récupérer les informations de l'utilisateur
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            if (!$user) {
                throw new Exception("Utilisateur introuvable");
            }
            
            // Vérifier si un billet existe déjà
            $stmt = $this->pdo->prepare("
                SELECT * FROM event_tickets 
                WHERE event_id = ? AND user_id = ?
            ");
            $stmt->execute([$eventId, $userId]);
            $existingTicket = $stmt->fetch();
            
            if ($existingTicket) {
                return [
                    'success' => true,
                    'ticket_id' => $existingTicket['id'],
                    'ticket_code' => $existingTicket['ticket_code'],
                    'pdf_path' => $existingTicket['pdf_path'],
                    'already_exists' => true
                ];
            }
            
            // Générer le code du billet
            $ticketCode = $this->generateTicketCode();
            
            // Générer le QR code
            $qrCodePath = $this->generateQRCode($ticketCode);
            
            // Préparer les données du billet
            $ticketData = [
                'ticket_code' => $ticketCode,
                'event_title' => $event['title'],
                'event_date' => $event['event_date'],
                'event_location' => $event['location'] ?? 'À définir',
                'user_name' => $user['pseudo'],
                'user_email' => $user['email']
            ];
            
            // Générer le PDF
            $pdfPath = $this->generatePDF($ticketData, $qrCodePath);
            
            // Enregistrer le billet dans la base de données
            $stmt = $this->pdo->prepare("
                INSERT INTO event_tickets (event_id, user_id, ticket_code, qr_code_path, pdf_path)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$eventId, $userId, $ticketCode, $qrCodePath, $pdfPath]);
            
            $ticketId = $this->pdo->lastInsertId();
            
            return [
                'success' => true,
                'ticket_id' => $ticketId,
                'ticket_code' => $ticketCode,
                'qr_code_path' => $qrCodePath,
                'pdf_path' => $pdfPath,
                'already_exists' => false
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Récupère les informations d'un billet
     */
    public function getTicket($ticketCode) {
        $stmt = $this->pdo->prepare("
            SELECT et.*, e.title as event_title, e.event_date, e.location as event_location,
                   u.pseudo as user_name, u.email as user_email,
                   s.pseudo as scanned_by_name
            FROM event_tickets et
            JOIN events e ON et.event_id = e.id
            JOIN users u ON et.user_id = u.id
            LEFT JOIN users s ON et.scanned_by = s.id
            WHERE et.ticket_code = ?
        ");
        $stmt->execute([$ticketCode]);
        return $stmt->fetch();
    }
}
