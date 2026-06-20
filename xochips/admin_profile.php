<?php
session_start();
include "database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: signin.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = "";
$success = "";

// Get current admin info
$query = "SELECT * FROM users WHERE id = '$user_id'";
$result = mysqli_query($conn, $query);
$admin = mysqli_fetch_assoc($result);

if (isset($_POST['update_profile'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    
    // Check if username already exists (excluding current user)
    $check = mysqli_query($conn, "SELECT id FROM users WHERE username='$username' AND id != '$user_id'");
    if (mysqli_num_rows($check) > 0) {
        $error = "Username already exists!";
    } else {
        $update = "UPDATE users SET username='$username', full_name='$full_name', email='$email', phone='$phone', city='$city' WHERE id='$user_id'";
        if (mysqli_query($conn, $update)) {
            $_SESSION['username'] = $username;
            $_SESSION['full_name'] = $full_name;
            $_SESSION['email'] = $email;
            $_SESSION['phone'] = $phone;
            $success = "Profile updated successfully!";
            // Refresh admin data
            $result = mysqli_query($conn, $query);
            $admin = mysqli_fetch_assoc($result);
        } else {
            $error = "Update failed: " . mysqli_error($conn);
        }
    }
}

// Change password
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Verify current password
    if (password_verify($current_password, $admin['password'])) {
        if ($new_password == $confirm_password) {
            if (strlen($new_password) >= 6) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update = "UPDATE users SET password='$hashed_password' WHERE id='$user_id'";
                if (mysqli_query($conn, $update)) {
                    $success = "Password changed successfully! Please login again.";
                    echo "<script>setTimeout(function(){ window.location.href='logout.php'; }, 2000);</script>";
                } else {
                    $error = "Failed to change password: " . mysqli_error($conn);
                }
            } else {
                $error = "New password must be at least 6 characters!";
            }
        } else {
            $error = "New passwords do not match!";
        }
    } else {
        $error = "Current password is incorrect!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile - XO Chinese Chips</title>
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
        .profile-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .profile-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .profile-card h2 {
            color: #8B0000;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
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
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #eee;
            border-radius: 10px;
            font-family: inherit;
            transition: all 0.3s;
        }
        .form-group input:focus {
            border-color: #FF6347;
            outline: none;
        }
        .btn-submit {
            background: linear-gradient(135deg, #8B0000, #FF6347);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255,99,71,0.3);
        }
        .alert-error {
            background: #fee;
            color: #d32f2f;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #d32f2f;
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
            <a href="admin_orders.php"><i class="fas fa-shopping-cart"></i> Orders</a>
            <a href="admin_users.php"><i class="fas fa-users"></i> Users</a>
            <a href="admin_add_product.php"><i class="fas fa-plus-circle"></i> Add Item</a>
            <a href="admin_profile.php" class="active"><i class="fas fa-user-cog"></i> Profile</a>
            <a href="index.php"><i class="fas fa-store"></i> View Store</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
    
    <div class="admin-main">
        <div class="profile-container">
            <h1 style="margin-bottom: 30px;">Admin Profile Settings</h1>
            
            <?php if($error): ?>
            <div class="alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if($success): ?>
            <div class="alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <!-- Update Profile Form -->
            <div class="profile-card">
                <h2><i class="fas fa-user-edit"></i> Edit Profile</h2>
                <form method="post">
                    <div class="form-group">
                        <label>Username *</label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($admin['username']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="full_name" value="<?php echo htmlspecialchars($admin['full_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" name="phone" value="<?php echo htmlspecialchars($admin['phone']); ?>">
                    </div>
                    <div class="form-group">
                        <label>City</label>
                        <input type="text" name="city" value="<?php echo htmlspecialchars($admin['city']); ?>">
                    </div>
                    <button type="submit" name="update_profile" class="btn-submit">Update Profile</button>
                </form>
            </div>
            
            <!-- Change Password Form -->
            <div class="profile-card">
                <h2><i class="fas fa-key"></i> Change Password</h2>
                <form method="post">
                    <div class="form-group">
                        <label>Current Password *</label>
                        <input type="password" name="current_password" required>
                    </div>
                    <div class="form-group">
                        <label>New Password * (min. 6 characters)</label>
                        <input type="password" name="new_password" required>
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password *</label>
                        <input type="password" name="confirm_password" required>
                    </div>
                    <button type="submit" name="change_password" class="btn-submit">Change Password</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>