# 🚀 Guide de déploiement - MAT sur serveur public

Ce guide explique comment déployer le projet MAT sur un serveur public (o2Switch ou autre hébergeur).

---

## 📋 Prérequis serveur

Assurez-vous que votre hébergement dispose de :

- ✅ **PHP 8.0+** (idéalement 8.2+)
- ✅ **MySQL 5.7+** ou **MariaDB 10.3+**
- ✅ **Extension PHP GD** (pour QR codes)
- ✅ **Extension PHP PDO** et **PDO_MySQL**
- ✅ **Accès SSH** (recommandé pour Composer)
- ✅ **Accès FTP/SFTP** (pour upload de fichiers)
- ✅ **Cron jobs** (optionnel, pour tâches automatiques)

---

## 📦 Étape 1 : Préparer les fichiers localement

### 1.1 Nettoyer les fichiers de développement

**NE PAS UPLOADER ces fichiers/dossiers :**
- ❌ `vendor/` (sera réinstallé sur le serveur)
- ❌ `config/database.php` (contient vos identifiants locaux)
- ❌ `qr-code/email_config.php` (contient vos identifiants email)
- ❌ `uploads/tickets/*.pdf` (billets de test)
- ❌ `uploads/qrcodes/*.png` (QR codes de test)
- ❌ `.git/` (si vous ne clonez pas via Git)

### 1.2 Créer une archive

**Option A : Archive ZIP complète**
```powershell
# Exclure les fichiers sensibles
Compress-Archive -Path "c:\xampp\htdocs\MAT\*" -DestinationPath "c:\MAT-deployment.zip" -CompressionLevel Optimal
```

**Option B : Via Git (recommandé)**
```powershell
cd c:\xampp\htdocs\MAT
git init
git add .
git commit -m "Initial deployment"
# Push vers votre dépôt Git privé
```

---

## 🗄️ Étape 2 : Configurer la base de données

### 2.1 Créer la base de données sur o2Switch

1. Connectez-vous au **cPanel** de o2Switch
2. Allez dans **MySQL Databases** ou **phpMyAdmin**
3. Créez une nouvelle base de données :
   - **Nom** : `zelu6269_airsoft_association` (ou votre choix)
   - **Utilisateur** : Créez un nouvel utilisateur avec mot de passe fort
   - **Privilèges** : Accordez TOUS les privilèges sur cette base

**Notez ces informations** (vous en aurez besoin) :
```
Hôte : localhost (ou localhost:3306)
Base : zelu6269_airsoft_association
User : zelu6269_user_airsoft
Pass : [votre_mot_de_passe_généré]
```

### 2.2 Importer le schéma SQL

1. Ouvrez **phpMyAdmin** sur o2Switch
2. Sélectionnez votre base de données
3. Allez dans l'onglet **Importer**
4. Importez **dans cet ordre** :
   - ✅ `database/schema.sql` (structure principale)
   - ✅ `database/tickets_system.sql` (système billetterie)
   - ✅ `database/update_dynamic_teams.sql` (équipes dynamiques)
   - ✅ `database/gallery_table.sql` (galerie, si nécessaire)

**Vérification :**
```sql
SHOW TABLES;
```
Vous devriez voir : `users`, `events`, `event_teams`, `registrations`, `event_tickets`, `blog_posts`, `galleries`

### 2.3 Créer un compte administrateur

```sql
-- Générez d'abord un hash avec hash_password.php en local
INSERT INTO users (username, email, password, role) 
VALUES ('admin', 'votre-email@example.com', 'HASH_BCRYPT_ICI', 'admin');
```

---

## 📤 Étape 3 : Uploader les fichiers sur le serveur

### 3.1 Via FTP/SFTP (FileZilla)

1. Connectez-vous en SFTP à votre serveur o2Switch
2. Naviguez vers le dossier racine (généralement `/www/` ou `/public_html/`)
3. Uploadez **tous les fichiers** sauf ceux listés en 1.1
4. Assurez-vous que la structure est respectée

### 3.2 Via SSH (recommandé)

