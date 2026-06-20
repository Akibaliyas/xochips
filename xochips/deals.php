<?php
session_start();
include "database.php";

$deals = mysqli_query($conn, "SELECT p.*, c.name as category_name, c.icon 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE p.status='active' AND p.type='deal' 
    ORDER BY p.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Family Deals - XO Chinese Chips</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .deals-hero {
            background: linear-gradient(135deg, #8B0000, #FF6347);
            text-align: center;
            padding: 80px 20px;
            color: white;
        }
        .deals-hero h1 {
            font-size: 48px;
            margin-bottom: 20px;
        }
        .deals-hero p {
            font-size: 18px;
            opacity: 0.9;
        }
        .deals-container {
            max-width: 1200px;
            margin: 60px auto;
            padding: 0 20px;
        }
        .deal-card-large {
            background: white;
            border-radius: 30px;
            overflow: hidden;
            display: grid;
            grid-template-columns: 1fr 1fr;
            margin-bottom: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .deal-card-large:hover {
            transform: translateY(-5px);
        }
        .deal-image-large {
            height: 100%;
            min-height: 350px;
            background-size: cover;
            background-position: center;
        }
        .deal-image-large img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .deal-content-large {
            padding: 40px;
        }
        .deal-content-large h2 {
            font-size: 32px;
            margin-bottom: 15px;
            color: #8B0000;
        }
        .deal-description {
            color: #666;
            line-height: 1.6;
            margin: 20px 0;
        }
        .deal-includes {
            background: #fff5f0;
            padding: 15px;
            border-radius: 15px;
            margin: 20px 0;
        }
        .deal-includes h4 {
            margin-bottom: 10px;
            color: #8B0000;
        }
        .deal-includes ul {
            list-style: none;
            padding-left: 0;
        }
        .deal-includes li {
            padding: 5px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .deal-includes li i {
            color: #FF6347;
        }
        .deal-price-large {
            font-size: 28px;
            font-weight: bold;
            color: #FF6347;
            margin: 20px 0;
        }
        .deal-price-large .old {
            font-size: 18px;
            color: #999;
            text-decoration: line-through;
            margin-left: 10px;
            font-weight: normal;
        }
        .btn-order-deal {
            display: inline-block;
            padding: 15px 40px;
            background: linear-gradient(135deg, #8B0000, #FF6347);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-order-deal:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 25px rgba(255,99,71,0.3);
        }
        .savings-badge {
            display: inline-block;
            background: #ff9800;
            color: white;
            padding: 5px 15px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: bold;
            margin-left: 15px;
        }
        @media (max-width: 768px) {
            .deal-card-large {
                grid-template-columns: 1fr;
            }
            .deal-image-large {
                min-height: 250px;
            }
            .deal-content-large {
                padding: 25px;
            }
            .deals-hero h1 {
                font-size: 32px;
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
                <a href="deals.php" class="active">Deals</a>
                <?php if(isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php">My Account</a>
                <a href="wishlist.php"><i class="fas fa-heart"></i> Wishlist</a>
                <a href="cart.php"><i class="fas fa-shopping-bag"></i> Cart</a>
                <a href="logout.php">Logout</a>
                <?php else: ?>
                <a href="signin.php">Sign In</a>
                <a href="register.php">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="deals-hero">
        <h1>🔥 Family Deals 🔥</h1>
        <p>Perfect for sharing with friends and family. Save big on our signature combos!</p>
    </div>

    <div class="deals-container">
        <?php if(mysqli_num_rows($deals) == 0): ?>
        <div style="text-align: center; padding: 60px;">
            <i class="fas fa-tag" style="font-size: 64px; color: #ddd;"></i>
            <h3>No deals available right now</h3>
            <p>Check back soon for exciting offers!</p>
            <a href="shop.php" class="btn-primary" style="display: inline-block; margin-top: 20px;">Browse Menu</a>
        </div>
        <?php else: ?>
        <?php while($deal = mysqli_fetch_assoc($deals)): 
            $image_path = 'uploads/products/' . $deal['main_image'];
            $savings = $deal['compare_price'] - $deal['price'];
            $savings_percent = round(($savings / $deal['compare_price']) * 100);
        ?>
        <div class="deal-card-large">
            <div class="deal-image-large">
                <?php if(file_exists($image_path) && $deal['main_image'] != 'default-product.jpg'): ?>
                <img src="<?php echo $image_path; ?>" alt="<?php echo htmlspecialchars($deal['name']); ?>">
                <?php else: ?>
                <div style="height:100%; background: linear-gradient(135deg, #8B0000, #FF6347); display: flex; align-items: center; justify-content: center; font-size: 80px;">🍽️</div>
                <?php endif; ?>
            </div>
            <div class="deal-content-large">
                <h2><?php echo htmlspecialchars($deal['name']); ?>
                    <span class="savings-badge">Save <?php echo $savings_percent; ?>%</span>
                </h2>
                <p class="deal-description"><?php echo htmlspecialchars($deal['description']); ?></p>
                
                <div class="deal-includes">
                    <h4><i class="fas fa-check-circle"></i> This Deal Includes:</h4>
                    <ul>
                        <?php 
                        $includes = explode(',', $deal['short_description']);
                        foreach($includes as $item): 
                            if(trim($item)):
                        ?>
                        <li><i class="fas fa-utensils"></i> <?php echo htmlspecialchars(trim($item)); ?></li>
                        <?php endif; endforeach; ?>
                    </ul>
                </div>
                
                <div class="deal-price-large">
                    PKR <?php echo number_format($deal['price']); ?>
                    <span class="old">PKR <?php echo number_format($deal['compare_price']); ?></span>
                </div>
                
                <a href="product_details.php?id=<?php echo $deal['id']; ?>" class="btn-order-deal">
                    Order This Deal <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
        <?php endwhile; ?>
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
            <p>&copy; <?php echo date('Y'); ?> XO Chinese Chips. All rights reserved.</p>
        </div>
    </div>
</body>
</html>