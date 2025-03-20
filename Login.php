<?php
session_start();
include 'db.php';

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate input
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Input validation
    if (empty($username) || empty($password)) {
        $error_message = "Please enter both username and password.";
    } else {
        // First, try to find in admin table
        $admin_stmt = $con->prepare("SELECT admin_id, username, password, 'admin' as user_type FROM admin WHERE username = ?");
        $admin_stmt->bind_param("s", $username);
        $admin_stmt->execute();
        $admin_result = $admin_stmt->get_result();

        // If no admin found, check user table
        if ($admin_result->num_rows == 0) {
            $admin_stmt->close();
            
            // Prepare statement for user login
            $user_stmt = $con->prepare("SELECT user_id, username, password, 'user' as user_type FROM users WHERE username = ?");
            $user_stmt->bind_param("s", $username);
            $user_stmt->execute();
            $user_result = $user_stmt->get_result();

            if ($user_result->num_rows > 0) {
                $user = $user_result->fetch_assoc();
                
                // Verify user password
                if (password_verify($password, $user['password'])) {
                    // User login successful
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['user_type'] = $user['user_type'];

                    // Log successful login
                    error_log("User {$username} logged in successfully at " . date('Y-m-d H:i:s'));

                    // Redirect to appropriate page
                    header("Location: index.php");
                    exit();
                } else {
                    $error_message = "Invalid username or password.";
                }
                
                $user_stmt->close();
            } else {
                $error_message = "No account found with that username.";
            }
        } else {
            // Admin login logic
            $admin = $admin_result->fetch_assoc();
            
            // Check admin password (replace with password_verify if using hashed passwords)
            if ($password === $admin['password']) {
                // Admin login successful
                session_regenerate_id(true);
                $_SESSION['admin_id'] = $admin['admin_id'];
                $_SESSION['username'] = $admin['username'];
                $_SESSION['user_type'] = $admin['user_type'];

                // Log successful admin login
                error_log("Admin {$username} logged in successfully at " . date('Y-m-d H:i:s'));

                // Redirect to admin dashboard
                header("Location: admin/index.php");
                exit();
            } else {
                $error_message = "Invalid credentials. Please try again.";
            }
            
            $admin_stmt->close();
        }
    }

    // Close database connection
    $con->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unified Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./Styles/Login.css">
</head>
<body>
    <div class="login-container">
        <form action="login.php" method="POST">
            <h1>Welcome Back</h1>
            
            <?php if (!empty($error_message)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text"
                       id="username"
                       name="username"
                       placeholder="Enter your username"
                       required
                       maxlength="50"
                       value="<?php echo htmlspecialchars($username ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password"
                       id="password"
                       name="password"
                       placeholder="Enter your password"
                       required
                       maxlength="50">
            </div>
            
            <input type="submit" value="Login">
            
            <div class="register-link">
                <a href="ForgotPassword.php">Forgot Password?</a>
                <br><br>
                <span>New User? </span>
                <a href="register.php">Register Here</a>
            </div>
        </form>
    </div>
</body>
</html>