```bash
# Connexion SSH
ssh votre_user@votre_domaine.com

# Naviguer vers le dossier web
cd /home/votre_user/www/

# Cloner depuis Git (ou uploader via SCP)
git clone https://votre-depot-git.com/MAT.git
cd MAT

# Ou via SCP depuis votre machine locale
# scp -r c:\xampp\htdocs\MAT votre_user@serveur:/home/votre_user/www/
```

---

## ⚙️ Étape 4 : Configurer les fichiers

### 4.1 Configurer la base de données

```bash
# Sur le serveur, copiez le template
cd /home/votre_user/www/MAT/config/
cp database.example.php database.php

# Éditez avec nano ou vi
nano database.php
```

**Modifiez avec vos vraies valeurs :**
```php
define('DB_HOST', 'localhost:3306');
define('DB_NAME', 'zelu6269_airsoft_association');
define('DB_USER', 'zelu6269_user_airsoft');
define('DB_PASS', 'VOTRE_MOT_DE_PASSE_BDD');
```

**Sauvegardez** : `Ctrl+O` puis `Ctrl+X` (nano)

### 4.2 Configurer l'email

```bash
cd /home/votre_user/www/MAT/qr-code/
cp email_config.example.php email_config.php
nano email_config.php
```

**Modifiez avec vos paramètres SMTP :**
```php
define('SMTP_HOST', 'smtp.gmail.com'); // ou smtp.office365.com
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'votre-email@domaine.com');
define('SMTP_PASSWORD', 'votre-mot-de-passe-app');
define('SMTP_SECURE', 'tls');

define('FROM_EMAIL', 'noreply@votre-domaine.com');
define('FROM_NAME', 'MAT - Billetterie');
```

**Sauvegardez** : `Ctrl+O` puis `Ctrl+X`

---

## 📚 Étape 5 : Installer les dépendances Composer

### 5.1 Via SSH (recommandé)

```bash
# Vérifier si Composer est installé
composer --version

# Si Composer n'est pas installé, l'installer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# Installer les dépendances
cd /home/votre_user/www/MAT/
composer install --no-dev --optimize-autoloader
```

### 5.2 Sans accès SSH (alternative)

1. Sur votre machine locale, exécutez :
   ```powershell
   composer install --no-dev
   ```
2. Uploadez tout le dossier `vendor/` via FTP (⚠️ peut être long : ~30 MB)

---

## 🔒 Étape 6 : Configurer les permissions

```bash
# Permissions des dossiers uploads
cd /home/votre_user/www/MAT/
chmod 755 uploads/
chmod 755 uploads/tickets/
chmod 755 uploads/qrcodes/
chmod 755 uploads/gallery/
chmod 755 uploads/profiles/
chmod 755 uploads/blog/

# Permissions d'écriture pour Apache/PHP-FPM
chown -R votre_user:votre_user uploads/
find uploads/ -type d -exec chmod 755 {} \;
find uploads/ -type f -exec chmod 644 {} \;

# Permissions des fichiers config (lecture seule)
chmod 600 config/database.php
chmod 600 qr-code/email_config.php
```

---

## 🌐 Étape 7 : Configurer Apache (.htaccess)

### 7.1 Vérifier le .htaccess racine

Créez ou modifiez `c:\xampp\htdocs\MAT\.htaccess` :

```apache
# Activer la réécriture d'URL
RewriteEngine On

# Redirection HTTPS (si SSL activé)
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Protection des fichiers sensibles
<FilesMatch "^(database\.php|email_config\.php|\.env)">
    Order allow,deny
    Deny from all
</FilesMatch>

# Protection des dossiers
Options -Indexes

# Sécurité headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>

# Cache des assets statiques
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

### 7.2 Vérifier le .htaccess de qr-code/

Le fichier `qr-code/.htaccess` existe déjà et protège les fichiers sensibles.

---

## ✅ Étape 8 : Tests de fonctionnement

### 8.1 Test de connexion

Accédez à : `https://votre-domaine.com/`

**Vérifications :**
- ✅ Page d'accueil s'affiche
- ✅ Pas d'erreurs 500
- ✅ Liens fonctionnels

### 8.2 Test de connexion BDD

Créez temporairement `test-db.php` à la racine :

