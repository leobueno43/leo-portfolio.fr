<?php
// pages/cgu.php - Conditions Générales d'Utilisation
session_start();
require_once '../config.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conditions Générales d'Utilisation - KIND WOLF</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/style.css">
</head>
<body>
    <?php include '../header.php'; ?>
    
    <div class="container legal-page">
        <h1>📋 Conditions Générales d'Utilisation</h1>
        <p class="last-updated">Dernière mise à jour : <?php echo date('d/m/Y'); ?></p>
        
        <section class="legal-section">
            <h2>1. Objet</h2>
            <p>Les présentes Conditions Générales d'Utilisation (CGU) ont pour objet de définir les modalités et conditions d'utilisation du site <?php echo BASE_URL; ?> ainsi que les droits et obligations des parties.</p>
        </section>
        
        <section class="legal-section">
            <h2>2. Acceptation des CGU</h2>
            <p>L'accès et l'utilisation du site impliquent l'acceptation pleine et entière des présentes CGU.</p>
            <p>KIND WOLF se réserve le droit de modifier à tout moment ces CGU. Les modifications entreront en vigueur dès leur publication sur le site.</p>
        </section>
        
        <section class="legal-section">
            <h2>3. Accès au site</h2>
            <p>Le site est accessible gratuitement à tout utilisateur disposant d'un accès à Internet.</p>
            <p>KIND WOLF met en œuvre tous les moyens raisonnables à sa disposition pour assurer un accès de qualité au site, mais n'est tenue à aucune obligation d'y parvenir.</p>
        </section>
        
        <section class="legal-section">
            <h2>4. Création de compte</h2>
            <p>Pour effectuer un achat, l'utilisateur doit créer un compte en fournissant des informations exactes et complètes.</p>
            <p>L'utilisateur est responsable de la confidentialité de ses identifiants de connexion.</p>
            <p>Toute utilisation du compte est réputée avoir été effectuée par son titulaire.</p>
        </section>
        
        <section class="legal-section">
            <h2>5. Propriété intellectuelle</h2>
            <p>Tous les éléments du site (textes, images, logos, vidéos, etc.) sont protégés par le droit de la propriété intellectuelle.</p>
            <p>Toute reproduction, représentation, modification, publication ou adaptation de tout ou partie des éléments du site est interdite sans autorisation écrite préalable de KIND WOLF.</p>
        </section>
        
        <section class="legal-section">
            <h2>6. Responsabilité</h2>
            <p>KIND WOLF ne peut être tenue responsable des dommages directs ou indirects causés au matériel de l'utilisateur lors de l'accès au site.</p>
            <p>L'utilisateur s'engage à utiliser le site de manière loyale et conforme à sa destination.</p>
        </section>
        
        <section class="legal-section">
            <h2>7. Données personnelles</h2>
            <p>KIND WOLF collecte et traite les données personnelles des utilisateurs conformément au RGPD.</p>
            <p>Les utilisateurs disposent d'un droit d'accès, de rectification, de suppression et de portabilité de leurs données.</p>
            <p>Pour exercer ces droits, contactez-nous à : contact@kindwolf.com</p>
        </section>
        
        <section class="legal-section">
            <h2>8. Cookies</h2>
            <p>Le site utilise des cookies pour améliorer l'expérience utilisateur et assurer le bon fonctionnement du panier d'achat.</p>
            <p>L'utilisateur peut désactiver les cookies dans les paramètres de son navigateur, mais certaines fonctionnalités du site pourraient ne plus être accessibles.</p>
        </section>
        
        <section class="legal-section">
            <h2>9. Avis clients</h2>
            <p>Les utilisateurs peuvent laisser des avis sur les produits achetés.</p>
            <p>KIND WOLF se réserve le droit de modérer et de supprimer tout avis inapproprié, offensant ou ne respectant pas les règles de publication.</p>
        </section>
        
        <section class="legal-section">
            <h2>10. Droit applicable et juridiction</h2>
            <p>Les présentes CGU sont régies par le droit français.</p>
            <p>En cas de litige, une solution amiable sera recherchée avant toute action judiciaire. À défaut, les tribunaux français seront seuls compétents.</p>
        </section>
        
        <section class="legal-section contact-info">
            <h2>Contact</h2>
            <p><strong>KIND WOLF</strong><br>
            Email : contact@kindwolf.com<br>
            Téléphone : 01 23 45 67 89</p>
        </section>
    </div>
    
    <?php include '../footer.php'; ?>
</body>
</html>
