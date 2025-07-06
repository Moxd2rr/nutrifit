<?php
require_once __DIR__ . '/../config/database.php';

// Vérifier si l'utilisateur est connecté et admin
if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ' . APP_URL . 'auth/login.php');
    exit;
}

$page_title = 'Administration';
$page_description = 'Tableau de bord administrateur';



// Récupération des données
$users = getAllUsers();
$programs = getAllPrograms();
$orders = getAllOrders();
$products = getAllProducts();

// GESTION DES UTILISATEURS
$errors = [];
$success_message = '';
$edit_user_data = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $errors[] = "Erreur de sécurité (jeton CSRF invalide). Veuillez réessayer.";
    } else if (isset($_POST['action']) && strpos($_POST['action'], 'user') !== false) {
        switch ($_POST['action']) {
            case 'add_user':
                // Validation et assainissement des données
                $new_user_data = [
                    'name' => sanitizeInput($_POST['name'] ?? ''),
                    'email' => sanitizeInput($_POST['email'] ?? ''),
                    'password' => $_POST['password'] ?? '',
                    'role' => sanitizeInput($_POST['role'] ?? 'user'),
                    'status' => sanitizeInput($_POST['status'] ?? 'active'),
                    'phone' => sanitizeInput($_POST['phone'] ?? ''),
                    'birth_date' => sanitizeInput($_POST['birth_date'] ?? ''),
                    'gender' => sanitizeInput($_POST['gender'] ?? ''),
                    'height' => sanitizeInput($_POST['height'] ?? ''),
                    'weight' => sanitizeInput($_POST['weight'] ?? ''),
                    'fitness_level' => sanitizeInput($_POST['fitness_level'] ?? ''),
                    'goals' => sanitizeInput($_POST['goals'] ?? ''),
                ];

                // Validation spécifique pour l'ajout
                if (empty($new_user_data['name']) || empty($new_user_data['email']) || empty($new_user_data['password'])) {
                    $errors[] = "Le nom, l'email et le mot de passe sont requis pour l'ajout.";
                } elseif (!filter_var($new_user_data['email'], FILTER_VALIDATE_EMAIL)) {
                    $errors[] = "L'email n'est pas valide.";
                } elseif (strlen($new_user_data['password']) < 8) {
                    $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
                } else {
                    // Vérifier si l'email existe déjà
                    $bdd = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                    $bdd->execute([$new_user_data['email']]);
                    if ($bdd->rowCount() > 0) {
                        $errors[] = "Cette adresse email est déjà utilisée.";
                    } else {
                        if (createUser($new_user_data)) {
                            $success_message = "Utilisateur ajouté avec succès !";
                        } else {
                            $errors[] = "Erreur lors de l'ajout de l'utilisateur.";
                        }
                    }
                }
                break;

            case 'edit_user':
                $user_id = (int)($_POST['user_id'] ?? 0);
                if (!$user_id) {
                    $errors[] = "ID utilisateur manquant pour la modification.";
                    break;
                }
                $updated_user_data = [
                    'name' => sanitizeInput($_POST['name'] ?? ''),
                    'email' => sanitizeInput($_POST['email'] ?? ''),
                    'password' => $_POST['password'] ?? '', // Might be empty if not changed
                    'role' => sanitizeInput($_POST['role'] ?? 'user'),
                    'status' => sanitizeInput($_POST['status'] ?? 'active'),
                    'phone' => sanitizeInput($_POST['phone'] ?? ''),
                    'birth_date' => sanitizeInput($_POST['birth_date'] ?? ''),
                    'gender' => sanitizeInput($_POST['gender'] ?? ''),
                    'height' => sanitizeInput($_POST['height'] ?? ''),
                    'weight' => sanitizeInput($_POST['weight'] ?? ''),
                    'fitness_level' => sanitizeInput($_POST['fitness_level'] ?? ''),
                    'goals' => sanitizeInput($_POST['goals'] ?? ''),
                ];
                
                // Validation spécifique pour la modification
                if (empty($updated_user_data['name']) || empty($updated_user_data['email'])) {
                    $errors[] = "Le nom et l'email sont requis pour la modification.";
                } elseif (!filter_var($updated_user_data['email'], FILTER_VALIDATE_EMAIL)) {
                    $errors[] = "L'email n'est pas valide.";
                } elseif (!empty($updated_user_data['password']) && strlen($updated_user_data['password']) < 8) {
                    $errors[] = "Le nouveau mot de passe doit contenir au moins 8 caractères.";
                } else {
                    // Vérifier si l'email est déjà utilisé par un AUTRE utilisateur
                    $bdd = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                    $bdd->execute([$updated_user_data['email'], $user_id]);
                    if ($bdd->rowCount() > 0) {
                        $errors[] = "Cette adresse email est déjà utilisée par un autre utilisateur.";
                    } else {
                        if (updateUser($user_id, $updated_user_data)) {
                            $success_message = "Utilisateur mis à jour avec succès !";
                        } else {
                            $errors[] = "Erreur lors de la mise à jour de l'utilisateur.";
                        }
                    }
                }
                break;

            case 'delete_user':
                $user_id = (int)($_POST['user_id'] ?? 0);
                if (!$user_id) {
                    $errors[] = "ID utilisateur manquant pour la suppression.";
                } elseif (deleteUser($user_id)) {
                    $success_message = "Utilisateur supprimé avec succès !";
                } else {
                    $errors[] = "Erreur lors de la suppression de l'utilisateur.";
                }
                break;

            case 'get_user_for_edit':
                $user_id = (int)($_POST['user_id'] ?? 0);
                if ($user_id) {
                    $edit_user_data = getUserById($user_id);
                    if (!$edit_user_data) {
                        $errors[] = "Utilisateur non trouvé pour édition.";
                    } else {
                         // Return JSON for AJAX request
                        header('Content-Type: application/json');
                        echo json_encode(['success' => true, 'user' => $edit_user_data]);
                        exit;
                    }
                } else {
                    $errors[] = "ID utilisateur manquant pour l'édition.";
                }
                break;
        }
    } else if (isset($_POST['action']) && strpos($_POST['action'], 'product') !== false) {
        // GESTION DES PRODUITS
        switch ($_POST['action']) {
            case 'add_product':
                // Logique d'ajout de produit
                break;
            case 'edit_product':
                // Logique de modification
                break;
            case 'delete_product':
                // Logique de suppression
                break;
        }
    }
}

