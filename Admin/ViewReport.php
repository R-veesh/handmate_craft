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

$total_sales = $con->query("SELECT SUM(total_amount) AS total FROM orders WHERE status = 'completed'")->fetch_assoc()['total'];
$monthly_sales = $con->query("SELECT DATE_FORMAT(order_date, '%Y-%m') as month, 
                             SUM(total_amount) as total 
                             FROM orders 
                             WHERE status = 'completed'
                             GROUP BY month 
                             ORDER BY month DESC 
                             LIMIT 12");

$top_products = $con->query("SELECT p.name as product_name, 
                            COUNT(o.product_id) as total_sold,
                            SUM(o.total_amount) as revenue
                            FROM orders o
                            JOIN products p ON o.product_id = p.product_id
                            WHERE o.status = 'completed'
                            GROUP BY o.product_id
                            ORDER BY total_sold DESC
                            LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Reports</title>
    <link rel="stylesheet" href="./admin.css">
</head>
<body>
    <header>
        <div class="header-container">
            <h1>Sales Reports</h1>
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
            <li><a href="ManagePayement.php">Manage Payments</a></li>
            <li><a href="ViewReport.php" class="active">View Reports</a></li>
        </ul>
    </nav>

    <div class="container">
        <div class="dashboard-stats">
            <div class="stat-box">
                <h3>Total Completed Sales</h3>
                <p>Rs <?php echo number_format($total_sales, 2); ?></p>
            </div>
        </div>

        <h2>Monthly Sales Report</h2>
        <table class="table">
            <tr>
                <th>Month</th>
                <th>Total Sales</th>
            </tr>
            <?php while ($month = $monthly_sales->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($month['month']); ?></td>
                <td>Rs <?php echo number_format($month['total'], 2); ?></td>
            </tr>
            <?php endwhile; ?>
        </table>

        <h2>Top Selling Products</h2>
        <table class="table">
            <tr>
                <th>Product Name</th>
                <th>Units Sold</th>
                <th>Revenue</th>
            </tr>
            <?php while ($product = $top_products->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                <td><?php echo htmlspecialchars($product['total_sold']); ?></td>
                <td>Rs <?php echo number_format($product['revenue'], 2); ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>