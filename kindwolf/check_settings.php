<?php
require_once 'config.php';

echo "=== Vérification des paramètres de livraison ===\n\n";

$stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings 
                     WHERE setting_key IN ('shipping_cost', 'default_shipping_cost', 'free_shipping_threshold')
                     ORDER BY setting_key");

while ($row = $stmt->fetch()) {
    echo $row['setting_key'] . " = " . $row['setting_value'] . "\n";
}

echo "\n=== Tous les paramètres ===\n\n";
$stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings ORDER BY setting_key");
while ($row = $stmt->fetch()) {
    echo $row['setting_key'] . " = " . $row['setting_value'] . "\n";
}
