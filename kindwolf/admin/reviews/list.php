<?php
// admin/reviews/list.php - Liste des avis
session_start();
require_once '../../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Filtres
$filter = $_GET['filter'] ?? 'all';
$search = $_GET['search'] ?? '';

// Construire la requête
$where = "WHERE 1=1";
$params = [];

if ($filter === 'pending') {
    $where .= " AND r.approved = 0";
} elseif ($filter === 'approved') {
    $where .= " AND r.approved = 1";
}

if ($search) {
    $where .= " AND (u.name LIKE ? OR p.name LIKE ? OR r.comment LIKE ?)";
    $search_param = "%$search%";
    $params = [$search_param, $search_param, $search_param];
}

// Total
$stmt = $pdo->prepare("SELECT COUNT(*) FROM reviews r 
                       JOIN users u ON r.user_id = u.id 
                       JOIN products p ON r.product_id = p.id 
                       $where");
$stmt->execute($params);
$total = $stmt->fetchColumn();
$total_pages = ceil($total / $per_page);

// Récupérer les avis
$stmt = $pdo->prepare("SELECT r.*, u.name as user_name, p.name as product_name 
                       FROM reviews r 
                       JOIN users u ON r.user_id = u.id 
                       JOIN products p ON r.product_id = p.id 
                       $where 
                       ORDER BY r.created_at DESC 
                       LIMIT ? OFFSET ?");
$stmt->execute(array_merge($params, [$per_page, $offset]));
$reviews = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des avis - Admin KIND WOLF</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/style.css">
</head>
<body>
    <div class="admin-container">
        <?php include '../admin_sidebar.php'; ?>
        
        <main class="admin-main">
            <div class="admin-section">
                <h1>📝 Gestion des avis (<?php echo $total; ?>)</h1>
                
                <!-- Filtres -->
                <div class="filters-bar">
                    <div class="search-form">
                        <form method="GET">
                            <input type="text" name="search" placeholder="Rechercher..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn-secondary">Rechercher</button>
                        </form>
                    </div>
                    
                    <div class="filter-tabs">
                        <a href="?filter=all" class="<?php echo $filter === 'all' ? 'active' : ''; ?>">
                            Tous (<?php echo $total; ?>)
                        </a>
                        <a href="?filter=pending" class="<?php echo $filter === 'pending' ? 'active' : ''; ?>">
                            En attente
                        </a>
                        <a href="?filter=approved" class="<?php echo $filter === 'approved' ? 'active' : ''; ?>">
                            Approuvés
                        </a>
                    </div>
                </div>
                
                <!-- Liste des avis -->
                <?php if (empty($reviews)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">📝</div>
                        <h3>Aucun avis</h3>
                    </div>
                <?php else: ?>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>Produit</th>
                                <th>Note</th>
                                <th>Commentaire</th>
                                <th>Achat vérifié</th>
                                <th>Date</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reviews as $review): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($review['user_name']); ?></td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>/pages/produit.php?id=<?php echo $review['product_id']; ?>" target="_blank">
                                        <?php echo htmlspecialchars($review['product_name']); ?>
                                    </a>
                                </td>
                                <td>
                                    <span class="rating-stars">
                                        <?php echo str_repeat('⭐', $review['rating']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars(substr($review['comment'], 0, 50)) . (strlen($review['comment']) > 50 ? '...' : ''); ?>
                                </td>
                                <td>
                                    <?php if ($review['verified_purchase']): ?>
                                        <span class="badge-success">✓ Vérifié</span>
                                    <?php else: ?>
                                        <span class="badge-warning">Non vérifié</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($review['created_at'])); ?></td>
                                <td>
                                    <?php if ($review['approved']): ?>
                                        <span class="status-approved">Approuvé</span>
                                    <?php else: ?>
                                        <span class="status-pending">En attente</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="view.php?id=<?php echo $review['id']; ?>" class="btn-icon" title="Voir">👁️</a>
                                    <?php if (!$review['approved']): ?>
                                        <a href="approve.php?id=<?php echo $review['id']; ?>" class="btn-icon" title="Approuver">✓</a>
                                    <?php endif; ?>
                                    <a href="delete.php?id=<?php echo $review['id']; ?>" 
                                       onclick="return confirm('Supprimer cet avis ?')" 
                                       class="btn-icon" title="Supprimer">🗑️</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>&filter=<?php echo $filter; ?>&search=<?php echo urlencode($search); ?>" 
                               class="pagination-prev">← Précédent</a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>&filter=<?php echo $filter; ?>&search=<?php echo urlencode($search); ?>" 
                               class="pagination-link <?php echo $i === $page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?>&filter=<?php echo $filter; ?>&search=<?php echo urlencode($search); ?>" 
                               class="pagination-next">Suivant →</a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <script src="<?php echo BASE_URL; ?>/script.js"></script>
</body>
</html>