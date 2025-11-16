<?php
// Page temporaire pour générer un hash de mot de passe

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    if ($password !== '') {
        $hash = password_hash($password, PASSWORD_DEFAULT);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Générer un hash</title>
</head>
<body>
    <h1>Générateur de hash de mot de passe</h1>
    <form method="post">
        <label>Mot de passe à hasher :</label>
        <input type="text" name="password" required>
        <button type="submit">Générer</button>
    </form>

    <?php if (!empty($hash)): ?>
        <h2>Hash généré :</h2>
        <pre><?= htmlspecialchars($hash) ?></pre>
    <?php endif; ?>
</body>
</html>
