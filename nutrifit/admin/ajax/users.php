<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
header('Content-Type: application/json');

if (!isLoggedIn() || !hasRole('admin')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Accès refusé']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'list':
        $users = getAllUsers();
        echo json_encode(['success' => true, 'users' => $users]);
        break;
    case 'get':
        $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
        if ($id) {
            $user = getUserById($id);
            if ($user) {
                echo json_encode(['success' => true, 'user' => $user]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Utilisateur non trouvé']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'ID manquant']);
        }
        break;
    case 'add':
        $data = [
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
        if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
            echo json_encode(['success' => false, 'error' => 'Nom, email et mot de passe requis']);
            break;
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'error' => 'Email invalide']);
            break;
        }
        if (strlen($data['password']) < 8) {
            echo json_encode(['success' => false, 'error' => 'Mot de passe trop court']);
            break;
        }
        $pdo = getDBConnection();
        $bdd = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $bdd->execute([$data['email']]);
        if ($bdd->rowCount() > 0) {
            echo json_encode(['success' => false, 'error' => 'Email déjà utilisé']);
            break;
        }
        if (createUser($data)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Erreur lors de l\'ajout']);
        }
        break;
    case 'edit':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'ID manquant']);
            break;
        }
        $data = [
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
        if (empty($data['name']) || empty($data['email'])) {
            echo json_encode(['success' => false, 'error' => 'Nom et email requis']);
            break;
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'error' => 'Email invalide']);
            break;
        }
        if (!empty($data['password']) && strlen($data['password']) < 8) {
            echo json_encode(['success' => false, 'error' => 'Mot de passe trop court']);
            break;
        }
        $pdo = getDBConnection();
        $bdd = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
        $bdd->execute([$data['email'], $id]);
        if ($bdd->rowCount() > 0) {
            echo json_encode(['success' => false, 'error' => 'Email déjà utilisé par un autre utilisateur']);
            break;
        }
        if (updateUser($id, $data)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Erreur lors de la mise à jour']);
        }
        break;
    case 'delete':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'ID manquant']);
            break;
        }
        if (deleteUser($id)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Erreur lors de la suppression']);
        }
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Action inconnue']);
        break;
} 