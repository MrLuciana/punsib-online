-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 30, 2025 at 06:13 PM
-- Server version: 10.6.19-MariaDB-log
-- PHP Version: 8.3.15

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `punsib_online`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `image`, `status`, `created_at`, `updated_at`) VALUES
(1, 'ขนมปั้นสิบ', 'ขนมปั้นสิบแบบดั้งเดิมของยายนิด', NULL, 1, '2025-03-27 10:55:37', '2025-03-29 07:09:38'),
(2, 'ขนมไทย', 'ขนมไทยสูตรโบรา', NULL, 1, '2025-03-27 10:55:37', '2025-03-27 10:55:37'),
(3, 'ขนมอบ', 'ขนมอบกรอบอร่อย', NULL, 1, '2025-03-27 10:55:37', '2025-03-27 10:55:37'),
(4, 'ขนมหวาน', 'ขนมหวานหลากหลาย', NULL, 1, '2025-03-27 10:55:37', '2025-03-27 10:55:37');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_number` varchar(20) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `payment_status` enum('pending','paid','failed') DEFAULT 'pending',
  `order_status` enum('pending','processing','shipped','delivered','completed','cancelled') DEFAULT 'pending',
  `shipping_address` text NOT NULL,
  `billing_address` text DEFAULT NULL,
  `note` text DEFAULT NULL,
  `payment_slips` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_number`, `total_amount`, `payment_method`, `payment_status`, `order_status`, `shipping_address`, `billing_address`, `note`, `payment_slips`, `created_at`, `updated_at`) VALUES
