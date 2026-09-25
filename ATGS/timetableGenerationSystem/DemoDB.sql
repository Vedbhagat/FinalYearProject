-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 25, 2026 at 08:18 PM
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
-- Database: `atgs_devlopment`
--

-- --------------------------------------------------------

--
-- Table structure for table `availability`
--

CREATE TABLE `availability` (
  `TEACHER_ID` int(11) NOT NULL,
  `SLOT_ID` int(11) NOT NULL,
  `WEEKDAY` enum('MONDAY','TUESDAY','WEDNESDAY','THURSDAY','FRIDAY','SATURDAY') NOT NULL,
  `STATUS` enum('AVAILABLE','ALLOTED') DEFAULT 'AVAILABLE'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `availability`
--

INSERT INTO `availability` (`TEACHER_ID`, `SLOT_ID`, `WEEKDAY`, `STATUS`) VALUES
(1, 1, 'MONDAY', 'AVAILABLE'),
(1, 1, 'TUESDAY', 'AVAILABLE'),
(1, 1, 'WEDNESDAY', 'AVAILABLE'),
(1, 1, 'THURSDAY', 'AVAILABLE'),
(1, 1, 'FRIDAY', 'AVAILABLE'),
(1, 1, 'SATURDAY', 'AVAILABLE'),
(1, 2, 'MONDAY', 'AVAILABLE'),
(1, 2, 'TUESDAY', 'AVAILABLE'),
(1, 2, 'WEDNESDAY', 'AVAILABLE'),
(1, 2, 'THURSDAY', 'AVAILABLE'),
(1, 2, 'FRIDAY', 'AVAILABLE'),
(1, 2, 'SATURDAY', 'AVAILABLE'),
(1, 3, 'MONDAY', 'AVAILABLE'),
(1, 3, 'TUESDAY', 'AVAILABLE'),
(1, 3, 'WEDNESDAY', 'AVAILABLE'),
(1, 3, 'THURSDAY', 'AVAILABLE'),
(1, 3, 'FRIDAY', 'AVAILABLE'),
(1, 3, 'SATURDAY', 'AVAILABLE'),
(1, 4, 'MONDAY', 'AVAILABLE'),
(1, 4, 'TUESDAY', 'AVAILABLE'),
(1, 4, 'WEDNESDAY', 'AVAILABLE'),
(1, 4, 'THURSDAY', 'AVAILABLE'),
(1, 4, 'FRIDAY', 'AVAILABLE'),
(1, 4, 'SATURDAY', 'AVAILABLE'),
(1, 5, 'MONDAY', 'AVAILABLE'),
(1, 5, 'TUESDAY', 'AVAILABLE'),
(1, 5, 'WEDNESDAY', 'AVAILABLE'),
(1, 5, 'THURSDAY', 'AVAILABLE'),
(1, 5, 'FRIDAY', 'AVAILABLE'),
(1, 5, 'SATURDAY', 'AVAILABLE'),
(1, 6, 'MONDAY', 'AVAILABLE'),
(1, 6, 'TUESDAY', 'AVAILABLE'),
(1, 6, 'WEDNESDAY', 'AVAILABLE'),
(1, 6, 'THURSDAY', 'AVAILABLE'),
(1, 6, 'FRIDAY', 'AVAILABLE'),
(1, 6, 'SATURDAY', 'AVAILABLE'),
(1, 7, 'MONDAY', 'AVAILABLE'),
(1, 7, 'TUESDAY', 'AVAILABLE'),
(1, 7, 'WEDNESDAY', 'AVAILABLE'),
(1, 7, 'THURSDAY', 'AVAILABLE'),
(1, 7, 'FRIDAY', 'AVAILABLE'),
(1, 7, 'SATURDAY', 'AVAILABLE'),
(1, 8, 'MONDAY', 'AVAILABLE'),
(1, 8, 'TUESDAY', 'AVAILABLE'),
(1, 8, 'WEDNESDAY', 'AVAILABLE'),
(1, 8, 'THURSDAY', 'AVAILABLE'),
(1, 8, 'FRIDAY', 'AVAILABLE'),
(1, 8, 'SATURDAY', 'AVAILABLE'),
(1, 9, 'MONDAY', 'AVAILABLE'),
(1, 9, 'TUESDAY', 'AVAILABLE'),
(1, 9, 'WEDNESDAY', 'AVAILABLE'),
(1, 9, 'THURSDAY', 'AVAILABLE'),
(1, 9, 'FRIDAY', 'AVAILABLE'),
(1, 9, 'SATURDAY', 'AVAILABLE'),
(1, 10, 'MONDAY', 'AVAILABLE'),
(1, 10, 'TUESDAY', 'AVAILABLE'),
(1, 10, 'WEDNESDAY', 'AVAILABLE'),
(1, 10, 'THURSDAY', 'AVAILABLE'),
(1, 10, 'FRIDAY', 'AVAILABLE'),
(1, 10, 'SATURDAY', 'AVAILABLE'),
(1, 11, 'MONDAY', 'AVAILABLE'),
(1, 11, 'TUESDAY', 'AVAILABLE'),
(1, 11, 'WEDNESDAY', 'AVAILABLE'),
(1, 11, 'THURSDAY', 'AVAILABLE'),
(1, 11, 'FRIDAY', 'AVAILABLE'),
(1, 11, 'SATURDAY', 'AVAILABLE'),
(1, 12, 'MONDAY', 'AVAILABLE'),
(1, 12, 'TUESDAY', 'AVAILABLE'),
(1, 12, 'WEDNESDAY', 'AVAILABLE'),
(1, 12, 'THURSDAY', 'AVAILABLE'),
(1, 12, 'FRIDAY', 'AVAILABLE'),
(1, 12, 'SATURDAY', 'AVAILABLE'),
(2, 1, 'MONDAY', 'AVAILABLE'),
(2, 1, 'TUESDAY', 'AVAILABLE'),
(2, 1, 'WEDNESDAY', 'AVAILABLE'),
(2, 1, 'THURSDAY', 'AVAILABLE'),
(2, 1, 'FRIDAY', 'AVAILABLE'),
(2, 1, 'SATURDAY', 'AVAILABLE'),
(2, 2, 'MONDAY', 'AVAILABLE'),
(2, 2, 'TUESDAY', 'AVAILABLE'),
(2, 2, 'WEDNESDAY', 'AVAILABLE'),
(2, 2, 'THURSDAY', 'AVAILABLE'),
(2, 2, 'FRIDAY', 'AVAILABLE'),
(2, 2, 'SATURDAY', 'AVAILABLE'),
(2, 3, 'MONDAY', 'AVAILABLE'),
(2, 3, 'TUESDAY', 'AVAILABLE'),
(2, 3, 'WEDNESDAY', 'AVAILABLE'),
(2, 3, 'THURSDAY', 'AVAILABLE'),
(2, 3, 'FRIDAY', 'AVAILABLE'),
(2, 3, 'SATURDAY', 'AVAILABLE'),
(2, 4, 'MONDAY', 'AVAILABLE'),
(2, 4, 'TUESDAY', 'AVAILABLE'),
(2, 4, 'WEDNESDAY', 'AVAILABLE'),
(2, 4, 'THURSDAY', 'AVAILABLE'),
(2, 4, 'FRIDAY', 'AVAILABLE'),
(2, 4, 'SATURDAY', 'AVAILABLE'),
(2, 5, 'MONDAY', 'AVAILABLE'),
(2, 5, 'TUESDAY', 'AVAILABLE'),
(2, 5, 'WEDNESDAY', 'AVAILABLE'),
(2, 5, 'THURSDAY', 'AVAILABLE'),
(2, 5, 'FRIDAY', 'AVAILABLE'),
(2, 5, 'SATURDAY', 'AVAILABLE'),
(2, 6, 'MONDAY', 'AVAILABLE'),
(2, 6, 'TUESDAY', 'AVAILABLE'),
(2, 6, 'WEDNESDAY', 'AVAILABLE'),
(2, 6, 'THURSDAY', 'AVAILABLE'),
(2, 6, 'FRIDAY', 'AVAILABLE'),
(2, 6, 'SATURDAY', 'AVAILABLE'),
(2, 7, 'MONDAY', 'AVAILABLE'),
(2, 7, 'TUESDAY', 'AVAILABLE'),
(2, 7, 'WEDNESDAY', 'AVAILABLE'),
(2, 7, 'THURSDAY', 'AVAILABLE'),
(2, 7, 'FRIDAY', 'AVAILABLE'),
(2, 7, 'SATURDAY', 'AVAILABLE'),
(2, 8, 'MONDAY', 'AVAILABLE'),
(2, 8, 'TUESDAY', 'AVAILABLE'),
(2, 8, 'WEDNESDAY', 'AVAILABLE'),
(2, 8, 'THURSDAY', 'AVAILABLE'),
(2, 8, 'FRIDAY', 'AVAILABLE'),
(2, 8, 'SATURDAY', 'AVAILABLE'),
(2, 9, 'MONDAY', 'AVAILABLE'),
(2, 9, 'TUESDAY', 'AVAILABLE'),
(2, 9, 'WEDNESDAY', 'AVAILABLE'),
(2, 9, 'THURSDAY', 'AVAILABLE'),
(2, 9, 'FRIDAY', 'AVAILABLE'),
(2, 9, 'SATURDAY', 'AVAILABLE'),
(2, 10, 'MONDAY', 'AVAILABLE'),
(2, 10, 'TUESDAY', 'AVAILABLE'),
(2, 10, 'WEDNESDAY', 'AVAILABLE'),
(2, 10, 'THURSDAY', 'AVAILABLE'),
(2, 10, 'FRIDAY', 'AVAILABLE'),
(2, 10, 'SATURDAY', 'AVAILABLE'),
(2, 11, 'MONDAY', 'AVAILABLE'),
(2, 11, 'TUESDAY', 'AVAILABLE'),
(2, 11, 'WEDNESDAY', 'AVAILABLE'),
(2, 11, 'THURSDAY', 'AVAILABLE'),
(2, 11, 'FRIDAY', 'AVAILABLE'),
(2, 11, 'SATURDAY', 'AVAILABLE'),
(2, 12, 'MONDAY', 'AVAILABLE'),
(2, 12, 'TUESDAY', 'AVAILABLE'),
(2, 12, 'WEDNESDAY', 'AVAILABLE'),
(2, 12, 'THURSDAY', 'AVAILABLE'),
(2, 12, 'FRIDAY', 'AVAILABLE'),
(2, 12, 'SATURDAY', 'AVAILABLE'),
(3, 1, 'MONDAY', 'AVAILABLE'),
(3, 1, 'TUESDAY', 'AVAILABLE'),
(3, 1, 'WEDNESDAY', 'AVAILABLE'),
(3, 1, 'THURSDAY', 'AVAILABLE'),
(3, 1, 'FRIDAY', 'AVAILABLE'),
(3, 1, 'SATURDAY', 'AVAILABLE'),
(3, 2, 'MONDAY', 'AVAILABLE'),
(3, 2, 'TUESDAY', 'AVAILABLE'),
(3, 2, 'WEDNESDAY', 'AVAILABLE'),
(3, 2, 'THURSDAY', 'AVAILABLE'),
(3, 2, 'FRIDAY', 'AVAILABLE'),
(3, 2, 'SATURDAY', 'AVAILABLE'),
(3, 3, 'MONDAY', 'AVAILABLE'),
(3, 3, 'TUESDAY', 'AVAILABLE'),
(3, 3, 'WEDNESDAY', 'AVAILABLE'),
(3, 3, 'THURSDAY', 'AVAILABLE'),
(3, 3, 'FRIDAY', 'AVAILABLE'),
(3, 3, 'SATURDAY', 'AVAILABLE'),
(3, 4, 'MONDAY', 'AVAILABLE'),
(3, 4, 'TUESDAY', 'AVAILABLE'),
(3, 4, 'WEDNESDAY', 'AVAILABLE'),
(3, 4, 'THURSDAY', 'AVAILABLE'),
(3, 4, 'FRIDAY', 'AVAILABLE'),
(3, 4, 'SATURDAY', 'AVAILABLE'),
(3, 5, 'MONDAY', 'AVAILABLE'),
(3, 5, 'TUESDAY', 'AVAILABLE'),
(3, 5, 'WEDNESDAY', 'AVAILABLE'),
(3, 5, 'THURSDAY', 'AVAILABLE'),
(3, 5, 'FRIDAY', 'AVAILABLE'),
(3, 5, 'SATURDAY', 'AVAILABLE'),
(3, 6, 'MONDAY', 'AVAILABLE'),
(3, 6, 'TUESDAY', 'AVAILABLE'),
(3, 6, 'WEDNESDAY', 'AVAILABLE'),
(3, 6, 'THURSDAY', 'AVAILABLE'),
(3, 6, 'FRIDAY', 'AVAILABLE'),
(3, 6, 'SATURDAY', 'AVAILABLE'),
(3, 7, 'MONDAY', 'AVAILABLE'),
(3, 7, 'TUESDAY', 'AVAILABLE'),
(3, 7, 'WEDNESDAY', 'AVAILABLE'),
(3, 7, 'THURSDAY', 'AVAILABLE'),
(3, 7, 'FRIDAY', 'AVAILABLE'),
(3, 7, 'SATURDAY', 'AVAILABLE'),
(3, 8, 'MONDAY', 'AVAILABLE'),
(3, 8, 'TUESDAY', 'AVAILABLE'),
(3, 8, 'WEDNESDAY', 'AVAILABLE'),
(3, 8, 'THURSDAY', 'AVAILABLE'),
(3, 8, 'FRIDAY', 'AVAILABLE'),
(3, 8, 'SATURDAY', 'AVAILABLE'),
(3, 9, 'MONDAY', 'AVAILABLE'),
(3, 9, 'TUESDAY', 'AVAILABLE'),
(3, 9, 'WEDNESDAY', 'AVAILABLE'),
(3, 9, 'THURSDAY', 'AVAILABLE'),
(3, 9, 'FRIDAY', 'AVAILABLE'),
(3, 9, 'SATURDAY', 'AVAILABLE'),
(3, 10, 'MONDAY', 'AVAILABLE'),
(3, 10, 'TUESDAY', 'AVAILABLE'),
(3, 10, 'WEDNESDAY', 'AVAILABLE'),
(3, 10, 'THURSDAY', 'AVAILABLE'),
(3, 10, 'FRIDAY', 'AVAILABLE'),
(3, 10, 'SATURDAY', 'AVAILABLE'),
(3, 11, 'MONDAY', 'AVAILABLE'),
(3, 11, 'TUESDAY', 'AVAILABLE'),
(3, 11, 'WEDNESDAY', 'AVAILABLE'),
(3, 11, 'THURSDAY', 'AVAILABLE'),
(3, 11, 'FRIDAY', 'AVAILABLE'),
(3, 11, 'SATURDAY', 'AVAILABLE'),
(3, 12, 'MONDAY', 'AVAILABLE'),
(3, 12, 'TUESDAY', 'AVAILABLE'),
(3, 12, 'WEDNESDAY', 'AVAILABLE'),
(3, 12, 'THURSDAY', 'AVAILABLE'),
(3, 12, 'FRIDAY', 'AVAILABLE'),
(3, 12, 'SATURDAY', 'AVAILABLE'),
(4, 1, 'MONDAY', 'AVAILABLE'),
(4, 1, 'TUESDAY', 'AVAILABLE'),
(4, 1, 'WEDNESDAY', 'AVAILABLE'),
(4, 1, 'THURSDAY', 'AVAILABLE'),
(4, 1, 'FRIDAY', 'AVAILABLE'),
(4, 1, 'SATURDAY', 'AVAILABLE'),
(4, 2, 'MONDAY', 'AVAILABLE'),
(4, 2, 'TUESDAY', 'AVAILABLE'),
(4, 2, 'WEDNESDAY', 'AVAILABLE'),
(4, 2, 'THURSDAY', 'AVAILABLE'),
(4, 2, 'FRIDAY', 'AVAILABLE'),
(4, 2, 'SATURDAY', 'AVAILABLE'),
(4, 3, 'MONDAY', 'AVAILABLE'),
(4, 3, 'TUESDAY', 'AVAILABLE'),
(4, 3, 'WEDNESDAY', 'AVAILABLE'),
(4, 3, 'THURSDAY', 'AVAILABLE'),
(4, 3, 'FRIDAY', 'AVAILABLE'),
(4, 3, 'SATURDAY', 'AVAILABLE'),
(4, 4, 'MONDAY', 'AVAILABLE'),
(4, 4, 'TUESDAY', 'AVAILABLE'),
(4, 4, 'WEDNESDAY', 'AVAILABLE'),
(4, 4, 'THURSDAY', 'AVAILABLE'),
(4, 4, 'FRIDAY', 'AVAILABLE'),
(4, 4, 'SATURDAY', 'AVAILABLE'),
(4, 5, 'MONDAY', 'AVAILABLE'),
(4, 5, 'TUESDAY', 'AVAILABLE'),
(4, 5, 'WEDNESDAY', 'AVAILABLE'),
(4, 5, 'THURSDAY', 'AVAILABLE'),
(4, 5, 'FRIDAY', 'AVAILABLE'),
(4, 5, 'SATURDAY', 'AVAILABLE'),
(4, 6, 'MONDAY', 'AVAILABLE'),
(4, 6, 'TUESDAY', 'AVAILABLE'),
(4, 6, 'WEDNESDAY', 'AVAILABLE'),
(4, 6, 'THURSDAY', 'AVAILABLE'),
(4, 6, 'FRIDAY', 'AVAILABLE'),
(4, 6, 'SATURDAY', 'AVAILABLE'),
(4, 7, 'MONDAY', 'AVAILABLE'),
(4, 7, 'TUESDAY', 'AVAILABLE'),
(4, 7, 'WEDNESDAY', 'AVAILABLE'),
(4, 7, 'THURSDAY', 'AVAILABLE'),
(4, 7, 'FRIDAY', 'AVAILABLE'),
(4, 7, 'SATURDAY', 'AVAILABLE'),
(4, 8, 'MONDAY', 'AVAILABLE'),
(4, 8, 'TUESDAY', 'AVAILABLE'),
(4, 8, 'WEDNESDAY', 'AVAILABLE'),
(4, 8, 'THURSDAY', 'AVAILABLE'),
(4, 8, 'FRIDAY', 'AVAILABLE'),
(4, 8, 'SATURDAY', 'AVAILABLE'),
(4, 9, 'MONDAY', 'AVAILABLE'),
(4, 9, 'TUESDAY', 'AVAILABLE'),
(4, 9, 'WEDNESDAY', 'AVAILABLE'),
(4, 9, 'THURSDAY', 'AVAILABLE'),
(4, 9, 'FRIDAY', 'AVAILABLE'),
(4, 9, 'SATURDAY', 'AVAILABLE'),
(4, 10, 'MONDAY', 'AVAILABLE'),
(4, 10, 'TUESDAY', 'AVAILABLE'),
(4, 10, 'WEDNESDAY', 'AVAILABLE'),
(4, 10, 'THURSDAY', 'AVAILABLE'),
(4, 10, 'FRIDAY', 'AVAILABLE'),
(4, 10, 'SATURDAY', 'AVAILABLE'),
(4, 11, 'MONDAY', 'AVAILABLE'),
(4, 11, 'TUESDAY', 'AVAILABLE'),
(4, 11, 'WEDNESDAY', 'AVAILABLE'),
(4, 11, 'THURSDAY', 'AVAILABLE'),
(4, 11, 'FRIDAY', 'AVAILABLE'),
(4, 11, 'SATURDAY', 'AVAILABLE'),
(4, 12, 'MONDAY', 'AVAILABLE'),
(4, 12, 'TUESDAY', 'AVAILABLE'),
(4, 12, 'WEDNESDAY', 'AVAILABLE'),
(4, 12, 'THURSDAY', 'AVAILABLE'),
(4, 12, 'FRIDAY', 'AVAILABLE'),
(4, 12, 'SATURDAY', 'AVAILABLE'),
(4, 13, 'MONDAY', 'AVAILABLE'),
(4, 13, 'TUESDAY', 'AVAILABLE'),
(4, 13, 'WEDNESDAY', 'AVAILABLE'),
(4, 13, 'THURSDAY', 'AVAILABLE'),
(4, 13, 'FRIDAY', 'AVAILABLE'),
(4, 13, 'SATURDAY', 'AVAILABLE'),
(4, 14, 'MONDAY', 'AVAILABLE'),
(4, 14, 'TUESDAY', 'AVAILABLE'),
(4, 14, 'WEDNESDAY', 'AVAILABLE'),
(4, 14, 'THURSDAY', 'AVAILABLE'),
(4, 14, 'FRIDAY', 'AVAILABLE'),
(4, 14, 'SATURDAY', 'AVAILABLE'),
(4, 15, 'MONDAY', 'AVAILABLE'),
(4, 15, 'TUESDAY', 'AVAILABLE'),
(4, 15, 'WEDNESDAY', 'AVAILABLE'),
(4, 15, 'THURSDAY', 'AVAILABLE'),
(4, 15, 'FRIDAY', 'AVAILABLE'),
(4, 15, 'SATURDAY', 'AVAILABLE'),
(4, 16, 'MONDAY', 'AVAILABLE'),
(4, 16, 'TUESDAY', 'AVAILABLE'),
(4, 16, 'WEDNESDAY', 'AVAILABLE'),
(4, 16, 'THURSDAY', 'AVAILABLE'),
(4, 16, 'FRIDAY', 'AVAILABLE'),
(4, 16, 'SATURDAY', 'AVAILABLE'),
(5, 1, 'MONDAY', 'AVAILABLE'),
(5, 1, 'TUESDAY', 'AVAILABLE'),
(5, 1, 'WEDNESDAY', 'AVAILABLE'),
(5, 1, 'THURSDAY', 'AVAILABLE'),
(5, 1, 'FRIDAY', 'AVAILABLE'),
(5, 1, 'SATURDAY', 'AVAILABLE'),
(5, 2, 'MONDAY', 'AVAILABLE'),
(5, 2, 'TUESDAY', 'AVAILABLE'),
(5, 2, 'WEDNESDAY', 'AVAILABLE'),
(5, 2, 'THURSDAY', 'AVAILABLE'),
(5, 2, 'FRIDAY', 'AVAILABLE'),
(5, 2, 'SATURDAY', 'AVAILABLE'),
(5, 3, 'MONDAY', 'AVAILABLE'),
(5, 3, 'TUESDAY', 'AVAILABLE'),
(5, 3, 'WEDNESDAY', 'AVAILABLE'),
(5, 3, 'THURSDAY', 'AVAILABLE'),
(5, 3, 'FRIDAY', 'AVAILABLE'),
(5, 3, 'SATURDAY', 'AVAILABLE'),
(5, 4, 'MONDAY', 'AVAILABLE'),
(5, 4, 'TUESDAY', 'AVAILABLE'),
(5, 4, 'WEDNESDAY', 'AVAILABLE'),
(5, 4, 'THURSDAY', 'AVAILABLE'),
(5, 4, 'FRIDAY', 'AVAILABLE'),
(5, 4, 'SATURDAY', 'AVAILABLE'),
(5, 5, 'MONDAY', 'AVAILABLE'),
(5, 5, 'TUESDAY', 'AVAILABLE'),
(5, 5, 'WEDNESDAY', 'AVAILABLE'),
(5, 5, 'THURSDAY', 'AVAILABLE'),
(5, 5, 'FRIDAY', 'AVAILABLE'),
(5, 5, 'SATURDAY', 'AVAILABLE'),
(5, 6, 'MONDAY', 'AVAILABLE'),
(5, 6, 'TUESDAY', 'AVAILABLE'),
(5, 6, 'WEDNESDAY', 'AVAILABLE'),
(5, 6, 'THURSDAY', 'AVAILABLE'),
(5, 6, 'FRIDAY', 'AVAILABLE'),
(5, 6, 'SATURDAY', 'AVAILABLE'),
(5, 7, 'MONDAY', 'AVAILABLE'),
(5, 7, 'TUESDAY', 'AVAILABLE'),
(5, 7, 'WEDNESDAY', 'AVAILABLE'),
(5, 7, 'THURSDAY', 'AVAILABLE'),
(5, 7, 'FRIDAY', 'AVAILABLE'),
(5, 7, 'SATURDAY', 'AVAILABLE'),
(5, 8, 'MONDAY', 'AVAILABLE'),
(5, 8, 'TUESDAY', 'AVAILABLE'),
(5, 8, 'WEDNESDAY', 'AVAILABLE'),
(5, 8, 'THURSDAY', 'AVAILABLE'),
(5, 8, 'FRIDAY', 'AVAILABLE'),
(5, 8, 'SATURDAY', 'AVAILABLE'),
(5, 9, 'MONDAY', 'AVAILABLE'),
(5, 9, 'TUESDAY', 'AVAILABLE'),
(5, 9, 'WEDNESDAY', 'AVAILABLE'),
(5, 9, 'THURSDAY', 'AVAILABLE'),
(5, 9, 'FRIDAY', 'AVAILABLE'),
(5, 9, 'SATURDAY', 'AVAILABLE'),
(5, 10, 'MONDAY', 'AVAILABLE'),
(5, 10, 'TUESDAY', 'AVAILABLE'),
(5, 10, 'WEDNESDAY', 'AVAILABLE'),
(5, 10, 'THURSDAY', 'AVAILABLE'),
(5, 10, 'FRIDAY', 'AVAILABLE'),
(5, 10, 'SATURDAY', 'AVAILABLE'),
(5, 11, 'MONDAY', 'AVAILABLE'),
(5, 11, 'TUESDAY', 'AVAILABLE'),
(5, 11, 'WEDNESDAY', 'AVAILABLE'),
(5, 11, 'THURSDAY', 'AVAILABLE'),
(5, 11, 'FRIDAY', 'AVAILABLE'),
(5, 11, 'SATURDAY', 'AVAILABLE'),
(5, 12, 'MONDAY', 'AVAILABLE'),
(5, 12, 'TUESDAY', 'AVAILABLE'),
(5, 12, 'WEDNESDAY', 'AVAILABLE'),
(5, 12, 'THURSDAY', 'AVAILABLE'),
(5, 12, 'FRIDAY', 'AVAILABLE'),
(5, 12, 'SATURDAY', 'AVAILABLE'),
(7, 1, 'MONDAY', 'AVAILABLE'),
(7, 1, 'TUESDAY', 'AVAILABLE'),
(7, 1, 'WEDNESDAY', 'AVAILABLE'),
(7, 1, 'THURSDAY', 'AVAILABLE'),
(7, 1, 'FRIDAY', 'AVAILABLE'),
(7, 1, 'SATURDAY', 'AVAILABLE'),
(7, 2, 'MONDAY', 'AVAILABLE'),
(7, 2, 'TUESDAY', 'AVAILABLE'),
(7, 2, 'WEDNESDAY', 'AVAILABLE'),
(7, 2, 'THURSDAY', 'AVAILABLE'),
(7, 2, 'FRIDAY', 'AVAILABLE'),
(7, 2, 'SATURDAY', 'AVAILABLE'),
(7, 3, 'MONDAY', 'AVAILABLE'),
(7, 3, 'TUESDAY', 'AVAILABLE'),
(7, 3, 'WEDNESDAY', 'AVAILABLE'),
(7, 3, 'THURSDAY', 'AVAILABLE'),
(7, 3, 'FRIDAY', 'AVAILABLE'),
(7, 3, 'SATURDAY', 'AVAILABLE'),
(7, 4, 'MONDAY', 'AVAILABLE'),
(7, 4, 'TUESDAY', 'AVAILABLE'),
(7, 4, 'WEDNESDAY', 'AVAILABLE'),
(7, 4, 'THURSDAY', 'AVAILABLE'),
(7, 4, 'FRIDAY', 'AVAILABLE'),
(7, 4, 'SATURDAY', 'AVAILABLE'),
(7, 5, 'MONDAY', 'AVAILABLE'),
(7, 5, 'TUESDAY', 'AVAILABLE'),
(7, 5, 'WEDNESDAY', 'AVAILABLE'),
(7, 5, 'THURSDAY', 'AVAILABLE'),
(7, 5, 'FRIDAY', 'AVAILABLE'),
(7, 5, 'SATURDAY', 'AVAILABLE'),
(7, 6, 'MONDAY', 'AVAILABLE'),
(7, 6, 'TUESDAY', 'AVAILABLE'),
(7, 6, 'WEDNESDAY', 'AVAILABLE'),
(7, 6, 'THURSDAY', 'AVAILABLE'),
(7, 6, 'FRIDAY', 'AVAILABLE'),
(7, 6, 'SATURDAY', 'AVAILABLE'),
(7, 7, 'MONDAY', 'AVAILABLE'),
(7, 7, 'TUESDAY', 'AVAILABLE'),
(7, 7, 'WEDNESDAY', 'AVAILABLE'),
(7, 7, 'THURSDAY', 'AVAILABLE'),
(7, 7, 'FRIDAY', 'AVAILABLE'),
(7, 7, 'SATURDAY', 'AVAILABLE'),
(7, 8, 'MONDAY', 'AVAILABLE'),
(7, 8, 'TUESDAY', 'AVAILABLE'),
(7, 8, 'WEDNESDAY', 'AVAILABLE'),
(7, 8, 'THURSDAY', 'AVAILABLE'),
(7, 8, 'FRIDAY', 'AVAILABLE'),
(7, 8, 'SATURDAY', 'AVAILABLE'),
(7, 9, 'MONDAY', 'AVAILABLE'),
(7, 9, 'TUESDAY', 'AVAILABLE'),
(7, 9, 'WEDNESDAY', 'AVAILABLE'),
(7, 9, 'THURSDAY', 'AVAILABLE'),
(7, 9, 'FRIDAY', 'AVAILABLE'),
(7, 9, 'SATURDAY', 'AVAILABLE'),
(7, 10, 'MONDAY', 'AVAILABLE'),
(7, 10, 'TUESDAY', 'AVAILABLE'),
(7, 10, 'WEDNESDAY', 'AVAILABLE'),
(7, 10, 'THURSDAY', 'AVAILABLE'),
(7, 10, 'FRIDAY', 'AVAILABLE'),
(7, 10, 'SATURDAY', 'AVAILABLE'),
(7, 11, 'MONDAY', 'AVAILABLE'),
(7, 11, 'TUESDAY', 'AVAILABLE'),
(7, 11, 'WEDNESDAY', 'AVAILABLE'),
(7, 11, 'THURSDAY', 'AVAILABLE'),
(7, 11, 'FRIDAY', 'AVAILABLE'),
(7, 11, 'SATURDAY', 'AVAILABLE'),
(7, 12, 'MONDAY', 'AVAILABLE'),
(7, 12, 'TUESDAY', 'AVAILABLE'),
(7, 12, 'WEDNESDAY', 'AVAILABLE'),
(7, 12, 'THURSDAY', 'AVAILABLE'),
(7, 12, 'FRIDAY', 'AVAILABLE'),
(7, 12, 'SATURDAY', 'AVAILABLE'),
(7, 13, 'MONDAY', 'AVAILABLE'),
(7, 13, 'TUESDAY', 'AVAILABLE'),
(7, 13, 'WEDNESDAY', 'AVAILABLE'),
(7, 13, 'THURSDAY', 'AVAILABLE'),
(7, 13, 'FRIDAY', 'AVAILABLE'),
(7, 13, 'SATURDAY', 'AVAILABLE'),
(7, 14, 'MONDAY', 'AVAILABLE'),
(7, 14, 'TUESDAY', 'AVAILABLE'),
(7, 14, 'WEDNESDAY', 'AVAILABLE'),
(7, 14, 'THURSDAY', 'AVAILABLE'),
(7, 14, 'FRIDAY', 'AVAILABLE'),
(7, 14, 'SATURDAY', 'AVAILABLE'),
(7, 15, 'MONDAY', 'AVAILABLE'),
(7, 15, 'TUESDAY', 'AVAILABLE'),
(7, 15, 'WEDNESDAY', 'AVAILABLE'),
(7, 15, 'THURSDAY', 'AVAILABLE'),
(7, 15, 'FRIDAY', 'AVAILABLE'),
(7, 15, 'SATURDAY', 'AVAILABLE'),
(7, 16, 'MONDAY', 'AVAILABLE'),
(7, 16, 'TUESDAY', 'AVAILABLE'),
(7, 16, 'WEDNESDAY', 'AVAILABLE'),
(7, 16, 'THURSDAY', 'AVAILABLE'),
(7, 16, 'FRIDAY', 'AVAILABLE'),
(7, 16, 'SATURDAY', 'AVAILABLE');

-- --------------------------------------------------------

--
-- Table structure for table `classroom`
--

CREATE TABLE `classroom` (
  `CLASSROOM_ID` int(11) NOT NULL,
  `FLOOR_NUMBER` varchar(15) NOT NULL,
  `ROOM_NUMBER` varchar(15) NOT NULL,
  `CAPACITY` int(11) DEFAULT NULL,
  `START_TIME` time DEFAULT NULL,
  `END_TIME` time DEFAULT NULL,
  `CATEGORY` enum('LECTURE HALL','IT LAB','PHYSICS LAB','CHEMISTRY LAB','BIOLOGY LAB') DEFAULT 'LECTURE HALL'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `classroom`
--

INSERT INTO `classroom` (`CLASSROOM_ID`, `FLOOR_NUMBER`, `ROOM_NUMBER`, `CAPACITY`, `START_TIME`, `END_TIME`, `CATEGORY`) VALUES
(1, 'First Floor', 'IT Lab 02', 200, '07:00:00', '12:00:00', 'IT LAB'),
(2, 'First Floor', 'IT Lab 01', 60, '07:00:00', '12:00:00', 'IT LAB'),
(3, 'First Floor', 'E-Leaning Lab', 20, '07:00:00', '12:00:00', 'IT LAB'),
(4, 'First Floor', '108', 20, '07:00:00', '12:00:00', 'LECTURE HALL'),
(5, 'Second Floor', '008', 60, '07:00:00', '12:00:00', 'LECTURE HALL'),
(6, 'Fourth Floor', '401', 200, '07:00:00', '12:00:00', 'LECTURE HALL');

-- --------------------------------------------------------

--
-- Table structure for table `consists`
--

CREATE TABLE `consists` (
  `YEAR_NUMBER` int(11) NOT NULL,
  `PROGRAMME_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `consists`
--

INSERT INTO `consists` (`YEAR_NUMBER`, `PROGRAMME_ID`) VALUES
(1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `course`
--

CREATE TABLE `course` (
  `COURSE_ID` int(11) NOT NULL,
  `OPTIONAL_ID` int(11) DEFAULT NULL,
  `YEAR_NUMBER` int(11) DEFAULT NULL,
  `PROGRAMME_ID` int(11) DEFAULT NULL,
  `TYPE` enum('LECTURE','IT PRACTICAL','PHYSICS PRACTICAL','BIOLOGY PRACTICAL','CHEMISTRY PRACTICAL') DEFAULT NULL,
  `SEMESTER` enum('EVEN','ODD') NOT NULL,
  `LONG_NAME` varchar(63) NOT NULL,
  `SHORT_NAME` varchar(15) NOT NULL,
  `WEEKLY_LECTURES` int(11) NOT NULL DEFAULT 0,
  `ISPRACTICAL` tinyint(1) DEFAULT 0,
  `ISOPTIONAL` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `course`
--

INSERT INTO `course` (`COURSE_ID`, `OPTIONAL_ID`, `YEAR_NUMBER`, `PROGRAMME_ID`, `TYPE`, `SEMESTER`, `LONG_NAME`, `SHORT_NAME`, `WEEKLY_LECTURES`, `ISPRACTICAL`, `ISOPTIONAL`) VALUES
(24, NULL, 1, 1, 'LECTURE', 'ODD', 'Principles of Programming Languages using C', 'C Programming', 2, 0, 0),
(25, NULL, 1, 1, 'LECTURE', 'ODD', 'Microprocessor Architecture with 8085', 'Microprocessor', 2, 0, 0),
(26, NULL, 1, 1, 'IT PRACTICAL', 'ODD', 'Principles of Programming Languages using C and Microprocessor ', 'C and 8085 Prac', 1, 1, 0),
(27, NULL, 1, 1, 'LECTURE', 'ODD', 'Discrete Mathematics', 'Discrete Maths', 2, 0, 0),
(28, NULL, 1, 1, 'IT PRACTICAL', 'ODD', 'Numerical Computations using Scilab Practical', 'Scilab Practica', 2, 1, 0),
(29, NULL, 1, 1, 'LECTURE', 'ODD', 'Effective Communication Skills I', 'Communication I', 2, 0, 0),
(30, NULL, 1, 1, 'LECTURE', 'ODD', 'Environmental Study for Sustainable IT I', 'Sustainable IT ', 2, 0, 0),
(31, NULL, 1, 1, 'LECTURE', 'ODD', 'Ancient Vedic Mathematics', 'Vedic Mathemati', 2, 0, 0),
(32, NULL, 1, 1, 'LECTURE', 'EVEN', 'Object Oriented Programming using C++', 'C++ Programming', 2, 0, 0),
(33, NULL, 1, 1, 'LECTURE', 'EVEN', 'Database Management Systems', 'DBMS', 2, 0, 0),
(34, NULL, 1, 1, 'IT PRACTICAL', 'EVEN', 'Object Oriented Programming using C++ and Database Management S', 'C++ and DBMS Pr', 1, 1, 0),
(35, NULL, 1, 1, 'LECTURE', 'ODD', 'BASICS OF DATA SCIENCE', 'D.S.', 2, 0, 0),
(36, NULL, 1, 1, 'LECTURE', 'ODD', 'FUNDAMENTALS OF DIGITAL ELECTRONICS', 'DE', 2, 0, 0),
(37, NULL, 1, 1, 'IT PRACTICAL', 'EVEN', 'Fundamentals of Digital Electronics Practical', 'Digital Electro', 2, 1, 0),
(38, NULL, 1, 1, 'LECTURE', 'EVEN', 'Effective Communication Skills II', 'Communication I', 2, 0, 0),
(39, NULL, 1, 1, 'LECTURE', 'EVEN', 'Environmental Study for Sustainable IT II', 'Sustainable IT ', 2, 0, 0),
(40, NULL, 1, 1, NULL, 'ODD', 'PRACTICAL PRINCIPLES OF PROGRAMMING LANGUAGE USING C', 'C PROGRAMMING P', 1, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `department`
--

CREATE TABLE `department` (
  `DEPARTMENT_ID` int(11) NOT NULL,
  `LONG_NAME` varchar(63) NOT NULL,
  `SHORT_NAME` varchar(31) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `department`
--

INSERT INTO `department` (`DEPARTMENT_ID`, `LONG_NAME`, `SHORT_NAME`) VALUES
(1, 'INFORMATION TECHNOLOGY', 'I.T.');

-- --------------------------------------------------------

--
-- Table structure for table `division`
--

CREATE TABLE `division` (
  `DIVISION_ID` int(11) NOT NULL,
  `YEAR_NUMBER` int(11) DEFAULT NULL,
  `PROGRAMME_ID` int(11) DEFAULT NULL,
  `NAME` char(1) NOT NULL,
  `STUDENT_COUNT` int(11) DEFAULT NULL,
  `START_TIME_ID` int(11) DEFAULT NULL,
  `END_TIME_ID` int(11) DEFAULT NULL,
  `CLASSROOM_ID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `division`
--

INSERT INTO `division` (`DIVISION_ID`, `YEAR_NUMBER`, `PROGRAMME_ID`, `NAME`, `STUDENT_COUNT`, `START_TIME_ID`, `END_TIME_ID`, `CLASSROOM_ID`) VALUES
(1, 1, 1, 'A', 60, NULL, NULL, NULL),
(4, 1, 1, 'B', 60, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `opted_by`
--

CREATE TABLE `opted_by` (
  `COURSE_ID` int(11) NOT NULL,
  `DIVISION_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `opted_by`
--

INSERT INTO `opted_by` (`COURSE_ID`, `DIVISION_ID`) VALUES
(4, 1),
(5, 4),
(15, 1),
(16, 1),
(21, 1),
(22, 1);

-- --------------------------------------------------------

--
-- Table structure for table `programme`
--

CREATE TABLE `programme` (
  `PROGRAMME_ID` int(11) NOT NULL,
  `DEPARTMENT_ID` int(11) DEFAULT NULL,
  `LONG_NAME` varchar(63) NOT NULL,
  `SHORT_NAME` varchar(15) NOT NULL,
  `DIVISION_COUNT` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `programme`
--

INSERT INTO `programme` (`PROGRAMME_ID`, `DEPARTMENT_ID`, `LONG_NAME`, `SHORT_NAME`, `DIVISION_COUNT`) VALUES
(1, 1, 'BACHELOR OF SCIENCE IN INFORMATION TECHNLOGY', 'B.SC.I.T.', 2);

-- --------------------------------------------------------

--
-- Table structure for table `teacher`
--

CREATE TABLE `teacher` (
  `TEACHER_ID` int(11) NOT NULL,
  `DEPARTMENT_ID` int(11) NOT NULL,
  `FIRST_NAME` varchar(15) NOT NULL,
  `LAST_NAME` varchar(15) NOT NULL,
  `ISPARTTIME` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `teacher`
--

INSERT INTO `teacher` (`TEACHER_ID`, `DEPARTMENT_ID`, `FIRST_NAME`, `LAST_NAME`, `ISPARTTIME`) VALUES
(1, 1, 'POURNIMA', 'BHANGALE', 0),
(2, 1, 'RAKHEE', 'RANE', 0),
(3, 1, 'NANDA', 'RUPNAR', 0),
(4, 1, 'VANDANA', 'NARWADE', 0),
(5, 1, 'MOHIN', 'BHOLE', 0),
(7, 1, 'NAMRATA', 'JADHAV', 0);

-- --------------------------------------------------------

--
-- Table structure for table `teaches`
--

CREATE TABLE `teaches` (
  `WORKLOAD_ID` int(11) NOT NULL,
  `TEACHER_ID` int(11) DEFAULT NULL,
  `COURSE_ID` int(11) DEFAULT NULL,
  `DIVISION_ID` int(11) DEFAULT NULL,
  `LECTURE_COUNT` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `teaches`
--

INSERT INTO `teaches` (`WORKLOAD_ID`, `TEACHER_ID`, `COURSE_ID`, `DIVISION_ID`, `LECTURE_COUNT`) VALUES
(14, 2, 9, 1, 2),
(15, 2, 9, 4, 2),
(28, 3, 10, 1, 2),
(29, 3, 10, 4, 2),
(30, 3, 6, 1, 2),
(31, 3, 6, 4, 2),
(32, 2, 30, 1, 2),
(33, 2, 30, 4, 2),
(35, 2, 24, 4, 2),
(38, 3, 31, 1, 2),
(39, 3, 31, 4, 2),
(40, 3, 27, 1, 2),
(41, 3, 27, 4, 2),
(44, 4, 25, 1, 2),
(45, 4, 25, 4, 2),
(46, 4, 26, 1, 1),
(47, 4, 26, 4, 1),
(48, 2, 26, 1, 1),
(49, 2, 26, 4, 1),
(52, 3, 28, 1, 1),
(53, 3, 28, 4, 1),
(54, 1, 33, 1, 2),
(55, 1, 33, 4, 2);

-- --------------------------------------------------------

--
-- Table structure for table `timeslot`
--

CREATE TABLE `timeslot` (
  `SLOT_ID` int(11) NOT NULL,
  `START_TIME` time NOT NULL,
  `END_TIME` time NOT NULL,
  `SLOT_TYPE` enum('BREAK','LECTURE','PRACTICAL') NOT NULL DEFAULT 'LECTURE'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `timeslot`
--

INSERT INTO `timeslot` (`SLOT_ID`, `START_TIME`, `END_TIME`, `SLOT_TYPE`) VALUES
(1, '07:00:00', '08:00:00', 'LECTURE'),
(2, '07:00:00', '09:00:00', 'PRACTICAL'),
(3, '08:00:00', '09:00:00', 'LECTURE'),
(4, '09:00:00', '09:15:00', 'BREAK'),
(5, '09:15:00', '09:30:00', 'BREAK'),
(6, '09:30:00', '11:30:00', 'PRACTICAL'),
(7, '09:30:00', '10:30:00', 'LECTURE'),
(8, '10:30:00', '11:30:00', 'LECTURE'),
(9, '11:30:00', '11:45:00', 'BREAK'),
(10, '11:45:00', '12:45:00', 'LECTURE'),
(11, '12:45:00', '13:45:00', 'LECTURE'),
(12, '11:45:00', '13:45:00', 'PRACTICAL'),
(13, '13:45:00', '14:00:00', 'BREAK'),
(14, '14:00:00', '15:00:00', 'LECTURE'),
(15, '15:00:00', '16:00:00', 'LECTURE'),
(16, '14:00:00', '16:00:00', 'PRACTICAL');

-- --------------------------------------------------------

--
-- Table structure for table `timetable`
--

CREATE TABLE `timetable` (
  `ALLOTMENT_ID` int(11) NOT NULL,
  `COURSE_ID` int(11) DEFAULT NULL,
  `DIVISION_ID` int(11) DEFAULT NULL,
  `CLASSROOM_ID` int(11) DEFAULT NULL,
  `SLOT_ID` int(11) DEFAULT NULL,
  `WEEKDAY` enum('MONDAY','TUESDAY','WEDNESDAY','THURSDAY','FRIDAY','SATURDAY') DEFAULT NULL,
  `TEACHER_ID` int(11) DEFAULT NULL,
  `ACADEMIC_YEAR` varchar(7) NOT NULL,
  `SEMESTER` enum('EVEN','ODD') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `timetable`
--

INSERT INTO `timetable` (`ALLOTMENT_ID`, `COURSE_ID`, `DIVISION_ID`, `CLASSROOM_ID`, `SLOT_ID`, `WEEKDAY`, `TEACHER_ID`, `ACADEMIC_YEAR`, `SEMESTER`) VALUES
(14, 24, 1, 1, 8, 'MONDAY', 2, '2026-27', 'ODD'),
(39, 24, 1, 1, 10, 'FRIDAY', 2, '2026-27', 'ODD'),
(17, 24, 4, 1, 3, 'MONDAY', 2, '2026-27', 'ODD'),
(24, 24, 4, 2, 10, 'TUESDAY', 2, '2026-27', 'ODD'),
(20, 25, 1, 1, 8, 'TUESDAY', 4, '2026-27', 'ODD'),
(38, 25, 1, 1, 8, 'FRIDAY', 4, '2026-27', 'ODD'),
(27, 25, 1, 1, 10, 'WEDNESDAY', 4, '2026-27', 'ODD'),
(25, 25, 1, 5, 7, 'WEDNESDAY', 4, '2026-27', 'ODD'),
(23, 25, 4, 1, 3, 'TUESDAY', 4, '2026-27', 'ODD'),
(29, 25, 4, 1, 3, 'WEDNESDAY', 4, '2026-27', 'ODD'),
(41, 25, 4, 1, 3, 'FRIDAY', 4, '2026-27', 'ODD'),
(47, 25, 4, 1, 3, 'SATURDAY', 4, '2026-27', 'ODD'),
(1, 26, 1, 2, 2, 'MONDAY', 4, '2026-27', 'ODD'),
(3, 26, 1, 2, 2, 'TUESDAY', 2, '2026-27', 'ODD'),
(7, 26, 1, 2, 2, 'THURSDAY', 4, '2026-27', 'ODD'),
(9, 26, 1, 2, 2, 'FRIDAY', 2, '2026-27', 'ODD'),
(2, 26, 4, 2, 6, 'MONDAY', 4, '2026-27', 'ODD'),
(4, 26, 4, 2, 6, 'TUESDAY', 2, '2026-27', 'ODD'),
(8, 26, 4, 2, 6, 'THURSDAY', 4, '2026-27', 'ODD'),
(10, 26, 4, 2, 6, 'FRIDAY', 2, '2026-27', 'ODD'),
(13, 27, 1, 5, 7, 'MONDAY', 3, '2026-27', 'ODD'),
(37, 27, 1, 5, 7, 'FRIDAY', 3, '2026-27', 'ODD'),
(44, 27, 1, 5, 10, 'SATURDAY', 3, '2026-27', 'ODD'),
(45, 27, 1, 5, 14, 'SATURDAY', 3, '2026-27', 'ODD'),
(30, 27, 4, 2, 10, 'WEDNESDAY', 3, '2026-27', 'ODD'),
(48, 27, 4, 2, 14, 'MONDAY', 3, '2026-27', 'ODD'),
(34, 27, 4, 5, 1, 'THURSDAY', 3, '2026-27', 'ODD'),
(40, 27, 4, 5, 1, 'FRIDAY', 3, '2026-27', 'ODD'),
(5, 28, 1, 2, 2, 'WEDNESDAY', 3, '2026-27', 'ODD'),
(11, 28, 1, 2, 2, 'SATURDAY', 3, '2026-27', 'ODD'),
(6, 28, 4, 2, 6, 'WEDNESDAY', 3, '2026-27', 'ODD'),
(12, 28, 4, 2, 6, 'SATURDAY', 3, '2026-27', 'ODD'),
(26, 30, 1, 1, 8, 'WEDNESDAY', 2, '2026-27', 'ODD'),
(32, 30, 1, 1, 8, 'THURSDAY', 2, '2026-27', 'ODD'),
(33, 30, 1, 1, 10, 'THURSDAY', 2, '2026-27', 'ODD'),
(43, 30, 1, 5, 7, 'SATURDAY', 2, '2026-27', 'ODD'),
(35, 30, 4, 1, 3, 'THURSDAY', 2, '2026-27', 'ODD'),
(18, 30, 4, 2, 10, 'MONDAY', 2, '2026-27', 'ODD'),
(28, 30, 4, 5, 1, 'WEDNESDAY', 2, '2026-27', 'ODD'),
(46, 30, 4, 5, 1, 'SATURDAY', 2, '2026-27', 'ODD'),
(15, 31, 1, 1, 10, 'MONDAY', 3, '2026-27', 'ODD'),
(21, 31, 1, 1, 10, 'TUESDAY', 3, '2026-27', 'ODD'),
(19, 31, 1, 5, 7, 'TUESDAY', 3, '2026-27', 'ODD'),
(31, 31, 1, 5, 7, 'THURSDAY', 3, '2026-27', 'ODD'),
(36, 31, 4, 2, 10, 'THURSDAY', 3, '2026-27', 'ODD'),
(42, 31, 4, 2, 10, 'FRIDAY', 3, '2026-27', 'ODD'),
(16, 31, 4, 5, 1, 'MONDAY', 3, '2026-27', 'ODD'),
(22, 31, 4, 5, 1, 'TUESDAY', 3, '2026-27', 'ODD');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `USERNAME` varchar(31) NOT NULL,
  `PASSWORD` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`USERNAME`, `PASSWORD`) VALUES
('TC', '$2y$10$C1D.XFTBxcy2bsq35VcQDOqqD0tkYe2zLdwzZxNyMWfv9EVdbRH62');

-- --------------------------------------------------------

--
-- Table structure for table `weekday`
--

CREATE TABLE `weekday` (
  `WEEKDAY` enum('MONDAY','TUESDAY','WEDNESDAY','THURSDAY','FRIDAY','SATURDAY') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `weekday`
--

INSERT INTO `weekday` (`WEEKDAY`) VALUES
('MONDAY'),
('TUESDAY'),
('WEDNESDAY'),
('THURSDAY'),
('FRIDAY'),
('SATURDAY');

-- --------------------------------------------------------

--
-- Table structure for table `year`
--

CREATE TABLE `year` (
  `YEAR_NUMBER` int(11) NOT NULL,
  `YEAR_NAME` enum('FIRST YEAR','SECOND YEAR','THIRD YEAR','FOURTH YEAR','FIFTH YEAR') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `year`
--

INSERT INTO `year` (`YEAR_NUMBER`, `YEAR_NAME`) VALUES
(1, 'FIRST YEAR'),
(2, 'SECOND YEAR'),
(3, 'THIRD YEAR'),
(4, 'FOURTH YEAR'),
(5, 'FIFTH YEAR');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `availability`
--
ALTER TABLE `availability`
  ADD PRIMARY KEY (`TEACHER_ID`,`SLOT_ID`,`WEEKDAY`),
  ADD UNIQUE KEY `TEACHER_ID` (`TEACHER_ID`,`SLOT_ID`,`WEEKDAY`),
  ADD KEY `fk_slotId_avlbtTbl` (`SLOT_ID`),
  ADD KEY `fk_wkdy_avlbtTbl` (`WEEKDAY`);

--
-- Indexes for table `classroom`
--
ALTER TABLE `classroom`
  ADD PRIMARY KEY (`CLASSROOM_ID`),
  ADD UNIQUE KEY `ROOM_NUMBER` (`ROOM_NUMBER`);

--
-- Indexes for table `consists`
--
ALTER TABLE `consists`
  ADD PRIMARY KEY (`YEAR_NUMBER`,`PROGRAMME_ID`),
  ADD KEY `fk_pgrmId_cnstTbl` (`PROGRAMME_ID`);

--
-- Indexes for table `course`
--
ALTER TABLE `course`
  ADD PRIMARY KEY (`COURSE_ID`),
  ADD KEY `fk_pgrmId_crseTbl` (`PROGRAMME_ID`),
  ADD KEY `fk_yearId_crseTbl` (`YEAR_NUMBER`),
  ADD KEY `fk_opnlId_crseTbl` (`OPTIONAL_ID`);

--
-- Indexes for table `department`
--
ALTER TABLE `department`
  ADD PRIMARY KEY (`DEPARTMENT_ID`);

--
-- Indexes for table `division`
--
ALTER TABLE `division`
  ADD PRIMARY KEY (`DIVISION_ID`),
  ADD UNIQUE KEY `NAME` (`NAME`,`YEAR_NUMBER`,`PROGRAMME_ID`),
  ADD KEY `fk_yrId_dvsnTbl` (`YEAR_NUMBER`),
  ADD KEY `fk_pgrmId_dvsnTbl` (`PROGRAMME_ID`),
  ADD KEY `fk_clsrmId_dvsnTbl` (`CLASSROOM_ID`),
  ADD KEY `fk_sTmeId_dvsnTbl` (`START_TIME_ID`),
  ADD KEY `fk_eTmeId_dvsnTbl` (`END_TIME_ID`);

--
-- Indexes for table `opted_by`
--
ALTER TABLE `opted_by`
  ADD PRIMARY KEY (`COURSE_ID`,`DIVISION_ID`),
  ADD KEY `fk_dvsnId_optdByTbl` (`DIVISION_ID`);

--
-- Indexes for table `programme`
--
ALTER TABLE `programme`
  ADD PRIMARY KEY (`PROGRAMME_ID`),
  ADD KEY `fk_deptId_pgrmTbl` (`DEPARTMENT_ID`);

--
-- Indexes for table `teacher`
--
ALTER TABLE `teacher`
  ADD PRIMARY KEY (`TEACHER_ID`),
  ADD KEY `fk_deptId_tchrTbl` (`DEPARTMENT_ID`);

--
-- Indexes for table `teaches`
--
ALTER TABLE `teaches`
  ADD PRIMARY KEY (`WORKLOAD_ID`),
  ADD UNIQUE KEY `TEACHER_ID` (`TEACHER_ID`,`COURSE_ID`,`DIVISION_ID`),
  ADD KEY `fk_crseId_tchsTbl` (`COURSE_ID`),
  ADD KEY `fk_dvsn_tchsTbl` (`DIVISION_ID`);

--
-- Indexes for table `timeslot`
--
ALTER TABLE `timeslot`
  ADD PRIMARY KEY (`SLOT_ID`);

--
-- Indexes for table `timetable`
--
ALTER TABLE `timetable`
  ADD PRIMARY KEY (`ALLOTMENT_ID`),
  ADD UNIQUE KEY `COURSE_ID` (`COURSE_ID`,`DIVISION_ID`,`CLASSROOM_ID`,`SLOT_ID`,`WEEKDAY`,`TEACHER_ID`,`ACADEMIC_YEAR`,`SEMESTER`),
  ADD KEY `fk_dvsnId_tTbl` (`DIVISION_ID`),
  ADD KEY `fk_clsrmId_tTbl` (`CLASSROOM_ID`),
  ADD KEY `fk_slotId_tTbl` (`SLOT_ID`),
  ADD KEY `fk_wkdy_tTbl` (`WEEKDAY`),
  ADD KEY `fk_tchrId_tTbl` (`TEACHER_ID`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`USERNAME`);

--
-- Indexes for table `weekday`
--
ALTER TABLE `weekday`
  ADD PRIMARY KEY (`WEEKDAY`);

--
-- Indexes for table `year`
--
ALTER TABLE `year`
  ADD PRIMARY KEY (`YEAR_NUMBER`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `classroom`
--
ALTER TABLE `classroom`
  MODIFY `CLASSROOM_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `course`
--
ALTER TABLE `course`
  MODIFY `COURSE_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `department`
--
ALTER TABLE `department`
  MODIFY `DEPARTMENT_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `division`
--
ALTER TABLE `division`
  MODIFY `DIVISION_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `programme`
--
ALTER TABLE `programme`
  MODIFY `PROGRAMME_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `teacher`
--
ALTER TABLE `teacher`
  MODIFY `TEACHER_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `teaches`
--
ALTER TABLE `teaches`
  MODIFY `WORKLOAD_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `timeslot`
--
ALTER TABLE `timeslot`
  MODIFY `SLOT_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `timetable`
--
ALTER TABLE `timetable`
  MODIFY `ALLOTMENT_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `availability`
--
ALTER TABLE `availability`
  ADD CONSTRAINT `fk_slotId_avlbtTbl` FOREIGN KEY (`SLOT_ID`) REFERENCES `timeslot` (`SLOT_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tchrId_avlbtTbl` FOREIGN KEY (`TEACHER_ID`) REFERENCES `teacher` (`TEACHER_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_wkdy_avlbtTbl` FOREIGN KEY (`WEEKDAY`) REFERENCES `weekday` (`WEEKDAY`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `consists`
--
ALTER TABLE `consists`
  ADD CONSTRAINT `fk_pgrmId_cnstTbl` FOREIGN KEY (`PROGRAMME_ID`) REFERENCES `programme` (`PROGRAMME_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_yearId_cnstTbl` FOREIGN KEY (`YEAR_NUMBER`) REFERENCES `year` (`YEAR_NUMBER`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `course`
--
ALTER TABLE `course`
  ADD CONSTRAINT `fk_opnlId_crseTbl` FOREIGN KEY (`OPTIONAL_ID`) REFERENCES `course` (`COURSE_ID`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pgrmId_crseTbl` FOREIGN KEY (`PROGRAMME_ID`) REFERENCES `programme` (`PROGRAMME_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_yearId_crseTbl` FOREIGN KEY (`YEAR_NUMBER`) REFERENCES `year` (`YEAR_NUMBER`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `division`
--
ALTER TABLE `division`
  ADD CONSTRAINT `fk_clsrmId_dvsnTbl` FOREIGN KEY (`CLASSROOM_ID`) REFERENCES `classroom` (`CLASSROOM_ID`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_eTmeId_dvsnTbl` FOREIGN KEY (`END_TIME_ID`) REFERENCES `timeslot` (`SLOT_ID`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pgrmId_dvsnTbl` FOREIGN KEY (`PROGRAMME_ID`) REFERENCES `programme` (`PROGRAMME_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sTmeId_dvsnTbl` FOREIGN KEY (`START_TIME_ID`) REFERENCES `timeslot` (`SLOT_ID`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_yrId_dvsnTbl` FOREIGN KEY (`YEAR_NUMBER`) REFERENCES `year` (`YEAR_NUMBER`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `opted_by`
--
ALTER TABLE `opted_by`
  ADD CONSTRAINT `fk_crseId_optdByTbl` FOREIGN KEY (`COURSE_ID`) REFERENCES `course` (`COURSE_ID`),
  ADD CONSTRAINT `fk_dvsnId_optdByTbl` FOREIGN KEY (`DIVISION_ID`) REFERENCES `division` (`DIVISION_ID`);

--
-- Constraints for table `programme`
--
ALTER TABLE `programme`
  ADD CONSTRAINT `fk_deptId_pgrmTbl` FOREIGN KEY (`DEPARTMENT_ID`) REFERENCES `department` (`DEPARTMENT_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `teacher`
--
ALTER TABLE `teacher`
  ADD CONSTRAINT `fk_deptId_tchrTbl` FOREIGN KEY (`DEPARTMENT_ID`) REFERENCES `department` (`DEPARTMENT_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `teaches`
--
ALTER TABLE `teaches`
  ADD CONSTRAINT `fk_crseId_tchsTbl` FOREIGN KEY (`COURSE_ID`) REFERENCES `course` (`COURSE_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dvsn_tchsTbl` FOREIGN KEY (`DIVISION_ID`) REFERENCES `division` (`DIVISION_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tchrId_tchsTbl` FOREIGN KEY (`TEACHER_ID`) REFERENCES `teacher` (`TEACHER_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `timetable`
--
ALTER TABLE `timetable`
  ADD CONSTRAINT `fk_clsrmId_tTbl` FOREIGN KEY (`CLASSROOM_ID`) REFERENCES `classroom` (`CLASSROOM_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_crseId_tTbl` FOREIGN KEY (`COURSE_ID`) REFERENCES `course` (`COURSE_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dvsnId_tTbl` FOREIGN KEY (`DIVISION_ID`) REFERENCES `division` (`DIVISION_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_slotId_tTbl` FOREIGN KEY (`SLOT_ID`) REFERENCES `timeslot` (`SLOT_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tchrId_tTbl` FOREIGN KEY (`TEACHER_ID`) REFERENCES `teacher` (`TEACHER_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_wkdy_tTbl` FOREIGN KEY (`WEEKDAY`) REFERENCES `weekday` (`WEEKDAY`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
