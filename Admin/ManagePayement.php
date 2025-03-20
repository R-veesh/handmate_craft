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

$admin_name = $con->query("SELECT username FROM admin WHERE admin_id = " . $_SESSION['admin_id'])->fetch_assoc()['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Payments</title>
    <link rel="stylesheet" href="./admin.css">
</head>
<body>
    <header>
        <div class="header-container">
            <h1>Manage Payments</h1>
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
            <li><a href="ManageProduct.php">Manage Products</a></li>
            <li><a href="ManageOrder.php">Manage Orders</a></li>
            <li><a href="ManagePayement.php" class="active">Manage Payments</a></li>
            <li><a href="ViewReport.php">View Reports</a></li>
        </ul>
    </nav>

    <div class="container">
        <h2>Payment Transactions</h2>
        <table class="table">
            <tr>
                <th>Payment ID</th>
                <th>Order ID</th>
                <th>User</th>
                <th>Amount</th>
                <th>Payment Type</th>
                <th>Payment Date</th>
                <th>Order Status</th>
            </tr>
            <?php
            $payments = $con->query("SELECT payments.*, orders.status, users.username 
                                    FROM payments 
                                    JOIN orders ON payments.order_id = orders.order_id 
                                    JOIN users ON orders.user_id = users.user_id 
                                    ORDER BY payment_date DESC");
            while ($payment = $payments->fetch_assoc()):
            ?>
            <tr>
                <td><?php echo htmlspecialchars($payment['payment_id']); ?></td>
                <td><?php echo htmlspecialchars($payment['order_id']); ?></td>
                <td><?php echo htmlspecialchars($payment['username']); ?></td>
                <td>Rs<?php echo number_format($payment['amount'], 2); ?></td>
                <td><?php echo htmlspecialchars($payment['payment_type']); ?></td>
                <td><?php echo htmlspecialchars($payment['payment_date']); ?></td>
                <td><?php echo htmlspecialchars($payment['status']); ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>