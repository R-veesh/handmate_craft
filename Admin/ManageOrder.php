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

if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['new_status'];
    $con->query("UPDATE orders SET status = '$new_status' WHERE order_id = $order_id");
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders</title>
    <link rel="stylesheet" href="./admin.css">
    <style>
        button{
            background-color: #4CAF50;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <h1>Manage Orders</h1>
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
            <li><a href="ManageOrder.php" class="active">Manage Orders</a></li>
            <li><a href="ManagePayement.php">Manage Payments</a></li>
            <li><a href="ViewReport.php">View Reports</a></li>
        </ul>
    </nav>

    <div class="container">
        <h2>All Orders</h2>
        <table class="table">
            <tr>
                <th>Order ID</th>
                <th>Username</th>
                <th>Product</th>
                <th>Order Date</th>
                <th>Total Amount</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php
            $orders = $con->query("SELECT orders.*, users.username, products.name as product_name 
                                 FROM orders 
                                 JOIN users ON orders.user_id = users.user_id 
                                 JOIN products ON orders.product_id = products.product_id
                                 ORDER BY orders.order_date DESC");
            while ($order = $orders->fetch_assoc()):
            ?>
            <tr>
                <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                <td><?php echo htmlspecialchars($order['username']); ?></td>
                <td><?php echo htmlspecialchars($order['product_name']); ?></td>
                <td><?php echo htmlspecialchars($order['order_date']); ?></td>
                <td>Rs<?php echo number_format($order['total_amount'], 2); ?></td>
                <td><?php echo htmlspecialchars($order['status']); ?></td>
                <td>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                        <select name="new_status">
                            <option value="pending">Pending</option>
                            <option value="completed">Completed</option>
                            <option value="canceled">Canceled</option>
                        </select>
                        <button name="update_status">Update</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>