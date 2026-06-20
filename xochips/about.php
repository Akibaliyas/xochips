<?php
session_start();
include "database.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - XO Chinese Chips</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .about-hero {
            background: linear-gradient(135deg, #8B0000, #FF6347);
            text-align: center;
            padding: 80px 20px;
            color: white;
        }
        .about-hero h1 {
            font-size: 48px;
            margin-bottom: 20px;
        }
        .about-container {
            max-width: 1200px;
            margin: 60px auto;
            padding: 0 20px;
        }
        .about-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            align-items: center;
            margin-bottom: 60px;
        }
        .about-grid h2 {
            color: #8B0000;
            font-size: 32px;
            margin-bottom: 20px;
        }
        .about-grid p {
            color: #666;
            line-height: 1.8;
            margin-bottom: 15px;
        }
        .about-image {
            background: #fff5f0;
            border-radius: 30px;
            padding: 40px;
            text-align: center;
            font-size: 120px;
        }
        .values-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin: 40px 0;
        }
        .value-card {
            background: white;
            padding: 30px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        .value-card i {
            font-size: 48px;
            color: #FF6347;
            margin-bottom: 15px;
        }
        .value-card h3 {
            color: #8B0000;
            margin-bottom: 10px;
        }
        .value-card p {
            color: #666;
            font-size: 14px;
        }

        /* Chef Section Styles */
        .chef-section {
            background: linear-gradient(135deg, #fff5f0, #ffe8e0);
            border-radius: 30px;
            padding: 60px 40px;
            margin: 60px 0 40px;
            position: relative;
            overflow: hidden;
        }
        .chef-section::before {
            content: '👨‍🍳';
            position: absolute;
            right: -50px;
            bottom: -50px;
            font-size: 300px;
            opacity: 0.1;
            transform: rotate(-10deg);
        }
        .chef-grid {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 50px;
            align-items: center;
            position: relative;
            z-index: 1;
        }
        .chef-photo-container {
            position: relative;
            text-align: center;
        }
        .chef-photo {
            width: 100%;
            max-width: 300px;
            aspect-ratio: 1/1;
            border-radius: 50%;
            object-fit: cover;
            border: 8px solid white;
            box-shadow: 0 10px 40px rgba(139,0,0,0.2);
            transition: all 0.3s ease;
            background: #fdf0ed;
        }
        .chef-photo:hover {
            transform: scale(1.02);
            box-shadow: 0 15px 50px rgba(255,99,71,0.3);
        }
        .chef-photo-placeholder {
            width: 100%;
            max-width: 300px;
            aspect-ratio: 1/1;
            border-radius: 50%;
            background: linear-gradient(135deg, #8B0000, #FF6347);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 120px;
            border: 8px solid white;
            box-shadow: 0 10px 40px rgba(139,0,0,0.2);
            margin: 0 auto;
        }
        .chef-badge {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: #FF6347;
            color: white;
            padding: 8px 16px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 600;
            box-shadow: 0 5px 15px rgba(255,99,71,0.3);
        }
        .chef-info h2 {
            color: #8B0000;
            font-size: 36px;
            margin-bottom: 5px;
        }
        .chef-title {
            color: #FF6347;
            font-size: 18px;
            font-weight: 500;
            margin-bottom: 20px;
            letter-spacing: 1px;
        }
        .chef-info p {
            color: #555;
            line-height: 1.8;
            margin-bottom: 15px;
        }
        .chef-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 25px;
        }
        .chef-stat {
            background: white;
            padding: 15px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 3px 15px rgba(0,0,0,0.05);
        }
        .chef-stat-number {
            font-size: 28px;
            font-weight: bold;
            color: #8B0000;
            display: block;
        }
        .chef-stat-label {
            font-size: 13px;
            color: #666;
        }
        .chef-social {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        .chef-social a {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #8B0000;
            text-decoration: none;
            font-size: 18px;
            transition: all 0.3s ease;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
        }
        .chef-social a:hover {
            background: #8B0000;
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(139,0,0,0.2);
        }

        @media (max-width: 768px) {
            .about-grid {
                grid-template-columns: 1fr;
            }
            .about-hero h1 {
                font-size: 32px;
            }
            .chef-grid {
                grid-template-columns: 1fr;
                text-align: center;
            }
            .chef-photo, .chef-photo-placeholder {
                max-width: 220px;
            }
            .chef-stats {
                grid-template-columns: 1fr 1fr;
            }
            .chef-section {
                padding: 40px 20px;
            }
            .chef-social {
                justify-content: center;
            }
        }
        @media (max-width: 480px) {
            .chef-stats {
                grid-template-columns: 1fr;
            }
            .chef-photo, .chef-photo-placeholder {
                max-width: 180px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo">
                <span class="logo-text">XO Chinese Chips</span>
            </div>
            <div class="nav">
                <a href="index.php">Home</a>
                <a href="shop.php">Menu</a>
                <a href="deals.php">Deals</a>
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

    <div class="about-hero">
        <h1>About XO Chinese Chips</h1>
        <p>Where tradition meets innovation in every wok-tossed bite</p>
    </div>

    <div class="about-container">
        <div class="about-grid">
            <div>
                <h2>Our Story</h2>
                <p>Founded in 2023, XO Chinese Chips was born from a simple idea: elevate the humble french fry into a culinary experience. Our founders, passionate about Chinese cuisine and street food culture, combined their expertise to create a unique fusion concept.</p>
                <p>We believe that great food starts with quality ingredients. Every dish we serve is prepared fresh, wok-tossed with care, and finished with our signature XO sauce - a secret recipe that took months to perfect.</p>
                <p>Today, we're proud to serve thousands of satisfied customers across Lahore, bringing the authentic taste of wok-tossed perfection right to your door.</p>
            </div>
            <div class="about-image">🍟</div>
        </div>

        <!-- ====== CHEF SECTION ====== -->
        <div class="chef-section">
            <div class="chef-grid">
                <div class="chef-photo-container">
                    <?php
                    // Chef photo path - replace 'chef-profile.jpg' with your actual chef image filename
                    $chef_photo_path = 'uploads/chef-profile.jpg';
                    $default_chef_name = 'Chef Wang';
                    $default_chef_title = 'Executive Chef & Co-Founder';
                    ?>
                    
                    <?php if(file_exists($chef_photo_path)): ?>
                        <img src="<?php echo $chef_photo_path; ?>" alt="Chef <?php echo $default_chef_name; ?>" class="chef-photo">
                    <?php else: ?>
                        <div class="chef-photo-placeholder">
                            👨‍🍳
                        </div>
                    <?php endif; ?>
                    <div class="chef-badge">
                        <i class="fas fa-star"></i> Michelin Trained
                    </div>
                </div>
                <div class="chef-info">
                    <h2>Chef <?php echo $default_chef_name; ?></h2>
                    <div class="chef-title"><?php echo $default_chef_title; ?></div>
                    <p>With over 15 years of experience in Michelin-starred kitchens across Asia and Europe, Chef Wang brings an unparalleled level of expertise to every dish at XO Chinese Chips.</p>
                    <p>His philosophy is simple: <em>"Great food starts with respect for ingredients and ends with joy on the plate."</em> Chef Wang personally crafts each of our signature sauces and trains every cook in the art of wok tossing.</p>
                    <p>From the fiery Dynamite Sauce to the umami-packed XO Spice Mix, every flavor you taste has been perfected through years of dedication to the craft.</p>
                    
                    <div class="chef-stats">
                        <div class="chef-stat">
                            <span class="chef-stat-number">15+</span>
                            <span class="chef-stat-label">Years Experience</span>
                        </div>
                        <div class="chef-stat">
                            <span class="chef-stat-number">3</span>
                            <span class="chef-stat-label">Michelin Kitchens</span>
                        </div>
                        <div class="chef-stat">
                            <span class="chef-stat-number">7</span>
                            <span class="chef-stat-label">Signature Recipes</span>
                        </div>
                    </div>
                    
                    <div class="chef-social">
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
        </div>
        <!-- ====== END CHEF SECTION ====== -->

        <h2 style="text-align: center; color: #8B0000; margin-bottom: 30px;">Our Core Values</h2>
        <div class="values-grid">
            <div class="value-card">
                <i class="fas fa-utensils"></i>
                <h3>Quality First</h3>
                <p>We use only the freshest ingredients and premium cuts to ensure every bite is perfect.</p>
            </div>
            <div class="value-card">
                <i class="fas fa-fire"></i>
                <h3>Wok-Fresh</h3>
                <p>Everything is cooked to order in our custom woks, ensuring maximum flavor and texture.</p>
            </div>
            <div class="value-card">
                <i class="fas fa-heart"></i>
                <h3>Made with Love</h3>
                <p>Our recipes are crafted with passion, combining traditional techniques with modern flair.</p>
            </div>
            <div class="value-card">
                <i class="fas fa-truck"></i>
                <h3>Fast Delivery</h3>
                <p>We deliver hot, fresh food within 45 minutes so you can enjoy our food at its best.</p>
            </div>
        </div>

        <div style="background: #fff5f0; border-radius: 20px; padding: 40px; text-align: center; margin-top: 40px;">
            <h3 style="color: #8B0000; font-size: 28px;">Come Taste the Difference!</h3>
            <p style="color: #666; margin: 15px 0;">Ready to experience wok-tossed perfection? Order now and taste what makes XO Chinese Chips special.</p>
            <a href="shop.php" class="btn-primary" style="display: inline-block; margin-top: 10px;">Explore Our Menu</a>
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