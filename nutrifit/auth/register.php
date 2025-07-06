<?php
$page_title = "Inscription";
$page_description = "Créez votre compte NutriFit et commencez votre transformation";

require_once '../includes/header.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification du token CSRF
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = "Erreur de sécurité. Veuillez réessayer.";
    } else {
        // Récupération et validation des données
        $name = sanitizeInput($_POST['name'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $phone = sanitizeInput($_POST['phone'] ?? '');
        $birth_date = $_POST['birth_date'] ?? '';
        $fitness_level = $_POST['fitness_level'] ?? 'beginner';
        $goals = sanitizeInput($_POST['goals'] ?? '');

        // Validation
        if (empty($name)) {
            $errors[] = "Le nom est requis.";
        } elseif (strlen($name) > 100) {
            $errors[] = "Le nom est trop long (maximum 100 caractères).";
        }

        if (empty($email)) {
            $errors[] = "L'email est requis.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "L'email n'est pas valide.";
        }

        if (empty($password)) {
            $errors[] = "Le mot de passe est requis.";
        } elseif (strlen($password) < 8) {
            $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
        }

        if ($password !== $confirm_password) {
            $errors[] = "Les mots de passe ne correspondent pas.";
        }

        if (!empty($birth_date)) {
            $birth_timestamp = strtotime($birth_date);
            if ($birth_timestamp === false || $birth_timestamp > time()) {
                $errors[] = "La date de naissance n'est pas valide.";
            }
        }

        // Si pas d'erreurs, procéder à l'inscription
        if (empty($errors)) {
            try {
                $pdo = getDBConnection();
                
                // Vérifier si l'email existe déjà
                $bdd = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $bdd->execute([$email]);
                
                if ($bdd->rowCount() > 0) {
                    $errors[] = "Cette adresse email est déjà utilisée.";
                } else {
                    // Hashage du mot de passe
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Insertion de l'utilisateur
                    $bdd = $pdo->prepare("
                        INSERT INTO users (name, email, password, phone, birth_date, fitness_level, goals, role, status) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, 'user', 'active')
                    ");
                    
                    $bdd->execute([
                        $name, 
                        $email, 
                        $password_hash, 
                        $phone, 
                        $birth_date ?: null, 
                        $fitness_level, 
                        $goals
                    ]);
                    
                    $user_id = $pdo->lastInsertId();
                    
                    // Créer la session
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_email'] = $email;
                    $_SESSION['user_role'] = 'user';
                    
                    // Message de succès
                    $_SESSION['flash_message'] = "Votre compte a été créé avec succès ! Bienvenue sur NutriFit.";
                    $_SESSION['flash_type'] = 'success';
                    
                    // Redirection vers le profil
                    redirect(APP_URL . 'auth/register.php');
                }
            } catch (PDOException $e) {
                logError("Erreur lors de l'inscription", ['email' => $email, 'error' => $e->getMessage()]);
                $errors[] = "Une erreur est survenue lors de l'inscription. Veuillez réessayer.";
            }
        }
    }
}
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1>Créer un compte</h1>
            <p>Inscrivez-vous pour accéder à tous nos services</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" class="auth-form">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="form-group">
                <label for="name">Nom complet *</label>
                <div class="input-group">
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                    <i class='bx bxs-user'></i>
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email *</label>
                <div class="input-group">
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                    <i class='bx bx-envelope'></i>
                </div>
            </div>

            <div class="form-group">
                <label for="phone">Téléphone</label>
                <div class="input-group">
                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                    <i class='bx bxs-phone'></i>
                </div>
            </div>

            <div class="form-group">
                <label for="birth_date">Date de naissance</label>
                <div class="input-group">
                    <input type="date" id="birth_date" name="birth_date" value="<?php echo htmlspecialchars($_POST['birth_date'] ?? ''); ?>">
                    <i class='bx bxs-calendar'></i>
                </div>
            </div>

            <div class="form-group">
                <label for="fitness_level">Niveau de fitness</label>
                <div class="input-group">
                    <select id="fitness_level" name="fitness_level">
                        <option value="beginner" <?php echo ($_POST['fitness_level'] ?? '') === 'beginner' ? 'selected' : ''; ?>>Débutant</option>
                        <option value="intermediate" <?php echo ($_POST['fitness_level'] ?? '') === 'intermediate' ? 'selected' : ''; ?>>Intermédiaire</option>
                        <option value="advanced" <?php echo ($_POST['fitness_level'] ?? '') === 'advanced' ? 'selected' : ''; ?>>Avancé</option>
                    </select>
                    <i class='bx bxs-dumbbell'></i>
                </div>
            </div>

            <div class="form-group">
                <label for="goals">Vos objectifs</label>
                <div class="input-group">
                    <textarea id="goals" name="goals" rows="3" placeholder="Décrivez vos objectifs fitness et nutrition..."><?php echo htmlspecialchars($_POST['goals'] ?? ''); ?></textarea>
                    <i class='bx bxs-target-lock'></i>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe *</label>
                <div class="input-group">
                    <input type="password" id="password" name="password" required>
                    <i class='bx bxs-lock-alt'></i>
                </div>
                <small>Minimum 8 caractères</small>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirmer le mot de passe *</label>
                <div class="input-group">
                    <input type="password" id="confirm_password" name="confirm_password" required>
                    <i class='bx bxs-lock-alt'></i>
                </div>
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="terms" required>
                    <span class="checkmark"></span>
                    J'accepte les <a href="<?php echo APP_URL; ?>terms/" target="_blank">conditions d'utilisation</a> et la <a href="<?php echo APP_URL; ?>privacy/" target="_blank">politique de confidentialité</a>
                </label>
            </div>

            <button type="submit" class="btn btn-primary btn-full">Créer mon compte</button>
        </form>

        <div class="auth-footer">
            <p>Déjà un compte ? <a href="<?php echo APP_URL; ?>auth/login.php">Se connecter</a></p>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 