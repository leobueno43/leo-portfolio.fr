<?php
// auth/login.php - Page connexion CORRIGÉE
// ============================================

session_start();
require_once '../config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        
        // Rediriger selon le rôle
        if ($user['role'] === 'admin') {
            header('Location: ' . BASE_URL . '/admin/dashboard.php');
        } else {
            // Rediriger vers la boutique pour les clients
            header('Location: ' . BASE_URL . '/pages/boutique.php');
        }
        exit;
    } else {
        $error = 'Email ou mot de passe incorrect';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - KIND WOLF</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/style.css">
</head>
<body>
    <?php include '../header.php'; ?>
    
    <div class="auth-container container">
        <div class="auth-box">
            <h1>Connexion</h1>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn-primary btn-block">Se connecter</button>
            </form>
            <p class="auth-link">
                <a href="forgot-password.php">Mot de passe oublié ?</a>
            </p>
            <p class="auth-link">
                Pas encore de compte ? <a href="register.php">S'inscrire</a>
            </p>
        </div>
    </div>

    <?php include '../footer.php'; ?>
    <script src="<?php echo BASE_URL; ?>/script.js"></script>
</body>
</html>