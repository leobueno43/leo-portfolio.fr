<?php
// pages/contact.php - Page de contact
// ============================================

session_start();
require_once '../config.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'Tous les champs sont obligatoires';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email invalide';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, subject, message, created_at) 
                                   VALUES (?, ?, ?, ?, NOW())");
            if ($stmt->execute([$name, $email, $subject, $message])) {
                $success = 'Votre message a été envoyé avec succès ! Nous vous répondrons dans les plus brefs délais.';
                
                // Réinitialiser les champs
                $name = $email = $subject = $message = '';
            } else {
                $error = 'Erreur lors de l\'envoi du message';
            }
        } catch (PDOException $e) {
            $error = 'Erreur lors de l\'envoi du message';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - KIND WOLF</title>
    <meta name="description" content="Contactez-nous pour toute question ou demande d'information">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/style.css">
</head>
<body>
    <?php include '../header.php'; ?>
    
    <!-- Hero Section -->
    <section class="contact-hero">
        <div class="container">
            <h1>Contactez-nous</h1>
            <p class="hero-subtitle">Nous sommes là pour vous aider</p>
        </div>
    </section>
    
    <div class="contact-container container">
        <div class="contact-grid">
            <!-- Formulaire de contact -->
            <div class="contact-form-section">
                <h2>📧 Envoyez-nous un message</h2>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <form method="POST" class="contact-form">
                    <div class="form-group">
                        <label for="name">Nom complet *</label>
                        <input type="text" id="name" name="name" 
                               value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" 
                               value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="subject">Sujet *</label>
                        <select id="subject" name="subject" required>
                            <option value="">Sélectionnez un sujet</option>
                            <option value="Commande" <?php echo (isset($subject) && $subject === 'Commande') ? 'selected' : ''; ?>>Question sur une commande</option>
                            <option value="Produit" <?php echo (isset($subject) && $subject === 'Produit') ? 'selected' : ''; ?>>Question sur un produit</option>
                            <option value="Livraison" <?php echo (isset($subject) && $subject === 'Livraison') ? 'selected' : ''; ?>>Livraison</option>
                            <option value="Retour" <?php echo (isset($subject) && $subject === 'Retour') ? 'selected' : ''; ?>>Retour / Échange</option>
                            <option value="Partenariat" <?php echo (isset($subject) && $subject === 'Partenariat') ? 'selected' : ''; ?>>Partenariat</option>
                            <option value="Autre" <?php echo (isset($subject) && $subject === 'Autre') ? 'selected' : ''; ?>>Autre</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Message *</label>
                        <textarea id="message" name="message" rows="6" required><?php echo htmlspecialchars($message ?? ''); ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn-primary btn-block">Envoyer le message</button>
                </form>
            </div>
            
            <!-- Informations de contact -->
            <div class="contact-info-section">
                <h2>📍 Nos coordonnées</h2>
                
                <div class="contact-info-card">
                    <div class="info-icon">📧</div>
                    <div class="info-content">
                        <h3>Email</h3>
                        <p><a href="mailto:contact@kindwolf.com">contact@kindwolf.com</a></p>
                    </div>
                </div>
                
                <div class="contact-info-card">
                    <div class="info-icon">📞</div>
                    <div class="info-content">
                        <h3>Téléphone</h3>
                        <p><a href="tel:+33123456789">+33 1 23 45 67 89</a></p>
                        <small>Du lundi au vendredi, 9h-18h</small>
                    </div>
                </div>
                
                <div class="contact-info-card">
                    <div class="info-icon">🏢</div>
                    <div class="info-content">
                        <h3>Adresse</h3>
                        <p>123 Rue de la Mode<br>75001 Paris, France</p>
                    </div>
                </div>
                
                <div class="contact-info-card">
                    <div class="info-icon">⏰</div>
                    <div class="info-content">
                        <h3>Horaires</h3>
                        <p>Lundi - Vendredi : 9h - 18h<br>
                        Samedi : 10h - 17h<br>
                        Dimanche : Fermé</p>
                    </div>
                </div>
                
                <!-- Réseaux sociaux -->
                <div class="social-links">
                    <h3>Suivez-nous</h3>
                    <div class="social-icons">
                        <a href="#" class="social-icon" title="Facebook">📘</a>
                        <a href="#" class="social-icon" title="Instagram">📷</a>
                        <a href="#" class="social-icon" title="Twitter">🐦</a>
                        <a href="#" class="social-icon" title="Pinterest">📌</a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- FAQ Section -->
        <section class="contact-faq">
            <h2 class="text-center">❓ Questions fréquentes</h2>
            <div class="faq-grid">
                <div class="faq-item">
                    <h3>Quels sont vos délais de livraison ?</h3>
                    <p>Nous livrons sous 2-5 jours ouvrés en France métropolitaine. Pour les autres pays, comptez 5-10 jours ouvrés.</p>
                </div>
                <div class="faq-item">
                    <h3>Comment puis-je retourner un article ?</h3>
                    <p>Vous disposez de 30 jours pour retourner un article dans son état d'origine. Consultez notre page "Retours" pour plus d'informations.</p>
                </div>
                <div class="faq-item">
                    <h3>Les frais de livraison sont-ils gratuits ?</h3>
                    <p>Oui, la livraison est gratuite pour toute commande supérieure à 50€ en France métropolitaine.</p>
                </div>
                <div class="faq-item">
                    <h3>Comment suivre ma commande ?</h3>
                    <p>Un email de confirmation avec un numéro de suivi vous sera envoyé dès l'expédition de votre commande.</p>
                </div>
            </div>
        </section>
    </div>
    
    <?php include '../footer.php'; ?>
    <script src="<?php echo BASE_URL; ?>/script.js"></script>
</body>
</html>