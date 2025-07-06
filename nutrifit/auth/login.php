<?php
$page_title = "Connexion";
$page_description = "Connectez-vous à votre compte NutriFit";

require_once '../includes/header.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification du token CSRF
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = "Erreur de sécurité. Veuillez réessayer.";
    } else {
        $email = sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);

        // Validation
        if (empty($email)) {
            $errors[] = "L'email est requis.";
        }

        if (empty($password)) {
            $errors[] = "Le mot de passe est requis.";
        }

        // Si pas d'erreurs, procéder à la connexion
        if (empty($errors)) {
            try {
                $pdo = getDBConnection();
                
                // Rechercher l'utilisateur
                $bdd = $pdo->prepare("SELECT id, name, email, password, role, status FROM users WHERE email = ?");
                $bdd->execute([$email]);
                $user = $bdd->fetch();
                
                if ($user && password_verify($password, $user['password'])) {
                    // Vérifier le statut de l'utilisateur
                    if ($user['status'] === 'banned') {
                        $errors[] = "Votre compte a été suspendu. Contactez le support.";
                    } elseif ($user['status'] === 'inactive') {
                        $errors[] = "Votre compte est inactif. Vérifiez votre email pour l'activation.";
                    } else {
                        // Connexion réussie
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_name'] = $user['name'];
                        $_SESSION['user_email'] = $user['email'];
                        $_SESSION['user_role'] = $user['role'];
                        
                        // Option "Se souvenir de moi"
                        if ($remember) {
                            $token = bin2hex(random_bytes(32));
                            setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/'); // 30 jours
                            
                            // Stocker le token en base (optionnel)
                            $bdd = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                            $bdd->execute([$token, $user['id']]);
                        }
                        
                        // Message de succès
                        $_SESSION['flash_message'] = "Connexion réussie ! Bienvenue " . $user['name'];
                        $_SESSION['flash_type'] = 'success';
                        
                        // Redirection vers le tableau de bord / profil
                        $redirect_url = APP_URL . 'profile/index.php';
                        ob_end_clean(); // Nettoie le buffer avant la redirection
                        redirect($redirect_url);
                    }
                } else {
                    $errors[] = "Email ou mot de passe incorrect.";
                }
            } catch (PDOException $e) {
                logError("Erreur lors de la connexion", ['email' => $email, 'error' => $e->getMessage()]);
                $errors[] = "Une erreur est survenue lors de la connexion. Veuillez réessayer.";
            }
        }
    }
}
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1>Connexion</h1>
            <p>Entrez vos identifiants pour accéder à votre compte</p>
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
                <label for="email">Email</label>
                <div class="input-group">
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                    <i class='bx bx-envelope'></i>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <div class="input-group">
                    <input type="password" id="password" name="password" required>
                    <i class='bx bxs-lock-alt'></i>
                </div>
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="remember" <?php echo isset($_POST['remember']) ? 'checked' : ''; ?>>
                    <span class="checkmark"></span>
                    Se souvenir de moi
                </label>
            </div>

            <button type="submit" class="btn btn-primary btn-full">Se connecter</button>
        </form>

        <div class="auth-footer">
            <p><a href="<?php echo APP_URL; ?>auth/forgot-password.php">Mot de passe oublié ?</a></p>
            <p>Pas encore de compte ? <a href="<?php echo APP_URL; ?>auth/register.php">S'inscrire</a></p>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 