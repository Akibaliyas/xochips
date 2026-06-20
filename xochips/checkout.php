<?php
session_start();
include "database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'"));

// Get cart items
$cart_items = mysqli_query($conn, "SELECT c.*, p.name, p.price 
    FROM cart c 
    JOIN products p ON c.product_id = p.id 
    WHERE c.user_id='$user_id'");

$subtotal = 0;
while($item = mysqli_fetch_assoc($cart_items)) {
    $subtotal += $item['price'] * $item['quantity'];
}
$delivery_charge = 150;
$tax = $subtotal * 0.05;
$total = $subtotal + $delivery_charge + $tax;

if(isset($_POST['place_order'])) {
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);
    $special_requests = mysqli_real_escape_string($conn, $_POST['special_requests']);
    
    $order_number = generateOrderNumber();
    
    $query = "INSERT INTO orders (order_number, user_id, total_amount, shipping_charge, grand_total, payment_method, delivery_address, delivery_city, delivery_phone, special_requests) 
              VALUES ('$order_number', '$user_id', '$subtotal', '$delivery_charge', '$total', '$payment_method', '$address', '$city', '$phone', '$special_requests')";
    
    if(mysqli_query($conn, $query)) {
        $order_id = mysqli_insert_id($conn);
        
        // Reset cart items query
        $cart_items2 = mysqli_query($conn, "SELECT c.*, p.name, p.price 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id='$user_id'");
        
        while($item = mysqli_fetch_assoc($cart_items2)) {
            $item_total = $item['price'] * $item['quantity'];
            mysqli_query($conn, "INSERT INTO order_items (order_id, product_id, product_name, product_price, quantity, total_price) 
                VALUES ('$order_id', '{$item['product_id']}', '{$item['name']}', '{$item['price']}', '{$item['quantity']}', '$item_total')");
        }
        
        // Clear cart
        mysqli_query($conn, "DELETE FROM cart WHERE user_id='$user_id'");
        
        header("Location: order_success.php?order=$order_number");
        exit();
    } else {
        $error = "Order failed: " . mysqli_error($conn);
    }
}

$cities = ['Karachi', 'Lahore', 'Islamabad', 'Rawalpindi', 'Multan', 'Faisalabad', 'Peshawar', 'Quetta'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - XO Chinese Chips</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .checkout-container {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 30px;
            max-width: 1200px;
            margin: 60px auto;
            padding: 0 20px;
        }
        .checkout-form {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        .form-section {
            margin-bottom: 30px;
            border-bottom: 1px solid #eee;
            padding-bottom: 25px;
        }
        .form-section h3 {
            margin-bottom: 20px;
            color: #8B0000;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #eee;
            border-radius: 10px;
            font-family: inherit;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            border-color: #FF6347;
            outline: none;
        }
        .order-summary {
            background: white;
            border-radius: 20px;
            padding: 25px;
            height: fit-content;
            position: sticky;
            top: 100px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }
        .btn-place-order {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #8B0000, #FF6347);
            color: white;
            border: none;
            border-radius: 50px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
        }
        .alert-error {
            background: #fee;
            color: #d32f2f;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        @media (max-width: 992px) {
            .checkout-container {
                grid-template-columns: 1fr;
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
                <a href="cart.php"><i class="fas fa-shopping-bag"></i> Cart</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </div>

    <div class="checkout-container">
        <form method="post" class="checkout-form">
            <div class="form-section">
                <h3><i class="fas fa-truck"></i> Delivery Information</h3>
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                </div>
                <div class="form-group">
                    <label>City</label>
                    <select name="city" required>
                        <option value="">Select City</option>
                        <?php foreach($cities as $city): ?>
                        <option value="<?php echo $city; ?>"><?php echo $city; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Complete Address</label>
                    <textarea name="address" rows="3" required placeholder="House #, Street, Sector, Landmark"></textarea>
                </div>
            </div>
            
            <div class="form-section">
                <h3><i class="fas fa-credit-card"></i> Payment Method</h3>
                <div class="form-group">
                    <select name="payment_method" required>
                        <option value="cod">Cash on Delivery (COD)</option>
                        <option value="card">Credit/Debit Card</option>
                        <option value="bank_transfer">Bank Transfer</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Special Requests (Optional)</label>
                    <textarea name="special_requests" rows="2" placeholder="Extra sauce? Less spice? Any allergies?"></textarea>
                </div>
            </div>
            
            <?php if(isset($error)): ?>
            <div class="alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <button type="submit" name="place_order" class="btn-place-order">Place Order <i class="fas fa-check"></i></button>
        </form>
        
        <div class="order-summary">
            <h3 style="margin-bottom: 20px;">Order Summary</h3>
            <div class="order-item">
                <span>Subtotal:</span>
                <span>PKR <?php echo number_format($subtotal); ?></span>
            </div>
            <div class="order-item">
                <span>Delivery:</span>
                <span>PKR <?php echo number_format($delivery_charge); ?></span>
            </div>
            <div class="order-item">
                <span>Tax (5%):</span>
                <span>PKR <?php echo number_format($tax); ?></span>
            </div>
            <div class="order-item" style="font-weight: bold; font-size: 20px; border-bottom: none; padding-top: 15px;">
                <span>Total:</span>
                <span style="color: #FF6347;">PKR <?php echo number_format($total); ?></span>
            </div>
        </div>
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