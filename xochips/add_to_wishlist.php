<?php
session_start();
include "database.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Store the intended page to redirect back after login
    $_SESSION['redirect_after_login'] = $_SERVER['HTTP_REFERER'] ?? 'index.php';
    header("Location: signin.php?error=please_login");
    exit();
}

$user_id = intval($_SESSION['user_id']);

// Verify user exists in database
$check_user = mysqli_query($conn, "SELECT id FROM users WHERE id = '$user_id'");
if (mysqli_num_rows($check_user) == 0) {
    session_destroy();
    header("Location: signin.php?error=invalid_user");
    exit();
}

if(isset($_GET['id'])) {
    $product_id = intval($_GET['id']);
    
    // Verify product exists
    $check_product = mysqli_query($conn, "SELECT id FROM products WHERE id = '$product_id' AND status = 'active'");
    if (mysqli_num_rows($check_product) == 0) {
        $_SESSION['wishlist_message'] = "Product not found!";
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'shop.php'));
        exit();
    }
    
    // Check if already in wishlist
    $check = mysqli_query($conn, "SELECT id FROM wishlist WHERE user_id='$user_id' AND product_id='$product_id'");
    
    if(mysqli_num_rows($check) == 0) {
        // Add to wishlist
        $insert = mysqli_query($conn, "INSERT INTO wishlist (user_id, product_id) VALUES ('$user_id', '$product_id')");
        if($insert) {
            $_SESSION['wishlist_message'] = "✓ Item added to wishlist!";
        } else {
            $_SESSION['wishlist_message'] = "Error adding to wishlist: " . mysqli_error($conn);
        }
    } else {
        // Remove from wishlist (toggle)
        mysqli_query($conn, "DELETE FROM wishlist WHERE user_id='$user_id' AND product_id='$product_id'");
        $_SESSION['wishlist_message'] = "Item removed from wishlist!";
    }
}

// Redirect back to previous page
if(isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
    header("Location: " . $_SERVER['HTTP_REFERER']);
} else {
    header("Location: wishlist.php");
}
exit();
?>