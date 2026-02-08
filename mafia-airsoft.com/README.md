# 🎮 Mafia Airsoft Team - Plateforme de Gestion

![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1?style=flat&logo=mysql&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-ES6+-F7DF1E?style=flat&logo=javascript&logoColor=black)
![License](https://img.shields.io/badge/License-MIT-green.svg)

Système de gestion complet pour association d'airsoft avec billetterie automatisée, QR codes, gestion d'équipes dynamiques, blog avec galeries d'images et carrousel photo.

---

## ✨ Fonctionnalités principales

| Module | Description | Statut |
|--------|-------------|--------|
| 🎫 **Billetterie QR** | Génération auto PDF + email + scan mobile | ✅ |
| 👥 **Équipes dynamiques** | Personnalisation complète par événement | ✅ |
| 📰 **Blog** | Articles avec galerie d'images intégrée | ✅ |
| 🖼️ **Galerie principale** | Carrousel automatique avec modal | ✅ |
| 📅 **Événements** | Création, édition, inscriptions temps réel | ✅ |
| 👤 **Profils** | Gestion joueurs et administrateurs | ✅ |
| 🔐 **Auth** | Authentification sécurisée (bcrypt) | ✅ |
| 🔑 **Hash Tool** | Outil admin de hachage de mots de passe | ✅ |

---

## 📋 Table des matières

- [Technologies](#-technologies)
- [Installation](#-installation)
- [Structure du projet](#-structure-du-projet)
- [Modules détaillés](#-modules-détaillés)
  - [Billetterie QR Code](#-billetterie-qr-code)
  - [Équipes dynamiques](#-équipes-dynamiques)
  - [Blog avec galeries](#-blog-avec-galeries)
  - [Galerie principale](#-galerie-principale)
- [Base de données](#-base-de-données)
- [API](#-api)
- [Sécurité](#-sécurité)
- [Déploiement](#-déploiement)

---

## 🚀 Technologies

### Backend
- **PHP 8.2+** - Langage serveur
- **MySQL/MariaDB** - Base de données relationnelle
- **PDO** - Accès base de données avec requêtes préparées
- **Composer** - Gestionnaire de dépendances

### Dépendances PHP (via Composer)
```json
{
  "chillerlan/php-qrcode": "^5.0",    // Génération QR codes
  "tecnickcom/tcpdf": "^6.7",         // Création PDF
  "phpmailer/phpmailer": "^6.9"       // Envoi emails SMTP
}
```

### Frontend
- **HTML5** - Structure sémantique (API Camera pour scanner)
- **CSS3** - Design moderne avec gradients tactiques
- **JavaScript Vanilla** - Interactivité (carrousel, modal, scan QR)
- **html5-qrcode** - Scanner QR via caméra mobile

### Serveur
- **Apache/XAMPP** - Serveur web
- **mod_rewrite** - URL rewriting
- **Extension GD** - Manipulation d'images

---

## 📦 Installation

### Prérequis

- **XAMPP** (Apache + PHP 8.2+ + MySQL) ou stack équivalente
- **Composer** : https://getcomposer.org/download/
- **Extension PHP GD** activée (génération QR codes)
- **Compte Gmail** avec mot de passe d'application (emails)

### Étapes d'installation

#### 1. Cloner le projet

```bash
cd c:\xampp\htdocs
git clone [url-du-depot] mafia-airsoft.com
cd mafia-airsoft.com
```

#### 2. Installer les dépendances Composer

```bash
composer install
```

Cela installe automatiquement :
- `chillerlan/php-qrcode` (génération QR codes)
- `tecnickcom/tcpdf` (création PDF billets)
- `phpmailer/phpmailer` (envoi emails)

#### 3. Activer l'extension GD

1. Ouvrir **XAMPP Control Panel**
2. Cliquer **Config** → **PHP (php.ini)**
3. Chercher `;extension=gd`
4. Enlever le `;` : `extension=gd`
5. Redémarrer Apache

**Vérification :**
```bash
php -m | findstr gd
```

#### 4. Créer la base de données

Dans **phpMyAdmin** :

```sql
CREATE DATABASE mafia_airsoft CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Importer dans cet ordre :
1. `database/schema.sql` (structure principale)
2. `database/tickets_system.sql` (billetterie)
3. `database/update_dynamic_teams.sql` (équipes)
4. `database/gallery_table.sql` (galerie)
5. `database/blog_gallery.sql` (galerie blog)

#### 5. Configurer la connexion BDD

Éditer `config/database.php` :

```php
<?php
$host = 'localhost';
$dbname = 'mafia_airsoft';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch (PDOException $e) {
    die('Erreur de connexion : ' . $e->getMessage());
}
?>
```

#### 6. Configurer l'envoi d'emails

Créer un mot de passe d'application Gmail :
1. Activer la validation en 2 étapes sur Google
2. Aller sur : https://myaccount.google.com/apppasswords
3. Générer un mot de passe d'application

Éditer `qr-code/email_config.php` :

```php
<?php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'votre-email@gmail.com');
define('SMTP_PASSWORD', 'xfpu vnex dval mrzx'); // Mot de passe app
define('SMTP_SECURE', 'tls');
define('SMTP_FROM_EMAIL', 'votre-email@gmail.com');
define('SMTP_FROM_NAME', 'Mafia Airsoft Team');
?>
```

#### 7. Créer un compte administrateur

Utiliser l'outil de hachage :
1. Accéder à : `http://localhost/mafia-airsoft.com/admin/hash_password.php`
2. Entrer un mot de passe sécurisé
3. Copier le hash généré
4. Insérer dans la BDD :

```sql
INSERT INTO users (pseudo, email, password_hash, is_admin) 
VALUES ('admin', 'admin@mat.com', 'HASH_COPIE_ICI', 1);
```

#### 8. Vérifier les permissions

S'assurer que les dossiers existent avec les bonnes permissions :

```bash
# Windows
icacls "c:\xampp\htdocs\mafia-airsoft.com\uploads" /grant "Tout le monde:(OI)(CI)F" /T
```

Les dossiers nécessaires :
- `uploads/tickets/` (billets PDF)
- `uploads/qrcodes/` (QR codes PNG)
- `uploads/gallery/` (photos galerie principale)
- `uploads/blog/` (images articles + galeries blog)
- `uploads/profiles/` (photos profils)

### ✅ Installation terminée !

Accéder à :
- **Site** : http://localhost/mafia-airsoft.com/
- **Admin** : http://localhost/mafia-airsoft.com/admin/
- **Scanner** : http://localhost/mafia-airsoft.com/qr-code/scan.php
- **Dashboard billets** : http://localhost/mafia-airsoft.com/qr-code/dashboard.php

---

## 📁 Structure du projet

```
mafia-airsoft.com/
│
├── 📄 index.php                    # Page d'accueil avec carrousel
├── 📄 login.php                    # Connexion
├── 📄 logout.php                   # Déconnexion
├── 📄 events.php                   # Liste des événements
├── 📄 event.php                    # Détail + inscription
├── 📄 blog.php                     # Liste des articles
├── 📄 blog_post.php                # Article avec galerie
├── 📄 404.php                      # Page erreur
├── 📄 composer.json                # Dépendances
├── 📄 .gitignore                   # Exclusions Git
├── 📄 .htaccess                    # Config Apache
│
├── 📁 admin/                       # Interface d'administration
│   ├── index.php                   # Dashboard admin
│   ├── create_event.php            # Créer événement
│   ├── edit_event.php              # Modifier événement
│   ├── delete_event.php            # Supprimer événement
│   ├── view_event.php              # Détails événement
│   ├── manage_blog.php             # Gestion blog + galeries
│   ├── manage_gallery.php          # Gestion galerie principale
│   ├── manage_teams.php            # Gestion équipes dynamiques
│   ├── players.php                 # Liste joueurs
│   └── hash_password.php           # Outil hachage mdp
│
├── 📁 player/                      # Espace joueur
│   └── dashboard.php               # Tableau de bord
│
├── 📁 qr-code/                     # Système billetterie
│   ├── .htaccess                   # Protection fichiers
│   ├── email_config.php            # Config SMTP
│   ├── generate_ticket.php         # Classe TicketGenerator
│   ├── send_ticket_email.php       # Classe TicketEmailer
│   ├── ticket_integration.php      # Intégration événements
│   ├── validate_ticket.php         # API validation
│   ├── scan.php                    # Scanner mobile
│   └── dashboard.php               # Dashboard billets
│
├── 📁 config/                      # Configuration
│   ├── database.php                # Connexion PDO
│   ├── session.php                 # Gestion sessions
│   ├── paths.php                   # Chemins absolus
│   └── team_helpers.php            # Fonctions équipes
│
├── 📁 includes/                    # Composants
│   ├── header.php                  # En-tête
│   ├── footer.php                  # Pied de page
│   └── icons.php                   # Icônes SVG
│
├── 📁 database/                    # Scripts SQL
│   ├── schema.sql                  # Schéma principal
│   ├── tickets_system.sql          # Billetterie
│   ├── update_dynamic_teams.sql    # Équipes
│   ├── gallery_table.sql           # Galerie
│   ├── blog_gallery.sql            # Galerie blog
│   └── update_v2.sql               # Mises à jour
│
├── 📁 css/                         # Styles
│   └── style.css                   # CSS principal (4560 lignes)
│
├── 📁 js/                          # Scripts JavaScript
│   ├── main.js                     # Script principal
│   ├── event.js                    # Événements
│   └── gallery.js                  # Carrousel galerie
│
├── 📁 images/                      # Images du site
│   ├── logo.png                    # Logo MAT
│   ├── favicon.ico                 # Icône navigateur
│   └── icons/                      # Icônes diverses
│
├── 📁 uploads/                     # Fichiers uploadés
│   ├── tickets/                    # Billets PDF générés
│   ├── qrcodes/                    # QR codes PNG
│   ├── gallery/                    # Photos galerie principale
│   ├── blog/                       # Images articles + galeries blog
│   └── profiles/                   # Photos profils joueurs
│
└── 📁 vendor/                      # Dépendances Composer (auto)
    ├── chillerlan/php-qrcode/
    ├── tecnickcom/tcpdf/
    └── phpmailer/phpmailer/
```

---

## 🎯 Modules détaillés

### 🎫 Billetterie QR Code

Système complet de génération, envoi et validation de billets avec QR codes uniques.

#### Architecture

```
event.php (inscription)
    ↓
ticket_integration.php::processTicketAfterRegistration()
    ↓
generate_ticket.php::TicketGenerator
    ├── generateQRCode() → uploads/qrcodes/TKT-XXX.png
    └── generatePDF() → uploads/tickets/TKT-XXX.pdf
    ↓
send_ticket_email.php::TicketEmailer
    └── sendTicket() → Email avec PDF attaché
    ↓
event_tickets (BDD)
```

#### Fonctionnalités

**Pour les participants :**
- Inscription à un événement → Génération automatique
- Email instantané avec billet PDF attaché
- QR code unique par billet
- Informations complètes (événement, équipe, date, lieu)

**Pour les admins :**
1. **Scanner mobile** (`qr-code/scan.php`) :
   - Accès caméra smartphone (HTTPS requis)
   - Scan automatique du QR code
   - Validation temps réel avec feedback visuel/sonore
   - Saisie manuelle possible (fallback)

2. **Dashboard** (`qr-code/dashboard.php`) :
   - Statistiques par événement (total, scannés, en attente)
   - Liste complète des billets
   - Filtres : tous / scannés / en attente
   - Recherche : nom, email, code billet
   - Auto-refresh toutes les 30s

#### Format des codes

```
TKT-XXXXXXXXXXXX
    └─ 12 caractères aléatoires (alphanumériques)
```

Exemple : `TKT-A7F3K9M2P5Q1`

#### API de validation

**Endpoint** : `POST qr-code/validate_ticket.php`

**Requête :**
```json
{
  "ticket_code": "TKT-XXXXXXXXXXXX"
}
```

**Réponse succès :**
```json
{
  "status": "success",
  "message": "Billet validé avec succès",
  "data": {
    "ticket_id": 42,
    "event_name": "Partie du 15 janvier 2024",
    "user_name": "Jean Dupont",
    "team": "Équipe Bleue",
    "scanned_at": "2024-01-15 14:30:00"
  }
}
```

**Réponse erreur :**
```json
{
  "status": "error",
  "message": "Billet déjà scanné le 15/01/2024 à 14:30"
}
```

#### Classe TicketGenerator

```php
class TicketGenerator {
    private $pdo;
    private $eventId;
    private $userId;
    private $ticketCode;
    
    // Génère un code unique
    private function generateTicketCode(): string
    
    // Génère le QR code PNG
    public function generateQRCode(): string
    
    // Génère le PDF avec TCPDF
    public function generatePDF(): string
    
    // Processus complet
    public function createTicket(): array
}
```

#### Classe TicketEmailer

```php
class TicketEmailer {
    private $mailer;
    
    // Configure PHPMailer avec SMTP
    private function setupMailer()
    
    // Envoie l'email avec PDF attaché
    public function sendTicket($recipientEmail, $recipientName, $pdfPath, $eventDetails): bool
}
```

#### Intégration automatique

```php
// Dans event.php, lors de l'inscription
require_once 'qr-code/ticket_integration.php';
$result = processTicketAfterRegistration($pdo, $eventId, $userId);

if ($result['success']) {
    echo "✅ Inscription réussie ! Billet envoyé par email.";
} else {
    echo "⚠️ Inscription OK mais erreur d'envoi du billet : " . $result['error'];
}

// Lors de la désinscription
deleteTicketAfterUnregistration($pdo, $eventId, $userId);
```

#### Sécurité

- ✅ Codes uniques vérifiés en BDD (pas de doublons)
- ✅ Détection des billets déjà scannés
- ✅ Vérification de la date d'événement (pas de scan après)
- ✅ Authentification admin requise pour scanner
- ✅ Validation côté serveur (pas de manipulation client)

---

### 👥 Équipes dynamiques

Système flexible permettant de personnaliser entièrement les équipes pour chaque événement.

#### Concept

Au lieu d'avoir des équipes fixes (Bleu, Rouge, Neutre), chaque événement peut définir ses propres équipes avec :
- **Nom personnalisé** (ex: "Alpha", "Zombies", "Snipers")
- **Couleur au choix** (sélecteur de couleur hexadécimal)
- **Limite de joueurs** ajustable
- **Ordre d'affichage** personnalisable

#### Table `event_teams`

```sql
CREATE TABLE event_teams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    team_key VARCHAR(50) NOT NULL,        -- Identifiant unique (BLUE, RED, ALPHA, etc.)
    team_name VARCHAR(100) NOT NULL,      -- Nom affiché
    team_color VARCHAR(7) NOT NULL,       -- Couleur hex (#RRGGBB)
    max_players INT NOT NULL,             -- Limite de joueurs
    display_order INT DEFAULT 0,          -- Ordre d'affichage
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_team_per_event (event_id, team_key),
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
);
```

#### Utilisation admin

**Créer un événement** (`admin/create_event.php`) :
- Remplir les infos de base
- Cliquer **"Créer"**
- 3 équipes par défaut sont créées automatiquement :
  - 🔵 Équipe Bleue (#3b82f6) - 15 joueurs
  - 🔴 Équipe Rouge (#dc2626) - 15 joueurs
  - ⚪ Organisation (#a3a3a3) - 3 joueurs

**Gérer les équipes** (`admin/manage_teams.php`) :
1. Depuis la liste des événements, cliquer **"Gérer équipes"**
2. **Ajouter** :
   - Clé : Identifiant unique (ex: `GREEN`, `SNIPER`, `ALPHA`)
   - Nom : Nom affiché (ex: "Équipe Verte", "Snipers")
   - Couleur : Sélecteur de couleur
   - Limite : Nombre max de joueurs
3. **Modifier** :
   - Nom, couleur et limite modifiables
   - La clé ne peut pas être changée
4. **Supprimer** :
   - Impossible si des joueurs sont inscrits
   - Déplacer les joueurs d'abord

#### Exemples de configurations

**Partie classique (2 équipes) :**
```
BLUE : Équipe Bleue (#3b82f6) - 20 joueurs
RED  : Équipe Rouge (#dc2626) - 20 joueurs
```

**Partie multi-factions :**
```
ALPHA   : Équipe Alpha (#3b82f6) - 10 joueurs
BRAVO   : Équipe Bravo (#10b981) - 10 joueurs
CHARLIE : Équipe Charlie (#f59e0b) - 10 joueurs
DELTA   : Équipe Delta (#8b5cf6) - 10 joueurs
```

**Partie avec rôles :**
```
ATTACK  : Attaquants (#dc2626) - 15 joueurs
DEFENSE : Défenseurs (#3b82f6) - 15 joueurs
SNIPER  : Snipers (#6b7280) - 4 joueurs
MEDIC   : Médics (#10b981) - 3 joueurs
```

**Partie thématique (Zombies) :**
```
HUMANS  : Survivants (#10b981) - 25 joueurs
ZOMBIES : Infectés (#dc2626) - 15 joueurs
ORGA    : Organisation (#a3a3a3) - 5 joueurs
```

#### Affichage frontend

```php
// Dans event.php
$stmt = $pdo->prepare("
    SELECT et.*,
           (SELECT COUNT(*) FROM registrations 
            WHERE event_id = et.event_id AND team = et.team_key) as current_players
    FROM event_teams et
    WHERE et.event_id = ?
    ORDER BY et.display_order ASC
");
$stmt->execute([$eventId]);
$teams = $stmt->fetchAll();

foreach ($teams as $team) {
    $is_full = ($team['current_players'] >= $team['max_players']);
    $percentage = round(($team['current_players'] / $team['max_players']) * 100);
    
    echo '<div class="team-card" style="border-left: 4px solid ' . $team['team_color'] . '">';
    echo '<h3>' . htmlspecialchars($team['team_name']) . '</h3>';
    echo '<p>' . $team['current_players'] . ' / ' . $team['max_players'] . ' joueurs (' . $percentage . '%)</p>';
    
    if ($is_full) {
        echo '<span class="badge-full">Complet</span>';
    } else {
        echo '<button class="btn-join" data-team="' . $team['team_key'] . '">Rejoindre</button>';
    }
    echo '</div>';
}
```

#### Helper function

```php
// config/team_helpers.php
function getEventTeams($pdo, $eventId) {
    $stmt = $pdo->prepare("
        SELECT et.*,
               (SELECT COUNT(*) FROM registrations 
                WHERE event_id = et.event_id AND team = et.team_key) as current_players
        FROM event_teams et
        WHERE et.event_id = ?
        ORDER BY et.display_order ASC
    ");
    $stmt->execute([$eventId]);
    return $stmt->fetchAll();
}
```

---

### 📰 Blog avec galeries

Système de blog permettant d'ajouter des articles avec une image de présentation **ET** une galerie d'images propre à l'article.

#### Architecture

```
Chaque article peut avoir :
├── featured_image (1 image de présentation)
└── blog_gallery (N images dans une galerie dédiée)
```

#### Table `blog_gallery`

```sql
CREATE TABLE blog_gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    blog_post_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    caption TEXT,                      -- Légende de l'image
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (blog_post_id) REFERENCES blog_posts(id) ON DELETE CASCADE
);
```

#### Fonctionnalités admin

**Créer/Modifier un article** (`admin/manage_blog.php`) :
1. Remplir titre, extrait, contenu
2. **Image de présentation** : Upload ou URL externe
3. **Galerie d'images** :
   - Sélectionner plusieurs images (Ctrl + clic)
   - Aperçu automatique avec champs de légende
   - Upload multiple en une fois
4. Publier ou mettre en brouillon

**Gérer les images de galerie** :
- Affichage des images actuelles
- Suppression individuelle (avec confirmation)
- Ordre d'affichage conservé

#### Upload multiple avec aperçu

```javascript
// Dans manage_blog.php
function previewGalleryImages(event) {
    const files = event.target.files;
    const container = document.getElementById('gallery-preview-container');
    
    container.innerHTML = '';
    
    Array.from(files).forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            // Créer l'aperçu avec champ de légende
            const div = document.createElement('div');
            div.innerHTML = `
                <img src="${e.target.result}" />
                <input type="text" name="gallery_captions[]" 
                       placeholder="Légende (optionnel)..." />
            `;
            container.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
}
```

#### Affichage frontend

**Page article** (`blog_post.php`) :

```php
// Récupérer les images de la galerie
$stmt_gallery = $pdo->prepare("
    SELECT * FROM blog_gallery 
    WHERE blog_post_id = ? 
    ORDER BY display_order ASC
");
$stmt_gallery->execute([$post['id']]);
$gallery_images = $stmt_gallery->fetchAll();
```

```html
<?php if (!empty($gallery_images)): ?>
<div class="blog-gallery">
    <h3>📷 Galerie de photos</h3>
    <div class="blog-gallery-grid">
        <?php foreach ($gallery_images as $index => $image): ?>
            <div class="blog-gallery-item" onclick="openGalleryModal(<?= $index ?>)">
                <img src="<?= $image['image_path'] ?>" alt="<?= $image['caption'] ?>">
                <?php if ($image['caption']): ?>
                    <div class="blog-gallery-caption">
                        <?= htmlspecialchars($image['caption']) ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
```

#### Modal visionneuse

Système de visionneuse lightbox avec navigation :
- Clic sur une image → Modal plein écran
- Navigation : flèches ou clavier (← →)
- Affichage des légendes
- Fermeture : croix, clic extérieur ou Escape

```javascript
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

function changeGalleryImage(direction) {
    currentGalleryIndex += direction;
    if (currentGalleryIndex < 0) {
        currentGalleryIndex = galleryImages.length - 1;
    } else if (currentGalleryIndex >= galleryImages.length) {
        currentGalleryIndex = 0;
    }
    // Mettre à jour l'image et la légende
}
```

#### CSS galerie blog

```css
.blog-gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
}

.blog-gallery-item {
    position: relative;
    overflow: hidden;
    border-radius: 8px;
    cursor: pointer;
    transition: transform 0.3s ease;
}

.blog-gallery-item:hover {
    transform: scale(1.05);
    box-shadow: 0 8px 20px rgba(220, 38, 38, 0.4);
}

.blog-gallery-caption {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(to top, rgba(0, 0, 0, 0.9), transparent);
    color: white;
    padding: 15px 10px 10px;
    transform: translateY(100%);
    transition: transform 0.3s ease;
}

.blog-gallery-item:hover .blog-gallery-caption {
    transform: translateY(0);
}
```

#### Cas d'usage

**Article de partie** :
- Image de présentation : Photo de groupe
- Galerie : 10-15 photos de la partie (action, terrain, équipes)

**Article tutoriel** :
- Image de présentation : Schéma principal
- Galerie : Photos étape par étape

**Compte-rendu événement** :
- Image de présentation : Affiche de l'événement
- Galerie : Photos des moments forts

---

### 🖼️ Galerie principale

Carrousel automatique de photos sur la page d'accueil avec navigation et responsive.

#### Fonctionnalités

- ✅ Carrousel automatique (5 secondes par slide)
- ✅ Navigation : flèches, dots, swipe mobile
- ✅ Pause au survol
- ✅ Légendes avec titre et description
- ✅ Responsive avec recalcul au resize
- ✅ Transition smooth (0.5s ease-in-out)

#### Table `gallery`

```sql
CREATE TABLE gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200),
    description TEXT,
    image_path VARCHAR(255) NOT NULL,
    display_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### Affichage frontend (index.php)

```php
// Récupérer les photos actives
$stmt = $pdo->query("
    SELECT * FROM gallery 
    WHERE is_active = 1 
    ORDER BY display_order ASC, created_at DESC 
    LIMIT 10
");
$gallery_photos = $stmt->fetchAll();
```

```html
<div class="gallery-carousel">
    <div class="carousel-container">
        <div class="carousel-track">
            <?php foreach ($gallery_photos as $photo): ?>
                <div class="carousel-slide">
                    <img src="<?= $photo['image_path'] ?>" alt="<?= $photo['title'] ?>">
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
        
        <!-- Boutons navigation -->
        <button class="carousel-btn carousel-btn-prev">‹</button>
        <button class="carousel-btn carousel-btn-next">›</button>
    </div>
    
    <!-- Dots navigation -->
    <div class="carousel-nav">
        <?php foreach ($gallery_photos as $index => $photo): ?>
            <button class="carousel-dot <?= $index === 0 ? 'active' : '' ?>"></button>
        <?php endforeach; ?>
    </div>
</div>
```

#### JavaScript carrousel (gallery.js)

```javascript
document.addEventListener('DOMContentLoaded', function() {
    const carousel = document.querySelector('.gallery-carousel');
    if (!carousel) return;
    
    const track = carousel.querySelector('.carousel-track');
    const slides = Array.from(track.children);
    const nextButton = carousel.querySelector('.carousel-btn-next');
    const prevButton = carousel.querySelector('.carousel-btn-prev');
    const dots = Array.from(carousel.querySelectorAll('.carousel-dot'));
    
    let currentIndex = 0;
    let autoplayInterval;
    const autoplayDelay = 5000; // 5 secondes
    
    // Déplacer le carrousel
    const moveToSlide = (targetIndex) => {
        const slideWidth = slides[0].getBoundingClientRect().width;
        track.style.transform = 'translateX(-' + slideWidth * targetIndex + 'px)';
        currentIndex = targetIndex;
        
        // Mettre à jour les dots
        dots.forEach(dot => dot.classList.remove('active'));
        dots[targetIndex].classList.add('active');
    };
    
    // Navigation
    nextButton.addEventListener('click', () => {
        const nextIndex = (currentIndex + 1) % slides.length;
        moveToSlide(nextIndex);
        resetAutoplay();
    });
    
    prevButton.addEventListener('click', () => {
        const prevIndex = (currentIndex - 1 + slides.length) % slides.length;
        moveToSlide(prevIndex);
        resetAutoplay();
    });
    
    // Dots navigation
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            moveToSlide(index);
            resetAutoplay();
        });
    });
    
    // Autoplay
    const startAutoplay = () => {
        autoplayInterval = setInterval(() => {
            const nextIndex = (currentIndex + 1) % slides.length;
            moveToSlide(nextIndex);
        }, autoplayDelay);
    };
    
    const stopAutoplay = () => {
        if (autoplayInterval) {
            clearInterval(autoplayInterval);
        }
    };
    
    const resetAutoplay = () => {
        stopAutoplay();
        startAutoplay();
    };
    
    // Pause au survol
    carousel.addEventListener('mouseenter', stopAutoplay);
    carousel.addEventListener('mouseleave', startAutoplay);
    
    // Swipe mobile
    let touchStartX = 0;
    let touchEndX = 0;
    
    track.addEventListener('touchstart', (e) => {
        touchStartX = e.changedTouches[0].screenX;
    });
    
    track.addEventListener('touchend', (e) => {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    });
    
    const handleSwipe = () => {
        if (touchEndX < touchStartX - 50) {
            // Swipe left
            const nextIndex = (currentIndex + 1) % slides.length;
            moveToSlide(nextIndex);
            resetAutoplay();
        }
        if (touchEndX > touchStartX + 50) {
            // Swipe right
            const prevIndex = (currentIndex - 1 + slides.length) % slides.length;
            moveToSlide(prevIndex);
            resetAutoplay();
        }
    };
    
    // Responsive : recalculer au resize
    let resizeTimeout;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            moveToSlide(currentIndex);
        }, 250);
    });
    
    // Démarrer l'autoplay
    if (slides.length > 1) {
        startAutoplay();
    }
});
```

#### CSS carrousel

```css
.carousel-container {
    position: relative;
    width: 100%;
    overflow: hidden;
    border-radius: 8px;
    background: #0f0f0f;
    border: 2px solid rgba(220, 38, 38, 0.3);
}

.carousel-track {
    display: flex;
    transition: transform 0.5s ease-in-out;
}

.carousel-slide {
    min-width: 100%;
    position: relative;
    aspect-ratio: 16/9;
}

.carousel-slide img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.carousel-caption {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(to top, rgba(0, 0, 0, 0.95), transparent);
    padding: 2rem;
    color: #ffffff;
}

.carousel-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(220, 38, 38, 0.8);
    color: white;
    border: none;
    font-size: 2rem;
    padding: 1rem;
    cursor: pointer;
    transition: background 0.3s ease;
    z-index: 10;
}

.carousel-btn:hover {
    background: rgba(220, 38, 38, 1);
}

.carousel-btn-prev { left: 20px; }
.carousel-btn-next { right: 20px; }

.carousel-nav {
    display: flex;
    justify-content: center;
    gap: 10px;
    padding: 20px;
}

.carousel-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.5);
    border: none;
    cursor: pointer;
    transition: background 0.3s ease;
}

.carousel-dot.active {
    background: var(--primary);
}
```

#### Gestion admin (manage_gallery.php)

- Upload d'images
- Titre et description (optionnels)
- Ordre d'affichage (drag & drop ou numérique)
- Activation/désactivation
- Suppression avec confirmation

---

## 🗄️ Base de données

### Schéma complet

#### Table `users`
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pseudo VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    profile_picture VARCHAR(255),
    is_admin TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### Table `events`
```sql
CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    scenario TEXT,
    rules TEXT,
    event_date DATETIME NOT NULL,
    location VARCHAR(255),
    max_players INT DEFAULT 50,
    price DECIMAL(10,2) DEFAULT 0.00,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### Table `event_teams`
```sql
CREATE TABLE event_teams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    team_key VARCHAR(50) NOT NULL,
    team_name VARCHAR(100) NOT NULL,
    team_color VARCHAR(7) NOT NULL,
    max_players INT NOT NULL,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_team_per_event (event_id, team_key),
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
);
```

#### Table `registrations`
```sql
CREATE TABLE registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    event_id INT NOT NULL,
    team VARCHAR(50),
    notes TEXT,
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_registration (user_id, event_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
);
```

#### Table `event_tickets`
```sql
CREATE TABLE event_tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    ticket_code VARCHAR(20) NOT NULL UNIQUE,
    qr_code_path VARCHAR(255),
    pdf_path VARCHAR(255),
    is_scanned TINYINT(1) DEFAULT 0,
    scanned_at TIMESTAMP NULL,
    scanned_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (scanned_by) REFERENCES users(id) ON DELETE SET NULL
);
```

#### Table `blog_posts`
```sql
CREATE TABLE blog_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(220) NOT NULL UNIQUE,
    content TEXT NOT NULL,
    excerpt TEXT,
    featured_image VARCHAR(255),
    author_id INT NOT NULL,
    is_published TINYINT(1) DEFAULT 0,
    published_at DATETIME,
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
);
```

#### Table `blog_gallery`
```sql
CREATE TABLE blog_gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    blog_post_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    caption TEXT,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (blog_post_id) REFERENCES blog_posts(id) ON DELETE CASCADE
);
```

#### Table `gallery`
```sql
CREATE TABLE gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200),
    description TEXT,
    image_path VARCHAR(255) NOT NULL,
    display_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Relations

```
users (1) ─── (N) registrations ─── (1) events
users (1) ─── (N) event_tickets ─── (1) events
users (1) ─── (N) blog_posts
events (1) ─── (N) event_teams
blog_posts (1) ─── (N) blog_gallery
```

### Index

```sql
-- Performance inscriptions
CREATE INDEX idx_registrations_event ON registrations(event_id);
CREATE INDEX idx_registrations_user ON registrations(user_id);
CREATE INDEX idx_registrations_team ON registrations(team);

-- Performance billetterie
CREATE INDEX idx_tickets_event ON event_tickets(event_id);
CREATE INDEX idx_tickets_code ON event_tickets(ticket_code);
CREATE INDEX idx_tickets_scanned ON event_tickets(is_scanned);

-- Performance blog
CREATE INDEX idx_blog_slug ON blog_posts(slug);
CREATE INDEX idx_blog_published ON blog_posts(is_published);
CREATE INDEX idx_blog_date ON blog_posts(published_at);

-- Performance équipes
CREATE INDEX idx_teams_event ON event_teams(event_id);
CREATE INDEX idx_teams_key ON event_teams(team_key);
```

---

## 🔌 API

### Validation de billets

**Endpoint** : `POST qr-code/validate_ticket.php`

**Authentification** : Session admin requise

**Requête :**
```json
{
  "ticket_code": "TKT-A7F3K9M2P5Q1"
}
```

**Réponses :**

```json
// Succès (200)
{
  "status": "success",
  "message": "Billet validé avec succès",
  "data": {
    "ticket_id": 42,
    "event_name": "Partie du 15 janvier 2024",
    "event_date": "2024-01-15 09:00:00",
    "user_name": "Jean Dupont",
    "team": "Équipe Bleue",
    "scanned_at": "2024-01-15 08:45:00"
  }
}

// Erreur - Déjà scanné (400)
{
  "status": "error",
  "message": "Billet déjà scanné le 15/01/2024 à 08:45"
}

// Erreur - Non trouvé (404)
{
  "status": "error",
  "message": "Billet non trouvé"
}

// Erreur - Événement expiré (400)
{
  "status": "error",
  "message": "L'événement est terminé depuis 2 jours"
}

// Erreur - Auth (401)
{
  "status": "error",
  "message": "Authentification requise"
}
```

### Helper functions

```php
// config/team_helpers.php

// Récupérer les équipes d'un événement avec stats
function getEventTeams($pdo, $eventId): array

// Récupérer un événement avec ses équipes
function getEventsWithTeams($pdo, $whereClause = '1=1'): array

// Vérifier si une équipe est pleine
function isTeamFull($pdo, $eventId, $teamKey): bool

// Compter les inscrits d'une équipe
function countTeamRegistrations($pdo, $eventId, $teamKey): int
```

```php
// qr-code/ticket_integration.php

// Générer et envoyer un billet après inscription
function processTicketAfterRegistration($pdo, $eventId, $userId): array

// Supprimer un billet après désinscription
function deleteTicketAfterUnregistration($pdo, $eventId, $userId): bool
```

---

## 🔒 Sécurité

### Authentification

- **Algorithme** : bcrypt (PASSWORD_DEFAULT)
- **Salage** : Automatique par PHP
- **Vérification** : `password_verify()` côté serveur
- **Sessions** : Sécurisées avec `session_regenerate_id()`

### Requêtes SQL

- **PDO** : Requêtes préparées uniquement
- **Paramètres bindés** : `?` ou `:named`
- **Pas d'interpolation** : Jamais de concaténation directe

```php
// ✅ Bon
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);

// ❌ Mauvais (injection SQL)
$stmt = $pdo->query("SELECT * FROM users WHERE id = $userId");
```

### XSS Protection

- **Échappement** : `htmlspecialchars()` en sortie
- **Contexte** : Adapté selon HTML, JS, CSS
- **ENT_QUOTES** : Échapper les guillemets simples et doubles

```php
// ✅ Bon
echo '<p>' . htmlspecialchars($userInput, ENT_QUOTES, 'UTF-8') . '</p>';

// ❌ Mauvais
echo '<p>' . $userInput . '</p>';
```

### CSRF Protection

- **Tokens** : Génération et vérification
- **Formulaires** : Token inclus dans chaque form
- **Vérification** : Côté serveur avant traitement

```php
// Génération
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Vérification
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    die('Token CSRF invalide');
}
```

### Upload de fichiers

- **Validation** : Type MIME et extension
- **Taille** : Limite à 5 MB par défaut
- **Noms** : Sanitizés et uniques (timestamp + uniqid)
- **Stockage** : Dossier `uploads/` séparé du code

```php
$allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$extension = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

if (!in_array($extension, $allowed)) {
    die('Type de fichier non autorisé');
}

if ($_FILES['file']['size'] > 5 * 1024 * 1024) {
    die('Fichier trop volumineux (max 5 MB)');
}

$newName = 'upload_' . time() . '_' . uniqid() . '.' . $extension;
move_uploaded_file($_FILES['file']['tmp_name'], 'uploads/' . $newName);
```

### Headers de sécurité

```apache
# .htaccess
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
    Header set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>
```

### Protection des fichiers sensibles

```apache
# .htaccess
<FilesMatch "(database\.php|email_config\.php|\.env)">
    Order allow,deny
    Deny from all
</FilesMatch>

Options -Indexes
```

### Billetterie

- **Codes uniques** : Vérification en BDD
- **Double scan** : Détection et refus
- **Expiration** : Vérification de la date d'événement
- **Auth admin** : Requis pour scanner

---

## 🚀 Déploiement

Voir le guide complet : [DEPLOYMENT.md](DEPLOYMENT.md)

### Checklist production

- [ ] Base de données créée et importée
- [ ] `config/database.php` configuré
- [ ] `qr-code/email_config.php` configuré
- [ ] Composer : `composer install --no-dev --optimize-autoloader`
- [ ] Extension GD activée
- [ ] Permissions `uploads/` correctes (755)
- [ ] HTTPS/SSL activé (Let's Encrypt)
- [ ] Erreurs PHP désactivées (`display_errors = Off`)
- [ ] Fichiers sensibles protégés (.htaccess)
- [ ] Sauvegardes automatiques configurées
- [ ] Compte admin créé avec mot de passe fort
- [ ] Test inscription + billet + email OK
- [ ] Test scanner QR mobile OK

### Environnements

**Local (XAMPP) :**
```
URL: http://localhost/mafia-airsoft.com/
BDD: localhost
SMTP: Gmail (test)
```

**Production :**
```
URL: https://votre-domaine.com/
BDD: localhost ou serveur distant
SMTP: Gmail ou serveur professionnel
SSL: Let's Encrypt (gratuit)
```

### Optimisations

1. **OPcache** : Activer dans php.ini
2. **Compression** : Gzip activé (.htaccess)
3. **Cache navigateur** : Expires headers (.htaccess)
4. **CDN** : Pour assets statiques (optionnel)
5. **Lazy loading** : Images chargées à la demande

---

## 🐛 Dépannage

### Emails non envoyés

**Vérifications :**
1. SMTP_PASSWORD = mot de passe d'app (pas le mdp du compte)
2. Port 587 non bloqué par firewall
3. Extension `openssl` activée dans php.ini
4. Logs : `tail -f error.log`

**Test SMTP :**
```php
// test-email.php
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;

$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->Port = 587;
$mail->SMTPAuth = true;
$mail->Username = 'votre-email@gmail.com';
$mail->Password = 'votre-mdp-app';
$mail->SMTPSecure = 'tls';
$mail->setFrom('votre-email@gmail.com');
$mail->addAddress('test@example.com');
$mail->Subject = 'Test';
$mail->Body = 'Test email';

if ($mail->send()) {
    echo "✅ Email envoyé";
} else {
    echo "❌ Erreur : " . $mail->ErrorInfo;
}
```

### QR codes non générés

**Vérifications :**
```bash
# Extension GD
php -m | findstr gd

# Permissions uploads
icacls "c:\xampp\htdocs\mafia-airsoft.com\uploads\qrcodes"

# Composer
composer show chillerlan/php-qrcode
```

**Solution :**
```bash
# Activer GD dans php.ini
extension=gd

# Redémarrer Apache
```

### Scanner QR ne fonctionne pas

**Causes :**
1. ❌ HTTPS non activé → **Obligatoire pour caméra**
2. ❌ Permissions caméra refusées → Vérifier navigateur
3. ❌ Auth admin requise → Se connecter d'abord

**Solution mobile :**
- Chrome/Safari : Autoriser accès caméra
- Utiliser HTTPS (même en local avec certificat auto-signé)

### Galerie carrousel : Image 2 invisible

**Problème :** Fond gris au lieu de l'image

**Cause :** JavaScript utilisait `style.left` au lieu de `translateX`

**Solution :** Utiliser seulement `transform: translateX()` sur le track
```javascript
// ✅ Bon
track.style.transform = 'translateX(-' + slideWidth * index + 'px)';

// ❌ Mauvais
slide.style.left = slideWidth * index + 'px';
```

---

## 📊 Statistiques du projet

### Lignes de code

| Fichier | Lignes |
|---------|--------|
| `css/style.css` | 4560 |
| `js/gallery.js` | 120 |
| `admin/manage_blog.php` | 250 |
| `qr-code/generate_ticket.php` | 300 |
| `blog_post.php` | 180 |
| **Total (estimation)** | **~8000+** |

### Base de données

| Table | Champs |
|-------|--------|
| `users` | 7 |
| `events` | 11 |
| `event_teams` | 8 |
| `registrations` | 6 |
| `event_tickets` | 10 |
| `blog_posts` | 12 |
| `blog_gallery` | 6 |
| `gallery` | 7 |
| **Total** | **67 champs** |

### Modules

- 🎫 Billetterie : 6 fichiers
- 👥 Équipes dynamiques : 1 fichier + helpers
- 📰 Blog : 3 fichiers
- 🖼️ Galeries : 2 systèmes
- 📅 Événements : 5 fichiers admin
- 👤 Utilisateurs : 3 fichiers

---

## 📞 Support

### Documentation

- **PHP** : https://www.php.net/manual/fr/
- **PDO** : https://www.php.net/manual/fr/book.pdo.php
- **Composer** : https://getcomposer.org/doc/
- **chillerlan/php-qrcode** : https://github.com/chillerlan/php-qrcode
- **TCPDF** : https://tcpdf.org/
- **PHPMailer** : https://github.com/PHPMailer/PHPMailer

### Logs

**Windows (XAMPP) :**
```powershell
Get-Content "c:\xampp\apache\logs\error.log" -Tail 50
Get-Content "c:\xampp\mysql\data\mysql_error.log" -Tail 50
```

**Linux :**
```bash
tail -f /var/log/apache2/error.log
tail -f /var/log/mysql/error.log
```

### Contribuer

Pour toute suggestion, bug ou amélioration, contactez l'administrateur système.

---

## 📝 License

Ce projet est sous licence MIT. Voir [LICENSE](LICENSE) pour plus de détails.

---

## 🎉 Remerciements

Développé pour la **Mafia Airsoft Team** avec ❤️

**Technologies utilisées :**
- PHP, MySQL, JavaScript
- chillerlan/php-qrcode, TCPDF, PHPMailer
- HTML5, CSS3

**Version** : 2.1.0  
**Dernière mise à jour** : 30 novembre 2025

---

**🎮 Bon jeu et bonnes parties !**
