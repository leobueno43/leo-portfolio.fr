<!-- pages/produit.php - Page produit détaillée CORRIGÉE -->
<!-- ============================================ -->
<?php
session_start();
require_once '../config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Récupérer le produit
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND active = 1");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: boutique.php');
    exit;
}

// Incrémenter les vues
$pdo->prepare("UPDATE products SET views = views + 1 WHERE id = ?")->execute([$id]);

// Produits similaires
$similar_stmt = $pdo->prepare("SELECT * FROM products 
                                WHERE category = ? AND id != ? AND active = 1 
                                ORDER BY RAND() 
                                LIMIT 4");
$similar_stmt->execute([$product['category'], $id]);
$similar_products = $similar_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - KIND WOLF</title>
    <meta name="description" content="<?php echo htmlspecialchars(substr($product['description'], 0, 155)); ?>">
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <?php include '../header.php'; ?>
    
    <div class="product-detail container">
        <div class="product-gallery">
            <img src="<?php echo BASE_URL . '/' . htmlspecialchars($product['image']); ?>" 
                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                 class="main-image zoomable-image"
                 onerror="this.src='<?php echo BASE_URL; ?>/images/products/default.jpg'">
            
            <!-- Galerie miniatures si disponible -->
            <?php if ($product['gallery']): ?>
                <div class="thumbnail-gallery">
                    <?php 
                    $gallery = json_decode($product['gallery'], true);
                    if (is_array($gallery)):
                        foreach ($gallery as $thumb): 
                    ?>
                        <img src="<?php echo BASE_URL . '/' . htmlspecialchars($thumb); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                             onclick="changeMainImage('<?php echo BASE_URL . '/' . htmlspecialchars($thumb); ?>')">
                    <?php 
                        endforeach;
                    endif;
                    ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="product-details">
            <span class="product-category">
                <a href="boutique.php?category=<?php echo urlencode($product['category']); ?>">
                    <?php echo htmlspecialchars($product['category']); ?>
                </a>
            </span>
            
            <h1><?php echo htmlspecialchars($product['name']); ?></h1>
            
            <?php if ($product['compare_price'] && $product['compare_price'] > $product['price']): ?>
                <p class="product-price-compare">
                    <span class="old-price"><?php echo number_format($product['compare_price'], 2); ?> €</span>
                    <span class="product-price-large discount"><?php echo number_format($product['price'], 2); ?> €</span>
                    <span class="discount-badge">
                        -<?php echo round((($product['compare_price'] - $product['price']) / $product['compare_price']) * 100); ?>%
                    </span>
                </p>
            <?php else: ?>
                <p class="product-price-large"><?php echo number_format($product['price'], 2); ?> €</p>
            <?php endif; ?>
            
            <div class="product-description">
                <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            </div>
            
            <?php if ($product['long_description']): ?>
            <div class="product-long-description">
                <h3>Description détaillée</h3>
                <p><?php echo nl2br(htmlspecialchars($product['long_description'])); ?></p>
            </div>
            <?php endif; ?>
            
            <?php if ($product['stock'] > 0): ?>
                <div class="product-actions">
                    <div class="quantity-selector">
                        <button onclick="decreaseQuantity()">−</button>
                        <input type="number" id="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>">
                        <button onclick="increaseQuantity()">+</button>
                    </div>
                    <button class="btn-primary btn-add-cart" onclick="addToCart(<?php echo $product['id']; ?>)">
                        🛒 Ajouter au panier
                    </button>
                    <button class="btn-wishlist" onclick="addToWishlist(<?php echo $product['id']; ?>)" title="Ajouter à la liste de souhaits">
                        ❤️
                    </button>
                </div>
            <?php else: ?>
                <div class="product-out-of-stock">
                    <p class="out-of-stock-message">❌ Produit actuellement en rupture de stock</p>
                </div>
            <?php endif; ?>
            
            <div class="product-meta">
                <p><strong>📦 Stock:</strong> 
                    <?php if ($product['stock'] > 10): ?>
                        <span class="stock-good">En stock (<?php echo $product['stock']; ?> disponibles)</span>
                    <?php elseif ($product['stock'] > 0): ?>
                        <span class="stock-low">Stock limité (<?php echo $product['stock']; ?> restants)</span>
                    <?php else: ?>
                        <span class="stock-out">Rupture de stock</span>
                    <?php endif; ?>
                </p>
                <p><strong>🏷️ SKU:</strong> <?php echo htmlspecialchars($product['sku']); ?></p>
                <p><strong>👁️ Vues:</strong> <?php echo $product['views']; ?></p>
                <?php if ($product['featured']): ?>
                    <p><strong>⭐ Produit en vedette</strong></p>
                <?php endif; ?>
            </div>
            
            <div class="product-shipping">
                <h3>🚚 Livraison</h3>
                <ul>
                    <li>✓ Livraison gratuite dès 50€ d'achat</li>
                    <li>✓ Expédition sous 24-48h</li>
                    <li>✓ Retours gratuits sous 30 jours</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Produits similaires -->
    <?php if (!empty($similar_products)): ?>
    <section class="similar-products container">
        <h2>Produits similaires</h2>
        <div class="products-grid">
            <?php foreach ($similar_products as $similar): ?>
            <div class="product-card">
                <div class="product-image">
                    <img src="<?php echo BASE_URL . '/' . htmlspecialchars($similar['image']); ?>" 
                         alt="<?php echo htmlspecialchars($similar['name']); ?>"
                         onerror="this.src='<?php echo BASE_URL; ?>/images/products/default.jpg'">
                    <div class="product-overlay">
                        <a href="produit.php?id=<?php echo $similar['id']; ?>" class="btn-secondary">Voir détails</a>
                    </div>
                </div>
                <div class="product-info">
                    <span class="product-category"><?php echo htmlspecialchars($similar['category']); ?></span>
                    <h3><?php echo htmlspecialchars($similar['name']); ?></h3>
                    <p class="product-price"><?php echo number_format($similar['price'], 2); ?> €</p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Section Avis clients -->
    <section class="reviews-section container">
        <div class="reviews-header">
            <h2>Avis clients</h2>
            <div class="reviews-summary">
                <div class="average-rating">
                    <span class="rating-number"><?php echo number_format($product['rating'] ?? 0, 1); ?></span>
                    <div class="stars">
                        <?php 
                        $rating = $product['rating'] ?? 0;
                        for ($i = 1; $i <= 5; $i++): 
                        ?>
                            <span class="star <?php echo $i <= round($rating) ? 'filled' : ''; ?>">★</span>
                        <?php endfor; ?>
                    </div>
                    <span class="review-count">(<?php echo $product['review_count'] ?? 0; ?> avis)</span>
                </div>
            </div>
        </div>

        <?php if (isset($_SESSION['user_id'])): ?>
        <!-- Formulaire d'ajout d'avis -->
        <div class="add-review-form">
            <h3>Laissez votre avis</h3>
            <form id="reviewForm">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                
                <div class="form-group">
                    <label>Note *</label>
                    <div class="rating-input">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <input type="radio" name="rating" id="star<?php echo $i; ?>" value="<?php echo $i; ?>" required>
                            <label for="star<?php echo $i; ?>">★</label>
                        <?php endfor; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="reviewTitle">Titre *</label>
                    <input type="text" id="reviewTitle" name="title" maxlength="200" required>
                </div>

                <div class="form-group">
                    <label for="reviewComment">Votre avis *</label>
                    <textarea id="reviewComment" name="comment" rows="4" maxlength="1000" required></textarea>
                </div>

                <button type="submit" class="btn-primary">Publier mon avis</button>
            </form>
        </div>
        <?php else: ?>
        <div class="login-required">
            <p>Vous devez être <a href="../auth/login.php">connecté</a> pour laisser un avis.</p>
        </div>
        <?php endif; ?>

        <!-- Liste des avis -->
        <div id="reviewsList" class="reviews-list">
            <!-- Les avis seront chargés ici via AJAX -->
        </div>
    </section>

    <?php include '../footer.php'; ?>
    <script src="../script.js"></script>
    <script>
        // Initialiser le zoom d'image
        initImageZoom();
        
        // Charger les avis au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            loadReviews(<?php echo $product['id']; ?>);
        });

        // Gérer la soumission du formulaire d'avis
        const reviewForm = document.getElementById('reviewForm');
        if (reviewForm) {
            reviewForm.addEventListener('submit', function(e) {
                e.preventDefault();
                submitReview(this);
            });
        }

        // Fonction pour charger les avis
        function loadReviews(productId) {
            fetch('<?php echo BASE_URL; ?>/api/review_actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=get_reviews&product_id=' + productId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.reviews) {
                    displayReviews(data.reviews);
                }
            })
            .catch(error => console.error('Erreur:', error));
        }

        // Fonction pour afficher les avis
        function displayReviews(reviews) {
            const reviewsList = document.getElementById('reviewsList');
            if (reviews.length === 0) {
                reviewsList.innerHTML = '<p class="no-reviews">Aucun avis pour le moment. Soyez le premier à donner votre avis !</p>';
                return;
            }

            let html = '';
            reviews.forEach(review => {
                const stars = '★'.repeat(review.rating) + '☆'.repeat(5 - review.rating);
                const verifiedBadge = review.verified_purchase ? '<span class="verified-badge">✓ Achat vérifié</span>' : '';
                const date = new Date(review.created_at).toLocaleDateString('fr-FR');
                
                html += `
                    <div class="review-item">
                        <div class="review-header">
                            <div>
                                <div class="review-stars">${stars}</div>
                                <div class="review-author">${review.user_name} ${verifiedBadge}</div>
                            </div>
                            <div class="review-date">${date}</div>
                        </div>
                        ${review.title ? `<h4 class="review-title">${review.title}</h4>` : ''}
                        <p class="review-comment">${review.comment}</p>
                    </div>
                `;
            });
            reviewsList.innerHTML = html;
        }
    </script>
</body>
</html>