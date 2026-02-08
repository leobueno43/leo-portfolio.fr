<!-- admin/users/view.php - Voir profil client -->
<!-- ============================================ -->
<?php
session_start();
require_once '../../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../../login.php');
    exit;
}

$user_id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: list.php');
    exit;
}

// Commandes du client
$orders = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$orders->execute([$user_id]);
$orders = $orders->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Client - KIND WOLF Admin</title>
    <link rel="stylesheet" href="../../style.css">
</head>
<body>
    <div class="admin-container">
        <?php include '../admin_sidebar.php'; ?>
        
        <div class="admin-main">
            <div class="admin-header">
                <h1>Profil de <?php echo htmlspecialchars($user['name']); ?></h1>
                <a href="edit.php?id=<?php echo $user['id']; ?>" class="btn-primary">Modifier</a>
            </div>
            
            <div class="admin-section">
                <h2>Informations</h2>
                <table class="info-table">
                    <tr>
                        <th>Email:</th>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                    </tr>
                    <tr>
                        <th>Téléphone:</th>
                        <td><?php echo htmlspecialchars($user['phone'] ?? '-'); ?></td>
                    </tr>
                    <tr>
                        <th>Inscription:</th>
                        <td><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></td>
                    </tr>
                </table>
            </div>
            
            <div class="admin-section">
                <h2>Commandes (<?php echo count($orders); ?>)</h2>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>N° Commande</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></td>
                            <td><?php echo number_format($order['total'], 2); ?> €</td>
                            <td><span class="status status-<?php echo $order['status']; ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>