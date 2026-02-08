<?php
// admin/reviews/view.php - Voir le détail d'un avis
session_start();
require_once '../../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

$review_id = (int)($_GET['id'] ?? 0);

// Récupérer l'avis
$stmt = $pdo->prepare("SELECT r.*, u.name as user_name, u.email as user_email, 
                              p.name as product_name, p.id as product_id
                       FROM reviews r 
                       JOIN users u ON r.user_id = u.id 
                       JOIN products p ON r.product_id = p.id 
                       WHERE r.id = ?");
$stmt->execute([$review_id]);
$review = $stmt->fetch();

if (!$review) {
    header('Location: list.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détail de l'avis - Admin KIND WOLF</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/style.css">
</head>
<body>
    <div class="admin-container">
        <?php include '../admin_sidebar.php'; ?>
        
        <main class="admin-main">
            <div class="admin-section">
                <div class="section-header">
                    <h1>📝 Détail de l'avis</h1>
                    <a href="list.php" class="btn-secondary">← Retour</a>
                </div>
                
                <div class="review-detail-card">
                    <div class="review-detail-header">
                        <div>
                            <h2><?php echo htmlspecialchars($review['title'] ?? 'Avis sans titre'); ?></h2>
                            <div class="review-meta">
                                <span class="rating-stars">
                                    <?php echo str_repeat('⭐', $review['rating']); ?>
                                    <?php echo str_repeat('☆', 5 - $review['rating']); ?>
                                </span>
                                <span class="review-date">
                                    📅 <?php echo date('d/m/Y à H:i', strtotime($review['created_at'])); ?>
                                </span>
                            </div>
                        </div>
                        <div class="review-status-badge">
                            <?php if ($review['approved']): ?>
                                <span class="status-approved">✓ Approuvé</span>
                            <?php else: ?>
                                <span class="status-pending">⏳ En attente</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="review-detail-body">
                        <div class="info-grid">
                            <div class="info-item">
                                <strong>👤 Client :</strong>
                                <span><?php echo htmlspecialchars($review['user_name']); ?></span>
                            </div>
                            <div class="info-item">
                                <strong>📧 Email :</strong>
                                <span><?php echo htmlspecialchars($review['user_email']); ?></span>
                            </div>
                            <div class="info-item">
                                <strong>📦 Produit :</strong>
                                <a href="<?php echo BASE_URL; ?>/pages/produit.php?id=<?php echo $review['product_id']; ?>" target="_blank">
                                    <?php echo htmlspecialchars($review['product_name']); ?>
                                </a>
                            </div>
                            <div class="info-item">
                                <strong>✓ Achat vérifié :</strong>
                                <?php if ($review['verified_purchase']): ?>
                                    <span class="badge-success">Oui</span>
                                <?php else: ?>
                                    <span class="badge-warning">Non</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="review-comment-section">
                            <h3>Commentaire :</h3>
                            <div class="review-comment-box">
                                <?php echo nl2br(htmlspecialchars($review['comment'])); ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="review-detail-actions">
                        <?php if (!$review['approved']): ?>
                            <a href="approve.php?id=<?php echo $review['id']; ?>" class="btn-primary">
                                ✓ Approuver cet avis
                            </a>
                        <?php endif; ?>
                        <a href="delete.php?id=<?php echo $review['id']; ?>" 
                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet avis ?')" 
                           class="btn-danger">
                            🗑️ Supprimer
                        </a>
                        <a href="list.php" class="btn-outline">
                            Retour à la liste
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script src="<?php echo BASE_URL; ?>/script.js"></script>
</body>
</html>
