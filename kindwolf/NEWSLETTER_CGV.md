# 📧 Système Newsletter & CGV - KIND WOLF

## 🎯 Fonctionnalités implémentées

### 1. Système d'envoi de newsletter par email

#### Interface admin (`/admin/newsletter/send.php`)
- ✅ Formulaire d'envoi de newsletter en masse
- ✅ Support contenu HTML + texte brut
- ✅ Variable `[UNSUBSCRIBE_LINK]` automatique pour désinscription
- ✅ Compteur d'abonnés actifs en temps réel
- ✅ Confirmation avant envoi
- ✅ Conseils et bonnes pratiques intégrés
- ✅ Alerte si SMTP non configuré

#### Fonctionnement technique
```php
// Récupère tous les abonnés actifs
$stmt = $pdo->query("SELECT email, token FROM newsletter_subscribers WHERE active = 1");

// Pour chaque abonné
foreach ($subscribers as $subscriber) {
    // Remplace [UNSUBSCRIBE_LINK] par le lien réel
    $unsubscribe_link = BASE_URL . "/pages/unsubscribe.php?token=" . $subscriber['token'];
    $final_html = str_replace('[UNSUBSCRIBE_LINK]', $unsubscribe_link, $html_content);
    
    // Envoie l'email
    mail($subscriber['email'], $subject, $final_html, $headers);
}
```

### 2. Page de désinscription (`/pages/unsubscribe.php`)
- ✅ Lien sécurisé avec token unique
- ✅ Confirmation avant désinscription
- ✅ Message de succès/erreur
- ✅ Mise à jour `active = 0` et `unsubscribed_at`

### 3. Cases à cocher au checkout

#### Case CGV/CGU (OBLIGATOIRE)
```html
<label class="checkbox-label required">
    <input type="checkbox" name="accept_cgv" id="accept_cgv" required>
    <span>J'accepte les CGV et CGU *</span>
</label>
```
- ✅ Marquée avec `*` rouge
- ✅ Attribut `required` HTML
- ✅ Validation PHP côté serveur
- ✅ Message d'erreur si non cochée
- ✅ Liens vers pages CGV/CGU

#### Case Newsletter (OPTIONNELLE)
```html
<label class="checkbox-label">
    <input type="checkbox" name="newsletter_subscribe" id="newsletter_subscribe">
    <span>Je souhaite recevoir la newsletter</span>
</label>
```
- ✅ Optionnelle (pas required)
- ✅ Inscription automatique si cochée
- ✅ Vérification anti-doublon
- ✅ Token unique généré

#### Logique PHP
```php
// Validation CGV obligatoire
if (!isset($_POST['accept_cgv'])) {
    $error = 'Vous devez accepter les CGV pour continuer';
}

// Inscription newsletter si cochée
if (isset($_POST['newsletter_subscribe'])) {
    $stmt = $pdo->prepare("SELECT id FROM newsletter_subscribers WHERE email = ?");
    $stmt->execute([$user_email]);
    
    if (!$stmt->fetch()) {
        $token = bin2hex(random_bytes(16));
        $pdo->prepare("INSERT INTO newsletter_subscribers (email, token, subscribed_at) 
                       VALUES (?, ?, NOW())")->execute([$user_email, $token]);
    }
}
```

### 4. Pages légales

#### CGV (`/pages/cgv.php`)
- ✅ Champ d'application
- ✅ Produits et prix
- ✅ Commande et paiement
- ✅ Livraison
- ✅ Droit de rétractation (14 jours)
- ✅ Garanties
- ✅ RGPD et données personnelles
- ✅ Litiges et droit applicable

#### CGU (`/pages/cgu.php`)
- ✅ Objet et acceptation
- ✅ Accès au site
- ✅ Création de compte
- ✅ Propriété intellectuelle
- ✅ Responsabilité
- ✅ Cookies
- ✅ Avis clients
- ✅ Juridiction

## 📂 Fichiers créés/modifiés

### Nouveaux fichiers
```
admin/newsletter/send.php       → Interface d'envoi newsletter
pages/unsubscribe.php          → Désinscription newsletter
pages/cgv.php                  → Conditions Générales de Vente
pages/cgu.php                  → Conditions Générales d'Utilisation
test-newsletter-cgv.html       → Page de test
NEWSLETTER_CGV.md              → Cette documentation
```

### Fichiers modifiés
```
pages/checkout.php             → Cases CGV + Newsletter + logique PHP
admin/newsletter/list.php      → Bouton "Envoyer newsletter"
style.css                      → CSS pour checkboxes, legal pages, newsletter form
```

## 🚀 Utilisation

### Envoyer une newsletter (Admin)

1. Allez dans **Admin > Newsletter > Liste des abonnés**
2. Cliquez sur **"📨 Envoyer une newsletter"**
3. Remplissez :
   - **Sujet** : Ex: "Nouvelles collections automne"
   - **Contenu texte** : Version texte brut (obligatoire)
   - **Contenu HTML** : Version HTML (optionnel)
   - Utilisez `[UNSUBSCRIBE_LINK]` pour le lien de désinscription
4. Cochez la confirmation
5. Cliquez **"Envoyer"**

