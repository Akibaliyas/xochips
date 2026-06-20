<?php
session_start();
include "database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: signin.php");
    exit();
}

// Delete user
if(isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $user_id = $_GET['delete'];
    // Don't allow admin to delete themselves
    if($user_id != $_SESSION['user_id']) {
        // Check if user has orders
        $check_orders = mysqli_query($conn, "SELECT id FROM orders WHERE user_id='$user_id' LIMIT 1");
        if(mysqli_num_rows($check_orders) > 0) {
            $_SESSION['error'] = "Cannot delete user with existing orders!";
        } else {
            // Delete user's cart and wishlist first
            mysqli_query($conn, "DELETE FROM cart WHERE user_id='$user_id'");
            mysqli_query($conn, "DELETE FROM wishlist WHERE user_id='$user_id'");
            mysqli_query($conn, "DELETE FROM users WHERE id='$user_id'");
            $_SESSION['message'] = "User deleted successfully!";
        }
    } else {
        $_SESSION['error'] = "You cannot delete your own admin account!";
    }
    header("Location: admin_users.php");
    exit();
}

// Toggle user status (active/inactive)
if(isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $user_id = $_GET['toggle'];
    if($user_id != $_SESSION['user_id']) {
        $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT status FROM users WHERE id='$user_id'"));
        $new_status = ($user['status'] == 'active') ? 'inactive' : 'active';
        mysqli_query($conn, "UPDATE users SET status='$new_status' WHERE id='$user_id'");
        $_SESSION['message'] = "User status updated!";
    } else {
        $_SESSION['error'] = "You cannot change your own status!";
    }
    header("Location: admin_users.php");
    exit();
}

// Change user role
if(isset($_GET['change_role']) && isset($_GET['id'])) {
    $user_id = $_GET['id'];
    $new_role = $_GET['change_role'];
    if($user_id != $_SESSION['user_id']) {
        mysqli_query($conn, "UPDATE users SET role='$new_role' WHERE id='$user_id'");
        $_SESSION['message'] = "User role updated!";
    } else {
        $_SESSION['error'] = "You cannot change your own role!";
    }
    header("Location: admin_users.php");
    exit();
}

// Get filter
$role_filter = isset($_GET['role']) ? $_GET['role'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

$where = "WHERE 1=1";
if($role_filter && $role_filter != 'all') {
    $where .= " AND role = '$role_filter'";
}
if($status_filter && $status_filter != 'all') {
    $where .= " AND status = '$status_filter'";
}

$users = mysqli_query($conn, "SELECT * FROM users $where ORDER BY created_at DESC");

// Get counts
$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users"))['count'];
$admin_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role='admin'"))['count'];
$user_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role='user'"))['count'];
$active_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE status='active'"))['count'];

$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
unset($_SESSION['message']);
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - XO Chinese Chips Admin</title>
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
        .filter-bar select {
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
            text-decoration: none;
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
        .badge-admin {
            background: #ff9800;
            color: white;
        }
        .badge-user {
            background: #4caf50;
            color: white;
        }
        .badge-active {
            background: #4caf50;
            color: white;
        }
        .badge-inactive {
            background: #f44336;
            color: white;
        }
        .btn-edit, .btn-delete, .btn-toggle, .btn-role {
            padding: 5px 10px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 11px;
            margin: 2px;
            display: inline-block;
        }
        .btn-role {
            background: #2196f3;
            color: white;
        }
        .btn-toggle {
            background: #ff9800;
            color: white;
        }
        .btn-delete {
            background: #f44336;
            color: white;
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
        .avatar {
            width: 40px;
            height: 40px;
            background: #FF6347;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
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
            <a href="admin_orders.php"><i class="fas fa-shopping-cart"></i> Orders</a>
            <a href="admin_users.php" class="active"><i class="fas fa-users"></i> Users</a>
            <a href="admin_add_product.php"><i class="fas fa-plus-circle"></i> Add Item</a>
            <a href="admin_profile.php"><i class="fas fa-user-cog"></i> Profile</a>
            <a href="index.php"><i class="fas fa-store"></i> View Store</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
    
    <div class="admin-main">
        <h1>Manage Users</h1>
        
        <?php if($message): ?>
        <div class="alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if($error): ?>
        <div class="alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <!-- Stats -->
        <div class="stats-row">
            <div class="stat-box">
                <h3><?php echo $total_users; ?></h3>
                <p>Total Users</p>
            </div>
            <div class="stat-box">
                <h3><?php echo $admin_count; ?></h3>
                <p>Admins</p>
            </div>
            <div class="stat-box">
                <h3><?php echo $user_count; ?></h3>
                <p>Customers</p>
            </div>
            <div class="stat-box">
                <h3><?php echo $active_users; ?></h3>
                <p>Active Users</p>
            </div>
        </div>
        
        <!-- Filter Bar -->
        <div class="filter-bar">
            <form method="get" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: center;">
                <select name="role">
                    <option value="all">All Roles</option>
                    <option value="admin" <?php echo $role_filter == 'admin' ? 'selected' : ''; ?>>Admin</option>
                    <option value="user" <?php echo $role_filter == 'user' ? 'selected' : ''; ?>>Customer</option>
                </select>
                <select name="status">
                    <option value="all">All Status</option>
                    <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $status_filter == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
                <button type="submit" class="btn-filter">Filter</button>
                <a href="admin_users.php" class="btn-filter" style="background: #666; text-decoration: none;">Reset</a>
            </form>
        </div>
        
        <div class="table-container">
            <?php if(mysqli_num_rows($users) == 0): ?>
            <p style="text-align: center; padding: 40px;">No users found</p>
            <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Avatar</th>
                        <th>Username</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>City</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($user = mysqli_fetch_assoc($users)): ?>
                    <tr>
                        <td>#<?php echo $user['id']; ?></td>
                        <td>
                            <div class="avatar">
                                <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                            </div>
                        </td>
                        <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['phone']); ?></td>
                        <td><?php echo htmlspecialchars($user['city'] ?: '-'); ?></td>
                        <td>
                            <span class="badge <?php echo $user['role'] == 'admin' ? 'badge-admin' : 'badge-user'; ?>">
                                <?php echo ucfirst($user['role']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?php echo $user['status'] == 'active' ? 'badge-active' : 'badge-inactive'; ?>">
                                <?php echo ucfirst($user['status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <?php if($user['id'] != $_SESSION['user_id']): ?>
                            <a href="?change_role=<?php echo $user['role'] == 'admin' ? 'user' : 'admin'; ?>&id=<?php echo $user['id']; ?>" class="btn-role" onclick="return confirm('Change user role?')">
                                <i class="fas fa-exchange-alt"></i> Role
                            </a>
                            <a href="?toggle=<?php echo $user['id']; ?>" class="btn-toggle" onclick="return confirm('Toggle user status?')">
                                <i class="fas fa-power-off"></i> Status
                            </a>
                            <a href="?delete=<?php echo $user['id']; ?>" class="btn-delete" onclick="return confirm('Delete this user? This will also delete their cart and wishlist.')">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                            <?php else: ?>
                            <span style="color: #999;">(You)</span>
                            <?php endif; ?>
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