<?php
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'config/team_helpers.php';
require_once 'qr-code/ticket_integration.php';

$event_id = $_GET['id'] ?? 0;
$message = '';
$error = '';
$ticket_warning = '';

// Récupérer les détails de la partie avec les équipes
$event = getEventWithTeams($pdo, $event_id);

if (!$event) {
    header('Location: events.php');
    exit;
}

// Vérifier si l'utilisateur est déjà inscrit
$user_registration = null;
if (isLoggedIn()) {
    $stmt = $pdo->prepare("SELECT * FROM registrations WHERE user_id = ? AND event_id = ?");
    $stmt->execute([$_SESSION['user_id'], $event_id]);
    $user_registration = $stmt->fetch();
}

// Gérer l'inscription
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isLoggedIn()) {
    $action = $_POST['action'] ?? '';
    $team = $_POST['team'] ?? '';
    
    if ($action === 'register' && !empty($team)) {
        // Vérifier que l'équipe existe
        $team_info = getEventTeam($pdo, $event_id, $team);
        
        if (!$team_info) {
            $error = 'Cette équipe n\'existe pas.';
        }
        // Vérifier que les inscriptions sont ouvertes
        elseif (!$event['registration_open']) {
            $error = 'Les inscriptions sont fermées pour cette partie.';
        }
        // Vérifier si déjà inscrit
        elseif ($user_registration) {
            $error = 'Vous êtes déjà inscrit à cette partie.';
        }
        // Vérifier les places disponibles
        elseif ($team_info['current_players'] >= $team_info['max_players']) {
            $error = 'Cette équipe est complète.';
        } else {
            // Inscrire le joueur
            $stmt = $pdo->prepare("INSERT INTO registrations (user_id, event_id, team) VALUES (?, ?, ?)");
            if ($stmt->execute([$_SESSION['user_id'], $event_id, $team])) {
                // 🎫 GÉNÉRATION AUTOMATIQUE DU BILLET
                $ticketResult = processTicketAfterRegistration($pdo, $event_id, $_SESSION['user_id']);
                
                if ($ticketResult['success']) {
                    if (isset($ticketResult['warning'])) {
                        $ticket_warning = $ticketResult['warning'];
                    }
                    $message = 'Inscription réussie ! Vous avez rejoint ' . htmlspecialchars($team_info['team_name']) . '. 🎫 Votre billet a été envoyé par email !';
                } else {
                    $message = 'Inscription réussie ! Vous avez rejoint ' . htmlspecialchars($team_info['team_name']) . '.';
                    $ticket_warning = 'Note: ' . $ticketResult['error'];
                }
                
                // Recharger les données
                header('Location: event.php?id=' . $event_id . '&success=1' . ($ticket_warning ? '&ticket_warning=1' : ''));
                exit;
            } else {
                $error = 'Erreur lors de l\'inscription.';
            }
        }
    } elseif ($action === 'unregister') {
        if ($user_registration) {
            // 🎫 SUPPRIMER LE BILLET AVANT DE DÉSINSCRIRE
            deleteTicketAfterUnregistration($pdo, $event_id, $_SESSION['user_id']);
            
            $stmt = $pdo->prepare("DELETE FROM registrations WHERE id = ?");
            if ($stmt->execute([$user_registration['id']])) {
                $message = 'Vous avez été désinscrit de cette partie.';
                header('Location: event.php?id=' . $event_id . '&unregistered=1');
                exit;
            }
        }
    }
}

// Messages de succès depuis les redirections
if (isset($_GET['success'])) {
    $message = 'Inscription réussie ! 🎫 Votre billet a été envoyé par email.';
    if (isset($_GET['ticket_warning'])) {
        $ticket_warning = 'Le billet a été créé mais l\'email n\'a peut-être pas été envoyé. Contactez un administrateur si besoin.';
    }
}
if (isset($_GET['unregistered'])) {
    $message = 'Désinscription effectuée.';
}

