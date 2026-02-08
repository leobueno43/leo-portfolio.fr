<!-- auth/forgot-password.php - Mot de passe oublié -->
<!-- ============================================ -->
<?php
session_start();
require_once '../config.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    
    if ($email) {
        $stmt = $pdo->prepare("SELECT id, name FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Générer un token de réinitialisation
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Sauvegarder le token (vous devrez créer une table password_resets)
            // Pour l'instant, on simule l'envoi d'email
            $success = 'Un email de réinitialisation a été envoyé à votre adresse.';
        } else {
            // Ne pas révéler si l'email existe ou non pour la sécurité
            $success = 'Un email de réinitialisation a été envoyé si cette adresse existe.';
        }
    } else {
        $error = 'Veuillez entrer une adresse email valide.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié - KIND WOLF</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <?php include '../header.php'; ?>
    
    <div class="auth-container container">
        <div class="auth-box">
            <h1>Mot de passe oublié</h1>
            <p>Entrez votre email pour recevoir un lien de réinitialisation</p>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <button type="submit" class="btn-primary btn-block">Envoyer le lien</button>
            </form>
            <p class="auth-link"><a href="../login.php">Retour à la connexion</a></p>
        </div>
    </div>

    <?php include '../footer.php'; ?>
</body>
</html>