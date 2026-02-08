<!-- admin/admin_header.php - En-tête admin -->
<!-- ============================================ -->
<header class="admin-header">
    <div class="admin-header-content">
        <div class="admin-brand">
            <span class="logo-icon">🐺</span>
            <span>KIND WOLF Admin</span>
        </div>
        <div class="admin-user">
            <span>👤 <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></span>
            <a href="/auth/logout.php" class="btn-outline-small">Déconnexion</a>
        </div>
    </div>
</header>