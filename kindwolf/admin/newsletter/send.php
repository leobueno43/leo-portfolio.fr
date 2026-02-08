<?php
// admin/newsletter/send.php - Envoyer une newsletter
session_start();
require_once '../../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

$message = '';
$success = false;

// Récupérer les paramètres d'email depuis site_settings
$stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings 
                     WHERE setting_key LIKE 'email_%'");
$settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Configuration email (à ajuster selon votre serveur SMTP)
$smtp_host = $settings['email_smtp_host'] ?? 'smtp.gmail.com';
$smtp_port = $settings['email_smtp_port'] ?? 587;
$smtp_username = $settings['email_smtp_username'] ?? '';
$smtp_password = $settings['email_smtp_password'] ?? '';
$from_email = $settings['email_from'] ?? 'noreply@kindwolf.com';
$from_name = $settings['email_from_name'] ?? 'KIND WOLF';

// Compter les abonnés actifs
$stmt = $pdo->query("SELECT COUNT(*) FROM newsletter_subscribers WHERE active = 1");
$active_count = $stmt->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject'] ?? '');
    $html_content = $_POST['html_content'] ?? '';
    $plain_content = trim($_POST['plain_content'] ?? '');
    
    if (empty($subject) || empty($plain_content)) {
        $message = 'Le sujet et le contenu sont obligatoires';
    } else {
        // Récupérer tous les abonnés actifs
        $stmt = $pdo->query("SELECT email, token FROM newsletter_subscribers WHERE active = 1");
        $subscribers = $stmt->fetchAll();
        
        $sent_count = 0;
        $failed_count = 0;
        
        foreach ($subscribers as $subscriber) {
            $unsubscribe_link = BASE_URL . "/pages/unsubscribe.php?token=" . $subscriber['token'];
            
            // Remplacer le lien de désinscription
            $final_html = str_replace('[UNSUBSCRIBE_LINK]', $unsubscribe_link, $html_content);
            $final_plain = str_replace('[UNSUBSCRIBE_LINK]', $unsubscribe_link, $plain_content);
            
            // Envoyer via SMTP avec les paramètres configurés
            $email_result = sendEmailSMTP(
                $smtp_host, 
                $smtp_port, 
                $smtp_username, 
                $smtp_password, 
                $from_email, 
                $from_name, 
                $subscriber['email'], 
                $subject, 
                !empty($final_html) ? $final_html : $final_plain,
                !empty($final_html)
            );
            
            if ($email_result === true) {
                $sent_count++;
            } else {
                $failed_count++;
            }
        }
        
        $success = true;
        $message = "Newsletter envoyée à $sent_count abonné(s).";
        if ($failed_count > 0) {
            $message .= " $failed_count échec(s).";
        }
        
        // Enregistrer dans l'historique (optionnel - nécessite une table newsletter_campaigns)
        // $pdo->prepare("INSERT INTO newsletter_campaigns (subject, content, sent_count, sent_at) VALUES (?, ?, ?, NOW())")
        //     ->execute([$subject, $html_content, $sent_count]);
    }
}

