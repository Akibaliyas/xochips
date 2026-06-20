<?php
session_start();
include "database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: signin.php");
    exit();
}

// Add category
if(isset($_POST['add_category'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $slug = strtolower(str_replace(' ', '-', $name));
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $icon = mysqli_real_escape_string($conn, $_POST['icon']);
    
    // Handle category image upload
    $image = 'default-category.jpg';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $upload_result = uploadImage($_FILES['image'], 'uploads/categories/');
        if (isset($upload_result['success'])) {
            $image = $upload_result['success'];
        }
    }
    
    $query = "INSERT INTO categories (name, slug, description, icon, image, status) VALUES ('$name', '$slug', '$description', '$icon', '$image', 'active')";
    if(mysqli_query($conn, $query)) {
        $_SESSION['message'] = "Category added successfully!";
    } else {
        $_SESSION['error'] = "Failed to add category: " . mysqli_error($conn);
    }
    header("Location: admin_categories.php");
    exit();
}

// Update category
if(isset($_POST['update_category'])) {
    $id = intval($_POST['id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $slug = strtolower(str_replace(' ', '-', $name));
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $icon = mysqli_real_escape_string($conn, $_POST['icon']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $query = "UPDATE categories SET name='$name', slug='$slug', description='$description', icon='$icon', status='$status' WHERE id='$id'";
    if(mysqli_query($conn, $query)) {
        $_SESSION['message'] = "Category updated successfully!";
    } else {
        $_SESSION['error'] = "Failed to update category";
    }
    header("Location: admin_categories.php");
    exit();
}

// Delete category
if(isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $cat_id = $_GET['delete'];
    // Check if category has products
    $check = mysqli_query($conn, "SELECT id FROM products WHERE category_id='$cat_id' LIMIT 1");
    if(mysqli_num_rows($check) > 0) {
        $_SESSION['error'] = "Cannot delete category with existing products!";
    } else {
        mysqli_query($conn, "DELETE FROM categories WHERE id='$cat_id'");
        $_SESSION['message'] = "Category deleted successfully!";
    }
    header("Location: admin_categories.php");
    exit();
}

// Toggle category status
if(isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $id = $_GET['toggle'];
    $cat = mysqli_fetch_assoc(mysqli_query($conn, "SELECT status FROM categories WHERE id='$id'"));
    $new_status = ($cat['status'] == 'active') ? 'inactive' : 'active';
    mysqli_query($conn, "UPDATE categories SET status='$new_status' WHERE id='$id'");
    header("Location: admin_categories.php");
    exit();
}

$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY id DESC");

// Get category for editing
$edit_category = null;
if(isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $edit_result = mysqli_query($conn, "SELECT * FROM categories WHERE id='$edit_id'");
    $edit_category = mysqli_fetch_assoc($edit_result);
}

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
    <title>Manage Categories - XO Chinese Chips Admin</title>
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
        .form-card, .table-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        input, select, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: inherit;
        }
        .btn-submit {
            background: linear-gradient(135deg, #8B0000, #FF6347);
            color: white;
            padding: 10px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
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
        .btn-edit, .btn-delete, .btn-toggle {
            padding: 5px 10px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 12px;
            margin: 0 2px;
            display: inline-block;
        }
        .btn-edit {
            background: #2196f3;
            color: white;
        }
        .btn-delete {
            background: #f44336;
            color: white;
        }
        .btn-toggle {
            background: #ff9800;
            color: white;
        }
        .badge-active {
            background: #4caf50;
            color: white;
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 11px;
        }
        .badge-inactive {
            background: #f44336;
            color: white;
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 11px;
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
            <a href="admin_categories.php" class="active"><i class="fas fa-tags"></i> Categories</a>
            <a href="admin_orders.php"><i class="fas fa-shopping-cart"></i> Orders</a>
            <a href="admin_users.php"><i class="fas fa-users"></i> Users</a>
            <a href="admin_add_product.php"><i class="fas fa-plus-circle"></i> Add Item</a>
            <a href="admin_profile.php"><i class="fas fa-user-cog"></i> Profile</a>
            <a href="index.php"><i class="fas fa-store"></i> View Store</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
    
    <div class="admin-main">
        <h1>Manage Categories</h1>
        
        <?php if($message): ?>
        <div class="alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if($error): ?>
        <div class="alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <!-- Add/Edit Category Form -->
        <div class="form-card">
            <h3><?php echo $edit_category ? 'Edit Category' : 'Add New Category'; ?></h3>
            <form method="post" enctype="multipart/form-data">
                <?php if($edit_category): ?>
                <input type="hidden" name="id" value="<?php echo $edit_category['id']; ?>">
                <?php endif; ?>
                <div class="form-group">
                    <label>Category Name *</label>
                    <input type="text" name="name" value="<?php echo $edit_category ? htmlspecialchars($edit_category['name']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label>Icon (Emoji) *</label>
                    <input type="text" name="icon" value="<?php echo $edit_category ? $edit_category['icon'] : ''; ?>" required placeholder="e.g., 🍟">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="2" placeholder="Category description"><?php echo $edit_category ? htmlspecialchars($edit_category['description']) : ''; ?></textarea>
                </div>
                <?php if($edit_category): ?>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="active" <?php echo $edit_category['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo $edit_category['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                <?php endif; ?>
                <button type="submit" name="<?php echo $edit_category ? 'update_category' : 'add_category'; ?>" class="btn-submit">
                    <?php echo $edit_category ? 'Update Category' : 'Add Category'; ?>
                </button>
                <?php if($edit_category): ?>
                <a href="admin_categories.php" class="btn-submit" style="background: #666; text-decoration: none; margin-left: 10px;">Cancel</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Categories List -->
        <div class="table-container">
            <h3>All Categories</h3>
            <table>
                <thead>
                    <tr>
                        <th>Icon</th>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($categories) == 0): ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">No categories found</td>
                    </tr>
                    <?php else: ?>
                    <?php while($cat = mysqli_fetch_assoc($categories)): ?>
                    <tr>
                        <td style="font-size: 28px;"><?php echo $cat['icon']; ?></td>
                        <td><strong><?php echo htmlspecialchars($cat['name']); ?></strong></td>
                        <td><?php echo $cat['slug']; ?></td>
                        <td><?php echo substr(htmlspecialchars($cat['description']), 0, 50); ?></td>
                        <td>
                            <span class="<?php echo $cat['status'] == 'active' ? 'badge-active' : 'badge-inactive'; ?>">
                                <?php echo ucfirst($cat['status']); ?>
                            </span>
                        </td>
                        <td>
                            <a href="?edit=<?php echo $cat['id']; ?>" class="btn-edit"><i class="fas fa-edit"></i> Edit</a>
                            <a href="?toggle=<?php echo $cat['id']; ?>" class="btn-toggle" onclick="return confirm('Toggle category status?')"><i class="fas fa-power-off"></i> Toggle</a>
                            <a href="?delete=<?php echo $cat['id']; ?>" class="btn-delete" onclick="return confirm('Delete this category? This will not delete products but they will become uncategorized.')"><i class="fas fa-trash"></i> Delete</a>
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