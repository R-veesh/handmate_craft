<?php
session_start();
include 'db.php';

// Fetch all categories with their products
$search_query = isset($_GET['search']) ? $con->real_escape_string($_GET['search']) : '';

$query = "SELECT c.category_id, c.name as category_name, 
          p.product_id, p.name as product_name, p.description, p.price, p.Image, p.stock
          FROM categories c
          LEFT JOIN products p ON c.category_id = p.category_id
          WHERE p.name LIKE '%$search_query%' OR p.description LIKE '%$search_query%' OR c.name LIKE '%$search_query%'
          ORDER BY c.name, p.name";
          
$result = $con->query($query);

// Organize products by category
$categories = [];
while ($row = $result->fetch_assoc()) {
    if (!isset($categories[$row['category_id']])) {
        $categories[$row['category_id']] = [
            'name' => $row['category_name'],
            'products' => []
        ];
    }
    if ($row['product_id']) {  // Only add if product exists
        $categories[$row['category_id']]['products'][] = [
            'id' => $row['product_id'],
            'name' => $row['product_name'],
            'description' => $row['description'],
            'price' => $row['price'],
            'image' => $row['Image'],
            'stock' => $row['stock']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Categories</title>
    <link rel="stylesheet" href="Styles/index.css">
    <style>
        .search-container {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
            padding: 1rem;
            background-color: #f4f4f4;
        }

        .search-container form {
            display: flex;
            width: 100%;
            max-width: 600px;
        }

        .search-input {
            flex-grow: 1;
            padding: 0.75rem;
            font-size: 1rem;
            border: 1px solid #ddd;
            border-right: none;
            border-radius: 4px 0 0 4px;
        }

        .search-button {
            padding: 0.75rem 1.5rem;
            background-color: #007bff;
            color: white;
            border: 1px solid #007bff;
            border-radius: 0 4px 4px 0;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .search-button:hover {
            background-color: #0056b3;
        }

        .category-section {
            margin: 2rem 0;
            padding: 1rem;
            background-color: #f9f9f9;
            border-radius: 8px;
        }

        .category-title {
            color: #333;
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #ddd;
        }

        .category-products {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
            padding: 1rem 0;
        }

        .product-item {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 1rem;
            transition: transform 0.2s;
        }

        .product-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .product-image-container {
            height: 200px;
            overflow: hidden;
            border-radius: 4px;
            margin-bottom: 1rem;
        }

        .product-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .error-image {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f5f5f5;
            color: #666;
        }

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

        .product-description {
            max-height: 55px;
            overflow: hidden;
            position: relative;
            transition: max-height 0.3s ease;
        }

        .product-more {
            color: blue;
            cursor: pointer;
            display: inline-block;
            margin-left: 5px;
        }

        .product-price {
            font-size: 1.2rem;
            font-weight: bold;
            color: #2c3e50;
            margin: 0.5rem 0;
        }

        .no-products {
            text-align: center;
            padding: 2rem;
            color: #666;
            font-style: italic;
        }

        .search-results-info {
            text-align: center;
            margin-bottom: 1rem;
            color: #666;
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
            <h1>Product Categories</h1>
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
                    <li><a href="index.php">Home</a></li>
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
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </div>
</header>

<div class="search-container">
    <form action="productCategory.php" method="GET">
        <input type="text" name="search" class="search-input" 
               placeholder="Search products by name, description, or category" 
               value="<?php echo htmlspecialchars($search_query); ?>">
        <button type="submit" class="search-button">Search</button>
    </form>
</div>

<?php if (!empty($search_query)): ?>
    <div class="search-results-info">
        <?php 
        $total_products = 0;
        foreach ($categories as $category) {
            $total_products += count($category['products']);
        }
        ?>
        Showing <?php echo $total_products; ?> results for "<?php echo htmlspecialchars($search_query); ?>"
    </div>
<?php endif; ?>

<div class="container">
    <?php if (empty($categories)): ?>
        <div class="no-products">
            <h2>No products found</h2>
        </div>
    <?php else: ?>
        <?php foreach ($categories as $category): ?>
            <section class="category-section">
                <h2 class="category-title"><?php echo htmlspecialchars($category['name']); ?></h2>
                
                <?php if (empty($category['products'])): ?>
                    <div class="no-products">
                        <p>No products available in this category</p>
                    </div>
                <?php else: ?>
                    <div class="category-products">
                        <?php foreach ($category['products'] as $product): ?>
                            <div class="product-item">
                                <div class="product-image-container">
                                    <?php if(!empty($product['image']) && file_exists($product['image'])): ?>
                                        <img src="<?php echo htmlspecialchars($product['image']); ?>"
                                            alt="<?php echo htmlspecialchars($product['name']); ?>"
                                            class="product-image">
                                    <?php else: ?>
                                        <div class="error-image">
                                            No image available
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                                <p class="product-description">
                                    <?php echo htmlspecialchars($product['description']); ?>
                                </p>
                                <span class="product-more" data-product-id="<?php echo $product['id']; ?>">More Details</span>

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
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <input type="hidden" name="available_stock" value="<?php echo $product['stock']; ?>">
                                    <button type="submit" 
                                            class="add-to-cart-btn" 
                                            <?php echo ($product['stock'] <= 0 ? 'disabled' : ''); ?>>
                                        <?php echo ($product['stock'] <= 0 ? 'Out of Stock' : 'Add to Cart'); ?>
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

    <footer>
        <div class="footer-content">
            <p>&copy; <?php echo date('Y'); ?> Online Handmade Goods. All Rights Reserved.</p>
        </div>
    </footer>
<script>
    // Redirect to product details page when "More Details" is clicked
    document.querySelectorAll('.product-more').forEach(item => {
        item.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            window.location.href = `productDetails.php?id=${productId}`;
        });
    });
</script>
</body>
</html>