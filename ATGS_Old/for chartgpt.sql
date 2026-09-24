-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 15, 2026 at 07:46 AM
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
-- Table structure for table `course`
--

CREATE TABLE `course` (
  `COURSE_ID` int(11) NOT NULL,
  `OPTIONAL_ID` int(11) DEFAULT NULL,
  `YEAR_NUMBER` int(11) DEFAULT NULL,
  `PROGRAMME_ID` int(11) DEFAULT NULL,
  `SEMESTER` enum('EVEN','ODD') NOT NULL,
  `LONG_NAME` varchar(63) NOT NULL,
  `SHORT_NAME` varchar(15) NOT NULL,
  `WEEKLY_LECTURES` int(11) NOT NULL DEFAULT 0,
  `ISPRACTICAL` tinyint(1) DEFAULT 0,
  `ISOPTIONAL` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course`
--

INSERT INTO `course` (`COURSE_ID`, `OPTIONAL_ID`, `YEAR_NUMBER`, `PROGRAMME_ID`, `SEMESTER`, `LONG_NAME`, `SHORT_NAME`, `WEEKLY_LECTURES`, `ISPRACTICAL`, `ISOPTIONAL`) VALUES
(5, NULL, 2, 1, 'EVEN', 'MARATHI', 'MARATHI', 1, 0, 1),
(6, NULL, 2, 1, 'EVEN', 'HINDI', 'HINDI', 1, 0, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `course`
--
ALTER TABLE `course`
  ADD PRIMARY KEY (`COURSE_ID`),
  ADD KEY `fk_pgrmId_crseTbl` (`PROGRAMME_ID`),
  ADD KEY `fk_yearId_crseTbl` (`YEAR_NUMBER`),
  ADD KEY `fk_opnlId_crseTbl` (`OPTIONAL_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `course`
--
ALTER TABLE `course`
  MODIFY `COURSE_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `course`
--
ALTER TABLE `course`
  ADD CONSTRAINT `fk_opnlId_crseTbl` FOREIGN KEY (`OPTIONAL_ID`) REFERENCES `course` (`COURSE_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pgrmId_crseTbl` FOREIGN KEY (`PROGRAMME_ID`) REFERENCES `programme` (`PROGRAMME_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_yearId_crseTbl` FOREIGN KEY (`YEAR_NUMBER`) REFERENCES `year` (`YEAR_NUMBER`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
