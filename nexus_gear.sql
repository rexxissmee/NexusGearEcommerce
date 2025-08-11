-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 11, 2025 at 11:09 AM
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
-- Database: `nexus_gear`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `status`, `created_at`) VALUES
(1, 'PC Handheld', 'Portable gaming PCs and handheld gaming devices for gaming on the go', 'active', '2025-08-07'),
(2, 'Controller', 'Wireless controllers, racing wheels, and arcade fight sticks', 'active', '2025-08-07'),
(3, 'Gaming Mouse', 'High-precision gaming mouse with customizable DPI and RGB lighting', 'active', '2025-08-07'),
(4, 'Accessories', 'Gaming mousepads, stands, LED strips, and other gaming essentials', 'active', '2025-08-07'),
(5, 'Power bank', 'Extend gaming time with high capacity power bank', 'inactive', '2025-08-09');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_code` varchar(255) NOT NULL,
  `note` text DEFAULT NULL,
  `status` varchar(100) DEFAULT 'pending',
  `subtotal` decimal(10,2) DEFAULT NULL,
  `tax_fee` decimal(10,2) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_details`
--

CREATE TABLE `order_details` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `original_price` decimal(10,2) DEFAULT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `category_id` int(11) DEFAULT NULL,
  `average_rating` decimal(3,2) DEFAULT 0.00,
  `review_count` int(11) DEFAULT 0,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_on_sale` tinyint(1) DEFAULT 0,
  `is_new_arrival` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `original_price`, `thumbnail`, `stock`, `category_id`, `average_rating`, `review_count`, `is_featured`, `is_on_sale`, `is_new_arrival`, `created_at`, `updated_at`) VALUES
(1, 'Valve Steam Deck (512GB)', 'Portable PC gaming console with 512GB SSD, 7-inch touchscreen, and access to the full Steam game library.', 649.00, 649.00, 'https://images.tcdn.com.br/img/img_prod/616573/console_steam_deck_16gb_ram_1067_2_5e9f703c4579f57b6ce610ee45f5f57d.jpg', 15, 1, 4.50, 150, 1, 0, 0, '2025-08-09 04:44:38', '2025-08-09 05:33:30'),
(2, 'ASUS ROG Ally (Z1 Extreme)', 'High-end handheld gaming PC featuring an AMD Ryzen Z1 Extreme processor, 120Hz 7-inch display, and Windows 11 for AAA gaming on the go.', 699.99, 799.99, 'https://tse1.mm.bing.net/th/id/OIP.IyUHxFkHEE-VH0HjKB86yAHaHa?r=0&rs=1&pid=ImgDetMain&o=7&rm=3', 10, 1, 4.30, 85, 0, 1, 1, '2025-08-09 04:44:38', '2025-08-09 05:32:19'),
(3, 'AYANEO 2 Handheld Gaming PC', 'Powerful handheld gaming PC with AMD Ryzen 7 processor, 16GB RAM, and a 7-inch 1920x1200 display for high-end portable gaming.', 1099.00, 1099.00, 'https://www.bigw.com.au/medias/sys_master/images/images/hf0/h3c/34752367722526.jpg', 5, 1, 4.00, 32, 0, 0, 0, '2025-08-09 04:44:38', '2025-08-09 07:10:12'),
(4, 'Nintendo Switch OLED Model', 'Nintendo’s hybrid console with a 7-inch vibrant OLED display, enhanced audio, and 64GB internal storage for both portable and docked play.', 349.99, 349.99, 'https://shop.urbanrepublic.com.my/cdn/shop/files/045496883386_1.jpg?v=1737358469&width=1426', 50, 1, 4.80, 200, 1, 0, 0, '2025-08-09 04:44:38', '2025-08-09 05:31:15'),
(5, 'Nintendo Switch Lite', 'Compact handheld-only version of the Nintendo Switch featuring integrated controls and a 5.5-inch screen, ideal for gaming on the go.', 199.99, 199.99, 'https://tse3.mm.bing.net/th/id/OIP.UAp7HAPQu5GQwXk4cCwWNwHaHa?r=0&rs=1&pid=ImgDetMain&o=7&rm=3', 40, 1, 4.70, 180, 0, 0, 0, '2025-08-09 04:44:38', '2025-08-09 05:29:35'),
(6, 'Logitech G Cloud Gaming Handheld', 'Cloud gaming handheld with 7-inch Full HD touchscreen, precision controls, and up to 12-hour battery life for streaming Xbox, Steam Link, and more.', 299.99, 349.99, 'https://media.karousell.com/media/photos/products/2023/9/14/ayn_odin_lite_1694698217_4ac3bd3f.jpg', 20, 1, 4.20, 60, 0, 1, 0, '2025-08-09 04:44:38', '2025-08-09 05:28:33'),
(8, 'Xbox Series X|S Wireless Controller', 'Official Xbox Wireless Controller (Carbon Black) featuring textured grips, hybrid D-pad, and Bluetooth connectivity for Xbox Series X|S, Xbox One, and PC.', 59.99, 59.99, 'https://tse1.mm.bing.net/th/id/OIP.EZS9i6-t1NlCxXz3NRibBQHaH7?r=0&w=800&h=857&rs=1&pid=ImgDetMain&o=7&rm=3', 100, 2, 4.60, 150, 0, 0, 0, '2025-08-09 04:44:38', '2025-08-09 05:27:55'),
(9, 'Sony DualSense Wireless Controller (PS5)', 'PlayStation 5 DualSense controller with adaptive triggers, haptic feedback, built-in microphone, and signature two-tone design for immersive gaming.', 69.99, 69.99, 'https://cdn.grupoelcorteingles.es/SGFM/dctm/MEDIA03/202311/16/00194481001025____5__1200x1200.jpg', 80, 2, 4.70, 160, 0, 0, 0, '2025-08-09 04:44:38', '2025-08-09 05:27:33'),
(10, 'Nintendo Switch Pro Controller', 'Ergonomic wireless controller for the Nintendo Switch featuring motion controls, HD rumble, and amiibo NFC support, offering a traditional gamepad feel.', 69.99, 79.99, 'https://phonesstorekenya.com/wp-content/uploads/2023/12/Nintendo-Switch-Pro-Controller1.jpg', 60, 2, 4.80, 90, 0, 1, 0, '2025-08-09 04:44:38', '2025-08-09 05:26:39'),
(11, 'Logitech G29 Driving Force Racing Wheel', 'Force feedback racing wheel and pedals set with dual-motor feedback, 900° rotation, and leather-wrapped wheel, compatible with PS5, PS4, and PC for realistic racing.', 299.99, 349.99, 'https://coolboxpe.vtexassets.com/arquivos/ids/274826-800-auto?v=638210770389200000&width=800&height=auto&aspect=true', 15, 2, 4.50, 50, 1, 1, 0, '2025-08-09 04:44:38', '2025-08-09 05:25:56'),
(15, 'Razer DeathAdder V2 Gaming Mouse', 'Ergonomic wired gaming mouse with a 20,000 DPI optical sensor, Razer Chroma RGB lighting, and 8 programmable buttons for precision and comfort.', 69.99, 79.99, 'https://www.jbhifi.com.au/cdn/shop/products/520525-Product-1-I-637581546089131828_1024x1024.jpg', 45, 3, 4.70, 150, 0, 1, 1, '2025-08-09 04:44:38', '2025-08-09 07:11:45'),
(16, 'SteelSeries Rival 600 Gaming Mouse', 'Dual sensor RGB gaming mouse offering true 1-to-1 tracking (up to 12,000 CPI) and customizable weight system for balanced, precise gameplay.', 79.99, 79.99, 'https://www.gamernecessary.com/wp-content/uploads/2020/07/SteelSeries-Rival-600-Appearance.jpg', 60, 3, 4.50, 110, 0, 0, 0, '2025-08-09 04:44:38', '2025-08-09 05:21:57'),
(17, 'Corsair Ironclaw RGB Gaming Mouse', 'Ergonomic FPS/MOBA gaming mouse with a 18,000 DPI optical sensor, dynamic RGB lighting, and seven programmable buttons for big-handed gamers.', 59.99, 69.99, 'https://tse4.mm.bing.net/th/id/OIP.sk-kdfU6k-iwvZILiXokJAHaE7?r=0&rs=1&pid=ImgDetMain&o=7&rm=3', 25, 3, 4.40, 70, 0, 1, 0, '2025-08-09 04:44:38', '2025-08-09 05:21:23'),
(18, 'Glorious Model O RGB Gaming Mouse', 'Ultra-lightweight honeycomb shell gaming mouse (67g) with a 12,000 DPI optical sensor, RGB lighting, and flexible Ascended Cord for competitive play.', 49.99, 59.99, 'https://c1.neweggimages.com/ProductImageCompressAll1280/AXU8D2107090OAM1.jpg', 30, 3, 4.60, 80, 0, 1, 0, '2025-08-09 04:44:38', '2025-08-09 05:20:21'),
(19, 'HyperX Pulsefire Surge Gaming Mouse', 'RGB gaming mouse with a 16,000 DPI Pixart sensor, 360° light ring, and Omron switches, delivering accuracy and style for any gaming setup.', 39.99, 39.99, 'https://uk.hyperx.com/cdn/shop/products/hyperx_pulsefire_surge_2_angled_back.jpg?v=1662988388', 50, 3, 4.30, 60, 0, 0, 0, '2025-08-09 04:44:38', '2025-08-09 05:19:35'),
(20, 'SteelSeries QcK Prism XL RGB Mouse Pad', 'Extra-large gaming mousepad with dual-zone RGB illumination and a micro-woven cloth surface for smooth, consistent mouse glide and style.', 59.99, 59.99, 'https://files.pccasegear.com/images/1613359085-SS-63511-thb.jpg', 70, 4, 4.80, 50, 0, 0, 0, '2025-08-09 04:44:38', '2025-08-09 05:47:04'),
(21, 'Corsair ST100 RGB Headset Stand', 'Premium headset stand with built-in 2-port USB 3.1 hub and RGB lighting powered by Corsair iCUE, providing stylish storage and connectivity for your headphones.', 69.99, 79.99, 'https://e.allegroimg.com/s1024/0c11d2/35a3d28a41bc8ed8d54b9d6f48be', 25, 4, 4.50, 30, 1, 1, 0, '2025-08-09 04:44:38', '2025-08-09 06:02:44'),
(22, 'Govee RGB LED Light Strip (5m)', '5-meter smart RGB LED light strip kit with adhesive backing and millions of color options, perfect for enhancing gaming setups or room ambience.', 29.99, 29.99, 'https://www.proshop.dk/Images/915x900/3129559_fb4cda94db98.jpg', 100, 4, 4.40, 90, 0, 0, 0, '2025-08-09 04:44:38', '2025-08-09 05:16:00'),
(23, 'PlayStation 5 DualSense Charging Station', 'Official charging dock for up to two DualSense controllers, providing quick and convenient charging without needing to connect to the PS5 console.', 29.99, 29.99, 'https://i0.wp.com/nextlevelgamingstore.com/wp-content/uploads/2020/11/Cargador-de-carga-r-pida-para-PS5-estaci-n-de-acoplamiento-de-carga-r-pida-con.jpg?w=1000&ssl=1', 40, 4, 4.90, 120, 1, 0, 1, '2025-08-09 04:44:38', '2025-08-09 07:14:24'),
(24, 'Gunnar Intercept Blue Light Glasses', 'Gaming eyewear with amber-tinted lenses that filter blue light and reduce eye strain during extended gaming or computer use.', 49.99, 59.99, 'https://cdn-s3.touchofmodern.com/products/002/720/197/1e086a6e8992e6832a870b7e078bed1e_large.jpg?1701979096', 10, 4, 4.20, 15, 1, 1, 1, '2025-08-09 04:44:38', '2025-08-09 06:02:28'),
(25, 'Razer Mouse Bungee v2', 'Cable management accessory that holds your wired mouse cable in place with a spring arm to eliminate cable drag and improve swipes during gameplay.', 19.99, 19.99, 'https://static0.makeuseofimages.com/wordpress/wp-content/uploads/2022/11/razer-mouse-bungee-v2.jpg', 35, 4, 4.30, 22, 0, 0, 0, '2025-08-09 04:44:38', '2025-08-09 05:13:21');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `product_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'user',
  `address_street` varchar(255) DEFAULT NULL,
  `address_ward` varchar(255) DEFAULT NULL,
  `address_city` varchar(255) DEFAULT NULL,
  `address_country` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `phone`, `date_of_birth`, `gender`, `password`, `role`, `address_street`, `address_ward`, `address_city`, `address_country`, `created_at`, `updated_at`) VALUES
