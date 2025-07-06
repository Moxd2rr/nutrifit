<?php
$page_title = "Accueil";
$page_description = "Découvrez NutriFit, votre plateforme complète de fitness et nutrition personnalisée. Programmes d'entraînement, plans nutritionnels et coaching expert.";

require_once 'includes/header.php';

// Récupération des données pour la page d'accueil
$pdo = getDBConnection();

// Récupérer les programmes populaires
$bdd = $pdo->query("SELECT * FROM programs WHERE status = 'active' ORDER BY created_at DESC LIMIT 3");
$popular_programs = $bdd->fetchAll();

// Récupérer les produits en vedette
$bdd = $pdo->query("SELECT * FROM products WHERE status = 'available' AND stock > 0 ORDER BY created_at DESC LIMIT 6");
$featured_products = $bdd->fetchAll();

// Récupérer les témoignages
$bdd = $pdo->query("SELECT * FROM testimonials WHERE status = 'active' ORDER BY rating DESC LIMIT 3");
$testimonials = $bdd->fetchAll();

// Récupérer les statistiques
$bdd = $pdo->query("SELECT COUNT(*) as total_users FROM users WHERE status = 'active'");
$total_users = $bdd->fetch()['total_users'];

$bdd = $pdo->query("SELECT COUNT(*) as total_programs FROM programs WHERE status = 'active'");
$total_programs = $bdd->fetch()['total_programs'];
?>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-container">
        <div class="hero-content">
            <h1 class="hero-title">
                Gérez votre forme et votre nutrition 
                <span class="highlight">de façon personnalisée</span>
            </h1>
            <p class="hero-description">
                Créez des programmes sportifs et nutritionnels adaptés à vos besoins et objectifs. 
                Suivi facile, conseils experts, progression garantie !
            </p>
            <div class="hero-actions">
                <a href="<?php echo APP_URL; ?>auth/register.php" class="btn btn-primary btn-large">
                    Commencer maintenant
                </a>
                <a href="<?php echo APP_URL; ?>programs/" class="btn btn-outline btn-large">
                    Découvrir les programmes
                </a>
            </div>
        </div>
        <div class="hero-image">
        </div>
    </div>
</section>

<!-- Statistiques -->
<section class="stats">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-number"><?php echo number_format($total_users); ?>+</div>
                <div class="stat-label">Utilisateurs actifs</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?php echo $total_programs; ?>+</div>
                <div class="stat-label">Programmes disponibles</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">95%</div>
                <div class="stat-label">Satisfaction client</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">24/7</div>
                <div class="stat-label">Support disponible</div>
            </div>
        </div>
    </div>
</section>

<!-- Fonctionnalités -->
<section class="features" id="features">
    <div class="container">
        <div class="section-header">
            <h2>Nos fonctionnalités</h2>
            <p>Une plateforme complète pour votre transformation physique et mentale</p>
        </div>
        
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class='bx bxs-dumbbell'></i>
                </div>
                <h3>Programmes d'entraînement</h3>
                <p>Des programmes personnalisés selon votre niveau et vos objectifs, avec exercices détaillés et vidéos explicatives.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class='bx bxs-bowl-rice'></i>
                </div>
                <h3>Plans nutritionnels</h3>
                <p>Des plans alimentaires équilibrés et personnalisés, tenant compte de vos préférences et restrictions.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class='bx bxs-chart-line'></i>
                </div>
                <h3>Suivi de progression</h3>
                <p>Suivez vos performances et votre évolution avec des outils de mesure précis et des visualisations intuitives.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class='bx bxs-user-check'></i>
                </div>
                <h3>Coaching personnalisé</h3>
                <p>Bénéficiez d'un soutien constant pour rester motivé et répondre à vos questions tout au long de votre parcours.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class='bx bxs-bell'></i>
                </div>
                <h3>Rappels et notifications</h3>
                <p>Ne manquez jamais une séance grâce à des rappels personnalisés et des notifications adaptées à votre emploi du temps.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class='bx bxs-shopping-bag'></i>
                </div>
                <h3>Boutique spécialisée</h3>
                <p>Accédez à une sélection de produits de qualité pour soutenir votre progression et optimiser vos résultats.</p>
            </div>
        </div>
    </div>
</section>

