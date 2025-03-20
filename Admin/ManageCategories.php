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

// Check if a category was added
if (isset($_POST['add_category'])) {
    $name = $_POST['name']; 
    $description = $_POST['description'];
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image = $_FILES['image']['name'];
        $image_tmp = $_FILES['image']['tmp_name'];
        $image_ext = strtolower(pathinfo($image, PATHINFO_EXTENSION)); 
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif']; 

        // Check if the file extension is allowed
        if (!in_array($image_ext, $allowed_extensions)) {
            $error = "Only JPG, JPEG, PNG, and GIF files are allowed.";
        } else {
            // Sanitize the image name to avoid file conflicts
            $sanitized_image_name = uniqid() . '.' . $image_ext;

            $target_dir = $_SERVER['DOCUMENT_ROOT'] . '/My Project/uploads/';
            $target_file = $target_dir . basename($sanitized_image_name);

            // Validate input
            if (empty($name) || empty($description)) {
                $error = "Please enter a category name and description.";
            } else {
                // Create directory if it doesn't exist
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }

                // Attempt to move the uploaded file
                if (move_uploaded_file($image_tmp, $target_file)) {
                    $query = "INSERT INTO categories (name, description, image) VALUES (?, ?, ?)";
                    $stmt = $con->prepare($query);
                    $stmt->bind_param("sss", $name, $description, $sanitized_image_name);
                    if ($stmt->execute()) {
                        $success = "Category added successfully.";
                    } else {
                        $error = "Error adding category: " . $stmt->error;
                    }
                    $stmt->close();
                } else {
                    $error = "Error uploading image.";
                }
            }
        }
    } else {
        $error = "Please select an image to upload.";
    }
}

// Check if a category was updated
if (isset($_POST['update_category'])) {
    $category_id = $_POST['category_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $image = $_FILES['image']['name'];
    $target_dir = "../../uploads/";

    if (!empty($image)) {
        $image_tmp = $_FILES['image']['tmp_name'];
        $image_ext = strtolower(pathinfo($image, PATHINFO_EXTENSION)); // Get file extension
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif']; // Allowed file types

        // Check if the file extension is allowed
        if (!in_array($image_ext, $allowed_extensions)) {
            $error = "Only JPG, JPEG, PNG, and GIF files are allowed.";
        } else {
            // Sanitize the image name to avoid file conflicts
            $sanitized_image_name = uniqid() . '.' . $image_ext;
            $target_file = $target_dir . basename($sanitized_image_name);

            // Attempt to move the uploaded file
            if (move_uploaded_file($image_tmp, $target_file)) {
                // Update the category in the database with the new image
                $query = "UPDATE categories SET name=?, description=?, image=? WHERE category_id=?";
                $stmt = $con->prepare($query);
                $stmt->bind_param("sssi", $name, $description, $sanitized_image_name, $category_id);
            } else {
                $error = "Error uploading image.";
            }
        }
    } else {
        // Update without changing the image
        $query = "UPDATE categories SET name=?, description=? WHERE category_id=?";
        $stmt = $con->prepare($query);
        $stmt->bind_param("ssi", $name, $description, $category_id);
    }

    if ($stmt->execute()) {
        $success = "Category updated successfully.";
    } else {
        $error = "Error updating category.";
    }
    $stmt->close();
}

// Check if a category was deleted
if (isset($_POST['delete_category'])) {
    $category_id = $_POST['category_id'];

    // Delete the category from the database
    $query = "DELETE FROM categories WHERE category_id=?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $category_id);
    if ($stmt->execute()) {
        $success = "Category deleted successfully.";
    } else {
        $error = "Error deleting category.";
    }
    $stmt->close();
}

$categories = $con->query("SELECT * FROM categories")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="admin.css">
    <title>Manage Categories</title>
    <style>
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
        
        .action-buttons input[type="submit"] {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .action-buttons input[value="Add"] {
            background-color: #4CAF50;
            color: white;
        }
        
        .action-buttons input[value="Update"] {
            background-color: #2196F3;
            color: white;
        }
        
        .action-buttons input[value="Delete"] {
            background-color: #f44336;
            color: white;
        }

        form input[type="text"],
        form input[type="number"],
        form textarea,
        form select {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 2px solid #e4e9f2;
            border-radius: 5px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        form input:focus,
        form select:focus,
        form textarea:focus {
            outline: none;
            border-color: #4776E6;
            box-shadow: 0 0 0 3px rgba(71, 118, 230, 0.1);
        }

        .add_category {
            width: 100%;
            max-width: 420px;
            padding: 20px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 24px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.8)
        }

        .add_category::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            border-radius: 28px;
            z-index: -1;
            filter: blur(24px);
            opacity: 0.6;
        }

        .product-image {
            max-width: 100px;
            max-height: 100px;
            object-fit: cover;
            border-radius: 4px;
        }

        .error-image {
            color: #721c24;
            background-color: #f8d7da;
            padding: 10px;
            border-radius: 4px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <header>
        <h1>Manage Categories</h1>
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
    <div class="container">
        <h2>Category List</h2>

        <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <table class="table">
            <tr>
                <th>Category ID</th>
                <th>Name</th>
                <th>Image</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($categories as $category): ?>
                <tr>
                    <td><?php echo htmlspecialchars($category['category_id']); ?></td>
                    <td><?php echo htmlspecialchars($category['name']); ?></td>
                    <td>
                        <?php
                        $image_path = '/My Project/uploads/' . urlencode(htmlspecialchars($category['Image']));
                        $full_image_path = $_SERVER['DOCUMENT_ROOT'] . '/My Project/uploads/' . htmlspecialchars($category['Image']);

                        if (file_exists($full_image_path)) {
                            echo '<img src="' . $image_path . '" alt="' . htmlspecialchars($category['name']) . '" class="product-image">';
                        } else {
                            echo '<div class="error-image">Image not found</div>';
                        }
                        ?>
                    </td>
                    <td><?php echo htmlspecialchars($category['description']); ?></td>
                    <td>
                        <form action="UpdateCategory.php" method="POST">
                            <input type="hidden" name="category_id" value="<?php echo $category['category_id']; ?>">
                            <input type="submit" value="Update">
                        </form>
                        <form action="ManageCategories.php" method="POST">
                            <input type="hidden" name="category_id" value="<?php echo $category['category_id']; ?>">
                            <input type="submit" name="delete_category" value="Delete" onclick="return confirm('Are you sure you want to delete this category?');">
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <h2>Add New Category</h2>
        <div class="add_category">
        <form action="ManageCategories.php" method="POST" enctype="multipart/form-data">
            <label>Category Name</label>
            <input type="text" name="name" placeholder="Enter Category name" required>
            <br>
            <br>
            <label>Description</label>
            <input type="text" name="description" placeholder="Enter Category Description" required>
            <br>
            <br>
            <label>Category Image</label>
            <br>
            <br>
            <input type="file" name="image" required>
            <br>
            <br>
            <input type="submit" name="add_category" value="Add">
        </form>
        </div>
    </div>
</body>
</html>