<?php
require_once 'config.php';

// Supprimer l'ancienne clé default_shipping_cost
$pdo->exec("DELETE FROM site_settings WHERE setting_key = 'default_shipping_cost'");

// Mettre à jour shipping_cost avec la bonne valeur (5.99 par défaut, à modifier selon vos besoins)
$stmt = $pdo->prepare("UPDATE site_settings SET setting_value = '5.99' WHERE setting_key = 'shipping_cost'");
$stmt->execute();

echo "✅ Paramètres de livraison corrigés !\n\n";

// Vérifier
$stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings 
                     WHERE setting_key IN ('shipping_cost', 'default_shipping_cost', 'free_shipping_threshold')
                     ORDER BY setting_key");

echo "Paramètres actuels :\n";
while ($row = $stmt->fetch()) {
    echo "  " . $row['setting_key'] . " = " . $row['setting_value'] . "\n";
}
