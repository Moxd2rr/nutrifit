<?php
$model = "tiiuae/falcon-7b-instruct";




$programme = "";
$error = "";
$step = isset($_POST['step']) ? (int)$_POST['step'] : 1;

// Fonctions pour garder les valeurs dans le formulaire
function get_value($name) { return isset($_POST[$name]) ? htmlspecialchars($_POST[$name]) : ''; }
function is_checked($name, $value) { return (isset($_POST[$name]) && $_POST[$name] == $value) ? 'checked' : ''; }
function is_selected($name, $value) { return (isset($_POST[$name]) && $_POST[$name] == $value) ? 'selected' : ''; }
function is_array_checked($name, $value) { return (isset($_POST[$name]) && is_array($_POST[$name]) && in_array($value, $_POST[$name])) ? 'checked' : ''; }

// Appel à l'API Hugging Face
function generate_with_hf($prompt, $hf_api_key, $model) {
    $url = "https://api-inference.huggingface.co/models/$model";
    $headers = [
        "Authorization: Bearer $hf_api_key",
        "Content-Type: application/json"
    ];
    $data = [
        "inputs" => $prompt,
        "parameters" => [
            "max_new_tokens" => 400,
            "temperature" => 0.7
        ]
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Timeout de 30 secondes
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // Timeout de connexion de 10 secondes
    
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        return "Erreur de connexion : " . curl_error($ch);
    }
    
    curl_close($ch);
    
    if ($httpCode === 403) {
        return "Erreur 403 - Clé API invalide ou expirée. Veuillez vérifier votre clé API Hugging Face.";
    } elseif ($httpCode === 401) {
        return "Erreur 401 - Clé API manquante ou incorrecte.";
    } elseif ($httpCode === 429) {
        return "Erreur 429 - Limite de requêtes dépassée. Veuillez réessayer plus tard.";
    } elseif ($httpCode !== 200) {
        return "Erreur HTTP : $httpCode - Impossible de contacter l'API Hugging Face";
    }
    
    $response = json_decode($result, true);
    if (isset($response[0]['generated_text'])) {
        return nl2br(htmlspecialchars($response[0]['generated_text']));
    } elseif (isset($response['error'])) {
        return "Erreur Hugging Face : " . htmlspecialchars($response['error']);
    } else {
        return "Réponse inattendue de l'API";
    }
}

// Traitement AJAX pour la génération
if (isset($_POST['ajax_generate']) && $_POST['ajax_generate'] == 'true') {
    header('Content-Type: application/json');
    
    $genre = $_POST['genre'];
    $age = (int)$_POST['age'];
    $poids = (int)$_POST['poids'];
    $taille = (int)$_POST['taille'];
    $objectif = $_POST['objectif'];
    $activite = $_POST['activite'];
    $restrictions = isset($_POST['restrictions']) ? $_POST['restrictions'] : [];
    $dispo = $_POST['dispo'];
    $experience = $_POST['experience'];

    // Construction du prompt pour l'IA
    $restrictions_str = empty($restrictions) ? "Aucune" : implode(", ", $restrictions);
    $prompt = "Voici les informations d'un utilisateur : 
- Genre : $genre
- Âge : $age ans
- Poids : $poids kg
- Taille : $taille cm
- Objectif : $objectif
- Niveau d'activité : $activite
- Restrictions alimentaires : $restrictions_str
- Disponibilité hebdomadaire : $dispo
- Niveau d'expérience : $experience

Génère un programme d'entraînement hebdomadaire (jours, exercices, répétitions) et un plan nutritionnel adapté au Contexte malien, en français, sous forme de liste claire et concise. Fais attention aux restrictions alimentaires.";

    $result = generate_with_hf($prompt, $hf_api_key, $model);
    
    echo json_encode(['success' => true, 'programme' => $result]);
    exit;
}

