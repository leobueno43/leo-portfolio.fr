<?php
// pages/about.php - Page À propos
// ============================================

session_start();
require_once '../config.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>À propos - KIND WOLF</title>
    <meta name="description" content="Découvrez l'histoire de KIND WOLF, notre mission et nos valeurs">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/style.css">
</head>
<body>
    <?php include '../header.php'; ?>
    
    <!-- Hero Section -->
    <section class="about-hero">
        <div class="container">
            <h1>Notre Histoire</h1>
            <p class="hero-subtitle">Une passion pour la mode durable et éthique</p>
        </div>
    </section>
    
    <!-- Notre Mission -->
    <section class="about-section">
        <div class="container">
            <div class="about-content">
                <div class="about-text">
                    <h2>🌿 Notre Mission</h2>
                    <p>Chez <strong>KIND WOLF</strong>, nous croyons que la mode peut être à la fois belle et responsable. Fondée en 2020, notre marque s'est donnée pour mission de créer des vêtements de qualité qui respectent à la fois les personnes et la planète.</p>
                    <p>Nous travaillons exclusivement avec des matériaux durables et des partenaires qui partagent nos valeurs d'équité et de transparence. Chaque pièce est conçue pour durer, réduisant ainsi l'impact environnemental de la fast fashion.</p>
                </div>
                <div class="about-image">
                    <img src="<?php echo BASE_URL; ?>/images/about-mission.jpg" alt="Notre mission" onerror="this.src='../images/site/MAISON_LOUP.jpg'">
                </div>
            </div>
        </div>
    </section>
    
    <!-- Nos Valeurs -->
    <section class="about-section bg-cream">
        <div class="container">
            <h2 class="text-center">✨ Nos Valeurs</h2>
            <div class="values-grid">
                <div class="value-card">
                    <div class="value-icon">🌱</div>
                    <h3>Durabilité</h3>
                    <p>Nous utilisons des matériaux écologiques et des procédés de fabrication respectueux de l'environnement.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon">🤝</div>
                    <h3>Éthique</h3>
                    <p>Nos partenaires de production respectent des conditions de travail justes et équitables.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon">💎</div>
                    <h3>Qualité</h3>
                    <p>Chaque pièce est confectionnée avec soin pour garantir durabilité et confort.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon">🔄</div>
                    <h3>Transparence</h3>
                    <p>Nous sommes ouverts sur nos processus de fabrication et notre chaîne d'approvisionnement.</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Notre Équipe -->
    <section class="about-section">
        <div class="container">
            <h2 class="text-center">👥 Notre Équipe</h2>
            <div class="team-grid">
                <div class="team-member">
                    <img src="<?php echo BASE_URL; ?>/images/team-1.jpg" alt="Sarah Martin" onerror="this.src='https://via.placeholder.com/300x300/2F5D50/FFFFFF?text=Sarah+M.'">
                    <h3>Sarah Martin</h3>
                    <p class="team-role">Fondatrice & Directrice Créative</p>
                    <p>Passionnée de mode durable depuis 15 ans</p>
                </div>
                <div class="team-member">
                    <img src="<?php echo BASE_URL; ?>/images/team-2.jpg" alt="Marc Dubois" onerror="this.src='https://via.placeholder.com/300x300/2F5D50/FFFFFF?text=Marc+D.'">
                    <h3>Marc Dubois</h3>
                    <p class="team-role">Responsable Production</p>
                    <p>Expert en textiles écologiques</p>
                </div>
                <div class="team-member">
                    <img src="<?php echo BASE_URL; ?>/images/team-3.jpg" alt="Julie Lefèvre" onerror="this.src='https://via.placeholder.com/300x300/2F5D50/FFFFFF?text=Julie+L.'">
                    <h3>Julie Lefèvre</h3>
                    <p class="team-role">Responsable Qualité</p>
                    <p>Garante de nos standards élevés</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Nos Engagements -->
    <section class="about-section bg-cream">
        <div class="container">
            <h2 class="text-center">🎯 Nos Engagements</h2>
            <div class="commitments-list">
                <div class="commitment-item">
                    <span class="commitment-icon">✓</span>
                    <div class="commitment-text">
                        <h3>Matériaux certifiés bio</h3>
                        <p>100% de nos cotons sont certifiés biologiques GOTS</p>
                    </div>
                </div>
                <div class="commitment-item">
                    <span class="commitment-icon">✓</span>
                    <div class="commitment-text">
                        <h3>Production locale</h3>
                        <p>70% de nos articles sont fabriqués en France et en Europe</p>
                    </div>
                </div>
                <div class="commitment-item">
                    <span class="commitment-icon">✓</span>
                    <div class="commitment-text">
                        <h3>Emballages recyclables</h3>
                        <p>Tous nos emballages sont 100% recyclables et compostables</p>
                    </div>
                </div>
                <div class="commitment-item">
                    <span class="commitment-icon">✓</span>
                    <div class="commitment-text">
                        <h3>Programme de recyclage</h3>
                        <p>Nous reprenons vos anciens vêtements pour leur donner une seconde vie</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Newsletter -->
    <section class="newsletter-section">
        <div class="container">
            <h2 class="text-center">📧 Restez informé</h2>
            <p class="text-center">Inscrivez-vous à notre newsletter pour recevoir nos nouveautés et offres exclusives</p>
            <form id="newsletterForm" class="newsletter-form-inline">
                <input type="email" 
                       id="newsletterEmail" 
                       placeholder="Votre adresse email" 
                       required
                       pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$">
                <button type="submit" class="btn-primary">S'inscrire</button>
            </form>
            <div id="newsletterMessage" class="newsletter-message"></div>
        </div>
    </section>
    
    <?php include '../footer.php'; ?>
    <script src="<?php echo BASE_URL; ?>/script.js"></script>
    <script>
        // Gérer la soumission du formulaire newsletter
        document.getElementById('newsletterForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const email = document.getElementById('newsletterEmail').value;
            subscribeNewsletter(email);
        });
    </script>
</body>
</html>