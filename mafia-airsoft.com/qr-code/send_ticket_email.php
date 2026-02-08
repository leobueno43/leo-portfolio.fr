<?php
/**
 * Système d'envoi d'emails pour les billets
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/email_config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class TicketEmailer {
    private $mailer;
    
    public function __construct() {
        $this->mailer = new PHPMailer(true);
        $this->configureMailer();
    }
    
    /**
     * Configure le mailer avec les paramètres SMTP
     */
    private function configureMailer() {
        $this->mailer->isSMTP();
        $this->mailer->Host = SMTP_HOST;
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = SMTP_USERNAME;
        $this->mailer->Password = SMTP_PASSWORD;
        $this->mailer->SMTPSecure = SMTP_SECURE;
        $this->mailer->Port = SMTP_PORT;
        $this->mailer->CharSet = 'UTF-8';
        
        $this->mailer->setFrom(FROM_EMAIL, FROM_NAME);
        $this->mailer->addReplyTo(REPLY_TO_EMAIL, REPLY_TO_NAME);
    }
    
    /**
     * Envoie le billet par email
     */
    public function sendTicket($ticketData) {
        try {
            // Réinitialiser le destinataire
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
            
            // Destinataire
            $this->mailer->addAddress($ticketData['user_email'], $ticketData['user_name']);
            
            // Sujet
            $this->mailer->Subject = 'Votre billet pour ' . $ticketData['event_title'];
            
            // Corps du message en HTML
            $this->mailer->isHTML(true);
            $this->mailer->Body = $this->getEmailTemplate($ticketData);
            
            // Version texte
            $this->mailer->AltBody = $this->getTextTemplate($ticketData);
            
            // Joindre le PDF du billet
            $pdfFullPath = __DIR__ . '/../' . $ticketData['pdf_path'];
            
            if (file_exists($pdfFullPath)) {
                $this->mailer->addAttachment($pdfFullPath, 'billet.pdf');
            } else {
                // Log l'erreur mais continue l'envoi
                error_log("PDF non trouvé: " . $pdfFullPath);
            }
            
            // Envoyer
            $this->mailer->send();
            
            return [
                'success' => true,
                'message' => 'Email envoyé avec succès'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Erreur lors de l\'envoi: ' . $this->mailer->ErrorInfo
            ];
        }
    }
    
    /**
     * Template HTML pour l'email
     */
    private function getEmailTemplate($data) {
        $eventDate = date('d/m/Y à H:i', strtotime($data['event_date']));
        
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .header h1 { margin: 0; font-size: 28px; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .ticket-box { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #667eea; }
                .ticket-code { background: #667eea; color: white; padding: 15px; text-align: center; font-size: 20px; font-weight: bold; border-radius: 5px; letter-spacing: 2px; margin: 15px 0; }
                .info-row { display: flex; padding: 10px 0; border-bottom: 1px solid #eee; }
                .info-label { font-weight: bold; width: 120px; color: #666; }
                .info-value { color: #333; }
                .button { display: inline-block; background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; padding: 20px; color: #999; font-size: 12px; }
                .warning { background: #fff3cd; border: 1px solid #ffc107; color: #856404; padding: 15px; border-radius: 5px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🎫 Votre Billet</h1>
                </div>
                <div class="content">
                    <p>Bonjour <strong>' . htmlspecialchars($data['user_name']) . '</strong>,</p>
                    
                    <p>Votre inscription a été confirmée ! Vous trouverez ci-joint votre billet pour l\'événement suivant :</p>
                    
                    <div class="ticket-box">
                        <h2 style="margin-top: 0; color: #667eea;">' . htmlspecialchars($data['event_title']) . '</h2>
                        
                        <div class="info-row">
                            <div class="info-label">📅 Date :</div>
                            <div class="info-value">' . $eventDate . '</div>
                        </div>
                        
                        <div class="info-row">
                            <div class="info-label">📍 Lieu :</div>
                            <div class="info-value">' . htmlspecialchars($data['event_location']) . '</div>
                        </div>
                        
                        <div class="info-row">
                            <div class="info-label">👤 Participant :</div>
                            <div class="info-value">' . htmlspecialchars($data['user_name']) . '</div>
                        </div>
                        
                        <div class="ticket-code">
                            ' . htmlspecialchars($data['ticket_code']) . '
                        </div>
                    </div>
                    
                    <div class="warning">
                        <strong>⚠️ Important :</strong><br>
                        • Présentez ce billet (version PDF ou imprimée) à l\'entrée<br>
                        • Le QR code sera scanné pour valider votre accès<br>
                        • Conservez ce billet jusqu\'à la fin de l\'événement<br>
                        • En cas de problème, contactez-nous avec votre code billet
                    </div>
                    
                    <p style="text-align: center;">
                        <strong>📱 Le billet est également disponible en pièce jointe de cet email</strong>
                    </p>
                    
                    <p>À très bientôt !</p>
                    <p><strong>L\'équipe MAT</strong></p>
                </div>
                <div class="footer">
                    <p>© ' . date('Y') . ' MAT - Tous droits réservés</p>
                    <p style="font-size: 10px;">Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
                </div>
            </div>
        </body>
        </html>
        ';
    }
    
    /**
     * Template texte pour l'email (version sans HTML)
     */
    private function getTextTemplate($data) {
        $eventDate = date('d/m/Y à H:i', strtotime($data['event_date']));
        
        return "
🎫 VOTRE BILLET - " . $data['event_title'] . "

Bonjour " . $data['user_name'] . ",

Votre inscription a été confirmée !

INFORMATIONS DE L'ÉVÉNEMENT :
━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📅 Date : " . $eventDate . "
📍 Lieu : " . $data['event_location'] . "
👤 Participant : " . $data['user_name'] . "

CODE DU BILLET :
━━━━━━━━━━━━━━━━━━━━━━━━━━━━
" . $data['ticket_code'] . "

⚠️ IMPORTANT :
• Présentez ce billet (PDF joint) à l'entrée
• Le QR code sera scanné pour valider votre accès
• Conservez ce billet jusqu'à la fin de l'événement

À très bientôt !
L'équipe MAT

━━━━━━━━━━━━━━━━━━━━━━━━━━━━
© " . date('Y') . " MAT - Tous droits réservés
        ";
    }
    
    /**
     * Envoie un email de confirmation de scan
     */
    public function sendScanConfirmation($ticketData) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
            
            $this->mailer->addAddress($ticketData['user_email'], $ticketData['user_name']);
            $this->mailer->Subject = '✅ Entrée validée - ' . $ticketData['event_title'];
            
            $scannedTime = date('d/m/Y à H:i', strtotime($ticketData['scanned_at']));
            
            $this->mailer->isHTML(true);
            $this->mailer->Body = '
            <div style="font-family: Arial; max-width: 600px; margin: 0 auto; padding: 20px;">
                <h2 style="color: #28a745;">✅ Entrée Validée</h2>
                <p>Bonjour <strong>' . htmlspecialchars($ticketData['user_name']) . '</strong>,</p>
                <p>Votre billet a été scanné avec succès !</p>
                <div style="background: #f0f8f0; padding: 15px; border-radius: 5px; margin: 20px 0;">
                    <p><strong>Événement :</strong> ' . htmlspecialchars($ticketData['event_title']) . '</p>
                    <p><strong>Code billet :</strong> ' . htmlspecialchars($ticketData['ticket_code']) . '</p>
                    <p><strong>Scanné le :</strong> ' . $scannedTime . '</p>
                </div>
                <p>Profitez bien de l\'événement ! 🎉</p>
                <p>L\'équipe MAT</p>
            </div>
            ';
            
            $this->mailer->send();
            
            return ['success' => true];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $this->mailer->ErrorInfo];
        }
    }
}
