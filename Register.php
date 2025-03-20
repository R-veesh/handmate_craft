<?php
// Start session
session_start();
include 'db.php';

// Predefined security questions with actual text as both key and value
$security_questions = [
    "What was the name of your first pet?" => "What was the name of your first pet?",
    "In which city were you born?" => "In which city were you born?",
    "What is your mother's maiden name?" => "What is your mother's maiden name?",
    "What was your favorite teacher's name?" => "What was your favorite teacher's name?",
    "What was the name of your first school?" => "What was the name of your first school?"
];

// Server-side validation and registration logic
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $address = trim($_POST['address']);
    $ph_no = trim($_POST['ph_no']);
    
    // Security fields
    $security_question = trim($_POST['security_question']);
    $security_answer = trim($_POST['security_answer']);

    // Server-side validations
    $errors = [];
    if (!preg_match("/^[a-zA-Z0-9]{3,20}$/", $username)) {
        $errors[] = "Username should be alphanumeric and 3-20 characters long.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    if (!preg_match("/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/", $password)) {
        $errors[] = "Password must be at least 8 characters, with at least one uppercase letter, one lowercase letter, one number, and one special character.";
    }
    if (!preg_match("/^\d{10}$/", $ph_no)) {
        $errors[] = "Phone number must be exactly 10 digits.";
    }
    if (empty($address)) {
        $errors[] = "Address cannot be empty.";
    }
    if (!array_key_exists($security_question, $security_questions)) {
        $errors[] = "Invalid security question selected.";
    }
    if (empty($security_answer)) {
        $errors[] = "Security answer cannot be empty.";
    }

    // Proceed if no errors
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        // Hash the security answer for additional security
        $hashed_security_answer = password_hash($security_answer, PASSWORD_DEFAULT);

        $stmt = $con->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error_message = "Username or email is already taken. Please choose a unique one.";
        } else {
            // Updated insert statement to store full security question text
            $stmt = $con->prepare("INSERT INTO users (username, email, password, Address, ph_no, security_question, security_answer) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $username, $email, $hashed_password, $address, $ph_no, $security_question, $hashed_security_answer);
            
            if ($stmt->execute()) {
                header("Location: Login.php");
                exit();
            } else {
                $error_message = "Error: " . $stmt->error;
            }
        }
        $stmt->close();
    }

    $con->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="./Styles/register.css">
    
    <script>
        // Client-side validation
        function validateForm() {
            let username = document.getElementById("username").value;
            let email = document.getElementById("email").value;
            let password = document.getElementById("password").value;
            let address = document.getElementById("address").value;
            let ph_no = document.getElementById("ph_no").value;
            let security_question = document.getElementById("security_question").value;
            let security_answer = document.getElementById("security_answer").value;
            let errors = [];

            // Username validation
            if (!/^[a-zA-Z0-9]{3,20}$/.test(username)) {
                errors.push("Username should be alphanumeric and 3-20 characters long.");
            }
            // Email validation
            if (!/^[^@]+@[^@]+\.[a-zA-Z]{2,}$/.test(email)) {
                errors.push("Invalid email format.");
            }
            // Password validation
            if (!/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/.test(password)) {
                errors.push("Password must be at least 8 characters, with at least one uppercase letter, one lowercase letter, one number, and one special character.");
            }
            // Phone number validation
            if (!/^\d{10}$/.test(ph_no)) {
                errors.push("Phone number must be exactly 10 digits.");
            }
            // Address validation
            if (address.trim() === "") {
                errors.push("Address cannot be empty.");
            }
            // Security question validation
            if (security_question === "") {
                errors.push("Please select a security question.");
            }
            // Security answer validation
            if (security_answer.trim() === "") {
                errors.push("Security answer cannot be empty.");
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
    <div class="login-container">
    <form action="register.php" method="POST" onsubmit="return validateForm()">
    <h1>Register Here</h1>

    <?php if (!empty($error_message)): ?>
        <div class="error-message">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <div class="form-grid">
        <div class="form-group">
            <label class="labels">User Name:</label>
            <input type="text" id="username" name="username" placeholder="Enter your name" required>
        </div>
        <div class="form-group">
            <label class="labels">Email:</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" required>
        </div>
        
        <!-- Continue with other fields in pairs -->
        <div class="form-group">
            <label class="labels">Password:</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>
        </div>
        <div class="form-group"> 
            <label class="labels">Address:</label>
            <input type="text" id="address" name="address" placeholder="Enter your address" required>
        </div>
        <div class="form-group">
           <label class="labels">Phone Number:</label>
          <input type="text" id="ph_no" name="ph_no" placeholder="Enter your phone number" required>
        </div>


        <!-- Security Question Fields -->
        <div class="form-group">
            <label class="labels">Security Question:</label>
            <select id="security_question" name="security_question" required>
                <option value="">Select a Security Question</option>
                <?php foreach($security_questions as $question): ?>
                    <option value="<?php echo htmlspecialchars($question); ?>"><?php echo htmlspecialchars($question); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label class="labels">Security Answer:</label>
            <input type="text" id="security_answer" name="security_answer" placeholder="Enter your security answer" required>
        </div>

        <input type="submit" value="Register">

        <div class="register-link">
            <span>Already Registered? </span>
            <a href="Login.php">Login Now</a>
        </div>
    </div>
</form>
    </div>
</body>
</html>