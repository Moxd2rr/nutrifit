<?php
require_once '../config/database.php';

header('Content-Type: application/json');

// Vérification de la méthode
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

// Vérification du token CSRF
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Erreur de sécurité']);
    exit;
}

// Récupération des données
$product_id = (int)($_POST['product_id'] ?? 0);

// Validation
if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Produit invalide']);
    exit;
}

try {
    // Supprimer le produit du panier
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
    }
    
    // Calculer le nouveau total du panier
    $cart_total = 0;
    $cart_count = 0;
    
    if (!empty($_SESSION['cart'])) {
        $pdo = getDBConnection();
        
        foreach ($_SESSION['cart'] as $pid => $qty) {
            $bdd = $pdo->prepare("SELECT price FROM products WHERE id = ?");
            $bdd->execute([$pid]);
            $prod = $bdd->fetch();
            if ($prod) {
                $cart_total += $prod['price'] * $qty;
                $cart_count += $qty;
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Produit supprimé du panier',
        'cart_count' => $cart_count,
        'cart_total' => $cart_total
    ]);
    
} catch (PDOException $e) {
    logError("Erreur lors de la suppression du panier", [
        'product_id' => $product_id,
        'error' => $e->getMessage()
    ]);
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression']);
}
?> 