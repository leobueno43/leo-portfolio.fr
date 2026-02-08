<!-- admin/admin_sidebar.php - Menu latéral admin VERSION OPTIMALE -->
<!-- ============================================ -->
<?php
// Définir la base URL si elle n'existe pas
if (!defined('BASE_URL')) {
    define('BASE_URL', '/kindwolf');
}
?>
<aside class="admin-sidebar">
    <div class="admin-logo">
        <h2>🐺 KIND WOLF</h2>
        <p>Administration</p>
    </div>
    <nav class="admin-nav">
        <ul>
            <li>
                <a href="<?php echo BASE_URL; ?>/admin/dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">
                    📊 Tableau de bord
                </a>
            </li>
            <li>
                <a href="<?php echo BASE_URL; ?>/admin/products/list.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'products') !== false ? 'active' : ''; ?>">
                    📦 Produits
                </a>
            </li>
            <li>
                <a href="<?php echo BASE_URL; ?>/admin/orders/list.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'orders') !== false ? 'active' : ''; ?>">
                    🛒 Commandes
                </a>
            </li>
            <li>
                <a href="<?php echo BASE_URL; ?>/admin/users/list.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'users') !== false ? 'active' : ''; ?>">
                    👥 Clients
                </a>
            </li>
            <li>
                <a href="<?php echo BASE_URL; ?>/admin/reviews/list.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'reviews') !== false ? 'active' : ''; ?>">
                    ⭐ Avis
                </a>
            </li>
            <li>
                <a href="<?php echo BASE_URL; ?>/admin/promo/list.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'promo') !== false ? 'active' : ''; ?>">
                    🎫 Codes Promo
                </a>
            </li>
            <li>
                <a href="<?php echo BASE_URL; ?>/admin/newsletter/list.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'newsletter') !== false ? 'active' : ''; ?>">
                    📧 Newsletter
                </a>
            </li>
            <li>
                <a href="<?php echo BASE_URL; ?>/admin/settings/site.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'settings') !== false ? 'active' : ''; ?>">
                    ⚙️ Paramètres
                </a>
            </li>
            <li>
                <a href="<?php echo BASE_URL; ?>/index.php">
                    🏠 Retour au site
                </a>
            </li>
            <li>
                <a href="<?php echo BASE_URL; ?>/auth/logout.php">
                    🚪 Déconnexion
                </a>
            </li>
        </ul>
    </nav>
</aside>