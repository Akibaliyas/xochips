<?php
session_start();
include "database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: signin.php");
    exit();
}

// Update order status
if(isset($_GET['update_status']) && isset($_GET['id'])) {
    $order_id = intval($_GET['id']);
    $new_status = mysqli_real_escape_string($conn, $_GET['update_status']);
    mysqli_query($conn, "UPDATE orders SET order_status='$new_status' WHERE id='$order_id'");
    $_SESSION['message'] = "Order status updated!";
    header("Location: admin_orders.php");
    exit();
}

// Update payment status
if(isset($_GET['update_payment']) && isset($_GET['id'])) {
    $order_id = intval($_GET['id']);
    $new_payment = mysqli_real_escape_string($conn, $_GET['update_payment']);
    mysqli_query($conn, "UPDATE orders SET payment_status='$new_payment' WHERE id='$order_id'");
    $_SESSION['message'] = "Payment status updated!";
    header("Location: admin_orders.php");
    exit();
}

// Delete order
if(isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $order_id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM order_items WHERE order_id='$order_id'");
    mysqli_query($conn, "DELETE FROM orders WHERE id='$order_id'");
    $_SESSION['message'] = "Order deleted successfully!";
    header("Location: admin_orders.php");
    exit();
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';

$where = "WHERE 1=1";
if($status_filter && $status_filter != 'all') {
    $where .= " AND o.order_status = '$status_filter'";
}
if($date_filter) {
    $where .= " AND DATE(o.created_at) = '$date_filter'";
}

$orders = mysqli_query($conn, "SELECT o.*, u.username, u.email, u.phone 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    $where
    ORDER BY o.created_at DESC");

$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
unset($_SESSION['message']);

// Get counts for different statuses
$total_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders"))['count'];
$pending_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE order_status='pending'"))['count'];
$delivered_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE order_status='delivered'"))['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - XO Chinese Chips Admin</title>
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
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-box {
            background: white;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .stat-box h3 {
            font-size: 28px;
            color: #FF6347;
        }
        .filter-bar {
            background: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }
        .filter-bar select, .filter-bar input {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .btn-filter {
            background: #8B0000;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
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
            color: #8B0000;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-pending { background: #ff9800; color: white; }
        .badge-confirmed { background: #2196f3; color: white; }
        .badge-preparing { background: #9c27b0; color: white; }
        .badge-ready { background: #ff5722; color: white; }
        .badge-delivered { background: #4caf50; color: white; }
        .badge-cancelled { background: #f44336; color: white; }
        .badge-paid { background: #4caf50; color: white; }
        .badge-pending_payment { background: #ff9800; color: white; }
        .status-select, .payment-select {
            padding: 5px;
            border-radius: 5px;
            border: 1px solid #ddd;
            font-size: 12px;
        }
        .btn-delete {
            background: #f44336;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 12px;
        }
        .btn-view {
            background: #2196f3;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 12px;
        }
        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #4caf50;
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
        <div class="admin-logo">🍟 XO Chips Admin</div>
        <div class="admin-nav">
            <a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="admin_products.php"><i class="fas fa-utensils"></i> Menu Items</a>
            <a href="admin_categories.php"><i class="fas fa-tags"></i> Categories</a>
            <a href="admin_orders.php" class="active"><i class="fas fa-shopping-cart"></i> Orders</a>
            <a href="admin_users.php"><i class="fas fa-users"></i> Users</a>
            <a href="admin_add_product.php"><i class="fas fa-plus-circle"></i> Add Item</a>
            <a href="admin_profile.php"><i class="fas fa-user-cog"></i> Profile</a>
            <a href="index.php"><i class="fas fa-store"></i> View Store</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
    
    <div class="admin-main">
        <h1>Manage Orders</h1>
        
        <?php if($message): ?>
        <div class="alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <!-- Stats -->
        <div class="stats-row">
            <div class="stat-box">
                <h3><?php echo $total_orders; ?></h3>
                <p>Total Orders</p>
            </div>
            <div class="stat-box">
                <h3><?php echo $pending_orders; ?></h3>
                <p>Pending</p>
            </div>
            <div class="stat-box">
                <h3><?php echo $delivered_orders; ?></h3>
                <p>Delivered</p>
            </div>
        </div>
        
        <!-- Filter Bar -->
        <div class="filter-bar">
            <form method="get" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: center;">
                <select name="status">
                    <option value="all">All Status</option>
                    <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="confirmed" <?php echo $status_filter == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                    <option value="preparing" <?php echo $status_filter == 'preparing' ? 'selected' : ''; ?>>Preparing</option>
                    <option value="ready" <?php echo $status_filter == 'ready' ? 'selected' : ''; ?>>Ready</option>
                    <option value="delivered" <?php echo $status_filter == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                    <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
                <input type="date" name="date" value="<?php echo $date_filter; ?>">
                <button type="submit" class="btn-filter">Filter</button>
                <a href="admin_orders.php" class="btn-filter" style="background: #666; text-decoration: none;">Reset</a>
            </form>
        </div>
        
        <div class="table-container">
            <?php if(mysqli_num_rows($orders) == 0): ?>
            <p style="text-align: center; padding: 40px;">No orders found</p>
            <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Contact</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Order Status</th>
                        <th>Payment</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($order = mysqli_fetch_assoc($orders)): ?>
                    <tr>
                        <td><strong><?php echo $order['order_number']; ?></strong></td>
                        <td><?php echo htmlspecialchars($order['username']); ?></td>
                        <td><?php echo htmlspecialchars($order['phone']); ?></td>
                        <td><?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?></td>
                        <td>PKR <?php echo number_format($order['grand_total']); ?></td>
                        <td>
                            <select class="status-select" onchange="window.location.href='?update_status='+this.value+'&id=<?php echo $order['id']; ?>'">
                                <option value="pending" <?php echo $order['order_status']=='pending'?'selected':''; ?>>Pending</option>
                                <option value="confirmed" <?php echo $order['order_status']=='confirmed'?'selected':''; ?>>Confirmed</option>
                                <option value="preparing" <?php echo $order['order_status']=='preparing'?'selected':''; ?>>Preparing</option>
                                <option value="ready" <?php echo $order['order_status']=='ready'?'selected':''; ?>>Ready</option>
                                <option value="delivered" <?php echo $order['order_status']=='delivered'?'selected':''; ?>>Delivered</option>
                                <option value="cancelled" <?php echo $order['order_status']=='cancelled'?'selected':''; ?>>Cancelled</option>
                            </select>
                        </td>
                        <td>
                            <select class="payment-select" onchange="window.location.href='?update_payment='+this.value+'&id=<?php echo $order['id']; ?>'">
                                <option value="pending" <?php echo $order['payment_status']=='pending'?'selected':''; ?>>Pending</option>
                                <option value="paid" <?php echo $order['payment_status']=='paid'?'selected':''; ?>>Paid</option>
                                <option value="failed" <?php echo $order['payment_status']=='failed'?'selected':''; ?>>Failed</option>
                            </select>
                        </td>
                        <td>
                            <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn-view"><i class="fas fa-eye"></i> View</a>
                            <a href="?delete=<?php echo $order['id']; ?>" class="btn-delete" onclick="return confirm('Delete this order? This action cannot be undone.')"><i class="fas fa-trash"></i> Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>