(1, 'Administrator', NULL, 'admin@nexusgear.com', '0987654321', NULL, NULL, '$2y$10$vZVw5sNK3YqPj7QZid6Ziu7hvXKakQwS3w58sNN7dI63TCAbVPj3.', 'admin', NULL, NULL, NULL, NULL, '2025-08-04 06:17:49', '2025-08-09 12:26:39'),
(2, 'Hung', 'Quach', 'rexxissmee@gmail.com', '0972314822', '2005-02-04', 'Male', '$2y$10$ntMl81TFvZHPyjmuACsKKOrgGDYPiYHdnHDmiE7kEzyW2Fvz/XqEu', 'user', '137 Nguyen Truyen Thanh', 'Binh Thuy', 'Can Tho', 'Viet Nam', '2025-08-04 06:19:35', '2025-08-05 22:11:30'),
(3, 'Khang', 'Le', 'khanglee2k5@gmail.com', '0914496322', '2004-04-20', 'Male', '$2y$10$5/ERUkHqVwS9Q8s9Jm8/WuzHw.XuEP9wE31SAnK5jWVA0qatSICzu', 'user', '', '', '', '', '2025-08-07 06:17:54', '2025-08-08 03:19:47'),
(6, 'Khang', 'Duong Van', 'duongvankhang2021@gmail.com', '0867046251', '2005-12-06', '', '$2y$10$M0jrj73Kk3a7b5PEfhZSmu3E4/ioYNqgSJK/v/TuUJDejiJkzw8gy', 'user', '', '', 'Can Tho', 'Vietnam', '2025-08-09 07:49:11', '2025-08-11 09:08:57');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_code` (`order_code`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_details`
--
ALTER TABLE `order_details`
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
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_details`
--
ALTER TABLE `order_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
