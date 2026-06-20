<?php
session_start();
include "database.php";

$featured_items = mysqli_query($conn, "SELECT p.*, c.name as category_name, c.icon 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE p.status='active' AND p.is_featured=1 
    ORDER BY p.created_at DESC LIMIT 8");

$deals = mysqli_query($conn, "SELECT p.*, c.name as category_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE p.status='active' AND p.type='deal' 
    ORDER BY p.created_at DESC");

$categories = mysqli_query($conn, "SELECT * FROM categories WHERE status='active'");

$store_open = isStoreOpen();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XO Chinese Chips - Best Wok Tossed Fries & Chinese Fusion</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo">
                <!-- <span class="">🍟</span> -->
                <span class="logo-text">XO Chinese Chips</span>
            </div>
            <div class="nav">
                <a href="index.php" class="active">Home</a>
                <a href="shop.php">Menu</a>
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

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Wok-Tossed <span class="highlight">Perfection</span></h1>
            <p>Experience the ultimate Chinese fusion - crispy fries, succulent proteins, and our signature XO sauce. Made fresh, tossed with love.</p>
            <div class="hero-buttons">
                <a href="shop.php" class="btn-primary">Order Now <i class="fas fa-arrow-right"></i></a>
                <a href="#deals" class="btn-secondary">View Deals</a>
            </div>
        </div>
        <div class="hero-image">
            <div class="floating-icon icon-1">🍟</div>
            <div class="floating-icon icon-2">🌶️</div>
            <div class="floating-icon icon-3">🍗</div>
            <div class="floating-icon icon-4">🥤</div>
        </div>
    </section>

    <!-- Store Status Banner -->
    <div class="store-status <?php echo $store_open ? 'open' : 'closed'; ?>">
        <i class="fas <?php echo $store_open ? 'fa-check-circle' : 'fa-clock'; ?>"></i>
        <?php if($store_open): ?>
        We're Open! Order now for delivery within 45 minutes.
        <?php else: ?>
        We're Closed. Opening at 11:00 AM tomorrow.
        <?php endif; ?>
    </div>

    <!-- Categories Section -->
    <section class="categories">
        <div class="container">
            <h2 class="section-title">What's On The Menu?</h2>
            <div class="categories-grid">
                <?php 
                $cats = mysqli_query($conn, "SELECT * FROM categories WHERE status='active' LIMIT 6");
                while($cat = mysqli_fetch_assoc($cats)): 
                ?>
                <a href="shop.php?category=<?php echo $cat['slug']; ?>" class="category-card">
                    <div class="category-icon"><?php echo $cat['icon']; ?></div>
                    <h3><?php echo $cat['name']; ?></h3>
                    <p><?php echo $cat['description']; ?></p>
                </a>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <!-- Featured Deals -->
    <section id="deals" class="deals-section">
        <div class="container">
            <h2 class="section-title">🔥 Sharing Deals</h2>
            <div class="deals-grid">
                <?php while($deal = mysqli_fetch_assoc($deals)): 
                    $image_path = 'uploads/products/' . $deal['main_image'];
                ?>
                <div class="deal-card">
                    <div class="deal-badge">Save <?php echo round((($deal['compare_price'] - $deal['price']) / $deal['compare_price']) * 100); ?>%</div>
                    <div class="deal-image">
                        <?php if(file_exists($image_path) && $deal['main_image'] != 'default-product.jpg'): ?>
                        <img src="<?php echo $image_path; ?>" alt="<?php echo htmlspecialchars($deal['name']); ?>">
                        <?php else: ?>
                        <div class="no-image">🍽️</div>
                        <?php endif; ?>
                    </div>
                    <div class="deal-info">
                        <h3><?php echo htmlspecialchars($deal['name']); ?></h3>
                        <p><?php echo htmlspecialchars($deal['short_description']); ?></p>
                        <div class="deal-price">
                            <span class="current-price">PKR <?php echo number_format($deal['price']); ?></span>
                            <span class="old-price">PKR <?php echo number_format($deal['compare_price']); ?></span>
                        </div>
                        <a href="product_details.php?id=<?php echo $deal['id']; ?>" class="btn-order">Order Now →</a>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <!-- Featured Items -->
    <section class="featured-section">
        <div class="container">
            <h2 class="section-title">⭐ Customer Favorites</h2>
            <div class="products-grid">
                <?php while($item = mysqli_fetch_assoc($featured_items)): 
                    $image_path = 'uploads/products/' . $item['main_image'];
                ?>
                <div class="product-card">
                    <div class="product-image">
                        <?php if(file_exists($image_path) && $item['main_image'] != 'default-product.jpg'): ?>
                        <img src="<?php echo $image_path; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                        <?php else: ?>
                        <div class="no-image">🍟</div>
                        <?php endif; ?>
                        <div class="product-actions">
                            <a href="quick_view.php?id=<?php echo $item['id']; ?>" class="quick-view"><i class="fas fa-eye"></i></a>
                            <a href="add_to_wishlist.php?id=<?php echo $item['id']; ?>" class="add-wishlist"><i class="fas fa-heart"></i></a>
                        </div>
                    </div>
                    <div class="product-info">
                        <span class="product-category"><?php echo $item['icon']; ?> <?php echo $item['category_name']; ?></span>
                        <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                        <div class="product-price">
                            <span class="current-price">PKR <?php echo number_format($item['price']); ?></span>
                        </div>
                        <a href="product_details.php?id=<?php echo $item['id']; ?>" class="btn-add-to-cart">Order Now</a>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <!-- Signature Sauces -->
    <section class="sauces-section">
        <div class="container">
            <h2 class="section-title">🌶️ Our Signature Sauces</h2>
            <div class="sauces-grid">
                <div class="sauce-card">
                    <div class="sauce-icon">🔥</div>
                    <h3>Dynamite Sauce</h3>
                    <p>Viral-Worthy Heat - Orange, creamy, absolute fire</p>
                </div>
                <div class="sauce-card">
                    <div class="sauce-icon">✨</div>
                    <h3>XO Spice Mix</h3>
                    <p>The Flavor Cheat Code - Smoky, savory umami bomb</p>
                </div>
                <div class="sauce-card">
                    <div class="sauce-icon">🥛</div>
                    <h3>House Mayo</h3>
                    <p>The Ultimate Chill Factor - Thick, velvety, essential</p>
                </div>
            </div>
        </div>
    </section>

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

    <style>
        .store-status {
            text-align: center;
            padding: 12px;
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 999;
        }
        .store-status.open {
            background: #4caf50;
            color: white;
        }
        .store-status.closed {
            background: #f44336;
            color: white;
        }
        .deals-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
        }
        .deal-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            position: relative;
        }
        .deal-card:hover {
            transform: translateY(-5px);
        }
        .deal-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: #ff6b6b;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: bold;
            z-index: 1;
        }
        .deal-image {
            height: 220px;
            overflow: hidden;
        }
        .deal-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .deal-info {
            padding: 20px;
        }
        .deal-info h3 {
            font-size: 22px;
            margin-bottom: 10px;
        }
        .deal-price {
            margin: 15px 0;
        }
        .btn-order {
            display: inline-block;
            padding: 10px 20px;
            background: linear-gradient(135deg, #8B0000, #FF6347);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
        }
        .sauces-section {
            background: linear-gradient(135deg, #fff5f0, #ffe8e0);
            padding: 60px 0;
        }
        .sauces-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            text-align: center;
        }
        .sauce-card {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        .sauce-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
    </style>
</body>
</html>