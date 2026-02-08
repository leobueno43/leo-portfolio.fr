<!-- admin/products/add.php - Ajouter produit -->
<?php
session_start();
require_once '../../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../../login.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $slug = $_POST['slug'] ?? strtolower(str_replace(' ', '-', $name));
    $description = $_POST['description'] ?? '';
    $price = (float)($_POST['price'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $category = $_POST['category'] ?? '';
    $sku = $_POST['sku'] ?? '';
    $featured = isset($_POST['featured']) ? 1 : 0;
    $active = isset($_POST['active']) ? 1 : 0;
    
    // Upload image
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../../images/products/';
        $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $file_ext;
        move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $filename);
        $image = 'images/products/' . $filename; // Chemin complet pour la BDD
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO products (name, slug, description, price, stock, category, sku, image, featured, active) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $slug, $description, $price, $stock, $category, $sku, $image, $featured, $active]);
        $success = 'Produit ajouté avec succès';
    } catch (PDOException $e) {
        $error = 'Erreur : ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter Produit - KIND WOLF Admin</title>
    <link rel="stylesheet" href="../../style.css">
</head>
<body>
    <div class="admin-container">
        <?php include '../admin_sidebar.php'; ?>
        
        <div class="admin-main">
            <div class="admin-header">
                <h1>Ajouter un Produit</h1>
                <a href="list.php" class="btn-outline">Retour à la liste</a>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <div class="admin-section">
                <form method="POST" enctype="multipart/form-data" class="product-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Nom du produit *</label>
                            <input type="text" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="slug">Slug (URL)</label>
                            <input type="text" id="slug" name="slug">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="5"></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="price">Prix (€) *</label>
                            <input type="number" id="price" name="price" step="0.01" min="0" required>
                        </div>
                        <div class="form-group">
                            <label for="stock">Stock *</label>
                            <input type="number" id="stock" name="stock" min="0" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="category">Catégorie</label>
                            <input type="text" id="category" name="category">
                        </div>
                        <div class="form-group">
                            <label for="sku">SKU</label>
                            <input type="text" id="sku" name="sku">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="image">Image</label>
                        <input type="file" id="image" name="image" accept="image/*">
                    </div>

                    <div class="form-checkboxes">
                        <label>
                            <input type="checkbox" name="featured">
                            Produit en vedette
                        </label>
                        <label>
                            <input type="checkbox" name="active" checked>
                            Actif
                        </label>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">Ajouter le produit</button>
                        <a href="list.php" class="btn-outline">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>