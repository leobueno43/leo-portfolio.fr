# ☁️ Système de Cloud Storage Personnel

## 📋 Description

Vous avez maintenant un **véritable système de stockage cloud** complètement **indépendant** de votre site web !

### 🎯 Caractéristiques principales

- ✅ **Stockage privé sécurisé** dans le dossier `/storage/`
- ✅ **Totalement séparé** des fichiers de votre site web
- ✅ **Protection automatique** via `.htaccess`
- ✅ **Upload, téléchargement, organisation** de vos fichiers personnels
- ✅ **Interface moderne** type Google Drive / Dropbox
- ✅ **Statistiques en temps réel** de l'espace utilisé

---

## 📁 Structure des fichiers

### **Nouveaux fichiers créés :**

```
/storage/                  ← Dossier de stockage cloud (créé automatiquement)
  ├── .htaccess           ← Protection (créé automatiquement)
  └── [vos fichiers]      ← Vos données personnelles

panel.php                  ← Interface principale du cloud
cloud_upload.php          ← Gestion des uploads
cloud_download.php        ← Téléchargement sécurisé
cloud_actions.php         ← Actions (copier, déplacer, etc.)
cloud_create_folder.php   ← Création de dossiers
```

### **Fichiers existants (inchangés) :**

```
login.php                  ← Connexion
logout.php                 ← Déconnexion
config.php                 ← Configuration
db.php                     ← Base de données
admin.css                  ← Styles
```

---

## 🚀 Utilisation

### **1. Se connecter**
Accédez à `votre-site.com/login.php` et connectez-vous avec vos identifiants admin.

### **2. Accéder au cloud**
Vous êtes automatiquement redirigé vers `panel.php` - votre interface cloud.

### **3. Fonctionnalités disponibles**

#### 📤 **Upload de fichiers**
- Cliquez sur "Upload"
- Sélectionnez un ou plusieurs fichiers
- Limite : 100 MB par fichier

#### 📁 **Créer des dossiers**
- Cliquez sur "Nouveau dossier"
- Entrez le nom du dossier
- Organisez vos fichiers comme vous le souhaitez

#### 📋 **Copier/Déplacer**
- Cochez les fichiers souhaités
- Cliquez sur "Copier" ou "Déplacer"
- Sélectionnez la destination

#### ✏️ **Renommer**
- Sélectionnez UN fichier
- Cliquez sur "Renommer"
- Entrez le nouveau nom

#### 👁️ **Aperçu**
- Sélectionnez UN fichier
- Cliquez sur "Aperçu"
- Visualisez images et fichiers texte

#### ⬇️ **Télécharger**
- Cochez les fichiers
- Cliquez sur "Télécharger"
- Les fichiers se téléchargent

#### 🗑️ **Supprimer**
- Cochez les fichiers
- Cliquez sur "Supprimer"
- Confirmez la suppression

---

## 🔒 Sécurité

### **Protection du dossier storage**

Le dossier `/storage/` est **automatiquement protégé** par un fichier `.htaccess` qui empêche tout accès direct via HTTP.

```apache
Deny from all
```

Cela signifie que **PERSONNE** ne peut accéder directement à :
- `votre-site.com/storage/mon-fichier.pdf` ❌

Les fichiers ne sont accessibles **QUE** via les scripts PHP authentifiés :
- `cloud_download.php` après connexion ✅

### **Accès sécurisé**

- ✅ Authentification obligatoire
- ✅ Vérification des sessions
- ✅ Validation des chemins
- ✅ Protection contre les injections
- ✅ Pas de sortie du dossier storage possible

---

## 💾 Gestion de l'espace

### **Statistiques affichées :**

1. **Espace utilisé** - Total de vos fichiers dans `/storage/`
2. **Nombre de dossiers** - Dossiers dans le répertoire actuel
3. **Nombre de fichiers** - Fichiers dans le répertoire actuel  
4. **Espace disponible** - Espace libre sur le serveur

### **Limites :**

- **Par fichier** : 100 MB (modifiable dans `cloud_upload.php`)
- **Total** : Limité par l'espace disque de votre hébergement

---

## 🎨 Personnalisation

### **Changer la limite d'upload**

Dans `cloud_upload.php`, ligne ~40 :

```php
// Limite de taille (100 MB par fichier)
$maxSize = 100 * 1024 * 1024; // Changez cette valeur
```

### **Modifier les types de fichiers acceptés**

Actuellement : **TOUS les types** sont acceptés.

Pour restreindre, ajoutez dans `cloud_upload.php` après la ligne 48 :

```php
// Extensions autorisées
$allowedExtensions = ['pdf', 'jpg', 'png', 'docx', 'zip'];
$extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

if (!in_array($extension, $allowedExtensions)) {
    header('Location: panel.php?path=' . urlencode($currentPath) . '&error=invalid_type');
    exit;
}
```

### **Changer les couleurs**

Dans `panel.php`, section `<style>`, ligne ~238 :

```css
background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
```

Changez les codes couleur pour personnaliser le dégradé.

---

## 🐛 Résolution de problèmes

### **Le dossier storage n'est pas créé**

Vérifiez les permissions :
```bash
chmod 755 /chemin/vers/votre/site/
```

### **Erreur d'upload**

1. Vérifiez `php.ini` :
```ini
upload_max_filesize = 100M
post_max_size = 100M
```

2. Vérifiez les permissions du dossier storage :
```bash
chmod 755 storage/
```

### **Les fichiers ne se téléchargent pas**

Vérifiez que vous êtes bien connecté et que le fichier existe dans `/storage/`.

---

## 📝 Notes importantes

### **Séparation site / cloud**

- Le **site web** reste dans les fichiers racine (index.html, images/, etc.)
- Le **cloud** stocke uniquement dans `/storage/`
- **Aucune interférence** entre les deux

### **Sauvegarde**

Pensez à **sauvegarder régulièrement** le dossier `/storage/` qui contient toutes vos données personnelles !

```bash
# Exemple de sauvegarde
tar -czf backup-cloud-$(date +%Y%m%d).tar.gz storage/
```

---

## 🎉 Félicitations !

Vous avez maintenant votre propre **cloud personnel** sécurisé et privé, complètement séparé de votre site web ! 

**Profitez de votre espace de stockage illimité** (dans la limite de votre hébergement) ! ☁️

---

## 📞 Support

Pour toute question ou amélioration, n'hésitez pas à me contacter !