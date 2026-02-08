<?php
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'config/team_helpers.php';

// Récupérer toutes les parties actives
$events = getEventsWithTeams($pdo, "e.is_active = 1");

// Séparer les événements futurs et passés
$upcoming = [];
$past = [];
$now = new DateTime();

foreach ($events as $event) {
    $eventDate = new DateTime($event['event_date']);
    if ($eventDate >= $now) {
        $upcoming[] = $event;
    } else {
        $past[] = $event;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nos Parties - Association Airsoft</title>
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    <link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container page-content">
        <h1><?= icon('calendar') ?> Agenda des parties</h1>
        
        <?php if (!isLoggedIn()): ?>
            <div class="alert alert-info">
                <strong>Connectez-vous</strong> pour vous inscrire aux parties. 
                <a href="login.php">Se connecter</a>
            </div>
        <?php endif; ?>

        <!-- Parties à venir -->
        <section class="events-section">
            <h2><?= icon('target') ?> Prochaines parties</h2>
            
            <?php if (empty($upcoming)): ?>
                <p class="no-events">Aucune partie programmée pour le moment.</p>
            <?php else: ?>
                <div class="events-list">
                    <?php foreach ($upcoming as $event): ?>
                        <div class="event-item">
                            <div class="event-header">
                                <div class="event-date-badge">
                                    <span class="date-day"><?= date('d', strtotime($event['event_date'])) ?></span>
                                    <span class="date-month"><?= date('F', strtotime($event['event_date'])) ?></span>
                                    <span class="date-year"><?= date('Y', strtotime($event['event_date'])) ?></span>
                                </div>
                                <div class="event-info">
                                    <h3><?= htmlspecialchars($event['title']) ?></h3>
                                    <p class="event-meta">
                                        <span>🕐 <?= date('H:i', strtotime($event['event_date'])) ?></span>
                                        <span>📍 <?= htmlspecialchars($event['location']) ?></span>
                                    </p>
                                    <p class="event-description"><?= htmlspecialchars(substr($event['description'], 0, 150)) ?>...</p>
                                </div>
                            </div>
                            
                            <div class="event-teams-summary">
                                <?php foreach ($event['teams'] as $team): ?>
                                    <div class="team-stat" style="border-left: 3px solid <?= htmlspecialchars($team['team_color']) ?>">
                                        <span class="team-label"><?= htmlspecialchars($team['team_name']) ?></span>
                                        <span class="team-count"><?= $team['current_players'] ?>/<?= $team['max_players'] ?></span>
                                        <?php if ($team['current_players'] >= $team['max_players']): ?>
                                            <span class="team-status full">Complet</span>
                                        <?php else: ?>
                                            <span class="team-status available"><?= ($team['max_players'] - $team['current_players']) ?> places</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="event-actions">
                                <a href="event.php?id=<?= $event['id'] ?>" class="btn btn-primary">
                                    Voir les détails et s'inscrire
                                </a>
                                <?php if (!$event['registration_open']): ?>
                                    <span class="registration-closed">Inscriptions fermées</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- Parties passées -->
        <?php if (!empty($past)): ?>
            <section class="events-section past-events">
                <h2>📜 Parties passées</h2>
                <div class="events-list-compact">
                    <?php foreach (array_slice($past, 0, 5) as $event): ?>
                        <div class="event-item-compact">
                            <span class="event-date-small"><?= date('d/m/Y', strtotime($event['event_date'])) ?></span>
                            <span class="event-title-small"><?= htmlspecialchars($event['title']) ?></span>
                            <a href="event.php?id=<?= $event['id'] ?>" class="btn-link-small">Détails</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
