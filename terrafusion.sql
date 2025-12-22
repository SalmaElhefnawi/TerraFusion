-- TerraFusion Final Database Schema
-- Updated: 2025-12-19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Database: `terra_fusion`
CREATE DATABASE IF NOT EXISTS `terra_fusion` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `terra_fusion`;

-- --------------------------------------------------------

-- Table structure for table `users`
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL UNIQUE,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `role` enum('Manager','Chef Boss','Table Manager','Waiter') NOT NULL DEFAULT 'Waiter',
  `last_login` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

-- Table structure for table `meals` (formerly menu_items)
CREATE TABLE `meals` (
  `meal_id` int(11) NOT NULL AUTO_INCREMENT,
  `meal_name` varchar(100) NOT NULL,
  `category` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `availability` enum('Available','Out of Stock') NOT NULL DEFAULT 'Available',
  `quantity` int(5) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`meal_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

-- Table structure for table `reservations`
CREATE TABLE `reservations` (
  `reservation_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_name` varchar(100) NOT NULL,
  `contact_phone` varchar(20) NOT NULL,
  `party_size` int(3) NOT NULL,
  `reservation_date` date NOT NULL,
  `reservation_time` time NOT NULL,
  `status` enum('Confirmed','seated','Cancelled') NOT NULL DEFAULT 'Confirmed',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`reservation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

-- Table structure for table `orders`
CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_name` varchar(100) NOT NULL,
  `table_number` int(5) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('New','Preparing','Ready','Served','Paid') NOT NULL DEFAULT 'New',
  `order_date` datetime NOT NULL DEFAULT current_timestamp(),
  `served_by_fk` int(11) DEFAULT NULL,
  PRIMARY KEY (`order_id`),
  KEY `served_by_fk` (`served_by_fk`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`served_by_fk`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

-- Table structure for table `order_details`
CREATE TABLE `order_details` (
  `detail_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_fk` int(11) NOT NULL,
  `item_fk` int(11) NOT NULL,
  `quantity` int(5) NOT NULL,
  `price_at_sale` decimal(10,2) NOT NULL,
  PRIMARY KEY (`detail_id`),
  KEY `order_fk` (`order_fk`),
  KEY `item_fk` (`item_fk`),
  CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_fk`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`item_fk`) REFERENCES `meals` (`meal_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

-- Table structure for table `carts` (internal tracking)
CREATE TABLE `carts` (
  `cart_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`cart_id`),
  KEY `customer_id` (`customer_id`),
  CONSTRAINT `carts_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

-- Table structure for table `cart_items` (internal tracking)
CREATE TABLE `cart_items` (
  `cart_item_id` int(11) NOT NULL AUTO_INCREMENT,
  `cart_id` int(11) NOT NULL,
  `meal_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`cart_item_id`),
  KEY `cart_id` (`cart_id`),
  KEY `meal_id` (`meal_id`),
  CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`cart_id`) ON DELETE CASCADE,
  CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`meal_id`) REFERENCES `meals` (`meal_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

-- Table structure for table `payments`
CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `method` enum('cash','card','mobile') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `status` enum('pending','completed','failed','refunded') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`payment_id`),
  KEY `order_id` (`order_id`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

-- Sample Data

INSERT INTO `users` (`email`, `password_hash`, `full_name`, `phone`, `role`) VALUES
('admin@terrafusion.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin Mahmoud', '0123456789', 'Manager');

INSERT INTO `meals` (`meal_name`, `category`, `description`, `price`, `availability`, `quantity`, `image`) VALUES
('Bruschetta', 'Appetizers', 'Toasted bread with tomatoes, garlic and fresh basil', 80.00, 'Available', 50, 'images/bruschetta.jpg'),
('Margherita Pizza', 'Main Course', 'Classic pizza with tomato, mozzarella, and basil', 150.00, 'Available', 30, 'images/pizza.jpg'),
('Chocolate Lava Cake', 'Desserts', 'Warm chocolate cake with a molten center', 90.00, 'Available', 20, 'images/lava-cake.jpg');

COMMIT;
