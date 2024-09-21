-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 09, 2024 at 09:45 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.0.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `inventory`
--

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL,
  `prefix_id` int(11) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `customer_type_id` int(11) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `tax_id` varchar(20) DEFAULT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `bank_account_number` varchar(50) DEFAULT NULL,
  `credit_limit` decimal(10,2) DEFAULT NULL,
  `credit_terms` varchar(50) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customer_id`, `prefix_id`, `name`, `customer_type_id`, `address`, `phone_number`, `tax_id`, `contact_person`, `bank_account_number`, `credit_limit`, `credit_terms`, `user_id`, `updated_at`) VALUES
(5, 1, 'สมชาย ใจดี', 1, '123 ถ.สุขุมวิท กรุงเทพฯ 10110', '02-123-4567', '1234567890123', NULL, '123-4-56789-0', 50000.00, 'Net 30', 1, '2024-08-28 14:30:03'),
(6, 2, 'สมหญิง รักดี', 1, '456 ถ.พหลโยธิน กรุงเทพฯ 10400', '02-987-6543', '9876543210987', NULL, '987-6-54321-0', 30000.00, 'Net 15', 1, '2024-08-28 14:30:03'),
(7, 4, 'เอบีซี จำกัด', 2, '789 ถ.สีลม กรุงเทพฯ 10500', '02-111-2222', '1111111111111', 'คุณสมศักดิ์ ผู้จัดการ', '111-2-22222-2', 1000000.00, 'Net 60', 1, '2024-09-04 16:51:09'),
(8, 5, 'รุ่งเรืองกิจ', 2, '101 ถ.เพชรบุรีตัดใหม่ กรุงเทพฯ 10310', '02-333-4444', '3333333333333', 'คุณวิชัย หุ้นส่วนผู้จัดการ', '333-4-44444-4', 500000.00, 'Net 45', 1, '2024-09-04 16:51:15');

-- --------------------------------------------------------

--
-- Table structure for table `customer_types`
--

CREATE TABLE `customer_types` (
  `type_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `discount_rate` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer_types`
--

INSERT INTO `customer_types` (`type_id`, `name`, `discount_rate`) VALUES
(1, 'บุคคลทั่วไป', 0.00),
(2, 'นิติบุคคล', 5.00);

-- --------------------------------------------------------

--
-- Table structure for table `d_issue`
--

CREATE TABLE `d_issue` (
  `issue_detail_id` int(11) NOT NULL,
  `issue_header_id` int(11) NOT NULL,
  `product_id` varchar(20) DEFAULT NULL,
  `location_id` int(11) DEFAULT NULL,
  `quantity` decimal(10,2) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `d_issue`
--

INSERT INTO `d_issue` (`issue_detail_id`, `issue_header_id`, `product_id`, `location_id`, `quantity`, `user_id`) VALUES
(60, 42, 'A001', 1, 40.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `d_receive`
--

CREATE TABLE `d_receive` (
  `receive_detail_id` int(11) NOT NULL,
  `receive_header_id` int(11) NOT NULL,
  `product_id` varchar(20) DEFAULT NULL,
  `quantity` decimal(10,2) DEFAULT NULL,
  `location_id` int(11) DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `d_receive`
--

INSERT INTO `d_receive` (`receive_detail_id`, `receive_header_id`, `product_id`, `quantity`, `location_id`, `unit`, `user_id`) VALUES
(158, 64, 'A001', 120.00, 1, 'ชิ้น', 1);

-- --------------------------------------------------------

--
-- Table structure for table `d_transfer`
--

CREATE TABLE `d_transfer` (
  `transfer_detail_id` int(11) NOT NULL,
  `transfer_header_id` int(11) NOT NULL,
  `product_id` varchar(20) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `h_issue`
--

CREATE TABLE `h_issue` (
  `issue_header_id` int(11) NOT NULL,
  `bill_number` varchar(10) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `issue_type` enum('sale','project') DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `project_id` int(11) DEFAULT NULL,
  `receiver_name` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `h_issue`
--

INSERT INTO `h_issue` (`issue_header_id`, `bill_number`, `user_id`, `issue_type`, `issue_date`, `customer_id`, `project_id`, `receiver_name`, `updated_at`) VALUES
(42, 'D670001', 1, 'sale', '2024-09-06', 7, NULL, NULL, '2024-09-08 07:47:36');

-- --------------------------------------------------------

--
-- Table structure for table `h_receive`
--

CREATE TABLE `h_receive` (
  `receive_header_id` int(11) NOT NULL,
  `bill_number` varchar(10) DEFAULT NULL,
  `received_date` date DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `is_opening_balance` tinyint(1) NOT NULL DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `h_receive`
--

INSERT INTO `h_receive` (`receive_header_id`, `bill_number`, `received_date`, `user_id`, `is_opening_balance`, `updated_at`) VALUES
(64, 'R670001', '2024-09-05', 1, 0, '2024-09-08 07:36:26');

-- --------------------------------------------------------

--
-- Table structure for table `h_transfer`
--

CREATE TABLE `h_transfer` (
  `transfer_header_id` int(11) NOT NULL,
  `bill_number` varchar(10) DEFAULT NULL,
  `transfer_date` date NOT NULL,
  `from_location_id` int(11) NOT NULL,
  `to_location_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `inventory_id` int(11) NOT NULL,
  `product_id` varchar(20) DEFAULT NULL,
  `location_id` int(11) DEFAULT NULL,
  `quantity` decimal(10,2) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`inventory_id`, `product_id`, `location_id`, `quantity`, `updated_at`, `user_id`) VALUES