$csrf_token = generateCSRFToken();

include_once __DIR__ . '/../includes/header.php';
?>

<!-- Menu d'administration horizontal -->
<div class="admin-menu">
    <div class="admin-menu-container">
        <div class="admin-menu-item active" data-section="dashboard">
            <i class='bx bxs-dashboard'></i>
            <span>Tableau de bord</span>
        </div>
        <div class="admin-menu-item" data-section="users">
            <i class='bx bxs-user-detail'></i>
            <span>Utilisateurs</span>
        </div>
        <div class="admin-menu-item" data-section="programs">
            <i class='bx bxs-dumbbell'></i>
            <span>Programmes</span>
        </div>
        <div class="admin-menu-item" data-section="orders">
            <i class='bx bxs-shopping-bag'></i>
            <span>Commandes</span>
        </div>
        <div class="admin-menu-item" data-section="products">
            <i class='bx bxs-package'></i>
            <span>Produits</span>
        </div>
        <div class="admin-menu-item" data-section="settings">
            <i class='bx bxs-cog'></i>
            <span>Paramètres</span>
        </div>
    </div>
</div>

<div class="admin-container">
    <!-- Section Tableau de bord -->
    <div class="admin-section active" id="dashboard">
        <div class="admin-header">
            <h1>Tableau de bord administrateur</h1>
            <p>Bienvenue dans votre espace d'administration</p>
        </div>

        <!-- Statistiques -->
        <div class="admin-stats">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class='bx bxs-user'></i>
                </div>
                <div class="stat-content">
                    <h3>Utilisateurs</h3>
                    <p class="stat-number"><?php echo getTotalUsers(); ?></p>
                    <p class="stat-label">Total inscrits</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class='bx bxs-dumbbell'></i>
                </div>
                <div class="stat-content">
                    <h3>Programmes</h3>
                    <p class="stat-number"><?php echo getTotalPrograms(); ?></p>
                    <p class="stat-label">Programmes créés</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class='bx bxs-shopping-bag'></i>
                </div>
                <div class="stat-content">
                    <h3>Commandes</h3>
                    <p class="stat-number"><?php echo getTotalOrders(); ?></p>
                    <p class="stat-label">Commandes totales</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class='bx bxs-dollar-circle'></i>
                </div>
                <div class="stat-content">
                    <h3>Revenus</h3>
                    <p class="stat-number"><?php echo getTotalRevenue(); ?> FCFA</p>
                    <p class="stat-label">Chiffre d'affaires</p>
                </div>
            </div>
        </div>

        <!-- Activité récente -->
        <div class="admin-recent">
            <h2>Activité récente</h2>
            <div class="recent-activity">
                <?php $recentActivities = getRecentActivity(); ?>
                <?php if (!empty($recentActivities)): ?>
                    <?php foreach ($recentActivities as $activity): ?>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class='bx <?php echo $activity['icon']; ?>'></i>
                            </div>
                            <div class="activity-content">
                                <p><?php echo htmlspecialchars($activity['description']); ?></p>
                                <span class="activity-time"><?php echo $activity['time']; ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-activity">Aucune activité récente</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Section Utilisateurs -->
    <div class="admin-section" id="users">
        <div class="admin-header">
            <h1>Gestion des utilisateurs</h1>
            <p>Affichez, ajoutez, modifiez et supprimez les comptes utilisateurs.</p>
        </div>

        <div class="admin-content">
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success_message); ?>
                    </div>
            <?php endif; ?>
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                            </div>
            <?php endif; ?>

            <div class="admin-actions-bar">
                <button class="btn btn-primary" id="addUserBtn">
                    <i class='bx bx-plus'></i> Nouvel utilisateur
                </button>
                <div class="search-box">
                    <input type="text" placeholder="Rechercher un utilisateur..." id="searchUsers">
                    <i class='bx bx-search'></i>
                            </div>
                    </div>

            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Statut</th>
                            <th>Date d'inscription</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="7" class="text-center">Aucun utilisateur inscrit</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td>
                                        <div class="user-details">
                                           
                                            <span><?php echo htmlspecialchars($user['name']); ?></span>
                    </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><span class="role-badge role-<?php echo htmlspecialchars($user['role']); ?>"><?php echo htmlspecialchars(ucfirst($user['role'])); ?></span></td>
                                    <td><span class="status-badge status-<?php echo htmlspecialchars($user['status']); ?>"><?php echo htmlspecialchars(ucfirst($user['status'])); ?></span></td>
                                    <td><?php echo htmlspecialchars(formatDate($user['created_at'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-sm btn-edit" data-id="<?php echo $user['id']; ?>">Modifier</button>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">
                                                <input type="hidden" name="action" value="delete_user">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                                <button type="submit" class="btn btn-sm btn-delete">Supprimer</button>
                </form>
            </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                            </div>
                            
            <!-- Formulaire d'ajout/modification d'utilisateur (caché par défaut) -->
            <div class="admin-card mt-4" id="userFormContainer" style="display:none;">
                
            </div>
        </div>
    </div>

    <!-- Section Programmes -->
    <div class="admin-section" id="programs">
        <div class="admin-header">
            <h1>Gestion des programmes</h1>
            <p>Programmes générés par les utilisateurs via l'IA</p>
        </div>

        <div class="admin-content">
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Titre</th>
                            <th>Description</th>
                            <th>Durée</th>
                            <th>Difficulté</th>
                            <th>Catégorie</th>
                            <th>Prix</th>
                            <th>Statut</th>
                            <th>Créé par</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($programs)): ?>
                            <tr>
                                <td colspan="11" class="text-center">Aucun programme généré pour le moment</td>
                            </tr>
                        <?php else: ?>
                        <?php foreach ($programs as $program): ?>
                        <tr>
                                    <td><?= $program['id'] ?></td>
                            <td>
                                        <div class="program-title">
                                            <strong><?= htmlspecialchars($program['title']) ?></strong>
                                        </div>
                            </td>
                                    <td>
                                        <div class="program-description">
                                            <?= htmlspecialchars(substr($program['description'], 0, 100)) ?>...
                                        </div>
                                    </td>
                                    <td><?= $program['duration_weeks'] ?> semaines</td>
                                    <td>
                                        <span class="badge badge-<?= $program['difficulty'] == 'Débutant' ? 'success' : ($program['difficulty'] == 'Intermédiaire' ? 'warning' : 'danger') ?>">
                                            <?= $program['difficulty'] ?>
                                </span>
                            </td>
                                    <td><?= htmlspecialchars($program['category']) ?></td>
                                    <td><?= number_format($program['price'], 0, ',', ' ') ?> FCFA</td>
                            <td>
                                        <span class="badge badge-<?= $program['status'] == 'active' ? 'success' : 'secondary' ?>">
                                            <?= $program['status'] == 'active' ? 'Actif' : 'Inactif' ?>
                                </span>
                            </td>
                                    <td><?= htmlspecialchars($program['created_by_name'] ?? 'Utilisateur supprimé') ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($program['created_at'])) ?></td>
                            <td>
                                <div class="table-actions">
                                            <button class="btn btn-sm btn-outline" onclick="viewProgram(<?= $program['id'] ?>)">
                                                <i class='bx bx-show'></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline" onclick="editProgram(<?= $program['id'] ?>)">
                                        <i class='bx bx-edit'></i>
                                    </button>
                                            <button class="btn btn-sm btn-danger" onclick="deleteProgram(<?= $program['id'] ?>)">
                                        <i class='bx bx-trash'></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Section Commandes -->
    <div class="admin-section" id="orders">
        <div class="admin-header">
            <h1>Gestion des commandes</h1>
            <p>Suivi des commandes des utilisateurs</p>
        </div>

        <div class="admin-content">
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Client</th>
                            <th>Produits</th>
                            <th>Total</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="7" class="text-center">Aucune commande pour le moment</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><?= $order['id'] ?></td>
                                    <td><?= htmlspecialchars($order['user_name'] ?? 'Utilisateur supprimé') ?></td>
                                    <td>
                                        <div class="order-items">
                                            <?= htmlspecialchars(substr($order['items'], 0, 50)) ?>...
                                    </div>
                                    </td>
                                    <td><?= number_format($order['total_amount'], 0, ',', ' ') ?> FCFA</td>
                                    <td>
                                        <span class="badge badge-<?= 
                                            $order['status'] == 'completed' ? 'success' : 
                                            ($order['status'] == 'pending' ? 'warning' : 
                                            ($order['status'] == 'cancelled' ? 'danger' : 'secondary')) 
                                        ?>">
                                            <?= ucfirst($order['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                                    <td>
                                        <div class="table-actions">
                                            <button class="btn btn-sm btn-outline" onclick="viewOrder(<?= $order['id'] ?>)">
                                                <i class='bx bx-show'></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline" onclick="editOrder(<?= $order['id'] ?>)">
                                                <i class='bx bx-edit'></i>
                                            </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteOrder(<?= $order['id'] ?>)">
                                                    <i class='bx bx-trash'></i>
                                                </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
                    </div>
                    </div>
                    
    <!-- Section Produits -->
    <div class="admin-section" id="products">
        <div class="admin-header">
            <h1>Gestion des produits</h1>
            <p>Ajoutez, modifiez et supprimez les produits de la boutique.</p>
        </div>
        <div class="admin-content">
                <div class="admin-actions-bar">
                <button class="btn btn-primary" id="addProductBtn">
                        <i class='bx bx-plus'></i> Nouveau produit
                    </button>
                </div>
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Prix</th>
                            <th>Stock</th>
                            <th>Catégorie</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="8" class="text-center">Aucun produit trouvé</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?php echo $product['id']; ?></td>
                                    <td>
                                        <div class="user-details">
                                            <img src="<?php echo htmlspecialchars($product['image_url'] ? $product['image_url'] : APP_URL . 'assets/images/products/default.jpg'); ?>" alt="Produit" class="user-avatar">
                                            <span><?php echo htmlspecialchars($product['name']); ?></span>
            </div>
                                    </td>
                                    <td><?php echo formatPrice($product['price']); ?></td>
                                    <td><?php echo $product['stock']; ?></td>
                                    <td><?php echo htmlspecialchars($product['category']); ?></td>
                                    <td><span class="status-badge status-<?php echo htmlspecialchars($product['status']); ?>"><?php echo htmlspecialchars(ucfirst($product['status'])); ?></span></td>
                                    <td><?php echo htmlspecialchars(formatDate($product['created_at'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-sm btn-edit btn-edit-product" data-id="<?php echo $product['id']; ?>">Modifier</button>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ?');">
                                                <input type="hidden" name="action" value="delete_product">
                                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                                <button type="submit" class="btn btn-sm btn-delete">Supprimer</button>
                                            </form>
            </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Section Paramètres -->
    <div class="admin-section" id="settings">
        <div class="admin-header">
            <h1>Paramètres du site</h1>
            <p>Configurez les paramètres généraux de votre site</p>
        </div>

        <div class="admin-content">
            <div class="settings-grid">
                <div class="admin-card">
                    <h3>Paramètres généraux</h3>
                    <form class="admin-form">
                        <div class="form-group">
                            <label>Nom du site</label>
                            <input type="text" name="site_name" value="<?php echo getSiteSetting('site_name', 'NutriFit'); ?>">
                        </div>
                        <div class="form-group">
                            <label>Description du site</label>
                            <textarea name="site_description"><?php echo getSiteSetting('site_description', ''); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Email de contact</label>
                            <input type="email" name="contact_email" value="<?php echo getSiteSetting('contact_email', ''); ?>">
                        </div>
                        <button type="submit" class="btn btn-primary">Sauvegarder</button>
                    </form>
                </div>

                <div class="admin-card">
                    <h3>Apparence</h3>
                    <form class="admin-form">
                        <div class="form-group">
                            <label>Couleur principale</label>
                            <input type="color" name="primary_color" value="<?php echo getSiteSetting('primary_color', '#10b981'); ?>">
                        </div>
                        <div class="form-group">
                            <label>Couleur secondaire</label>
                            <input type="color" name="secondary_color" value="<?php echo getSiteSetting('secondary_color', '#3b82f6'); ?>">
                        </div>
                        <button type="submit" class="btn btn-primary">Sauvegarder</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gestion de la modale
        const modal = document.getElementById('formModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalBody = document.getElementById('modalBody');
        const closeModalBtn = document.getElementById('closeModal');

        function openModal() { modal.style.display = 'flex'; }
        function closeModal() { modal.style.display = 'none'; }

        closeModalBtn.addEventListener('click', closeModal);
        window.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal();
            }
        });

        // Gestion des sections admin
        const adminMenuItems = document.querySelectorAll('.admin-menu-item');
        const adminSections = document.querySelectorAll('.admin-section');

        adminMenuItems.forEach(item => {
            item.addEventListener('click', function() {
                adminMenuItems.forEach(i => i.classList.remove('active'));
                this.classList.add('active');
                const targetSection = this.dataset.section;
                adminSections.forEach(section => {
                    if (section.id === targetSection) {
                        section.classList.add('active');
                    } else {
                        section.classList.remove('active');
                    }
                });
            });
        });

        // --- Gestion des utilisateurs ---
        const addUserBtn = document.getElementById('addUserBtn');
        const userFormContent = document.getElementById('userFormContent');
        const userForm = document.getElementById('userForm');
        const userFormTitle = document.getElementById('userFormTitle');
        const userFormAction = document.getElementById('userFormAction');
        const userId = document.getElementById('userId');
        const userName = document.getElementById('userName');
        const userEmail = document.getElementById('userEmail');
        const userPassword = document.getElementById('userPassword');
        const userRole = document.getElementById('userRole');
        const userStatus = document.getElementById('userStatus');
        const userPhone = document.getElementById('userPhone');
        const userBirthDate = document.getElementById('userBirthDate');
        const userGender = document.getElementById('userGender');
        const userHeight = document.getElementById('userHeight');
        const userWeight = document.getElementById('userWeight');
        const userFitnessLevel = document.getElementById('userFitnessLevel');
        const userGoals = document.getElementById('userGoals');
        const userSubmitBtnText = document.getElementById('userSubmitBtnText');
        const cancelUserForm = document.getElementById('cancelUserForm');
        const editUserBtns = document.querySelectorAll('.btn-edit[data-id]');

        function showUserForm() {
            // Cacher les autres formulaires potentiels dans la modale
            // Exemple: document.getElementById('productFormContent').style.display = 'none';
            userFormContent.style.display = 'block';
            openModal();
        }

        if (addUserBtn) {
        addUserBtn.addEventListener('click', function() {
                showUserForm();
                modalTitle.textContent = 'Ajouter un nouvel utilisateur';
                userForm.reset(); // Reset all form fields
            userFormAction.value = 'add_user';
                userId.value = '0';
            userPassword.required = true; // Password is required for new user
            userSubmitBtnText.textContent = 'Ajouter l\'utilisateur';
        });
        }

        if (cancelUserForm) {
            cancelUserForm.addEventListener('click', closeModal);
        }

        editUserBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const userIdToEdit = this.dataset.id;
                showUserForm();
                modalTitle.textContent = 'Modifier l\'utilisateur';
                userFormAction.value = 'edit_user';
                userId.value = userIdToEdit;
                userPassword.required = false; // Password is not required for edit
                userSubmitBtnText.textContent = 'Mettre à jour l\'utilisateur';

                // Fetch user data via AJAX to pre-fill the form
                const formData = new FormData();
                formData.append('action', 'get_user_for_edit');
                formData.append('user_id', userIdToEdit);

                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text()) // Get raw text to debug PHP output issues
                .then(text => {
                    try {
                        const data = JSON.parse(text);
                        if (data && data.success && data.user) {
                            userName.value = data.user.name;
                            userEmail.value = data.user.email;
                            userRole.value = data.user.role;
                            userStatus.value = data.user.status;
                            userPhone.value = data.user.phone || '';
                            userBirthDate.value = data.user.birth_date || '';
                            userGender.value = data.user.gender || '';
                            userHeight.value = data.user.height || '';
                            userWeight.value = data.user.weight || '';
                            userFitnessLevel.value = data.user.fitness_level || '';
                            userGoals.value = data.user.goals || '';
                        } else {
                            alert('Erreur lors du chargement des données utilisateur: ' + (data ? data.error : 'Réponse inattendue.'));
                        }
                    } catch (e) {
                        console.error('Erreur de parse JSON:', e);
                        console.error('Réponse brute:', text);
                        alert('Erreur de communication avec le serveur lors du chargement des données utilisateur.');
                    }
                })
                .catch(error => {
                    console.error('Erreur Fetch:', error);
                    alert('Erreur réseau lors du chargement des données utilisateur.');
                });
            });
        });

        // Update active section on load if a section ID is present in URL hash
        if (window.location.hash) {
            const targetId = window.location.hash.substring(1);
            const correspondingMenuItem = document.querySelector(`.admin-menu-item[data-section="${targetId}"]`);
            if (correspondingMenuItem) {
                adminMenuItems.forEach(i => i.classList.remove('active'));
                correspondingMenuItem.classList.add('active');
                adminSections.forEach(section => {
                    if (section.id === targetId) {
                        section.classList.add('active');
                    } else {
                        section.classList.remove('active');
                    }
                });
            }
        }

        // --- Gestion des produits ---
        const addProductBtn = document.getElementById('addProductBtn'); // A AJOUTER AU HTML
        const productFormContent = document.getElementById('productFormContent');
        const productForm = document.getElementById('productForm');
        const cancelProductForm = document.getElementById('cancelProductForm');

        function showProductForm() {
            userFormContent.style.display = 'none';
            productFormContent.style.display = 'block';
            openModal();
        }

        if(addProductBtn) {
            addProductBtn.addEventListener('click', function() {
                showProductForm();
                modalTitle.textContent = 'Ajouter un nouveau produit';
                productForm.reset();
                document.getElementById('productFormAction').value = 'add_product';
                document.getElementById('productId').value = '0';
                document.getElementById('productSubmitBtnText').textContent = 'Ajouter';
            });
        }
        
        if(cancelProductForm) {
            cancelProductForm.addEventListener('click', closeModal);
        }
    });

    // Fonctions pour les programmes
    function viewProgram(id) {
        alert('Voir les détails du programme ' + id);
        // Ici tu peux ajouter une modal ou rediriger vers une page de détails
    }
    
    function editProgram(id) {
        alert('Modifier le programme ' + id);
        // Ici tu peux ouvrir un formulaire de modification
    }
    
    function deleteProgram(id) {
        if (confirm('Êtes-vous sûr de vouloir supprimer ce programme ?')) {
            // Ici tu peux faire un appel AJAX pour supprimer
            alert('Programme ' + id + ' supprimé');
        }
    }
    
    // Fonctions pour les commandes
    function viewOrder(id) {
        alert('Voir les détails de la commande ' + id);
        // Ici tu peux ajouter une modal ou rediriger vers une page de détails
    }
    
    function editOrder(id) {
        alert('Modifier la commande ' + id);
        // Ici tu peux ouvrir un formulaire de modification
    }
    
    function deleteOrder(id) {
        if (confirm('Êtes-vous sûr de vouloir supprimer cette commande ?')) {
            // Ici tu peux faire un appel AJAX pour supprimer
            alert('Commande ' + id + ' supprimée');
        }
    }
