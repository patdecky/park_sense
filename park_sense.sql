-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: mysql-1b962c14-pat-f5fd.k.aivencloud.com:13490
-- Generation Time: Oct 19, 2024 at 02:40 PM
-- Server version: 8.0.30
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `park_sense`
--
CREATE DATABASE IF NOT EXISTS `park_sense` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `park_sense`;

-- --------------------------------------------------------

--
-- Table structure for table `camera`
--

DROP TABLE IF EXISTS `camera`;
CREATE TABLE `camera` (
  `id` bigint UNSIGNED NOT NULL,
  `parkinglot_id` bigint UNSIGNED NOT NULL,
  `address` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `parkinglot`
--

DROP TABLE IF EXISTS `parkinglot`;
CREATE TABLE `parkinglot` (
  `id` bigint UNSIGNED NOT NULL,
  `geopos` point NOT NULL,
  `car_capacity` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pl_history`
--

DROP TABLE IF EXISTS `pl_history`;
CREATE TABLE `pl_history` (
  `id` bigint UNSIGNED NOT NULL,
  `parkinglot_id` bigint UNSIGNED NOT NULL,
  `vacancy` int UNSIGNED NOT NULL,
  `current_timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `statistics`
--

DROP TABLE IF EXISTS `statistics`;
CREATE TABLE `statistics` (
  `id` bigint UNSIGNED NOT NULL,
  `day_w` tinyint UNSIGNED NOT NULL,
  `hours` tinyint UNSIGNED NOT NULL,
  `minutes` tinyint UNSIGNED NOT NULL,
  `total_arrival_count` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `camera`
--
ALTER TABLE `camera`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parkinglot_id` (`parkinglot_id`);

--
-- Indexes for table `parkinglot`
--
ALTER TABLE `parkinglot`
  ADD PRIMARY KEY (`id`),
  ADD SPATIAL KEY `geopos` (`geopos`);

--
-- Indexes for table `pl_history`
--
ALTER TABLE `pl_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parkinglot_id` (`parkinglot_id`);

--
-- Indexes for table `statistics`
--
ALTER TABLE `statistics`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `camera`
--
ALTER TABLE `camera`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `parkinglot`
--
ALTER TABLE `parkinglot`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pl_history`
--
ALTER TABLE `pl_history`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `statistics`
--
ALTER TABLE `statistics`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `camera`
--
ALTER TABLE `camera`
  ADD CONSTRAINT `camera_ibfk_1` FOREIGN KEY (`parkinglot_id`) REFERENCES `camera` (`id`);

--
-- Constraints for table `parkinglot`
--
ALTER TABLE `parkinglot`
  ADD CONSTRAINT `parkinglot_ibfk_1` FOREIGN KEY (`id`) REFERENCES `camera` (`parkinglot_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `parkinglot_ibfk_2` FOREIGN KEY (`id`) REFERENCES `pl_history` (`parkinglot_id`) ON DELETE CASCADE;

--
-- Constraints for table `pl_history`
--
ALTER TABLE `pl_history`
  ADD CONSTRAINT `pl_history_ibfk_1` FOREIGN KEY (`parkinglot_id`) REFERENCES `parkinglot` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
