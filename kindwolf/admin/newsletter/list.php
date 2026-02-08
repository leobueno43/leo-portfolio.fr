<?php
// admin/newsletter/list.php - Gestion des abonnés newsletter
session_start();
require_once '../../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 50;
$offset = ($page - 1) * $per_page;

// Filtres
$filter = $_GET['filter'] ?? 'all';
$search = $_GET['search'] ?? '';

// Construire la requête
$where = "WHERE 1=1";
$params = [];

if ($filter === 'active') {
    $where .= " AND active = 1";
} elseif ($filter === 'inactive') {
    $where .= " AND active = 0";
}

if ($search) {
    $where .= " AND email LIKE ?";
    $params[] = "%$search%";
}

// Total
$stmt = $pdo->prepare("SELECT COUNT(*) FROM newsletter_subscribers $where");
$stmt->execute($params);
$total = $stmt->fetchColumn();
$total_pages = ceil($total / $per_page);

// Récupérer les abonnés
$stmt = $pdo->prepare("SELECT * FROM newsletter_subscribers 
                       $where 
                       ORDER BY subscribed_at DESC 
                       LIMIT ? OFFSET ?");
$stmt->execute(array_merge($params, [$per_page, $offset]));
$subscribers = $stmt->fetchAll();

// Stats
$stats = [
    'total' => $pdo->query("SELECT COUNT(*) FROM newsletter_subscribers")->fetchColumn(),
    'active' => $pdo->query("SELECT COUNT(*) FROM newsletter_subscribers WHERE active = 1")->fetchColumn(),
    'inactive' => $pdo->query("SELECT COUNT(*) FROM newsletter_subscribers WHERE active = 0")->fetchColumn(),
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Newsletter - Admin KIND WOLF</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/style.css">
</head>
<body>
    <div class="admin-container">
        <?php include '../admin_sidebar.php'; ?>
        
        <main class="admin-main">
            <div class="admin-section">
                <div class="section-header">
                    <h1>📧 Abonnés Newsletter</h1>
                    <a href="send.php" class="btn-primary">📨 Envoyer une newsletter</a>
                </div>
                
                <!-- Stats -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $stats['total']; ?></div>
                        <div class="stat-label">Total abonnés</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $stats['active']; ?></div>
                        <div class="stat-label">Actifs</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $stats['inactive']; ?></div>
                        <div class="stat-label">Désabonnés</div>
                    </div>
                </div>
                
                <!-- Filtres -->
                <div class="filters-bar">
                    <div class="search-form">
                        <form method="GET">
                            <input type="text" name="search" placeholder="Rechercher un email..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn-secondary">Rechercher</button>
                        </form>
                    </div>
                    
                    <div class="filter-tabs">
                        <a href="?filter=all" class="<?php echo $filter === 'all' ? 'active' : ''; ?>">
                            Tous (<?php echo $stats['total']; ?>)
                        </a>
                        <a href="?filter=active" class="<?php echo $filter === 'active' ? 'active' : ''; ?>">
                            Actifs (<?php echo $stats['active']; ?>)
                        </a>
                        <a href="?filter=inactive" class="<?php echo $filter === 'inactive' ? 'active' : ''; ?>">
                            Désabonnés (<?php echo $stats['inactive']; ?>)
                        </a>
                    </div>
                </div>
                
                <!-- Liste des abonnés -->
                <?php if (empty($subscribers)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">📧</div>
                        <h3>Aucun abonné</h3>
                        <p>Les inscriptions à la newsletter apparaîtront ici</p>
                    </div>
                <?php else: ?>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Email</th>
                                <th>Date d'inscription</th>
                                <th>Statut</th>
                                <th>Date désabonnement</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($subscribers as $subscriber): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($subscriber['email']); ?></strong></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($subscriber['subscribed_at'])); ?></td>
                                <td>
                                    <?php if ($subscriber['active']): ?>
                                        <span class="status-active">✓ Actif</span>
                                    <?php else: ?>
                                        <span class="status-inactive">✗ Désabonné</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($subscriber['unsubscribed_at']): ?>
                                        <?php echo date('d/m/Y H:i', strtotime($subscriber['unsubscribed_at'])); ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($subscriber['active']): ?>
                                        <a href="unsubscribe.php?id=<?php echo $subscriber['id']; ?>" 
                                           class="btn-sm btn-outline"
                                           onclick="return confirm('Désabonner cet email ?')">
                                            Désabonner
                                        </a>
                                    <?php else: ?>
                                        <a href="resubscribe.php?id=<?php echo $subscriber['id']; ?>" 
                                           class="btn-sm btn-primary">
                                            Réabonner
                                        </a>
                                    <?php endif; ?>
                                    <a href="delete.php?id=<?php echo $subscriber['id']; ?>" 
                                       class="btn-sm btn-danger"
                                       onclick="return confirm('Supprimer définitivement ?')">
                                        Supprimer
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>&filter=<?php echo $filter; ?>&search=<?php echo urlencode($search); ?>" 
                               class="pagination-prev">« Précédent</a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>&filter=<?php echo $filter; ?>&search=<?php echo urlencode($search); ?>" 
                               class="pagination-link <?php echo $i === $page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?>&filter=<?php echo $filter; ?>&search=<?php echo urlencode($search); ?>" 
                               class="pagination-next">Suivant »</a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Export -->
                    <div class="export-section">
                        <h3>Exporter les emails</h3>
                        <p>Téléchargez la liste des emails actifs pour vos campagnes</p>
                        <a href="export.php?format=csv&filter=active" class="btn-primary">
                            📥 Exporter en CSV
                        </a>
                        <a href="export.php?format=txt&filter=active" class="btn-outline">
                            📄 Exporter en TXT
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <script src="<?php echo BASE_URL; ?>/script.js"></script>
</body>
</html>
