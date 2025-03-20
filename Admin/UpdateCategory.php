<?php
session_start();
include '../db.php'; // Include your database connection

if (!isset($_SESSION['admin_id'])) {
    header("Location: AdminLogin.php"); // Redirect to admin login if not logged in
    exit();
}

// Initialize variables
$category = null;
$error = null;
$success = null;

// Fetch category details based on category_id (from either GET or POST)
$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : (isset($_POST['category_id']) ? intval($_POST['category_id']) : null);

if ($category_id) {
    $stmt = $con->prepare("SELECT * FROM categories WHERE category_id = ?");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $category = $result->fetch_assoc();
    $stmt->close();
    
    if (!$category) {
        header("Location: ManageCategories.php");
        exit();
    }
} else {
    header("Location: ManageCategories.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_category'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image = $_FILES['image']['name'];
        $image_tmp = $_FILES['image']['tmp_name'];
        $image_ext = strtolower(pathinfo($image, PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($image_ext, $allowed_extensions)) {
            $error = "Only JPG, JPEG, PNG, and GIF files are allowed.";
        } else {
            // Generate unique filename
            $sanitized_image_name = uniqid() . '.' . $image_ext;
            $target_dir = $_SERVER['DOCUMENT_ROOT'] . '/My Project/uploads/';
            $target_file = $target_dir . basename($sanitized_image_name);

            // Create directory if it doesn't exist
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            if (move_uploaded_file($image_tmp, $target_file)) {
                // Update with new image
                $stmt = $con->prepare("UPDATE categories SET name=?, description=?, image=? WHERE category_id=?");
                $stmt->bind_param("sssi", $name, $description, $sanitized_image_name, $category_id);
            } else {
                $error = "Error uploading image.";
            }
        }
    } else {
        // Update without changing the image
        $stmt = $con->prepare("UPDATE categories SET name=?, description=? WHERE category_id=?");
        $stmt->bind_param("ssi", $name, $description, $category_id);
    }

    if (isset($stmt)) {
        if ($stmt->execute()) {
            $success = "Category updated successfully.";
            // Refresh category data
            $stmt = $con->prepare("SELECT * FROM categories WHERE category_id = ?");
            $stmt->bind_param("i", $category_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $category = $result->fetch_assoc();
        } else {
            $error = "Error updating category: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="admin.css">
    <title>Update Category</title>
    <style>
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        
        input[type="text"],
        textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 16px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        
        input[type="file"] {
            margin-bottom: 16px;
        }
        
        input[type="submit"] {
            background-color: #2196F3;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        input[type="submit"]:hover {
            background-color: #1976D2;
        }
        
        .error {
            color: #f44336;
            margin-bottom: 16px;
            padding: 10px;
            background-color: #ffebee;
            border-radius: 4px;
        }
        
        .success {
            color: #4CAF50;
            margin-bottom: 16px;
            padding: 10px;
            background-color: #E8F5E9;
            border-radius: 4px;
        }
        
        .product-image {
            max-width: 200px;
            margin-bottom: 16px;
            border-radius: 4px;
        }

        .back-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #666;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .back-button:hover {
            background-color: #555;
        }
    </style>
</head>
<body>
    <header>
        <h1>Update Category</h1>
    </header>
    <nav>
        <ul>
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="ManageCategories.php">Manage Categories</a></li>
            <li><a href="ManageProduct.php">Manage Products</a></li>
            <li><a href="ManageOrder.php">Manage Orders</a></li>
            <li><a href="ManagePayement.php">Manage Payments</a></li>
            <li><a href="ViewReport.php">View Reports</a></li>
        </ul>
    </nav>
    <div class="container">
        <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if ($category): ?>
        <form action="UpdateCategory.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="category_id" value="<?php echo htmlspecialchars($category['category_id']); ?>">
            
            <label for="name">Category Name:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($category['name']); ?>" required>

            <label for="description">Description:</label>
            <textarea id="description" name="description" required><?php echo htmlspecialchars($category['description']); ?></textarea>

            <!-- Display Current Image -->
            <label>Current Image:</label><br>
            <?php 
            $image_path = '/My Project/uploads/' . htmlspecialchars($category['Image']);
            $full_image_path = $_SERVER['DOCUMENT_ROOT'] . $image_path;
            if (!empty($category['Image']) && file_exists($full_image_path)): ?>
                <img src="<?php echo $image_path; ?>" alt="<?php echo htmlspecialchars($category['name']); ?>" class="product-image">
            <?php else: ?>
                <div>No image available</div>
            <?php endif; ?>

            <!-- Input for New Image -->
            <label for="image">Upload New Image (optional):</label>
            <input type="file" id="image" name="image">
            
            <input type="submit" name="update_category" value="Update Category">
        </form>
        <?php endif; ?>
    </div>
</body>
</html>