(342, 'A001', 1, 80.00, '2024-09-08 07:49:50', 1),
(343, 'A002', 2, 0.00, '2024-09-08 07:38:35', 1),
(346, 'A001', 2, 0.00, '2024-09-08 07:49:50', 1);

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
  `location_id` int(11) NOT NULL,
  `location` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` (`location_id`, `location`) VALUES
(1, 'คลังสินค้าหลัก'),
(2, 'คลังสินค้ารอง');

-- --------------------------------------------------------

--
-- Table structure for table `prefixes`
--

CREATE TABLE `prefixes` (
  `prefix_id` int(11) NOT NULL,
  `prefix` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prefixes`
--

INSERT INTO `prefixes` (`prefix_id`, `prefix`) VALUES
(1, 'นาย'),
(2, 'นาง'),
(3, 'นางสาว'),
(4, 'บริษัท'),
(5, 'ห้างหุ้นส่วนจำกัด');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` varchar(50) NOT NULL,
  `name_th` varchar(255) DEFAULT NULL,
  `name_en` varchar(255) DEFAULT NULL,
  `size` varchar(50) DEFAULT NULL,
  `product_type_id` int(11) DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `img` varchar(255) DEFAULT 'product.png',
  `low_level` int(5) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `name_th`, `name_en`, `size`, `product_type_id`, `unit`, `user_id`, `updated_at`, `img`, `low_level`) VALUES
('A001', 'เทส', 'Test', '5*5', 1, 'ชิ้น', 1, '2024-08-24 08:33:00', 'product.png', 6),
('A002', 'เทส2', 'Test2', '7*7', 2, 'อัน', 1, '2024-08-25 08:03:02', 'product.png', 10);

-- --------------------------------------------------------

--
-- Table structure for table `product_cate`
--

CREATE TABLE `product_cate` (
  `category_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `product_category_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_cate`
--

INSERT INTO `product_cate` (`category_id`, `name`, `product_category_id`) VALUES
(1, 'โทรศัพท์มือถือ', 1),
(2, 'คอมพิวเตอร์พกพา', 1),
(3, 'เก้าอี้', 2),
(4, 'โต๊ะ', 2),
(5, 'เสื้อผ้าผู้ชาย', 3),
(6, 'เสื้อผ้าผู้หญิง', 3);

-- --------------------------------------------------------

--
-- Table structure for table `product_types`
--

CREATE TABLE `product_types` (
  `type_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_types`
--

INSERT INTO `product_types` (`type_id`, `name`) VALUES
(1, 'อิเล็กทรอนิกส์'),
(2, 'เฟอร์นิเจอร์'),
(3, 'เสื้อผ้า');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `project_id` int(11) NOT NULL,
  `project_name` varchar(100) DEFAULT NULL,
  `project_description` text DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`project_id`, `project_name`, `project_description`, `start_date`, `end_date`, `user_id`, `updated_at`) VALUES
