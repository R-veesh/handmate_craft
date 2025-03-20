<?php
session_start();
include '../db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: AdminLogin.php");
    exit();
}

if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: AdminLogin.php");
    exit();
}

// Fetch all categories to populate the dropdown
$categories = $con->query("SELECT * FROM categories")->fetch_all(MYSQLI_ASSOC);

// Handle the form submission for adding a new product
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['name'], $_POST['description'], $_POST['price'], $_POST['stock'], $_POST['category']) && isset($_FILES['image'])) {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $stock = $_POST['stock'];
        $category_id = $_POST['category'];

        // Create uploads directory if it doesn't exist
        $upload_dir = "../uploads/products/"; // Change this to your desired path
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Generate unique filename
        $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $unique_filename = uniqid() . '_' . time() . '.' . $file_extension;
        $target_file = $upload_dir . $unique_filename;
        $db_file_path = "uploads/products/" . $unique_filename; // Path to store in database
        $uploadOk = 1;

        // Check if image file is actual image
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if($check === false) {
            echo "<div class='error'>File is not an image.</div>";
            $uploadOk = 0;
        }

        // Check file size (5MB limit)
        if ($_FILES["image"]["size"] > 5000000) {
            echo "<div class='error'>Sorry, your file is too large. Maximum size is 5MB.</div>";
            $uploadOk = 0;
        }

        // Allow certain file formats
        if($file_extension != "jpg" && $file_extension != "png" && $file_extension != "jpeg" && $file_extension != "gif" ) {
            echo "<div class='error'>Sorry, only JPG, JPEG, PNG & GIF files are allowed.</div>";
            $uploadOk = 0;
        }

        if ($uploadOk == 0) {
            echo "<div class='error'>Sorry, your file was not uploaded.</div>";
        } else {
            // Try to upload file
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                // Prepare the SQL statement
                $stmt = $con->prepare("INSERT INTO products (name, description, price, stock, image,category_id) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssdiss", $name, $description, $price, $stock, $db_file_path,$category_id);

                if ($stmt->execute()) {
                    echo "<div class='success'>Product added successfully!</div>";
                } else {
                    echo "<div class='error'>Database Error: " . $stmt->error . "</div>";
                    // If database insert fails, remove the uploaded image
                    unlink($target_file);
                }
                $stmt->close();
            } else {
                echo "<div class='error'>Sorry, there was an error uploading your file.</div>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./admin.css">
    <link rel="stylesheet" href="./main.css">
    <style>
        
    </style>
    <title>Add Product</title>
</head>
<body>
    <header>
        <h1>Add New Product</h1>
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
    <form action="AddProduct.php" method="POST" enctype="multipart/form-data">
        <div >
            <label for="name">Product Name:</label>
            <input type="text" name="name" required>
        </div>
        
        <div>
            <label for="description">Description:</label>
            <textarea name="description" required></textarea>
        </div>
        
        <div>
            <label for="price">Price:</label>
            <input type="number" name="price" step="0.01" min="0" required>
        </div>
        
        <div>
            <label for="stock">Stock:</label>
            <input type="number" name="stock" min="0" required>
        </div>
        
        <div>
            <label for="category">Category:</label>
            <select name="category" required>
                <option value="">Select a category</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo htmlspecialchars($category['category_id']); ?>">
                        <?php echo htmlspecialchars($category['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div>
            <label for="image">Product Image:</label>
            <input type="file" name="image" accept="image/*" required>
            <small>Maximum file size: 5MB. Allowed formats: JPG, JPEG, PNG, GIF</small>
        </div>
        
        <input type="submit" value="Add Product">
    </form>
</div>
</body>
</html>