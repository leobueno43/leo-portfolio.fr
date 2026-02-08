-- Script de mise à jour de la base de données
-- À exécuter dans phpMyAdmin pour ajouter les nouvelles fonctionnalités

USE zelu6269_airsoft_association;

-- Ajouter la colonne profile_picture à la table users (si elle n'existe pas)
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS profile_picture VARCHAR(255) DEFAULT NULL AFTER phone;

-- Créer la table blog_posts (si elle n'existe pas)
CREATE TABLE IF NOT EXISTS blog_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(220) NOT NULL UNIQUE,
    content TEXT NOT NULL,
    excerpt TEXT,
    featured_image VARCHAR(255),
    author_id INT NOT NULL,
    is_published TINYINT(1) DEFAULT 0,
    published_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    views INT DEFAULT 0,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_slug (slug),
    INDEX idx_published (is_published),
    INDEX idx_published_at (published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insérer des exemples d'articles de blog
INSERT INTO blog_posts (title, slug, content, excerpt, author_id, is_published, published_at)
VALUES 
('Bienvenue sur le blog M.A.T', 
 'bienvenue-sur-le-blog-mat',
 'Bienvenue sur le blog officiel de Mission Airsoft Tactique ! Ici, vous trouverez tous les comptes-rendus de nos parties, des conseils pour améliorer votre jeu, et toutes les actualités de l\'association.\n\nNous publierons régulièrement :\n- Les comptes-rendus des parties passées avec photos\n- Des conseils tactiques et techniques\n- Les annonces des événements à venir\n- Des interviews de joueurs\n- Des tests de matériel\n\nRestez connectés pour ne rien manquer de nos aventures sur le terrain !',
 'Découvrez le blog officiel de M.A.T avec comptes-rendus, conseils et actualités airsoft.',
 1,
 1,
 NOW()),
 
('Compte-rendu : Opération Aiguille Noire', 
 'compte-rendu-operation-aiguille-noire',
 'Ce dimanche avait lieu notre première grande opération de l\'année : Opération Aiguille Noire.\n\n15 joueurs de l\'équipe BLEUE et 15 de l\'équipe ROUGE se sont affrontés dans un scénario d\'infiltration intense. L\'équipe BLEUE devait récupérer des documents classifiés dans la base ROUGE.\n\nLe match a été très serré avec plusieurs retournements de situation. Félicitations à l\'équipe BLEUE qui a réussi sa mission après 3 heures de jeu intense !\n\nPoints positifs :\n- Excellent esprit d\'équipe\n- Fair-play exemplaire\n- Météo idéale\n\nOn se retrouve le mois prochain pour l\'Opération Défense du Fort !',
 'Retour sur une partie intense avec 30 joueurs dans la forêt de Fontainebleau.',
 1,
 1,
 DATE_SUB(NOW(), INTERVAL 2 DAY));

-- Message de confirmation
SELECT 'Base de données mise à jour avec succès !' as Message;
SELECT 'Nouvelles fonctionnalités ajoutées :' as Info;
SELECT '- Photos de profil pour les utilisateurs' as Feature1;
SELECT '- Système de blog complet' as Feature2;
