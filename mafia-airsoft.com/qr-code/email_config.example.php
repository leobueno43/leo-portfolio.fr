<?php
/**
 * Configuration pour l'envoi d'emails
 * 
 * INSTRUCTIONS POUR LE DÉPLOIEMENT :
 * 1. Copiez ce fichier et renommez-le en "email_config.php"
 * 2. Modifiez les valeurs ci-dessous avec vos paramètres SMTP
 * 3. NE COMMITEZ JAMAIS le fichier email_config.php dans Git (déjà dans .gitignore)
 * 
 * POUR GMAIL :
 * - Activez la validation en 2 étapes sur votre compte Google
 * - Générez un "Mot de passe d'application" : https://myaccount.google.com/apppasswords
 * - Utilisez ce mot de passe dans SMTP_PASSWORD
 */

// ========================================
// Configuration SMTP
// ========================================
define('SMTP_HOST', 'smtp.gmail.com');          // Serveur SMTP (Gmail, Office365, etc.)
define('SMTP_PORT', 587);                       // Port SMTP (587 pour TLS, 465 pour SSL)
define('SMTP_USERNAME', 'votre-email@gmail.com'); // Votre adresse email complète
define('SMTP_PASSWORD', 'votre-mot-de-passe-app'); // Mot de passe d'application (16 caractères)
define('SMTP_SECURE', 'tls');                   // 'tls' ou 'ssl'

// ========================================
// Email d'expédition (affiché dans "De:")
// ========================================
define('FROM_EMAIL', 'noreply@votre-domaine.com');
define('FROM_NAME', 'MAT - Billetterie');

// ========================================
// Email de réponse (affiché dans "Répondre à:")
// ========================================
define('REPLY_TO_EMAIL', 'contact@votre-domaine.com');
define('REPLY_TO_NAME', 'MAT Support');

// ========================================
// Configuration des billets
// ========================================
define('TICKET_VALIDITY_HOURS', 24);  // Validité du billet en heures avant l'événement
define('QR_CODE_SIZE', 300);          // Taille du QR code en pixels

?>
