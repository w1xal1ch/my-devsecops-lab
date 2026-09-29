CREATE DATABASE IF NOT EXISTS acs;
USE acs;
-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jun 10, 2026 at 05:09 PM
-- Server version: 12.3.2-MariaDB
-- PHP Version: 8.5.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `acs`
--

-- --------------------------------------------------------

--
-- Table structure for table `client`
--

CREATE TABLE `client` (
  `client_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `client`
--

INSERT INTO `client` (`client_id`, `full_name`, `phone`, `email`) VALUES
(1, 'Бекк Александр Юрьевич', '+79199095488', 'modgod265@gmail.com'),
(2, 'Бехтерев Сама', '+79199095481', '223@gmail.com'),
(3, 'Бекк Александр Юрьевич', '+79199095488', 'modgdo265@gmail.com'),
(4, 'ыфы', 'ыфы', 'test123@yandex.ru'),
(5, '1', '1', '1@mail.ru'),
(6, 'wqeqwe', 'asdsadfsdgsdfg', 'qweqwe@hhh.ru'),
(7, 'ыфы', '+79125550909', '123434534354@gmail.com'),
(8, 'Дресвянников Павел', '+79991112233', 'drev.pavel@gmail.com'),
(9, 'алехандро', '+7 (912) 452-08-00', 'tesaad2@dasda.ru'),
(10, 'Анатолий', '+71234567890', 'tolik@tester.ru'),
(12, 'Тестеров Анатолий', '+79567890133', 'tolik-_-tester@testerov.ru'),
(13, 'Алехандр', '+79124445566', 'alehandro@mail.ru'),
(14, '2', '2', '2@2.ru'),
(15, '2', '3', '3@3.ru'),
(16, 'pentester', '+79098765432', 'pentester@mail.ru');

-- --------------------------------------------------------

--
-- Table structure for table `manufacturer`
--

CREATE TABLE `manufacturer` (
  `manufacturer_id` int(11) NOT NULL,
  `country` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `manufacturer`
--

INSERT INTO `manufacturer` (`manufacturer_id`, `country`, `name`) VALUES
(1, 'Италия', 'Gucci'),
(2, 'США', 'Apple'),
(3, 'Япония', 'Sony'),
(4, 'Франция', 'Chanel'),
(5, 'Германия', 'Adidas');

-- --------------------------------------------------------

--
-- Table structure for table `order`
--

CREATE TABLE `order` (
  `order_id` int(11) NOT NULL,
  `client_name` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `comment` text DEFAULT NULL,
  `total` decimal(10,2) DEFAULT 0.00,
  `order_date` datetime DEFAULT current_timestamp(),
  `status` varchar(50) NOT NULL DEFAULT 'Новый'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order`
--

INSERT INTO `order` (`order_id`, `client_name`, `phone`, `email`, `address`, `comment`, `total`, `order_date`, `status`) VALUES
(1, 'Тестеров Анатолий', '+79567890133', 'tolik-_-tester@testerov.ru', 'ижевск сабурова 9 кв 14', '', 3000.00, '2026-05-18 14:43:59', 'Новый'),
(2, 'Тестеров Анатолий', '+79567890133', 'tolik-_-tester@testerov.ru', 'ижевск сабурова 9 кв 14', '', 15000.00, '2026-05-18 14:44:15', 'Новый'),
(3, 'Тестеров Анатолий', '+79567890133', 'tolik-_-tester@testerov.ru', 'ижевск сабурова 9 кв 14', '', 0.00, '2026-05-18 14:49:53', 'Новый'),
(4, 'Тестеров Анатолий', '+79567890133', 'tolik-_-tester@testerov.ru', 'ижевск сабурова 9 кв 14', '', 0.00, '2026-05-18 14:51:01', 'Новый'),
(5, 'Тестеров Анатолий', '+79567890133', 'tolik-_-tester@testerov.ru', 'ижевск сабурова 9 кв 14', '', 0.00, '2026-05-18 18:56:16', 'Новый'),
(6, 'Тестеров Анатолий', '+79567890133', 'tolik-_-tester@testerov.ru', 'ижевск сабурова 9 кв 14', '', 0.00, '2026-05-19 17:28:52', 'Новый'),
(7, 'Тестеров Анатолий', '+79567890133', 'tolik-_-tester@testerov.ru', 'ижевск сабурова 9 кв 14', '', 0.00, '2026-05-19 19:15:35', 'Новый'),
(8, 'Тестеров Анатолий', '+79567890133', 'tolik-_-tester@testerov.ru', 'ижевск сабурова 9 кв 14', '', 0.00, '2026-05-19 20:33:08', 'Новый'),
(9, 'Тестеров Анатолий', '+79567890133', 'tolik-_-tester@testerov.ru', 'ижевск сабурова 9 кв 14', '', 0.00, '2026-05-27 20:53:12', 'Новый'),
(10, 'Тестеров Анатолий', '+79567890133', 'tolik-_-tester@testerov.ru', 'Ижевск улица Берша дом 42 подъезд 7', 'sad', 0.00, '2026-06-10 13:32:54', 'Новый');

-- --------------------------------------------------------

--
-- Table structure for table `orderitem`
--

CREATE TABLE `orderitem` (
  `item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orderitem`
--

INSERT INTO `orderitem` (`item_id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 5, 1, 25000.00),
(2, 1, 8, 1, 18000.00),
(3, 1, 15, 1, 3500.00),
(5, 2, 1, 1, 15000.00),
(6, 3, 1, 1, 15000.00),
(7, 3, 2, 1, 45000.00),
(8, 4, 2, 1, 45000.00),
(9, 5, 2, 1, 45000.00),
(28, 1, 10, 1, 3000.00),
(29, 2, 1, 1, 15000.00),
(30, 3, 1, 1, 15000.00),
(31, 4, 9, 1, 7000.00),
(32, 5, 5, 1, 25000.00),
(33, 5, 15, 1, 3500.00),
(34, 6, 2, 1, 45000.00),
(35, 7, 5, 1, 25000.00),
(36, 8, 5, 1, 25000.00),
(37, 8, 15, 1, 3500.00),
(38, 9, 1, 1, 15000.00),
(39, 10, 9, 1, 7000.00);

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `product_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `size` varchar(50) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `manufacturer_id` int(11) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `ID_Type` int(11) DEFAULT NULL,
  `is_deleted` tinyint(4) DEFAULT 0,
  `stock` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`product_id`, `name`, `size`, `color`, `manufacturer_id`, `photo`, `price`, `type`, `description`, `ID_Type`, `is_deleted`, `stock`) VALUES
(1, 'Кожаный ремень', 'M', 'Черный', 1, 'product1.jpg', 15000.00, 'Ремень', NULL, 1, 0, 9),
(2, 'Часы Smartwatch', 'One Size', 'Серебристый', 2, 'product2.jpg', 45000.00, 'Часы', NULL, 2, 0, 7),
(3, 'Наушники Bluetooth', 'One Size', 'Черный', 3, 'product3.jpg', 12000.00, 'Аудио', NULL, 2, 0, 15),
(4, 'Сумка женская', 'L', 'Красный', 4, 'product4.jpg', 30000.00, 'Сумка', NULL, 3, 0, 7),
(5, 'Кроссовки беговые', '42', 'Белый', 5, 'product5.jpg', 25000.00, 'Обувь', NULL, 4, 0, 7),
(6, 'Очки солнцезащитные', 'One Size', 'Темно-коричневый', 1, 'product6.jpg', 8000.00, 'Очки', NULL, 1, 0, 13),
(7, 'Портативная колонка', 'One Size', 'Черный', 3, 'product7.jpg', 9000.00, 'Аудио', NULL, 2, 0, 9),
(8, 'Рюкзак городской', 'M', 'Синий', 5, 'product8.jpg', 18000.00, 'Сумка', NULL, 3, 0, 11),
(9, 'Кошелек мужской', 'One Size', 'Коричневый', 1, 'product9.jpg', 7000.00, 'Кошелек', NULL, 1, 0, 12),
(10, 'Флешка USB', 'One Size', 'Серебристый', 2, 'product10.jpg', 3000.00, 'Гаджет', NULL, 2, 0, 19),
(11, 'Поясная сумка', 'One Size', 'Черный', 4, 'product11.jpg', 12000.00, 'Сумка', NULL, 3, 0, 7),
(12, 'Браслет из кожи', 'One Size', 'Черный', 1, 'product12.jpg', 6000.00, 'Аксессуар', NULL, 1, 0, 10),
(13, 'Чехол для телефона', 'One Size', 'Красный', 2, 'product13.jpg', 4000.00, 'Гаджет', '', 2, 0, 25),
(15, 'Спортивная кепка', 'One Size', 'Темно-синий', 5, 'product15.jpg', 3500.00, 'Головные уборы', '<script>alert(1)</script>\r\n<b>zhirniy</b>', 5, 0, 7);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `product_id`, `client_id`, `rating`, `comment`, `created_at`) VALUES
(1, 1, 12, 5, 'удобно', '2026-05-14 12:31:09'),
(2, 8, 12, 5, 'очень удобный рюкзак для повседневных задач. вместительный', '2026-05-19 14:37:55');

-- --------------------------------------------------------

--
-- Table structure for table `souvenir_type`
--

CREATE TABLE `souvenir_type` (
  `ID_Type` int(11) NOT NULL,
  `Name` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `souvenir_type`
--

INSERT INTO `souvenir_type` (`ID_Type`, `Name`) VALUES
(1, 'Аксессуары'),
(2, 'Электроника'),
(3, 'Сумки и рюкзаки'),
(4, 'Обувь'),
(5, 'Головные уборы');

-- --------------------------------------------------------

--
-- Table structure for table `supplies`
--

CREATE TABLE `supplies` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `supplies`
--

INSERT INTO `supplies` (`id`, `product_id`, `quantity`, `date`) VALUES
(1, 1, 12, '2026-05-18 14:09:23'),
(2, 2, 8, '2026-05-18 14:09:23'),
(3, 3, 15, '2026-05-18 14:09:23'),
(4, 4, 7, '2026-05-18 14:09:23'),
(5, 5, 10, '2026-05-18 14:09:23'),
(6, 6, 13, '2026-05-18 14:09:23'),
(7, 7, 9, '2026-05-18 14:09:23'),
(8, 8, 11, '2026-05-18 14:09:23'),
(9, 9, 14, '2026-05-18 14:09:23'),
(10, 10, 20, '2026-05-18 14:09:23'),
(11, 11, 7, '2026-05-18 14:09:23'),
(12, 12, 10, '2026-05-18 14:09:23'),
(13, 13, 25, '2026-05-18 14:09:23'),
(14, 15, 9, '2026-05-18 14:09:23');

-- --------------------------------------------------------

--
-- Table structure for table `user_addresses`
--

CREATE TABLE `user_addresses` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `address` text NOT NULL,
  `is_default` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_addresses`
--

INSERT INTO `user_addresses` (`id`, `client_id`, `address`, `is_default`) VALUES
(1, 12, 'ижевск сабурова 9 кв 14', 0),
(2, 12, 'Ижевск улица Берша дом 42 подъезд 7', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `client`
--
ALTER TABLE `client`
  ADD PRIMARY KEY (`client_id`);

--
-- Indexes for table `manufacturer`
--
ALTER TABLE `manufacturer`
  ADD PRIMARY KEY (`manufacturer_id`);

--
-- Indexes for table `order`
--
ALTER TABLE `order`
  ADD PRIMARY KEY (`order_id`);

--
-- Indexes for table `orderitem`
--
ALTER TABLE `orderitem`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `fk_orderitem_order` (`order_id`),
  ADD KEY `fk_orderitem_product` (`product_id`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `fk_product_manufacturer` (`manufacturer_id`),
  ADD KEY `fk_product_type` (`ID_Type`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `client_id` (`client_id`);

--
-- Indexes for table `souvenir_type`
--
ALTER TABLE `souvenir_type`
  ADD PRIMARY KEY (`ID_Type`);

--
-- Indexes for table `supplies`
--
ALTER TABLE `supplies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_supplies_product` (`product_id`);

--
-- Indexes for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_id` (`client_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `client`
--
ALTER TABLE `client`
  MODIFY `client_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `manufacturer`
--
ALTER TABLE `manufacturer`
  MODIFY `manufacturer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `order`
--
ALTER TABLE `order`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `orderitem`
--
ALTER TABLE `orderitem`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `souvenir_type`
--
ALTER TABLE `souvenir_type`
  MODIFY `ID_Type` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `supplies`
--
ALTER TABLE `supplies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `user_addresses`
--
ALTER TABLE `user_addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orderitem`
--
ALTER TABLE `orderitem`
  ADD CONSTRAINT `fk_orderitem_order` FOREIGN KEY (`order_id`) REFERENCES `order` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_orderitem_product` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`);

--
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `fk_product_manufacturer` FOREIGN KEY (`manufacturer_id`) REFERENCES `manufacturer` (`manufacturer_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_product_type` FOREIGN KEY (`ID_Type`) REFERENCES `souvenir_type` (`ID_Type`) ON DELETE SET NULL;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `1` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `2` FOREIGN KEY (`client_id`) REFERENCES `client` (`client_id`) ON DELETE CASCADE;

--
-- Constraints for table `supplies`
--
ALTER TABLE `supplies`
  ADD CONSTRAINT `fk_supplies_product` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD CONSTRAINT `fk_user_addres_client` FOREIGN KEY (`client_id`) REFERENCES `client` (`client_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
