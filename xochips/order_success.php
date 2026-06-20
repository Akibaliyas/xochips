<?php
session_start();
include "database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

$order_number = isset($_GET['order']) ? $_GET['order'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmed - XO Chinese Chips</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #8B0000, #FF6347);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .success-card {
            background: white;
            border-radius: 40px;
            padding: 60px;
            text-align: center;
            max-width: 550px;
            margin: 20px;
            animation: slideUp 0.5s ease;
            box-shadow: 0 30px 60px rgba(0,0,0,0.3);
        }
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .success-icon {
            font-size: 100px;
            color: #4caf50;
            margin-bottom: 20px;
        }
        h1 {
            color: #8B0000;
            margin-bottom: 15px;
            font-size: 32px;
        }
        .order-number {
            background: #fff5f0;
            padding: 15px;
            border-radius: 15px;
            margin: 25px 0;
            font-family: monospace;
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .estimated-time {
            background: #e8f5e9;
            padding: 15px;
            border-radius: 15px;
            margin: 20px 0;
            color: #2e7d32;
        }
        .btn-continue {
            display: inline-block;
            padding: 14px 35px;
            background: linear-gradient(135deg, #8B0000, #FF6347);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            margin: 10px 5px;
            transition: all 0.3s;
        }
        .btn-continue:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(255,99,71,0.3);
        }
        .btn-outline {
            background: transparent;
            border: 2px solid #FF6347;
            color: #FF6347;
        }
        .btn-outline:hover {
            background: #FF6347;
            color: white;
        }
        @media (max-width: 500px) {
            .success-card {
                padding: 35px;
            }
            h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="success-card">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h1>Order Confirmed! 🎉</h1>
        <p>Thank you for ordering from XO Chinese Chips!</p>
        
        <div class="order-number">
            Order #: <?php echo $order_number; ?>
        </div>
        
        <div class="estimated-time">
            <i class="fas fa-clock"></i> Estimated Delivery Time: 30-45 minutes
        </div>
        
        <p>You will receive a confirmation SMS shortly with tracking details.</p>
        
        <div style="margin-top: 30px;">
            <a href="dashboard.php" class="btn-continue">Track My Order</a>
            <a href="shop.php" class="btn-continue btn-outline">Order More</a>
        </div>
    </div>
</body>
</html>