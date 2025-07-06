<?php
$page_title = "Boutique";
$page_description = "Découvrez notre sélection de produits fitness et nutrition de qualité";

require_once '../includes/header.php';

// Récupération des paramètres de filtrage
$category = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'name';
$order = $_GET['order'] ?? 'ASC';

// Construction de la requête
$pdo = getDBConnection();
$where_conditions = ["status = 'available'", "stock > 0"];
$params = [];

if (!empty($category)) {
    $where_conditions[] = "category = ?";
    $params[] = $category;
}

if (!empty($search)) {
    $where_conditions[] = "(name LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = implode(" AND ", $where_conditions);
$order_clause = "ORDER BY $sort $order";

$sql = "SELECT * FROM products WHERE $where_clause $order_clause";
$bdd = $pdo->prepare($sql);
$bdd->execute($params);
$products = $bdd->fetchAll();

// Récupérer les catégories pour le filtre
$bdd = $pdo->query("SELECT DISTINCT category FROM products WHERE status = 'available' ORDER BY category");
$categories = $bdd->fetchAll();
?>

<div class="shop-container">
    <!-- En-tête de la boutique -->
    <div class="shop-header">
        <div class="container">
            <h1>Boutique NutriFit</h1>
            <p>Équipements, suppléments et accessoires pour optimiser vos performances</p>
        </div>
    </div>

    <!-- Filtres et recherche -->
    <div class="shop-filters">
        <div class="container">
            <form method="GET" class="filters-form">
                <div class="filters-row">
                    <div class="filter-group">
                        <label for="search">Rechercher</label>
                        <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Nom du produit...">
                    </div>
                    
                    <div class="filter-group">
                        <label for="category">Catégorie</label>
                        <select id="category" name="category">
                            <option value="">Toutes les catégories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['category']; ?>" 
                                        <?php echo $category === $cat['category'] ? 'selected' : ''; ?>>
                                    <?php echo ucfirst($cat['category']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="sort">Trier par</label>
                        <select id="sort" name="sort">
                            <option value="name" <?php echo $sort === 'name' ? 'selected' : ''; ?>>Nom</option>
                            <option value="price" <?php echo $sort === 'price' ? 'selected' : ''; ?>>Prix</option>
                            <option value="created_at" <?php echo $sort === 'created_at' ? 'selected' : ''; ?>>Date</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="order">Ordre</label>
                        <select id="order" name="order">
                            <option value="ASC" <?php echo $order === 'ASC' ? 'selected' : ''; ?>>Croissant</option>
                            <option value="DESC" <?php echo $order === 'DESC' ? 'selected' : ''; ?>>Décroissant</option>
                        </select>
                    </div>
                    
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary">Filtrer</button>
                        <a href="<?php echo APP_URL; ?>shop/" class="btn btn-outline">Réinitialiser</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Résultats -->
    <div class="shop-content">
        <div class="container">
            <div class="shop-results">
                <div class="results-header">
                    <h2><?php echo count($products); ?> produit(s) trouvé(s)</h2>
                    <?php if (!empty($search) || !empty($category)): ?>
                        <div class="active-filters">
                            <?php if (!empty($search)): ?>
                                <span class="filter-tag">Recherche: "<?php echo htmlspecialchars($search); ?>"</span>
                            <?php endif; ?>
                            <?php if (!empty($category)): ?>
                                <span class="filter-tag">Catégorie: <?php echo ucfirst($category); ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (empty($products)): ?>
                    <div class="no-results">
                        <i class='bx bx-search-alt'></i>
                        <h3>Aucun produit trouvé</h3>
                        <p>Essayez de modifier vos critères de recherche</p>
                        <a href="<?php echo APP_URL; ?>shop/" class="btn btn-primary">Voir tous les produits</a>
                    </div>
                <?php else: ?>
                    <div class="products-grid">
                        <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <?php if (!empty($product['image_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                                         onerror="this.src='<?php echo APP_URL; ?>assets/images/products/default.jpg'">
                                <?php else: ?>
                                    <img src="<?php echo APP_URL; ?>assets/images/products/default.jpg" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>">
                                <?php endif; ?>
                                <div class="product-overlay">
                                    <div class="product-actions">
                                        <a href="<?php echo APP_URL; ?>shop/product.php?id=<?php echo $product['id']; ?>" 
                                           class="btn btn-primary btn-sm">Voir détails</a>
                                        <button class="btn btn-outline btn-sm add-to-cart" 
                                                data-product-id="<?php echo $product['id']; ?>">
                                            <i class='bx bxs-cart-add'></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="product-content">
                                <div class="product-category">
                                    <?php echo ucfirst($product['category']); ?>
                                </div>
                                <h3 class="product-title">
                                    <a href="<?php echo APP_URL; ?>shop/product.php?id=<?php echo $product['id']; ?>">
                                        <?php echo htmlspecialchars($product['name']); ?>
                                    </a>
                                </h3>
                                <p class="product-description">
                                    <?php echo htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?>
                                </p>
                                <div class="product-meta">
                                    <div class="product-stock">
                                        <i class='bx bxs-package'></i>
                                        <?php echo $product['stock']; ?> en stock
                                    </div>
                                </div>
                                <div class="product-footer">
                                    <div class="product-price">
                                        <span class="price"><?php echo formatPrice($product['price']); ?></span>
                                    </div>
                                    <button class="btn btn-primary add-to-cart-btn" 
                                            data-product-id="<?php echo $product['id']; ?>">
                                        <i class='bx bxs-cart-add'></i>
                                        Ajouter
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Script pour l'ajout au panier -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion de l'ajout au panier
    const addToCartButtons = document.querySelectorAll('.add-to-cart, .add-to-cart-btn');
    
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const productId = this.dataset.productId;
            
            // Animation de chargement
            this.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Ajout...';
            this.disabled = true;
            
            // Requête AJAX pour ajouter au panier
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
                    // Succès
                    this.innerHTML = '<i class="bx bxs-check"></i> Ajouté !';
                    this.classList.add('success');
                    
                    // Notification
                    showNotification('Produit ajouté au panier !', 'success');
                    
                    // Mise à jour du compteur du panier
                    updateCartCount();
                    
                    setTimeout(() => {
                        this.innerHTML = '<i class="bx bxs-cart-add"></i> Ajouter';
                        this.classList.remove('success');
                        this.disabled = false;
                    }, 2000);
                } else {
                    // Erreur
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