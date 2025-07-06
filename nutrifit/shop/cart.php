<?php
$page_title = "Mon Panier";
$page_description = "Gérez votre panier d'achat";

require_once '../includes/header.php';

$cart = $_SESSION['cart'] ?? [];
$cart_items = [];
$cart_total = 0;

if (!empty($cart)) {
    $pdo = getDBConnection();
    
    // Récupérer les détails des produits dans le panier
    $product_ids = implode(',', array_keys($cart));
    $bdd = $pdo->prepare("SELECT id, name, price, stock, image_url FROM products WHERE id IN ($product_ids)");
    $bdd->execute();
    $products = $bdd->fetchAll();
    
    // Organiser les données du panier
    foreach ($products as $product) {
        $quantity = $cart[$product['id']];
        $subtotal = $product['price'] * $quantity;
        $cart_total += $subtotal;
        
        $cart_items[] = [
            'product' => $product,
            'quantity' => $quantity,
            'subtotal' => $subtotal
        ];
    }
}
?>

<div class="cart-container">
    <div class="container">
        <div class="cart-header">
            <h1>Mon Panier</h1>
            <p>Gérez vos articles et passez votre commande</p>
        </div>

        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
                <i class='bx bxs-cart'></i>
                <h2>Votre panier est vide</h2>
                <p>Découvrez nos produits et commencez vos achats</p>
                <a href="<?php echo APP_URL; ?>shop/" class="btn btn-primary btn-large">
                    Continuer mes achats
                </a>
            </div>
        <?php else: ?>
            <div class="cart-content">
                <div class="cart-items">
                    <div class="cart-items-header">
                        <h3>Articles (<?php echo count($cart_items); ?>)</h3>
                    </div>
                    
                    <?php foreach ($cart_items as $item): ?>
                    <div class="cart-item" data-product-id="<?php echo $item['product']['id']; ?>">
                        <div class="item-image">
                            <img src="<?php echo APP_URL; ?>assets/images/products/<?php echo $item['product']['id']; ?>.jpg" 
                                 alt="<?php echo htmlspecialchars($item['product']['name']); ?>"
                                 onerror="this.src='<?php echo APP_URL; ?>assets/images/products/default.jpg'">
                        </div>
                        
                        <div class="item-details">
                            <h4 class="item-name">
                                <a href="<?php echo APP_URL; ?>shop/product.php?id=<?php echo $item['product']['id']; ?>">
                                    <?php echo htmlspecialchars($item['product']['name']); ?>
                                </a>
                            </h4>
                            <div class="item-price">
                                <span class="unit-price"><?php echo formatPrice($item['product']['price']); ?></span>
                            </div>
                        </div>
                        
                        <div class="item-quantity">
                            <div class="quantity-controls">
                                <button type="button" class="qty-btn" data-action="decrease" 
                                        data-product-id="<?php echo $item['product']['id']; ?>">-</button>
                                <input type="number" class="qty-input" value="<?php echo $item['quantity']; ?>" 
                                       min="1" max="<?php echo $item['product']['stock']; ?>"
                                       data-product-id="<?php echo $item['product']['id']; ?>">
                                <button type="button" class="qty-btn" data-action="increase" 
                                        data-product-id="<?php echo $item['product']['id']; ?>">+</button>
                            </div>
                        </div>
                        
                        <div class="item-subtotal">
                            <span class="subtotal"><?php echo formatPrice($item['subtotal']); ?></span>
                        </div>
                        
                        <div class="item-actions">
                            <button class="btn btn-outline btn-sm remove-item" 
                                    data-product-id="<?php echo $item['product']['id']; ?>">
                                <i class='bx bxs-trash'></i>
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="cart-summary">
                    <div class="summary-card">
                        <h3>Résumé de la commande</h3>
                        
                        <div class="summary-items">
                            <div class="summary-item">
                                <span>Sous-total</span>
                                <span><?php echo formatPrice($cart_total); ?></span>
                            </div>
                            <div class="summary-item">
                                <span>Livraison</span>
                                <span><?php echo $cart_total >= 50 ? 'Gratuite' : '5,00 €'; ?></span>
                            </div>
                            <?php if ($cart_total < 50): ?>
                            <div class="summary-item free-shipping">
                                <span>Plus que <?php echo formatPrice(50 - $cart_total); ?> pour la livraison gratuite</span>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="summary-total">
                            <span>Total</span>
                            <span><?php echo formatPrice($cart_total + ($cart_total >= 50 ? 0 : 5)); ?></span>
                        </div>
                        
                        <div class="summary-actions">
                            <a href="<?php echo APP_URL; ?>shop/checkout.php" class="btn btn-primary btn-full">
                                Passer la commande
                            </a>
                            <a href="<?php echo APP_URL; ?>shop/" class="btn btn-outline btn-full">
                                Continuer mes achats
                            </a>
                        </div>
                        
                        <div class="summary-features">
                            <div class="feature">
                                <i class='bx bxs-shield-check'></i>
                                <span>Paiement sécurisé</span>
                            </div>
                            <div class="feature">
                                <i class='bx bxs-truck'></i>
                                <span>Livraison rapide</span>
                            </div>
                            <div class="feature">
                                <i class='bx bxs-undo'></i>
                                <span>Retour facile</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Script pour la gestion du panier -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des boutons de quantité
    const qtyBtns = document.querySelectorAll('.qty-btn');
    
    qtyBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const action = this.dataset.action;
            const productId = this.dataset.productId;
            const qtyInput = document.querySelector(`.qty-input[data-product-id="${productId}"]`);
            let currentValue = parseInt(qtyInput.value);
            const maxValue = parseInt(qtyInput.max);
            
            if (action === 'increase' && currentValue < maxValue) {
                updateQuantity(productId, currentValue + 1);
            } else if (action === 'decrease' && currentValue > 1) {
                updateQuantity(productId, currentValue - 1);
            }
        });
    });
    
    // Gestion des inputs de quantité
    const qtyInputs = document.querySelectorAll('.qty-input');
    
    qtyInputs.forEach(input => {
        input.addEventListener('change', function() {
            const productId = this.dataset.productId;
            const newValue = parseInt(this.value);
            
            if (newValue >= 1 && newValue <= parseInt(this.max)) {
                updateQuantity(productId, newValue);
            } else {
                this.value = Math.max(1, Math.min(newValue, parseInt(this.max)));
            }
        });
    });
    
    // Gestion de la suppression d'articles
    const removeButtons = document.querySelectorAll('.remove-item');
    
    removeButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.dataset.productId;
            removeItem(productId);
        });
    });
    
    // Fonction pour mettre à jour la quantité
    function updateQuantity(productId, quantity) {
        fetch('<?php echo APP_URL; ?>shop/update-cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `product_id=${productId}&quantity=${quantity}&csrf_token=<?php echo generateCSRFToken(); ?>`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Mettre à jour l'affichage
                location.reload();
            } else {
                showNotification(data.message || 'Erreur lors de la mise à jour', 'error');
            }
        })
        .catch(error => {
            showNotification('Erreur de connexion', 'error');
        });
    }
    
    // Fonction pour supprimer un article
    function removeItem(productId) {
        if (confirm('Êtes-vous sûr de vouloir supprimer cet article ?')) {
            fetch('<?php echo APP_URL; ?>shop/remove-from-cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}&csrf_token=<?php echo generateCSRFToken(); ?>`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Mettre à jour l'affichage
                    location.reload();
                } else {
                    showNotification(data.message || 'Erreur lors de la suppression', 'error');
                }
            })
            .catch(error => {
                showNotification('Erreur de connexion', 'error');
            });
        }
    }
    
    // Fonction pour afficher les notifications
    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <i class="bx ${type === 'success' ? 'bxs-check-circle' : 'bxs-error-circle'}"></i>
            <span>${message}</span>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.classList.add('show');
        }, 100);
        
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }
});
</script>

<?php require_once '../includes/footer.php'; ?> 