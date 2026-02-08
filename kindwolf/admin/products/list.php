<!-- admin/products/list.php - Liste produits CORRIGÉ -->
<?php
session_start();
require_once '../../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../../login.php');
    exit;
}

// Filtres et pagination
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

$where = "WHERE active = 1"; // N'afficher que les produits actifs
$params = [];

if ($search) {
    $where .= " AND (name LIKE ? OR sku LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category) {
    $where .= " AND category = ?";
    $params[] = $category;
}

// Total produits
$stmt = $pdo->prepare("SELECT COUNT(*) FROM products $where");
$stmt->execute($params);
$total = $stmt->fetchColumn();
$total_pages = ceil($total / $per_page);

// Récupérer les produits
$sql = "SELECT * FROM products $where ORDER BY created_at DESC LIMIT $per_page OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Catégories pour le filtre (uniquement des produits actifs)
$categories = $pdo->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND active = 1")->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Produits - KIND WOLF Admin</title>
    <link rel="stylesheet" href="../../style.css">
</head>
<body>
    <div class="admin-container">
        <?php include '../admin_sidebar.php'; ?>
        
        <div class="admin-main">
            <div class="admin-header">
                <h1>Gestion des Produits</h1>
                <a href="add.php" class="btn-primary">+ Ajouter un produit</a>
            </div>

            <!-- Filtres -->
            <div class="admin-filters">
                <form method="GET" class="filter-form">
                    <input type="text" name="search" placeholder="Rechercher..." value="<?php echo htmlspecialchars($search); ?>">
                    <select name="category">
                        <option value="">Toutes les catégories</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $category === $cat ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn-secondary">Filtrer</button>
                    <a href="list.php" class="btn-outline">Réinitialiser</a>
                </form>
            </div>

            <!-- Tableau des produits -->
            <div class="admin-section">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Nom</th>
                            <th>SKU</th>
                            <th>Prix</th>
                            <th>Stock</th>
                            <th>Catégorie</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                        <tr>
                            <td>
                                <!-- LIGNE CORRIGÉE ICI -->
                                <?php if (!empty($product['image'])): ?>
                                    <img src="<?php echo BASE_URL . '/' . htmlspecialchars($product['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                         style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                <?php else: ?>
                                    <div style="width: 50px; height: 50px; background: #f0f0f0; border-radius: 5px; display: flex; align-items: center; justify-content: center;">
                                        📷
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                <?php if ($product['featured']): ?>
                                    <span class="badge badge-featured">Vedette</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($product['sku'] ?? '-'); ?></td>
                            <td><?php echo number_format($product['price'], 2); ?> €</td>
                            <td>
                                <span class="stock-badge <?php echo $product['stock'] > 0 ? 'stock-in' : 'stock-out'; ?>">
                                    <?php echo $product['stock']; ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($product['category'] ?? '-'); ?></td>
                            <td>
                                <span class="status <?php echo $product['active'] ? 'status-active' : 'status-inactive'; ?>">
                                    <?php echo $product['active'] ? 'Actif' : 'Inactif'; ?>
                                </span>
                            </td>
                            <td class="actions">
                                <a href="edit.php?id=<?php echo $product['id']; ?>" class="btn-icon" title="Modifier">✏️</a>
                                <a href="delete.php?id=<?php echo $product['id']; ?>" 
                                   onclick="return confirm('Supprimer ce produit ?')" 
                                   class="btn-icon" title="Supprimer">🗑️</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>" 
                           class="<?php echo $i === $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>