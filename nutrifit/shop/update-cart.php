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
$quantity = (int)($_POST['quantity'] ?? 1);

// Validation
if ($product_id <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Données invalides']);
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Vérifier que le produit existe et est disponible
    $bdd = $pdo->prepare("SELECT id, name, price, stock FROM products WHERE id = ? AND status = 'available'");
    $bdd->execute([$product_id]);
    $product = $bdd->fetch();
    
    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Produit non trouvé ou indisponible']);
        exit;
    }
    
    // Vérifier le stock
    if ($product['stock'] < $quantity) {
        echo json_encode(['success' => false, 'message' => 'Stock insuffisant']);
        exit;
    }
    
    // Mettre à jour le panier
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    $_SESSION['cart'][$product_id] = $quantity;
    
    // Calculer le total du panier
    $cart_total = 0;
    $cart_count = 0;
    
    foreach ($_SESSION['cart'] as $pid => $qty) {
        $bdd = $pdo->prepare("SELECT price FROM products WHERE id = ?");
        $bdd->execute([$pid]);
        $prod = $bdd->fetch();
        if ($prod) {
            $cart_total += $prod['price'] * $qty;
            $cart_count += $qty;
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Quantité mise à jour',
        'cart_count' => $cart_count,
        'cart_total' => $cart_total,
        'quantity' => $quantity
    ]);
    
} catch (PDOException $e) {
    logError("Erreur lors de la mise à jour du panier", [
        'product_id' => $product_id,
        'quantity' => $quantity,
        'error' => $e->getMessage()
    ]);
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
}
?> 