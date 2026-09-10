-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 10, 2026 at 07:26 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET FOREIGN_KEY_CHECKS=0;
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

--
-- Dumping data for table `consists`
--

INSERT INTO `consists` (`YEAR_NUMBER`, `PROGRAMME_ID`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(2, 1),
(2, 2),
(2, 3),
(2, 4),
(3, 1),
(3, 2),
(3, 4);

--
-- Dumping data for table `course`
--

INSERT INTO `course` (`COURSE_ID`, `OPTIONAL_ID`, `YEAR_NUMBER`, `PROGRAMME_ID`, `SEMESTER`, `LONG_NAME`, `SHORT_NAME`, `WEEKLY_LECTURES`, `ISPRACTICAL`, `ISOPTIONAL`) VALUES
(1, NULL, 1, 2, 'ODD', 'PRINCIPLE OF PROGRAMMING LANGUAGE', 'PPLC', 4, 0, 0),
(2, NULL, 2, 2, 'ODD', 'HINDI', 'HINDI', 1, 0, 1),
(3, NULL, 2, 2, 'ODD', 'MARATHI', 'MARATHI', 1, 0, 1),
(4, 5, 2, 2, 'EVEN', 'HINDI', 'HINDI', 1, 0, 1),
(5, 4, 2, 2, 'EVEN', 'MARATHI', 'MARATHI', 1, 0, 1),
(6, 7, 3, 4, 'ODD', 'P. MS OPTIONAL ONE', 'P. MS OPTIONAL ', 1, 0, 1),
(7, 6, 3, 4, 'ODD', 'P. MS OPTIONAL TWO', 'P. MS OPTIONAL ', 1, 0, 1);

--
-- Dumping data for table `department`
--

INSERT INTO `department` (`DEPARTMENT_ID`, `USERNAME`, `LONG_NAME`, `SHORT_NAME`) VALUES
(1, NULL, 'INFORMATION TECHNOLOGY', 'I.T.'),
(2, NULL, 'BANKING AND INSURANCE', 'B.N.I.'),
(3, NULL, 'MANAGEMENT STUDIES', 'B.M.S.');

--
-- Dumping data for table `division`
--

INSERT INTO `division` (`DIVISION_ID`, `YEAR_NUMBER`, `PROGRAMME_ID`, `NAME`, `STUDENT_COUNT`, `START_TIME_ID`, `END_TIME_ID`, `CLASSROOM_ID`) VALUES
(1, 1, 1, 'A', NULL, NULL, NULL, NULL),
(2, 1, 1, 'B', NULL, NULL, NULL, NULL),
(3, 2, 1, 'A', NULL, NULL, NULL, NULL),
(4, 2, 1, 'B', NULL, NULL, NULL, NULL),
(5, 3, 1, 'A', NULL, NULL, NULL, NULL),
(6, 3, 1, 'B', NULL, NULL, NULL, NULL),
(7, 1, 2, 'A', NULL, NULL, NULL, NULL),
(8, 2, 2, 'A', NULL, NULL, NULL, NULL),
(9, 3, 2, 'A', NULL, NULL, NULL, NULL),
(10, 1, 3, 'A', NULL, NULL, NULL, NULL),
(11, 2, 3, 'A', 50, 4, NULL, 1),
(12, 1, 4, 'A', NULL, NULL, NULL, NULL),
(13, 2, 4, 'A', NULL, NULL, NULL, NULL),
(14, 3, 4, 'A', NULL, NULL, NULL, NULL),
(15, 1, 2, 'B', NULL, NULL, NULL, NULL),
(16, 1, 2, 'C', NULL, NULL, NULL, NULL),
(17, 2, 2, 'B', NULL, NULL, NULL, NULL),
(18, 2, 2, 'C', NULL, NULL, NULL, NULL),
(19, 3, 2, 'B', NULL, NULL, NULL, NULL),
(20, 3, 2, 'C', NULL, NULL, NULL, NULL);

--
-- Dumping data for table `opted_by`
--

INSERT INTO `opted_by` (`COURSE_ID`, `DIVISION_ID`) VALUES
(4, 8),
(5, 17),
(6, 14),
(7, 14);

--
-- Dumping data for table `programme`
--

INSERT INTO `programme` (`PROGRAMME_ID`, `DEPARTMENT_ID`, `LONG_NAME`, `SHORT_NAME`, `DIVISION_COUNT`) VALUES
(1, 2, 'PROGRAM OF BNI', 'P. BNI', 6),
(2, 1, 'BACHELOR OF SCIENCE IN INFORMATION TECHNOLOGY', 'B.SC.I.T.', 9),
(3, 1, 'MASTERS OF SCIENCE IN INFORMATION TECHNOLOGY', 'M.SC.I.T.', 2),
(4, 3, 'PROGRAM OF MS', 'P. MS', 3);

--
-- Dumping data for table `teacher`
--

INSERT INTO `teacher` (`TEACHER_ID`, `DEPARTMENT_ID`, `FIRST_NAME`, `LAST_NAME`, `ISPARTTIME`) VALUES
(1, 3, 'BNI', 'TEACHERONE', 1),
(2, 2, 'BT', 'TEACHERTWO', 0),
(3, 1, 'POURNIMA', 'BHANGALE', 0);

--
-- Dumping data for table `teaches`
--

INSERT INTO `teaches` (`WORKLOAD_ID`, `TEACHER_ID`, `COURSE_ID`, `DIVISION_ID`, `LECTURE_COUNT`) VALUES
(6, 3, 4, 8, 4),
(7, 3, 4, 17, 4),
(8, 3, 4, 18, 4);

--
-- Dumping data for table `timeslot`
--

INSERT INTO `timeslot` (`SLOT_ID`, `START_TIME`, `END_TIME`, `SLOT_TYPE`) VALUES
(1, '07:00:00', '08:00:00', 'LECTURE'),
(2, '08:00:00', '10:00:00', 'PRACTICAL'),
(3, '10:00:00', '11:00:00', 'LECTURE'),
(4, '08:00:00', '09:00:00', 'LECTURE');
SET FOREIGN_KEY_CHECKS=1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
