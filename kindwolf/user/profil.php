<?php
// user/profil.php - Modifier le profil
// ============================================

session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Récupérer les infos utilisateur
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Traiter la modification du profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    
    if (empty($name) || empty($email)) {
        $error = 'Le nom et l\'email sont obligatoires';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email invalide';
    } else {
        // Vérifier si l'email est déjà utilisé par un autre compte
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user_id]);
        
        if ($stmt->fetch()) {
            $error = 'Cet email est déjà utilisé par un autre compte';
        } else {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?");
            if ($stmt->execute([$name, $email, $phone, $user_id])) {
                $_SESSION['user_name'] = $name;
                $success = 'Profil mis à jour avec succès';
                
                // Recharger les données
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch();
            } else {
                $error = 'Erreur lors de la mise à jour';
            }
        }
    }
}

// Traiter le changement de mot de passe
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'Tous les champs sont obligatoires';
    } elseif (!password_verify($current_password, $user['password'])) {
        $error = 'Mot de passe actuel incorrect';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Les nouveaux mots de passe ne correspondent pas';
    } elseif (strlen($new_password) < 6) {
        $error = 'Le mot de passe doit contenir au moins 6 caractères';
    } else {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        if ($stmt->execute([$hashed, $user_id])) {
            $success = 'Mot de passe modifié avec succès';
        } else {
            $error = 'Erreur lors de la modification du mot de passe';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon profil - KIND WOLF</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/style.css">
</head>
<body>
    <?php include '../header.php'; ?>
    
    <div class="page-header">
        <h1>Mon profil</h1>
        <p>Gérez vos informations personnelles</p>
    </div>

    <div class="account-container container">
        <aside class="account-sidebar">
            <nav class="account-menu">
                <a href="<?php echo BASE_URL; ?>/user/compte.php">
                    📊 Tableau de bord
                </a>
                <a href="<?php echo BASE_URL; ?>/user/commandes.php">
                    📦 Mes commandes
                </a>
                <a href="<?php echo BASE_URL; ?>/user/profil.php" class="active">
                    👤 Mon profil
                </a>
                <a href="<?php echo BASE_URL; ?>/user/adresses.php">
                    📍 Mes adresses
                </a>
                <a href="<?php echo BASE_URL; ?>/pages/boutique.php">
                    🛍️ Continuer mes achats
                </a>
                <a href="<?php echo BASE_URL; ?>/auth/logout.php">
                    🚪 Déconnexion
                </a>
            </nav>
        </aside>

        <div class="account-main">
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <!-- Informations personnelles -->
            <section class="account-section">
                <h2>👤 Informations personnelles</h2>
                <form method="POST" class="profile-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Nom complet *</label>
                            <input type="text" id="name" name="name" 
                                   value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Téléphone</label>
                        <input type="tel" id="phone" name="phone" 
                               value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="update_profile" class="btn-primary">
                            💾 Enregistrer les modifications
                        </button>
                    </div>
                </form>
            </section>
            
            <!-- Changement de mot de passe -->
            <section class="account-section">
                <h2>🔒 Modifier le mot de passe</h2>
                <form method="POST" class="profile-form">
                    <div class="form-group">
                        <label for="current_password">Mot de passe actuel *</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="new_password">Nouveau mot de passe *</label>
                            <input type="password" id="new_password" name="new_password" 
                                   minlength="6" required>
                            <small>Minimum 6 caractères</small>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirmer le mot de passe *</label>
                            <input type="password" id="confirm_password" name="confirm_password" 
                                   minlength="6" required>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="change_password" class="btn-primary">
                            🔑 Modifier le mot de passe
                        </button>
                    </div>
                </form>
            </section>
            
            <!-- Informations du compte -->
            <section class="account-section">
                <h2>ℹ️ Informations du compte</h2>
                <div class="info-list">
                    <div class="info-item">
                        <span class="info-label">Membre depuis</span>
                        <span class="info-value"><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Type de compte</span>
                        <span class="info-value">
                            <?php echo $user['role'] === 'admin' ? '👑 Administrateur' : '👤 Client'; ?>
                        </span>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <?php include '../footer.php'; ?>
    <script src="<?php echo BASE_URL; ?>/script.js"></script>
</body>
</html>