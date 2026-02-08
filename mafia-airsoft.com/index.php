<?php
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'config/team_helpers.php';

// Récupérer les prochaines parties actives (max 3)
$upcoming_events = getEventsWithTeams($pdo, "e.is_active = 1 AND e.event_date >= NOW()");
$upcoming_events = array_slice($upcoming_events, 0, 3);

// Statistiques publiques
$stmt = $pdo->query("SELECT COUNT(*) FROM events WHERE is_active = 1");
$total_events = $stmt->fetchColumn();
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE is_admin = 0");
$total_players = $stmt->fetchColumn();

// Récupérer les photos de la galerie
$stmt = $pdo->query("SELECT * FROM gallery WHERE is_active = 1 ORDER BY display_order ASC, created_at DESC LIMIT 10");
$gallery_photos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - Association Airsoft</title>
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    <link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container page-content">
        <!-- Hero Section -->
        <section class="hero-section">
            <h1><?= icon('target') ?> Bienvenue sur le site de la Mafia Airsoft Team</h1>
            <p class="hero-subtitle">Rejoignez-nous pour des parties d'airsoft passionnantes !</p>
            
            <?php if (!isLoggedIn()): ?>
                <div class="hero-actions">
                    <a href="login.php" class="btn btn-primary btn-lg">Se connecter</a>
                    <a href="events.php" class="btn btn-secondary btn-lg">Voir les parties</a>
                </div>
            <?php else: ?>
                <div class="hero-actions">
                    <a href="events.php" class="btn btn-primary btn-lg">Voir toutes les parties</a>
                    <?php if (isAdmin()): ?>
                        <a href="admin/index.php" class="btn btn-secondary btn-lg">Administration</a>
                    <?php else: ?>
                        <a href="player/dashboard.php" class="btn btn-secondary btn-lg">Mon tableau de bord</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- Galerie Photo Carrousel -->
        <?php if (!empty($gallery_photos)): ?>
        <section class="gallery-section">
            <div class="gallery-carousel">
                <div class="carousel-container">
                    <div class="carousel-track">
                        <?php foreach ($gallery_photos as $photo): ?>
                            <div class="carousel-slide">
                                <img src="<?= htmlspecialchars($photo['image_path']) ?>" alt="<?= htmlspecialchars($photo['title']) ?>">
                                <?php if ($photo['title'] || $photo['description']): ?>
                                    <div class="carousel-caption">
                                        <?php if ($photo['title']): ?>
                                            <h3><?= htmlspecialchars($photo['title']) ?></h3>
                                        <?php endif; ?>
                                        <?php if ($photo['description']): ?>
                                            <p><?= htmlspecialchars($photo['description']) ?></p>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if (count($gallery_photos) > 1): ?>
                        <button class="carousel-btn carousel-btn-prev" aria-label="Photo précédente">
                            <span>‹</span>
                        </button>
                        <button class="carousel-btn carousel-btn-next" aria-label="Photo suivante">
                            <span>›</span>
                        </button>
                    <?php endif; ?>
                </div>
                
                <?php if (count($gallery_photos) > 1): ?>
                    <div class="carousel-nav">
                        <?php foreach ($gallery_photos as $index => $photo): ?>
                            <button class="carousel-dot <?= $index === 0 ? 'active' : '' ?>" 
                                    aria-label="Aller à la photo <?= $index + 1 ?>"></button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Statistiques -->
        <section class="stats-section">
            <div class="stats-grid">
                <div class="stat-card stat-primary">
                    <div class="stat-number"><?= $total_events ?></div>
                    <div class="stat-label">Parties actives</div>
                </div>
                <div class="stat-card stat-success">
                    <div class="stat-number"><?= $total_players ?></div>
                    <div class="stat-label">Joueurs inscrits</div>
                </div>
            </div>
        </section>

        <!-- Prochaines parties -->
        <section class="upcoming-events-section">
            <h2><?= icon('calendar') ?> Prochaines parties</h2>
            
            <?php if (empty($upcoming_events)): ?>
                <p class="no-events">Aucune partie programmée pour le moment.</p>
            <?php else: ?>
                <div class="events-list">
                    <?php foreach ($upcoming_events as $event): ?>
                        <div class="event-item">
                            <div class="event-header">
                                <div class="event-date-badge">
                                    <span class="date-day"><?= date('d', strtotime($event['event_date'])) ?></span>
                                    <span class="date-month"><?= date('M', strtotime($event['event_date'])) ?></span>
                                </div>
                                <div class="event-info">
                                    <h3><?= htmlspecialchars($event['title']) ?></h3>
                                    <p class="event-meta">
                                        <span><?= icon('clock') ?> <?= date('H:i', strtotime($event['event_date'])) ?></span>
                                        <span><?= icon('map-pin') ?> <?= htmlspecialchars($event['location']) ?></span>
                                    </p>
                                    <p class="event-description"><?= htmlspecialchars(substr($event['description'], 0, 150)) ?>...</p>
                                </div>
                            </div>
                            
                            <div class="event-teams">
                                <?php foreach ($event['teams'] as $team): ?>
                                    <div class="team-stat" style="border-left: 3px solid <?= htmlspecialchars($team['team_color']) ?>">
                                        <span class="team-label"><?= htmlspecialchars($team['team_name']) ?></span>
                                        <span class="team-count"><?= $team['current_players'] ?>/<?= $team['max_players'] ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="event-actions">
                                <a href="event.php?id=<?= $event['id'] ?>" class="btn btn-primary">
                                    Voir les détails
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="see-all-events">
                    <a href="events.php" class="btn btn-secondary">Voir toutes les parties</a>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="js/gallery.js"></script>
</body>
</html>
