<?php
/**
 * Fonctions métiers réutilisables pour l'administration et les API AJAX
 * Ce fichier est chargé par :
 *   • admin/ajax/users.php
 *   • (potentiellement) d'autres scripts back-office
 * Il dépend de la connexion PDO instanciée dans config/database.php
 */
require_once __DIR__ . '/../config/database.php';

/* ------------------------------------------------------------------
   DASHBOARD
------------------------------------------------------------------*/
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
    // Exemple statique – à adapter si tu veux récupérer depuis la BDD
    return [
        [
            'icon' => 'bxs-user-plus',
            'description' => 'Nouvel utilisateur inscrit',
            'time' => 'Il y a 2 heures'
        ],
        [
            'icon' => 'bxs-shopping-bag',
            'description' => 'Nouvelle commande reçue',
            'time' => 'Il y a 3 heures'
        ],
        [
            'icon' => 'bxs-dumbbell',
            'description' => 'Nouveau programme ajouté',
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

/* ------------------------------------------------------------------
   PROGRAMMES / COMMANDES / UTILISATEURS
------------------------------------------------------------------*/
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
        $bdd = $pdo->prepare("INSERT INTO users (name, email, password, role, status, phone, birth_date, gender, height, weight, fitness_level, goals)
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
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

/* ------------------------------------------------------------------
   PRODUITS
------------------------------------------------------------------*/
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
    // TODO: gérer l'upload d'image
    $image_url = $data['image_url'] ?? null;
    try {
        $bdd = $pdo->prepare("INSERT INTO products (name, description, price, stock, category, status, image_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $bdd->execute([$data['name'], $data['description'], $data['price'], $data['stock'], $data['category'], $data['status'], $image_url]);
        return true;
    } catch(PDOException $e) {
        return false;
    }
}

function updateProduct($id, $data, $file) {
    global $pdo;
    $image_sql = '';
    if (isset($data['image_url'])) {
        $image_sql = ', image_url = :image_url';
    }
    try {
        $bdd = $pdo->prepare("UPDATE products SET name = :name, description = :description, price = :price, stock = :stock, category = :category, status = :status $image_sql WHERE id = :id");
        $params = array_merge($data, ['id' => $id]);
        $bdd->execute($params);
        return true;
    } catch(PDOException $e) {
        return false;
    }
}

function deleteProduct($id) {
    global $pdo;
    try {
        $bdd = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $bdd->execute([$id]);
        return true;
    } catch(Exception $e) {
        return false;
    }
}

// Fin du fichier