// Récupérer la liste des inscrits (visible pour tous)
$stmt = $pdo->prepare("
    SELECT r.team, u.pseudo, u.profile_picture
    FROM registrations r
    JOIN users u ON r.user_id = u.id
    WHERE r.event_id = ?
    ORDER BY r.team, r.registered_at
");
$stmt->execute([$event_id]);
$registrations = $stmt->fetchAll();

$teams = [
    'BLUE' => [],
    'RED' => [],
    'NEUTRAL' => [],
    'ORGA' => []
];

foreach ($registrations as $reg) {
    $teams[$reg['team']][] = [
        'pseudo' => $reg['pseudo'],
        'picture' => $reg['profile_picture']
    ];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($event['title']) ?> - Association Airsoft</title>
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    <link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container page-content">
        <div class="event-detail">
            <!-- En-tête de l'événement -->
            <div class="event-detail-header">
                <h1><?= htmlspecialchars($event['title']) ?></h1>
                <div class="event-meta-large">
                    <span class="meta-item">
                        <strong><?= icon('calendar') ?> Date:</strong> <?= date('l d F Y', strtotime($event['event_date'])) ?>
                    </span>
                    <span class="meta-item">
                        <strong>🕐 Heure:</strong> <?= date('H:i', strtotime($event['event_date'])) ?>
                    </span>
                    <span class="meta-item">
                        <strong>📍 Lieu:</strong> <?= htmlspecialchars($event['location']) ?>
                    </span>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success"><?= $message ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($ticket_warning): ?>
                <div class="alert alert-warning"><?= htmlspecialchars($ticket_warning) ?></div>
            <?php endif; ?>

            <!-- Statut d'inscription de l'utilisateur -->
            <?php if ($user_registration): ?>
                <div class="alert alert-info">
                    <?= icon('check') ?> Vous êtes inscrit au camp <strong><?= $user_registration['team'] ?></strong>
                    <form method="POST" style="display: inline-block; margin-left: 15px;">
                        <input type="hidden" name="action" value="unregister">
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir vous désinscrire ?')">
                            Se désinscrire
                        </button>
                    </form>
                </div>
            <?php endif; ?>

            <div class="event-detail-grid">
                <!-- Colonne gauche: Informations -->
                <div class="event-detail-content">
                    <section class="event-section">
                        <h2><?= icon('list') ?> Description</h2>
                        <p><?= nl2br(htmlspecialchars($event['description'])) ?></p>
                    </section>

                    <section class="event-section">
                        <h2><?= icon('target') ?> Scénario</h2>
                        <div class="scenario-box">
                            <?= nl2br(htmlspecialchars($event['scenario'])) ?>
                        </div>
                    </section>

                    <?php if ($event['equipment_required']): ?>
                    <section class="event-section">
                        <h2>🎒 Matériel recommandé</h2>
                        <p><?= nl2br(htmlspecialchars($event['equipment_required'])) ?></p>
                    </section>
                    <?php endif; ?>

                    <?php if ($event['rules']): ?>
                    <section class="event-section">
                        <h2>📜 Règles spéciales</h2>
                        <p><?= nl2br(htmlspecialchars($event['rules'])) ?></p>
                    </section>
                    <?php endif; ?>
                    
                    <!-- Inscription et équipes en mobile -->
                    <div class="event-detail-sidebar-mobile">
                        <!-- Inscription -->
                        <?php if (isLoggedIn() && !$user_registration && $event['registration_open']): ?>
                            <div class="registration-box">
                                <h3>Rejoindre la partie</h3>
                                <p>Choisissez votre équipe :</p>
                                <form method="POST" class="registration-form">
                                    <input type="hidden" name="action" value="register">
                                    
                                    <?php foreach ($event['teams'] as $team): ?>
                                        <?php $is_full = $team['current_players'] >= $team['max_players']; ?>
                                        <button type="submit" name="team" value="<?= htmlspecialchars($team['team_key']) ?>" 
                                                class="btn-team" 
                                                style="border-left: 4px solid <?= htmlspecialchars($team['team_color']) ?>"
                                                <?= $is_full ? 'disabled' : '' ?>>
                                            <?= htmlspecialchars($team['team_name']) ?>
                                            <span class="team-count"><?= $team['current_players'] ?>/<?= $team['max_players'] ?></span>
                                            <?php if ($is_full): ?>
                                                <span class="team-full">COMPLET</span>
                                            <?php endif; ?>
                                        </button>
                                    <?php endforeach; ?>
                                </form>
                            </div>
                        <?php elseif (!isLoggedIn()): ?>
                            <div class="registration-box">
                                <h3>Rejoindre la partie</h3>
                                <p>Connectez-vous pour vous inscrire à cette partie.</p>
                                <a href="login.php" class="btn btn-primary btn-block">Se connecter</a>
                            </div>
                        <?php elseif (!$event['registration_open']): ?>
                            <div class="alert alert-warning">
                                Inscriptions fermées
                            </div>
                        <?php endif; ?>

                        <!-- Liste des inscrits -->
                        <div class="players-list">
                            <h3><?= icon('users') ?> Joueurs inscrits</h3>
                            
                            <?php foreach ($event['teams'] as $team_info): ?>
                                <?php $team_key = $team_info['team_key']; ?>
                                <div class="team-list">
                                    <h4 class="team-title" style="border-left: 4px solid <?= htmlspecialchars($team_info['team_color']) ?>">
                                        <?= htmlspecialchars($team_info['team_name']) ?> 
                                        (<?= isset($teams[$team_key]) ? count($teams[$team_key]) : 0 ?>/<?= $team_info['max_players'] ?>)
                                    </h4>
                                    <?php if (empty($teams[$team_key])): ?>
                                        <p class="no-players">Aucun joueur inscrit</p>
                                    <?php else: ?>
                                        <div class="team-players">
                                            <?php foreach ($teams[$team_key] as $player): ?>
                                                <div class="player-item">
                                                    <?php if ($player['picture']): ?>
                                                        <img src="<?= htmlspecialchars($player['picture']) ?>" alt="<?= htmlspecialchars($player['pseudo']) ?>" class="player-mini-avatar">
                                                    <?php else: ?>
                                                        <div class="player-mini-avatar"><?= icon('user') ?></div>
                                                    <?php endif; ?>
                                                    <span><?= htmlspecialchars($player['pseudo']) ?></span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Colonne droite: Inscription et équipes (Desktop seulement) -->
                <div class="event-detail-sidebar">
                    <!-- Inscription -->
                    <?php if (isLoggedIn() && !$user_registration && $event['registration_open']): ?>
                        <div class="registration-box">
                            <h3>Rejoindre la partie</h3>
                            <p>Choisissez votre équipe :</p>
                            <form method="POST" class="registration-form">
                                <input type="hidden" name="action" value="register">
                                
                                <?php foreach ($event['teams'] as $team): ?>
                                    <?php $is_full = $team['current_players'] >= $team['max_players']; ?>
                                    <button type="submit" name="team" value="<?= htmlspecialchars($team['team_key']) ?>" 
                                            class="btn-team" 
                                            style="border-left: 4px solid <?= htmlspecialchars($team['team_color']) ?>"
                                            <?= $is_full ? 'disabled' : '' ?>>
                                        <?= htmlspecialchars($team['team_name']) ?>
                                        <span class="team-count"><?= $team['current_players'] ?>/<?= $team['max_players'] ?></span>
                                        <?php if ($is_full): ?>
                                            <span class="team-full">COMPLET</span>
                                        <?php endif; ?>
                                    </button>
                                <?php endforeach; ?>
                            </form>
                        </div>
                    <?php elseif (!isLoggedIn()): ?>
                        <div class="registration-box">
                            <h3>Rejoindre la partie</h3>
                            <p>Connectez-vous pour vous inscrire à cette partie.</p>
                            <a href="login.php" class="btn btn-primary btn-block">Se connecter</a>
                        </div>
                    <?php elseif (!$event['registration_open']): ?>
                        <div class="alert alert-warning">
                            Inscriptions fermées
                        </div>
                    <?php endif; ?>

                    <!-- Liste des inscrits -->
                    <div class="players-list">
                        <h3><?= icon('users') ?> Joueurs inscrits</h3>
                        
                        <?php foreach ($event['teams'] as $team_info): ?>
                            <?php $team_key = $team_info['team_key']; ?>
                            <div class="team-list">
                                <h4 class="team-title" style="border-left: 4px solid <?= htmlspecialchars($team_info['team_color']) ?>">
                                    <?= htmlspecialchars($team_info['team_name']) ?> 
                                    (<?= isset($teams[$team_key]) ? count($teams[$team_key]) : 0 ?>/<?= $team_info['max_players'] ?>)
                                </h4>
                                <?php if (empty($teams[$team_key])): ?>
                                    <p class="no-players">Aucun joueur inscrit</p>
                                <?php else: ?>
                                    <div class="team-players">
                                        <?php foreach ($teams[$team_key] as $player): ?>
                                            <div class="player-item">
                                                <?php if ($player['picture']): ?>
                                                    <img src="<?= htmlspecialchars($player['picture']) ?>" alt="<?= htmlspecialchars($player['pseudo']) ?>" class="player-mini-avatar">
                                                <?php else: ?>
                                                    <div class="player-mini-avatar"><?= icon('user') ?></div>
                                                <?php endif; ?>
                                                <span><?= htmlspecialchars($player['pseudo']) ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="event-detail-footer">
                <a href="events.php" class="btn btn-secondary">← Retour à l'agenda</a>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="js/event.js"></script>
</body>
</html>
