<?php
session_start();
include "database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = "";
$success = "";

// Get user info
$query = "SELECT * FROM users WHERE id = '$user_id'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

if (isset($_POST['update_profile'])) {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    
    $update = "UPDATE users SET full_name='$full_name', phone='$phone', city='$city', address='$address' WHERE id='$user_id'";
    if (mysqli_query($conn, $update)) {
        $_SESSION['full_name'] = $full_name;
        $_SESSION['phone'] = $phone;
        $success = "Profile updated successfully!";
        // Refresh user data
        $result = mysqli_query($conn, $query);
        $user = mysqli_fetch_assoc($result);
    } else {
        $error = "Update failed: " . mysqli_error($conn);
    }
}

if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (password_verify($current_password, $user['password'])) {
        if ($new_password == $confirm_password) {
            if (strlen($new_password) >= 6) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update = "UPDATE users SET password='$hashed_password' WHERE id='$user_id'";
                if (mysqli_query($conn, $update)) {
                    $success = "Password changed successfully! Please login again.";
                    echo "<script>setTimeout(function(){ window.location.href='logout.php'; }, 2000);</script>";
                } else {
                    $error = "Failed to change password";
                }
            } else {
                $error = "Password must be at least 6 characters";
            }
        } else {
            $error = "Passwords do not match";
        }
    } else {
        $error = "Current password is incorrect";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings - XO Chinese Chips</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .profile-container {
            max-width: 800px;
            margin: 60px auto;
            padding: 0 20px;
        }
        .profile-card {
            background: white;
            border-radius: 25px;
            padding: 35px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        .profile-card h2 {
            color: #8B0000;
            margin-bottom: 25px;
            padding-bottom: 15px;
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
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #eee;
            border-radius: 12px;
            font-family: inherit;
            transition: all 0.3s;
        }
        .form-group input:focus, .form-group textarea:focus {
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
                <span class="logo-icon">🍟</span>
                <span class="logo-text">XO Chinese Chips</span>
            </div>
            <div class="nav">
                <a href="index.php">Home</a>
                <a href="shop.php">Menu</a>
                <a href="deals.php">Deals</a>
                <a href="dashboard.php">My Account</a>
                <a href="wishlist.php">Wishlist</a>
                <a href="cart.php">Cart</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </div>

    <div class="profile-container">
        <a href="dashboard.php" class="btn-back"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        
        <?php if($error): ?>
        <div class="alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if($success): ?>
        <div class="alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="profile-card">
            <h2><i class="fas fa-user-edit"></i> Edit Profile</h2>
            <form method="post">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                    <small style="color: #999;">Username cannot be changed</small>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                    <small style="color: #999;">Email cannot be changed</small>
                </div>
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Phone *</label>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                </div>
                <div class="form-group">
                    <label>City</label>
                    <input type="text" name="city" value="<?php echo htmlspecialchars($user['city']); ?>">
                </div>
                <div class="form-group">
                    <label>Delivery Address</label>
                    <textarea name="address" rows="3" placeholder="Your complete address for delivery"><?php echo htmlspecialchars($user['address']); ?></textarea>
                </div>
                <button type="submit" name="update_profile" class="btn-submit">Update Profile</button>
            </form>
        </div>
        
        <div class="profile-card">
            <h2><i class="fas fa-key"></i> Change Password</h2>
            <form method="post">
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" required>
                </div>
                <div class="form-group">
                    <label>New Password (min. 6 characters)</label>
                    <input type="password" name="new_password" required>
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" required>
                </div>
                <button type="submit" name="change_password" class="btn-submit">Change Password</button>
            </form>
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