```php
<?php
require_once 'config/database.php';
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    echo "✅ Connexion BDD OK - Nombre d'utilisateurs : " . $stmt->fetchColumn();
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage();
}
?>
```

Accédez à : `https://votre-domaine.com/test-db.php`

**⚠️ SUPPRIMEZ ce fichier après le test !**

### 8.3 Test de connexion admin

1. Allez sur : `https://votre-domaine.com/login.php`
2. Connectez-vous avec le compte admin créé
3. Accédez à : `https://votre-domaine.com/admin/`
4. Vérifiez que le dashboard s'affiche

### 8.4 Test de génération de QR codes

1. Créez un événement de test
2. Inscrivez-vous en tant que joueur
3. Vérifiez que :
   - ✅ Billet PDF généré dans `uploads/tickets/`
   - ✅ QR code PNG généré dans `uploads/qrcodes/`
   - ✅ Email reçu avec pièce jointe

**Si les QR codes ne se génèrent pas :**
```bash
# Vérifier l'extension GD
php -m | grep gd

# Si absente, activer dans php.ini
nano /etc/php/8.2/apache2/php.ini
# Décommenter : extension=gd
# Redémarrer Apache
sudo service apache2 restart
```

### 8.5 Test du scanner mobile

1. Sur smartphone, allez sur : `https://votre-domaine.com/qr-code/scan.php`
2. Autorisez l'accès à la caméra
3. Scannez un QR code de test
4. Vérifiez la validation

**⚠️ Le scanner nécessite HTTPS !**

---

## 🔐 Étape 9 : Sécurité supplémentaire

### 9.1 Activer HTTPS (SSL)

**Sur o2Switch :**
1. Allez dans cPanel → **SSL/TLS**
2. Activez **Let's Encrypt SSL** (gratuit)
3. Attendez la génération du certificat (~5 min)
4. Vérifiez : `https://votre-domaine.com/`

### 9.2 Protéger les fichiers de config

```bash
# Rendre les fichiers config non lisibles via web
chmod 600 config/database.php
chmod 600 qr-code/email_config.php

# Vérifier qu'ils ne sont pas accessibles
# https://votre-domaine.com/config/database.php → Doit retourner 403
```

### 9.3 Désactiver les erreurs PHP en production

Dans `/etc/php/8.2/apache2/php.ini` (ou via cPanel) :

```ini
display_errors = Off
log_errors = On
error_log = /home/votre_user/logs/php_errors.log
```

Redémarrez Apache :
```bash
sudo service apache2 restart
```

### 9.4 Ajouter une authentification .htpasswd (optionnel)

Pour protéger `/admin/` avec un double mot de passe :

```bash
cd /home/votre_user/www/MAT/admin/
htpasswd -c .htpasswd admin_user
# Entrez un mot de passe fort
```

Créez `admin/.htaccess` :
```apache
AuthType Basic
AuthName "Zone Admin"
AuthUserFile /home/votre_user/www/MAT/admin/.htpasswd
Require valid-user
```

---

## 🔄 Étape 10 : Sauvegardes automatiques

### 10.1 Sauvegarde base de données (Cron)

Créez `/home/votre_user/scripts/backup-db.sh` :

```bash
#!/bin/bash
DATE=$(date +%Y-%m-%d_%H-%M-%S)
BACKUP_DIR="/home/votre_user/backups/database"
DB_NAME="zelu6269_airsoft_association"
DB_USER="zelu6269_user_airsoft"
DB_PASS="VOTRE_MOT_DE_PASSE"

mkdir -p $BACKUP_DIR
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/backup_$DATE.sql.gz

# Garder seulement les 30 derniers backups
find $BACKUP_DIR -name "backup_*.sql.gz" -mtime +30 -delete
```

```bash
chmod +x /home/votre_user/scripts/backup-db.sh
```

**Ajouter un Cron job (tous les jours à 2h00) :**
```bash
crontab -e
```

Ajoutez :
```cron
0 2 * * * /home/votre_user/scripts/backup-db.sh
```

### 10.2 Sauvegarde fichiers uploads

