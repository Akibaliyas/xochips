<?php
session_start();
include "database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] == 'admin') {
    header("Location: signin.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$order_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE user_id='$user_id'"))['count'];
$wishlist_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM wishlist WHERE user_id='$user_id'"))['count'];
$cart_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(quantity) as count FROM cart WHERE user_id='$user_id'"))['count'];

$recent_orders = mysqli_query($conn, "SELECT * FROM orders WHERE user_id='$user_id' ORDER BY created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - XO Chinese Chips</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .welcome-banner {
            background: linear-gradient(135deg, #8B0000, #FF6347);
            border-radius: 25px;
            padding: 40px;
            color: white;
            margin-bottom: 40px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        .stat-card {
            background: white;
            padding: 30px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
        .stat-number {
            font-size: 36px;
            font-weight: bold;
            color: #FF6347;
        }
        .table-container {
            background: white;
            border-radius: 20px;
            overflow-x: auto;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background: #fff5f0;
            color: #8B0000;
        }
        .badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-delivered { background: #4caf50; color: white; }
        .badge-pending { background: #ff9800; color: white; }
        .badge-confirmed { background: #2196f3; color: white; }
        .badge-preparing { background: #9c27b0; color: white; }
        .badge-ready { background: #ff5722; color: white; }
        .badge-cancelled { background: #f44336; color: white; }
        .btn-small {
            padding: 6px 15px;
            background: #FF6347;
            color: white;
            text-decoration: none;
            border-radius: 20px;
            font-size: 12px;
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
                <a href="dashboard.php" class="active">Dashboard</a>
                <a href="wishlist.php"><i class="fas fa-heart"></i> Wishlist</a>
                <a href="cart.php"><i class="fas fa-shopping-bag"></i> Cart</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </div>

    <div class="dashboard-container">
        <div class="welcome-banner">
            <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['full_name']); ?>! 🍟</h1>
            <p>Track your orders, save your favorites, and order your next meal.</p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📦</div>
                <div class="stat-number"><?php echo $order_count; ?></div>
                <div>Total Orders</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">❤️</div>
                <div class="stat-number"><?php echo $wishlist_count; ?></div>
                <div>Saved Items</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🛒</div>
                <div class="stat-number"><?php echo $cart_count ?? 0; ?></div>
                <div>Cart Items</div>
            </div>
        </div>
        
        <?php if(mysqli_num_rows($recent_orders) > 0): ?>
        <div class="table-container">
            <h3 style="padding: 20px 20px 0;">Recent Orders</h3>
            <table>
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($order = mysqli_fetch_assoc($recent_orders)): ?>
                    <tr>
                        <td><?php echo $order['order_number']; ?></td>
                        <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                        <td>PKR <?php echo number_format($order['grand_total']); ?></td>
                        <td><span class="badge badge-<?php echo $order['order_status']; ?>"><?php echo ucfirst($order['order_status']); ?></span></td>
                        <td><span class="badge badge-<?php echo $order['payment_status']; ?>"><?php echo ucfirst($order['payment_status']); ?></span></td>
                        <td><a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn-small">Details</a></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        
        <div style="margin-top: 30px; text-align: center;">
            <a href="shop.php" class="btn-primary" style="display: inline-block; padding: 15px 40px;">Order Now <i class="fas fa-arrow-right"></i></a>
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