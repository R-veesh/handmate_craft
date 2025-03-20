<?php
session_start();
include 'db.php';

$error_message = '';
$success_message = '';
$show_security_answer = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['security_step'])) {
        // First step: Verify email and show security question
        $email = trim($_POST['email']);

        $stmt = $con->prepare("SELECT username, security_question FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $_SESSION['reset_email'] = $email;
            $show_security_answer = true;
            $security_question = $user['security_question'];
        } else {
            $error_message = "No user found with that email address.";
        }
        $stmt->close();
    } elseif (isset($_POST['security_step']) && $_POST['security_step'] == 'verify_answer') {
        // Second step: Verify security answer
        $email = $_SESSION['reset_email'];
        $security_answer = $_POST['security_answer'];

        $stmt = $con->prepare("SELECT security_answer FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Verify security answer
            if (password_verify($security_answer, $user['security_answer'])) {
                // Security answer is correct, allow password reset
                $_SESSION['security_verified'] = true;
                $show_security_answer = false;
            } else {
                $error_message = "Incorrect security answer. Please try again.";
            }
        } else {
            $error_message = "User not found.";
        }
        $stmt->close();
    } elseif (isset($_POST['security_step']) && $_POST['security_step'] == 'reset_password') {
        // Third step: Reset password
        if (!$_SESSION['security_verified']) {
            $error_message = "Security verification required.";
        } else {
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];
            $email = $_SESSION['reset_email'];

            // Validate new password
            if (empty($new_password) || empty($confirm_password)) {
                $error_message = "Please enter both password fields.";
            } elseif ($new_password !== $confirm_password) {
                $error_message = "Passwords do not match.";
            } elseif (!preg_match("/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/", $new_password)) {
                $error_message = "Password must be at least 8 characters, with at least one uppercase letter, one lowercase letter, one number, and one special character.";
            } else {
                // Hash the new password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                // Update password in database
                $stmt = $con->prepare("UPDATE users SET password = ? WHERE email = ?");
                $stmt->bind_param("ss", $hashed_password, $email);
                
                if ($stmt->execute()) {
                    // Clear session variables
                    unset($_SESSION['reset_email']);
                    unset($_SESSION['security_verified']);
                    
                    $success_message = "Password reset successfully. You can now log in.";
                } else {
                    $error_message = "Error updating password. Please try again.";
                }
                $stmt->close();
            }
        }
    }
}

$con->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./Styles/Login.css">
    <style>
        .success-message {
            background: rgba(82, 255, 88, 0.1);
            color: green;
            padding: 12px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
            border: 1px solid rgba(82, 255, 88, 0.2);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <form action="ForgotPassword.php" method="POST">
            <h1>Reset Password</h1>
            
            <?php if (!empty($error_message)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div class="success-message">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <?php if (!$show_security_answer && !isset($_SESSION['security_verified'])): ?>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                </div>
                <input type="submit" value="Verify Email">
            <?php endif; ?>

            <?php if ($show_security_answer): ?>
                <input type="hidden" name="security_step" value="verify_answer">
                <div class="form-group">
                    <label>Security Question</label>
                    <input type="text" value="<?php echo htmlspecialchars($security_question); ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="security_answer">Security Answer</label>
                    <input type="text" id="security_answer" name="security_answer" placeholder="Enter your security answer" required>
                </div>
                <input type="submit" value="Verify Answer">
            <?php endif; ?>

            <?php if (isset($_SESSION['security_verified']) && $_SESSION['security_verified']): ?>
                <input type="hidden" name="security_step" value="reset_password">
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" placeholder="Enter new password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required>
                </div>
                <input type="submit" value="Reset Password">
            <?php endif; ?>

            <div class="register-link">
                <span>Remember your password? </span>
                <a href="login.php">Back to Login</a>
            </div>
        </form>
    </div>
</body>
</html>