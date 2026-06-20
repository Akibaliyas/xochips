<?php
session_start();
include "database.php";

// Build query based on filters
$where = "WHERE p.status = 'active'";

if(isset($_GET['category']) && !empty($_GET['category'])) {
    $category_slug = mysqli_real_escape_string($conn, $_GET['category']);
    $where .= " AND c.slug = '$category_slug'";
}

if(isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $where .= " AND (p.name LIKE '%$search%' OR p.description LIKE '%$search%')";
}

if(isset($_GET['type']) && !empty($_GET['type'])) {
    $type = mysqli_real_escape_string($conn, $_GET['type']);
    $where .= " AND p.type = '$type'";
}

$order_by = "ORDER BY p.created_at DESC";
if(isset($_GET['sort'])) {
    switch($_GET['sort']) {
        case 'price_low':
            $order_by = "ORDER BY p.price ASC";
            break;
        case 'price_high':
            $order_by = "ORDER BY p.price DESC";
            break;
        case 'popular':
            $order_by = "ORDER BY p.id DESC";
            break;
    }
}

$products = mysqli_query($conn, "SELECT p.*, c.name as category_name, c.slug as category_slug, c.icon 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    $where 
    $order_by");

$categories = mysqli_query($conn, "SELECT * FROM categories WHERE status = 'active'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - XO Chinese Chips</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .shop-container {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 30px;
            max-width: 1400px;
            margin: 40px auto;
            padding: 0 30px;
        }
        .sidebar {
            background: white;
            border-radius: 20px;
            padding: 25px;
            height: fit-content;
            position: sticky;
            top: 100px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        .filter-section {
            margin-bottom: 25px;
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
        }
        .filter-section h4 {
            margin-bottom: 15px;
            color: #8B0000;
            font-size: 18px;
        }
        .filter-section select, .filter-section input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        .category-filter a {
            display: block;
            padding: 10px 0;
            color: #666;
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
            padding-left: 10px;
        }
        .category-filter a:hover, .category-filter a.active {
            color: #FF6347;
            border-left-color: #FF6347;
            background: #fff5f0;
        }
        .apply-filters {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #8B0000, #FF6347);
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
            margin-top: 15px;
        }
        .sort-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .sort-select {
            padding: 10px 20px;
            border: 1px solid #ddd;
            border-radius: 25px;
            background: white;
        }
        .products-count {
            color: #666;
            font-weight: 500;
        }
        .type-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        .type-btn {
            padding: 8px 16px;
            background: #f5f5f5;
            border-radius: 25px;
            text-decoration: none;
            color: #666;
            font-size: 14px;
            transition: all 0.3s;
        }
        .type-btn:hover, .type-btn.active {
            background: #FF6347;
            color: white;
        }
        @media (max-width: 992px) {
            .shop-container {
                grid-template-columns: 1fr;
            }
            .sidebar {
                position: static;
            }
        }
        @media (max-width: 768px) {
            .shop-container {
                padding: 0 20px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo">
                <!-- <span class="logo-icon">🍟</span> -->
                <span class="logo-text">XO Chinese Chips</span>
            </div>
            <div class="nav">
                <a href="index.php">Home</a>
                <a href="shop.php" class="active">Menu</a>
                <a href="deals.php">Deals</a>
                <?php if(isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php">My Account</a>
                <a href="wishlist.php"><i class="fas fa-heart"></i> Wishlist <span class="badge"><?php echo getWishlistCount($_SESSION['user_id']); ?></span></a>
                <a href="cart.php"><i class="fas fa-shopping-bag"></i> Cart <span class="badge"><?php echo getCartCount($_SESSION['user_id']); ?></span></a>
                <a href="logout.php">Logout</a>
                <?php else: ?>
                <a href="signin.php">Sign In</a>
                <a href="register.php">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="shop-container">
        <!-- Sidebar Filters -->
        <div class="sidebar">
            <h3 style="margin-bottom: 20px; color: #8B0000;">Filters</h3>
            
            <div class="filter-section">
                <h4><i class="fas fa-search"></i> Search</h4>
                <form method="get">
                    <input type="text" name="search" placeholder="Search menu..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button type="submit" class="apply-filters">Search</button>
                </form>
            </div>
            
            <div class="filter-section">
                <h4><i class="fas fa-tag"></i> Categories</h4>
                <div class="category-filter">
                    <a href="shop.php" class="<?php echo !isset($_GET['category']) ? 'active' : ''; ?>">All Items</a>
                    <?php while($cat = mysqli_fetch_assoc($categories)): ?>
                    <a href="shop.php?category=<?php echo $cat['slug']; ?>" class="<?php echo (isset($_GET['category']) && $_GET['category'] == $cat['slug']) ? 'active' : ''; ?>">
                        <?php echo $cat['icon']; ?> <?php echo $cat['name']; ?>
                    </a>
                    <?php endwhile; ?>
                </div>
            </div>
            
            <div class="filter-section">
                <h4><i class="fas fa-fire"></i> Item Type</h4>
                <div class="type-buttons">
                    <a href="shop.php<?php echo isset($_GET['category']) ? '?category='.$_GET['category'] : ''; ?>" class="type-btn <?php echo !isset($_GET['type']) ? 'active' : ''; ?>">All</a>
                    <a href="shop.php?type=deal<?php echo isset($_GET['category']) ? '&category='.$_GET['category'] : ''; ?>" class="type-btn <?php echo (isset($_GET['type']) && $_GET['type'] == 'deal') ? 'active' : ''; ?>">🍽️ Deals</a>
                    <a href="shop.php?type=main<?php echo isset($_GET['category']) ? '&category='.$_GET['category'] : ''; ?>" class="type-btn <?php echo (isset($_GET['type']) && $_GET['type'] == 'main') ? 'active' : ''; ?>">🍟 Mains</a>
                    <a href="shop.php?type=drink<?php echo isset($_GET['category']) ? '&category='.$_GET['category'] : ''; ?>" class="type-btn <?php echo (isset($_GET['type']) && $_GET['type'] == 'drink') ? 'active' : ''; ?>">🥤 Drinks</a>
                </div>
            </div>
        </div>
        
        <!-- Products Grid -->
        <div>
            <div class="sort-bar">
                <div class="products-count">
                    <i class="fas fa-utensils"></i> <?php echo mysqli_num_rows($products); ?> items found
                </div>
                <form method="get" class="sort-form">
                    <select name="sort" class="sort-select" onchange="this.form.submit()">
                        <option value="newest" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'newest') ? 'selected' : ''; ?>>Newest First</option>
                        <option value="price_low" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price_low') ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price_high" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price_high') ? 'selected' : ''; ?>>Price: High to Low</option>
                        <option value="popular" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'popular') ? 'selected' : ''; ?>>Most Popular</option>
                    </select>
                    <?php foreach($_GET as $key => $value): ?>
                        <?php if($key != 'sort'): ?>
                        <input type="hidden" name="<?php echo $key; ?>" value="<?php echo htmlspecialchars($value); ?>">
                        <?php endif; ?>
                    <?php endforeach; ?>
                </form>
            </div>
            
            <?php if(mysqli_num_rows($products) == 0): ?>
            <div class="no-products" style="text-align: center; padding: 60px; background: white; border-radius: 20px;">
                <i class="fas fa-search" style="font-size: 64px; color: #ddd;"></i>
                <h3 style="margin-top: 20px;">No items found!</h3>
                <p>Try adjusting your filters or search criteria</p>
                <a href="shop.php" class="btn-primary" style="display: inline-block; margin-top: 20px;">Clear Filters</a>
            </div>
            <?php else: ?>
            <div class="products-grid">
                <?php while($product = mysqli_fetch_assoc($products)): 
                    $image_path = 'uploads/products/' . $product['main_image'];
                ?>
                <div class="product-card">
                    <div class="product-badge">
                        <?php if($product['type'] == 'deal'): ?>
                        <span class="badge-sale">🔥 Deal</span>
                        <?php endif; ?>
                        <?php if($product['is_new']): ?>
                        <span class="badge-new">New</span>
                        <?php endif; ?>
                        <?php if($product['is_on_sale'] && $product['discount_percent'] > 0): ?>
                        <span class="badge-sale">-<?php echo $product['discount_percent']; ?>%</span>
                        <?php endif; ?>
                    </div>
                    <div class="product-image">
                        <?php if(file_exists($image_path) && $product['main_image'] != 'default-product.jpg'): ?>
                        <img src="<?php echo $image_path; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <?php else: ?>
                        <div class="no-image">🍟</div>
                        <?php endif; ?>
                        <div class="product-actions">
                            <a href="quick_view.php?id=<?php echo $product['id']; ?>" class="quick-view"><i class="fas fa-eye"></i></a>
                            <a href="add_to_wishlist.php?id=<?php echo $product['id']; ?>" class="add-wishlist"><i class="fas fa-heart"></i></a>
                        </div>
                    </div>
                    <div class="product-info">
                        <span class="product-category"><?php echo $product['icon']; ?> <?php echo $product['category_name']; ?></span>
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="product-short-desc" style="font-size: 12px; color: #999; margin: 5px 0;"><?php echo htmlspecialchars(substr($product['short_description'], 0, 60)); ?></p>
                        <div class="product-price">
                            <span class="current-price">PKR <?php echo number_format($product['price']); ?></span>
                            <?php if($product['compare_price'] > 0): ?>
                            <span class="old-price">PKR <?php echo number_format($product['compare_price']); ?></span>
                            <?php endif; ?>
                        </div>
                        <a href="product_details.php?id=<?php echo $product['id']; ?>" class="btn-add-to-cart">Order Now <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>🍟 XO Chinese Chips</h3>
                <p>Authentic wok-tossed fries and Chinese fusion delivered to your door.</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-tiktok"></i></a>
                </div>
            </div>
            <div class="footer-section">
                <h4>Quick Links</h4>
                <a href="shop.php">Full Menu</a>
                <a href="deals.php">Family Deals</a>
                <a href="about.php">About Us</a>
                <a href="contact.php">Contact</a>
            </div>
            <div class="footer-section">
                <h4>Contact</h4>
                <p><i class="fas fa-phone"></i> +92 300 1234567</p>
                <p><i class="fas fa-envelope"></i> order@xochips.com</p>
                <p><i class="fas fa-map-marker-alt"></i> Lahore, Pakistan</p>
            </div>
            <div class="footer-section">
                <h4>Hours</h4>
                <p>Mon-Thu: 11AM - 11PM</p>
                <p>Fri-Sat: 11AM - 11:30PM</p>
                <p>Sun: 12PM - 10PM</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> XO Chinese Chips. All rights reserved. | Made with 🍟 for chip lovers</p>
        </div>
    </div>
</body>
</html>