### Exemple de contenu HTML
```html
<h1>🎉 Nouvelle collection automne !</h1>
<p>Découvrez nos nouveaux produits inspirés de la nature...</p>

<a href="<?php echo BASE_URL; ?>/pages/boutique.php">Voir la boutique</a>

<hr>
<p><small><a href="[UNSUBSCRIBE_LINK]">Se désinscrire</a></small></p>
```

### Test du checkout avec CGV

1. Ajoutez un produit au panier
2. Allez au checkout
3. **Sans cocher CGV** → Erreur : "Vous devez accepter les CGV"
4. **Cochez CGV** → Peut continuer
5. **Cochez aussi Newsletter** → Inscrit automatiquement

## ⚙️ Configuration SMTP (pour envoi réel)

Pour envoyer de vrais emails, ajoutez dans `site_settings` :

```sql
INSERT INTO site_settings (setting_key, setting_value) VALUES
('email_smtp_host', 'smtp.gmail.com'),
('email_smtp_port', '587'),
('email_smtp_username', 'votre-email@gmail.com'),
('email_smtp_password', 'votre-mot-de-passe-app'),
('email_from', 'noreply@kindwolf.com'),
('email_from_name', 'KIND WOLF');
```

### Gmail App Password
1. Activez la vérification en 2 étapes
2. Générez un mot de passe d'application
3. Utilisez ce mot de passe dans `email_smtp_password`

## 🎨 CSS ajouté

### Cases à cocher checkout
```css
.checkout-checkboxes {
    margin: 1.5rem 0;
    padding: 1.5rem;
    background: var(--cream);
    border-radius: 8px;
}

.checkbox-label {
    display: flex;
    gap: 0.75rem;
    cursor: pointer;
}

.checkbox-label.required span::after {
    content: " *";
    color: var(--deep-red);
}
```

### Pages légales
```css
.legal-page {
    max-width: 900px;
    padding: 3rem 1rem;
}

.legal-section {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    margin-bottom: 2.5rem;
}
```

## 🧪 Tests

Ouvrez : `http://localhost/kindwolf/test-newsletter-cgv.html`

### Checklist de test

- [ ] Admin peut voir le bouton "Envoyer newsletter"
- [ ] Interface d'envoi affiche le nombre d'abonnés
- [ ] Formulaire d'envoi valide les champs obligatoires
- [ ] [UNSUBSCRIBE_LINK] est bien remplacé
- [ ] Page CGV affiche toutes les sections
- [ ] Page CGU affiche toutes les sections
- [ ] Checkout affiche les 2 cases à cocher
- [ ] CGV est marquée obligatoire avec *
- [ ] Impossible de continuer sans cocher CGV
- [ ] Newsletter optionnelle fonctionne
- [ ] Inscription newsletter automatique si cochée
- [ ] Pas de doublon si déjà inscrit
- [ ] Page de désinscription fonctionne

## 🔒 Sécurité

### Validation côté serveur
```php
// Vérification CGV obligatoire
$accept_cgv = isset($_POST['accept_cgv']);
if (!$accept_cgv) {
    $error = 'Vous devez accepter les CGV';
    exit;
}
```

### Token unique pour désinscription
```php
$token = bin2hex(random_bytes(16)); // 32 caractères hex
// Lien : /pages/unsubscribe.php?token=abc123...
```

### Protection anti-spam
- Cases à cocher visibles (pas de honeypot nécessaire)
- Validation email avant inscription
- Vérification anti-doublon
- Token sécurisé pour désinscription

## 📊 Base de données

### Table newsletter_subscribers (existante)
```sql
CREATE TABLE newsletter_subscribers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE,
    token VARCHAR(64) UNIQUE,
    active TINYINT(1) DEFAULT 1,
    subscribed_at DATETIME,
    unsubscribed_at DATETIME
);
```

### Requêtes utiles
```sql
-- Nombre d'abonnés actifs
SELECT COUNT(*) FROM newsletter_subscribers WHERE active = 1;

-- Dernières inscriptions
SELECT email, subscribed_at FROM newsletter_subscribers 
ORDER BY subscribed_at DESC LIMIT 10;

-- Taux de désinscription
SELECT 
    COUNT(*) as total,
    SUM(active = 1) as actifs,
    SUM(active = 0) as desincrits,
    ROUND(SUM(active = 0) * 100.0 / COUNT(*), 2) as taux_desincription
FROM newsletter_subscribers;
```

## 🎯 Améliorations futures possibles

### Newsletter
- [ ] Table `newsletter_campaigns` pour historique des envois
- [ ] Statistiques d'ouverture (tracking pixel)
- [ ] Templates HTML prédéfinis
- [ ] Envoi programmé (cron job)
- [ ] Segmentation des abonnés
- [ ] A/B testing

### Checkout
- [ ] Case "Créer un compte" si utilisateur invité
- [ ] Case "Sauvegarder carte bancaire"
- [ ] Case "Accepter SMS notifications"

### Pages légales
- [ ] Politique de confidentialité séparée
- [ ] Mentions légales
- [ ] Politique de cookies

## 📞 Support

Pour toute question :
- Email : contact@kindwolf.com
- Téléphone : 01 23 45 67 89

---

**KIND WOLF** - E-commerce artisanal inspiré de la nature 🐺🌲
