<?php
session_start();
include "database.php";

if(isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = "";
$success = "";
$cities = ['Karachi', 'Lahore', 'Islamabad', 'Rawalpindi', 'Multan', 'Faisalabad', 'Peshawar', 'Quetta'];

if (isset($_POST['register'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    
    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters!";
    } else {
        $check_query = "SELECT id FROM users WHERE username='$username' OR email='$email'";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error = "Username or email already exists!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $query = "INSERT INTO users (username, email, password, full_name, phone, city, role) 
                      VALUES ('$username', '$email', '$hashed_password', '$full_name', '$phone', '$city', 'user')";
            
            if (mysqli_query($conn, $query)) {
                $success = "Registration successful! Redirecting to login...";
                echo "<script>setTimeout(function(){ window.location.href='signin.php'; }, 2000);</script>";
            } else {
                $error = "Registration failed: " . mysqli_error($conn);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - XO Chinese Chips</title>
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
            padding: 40px 20px;
        }
        .auth-card {
            background: white;
            border-radius: 30px;
            padding: 40px;
            max-width: 550px;
            width: 100%;
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
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e8e8e8;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s ease;
        }
        .form-group input:focus, .form-group select:focus {
            border-color: #FF6347;
            outline: none;
            box-shadow: 0 0 0 3px rgba(255,99,71,0.1);
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
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
        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #2e7d32;
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
        @media (max-width: 500px) {
            .auth-card {
                padding: 25px;
            }
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="auth-header">
            <div class="logo-icon">🍟</div>
            <h2>Create Account</h2>
            <p>Join the XO Chinese Chips family</p>
        </div>
        
        <?php if($error): ?>
        <div class="alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if($success): ?>
        <div class="alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="post">
            <div class="form-row">
                <div class="form-group">
                    <label>Username *</label>
                    <input type="text" name="username" required placeholder="Username">
                </div>
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="full_name" required placeholder="Full Name">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" required placeholder="Email">
                </div>
                <div class="form-group">
                    <label>Phone *</label>
                    <input type="tel" name="phone" required placeholder="03XXXXXXXXX">
                </div>
            </div>
            
            <div class="form-group">
                <label>City *</label>
                <select name="city" required>
                    <option value="">Select City</option>
                    <?php foreach($cities as $city): ?>
                    <option value="<?php echo $city; ?>"><?php echo $city; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Password *</label>
                    <input type="password" name="password" required placeholder="Min. 6 characters">
                </div>
                <div class="form-group">
                    <label>Confirm Password *</label>
                    <input type="password" name="confirm_password" required placeholder="Re-enter password">
                </div>
            </div>
            
            <button type="submit" name="register" class="btn-auth">Create Account <i class="fas fa-user-plus"></i></button>
        </form>
        
        <div class="auth-footer">
            <p>Already have an account? <a href="signin.php">Sign In</a></p>
        </div>
    </div>
</body>
</html>