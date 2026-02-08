<?php
// admin/newsletter/delete.php
session_start();
require_once '../../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    $stmt = $pdo->prepare("DELETE FROM newsletter_subscribers WHERE id = ?");
    $stmt->execute([$id]);
}

header('Location: list.php');
exit;
