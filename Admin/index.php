<?php 
session_start(); 
include '../db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: AdminLogin.php");
    exit();
}

// Fetch admin name - adjust the query based on your database structure
$admin_name = $con->query("SELECT username FROM admin WHERE admin_id = " . $_SESSION['admin_id'])->fetch_assoc()['username'];

// Handle logout
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: AdminLogin.php");
    exit();
}

// Your existing database queries
$total_users = $con->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];
$total_products = $con->query("SELECT COUNT(*) AS total FROM products")->fetch_assoc()['total'];
$total_orders = $con->query("SELECT COUNT(*) AS total FROM orders")->fetch_assoc()['total'];
$total_sales = $con->query("SELECT SUM(total_amount) AS total FROM orders")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="./admin.css">
</head>
<body>
    <header>
        <div class="header-container">
            <h1>Admin Dashboard</h1>
            <div class="admin-info">
                <span>Welcome, <?php echo htmlspecialchars($admin_name); ?></span>
                <form method="POST" style="display: inline;">
                    <button type="submit" name="logout" class="logout-btn">Logout</button>
                </form>
            </div>
        </div>
    </header>

    <!-- Rest of your existing HTML remains the same -->
    <nav>
        <ul>
            <li><a href="ManageProduct.php">Manage Products</a></li>
            <li><a href="ManageOrder.php">Manage Orders</a></li>
            <li><a href="ManagePayement.php">Manage Payments</a></li>
            <li><a href="ViewReport.php">View Reports</a></li>
        </ul>
    </nav>
    <div class="container">
        <h2>Dashboard Overview</h2>
        <div class="dashboard-stats">
            <div class="stat-box">
                <h3>Total Users</h3>
                <p><?php echo $total_users; ?></p>
            </div>
            <div class="stat-box">
                <h3>Total Products</h3>
                <p><?php echo $total_products; ?></p>
            </div>
            <div class="stat-box">
                <h3>Total Orders</h3>
                <p><?php echo $total_orders; ?></p>
            </div>
            <div class="stat-box">
                <h3>Total Sales</h3>
                <p>Rs <?php echo number_format($total_sales, 2); ?></p>
            </div>
        </div>

        <h2>Recent Orders</h2>
        <table class="table">
            <tr>
                <th>Order ID</th>
                <th>Username</th>
                <th>Order Date</th>
                <th>Status</th>
            </tr>
            <?php
            $recent_orders = $con->query("SELECT orders.order_id, users.username, orders.order_date, orders.status 
                                            FROM orders 
                                            JOIN users ON orders.user_id = users.user_id 
                                            ORDER BY orders.order_date DESC 
                                            LIMIT 5");
            while ($order = $recent_orders->fetch_assoc()):
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                    <td><?php echo htmlspecialchars($order['username']); ?></td>
                    <td><?php echo htmlspecialchars($order['order_date']); ?></td>
                    <td><?php echo htmlspecialchars($order['status']); ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>
