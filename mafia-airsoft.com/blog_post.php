<?php
require_once 'config/database.php';
require_once 'config/session.php';

$slug = $_GET['slug'] ?? '';

// Récupérer l'article
$stmt = $pdo->prepare("
    SELECT bp.*, u.pseudo as author_name, u.profile_picture as author_picture
    FROM blog_posts bp
    JOIN users u ON bp.author_id = u.id
    WHERE bp.slug = ? AND bp.is_published = 1
");
$stmt->execute([$slug]);
$post = $stmt->fetch();

if (!$post) {
    header('Location: blog.php');
    exit;
}

// Récupérer les images de la galerie
$stmt_gallery = $pdo->prepare("
    SELECT * FROM blog_gallery 
    WHERE blog_post_id = ? 
    ORDER BY display_order ASC
");
$stmt_gallery->execute([$post['id']]);
$gallery_images = $stmt_gallery->fetchAll();

// Incrémenter le compteur de vues
$stmt = $pdo->prepare("UPDATE blog_posts SET views = views + 1 WHERE id = ?");
$stmt->execute([$post['id']]);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($post['title']) ?> - Blog Mafia Airsoft Team</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container page-content">
        <div class="blog-post-header">
            <a href="blog.php" class="btn btn-outline">← Retour au blog</a>
        </div>

        <article class="blog-post">
            <?php if ($post['featured_image']): ?>
                <div class="blog-post-image">
                    <img src="<?= htmlspecialchars($post['featured_image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>">
                </div>
            <?php endif; ?>
            
            <div class="blog-post-meta">
                <div class="author-info">
                    <?php if ($post['author_picture']): ?>
                        <img src="<?= htmlspecialchars($post['author_picture']) ?>" alt="<?= htmlspecialchars($post['author_name']) ?>" class="author-avatar">
                    <?php else: ?>
                        <div class="author-avatar"><?= icon('user') ?></div>
                    <?php endif; ?>
                    <div>
                        <strong><?= htmlspecialchars($post['author_name']) ?></strong>
                        <span class="blog-date">
                            Publié le <?= date('d/m/Y à H:i', strtotime($post['published_at'])) ?>
                        </span>
                    </div>
                </div>
                <span class="blog-views"><?= icon('eye') ?> <?= $post['views'] + 1 ?> vues</span>
            </div>

            <h1 class="blog-post-title"><?= htmlspecialchars($post['title']) ?></h1>

            <div class="blog-post-content">
                <?= nl2br(htmlspecialchars($post['content'])) ?>
            </div>

            <?php if (!empty($gallery_images)): ?>
                <div class="blog-gallery">
                    <h3>📷 Galerie de photos</h3>
                    <div class="blog-gallery-grid">
                        <?php foreach ($gallery_images as $index => $image): ?>
                            <div class="blog-gallery-item" onclick="openGalleryModal(<?= $index ?>)">
                                <img src="<?= htmlspecialchars($image['image_path']) ?>" 
                                     alt="<?= htmlspecialchars($image['caption'] ?: 'Image de la galerie') ?>">
                                <?php if ($image['caption']): ?>
                                    <div class="blog-gallery-caption">
                                        <?= htmlspecialchars($image['caption']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Modal pour afficher les images en grand -->
                <div id="galleryModal" class="gallery-modal" onclick="closeGalleryModal()">
                    <span class="gallery-modal-close">&times;</span>
                    <div class="gallery-modal-content" onclick="event.stopPropagation()">
                        <button class="gallery-modal-prev" onclick="changeGalleryImage(-1)">&#10094;</button>
                        <img id="galleryModalImg" src="" alt="Image en grand">
                        <div id="galleryModalCaption"></div>
                        <button class="gallery-modal-next" onclick="changeGalleryImage(1)">&#10095;</button>
                    </div>
                </div>

                <script>
                    const galleryImages = <?= json_encode(array_map(function($img) {
                        return [
                            'path' => $img['image_path'],
                            'caption' => $img['caption']
                        ];
                    }, $gallery_images)) ?>;
                    let currentGalleryIndex = 0;

                    function openGalleryModal(index) {
                        currentGalleryIndex = index;
                        const modal = document.getElementById('galleryModal');
                        const img = document.getElementById('galleryModalImg');
                        const caption = document.getElementById('galleryModalCaption');
                        
                        modal.style.display = 'flex';
                        img.src = galleryImages[index].path;
                        caption.textContent = galleryImages[index].caption || '';
                        document.body.style.overflow = 'hidden';
                    }

                    function closeGalleryModal() {
                        document.getElementById('galleryModal').style.display = 'none';
                        document.body.style.overflow = 'auto';
                    }

                    function changeGalleryImage(direction) {
                        currentGalleryIndex += direction;
                        if (currentGalleryIndex < 0) {
                            currentGalleryIndex = galleryImages.length - 1;
                        } else if (currentGalleryIndex >= galleryImages.length) {
                            currentGalleryIndex = 0;
                        }
                        
                        const img = document.getElementById('galleryModalImg');
                        const caption = document.getElementById('galleryModalCaption');
                        img.src = galleryImages[currentGalleryIndex].path;
                        caption.textContent = galleryImages[currentGalleryIndex].caption || '';
                    }

                    // Fermer avec la touche Escape
                    document.addEventListener('keydown', function(event) {
                        if (event.key === 'Escape') {
                            closeGalleryModal();
                        } else if (event.key === 'ArrowLeft') {
                            changeGalleryImage(-1);
                        } else if (event.key === 'ArrowRight') {
                            changeGalleryImage(1);
                        }
                    });
                </script>
            <?php endif; ?>

            <div class="blog-post-footer">
                <a href="blog.php" class="btn btn-primary">← Retour au blog</a>
            </div>
        </article>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
