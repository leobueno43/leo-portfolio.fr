<?php
// pages/cgv.php - Conditions Générales de Vente
session_start();
require_once '../config.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conditions Générales de Vente - KIND WOLF</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/style.css">
</head>
<body>
    <?php include '../header.php'; ?>
    
    <div class="container legal-page">
        <h1>📄 Conditions Générales de Vente</h1>
        <p class="last-updated">Dernière mise à jour : <?php echo date('d/m/Y'); ?></p>
        
        <section class="legal-section">
            <h2>1. Champ d'application</h2>
            <p>Les présentes Conditions Générales de Vente (CGV) régissent les relations contractuelles entre KIND WOLF et tout client souhaitant effectuer un achat sur le site <?php echo BASE_URL; ?>.</p>
            <p>Toute commande implique l'acceptation sans réserve des présentes CGV.</p>
        </section>
        
        <section class="legal-section">
            <h2>2. Produits</h2>
            <p>Les produits proposés sont ceux qui figurent sur le site au jour de la consultation par le client, dans la limite des stocks disponibles.</p>
            <p>Les photographies et illustrations accompagnant la présentation des produits n'ont aucune valeur contractuelle.</p>
        </section>
        
        <section class="legal-section">
            <h2>3. Prix</h2>
            <p>Les prix de nos produits sont indiqués en euros toutes taxes comprises (TVA + autres taxes applicables).</p>
            <p>KIND WOLF se réserve le droit de modifier ses prix à tout moment, mais les produits seront facturés sur la base des tarifs en vigueur au moment de la validation de la commande.</p>
        </section>
        
        <section class="legal-section">
            <h2>4. Commande</h2>
            <p>Le client passe commande sur le site Internet. La vente ne sera considérée comme définitive qu'après l'envoi au client de la confirmation de l'acceptation de la commande par KIND WOLF par courrier électronique.</p>
            <p>KIND WOLF se réserve le droit d'annuler ou de refuser toute commande d'un client avec lequel il existerait un litige.</p>
        </section>
        
        <section class="legal-section">
            <h2>5. Paiement</h2>
            <p>Le paiement s'effectue par carte bancaire ou via PayPal de manière sécurisée.</p>
            <p>Le débit de la carte n'est effectué qu'au moment de l'expédition de la commande.</p>
            <p>Les données de paiement sont échangées en mode crypté grâce au protocole SSL.</p>
        </section>
        
        <section class="legal-section">
            <h2>6. Livraison</h2>
            <p>Les livraisons sont effectuées à l'adresse indiquée lors de la commande.</p>
            <p>Les délais de livraison sont de 5 à 7 jours ouvrés en France métropolitaine.</p>
            <p>Les frais de livraison sont indiqués avant la validation définitive de la commande.</p>
        </section>
        
        <section class="legal-section">
            <h2>7. Droit de rétractation</h2>
            <p>Conformément à l'article L221-18 du Code de la consommation, le client dispose d'un délai de 14 jours à compter de la réception de sa commande pour exercer son droit de rétractation sans avoir à justifier de motifs ni à payer de pénalités.</p>
            <p>Les retours sont à effectuer dans leur état d'origine et complets (emballage, accessoires, notice).</p>
        </section>
        
        <section class="legal-section">
            <h2>8. Garanties</h2>
            <p>Tous nos produits bénéficient de la garantie légale de conformité et de la garantie contre les vices cachés.</p>
            <p>La garantie légale de conformité s'applique indépendamment de toute garantie commerciale éventuellement consentie.</p>
        </section>
        
        <section class="legal-section">
            <h2>9. Données personnelles</h2>
            <p>Les informations recueillies font l'objet d'un traitement informatique destiné à la gestion de votre commande.</p>
            <p>Conformément au RGPD, vous disposez d'un droit d'accès, de rectification et de suppression des données vous concernant.</p>
        </section>
        
        <section class="legal-section">
            <h2>10. Litiges</h2>
            <p>Les présentes CGV sont soumises au droit français. En cas de litige, une solution amiable sera recherchée avant toute action judiciaire.</p>
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
