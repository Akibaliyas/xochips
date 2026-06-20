<?php
$server = "localhost";
$user = "root";
$password = "";
$database = "xo_chips_db";

$conn = mysqli_connect($server, $user, $password, $database);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");

// Image upload function with multiple file support
function uploadImage($file, $target_dir, $existing_image = null) {
    if (isset($file) && $file['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $filename = $file['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (!in_array($ext, $allowed)) {
            return ['error' => 'Only JPG, JPEG, PNG, GIF, WEBP files are allowed'];
        }
        
        if ($file['size'] > 5 * 1024 * 1024) {
            return ['error' => 'File size must be less than 5MB'];
        }
        
        // Create directory if not exists
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $new_filename = time() . '_' . uniqid() . '.' . $ext;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            if ($existing_image && file_exists($target_dir . $existing_image) && $existing_image != 'default-product.jpg' && $existing_image != 'default-avatar.png') {
                unlink($target_dir . $existing_image);
            }
            return ['success' => $new_filename];
        }
    }
    return ['success' => $existing_image];
}

function uploadMultipleImages($files, $target_dir) {
    $uploaded = [];
    $errors = [];
    
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    foreach ($files['tmp_name'] as $key => $tmp_name) {
        if ($files['error'][$key] == 0) {
            $filename = $files['name'][$key];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (in_array($ext, $allowed) && $files['size'][$key] <= 5 * 1024 * 1024) {
                $new_filename = time() . '_' . uniqid() . '_' . $key . '.' . $ext;
                $target_file = $target_dir . $new_filename;
                
                if (move_uploaded_file($tmp_name, $target_file)) {
                    $uploaded[] = $new_filename;
                }
            }
        }
    }
    
    return $uploaded;
}

function getCartCount($user_id) {
    global $conn;
    $result = mysqli_query($conn, "SELECT SUM(quantity) as total FROM cart WHERE user_id='$user_id'");
    $data = mysqli_fetch_assoc($result);
    return $data['total'] ?? 0;
}

function getWishlistCount($user_id) {
    global $conn;
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM wishlist WHERE user_id='$user_id'");
    $data = mysqli_fetch_assoc($result);
    return $data['total'] ?? 0;
}

function generateOrderNumber() {
    return 'XO-' . date('Ymd') . '-' . rand(1000, 9999);
}

function isStoreOpen() {
    global $conn;
    $current_day = date('l');
    $current_time = date('H:i:s');
    
    $result = mysqli_query($conn, "SELECT * FROM business_hours WHERE day_of_week='$current_day' AND is_closed=0");
    $hours = mysqli_fetch_assoc($result);
    
    if ($hours && $current_time >= $hours['open_time'] && $current_time <= $hours['close_time']) {
        return true;
    }
    return false;
}
?>