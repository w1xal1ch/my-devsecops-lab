CREATE DATABASE IF NOT EXISTS syvenir;
USE syvenir;
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
-- Database: `syvenir`
--

-- --------------------------------------------------------

--
-- Table structure for table `client`
--

CREATE TABLE `client` (
  `ID_Client` int(11) NOT NULL,
  `Full_Name` varchar(50) DEFAULT NULL,
  `Phone_Number` varchar(25) DEFAULT NULL,
  `Email` varchar(50) DEFAULT NULL,
  `ID_User` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `client`
--

INSERT INTO `client` (`ID_Client`, `Full_Name`, `Phone_Number`, `Email`, `ID_User`) VALUES
(1, 'Кузнецова Мария Дмитриевна', '+79161234567', 'client1@example.com', 4),
(2, 'Смирнов Денис Олегович', '+79167654321', 'client2@example.com', 5),
(5, '1', '12345678911', '2222@mail.ru', 7),
(6, 'Светлана', '+79998887766', 'sveta@mail.ru', 8),
(7, 'Виктор', '+79997897878', 'viktor@ya.ru', 9),
(8, 'Виталя', '+79345678989', 'vitalya@mailll.ru', 10);

-- --------------------------------------------------------

--
-- Table structure for table `customer_reviews`
--

CREATE TABLE `customer_reviews` (
  `Code` int(11) NOT NULL,
  `ID_Client` int(11) DEFAULT NULL,
  `ID_Product` int(11) DEFAULT NULL,
  `Rating_1_to_10` int(11) DEFAULT NULL CHECK (`Rating_1_to_10` between 1 and 10),
  `Text` varchar(100) DEFAULT NULL,
  `Date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customer_reviews`
--

INSERT INTO `customer_reviews` (`Code`, `ID_Client`, `ID_Product`, `Rating_1_to_10`, `Text`, `Date`) VALUES
(1, 1, 1, 9, '', '2025-05-03'),
(2, 1, 4, 8, 'Брелок красивый, но немного тяжеловат', '2025-05-04'),
(3, 2, 2, 10, '', '2025-05-07'),
(4, 2, 3, 7, '', '2025-05-08'),
(5, 1, 5, 10, 'Хорошее качество статуэтки, выглядит очень красиво.', '2025-05-29');

-- --------------------------------------------------------

--
-- Table structure for table `delivery`
--

CREATE TABLE `delivery` (
  `ID_Delivery` int(11) NOT NULL,
  `Date` date DEFAULT NULL,
  `ID_Supplier` int(11) DEFAULT NULL,
  `Responsible_Person` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `delivery`
--

INSERT INTO `delivery` (`ID_Delivery`, `Date`, `ID_Supplier`, `Responsible_Person`) VALUES
(1, '2025-04-10', 1, 'Иванов И.И.'),
(2, '2025-04-15', 2, 'Иванов И.И.');

-- --------------------------------------------------------

--
-- Table structure for table `delivery_contents`
--

CREATE TABLE `delivery_contents` (
  `Code` int(11) NOT NULL,
  `ID_Deliver` int(11) DEFAULT NULL,
  `ID_Product` int(11) DEFAULT NULL,
  `Quantity` int(11) DEFAULT NULL,
  `Purchase_Price` varchar(50) DEFAULT NULL,
  `Total` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `delivery_contents`
--

INSERT INTO `delivery_contents` (`Code`, `ID_Deliver`, `ID_Product`, `Quantity`, `Purchase_Price`, `Total`) VALUES
(1, 1, 1, 100, '100.00', '10000.00'),
(2, 1, 2, 50, '250.00', '12500.00'),
(3, 2, 3, 30, '600.00', '18000.00'),
(4, 2, 4, 60, '180.00', '10800.00'),
(5, 2, 5, 20, '900.00', '18000.00');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `ID_Products` int(11) NOT NULL,
  `Souvenir_Name` varchar(50) DEFAULT NULL,
  `Description` varchar(100) DEFAULT NULL,
  `Price` varchar(50) DEFAULT NULL,
  `Stock_Quantity` int(11) DEFAULT NULL,
  `Photo` text DEFAULT NULL,
  `ID_Type` int(11) DEFAULT NULL,
  `is_deleted` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`ID_Products`, `Souvenir_Name`, `Description`, `Price`, `Stock_Quantity`, `Photo`, `ID_Type`, `is_deleted`) VALUES
(1, 'Магнит \"Москва\"', 'Магнит с изображением достопримечательностей Москвы', '155.00', 15, 'magnet_moscow.jpg', 1, 0),
(2, 'Кружка \"Санкт-Петербург\"', 'Керамическая кружка с панорамой Санкт-Петербурга', '350.00', 6, 'cup_spb.jpg', 2, 0),
(3, 'Футболка \"Россия\"', 'Хлопковая футболка с национальным орнаментом', '800.00', 12, 'tshirt_russia.jpg', 3, 0),
(4, 'Брелок \"Кремль\"', 'Металлический брелок в форме Московского Кремля', '250.00', 17, 'keychain_kremlin.jpg', 4, 0),
(5, 'Статуэтка \"Медведь\"', 'Деревянная статуэтка медведя - символа России', '1200.00', 5, 'statue_bear.jpg', 5, 0),
(8, 'Магнит \"Москва\"', 'Магнит с изображением достопримечательностей Москвы', '200', 10, 'magnet_moscow2.jpg', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `sale`
--

CREATE TABLE `sale` (
  `ID_S` int(11) NOT NULL,
  `ID_Client` int(11) DEFAULT NULL,
  `Sale_Date` date DEFAULT NULL,
  `Status` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sale`
--

INSERT INTO `sale` (`ID_S`, `ID_Client`, `Sale_Date`, `Status`) VALUES
(1, 1, '2025-05-01', 1),
(2, 2, '2025-05-05', 1),
(3, 1, '2025-05-10', 2),
(4, 1, '2025-05-27', 1),
(5, 7, '2026-05-06', 1),
(6, 8, '2026-05-14', 1),
(7, 8, '2026-05-14', 1),
(8, 8, '2026-05-14', 1),
(9, 8, '2026-05-14', 1),
(10, 8, '2026-05-24', 1),
(11, 8, '2026-05-24', 1),
(12, 8, '2026-06-10', 1),
(13, 8, '2026-06-10', 1);

-- --------------------------------------------------------

--
-- Table structure for table `sale_contents`
--

CREATE TABLE `sale_contents` (
  `Code` int(11) NOT NULL,
  `ID_Sale` int(11) DEFAULT NULL,
  `ID_Product` int(11) DEFAULT NULL,
  `Quantity` int(11) DEFAULT NULL,
  `Price` varchar(50) DEFAULT NULL,
  `Total` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sale_contents`
--

INSERT INTO `sale_contents` (`Code`, `ID_Sale`, `ID_Product`, `Quantity`, `Price`, `Total`) VALUES
(1, 1, 1, 2, '150.00', '300.00'),
(2, 1, 4, 1, '250.00', '250.00'),
(3, 2, 2, 1, '350.00', '350.00'),
(4, 2, 3, 1, '800.00', '800.00'),
(5, 3, 5, 1, '1200.00', '1200.00'),
(6, 4, 1, 1, '150.00', '150'),
(7, 5, 4, 1, '250.00', NULL),
(8, 5, 2, 1, '350.00', NULL),
(9, 6, 1, 1, '155', NULL),
(10, 7, 8, 1, '200', NULL),
(11, 8, 4, 1, '250', NULL),
(12, 9, 2, 1, '350', NULL),
(13, 10, 4, 1, '250', NULL),
(14, 11, 4, 1, '250', NULL),
(15, 11, 2, 1, '350', NULL),
(16, 12, 2, 1, '350', NULL),
(17, 13, 4, 1, '250', NULL);

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
(1, 'Магниты'),
(2, 'Кружки'),
(3, 'Футболки'),
(4, 'Брелоки'),
(5, 'Статуэтки');

-- --------------------------------------------------------

--
-- Table structure for table `supplier`
--

CREATE TABLE `supplier` (
  `ID_Supplier` int(11) NOT NULL,
  `Company_Name` varchar(50) DEFAULT NULL,
  `Phone` varchar(50) DEFAULT NULL,
  `Address` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `supplier`
--

INSERT INTO `supplier` (`ID_Supplier`, `Company_Name`, `Phone`, `Address`) VALUES
(1, 'ООО \"Сувениры оптом\"', '+74951234567', 'г. Москва, ул. Поставщиков, д.1'),
(2, 'ИП Петров П.П.', '+74959876543', 'г. Санкт-Петербург, пр. Поставщиков, д.10');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `ID_User` int(11) NOT NULL,
  `Login` varchar(50) DEFAULT NULL,
  `Password` varchar(255) NOT NULL,
  `Role` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`ID_User`, `Login`, `Password`, `Role`) VALUES
(1, 'admin1', 'admin123', 1),
(2, 'manager', 'manager123', 2),
(4, 'client1', 'client123', 4),
(5, 'client2', 'client456', 4),
(7, 'adm', '1357qetu', 1),
(8, 'admin', '$2y$10$1jDPj4k8qLIYsdzmnnVURe8ABGdiQx2L1lFzdZbD.EeeX0uwxkd0W', 1),
(9, 'vitya', '$2y$12$tm9EsXxW7Ltd7Ypu0yf9WOb0ppvFRtSShF7VS9R61lu8Auugjd0OS', 1),
(10, 'tolik', '$2y$12$m6VTg/jGxYd4Eldle4vQ5ery/2UB.sGVKoI3jP3BAAJLInd4NpNC6', 4);

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
(2, 8, 'Светлое', 0),
(3, 8, 'Ижевск улица Берша дом 42 подъезд 7', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `client`
--
ALTER TABLE `client`
  ADD PRIMARY KEY (`ID_Client`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD KEY `ID_User` (`ID_User`);

--
-- Indexes for table `customer_reviews`
--
ALTER TABLE `customer_reviews`
  ADD PRIMARY KEY (`Code`),
  ADD KEY `ID_Client` (`ID_Client`),
  ADD KEY `ID_Product` (`ID_Product`);

--
-- Indexes for table `delivery`
--
ALTER TABLE `delivery`
  ADD PRIMARY KEY (`ID_Delivery`),
  ADD KEY `ID_Supplier` (`ID_Supplier`);

--
-- Indexes for table `delivery_contents`
--
ALTER TABLE `delivery_contents`
  ADD PRIMARY KEY (`Code`),
  ADD KEY `ID_Deliver` (`ID_Deliver`),
  ADD KEY `ID_Product` (`ID_Product`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`ID_Products`),
  ADD KEY `ID_Type` (`ID_Type`);

--
-- Indexes for table `sale`
--
ALTER TABLE `sale`
  ADD PRIMARY KEY (`ID_S`),
  ADD KEY `ID_Client` (`ID_Client`);

--
-- Indexes for table `sale_contents`
--
ALTER TABLE `sale_contents`
  ADD PRIMARY KEY (`Code`),
  ADD KEY `ID_Sale` (`ID_Sale`),
  ADD KEY `ID_Product` (`ID_Product`);

--
-- Indexes for table `souvenir_type`
--
ALTER TABLE `souvenir_type`
  ADD PRIMARY KEY (`ID_Type`);

--
-- Indexes for table `supplier`
--
ALTER TABLE `supplier`
  ADD PRIMARY KEY (`ID_Supplier`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`ID_User`),
  ADD UNIQUE KEY `Login` (`Login`);

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
  MODIFY `ID_Client` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `customer_reviews`
--
ALTER TABLE `customer_reviews`
  MODIFY `Code` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `delivery`
--
ALTER TABLE `delivery`
  MODIFY `ID_Delivery` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `delivery_contents`
--
ALTER TABLE `delivery_contents`
  MODIFY `Code` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `ID_Products` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `sale`
--
ALTER TABLE `sale`
  MODIFY `ID_S` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `sale_contents`
--
ALTER TABLE `sale_contents`
  MODIFY `Code` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `souvenir_type`
--
ALTER TABLE `souvenir_type`
  MODIFY `ID_Type` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `supplier`
--
ALTER TABLE `supplier`
  MODIFY `ID_Supplier` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `ID_User` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `user_addresses`
--
ALTER TABLE `user_addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `client`
--
ALTER TABLE `client`
  ADD CONSTRAINT `client_ibfk_1` FOREIGN KEY (`ID_User`) REFERENCES `user` (`ID_User`);

--
-- Constraints for table `customer_reviews`
--
ALTER TABLE `customer_reviews`
  ADD CONSTRAINT `customer_reviews_ibfk_1` FOREIGN KEY (`ID_Client`) REFERENCES `client` (`ID_Client`),
  ADD CONSTRAINT `customer_reviews_ibfk_2` FOREIGN KEY (`ID_Product`) REFERENCES `products` (`ID_Products`);

--
-- Constraints for table `delivery`
--
ALTER TABLE `delivery`
  ADD CONSTRAINT `delivery_ibfk_1` FOREIGN KEY (`ID_Supplier`) REFERENCES `supplier` (`ID_Supplier`);

--
-- Constraints for table `delivery_contents`
--
ALTER TABLE `delivery_contents`
  ADD CONSTRAINT `delivery_contents_ibfk_1` FOREIGN KEY (`ID_Deliver`) REFERENCES `delivery` (`ID_Delivery`),
  ADD CONSTRAINT `delivery_contents_ibfk_2` FOREIGN KEY (`ID_Product`) REFERENCES `products` (`ID_Products`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`ID_Type`) REFERENCES `souvenir_type` (`ID_Type`);

--
-- Constraints for table `sale`
--
ALTER TABLE `sale`
  ADD CONSTRAINT `sale_ibfk_1` FOREIGN KEY (`ID_Client`) REFERENCES `client` (`ID_Client`);

--
-- Constraints for table `sale_contents`
--
ALTER TABLE `sale_contents`
  ADD CONSTRAINT `sale_contents_ibfk_1` FOREIGN KEY (`ID_Sale`) REFERENCES `sale` (`ID_S`),
  ADD CONSTRAINT `sale_contents_ibfk_2` FOREIGN KEY (`ID_Product`) REFERENCES `products` (`ID_Products`);

--
-- Constraints for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD CONSTRAINT `1` FOREIGN KEY (`client_id`) REFERENCES `client` (`ID_Client`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
