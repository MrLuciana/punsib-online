-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 12, 2025 at 12:29 AM
-- Server version: 10.6.19-MariaDB-log
-- PHP Version: 8.3.17

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
(25, 'ขนมปั้นสิบ', '', 'cat_68187c45d623e6.00298537.png', 1, '2025-05-05 08:52:21', '2025-05-05 08:52:21'),
(26, 'ขนม', '', 'cat_68187c692b1bf8.11176975.png', 1, '2025-05-05 08:52:57', '2025-05-05 08:52:57'),
(27, 'วัตถุดิบ', '', 'cat_68187c73ceebc7.90221089.png', 1, '2025-05-05 08:53:07', '2025-05-05 08:53:07'),
(28, 'เครื่องดื่ม', '', 'cat_68187c7b82d191.22476145.png', 1, '2025-05-05 08:53:15', '2025-05-05 08:53:15'),
(30, 'เครื่องปรุง  และส่วนผสม อาหาร', '', 'cat_68187d5597f2b5.22165616.png', 1, '2025-05-05 08:56:53', '2025-05-05 08:56:53'),
(33, 'ขนมปั้นสิบแบบอบ', '', 'cat_6819f13c987da9.19681059.png', 1, '2025-05-06 11:23:40', '2025-05-06 11:23:40');

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
(6, 1, 'ORD-1743258380137', 85.00, 'bank_transfer', 'paid', 'completed', 'test', 'test', '', '67e80316e94fc_1743258390.jpg', '2025-03-29 14:26:20', '2025-03-29 14:27:13'),
(7, 1, 'ORD-1746261292935', 509.00, 'cash', 'pending', 'cancelled', '172 ม.7 ต.มะกอกเหนือ อ.ควนขนุน จ.พัทลุง93150', '172 ม.7 ต.มะกอกเหนือ อ.ควนขนุน จ.พัทลุง93150', '', NULL, '2025-05-03 08:34:52', '2025-05-03 08:35:26'),
(8, 4, 'ORD-1746269286434', 309.00, 'cash', 'paid', 'processing', '172 หมู่7 ตำบลมะกอกเหนือ อำเภอควนขนุข จังหวัดพัทลุง93150', '172 หมู่7 ตำบลมะกอกเหนือ อำเภอควนขนุข จังหวัดพัทลุง93150', '', NULL, '2025-05-03 10:48:06', '2025-05-03 10:48:57'),
(9, 1, 'ORD-1746533795991', 577.00, 'cash', 'pending', 'pending', 'test', 'test', '', NULL, '2025-05-06 12:16:35', '2025-05-06 12:16:35'),
(10, 4, 'ORD-1746614898841', 370.00, 'cash', 'paid', 'completed', '172 หมู่7 ตำบลมะกอกเหนือ อำเภอควนขนุน จังหวัดพัทลุง93150', '172 หมู่7 ตำบลมะกอกเหนือ อำเภอควนขนุน จังหวัดพัทลุง93150', '', NULL, '2025-05-07 10:48:18', '2025-05-07 11:04:57'),
(11, 4, 'ORD-1746877463627', 678.00, 'bank_transfer', 'pending', 'pending', '172 หมู่7 ตำบลมะกอกเหนือ อำเภอควนขนุข จังหวัดพัทลุง93150', '172 หมู่7 ตำบลมะกอกเหนือ อำเภอควนขนุข จังหวัดพัทลุง93150', '', NULL, '2025-05-10 11:44:23', '2025-05-10 11:44:23'),
(12, 4, 'ORD-1746877604125', 110.00, 'bank_transfer', 'pending', 'pending', '172 หมู่7 ตำบลมะกอกเหนือ อำเภอควนขนุข จังหวัดพัทลุง93150', '172 หมู่7 ตำบลมะกอกเหนือ อำเภอควนขนุข จังหวัดพัทลุง93150', '', NULL, '2025-05-10 11:46:44', '2025-05-10 11:46:44'),
(13, 4, 'ORD-1746881121194', 130.00, 'cash', 'pending', 'pending', '172 หมู่7 ตำบลมะกอกเหนือ อำเภอควนขนุข จังหวัดพัทลุง93150', '172 หมู่7 ตำบลมะกอกเหนือ อำเภอควนขนุข จังหวัดพัทลุง93150', '', NULL, '2025-05-10 12:45:21', '2025-05-10 12:45:21'),
(14, 4, 'ORD-1746881887845', 200.00, 'bank_transfer', 'pending', 'completed', '172 หมู่7 ตำบลมะกอกเหนือ อำเภอควนขนุข จังหวัดพัทลุง93150', '172 หมู่7 ตำบลมะกอกเหนือ อำเภอควนขนุข จังหวัดพัทลุง93150', '', NULL, '2025-05-10 12:58:07', '2025-05-10 13:40:36'),
(15, 4, 'ORD-1746884601283', 239.00, 'cash', 'pending', 'pending', '172 หมู่7 ตำบลมะกอกเหนือ อำเภอควนขนุข จังหวัดพัทลุง93150', '172 หมู่7 ตำบลมะกอกเหนือ อำเภอควนขนุข จังหวัดพัทลุง93150', '', NULL, '2025-05-10 13:43:21', '2025-05-10 13:43:21'),
(16, 4, 'ORD-1747210592920', 239.00, 'bank_transfer', 'pending', 'pending', '172 หมู่7 ตำบลมะกอกเหนือ อำเภอควนขนุข จังหวัดพัทลุง93150', '172 หมู่7 ตำบลมะกอกเหนือ อำเภอควนขนุข จังหวัดพัทลุง93150', '', NULL, '2025-05-14 08:16:32', '2025-05-14 08:16:32'),
(17, 4, 'ORD-1747210664307', 289.00, 'bank_transfer', 'pending', 'pending', '172 หมู่7 ตำบลมะกอกเหนือ อำเภอควนขนุข จังหวัดพัทลุง93150', '172 หมู่7 ตำบลมะกอกเหนือ อำเภอควนขนุข จังหวัดพัทลุง93150', '', NULL, '2025-05-14 08:17:44', '2025-05-14 08:17:44'),
(18, 4, 'ORD-1747211965818', 289.00, 'bank_transfer', 'pending', 'cancelled', '172 หมู่7 ตำบลมะกอกเหนือ อำเภอควนขนุข จังหวัดพัทลุง93150', '172 หมู่7 ตำบลมะกอกเหนือ อำเภอควนขนุข จังหวัดพัทลุง93150', '', NULL, '2025-05-14 08:39:25', '2025-05-14 09:27:56'),
(19, 4, 'ORD-1748974477391', 210.00, 'bank_transfer', 'pending', 'pending', '172 หมู่7 ตำบลมะกอกเหนือ อำเภอควนขนุข จังหวัดพัทลุง93150', '172 หมู่7 ตำบลมะกอกเหนือ อำเภอควนขนุข จังหวัดพัทลุง93150', '', NULL, '2025-06-03 18:14:37', '2025-06-03 18:14:37'),
(20, 4, 'ORD-1748974890197', 239.00, 'qr_code', 'pending', 'pending', '172 หมู่7 ตำบลมะกอกเหนือ อำเภอควนขนุข จังหวัดพัทลุง93150', '172 หมู่7 ตำบลมะกอกเหนือ อำเภอควนขนุข จังหวัดพัทลุง93150', '', NULL, '2025-06-03 18:21:30', '2025-06-03 18:21:30'),
(21, 4, 'ORD-1748975259779', 220.00, 'qr_code', 'pending', 'pending', '172 หมู่7 ตำบลมะกอกเหนือ อำเภอควนขนุข จังหวัดพัทลุง93150', '172 หมู่7 ตำบลมะกอกเหนือ อำเภอควนขนุข จังหวัดพัทลุง93150', '', NULL, '2025-06-03 18:27:39', '2025-06-03 18:27:39'),
(22, 5, 'ORD-1748975410345', 289.00, 'qr_code', 'paid', 'processing', '111 ม.3 ต.ตำนาน อ.เมือง จ.พัทลุง93000', '111 ม.3 ต.ตำนาน อ.เมือง จ.พัทลุง93000', '', NULL, '2025-06-03 18:30:10', '2025-06-03 18:30:50'),
(23, 5, 'ORD-1748975534275', 300.00, 'qr_code', 'pending', 'pending', '111 ม.3 ต.ตำนาน อ.เมือง จ.พัทลุง93000', '111 ม.3 ต.ตำนาน อ.เมือง จ.พัทลุง93000', '', '683f3fe8c9b19_1748975592.png', '2025-06-03 18:32:14', '2025-06-03 18:33:12'),
(24, 6, 'ORD-1749114639517', 289.00, 'qr_code', 'pending', 'pending', '76 ม.7 ต.มะกอกเหนือ อ.ควนขนุน จ.พัทลุง 93150', '76 ม.7 ต.มะกอกเหนือ อ.ควนขนุน จ.พัทลุง 93150', '', NULL, '2025-06-05 09:10:39', '2025-06-05 09:10:39'),
(25, 6, 'ORD-1749114763960', 110.00, 'bank_transfer', 'pending', 'pending', '76 ม.7 ต.มะกอกเหนือ อ.ควนขนุน จ.พัทลุง 93150', '76 ม.7 ต.มะกอกเหนือ อ.ควนขนุน จ.พัทลุง 93150', '', NULL, '2025-06-05 09:12:43', '2025-06-05 09:12:43'),
(26, 6, 'ORD-1749115145773', 300.00, 'bank_transfer', 'pending', 'pending', '76 ม.7 ต.มะกอกเหนือ อ.ควนขนุน จ.พัทลุง 93150', '76 ม.7 ต.มะกอกเหนือ อ.ควนขนุน จ.พัทลุง 93150', '', NULL, '2025-06-05 09:19:05', '2025-06-05 09:19:05'),
(27, 1, 'ORD-1749115554920', 239.00, 'qr_code', 'pending', 'pending', 'test', 'test', '', '68431148b5dfc_1749225800.png', '2025-06-05 09:25:54', '2025-06-06 16:03:20'),
(28, 1, 'ORD-1749116788474', 110.00, 'qr_code', 'pending', 'pending', 'test', 'test', '', '6841677ec2d81_1749116798.png', '2025-06-05 09:46:28', '2025-06-05 09:46:38'),
(29, 7, 'ORD-1749117573928', 210.00, 'cash', 'pending', 'pending', '46/2 ม.3 ต.ชัยบุรี อ.เมือง จ.พัทลุง 93000', '46/2 ม.3 ต.ชัยบุรี อ.เมือง จ.พัทลุง 93000', '', NULL, '2025-06-05 09:59:33', '2025-06-05 09:59:33'),
(30, 6, 'ORD-1749225517462', 210.00, 'bank_transfer', 'paid', 'processing', '76 ม.7 ต.มะกอกเหนือ อ.ควนขนุน จ.พัทลุง 93150', '76 ม.7 ต.มะกอกเหนือ อ.ควนขนุน จ.พัทลุง 93150', '', NULL, '2025-06-06 15:58:37', '2025-06-06 16:20:52'),
(31, 6, 'ORD-1749226436535', 239.00, 'bank_transfer', 'failed', 'cancelled', '76 ม.7 ต.มะกอกเหนือ อ.ควนขนุน จ.พัทลุง 93150', '76 ม.7 ต.มะกอกเหนือ อ.ควนขนุน จ.พัทลุง 93150', '', '684313da07025_1749226458.jpg', '2025-06-06 16:13:56', '2025-06-06 16:14:58'),
(32, 8, 'ORD-1749227383100', 577.00, 'qr_code', 'paid', 'processing', '10 ม.3 ต.ท่าแค อ.เมือง จ.พัทลุง 93000', '10 ม.3 ต.ท่าแค อ.เมือง จ.พัทลุง 93000', '', '68431787c60aa_1749227399.jpg', '2025-06-06 16:29:43', '2025-06-06 16:31:31');

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
(10, 6, 4, 1, 35.00, 35.00, '2025-03-29 14:26:20'),
(11, 7, 18, 1, 259.00, 259.00, '2025-05-03 08:34:52'),
(12, 7, 21, 1, 200.00, 200.00, '2025-05-03 08:34:52'),
(13, 8, 22, 1, 259.00, 259.00, '2025-05-03 10:48:06'),
(14, 9, 27, 1, 189.00, 189.00, '2025-05-06 12:16:35'),
(15, 9, 30, 1, 259.00, 259.00, '2025-05-06 12:16:35'),
(16, 9, 28, 1, 129.00, 129.00, '2025-05-06 12:16:35'),
(17, 10, 42, 2, 160.00, 320.00, '2025-05-07 10:48:18'),
(18, 11, 24, 1, 239.00, 239.00, '2025-05-10 11:44:23'),
(19, 11, 29, 1, 250.00, 250.00, '2025-05-10 11:44:23'),
(20, 11, 27, 1, 189.00, 189.00, '2025-05-10 11:44:23'),
(21, 12, 49, 1, 60.00, 60.00, '2025-05-10 11:46:44'),
(22, 13, 47, 1, 80.00, 80.00, '2025-05-10 12:45:21'),
(23, 14, 45, 1, 150.00, 150.00, '2025-05-10 12:58:07'),
(24, 15, 34, 1, 189.00, 189.00, '2025-05-10 13:43:21'),
(25, 16, 27, 1, 189.00, 189.00, '2025-05-14 08:16:32'),
(26, 17, 24, 1, 239.00, 239.00, '2025-05-14 08:17:44'),
(27, 18, 24, 1, 239.00, 239.00, '2025-05-14 08:39:25'),
(28, 19, 42, 1, 160.00, 160.00, '2025-06-03 18:14:37'),
(29, 20, 34, 1, 189.00, 189.00, '2025-06-03 18:21:30'),
(30, 21, 44, 1, 170.00, 170.00, '2025-06-03 18:27:39'),
(31, 22, 24, 1, 239.00, 239.00, '2025-06-03 18:30:10'),
(32, 23, 29, 1, 250.00, 250.00, '2025-06-03 18:32:14'),
(33, 24, 24, 1, 239.00, 239.00, '2025-06-05 09:10:39'),
(34, 25, 49, 1, 60.00, 60.00, '2025-06-05 09:12:43'),
(35, 26, 29, 1, 250.00, 250.00, '2025-06-05 09:19:05'),
(36, 27, 27, 1, 189.00, 189.00, '2025-06-05 09:25:54'),
(37, 28, 49, 1, 60.00, 60.00, '2025-06-05 09:46:28'),
(38, 29, 42, 1, 160.00, 160.00, '2025-06-05 09:59:33'),
(39, 30, 42, 1, 160.00, 160.00, '2025-06-06 15:58:37'),
(40, 31, 34, 1, 189.00, 189.00, '2025-06-06 16:13:56'),
(41, 32, 42, 1, 160.00, 160.00, '2025-06-06 16:29:43'),
(42, 32, 32, 1, 239.00, 239.00, '2025-06-06 16:29:43'),
(43, 32, 37, 2, 89.00, 178.00, '2025-06-06 16:29:43');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(24, 25, 'ปั้นสิบไส้ปลายายนิดพัทลุง           (ถุงใหญ่)', 'สั่ง 1 กิโล \r\n✅ไส้เข้มข้นอร่อยถูกปาก ไส้ใช้ปลาทูล้วนๆ \r\n✅อิสลามทานได้100%\r\n✅ผลิตใหม่ต่อวันจริง \r\n✅เก็บนานนอกตู้เย็น 1 เดือน ใส่ตู้เย็น2เดือน', 239.00, NULL, '681884e7cd7fd_666.jpg', 96, 1, 1, NULL, 5, '2025-05-05 09:29:11', '2025-06-05 09:10:39'),
(26, 33, 'อบ100%  ไร้น้ำมัน  ปั้นสิบยายนิดพัทลุง ถุง  1 โล', '❌ขนมจะทำใหม่หลังลูกค้าออเด้อ ทางร้านใช้เวลา 1 -2 วัน ในการทำ ขนมไม่ค้างในสต๊อก ลูกค้าจะได้แต่ที่ทำใหม่ทุกคน ❌\r\n❤️ของดีเมืองพัทลุง ❤️  หรอยแรง\r\n✅ไทยพุธมุสลิมกินได้หมด\r\n✅ขนมปั้นสิบ ไส้ปลา ✅ใช้วิธีการ อบ  \r\n✅ ไม่เคลือบหวาน\r\n✅ทำสดใหม่ทุกวัน ทำวันต่อวัน \r\n✅ไส้แน่นๆ แป้งบางๆ มีรสชาติของพริกไทยนิดๆ กินแล้วเพลิน หรอยเว้อ \r\n#สามารถเก็บนาน 1 เดือน (ใส่ตู้เย็น)', 350.00, 339.00, '681b333dcf0f1_999.jpg', 100, 1, 0, NULL, NULL, '2025-05-06 11:28:24', '2025-05-07 10:17:33'),
(27, 33, 'อบครึ่งโล', '❌ขนมจะทำใหม่หลังลูกค้าออเด้อ ทางร้านใช้เวลา 1 -2 วัน ในการทำ ขนมไม่ค้างในสต๊อก ลูกค้าจะได้แต่ที่ทำใหม่ทุกคน ❌\r\n❤️ของดีเมืองพัทลุง ❤️  หรอยแรง\r\n✅ไทยพุธมุสลิมกินได้หมด\r\n✅ขนมปั้นสิบ ไส้ปลา ✅ใช้วิธีการ อบ  \r\n✅ ไม่เคลือบหวาน\r\n✅ทำสดใหม่ทุกวัน ทำวันต่อวัน \r\n✅ไส้แน่นๆ แป้งบางๆ มีรสชาติของพริกไทยนิดๆ กินแล้วเพลิน หรอยเว้อ \r\n#สามารถเก็บนาน 1 เดือน (ใส่ตู้เย็น)', 189.00, 189.00, '681b330331323_อบบ500.jpg', 96, 1, 1, NULL, 4, '2025-05-06 11:30:19', '2025-06-05 09:25:54'),
(28, 25, 'ปั้นสิบสูตรดั้งเดิมยายนิด(ครึ่งโล)', '❌ขนมจะทำใหม่หลังลูกค้าออเด้อ ทางร้านใช้เวลา 1 -2 วัน ในการทำ ขนมไม่ค้างในสต๊อก ลูกค้าจะได้แต่ที่ทำใหม่ทุกคน ❌\r\n❤️ของดีเมืองพัทลุง ❤️  หรอยแรง\r\n✅ไทยพุธมุสลิมกินได้หมด\r\n✅ทำสดใหม่ทุกวัน ทำวันต่อวัน \r\n✅ไส้แน่นๆ แป้งบางๆ มีรสชาติของพริกไทยนิดๆ กินแล้วเพลิน หรอยเว้อ \r\n#สามารถเก็บนาน 1 เดือน (ใส่ตู้เย็น)', 129.00, NULL, '6819f3382366a_ทอดครึ่งโล.jpg', 99, 1, 0, NULL, 1, '2025-05-06 11:32:08', '2025-05-07 10:04:53'),
(29, 25, 'ปั้นสิบไส้ปลา(แป้งข้าวไรเบอรี่ 1 กิโล)', '❌ขนมจะทำใหม่หลังลูกค้าออเด้อ ทางร้านใช้เวลา 1 -2 วัน ในการทำ ขนมไม่ค้างในสต๊อก ลูกค้าจะได้แต่ที่ทำใหม่ทุกคน ❌\r\n\r\n✅ไทยพุธมุสลิมกินได้หมด\r\n✅ทำสดใหม่ทุกวัน ทำวันต่อวัน \r\n✅ไส้แน่นๆ แป้งบางๆ มีรสชาติของพริกไทยนิดๆ กินแล้วเพลิน หรอยเว้อ\r\n\r\n#สามารถเก็บนาน 1 เดือน (ใส่ตู้เย็น)', 250.00, NULL, '6819f44f8541a_111.jpg', 97, 1, 1, NULL, 3, '2025-05-06 11:36:47', '2025-06-05 09:19:05'),
(33, 26, 'ขนมผิงยายนิด ผลิตสดใหม่ หอม หวาน มัน ผลิตจากกะทิ', 'ขนมผิงยายนิด ผลิตสดใหม่ หอม หวาน มัน ผลิตจากกะทิสด 100%\r\n\r\nปริมาน 150 กรัม', 69.00, NULL, '681b31e514db0_image.png', 20, 1, 0, NULL, NULL, '2025-05-07 10:11:49', '2025-05-07 10:11:49'),
(30, 25, 'ปั้นสิบไส้กุ้ง ถุงใหญ่ 1 โล', 'ขนาด 1 กิโล \r\n\r\n❌ขนมจะทำใหม่หลังลูกค้าออเด้อ ทางร้านใช้เวลา 1 -2 วัน ในการทำ ขนมไม่ค้างในสต๊อก ลูกค้าจะได้แต่ที่ทำใหม่ทุกคน ❌\r\nของดีเมืองพัทลุง  หรอยแรง\r\n✅ไทยพุธมุสลิมกินได้หมด\r\n✅ทำสดใหม่ทุกวัน ทำวันต่อวัน \r\n\r\n#สามารถเก็บนาน 1 เดือน (ใส่ตู้เย็น)', 259.00, NULL, '6819f5fe560c5_482A8A66-49F5-45D9-A4EC-3F2EC3D0F0D6_L0_001-15_3_2568 09_06_03.jpg', 49, 1, 0, NULL, 1, '2025-05-06 11:43:58', '2025-05-07 10:07:26'),
(31, 25, 'ปั้นสิบไส้สับปะรด ถุงใหญ่ 1 โล', 'ขนาด 1 กิโล \r\n\r\n❌ขนมจะทำใหม่หลังลูกค้าออเด้อ ทางร้านใช้เวลา 1 -2 วัน ในการทำ ขนมไม่ค้างในสต๊อก ลูกค้าจะได้แต่ที่ทำใหม่ทุกคน ❌\r\nของดีเมืองพัทลุง  หรอยแรง\r\n✅ไทยพุธมุสลิมกินได้หมด\r\n✅ทำสดใหม่ทุกวัน ทำวันต่อวัน \r\n\r\n#สามารถเก็บนาน 1 เดือน (ใส่ตู้เย็น)', 239.00, NULL, '6819f6516f5d0_89934FF0-759A-4D64-AC05-F733AD94D212_L0_001-15_3_2568 09_06_03.jpg', 0, 1, 0, NULL, NULL, '2025-05-06 11:45:21', '2025-05-07 10:07:14'),
(32, 25, 'ปั้นสิบไส้ไก่ ถุงใหญ่ 1 โล', 'ขนาด 1 กิโล \r\n\r\n❌ขนมจะทำใหม่หลังลูกค้าออเด้อ ทางร้านใช้เวลา 1 -2 วัน ในการทำ ขนมไม่ค้างในสต๊อก ลูกค้าจะได้แต่ที่ทำใหม่ทุกคน ❌\r\nของดีเมืองพัทลุง  หรอยแรง\r\n✅ไทยพุธมุสลิมกินได้หมด\r\n✅ทำสดใหม่ทุกวัน ทำวันต่อวัน \r\n\r\n#สามารถเก็บนาน 1 เดือน (ใส่ตู้เย็น)', 239.00, NULL, '6819f6db74bb0_C5EE1CBE-0674-491C-96A2-73EF217CB65F_L0_001-15_3_2568 09_03_16.jpg', 19, 1, 0, NULL, 1, '2025-05-06 11:47:39', '2025-06-06 16:29:43'),
(34, 26, 'ขนมผูกรักไส้ปลา ยายนิดพัทลุงกรอบมีไส้ทำใหม่ๆทุกวัน', 'ขนาด 500 กรัม\r\nขนมผูกรักไส้ปลา กรอบหอมมัน ทานเพลินมากค่ะ\r\n❌เนื่องจากขนมกรอบมาก แตกง่าย ระหว่างขนส่งถึงลูกค้าขนมแตกบ้างนะคะ ขอคนเข้าใจ', 200.00, 189.00, '681b32c5c8107_33.jpg', 98, 1, 1, NULL, 3, '2025-05-07 10:15:33', '2025-06-06 16:14:24'),
(35, 26, 'ครองแครงทรงเครื่องแป้งสาคูต้น (ครึ่งโล)', 'ครองแครงทางเครื่องแป้งสาคูต้น กรอบนุ่ม \r\nไม่แข็ง กินง่าน หอมเครื่องเทศ\r\n พริกไทย ต้นหอม ผักชี', 100.00, NULL, '681b33f7362c9_image (1).png', 0, 1, 0, NULL, NULL, '2025-05-07 10:20:39', '2025-05-07 10:20:39'),
(36, 26, 'ก้านบัวโบราณทรงเครื่องสูตรต้นตำรับ', 'ขนาด 500 กรัม\r\nร้านผลิตใหม่ๆวันต่อวัน รับประกันรสชาตอร่อยถูกปาก', 159.00, 129.00, '681b343d5d2f7_44444.jpg', 100, 1, 0, NULL, NULL, '2025-05-07 10:21:49', '2025-05-07 10:21:49'),
(37, 26, 'แคปปลาทูแท้รับประกันอร่อยติดใจ', 'แคปปลาทูแท้รับประกันอร่อยติดใจ \r\nถุงใหญ่ 180 กรัม น้องเบาหวิวแต่ได้เยอะมาก \r\nทานเล่น ทานกับส้มตำ ได้หมด', 89.00, NULL, '681b347b7c3d3_33333.jpg', 8, 1, 0, NULL, 2, '2025-05-07 10:22:51', '2025-06-06 16:29:43'),
(38, 26, 'กล้วยตากกลม อร่อยติดใจ(500 กรัม)', 'กล้วยตากกลม อร่อยติดใจ แพ็คเกจ500กรัม ซีนสูญญากาศให้ หอมรสกล้วยแบบธรรมชาติ', 120.00, NULL, '681b34eea713b_image (2).png', 0, 1, 0, NULL, NULL, '2025-05-07 10:24:46', '2025-05-07 10:24:46'),
(39, 26, 'ลูกชิดอบแห้ง ไม่เก่า อร่อยหวานธรรมชาติ', 'ลูกชิดอบแห้ง ไม่เก่า อร่อยหวานธรรมชาติ \r\nทานเพลิน ประโยชน์เยอะ ไม่ใส่สารกันเสีย100%', 45.00, NULL, '681b35466730d_image (3).png', 0, 1, 0, NULL, NULL, '2025-05-07 10:26:14', '2025-05-07 10:26:14'),
(40, 26, 'กาละแมพัทลุงหอมมันอร่อยกวนใหม่ๆวันต่อวัน', 'กาละแมพัทลุงหอมมันอร่อยกวนใหม่ๆทุก \r\nมีไส้ รสดั้งเดิม รสข้าวเหนียวดำ รสใบเตย รสทุเรียน\r\n✅รสชาติไม่หวานมาก อร่อยติดใจ แน่นอน\r\n✅อายุ นับจากวันขนส่งเข้าระบบ 7 วัน  ร้านผลิต และส่งเลย', 150.00, 120.00, '681b35bde9f59_image (4).png', 1, 1, 0, NULL, NULL, '2025-05-07 10:28:13', '2025-05-07 10:28:13'),
(41, 26, 'ขนุนอบ กรอบ หวาน มันอร่อย', 'ขนุนอบ กรอบ หวาน มันอร่อย \r\n\r\nรับประกันอร่อย ไม่เปรี้ยวแน่นอน  อบใหม่', 100.00, NULL, '681b362588fdb_image (5).png', 0, 1, 0, NULL, NULL, '2025-05-07 10:29:57', '2025-05-07 10:29:57'),
(42, 27, 'สาคูต้นแท้100% โรงสาคูมาเอง', 'ถุงใหญ่ 1 กิโล\r\nสาคูแท้ 100%ทำมาจากต้นสาคูไม่มีสารปนเปื้อน ออแกนิก100% \r\nทำสดใหม่ทุกวัน เก็บได้นานเป็นปี 1 กิโล ทำได้เยอะ ประมาณ 60-70 ถ้วย  กวนขายกำไรเกินคุ้ม', 160.00, NULL, '681b3673d2520_11111.jpg', 9, 1, 1, NULL, 6, '2025-05-07 10:31:15', '2025-06-06 16:29:43'),
(43, 27, 'ปลาดุกร้าทะเลน้อย คัดขนาดใหญ่', 'ปลาดุร้าทะเลน้อย คัดขนาดใหญ่ \r\nรับประกันสะอาด อร่อย ทอดแล้วหอมยั่วๆเลยจ้า\r\nร้านคัดขนาดตัวใหญ่ๆให้เลย เนื้อแน่นๆ\r\nตัวใหญ่ ครึ่งกิโล ประมาณ 4-5 ตัว', 200.00, NULL, '681b36e355513_image (6).png', 0, 1, 0, NULL, NULL, '2025-05-07 10:33:07', '2025-05-07 10:33:07'),
(44, 28, 'ผงชาหัวชา ต้นตำหรับชาใต้แท้เข้มข้น100%ติดใจ', 'ถุงละ500กรัม \r\nชงขายได้กำไร ลูกค้าติดใจ 100%\r\nผงชาที่ผสมหัวชาหลายตัว ให้ความหอม สีสวย ไม่ฝาดเพื่อความกลมกล่อม \r\n มีบริการเก็บปลายทางไม่บวกเพิ่ม\r\nซื้อไปขายลูกค้าติดร้านแน่นอน \r\nเพราะชาสูตรของเราไม่เหมือนใคร \r\nผงชาชัก ไม่ชักก็อร่อยกล่มกล่อมแน่นอน\r\nต้นตำหรับชาปักใต้', 170.00, NULL, '681b376eecefe_image (7).png', 9, 1, 0, NULL, 1, '2025-05-07 10:35:26', '2025-06-03 18:27:39'),
(45, 30, 'เครื่องแกงคั่ว/แกงกะทิ ครึ่งโล', 'ขนาด 500 กรัม', 150.00, NULL, '681b381b1facc_image (8).png', 1, 1, 0, NULL, 1, '2025-05-07 10:38:19', '2025-05-10 12:58:07'),
(46, 30, 'เครื่องแกงส้มยายนิด  ครึ่งโล', 'ขนาด 500 กรัม', 150.00, NULL, '681b385cbc543_image (9).png', 0, 1, 0, NULL, NULL, '2025-05-07 10:39:24', '2025-05-07 10:39:24'),
(47, 30, 'น้ำตาลแว่น 150 กรัม ใช้ได้นาน', 'ขนาด 150 กรัม', 80.00, NULL, '681b38aabdc2f_image (10).png', 0, 1, 0, NULL, 1, '2025-05-07 10:40:42', '2025-05-10 12:45:21'),
(48, 30, '(1 แถม1 )น้ำพริกปลาลูกแบร่/น้ำพริกหนังไก่', 'น้ำพริกปลาลูกแบร่และน้ำพริกหนังไก่  รสชาติเข้มข้นเผ็ดแบบขาวใต้กินกับข้าวสวยๆร้อนๆ ฟิน\r\nน้ำหนัก 100 กรัม', 60.00, NULL, '681b390289bd6_image (11).png', 0, 1, 0, NULL, NULL, '2025-05-07 10:42:10', '2025-05-07 10:42:10'),
(49, 30, 'ไข่ปลาสูตรเด็ดอร่อยสุดๆ(ทะเลน้อยพัทลุงแท้)', 'ไข่ปลาทอดทะเลน้อยเจ้าดังอร่อยสุดๆ\r\n⭕️ อยู่นอกตู้เย็นได้นาน  7 วันนับจากวันจัดส่ง ทอด สดใหม่ แพ็คต่อแพ็ค \r\nทำจากไข่ปลาตะเพียนล้วนๆ ผสมกับเครื่องสมุนไพรสูตรเฉพาะ ที่ทำให้หอม ดับกลิ่นคาว และรสชาติกลมกล่อม ทำได้หลากหลายเมนู\r\n✅ พร้อมทานได้เลย   ✅ ทำจากไข่ปลาตะเพียนล้วน ไม่ผสมแป้ง????\r\n เก็บไว้นอกตู้เย็นได้ 7 วัน \r\nแช่ตู้เย็นไว้ได้ 15 - 20 วัน \r\nเอาทำยำ แกงส้ม  หรือกินแบบไม่ใส่อะไรเลยก็อร่อยหมดทุกแบบน้ะจ้ะ', 60.00, NULL, '681b399844ef4_image (12).png', 7, 1, 0, NULL, 3, '2025-05-07 10:43:10', '2025-06-05 09:46:28'),
(50, 30, 'แกงไตปลาแห้งรสเด็ดอร่อยถุง(500กรัม)', 'แกงไตปลาแห้งรสเด็ดอร่อยแพ็คถุงสูญญากาศ \r\nถุง 500 กรัม \r\nอร่อยเข้มข้น รสชาวใต้', 200.00, NULL, '681b3a100aa45_image (13).png', 0, 1, 0, NULL, NULL, '2025-05-07 10:46:40', '2025-05-07 10:46:40');

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
(6, '2025-03-29', 6, 4, 2, 35.00, 'bank_transfer', 'online', 0.00, 0.00, 0.00, 35.00, 'รายได้จากคำสั่งซื้อ #ORD-1743258380137', '2025-03-29 14:27:13', '2025-03-29 14:27:13'),
(7, '2025-05-07', 10, 42, 27, 320.00, 'cash', 'online', -160.00, 0.00, 0.00, 320.00, 'รายได้จากคำสั่งซื้อ #ORD-1746614898841', '2025-05-07 11:04:57', '2025-05-07 11:04:57'),
(8, '2025-05-10', 14, 45, 30, 150.00, 'bank_transfer', 'online', 0.00, 0.00, 0.00, 150.00, 'รายได้จากคำสั่งซื้อ #ORD-1746881887845', '2025-05-10 13:40:36', '2025-05-10 13:40:36');

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
(2, 'test', '$2y$10$qLGM8cRo7fUyQfYjS0RInu1Us5IEgVAfPSM2tq9yipq/3jvtYYahy', 'test@gmail.com', 'Hello', '0930000000', 'test', 'customer', 1, '2025-03-27 10:49:10', '2025-06-11 17:29:02'),
(3, 'tt', '$2y$10$lNErURvIADguF1MfjMtYeOR4J1b5nG0De60kY/MYkobyRbDztqtWG', 'testt@gmail.com', 'test', '0930000000', '11', 'customer', 1, '2025-03-29 11:32:17', '2025-03-29 11:51:35'),
(4, 'da_daw0503', '$2y$10$7waYhrcOCUXN0L/npGMEKuLakWzXSmLUcK.u7jNdz9lnW1vJVBV.e', 'nong.dawz2018@gmail.com', 'ฐานิยา นวลกริ่ม', '080-4285696', '172 หมู่7 ตำบลมะกอกเหนือ อำเภอควนขนุข จังหวัดพัทลุง93150', 'customer', 1, '2025-05-03 10:47:47', '2025-05-03 10:47:47'),
(5, '0202', '$2y$10$uXcsxfNsCKcFgUfwcX31Ye5QRDKxDZKJvGFOFO2jLo1xcfxrTOEZC', 'thaniya5346@gmail.com', 'ใจดี', '080-4285696', '111 ม.3 ต.ตำนาน อ.เมือง จ.พัทลุง93000', 'customer', 1, '2025-06-03 18:29:54', '2025-06-03 18:29:54'),
(6, 'แตงโม', '$2y$10$27b/Wa76ZZbTqgeiDPjQBOjR5Jh45WSMXf./r0D/5bEVaeIYQs.46', 'thaniyadawz123@gmail.com', 'ดาราพร เส้งรอด', '0800863568', '76 ม.7 ต.มะกอกเหนือ อ.ควนขนุน จ.พัทลุง 93150', 'customer', 1, '2025-06-05 09:09:50', '2025-06-05 09:09:50'),
(7, 'นะโม', '$2y$10$hWAglh9/zE6pMtUjw165bO02UtYjk/JXH95BNbfqeJcylSgYS7JPW', 'thanawan.n@rmutsvmail.com', 'ธนวรรณ หนูวัน', '0936138723', '46/2 ม.3 ต.ชัยบุรี อ.เมือง จ.พัทลุง 93000', 'customer', 1, '2025-06-05 09:56:17', '2025-06-05 09:56:17'),
(8, 'สมชาย', '$2y$10$74GaI.QvogccQkBgfE63X.npqlVAE/1n7aINo74n0HOtVVIzDSW.O', 'kaka2234q@gmail.com', 'นายสมชาย มีบุญ', '089-442-5582', '10 ม.3 ต.ท่าแค อ.เมือง จ.พัทลุง 93000', 'customer', 1, '2025-06-06 16:25:40', '2025-06-06 16:25:40');

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
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `revenue`
--
ALTER TABLE `revenue`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
