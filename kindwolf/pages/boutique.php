<!-- pages/boutique.php - Page boutique CORRIGÉE -->
<!-- ============================================ -->
<?php
session_start();
require_once '../config.php';

// Filtres
$category = isset($_GET['category']) ? $_GET['category'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'name';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$wishlist_only = ($sort === 'wishlist');

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Construire la requête SQL
if ($wishlist_only && isset($_SESSION['user_id'])) {
    // Afficher uniquement les produits de la wishlist
    $sql = "SELECT p.* FROM products p 
            INNER JOIN wishlist w ON p.id = w.product_id 
            WHERE p.active = 1 AND w.user_id = ?";
    $params = [$_SESSION['user_id']];
} else {
    $sql = "SELECT * FROM products WHERE active = 1";
    $params = [];
}

if ($category) {
    $sql .= " AND category = ?";
    $params[] = $category;
}

if ($search) {
    $sql .= " AND (name LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Tri
switch ($sort) {
    case 'wishlist':
        $sql .= " ORDER BY p.created_at DESC";
        break;
    case 'price_asc':
        $sql .= " ORDER BY " . ($wishlist_only ? "p." : "") . "price ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY " . ($wishlist_only ? "p." : "") . "price DESC";
        break;
    case 'newest':
        $sql .= " ORDER BY " . ($wishlist_only ? "p." : "") . "created_at DESC";
        break;
    default:
        $sql .= " ORDER BY " . ($wishlist_only ? "p." : "") . "name ASC";
}

// Compter le total
if ($wishlist_only && isset($_SESSION['user_id'])) {
    $count_sql = "SELECT COUNT(*) FROM products p 
                  INNER JOIN wishlist w ON p.id = w.product_id 
                  WHERE p.active = 1 AND w.user_id = ?";
    $count_params = [$_SESSION['user_id']];
} else {
    $count_sql = str_replace('SELECT *', 'SELECT COUNT(*)', preg_replace('/ ORDER BY .+/', '', $sql));
    $count_params = $params;
}

$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($count_params);
$total_products = $count_stmt->fetchColumn();
$total_pages = ceil($total_products / $per_page);

// Récupérer les produits avec pagination
$sql .= " LIMIT $per_page OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Récupérer les catégories
$categories = $pdo->query("SELECT DISTINCT category FROM products WHERE active = 1 AND category IS NOT NULL ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boutique - KIND WOLF</title>
    <meta name="description" content="Découvrez notre collection complète de produits dérivés KIND WOLF">
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <?php include '../header.php'; ?>
    
    <div class="page-header">
        <h1>Notre Collection</h1>
        <p>Découvrez tous nos produits inspirés de la nature sauvage</p>
    </div>

    <div class="shop-container container">
        <aside class="shop-sidebar">
            <div class="filter-section">
                <h3>Rechercher</h3>
                <form method="GET" class="search-form">
                    <input type="text" name="search" placeholder="Rechercher un produit..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn-secondary">🔍</button>
                </form>
            </div>
            
            <div class="filter-section">
                <h3>Catégories</h3>
                <ul class="category-list">
                    <li>
                        <a href="boutique.php" class="<?php echo !$category ? 'active' : ''; ?>">
                            Tous les produits (<?php echo $total_products; ?>)
                        </a>
                    </li>
                    <?php foreach ($categories as $cat): ?>
                    <li>
                        <a href="boutique.php?category=<?php echo urlencode($cat); ?>" 
                           class="<?php echo $category === $cat ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($cat); ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <div class="filter-section">
                <h3>Trier par</h3>
                <form method="GET" class="sort-form">
                    <?php if ($category): ?>
                        <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
                    <?php endif; ?>
                    <?php if ($search): ?>
                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                    <?php endif; ?>
                    <select name="sort" onchange="this.form.submit()">
                        <option value="name" <?php echo $sort === 'name' ? 'selected' : ''; ?>>Nom (A-Z)</option>
                        <option value="price_asc" <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>Prix croissant</option>
                        <option value="price_desc" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>Prix décroissant</option>
                        <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Nouveautés</option>
                        <?php if (isset($_SESSION['user_id'])): ?>
                        <option value="wishlist" <?php echo $sort === 'wishlist' ? 'selected' : ''; ?>>❤️ Ma liste de souhaits</option>
                        <?php endif; ?>
                    </select>
                </form>
            </div>
        </aside>

        <div class="shop-main">
            <?php if ($search): ?>
                <div class="search-info">
                    <p>Résultats pour "<?php echo htmlspecialchars($search); ?>" : 
                       <strong><?php echo $total_products; ?></strong> produit(s) trouvé(s)
                    </p>
                </div>
            <?php endif; ?>
            
            <?php if (empty($products)): ?>
                <div class="no-products">
                    <p>Aucun produit trouvé.</p>
                    <a href="boutique.php" class="btn-primary">Voir tous les produits</a>
                </div>
            <?php else: ?>
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <img src="<?php echo BASE_URL . '/' . htmlspecialchars($product['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                                 onerror="this.src='<?php echo BASE_URL; ?>/images/products/default.jpg'">
                            <div class="product-overlay">
                                <a href="produit.php?id=<?php echo $product['id']; ?>" class="btn-secondary">Voir détails</a>
                            </div>
                            <?php if ($product['featured']): ?>
                                <span class="badge badge-featured">⭐ Vedette</span>
                            <?php endif; ?>
                            <?php if ($product['stock'] < 10 && $product['stock'] > 0): ?>
                                <span class="badge badge-stock">Stock limité</span>
                            <?php elseif ($product['stock'] == 0): ?>
                                <span class="badge badge-out">Épuisé</span>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <span class="product-category"><?php echo htmlspecialchars($product['category']); ?></span>
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="product-price"><?php echo number_format($product['price'], 2); ?> €</p>
                            <?php if ($product['stock'] > 0): ?>
                                <button onclick="event.stopPropagation(); addToCart(<?php echo $product['id']; ?>);" class="btn-add-to-cart">
                                    🛒 Ajouter au panier
                                </button>
                            <?php else: ?>
                                <button class="btn-disabled" disabled>Rupture de stock</button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&category=<?php echo urlencode($category); ?>&sort=<?php echo urlencode($sort); ?>&search=<?php echo urlencode($search); ?>" class="pagination-prev">
                            ← Précédent
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <?php if ($i == 1 || $i == $total_pages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                            <a href="?page=<?php echo $i; ?>&category=<?php echo urlencode($category); ?>&sort=<?php echo urlencode($sort); ?>&search=<?php echo urlencode($search); ?>" 
                               class="pagination-link <?php echo $i === $page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php elseif ($i == $page - 3 || $i == $page + 3): ?>
                            <span class="pagination-dots">...</span>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&category=<?php echo urlencode($category); ?>&sort=<?php echo urlencode($sort); ?>&search=<?php echo urlencode($search); ?>" class="pagination-next">
                            Suivant →
                        </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php include '../footer.php'; ?>
    <script src="../script.js"></script>
</body>
</html>