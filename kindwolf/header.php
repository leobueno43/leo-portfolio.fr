<!-- header.php - En-tête du site CORRIGÉ -->
<!-- ============================================ -->
<?php
// Définir la base URL si elle n'existe pas
if (!defined('BASE_URL')) {
    define('BASE_URL', '/kindwolf');
}
?>
<script>
// Variable JavaScript globale pour BASE_URL
const BASE_URL = '<?php echo BASE_URL; ?>';
</script>
<header class="main-header">
    <div class="container">
        <nav class="navbar">
            <div class="logo">
                <a href="<?php echo BASE_URL; ?>/index.php">
                    <span class="logo-text">KIND WOLF</span>
                </a>
            </div>
            <ul class="nav-menu">
                <li><a href="<?php echo BASE_URL; ?>/index.php">Accueil</a></li>
                <li><a href="<?php echo BASE_URL; ?>/pages/boutique.php">Boutique</a></li>
                <li><a href="<?php echo BASE_URL; ?>/pages/about.php">À propos</a></li>
                <li><a href="<?php echo BASE_URL; ?>/pages/contact.php">Contact</a></li>
            </ul>
            <div class="nav-actions">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                        <a href="<?php echo BASE_URL; ?>/admin/dashboard.php" class="nav-icon" title="Administration">
                            <span class="icon">⚙️</span>
                        </a>
                    <?php endif; ?>
                    <a href="<?php echo BASE_URL; ?>/user/compte.php" class="nav-icon" title="Mon compte">
                        <span class="icon">👤</span>
                    </a>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>/auth/login.php" class="nav-icon" title="Connexion">
                        <span class="icon">👤</span>
                    </a>
                <?php endif; ?>
                <a href="<?php echo BASE_URL; ?>/pages/panier.php" class="nav-icon cart-icon" title="Panier">
                    <span class="icon">🛒</span>
                    <span class="cart-count" id="cartCount">0</span>
                </a>
            </div>
            <button class="mobile-menu-toggle">☰</button>
        </nav>
    </div>
</header>