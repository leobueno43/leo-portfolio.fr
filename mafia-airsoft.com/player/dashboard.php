<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../config/team_helpers.php';
requireLogin();

// Récupérer les informations du joueur
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Récupérer les inscriptions du joueur
$stmt = $pdo->prepare("
    SELECT e.*, r.team, r.registered_at, r.id as registration_id,
           et.team_name, et.team_color
    FROM registrations r
    JOIN events e ON r.event_id = e.id
    LEFT JOIN event_teams et ON r.event_id = et.event_id AND r.team = et.team_key
    WHERE r.user_id = ?
    ORDER BY e.event_date ASC
");
$stmt->execute([$_SESSION['user_id']]);
$registrations = $stmt->fetchAll();

// Enrichir avec les données d'équipe
foreach ($registrations as &$reg) {
    $team_info = getEventTeam($pdo, $reg['id'], $reg['team']);
    if ($team_info) {
        $reg['team_name'] = $team_info['team_name'];
        $reg['team_color'] = $team_info['team_color'];
    } else {
        $reg['team_name'] = $reg['team'];
        $reg['team_color'] = '#a3a3a3';
    }
}
unset($reg);

// Séparer les inscriptions futures et passées
$upcoming_registrations = [];
$past_registrations = [];
$now = new DateTime();

foreach ($registrations as $reg) {
    $eventDate = new DateTime($reg['event_date']);
    if ($eventDate >= $now) {
        $upcoming_registrations[] = $reg;
    } else {
        $past_registrations[] = $reg;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Espace - Association Airsoft</title>
    <link rel="icon" type="image/x-icon" href="../images/favicon.ico">
    <link rel="shortcut icon" type="image/x-icon" href="../images/favicon.ico">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main class="container page-content">
        <div class="dashboard">
            <h1><?= icon('user') ?> Mon espace joueur</h1>
            <p class="welcome-text">Bienvenue, <strong><?= htmlspecialchars($user['pseudo']) ?></strong> !</p>

            <!-- Informations personnelles -->
            <section class="dashboard-section">
                <h2><?= icon('edit') ?> Mes informations</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <strong>Pseudo:</strong> <?= htmlspecialchars($user['pseudo']) ?>
                    </div>
                    <div class="info-item">
                        <strong>Email:</strong> <?= htmlspecialchars($user['email']) ?>
                    </div>
                    <?php if ($user['phone']): ?>
                    <div class="info-item">
                        <strong>Téléphone:</strong> <?= htmlspecialchars($user['phone']) ?>
                    </div>
                    <?php endif; ?>
                    <div class="info-item">
                        <strong>Membre depuis:</strong> <?= date('d/m/Y', strtotime($user['created_at'])) ?>
                    </div>
                </div>
            </section>

            <!-- Statistiques -->
            <section class="dashboard-section">
                <h2><?= icon('stats') ?> Mes statistiques</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number"><?= count($upcoming_registrations) ?></div>
                        <div class="stat-label">Parties à venir</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?= count($past_registrations) ?></div>
                        <div class="stat-label">Parties jouées</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?= count($registrations) ?></div>
                        <div class="stat-label">Total inscriptions</div>
                    </div>
                </div>
            </section>

            <!-- Prochaines parties -->
            <section class="dashboard-section">
                <h2><?= icon('target') ?> Mes prochaines parties</h2>
                
                <?php if (empty($upcoming_registrations)): ?>
                    <p class="no-data">Vous n'êtes inscrit à aucune partie pour le moment.</p>
                    <a href="../events.php" class="btn btn-primary">Voir les parties disponibles</a>
                <?php else: ?>
                    <div class="registrations-list">
                        <?php foreach ($upcoming_registrations as $reg): ?>
                            <div class="registration-card">
                                <div class="registration-header">
                                    <h3><?= htmlspecialchars($reg['title']) ?></h3>
                                    <span class="team-badge" style="background-color: <?= htmlspecialchars($reg['team_color']) ?>20; border-left: 3px solid <?= htmlspecialchars($reg['team_color']) ?>">
                                        <?= htmlspecialchars($reg['team_name']) ?>
                                    </span>
                                </div>
                                
                                <div class="registration-info">
                                    <div class="info-row">
                                        <span><?= icon('calendar') ?> <strong>Date:</strong></span>
                                        <span><?= date('d/m/Y', strtotime($reg['event_date'])) ?></span>
                                    </div>
                                    <div class="info-row">
                                        <span>🕐 <strong>Heure:</strong></span>
                                        <span><?= date('H:i', strtotime($reg['event_date'])) ?></span>
                                    </div>
                                    <div class="info-row">
                                        <span>📍 <strong>Lieu:</strong></span>
                                        <span><?= htmlspecialchars($reg['location']) ?></span>
                                    </div>
                                </div>
                                
                                <div class="registration-actions">
                                    <a href="../event.php?id=<?= $reg['id'] ?>" class="btn btn-primary btn-sm">
                                        Voir les détails
                                    </a>
                                    <small class="registration-date">
                                        Inscrit le <?= date('d/m/Y', strtotime($reg['registered_at'])) ?>
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

            <!-- Historique -->
            <?php if (!empty($past_registrations)): ?>
                <section class="dashboard-section">
                    <h2>📜 Historique des parties</h2>
                    <div class="history-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Partie</th>
                                    <th>Camp</th>
                                    <th>Lieu</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($past_registrations as $reg): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($reg['event_date'])) ?></td>
                                        <td><?= htmlspecialchars($reg['title']) ?></td>
                                        <td>
                                            <span class="team-badge-small team-badge-<?= strtolower($reg['team']) ?>">
                                                <?= $reg['team'] ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($reg['location']) ?></td>
                                        <td>
                                            <a href="../event.php?id=<?= $reg['id'] ?>" class="btn-link">Détails</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            <?php endif; ?>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
