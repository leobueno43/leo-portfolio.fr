<!-- footer.php - Pied de page CORRIGÉ -->
<!-- ============================================ -->
<?php
// Définir la base URL si elle n'existe pas
if (!defined('BASE_URL')) {
    define('BASE_URL', '/kindwolf');
}
?>
<footer class="main-footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <h3>KIND WOLF</h3>
                <p>Collection inspirée de "Le Mal Aimée" - Célébrant la nature et l'esprit du loup</p>
            </div>
            <div class="footer-section">
                <h4>Navigation</h4>
                <ul>
                    <li><a href="<?php echo BASE_URL; ?>/pages/boutique.php">Boutique</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/pages/about.php">À propos</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/pages/contact.php">Contact</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/pages/cgv.php">CGV</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Mon Compte</h4>
                <ul>
                    <li><a href="<?php echo BASE_URL; ?>/auth/login.php">Connexion</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/auth/register.php">Inscription</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/user/compte.php">Mes commandes</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Contact</h4>
                <p>Email: contact@kindwolf.com</p>
                <p>Suivez-nous sur les réseaux sociaux</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> KIND WOLF. Tous droits réservés.</p>
        </div>
    </div>
</footer>