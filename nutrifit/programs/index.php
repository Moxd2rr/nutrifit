<?php
$page_title = "Programmes d'entraînement";
$page_description = "Découvrez nos programmes d'entraînement personnalisés pour tous niveaux";

require_once '../includes/header.php';

// Récupération des paramètres de filtrage
$difficulty = $_GET['difficulty'] ?? '';
$duration = $_GET['duration'] ?? '';
$search = $_GET['search'] ?? '';

// Construction de la requête
$pdo = getDBConnection();
$where_conditions = ["status = 'active'"];
$params = [];

if (!empty($difficulty)) {
    $where_conditions[] = "difficulty = ?";
    $params[] = $difficulty;
}

if (!empty($duration)) {
    $where_conditions[] = "duration_weeks <= ?";
    $params[] = $duration;
}

if (!empty($search)) {
    $where_conditions[] = "(title LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = implode(" AND ", $where_conditions);

$sql = "SELECT * FROM programs WHERE $where_clause ORDER BY created_at DESC";
$bdd = $pdo->prepare($sql);
$bdd->execute($params);
$programs = $bdd->fetchAll();

// Récupérer les statistiques
$bdd = $pdo->query("SELECT COUNT(*) as total FROM programs WHERE status = 'active'");
$total_programs = $bdd->fetch()['total'];

$bdd = $pdo->query("SELECT COUNT(*) as total FROM users WHERE status = 'active'");
$total_users = $bdd->fetch()['total'];
?>

<div class="programs-container">
    <!-- En-tête des programmes -->
    <div class="programs-header">
        <div class="container">
            <h1>Programmes d'entraînement</h1>
            <p>Des programmes personnalisés pour tous niveaux et tous objectifs</p>
            
            <!-- Statistiques -->
            <div class="programs-stats">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $total_programs; ?>+</div>
                    <div class="stat-label">Programmes disponibles</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo number_format($total_users); ?>+</div>
                    <div class="stat-label">Utilisateurs satisfaits</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">95%</div>
                    <div class="stat-label">Taux de réussite</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="programs-filters">
        <div class="container">
            <form method="GET" class="filters-form">
                <div class="filters-row">
                    <div class="filter-group">
                        <label for="search">Rechercher</label>
                        <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Nom du programme...">
                    </div>
                    
                    <div class="filter-group">
                        <label for="difficulty">Niveau</label>
                        <select id="difficulty" name="difficulty">
                            <option value="">Tous niveaux</option>
                            <option value="beginner" <?php echo $difficulty === 'beginner' ? 'selected' : ''; ?>>Débutant</option>
                            <option value="intermediate" <?php echo $difficulty === 'intermediate' ? 'selected' : ''; ?>>Intermédiaire</option>
                            <option value="advanced" <?php echo $difficulty === 'advanced' ? 'selected' : ''; ?>>Avancé</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="duration">Durée max</label>
                        <select id="duration" name="duration">
                            <option value="">Toutes durées</option>
                            <option value="4" <?php echo $duration === '4' ? 'selected' : ''; ?>>4 semaines</option>
                            <option value="8" <?php echo $duration === '8' ? 'selected' : ''; ?>>8 semaines</option>
                            <option value="12" <?php echo $duration === '12' ? 'selected' : ''; ?>>12 semaines</option>
                            <option value="16" <?php echo $duration === '16' ? 'selected' : ''; ?>>16 semaines</option>
                        </select>
                    </div>
                    
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary">Filtrer</button>
                        <a href="<?php echo APP_URL; ?>programs/" class="btn btn-outline">Réinitialiser</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des programmes -->
    <div class="programs-content">
        <div class="container">
            <div class="programs-results">
                <div class="results-header">
                    <h2><?php echo count($programs); ?> programme(s) trouvé(s)</h2>
                    <?php if (!empty($search) || !empty($difficulty) || !empty($duration)): ?>
                        <div class="active-filters">
                            <?php if (!empty($search)): ?>
                                <span class="filter-tag">Recherche: "<?php echo htmlspecialchars($search); ?>"</span>
                            <?php endif; ?>
                            <?php if (!empty($difficulty)): ?>
                                <span class="filter-tag">Niveau: <?php echo ucfirst($difficulty); ?></span>
                            <?php endif; ?>
                            <?php if (!empty($duration)): ?>
                                <span class="filter-tag">Durée: <?php echo $duration; ?> semaines max</span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (empty($programs)): ?>
                    <div class="no-results">
                        <i class='bx bx-search-alt'></i>
                        <h3>Aucun programme trouvé</h3>
                        <p>Essayez de modifier vos critères de recherche</p>
                        <a href="<?php echo APP_URL; ?>programs/" class="btn btn-primary">Voir tous les programmes</a>
                    </div>
                <?php else: ?>
                    <div class="programs-grid">
                        <?php foreach ($programs as $program): ?>
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
                                <div class="program-overlay">
                                    <div class="program-actions">
                                        <a href="<?php echo APP_URL; ?>programs/view.php?id=<?php echo $program['id']; ?>" 
                                           class="btn btn-primary btn-sm">Voir détails</a>
                                        <button class="btn btn-outline btn-sm enroll-program" 
                                                data-program-id="<?php echo $program['id']; ?>">
                                            <i class='bx bxs-user-plus'></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="program-content">
                                <div class="program-meta">
                                    <span class="difficulty <?php echo $program['difficulty']; ?>">
                                        <?php echo ucfirst($program['difficulty']); ?>
                                    </span>
                                    <span class="duration">
                                        <i class='bx bxs-calendar'></i>
                                        <?php echo $program['duration_weeks']; ?> semaines
                                    </span>
                                </div>
                                <h3 class="program-title">
                                    <a href="<?php echo APP_URL; ?>programs/view.php?id=<?php echo $program['id']; ?>">
                                        <?php echo htmlspecialchars($program['title']); ?>
                                    </a>
                                </h3>
                                <p class="program-description">
                                    <?php echo htmlspecialchars(substr($program['description'], 0, 120)) . '...'; ?>
                                </p>
                                <div class="program-footer">
                                    <div class="program-price">
                                        <span class="price"><?php echo formatPrice($program['price']); ?></span>
                                    </div>
                                    <a href="<?php echo APP_URL; ?>programs/view.php?id=<?php echo $program['id']; ?>" 
                                       class="btn btn-primary">Voir le programme</a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Script pour l'inscription aux programmes -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion de l'inscription aux programmes
    const enrollButtons = document.querySelectorAll('.enroll-program');
    
    enrollButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const programId = this.dataset.programId;
            
            // Animation de chargement
            this.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i>';
            this.disabled = true;
            
            // Requête AJAX pour s'inscrire au programme
            fetch('<?php echo APP_URL; ?>programs/enroll.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    program_id: programId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Programme ajouté avec succès!', 'success');
                    // Rediriger vers la page du programme
                    window.location.href = '<?php echo APP_URL; ?>programs/view.php?id=' + programId;
                } else {
                    showNotification(data.message || 'Erreur lors de l\'inscription', 'error');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                showNotification('Erreur de connexion', 'error');
            })
            .finally(() => {
                // Restaurer le bouton
                this.innerHTML = '<i class="bx bxs-user-plus"></i>';
                this.disabled = false;
            });
        });
    });
});
</script>

<?php require_once '../includes/footer.php'; ?> 