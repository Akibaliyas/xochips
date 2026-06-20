<?php
session_start();
include "database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: signin.php");
    exit();
}

$error = "";
$success = "";

$categories = mysqli_query($conn, "SELECT * FROM categories WHERE status='active'");

if (isset($_POST['add_product'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $slug = strtolower(str_replace(' ', '-', $name)) . '-' . time();
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $short_description = mysqli_real_escape_string($conn, $_POST['short_description']);
    $price = floatval($_POST['price']);
    $compare_price = floatval($_POST['compare_price']);
    $category_id = intval($_POST['category_id']);
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $sku = mysqli_real_escape_string($conn, $_POST['sku']);
    $stock_quantity = intval($_POST['stock_quantity']);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_new = isset($_POST['is_new']) ? 1 : 0;
    $is_on_sale = isset($_POST['is_on_sale']) ? 1 : 0;
    $discount_percent = intval($_POST['discount_percent']);
    
    // Handle main image upload
    $main_image = 'default-product.jpg';
    if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] == 0) {
        $upload_result = uploadImage($_FILES['main_image'], 'uploads/products/');
        if (isset($upload_result['success'])) {
            $main_image = $upload_result['success'];
        } elseif (isset($upload_result['error'])) {
            $error = $upload_result['error'];
        }
    } else {
        $error = "Please upload a main image";
    }
    
    // Handle additional images
    $additional_images = '';
    if (isset($_FILES['additional_images']) && !empty($_FILES['additional_images']['name'][0])) {
        $uploaded_images = uploadMultipleImages($_FILES['additional_images'], 'uploads/products/');
        if (!empty($uploaded_images)) {
            $additional_images = implode(',', $uploaded_images);
        }
    }
    
    if (empty($error)) {
        $query = "INSERT INTO products (name, slug, description, short_description, price, compare_price, category_id, type, sku, stock_quantity, is_featured, is_new, is_on_sale, discount_percent, main_image, additional_images, status) 
                  VALUES ('$name', '$slug', '$description', '$short_description', '$price', '$compare_price', '$category_id', '$type', '$sku', '$stock_quantity', '$is_featured', '$is_new', '$is_on_sale', '$discount_percent', '$main_image', '$additional_images', 'active')";
        
        if (mysqli_query($conn, $query)) {
            $success = "Product added successfully!";
            echo "<script>setTimeout(function(){ window.location.href='admin_products.php'; }, 1500);</script>";
        } else {
            $error = "Failed to add product: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Menu Item - XO Chinese Chips Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #f5f5f5; }
        .admin-sidebar {
            position: fixed; left: 0; top: 0; width: 260px; height: 100vh;
            background: #8B0000; color: white; padding: 30px 20px; overflow-y: auto;
        }
        .admin-main { margin-left: 260px; padding: 20px; }
        .admin-logo { font-size: 24px; text-align: center; margin-bottom: 30px; }
        .admin-nav a {
            display: block; padding: 12px 20px; color: rgba(255,255,255,0.8);
            text-decoration: none; border-radius: 10px; margin-bottom: 5px;
        }
        .admin-nav a:hover, .admin-nav a.active { background: #FF6347; color: white; }
        .form-card {
            background: white; border-radius: 15px; padding: 30px;
            max-width: 900px; margin: 0 auto;
        }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 500; }
        input, select, textarea {
            width: 100%; padding: 12px; border: 1px solid #ddd;
            border-radius: 8px; font-family: inherit;
        }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-row-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; }
        .checkbox-group {
            display: flex; gap: 20px; align-items: center; flex-wrap: wrap;
        }
        .checkbox-group label { display: flex; align-items: center; gap: 8px; margin-bottom: 0; }
        .checkbox-group input { width: auto; }
        .btn-submit {
            background: linear-gradient(135deg, #8B0000, #FF6347);
            color: white; padding: 12px 30px; border: none;
            border-radius: 8px; cursor: pointer; font-size: 16px;
        }
        .alert-error, .alert-success {
            padding: 12px; border-radius: 8px; margin-bottom: 20px;
        }
        .alert-error { background: #fee; color: #d32f2f; border-left: 4px solid #d32f2f; }
        .alert-success { background: #e8f5e9; color: #2e7d32; border-left: 4px solid #2e7d32; }
        .image-preview { margin-top: 10px; }
        .image-preview img { max-width: 150px; border-radius: 8px; margin: 5px; }
        .additional-preview { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px; }
        hr { margin: 20px 0; border: none; border-top: 1px solid #eee; }
        @media (max-width: 768px) {
            .admin-sidebar { width: 100%; height: auto; position: relative; }
            .admin-main { margin-left: 0; }
            .form-row, .form-row-3 { grid-template-columns: 1fr; }
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
            <a href="admin_add_product.php" class="active"><i class="fas fa-plus-circle"></i> Add Item</a>
            <a href="index.php"><i class="fas fa-store"></i> View Store</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
    
    <div class="admin-main">
        <h1>Add New Menu Item</h1>
        
        <div class="form-card">
            <?php if($error): ?>
            <div class="alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if($success): ?>
            <div class="alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Item Name *</label>
                    <input type="text" name="name" required placeholder="e.g., Chicken Wok Tossed Fries">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Price (PKR) *</label>
                        <input type="number" name="price" required step="0.01" placeholder="e.g., 550">
                    </div>
                    <div class="form-group">
                        <label>Compare Price (Optional)</label>
                        <input type="number" name="compare_price" step="0.01" placeholder="e.g., 650">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Category *</label>
                        <select name="category_id" required>
                            <option value="">Select Category</option>
                            <?php while($cat = mysqli_fetch_assoc($categories)): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo $cat['icon']; ?> <?php echo $cat['name']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Item Type</label>
                        <select name="type">
                            <option value="main">Main Dish</option>
                            <option value="deal">Deal/Combo</option>
                            <option value="drink">Drink</option>
                            <option value="condiment">Condiment</option>
                            <option value="energy">Energy Drink</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>SKU</label>
                        <input type="text" name="sku" placeholder="e.g., XO-001">
                    </div>
                    <div class="form-group">
                        <label>Stock Quantity</label>
                        <input type="number" name="stock_quantity" value="999">
                    </div>
                </div>
                
                <div class="form-row-3">
                    <div class="form-group">
                        <label>Discount Percent</label>
                        <input type="number" name="discount_percent" value="0" placeholder="0">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Short Description</label>
                    <textarea name="short_description" rows="2" placeholder="Brief description for card"></textarea>
                </div>
                
                <div class="form-group">
                    <label>Full Description *</label>
                    <textarea name="description" rows="5" required placeholder="Detailed description"></textarea>
                </div>
                
                <div class="checkbox-group">
                    <label><input type="checkbox" name="is_featured" value="1"> Featured Item</label>
                    <label><input type="checkbox" name="is_new" value="1" checked> New Arrival</label>
                    <label><input type="checkbox" name="is_on_sale" value="1"> On Sale</label>
                </div>
                
                <hr>
                
                <div class="form-group">
                    <label>Main Image *</label>
                    <input type="file" name="main_image" accept="image/*" onchange="previewMainImage(this)" required>
                    <div id="mainImagePreview" class="image-preview"></div>
                </div>
                
                <div class="form-group">
                    <label>Additional Images (Optional, up to 5)</label>
                    <input type="file" name="additional_images[]" accept="image/*" multiple onchange="previewAdditionalImages(this)">
                    <small>You can upload up to 5 additional images (Max 5MB each)</small>
                    <div id="additionalImagesPreview" class="additional-preview"></div>
                </div>
                
                <button type="submit" name="add_product" class="btn-submit">Add Menu Item</button>
            </form>
        </div>
    </div>
    
    <script>
        function previewMainImage(input) {
            const preview = document.getElementById('mainImagePreview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        function previewAdditionalImages(input) {
            const preview = document.getElementById('additionalImagesPreview');
            if (input.files) {
                preview.innerHTML = '';
                Array.from(input.files).forEach(file => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.style.maxWidth = '100px';
                        img.style.margin = '5px';
                        img.style.borderRadius = '8px';
                        preview.appendChild(img);
                    }
                    reader.readAsDataURL(file);
                });
            }
        }
    </script>
</body>
</html>