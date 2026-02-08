<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../config/team_helpers.php';
requireAdmin();

$event_id = $_GET['id'] ?? 0;

// Récupérer les détails de la partie avec les équipes
$event = getEventWithTeams($pdo, $event_id);

if (!$event) {
    header('Location: index.php');
    exit;
}

// Récupérer tous les inscrits avec leurs informations
$stmt = $pdo->prepare("
    SELECT r.*, u.pseudo, u.email, u.phone
    FROM registrations r
    JOIN users u ON r.user_id = u.id
    WHERE r.event_id = ?
    ORDER BY r.team, r.registered_at
");
$stmt->execute([$event_id]);
$registrations = $stmt->fetchAll();

// Organiser les inscriptions par équipe
$teams = [];
foreach ($event['teams'] as $team_info) {
    $teams[$team_info['team_key']] = [];
}

foreach ($registrations as $reg) {
    if (isset($teams[$reg['team']])) {
        $teams[$reg['team']][] = $reg;
    }
}

// Gérer la suppression d'une inscription
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_registration'])) {
    $reg_id = $_POST['registration_id'] ?? 0;
    $stmt = $pdo->prepare("DELETE FROM registrations WHERE id = ?");
    $stmt->execute([$reg_id]);
    header('Location: view_event.php?id=' . $event_id . '&deleted=1');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails - <?= htmlspecialchars($event['title']) ?></title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main class="container page-content">
        <div class="admin-view">
            <div class="page-header">
                <h1><?= icon('eye') ?> <?= htmlspecialchars($event['title']) ?></h1>
                <div class="header-actions">
                    <a href="edit_event.php?id=<?= $event['id'] ?>" class="btn btn-primary"><?= icon('edit') ?> Modifier</a>
                    <a href="index.php" class="btn btn-secondary">← Retour</a>
                </div>
            </div>

            <?php if (isset($_GET['deleted'])): ?>
                <div class="alert alert-success">Inscription supprimée avec succès.</div>
            <?php endif; ?>

            <!-- Informations de la partie -->
            <section class="info-section">
                <h2><?= icon('list') ?> Informations</h2>
                <div class="info-grid-large">
                    <div class="info-item">
                        <strong>Date et heure:</strong> <?= date('d/m/Y à H:i', strtotime($event['event_date'])) ?>
                    </div>
                    <div class="info-item">
                        <strong>Lieu:</strong> <?= htmlspecialchars($event['location']) ?>
                    </div>
                    <div class="info-item">
                        <strong>Statut:</strong> 
                        <?= $event['is_active'] ? '<span class="badge badge-success">Actif</span>' : '<span class="badge badge-danger">Inactif</span>' ?>
                        <?= $event['registration_open'] ? '<span class="badge badge-success">Inscriptions ouvertes</span>' : '<span class="badge badge-warning">Inscriptions fermées</span>' ?>
                    </div>
                    <div class="info-item">
                        <strong>Total inscrits:</strong> <?= count($registrations) ?>
                    </div>
                </div>

                <?php if ($event['description']): ?>
                    <div class="info-block">
                        <strong>Description:</strong>
                        <p><?= nl2br(htmlspecialchars($event['description'])) ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($event['scenario']): ?>
                    <div class="info-block">
                        <strong>Scénario:</strong>
                        <p><?= nl2br(htmlspecialchars($event['scenario'])) ?></p>
                    </div>
                <?php endif; ?>
            </section>

            <!-- Statistiques par camp -->
            <section class="stats-section">
                <h2><?= icon('stats') ?> Répartition des équipes</h2>
                <div class="team-stats-grid">
                    <?php foreach ($event['teams'] as $team): ?>
                        <div class="team-stat-card" style="border-top: 3px solid <?= htmlspecialchars($team['team_color']) ?>">
                            <h3><?= htmlspecialchars($team['team_name']) ?></h3>
                            <div class="stat-big"><?= $team['current_players'] ?> / <?= $team['max_players'] ?></div>
                            <div class="stat-progress">
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?= ($team['max_players'] > 0) ? ($team['current_players'] / $team['max_players'] * 100) : 0 ?>%; background: <?= htmlspecialchars($team['team_color']) ?>"></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- Liste des inscrits par camp -->
            <section class="registrations-section">
                <h2><?= icon('users') ?> Liste des inscrits</h2>
                
                <?php foreach ($event['teams'] as $team_info): ?>
                    <?php $team_key = $team_info['team_key']; ?>
                    <div class="team-registration-block">
                        <h3 style="border-left: 4px solid <?= htmlspecialchars($team_info['team_color']) ?>">
                            <?= htmlspecialchars($team_info['team_name']) ?> 
                            (<?= isset($teams[$team_key]) ? count($teams[$team_key]) : 0 ?>)
                        </h3>
                        
                        <?php if (empty($teams[$team_key])): ?>
                            <p class="no-data">Aucun joueur inscrit</p>
                        <?php else: ?>
                                <table class="players-table">
                                    <thead>
                                        <tr>
                                            <th>Pseudo</th>
                                            <th>Email</th>
                                            <th>Téléphone</th>
                                            <th>Inscrit le</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($teams[$team_key] as $reg): ?>
                                            <tr>
                                                <td><strong><?= htmlspecialchars($reg['pseudo']) ?></strong></td>
                                                <td><?= htmlspecialchars($reg['email']) ?></td>
                                                <td><?= htmlspecialchars($reg['phone'] ?: '-') ?></td>
                                                <td><?= date('d/m/Y H:i', strtotime($reg['registered_at'])) ?></td>
                                                <td>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="registration_id" value="<?= $reg['id'] ?>">
                                                        <button type="submit" name="delete_registration" class="btn-icon-danger" 
                                                                onclick="return confirm('Supprimer cette inscription ?')" title="Supprimer">
                                                            <?= icon('trash') ?>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                <?php endforeach; ?>
            </section>

            <!-- Export -->
            <section class="export-section">
                <h2>📥 Exporter les données</h2>
                <p>Exportez la liste des inscrits pour impression ou traitement externe.</p>
                <button onclick="window.print()" class="btn btn-secondary">🖨️ Imprimer</button>
            </section>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
