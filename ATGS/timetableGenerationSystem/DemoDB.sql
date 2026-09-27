-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 27, 2026 at 02:42 PM
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
(29, 39, 1, 1, 'LECTURE', 'ODD', 'EFFECTIVE COMMUNICATION SKILLS I', 'COMMUNICATION I', 2, 0, 1),
(30, NULL, 1, 1, 'LECTURE', 'ODD', 'Environmental Study for Sustainable IT I', 'Sustainable IT ', 2, 0, 0),
(31, NULL, 1, 1, 'LECTURE', 'ODD', 'Ancient Vedic Mathematics', 'Vedic Mathemati', 2, 0, 0),
(32, NULL, 1, 1, 'LECTURE', 'ODD', 'OBJECT ORIENTED PROGRAMMING USING C PLUSPLUS', 'C PROGRAMMING', 2, 0, 0),
(33, NULL, 1, 1, 'LECTURE', 'EVEN', 'Database Management Systems', 'DBMS', 2, 0, 0),
(34, NULL, 1, 1, 'IT PRACTICAL', 'EVEN', 'Object Oriented Programming using C++ and Database Management S', 'C++ and DBMS Pr', 1, 1, 0),
(35, NULL, 1, 1, 'LECTURE', 'ODD', 'BASICS OF DATA SCIENCE', 'D.S.', 2, 0, 0),
(36, NULL, 1, 1, 'LECTURE', 'ODD', 'FUNDAMENTALS OF DIGITAL ELECTRONICS', 'DE', 2, 0, 0),
(37, NULL, 1, 1, 'IT PRACTICAL', 'EVEN', 'Fundamentals of Digital Electronics Practical', 'Digital Electro', 1, 1, 0),
(38, NULL, 1, 1, 'LECTURE', 'EVEN', 'Effective Communication Skills II', 'Communication I', 2, 0, 0),
(39, 29, 1, 1, 'LECTURE', 'ODD', 'ENVIRONMENTAL STUDY FOR SUSTAINABLE IT II', 'SUSTAINABLE IT ', 2, 0, 1);

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
  MODIFY `COURSE_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `course`
--
ALTER TABLE `course`
  ADD CONSTRAINT `fk_opnlId_crseTbl` FOREIGN KEY (`OPTIONAL_ID`) REFERENCES `course` (`COURSE_ID`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pgrmId_crseTbl` FOREIGN KEY (`PROGRAMME_ID`) REFERENCES `programme` (`PROGRAMME_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_yearId_crseTbl` FOREIGN KEY (`YEAR_NUMBER`) REFERENCES `year` (`YEAR_NUMBER`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
