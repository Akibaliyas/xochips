<?php
session_start();
include "database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: signin.php");
    exit();
}

// Get all stats
$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role='user'"))['count'];
$total_products = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM products"))['count'];
$total_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders"))['count'];
$pending_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE order_status='pending'"))['count'];
$total_revenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(grand_total) as total FROM orders WHERE order_status='delivered'"))['total'];
$low_stock = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM products WHERE stock_quantity < 10 AND status='active'"))['count'];

$recent_orders = mysqli_query($conn, "SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 10");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - XO Chinese Chips</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background: #f5f5f5;
        }
        .admin-sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 260px;
            height: 100vh;
            background: #8B0000;
            color: white;
            padding: 30px 20px;
            overflow-y: auto;
        }
        .admin-main {
            margin-left: 260px;
            padding: 30px;
        }
        .admin-logo {
            font-size: 24px;
            text-align: center;
            margin-bottom: 30px;
        }
        .admin-logo span {
            background: linear-gradient(135deg, #fff, #FF6347);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        .admin-nav a {
            display: block;
            padding: 12px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            border-radius: 10px;
            margin-bottom: 5px;
            transition: all 0.3s;
        }
        .admin-nav a:hover, .admin-nav a.active {
            background: #FF6347;
            color: white;
        }
        .admin-nav a i {
            margin-right: 10px;
            width: 20px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-card i {
            font-size: 40px;
            color: #FF6347;
            margin-bottom: 10px;
        }
        .stat-card strong {
            font-size: 32px;
            display: block;
            margin: 10px 0;
        }
        .table-container {
            background: white;
            border-radius: 15px;
            overflow-x: auto;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #8B0000;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
        }
        .badge-pending { background: #ff9800; color: white; }
        .badge-confirmed { background: #2196f3; color: white; }
        .badge-preparing { background: #9c27b0; color: white; }
        .badge-ready { background: #ff5722; color: white; }
        .badge-delivered { background: #4caf50; color: white; }
        .badge-cancelled { background: #f44336; color: white; }
        .welcome-text {
            margin-bottom: 30px;
        }
        .welcome-text h1 {
            color: #8B0000;
        }
        @media (max-width: 768px) {
            .admin-sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            .admin-main {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="admin-sidebar">
        <div class="admin-logo">
            <span>🍟 XO Chips Admin</span>
        </div>
        <div class="admin-nav">
            <a href="admin_dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="admin_products.php"><i class="fas fa-utensils"></i> Menu Items</a>
            <a href="admin_categories.php"><i class="fas fa-tags"></i> Categories</a>
            <a href="admin_orders.php"><i class="fas fa-shopping-cart"></i> Orders</a>
            <a href="admin_users.php"><i class="fas fa-users"></i> Users</a>
            <a href="admin_add_product.php"><i class="fas fa-plus-circle"></i> Add Item</a>
            <a href="admin_profile.php"><i class="fas fa-user-cog"></i> Profile</a>
            <a href="index.php"><i class="fas fa-store"></i> View Store</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
    
    <div class="admin-main">
        <div class="welcome-text">
            <h1>Admin Dashboard</h1>
            <p>Welcome back, <?php echo htmlspecialchars($_SESSION['full_name']); ?>! 🍟</p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-users"></i>
                <strong><?php echo $total_users; ?></strong>
                <span>Total Users</span>
            </div>
            <div class="stat-card">
                <i class="fas fa-utensils"></i>
                <strong><?php echo $total_products; ?></strong>
                <span>Menu Items</span>
            </div>
            <div class="stat-card">
                <i class="fas fa-shopping-cart"></i>
                <strong><?php echo $total_orders; ?></strong>
                <span>Total Orders</span>
            </div>
            <div class="stat-card">
                <i class="fas fa-clock"></i>
                <strong><?php echo $pending_orders; ?></strong>
                <span>Pending Orders</span>
            </div>
            <div class="stat-card">
                <i class="fas fa-money-bill"></i>
                <strong>PKR <?php echo number_format($total_revenue ?? 0); ?></strong>
                <span>Total Revenue</span>
            </div>
            <div class="stat-card">
                <i class="fas fa-exclamation-triangle"></i>
                <strong><?php echo $low_stock; ?></strong>
                <span>Low Stock Items</span>
            </div>
        </div>
        
        <div class="table-container">
            <h3 style="margin-bottom: 20px;">Recent Orders</h3>
            <?php if(mysqli_num_rows($recent_orders) == 0): ?>
            <p style="text-align: center; padding: 20px;">No orders yet</p>
            <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($order = mysqli_fetch_assoc($recent_orders)): ?>
                    <tr>
                        <td><?php echo $order['order_number']; ?></td>
                        <td><?php echo htmlspecialchars($order['username']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                        <td>PKR <?php echo number_format($order['grand_total']); ?></td>
                        <td><span class="badge badge-<?php echo $order['order_status']; ?>"><?php echo ucfirst($order['order_status']); ?></span></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>