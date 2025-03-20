<?php
session_start();
include '../db.php'; 

if (!isset($_SESSION['admin_id'])) {
    header("Location: AdminLogin.php"); 
    exit();
}

// Fetch product details based on product_id
if (isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);
    // Join products with categories to get category name
    $product = $con->query("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id WHERE p.product_id = $product_id")->fetch_assoc();
} else {
    // Redirect if no product ID is provided
    header("Location: manageProduct.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_product'])) {
    // Update product details in the database
    $name = $con->real_escape_string($_POST['name']);
    $description = $con->real_escape_string($_POST['description']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $category_id = intval($_POST['category_id']); // Get selected category ID

    $update_query = "UPDATE products SET name='$name', description='$description', price='$price', stock='$stock', category_id='$category_id' WHERE product_id=$product_id";
    
    if ($con->query($update_query)) {
        header("Location: manageProduct.php?success=1"); // Redirect back to manage products
        exit();
    } else {
        echo "Error updating product: " . $con->error;
    }
}

$categories = $con->query("SELECT * FROM categories")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="./main.css">
    <title>Update Product</title>
</head>
<body>
<header>
    <h1>Update Product</h1>
</header>
<nav>
    <ul>
        <li><a href="index.php">Dashboard</a></li>
        <li><a href="ManageProduct.php">Manage Products</a></li>
        <li><a href="ManageOrder.php">Manage Orders</a></li>
        <li><a href="ManagePayement.php">Manage Payments</a></li>
        <li><a href="ViewReport.php">View Reports</a></li>
    </ul>
</nav>
<div>
    <form action="" method="POST">
        <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['product_id']); ?>">
        
        <label for="name">Product Name:</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>

        <label for="description">Description:</label>
        <textarea id="description" name="description" required><?php echo htmlspecialchars($product['description']); ?></textarea>

        <label for="price">Price:</label>
        <input type="number" id="price" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" step="0.01" required>

        <label for="stock">Stock:</label>
        <input type="number" id="stock" name="stock" value="<?php echo htmlspecialchars($product['stock']); ?>" required>

        <!-- Category Dropdown -->
        <label for="category">Category:</label>
        <select id="category" name="category_id" required>
            <?php foreach ($categories as $category): ?>
                <option value="<?php echo htmlspecialchars($category['category_id']); ?>" <?php echo ($category['category_id'] == $product['category_id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($category['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <!-- Display Product Image -->
        <label>Current Image:</label><br>
        <?php 
        $image_path = '../' . $product['Image']; 
        if (!empty($image_path) && file_exists($image_path)): ?>
            <img src="<?php echo htmlspecialchars($image_path); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
        <?php else: ?>
            <div>No image available</div>
        <?php endif; ?>

        <!-- Input for New Image (optional) -->
        <label for="image">Upload New Image (optional):</label>
        <input type="file" id="image" name="image">
        
        <input type="submit" name="update_product" value="Update Product">
    </form>
</div>
</body>
</html>