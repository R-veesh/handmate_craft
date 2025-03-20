<?php 
session_start(); 
include 'db.php';  

// Initialize cart count if not set
if (!isset($_SESSION['cart_count'])) {
    $_SESSION['cart_count'] = 0;
}

// Fetch products from the database with category and stock information
$products = $con->query("SELECT p.*, c.name as category_name 
                        FROM products p 
                        LEFT JOIN categories c ON p.category_id = c.category_id
                        WHERE p.stock > 0"); 
?> 

<!DOCTYPE html> 
<html lang="en"> 
<head>     
    <meta charset="UTF-8">     
    <meta name="viewport" content="width=device-width, initial-scale=1.0">     
    <title>Online Handmade Goods Store</title>     
    <link rel="stylesheet" href="Styles/index.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        .stock-info {
            margin: 10px 0;
            font-size: 0.9em;
            color: #666;
        }
        .out-of-stock {
            color: #ff0000;
            font-weight: bold;
        }
        .low-stock {
            color: #ffa500;
        }
        .add-to-cart-btn:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }
        .product-description {
            max-height: 55px;
            overflow: hidden;
            position: relative;
            transition: max-height 0.3s ease;
        }
        .product-description.expanded {
            max-height: 200px;
        }
        .read-more {
            color: blue;
            cursor: pointer;
            display: inline-block;
            margin-left: 5px;
        }
      
        .error-image {
            width: 100%;
            height: 200px;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f0f0f0;
            color: #888;
        }
        footer .footer-content{
            align-items: center;
            justify-content: center;
            display: flex;
            color: #2874f0;
            font-weight: bold;
        }
    </style>
</head> 
<body> 
    <header>     
        <div class="header-content">         
            <div class="header-title">             
                <h1>Welcome to Online Handmade Goods</h1>         
            </div>                  
            
            <?php if(isset($_SESSION['user_id'])): ?>             
                <a href="UserProfile.php" class="user-profile-link">
                    <div class="user-profile">                 
                        <div class="user-profile-container">                     
                            <div class="user-logo">                         
                                <?php                          
                                $username = $_SESSION['username'] ?? 'User';                         
                                echo strtoupper(substr($username, 0, 1));                          
                                ?>                     
                            </div>                     
                            <div class="user-name">                         
                                <?php echo htmlspecialchars($username); ?>                     
                            </div>                 
                        </div>                 
                    </div>
                </a>
            <?php endif; ?>          

            <div class="header-navigation">             
                <nav>                 
                    <ul>
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <li><a class="logoutbtn" href="logout.php">Logout</a></li>
                            <li>
                                <a href="Cart.php" class="cart-icon">
                                    My Cart
                                    <?php
                                    $cartCount = isset($_SESSION['cart_count']) ? $_SESSION['cart_count'] : 0;
                                    if($cartCount > 0): ?>
                                        <span class="cart-count"><?php echo $cartCount; ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                            <li><a href="productCategory.php">Product Categories</a></li>     
                        <?php else: ?>      
                            <li><a href="login.php">Login</a></li>                         
                            <li><a href="register.php">Register</a></li>   
                            <li><a href="productCategory.php">Product Categories</a></li>                  
                        <?php endif; ?>                 
                    </ul>             
                </nav>         
            </div>     
        </div> 
    </header>    

    <div class="container">         
        <h2>Our Products</h2>         
        <div class="product-list">             
            <?php 
            // Check if there are any products
            if ($products->num_rows > 0):
                while($product = $products->fetch_assoc()): 
            ?>                 
                <div class="product-item">                     
                    <div class="product-image-container">                         
                        <?php                          
                        $image_path = $product['Image'];                         
                        if(!empty($image_path) && file_exists($image_path)):                         
                        ?>                             
                            <img src="<?php echo htmlspecialchars($image_path); ?>"                                 
                            alt="<?php echo htmlspecialchars($product['name']); ?>"                                 
                            class="product-image">                         
                        <?php else: ?>                             
                        <div class="error-image">                                 
                            No image available                             
                        </div>                         
                        <?php endif; ?>                     
                    </div>                    

                    <h3><?php echo htmlspecialchars($product['name']); ?></h3>   
                                      
                    <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>  
                    <span class="read-more">... More</span>

                    <?php if(!empty($product['category_name'])): ?>                         
                        <div class="product-category-tag">                             
                            <?php echo "Category : ", htmlspecialchars($product['category_name']); ?>                         
                        </div>                     
                    <?php endif; ?>

                    <div class="stock-info <?php echo ($product['stock'] <= 0 ? 'out-of-stock' : ($product['stock'] <= 5 ? 'low-stock' : '')); ?>">
                        <?php
                        if ($product['stock'] <= 0) {
                            echo 'Out of Stock';
                        } elseif ($product['stock'] <= 5) {
                            echo 'Low Stock: ' . $product['stock'] . ' left';
                        } else {
                            echo 'Available: ' . $product['stock'];
                        }
                        ?>
                    </div>                   
                    <div class="product-price">                         
                        ₨ <?php echo number_format($product['price'], 2); ?>                     
                    </div>

                    <form action="Cart.php" method="POST" class="cart-form">                         
                        <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                        <input type="hidden" name="available_stock" value="<?php echo $product['stock']; ?>">
                        <button type="submit" 
                                class="add-to-cart-btn" 
                                <?php echo ($product['stock'] <= 0 ? 'disabled' : ''); ?>>
                            <?php echo ($product['stock'] <= 0 ? 'Out of Stock' : 'Add to Cart'); ?>
                        </button>
                    </form>
                </div>             
            <?php 
                endwhile; 
            else:
            ?>
                <div class="no-products">
                    <p>No products available at the moment.</p>
                </div>
            <?php endif; ?>         
        </div>     
    </div>

    <footer>
        <div class="footer-content">
            <p>&copy; <?php echo date('Y'); ?> Online Handmade Goods. All Rights Reserved.</p>
        </div>
    </footer>

    <script>
        // Product description expand/collapse functionality
        document.querySelectorAll('.product-item').forEach(item => {
            const description = item.querySelector('.product-description');
            const readMore = item.querySelector('.read-more');

            if (description && readMore) {
                readMore.addEventListener('click', () => {
                    const productId = item.querySelector('input[name="product_id"]').value;
                    window.location.href = `productDetails.php?id=${productId}`;
                });
            }
        });

        // Optional: Cart count update animation
        function updateCartCount(count) {
            const cartCount = document.querySelector('.cart-count');
            if (cartCount) {
                cartCount.textContent = count;
                cartCount.style.animation = 'pulse 0.5s';
                setTimeout(() => {
                    cartCount.style.animation = '';
                }, 500);
            }
        }
    </script>
</body> 
</html>

<?php 
// Close the database connection
$con->close(); 
?>