<?php
session_start();
include "database.php";

if(!isset($_GET['id'])) {
    die("Product not found");
}

$product_id = intval($_GET['id']);
$product = mysqli_fetch_assoc(mysqli_query($conn, "SELECT p.*, c.name as category_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE p.id='$product_id' AND p.status='active'"));

if(!$product) {
    die("Product not found");
}

$image_path = 'uploads/products/' . $product['main_image'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Quick View</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: rgba(0,0,0,0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .quick-view-modal {
            background: white;
            border-radius: 30px;
            max-width: 900px;
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            overflow: hidden;
            animation: slideUp 0.3s ease;
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
        .quick-view-image {
            background: #fff5f0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px;
        }
        .quick-view-image img {
            max-width: 100%;
            max-height: 350px;
            object-fit: cover;
            border-radius: 20px;
        }
        .quick-view-info {
            padding: 30px;
        }
        .quick-view-info h2 {
            font-size: 24px;
            margin-bottom: 10px;
            color: #8B0000;
        }
        .quick-view-info .price {
            font-size: 28px;
            font-weight: bold;
            color: #FF6347;
            margin: 15px 0;
        }
        .quick-view-info .description {
            color: #666;
            line-height: 1.6;
            margin: 15px 0;
        }
        .btn-add {
            display: inline-block;
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #8B0000, #FF6347);
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            margin-top: 20px;
        }
        .close-btn {
            position: absolute;
            top: 20px;
            right: 30px;
            font-size: 30px;
            cursor: pointer;
            color: white;
        }
        @media (max-width: 700px) {
            .quick-view-modal {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="quick-view-modal">
        <div class="quick-view-image">
            <?php if(file_exists($image_path) && $product['main_image'] != 'default-product.jpg'): ?>
            <img src="<?php echo $image_path; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
            <?php else: ?>
            <div style="font-size: 80px;">🍟</div>
            <?php endif; ?>
        </div>
        <div class="quick-view-info">
            <h2><?php echo htmlspecialchars($product['name']); ?></h2>
            <span class="product-category" style="color: #999; font-size: 14px;"><?php echo $product['category_name']; ?></span>
            <div class="price">PKR <?php echo number_format($product['price']); ?></div>
            <div class="description"><?php echo htmlspecialchars($product['short_description']); ?></div>
            <a href="product_details.php?id=<?php echo $product['id']; ?>" class="btn-add">View Full Details <i class="fas fa-arrow-right"></i></a>
            <a href="add_to_wishlist.php?id=<?php echo $product['id']; ?>" class="btn-add" style="background: #666; margin-top: 10px;">❤️ Save to Wishlist</a>
        </div>
    </div>
    <script>
        // Close on ESC key
        document.addEventListener('keydown', function(e) {
            if(e.key === 'Escape') {
                window.close();
            }
        });
    </script>
</body>
</html>