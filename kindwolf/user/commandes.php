<?php
// user/commandes.php - Liste des commandes client
// ============================================

session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Récupérer le total de commandes payées uniquement
$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ? AND payment_status = 'succeeded'");
$stmt->execute([$user_id]);
$total = $stmt->fetchColumn();
$total_pages = ceil($total / $per_page);

// Récupérer les commandes payées uniquement
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? AND payment_status = 'succeeded' ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt->execute([$user_id, $per_page, $offset]);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes commandes - KIND WOLF</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/style.css">
</head>
<body>
    <?php include '../header.php'; ?>
    
    <div class="page-header">
        <h1>Mes commandes</h1>
        <p>Historique de vos achats</p>
    </div>

    <div class="account-container container">
        <aside class="account-sidebar">
            <nav class="account-menu">
                <a href="<?php echo BASE_URL; ?>/user/compte.php">
                    📊 Tableau de bord
                </a>
                <a href="<?php echo BASE_URL; ?>/user/commandes.php" class="active">
                    📦 Mes commandes
                </a>
                <a href="<?php echo BASE_URL; ?>/user/profil.php">
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
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php 
                    echo htmlspecialchars($_SESSION['success']); 
                    unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <?php 
                    echo htmlspecialchars($_SESSION['error']); 
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>
            
            <section class="account-section">
                <h2>📦 Toutes mes commandes (<?php echo $total; ?>)</h2>
                
                <?php if (empty($orders)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">📦</div>
                        <h3>Aucune commande</h3>
                        <p>Vous n'avez pas encore passé de commande.</p>
                        <a href="<?php echo BASE_URL; ?>/pages/boutique.php" class="btn-primary">
                            Découvrir nos produits
                        </a>
                    </div>
                <?php else: ?>
                    <div class="orders-list">
                        <?php foreach ($orders as $order): ?>
                        <div class="order-card">
                            <div class="order-header">
                                <div class="order-info">
                                    <h3>Commande #<?php echo htmlspecialchars($order['order_number']); ?></h3>
                                    <p class="order-date">
                                        📅 <?php echo date('d/m/Y à H:i', strtotime($order['created_at'])); ?>
                                    </p>
                                </div>
                                <div class="order-status">
                                    <span class="status status-<?php echo $order['status']; ?>">
                                        <?php 
                                        $statuses = [
                                            'pending' => '⏳ En attente',
                                            'processing' => '⚙️ En cours',
                                            'shipped' => '🚚 Expédiée',
                                            'completed' => '✅ Terminée',
                                            'cancelled' => '❌ Annulée'
                                        ];
                                        echo $statuses[$order['status']] ?? ucfirst($order['status']); 
                                        ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="order-body">
                                <div class="order-details">
                                    <div class="detail-item">
                                        <span class="detail-label">Total</span>
                                        <span class="detail-value"><?php echo number_format($order['total'], 2); ?> €</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Paiement</span>
                                        <span class="detail-value">
                                            <?php 
                                            $methods = [
                                                'card' => '💳 Carte bancaire',
                                                'paypal' => '🅿️ PayPal',
                                                'bank_transfer' => '🏦 Virement'
                                            ];
                                            echo $methods[$order['payment_method']] ?? $order['payment_method']; 
                                            ?>
                                        </span>
                                    </div>
                                    <?php if ($order['tracking_number']): ?>
                                    <div class="detail-item">
                                        <span class="detail-label">Suivi</span>
                                        <span class="detail-value"><?php echo htmlspecialchars($order['tracking_number']); ?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="order-footer">
                                <a href="<?php echo BASE_URL; ?>/user/order-detail.php?id=<?php echo $order['id']; ?>" 
                                   class="btn-primary">
                                    Voir les détails
                                </a>
                                <?php if (in_array($order['status'], ['completed', 'shipped'])): ?>
                                    <a href="<?php echo BASE_URL; ?>/user/order-invoice.php?id=<?php echo $order['id']; ?>" 
                                       class="btn-outline" target="_blank">
                                        📄 Facture
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>" class="pagination-prev">← Précédent</a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>" 
                               class="pagination-link <?php echo $i === $page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?>" class="pagination-next">Suivant →</a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </section>
        </div>
    </div>

    <?php include '../footer.php'; ?>
    <script src="<?php echo BASE_URL; ?>/script.js"></script>
</body>
</html>