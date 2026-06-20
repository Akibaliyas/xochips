<?php
session_start();
include "database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: signin.php");
    exit();
}

// Handle product deletion with images
if(isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $product_id = $_GET['delete'];
    
    // Get product images to delete
    $product = mysqli_fetch_assoc(mysqli_query($conn, "SELECT main_image, additional_images FROM products WHERE id='$product_id'"));
    if($product['main_image'] != 'default-product.jpg' && file_exists('uploads/products/' . $product['main_image'])) {
        unlink('uploads/products/' . $product['main_image']);
    }
    if(!empty($product['additional_images'])) {
        $images = explode(',', $product['additional_images']);
        foreach($images as $img) {
            if(file_exists('uploads/products/' . $img)) {
                unlink('uploads/products/' . $img);
            }
        }
    }
    
    mysqli_query($conn, "DELETE FROM products WHERE id='$product_id'");
    header("Location: admin_products.php");
    exit();
}

// Handle product status toggle
if(isset($_GET['toggle_status']) && is_numeric($_GET['toggle_status'])) {
    $product_id = $_GET['toggle_status'];
    $product = mysqli_fetch_assoc(mysqli_query($conn, "SELECT status FROM products WHERE id='$product_id'"));
    $new_status = ($product['status'] == 'active') ? 'inactive' : 'active';
    mysqli_query($conn, "UPDATE products SET status='$new_status' WHERE id='$product_id'");
    header("Location: admin_products.php");
    exit();
}

$products = mysqli_query($conn, "SELECT p.*, c.name as category_name, c.icon 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    ORDER BY p.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Menu - XO Chinese Chips Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #f5f5f5; }
        .admin-sidebar {
            position: fixed; left: 0; top: 0; width: 260px; height: 100vh;
            background: #8B0000; color: white; padding: 30px 20px;
        }
        .admin-main { margin-left: 260px; padding: 20px; }
        .admin-logo { font-size: 24px; text-align: center; margin-bottom: 30px; }
        .admin-nav a {
            display: block; padding: 12px 20px; color: rgba(255,255,255,0.8);
            text-decoration: none; border-radius: 10px; margin-bottom: 5px;
        }
        .admin-nav a:hover, .admin-nav a.active { background: #FF6347; color: white; }
        .page-header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 30px; flex-wrap: wrap; gap: 15px;
        }
        .btn-add {
            background: linear-gradient(135deg, #8B0000, #FF6347);
            color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none;
        }
        .table-container {
            background: white; border-radius: 15px; overflow-x: auto;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; font-weight: 600; }
        .product-thumb { width: 60px; height: 60px; object-fit: cover; border-radius: 10px; }
        .badge {
            display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px;
        }
        .badge-active { background: #4caf50; color: white; }
        .badge-inactive { background: #f44336; color: white; }
        .badge-deal { background: #ff9800; color: white; }
        .btn-edit, .btn-delete, .btn-toggle {
            padding: 5px 10px; border-radius: 5px; text-decoration: none;
            font-size: 12px; margin: 0 2px; display: inline-block;
        }
        .btn-edit { background: #2196f3; color: white; }
        .btn-delete { background: #f44336; color: white; }
        .btn-toggle { background: #ff9800; color: white; }
        @media (max-width: 768px) {
            .admin-sidebar { width: 100%; height: auto; position: relative; }
            .admin-main { margin-left: 0; }
        }
    </style>
</head>
<body>
    <div class="admin-sidebar">
        <div class="admin-logo">🍟 XO Chips Admin</div>
        <div class="admin-nav">
            <a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="admin_products.php" class="active"><i class="fas fa-utensils"></i> Menu Items</a>
            <a href="admin_categories.php"><i class="fas fa-tags"></i> Categories</a>
            <a href="admin_orders.php"><i class="fas fa-shopping-cart"></i> Orders</a>
            <a href="admin_users.php"><i class="fas fa-users"></i> Users</a>
            <a href="admin_add_product.php"><i class="fas fa-plus-circle"></i> Add Item</a>
            <a href="index.php"><i class="fas fa-store"></i> View Store</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
    
    <div class="admin-main">
        <div class="page-header">
            <h1>Manage Menu Items</h1>
            <a href="admin_add_product.php" class="btn-add"><i class="fas fa-plus"></i> Add New Item</a>
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Image</th><th>ID</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($products) == 0): ?>
                    <tr><td colspan="8" style="text-align: center;">No menu items found</td></tr>
                    <?php else: ?>
                    <?php while($product = mysqli_fetch_assoc($products)): 
                        $image_path = 'uploads/products/' . $product['main_image'];
                    ?>
                    <tr>
                        <td>
                            <?php if(file_exists($image_path) && $product['main_image'] != 'default-product.jpg'): ?>
                            <img src="<?php echo $image_path; ?>" class="product-thumb" alt="Product">
                            <?php else: ?>
                            <div style="width:60px;height:60px;background:#fdf0ed;border-radius:10px;display:flex;align-items:center;justify-content:center;">🍟</div>
                            <?php endif; ?>
                        </td>
                        <td>#<?php echo $product['id']; ?></td>
                        <td><?php echo htmlspecialchars($product['name']); ?>
                            <?php if($product['type'] == 'deal'): ?>
                            <span class="badge badge-deal" style="margin-left: 5px;">Deal</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $product['icon']; ?> <?php echo $product['category_name']; ?></td>
                        <td>PKR <?php echo number_format($product['price']); ?></td>
                        <td><?php echo $product['stock_quantity']; ?></td>
                        <td>
                            <span class="badge <?php echo $product['status'] == 'active' ? 'badge-active' : 'badge-inactive'; ?>">
                                <?php echo ucfirst($product['status']); ?>
                            </span>
                        </td>
                        <td>
                            <a href="admin_edit_product.php?id=<?php echo $product['id']; ?>" class="btn-edit"><i class="fas fa-edit"></i> Edit</a>
                            <a href="?toggle_status=<?php echo $product['id']; ?>" class="btn-toggle" onclick="return confirm('Toggle item status?')"><i class="fas fa-power-off"></i> Toggle</a>
                            <a href="?delete=<?php echo $product['id']; ?>" class="btn-delete" onclick="return confirm('Delete this item? This will also delete all images.')"><i class="fas fa-trash"></i> Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>