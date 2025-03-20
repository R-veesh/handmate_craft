<?php 
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Initialize variables
$user_id = $_SESSION['user_id'];
$cart_items = [];
$total = 0;
$error_message = '';
$success_message = '';

// Check if a product is being added to the cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    $quantity = 1;

    // Start transaction
    $con->begin_transaction();

    try {
        // Check product stock
        $stock_query = "SELECT stock FROM products WHERE product_id = ? FOR UPDATE";
        $stock_stmt = $con->prepare($stock_query);
        $stock_stmt->bind_param("i", $product_id);
        $stock_stmt->execute();
        $stock_result = $stock_stmt->get_result();
        $product_stock = $stock_result->fetch_assoc();

        if ($product_stock && $product_stock['stock'] > 0) {
            // Check if the product is already in the cart
            $check_query = "SELECT * FROM cart WHERE product_id = ? AND user_id = ?";
            $stmt = $con->prepare($check_query);
            $stmt->bind_param("ii", $product_id, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Product already exists in cart, check if update is possible
                $item = $result->fetch_assoc();
                $new_quantity = $item['quantity'] + $quantity;
                
                if ($new_quantity <= $product_stock['stock']) {
                    $update_query = "UPDATE cart SET quantity = ? WHERE cart_id = ?";
                    $update_stmt = $con->prepare($update_query);
                    $update_stmt->bind_param("ii", $new_quantity, $item['cart_id']);
                    $update_stmt->execute();
                    $update_stmt->close();
                    
                    // Update stock
                    $new_stock = $product_stock['stock'] - $quantity;
                    $update_stock_query = "UPDATE products SET stock = ? WHERE product_id = ?";
                    $update_stock_stmt = $con->prepare($update_stock_query);
                    $update_stock_stmt->bind_param("ii", $new_stock, $product_id);
                    $update_stock_stmt->execute();
                    $update_stock_stmt->close();
                    
                    $success_message = "Cart updated successfully!";
                } else {
                    throw new Exception("Not enough stock available!");
                }
            } else {
                // Product not in cart, insert new item
                $insert_query = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
                $insert_stmt = $con->prepare($insert_query);
                $insert_stmt->bind_param("iii", $user_id, $product_id, $quantity);
                $insert_stmt->execute();
                $insert_stmt->close();
                
                // Update stock
                $new_stock = $product_stock['stock'] - $quantity;
                $update_stock_query = "UPDATE products SET stock = ? WHERE product_id = ?";
                $update_stock_stmt = $con->prepare($update_stock_query);
                $update_stock_stmt->bind_param("ii", $new_stock, $product_id);
                $update_stock_stmt->execute();
                $update_stock_stmt->close();
                
                $success_message = "Item added to cart!";
            }
            
            // Update cart count in session
            $_SESSION['cart_count'] = ($_SESSION['cart_count'] ?? 0) + $quantity;
            
            $con->commit();
        } else {
            throw new Exception("Item is out of stock!");
        }
    } catch (Exception $e) {
        $con->rollback();
        $error_message = $e->getMessage();
    }

    // Redirect with message
    if ($error_message) {
        $_SESSION['error_message'] = $error_message;
    } else {
        $_SESSION['success_message'] = $success_message;
    }
    header('Location: index.php');
    exit();
}

// Handle quantity updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_quantity'])) {
    $cart_id = $_POST['cart_id'];
    $new_quantity = (int)$_POST['quantity'];
    
    // Start transaction
    $con->begin_transaction();
    
    try {
        // Get current cart item details
        $cart_query = "SELECT c.product_id, c.quantity, p.stock FROM cart c 
                      JOIN products p ON c.product_id = p.product_id 
                      WHERE c.cart_id = ? AND c.user_id = ? FOR UPDATE";
        $cart_stmt = $con->prepare($cart_query);
        $cart_stmt->bind_param("ii", $cart_id, $user_id);
        $cart_stmt->execute();
        $cart_result = $cart_stmt->get_result();
        $cart_item = $cart_result->fetch_assoc();
        
        if ($cart_item) {
            $quantity_difference = $new_quantity - $cart_item['quantity'];
            $available_stock = $cart_item['stock'] + $cart_item['quantity']; // Current stock + what's in cart
            
            if ($new_quantity <= $available_stock) {
                if ($new_quantity > 0) {
                    // Update cart quantity
                    $update_query = "UPDATE cart SET quantity = ? WHERE cart_id = ? AND user_id = ?";
                    $stmt = $con->prepare($update_query);
                    $stmt->bind_param("iii", $new_quantity, $cart_id, $user_id);
                    $stmt->execute();
                    
                    // Update product stock
                    $new_stock = $available_stock - $new_quantity;
                    $update_stock_query = "UPDATE products SET stock = ? WHERE product_id = ?";
                    $stock_stmt = $con->prepare($update_stock_query);
                    $stock_stmt->bind_param("ii", $new_stock, $cart_item['product_id']);
                    $stock_stmt->execute();
                    
                    $_SESSION['success_message'] = "Cart updated successfully!";
                }
            } else {
                throw new Exception("Not enough stock available!");
            }
        }
        
        $con->commit();
    } catch (Exception $e) {
        $con->rollback();
        $_SESSION['error_message'] = $e->getMessage();
    }
    
    header('Location: cart.php');
    exit();
}

