<?php
require_once __DIR__ . '/../config/database.php';
// Active le tampon de sortie pour laisser les scripts effectuer des redirections même après l'inclusion du header
if (!ob_get_level()) {
    ob_start();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . APP_NAME : APP_NAME; ?></title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?php echo APP_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>assets/css/shop.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    
    <!-- Meta tags -->
    <meta name="description" content="<?php echo isset($page_description) ? $page_description : 'Plateforme de fitness et nutrition personnalisée'; ?>">
    <meta name="keywords" content="fitness, nutrition, entraînement, santé, bien-être">
    <meta name="author" content="NutriFit Team">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo APP_URL; ?>assets/images/favicon.ico">
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?php echo isset($page_title) ? $page_title . ' - ' . APP_NAME : APP_NAME; ?>">
    <meta property="og:description" content="<?php echo isset($page_description) ? $page_description : 'Plateforme de fitness et nutrition personnalisée'; ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo $_SERVER['REQUEST_URI']; ?>">
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?php echo generateCSRFToken(); ?>">


</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <a href="<?php echo APP_URL; ?>">
                    <span class="logo-text"><?php echo APP_NAME; ?></span>
                </a>
            </div>
            
            <div class="nav-menu">
                <a href="<?php echo APP_URL; ?>" class="nav-link">Accueil</a>
                <a href="<?php echo APP_URL; ?>programs/" class="nav-link">Programmes</a>
                <a href="<?php echo APP_URL; ?>programs/generate_program_ai.php" class="nav-link">Générer un programme</a>
                <a href="<?php echo APP_URL; ?>shop/" class="nav-link">Boutique</a>
                <a href="<?php echo APP_URL; ?>about/" class="nav-link">À Propos</a>
            </div>
            
            <div class="nav-actions">
                <?php if (isLoggedIn()): ?>
                    <div class="user-menu">
                        <span class="user-name"><i class='bx bxs-user-circle'></i> <?php echo $_SESSION['user_name']; ?></span>
                        <div class="user-dropdown">
                            <a href="<?php echo APP_URL; ?>profile/" class="dropdown-item">
                                <i class='bx bxs-user'></i> Mon Profil
                            </a>
                            <?php if (hasRole('admin')): ?>
    <a href="<?php echo APP_URL; ?>admin/" class="dropdown-item">
        <i class='bx bxs-cog'></i> Administration
    </a>
<?php endif; ?>
                            <a href="<?php echo APP_URL; ?>shop/cart.php" class="dropdown-item">
                                <i class='bx bxs-cart'></i> Panier
                                <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                                    <span class="cart-count"><?php echo array_sum($_SESSION['cart']); ?></span>
                                <?php endif; ?>
                            </a>
                            <a href="<?php echo APP_URL; ?>auth/logout.php" class="dropdown-item">
                                <i class='bx bxs-log-out'></i> Déconnexion
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?php echo APP_URL; ?>auth/login.php" class="btn btn-outline">Connexion</a>
                    <a href="<?php echo APP_URL; ?>auth/register.php" class="btn btn-primary">S'inscrire</a>
                <?php endif; ?>
            </div>
            
            <div class="nav-toggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <!-- Messages flash -->
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="flash-message <?php echo $_SESSION['flash_type'] ?? 'info'; ?>">
            <?php 
            echo $_SESSION['flash_message']; 
            unset($_SESSION['flash_message'], $_SESSION['flash_type']);
            ?>
        </div>
    <?php endif; ?>

    <!-- Contenu principal -->
    <main class="main-content">

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const navToggle = document.querySelector('.nav-toggle');
            const navMenu = document.querySelector('.nav-menu');
            const navLinks = document.querySelectorAll('.nav-link');
            const userMenu = document.querySelector('.user-menu');
            const userDropdown = document.querySelector('.user-dropdown');

            // Toggle mobile menu
            if (navToggle) {
                navToggle.addEventListener('click', () => {
                    navMenu.classList.toggle('active');
                    navToggle.classList.toggle('active');
                    if (navMenu.classList.contains('active') && userDropdown) {
                        userDropdown.style.display = 'none';
                    }
                });
            }

            // Close mobile menu and update active link when a link is clicked
            navLinks.forEach(link => {
                link.addEventListener('click', () => {
                    navMenu.classList.remove('active');
                    navToggle.classList.remove('active');
                    // Mettre à jour l'état actif
                    navLinks.forEach(l => l.classList.remove('active'));
                    link.classList.add('active');
                });
            });

            // Highlight active navigation link on page load
            const normalize = (path) => {
                // Supprime les barres de fin/début pour uniformiser et passe en minuscule
                return path.replace(/(^\/|\/$)/g, '').toLowerCase() || '/';
            };

            const currentPath = normalize(window.location.pathname);
            navLinks.forEach(link => {
                const linkPath = normalize(new URL(link.href).pathname);
                if (linkPath === currentPath) {
                    link.classList.add('active');
                } else {
                    link.classList.remove('active');
                }
            });

            // Handle responsive menu
            const navActions = document.querySelector('.nav-actions');
            const navMenuContainer = document.querySelector('.nav-menu');
            const originalParent = navActions.parentNode;

            const handleResponsiveMenu = () => {
                if (window.innerWidth <= 1024) {
                    if (!navMenuContainer.contains(navActions)) {
                        navMenuContainer.appendChild(navActions);
                    }
                } else {
                    if (!originalParent.contains(navActions)) {
                        originalParent.insertBefore(navActions, navToggle);
                    }
                    navMenu.classList.remove('active');
                    navToggle.classList.remove('active');
                }
            };

            window.addEventListener('resize', handleResponsiveMenu);
            handleResponsiveMenu();

            // Close dropdowns if clicked outside (for desktop)
            document.addEventListener('click', (event) => {
                if (userMenu && !userMenu.contains(event.target) && !navToggle.contains(event.target)) {
                    if (!navMenu.classList.contains('active')) {
                        userDropdown.style.display = '';
                    }
                }
            });
        });
    </script>

</body>
</html>