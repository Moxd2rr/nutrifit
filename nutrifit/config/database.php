<?php
/**
 * Configuration de la base de données NutriFit
 * Fichier centralisé pour toutes les connexions
 */

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'nutrifit_db');
define('DB_USER', 'root');
define('DB_PASS', ''); // XAMPP par défaut (pas de mot de passe)

// Configuration de l'application
define('APP_NAME', 'NutriFit');
define('APP_URL', 'http://localhost/sites/database/nutrifit/');
define('APP_VERSION', '1.0.0');

// Configuration des sessions
define('SESSION_NAME', 'nutrifit_session');
define('SESSION_LIFETIME', 3600); // 1 heure

// Démarrage sécurisé de la session (si non déjà démarrée)
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path' => '/',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// Configuration des uploads
define('UPLOAD_PATH', '../assets/images/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Configuration des emails
define('SMTP_HOST', 'localhost');
define('SMTP_PORT', 587);
define('SMTP_USER', '');
define('SMTP_PASS', '');

// Configuration de sécurité
define('HASH_COST', 12); // Pour password_hash()
define('JWT_SECRET', 'nutrifit_secret_key_2025');

// Fonction de connexion à la base de données
function getDBConnection() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        error_log("Erreur de connexion à la base de données: " . $e->getMessage());
        die("Erreur de connexion à la base de données. Veuillez réessayer plus tard.");
    }
}

// Initialisation de la connexion PDO
$pdo = getDBConnection();

// Fonction pour sécuriser les entrées
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Fonction pour vérifier si l'utilisateur est connecté
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Fonction pour vérifier le rôle de l'utilisateur
function hasRole($role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

// Fonction pour rediriger
function redirect($url) {
    // Si un tampon de sortie est actif, on le vide pour éviter "headers already sent"
    if (ob_get_level()) {
        ob_end_clean();
    }
    header("Location: " . $url);
    exit();
}

// Fonction pour générer un token CSRF
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Fonction pour vérifier le token CSRF
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Fonction pour logger les erreurs
function logError($message, $context = []) {
    $logFile = __DIR__ . '/../logs/error.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message " . json_encode($context) . PHP_EOL;
    error_log($logMessage, 3, $logFile);
}

// Fonction pour formater les prix
function formatPrice($price) {
    return number_format($price, 2, ',', ' ') . ' €';
}

// Fonction pour formater les dates
function formatDate($date, $format = 'd/m/Y') {
    return date($format, strtotime($date));
}

// Fonction pour récupérer l'URL de l'avatar
function getAvatarUrl($user_id) {
    $avatar_path = UPLOAD_PATH . 'avatars/' . $user_id . '.png';
    if (file_exists(__DIR__ . '/../' . $avatar_path)) {
        return APP_URL . 'assets/images/avatars/' . $user_id . '.png';
    }
    return APP_URL . 'assets/images/avatars/default.png';
}

// Configuration des timezones
date_default_timezone_set('Europe/Paris');

// Démarrage de la session si pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}
?> 