<?php
session_start();
include "database.php";

if(!isset($_GET['id'])) {
    header("Location: shop.php");
    exit();
}

$product_id = intval($_GET['id']);
$product = mysqli_fetch_assoc(mysqli_query($conn, "SELECT p.*, c.name as category_name, c.slug as category_slug, c.icon 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE p.id='$product_id' AND p.status='active'"));

if(!$product) {
    header("Location: shop.php");
    exit();
}

// Get additional images
$additional_images = !empty($product['additional_images']) ? explode(',', $product['additional_images']) : [];

// Get related products (same category)
$related = mysqli_query($conn, "SELECT p.*, c.name as category_name, c.icon 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE p.category_id='{$product['category_id']}' AND p.id != '$product_id' AND p.status='active' 
    LIMIT 4");

// Add to cart
if(isset($_POST['add_to_cart']) && isset($_SESSION['user_id'])) {
    $quantity = intval($_POST['quantity']);
    $user_id = $_SESSION['user_id'];
    
    $check_cart = mysqli_query($conn, "SELECT id, quantity FROM cart WHERE user_id='$user_id' AND product_id='$product_id'");
    
    if(mysqli_num_rows($check_cart) > 0) {
        $cart_item = mysqli_fetch_assoc($check_cart);
        $new_qty = $cart_item['quantity'] + $quantity;
        mysqli_query($conn, "UPDATE cart SET quantity='$new_qty' WHERE id='{$cart_item['id']}'");
    } else {
        mysqli_query($conn, "INSERT INTO cart (user_id, product_id, quantity) VALUES ('$user_id', '$product_id', '$quantity')");
    }
    $cart_success = "Product added to cart!";
}

// Check if in wishlist
$in_wishlist = false;
if(isset($_SESSION['user_id'])) {
    $check_wishlist = mysqli_query($conn, "SELECT id FROM wishlist WHERE user_id='{$_SESSION['user_id']}' AND product_id='$product_id'");
    $in_wishlist = mysqli_num_rows($check_wishlist) > 0;
}

$savings_percent = 0;
if($product['compare_price'] > 0 && $product['compare_price'] > $product['price']) {
    $savings_percent = round((($product['compare_price'] - $product['price']) / $product['compare_price']) * 100);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - XO Chinese Chips</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .product-detail {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            max-width: 1200px;
            margin: 60px auto;
            padding: 0 20px;
        }
        .product-gallery {
            position: sticky;
            top: 100px;
        }
        .main-image {
            width: 100%;
            height: 450px;
            object-fit: cover;
            border-radius: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .thumbnail-gallery {
            display: flex;
            gap: 12px;
            margin-top: 15px;
            overflow-x: auto;
            padding-bottom: 5px;
        }
        .thumbnail {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 12px;
            cursor: pointer;
            border: 3px solid transparent;
            transition: all 0.3s;
        }
        .thumbnail.active, .thumbnail:hover {
            border-color: #FF6347;
            transform: scale(1.05);
        }
        .product-info h1 {
            font-size: 32px;
            margin-bottom: 15px;
            color: #8B0000;
        }
        .product-category-badge {
            display: inline-block;
            background: #fff5f0;
            padding: 5px 15px;
            border-radius: 25px;
            font-size: 14px;
            margin-bottom: 15px;
        }
        .product-price {
            font-size: 32px;
            color: #FF6347;
            font-weight: bold;
            margin: 20px 0;
        }
        .old-price {
            font-size: 20px;
            color: #999;
            text-decoration: line-through;
            margin-left: 15px;
            font-weight: normal;
        }
        .savings-badge {
            display: inline-block;
            background: #4caf50;
            color: white;
            padding: 5px 12px;
            border-radius: 25px;
            font-size: 14px;
            margin-left: 15px;
        }
        .product-description {
            margin: 25px 0;
            line-height: 1.8;
            color: #555;
        }
        .quantity-selector {
            display: flex;
            align-items: center;
            gap: 15px;
            margin: 25px 0;
        }
        .quantity-btn {
            width: 45px;
            height: 45px;
            border: 2px solid #eee;
            background: white;
            border-radius: 50%;
            cursor: pointer;
            font-size: 20px;
            font-weight: bold;
            transition: all 0.3s;
        }
        .quantity-btn:hover {
            background: #FF6347;
            color: white;
            border-color: #FF6347;
        }
        .quantity-input {
            width: 80px;
            text-align: center;
            padding: 12px;
            border: 2px solid #eee;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 500;
        }
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 25px;
        }
        .btn-cart {
            flex: 1;
            padding: 16px;
            background: linear-gradient(135deg, #8B0000, #FF6347);
            color: white;
            border: none;
            border-radius: 50px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-cart:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(255,99,71,0.3);
        }
        .btn-wishlist {
            padding: 16px 30px;
            background: white;
            border: 2px solid #FF6347;
            color: #FF6347;
            border-radius: 50px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-wishlist:hover {
            background: #FF6347;
            color: white;
        }
        .btn-wishlist.active {
            background: #FF6347;
            color: white;
        }
        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 12px 20px;
            border-radius: 12px;
            margin: 15px 0;
            border-left: 4px solid #4caf50;
        }
        .related-section {
            max-width: 1200px;
            margin: 60px auto;
            padding: 0 20px;
        }
        .related-section h2 {
            font-size: 28px;
            margin-bottom: 30px;
            text-align: center;
            color: #8B0000;
        }
        @media (max-width: 992px) {
            .product-detail {
                grid-template-columns: 1fr;
                gap: 30px;
            }
            .product-gallery {
                position: static;
            }
            .main-image {
                height: 350px;
            }
        }
        @media (max-width: 768px) {
            .product-detail {
                margin: 30px auto;
            }
            .product-info h1 {
                font-size: 24px;
            }
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo">
                <span class="logo-icon">🍟</span>
                <span class="logo-text">XO Chinese Chips</span>
            </div>
            <div class="nav">
                <a href="index.php">Home</a>
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

    <div class="product-detail">
        <div class="product-gallery">
            <?php 
            $main_image_path = 'uploads/products/' . $product['main_image'];
            if(file_exists($main_image_path) && $product['main_image'] != 'default-product.jpg'): 
            ?>
            <img id="mainImage" src="<?php echo $main_image_path; ?>" class="main-image" alt="<?php echo htmlspecialchars($product['name']); ?>">
            <?php else: ?>
            <div class="main-image" style="background: linear-gradient(135deg, #8B0000, #FF6347); display: flex; align-items: center; justify-content: center; font-size: 100px;">🍟</div>
            <?php endif; ?>
            
            <?php if(!empty($additional_images) || ($product['main_image'] != 'default-product.jpg')): ?>
            <div class="thumbnail-gallery">
                <?php if(file_exists($main_image_path) && $product['main_image'] != 'default-product.jpg'): ?>
                <img src="<?php echo $main_image_path; ?>" class="thumbnail active" onclick="changeImage(this.src)">
                <?php endif; ?>
                <?php foreach($additional_images as $img): 
                    $img_path = 'uploads/products/' . $img;
                    if(file_exists($img_path)):
                ?>
                <img src="<?php echo $img_path; ?>" class="thumbnail" onclick="changeImage(this.src)">
                <?php endif; endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="product-info">
            <span class="product-category-badge">
                <i class="fas fa-tag"></i> <?php echo $product['icon']; ?> <?php echo htmlspecialchars($product['category_name']); ?>
            </span>
            <h1><?php echo htmlspecialchars($product['name']); ?></h1>
            
            <div class="product-price">
                PKR <?php echo number_format($product['price']); ?>
                <?php if($product['compare_price'] > 0): ?>
                <span class="old-price">PKR <?php echo number_format($product['compare_price']); ?></span>
                <?php if($savings_percent > 0): ?>
                <span class="savings-badge">Save <?php echo $savings_percent; ?>%</span>
                <?php endif; ?>
                <?php endif; ?>
            </div>
            
            <div class="product-description">
                <strong>Description:</strong>
                <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            </div>
            
            <?php if($product['short_description']): ?>
            <div class="product-description" style="background: #fff5f0; padding: 15px; border-radius: 15px;">
                <strong><i class="fas fa-info-circle"></i> What's included:</strong>
                <p><?php echo nl2br(htmlspecialchars($product['short_description'])); ?></p>
            </div>
            <?php endif; ?>
            
            <?php if(isset($cart_success)): ?>
            <div class="alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $cart_success; ?>
            </div>
            <?php endif; ?>
            
            <form method="post">
                <div class="quantity-selector">
                    <button type="button" class="quantity-btn" onclick="changeQuantity(-1)">-</button>
                    <input type="number" name="quantity" id="quantity" class="quantity-input" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>">
                    <button type="button" class="quantity-btn" onclick="changeQuantity(1)">+</button>
                    <span style="color: #666; margin-left: 10px;"><i class="fas fa-box"></i> <?php echo $product['stock_quantity']; ?> in stock</span>
                </div>
                
                <div class="action-buttons">
                    <button type="submit" name="add_to_cart" class="btn-cart">
                        <i class="fas fa-shopping-cart"></i> Add to Cart
                    </button>
                    <a href="add_to_wishlist.php?id=<?php echo $product['id']; ?>" class="btn-wishlist <?php echo $in_wishlist ? 'active' : ''; ?>" onclick="return confirm('<?php echo $in_wishlist ? 'Remove from wishlist?' : 'Add to wishlist?'; ?>')">
                        <i class="fas fa-heart"></i> <?php echo $in_wishlist ? 'Saved' : 'Save'; ?>
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <?php if(mysqli_num_rows($related) > 0): ?>
    <div class="related-section">
        <h2><i class="fas fa-fire"></i> You May Also Like</h2>
        <div class="products-grid">
            <?php while($rel = mysqli_fetch_assoc($related)): 
                $rel_image = 'uploads/products/' . $rel['main_image'];
            ?>
            <div class="product-card">
                <div class="product-image">
                    <?php if(file_exists($rel_image) && $rel['main_image'] != 'default-product.jpg'): ?>
                    <img src="<?php echo $rel_image; ?>" alt="<?php echo htmlspecialchars($rel['name']); ?>">
                    <?php else: ?>
                    <div class="no-image">🍟</div>
                    <?php endif; ?>
                    <div class="product-actions">
                        <a href="quick_view.php?id=<?php echo $rel['id']; ?>" class="quick-view"><i class="fas fa-eye"></i></a>
                        <a href="add_to_wishlist.php?id=<?php echo $rel['id']; ?>" class="add-wishlist"><i class="fas fa-heart"></i></a>
                    </div>
                </div>
                <div class="product-info">
                    <span class="product-category"><?php echo $rel['icon']; ?> <?php echo $rel['category_name']; ?></span>
                    <h3><?php echo htmlspecialchars($rel['name']); ?></h3>
                    <div class="product-price">
                        <span class="current-price">PKR <?php echo number_format($rel['price']); ?></span>
                    </div>
                    <a href="product_details.php?id=<?php echo $rel['id']; ?>" class="btn-add-to-cart">View Details</a>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
    <?php endif; ?>
    
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
                <a href="#">About Us</a>
                <a href="#">Contact</a>
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
    
    <script>
        function changeImage(src) {
            document.getElementById('mainImage').src = src;
            document.querySelectorAll('.thumbnail').forEach(thumb => {
                thumb.classList.remove('active');
            });
            event.target.classList.add('active');
        }
        
        function changeQuantity(delta) {
            let qty = parseInt(document.getElementById('quantity').value);
            let max = <?php echo $product['stock_quantity']; ?>;
            qty = Math.max(1, Math.min(max, qty + delta));
            document.getElementById('quantity').value = qty;
        }
    </script>
</body>
</html>