(1, 2, 'ORD-1743224945200', 95.00, 'bank_transfer', 'pending', 'pending', 'test', 'test', '', '67e7982572c69_1743231013.jpg', '2025-03-29 05:09:05', '2025-03-29 06:50:13'),
(2, 2, 'ORD-1743226285555', 250.00, 'qr_code', 'pending', 'cancelled', 'test', 'test', '', NULL, '2025-03-29 05:31:25', '2025-03-29 06:49:48'),
(3, 2, 'ORD-1743231121208', 95.00, 'bank_transfer', 'pending', 'pending', 'test', 'test', '', '67e7989c3cb4e_1743231132.png', '2025-03-29 06:52:01', '2025-03-29 06:52:12'),
(4, 2, 'ORD-1743231293475', 95.00, 'cash', 'pending', 'cancelled', 'test', 'test', '', NULL, '2025-03-29 06:54:53', '2025-03-29 10:59:35'),
(5, 1, 'ORD-1743235753994', 130.00, 'bank_transfer', 'paid', 'completed', 'test', 'test', '', '67e7aab2081aa_1743235762.png', '2025-03-29 08:09:13', '2025-03-29 09:31:50'),
(6, 1, 'ORD-1743258380137', 85.00, 'bank_transfer', 'paid', 'completed', 'test', 'test', '', '67e80316e94fc_1743258390.jpg', '2025-03-29 14:26:20', '2025-03-29 14:27:13');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`, `total_price`, `created_at`) VALUES
(1, 1, 1, 1, 45.00, 45.00, '2025-03-29 05:09:05'),
(2, 2, 2, 1, 60.00, 60.00, '2025-03-29 05:31:25'),
(3, 2, 3, 1, 50.00, 50.00, '2025-03-29 05:31:25'),
(4, 2, 1, 1, 45.00, 45.00, '2025-03-29 05:31:25'),
(5, 2, 5, 1, 45.00, 45.00, '2025-03-29 05:31:25'),
(6, 3, 1, 1, 45.00, 45.00, '2025-03-29 06:52:01'),
(7, 4, 1, 1, 45.00, 45.00, '2025-03-29 06:54:53'),
(8, 5, 1, 1, 45.00, 45.00, '2025-03-29 08:09:13'),
(9, 5, 4, 1, 35.00, 35.00, '2025-03-29 08:09:13'),
(10, 6, 4, 1, 35.00, 35.00, '2025-03-29 14:26:20');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `discount_price` decimal(10,2) DEFAULT 0.00,
  `image` varchar(255) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `featured` tinyint(1) DEFAULT 0,
  `views` int(100) DEFAULT NULL,
  `sold` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `price`, `discount_price`, `image`, `stock`, `status`, `featured`, `views`, `sold`, `created_at`, `updated_at`) VALUES
(1, 1, 'ขนมปั้นสิบแบบดั้งเดิม', 'ขนมปั้นสิบสูตรดั้งเดิมของยายนิด ทำจากแป้งข้าวเหนียวและมะพร้าวอ่อน', 50.00, 45.00, '67e7a6d6eda98_product1.jpg', 97, 1, 1, 39, 5, '2025-03-27 10:55:37', '2025-03-29 10:59:35'),
(2, 1, 'ขนมปั้นสิบไส้ทุเรียน', 'ขนมปั้นสิบไส้ทุเรียนหมอนทอง หอมอร่อย', 60.00, NULL, '67e7a76801cae_product1.jpg', 80, 1, 1, 9, 1, '2025-03-27 10:55:37', '2025-03-29 07:55:20'),
(3, 1, 'ขนมปั้นสิบไส้เผือก', 'ขนมปั้นสิบไส้เผือกหวานมัน', 55.00, 50.00, '67e7a7a071f80_product1.jpg', 90, 1, 0, 0, 1, '2025-03-27 10:55:37', '2025-03-29 07:56:16'),
(4, 2, 'ขนมกล้วย', 'ขนมกล้วยนึ่งหอมหวาน', 40.00, 35.00, NULL, 118, 1, 1, 6, 2, '2025-03-27 10:55:37', '2025-03-29 14:26:20'),
(5, 2, 'ขนมตาล', 'ขนมตาลสูตรโบรา', 45.00, 0.00, NULL, 70, 1, 0, 1, 1, '2025-03-27 10:55:37', '2025-03-29 06:49:48'),
(6, 2, 'ขนมใส่ไส้', 'ขนมไทยใส่ไส้ถั่วเขียว', 50.00, 0.00, NULL, 60, 1, 0, 0, NULL, '2025-03-27 10:55:37', '2025-03-27 15:32:48'),
(7, 3, 'ขนมปังสังขยา', 'ขนมปังสังขยานุ่มหอม', 30.00, 25.00, NULL, 150, 1, 1, 0, NULL, '2025-03-27 10:55:37', '2025-03-27 15:32:53'),
(8, 3, 'คุกกี้มะพร้าว', 'คุกกี้มะพร้าวกรอบอร่อย', 35.00, 0.00, NULL, 200, 1, 0, 0, NULL, '2025-03-27 10:55:37', '2025-03-27 15:32:57'),
(9, 4, 'วุ้นมะพร้าว', 'วุ้นมะพร้าวอ่อนสดชื่น', 40.00, 35.00, NULL, 100, 1, 1, 2, NULL, '2025-03-27 10:55:37', '2025-03-29 11:34:46'),
(10, 4, 'ขนมชั้น', 'ขนมชั้นสีสันสวยงาม', 50.00, 0.00, NULL, 80, 1, 0, 0, NULL, '2025-03-27 10:55:37', '2025-03-27 15:32:54');

-- --------------------------------------------------------

--
-- Table structure for table `revenue`
--

CREATE TABLE `revenue` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','credit_card','bank_transfer','qr_code') NOT NULL,
  `source` enum('online','walk_in','delivery') NOT NULL,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `shipping_fee` decimal(10,2) DEFAULT 0.00,
  `tax_amount` decimal(10,2) DEFAULT 0.00,
  `net_amount` decimal(10,2) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `revenue`
--

INSERT INTO `revenue` (`id`, `date`, `order_id`, `product_id`, `category_id`, `amount`, `payment_method`, `source`, `discount_amount`, `shipping_fee`, `tax_amount`, `net_amount`, `notes`, `created_at`, `updated_at`) VALUES
(5, '2025-03-29', 5, 4, 2, 35.00, 'bank_transfer', 'online', 0.00, 0.00, 0.00, 35.00, 'รายได้จากคำสั่งซื้อ #ORD-1743235753994', '2025-03-29 09:31:50', '2025-03-29 09:31:50'),
(4, '2025-03-29', 5, 1, 1, 45.00, 'bank_transfer', 'online', 0.00, 0.00, 0.00, 45.00, 'รายได้จากคำสั่งซื้อ #ORD-1743235753994', '2025-03-29 09:31:50', '2025-03-29 09:31:50'),
(6, '2025-03-29', 6, 4, 2, 35.00, 'bank_transfer', 'online', 0.00, 0.00, 0.00, 35.00, 'รายได้จากคำสั่งซื้อ #ORD-1743258380137', '2025-03-29 14:27:13', '2025-03-29 14:27:13');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `role` enum('customer','admin') DEFAULT 'customer',
  `status` int(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `fullname`, `phone`, `address`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$0GSXGEDLlEmXVpwaU.pG1OWYKePxnqz5QvIe9iGC/jSl47mMtNTIG', 'admin@punsib.com', 'ผู้ดูแลระบบ', '', 'test', 'admin', 1, '2025-03-27 09:57:33', '2025-03-29 10:19:11'),
(2, 'test', '$2y$10$YGFdP0ojQwc8w0Gn/ywjSeTwIsSNvZ8fAcI2yOpdZIj7/XDHHM/ia', 'test@gmail.com', 'Hello', '0930000000', 'test', 'customer', 1, '2025-03-27 10:49:10', '2025-03-29 11:35:35'),
(3, 'tt', '$2y$10$lNErURvIADguF1MfjMtYeOR4J1b5nG0De60kY/MYkobyRbDztqtWG', 'testt@gmail.com', 'test', '0930000000', '11', 'customer', 1, '2025-03-29 11:32:17', '2025-03-29 11:51:35');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `revenue`
--
ALTER TABLE `revenue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `idx_revenue_date` (`date`),
  ADD KEY `idx_revenue_product` (`product_id`),
  ADD KEY `idx_revenue_category` (`category_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `revenue`
--
ALTER TABLE `revenue`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
