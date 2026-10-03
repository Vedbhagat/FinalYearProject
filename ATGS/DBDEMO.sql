-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 03, 2026 at 08:00 AM
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

--
-- Dumping data for table `availability`
--

INSERT INTO `availability` (`TEACHER_ID`, `SLOT_ID`, `WEEKDAY`, `STATUS`) VALUES
(4, 1, 'MONDAY', 'AVAILABLE'),
(4, 1, 'TUESDAY', 'AVAILABLE'),
(4, 1, 'WEDNESDAY', 'AVAILABLE'),
(4, 1, 'THURSDAY', 'AVAILABLE'),
(4, 1, 'FRIDAY', 'AVAILABLE'),
(4, 1, 'SATURDAY', 'AVAILABLE'),
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
(5, 1, 'MONDAY', 'AVAILABLE'),
(5, 1, 'TUESDAY', 'AVAILABLE'),
(5, 1, 'WEDNESDAY', 'AVAILABLE'),
(5, 1, 'THURSDAY', 'AVAILABLE'),
(5, 1, 'FRIDAY', 'AVAILABLE'),
(5, 1, 'SATURDAY', 'AVAILABLE'),
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
(6, 1, 'MONDAY', 'AVAILABLE'),
(6, 1, 'TUESDAY', 'AVAILABLE'),
(6, 1, 'WEDNESDAY', 'AVAILABLE'),
(6, 1, 'THURSDAY', 'AVAILABLE'),
(6, 1, 'FRIDAY', 'AVAILABLE'),
(6, 1, 'SATURDAY', 'AVAILABLE'),
(6, 3, 'MONDAY', 'AVAILABLE'),
(6, 3, 'TUESDAY', 'AVAILABLE'),
(6, 3, 'WEDNESDAY', 'AVAILABLE'),
(6, 3, 'THURSDAY', 'AVAILABLE'),
(6, 3, 'FRIDAY', 'AVAILABLE'),
(6, 3, 'SATURDAY', 'AVAILABLE'),
(6, 4, 'MONDAY', 'AVAILABLE'),
(6, 4, 'TUESDAY', 'AVAILABLE'),
(6, 4, 'WEDNESDAY', 'AVAILABLE'),
(6, 4, 'THURSDAY', 'AVAILABLE'),
(6, 4, 'FRIDAY', 'AVAILABLE'),
(6, 4, 'SATURDAY', 'AVAILABLE'),
(6, 5, 'MONDAY', 'AVAILABLE'),
(6, 5, 'TUESDAY', 'AVAILABLE'),
(6, 5, 'WEDNESDAY', 'AVAILABLE'),
(6, 5, 'THURSDAY', 'AVAILABLE'),
(6, 5, 'FRIDAY', 'AVAILABLE'),
(6, 5, 'SATURDAY', 'AVAILABLE'),
(6, 6, 'MONDAY', 'AVAILABLE'),
(6, 6, 'TUESDAY', 'AVAILABLE'),
(6, 6, 'WEDNESDAY', 'AVAILABLE'),
(6, 6, 'THURSDAY', 'AVAILABLE'),
(6, 6, 'FRIDAY', 'AVAILABLE'),
(6, 6, 'SATURDAY', 'AVAILABLE'),
(6, 7, 'MONDAY', 'AVAILABLE'),
(6, 7, 'TUESDAY', 'AVAILABLE'),
(6, 7, 'WEDNESDAY', 'AVAILABLE'),
(6, 7, 'THURSDAY', 'AVAILABLE'),
(6, 7, 'FRIDAY', 'AVAILABLE'),
(6, 7, 'SATURDAY', 'AVAILABLE'),
(6, 8, 'MONDAY', 'AVAILABLE'),
(6, 8, 'TUESDAY', 'AVAILABLE'),
(6, 8, 'WEDNESDAY', 'AVAILABLE'),
(6, 8, 'THURSDAY', 'AVAILABLE'),
(6, 8, 'FRIDAY', 'AVAILABLE'),
(6, 8, 'SATURDAY', 'AVAILABLE'),
(6, 9, 'MONDAY', 'AVAILABLE'),
(6, 9, 'TUESDAY', 'AVAILABLE'),
(6, 9, 'WEDNESDAY', 'AVAILABLE'),
(6, 9, 'THURSDAY', 'AVAILABLE'),
(6, 9, 'FRIDAY', 'AVAILABLE'),
(6, 9, 'SATURDAY', 'AVAILABLE'),
(7, 1, 'MONDAY', 'AVAILABLE'),
(7, 1, 'TUESDAY', 'AVAILABLE'),
(7, 1, 'WEDNESDAY', 'AVAILABLE'),
(7, 1, 'THURSDAY', 'AVAILABLE'),
(7, 1, 'FRIDAY', 'AVAILABLE'),
(7, 1, 'SATURDAY', 'AVAILABLE'),
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
(8, 1, 'MONDAY', 'AVAILABLE'),
(8, 1, 'TUESDAY', 'AVAILABLE'),
(8, 1, 'WEDNESDAY', 'AVAILABLE'),
(8, 1, 'THURSDAY', 'AVAILABLE'),
(8, 1, 'FRIDAY', 'AVAILABLE'),
(8, 1, 'SATURDAY', 'AVAILABLE'),
(8, 3, 'MONDAY', 'AVAILABLE'),
(8, 3, 'TUESDAY', 'AVAILABLE'),
(8, 3, 'WEDNESDAY', 'AVAILABLE'),
(8, 3, 'THURSDAY', 'AVAILABLE'),
(8, 3, 'FRIDAY', 'AVAILABLE'),
(8, 3, 'SATURDAY', 'AVAILABLE'),
(8, 4, 'MONDAY', 'AVAILABLE'),
(8, 4, 'TUESDAY', 'AVAILABLE'),
(8, 4, 'WEDNESDAY', 'AVAILABLE'),
(8, 4, 'THURSDAY', 'AVAILABLE'),
(8, 4, 'FRIDAY', 'AVAILABLE'),
(8, 4, 'SATURDAY', 'AVAILABLE'),
(8, 5, 'MONDAY', 'AVAILABLE'),
(8, 5, 'TUESDAY', 'AVAILABLE'),
(8, 5, 'WEDNESDAY', 'AVAILABLE'),
(8, 5, 'THURSDAY', 'AVAILABLE'),
(8, 5, 'FRIDAY', 'AVAILABLE'),
(8, 5, 'SATURDAY', 'AVAILABLE'),
(8, 6, 'MONDAY', 'AVAILABLE'),
(8, 6, 'TUESDAY', 'AVAILABLE'),
(8, 6, 'WEDNESDAY', 'AVAILABLE'),
(8, 6, 'THURSDAY', 'AVAILABLE'),
(8, 6, 'FRIDAY', 'AVAILABLE'),
(8, 6, 'SATURDAY', 'AVAILABLE'),
(8, 7, 'MONDAY', 'AVAILABLE'),
(8, 7, 'TUESDAY', 'AVAILABLE'),
(8, 7, 'WEDNESDAY', 'AVAILABLE'),
(8, 7, 'THURSDAY', 'AVAILABLE'),
(8, 7, 'FRIDAY', 'AVAILABLE'),
(8, 7, 'SATURDAY', 'AVAILABLE'),
(8, 8, 'MONDAY', 'AVAILABLE'),
(8, 8, 'TUESDAY', 'AVAILABLE'),
(8, 8, 'WEDNESDAY', 'AVAILABLE'),
(8, 8, 'THURSDAY', 'AVAILABLE'),
(8, 8, 'FRIDAY', 'AVAILABLE'),
(8, 8, 'SATURDAY', 'AVAILABLE'),
(8, 9, 'MONDAY', 'AVAILABLE'),
(8, 9, 'TUESDAY', 'AVAILABLE'),
(8, 9, 'WEDNESDAY', 'AVAILABLE'),
(8, 9, 'THURSDAY', 'AVAILABLE'),
(8, 9, 'FRIDAY', 'AVAILABLE'),
(8, 9, 'SATURDAY', 'AVAILABLE'),
(9, 1, 'MONDAY', 'AVAILABLE'),
(9, 1, 'TUESDAY', 'AVAILABLE'),
(9, 1, 'WEDNESDAY', 'AVAILABLE'),
(9, 1, 'THURSDAY', 'AVAILABLE'),
(9, 1, 'FRIDAY', 'AVAILABLE'),
(9, 1, 'SATURDAY', 'AVAILABLE'),
(9, 3, 'MONDAY', 'AVAILABLE'),
(9, 3, 'TUESDAY', 'AVAILABLE'),
(9, 3, 'WEDNESDAY', 'AVAILABLE'),
(9, 3, 'THURSDAY', 'AVAILABLE'),
(9, 3, 'FRIDAY', 'AVAILABLE'),
(9, 3, 'SATURDAY', 'AVAILABLE'),
(9, 4, 'MONDAY', 'AVAILABLE'),
(9, 4, 'TUESDAY', 'AVAILABLE'),
(9, 4, 'WEDNESDAY', 'AVAILABLE'),
(9, 4, 'THURSDAY', 'AVAILABLE'),
(9, 4, 'FRIDAY', 'AVAILABLE'),
(9, 4, 'SATURDAY', 'AVAILABLE'),
(9, 5, 'MONDAY', 'AVAILABLE'),
(9, 5, 'TUESDAY', 'AVAILABLE'),
(9, 5, 'WEDNESDAY', 'AVAILABLE'),
(9, 5, 'THURSDAY', 'AVAILABLE'),
(9, 5, 'FRIDAY', 'AVAILABLE'),
(9, 5, 'SATURDAY', 'AVAILABLE'),
(9, 6, 'MONDAY', 'AVAILABLE'),
(9, 6, 'TUESDAY', 'AVAILABLE'),
(9, 6, 'WEDNESDAY', 'AVAILABLE'),
(9, 6, 'THURSDAY', 'AVAILABLE'),
(9, 6, 'FRIDAY', 'AVAILABLE'),
(9, 6, 'SATURDAY', 'AVAILABLE'),
(9, 7, 'MONDAY', 'AVAILABLE'),
(9, 7, 'TUESDAY', 'AVAILABLE'),
(9, 7, 'WEDNESDAY', 'AVAILABLE'),
(9, 7, 'THURSDAY', 'AVAILABLE'),
(9, 7, 'FRIDAY', 'AVAILABLE'),
(9, 7, 'SATURDAY', 'AVAILABLE'),
(9, 8, 'MONDAY', 'AVAILABLE'),
(9, 8, 'TUESDAY', 'AVAILABLE'),
(9, 8, 'WEDNESDAY', 'AVAILABLE'),
(9, 8, 'THURSDAY', 'AVAILABLE'),
(9, 8, 'FRIDAY', 'AVAILABLE'),
(9, 8, 'SATURDAY', 'AVAILABLE'),
(9, 9, 'MONDAY', 'AVAILABLE'),
(9, 9, 'TUESDAY', 'AVAILABLE'),
(9, 9, 'WEDNESDAY', 'AVAILABLE'),
(9, 9, 'THURSDAY', 'AVAILABLE'),
(9, 9, 'FRIDAY', 'AVAILABLE'),
(9, 9, 'SATURDAY', 'AVAILABLE');

