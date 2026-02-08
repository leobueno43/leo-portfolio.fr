# KIND WOLF - Systèmes Restaurés

## 📋 Résumé des modifications

Les systèmes suivants ont été **restaurés et complétés** :

1. ✅ **Système d'avis** - Avis clients avec notes et commentaires
2. ✅ **Codes promo** - Codes de réduction avec gestion avancée
3. ✅ **Newsletter** - Inscription et gestion des abonnés

## 🗂️ Fichiers créés/modifiés

### Frontend (Pages utilisateurs)

#### `/pages/produit.php`
- ✅ Ajout section avis clients avec :
  - Formulaire de soumission d'avis (si connecté)
  - Affichage de la note moyenne
  - Liste des avis avec pagination
  - Badge "Achat vérifié"

#### `/pages/panier.php`
- ✅ Ajout section code promo avec :
  - Champ de saisie du code
  - Bouton d'application
  - Affichage de la réduction
  - Bouton de suppression

#### `/pages/about.php`
- ✅ Ajout formulaire newsletter en bas de page

### Admin (Gestion)

#### Newsletter
- ✅ `/admin/newsletter/list.php` - Liste complète des abonnés
- ✅ `/admin/newsletter/delete.php` - Suppression d'abonnés
- ✅ `/admin/newsletter/export.php` - Export CSV/TXT

#### Codes Promo
- ✅ `/admin/promo/add.php` - Création de nouveaux codes
- ✅ `/admin/promo/edit.php` - Modification des codes existants
- ✅ `/admin/promo/delete.php` - Suppression de codes
- ✅ `/admin/promo/list.php` - Liste des codes (déjà existant)

#### Avis
- ✅ `/admin/reviews/list.php` - Gestion des avis (déjà existant)
- ✅ `/admin/reviews/approve.php` - Approbation d'avis (déjà existant)
- ✅ `/admin/reviews/delete.php` - Suppression d'avis (déjà existant)

### API (Backend)
Tous les fichiers API existent déjà et sont fonctionnels :
- ✅ `/api/review_actions.php` - Actions pour les avis
- ✅ `/api/promo_actions.php` - Actions pour les codes promo
- ✅ `/api/newsletter_actions.php` - Actions pour la newsletter

### Styles
- ✅ `/style.css` - Styles ajoutés pour :
  - Section avis (.reviews-section, .review-item, etc.)
  - Section code promo (.promo-code-section, .promo-form, etc.)
  - Section newsletter (.newsletter-section, .newsletter-form-inline, etc.)

### JavaScript
- ✅ `/script.js` - Fonctions restaurées :
  - `subscribeNewsletter()` - Inscription newsletter
  - `validateEmail()` - Validation email
  - `applyPromoCode()` - Application code promo
  - `removePromoCode()` - Suppression code promo
  - `submitReview()` - Soumission avis

### Base de données
- ✅ `/database/kindwolf_db.sql` - **Script SQL unique et complet** avec :
  - Toutes les tables (reviews avec `title`, products avec `rating` et `review_count`)
  - Tous les triggers pour mise à jour automatique des ratings
  - Tous les index pour optimisation
  - Données initiales
  - Vues et statistiques

## 🚀 Installation

### 1. Importer la base de données

**Un seul fichier SQL à exécuter !** 

Via la ligne de commande MySQL :

```bash
mysql -u root -p < database/kindwolf_db.sql
```

Ou via phpMyAdmin :
1. Ouvrez phpMyAdmin
2. Cliquez sur "Importer" dans le menu
3. Sélectionnez `database/kindwolf_db.sql`
4. Cliquez sur "Exécuter"

**C'est tout !** Toutes les tables, colonnes, triggers et données sont créés automatiquement.

### 2. Vérifier les tables

Assurez-vous que ces tables existent :
- ✅ `reviews` (avec colonne `title`)
- ✅ `products` (avec colonnes `rating` et `review_count`)
- ✅ `promo_codes`
- ✅ `newsletter_subscribers`

