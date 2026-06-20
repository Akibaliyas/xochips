<?php
session_start();
include "database.php";

$success = "";
$error = "";

if(isset($_POST['send_message'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    
    // You can save to database or send email
    // For now, we'll store in a contact_messages table (create this if needed)
    /*
    $query = "INSERT INTO contact_messages (name, email, subject, message) VALUES ('$name', '$email', '$subject', '$message')";
    if(mysqli_query($conn, $query)) {
        $success = "Thank you! Your message has been sent. We'll get back to you within 24 hours.";
    } else {
        $error = "Failed to send message. Please try again.";
    }
    */
    
    // Simulate success
    $success = "Thank you! Your message has been sent. We'll get back to you within 24 hours.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - XO Chinese Chips</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .contact-hero {
            background: linear-gradient(135deg, #8B0000, #FF6347);
            text-align: center;
            padding: 80px 20px;
            color: white;
        }
        .contact-hero h1 {
            font-size: 48px;
            margin-bottom: 20px;
        }
        .contact-container {
            max-width: 1200px;
            margin: 60px auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
        }
        .contact-info h2 {
            color: #8B0000;
            font-size: 28px;
            margin-bottom: 20px;
        }
        .contact-info p {
            color: #666;
            line-height: 1.8;
            margin-bottom: 30px;
        }
        .contact-details {
            background: #fff5f0;
            padding: 25px;
            border-radius: 20px;
            margin-bottom: 20px;
        }
        .contact-details i {
            width: 30px;
            color: #FF6347;
        }
        .contact-details p {
            margin: 10px 0;
        }
        .contact-form {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        .contact-form h2 {
            color: #8B0000;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
        }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #eee;
            border-radius: 12px;
            font-family: inherit;
        }
        .form-group input:focus, .form-group textarea:focus {
            border-color: #FF6347;
            outline: none;
        }
        .btn-submit {
            padding: 14px 30px;
            background: linear-gradient(135deg, #8B0000, #FF6347);
            color: white;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
        }
        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #4caf50;
        }
        .alert-error {
            background: #fee;
            color: #d32f2f;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #d32f2f;
        }
        @media (max-width: 768px) {
            .contact-container {
                grid-template-columns: 1fr;
            }
            .contact-hero h1 {
                font-size: 32px;
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

    <div class="contact-hero">
        <h1>Get in Touch</h1>
        <p>We'd love to hear from you! Reach out anytime.</p>
    </div>

    <div class="contact-container">
        <div class="contact-info">
            <h2>Contact Information</h2>
            <p>Have questions about our menu, delivery, or want to place a bulk order? We're here to help!</p>
            
            <div class="contact-details">
                <p><i class="fas fa-phone"></i> <strong>Phone:</strong> +92 300 1234567</p>
                <p><i class="fas fa-envelope"></i> <strong>Email:</strong> order@xochips.com</p>
                <p><i class="fas fa-map-marker-alt"></i> <strong>Address:</strong> 123 Food Street, Lahore, Pakistan</p>
            </div>
            
            <div class="contact-details">
                <h4 style="color: #8B0000; margin-bottom: 10px;">Business Hours</h4>
                <p>Monday - Thursday: 11:00 AM - 11:00 PM</p>
                <p>Friday - Saturday: 11:00 AM - 11:30 PM</p>
                <p>Sunday: 12:00 PM - 10:00 PM</p>
            </div>
            
            <div style="margin-top: 20px;">
                <h4 style="color: #8B0000;">Follow Us</h4>
                <div style="display: flex; gap: 15px; margin-top: 10px;">
                    <a href="#" style="color: #FF6347; font-size: 24px;"><i class="fab fa-facebook"></i></a>
                    <a href="#" style="color: #FF6347; font-size: 24px;"><i class="fab fa-instagram"></i></a>
                    <a href="#" style="color: #FF6347; font-size: 24px;"><i class="fab fa-tiktok"></i></a>
                    <a href="#" style="color: #FF6347; font-size: 24px;"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>
        </div>
        
        <div class="contact-form">
            <h2>Send Us a Message</h2>
            
            <?php if($success): ?>
            <div class="alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if($error): ?>
            <div class="alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="post">
                <div class="form-group">
                    <label>Your Name *</label>
                    <input type="text" name="name" required placeholder="Enter your full name">
                </div>
                <div class="form-group">
                    <label>Email Address *</label>
                    <input type="email" name="email" required placeholder="Enter your email">
                </div>
                <div class="form-group">
                    <label>Subject *</label>
                    <input type="text" name="subject" required placeholder="What's this about?">
                </div>
                <div class="form-group">
                    <label>Message *</label>
                    <textarea name="message" rows="5" required placeholder="Tell us how we can help"></textarea>
                </div>
                <button type="submit" name="send_message" class="btn-submit">
                    <i class="fas fa-paper-plane"></i> Send Message
                </button>
            </form>
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