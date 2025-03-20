<?php
session_start();
include '../db.php'; // Include your database connection

if (!isset($_SESSION['admin_id'])) {
    header("Location: AdminLogin.php"); // Redirect to admin login if not logged in
    exit();
}

if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: AdminLogin.php");
    exit();
}

$admin_name = $con->query("SELECT username FROM admin WHERE admin_id = " . $_SESSION['admin_id'])->fetch_assoc()['username'];

// Fetch all categories from the database
$categories = $con->query("SELECT * FROM categories")->fetch_all(MYSQLI_ASSOC);

// Fetch all products from the database, including the image path
$products = $con->query("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id ORDER BY c.name")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Admin.css">
    <title>Manage Products</title>
    <style>
        .product-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        .table th, .table td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }
        
        .table th {
            background-color: #f5f5f5;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        
        .action-buttons input[type="submit"] {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .action-buttons input[value="Update"] {
            background-color: #4CAF50;
            color: white;
        }
        
        .action-buttons input[value="Delete"] {
            background-color: #f44336;
            color: white;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <h1>Manage Products</h1>
            <div class="admin-info">
                <span>Welcome, <?php echo htmlspecialchars($admin_name); ?></span>
                <form method="POST" style="display: inline;">
                    <button type="submit" name="logout" class="logout-btn">Logout</button>
                </form>
            </div>
        </div>
    </header>
    <nav>
        <ul>
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="ManageProduct.php">Manage Product</a></li>
            <li><a href="ManageOrder.php">Manage Orders</a></li>
            <li><a href="ManagePayement.php">Manage Payments</a></li>
            <li><a href="ViewReport.php">View Reports</a></li>
        </ul>
    </nav>
    <div class="container">
        <h2>Product List</h2>

        <?php foreach ($categories as $category): ?>
            <h3><?php echo htmlspecialchars($category['name']); ?></h3>
            <table class="table">
                <tr>
                    <th>Product ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Image</th>
                    <th>Actions</th>
                </tr>
                <?php foreach ($products as $product): ?>
                    <?php if ($product['category_id'] == $category['category_id']): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($product['product_id']); ?></td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo htmlspecialchars($product['description']); ?></td>
                            <td>₨ <?php echo number_format($product['price'], 2); ?></td>
                            <td><?php echo htmlspecialchars($product['stock']); ?></td>
                            <td>
                                <?php                          
                                    $image_path = '../' . $product['Image']; // Adjust the path accordingly
                                    if (!empty($image_path) && file_exists($image_path)):                         
                                ?>                             
                                    <img src="<?php echo htmlspecialchars($image_path); ?>"                                 
                                        alt="<?php echo htmlspecialchars($product['name']); ?>"                                 
                                        class="product-image">                         
                                <?php else: ?>                             
                                    <div class="error-image">                                 
                                        Image not found                              
                                    </div>                         
                                <?php endif; ?>  
                            </td>
                            <td class="action-buttons">
                                <form action="UpdateProduct.php" method="POST">
                                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                    <input type="submit" value="Update">
                                </form>
                                <form action="DeleteProduct.php" method="POST">
                                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                    <input type="submit" value="Delete" onclick="return confirm('Are you sure you want to delete this product?');">
                                </form>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </table>
        <?php endforeach; ?>

        <h2>Actions</h2>
        <div class="action-buttons">
            <form action="AddProduct.php" method="POST">
                <input type="submit" value="Add New Product">
            </form>
            <form action="ManageCategories.php" method="POST">
                <input type="submit" value="Manage Categories">
            </form>
        </div>
    </div>

    <script>
        // Add error handling for images
        document.querySelectorAll('.product-image').forEach(img => {
            img.onerror = function() {
                this.onerror = null;
                this.src = 'placeholder.jpg'; // Add a placeholder image path
                this.alt = 'Image not found';
            };
        });
    </script>
</body>
</html>