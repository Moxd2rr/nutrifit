<?php
require_once '../config/database.php';

// Détruire la session
session_destroy();

// Supprimer le cookie "Se souvenir de moi"
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
    
    // Supprimer le token de la base de données
    try {
        $pdo = getDBConnection();
        $bdd = $pdo->prepare("UPDATE users SET remember_token = NULL WHERE remember_token = ?");
        $bdd->execute([$_COOKIE['remember_token']]);
    } catch (PDOException $e) {
        // Ignorer les erreurs lors de la déconnexion
    }
}

// Redirection vers la page d'accueil avec message
$_SESSION['flash_message'] = "Vous avez été déconnecté avec succès.";
$_SESSION['flash_type'] = 'info';

redirect(APP_URL);
?> 