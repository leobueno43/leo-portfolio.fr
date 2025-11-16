<?php
require_once __DIR__ . '/config.php';

$error = '';
$username = '';

// Si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = "Merci de remplir tous les champs.";
    } else {
        // Récupérer l'admin depuis la base
        $stmt = $pdo->prepare("SELECT id, username, password_hash FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            // Connexion OK → on stocke les infos dans la session
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];

            // Redirection vers le panel
            header('Location: panel.php');
            exit;
        } else {
            $error = "Identifiants incorrects.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - Panel</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body class="admin-body">

    <div class="card card-centered">
        <h2>Connexion au panel</h2>

        <?php if ($error): ?>
            <p class="alert-error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label for="username">Nom d'utilisateur</label>
                <input type="text"
                    id="username"
                    name="username"
                    class="form-control"
                    placeholder="Admin">
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password"
                    id="password"
                    name="password"
                    class="form-control"
                    placeholder="********">
            </div>

            <button type="submit" class="btn-primary btn-full">
                Se connecter
            </button>

            <p class="back-link">
                <a href="index.html" class="link-muted">
                    Retour au portfolio
                </a>
            </p>
        </form>
    </div>
</body>
</html>
