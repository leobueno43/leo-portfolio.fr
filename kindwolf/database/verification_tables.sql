-- SQL de vérification et création des tables nécessaires
-- KIND WOLF - Fonctionnalités avancées
-- Date: 9 janvier 2026

-- ============================================
-- VÉRIFICATION TABLE REVIEWS
-- ============================================
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 AND `rating` <= 5),
  `title` varchar(100) DEFAULT NULL,
  `comment` text,
  `verified_purchase` tinyint(1) DEFAULT 0,
  `approved` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `user_id` (`user_id`),
  KEY `approved` (`approved`),
  CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- VÉRIFICATION TABLE PROMO_CODES
-- ============================================
CREATE TABLE IF NOT EXISTS `promo_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL UNIQUE,
  `description` text,
  `discount_type` enum('percentage','fixed') NOT NULL DEFAULT 'percentage',
  `discount_percent` decimal(5,2) DEFAULT NULL,
  `discount_amount` decimal(10,2) DEFAULT NULL,
  `minimum_amount` decimal(10,2) DEFAULT 0.00,
  `usage_limit` int(11) DEFAULT NULL COMMENT 'Nombre total d\'utilisations autorisées',
  `usage_count` int(11) DEFAULT 0,
  `user_limit` int(11) DEFAULT 1 COMMENT 'Nombre d\'utilisations par utilisateur',
  `expires_at` timestamp NULL DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- VÉRIFICATION TABLE PROMO_USAGE
-- ============================================
CREATE TABLE IF NOT EXISTS `promo_usage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `promo_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `used_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `promo_id` (`promo_id`),
  KEY `user_id` (`user_id`),
  KEY `order_id` (`order_id`),
  CONSTRAINT `promo_usage_ibfk_1` FOREIGN KEY (`promo_id`) REFERENCES `promo_codes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `promo_usage_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `promo_usage_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- VÉRIFICATION TABLE NEWSLETTER_SUBSCRIBERS
-- ============================================
CREATE TABLE IF NOT EXISTS `newsletter_subscribers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL UNIQUE,
  `token` varchar(64) NOT NULL UNIQUE,
  `active` tinyint(1) DEFAULT 1,
  `subscribed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `unsubscribed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `token` (`token`),
  KEY `active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- AJOUT COLONNES MANQUANTES SI NÉCESSAIRE
-- ============================================

-- Ajouter admin_notes à la table orders si elle n'existe pas
ALTER TABLE `orders` 
ADD COLUMN IF NOT EXISTS `admin_notes` text DEFAULT NULL COMMENT 'Notes internes admin';

-- Ajouter promo_code à la table orders si elle n'existe pas
ALTER TABLE `orders` 
ADD COLUMN IF NOT EXISTS `promo_code` varchar(50) DEFAULT NULL COMMENT 'Code promo utilisé';

-- Ajouter discount_amount à la table orders si elle n'existe pas
ALTER TABLE `orders` 
ADD COLUMN IF NOT EXISTS `discount_amount` decimal(10,2) DEFAULT 0.00 COMMENT 'Montant de la réduction';

-- ============================================
-- INDEX POUR OPTIMISATION
-- ============================================

-- Index pour recherche rapide des avis par produit approuvés
CREATE INDEX IF NOT EXISTS `idx_reviews_product_approved` ON `reviews` (`product_id`, `approved`);

-- Index pour recherche des codes promo actifs
CREATE INDEX IF NOT EXISTS `idx_promo_active` ON `promo_codes` (`active`, `expires_at`);

-- Index pour recherche des usages par utilisateur
CREATE INDEX IF NOT EXISTS `idx_promo_usage_user` ON `promo_usage` (`user_id`, `promo_id`);

-- Index pour newsletter active
CREATE INDEX IF NOT EXISTS `idx_newsletter_active` ON `newsletter_subscribers` (`active`);

-- ============================================
-- VÉRIFICATIONS DE CONTRAINTES
-- ============================================

-- Vérifier que les statuts de commande incluent 'cancelled'
-- (Modification de la contrainte si nécessaire)

-- ============================================
-- DONNÉES DE TEST (OPTIONNEL)
-- ============================================

-- Codes promo de démonstration
INSERT IGNORE INTO `promo_codes` (`code`, `description`, `discount_type`, `discount_percent`, `discount_amount`, `minimum_amount`, `active`) 
VALUES 
('BIENVENUE10', 'Bienvenue ! 10% de réduction sur votre première commande', 'percentage', 10.00, NULL, 30.00, 1),
('NOEL25', 'Offre Noël : 25€ de réduction', 'fixed', NULL, 25.00, 100.00, 1),
('PRINTEMPS15', 'Printemps : 15% de réduction', 'percentage', 15.00, NULL, 50.00, 1),
('LIVRAISON', 'Livraison gratuite', 'fixed', NULL, 5.90, 0.00, 1);

-- ============================================
-- REQUÊTES DE VÉRIFICATION
-- ============================================

-- Vérifier les tables créées
SELECT 
    TABLE_NAME, 
    TABLE_ROWS, 
    CREATE_TIME, 
    UPDATE_TIME
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME IN ('reviews', 'promo_codes', 'promo_usage', 'newsletter_subscribers')
ORDER BY TABLE_NAME;

-- Vérifier les colonnes de la table reviews
SELECT 
    COLUMN_NAME, 
    COLUMN_TYPE, 
    IS_NULLABLE, 
    COLUMN_DEFAULT
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'reviews'
ORDER BY ORDINAL_POSITION;

-- Vérifier les codes promo actifs
SELECT 
    code, 
    discount_type, 
    COALESCE(discount_percent, discount_amount) as discount_value,
    minimum_amount,
    usage_count,
    usage_limit,
    expires_at,
    active
FROM promo_codes 
WHERE active = 1;

-- Compter les avis en attente de modération
SELECT 
    COUNT(*) as pending_reviews,
    (SELECT COUNT(*) FROM reviews WHERE approved = 1) as approved_reviews,
    (SELECT COUNT(*) FROM reviews) as total_reviews
FROM reviews 
WHERE approved = 0;

-- Compter les inscrits newsletter
SELECT 
    COUNT(*) as total_subscribers,
    (SELECT COUNT(*) FROM newsletter_subscribers WHERE active = 1) as active_subscribers,
    (SELECT COUNT(*) FROM newsletter_subscribers WHERE active = 0) as unsubscribed
FROM newsletter_subscribers;

-- Vérifier les commandes annulées récemment
SELECT 
    order_number, 
    total, 
    status, 
    promo_code,
    admin_notes,
    created_at, 
    updated_at
FROM orders 
WHERE status = 'cancelled' 
ORDER BY updated_at DESC 
LIMIT 10;

-- ============================================
-- NETTOYAGE (OPTIONNEL - À UTILISER AVEC PRÉCAUTION)
-- ============================================

-- Supprimer les avis non approuvés de plus de 30 jours
-- DELETE FROM reviews WHERE approved = 0 AND created_at < DATE_SUB(NOW(), INTERVAL 30 DAY);

-- Désactiver les codes promo expirés
-- UPDATE promo_codes SET active = 0 WHERE expires_at < NOW() AND active = 1;

-- Nettoyer les anciens inscrits newsletter inactifs (plus de 2 ans)
-- DELETE FROM newsletter_subscribers WHERE active = 0 AND unsubscribed_at < DATE_SUB(NOW(), INTERVAL 2 YEAR);

-- ============================================
-- FIN DU SCRIPT
-- ============================================

SELECT '✅ Vérification terminée !' as status;
