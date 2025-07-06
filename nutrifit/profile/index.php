<?php
require_once __DIR__ . '/../config/database.php';
// require_once __DIR__ . '/../includes/functions.php'; // Assuming functions.php contains isLoggedIn() and other helpers

// Rediriger si l'utilisateur n'est pas connecté
if (!isLoggedIn()) {
    header('Location: ' . APP_URL . 'auth/login.php');
    exit;
}

$page_title = "Mon Profil";
$page_description = "Gérez votre profil et suivez votre évolution fitness.";

// Données utilisateur (exemple, à remplacer par des données réelles de la base de données)
$user_name = $_SESSION['user_name'] ?? 'Utilisateur';
$user_email = $_SESSION['user_email'] ?? 'utilisateur@exemple.com';
$user_registration_date = $_SESSION['user_registration_date'] ?? '2023-01-01';

// Données de suivi d'évolution (exemples, à remplacer par des données réelles de la base de données)
$evolution_data = [
    ['date' => '2023-01-01', 'weight' => 75, 'bmi' => 24.5, 'progression' => '+2% muscle'],
    ['date' => '2023-02-01', 'weight' => 74, 'bmi' => 24.2, 'progression' => '+1% muscle'],
    ['date' => '2023-03-01', 'weight' => 73, 'bmi' => 23.9, 'progression' => '-1% graisse'],
    ['date' => '2023-04-01', 'weight' => 72, 'bmi' => 23.5, 'progression' => '+0.5% muscle'],
];

?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="profile-container container">
    <div class="profile-header">
        <h1>Bienvenue, <?php echo htmlspecialchars($user_name); ?></h1>
        <p>Gérez vos informations personnelles et suivez votre progression.</p>
    </div>

    <div class="profile-tabs">
        <button class="tab-btn active" onclick="showProfileTab('info')">Informations Personnelles</button>
        <button class="tab-btn" onclick="showProfileTab('evolution')">Suivi d'Évolution</button>
        <button class="tab-btn" onclick="showProfileTab('settings')">Paramètres du Compte</button>
    </div>

    <div class="profile-content">
        <!-- Informations Personnelles Tab -->
        <div id="info" class="tab-content active">
            <div class="profile-card">
                <h2>Mes Informations</h2>
                <div class="info-group">
                    <label>Nom d'utilisateur :</label>
                    <span><?php echo htmlspecialchars($user_name); ?></span>
                </div>
                <div class="info-group">
                    <label>Email :</label>
                    <span><?php echo htmlspecialchars($user_email); ?></span>
                </div>
                <div class="info-group">
                    <label>Membre depuis :</label>
                    <span><?php echo htmlspecialchars($user_registration_date); ?></span>
                </div>
                <button class="btn btn-primary mt-4">Modifier le profil</button>
            </div>
        </div>

        <!-- Suivi d'Évolution Tab -->
        <div id="evolution" class="tab-content">
            <div class="profile-card">
                <h2>Suivi de votre Évolution</h2>
                <p class="mb-4">Visualisez vos progrès au fil du temps.</p>

                <?php if (!empty($evolution_data)): ?>
                    <div class="evolution-chart" style="height: 300px; width: 100%;">
                        <canvas id="evolutionChart"></canvas>
                    </div>
                    <h3 class="mt-5 mb-3">Historique Détaillé</h3>
                    <div class="admin-table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Poids (kg)</th>
                                    <th>IMC</th>
                                    <th>Progression</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($evolution_data as $data): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($data['date']); ?></td>
                                        <td><?php echo htmlspecialchars($data['weight']); ?></td>
                                        <td><?php echo htmlspecialchars($data['bmi']); ?></td>
                                        <td><?php echo htmlspecialchars($data['progression']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p>Aucune donnée d'évolution disponible pour le moment. Commencez à enregistrer vos progrès !</p>
                    <button class="btn btn-primary mt-4">Ajouter une nouvelle donnée</button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Paramètres du Compte Tab -->
        <div id="settings" class="tab-content">
            <div class="profile-card">
                <h2>Paramètres du Compte</h2>
                <form class="admin-form">
                    <div class="form-group">
                        <label for="new_email">Changer d'Email :</label>
                        <input type="email" id="new_email" name="new_email" value="<?php echo htmlspecialchars($user_email); ?>">
                    </div>
                    <div class="form-group">
                        <label for="old_password">Ancien Mot de Passe :</label>
                        <input type="password" id="old_password" name="old_password">
                    </div>
                    <div class="form-group">
                        <label for="new_password">Nouveau Mot de Passe :</label>
                        <input type="password" id="new_password" name="new_password">
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirmer Nouveau Mot de Passe :</label>
                        <input type="password" id="confirm_password" name="confirm_password">
                    </div>
                    <button type="submit" class="btn btn-primary mt-4">Sauvegarder les paramètres</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<!-- Chart.js pour le graphique d'évolution -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function showProfileTab(tabId) {
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.remove('active');
        });
        document.getElementById(tabId).classList.add('active');

        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        document.querySelector(`.tab-btn[onclick="showProfileTab('${tabId}')"]`).classList.add('active');
        
        // Render chart only when evolution tab is active
        if (tabId === 'evolution') {
            renderEvolutionChart();
        }
    }

    function renderEvolutionChart() {
        const ctx = document.getElementById('evolutionChart');
        if (ctx) {
            const evolutionData = <?php echo json_encode($evolution_data); ?>;
            const dates = evolutionData.map(data => data.date);
            const weights = evolutionData.map(data => data.weight);
            const bmis = evolutionData.map(data => data.bmi);

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: dates,
                    datasets: [
                        {
                            label: 'Poids (kg)',
                            data: weights,
                            borderColor: 'rgb(16, 185, 129)', // primary-color
                            tension: 0.1,
                            fill: false
                        },
                        {
                            label: 'IMC',
                            data: bmis,
                            borderColor: 'rgb(5, 150, 105)', // primary-dark
                            tension: 0.1,
                            fill: false
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: false,
                            title: {
                                display: true,
                                text: 'Valeur'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Date'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    }
                }
            });
        }
    }

    // Initial load: show personal info tab and render chart if it's the active tab
    document.addEventListener('DOMContentLoaded', () => {
        showProfileTab('info'); 
    });
