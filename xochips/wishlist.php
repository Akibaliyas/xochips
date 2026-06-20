<?php
session_start();
include "database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Remove from wishlist via GET
if(isset($_GET['remove'])) {
    $product_id = intval($_GET['remove']);
    mysqli_query($conn, "DELETE FROM wishlist WHERE user_id='$user_id' AND product_id='$product_id'");
    $_SESSION['wishlist_message'] = "Item removed from wishlist!";
    header("Location: wishlist.php");
    exit();
}

// Add to cart from wishlist
if(isset($_GET['add_to_cart'])) {
    $product_id = intval($_GET['add_to_cart']);
    
    // Check if already in cart
    $check_cart = mysqli_query($conn, "SELECT id, quantity FROM cart WHERE user_id='$user_id' AND product_id='$product_id'");
    
    if(mysqli_num_rows($check_cart) > 0) {
        $cart_item = mysqli_fetch_assoc($check_cart);
        $new_qty = $cart_item['quantity'] + 1;
        mysqli_query($conn, "UPDATE cart SET quantity='$new_qty' WHERE id='{$cart_item['id']}'");
    } else {
        mysqli_query($conn, "INSERT INTO cart (user_id, product_id, quantity) VALUES ('$user_id', '$product_id', 1)");
    }
    
    // Remove from wishlist after adding to cart
    mysqli_query($conn, "DELETE FROM wishlist WHERE user_id='$user_id' AND product_id='$product_id'");
    $_SESSION['cart_message'] = "Item added to cart!";
    header("Location: cart.php");
    exit();
}

// Get wishlist items with product details
$wishlist = mysqli_query($conn, "SELECT p.*, c.name as category_name, c.icon 
    FROM products p 
    JOIN wishlist w ON p.id = w.product_id 
    JOIN categories c ON p.category_id = c.id
    WHERE w.user_id='$user_id' AND p.status='active'
    ORDER BY w.created_at DESC");

$wishlist_count = mysqli_num_rows($wishlist);

// Display message if exists
$message = '';
if(isset($_SESSION['wishlist_message'])) {
    $message = $_SESSION['wishlist_message'];
    unset($_SESSION['wishlist_message']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist - XO Chinese Chips</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .wishlist-container {
            max-width: 1200px;
            margin: 60px auto;
            padding: 0 20px;
        }
        .wishlist-header {
            text-align: center;
            margin-bottom: 40px;
        }
        .wishlist-header h1 {
            font-size: 36px;
            color: #8B0000;
        }
        .wishlist-header p {
            color: #666;
        }
        .message {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 12px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        .empty-wishlist {
            text-align: center;
            padding: 80px;
            background: white;
            border-radius: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        .empty-wishlist i {
            font-size: 80px;
            color: #ddd;
        }
        .empty-wishlist h3 {
            margin: 20px 0 10px;
            color: #8B0000;
        }
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
        }
        .product-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            position: relative;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
        }
        .product-image {
            position: relative;
            height: 220px;
            overflow: hidden;
            background: #fff5f0;
        }
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        .product-card:hover .product-image img {
            transform: scale(1.05);
        }
        .no-image {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            font-size: 64px;
        }
        .product-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            z-index: 1;
        }
        .badge-deal {
            background: #ff9800;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
        }
        .product-info {
            padding: 20px;
        }
        .product-category {
            font-size: 12px;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .product-info h3 {
            font-size: 18px;
            margin: 10px 0;
            color: #2d1810;
        }
        .product-price {
            margin: 10px 0;
        }
        .current-price {
            font-size: 20px;
            font-weight: bold;
            color: #FF6347;
        }
        .old-price {
            font-size: 14px;
            color: #999;
            text-decoration: line-through;
            margin-left: 10px;
        }
        .product-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        .btn-cart {
            flex: 1;
            padding: 10px;
            background: linear-gradient(135deg, #8B0000, #FF6347);
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 25px;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-cart:hover {
            transform: translateY(-2px);
        }
        .btn-remove {
            padding: 10px 15px;
            background: #f44336;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 25px;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-remove:hover {
            background: #d32f2f;
        }
        .remove-icon {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(0,0,0,0.5);
            color: white;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            z-index: 1;
            transition: all 0.3s;
        }
        .remove-icon:hover {
            background: #f44336;
            transform: scale(1.1);
        }
        @media (max-width: 768px) {
            .wishlist-container {
                margin: 30px auto;
            }
            .products-grid {
                gap: 20px;
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
                <a href="shop.php">Menu</a>
                <a href="deals.php">Deals</a>
                <?php if(isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php">My Account</a>
                <a href="wishlist.php" class="active"><i class="fas fa-heart"></i> Wishlist <span class="badge"><?php echo $wishlist_count; ?></span></a>
                <a href="cart.php"><i class="fas fa-shopping-bag"></i> Cart <span class="badge"><?php echo getCartCount($_SESSION['user_id']); ?></span></a>
                <a href="logout.php">Logout</a>
                <?php else: ?>
                <a href="signin.php">Sign In</a>
                <a href="register.php">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="wishlist-container">
        <div class="wishlist-header">
            <h1><i class="fas fa-heart" style="color: #FF6347;"></i> My Wishlist</h1>
            <p>Save your favorite items and order them later</p>
        </div>
        
        <?php if($message): ?>
        <div class="message">
            <i class="fas fa-check-circle"></i> <?php echo $message; ?>
        </div>
        <?php endif; ?>
        
        <?php if($wishlist_count == 0): ?>
        <div class="empty-wishlist">
            <i class="fas fa-heart"></i>
            <h3>Your wishlist is empty!</h3>
            <p>Save your favorite menu items here by clicking the heart icon.</p>
            <a href="shop.php" class="btn-primary" style="display: inline-block; margin-top: 20px;">Browse Menu</a>
        </div>
        <?php else: ?>
        <div class="products-grid">
            <?php while($item = mysqli_fetch_assoc($wishlist)): 
                $image_path = 'uploads/products/' . $item['main_image'];
            ?>
            <div class="product-card">
                <a href="?remove=<?php echo $item['id']; ?>" class="remove-icon" onclick="return confirm('Remove from wishlist?')">
                    <i class="fas fa-times"></i>
                </a>
                <div class="product-badge">
                    <?php if($item['type'] == 'deal'): ?>
                    <span class="badge-deal">🔥 Deal</span>
                    <?php endif; ?>
                </div>
                <div class="product-image">
                    <?php if(file_exists($image_path) && $item['main_image'] != 'default-product.jpg'): ?>
                    <img src="<?php echo $image_path; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                    <?php else: ?>
                    <div class="no-image">🍟</div>
                    <?php endif; ?>
                </div>
                <div class="product-info">
                    <span class="product-category"><?php echo $item['icon']; ?> <?php echo $item['category_name']; ?></span>
                    <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                    <p style="font-size: 12px; color: #999; margin: 5px 0;"><?php echo htmlspecialchars(substr($item['short_description'], 0, 60)); ?></p>
                    <div class="product-price">
                        <span class="current-price">PKR <?php echo number_format($item['price']); ?></span>
                        <?php if($item['compare_price'] > 0): ?>
                        <span class="old-price">PKR <?php echo number_format($item['compare_price']); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="product-actions">
                        <a href="?add_to_cart=<?php echo $item['id']; ?>" class="btn-cart" onclick="return confirm('Add to cart? This item will be removed from wishlist.')">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </a>
                        <a href="product_details.php?id=<?php echo $item['id']; ?>" class="btn-cart" style="background: #666;">
                            <i class="fas fa-eye"></i> View
                        </a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php endif; ?>
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