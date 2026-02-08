<?php
// admin/newsletter/export.php
session_start();
require_once '../../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

$format = $_GET['format'] ?? 'csv';
$filter = $_GET['filter'] ?? 'active';

// Récupérer les emails
$where = $filter === 'active' ? 'WHERE active = 1' : '';
$stmt = $pdo->query("SELECT email FROM newsletter_subscribers $where ORDER BY email");
$subscribers = $stmt->fetchAll(PDO::FETCH_COLUMN);

if ($format === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="newsletter_subscribers_' . date('Y-m-d') . '.csv"');
    
    echo "Email\n";
    foreach ($subscribers as $email) {
        echo $email . "\n";
    }
} else {
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="newsletter_subscribers_' . date('Y-m-d') . '.txt"');
    
    foreach ($subscribers as $email) {
        echo $email . "\n";
    }
}
exit;
