<?php
$page_title = "Détail produit";
$page_description = "Découvrez les détails de ce produit";

require_once '../includes/header.php';

$product_id = (int)($_GET['id'] ?? 0);

if (!$product_id) {
    $_SESSION['flash_message'] = "Produit non trouvé.";
    $_SESSION['flash_type'] = 'error';
    redirect(APP_URL . 'shop/');
}

$pdo = getDBConnection();

// Récupérer les détails du produit
$bdd = $pdo->prepare("SELECT * FROM products WHERE id = ? AND status = 'available'");
$bdd->execute([$product_id]);
$product = $bdd->fetch();

if (!$product) {
    $_SESSION['flash_message'] = "Produit non trouvé ou indisponible.";
    $_SESSION['flash_type'] = 'error';
    redirect(APP_URL . 'shop/');
}

// Récupérer les produits similaires
$bdd = $pdo->prepare("
    SELECT * FROM products 
    WHERE category = ? AND id != ? AND status = 'available' AND stock > 0 
    ORDER BY created_at DESC LIMIT 4
");
$bdd->execute([$product['category'], $product_id]);
$related_products = $bdd->fetchAll();

$page_title = $product['name'];
$page_description = $product['description'];
?>

<div class="product-detail-container">
    <div class="container">
        <!-- Breadcrumb -->
        <nav class="breadcrumb">
            <a href="<?php echo APP_URL; ?>">Accueil</a>
            <i class='bx bx-chevron-right'></i>
            <a href="<?php echo APP_URL; ?>shop/">Boutique</a>
            <i class='bx bx-chevron-right'></i>
            <a href="<?php echo APP_URL; ?>shop/?category=<?php echo $product['category']; ?>">
                <?php echo ucfirst($product['category']); ?>
            </a>
            <i class='bx bx-chevron-right'></i>
            <span><?php echo htmlspecialchars($product['name']); ?></span>
        </nav>

        <!-- Détails du produit -->
        <div class="product-detail">
            <div class="product-gallery">
                <div class="main-image">
                    <?php if (!empty($product['image_url'])): ?>
                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                             onerror="this.src='<?php echo APP_URL; ?>assets/images/products/default.jpg'">
                    <?php else: ?>
                        <img src="<?php echo APP_URL; ?>assets/images/products/default.jpg" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>">
                    <?php endif; ?>
                </div>
            </div>

            <div class="product-info">
                <div class="product-header">
                    <div class="product-category">
                        <?php echo ucfirst($product['category']); ?>
                    </div>
                    <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                    <div class="product-price">
                        <span class="price"><?php echo formatPrice($product['price']); ?></span>
                    </div>
                </div>

                <div class="product-description">
                    <h3>Description</h3>
                    <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                </div>

                <div class="product-meta">
                    <div class="meta-item">
                        <i class='bx bxs-package'></i>
                        <span>Stock: <strong><?php echo $product['stock']; ?> disponible(s)</strong></span>
                    </div>
                    <div class="meta-item">
                        <i class='bx bxs-category'></i>
                        <span>Catégorie: <strong><?php echo ucfirst($product['category']); ?></strong></span>
                    </div>
                    <div class="meta-item">
                        <i class='bx bxs-calendar'></i>
                        <span>Ajouté le: <strong><?php echo formatDate($product['created_at']); ?></strong></span>
                    </div>
                </div>

                <?php if ($product['stock'] > 0): ?>
                    <div class="product-actions">
                        <form class="add-to-cart-form" method="POST" action="<?php echo APP_URL; ?>shop/add-to-cart.php">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            
                            <div class="quantity-selector">
                                <label for="quantity">Quantité:</label>
                                <div class="quantity-controls">
                                    <button type="button" class="qty-btn" data-action="decrease">-</button>
                                    <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>">
                                    <button type="button" class="qty-btn" data-action="increase">+</button>
                                </div>
                            </div>

                            <div class="action-buttons">
                                <button type="submit" class="btn btn-primary btn-large add-to-cart-btn">
                                    <i class='bx bxs-cart-add'></i>
                                    Ajouter au panier
                                </button>
                                <button type="button" class="btn btn-outline btn-large wishlist-btn">
                                    <i class='bx bxs-heart'></i>
                                    Ajouter aux favoris
                                </button>
                            </div>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="product-unavailable">
                        <i class='bx bxs-error-circle'></i>
                        <p>Ce produit est actuellement indisponible</p>
                        <button class="btn btn-outline notify-btn">
                            <i class='bx bxs-bell'></i>
                            Être notifié quand disponible
                        </button>
                    </div>
                <?php endif; ?>

                <!-- Informations supplémentaires -->
                <div class="product-features">
                    <div class="feature">
                        <i class='bx bxs-truck'></i>
                        <div>
                            <h4>Livraison gratuite</h4>
                            <p>Pour toute commande supérieure à 50€</p>
                        </div>
                    </div>
                    <div class="feature">
                        <i class='bx bxs-shield-check'></i>
                        <div>
                            <h4>Garantie qualité</h4>
                            <p>Produits testés et approuvés</p>
                        </div>
                    </div>
                    <div class="feature">
                        <i class='bx bxs-undo'></i>
                        <div>
                            <h4>Retour facile</h4>
                            <p>30 jours pour changer d'avis</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Produits similaires -->
        <?php if (!empty($related_products)): ?>
        <section class="related-products">
            <div class="section-header">
                <h2>Produits similaires</h2>
                <p>Découvrez d'autres produits de la même catégorie</p>
            </div>
            
            <div class="products-grid">
                <?php foreach ($related_products as $related): ?>
                <div class="product-card">
                    <div class="product-image">
                        <?php if (!empty($related['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($related['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($related['name']); ?>"
                                 onerror="this.src='<?php echo APP_URL; ?>assets/images/products/default.jpg'">
                        <?php else: ?>
                            <img src="<?php echo APP_URL; ?>assets/images/products/default.jpg" 
                                 alt="<?php echo htmlspecialchars($related['name']); ?>">
                        <?php endif; ?>
                        <div class="product-overlay">
                            <div class="product-actions">
                                <a href="<?php echo APP_URL; ?>shop/product.php?id=<?php echo $related['id']; ?>" 
                                   class="btn btn-primary btn-sm">Voir détails</a>
                                <button class="btn btn-outline btn-sm add-to-cart" 
                                        data-product-id="<?php echo $related['id']; ?>">
                                    <i class='bx bxs-cart-add'></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="product-content">
                        <div class="product-category">
                            <?php echo ucfirst($related['category']); ?>
                        </div>
                        <h3 class="product-title">
                            <a href="<?php echo APP_URL; ?>shop/product.php?id=<?php echo $related['id']; ?>">
                                <?php echo htmlspecialchars($related['name']); ?>
                            </a>
                        </h3>
                        <p class="product-description">
                            <?php echo htmlspecialchars(substr($related['description'], 0, 80)) . '...'; ?>
                        </p>
                        <div class="product-footer">
                            <div class="product-price">
                                <span class="price"><?php echo formatPrice($related['price']); ?></span>
                            </div>
                            <button class="btn btn-primary add-to-cart-btn" 
                                    data-product-id="<?php echo $related['id']; ?>">
                                <i class='bx bxs-cart-add'></i>
                                Ajouter
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
    </div>
</div>

<!-- Script pour la gestion de la quantité et l'ajout au panier -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des boutons de quantité
    const qtyBtns = document.querySelectorAll('.qty-btn');
    const qtyInput = document.getElementById('quantity');
    
    qtyBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const action = this.dataset.action;
            let currentValue = parseInt(qtyInput.value);
            const maxValue = parseInt(qtyInput.max);
            
            if (action === 'increase' && currentValue < maxValue) {
                qtyInput.value = currentValue + 1;
            } else if (action === 'decrease' && currentValue > 1) {
                qtyInput.value = currentValue - 1;
            }
        });
    });
    
    // Gestion de l'ajout au panier
    const addToCartForm = document.querySelector('.add-to-cart-form');
    const addToCartBtn = document.querySelector('.add-to-cart-btn');
    
    if (addToCartForm) {
        addToCartForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            // Animation de chargement
            addToCartBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Ajout...';
            addToCartBtn.disabled = true;
            
            // Requête AJAX
            fetch(this.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Succès
                    addToCartBtn.innerHTML = '<i class="bx bxs-check"></i> Ajouté !';
                    addToCartBtn.classList.add('success');
                    
                    // Notification
                    showNotification('Produit ajouté au panier !', 'success');
                    
                    // Mise à jour du compteur du panier
                    updateCartCount();
                    
                    setTimeout(() => {
                        addToCartBtn.innerHTML = '<i class="bx bxs-cart-add"></i> Ajouter au panier';
                        addToCartBtn.classList.remove('success');
                        addToCartBtn.disabled = false;
                    }, 2000);
                } else {
                    // Erreur
                    addToCartBtn.innerHTML = '<i class="bx bxs-cart-add"></i> Ajouter au panier';
                    addToCartBtn.disabled = false;
                    showNotification(data.message || 'Erreur lors de l\'ajout au panier', 'error');
                }
            })
            .catch(error => {
                addToCartBtn.innerHTML = '<i class="bx bxs-cart-add"></i> Ajouter au panier';
                addToCartBtn.disabled = false;
                showNotification('Erreur de connexion', 'error');
            });
        });
    }
    
    // Gestion des boutons d'ajout au panier des produits similaires
    const relatedAddToCartButtons = document.querySelectorAll('.related-products .add-to-cart, .related-products .add-to-cart-btn');
    
    relatedAddToCartButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const productId = this.dataset.productId;
            
            // Animation de chargement
            this.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Ajout...';
            this.disabled = true;
            
            // Requête AJAX
            fetch('<?php echo APP_URL; ?>shop/add-to-cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'product_id=' + productId + '&quantity=1&csrf_token=<?php echo generateCSRFToken(); ?>'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.innerHTML = '<i class="bx bxs-check"></i> Ajouté !';
                    this.classList.add('success');
                    showNotification('Produit ajouté au panier !', 'success');
                    updateCartCount();
                    
                    setTimeout(() => {
                        this.innerHTML = '<i class="bx bxs-cart-add"></i> Ajouter';
                        this.classList.remove('success');
                        this.disabled = false;
                    }, 2000);
                } else {
                    this.innerHTML = '<i class="bx bxs-cart-add"></i> Ajouter';
                    this.disabled = false;
                    showNotification(data.message || 'Erreur lors de l\'ajout au panier', 'error');
                }
            })
            .catch(error => {
                this.innerHTML = '<i class="bx bxs-cart-add"></i> Ajouter';
                this.disabled = false;
                showNotification('Erreur de connexion', 'error');
            });
        });
    });
    
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
    
    // Fonction pour mettre à jour le compteur du panier
    function updateCartCount() {
        fetch('<?php echo APP_URL; ?>shop/get-cart-count.php')
        .then(response => response.json())
        .then(data => {
            const cartCount = document.querySelector('.cart-count');
            if (cartCount) {
                cartCount.textContent = data.count;
                cartCount.style.display = data.count > 0 ? 'block' : 'none';
            }
        });
    }
});
</script>

<?php require_once '../includes/footer.php'; ?> 