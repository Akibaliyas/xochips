<?php
session_start();
include "database.php";

if(isset($_SESSION['user_id'])) {
    if($_SESSION['role'] == 'admin') {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: index.php");
    }
    exit();
}

$error = "";

if (isset($_POST['signin'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    
    $query = "SELECT * FROM users WHERE username='$username' OR email='$username'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['full_name'] = $row['full_name'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['phone'] = $row['phone'];
            $_SESSION['profile_image'] = $row['profile_image'];
            
            // Check if there was a redirect after login
            if(isset($_SESSION['redirect_after_login'])) {
                $redirect = $_SESSION['redirect_after_login'];
                unset($_SESSION['redirect_after_login']);
                header("Location: " . $redirect);
            } elseif ($row['role'] == 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            $error = "Invalid username/email or password!";
        }
    } else {
        $error = "Invalid username/email or password!";
    }
}

// Check for login required message
$login_message = "";
if(isset($_GET['error'])) {
    if($_GET['error'] == 'please_login') {
        $login_message = "Please login first to add items to your wishlist!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - XO Chinese Chips</title>
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
            background: linear-gradient(135deg, #8B0000, #FF6347);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .auth-card {
            background: white;
            border-radius: 30px;
            padding: 50px;
            max-width: 500px;
            width: 90%;
            animation: slideUp 0.5s ease;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .auth-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo-icon {
            font-size: 60px;
            margin-bottom: 10px;
        }
        .auth-header h2 {
            font-size: 28px;
            color: #8B0000;
        }
        .auth-header p {
            color: #666;
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
            padding: 14px 16px;
            border: 2px solid #e8e8e8;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s ease;
        }
        .form-group input:focus {
            border-color: #FF6347;
            outline: none;
            box-shadow: 0 0 0 3px rgba(255,99,71,0.1);
        }
        .btn-auth {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #8B0000, #FF6347);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-auth:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(255,99,71,0.3);
        }
        .alert-error {
            background: #fee;
            color: #d32f2f;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #d32f2f;
        }
        .alert-info {
            background: #e3f2fd;
            color: #1976d2;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #1976d2;
        }
        .auth-footer {
            text-align: center;
            margin-top: 25px;
            color: #666;
        }
        .auth-footer a {
            color: #FF6347;
            text-decoration: none;
        }
        .demo-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 12px;
            margin-top: 20px;
            font-size: 12px;
            text-align: center;
        }
        @media (max-width: 500px) {
            .auth-card {
                padding: 30px 25px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="auth-header">
            <div class="logo-icon">🍟</div>
            <h2>Welcome Back!</h2>
            <p>Sign in to your XO Chinese Chips account</p>
        </div>
        
        <?php if($error): ?>
        <div class="alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if($login_message): ?>
        <div class="alert-info"><?php echo $login_message; ?></div>
        <?php endif; ?>
        
        <form method="post">
            <div class="form-group">
                <label>Username or Email</label>
                <input type="text" name="username" required placeholder="Enter your username or email">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="Enter your password">
            </div>
            <button type="submit" name="signin" class="btn-auth">Sign In <i class="fas fa-arrow-right"></i></button>
        </form>
        
        <div class="auth-footer">
            <p>Don't have an account? <a href="register.php">Register here</a></p>
        </div>
        
        <!-- <div class="demo-info">
            <strong>Demo Accounts:</strong><br>
            Admin: admin@xochips.com / admin123<br>
            User: Create your own account via Register
        </div> -->
    </div>
</body>
</html>