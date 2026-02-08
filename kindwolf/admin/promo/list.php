<?php
// admin/promo/list.php - Gestion des codes promo
session_start();
require_once '../../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

// Récupérer tous les codes promo
$stmt = $pdo->query("SELECT * FROM promo_codes ORDER BY created_at DESC");
$promos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Codes promo - Admin KIND WOLF</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/style.css">
</head>
<body>
    <div class="admin-container">
        <?php include '../admin_sidebar.php'; ?>
        
        <main class="admin-main">
            <div class="admin-section">
                <div class="section-header">
                    <h1>🎟️ Codes promo</h1>
                    <a href="add.php" class="btn-primary">+ Nouveau code promo</a>
                </div>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <?php if (empty($promos)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">🎟️</div>
                        <h3>Aucun code promo</h3>
                        <p>Créez votre premier code promo</p>
                        <a href="add.php" class="btn-primary">Créer un code promo</a>
                    </div>
                <?php else: ?>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Type</th>
                                <th>Valeur</th>
                                <th>Minimum</th>
                                <th>Utilisation</th>
                                <th>Expire le</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($promos as $promo): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($promo['code']); ?></strong></td>
                                <td>
                                    <?php if ($promo['discount_type'] === 'percentage'): ?>
                                        Pourcentage
                                    <?php else: ?>
                                        Montant fixe
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($promo['discount_type'] === 'percentage'): ?>
                                        <?php echo $promo['discount_percent']; ?>%
                                    <?php else: ?>
                                        <?php echo number_format($promo['discount_amount'], 2); ?> €
                                    <?php endif; ?>
                                </td>
                                <td><?php echo number_format($promo['minimum_amount'], 2); ?> €</td>
                                <td>
                                    <?php echo $promo['usage_count']; ?> / 
                                    <?php echo $promo['usage_limit'] ?: '∞'; ?>
                                </td>
                                <td>
                                    <?php if ($promo['expires_at']): ?>
                                        <?php 
                                        $expires = strtotime($promo['expires_at']);
                                        $now = time();
                                        if ($expires < $now): ?>
                                            <span class="status-expired">Expiré</span>
                                        <?php else: ?>
                                            <?php echo date('d/m/Y', $expires); ?>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        Jamais
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($promo['active']): ?>
                                        <span class="status-approved">Actif</span>
                                    <?php else: ?>
                                        <span class="status-cancelled">Inactif</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="edit.php?id=<?php echo $promo['id']; ?>" class="btn-icon" title="Modifier">✏️</a>
                                    <a href="toggle.php?id=<?php echo $promo['id']; ?>" class="btn-icon" title="Activer/Désactiver">
                                        <?php echo $promo['active'] ? '🔴' : '🟢'; ?>
                                    </a>
                                    <a href="delete.php?id=<?php echo $promo['id']; ?>" 
                                       onclick="return confirm('Supprimer ce code promo ?')" 
                                       class="btn-icon" title="Supprimer">🗑️</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <script src="<?php echo BASE_URL; ?>/script.js"></script>
</body>
</html>