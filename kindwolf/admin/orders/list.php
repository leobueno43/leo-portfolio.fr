<!-- admin/orders/list.php - Liste commandes -->
<!-- ============================================ -->
<?php
session_start();
require_once '../../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../../login.php');
    exit;
}

$status_filter = $_GET['status'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

$where = "WHERE o.payment_status = 'succeeded'";
$params = [];

if ($status_filter) {
    $where .= " AND o.status = ?";
    $params[] = $status_filter;
}

$total_stmt = $pdo->prepare("SELECT COUNT(*) FROM orders o $where");
$total_stmt->execute($params);
$total = $total_stmt->fetchColumn();
$total_pages = ceil($total / $per_page);

$stmt = $pdo->prepare("SELECT o.*, u.name as customer_name, u.email as customer_email 
                       FROM orders o 
                       JOIN users u ON o.user_id = u.id 
                       $where 
                       ORDER BY o.created_at DESC 
                       LIMIT $per_page OFFSET $offset");
$stmt->execute($params);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Commandes - KIND WOLF Admin</title>
    <link rel="stylesheet" href="../../style.css">
</head>
<body>
    <div class="admin-container">
        <?php include '../admin_sidebar.php'; ?>
        
        <div class="admin-main">
            <div class="admin-header">
                <h1>Gestion des Commandes</h1>
            </div>

            <div class="admin-filters">
                <form method="GET" class="filter-form">
                    <select name="status" onchange="this.form.submit()">
                        <option value="">Tous les statuts</option>
                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>En attente</option>
                        <option value="processing" <?php echo $status_filter === 'processing' ? 'selected' : ''; ?>>En cours</option>
                        <option value="shipped" <?php echo $status_filter === 'shipped' ? 'selected' : ''; ?>>Expédié</option>
                        <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Terminé</option>
                        <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Annulé</option>
                    </select>
                </form>
            </div>

            <div class="admin-section">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>N° Commande</th>
                            <th>Client</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Statut</th>
                            <th>Paiement</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($order['order_number']); ?></strong></td>
                            <td>
                                <?php echo htmlspecialchars($order['customer_name']); ?><br>
                                <small><?php echo htmlspecialchars($order['customer_email']); ?></small>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                            <td><strong><?php echo number_format($order['total'], 2); ?> €</strong></td>
                            <td>
                                <select onchange="updateOrderStatus(<?php echo $order['id']; ?>, this.value)" 
                                        class="status-select status-<?php echo $order['status']; ?>">
                                    <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>En attente</option>
                                    <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>En cours</option>
                                    <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Expédié</option>
                                    <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Terminé</option>
                                    <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Annulé</option>
                                </select>
                            </td>
                            <td>
                                <span class="payment-status payment-<?php echo $order['payment_status']; ?>">
                                    <?php echo ucfirst($order['payment_status']); ?>
                                </span>
                            </td>
                            <td class="actions">
                                <a href="view.php?id=<?php echo $order['id']; ?>" class="btn-icon" title="Voir">👁️</a>
                                <a href="invoice.php?id=<?php echo $order['id']; ?>" class="btn-icon" title="Facture">📄</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>&status=<?php echo urlencode($status_filter); ?>" 
                           class="<?php echo $i === $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
    function updateOrderStatus(orderId, status) {
        if (confirm('Changer le statut de cette commande ?')) {
            fetch('../../api/order_actions.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=update_status&order_id=${orderId}&status=${status}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Erreur: ' + (data.message || 'Échec de la mise à jour'));
                }
            });
        }
    }
    </script>
</body>
</html>