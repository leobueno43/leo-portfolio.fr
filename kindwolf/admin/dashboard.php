<!-- admin/dashboard.php - Tableau de bord COMPLET -->
<!-- ============================================ -->
<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Statistiques générales (uniquement commandes payées)
$stats = [
    'total_products' => $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn(),
    'total_orders' => $pdo->query("SELECT COUNT(*) FROM orders WHERE payment_status = 'succeeded'")->fetchColumn(),
    'total_users' => $pdo->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetchColumn(),
    'total_revenue' => $pdo->query("SELECT SUM(total) FROM orders WHERE payment_status = 'succeeded' AND status IN ('completed', 'shipped')")->fetchColumn() ?? 0,
    'pending_orders' => $pdo->query("SELECT COUNT(*) FROM orders WHERE payment_status = 'succeeded' AND status='pending'")->fetchColumn(),
    'low_stock' => $pdo->query("SELECT COUNT(*) FROM products WHERE stock < 10")->fetchColumn()
];

// Commandes récentes (uniquement payées)
$recent_orders = $pdo->query("SELECT o.*, u.name as customer_name FROM orders o 
                               JOIN users u ON o.user_id = u.id 
                               WHERE o.payment_status = 'succeeded'
                               ORDER BY o.created_at DESC LIMIT 10")->fetchAll();

// Produits populaires
$popular_products = $pdo->query("SELECT p.name, COUNT(oi.id) as sales 
                                 FROM products p 
                                 JOIN order_items oi ON p.id = oi.product_id 
                                 JOIN orders o ON oi.order_id = o.id 
                                 WHERE o.status IN ('completed', 'shipped')
                                 GROUP BY p.id 
                                 ORDER BY sales DESC 
                                 LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - KIND WOLF</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'admin_sidebar.php'; ?>
        
        <div class="admin-main">
            <h1>Tableau de bord</h1>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>📦 Produits</h3>
                    <p class="stat-number"><?php echo $stats['total_products']; ?></p>
                    <?php if ($stats['low_stock'] > 0): ?>
                        <p class="stat-alert"><?php echo $stats['low_stock']; ?> en stock faible</p>
                    <?php endif; ?>
                </div>
                <div class="stat-card">
                    <h3>🛒 Commandes</h3>
                    <p class="stat-number"><?php echo $stats['total_orders']; ?></p>
                    <?php if ($stats['pending_orders'] > 0): ?>
                        <p class="stat-alert"><?php echo $stats['pending_orders']; ?> en attente</p>
                    <?php endif; ?>
                </div>
                <div class="stat-card">
                    <h3>👥 Clients</h3>
                    <p class="stat-number"><?php echo $stats['total_users']; ?></p>
                </div>
                <div class="stat-card">
                    <h3>💰 Revenus</h3>
                    <p class="stat-number"><?php echo number_format($stats['total_revenue'], 2); ?> €</p>
                </div>
            </div>
            
            <div class="dashboard-grid">
                <div class="admin-section">
                    <h2>Commandes récentes</h2>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>N°</th>
                                <th>Client</th>
                                <th>Total</th>
                                <th>Statut</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_orders as $order): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                                <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                <td><?php echo number_format($order['total'], 2); ?> €</td>
                                <td><span class="status status-<?php echo $order['status']; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span></td>
                                <td><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="admin-section">
                    <h2>Produits populaires</h2>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Produit</th>
                                <th>Ventes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($popular_products as $product): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><strong><?php echo $product['sales']; ?></strong></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>