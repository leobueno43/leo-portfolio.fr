<?php
// user/compte.php - Espace client
// ============================================

session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Récupérer infos utilisateur
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

// Statistiques (uniquement commandes payées)
$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ? AND payment_status = 'succeeded'");
$stmt->execute([$user_id]);
$total_orders = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT SUM(total) FROM orders WHERE user_id = ? AND payment_status = 'succeeded' AND status IN ('completed', 'shipped')");
$stmt->execute([$user_id]);
$total_spent = $stmt->fetchColumn() ?? 0;

// Dernières commandes (uniquement payées)
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? AND payment_status = 'succeeded' ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$user_id]);
$recent_orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon compte - KIND WOLF</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/style.css">
</head>
<body>
    <?php include '../header.php'; ?>
    
    <div class="page-header">
        <h1>Mon compte</h1>
        <p>Bonjour, <?php echo htmlspecialchars($user['name']); ?> ! 👋</p>
    </div>

    <div class="account-container container">
        <aside class="account-sidebar">
            <nav class="account-menu">
                <a href="<?php echo BASE_URL; ?>/user/compte.php" class="active">
                    📊 Tableau de bord
                </a>
                <a href="<?php echo BASE_URL; ?>/user/commandes.php">
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
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>📦 Commandes</h3>
                    <p class="stat-number"><?php echo $total_orders; ?></p>
                    <p class="stat-label">Total de commandes</p>
                </div>
                <div class="stat-card">
                    <h3>💰 Total dépensé</h3>
                    <p class="stat-number"><?php echo number_format($total_spent, 2); ?> €</p>
                    <p class="stat-label">Montant total</p>
                </div>
            </div>

            <section class="account-section">
                <div class="section-header">
                    <h2>📦 Commandes récentes</h2>
                    <a href="<?php echo BASE_URL; ?>/user/commandes.php" class="btn-outline-small">Voir tout</a>
                </div>
                
                <?php if (empty($recent_orders)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">📦</div>
                        <h3>Aucune commande pour le moment</h3>
                        <p>Découvrez notre collection et passez votre première commande !</p>
                        <a href="<?php echo BASE_URL; ?>/pages/boutique.php" class="btn-primary">
                            Découvrir nos produits
                        </a>
                    </div>
                <?php else: ?>
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>Numéro</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_orders as $order): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($order['order_number']); ?></strong></td>
                                <td><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></td>
                                <td><?php echo number_format($order['total'], 2); ?> €</td>
                                <td>
                                    <span class="status status-<?php echo $order['status']; ?>">
                                        <?php 
                                        $statuses = [
                                            'pending' => 'En attente',
                                            'processing' => 'En cours',
                                            'shipped' => 'Expédiée',
                                            'completed' => 'Terminée',
                                            'cancelled' => 'Annulée'
                                        ];
                                        echo $statuses[$order['status']] ?? ucfirst($order['status']); 
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>/user/order-detail.php?id=<?php echo $order['id']; ?>" 
                                       class="btn-icon" title="Voir les détails">👁️</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </section>
            
            <section class="account-section">
                <h2>🎯 Actions rapides</h2>
                <div class="quick-actions">
                    <a href="<?php echo BASE_URL; ?>/pages/boutique.php" class="action-card">
                        <div class="action-icon">🛍️</div>
                        <h3>Continuer mes achats</h3>
                        <p>Découvrir nos nouveaux produits</p>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/user/profil.php" class="action-card">
                        <div class="action-icon">👤</div>
                        <h3>Modifier mon profil</h3>
                        <p>Mettre à jour mes informations</p>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/user/adresses.php" class="action-card">
                        <div class="action-icon">📍</div>
                        <h3>Gérer mes adresses</h3>
                        <p>Ajouter ou modifier une adresse</p>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/pages/contact.php" class="action-card">
                        <div class="action-icon">📧</div>
                        <h3>Nous contacter</h3>
                        <p>Besoin d'aide ?</p>
                    </a>
                </div>
            </section>
        </div>
    </div>

    <?php include '../footer.php'; ?>
    <script src="<?php echo BASE_URL; ?>/script.js"></script>
</body>
</html>