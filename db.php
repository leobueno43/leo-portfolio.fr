<?php
// === Paramètres à modifier avec ceux de ton cPanel ===
$DB_HOST = 'localhost:3306';       // en général "localhost" sur cPanel
$DB_NAME = 'zelu6269_panel_portfolio'; // ex : monsite_portfolio
$DB_USER = 'zelu6269_user_portfolio';    // ex : monsite_admin
$DB_PASS = 'K_[y(;439p3)';     // le mot de passe MySQL

try {
    $pdo = new PDO(
        "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    // En prod on évite d'afficher l'erreur exacte
    die("Erreur de connexion à la base de données.");
}
?>