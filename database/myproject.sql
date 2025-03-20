-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 10, 2024 at 04:59 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `myproject`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `username`, `password`) VALUES
(15, 'divesh', 'divesh');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`cart_id`, `user_id`, `product_id`, `quantity`) VALUES
(10, 1, 9, 1),
(12, 3, 3, 4);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `Image` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `name`, `Image`, `description`) VALUES
(3, 'Home Decor', '67347238d1224.jpeg', ' Discover unique, handcrafted artwork and crafts created with passion and skill. From vibrant paintings and intricate sculptures to charming pottery and delicate needlework, find one-of-a-kind pieces to add personality to your home or gift to someone spec'),
(4, 'Art & Craft', '6734728b2dcba.jpeg', ' Discover unique, handcrafted artwork and crafts created with passion and skill. '),
(5, 'Jewelry', '673472a3842b9.jpeg', ' Discover unique, handcrafted artwork and crafts created with passion and skill. ');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','completed','canceled') DEFAULT 'pending',
  `total_amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `amount` decimal(10,2) NOT NULL,
  `payment_type` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL,
  `Image` varchar(255) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `name`, `description`, `price`, `stock`, `Image`, `category_id`) VALUES
(1, 'Paper Flower Frame', 'Modern 3D paper art featuring elegant fan-folded orange flowers against a black frame. This contemporary piece includes 6 handcrafted paper flowers with curved stems, creating a stunning dimensional effect. Perfect for modern home decor. Frame size: 12\" x 18\".', 150.00, 13, 'uploads/products/6734762dcde03_1731491373.jpeg', 3),
(2, 'Navrang Matka', 'Yellow matka with blue rim and temple motif medallion.', 180.00, 10, 'uploads/products/673476ce2baea_1731491534.jpg', 4),
(3, 'Pink Chain Necklace', 'Delicate gold chain necklace with royal blue thread wrap and pink pom poms. Perfect for casual wear. Length: 18 inches.', 50.00, 12, 'uploads/products/673476f7d883b_1731491575.jpg', 5),
(7, 'Welcome Sign Wall Decor', 'cheerful three-tiered welcome sign reading \"Welcome to Our Happy Place\". Features hand-painted designs including birds, flowers, and bees with colorful tassels. Made with high-quality wood and weather-resistant paint. Perfect for entryways, porches, or living rooms.', 150.00, 12, 'uploads/products/6734789eb41ce_1731491998.jpeg', 3),
(8, 'Mandala Wall Hanging', 'Handcrafted geometric mandala wall hanging featuring intricate traditional designs in vibrant colors of blue, green, yellow, and pink. Adorned with colorful tassels in blue, pink, and yellow. Perfect for adding a bohemian touch to any room.', 180.00, 20, 'uploads/products/673478cf99d86_1731492047.jpeg', 3),
(9, 'Toran', 'Elegant rope art featuring handwoven leaf designs in natural earth tones and green. This unique piece combines traditional rope weaving techniques with modern design. Mounted in a light wooden frame, perfect for adding natural texture to any room. Size: 16\" x 24\".', 200.00, 14, 'uploads/products/673478ed77b56_1731492077.jpeg', 3),
(10, ' Necklace Set', 'Bohemian style necklace with colorful layered tassels in navy, red, and mustard. Includes matching earrings. Adorned with silver beads. Adjustable length.', 150.00, 10, 'uploads/products/67347920d6aef_1731492128.jpg', 5),
(11, 'Multi-Gemstone Bracelet', 'Adjustable macrame bracelet featuring mixed pastel gemstones and silver accents. Includes rose quartz, amazonite, and garnet beads.', 50.00, 4, 'uploads/products/6734794b9e9fb_1731492171.jpeg', 5),
(12, 'Earrings', 'Handcrafted ceramic tile design earrings with blue and turquoise patterns. Features golden-banded navy silk tassels. Length: 3 inches.', 80.00, 12, 'uploads/products/673479af63559_1731492271.jpg', 5),
(13, 'Kalash', 'Yellow hand-painted matka with meenakari style floral design.', 180.00, 2, 'uploads/products/673479e20a9f6_1731492322.jpeg', 4),
(14, 'Sanskritik Kalash', 'Cream-colored kalash set with black tribal art, ideal for traditional decor', 500.00, 21, 'uploads/products/67347a0f3f121_1731492367.jpg', 4),
(15, 'Neelam Gharha', 'Deep blue gharha with white paisley patterns.', 280.00, 8, 'uploads/products/67347a5659fcb_1731492438.jpg', 4);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `Address` varchar(255) DEFAULT NULL,
  `ph_no` varchar(15) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `security_question` varchar(255) NOT NULL,
  `security_answer` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `Address`, `ph_no`, `created_at`, `security_question`, `security_answer`) VALUES
(3, 'abc', 'abc123@gmail.com', '$2y$10$E5eY9/67L9qWpwBdwGwnR.oQG1vVdNFynTdv0.CvBSGTQG1lasyna', 'london', '6532236598', '2024-12-06 09:03:30', 'What was the name of your first pet?', '$2y$10$Owsgt8uZtLEiN3NOYdWZ8eUEo/GB3Lo//HhssXj2IzQ5ze0sEDCBe');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `Foreign Key` (`product_id`),
  ADD KEY `FK` (`user_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `foreign key` (`category_id`) USING BTREE;

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `Foreign Key` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