// Traitement à l'étape 3
if ($_SERVER["REQUEST_METHOD"] == "POST" && $step == 3) {
    $genre = get_value('genre');
    $age = (int)get_value('age');
    $poids = (int)get_value('poids');
    $taille = (int)get_value('taille');
    $objectif = get_value('objectif');
    $activite = get_value('activite');
    $restrictions = isset($_POST['restrictions']) ? $_POST['restrictions'] : [];
    $dispo = get_value('dispo');
    $experience = get_value('experience');

    // Construction du prompt pour l'IA
    $restrictions_str = empty($restrictions) ? "Aucune" : implode(", ", $restrictions);
    $prompt = "Voici les informations d'un utilisateur : 
- Genre : $genre
- Âge : $age ans
- Poids : $poids kg
- Taille : $taille cm
- Objectif : $objectif
- Niveau d'activité : $activite
- Restrictions alimentaires : $restrictions_str
- Disponibilité hebdomadaire : $dispo
- Niveau d'expérience : $experience

Génère un programme d'entraînement hebdomadaire (jours, exercices, répétitions) et un plan nutritionnel adapté, en français, sous forme de liste claire et concise. Fais attention aux restrictions alimentaires.";

    $programme = generate_with_hf($prompt, $hf_api_key, $model);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Générateur </title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .ai-generator-container {
            max-width: 800px;
            margin: 2rem auto;
            background: var(--bg-primary);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }
        
        .ai-generator-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .ai-generator-header h1 {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }
        
        .ai-generator-header p {
            opacity: 0.9;
            font-size: 1.125rem;
        }
        
        .ai-generator-content {
            padding: 2rem;
        }
        
        .form-step {
            display: none;
        }
        
        .form-step.active {
            display: block;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid var(--border-color);
            border-radius: var(--radius-md);
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        
        .radio-group {
            display: flex;
            gap: 1rem;
            margin-top: 0.5rem;
        }
        
        .radio-group label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            font-weight: normal;
        }
        
        .radio-group input[type="radio"] {
            width: auto;
            margin: 0;
        }
        
        .checkbox-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 0.75rem;
            margin-top: 0.5rem;
        }
        
        .checkbox-group label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            font-weight: normal;
            padding: 0.5rem;
            border-radius: var(--radius-sm);
            transition: background 0.3s ease;
        }
        
        .checkbox-group label:hover {
            background: var(--bg-secondary);
        }
        
        .checkbox-group input[type="checkbox"] {
            width: auto;
            margin: 0;
        }
        
        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid var(--border-color);
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 25px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
        }
        
        .btn-outline {
            background: transparent;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
        }
        
        .btn-outline:hover {
            background: var(--primary-color);
            color: white;
        }
        
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }
        
        /* Loading */
        .loading-container {
            display: none;
            text-align: center;
            padding: 3rem 2rem;
        }
        
        .loading-spinner {
            width: 60px;
            height: 60px;
            border: 4px solid var(--border-color);
            border-top: 4px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1.5rem;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .loading-text {
            font-size: 1.125rem;
            color: var(--text-secondary);
            margin-bottom: 1rem;
        }
        
        .loading-dots {
            display: inline-block;
            animation: dots 1.5s infinite;
        }
        
        @keyframes dots {
            0%, 20% { content: ""; }
            40% { content: "."; }
            60% { content: ".."; }
            80%, 100% { content: "..."; }
        }
        
        /* Résultat */
        .result-container {
            display: none;
            padding: 2rem;
            background: var(--bg-secondary);
            border-radius: var(--radius-lg);
            margin-top: 2rem;
        }
        
        .result-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .result-header h2 {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .result-header p {
            color: var(--text-secondary);
        }
        
        .result-content {
            background: var(--bg-primary);
            padding: 2rem;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            line-height: 1.7;
        }
        
        .result-content h3 {
            color: var(--primary-color);
            font-size: 1.25rem;
            font-weight: 600;
            margin: 1.5rem 0 1rem;
            border-bottom: 2px solid var(--primary-light);
            padding-bottom: 0.5rem;
        }
        
        .result-content h3:first-child {
            margin-top: 0;
        }
        
        .result-content ul {
            margin: 1rem 0;
            padding-left: 1.5rem;
        }
        
        .result-content li {
            margin-bottom: 0.5rem;
        }
        
        .result-content strong {
            color: var(--primary-color);
        }
        
        .error-message {
            background: #fee2e2;
            color: #991b1b;
            padding: 1rem;
            border-radius: var(--radius-md);
            border: 1px solid #fecaca;
            margin-top: 1rem;
        }
        
        /* Progress bar */
        .progress-bar {
            width: 100%;
            height: 4px;
            background: var(--border-color);
            border-radius: 2px;
            overflow: hidden;
            margin: 1rem 0;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            width: 0%;
            transition: width 0.3s ease;
        }
        
        .step-indicator {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .step-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--border-color);
            transition: all 0.3s ease;
        }
        
        .step-dot.active {
            background: var(--primary-color);
            transform: scale(1.2);
        }
        
        .step-dot.completed {
            background: var(--success-color);
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php';  ?>

    <div class="ai-generator-container">
        <div class="ai-generator-header">
            <h1> Générateur de Programmes</h1>
            <p>Créez votre programme d'entraînement personnalisé avec l'intelligence artificielle</p>
        </div>
        
        <div class="ai-generator-content">
            <!-- Indicateur de progression -->
            <div class="step-indicator">
                <div class="step-dot <?= $step >= 1 ? 'active' : '' ?> <?= $step > 1 ? 'completed' : '' ?>"></div>
                <div class="step-dot <?= $step >= 2 ? 'active' : '' ?> <?= $step > 2 ? 'completed' : '' ?>"></div>
                <div class="step-dot <?= $step >= 3 ? 'active' : '' ?>"></div>
            </div>
            
            <!-- Formulaire -->
            <form id="aiForm" method="post" action="">
                <!-- Étape 1 -->
                <div class="form-step <?= $step == 1 ? 'active' : '' ?>" id="step1">
                    <h3> Informations personnelles</h3>
                    
                    <div class="form-group">
                        <label>Genre :</label>
                        <div class="radio-group">
                            <label>
                                <input type="radio" name="genre" value="Homme" <?=is_checked('genre','Homme')?> required>
                                Homme
                            </label>
                            <label>
                                <input type="radio" name="genre" value="Femme" <?=is_checked('genre','Femme')?>>
                                Femme
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Âge :</label>
                        <input type="number" name="age" min="10" max="100" value="<?=get_value('age')?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Poids (kg) :</label>
                        <input type="number" name="poids" min="30" max="200" value="<?=get_value('poids')?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Taille (cm) :</label>
                        <input type="number" name="taille" min="120" max="220" value="<?=get_value('taille')?>" required>
                    </div>
                    
                    <div class="form-actions">
                        <div></div>
                        <button type="button" class="btn btn-primary" onclick="nextStep()">
                            <i class="fas fa-arrow-right"></i>
                            Suivant
                        </button>
                    </div>
                </div>
                
                <!-- Étape 2 -->
                <div class="form-step <?= $step == 2 ? 'active' : '' ?>" id="step2">
                    <h3>🎯 Objectifs et préférences</h3>
                    
                    <input type="hidden" name="genre" value="<?=get_value('genre')?>">
                    <input type="hidden" name="age" value="<?=get_value('age')?>">
                    <input type="hidden" name="poids" value="<?=get_value('poids')?>">
                    <input type="hidden" name="taille" value="<?=get_value('taille')?>">
                    
                    <div class="form-group">
                        <label>Objectif principal :</label>
                        <select name="objectif" required>
                            <option value="">Sélectionnez votre objectif</option>
                            <option value="Perte de poids" <?=is_selected('objectif','Perte de poids')?>>Perte de poids</option>
                            <option value="Prise de masse" <?=is_selected('objectif','Prise de masse')?>>Prise de masse</option>
                            <option value="Remise en forme" <?=is_selected('objectif','Remise en forme')?>>Remise en forme</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Niveau d'activité actuel :</label>
                        <select name="activite" required>
                            <option value="">Sélectionnez votre niveau</option>
                            <option value="Sédentaire" <?=is_selected('activite','Sédentaire')?>>Sédentaire</option>
                            <option value="Légèrement actif" <?=is_selected('activite','Légèrement actif')?>>Légèrement actif</option>
                            <option value="Modérément actif" <?=is_selected('activite','Modérément actif')?>>Modérément actif</option>
                            <option value="Très actif" <?=is_selected('activite','Très actif')?>>Très actif</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Restrictions alimentaires :</label>
                        <div class="checkbox-group">
                            <label>
                                <input type="checkbox" name="restrictions[]" value="Végétarien" <?=is_array_checked('restrictions','Végétarien')?>>
                                Végétarien
                            </label>
                            <label>
                                <input type="checkbox" name="restrictions[]" value="Végétalien" <?=is_array_checked('restrictions','Végétalien')?>>
                                Végétalien
                            </label>
                            <label>
                                <input type="checkbox" name="restrictions[]" value="Sans gluten" <?=is_array_checked('restrictions','Sans gluten')?>>
                                Sans gluten
                            </label>
                            <label>
                                <input type="checkbox" name="restrictions[]" value="Sans lactose" <?=is_array_checked('restrictions','Sans lactose')?>>
                                Sans lactose
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Disponibilité hebdomadaire :</label>
                        <select name="dispo" required>
                            <option value="">Sélectionnez votre disponibilité</option>
                            <option value="2-3 jours" <?=is_selected('dispo','2-3 jours')?>>2-3 jours par semaine</option>
                            <option value="3-4 jours" <?=is_selected('dispo','3-4 jours')?>>3-4 jours par semaine</option>
                            <option value="5+ jours" <?=is_selected('dispo','5+ jours')?>>5 jours ou plus</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Niveau d'expérience en fitness :</label>
                        <select name="experience" required>
                            <option value="">Sélectionnez votre niveau</option>
                            <option value="Débutant" <?=is_selected('experience','Débutant')?>>Débutant</option>
                            <option value="Intermédiaire" <?=is_selected('experience','Intermédiaire')?>>Intermédiaire</option>
                            <option value="Avancé" <?=is_selected('experience','Avancé')?>>Avancé</option>
                        </select>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline" onclick="previousStep()">
                            <i class="fas fa-arrow-left"></i>
                            Précédent
                        </button>
                        <button type="button" class="btn btn-primary" onclick="generateProgram()">
                            <i class="fas fa-magic"></i>
                            Générer mon programme IA
                        </button>
                    </div>
                </div>
            </form>
            
            <!-- Loading -->
            <div class="loading-container" id="loadingContainer">
                <div class="loading-spinner"></div>
                <div class="loading-text">
                    L'IA analyse vos informations<span class="loading-dots"></span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" id="progressFill"></div>
                </div>
                <p style="color: var(--text-light); font-size: 0.875rem;">
                    Cela peut prendre quelques secondes
                </p>
            </div>
            
            <!-- Résultat -->
            <div class="result-container" id="resultContainer">
                <div class="result-header">
                    <h2>🎉 Votre Programme Personnalisé</h2>
                    <p>Généré spécialement pour vous par notre IA</p>
                </div>
                <div class="result-content" id="resultContent"></div>
            </div>
            
            <!-- Erreur -->
            <div class="error-message" id="errorMessage" style="display: none;"></div>
        </div>
    </div>

    <?php include '../includes/footer.php'; // Inclusion du pied de page ?>

    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js"></script>
    <script>
        let currentStep = <?= $step ?>;
        
        function showStep(step) {
            document.querySelectorAll('.form-step').forEach(s => s.classList.remove('active'));
            document.getElementById('step' + step).classList.add('active');
            currentStep = step;
            updateStepIndicators();
        }
        
        function nextStep() {
            // Basic validation for step 1 fields before proceeding
            const genre = document.querySelector('input[name="genre"]:checked');
            const age = document.querySelector('input[name="age"]');
            const poids = document.querySelector('input[name="poids"]');
            const taille = document.querySelector('input[name="taille"]');

            if (!genre || !age.value || !poids.value || !taille.value) {
                alert('Veuillez remplir tous les champs personnels avant de continuer.');
                return;
            }
            showStep(currentStep + 1);
        }
        
        function previousStep() {
            if (currentStep > 1) {
                showStep(currentStep - 1);
            }
        }
        
        function generateProgram() {
            // Validation
            const form = document.getElementById('aiForm');
            const formData = new FormData(form);
            
            // Vérifier que tous les champs requis sont remplis
            const requiredFields = ['objectif', 'activite', 'dispo', 'experience'];
            for (let field of requiredFields) {
                if (!formData.get(field)) {
                    alert('Veuillez remplir tous les champs requis');
                    return;
                }
            }
            
            // Afficher le loading
            document.getElementById('loadingContainer').style.display = 'block';
            document.getElementById('resultContainer').style.display = 'none';
            document.getElementById('errorMessage').style.display = 'none';
            
            // Animation de la barre de progression
            let progress = 0;
            const progressFill = document.getElementById('progressFill');
            const progressInterval = setInterval(() => {
                progress += Math.random() * 15;
                if (progress > 90) progress = 90;
                progressFill.style.width = progress + '%';
            }, 500);
            
            // Préparer les données
            formData.append('ajax_generate', 'true');
            
            // Appel AJAX
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                clearInterval(progressInterval);
                progressFill.style.width = '100%';
                
                setTimeout(() => {
                    document.getElementById('loadingContainer').style.display = 'none';
                    
                    if (data.success) {
                        document.getElementById('resultContent').innerHTML = data.programme;
                        document.getElementById('resultContainer').style.display = 'block';
                        
                        // Scroll vers le résultat
                        document.getElementById('resultContainer').scrollIntoView({
                            behavior: 'smooth'
                        });
                    } else {
                        document.getElementById('errorMessage').textContent = data.programme || 'Une erreur est survenue';
                        document.getElementById('errorMessage').style.display = 'block';
                    }
                }, 500);
            })
            .catch(error => {
                clearInterval(progressInterval);
                document.getElementById('loadingContainer').style.display = 'none';
                document.getElementById('errorMessage').textContent = 'Erreur de connexion : ' + error.message;
                document.getElementById('errorMessage').style.display = 'block';
            });
        }
        
        function updateStepIndicators() {
            const dots = document.querySelectorAll('.step-dot');
            dots.forEach((dot, index) => {
                if (index + 1 === currentStep) {
                    dot.classList.add('active');
                    dot.classList.remove('completed');
                } else if (index + 1 < currentStep) {
                    dot.classList.remove('active');
                    dot.classList.add('completed');
                } else {
                    dot.classList.remove('active', 'completed');
                }
            });
        }

        // Initial call to set the correct step indicator on page load
        updateStepIndicators();
    </script>
</body>
</html>