### 3. Tester les fonctionnalités

#### Avis clients
1. Allez sur une page produit : `/pages/produit.php?id=1`
2. Connectez-vous en tant qu'utilisateur
3. Remplissez le formulaire d'avis
4. Vérifiez dans `/admin/reviews/list.php`

#### Codes promo
1. Créez un code dans `/admin/promo/add.php`
   - Code : `TEST10`
   - Type : Pourcentage
   - Réduction : 10%
2. Ajoutez des produits au panier
3. Appliquez le code `TEST10`
4. Vérifiez que la réduction s'applique

#### Newsletter
1. Allez sur `/pages/about.php`
2. Inscrivez-vous avec votre email
3. Vérifiez dans `/admin/newsletter/list.php`

## 📊 Structure de la base de données

### Table `reviews`
```sql
- id (INT, PRIMARY KEY)
- product_id (INT, FK -> products)
- user_id (INT, FK -> users)
- rating (INT, 1-5)
- title (VARCHAR(200)) ⬅️ NOUVELLE COLONNE
- comment (TEXT)
- verified_purchase (BOOLEAN)
- approved (BOOLEAN)
- created_at (TIMESTAMP)
```

### Table `promo_codes`
```sql
- id (INT, PRIMARY KEY)
- code (VARCHAR(50), UNIQUE)
- discount_type (ENUM: percentage, fixed)
- discount_percent (DECIMAL)
- discount_amount (DECIMAL)
- minimum_amount (DECIMAL)
- maximum_discount (DECIMAL)
- usage_limit (INT)
- usage_count (INT)
- user_limit (INT)
- active (BOOLEAN)
- expires_at (TIMESTAMP)
```

### Table `newsletter_subscribers`
```sql
- id (INT, PRIMARY KEY)
- email (VARCHAR(255), UNIQUE)
- token (VARCHAR(64), UNIQUE)
- active (BOOLEAN)
- subscribed_at (TIMESTAMP)
- unsubscribed_at (TIMESTAMP)
```

## 🔧 Fonctionnalités

### Système d'avis

**Frontend (utilisateur) :**
- Note de 1 à 5 étoiles
- Titre de l'avis
- Commentaire
- Badge "Achat vérifié" automatique
- Affichage note moyenne + nombre d'avis

**Backend (admin) :**
- Modération des avis (approbation/rejet)
- Suppression d'avis
- Filtres et recherche
- Statistiques par produit

### Codes promo

**Frontend (utilisateur) :**
- Champ de saisie dans le panier
- Application automatique du code
- Affichage de la réduction
- Messages d'erreur si code invalide

**Backend (admin) :**
- Création de codes
- Types : Pourcentage ou montant fixe
- Limite d'utilisation globale
- Limite par utilisateur
- Montant minimum de commande
- Date d'expiration
- Réduction maximum (pour les %)
- Activation/désactivation

### Newsletter

**Frontend (utilisateur) :**
- Formulaire d'inscription (email uniquement)
- Validation email
- Message de confirmation
- Token de désinscription unique

**Backend (admin) :**
- Liste des abonnés
- Filtres : Tous / Actifs / Inactifs
- Recherche par email
- Statistiques (total, actifs, inactifs)
- Export CSV/TXT
- Actions : Activer / Désactiver / Supprimer

## 🎨 Styles CSS

Toutes les sections ont été stylisées avec :
- Design cohérent avec le reste du site
- Couleurs de la palette KIND WOLF
- Responsive mobile-first
- Animations et transitions
- Messages de succès/erreur
- Badges et indicateurs visuels

## 🐛 Debug

### Si rien ne fonctionne après installation :
1. **Vérifiez que la base de données est importée** :
   - Ouvrez phpMyAdmin
   - Vérifiez que `kindwolf_db` existe
   - Vérifiez que toutes les tables sont créées (11 tables au total)

