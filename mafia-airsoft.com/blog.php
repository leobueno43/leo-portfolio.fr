<?php
require_once 'config/database.php';
require_once 'config/session.php';

// Récupérer tous les articles publiés
$stmt = $pdo->prepare("
    SELECT bp.*, u.pseudo as author_name, u.profile_picture as author_picture
    FROM blog_posts bp
    JOIN users u ON bp.author_id = u.id
    WHERE bp.is_published = 1
    ORDER BY bp.published_at DESC
");
$stmt->execute();
$posts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog - Association Airsoft</title>
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    <link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container page-content">
        <h1><?= icon('edit') ?> Blog Mafia Airsoft Team</h1>
        <p class="page-subtitle">Actualités, comptes-rendus de parties et conseils airsoft</p>

        <?php if (empty($posts)): ?>
            <div class="no-data">
                <p>Aucun article publié pour le moment.</p>
            </div>
        <?php else: ?>
            <div class="blog-grid">
                <?php foreach ($posts as $post): ?>
                    <article class="blog-card">
                        <?php if ($post['featured_image']): ?>
                            <div class="blog-card-image" style="background-image: url('<?= htmlspecialchars($post['featured_image']) ?>');">
                            </div>
                        <?php else: ?>
                            <div class="blog-card-image" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                <div style="display: flex; align-items: center; justify-content: center; height: 100%; font-size: 48px;">
                                    <?= icon('edit') ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="blog-card-content">
                            <div class="blog-card-meta">
                                <div class="author-info">
                                    <?php if ($post['author_picture']): ?>
                                        <img src="<?= htmlspecialchars($post['author_picture']) ?>" alt="<?= htmlspecialchars($post['author_name']) ?>" class="author-avatar">
                                    <?php else: ?>
                                        <div class="author-avatar"><?= icon('user') ?></div>
                                    <?php endif; ?>
                                    <span><?= htmlspecialchars($post['author_name']) ?></span>
                                </div>
                                <span class="blog-date">
                                    <?= icon('calendar') ?> <?= date('d/m/Y', strtotime($post['published_at'])) ?>
                                </span>
                            </div>
                            
                            <h2 class="blog-card-title">
                                <a href="blog_post.php?slug=<?= htmlspecialchars($post['slug']) ?>">
                                    <?= htmlspecialchars($post['title']) ?>
                                </a>
                            </h2>
                            
                            <p class="blog-card-excerpt">
                                <?= htmlspecialchars($post['excerpt'] ?? substr(strip_tags($post['content']), 0, 150)) ?>...
                            </p>
                            
                            <div class="blog-card-footer">
                                <a href="blog_post.php?slug=<?= htmlspecialchars($post['slug']) ?>" class="btn btn-outline">
                                    Lire la suite →
                                </a>
                                <span class="blog-views"><?= icon('eye') ?> <?= $post['views'] ?> vues</span>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