--
-- Dumping data for table `consists`
--

INSERT INTO `consists` (`YEAR_NUMBER`, `PROGRAMME_ID`) VALUES
(1, 2),
(1, 3);

--
-- Dumping data for table `course`
--

INSERT INTO `course` (`COURSE_ID`, `OPTIONAL_ID`, `YEAR_NUMBER`, `PROGRAMME_ID`, `TYPE`, `SEMESTER`, `LONG_NAME`, `SHORT_NAME`, `WEEKLY_LECTURES`, `ISPRACTICAL`, `ISOPTIONAL`) VALUES
(8, NULL, 1, 2, 'LECTURE', 'ODD', 'PPLUC', 'PPLUC', 2, 0, 0),
(9, NULL, 1, 2, 'LECTURE', 'ODD', 'MA', 'MA', 2, 0, 0),
(10, NULL, 1, 2, 'LECTURE', 'ODD', 'VM', 'VM', 2, 0, 0),
(11, NULL, 1, 2, 'LECTURE', 'ODD', 'DM', 'DM', 2, 0, 0),
(12, NULL, 1, 2, 'LECTURE', 'ODD', 'ACCOUNTING', 'ACCOUNTING', 2, 0, 0),
(13, NULL, 1, 2, 'IT PRACTICAL', 'ODD', 'PPLUC MA PRACTICAL', 'PPLUC MA PRACTICAL', 2, 1, 0),
(14, NULL, 1, 2, 'IT PRACTICAL', 'ODD', 'DM PRACTICAL', 'DM PRACTICAL', 2, 1, 0),
(15, NULL, 1, 2, 'LECTURE', 'ODD', 'SIT', 'SIT', 2, 0, 0),
(16, NULL, 1, 3, 'LECTURE', 'ODD', 'BNI', 'BNI', 2, 0, 0);

