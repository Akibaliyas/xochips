<?php
session_start();
include "database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: signin.php");
    exit();
}

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if($product_id == 0) {
    header("Location: admin_products.php");
    exit();
}

$product = mysqli_fetch_assoc(mysqli_query($conn, "SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.id = '$product_id'"));
if(!$product) {
    header("Location: admin_products.php");
    exit();
}

$categories = mysqli_query($conn, "SELECT * FROM categories WHERE status='active' ORDER BY name");

$error = "";
$success = "";

if (isset($_POST['update_product'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $short_description = mysqli_real_escape_string($conn, $_POST['short_description']);
    $price = floatval($_POST['price']);
    $compare_price = !empty($_POST['compare_price']) ? floatval($_POST['compare_price']) : 0;
    $category_id = intval($_POST['category_id']);
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $sku = mysqli_real_escape_string($conn, $_POST['sku']);
    $stock_quantity = intval($_POST['stock_quantity']);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_new = isset($_POST['is_new']) ? 1 : 0;
    $is_on_sale = isset($_POST['is_on_sale']) ? 1 : 0;
    $discount_percent = intval($_POST['discount_percent']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $main_image = $product['main_image'];
    if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] == 0) {
        $upload_result = uploadImage($_FILES['main_image'], 'uploads/products/', $product['main_image']);
        if (isset($upload_result['success'])) {
            $main_image = $upload_result['success'];
        } elseif (isset($upload_result['error'])) {
            $error = $upload_result['error'];
        }
    }
    
    // Handle new additional images
    $additional_images = $product['additional_images'];
    if (isset($_FILES['additional_images']) && !empty($_FILES['additional_images']['name'][0])) {
        $new_images = uploadMultipleImages($_FILES['additional_images'], 'uploads/products/');
        if (!empty($new_images)) {
            $existing = !empty($additional_images) ? explode(',', $additional_images) : [];
            $all_images = array_merge($existing, $new_images);
            $additional_images = implode(',', $all_images);
        }
    }
    
    if (empty($error)) {
        $update_query = "UPDATE products SET 
                        name='$name', description='$description', short_description='$short_description',
                        price='$price', compare_price='$compare_price', category_id='$category_id',
                        type='$type', sku='$sku', stock_quantity='$stock_quantity',
                        is_featured='$is_featured', is_new='$is_new', is_on_sale='$is_on_sale',
                        discount_percent='$discount_percent', status='$status',
                        main_image='$main_image', additional_images='$additional_images'
                        WHERE id='$product_id'";
        
        if (mysqli_query($conn, $update_query)) {
            $success = "Product updated successfully!";
            $product = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM products WHERE id='$product_id'"));
        } else {
            $error = "Update failed: " . mysqli_error($conn);
        }
    }
}

// Delete additional image
if(isset($_GET['delete_img']) && isset($_GET['img'])) {
    $img_to_delete = $_GET['img'];
    $additional_images = $product['additional_images'];
    $images_array = explode(',', $additional_images);
    
    if(($key = array_search($img_to_delete, $images_array)) !== false) {
        unset($images_array[$key]);
        if(file_exists('uploads/products/' . $img_to_delete)) {
            unlink('uploads/products/' . $img_to_delete);
        }
        $new_images = implode(',', $images_array);
        mysqli_query($conn, "UPDATE products SET additional_images='$new_images' WHERE id='$product_id'");
        header("Location: admin_edit_product.php?id=$product_id");
        exit();
    }
}

$additional_images_array = !empty($product['additional_images']) ? explode(',', $product['additional_images']) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Menu Item - XO Chips Admin</title>
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
        .form-card {
            background: white; border-radius: 15px; padding: 30px;
            max-width: 1000px; margin: 0 auto;
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
        .btn-cancel {
            background: #999; color: white; padding: 12px 30px;
            border: none; border-radius: 8px; cursor: pointer;
            text-decoration: none; display: inline-block; margin-left: 10px;
        }
        .alert-error, .alert-success {
            padding: 12px; border-radius: 8px; margin-bottom: 20px;
        }
        .alert-error { background: #fee; color: #d32f2f; border-left: 4px solid #d32f2f; }
        .alert-success { background: #e8f5e9; color: #2e7d32; border-left: 4px solid #2e7d32; }
        .current-image { margin: 10px 0; }
        .current-image img { max-width: 150px; border-radius: 8px; border: 2px solid #ddd; }
        .image-preview { margin-top: 10px; }
        .image-preview img { max-width: 150px; border-radius: 8px; }
        .additional-images {
            display: flex; flex-wrap: wrap; gap: 15px; margin-top: 10px;
        }
        .additional-image-item {
            position: relative; width: 100px;
        }
        .additional-image-item img {
            width: 100%; height: 100px; object-fit: cover;
            border-radius: 8px; border: 2px solid #ddd;
        }
        .additional-image-item .delete-img {
            position: absolute; top: -10px; right: -10px;
            background: #f44336; color: white; border-radius: 50%;
            width: 25px; height: 25px; display: flex;
            align-items: center; justify-content: center;
            text-decoration: none; font-size: 12px;
        }
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
        <h1>Edit Menu Item</h1>
        <p style="margin-bottom: 20px;">Editing: <strong><?php echo htmlspecialchars($product['name']); ?></strong></p>
        
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
                    <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Price (PKR) *</label>
                        <input type="number" name="price" value="<?php echo $product['price']; ?>" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>Compare Price (Optional)</label>
                        <input type="number" name="compare_price" value="<?php echo $product['compare_price']; ?>" step="0.01">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Category *</label>
                        <select name="category_id" required>
                            <option value="">Select Category</option>
                            <?php while($cat = mysqli_fetch_assoc($categories)): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo ($product['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo $cat['icon']; ?> <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Item Type</label>
                        <select name="type">
                            <option value="main" <?php echo $product['type']=='main'?'selected':''; ?>>Main Dish</option>
                            <option value="deal" <?php echo $product['type']=='deal'?'selected':''; ?>>Deal/Combo</option>
                            <option value="drink" <?php echo $product['type']=='drink'?'selected':''; ?>>Drink</option>
                            <option value="condiment" <?php echo $product['type']=='condiment'?'selected':''; ?>>Condiment</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>SKU</label>
                        <input type="text" name="sku" value="<?php echo htmlspecialchars($product['sku']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Stock Quantity</label>
                        <input type="number" name="stock_quantity" value="<?php echo $product['stock_quantity']; ?>">
                    </div>
                </div>
                
                <div class="form-row-3">
                    <div class="form-group">
                        <label>Discount Percent</label>
                        <input type="number" name="discount_percent" value="<?php echo $product['discount_percent']; ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="active" <?php echo $product['status']=='active'?'selected':''; ?>>Active</option>
                        <option value="inactive" <?php echo $product['status']=='inactive'?'selected':''; ?>>Inactive</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Short Description</label>
                    <textarea name="short_description" rows="2"><?php echo htmlspecialchars($product['short_description']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Full Description *</label>
                    <textarea name="description" rows="5" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                </div>
                
                <div class="checkbox-group">
                    <label><input type="checkbox" name="is_featured" value="1" <?php echo $product['is_featured']?'checked':''; ?>> Featured Item</label>
                    <label><input type="checkbox" name="is_new" value="1" <?php echo $product['is_new']?'checked':''; ?>> New Arrival</label>
                    <label><input type="checkbox" name="is_on_sale" value="1" <?php echo $product['is_on_sale']?'checked':''; ?>> On Sale</label>
                </div>
                
                <hr>
                
                <div class="form-group">
                    <label>Current Main Image</label>
                    <div class="current-image">
                        <?php 
                        $img_path = 'uploads/products/' . $product['main_image'];
                        if(file_exists($img_path) && $product['main_image'] != 'default-product.jpg'): ?>
                        <img src="<?php echo $img_path; ?>" alt="Current Main Image">
                        <?php else: ?>
                        <div style="width:150px;height:150px;background:#fdf0ed;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:48px;">🍟</div>
                        <?php endif; ?>
                    </div>
                    <label style="margin-top: 15px;">Change Main Image (Optional)</label>
                    <input type="file" name="main_image" accept="image/*" onchange="previewMainImage(this)">
                    <div id="mainImagePreview" class="image-preview"></div>
                </div>
                
                <?php if(!empty($additional_images_array)): ?>
                <div class="form-group">
                    <label>Additional Images</label>
                    <div class="additional-images">
                        <?php foreach($additional_images_array as $img): 
                            $img_path = 'uploads/products/' . $img;
                            if(file_exists($img_path)):
                        ?>
                        <div class="additional-image-item">
                            <img src="<?php echo $img_path; ?>" alt="Additional Image">
                            <a href="?delete_img=1&img=<?php echo urlencode($img); ?>&id=<?php echo $product_id; ?>" class="delete-img" onclick="return confirm('Delete this image?')">×</a>
                        </div>
                        <?php endif; endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label>Add More Images (Optional)</label>
                    <input type="file" name="additional_images[]" accept="image/*" multiple onchange="previewAdditionalImages(this)">
                    <small>You can upload up to 5 additional images (Max 5MB each)</small>
                    <div id="additionalImagesPreview" class="additional-images"></div>
                </div>
                
                <div style="margin-top: 30px;">
                    <button type="submit" name="update_product" class="btn-submit"><i class="fas fa-save"></i> Update Item</button>
                    <a href="admin_products.php" class="btn-cancel"><i class="fas fa-times"></i> Cancel</a>
                </div>
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
            } else {
                preview.innerHTML = '';
            }
        }
        
        function previewAdditionalImages(input) {
            const preview = document.getElementById('additionalImagesPreview');
            if (input.files) {
                preview.innerHTML = '';
                Array.from(input.files).forEach(file => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const div = document.createElement('div');
                        div.className = 'additional-image-item';
                        div.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                        preview.appendChild(div);
                    }
                    reader.readAsDataURL(file);
                });
            }
        }
    </script>
</body>
</html>