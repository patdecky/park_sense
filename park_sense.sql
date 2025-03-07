-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 192.168.12.210
-- Generation Time: Mar 07, 2025 at 08:54 PM
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
-- Database: `park_sense`
--
CREATE DATABASE IF NOT EXISTS `park_sense` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci;
USE `park_sense`;

-- --------------------------------------------------------

--
-- Table structure for table `camera`
--

DROP TABLE IF EXISTS `camera`;
CREATE TABLE IF NOT EXISTS `camera` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `parkinglot_id` bigint(20) UNSIGNED NOT NULL,
  `address` varchar(250) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `parkinglot_id` (`parkinglot_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- --------------------------------------------------------

--
-- Table structure for table `data_source`
--

DROP TABLE IF EXISTS `data_source`;
CREATE TABLE IF NOT EXISTS `data_source` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `parkinglot_id` bigint(20) UNSIGNED NOT NULL,
  `type` tinyint(3) UNSIGNED NOT NULL,
  `source` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `parkinglot_id` (`parkinglot_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- --------------------------------------------------------

--
-- Table structure for table `parkinglot`
--

DROP TABLE IF EXISTS `parkinglot`;
CREATE TABLE IF NOT EXISTS `parkinglot` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `geopos` point NOT NULL,
  `car_capacity` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`),
  SPATIAL KEY `geopos` (`geopos`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pl_history`
--

DROP TABLE IF EXISTS `pl_history`;
CREATE TABLE IF NOT EXISTS `pl_history` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `parkinglot_id` bigint(20) UNSIGNED NOT NULL,
  `vacancy` int(10) UNSIGNED NOT NULL,
  `current_timestamp` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `parkinglot_id` (`parkinglot_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pl_prediction`
--

DROP TABLE IF EXISTS `pl_prediction`;
CREATE TABLE IF NOT EXISTS `pl_prediction` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `parkinglot_id` bigint(20) UNSIGNED NOT NULL,
  `day` smallint(1) UNSIGNED NOT NULL,
  `vacancy` int(10) UNSIGNED NOT NULL,
  `day_timestamp` mediumint(8) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `parkinglot_id` (`parkinglot_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- --------------------------------------------------------

--
-- Table structure for table `statistics`
--

DROP TABLE IF EXISTS `statistics`;
CREATE TABLE IF NOT EXISTS `statistics` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `day_w` tinyint(3) UNSIGNED NOT NULL,
  `hours` tinyint(3) UNSIGNED NOT NULL,
  `minutes` tinyint(3) UNSIGNED NOT NULL,
  `total_arrival_count` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `camera`
--
ALTER TABLE `camera`
  ADD CONSTRAINT `camera_ibfk_1` FOREIGN KEY (`parkinglot_id`) REFERENCES `camera` (`id`);

--
-- Constraints for table `data_source`
--
ALTER TABLE `data_source`
  ADD CONSTRAINT `data_source_ibfk_1` FOREIGN KEY (`parkinglot_id`) REFERENCES `parkinglot` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pl_prediction`
--
ALTER TABLE `pl_prediction`
  ADD CONSTRAINT `pl_prediction_ibfk_1` FOREIGN KEY (`parkinglot_id`) REFERENCES `parkinglot` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;


--
-- Constraints for table `pl_history`
--
ALTER TABLE `pl_history`
    ADD CONSTRAINT `pl_history_ibfk_1` FOREIGN KEY (`parkinglot_id`) REFERENCES `parkinglot` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