--
-- Dumping data for table `department`
--

INSERT INTO `department` (`DEPARTMENT_ID`, `LONG_NAME`, `SHORT_NAME`) VALUES
(1, 'INFORMATION TECHNOLOGY', 'I.T.'),
(2, 'BANKING AND INSURANCE', 'B.N.I.');

--
-- Dumping data for table `division`
--

INSERT INTO `division` (`DIVISION_ID`, `YEAR_NUMBER`, `PROGRAMME_ID`, `NAME`, `STUDENT_COUNT`, `START_TIME_ID`, `END_TIME_ID`, `CLASSROOM_ID`) VALUES
(13, 1, 2, 'A', 60, 1, NULL, 5),
(14, 1, 2, 'B', 60, NULL, 8, 6),
(15, 1, 3, 'A', 60, NULL, NULL, NULL);

--
-- Dumping data for table `programme`
--

INSERT INTO `programme` (`PROGRAMME_ID`, `DEPARTMENT_ID`, `LONG_NAME`, `SHORT_NAME`, `DIVISION_COUNT`) VALUES
(2, 1, 'BACHELOR OF SCIENCE IN INFORMATION TECHNLOGY', 'B.SC.I.T.', 2),
(3, 2, 'BNI', 'BNI', 1);

--
-- Dumping data for table `teacher`
--