// Handle item removal
if (isset($_GET['remove'])) {
    $cart_id = $_GET['remove'];
    
    // Start transaction
    $con->begin_transaction();
    
    try {
        // Get cart item details before deletion
        $cart_query = "SELECT product_id, quantity FROM cart WHERE cart_id = ? AND user_id = ?";
        $cart_stmt = $con->prepare($cart_query);
        $cart_stmt->bind_param("ii", $cart_id, $user_id);
        $cart_stmt->execute();
        $cart_result = $cart_stmt->get_result();
        $cart_item = $cart_result->fetch_assoc();
        
        if ($cart_item) {
            // Return quantity to product stock
            $update_stock = "UPDATE products SET stock = stock + ? WHERE product_id = ?";
            $stock_stmt = $con->prepare($update_stock);
            $stock_stmt->bind_param("ii", $cart_item['quantity'], $cart_item['product_id']);
            $stock_stmt->execute();
            
            // Delete cart item
            $delete_query = "DELETE FROM cart WHERE cart_id = ? AND user_id = ?";
            $delete_stmt = $con->prepare($delete_query);
            $delete_stmt->bind_param("ii", $cart_id, $user_id);
            $delete_stmt->execute();
            
            // Update cart count in session
            $_SESSION['cart_count'] = max(0, ($_SESSION['cart_count'] ?? 0) - $cart_item['quantity']);
            
            $_SESSION['success_message'] = "Item removed from cart!";
        }
        
        $con->commit();
    } catch (Exception $e) {
        $con->rollback();
        $_SESSION['error_message'] = $e->getMessage();
    }
    
    header('Location: cart.php');
    exit();
}

// Fetch cart items for the current user with stock information
$query = "SELECT c.*, p.name, p.price, p.Image, p.stock 
          FROM cart c 
          JOIN products p ON c.product_id = p.product_id 
          WHERE c.user_id = ?";
if ($stmt = $con->prepare($query)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $cart_items = $result->fetch_all(MYSQLI_ASSOC);
    
    // Calculate total
    foreach ($cart_items as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="Styles/index.css">
    <style>
        .cart-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }
        
        .cart-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #eee;
            gap: 20px;
        }
        
        .cart-item img {
            width: 100px;
            height: 100px;
            object-fit: cover;
        }
        
        .item-details {
            flex-grow: 1;
        }
        
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .quantity-controls input {
            width: 60px;
            padding: 5px;
            text-align: center;
        }
        
        .remove-btn {
            background-color: #ff4444;
            text-decoration: none;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .update-btn {
            background-color: #4CAF50;
            text-decoration: none;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        }

        .cart-total {
            margin-top: 20px;
            text-align: right;
            font-size: 1.2em;
            font-weight: bold;
        }
        
        .checkout-btn {
            background-color: #4CAF50;
            text-decoration: none;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1.1em;
            margin-top: 20px;
            float: right;
        }
        
        .empty-cart {
            text-align: center;
            padding: 40px;
            font-size: 1.2em;
            color: #666;
        }

        .stock-info {
            font-size: 0.9em;
            color: #666;
        }

        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }

        .error {
            background-color: #ffe6e6;
            color: #ff0000;
        }

        .success {
            background-color: #e6ffe6;
            color: #008000;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <div class="header-title">
                <h1>Shopping Cart</h1>
            </div>
            <div class="header-navigation">
                <nav>
                    <ul>
                        <li><a href="index.php">Continue Shopping</a></li>
                        <li><a href="logout.php">Logout</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <div class="cart-container">
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="message error">
                <?php 
                echo htmlspecialchars($_SESSION['error_message']);
                unset($_SESSION['error_message']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="message success">
                <?php 
                echo htmlspecialchars($_SESSION['success_message']);
                unset($_SESSION['success_message']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
                <p>Your cart is empty</p>
                <a href="index.php" class="checkout-btn">Continue Shopping</a>
            </div>
        <?php else: ?>
            <?php foreach ($cart_items as $item): ?>
                <div class="cart-item">
                    <img src="<?php echo htmlspecialchars($item['Image']); ?>" 
                         alt="<?php echo htmlspecialchars($item['name']); ?>">
                    
                    <div class="item-details">
                        <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                        <p>Price: ₨<?php echo number_format($item['price'], 2); ?></p>
                        <p class="stock-info">Available Stock: <?php echo $item['stock'] + $item['quantity']; ?></p>
                    </div>
                    
                    <form method="POST" class="quantity-controls">
                        <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                        <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" 
                               min="1" max="<?php echo $item['stock'] + $item['quantity']; ?>">
                        <button type="submit" name="update_quantity" class="update-btn">Update</button>
                    </form>
                    
                    <p>Subtotal: ₨ <?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                    
                    <a href="?remove=<?php echo $item['cart_id']; ?>" class="remove-btn" 
                       onclick="return confirm('Are you sure you want to remove this item?')">
                        Remove
                    </a>
                </div>
            <?php endforeach; ?>
            
            <div class="cart-total">
                <p>Total: ₨<?php echo number_format($total, 2); ?></p>
                <a href="checkout.php" class="checkout-btn">Proceed to Checkout</a>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Optional: Add confirmation for quantity updates
        document.querySelectorAll('.quantity-controls form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const quantity = this.querySelector('input[name="quantity"]').value;
                if (quantity < 1) {
                    e.preventDefault();
                    alert('Quantity must be at least 1');
                }
            });
        });
    </script>
</body>
</html>