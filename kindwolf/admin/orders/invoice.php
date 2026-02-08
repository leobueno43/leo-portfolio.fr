<?php
// admin/orders/invoice.php - Facture depuis l'admin
// ============================================

session_start();
require_once '../../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

$order_id = (int)($_GET['id'] ?? 0);

// Récupérer la commande
$stmt = $pdo->prepare("SELECT o.*, u.name as customer_name, u.email as customer_email, u.phone as customer_phone 
                       FROM orders o 
                       JOIN users u ON o.user_id = u.id 
                       WHERE o.id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    die('Commande introuvable');
}

// Récupérer les articles
$stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();

$shipping_address = json_decode($order['shipping_address'], true);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture #<?php echo htmlspecialchars($order['order_number']); ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: Arial, sans-serif; 
            padding: 2rem;
            max-width: 800px;
            margin: 0 auto;
        }
        .invoice-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 2px solid #2F5D50;
        }
        .company-info h1 { 
            color: #2F5D50; 
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        .company-info p { 
            color: #666; 
            margin: 0.2rem 0;
        }
        .invoice-details {
            text-align: right;
        }
        .invoice-details h2 {
            color: #2F5D50;
            margin-bottom: 0.5rem;
        }
        .invoice-details p {
            margin: 0.2rem 0;
        }
        .addresses {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        .address-box {
            padding: 1rem;
            background: #f5f5f5;
            border-radius: 5px;
        }
        .address-box h3 {
            color: #2F5D50;
            margin-bottom: 1rem;
        }
        .address-box p {
            margin: 0.3rem 0;
            line-height: 1.6;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 2rem 0;
        }
        .items-table thead {
            background: #2F5D50;
            color: white;
        }
        .items-table th,
        .items-table td {
            padding: 0.8rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .items-table tfoot {
            background: #f5f5f5;
            font-weight: bold;
        }
        .total-row {
            font-size: 1.2rem;
            color: #2F5D50;
        }
        .invoice-footer {
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #666;
            font-size: 0.9rem;
        }
        .action-buttons {
            position: fixed;
            top: 1rem;
            right: 1rem;
            display: flex;
            gap: 0.5rem;
        }
        .action-buttons button,
        .action-buttons a {
            padding: 0.8rem 1.5rem;
            background: #2F5D50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            text-decoration: none;
        }
        .action-buttons button:hover,
        .action-buttons a:hover {
            background: #234739;
        }
        @media print {
            .action-buttons { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="action-buttons">
        <button onclick="window.print()">🖨️ Imprimer</button>
        <a href="<?php echo BASE_URL; ?>/admin/orders/detail.php?id=<?php echo $order_id; ?>">← Retour</a>
    </div>
    
    <div class="invoice-header">
        <div class="company-info">
            <h1>KIND WOLF</h1>
            <p>123 Rue de la Mode</p>
            <p>75001 Paris, France</p>
            <p>Email: contact@kindwolf.com</p>
            <p>Tel: +33 1 23 45 67 89</p>
            <p>SIRET: 123 456 789 00010</p>
        </div>
        <div class="invoice-details">
            <h2>FACTURE</h2>
            <p><strong>N°:</strong> <?php echo htmlspecialchars($order['order_number']); ?></p>
            <p><strong>Date:</strong> <?php echo date('d/m/Y', strtotime($order['created_at'])); ?></p>
            <p><strong>Statut:</strong> 
                <?php 
                $statuses = [
                    'pending' => 'En attente',
                    'processing' => 'En cours',
                    'shipped' => 'Expédiée',
                    'completed' => 'Terminée',
                    'cancelled' => 'Annulée'
                ];
                echo $statuses[$order['status']] ?? ucfirst($order['status']); 
                ?>
            </p>
        </div>
    </div>
    
    <div class="addresses">
        <div class="address-box">
            <h3>Client</h3>
            <p><strong><?php echo htmlspecialchars($order['customer_name']); ?></strong></p>
            <p><?php echo htmlspecialchars($order['customer_email']); ?></p>
            <?php if ($order['customer_phone']): ?>
                <p><?php echo htmlspecialchars($order['customer_phone']); ?></p>
            <?php endif; ?>
        </div>
        
        <?php if ($shipping_address): ?>
        <div class="address-box">
            <h3>Livraison</h3>
            <p><strong><?php echo htmlspecialchars($shipping_address['firstname'] . ' ' . $shipping_address['lastname']); ?></strong></p>
            <?php if (!empty($shipping_address['company'])): ?>
                <p><?php echo htmlspecialchars($shipping_address['company']); ?></p>
            <?php endif; ?>
            <p><?php echo htmlspecialchars($shipping_address['address_line1']); ?></p>
            <?php if (!empty($shipping_address['address_line2'])): ?>
                <p><?php echo htmlspecialchars($shipping_address['address_line2']); ?></p>
            <?php endif; ?>
            <p><?php echo htmlspecialchars($shipping_address['postal_code'] . ' ' . $shipping_address['city']); ?></p>
            <p><?php echo htmlspecialchars($shipping_address['country']); ?></p>
            <p><?php echo htmlspecialchars($shipping_address['phone']); ?></p>
        </div>
        <?php endif; ?>
    </div>
    
    <table class="items-table">
        <thead>
            <tr>
                <th>Produit</th>
                <th>Référence</th>
                <th>Prix unitaire</th>
                <th>Quantité</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
            <tr>
                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                <td><?php echo htmlspecialchars($item['product_sku']); ?></td>
                <td><?php echo number_format($item['price'], 2); ?> €</td>
                <td><?php echo $item['quantity']; ?></td>
                <td><?php echo number_format($item['subtotal'], 2); ?> €</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" style="text-align: right;">Sous-total</td>
                <td><?php echo number_format($order['subtotal'], 2); ?> €</td>
            </tr>
            <tr>
                <td colspan="4" style="text-align: right;">Livraison</td>
                <td><?php echo $order['shipping_cost'] > 0 ? number_format($order['shipping_cost'], 2) . ' €' : 'Gratuite'; ?></td>
            </tr>
            <?php if ($order['discount_amount'] > 0): ?>
            <tr>
                <td colspan="4" style="text-align: right;">
                    Réduction <?php echo $order['promo_code'] ? '(' . htmlspecialchars($order['promo_code']) . ')' : ''; ?>
                </td>
                <td>-<?php echo number_format($order['discount_amount'], 2); ?> €</td>
            </tr>
            <?php endif; ?>
            <tr class="total-row">
                <td colspan="4" style="text-align: right;"><strong>TOTAL TTC</strong></td>
                <td><strong><?php echo number_format($order['total'], 2); ?> €</strong></td>
            </tr>
        </tfoot>
    </table>
    
    <div class="payment-info">
        <p><strong>Mode de paiement:</strong> 
            <?php 
            $methods = [
                'card' => 'Carte bancaire',
                'paypal' => 'PayPal',
                'bank_transfer' => 'Virement bancaire'
            ];
            echo $methods[$order['payment_method']] ?? $order['payment_method']; 
            ?>
        </p>
    </div>
    
    <div class="invoice-footer">
        <p>Merci de votre confiance !</p>
        <p>KIND WOLF - Votre partenaire mode</p>
    </div>
</body>
</html>