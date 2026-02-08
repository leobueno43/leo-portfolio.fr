<!-- user/compte.php - Espace client -->
<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Récupérer infos utilisateur
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Statistiques
$total_orders = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
$total_orders->execute([$user_id]);
$total_orders = $total_orders->fetchColumn();

$total_spent = $pdo->prepare("SELECT SUM(total) FROM orders WHERE user_id = ? AND status = 'completed'");
$total_spent->execute([$user_id]);
$total_spent = $total_spent->fetchColumn() ?? 0;

// Dernières commandes
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$user_id]);
$recent_orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon compte - KIND WOLF</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <?php include '../header.php'; ?>
    
    <div class="page-header">
        <h1>Mon compte</h1>
        <p>Bonjour, <?php echo htmlspecialchars($user['name']); ?> !</p>
    </div>

    <div class="account-container container">
        <aside class="account-sidebar">
            <nav class="account-menu">
                <a href="compte.php" class="active">Tableau de bord</a>
                <a href="commandes.php">Mes commandes</a>
                <a href="profil.php">Mon profil</a>
                <a href="adresses.php">Mes adresses</a>
                <a href="../logout.php">Déconnexion</a>
            </nav>
        </aside>

        <div class="account-main">
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Commandes</h3>
                    <p class="stat-number"><?php echo $total_orders; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total dépensé</h3>
                    <p class="stat-number"><?php echo number_format($total_spent, 2); ?> €</p>
                </div>
            </div>

            <section class="account-section">
                <h2>Commandes récentes</h2>
                <?php if (empty($recent_orders)): ?>
                    <p>Vous n'avez pas encore passé de commande.</p>
                    <a href="../boutique.php" class="btn-primary">Découvrir nos produits</a>
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
                                <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></td>
                                <td><?php echo number_format($order['total'], 2); ?> €</td>
                                <td><span class="status status-<?php echo $order['status']; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span></td>
                                <td><a href="order-detail.php?id=<?php echo $order['id']; ?>">Voir</a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </section>
        </div>
    </div>

    <?php include '../footer.php'; ?>
</body>
</html>