<!-- Programmes populaires -->
<section class="programs">
    <div class="container">
        <div class="section-header">
            <h2>Programmes populaires</h2>
            <p>Découvrez nos programmes les plus appréciés</p>
        </div>
        
        <div class="programs-grid">
            <?php foreach ($popular_programs as $program): ?>
            <div class="program-card">
                <div class="program-image">
                    <?php if (!empty($program['image_url'])): ?>
                        <img src="<?php echo htmlspecialchars($program['image_url']); ?>" 
                             alt="<?php echo htmlspecialchars($program['title']); ?>"
                             onerror="this.src='<?php echo APP_URL; ?>assets/images/programs/default.jpg'">
                    <?php else: ?>
                        <img src="<?php echo APP_URL; ?>assets/images/programs/default.jpg" 
                             alt="<?php echo htmlspecialchars($program['title']); ?>">
                    <?php endif; ?>
                </div>
                <div class="program-content">
                    <h3><?php echo htmlspecialchars($program['title']); ?></h3>
                    <p><?php echo htmlspecialchars(substr($program['description'], 0, 100)) . '...'; ?></p>
                    <div class="program-meta">
                        <span class="difficulty <?php echo $program['difficulty']; ?>">
                            <?php echo ucfirst($program['difficulty']); ?>
                        </span>
                        <span class="duration"><?php echo $program['duration_weeks']; ?> semaines</span>
                    </div>
                    <div class="program-price">
                        <span class="price"><?php echo formatPrice($program['price']); ?></span>
                        <a href="<?php echo APP_URL; ?>programs/view.php?id=<?php echo $program['id']; ?>" 
                           class="btn btn-primary">Voir le programme</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="section-actions">
            <a href="<?php echo APP_URL; ?>programs/" class="btn btn-outline">Voir tous les programmes</a>
        </div>
    </div>
</section>

<!-- Produits en vedette -->
<section class="featured-products">
    <div class="container">
        <div class="section-header">
            <h2>Produits en vedette</h2>
            <p>Équipements et suppléments de qualité pour votre progression</p>
        </div>
        
        <div class="products-grid">
            <?php foreach ($featured_products as $product): ?>
            <div class="product-card">
                <div class="product-image">
                    <?php if (!empty($product['image_url'])): ?>
                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                             onerror="this.src='<?php echo APP_URL; ?>assets/images/products/default.jpg'">
                    <?php else: ?>
                        <img src="<?php echo APP_URL; ?>assets/images/products/default.jpg" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>">
                    <?php endif; ?>
                </div>
                <div class="product-content">
                    <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                    <p><?php echo htmlspecialchars(substr($product['description'], 0, 80)) . '...'; ?></p>
                    <div class="product-category">
                        <?php echo ucfirst($product['category']); ?>
                    </div>
                    <div class="product-price">
                        <span class="price"><?php echo formatPrice($product['price']); ?></span>
                        <a href="<?php echo APP_URL; ?>shop/product.php?id=<?php echo $product['id']; ?>" 
                           class="btn btn-primary">Voir le produit</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="section-actions">
            <a href="<?php echo APP_URL; ?>shop/" class="btn btn-outline">Voir tous les produits</a>
        </div>
    </div>
</section>

<!-- Témoignages -->
<section class="testimonials">
    <div class="container">
        <div class="section-header">
            <h2>Ils nous font confiance</h2>
            <p>Découvrez les témoignages de nos utilisateurs satisfaits</p>
        </div>
        
        <div class="testimonials-grid">
            <?php foreach ($testimonials as $testimonial): ?>
            <div class="testimonial-card">
                <div class="testimonial-content">
                    <p>"<?php echo htmlspecialchars($testimonial['content']); ?>"</p>
                </div>
                <div class="testimonial-author">
                    <div class="author-image">
                        <img src="<?php echo $testimonial['image_url'] ?: APP_URL . 'assets/images/default-avatar.jpg'; ?>" 
                             alt="<?php echo htmlspecialchars($testimonial['name']); ?>">
                    </div>
                    <div class="author-info">
                        <h4><?php echo htmlspecialchars($testimonial['name']); ?></h4>
                        <div class="rating">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class='bx bxs-star <?php echo $i <= $testimonial['rating'] ? 'filled' : ''; ?>'></i>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="cta">
    <div class="container">
        <div class="cta-content">
            <h2>Prêt à vous transformer ?</h2>
            <p>Rejoignez NutriFit et commencez votre parcours vers une meilleure santé dès aujourd'hui !</p>
            <div class="cta-actions">
                <a href="<?php echo APP_URL; ?>auth/register.php" class="btn btn-primary btn-large">
                    Créer mon compte
                </a>
                <a href="<?php echo APP_URL; ?>contact/" class="btn btn-outline btn-large">
                    Contacter un coach
                </a>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?> 