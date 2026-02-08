-- ============================================
-- BASE DE DONNÉES KIND WOLF - SCRIPT COMPLET UNIFIÉ
-- Version: 2.0 - Tous les systèmes intégrés
-- Date: 2026-01-09
-- ============================================

-- Supprimer la base si elle existe (ATTENTION: supprime toutes les données!)
-- Commentez cette ligne si vous voulez conserver les données existantes
-- DROP DATABASE IF EXISTS kindwolf_db;

CREATE DATABASE IF NOT EXISTS kindwolf_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE kindwolf_db;

-- ============================================
-- Table: users - Utilisateurs
-- ============================================
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('customer', 'admin') DEFAULT 'customer',
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: categories - Catégories de produits
-- ============================================
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    image VARCHAR(255),
    display_order INT DEFAULT 0,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_active (active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: products - Produits
-- ============================================
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE NOT NULL,
    description TEXT,
    long_description TEXT,
    price DECIMAL(10,2) NOT NULL,
    compare_price DECIMAL(10,2), -- Prix barré pour promotions
    stock INT DEFAULT 0,
    category VARCHAR(100),
    image VARCHAR(255),
    gallery TEXT, -- JSON array pour plusieurs images
    sku VARCHAR(50) UNIQUE,
    featured BOOLEAN DEFAULT FALSE,
    active BOOLEAN DEFAULT TRUE,
    meta_title VARCHAR(200),
    meta_description TEXT,
    meta_keywords TEXT,
    views INT DEFAULT 0,
    rating DECIMAL(3,2) DEFAULT 0, -- Note moyenne (0-5)
    review_count INT DEFAULT 0, -- Nombre d'avis
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_category (category),
    INDEX idx_featured (featured),
    INDEX idx_active (active),
    INDEX idx_rating (rating),
    FULLTEXT idx_search (name, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: orders - Commandes
-- ============================================
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    shipping_cost DECIMAL(10,2) DEFAULT 0,
    discount_amount DECIMAL(10,2) DEFAULT 0,
    promo_code VARCHAR(50),
    status ENUM('pending', 'processing', 'shipped', 'completed', 'cancelled', 'refunded') DEFAULT 'pending',
    payment_method VARCHAR(50),
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    transaction_id VARCHAR(100),
    shipping_address TEXT,
    billing_address TEXT,
    customer_notes TEXT,
    admin_notes TEXT,
    tracking_number VARCHAR(100),
    shipped_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_order_number (order_number),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: order_items - Articles de commandes
-- ============================================
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(200),
    product_sku VARCHAR(50),
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
    INDEX idx_order_id (order_id),
    INDEX idx_product_id (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: addresses - Adresses clients
-- ============================================
CREATE TABLE addresses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    type ENUM('shipping', 'billing', 'both') NOT NULL,
    firstname VARCHAR(100),
    lastname VARCHAR(100),
    company VARCHAR(150),
    address_line1 VARCHAR(255),
    address_line2 VARCHAR(255),
    city VARCHAR(100),
    state VARCHAR(100),
    postal_code VARCHAR(20),
    country VARCHAR(100) DEFAULT 'France',
    phone VARCHAR(20),
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_is_default (is_default)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: reviews - Avis clients
-- ============================================
CREATE TABLE reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    title VARCHAR(200),
    comment TEXT,
    verified_purchase BOOLEAN DEFAULT FALSE,
    helpful_count INT DEFAULT 0,
    approved BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (user_id, product_id),
    INDEX idx_product_id (product_id),
    INDEX idx_rating (rating),
    INDEX idx_approved (approved)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: newsletter_subscribers - Abonnés newsletter
-- ============================================
CREATE TABLE newsletter_subscribers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    token VARCHAR(64) UNIQUE NOT NULL,
    active TINYINT(1) DEFAULT 1,
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    unsubscribed_at TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_active (active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: promo_codes - Codes promotionnels
-- ============================================
CREATE TABLE promo_codes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) UNIQUE NOT NULL,
    description VARCHAR(255),
    discount_type ENUM('percentage', 'fixed') DEFAULT 'percentage',
    discount_percent DECIMAL(5,2) DEFAULT 0,
    discount_amount DECIMAL(10,2) DEFAULT 0,
    minimum_amount DECIMAL(10,2) DEFAULT 0,
    maximum_discount DECIMAL(10,2),
    usage_limit INT,
    usage_count INT DEFAULT 0,
    user_limit INT DEFAULT 1, -- Limite par utilisateur
    active BOOLEAN DEFAULT TRUE,
    starts_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_code (code),
    INDEX idx_active (active),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: promo_usage - Utilisation des codes promo
-- ============================================
CREATE TABLE promo_usage (
    id INT PRIMARY KEY AUTO_INCREMENT,
    promo_id INT NOT NULL,
    user_id INT NOT NULL,
    order_id INT NOT NULL,
    discount_amount DECIMAL(10,2),
    used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (promo_id) REFERENCES promo_codes(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_promo_user (promo_id, user_id),
    INDEX idx_order_id (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: wishlist - Liste de souhaits
-- ============================================
CREATE TABLE wishlist (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (user_id, product_id),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: site_settings - Paramètres du site
-- ============================================
CREATE TABLE site_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type VARCHAR(50) DEFAULT 'text',
    description VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: contact_messages - Messages de contact
-- ============================================
CREATE TABLE contact_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    subject VARCHAR(200),
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'replied', 'archived') DEFAULT 'new',
    ip_address VARCHAR(45),
    user_agent TEXT,
    replied_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- DONNÉES INITIALES
-- ============================================

-- Créer un compte administrateur
-- Mot de passe: admin123 (à changer après installation)
INSERT INTO users (name, email, password, role) VALUES 
('Admin KIND WOLF', 'admin@kindwolf.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Catégories
INSERT INTO categories (name, slug, description, display_order) VALUES 
('Vêtements', 'vetements', 'T-shirts, sweat-shirts et plus', 1),
('Accessoires', 'accessoires', 'Mugs, sacs et accessoires', 2),
('Décoration', 'decoration', 'Posters, stickers et déco', 3),
('Collection Limitée', 'collection-limitee', 'Éditions limitées exclusives', 4);

-- Produits d'exemple
INSERT INTO products (name, slug, description, price, stock, category, sku, featured, image) VALUES 
('T-Shirt Le Loup Solitaire', 't-shirt-loup-solitaire', 'T-shirt 100% coton bio avec design exclusif du loup dans la forêt enneigée. Coupe unisexe, disponible en plusieurs tailles.', 29.99, 50, 'Vêtements', 'TSH-001', TRUE, 'images/products/tshirt-loup.jpg'),
('Sweat à Capuche Forêt Mystique', 'sweat-foret-mystique', 'Sweat à capuche ultra-confortable avec illustration de la forêt enneigée. Matière douce et chaude.', 49.99, 30, 'Vêtements', 'SWH-001', TRUE, 'images/products/sweat-foret.jpg'),
('Mug Céramique Le Mal Aimée', 'mug-mal-aimee', 'Mug en céramique de qualité avec design inspiré de la pub. Capacité 350ml, passe au lave-vaisselle.', 14.99, 100, 'Accessoires', 'MUG-001', TRUE, 'images/products/mug-mal-aimee.jpg'),
('Poster Premium A2', 'poster-premium-a2', 'Poster haute qualité format A2 (42x59cm) sur papier mat 200g. Design exclusif KIND WOLF.', 19.99, 80, 'Décoration', 'PST-001', TRUE, 'images/products/poster-a2.jpg'),
('Casquette Brodée Loup', 'casquette-brodee-loup', 'Casquette ajustable avec broderie du loup. Matière respirante, visière préformée.', 24.99, 40, 'Accessoires', 'CAP-001', FALSE, 'images/products/casquette-loup.jpg'),
('Tote Bag Canvas', 'tote-bag-canvas', 'Sac en toile de coton robuste avec sérigraphie. Idéal pour le quotidien. Dimensions: 38x42cm.', 16.99, 60, 'Accessoires', 'BAG-001', TRUE, 'images/products/tote-bag.jpg'),
('Stickers Pack Nature', 'stickers-pack-nature', 'Pack de 10 stickers résistants à l\'eau avec différents designs forêt et animaux.', 7.99, 150, 'Décoration', 'STK-001', FALSE, 'images/products/stickers-pack.jpg'),
('Sweat Oversized Édition Limitée', 'sweat-oversized-edition', 'Sweat oversized en édition limitée à 100 exemplaires. Design exclusif numéroté.', 69.99, 15, 'Collection Limitée', 'SWO-001', TRUE, 'images/products/sweat-limited.jpg');

-- Corriger les chemins d'images des produits existants (si nécessaire)
UPDATE products 
SET image = CONCAT('images/products/', image) 
WHERE image NOT LIKE 'images/%' AND image != '' AND image IS NOT NULL;

-- Codes promo d'exemple
INSERT INTO promo_codes (code, description, discount_percent, minimum_amount, usage_limit, expires_at) VALUES 
('BIENVENUE10', 'Code de bienvenue - 10% de réduction', 10.00, 0, NULL, DATE_ADD(NOW(), INTERVAL 1 YEAR)),
('NOEL20', 'Promotion de Noël - 20% de réduction', 20.00, 50.00, 500, '2024-12-31 23:59:59'),
('PRINTEMPS15', 'Promotion Printemps - 15% de réduction', 15.00, 30.00, 1000, '2025-06-30 23:59:59'),
('VIP25', 'Code VIP exclusif - 25% de réduction', 25.00, 100.00, 50, NULL);

-- Paramètres du site
INSERT INTO site_settings (setting_key, setting_value, setting_type, description) VALUES 
('site_name', 'KIND WOLF', 'text', 'Nom du site'),
('site_email', 'contact@kindwolf.com', 'email', 'Email de contact'),
('free_shipping_threshold', '50.00', 'number', 'Montant pour livraison gratuite'),
('default_shipping_cost', '5.99', 'number', 'Frais de port par défaut'),
('currency', 'EUR', 'text', 'Devise'),
('products_per_page', '12', 'number', 'Produits par page'),
('enable_reviews', '1', 'boolean', 'Activer les avis'),
('enable_wishlist', '1', 'boolean', 'Activer la liste de souhaits'),
('maintenance_mode', '0', 'boolean', 'Mode maintenance'),
('google_analytics_id', '', 'text', 'ID Google Analytics'),
('facebook_pixel_id', '', 'text', 'ID Facebook Pixel'),
('instagram_url', 'https://instagram.com/kindwolf', 'url', 'URL Instagram'),
('facebook_url', 'https://facebook.com/kindwolf', 'url', 'URL Facebook');

-- ============================================
-- TRIGGERS UTILES
-- ============================================

-- Générer token de désinscription newsletter
DELIMITER $$
CREATE TRIGGER before_newsletter_insert
BEFORE INSERT ON newsletter_subscribers
FOR EACH ROW
BEGIN
    IF NEW.token IS NULL OR NEW.token = '' THEN
        SET NEW.token = SHA2(CONCAT(NEW.email, RAND(), NOW()), 256);
    END IF;
END$$

-- Mettre à jour automatiquement les ratings des produits
CREATE TRIGGER update_product_rating_after_insert
AFTER INSERT ON reviews
FOR EACH ROW
BEGIN
    UPDATE products SET 
        rating = (
            SELECT COALESCE(AVG(rating), 0) 
            FROM reviews 
            WHERE product_id = NEW.product_id AND approved = 1
        ),
        review_count = (
            SELECT COUNT(*) 
            FROM reviews 
            WHERE product_id = NEW.product_id AND approved = 1
        )
    WHERE id = NEW.product_id;
END$$

CREATE TRIGGER update_product_rating_after_update
AFTER UPDATE ON reviews
FOR EACH ROW
BEGIN
    UPDATE products SET 
        rating = (
            SELECT COALESCE(AVG(rating), 0) 
            FROM reviews 
            WHERE product_id = NEW.product_id AND approved = 1
        ),
        review_count = (
            SELECT COUNT(*) 
            FROM reviews 
            WHERE product_id = NEW.product_id AND approved = 1
        )
    WHERE id = NEW.product_id;
END$$

CREATE TRIGGER update_product_rating_after_delete
AFTER DELETE ON reviews
FOR EACH ROW
BEGIN
    UPDATE products SET 
        rating = (
            SELECT COALESCE(AVG(rating), 0) 
            FROM reviews 
            WHERE product_id = OLD.product_id AND approved = 1
        ),
        review_count = (
            SELECT COUNT(*) 
            FROM reviews 
            WHERE product_id = OLD.product_id AND approved = 1
        )
    WHERE id = OLD.product_id;
END$$

DELIMITER ;

-- ============================================
-- VUES UTILES
-- ============================================

-- Vue des produits les plus vendus
CREATE VIEW v_best_sellers AS
SELECT 
    p.id,
    p.name,
    p.slug,
    p.price,
    p.image,
    SUM(oi.quantity) as total_sold,
    COUNT(DISTINCT o.id) as order_count
FROM products p
JOIN order_items oi ON p.id = oi.product_id
JOIN orders o ON oi.order_id = o.id
WHERE o.status IN ('completed', 'shipped')
GROUP BY p.id
ORDER BY total_sold DESC;

-- Vue des statistiques produits
CREATE VIEW v_product_stats AS
SELECT 
    p.id,
    p.name,
    p.price,
    p.stock,
    p.views,
    COALESCE(AVG(r.rating), 0) as avg_rating,
    COUNT(DISTINCT r.id) as review_count,
    COALESCE(SUM(oi.quantity), 0) as total_sold
FROM products p
LEFT JOIN reviews r ON p.id = r.product_id
LEFT JOIN order_items oi ON p.id = oi.product_id
LEFT JOIN orders o ON oi.order_id = o.id AND o.status IN ('completed', 'shipped')
GROUP BY p.id;

-- ============================================
-- INDEX SUPPLÉMENTAIRES POUR PERFORMANCE
-- ============================================

CREATE INDEX idx_orders_user_status ON orders(user_id, status);
CREATE INDEX idx_products_category_active ON products(category, active);
CREATE INDEX idx_reviews_product_approved ON reviews(product_id, approved);
CREATE INDEX idx_promo_active ON promo_codes(active, expires_at);
CREATE INDEX idx_newsletter_active ON newsletter_subscribers(active);

-- ============================================
-- FIN DU SCRIPT UNIFIÉ
-- ============================================