</script>

<!-- Modale pour les formulaires -->
<div id="formModal" class="modal-overlay" style="display:none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle"></h2>
            <button id="closeModal" class="modal-close">&times;</button>
        </div>
        <div class="modal-body" id="modalBody">
            <!-- Le contenu du formulaire sera injecté ici par JavaScript -->
            <div id="userFormContent">
                <form id="userForm" method="POST">
                    <input type="hidden" name="action" id="userFormAction" value="add_user">
                    <input type="hidden" name="user_id" id="userId" value="0">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="userName">Nom complet *</label>
                            <input type="text" id="userName" name="name" value="<?php echo htmlspecialchars($edit_user_data['name'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="userEmail">Email *</label>
                            <input type="email" id="userEmail" name="email" value="<?php echo htmlspecialchars($edit_user_data['email'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="userPassword">Mot de passe <?php echo $edit_user_data ? '(laisser vide si inchangé)' : '*'; ?></label>
                            <input type="password" id="userPassword" name="password" <?php echo $edit_user_data ? '' : 'required'; ?> minlength="8">
                            <?php if ($edit_user_data): ?><small>Laissez vide pour conserver l'ancien mot de passe.</small><?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label for="userRole">Rôle *</label>
                            <select id="userRole" name="role" required>
                                <option value="user" <?php echo (($edit_user_data['role'] ?? '') === 'user') ? 'selected' : ''; ?>>Utilisateur</option>
                                <option value="coach" <?php echo (($edit_user_data['role'] ?? '') === 'coach') ? 'selected' : ''; ?>>Coach</option>
                                <option value="admin" <?php echo (($edit_user_data['role'] ?? '') === 'admin') ? 'selected' : ''; ?>>Administrateur</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="userStatus">Statut *</label>
                            <select id="userStatus" name="status" required>
                                <option value="active" <?php echo (($edit_user_data['status'] ?? '') === 'active') ? 'selected' : ''; ?>>Actif</option>
                                <option value="inactive" <?php echo (($edit_user_data['status'] ?? '') === 'inactive') ? 'selected' : ''; ?>>Inactif</option>
                                <option value="banned" <?php echo (($edit_user_data['status'] ?? '') === 'banned') ? 'selected' : ''; ?>>Banni</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="userPhone">Téléphone</label>
                            <input type="tel" id="userPhone" name="phone" value="<?php echo htmlspecialchars($edit_user_data['phone'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="userBirthDate">Date de naissance</label>
                            <input type="date" id="userBirthDate" name="birth_date" value="<?php echo htmlspecialchars($edit_user_data['birth_date'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="userGender">Genre</label>
                            <select id="userGender" name="gender">
                                <option value="">Sélectionner</option>
                                <option value="male" <?php echo (($edit_user_data['gender'] ?? '') === 'male') ? 'selected' : ''; ?>>Homme</option>
                                <option value="female" <?php echo (($edit_user_data['gender'] ?? '') === 'female') ? 'selected' : ''; ?>>Femme</option>
                                <option value="other" <?php echo (($edit_user_data['gender'] ?? '') === 'other') ? 'selected' : ''; ?>>Autre</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="userHeight">Taille (cm)</label>
                            <input type="number" id="userHeight" name="height" step="0.01" value="<?php echo htmlspecialchars($edit_user_data['height'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="userWeight">Poids (kg)</label>
                            <input type="number" id="userWeight" name="weight" step="0.01" value="<?php echo htmlspecialchars($edit_user_data['weight'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="userFitnessLevel">Niveau de fitness</label>
                            <select id="userFitnessLevel" name="fitness_level">
                                <option value="">Sélectionner</option>
                                <option value="beginner" <?php echo (($edit_user_data['fitness_level'] ?? '') === 'beginner') ? 'selected' : ''; ?>>Débutant</option>
                                <option value="intermediate" <?php echo (($edit_user_data['fitness_level'] ?? '') === 'intermediate') ? 'selected' : ''; ?>>Intermédiaire</option>
                                <option value="advanced" <?php echo (($edit_user_data['fitness_level'] ?? '') === 'advanced') ? 'selected' : ''; ?>>Avancé</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="userGoals">Objectifs</label>
                            <textarea id="userGoals" name="goals" rows="3"><?php echo htmlspecialchars($edit_user_data['goals'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary"><i class='bx bx-save'></i> <span id="userSubmitBtnText">Ajouter l'utilisateur</span></button>
                        <button type="button" class="btn btn-outline" id="cancelUserForm">Annuler</button>
                    </div>
                </form>
            </div>
            <div id="productFormContent" style="display:none;">
                <form id="productForm" method="POST" enctype="multipart/form-data">
                     <input type="hidden" name="action" id="productFormAction" value="add_product">
                     <input type="hidden" name="product_id" id="productId" value="0">
                     <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                     <div class="form-row">
                        <div class="form-group">
                            <label for="productName">Nom du produit *</label>
                            <input type="text" id="productName" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="productPrice">Prix *</label>
                            <input type="number" id="productPrice" name="price" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="productStock">Stock *</label>
                            <input type="number" id="productStock" name="stock" required>
                        </div>
                        <div class="form-group">
                            <label for="productCategory">Catégorie *</label>
                            <input type="text" id="productCategory" name="category" required>
                        </div>
                         <div class="form-group form-group-full">
                            <label for="productDescription">Description</label>
                            <textarea id="productDescription" name="description" rows="4"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="productImage">Image</label>
                            <input type="file" id="productImage" name="image" accept="image/*">
                        </div>
                        <div class="form-group">
                            <label for="productStatus">Statut</label>
                            <select id="productStatus" name="status">
                                <option value="available">Disponible</option>
                                <option value="unavailable">Indisponible</option>
                            </select>
                        </div>
                     </div>
                     <div class="form-actions">
                         <button type="submit" class="btn btn-primary"><i class='bx bx-save'></i> <span id="productSubmitBtnText">Ajouter</span></button>
                         <button type="button" class="btn btn-outline" id="cancelProductForm">Annuler</button>
                     </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// Fonctions pour les statistiques
function getTotalUsers() {
    global $pdo;
    try {
        $bdd = $pdo->query("SELECT COUNT(*) FROM users");
        return $bdd->fetchColumn();
    } catch (Exception $e) {
        return 0;
    }
}

function getTotalPrograms() {
    global $pdo;
    try {
        $bdd = $pdo->query("SELECT COUNT(*) FROM programs");
        return $bdd->fetchColumn();
    } catch (Exception $e) {
        return 0;
    }
}

function getTotalOrders() {
    global $pdo;
    try {
        $bdd = $pdo->query("SELECT COUNT(*) FROM orders");
        return $bdd->fetchColumn();
    } catch (Exception $e) {
        return 0;
    }
}

function getTotalRevenue() {
    global $pdo;
    try {
        $bdd = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE status = 'completed'");
        return $bdd->fetchColumn() ?: 0;
    } catch (Exception $e) {
        return 0;
    }
}

function getRecentActivity() {
    return [
        [
            'icon' => 'bxs-user-plus',
            'description' => 'Nouvel utilisateur inscrit : Jean Dupont',
            'time' => 'Il y a 2 heures'
        ],
        [
            'icon' => 'bxs-shopping-bag',
            'description' => 'Nouvelle commande #1234 reçue',
            'time' => 'Il y a 3 heures'
        ],
        [
            'icon' => 'bxs-dumbbell',
            'description' => 'Programme "Musculation débutant" créé',
            'time' => 'Il y a 5 heures'
        ]
    ];
}

function getSiteSetting($key, $default = '') {
    global $pdo;
    try {
        $bdd = $pdo->prepare("SELECT value FROM settings WHERE setting_key = ?");
        $bdd->execute([$key]);
        return $bdd->fetchColumn() ?: $default;
    } catch (Exception $e) {
        return $default;
    }
}

function getAllPrograms() {
    global $pdo;
    try {
        $bdd = $pdo->query("SELECT p.*, u.name as created_by_name 
                           FROM programs p 
                           LEFT JOIN users u ON p.created_by = u.id 
                           ORDER BY p.created_at DESC");
        return $bdd->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

function getAllUsers() {
    global $pdo;
    try {
        $bdd = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
        return $bdd->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

function getAllOrders() {
    global $pdo;
    try {
        $bdd = $pdo->query("SELECT o.*, u.name as user_name 
                           FROM orders o 
                           LEFT JOIN users u ON o.user_id = u.id 
                           ORDER BY o.created_at DESC");
        return $bdd->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

function getUserById($id) {
    global $pdo;
    try {
        $bdd = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $bdd->execute([$id]);
        return $bdd->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return null;
    }
}

function createUser($data) {
    global $pdo;
    try {
        $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
        $bdd = $pdo->prepare("
            INSERT INTO users (name, email, password, role, status, phone, birth_date, gender, height, weight, fitness_level, goals)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $bdd->execute([
            $data['name'], $data['email'], $password_hash, $data['role'], $data['status'],
            $data['phone'] ?: null, $data['birth_date'] ?: null, $data['gender'] ?: null,
            $data['height'] ?: null, $data['weight'] ?: null, $data['fitness_level'] ?: null, $data['goals'] ?: null
        ]);
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        return false;
    }
}

function updateUser($id, $data) {
    global $pdo;
    try {
        $sql = "UPDATE users SET name = ?, email = ?, role = ?, status = ?, phone = ?, birth_date = ?, gender = ?, height = ?, weight = ?, fitness_level = ?, goals = ?";
        $params = [
            $data['name'], $data['email'], $data['role'], $data['status'],
            $data['phone'] ?: null, $data['birth_date'] ?: null, $data['gender'] ?: null,
            $data['height'] ?: null, $data['weight'] ?: null, $data['fitness_level'] ?: null, $data['goals'] ?: null
        ];

        // Ajouter le mot de passe si fourni
        if (!empty($data['password'])) {
            $sql .= ", password = ?";
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $sql .= " WHERE id = ?";
        $params[] = $id;

        $bdd = $pdo->prepare($sql);
        $bdd->execute($params);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

function deleteUser($id) {
    global $pdo;
    try {
        $bdd = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $bdd->execute([$id]);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function getAllProducts() {
    global $pdo;
    try {
        $bdd = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
        return $bdd->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

function createProduct($data, $file) {
    global $pdo;
    // Logique d'upload d'image ici...
    $image_url = $data['image_url'] ?? null; // Placeholder
    try {
        $bdd = $pdo->prepare("INSERT INTO products (name, description, price, stock, category, status, image_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $bdd->execute([$data['name'], $data['description'], $data['price'], $data['stock'], $data['category'], $data['status'], $image_url]);
        return true;
    } catch(PDOException $e) { return false; }
}

function updateProduct($id, $data, $file) {
    global $pdo;
    // Logique d'upload d'image...
    $image_sql = "";
    if (isset($data['image_url'])) {
        $image_sql = ", image_url = :image_url";
    }
    try {
        $bdd = $pdo->prepare("UPDATE products SET name = :name, description = :description, price = :price, stock = :stock, category = :category, status = :status $image_sql WHERE id = :id");
        $params = array_merge($data, ['id' => $id]);
        $bdd->execute($params);
        return true;
    } catch(PDOException $e) { return false; }
}

function deleteProduct($id) {
    global $pdo;
    try {
        $bdd = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $bdd->execute([$id]);
        return true;
    } catch(Exception $e) { return false; }
}

include_once __DIR__ . '/../includes/footer.php';
?> 