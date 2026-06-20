<?php
session_start();
include "database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$all_orders = mysqli_query($conn, "SELECT * FROM orders WHERE user_id='$user_id' ORDER BY created_at DESC");

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
    <title>My Orders - XO Chinese Chips</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .orders-container {
            max-width: 1200px;
            margin: 60px auto;
            padding: 0 20px;
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
            color: white;
        }
        .btn-back {
            display: inline-block;
            padding: 10px 20px;
            background: #666;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            margin-bottom: 20px;
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
                <a href="dashboard.php">My Account</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </div>

    <div class="orders-container">
        <a href="dashboard.php" class="btn-back"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        <h1 style="margin-bottom: 30px;">My Orders</h1>
        
        <div class="table-container">
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
                    <?php if(mysqli_num_rows($all_orders) == 0): ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">No orders found</td>
                    </tr>
                    <?php else: ?>
                    <?php while($order = mysqli_fetch_assoc($all_orders)): ?>
                    <tr>
                        <td><?php echo $order['order_number']; ?></td>
                        <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                        <td>PKR <?php echo number_format($order['grand_total']); ?></td>
                        <td><span class="badge" style="background: <?php echo $status_colors[$order['order_status']]; ?>"><?php echo ucfirst($order['order_status']); ?></span></td>
                        <td><span class="badge" style="background: <?php echo $order['payment_status'] == 'paid' ? '#4caf50' : '#ff9800'; ?>"><?php echo ucfirst($order['payment_status']); ?></span></td>
                        <td><a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn-small">View</a></td>
                    </tr>
                    <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>🍟 XO Chinese Chips</h3>
                <p>Authentic wok-tossed fries and Chinese fusion delivered to your door.</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> XO Chinese Chips. All rights reserved.</p>
        </div>
    </div>
</body>
</html>