(1, 'โครงการพัฒนาระบบ ERP', 'พัฒนาและติดตั้งระบบ ERP สำหรับบริษัท เอบีซี จำกัด', '2024-09-01', '2025-03-31', 1, '2024-08-28 14:31:04'),
(2, 'โครงการปรับปรุงเว็บไซต์', 'ปรับปรุงเว็บไซต์ของบริษัทให้รองรับการใช้งานบนมือถือ', '2024-10-01', '2024-12-31', 1, '2024-08-28 14:31:04'),
(3, 'โครงการฝึกอบรม IT', 'จัดฝึกอบรมด้าน IT ให้กับพนักงานทั้งองค์กร', '2024-11-01', '2024-11-30', 1, '2024-08-28 14:31:04'),
(4, 'โครงการพัฒนาแอพมือถือ', 'พัฒนาแอพพลิเคชันมือถือสำหรับลูกค้า', '2025-01-01', '2025-06-30', 1, '2024-08-28 14:31:04');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `RoleID` int(11) NOT NULL,
  `RoleName` varchar(100) DEFAULT NULL,
  `permissions` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`RoleID`, `RoleName`, `permissions`) VALUES
(1, 'แอดมิน', NULL),
(2, 'ผู้ใช้ทั่วไป', NULL),
(3, 'ผู้ดูแลระบบ', NULL),
(4, 'ผู้จัดการ', NULL),
(5, 'พนักงาน', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `UserID` int(11) NOT NULL,
  `Username` varchar(150) DEFAULT NULL,
  `Password` varchar(255) DEFAULT NULL,
  `fname` varchar(50) DEFAULT NULL,
  `lname` varchar(50) DEFAULT NULL,
  `RoleID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`UserID`, `Username`, `Password`, `fname`, `lname`, `RoleID`) VALUES
(1, 'admin', '$2y$10$cspRJ0IyPeC3OVSzsqj/x.FAh0BwUTc78NBcNbnfYs2HMnsS3LViK', 'james', 'adithep', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`),
  ADD KEY `prefix_id` (`prefix_id`),
  ADD KEY `customer_type_id` (`customer_type_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `customer_types`
--
ALTER TABLE `customer_types`
  ADD PRIMARY KEY (`type_id`);

--
-- Indexes for table `d_issue`
--
ALTER TABLE `d_issue`
  ADD PRIMARY KEY (`issue_detail_id`),
  ADD KEY `issue_header_id` (`issue_header_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `fk_d_issue_user` (`user_id`),
  ADD KEY `idx_d_issue_location` (`location_id`);

--
-- Indexes for table `d_receive`
--
ALTER TABLE `d_receive`
  ADD PRIMARY KEY (`receive_detail_id`),
  ADD KEY `receipt_header_id` (`receive_header_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `fk_d_receipt_user` (`user_id`),
  ADD KEY `fk_d_receive_location` (`location_id`);

--
-- Indexes for table `d_transfer`
--
ALTER TABLE `d_transfer`
  ADD PRIMARY KEY (`transfer_detail_id`),
  ADD KEY `transfer_header_id` (`transfer_header_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `fk_d_transfer_user` (`user_id`);

--
-- Indexes for table `h_issue`
--
ALTER TABLE `h_issue`
  ADD PRIMARY KEY (`issue_header_id`),
  ADD UNIQUE KEY `bill_number` (`bill_number`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `h_receive`
--
ALTER TABLE `h_receive`
  ADD PRIMARY KEY (`receive_header_id`),
  ADD UNIQUE KEY `bill_number` (`bill_number`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `h_transfer`
--
ALTER TABLE `h_transfer`
  ADD PRIMARY KEY (`transfer_header_id`),
  ADD UNIQUE KEY `bill_number` (`bill_number`),
  ADD KEY `from_location_id` (`from_location_id`),
  ADD KEY `to_location_id` (`to_location_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`inventory_id`),
  ADD UNIQUE KEY `unique_product_location` (`product_id`,`location_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `location_id` (`location_id`),
  ADD KEY `fk_inventory_user` (`user_id`);

--
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`location_id`);

--
-- Indexes for table `prefixes`
--
ALTER TABLE `prefixes`
  ADD PRIMARY KEY (`prefix_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `product_type_id` (`product_type_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `product_cate`
--
ALTER TABLE `product_cate`
  ADD PRIMARY KEY (`category_id`),
  ADD KEY `product_category_id` (`product_category_id`);

--
-- Indexes for table `product_types`
--
ALTER TABLE `product_types`
  ADD PRIMARY KEY (`type_id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`project_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`RoleID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`UserID`),
  ADD KEY `RoleID` (`RoleID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `customer_types`
--
ALTER TABLE `customer_types`
  MODIFY `type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `d_issue`
--
ALTER TABLE `d_issue`
  MODIFY `issue_detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `d_receive`
--
ALTER TABLE `d_receive`
  MODIFY `receive_detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=160;

--
-- AUTO_INCREMENT for table `d_transfer`
--
ALTER TABLE `d_transfer`
  MODIFY `transfer_detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `h_issue`
--
ALTER TABLE `h_issue`
  MODIFY `issue_header_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `h_receive`
--
ALTER TABLE `h_receive`
  MODIFY `receive_header_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `h_transfer`
--
ALTER TABLE `h_transfer`
  MODIFY `transfer_header_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `inventory_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=347;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `location_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `prefixes`
--
ALTER TABLE `prefixes`
  MODIFY `prefix_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `product_cate`
--
ALTER TABLE `product_cate`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `product_types`
--
ALTER TABLE `product_types`
  MODIFY `type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `project_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `RoleID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`prefix_id`) REFERENCES `prefixes` (`prefix_id`),
  ADD CONSTRAINT `customers_ibfk_2` FOREIGN KEY (`customer_type_id`) REFERENCES `customer_types` (`type_id`),
  ADD CONSTRAINT `customers_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`UserID`);

--
-- Constraints for table `d_issue`
--
ALTER TABLE `d_issue`
  ADD CONSTRAINT `d_issue_ibfk_1` FOREIGN KEY (`issue_header_id`) REFERENCES `h_issue` (`issue_header_id`),
  ADD CONSTRAINT `d_issue_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`),
  ADD CONSTRAINT `fk_d_issue_location` FOREIGN KEY (`location_id`) REFERENCES `locations` (`location_id`),
  ADD CONSTRAINT `fk_d_issue_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`UserID`);

--
-- Constraints for table `d_receive`
--
ALTER TABLE `d_receive`
  ADD CONSTRAINT `d_receive_ibfk_1` FOREIGN KEY (`receive_header_id`) REFERENCES `h_receive` (`receive_header_id`),
  ADD CONSTRAINT `d_receive_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`),
  ADD CONSTRAINT `fk_d_receipt_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`UserID`),
  ADD CONSTRAINT `fk_d_receive_location` FOREIGN KEY (`location_id`) REFERENCES `locations` (`location_id`);

--
-- Constraints for table `d_transfer`
--
ALTER TABLE `d_transfer`
  ADD CONSTRAINT `d_transfer_ibfk_1` FOREIGN KEY (`transfer_header_id`) REFERENCES `h_transfer` (`transfer_header_id`),
  ADD CONSTRAINT `d_transfer_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`),
  ADD CONSTRAINT `fk_d_transfer_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`UserID`);

--
-- Constraints for table `h_issue`
--
ALTER TABLE `h_issue`
  ADD CONSTRAINT `h_issue_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`UserID`),
  ADD CONSTRAINT `h_issue_ibfk_3` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`),
  ADD CONSTRAINT `h_issue_ibfk_4` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`);

--
-- Constraints for table `h_receive`
--
ALTER TABLE `h_receive`
  ADD CONSTRAINT `h_receive_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`UserID`);

--
-- Constraints for table `h_transfer`
--
ALTER TABLE `h_transfer`
  ADD CONSTRAINT `h_transfer_ibfk_1` FOREIGN KEY (`from_location_id`) REFERENCES `locations` (`location_id`),
  ADD CONSTRAINT `h_transfer_ibfk_2` FOREIGN KEY (`to_location_id`) REFERENCES `locations` (`location_id`),
  ADD CONSTRAINT `h_transfer_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`UserID`);

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `fk_inventory_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`UserID`),
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`),
  ADD CONSTRAINT `inventory_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `locations` (`location_id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`product_type_id`) REFERENCES `product_cate` (`category_id`),
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`UserID`);

--
-- Constraints for table `product_cate`
--
ALTER TABLE `product_cate`
  ADD CONSTRAINT `product_cate_ibfk_1` FOREIGN KEY (`product_category_id`) REFERENCES `product_types` (`type_id`);

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`UserID`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`RoleID`) REFERENCES `roles` (`RoleID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
