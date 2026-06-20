<?php
session_start();
include "database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Update quantity
if(isset($_POST['update_cart'])) {
    foreach($_POST['quantity'] as $cart_id => $qty) {
        $qty = intval($qty);
        if($qty <= 0) {
            mysqli_query($conn, "DELETE FROM cart WHERE id='$cart_id' AND user_id='$user_id'");
        } else {
            mysqli_query($conn, "UPDATE cart SET quantity='$qty' WHERE id='$cart_id' AND user_id='$user_id'");
        }
    }
    header("Location: cart.php");
    exit();
}

// Remove item
if(isset($_GET['remove'])) {
    $cart_id = $_GET['remove'];
    mysqli_query($conn, "DELETE FROM cart WHERE id='$cart_id' AND user_id='$user_id'");
    header("Location: cart.php");
    exit();
}

// Get cart items
$cart_items = mysqli_query($conn, "SELECT c.*, p.name, p.price, p.main_image, p.slug, p.type 
    FROM cart c 
    JOIN products p ON c.product_id = p.id 
    WHERE c.user_id='$user_id'");

$subtotal = 0;
$delivery_charge = 150;
$tax_rate = 0.05; // 5% tax
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - XO Chinese Chips</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .cart-container {
            max-width: 1200px;
            margin: 60px auto;
            padding: 0 20px;
        }
        .cart-header {
            text-align: center;
            margin-bottom: 40px;
        }
        .cart-header h1 {
            font-size: 36px;
            color: #8B0000;
        }
        .cart-table {
            width: 100%;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        .cart-table th, .cart-table td {
            padding: 20px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .cart-table th {
            background: #fff5f0;
            font-weight: 600;
            color: #8B0000;
        }
        .cart-product {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .cart-product img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 15px;
        }
        .quantity-input {
            width: 70px;
            padding: 8px;
            text-align: center;
            border: 2px solid #eee;
            border-radius: 10px;
            font-weight: 500;
        }
        .cart-summary {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-top: 30px;
            max-width: 450px;
            margin-left: auto;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }
        .summary-total {
            font-size: 22px;
            font-weight: bold;
            color: #FF6347;
            border-bottom: none;
            padding-top: 15px;
        }
        .btn-checkout {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #8B0000, #FF6347);
            color: white;
            border: none;
            border-radius: 50px;
            font-size: 18px;
            font-weight: 600;
            margin-top: 20px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-checkout:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(255,99,71,0.3);
        }
        .btn-update {
            background: #666;
            color: white;
            padding: 10px 25px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
        }
        .empty-cart {
            text-align: center;
            padding: 80px;
            background: white;
            border-radius: 20px;
        }
        .empty-cart i {
            font-size: 80px;
            color: #ddd;
        }
        @media (max-width: 768px) {
            .cart-table th, .cart-table td {
                padding: 12px;
            }
            .cart-product {
                flex-direction: column;
                text-align: center;
            }
            .cart-summary {
                max-width: 100%;
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
                <a href="wishlist.php"><i class="fas fa-heart"></i> Wishlist</a>
                <a href="cart.php" class="active"><i class="fas fa-shopping-bag"></i> Cart</a>
                <a href="logout.php">Logout</a>
                <?php else: ?>
                <a href="signin.php">Sign In</a>
                <a href="register.php">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="cart-container">
        <div class="cart-header">
            <h1><i class="fas fa-shopping-bag"></i> Your Cart</h1>
            <p>Review your order before checkout</p>
        </div>
        
        <?php if(mysqli_num_rows($cart_items) == 0): ?>
        <div class="empty-cart">
            <i class="fas fa-shopping-bag"></i>
            <h3>Your cart is empty!</h3>
            <p>Looks like you haven't added any items yet.</p>
            <a href="shop.php" class="btn-primary" style="display: inline-block; margin-top: 20px;">Browse Menu</a>
        </div>
        <?php else: ?>
        
        <form method="post">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($item = mysqli_fetch_assoc($cart_items)): 
                        $total = $item['price'] * $item['quantity'];
                        $subtotal += $total;
                        $image_path = 'uploads/products/' . $item['main_image'];
                    ?>
                    <tr>
                        <td>
                            <div class="cart-product">
                                <?php if(file_exists($image_path) && $item['main_image'] != 'default-product.jpg'): ?>
                                <img src="<?php echo $image_path; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                <?php else: ?>
                                <div style="width:80px;height:80px;background:#fff5f0;border-radius:15px;display:flex;align-items:center;justify-content:center;font-size:32px;">🍟</div>
                                <?php endif; ?>
                                <div>
                                    <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                    <?php if($item['type'] == 'deal'): ?>
                                    <span style="display: inline-block; background: #ff9800; color: white; font-size: 10px; padding: 2px 8px; border-radius: 10px; margin-left: 8px;">Deal</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td>PKR <?php echo number_format($item['price']); ?></td>
                        <td>
                            <input type="number" name="quantity[<?php echo $item['id']; ?>]" class="quantity-input" value="<?php echo $item['quantity']; ?>" min="1" max="20">
                        </td>
                        <td>PKR <?php echo number_format($total); ?></td
                        <td>
                            <a href="?remove=<?php echo $item['id']; ?>" onclick="return confirm('Remove item?')" style="color: #FF6347; font-size: 20px;">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            
            <div style="display: flex; justify-content: space-between; margin-top: 20px;">
                <a href="shop.php" class="btn-secondary" style="padding: 12px 25px;">Continue Shopping</a>
                <button type="submit" name="update_cart" class="btn-update">Update Cart</button>
            </div>
        </form>
        
        <?php 
        $tax = $subtotal * $tax_rate;
        $grand_total = $subtotal + $delivery_charge + $tax;
        ?>
        
        <div class="cart-summary">
            <h3 style="margin-bottom: 20px;">Order Summary</h3>
            <div class="summary-row">
                <span>Subtotal:</span>
                <span>PKR <?php echo number_format($subtotal); ?></span>
            </div>
            <div class="summary-row">
                <span>Delivery Charge:</span>
                <span>PKR <?php echo number_format($delivery_charge); ?></span>
            </div>
            <div class="summary-row">
                <span>Tax (5%):</span>
                <span>PKR <?php echo number_format($tax); ?></span>
            </div>
            <div class="summary-row summary-total">
                <span>Total:</span>
                <span>PKR <?php echo number_format($grand_total); ?></span>
            </div>
            <a href="checkout.php" class="btn-checkout">Proceed to Checkout <i class="fas fa-arrow-right"></i></a>
        </div>
        
        <?php endif; ?>
    </div>

    <div class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>🍟 XO Chinese Chips</h3>
                <p>Authentic wok-tossed fries and Chinese fusion delivered to your door.</p>
            </div>
            <div class="footer-section">
                <h4>Quick Links</h4>
                <a href="shop.php">Full Menu</a>
                <a href="deals.php">Family Deals</a>
                <a href="contact.php">Contact</a>
            </div>
            <div class="footer-section">
                <h4>Contact</h4>
                <p><i class="fas fa-phone"></i> +92 300 1234567</p>
                <p><i class="fas fa-envelope"></i> order@xochips.com</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> XO Chinese Chips. All rights reserved.</p>
        </div>
    </div>
</body>
</html>