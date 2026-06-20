<?php
session_start();
include "database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if($_SESSION['role'] == 'admin') {
    $order = mysqli_fetch_assoc(mysqli_query($conn, "SELECT o.*, u.username, u.email, u.phone 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        WHERE o.id='$order_id'"));
} else {
    $order = mysqli_fetch_assoc(mysqli_query($conn, "SELECT o.*, u.username, u.email, u.phone 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        WHERE o.id='$order_id' AND o.user_id='{$_SESSION['user_id']}'"));
}

if(!$order) {
    header("Location: dashboard.php");
    exit();
}

$order_items = mysqli_query($conn, "SELECT * FROM order_items WHERE order_id='$order_id'");

// Status badge colors
$status_colors = [
    'pending' => '#ff9800',
    'confirmed' => '#2196f3',
    'preparing' => '#9c27b0',
    'ready' => '#ff5722',
    'delivered' => '#4caf50',
    'cancelled' => '#f44336'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - XO Chinese Chips</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .order-container {
            max-width: 1000px;
            margin: 60px auto;
            padding: 0 20px;
        }
        .order-header {
            background: white;
            border-radius: 25px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        .order-header h1 {
            color: #8B0000;
            margin-bottom: 10px;
        }
        .order-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            background: #fff5f0;
            padding: 20px;
            border-radius: 20px;
            margin: 20px 0;
        }
        .order-info-item {
            text-align: center;
        }
        .order-info-item strong {
            display: block;
            color: #8B0000;
            margin-bottom: 8px;
        }
        .status-badge {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 50px;
            font-weight: bold;
            color: white;
        }
        table {
            width: 100%;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
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
        .btn-back {
            display: inline-block;
            padding: 12px 25px;
            background: #8B0000;
            color: white;
            text-decoration: none;
            border-radius: 50px;
            margin-top: 20px;
            transition: all 0.3s;
        }
        .btn-back:hover {
            background: #FF6347;
        }
        .tracking-status {
            margin-top: 20px;
        }
        .tracking-steps {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        .tracking-step {
            text-align: center;
            flex: 1;
            position: relative;
        }
        .tracking-step .step-icon {
            width: 50px;
            height: 50px;
            background: #eee;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-size: 20px;
            color: #999;
        }
        .tracking-step.completed .step-icon {
            background: #4caf50;
            color: white;
        }
        .tracking-step.active .step-icon {
            background: #FF6347;
            color: white;
            animation: pulse 1s infinite;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        @media (max-width: 768px) {
            .tracking-step .step-icon {
                width: 40px;
                height: 40px;
                font-size: 16px;
            }
            .tracking-step span {
                font-size: 10px;
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
                <a href="dashboard.php">My Account</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </div>

    <div class="order-container">
        <div class="order-header">
            <h1><i class="fas fa-receipt"></i> Order Details</h1>
            <p>Order #: <?php echo $order['order_number']; ?></p>
            <p>Placed on: <?php echo date('F d, Y h:i A', strtotime($order['created_at'])); ?></p>
            
            <div class="order-info-grid">
                <div class="order-info-item">
                    <strong>Order Status</strong>
                    <span class="status-badge" style="background: <?php echo $status_colors[$order['order_status']] ?? '#666'; ?>">
                        <?php echo ucfirst($order['order_status']); ?>
                    </span>
                </div>
                <div class="order-info-item">
                    <strong>Payment Status</strong>
                    <span class="status-badge" style="background: <?php echo $order['payment_status'] == 'paid' ? '#4caf50' : '#ff9800'; ?>">
                        <?php echo ucfirst($order['payment_status']); ?>
                    </span>
                </div>
                <div class="order-info-item">
                    <strong>Payment Method</strong>
                    <span><?php echo strtoupper(str_replace('_', ' ', $order['payment_method'])); ?></span>
                </div>
            </div>
            
            <!-- Order Tracking Timeline -->
            <div class="tracking-status">
                <strong><i class="fas fa-map-marker-alt"></i> Order Tracking</strong>
                <div class="tracking-steps">
                    <?php 
                    $steps = ['pending', 'confirmed', 'preparing', 'ready', 'delivered'];
                    $current_step = array_search($order['order_status'], $steps);
                    foreach($steps as $index => $step):
                        $is_completed = $index < $current_step;
                        $is_active = $index == $current_step;
                    ?>
                    <div class="tracking-step <?php echo $is_completed ? 'completed' : ($is_active ? 'active' : ''); ?>">
                        <div class="step-icon">
                            <i class="fas <?php echo 
                                $step == 'pending' ? 'fa-clock' : 
                                ($step == 'confirmed' ? 'fa-check-circle' : 
                                ($step == 'preparing' ? 'fa-utensils' : 
                                ($step == 'ready' ? 'fa-bell' : 'fa-flag-checkered'))); ?>"></i>
                        </div>
                        <span><?php echo ucfirst($step); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="order-info-grid">
                <div class="order-info-item">
                    <strong>Delivery Address</strong>
                    <span><?php echo nl2br(htmlspecialchars($order['delivery_address'])); ?></span>
                </div>
                <div class="order-info-item">
                    <strong>Delivery City</strong>
                    <span><?php echo htmlspecialchars($order['delivery_city']); ?></span>
                </div>
                <div class="order-info-item">
                    <strong>Phone</strong>
                    <span><?php echo htmlspecialchars($order['delivery_phone']); ?></span>
                </div>
            </div>
        </div>
        
        <h2 style="margin-bottom: 20px;"><i class="fas fa-shopping-bag"></i> Order Items</h2>
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php while($item = mysqli_fetch_assoc($order_items)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                    <td>PKR <?php echo number_format($item['product_price']); ?></td>
                    <td><?php echo $item['quantity']; ?></td
                    <td>PKR <?php echo number_format($item['total_price']); ?></td
                </tr>
                <?php endwhile; ?>
            </tbody>
            <tfoot>
                <tr><th colspan="3" style="text-align: right;">Subtotal:</th><th>PKR <?php echo number_format($order['total_amount']); ?></th></tr>
                <tr><th colspan="3" style="text-align: right;">Delivery:</th><th>PKR <?php echo number_format($order['shipping_charge']); ?></th></tr>
                <tr><th colspan="3" style="text-align: right; font-size: 18px;">Grand Total:</th><th><strong style="color: #FF6347; font-size: 20px;">PKR <?php echo number_format($order['grand_total']); ?></strong></th></tr>
            </tfoot>
        </table>
        
        <?php if($order['special_requests']): ?>
        <div class="order-header" style="margin-top: 20px;">
            <strong><i class="fas fa-comment"></i> Special Requests:</strong>
            <p><?php echo nl2br(htmlspecialchars($order['special_requests'])); ?></p>
        </div>
        <?php endif; ?>
        
        <a href="dashboard.php" class="btn-back"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
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