<?php
// config.php - Configuration Base de donnees

// Configuration de la base de donnees
// define('DB_HOST', 'localhost');
// // define('DB_NAME', 'kindwolf_db');
// define('DB_USER', 'root');
// define('DB_PASS', '');

// Configuration de la base de donnees serveur
define('DB_HOST', 'localhost:3306');
define('DB_NAME', 'zelu6269_panel_kindwolf');
define('DB_USER', 'zelu6269_user_kindwolf');
define('DB_PASS', '!jwD%]SuRO*~');

// Configuration de l'URL de base
// IMPORTANT: Changez '/kindwolf' par '/' si votre site est à la racine du domaine
// ou par '/votre-dossier' si votre site est dans un sous-dossier
define('BASE_URL', '/kindwolf');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Parametres du site
define('SITE_NAME', 'KIND WOLF');
define('SITE_EMAIL', 'contact@kindwolf.com');
?>