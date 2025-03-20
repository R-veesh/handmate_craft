<?php
session_start();
include '../db.php';

// Secure admin login
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate input
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Use prepared statement to prevent SQL injection
    $stmt = $con->prepare("SELECT admin_id, username, password FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();

        // Use password_verify if you want to upgrade to hashed passwords
        if ($password === $admin['password']) {
            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);

            // Set admin-specific session variables
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['username'] = $admin['username'];
            $_SESSION['is_admin'] = true;

            // Log login attempt (optional but recommended)
            error_log("Admin {$username} logged in successfully at " . date('Y-m-d H:i:s'));

            // Redirect to admin dashboard
            header("Location: index.php");
            exit();
        } else {
            $error_message = "Invalid credentials. Please try again.";
            
            // Log failed login attempt
            error_log("Failed admin login attempt for username {$username} at " . date('Y-m-d H:i:s'));
        }
    } else {
        $error_message = "No admin account found.";
    }

    $stmt->close();
}

// Close the database connection
$con->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="./admin.css">
</head>
<body>
    <div class="login-container">
        <h1>Admin Login</h1>

        <?php if(!empty($error_message)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form action="AdminLogin.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" 
                       placeholder="Enter your username" 
                       required 
                       autocomplete="username"
                       maxlength="50">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" 
                       placeholder="Enter your password" 
                       required 
                       autocomplete="current-password"
                       maxlength="50">
            </div>

            <input type="submit" value="Login">
        </form>

        <div class="back-to-site">
            <a href="../index.php">← Back to Main Site</a>
        </div>
    </div>
</body>
</html>