// Fonction d'envoi SMTP
function sendEmailSMTP($host, $port, $username, $password, $from_email, $from_name, $to_email, $subject, $body, $is_html = true) {
    if (empty($host) || empty($username) || empty($password)) {
        return "Configuration SMTP manquante";
    }
    
    try {
        $smtp = fsockopen($host, $port, $errno, $errstr, 30);
        if (!$smtp) {
            return "Connexion échouée: $errstr ($errno)";
        }
        
        // Fonction helper pour lire la réponse
        $read = function() use ($smtp) {
            return fgets($smtp, 512);
        };
        
        // Fonction helper pour envoyer une commande
        $send = function($cmd) use ($smtp) {
            fputs($smtp, $cmd . "\r\n");
        };
        
        // Attendre le message de bienvenue
        $read();
        
        // EHLO
        $send("EHLO " . $host);
        while ($line = $read()) {
            if (!preg_match('/^\d{3}-/', $line)) break;
        }
        
        // STARTTLS si port 587
        if ($port == 587) {
            $send("STARTTLS");
            $read();
            stream_socket_enable_crypto($smtp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            $send("EHLO " . $host);
            while ($line = $read()) {
                if (!preg_match('/^\d{3}-/', $line)) break;
            }
        }
        
        // AUTH LOGIN
        $send("AUTH LOGIN");
        $read();
        $send(base64_encode($username));
        $read();
        $send(base64_encode($password));
        $response = $read();
        
        if (!preg_match('/^235/', $response)) {
            fclose($smtp);
            return "Authentification échouée";
        }
        
        // MAIL FROM
        $send("MAIL FROM:<$from_email>");
        $read();
        
        // RCPT TO
        $send("RCPT TO:<$to_email>");
        $read();
        
        // DATA
        $send("DATA");
        $read();
        
        // Headers et contenu
        $send("From: $from_name <$from_email>");
        $send("To: <$to_email>");
        $send("Subject: =?UTF-8?B?" . base64_encode($subject) . "?=");
        $send("MIME-Version: 1.0");
        if ($is_html) {
            $send("Content-Type: text/html; charset=UTF-8");
        } else {
            $send("Content-Type: text/plain; charset=UTF-8");
        }
        $send("Content-Transfer-Encoding: base64");
        $send("");
        
        // Corps du message en base64
        $body_base64 = chunk_split(base64_encode($body));
        $send($body_base64);
        $send(".");
        $read();
        
        // QUIT
        $send("QUIT");
        $read();
        
        fclose($smtp);
        return true;
        
    } catch (Exception $e) {
        return "Erreur: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Envoyer une Newsletter - Admin KIND WOLF</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/style.css">
</head>
<body>
    <div class="admin-container">
        <?php include '../admin_sidebar.php'; ?>
        
        <main class="admin-main">
            <div class="admin-section">
                <div class="section-header">
                    <h1>📧 Envoyer une Newsletter</h1>
                    <a href="list.php" class="btn-secondary">← Retour aux abonnés</a>
                </div>
                
                <?php if ($message): ?>
                <div class="alert <?php echo $success ? 'alert-success' : 'alert-error'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
                <?php endif; ?>
                
                <div class="newsletter-send-card">
                    <div class="info-banner">
                        <strong>📊 Abonnés actifs :</strong> <?php echo $active_count; ?>
                        <br>
                        <small>La newsletter sera envoyée à tous les abonnés actifs</small>
                    </div>
                    
                    <form method="POST" class="newsletter-form-send">
                        <div class="form-group">
                            <label for="subject">Sujet de l'email *</label>
                            <input type="text" id="subject" name="subject" required 
                                   placeholder="Ex: Nouvelles collections automne 2026">
                        </div>
                        
                        <div class="form-group">
                            <label for="plain_content">Contenu texte *</label>
                            <textarea id="plain_content" name="plain_content" rows="8" required 
                                      placeholder="Version texte de votre newsletter...&#10;&#10;Utilisez [UNSUBSCRIBE_LINK] pour le lien de désinscription"></textarea>
                            <small>Version texte de l'email (obligatoire pour la compatibilité)</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="html_content">Contenu HTML (optionnel)</label>
                            <textarea id="html_content" name="html_content" rows="12" 
                                      placeholder="<h1>Votre titre</h1>&#10;<p>Votre contenu HTML...</p>&#10;&#10;<a href='[UNSUBSCRIBE_LINK]'>Se désinscrire</a>"></textarea>
                            <small>Utilisez [UNSUBSCRIBE_LINK] pour insérer le lien de désinscription</small>
                        </div>
                        
                        <div class="form-group">
                            <label>
                                <input type="checkbox" id="confirm_send" required>
                                Je confirme vouloir envoyer cette newsletter à <?php echo $active_count; ?> abonné(s)
                            </label>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn-primary" id="send-btn">
                                📧 Envoyer la newsletter
                            </button>
                            <a href="list.php" class="btn-outline">Annuler</a>
                        </div>
                    </form>
                    
                    <div class="newsletter-tips">
                        <h3>💡 Conseils pour une bonne newsletter</h3>
                        <ul>
                            <li>Utilisez un sujet accrocheur et pertinent</li>
                            <li>Incluez toujours le lien de désinscription [UNSUBSCRIBE_LINK]</li>
                            <li>Testez d'abord en vous envoyant un email à vous-même</li>
                            <li>Évitez le spam : pas de CAPS LOCK excessif, mots comme "GRATUIT", etc.</li>
                            <li>Personnalisez le contenu et ajoutez de la valeur</li>
                        </ul>
                    </div>
                    
                    <div class="warning-box">
                        <strong>⚠️ Configuration SMTP requise</strong>
                        <p>Pour envoyer des emails, configurez les paramètres SMTP dans les <a href="../settings/site.php">paramètres du site</a></p>
                        <p><small>Actuellement configuré : <?php echo $smtp_username ? "✅ $smtp_username" : "❌ Non configuré"; ?></small></p>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script>
    document.getElementById('send-btn').addEventListener('click', function(e) {
        const confirmed = document.getElementById('confirm_send').checked;
        if (!confirmed) {
            e.preventDefault();
            alert('Veuillez confirmer l\'envoi de la newsletter');
            return false;
        }
        
        if (!confirm('Êtes-vous sûr de vouloir envoyer cette newsletter à <?php echo $active_count; ?> abonné(s) ?')) {
            e.preventDefault();
            return false;
        }
    });
    </script>
</body>
</html>