</script>

<style>
    .profile-container {
        padding-top: 2rem;
        padding-bottom: 2rem;
    }

    .profile-header {
        text-align: center;
        margin-bottom: 2rem;
    }

    .profile-header h1 {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }

    .profile-header p {
        color: var(--text-secondary);
        font-size: 1.125rem;
    }

    .profile-tabs {
        display: flex;
        justify-content: center;
        margin-bottom: 2rem;
        border-bottom: 1px solid var(--border-color);
    }

    .profile-tabs .tab-btn {
        background: none;
        border: none;
        padding: 1rem 1.5rem;
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-secondary);
        cursor: pointer;
        border-bottom: 3px solid transparent;
        transition: all 0.3s ease;
    }

    .profile-tabs .tab-btn:hover {
        color: var(--primary-color);
    }

    .profile-tabs .tab-btn.active {
        color: var(--primary-color);
        border-bottom-color: var(--primary-color);
    }

    .profile-content .tab-content {
        display: none;
        animation: fadeIn 0.5s ease-out;
    }

    .profile-content .tab-content.active {
        display: block;
    }

    .profile-card {
        background: var(--bg-primary);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md);
        padding: 2rem;
        margin-bottom: 2rem;
    }

    .profile-card h2 {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 1.5rem;
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 1rem;
    }

    .info-group {
        display: flex;
        flex-direction: column;
        margin-bottom: 1rem;
    }

    .info-group label {
        font-weight: 600;
        color: var(--text-secondary);
        margin-bottom: 0.25rem;
        font-size: 0.9rem;
        text-transform: uppercase;
    }

    .info-group span {
        font-size: 1.1rem;
        color: var(--text-primary);
    }

    .evolution-chart {
        margin-top: 2rem;
        background: var(--bg-secondary);
        padding: 1.5rem;
        border-radius: var(--radius-md);
        border: 1px solid var(--border-light);
    }

    /* Override admin-table styles for profile page if needed, or ensure compatibility */
    .admin-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1.5rem;
    }

    .admin-table th,
    .admin-table td {
        padding: 1rem;
        border: 1px solid var(--border-color);
        text-align: left;
    }

    .admin-table th {
        background: var(--bg-secondary);
        font-weight: 600;
        color: var(--text-primary);
    }

    .admin-table tr:nth-child(even) {
        background: var(--bg-secondary);
    }

    .admin-form .form-group {
        margin-bottom: 1.5rem;
    }

    @media (max-width: 768px) {
        .profile-tabs {
            flex-direction: column;
        }

        .profile-tabs .tab-btn {
            width: 100%;
            border-bottom: 1px solid var(--border-color);
        }

        .profile-tabs .tab-btn:last-child {
            border-bottom: none;
        }

        .profile-card {
            padding: 1.5rem;
        }

        .profile-card h2 {
            font-size: 1.5rem;
        }

        .admin-table {
            font-size: 0.9rem;
        }
    }
</style> 