2. **Vérifiez les colonnes importantes** :
   - Table `reviews` doit avoir la colonne `title`
   - Table `products` doit avoir `rating` et `review_count`
   - Table `newsletter_subscribers` (pas `newsletter`)

3. **Si vous aviez déjà une ancienne base** :
   - Sauvegardez vos données si nécessaire
   - Supprimez l'ancienne base : `DROP DATABASE kindwolf_db;`
   - Réimportez `database/kindwolf_db.sql`

### Si les codes promo ne fonctionnent pas :
1. Vérifiez que le code est actif dans `/admin/promo/list.php`
2. Vérifiez la date d'expiration
3. Vérifiez le montant minimum de commande
4. Vérifiez les limites d'utilisation
5. Regardez la console réseau (F12 -> Network) pour voir les erreurs

### Si la newsletter ne fonctionne pas :
1. Vérifiez que `/api/newsletter_actions.php` existe
2. Vérifiez les permissions du fichier
3. Regardez les logs PHP pour voir les erreurs SQL
4. Vérifiez que la table `newsletter_subscribers` existe

## 📝 Notes importantes

1. **Sécurité** : Tous les formulaires utilisent la validation côté serveur
2. **Session** : Les codes promo sont stockés dans `$_SESSION['promo_code']`
3. **AJAX** : Les avis utilisent fetch() pour chargement asynchrone
4. **Validation** : Les emails sont validés avec regex
5. **P**Base de données importée** (`database/kindwolf_db.sql` exécuté avec succès)
- [ ] 11 tables créées dans `kindwolf_db`
- [ ] Compte admin accessible : admin@kindwolf.com / admin123
- [ ] Formulaire d'avis visible sur page produit (si connecté)
- [ ] Champ code promo visible dans le panier
- [ ] Formulaire newsletter visible sur page à propos
- [ ] Admin newsletter accessible : `/admin/newsletter/list.php`
- [ ] Admin promo accessible : `/admin/promo/list.php`
- [ ] Admin avis accessible : `/admin/reviews/list.php`
- [ ] Tests effectués sur chaque système

## 📦 Structure complète de la base de données

Toutes ces tables sont créées automatiquement par `kindwolf_db.sql` :

1. **users** - Comptes utilisateurs et admins
2. **categories** - Catégories de produits
3. **products** - Produits (avec rating et review_count intégrés)
4. **reviews** - Avis clients (avec colonne title)
5. **orders** - Commandes
6. **order_items** - Articles des commandes
7. **addresses** - Adresses clients
8. **promo_codes** - Codes promotionnels
9. **promo_usage** - Utilisation des codes promo
10. **newsletter_subscribers** - Abonnés newsletter
11. **wishlist** - Liste de souhaits
12. **site_settings** - Paramètres du site
13. **contact_messages** - Messages de contact

**+ Triggers automatiques** pour mise à jour des ratings
**+ Vues SQL** pour statistiques (v_best_sellers, v_product_stats)
**+ Index** pour performance optimal
- [ ] Table `reviews` a la colonne `title`
- [ ] Table `products` a `rating` et `review_count`
- [ ] Triggers créés pour auto-update ratings
- [ ] Formulaire d'avis visible sur page produit (si connecté)
- [ ] Champ code promo visible dans le panier
- [ ] Formulaire newsletter visible sur page à propos
- [ ] Admin newsletter accessible et fonctionnel
- [ ] Admin promo accessible et fonctionnel
- [ ] Admin avis accessible et fonctionnel
- [ ] Tests effectués sur chaque système

## 📞 Support

Si vous rencontrez des problèmes :
1. Vérifiez les logs d'erreur PHP
2. Ouvrez la console navigateur (F12)
3. Vérifiez les permissions des fichiers
4. Assurez-vous que la session PHP fonctionne
5. Vérifiez la connexion à la base de données dans `config.php`
