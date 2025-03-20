<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user data
$user_id = $_SESSION['user_id'];
$stmt = $con->prepare("SELECT username, email, address, ph_no FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $address = trim($_POST['address']);
    $ph_no = trim($_POST['ph_no']);
    $email = trim($_POST['email']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    
    $errors = [];
    
    // Validate inputs
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    if (!empty($ph_no) && !preg_match("/^\d{10}$/", $ph_no)) {
        $errors[] = "Phone number must be exactly 10 digits.";
    }
    if (!empty($new_password) && !preg_match("/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/", $new_password)) {
        $errors[] = "New password must be at least 8 characters, with uppercase, lowercase, number, and special character.";
    }

    // Proceed if no errors
    if (empty($errors)) {
        // If changing password, verify current password first
        if (!empty($new_password)) {
            $stmt = $con->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user_data = $result->fetch_assoc();
            
            if (!password_verify($current_password, $user_data['password'])) {
                $errors[] = "Current password is incorrect.";
            } else {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $con->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $hashed_password, $user_id);
                $stmt->execute();
            }
        }

        if (empty($errors)) {
            // Update other profile information
            $stmt = $con->prepare("UPDATE users SET email = ?, address = ?, ph_no = ? WHERE user_id = ?");
            $stmt->bind_param("sssi", $email, $address, $ph_no, $user_id);
            
            if ($stmt->execute()) {
                $success_message = "Profile updated successfully!";
                // Refresh user data
                $stmt = $con->prepare("SELECT username, email, address, ph_no FROM users WHERE user_id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
            } else {
                $errors[] = "Error updating profile.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Profile</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
/* Modern Profile Page Styles with Ocean Gradients */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
}

body {
    min-height: 100vh;
    /* Ocean gradient background */
    background: linear-gradient(135deg, #E3F4F4 0%, #D2E9E9 100%);
    padding: 2rem;
    display: flex;
    justify-content: center;
    align-items: flex-start;
}

.profile-container {
    /* Subtle cool white gradient */
    background: linear-gradient(145deg, #ffffff 0%, #F8FFFE 100%);
    width: 100%;
    max-width: 600px;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(116, 185, 185, 0.15);
    padding: 2.5rem;
    margin-top: 2rem;
    border: 1px solid rgba(255, 255, 255, 0.8);
}

h1 {
    color: #2C3333;
    font-size: 1.875rem;
    font-weight: 700;
    text-align: center;
    margin-bottom: 2rem;
    letter-spacing: -0.5px;
}

h2 {
    color: #2C3333;
    font-size: 1.25rem;
    font-weight: 600;
    margin: 2rem 0 1.5rem;
    padding-top: 1.5rem;
    border-top: 2px solid #E3F4F4;
}

.form-group {
    margin-bottom: 1.5rem;
}

.labels {
    display: block;
    color: #395B64;
    font-size: 0.95rem;
    font-weight: 500;
    margin-bottom: 0.5rem;
}

input[type="text"],
input[type="email"],
input[type="password"] {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 2px solid #E3F4F4;
    border-radius: 10px;
    font-size: 0.95rem;
    color: #2C3333;
    background: rgba(255, 255, 255, 0.8);
    transition: all 0.3s ease;
}

input[type="text"]:focus,
input[type="email"]:focus,
input[type="password"]:focus {
    outline: none;
    border-color: #64CCC5;
    box-shadow: 0 0 0 3px rgba(100, 204, 197, 0.15);
    background: white;
}

input[disabled] {
    background: #F8FFFE;
    color: #76928F;
    cursor: not-allowed;
    border-color: #E3F4F4;
}

.note {
    display: block;
    color: #76928F;
    font-size: 0.875rem;
    margin-top: 0.5rem;
    font-style: italic;
}

input[type="submit"] {
    width: 100%;
    padding: 0.875rem;
    /* Ocean gradient button */
    background: linear-gradient(135deg, #64CCC5 0%, #53B8B1 100%);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-top: 1rem;
}

input[type="submit"]:hover {
    transform: translateY(-2px);
    box-shadow: 0 7px 14px rgba(100, 204, 197, 0.25);
    background: linear-gradient(135deg, #5CBFB8 0%, #4AABA4 100%);
}

input[type="submit"]:active {
    transform: translateY(0);
}

.error {
    background: #FFF5F5;
    color: #E53E3E;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    font-size: 0.95rem;
    border: 1px solid #FED7D7;
}

.success {
    background: #F3FFF8;
    color: #38A169;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    font-size: 0.95rem;
    border: 1px solid #C6F6D5;
}

.links {
    display: flex;
    justify-content: space-between;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 2px solid #E3F4F4;
}

.links a {
    color: #53B8B1;
    text-decoration: none;
    font-weight: 500;
    font-size: 0.95rem;
    transition: color 0.3s ease;
}

.links a:hover {
    color: #4AABA4;
    text-decoration: underline;
}

/* Glass effect */
.profile-container {
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
}

/* Responsive Design */
@media (max-width: 640px) {
    body {
        padding: 1rem;
    }
    .profile-container {
        padding: 1.5rem;
        margin-top: 1rem;
    }
    h1 {
        font-size: 1.5rem;
    }
    h2 {
        font-size: 1.125rem;
    }
    input[type="submit"] {
        padding: 0.75rem;
    }
}

/* Animations */
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.profile-container {
    animation: slideIn 0.5s ease-out;
}
    </style>
    <script>
        function validateProfileForm() {
            let email = document.getElementById("email").value;
            let ph_no = document.getElementById("ph_no").value;
            let new_password = document.getElementById("new_password").value;
            let errors = [];

            if (email && !/^[^@]+@[^@]+\.[a-zA-Z]{2,}$/.test(email)) {
                errors.push("Invalid email format.");
            }
            if (ph_no && !/^\d{10}$/.test(ph_no)) {
                errors.push("Phone number must be exactly 10 digits.");
            }
            if (new_password && !/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/.test(new_password)) {
                errors.push("New password must be at least 8 characters, with uppercase, lowercase, number, and special character.");
            }

            if (errors.length > 0) {
                alert(errors.join("\n"));
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
    <div class="profile-container">
        <h1>My Profile</h1>
        
        <?php if (isset($errors)): ?>
            <?php foreach ($errors as $error): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if (isset($success_message)): ?>
            <p class="success"><?php echo htmlspecialchars($success_message); ?></p>
        <?php endif; ?>

        <form method="POST" onsubmit="return validateProfileForm()">
            <div class="form-group">
                <label class="labels">Username:</label>
                <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                <span class="note">(Username cannot be changed)</span>
            </div>

            <div class="form-group">
                <label class="labels">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>

            <div class="form-group">
                <label class="labels">Address:</label>
                <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($user['address']); ?>" required>
            </div>

            <div class="form-group">
                <label class="labels">Phone Number:</label>
                <input type="text" id="ph_no" name="ph_no" value="<?php echo htmlspecialchars($user['ph_no']); ?>" required>
            </div>

            <h2>Change Password</h2>
            <div class="form-group">
                <label class="labels">Current Password:</label>
                <input type="password" id="current_password" name="current_password">
            </div>

            <div class="form-group">
                <label class="labels">New Password:</label>
                <input type="password" id="new_password" name="new_password">
                <span class="note">(Leave blank to keep current password)</span>
            </div>

            <input type="submit" value="Update Profile">
            <br>
            <br>
            <div class="links">
            <a href="index.php">Back to Shopping</a>
            <a href="logout.php">Logout</a>
        </div>
        </form>

     
    </div>
</body>
</html>