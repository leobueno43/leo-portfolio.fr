<?php
// index.php - Page d'accueil SIMPLE (récupère image depuis BDD)
// ============================================

session_start();
require_once 'config.php';

// Récupérer les produits en vedette
$stmt = $pdo->query("SELECT * FROM products WHERE active = 1 AND featured = 1 ORDER BY created_at DESC LIMIT 8");
$featured_products = $stmt->fetchAll();

// Récupérer les derniers produits
$stmt = $pdo->query("SELECT * FROM products WHERE active = 1 ORDER BY created_at DESC LIMIT 4");
$latest_products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KIND WOLF - Mode Durable & Éthique</title>
    <meta name="description" content="Découvrez notre collection de vêtements durables et éthiques">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <!-- Hero Section -->
    <section class="hero">
        <video autoplay muted loop playsinline preload="auto" class="hero-video" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; z-index: 0; filter: brightness(0.8);">
            <source src="<?php echo BASE_URL; ?>/images/videos/intermache.mp4" type="video/mp4">
            Votre navigateur ne supporte pas la balise vidéo.
        </video>
        <div class="hero-content">
            <h1>Bienvenue chez KIND WOLF</h1>
            <p>Mode durable et éthique pour un avenir meilleur</p>
            <a href="<?php echo BASE_URL; ?>/pages/boutique.php" class="btn-primary">Découvrir la collection</a>
        </div>
    </section>
    
    <!-- Produits en vedette -->
    <section class="featured-products">
        <div class="container">
            <h2 class="section-title">🌟 Produits en vedette</h2>
            <div class="products-grid">
                <?php foreach ($featured_products as $product): ?>
                <div class="product-card" onclick="window.location.href='<?php echo BASE_URL; ?>/pages/produit.php?id=<?php echo $product['id']; ?>'" style="cursor: pointer;">
                    <?php if ($product['stock'] < 5 && $product['stock'] > 0): ?>
                        <span class="badge badge-stock">Plus que <?php echo $product['stock']; ?> en stock</span>
                    <?php elseif ($product['stock'] == 0): ?>
                        <span class="badge badge-out">Rupture de stock</span>
                    <?php endif; ?>
                    
                    <div class="product-image">
                        <img src="<?php echo BASE_URL . '/' . htmlspecialchars($product['image']); ?>" 
                            alt="<?php echo htmlspecialchars($product['name']); ?>">
                    </div>
                    
                    <div class="product-info">
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="product-price"><?php echo number_format($product['price'], 2); ?> €</p>
                        
                        <?php if ($product['stock'] > 0): ?>
                            <button onclick="event.stopPropagation(); addToCart(<?php echo $product['id']; ?>)" class="btn-primary">
                                Ajouter au panier
                            </button>
                        <?php else: ?>
                            <button class="btn-disabled" disabled>Rupture de stock</button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    
    <!-- Nouveautés -->
    <section class="latest-products bg-cream">
        <div class="container">
            <h2 class="section-title">✨ Nouveautés</h2>
            <div class="products-grid">
                <?php foreach ($latest_products as $product): ?>
                <div class="product-card" onclick="window.location.href='<?php echo BASE_URL; ?>/pages/produit.php?id=<?php echo $product['id']; ?>'" style="cursor: pointer;">
                    <div class="product-image">
                        <img src="<?php echo BASE_URL . '/' . htmlspecialchars($product['image']); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>">
                    </div>
                    
                    <div class="product-info">
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="product-price"><?php echo number_format($product['price'], 2); ?> €</p>
                        
                        <?php if ($product['stock'] > 0): ?>
                            <button onclick="event.stopPropagation(); addToCart(<?php echo $product['id']; ?>)" class="btn-primary">
                                Ajouter au panier
                            </button>
                        <?php else: ?>
                            <button class="btn-disabled" disabled>Rupture de stock</button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center" style="margin-top: 2rem;">
                <a href="<?php echo BASE_URL; ?>/pages/boutique.php" class="btn-outline">
                    Voir toute la collection →
                </a>
            </div>
        </div>
    </section>
    
    <!-- Avantages -->
    <section class="features">
        <div class="container">
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">🚚</div>
                    <h3>Livraison gratuite</h3>
                    <p>Dès 50€ d'achat</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🔒</div>
                    <h3>Paiement sécurisé</h3>
                    <p>100% sécurisé</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">↩️</div>
                    <h3>Retours faciles</h3>
                    <p>30 jours pour changer d'avis</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🌱</div>
                    <h3>Mode durable</h3>
                    <p>Matériaux écologiques</p>
                </div>
            </div>
        </div>
    </section>
    
    <?php include 'footer.php'; ?>
    <script src="<?php echo BASE_URL; ?>/script.js"></script>
</body>
</html>