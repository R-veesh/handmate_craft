<?php
session_start();
include '../db.php'; // Include your database connection

if (!isset($_SESSION['admin_id'])) {
    header("Location: AdminLogin.php"); // Redirect to admin login if not logged in
    exit();
}

// Check if product_id is set
if (isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);

    // First, delete related entries in the cart
    $delete_cart_query = "DELETE FROM cart WHERE product_id = $product_id";
    $con->query($delete_cart_query); // Ignore errors for now; we will handle them later

    // Now delete the product
    $delete_product_query = "DELETE FROM products WHERE product_id = $product_id";

    if ($con->query($delete_product_query)) {
        header("Location: manageProduct.php?success=1"); // Redirect back to manage products with success message
        exit();
    } else {
        echo "Error deleting product: " . $con->error;
    }
} else {
    // Redirect if no product ID is provided
    header("Location: manageProduct.php");
    exit();
}
?>