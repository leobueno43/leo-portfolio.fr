<?php
// pages/unsubscribe.php - Désinscription newsletter
session_start();
require_once '../config.php';

$token = $_GET['token'] ?? '';
$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($token)) {
    $stmt = $pdo->prepare("UPDATE newsletter_subscribers SET active = 0, unsubscribed_at = NOW() WHERE token = ?");
    
    if ($stmt->execute([$token]) && $stmt->rowCount() > 0) {
        $success = true;
        $message = 'Vous avez été désinscrit de notre newsletter avec succès.';
    } else {
        $message = 'Lien invalide ou déjà désinscrit.';
    }
} elseif (!empty($token)) {
    // Vérifier que le token existe
    $stmt = $pdo->prepare("SELECT email, active FROM newsletter_subscribers WHERE token = ?");
    $stmt->execute([$token]);
    $subscriber = $stmt->fetch();
    
    if (!$subscriber) {
        $message = 'Lien invalide.';
        $token = '';
    } elseif ($subscriber['active'] == 0) {
        $message = 'Vous êtes déjà désinscrit de notre newsletter.';
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Se désinscrire - Newsletter KIND WOLF</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/style.css">
</head>
<body>
    <?php include '../header.php'; ?>
    
    <div class="container" style="padding: 4rem 0; min-height: 60vh;">
        <div class="unsubscribe-page" style="max-width: 600px; margin: 0 auto; text-align: center;">
            <?php if ($message): ?>
                <div class="alert <?php echo $success ? 'alert-success' : 'alert-error'; ?>" style="margin-bottom: 2rem;">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!$message && $token): ?>
                <div class="unsubscribe-form">
                    <h1>📧 Se désinscrire de la newsletter</h1>
                    <p>Nous sommes désolés de vous voir partir...</p>
                    <p><strong>Email :</strong> <?php echo htmlspecialchars($subscriber['email'] ?? ''); ?></p>
                    
                    <form method="POST" style="margin-top: 2rem;">
                        <button type="submit" class="btn-primary" style="background: var(--deep-red);">
                            Confirmer la désinscription
                        </button>
                        <br><br>
                        <a href="<?php echo BASE_URL; ?>" class="btn-outline">Retour au site</a>
                    </form>
                </div>
            <?php elseif ($success): ?>
                <p style="margin-top: 2rem;">
                    <a href="<?php echo BASE_URL; ?>" class="btn-primary">Retour au site</a>
                </p>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include '../footer.php'; ?>
</body>
</html>