INSERT INTO `teacher` (`TEACHER_ID`, `DEPARTMENT_ID`, `FIRST_NAME`, `LAST_NAME`, `ISPARTTIME`) VALUES
(4, 1, 'POURNIMA', 'BHANGALE', 0),
(5, 1, 'VANDANA', 'KADAM', 0),
(6, 1, 'RAKHEE', 'RANE', 0),
(7, 1, 'NANDA', 'RUPNAR', 0),
(8, 1, 'PRANALI', 'PAWAR', 0),
(9, 2, 'BNI', 'TEACHER', 0);

--
-- Dumping data for table `teaches`
--

INSERT INTO `teaches` (`WORKLOAD_ID`, `TEACHER_ID`, `COURSE_ID`, `DIVISION_ID`, `LECTURE_COUNT`) VALUES
(1, 6, 8, 13, 2),
(2, 6, 8, 14, 2),
(3, 6, 15, 13, 2),
(4, 6, 15, 14, 2),
(5, 6, 13, 13, 1),
(6, 6, 13, 14, 1),
(7, 5, 9, 13, 2),
(8, 5, 9, 14, 2),
(9, 5, 13, 13, 1),
(10, 5, 13, 14, 1),
(11, 8, 10, 13, 2),
(12, 8, 10, 14, 2),
(13, 8, 11, 13, 2),
(14, 8, 11, 14, 2),
(15, 8, 14, 13, 2),
(16, 8, 14, 14, 2),
(17, 9, 16, 15, 2);

--
-- Dumping data for table `timeslot`
--

INSERT INTO `timeslot` (`SLOT_ID`, `START_TIME`, `END_TIME`, `SLOT_TYPE`) VALUES
(1, '07:00:00', '08:00:00', 'LECTURE'),
(3, '08:00:00', '09:00:00', 'LECTURE'),
(4, '07:00:00', '09:00:00', 'PRACTICAL'),
(5, '09:00:00', '09:15:00', 'BREAK'),
(6, '09:15:00', '09:30:00', 'BREAK'),
(7, '09:30:00', '10:30:00', 'LECTURE'),
(8, '10:30:00', '11:30:00', 'LECTURE'),
(9, '09:30:00', '11:30:00', 'PRACTICAL');

--
-- Dumping data for table `timetable`
--