```bash
#!/bin/bash
DATE=$(date +%Y-%m-%d_%H-%M-%S)
BACKUP_DIR="/home/votre_user/backups/uploads"
SOURCE_DIR="/home/votre_user/www/MAT/uploads"

mkdir -p $BACKUP_DIR
tar -czf $BACKUP_DIR/uploads_$DATE.tar.gz -C $SOURCE_DIR .

# Garder seulement les 15 derniers backups
find $BACKUP_DIR -name "uploads_*.tar.gz" -mtime +15 -delete
```

---

## 📊 Étape 11 : Monitoring et logs

### 11.1 Logs Apache

```bash
# Logs d'erreur
tail -f /var/log/apache2/error.log

# Logs d'accès
tail -f /var/log/apache2/access.log
```

### 11.2 Logs PHP

```bash
tail -f /home/votre_user/logs/php_errors.log
```

### 11.3 Logs applicatifs (optionnel)

Créez `config/logger.php` :

```php
<?php
function logEvent($level, $message, $context = []) {
    $logFile = __DIR__ . '/../logs/app.log';
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? json_encode($context) : '';
    $logLine = "[$timestamp] [$level] $message $contextStr" . PHP_EOL;
    file_put_contents($logFile, $logLine, FILE_APPEND);
}
?>
```

---

## 🎯 Checklist finale de déploiement

- [ ] Base de données créée et importée
- [ ] Compte admin créé et testé
- [ ] Fichier `config/database.php` configuré avec bonnes valeurs
- [ ] Fichier `qr-code/email_config.php` configuré
- [ ] Composer installé et dépendances OK
- [ ] Extension PHP GD activée
- [ ] Permissions dossiers `uploads/` correctes (755)
- [ ] HTTPS/SSL activé et fonctionnel
- [ ] Test de connexion OK
- [ ] Test d'inscription + billet + email OK
- [ ] Test scanner QR sur mobile OK
- [ ] Fichiers sensibles protégés (.htaccess)
- [ ] Erreurs PHP désactivées en production
- [ ] Sauvegardes automatiques configurées
- [ ] `.gitignore` vérifié (pas de fichiers sensibles)
- [ ] Monitoring logs activé

---

## 🐛 Dépannage production

### Erreur 500 Internal Server Error

**Causes fréquentes :**
1. Erreur de syntaxe PHP
2. Permissions incorrectes
3. `.htaccess` mal configuré
4. Extension PHP manquante

**Solution :**
```bash
# Vérifier les logs
tail -f /var/log/apache2/error.log

# Activer temporairement les erreurs PHP
nano /etc/php/8.2/apache2/php.ini
# display_errors = On
sudo service apache2 restart
```

### Billets/QR codes non générés

**Vérifications :**
```bash
# Extension GD
php -m | grep gd

# Permissions uploads
ls -la /home/votre_user/www/MAT/uploads/

# Logs PHP
tail -f /home/votre_user/logs/php_errors.log
```

### Emails non envoyés

**Vérifications :**
1. SMTP_PASSWORD correct (mot de passe d'app, pas celui du compte)
2. Port 587 non bloqué par le firewall
3. Logs : `tail -f /home/votre_user/logs/php_errors.log`

**Test SMTP :**
```bash
php -r "phpinfo();" | grep -i smtp
```

### Scanner QR ne fonctionne pas

**Causes :**
1. ❌ HTTPS non activé → **Obligatoire pour caméra**
2. ❌ Permissions caméra refusées → Vérifier paramètres navigateur
3. ❌ Authentification admin requise → Connectez-vous d'abord

---

## 📞 Support

- **Documentation PHP** : https://www.php.net/manual/fr/
- **o2Switch Support** : https://www.o2switch.fr/support/
- **Composer** : https://getcomposer.org/doc/
- **PHPMailer** : https://github.com/PHPMailer/PHPMailer

---

## 🎉 Félicitations !

Votre application MAT est maintenant déployée en production ! 🚀

**Prochaines étapes recommandées :**
- Configurez Google Analytics (optionnel)
- Ajoutez un système de newsletter (optionnel)
- Configurez les backups automatiques
- Testez régulièrement le système de billetterie
- Surveillez les logs d'erreurs

**Bon jeu !** 🎮
