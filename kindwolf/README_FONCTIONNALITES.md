# 🐺 KIND WOLF - Guide d'utilisation des nouvelles fonctionnalités

## 📋 Table des matières
1. [Annulation de commande](#1-annulation-de-commande)
2. [Système d'avis](#2-système-davis)
3. [Codes promo](#3-codes-promo)
4. [Newsletter](#4-newsletter)
5. [Tests](#5-tests)

---

## 1️⃣ Annulation de commande

### 👤 Côté Client

**Où :** `http://localhost/kindwolf/user/order-detail.php?id=X`

**Conditions :**
- Doit être connecté
- La commande doit être en statut "pending"
- Doit être propriétaire de la commande

**Comment :**
1. Se connecter à son compte
2. Aller dans "Mes commandes"
3. Cliquer sur une commande
4. Cliquer sur "❌ Annuler la commande"
5. Confirmer l'annulation

**Résultat :**
- Commande passe en statut "cancelled"
- Le stock des produits est restauré
- Le code promo (si utilisé) est décrementé
- Message de confirmation affiché

---

### 👨‍💼 Côté Admin

**Où :** `http://localhost/kindwolf/admin/orders/view.php?id=X`

**Conditions :**
- Doit être connecté en tant qu'admin
- La commande ne doit pas être "cancelled" ou "completed"

**Comment :**
1. Se connecter en tant qu'admin
2. Aller dans "Gestion des commandes"
3. Voir les détails d'une commande
4. Cliquer sur "❌ Annuler"
5. Entrer une raison d'annulation
6. Confirmer

**Résultat :**
- Même effet que côté client
- Raison enregistrée dans "admin_notes"
- Email de notification envoyé au client (si configuré)

---

## 2️⃣ Système d'avis

### ✍️ Laisser un avis

**Où :** `http://localhost/kindwolf/pages/produit.php?id=X`

**Conditions :**
- Doit être connecté

**Comment :**
1. Se connecter
2. Aller sur une page produit
3. Descendre jusqu'à la section "Avis clients"
4. Remplir le formulaire :
   - Sélectionner une note (1-5 étoiles)
   - Entrer un titre (obligatoire)
   - Entrer un commentaire (optionnel)
5. Cliquer sur "Publier mon avis"

**Résultat :**
- Avis créé avec statut "en attente de modération" (approved = 0)
- Badge "Achat vérifié" si l'utilisateur a acheté le produit
- Message de confirmation
- Avis visible après approbation par l'admin

---

### 👨‍💼 Modérer les avis (Admin)

**Où :** `http://localhost/kindwolf/admin/reviews/list.php`

**Comment :**
1. Se connecter en tant qu'admin
2. Aller dans "Gestion des avis"
3. Filtrer par "En attente"
4. Approuver ou supprimer les avis
5. Une fois approuvé, l'avis devient visible

**Filtres disponibles :**
- Tous les avis
- En attente de modération
- Approuvés
- Par produit
- Par recherche

---

## 3️⃣ Codes promo

### 🎫 Utiliser un code promo

**Où :** `http://localhost/kindwolf/pages/panier.php`

**Comment :**
1. Ajouter des produits au panier
2. Aller sur la page panier
3. Dans la section "Code promo" :
   - Entrer le code (ex: BIENVENUE10)
   - Cliquer sur "Appliquer"

**Validation automatique :**
- ✓ Code actif et non expiré
- ✓ Limite d'utilisation globale
- ✓ Limite d'utilisation par utilisateur
- ✓ Montant minimum du panier

**Résultat :**
- Réduction appliquée au total
- Affichage du code et du montant économisé
- Possibilité de retirer le code

---

### 👨‍💼 Créer un code promo (Admin)

**Où :** `http://localhost/kindwolf/admin/promo/list.php`

**Comment :**
1. Se connecter en tant qu'admin
2. Aller dans "Codes promo"
3. Cliquer sur "Ajouter un code"
4. Remplir le formulaire :
   - Code (ex: NOEL2026)
   - Type : Pourcentage ou Montant fixe
   - Valeur de réduction
   - Date d'expiration (optionnel)
   - Montant minimum (optionnel)
   - Limite d'utilisation globale (optionnel)
   - Limite par utilisateur (optionnel)
5. Activer le code
6. Enregistrer

**Exemples de codes :**
```
Code: BIENVENUE10
Type: Pourcentage
Valeur: 10
Minimum: 30€
→ 10% de réduction pour commandes > 30€

Code: NOEL25
Type: Montant fixe
Valeur: 25
Minimum: 100€
→ 25€ de réduction pour commandes > 100€
```

---

## 4️⃣ Newsletter

### 📧 S'inscrire à la newsletter

**Où :**
- Page d'accueil
- Page "À propos"
- Footer (si ajouté)
- Produits en rupture de stock

**Comment :**
1. Trouver le formulaire newsletter
2. Entrer son email
3. Cliquer sur "S'inscrire"

**Validation :**
- Format email valide
- Pas de doublon

**Résultat :**
- Email enregistré dans la base de données
- Token unique généré (pour désincription)
- Message de confirmation

---

### 👨‍💼 Gérer la newsletter (Admin)

**Où :** `http://localhost/kindwolf/admin/newsletter/list.php`

**Fonctionnalités :**
- Voir tous les inscrits
- Exporter la liste
- Envoyer des emails groupés
- Voir les statistiques

---

## 5️⃣ Tests

### 🧪 Test complet - Scénario 1 : Commande avec annulation

```
1. Créer un compte client
2. Ajouter des produits au panier
3. Appliquer un code promo (ex: BIENVENUE10)
4. Passer commande
5. Vérifier que la commande est en "pending"
6. Annuler la commande depuis "Mes commandes"
7. Vérifier :
   - Commande en statut "cancelled"
   - Stock restauré
   - Code promo réutilisable
```

---

### 🧪 Test complet - Scénario 2 : Avis produit

```
1. Se connecter
2. Acheter un produit (commande en "completed")
3. Aller sur la page du produit
4. Laisser un avis avec 5 étoiles
5. Vérifier message "en attente de modération"
6. Se connecter en admin
7. Approuver l'avis
8. Vérifier que l'avis est visible
9. Vérifier le badge "Achat vérifié"
```

---

### 🧪 Test complet - Scénario 3 : Code promo

```
ADMIN :
1. Créer code "TEST20" : 20% de réduction, minimum 50€
2. Activer le code

CLIENT :
1. Ajouter produits pour total = 30€
2. Tenter d'appliquer "TEST20"
3. Vérifier erreur "Montant minimum 50€"
4. Ajouter plus de produits (total > 50€)
5. Appliquer "TEST20"
6. Vérifier réduction de 20%
7. Passer commande
8. Vérifier code enregistré

ADMIN :
1. Vérifier usage du code incrémenté
```

---

### 🧪 Test complet - Scénario 4 : Newsletter

```
1. Aller sur la page d'accueil
2. Trouver le formulaire newsletter
3. Entrer email : test@example.com
4. Cliquer "S'inscrire"
5. Vérifier message de succès
6. Tenter de réinscrire le même email
7. Vérifier erreur "Déjà inscrit"

ADMIN :
1. Aller dans "Newsletter"
2. Voir test@example.com dans la liste
```

---

## 📊 Données de test suggérées

### Codes promo de test
```sql
INSERT INTO promo_codes (code, discount_type, discount_percent, discount_amount, minimum_amount, active, created_at) 
VALUES 
('BIENVENUE10', 'percentage', 10, NULL, 30, 1, NOW()),
('NOEL25', 'fixed', NULL, 25, 100, 1, NOW()),
('PRINTEMPS15', 'percentage', 15, NULL, 50, 1, NOW());
```

### Produits de test
- Au moins 3-5 produits actifs
- Stock varié (certains > 10, d'autres < 5)
- Prix variés pour tester minimum des codes promo

### Utilisateurs de test
```
Admin :
- Email: admin@kindwolf.com
- Password: admin123

Client :
- Email: client@example.com
- Password: client123
```

---

## 🔧 Dépannage

### ❌ Le code promo ne s'applique pas
**Vérifier :**
- Code actif (active = 1)
- Date d'expiration non dépassée
- Montant minimum atteint
- Limite d'utilisation non dépassée

### ❌ L'avis ne s'affiche pas
**Vérifier :**
- Avis approuvé par admin (approved = 1)
- Page rechargée après approbation

### ❌ L'annulation ne fonctionne pas
**Vérifier :**
- Statut de la commande (doit être "pending")
- Utilisateur connecté et propriétaire
- Pas d'erreur dans les logs PHP

### ❌ La newsletter ne s'inscrit pas
**Vérifier :**
- Format email valide
- Table newsletter_subscribers existe
- Pas de doublon dans la base

---

## 📚 Ressources

**Fichiers importants :**
- `/api/review_actions.php` - API avis
- `/api/promo_actions.php` - API codes promo
- `/api/newsletter_actions.php` - API newsletter
- `/user/cancel-order.php` - Annulation client
- `/admin/orders/cancel.php` - Annulation admin
- `/script.js` - Fonctions JavaScript
- `/FONCTIONNALITES.md` - Documentation technique complète

**Base de données :**
- Table `reviews` - Avis produits
- Table `promo_codes` - Codes promo
- Table `promo_usage` - Usage des codes
- Table `newsletter_subscribers` - Inscrits newsletter
- Table `orders` - Commandes

---

## ✅ Checklist de vérification

Avant de mettre en production :

- [ ] Tester annulation commande (client + admin)
- [ ] Tester soumission et modération d'avis
- [ ] Tester application de codes promo
- [ ] Tester inscription newsletter
- [ ] Vérifier restauration du stock
- [ ] Vérifier calculs de réduction
- [ ] Vérifier notifications utilisateur
- [ ] Tester sur mobile/responsive
- [ ] Vérifier sécurité (SQL injection, XSS)
- [ ] Configurer emails de notification

---

**Date de création :** 9 janvier 2026  
**Version :** 1.0  
**Statut :** ✅ Prêt pour utilisation