INSERT INTO `timetable` (`ALLOTMENT_ID`, `COURSE_ID`, `DIVISION_ID`, `CLASSROOM_ID`, `SLOT_ID`, `WEEKDAY`, `TEACHER_ID`, `ACADEMIC_YEAR`, `SEMESTER`) VALUES
(5, 8, 13, 4, 1, 'TUESDAY', 6, '2026-27', 'ODD'),
(8, 8, 13, 4, 3, 'WEDNESDAY', 6, '2026-27', 'ODD'),
(23, 8, 14, 4, 1, 'SATURDAY', 6, '2026-27', 'ODD'),
(24, 8, 14, 4, 3, 'SATURDAY', 6, '2026-27', 'ODD'),
(21, 8, 14, 5, 1, 'FRIDAY', 6, '2026-27', 'ODD'),
(16, 8, 14, 5, 3, 'TUESDAY', 6, '2026-27', 'ODD'),
(20, 8, 14, 5, 3, 'THURSDAY', 6, '2026-27', 'ODD'),
(22, 8, 14, 5, 3, 'FRIDAY', 6, '2026-27', 'ODD'),
(11, 9, 13, 4, 1, 'FRIDAY', 5, '2026-27', 'ODD'),
(12, 9, 13, 4, 3, 'FRIDAY', 5, '2026-27', 'ODD'),
(13, 9, 14, 5, 1, 'MONDAY', 5, '2026-27', 'ODD'),
(17, 9, 14, 5, 1, 'WEDNESDAY', 5, '2026-27', 'ODD'),
(3, 10, 13, 4, 1, 'MONDAY', 8, '2026-27', 'ODD'),
(10, 10, 13, 4, 3, 'THURSDAY', 8, '2026-27', 'ODD'),
(19, 10, 14, 5, 1, 'THURSDAY', 8, '2026-27', 'ODD'),
(18, 10, 14, 5, 3, 'WEDNESDAY', 8, '2026-27', 'ODD'),
(7, 11, 13, 4, 1, 'WEDNESDAY', 8, '2026-27', 'ODD'),
(6, 11, 13, 4, 3, 'TUESDAY', 8, '2026-27', 'ODD'),
(15, 11, 14, 5, 1, 'TUESDAY', 8, '2026-27', 'ODD'),
(14, 11, 14, 5, 3, 'MONDAY', 8, '2026-27', 'ODD'),
(1, 13, 13, 3, 4, 'MONDAY', 6, '2026-27', 'ODD'),
(2, 13, 14, 3, 9, 'MONDAY', 6, '2026-27', 'ODD'),
(9, 15, 13, 4, 1, 'THURSDAY', 6, '2026-27', 'ODD'),
(4, 15, 13, 4, 3, 'MONDAY', 6, '2026-27', 'ODD'),
(27, 16, 14, 4, 7, 'MONDAY', 9, '2026-27', 'ODD'),
(25, 16, 15, 6, 1, 'MONDAY', 9, '2026-27', 'ODD'),
(26, 16, 15, 6, 1, 'TUESDAY', 9, '2026-27', 'ODD');

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_minimal_course`
-- (See below for the actual view)
--
CREATE TABLE `view_minimal_course` (
`COURSE_ID` int(11)
,`PROGRAMME_ID` int(11)
,`DEPARTMENT_ID` int(11)
,`PROGRAMME_NAME` varchar(15)
,`DEPARTMENT_NAME` varchar(31)
,`LONG_NAME` varchar(100)
,`SHORT_NAME` varchar(20)
,`WEEKLY_LECTURES` int(11)
,`ISPRACTICAL` tinyint(1)
,`ISOPTIONAL` tinyint(1)
,`YEAR_NUMBER` int(11)
,`SEMESTER` enum('EVEN','ODD')
,`OPTIONAL_ID` int(11)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_minimal_division`
-- (See below for the actual view)
--
CREATE TABLE `view_minimal_division` (
`DEPARTMENT_ID` int(11)
,`DEPARTMENT_NAME` varchar(31)
,`PROGRAMME_ID` int(11)
,`PROGRAMME_NAME` varchar(15)
,`YEAR_NUMBER` int(11)
,`YEAR_NAME` enum('FIRST YEAR','SECOND YEAR','THIRD YEAR','FOURTH YEAR','FIFTH YEAR')
,`DIVISION_ID` int(11)
,`DIVISION_NAME` char(1)
,`STUDENT_COUNT` int(11)
,`CLASSROOM_ID` int(11)
,`START_TIME_ID` int(11)
,`END_TIME_ID` int(11)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_minimal_optionalcourse`
-- (See below for the actual view)
--
CREATE TABLE `view_minimal_optionalcourse` (
`DEPARTMENT_ID` int(11)
,`DEPARTMENT_SHORT_NAME` varchar(31)
,`PROGRAMME_ID` int(11)
,`PROGRAMME_SHORT_NAME` varchar(15)
,`YEAR_NUMBER` int(11)
,`YEAR_NAME` enum('FIRST YEAR','SECOND YEAR','THIRD YEAR','FOURTH YEAR','FIFTH YEAR')
,`COURSE_ID` int(11)
,`SEMESTER` enum('EVEN','ODD')
,`COURSE_FULL_NAME` varchar(100)
,`COURSE_SHORT_NAME` varchar(20)
,`ISOPTIONAL` tinyint(1)
,`OPTIONAL_ID` int(11)
,`OPTIONAL_COURSE_NAME` varchar(20)
,`ISPRACTICAL` tinyint(1)
,`WEEKLY_LECTURES` int(11)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_minimal_optionalcoursemapper`
-- (See below for the actual view)
--
CREATE TABLE `view_minimal_optionalcoursemapper` (
`DEPARTMENT_ID` int(11)
,`DEPARTMENT_SHORT_NAME` varchar(31)
,`PROGRAMME_ID` int(11)
,`PROGRAMME_SHORT_NAME` varchar(15)
,`YEAR_NUMBER` int(11)
,`YEAR_NAME` enum('FIRST YEAR','SECOND YEAR','THIRD YEAR','FOURTH YEAR','FIFTH YEAR')
,`COURSE_ID` int(11)
,`SEMESTER` enum('EVEN','ODD')
,`COURSE_FULL_NAME` varchar(100)
,`COURSE_SHORT_NAME` varchar(20)
,`ISOPTIONAL` tinyint(1)
,`OPTIONAL_ID` int(11)
,`OPTIONAL_COURSE_NAME` varchar(20)
,`ISPRACTICAL` tinyint(1)
,`WEEKLY_LECTURES` int(11)
,`MAPPED_DIVISION_ID` int(11)
,`MAPPED_DIVISION_NAME` char(1)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_minimal_programme`
-- (See below for the actual view)
--
CREATE TABLE `view_minimal_programme` (
`DEPARTMENT_ID` int(11)
,`PROGRAMME_ID` int(11)
,`LONG_NAME` varchar(63)
,`SHORT_NAME` varchar(15)
,`DURATION` bigint(21)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_minimal_workload`
-- (See below for the actual view)
--
CREATE TABLE `view_minimal_workload` (
`WORKLOAD_ID` int(11)
,`TEACHER_ID` int(11)
,`COURSE_ID` int(11)
,`DIVISION_ID` int(11)
,`DEPARTMENT_ID` int(11)
,`DEPARTMENT_NAME` varchar(31)
,`PROGRAMME_ID` int(11)
,`PROGRAMME_NAME` varchar(15)
,`YEAR_NUMBER` int(11)
,`YEAR_NAME` enum('FIRST YEAR','SECOND YEAR','THIRD YEAR','FOURTH YEAR','FIFTH YEAR')
,`DIVISION_NAME` char(1)
,`SEMESTER` enum('EVEN','ODD')
,`COURSE_NAME` varchar(100)
,`COURSE_SHORT_NAME` varchar(20)
,`LECTURE_COUNT` int(11)
,`TEACHER_NAME` varchar(31)
);

-- --------------------------------------------------------

--
-- Structure for view `view_minimal_course`
--
DROP TABLE IF EXISTS `view_minimal_course`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_minimal_course`  AS SELECT `c`.`COURSE_ID` AS `COURSE_ID`, `c`.`PROGRAMME_ID` AS `PROGRAMME_ID`, `p`.`DEPARTMENT_ID` AS `DEPARTMENT_ID`, `p`.`SHORT_NAME` AS `PROGRAMME_NAME`, `d`.`SHORT_NAME` AS `DEPARTMENT_NAME`, `c`.`LONG_NAME` AS `LONG_NAME`, `c`.`SHORT_NAME` AS `SHORT_NAME`, `c`.`WEEKLY_LECTURES` AS `WEEKLY_LECTURES`, `c`.`ISPRACTICAL` AS `ISPRACTICAL`, `c`.`ISOPTIONAL` AS `ISOPTIONAL`, `c`.`YEAR_NUMBER` AS `YEAR_NUMBER`, `c`.`SEMESTER` AS `SEMESTER`, `c`.`OPTIONAL_ID` AS `OPTIONAL_ID` FROM ((`course` `c` left join `programme` `p` on(`c`.`PROGRAMME_ID` = `p`.`PROGRAMME_ID`)) left join `department` `d` on(`p`.`DEPARTMENT_ID` = `d`.`DEPARTMENT_ID`)) ORDER BY `p`.`DEPARTMENT_ID` ASC, `p`.`SHORT_NAME` ASC, `c`.`YEAR_NUMBER` ASC, `c`.`SEMESTER` ASC ;

-- --------------------------------------------------------

--
-- Structure for view `view_minimal_division`
--
DROP TABLE IF EXISTS `view_minimal_division`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_minimal_division`  AS SELECT `d`.`DEPARTMENT_ID` AS `DEPARTMENT_ID`, `d`.`SHORT_NAME` AS `DEPARTMENT_NAME`, `p`.`PROGRAMME_ID` AS `PROGRAMME_ID`, `p`.`SHORT_NAME` AS `PROGRAMME_NAME`, `y`.`YEAR_NUMBER` AS `YEAR_NUMBER`, `y`.`YEAR_NAME` AS `YEAR_NAME`, `dv`.`DIVISION_ID` AS `DIVISION_ID`, `dv`.`NAME` AS `DIVISION_NAME`, `dv`.`STUDENT_COUNT` AS `STUDENT_COUNT`, `dv`.`CLASSROOM_ID` AS `CLASSROOM_ID`, `dv`.`START_TIME_ID` AS `START_TIME_ID`, `dv`.`END_TIME_ID` AS `END_TIME_ID` FROM ((((`department` `d` join `programme` `p` on(`p`.`DEPARTMENT_ID` = `d`.`DEPARTMENT_ID`)) join `consists` `c` on(`c`.`PROGRAMME_ID` = `p`.`PROGRAMME_ID`)) join `year` `y` on(`y`.`YEAR_NUMBER` = `c`.`YEAR_NUMBER`)) left join `division` `dv` on(`dv`.`PROGRAMME_ID` = `c`.`PROGRAMME_ID` and `dv`.`YEAR_NUMBER` = `c`.`YEAR_NUMBER`)) ORDER BY `dv`.`STUDENT_COUNT` ASC, `d`.`SHORT_NAME` ASC, `p`.`SHORT_NAME` ASC, `y`.`YEAR_NUMBER` ASC, `dv`.`NAME` ASC ;

-- --------------------------------------------------------

--
-- Structure for view `view_minimal_optionalcourse`
--
DROP TABLE IF EXISTS `view_minimal_optionalcourse`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_minimal_optionalcourse`  AS SELECT `d`.`DEPARTMENT_ID` AS `DEPARTMENT_ID`, `d`.`SHORT_NAME` AS `DEPARTMENT_SHORT_NAME`, `p`.`PROGRAMME_ID` AS `PROGRAMME_ID`, `p`.`SHORT_NAME` AS `PROGRAMME_SHORT_NAME`, `y`.`YEAR_NUMBER` AS `YEAR_NUMBER`, `y`.`YEAR_NAME` AS `YEAR_NAME`, `c`.`COURSE_ID` AS `COURSE_ID`, `c`.`SEMESTER` AS `SEMESTER`, `c`.`LONG_NAME` AS `COURSE_FULL_NAME`, `c`.`SHORT_NAME` AS `COURSE_SHORT_NAME`, `c`.`ISOPTIONAL` AS `ISOPTIONAL`, `c`.`OPTIONAL_ID` AS `OPTIONAL_ID`, `op`.`SHORT_NAME` AS `OPTIONAL_COURSE_NAME`, `c`.`ISPRACTICAL` AS `ISPRACTICAL`, `c`.`WEEKLY_LECTURES` AS `WEEKLY_LECTURES` FROM ((((`course` `c` left join `course` `op` on(`op`.`COURSE_ID` = `c`.`OPTIONAL_ID`)) left join `programme` `p` on(`c`.`PROGRAMME_ID` = `p`.`PROGRAMME_ID`)) left join `department` `d` on(`p`.`DEPARTMENT_ID` = `d`.`DEPARTMENT_ID`)) left join `year` `y` on(`c`.`YEAR_NUMBER` = `y`.`YEAR_NUMBER`)) WHERE `c`.`ISOPTIONAL` = 1 AND (`c`.`OPTIONAL_ID` is null OR `c`.`COURSE_ID` < `c`.`OPTIONAL_ID`) ORDER BY `d`.`DEPARTMENT_ID` ASC, `p`.`PROGRAMME_ID` ASC, `y`.`YEAR_NUMBER` ASC, `c`.`SEMESTER` ASC ;

-- --------------------------------------------------------

--
-- Structure for view `view_minimal_optionalcoursemapper`
--
DROP TABLE IF EXISTS `view_minimal_optionalcoursemapper`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_minimal_optionalcoursemapper`  AS SELECT `d`.`DEPARTMENT_ID` AS `DEPARTMENT_ID`, `d`.`SHORT_NAME` AS `DEPARTMENT_SHORT_NAME`, `p`.`PROGRAMME_ID` AS `PROGRAMME_ID`, `p`.`SHORT_NAME` AS `PROGRAMME_SHORT_NAME`, `y`.`YEAR_NUMBER` AS `YEAR_NUMBER`, `y`.`YEAR_NAME` AS `YEAR_NAME`, `c`.`COURSE_ID` AS `COURSE_ID`, `c`.`SEMESTER` AS `SEMESTER`, `c`.`LONG_NAME` AS `COURSE_FULL_NAME`, `c`.`SHORT_NAME` AS `COURSE_SHORT_NAME`, `c`.`ISOPTIONAL` AS `ISOPTIONAL`, `c`.`OPTIONAL_ID` AS `OPTIONAL_ID`, `op`.`SHORT_NAME` AS `OPTIONAL_COURSE_NAME`, `c`.`ISPRACTICAL` AS `ISPRACTICAL`, `c`.`WEEKLY_LECTURES` AS `WEEKLY_LECTURES`, `ob`.`DIVISION_ID` AS `MAPPED_DIVISION_ID`, `dv`.`NAME` AS `MAPPED_DIVISION_NAME` FROM ((((((`course` `c` left join `course` `op` on(`op`.`COURSE_ID` = `c`.`OPTIONAL_ID`)) left join `opted_by` `ob` on(`ob`.`COURSE_ID` = `c`.`COURSE_ID`)) left join `division` `dv` on(`dv`.`DIVISION_ID` = `ob`.`DIVISION_ID`)) join `programme` `p` on(`c`.`PROGRAMME_ID` = `p`.`PROGRAMME_ID`)) join `department` `d` on(`p`.`DEPARTMENT_ID` = `d`.`DEPARTMENT_ID`)) join `year` `y` on(`c`.`YEAR_NUMBER` = `y`.`YEAR_NUMBER`)) WHERE `c`.`ISOPTIONAL` = 1 AND `c`.`OPTIONAL_ID` is not null ORDER BY `d`.`DEPARTMENT_ID` ASC, `p`.`PROGRAMME_ID` ASC, `y`.`YEAR_NUMBER` ASC, `c`.`SEMESTER` ASC, `c`.`COURSE_ID` ASC ;

-- --------------------------------------------------------

--
-- Structure for view `view_minimal_programme`
--
DROP TABLE IF EXISTS `view_minimal_programme`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_minimal_programme`  AS SELECT `p`.`DEPARTMENT_ID` AS `DEPARTMENT_ID`, `p`.`PROGRAMME_ID` AS `PROGRAMME_ID`, `p`.`LONG_NAME` AS `LONG_NAME`, `p`.`SHORT_NAME` AS `SHORT_NAME`, count(`c`.`PROGRAMME_ID`) AS `DURATION` FROM (`programme` `p` left join `consists` `c` on(`p`.`PROGRAMME_ID` = `c`.`PROGRAMME_ID`)) GROUP BY `p`.`DEPARTMENT_ID`, `p`.`PROGRAMME_ID`, `p`.`LONG_NAME`, `p`.`SHORT_NAME` ORDER BY `p`.`LONG_NAME` ASC ;

-- --------------------------------------------------------

--
-- Structure for view `view_minimal_workload`
--
DROP TABLE IF EXISTS `view_minimal_workload`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_minimal_workload`  AS SELECT `t`.`WORKLOAD_ID` AS `WORKLOAD_ID`, `t`.`TEACHER_ID` AS `TEACHER_ID`, `t`.`COURSE_ID` AS `COURSE_ID`, `t`.`DIVISION_ID` AS `DIVISION_ID`, `d`.`DEPARTMENT_ID` AS `DEPARTMENT_ID`, `d`.`SHORT_NAME` AS `DEPARTMENT_NAME`, `p`.`PROGRAMME_ID` AS `PROGRAMME_ID`, `p`.`SHORT_NAME` AS `PROGRAMME_NAME`, `y`.`YEAR_NUMBER` AS `YEAR_NUMBER`, `y`.`YEAR_NAME` AS `YEAR_NAME`, `dv`.`NAME` AS `DIVISION_NAME`, `c`.`SEMESTER` AS `SEMESTER`, `c`.`LONG_NAME` AS `COURSE_NAME`, `c`.`SHORT_NAME` AS `COURSE_SHORT_NAME`, `t`.`LECTURE_COUNT` AS `LECTURE_COUNT`, concat(`tr`.`FIRST_NAME`,' ',`tr`.`LAST_NAME`) AS `TEACHER_NAME` FROM ((((((`teaches` `t` join `teacher` `tr` on(`t`.`TEACHER_ID` = `tr`.`TEACHER_ID`)) join `course` `c` on(`t`.`COURSE_ID` = `c`.`COURSE_ID`)) join `division` `dv` on(`t`.`DIVISION_ID` = `dv`.`DIVISION_ID`)) join `programme` `p` on(`c`.`PROGRAMME_ID` = `p`.`PROGRAMME_ID`)) join `department` `d` on(`p`.`DEPARTMENT_ID` = `d`.`DEPARTMENT_ID`)) join `year` `y` on(`c`.`YEAR_NUMBER` = `y`.`YEAR_NUMBER`)) ORDER BY `d`.`LONG_NAME` ASC, `p`.`LONG_NAME` ASC, `y`.`YEAR_NUMBER` ASC, `dv`.`NAME` ASC, `c`.`SEMESTER` ASC, `c`.`LONG_NAME` ASC, concat(`tr`.`FIRST_NAME`,' ',`tr`.`LAST_NAME`) ASC ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
