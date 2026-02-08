<?php
// user/adresses.php - Gestion des adresses
// ============================================

session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Ajouter une adresse
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_address'])) {
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $company = trim($_POST['company'] ?? '');
    $address_line1 = trim($_POST['address_line1'] ?? '');
    $address_line2 = trim($_POST['address_line2'] ?? '');
    $postal_code = trim($_POST['postal_code'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $country = trim($_POST['country'] ?? 'France');
    $phone = trim($_POST['phone'] ?? '');
    $is_default = isset($_POST['is_default']) ? 1 : 0;
    
    if (empty($firstname) || empty($lastname) || empty($address_line1) || empty($postal_code) || empty($city) || empty($phone)) {
        $error = 'Veuillez remplir tous les champs obligatoires';
    } else {
        // Si c'est l'adresse par défaut, retirer le statut par défaut des autres
        if ($is_default) {
            $pdo->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ?")->execute([$user_id]);
        }
        
        $stmt = $pdo->prepare("INSERT INTO addresses (user_id, type, firstname, lastname, company, address_line1, address_line2, postal_code, city, country, phone, is_default) 
                               VALUES (?, 'both', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$user_id, $firstname, $lastname, $company, $address_line1, $address_line2, $postal_code, $city, $country, $phone, $is_default])) {
            $success = 'Adresse ajoutée avec succès';
        } else {
            $error = 'Erreur lors de l\'ajout de l\'adresse';
        }
    }
}

// Définir comme adresse par défaut
if (isset($_GET['set_default'])) {
    $address_id = (int)$_GET['set_default'];
    $pdo->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ?")->execute([$user_id]);
    $stmt = $pdo->prepare("UPDATE addresses SET is_default = 1 WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$address_id, $user_id])) {
        $success = 'Adresse par défaut mise à jour';
    }
}

// Supprimer une adresse
if (isset($_GET['delete'])) {
    $address_id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM addresses WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$address_id, $user_id])) {
        $success = 'Adresse supprimée';
    }
}

// Récupérer les adresses
$stmt = $pdo->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC");
$stmt->execute([$user_id]);
$addresses = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes adresses - KIND WOLF</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/style.css">
</head>
<body>
    <?php include '../header.php'; ?>
    
    <div class="page-header">
        <h1>Mes adresses</h1>
        <p>Gérez vos adresses de livraison</p>
    </div>

    <div class="account-container container">
        <aside class="account-sidebar">
            <nav class="account-menu">
                <a href="<?php echo BASE_URL; ?>/user/compte.php">
                    📊 Tableau de bord
                </a>
                <a href="<?php echo BASE_URL; ?>/user/commandes.php">
                    📦 Mes commandes
                </a>
                <a href="<?php echo BASE_URL; ?>/user/profil.php">
                    👤 Mon profil
                </a>
                <a href="<?php echo BASE_URL; ?>/user/adresses.php" class="active">
                    📍 Mes adresses
                </a>
                <a href="<?php echo BASE_URL; ?>/pages/boutique.php">
                    🛍️ Continuer mes achats
                </a>
                <a href="<?php echo BASE_URL; ?>/auth/logout.php">
                    🚪 Déconnexion
                </a>
            </nav>
        </aside>

        <div class="account-main">
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <!-- Liste des adresses -->
            <section class="account-section">
                <div class="section-header">
                    <h2>📍 Vos adresses (<?php echo count($addresses); ?>)</h2>
                    <button onclick="document.getElementById('addAddressForm').style.display='block'" 
                            class="btn-primary">
                        ➕ Ajouter une adresse
                    </button>
                </div>
                
                <?php if (empty($addresses)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">📍</div>
                        <h3>Aucune adresse enregistrée</h3>
                        <p>Ajoutez une adresse pour faciliter vos futures commandes</p>
                    </div>
                <?php else: ?>
                    <div class="addresses-grid">
                        <?php foreach ($addresses as $addr): ?>
                        <div class="address-box <?php echo $addr['is_default'] ? 'default' : ''; ?>">
                            <?php if ($addr['is_default']): ?>
                                <span class="default-badge">✓ Par défaut</span>
                            <?php endif; ?>
                            
                            <div class="address-content">
                                <strong><?php echo htmlspecialchars($addr['firstname'] . ' ' . $addr['lastname']); ?></strong>
                                <?php if ($addr['company']): ?>
                                    <p><?php echo htmlspecialchars($addr['company']); ?></p>
                                <?php endif; ?>
                                <p><?php echo htmlspecialchars($addr['address_line1']); ?></p>
                                <?php if ($addr['address_line2']): ?>
                                    <p><?php echo htmlspecialchars($addr['address_line2']); ?></p>
                                <?php endif; ?>
                                <p><?php echo htmlspecialchars($addr['postal_code'] . ' ' . $addr['city']); ?></p>
                                <p><?php echo htmlspecialchars($addr['country']); ?></p>
                                <p>📞 <?php echo htmlspecialchars($addr['phone']); ?></p>
                            </div>
                            
                            <div class="address-actions">
                                <?php if (!$addr['is_default']): ?>
                                    <a href="?set_default=<?php echo $addr['id']; ?>" class="btn-outline-small">
                                        Définir par défaut
                                    </a>
                                <?php endif; ?>
                                <a href="?delete=<?php echo $addr['id']; ?>" 
                                   onclick="return confirm('Supprimer cette adresse ?')" 
                                   class="btn-danger-small">
                                    🗑️ Supprimer
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
            
            <!-- Formulaire d'ajout d'adresse -->
            <section id="addAddressForm" class="account-section" style="display: none;">
                <h2>➕ Ajouter une nouvelle adresse</h2>
                <form method="POST" class="address-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="firstname">Prénom *</label>
                            <input type="text" id="firstname" name="firstname" required>
                        </div>
                        <div class="form-group">
                            <label for="lastname">Nom *</label>
                            <input type="text" id="lastname" name="lastname" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="company">Société (optionnel)</label>
                        <input type="text" id="company" name="company">
                    </div>
                    
                    <div class="form-group">
                        <label for="address_line1">Adresse *</label>
                        <input type="text" id="address_line1" name="address_line1" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="address_line2">Complément d'adresse</label>
                        <input type="text" id="address_line2" name="address_line2">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="postal_code">Code postal *</label>
                            <input type="text" id="postal_code" name="postal_code" required>
                        </div>
                        <div class="form-group">
                            <label for="city">Ville *</label>
                            <input type="text" id="city" name="city" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="country">Pays *</label>
                            <select id="country" name="country" required>
                                <option value="France">France</option>
                                <option value="Belgique">Belgique</option>
                                <option value="Suisse">Suisse</option>
                                <option value="Luxembourg">Luxembourg</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="phone">Téléphone *</label>
                            <input type="tel" id="phone" name="phone" required>
                        </div>
                    </div>
                    
                    <div class="form-checkboxes">
                        <label>
                            <input type="checkbox" name="is_default">
                            Définir comme adresse par défaut
                        </label>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="add_address" class="btn-primary">
                            💾 Enregistrer l'adresse
                        </button>
                        <button type="button" onclick="document.getElementById('addAddressForm').style.display='none'" 
                                class="btn-outline">
                            Annuler
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </div>

    <?php include '../footer.php'; ?>
    <script src="<?php echo BASE_URL; ?>/script.js"></script>
</body>
</html>