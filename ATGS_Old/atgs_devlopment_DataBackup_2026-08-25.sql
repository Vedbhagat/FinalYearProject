-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 25, 2026 at 06:59 PM
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(3, 2, 'SATURDAY', 'AVAILABLE');

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
  `CATEGORY` enum('LECTURE_HALL','LAB') DEFAULT 'LECTURE_HALL'
) ;

--
-- Dumping data for table `classroom`
--

INSERT INTO `classroom` (`CLASSROOM_ID`, `FLOOR_NUMBER`, `ROOM_NUMBER`, `CAPACITY`, `START_TIME`, `END_TIME`, `CATEGORY`) VALUES
(1, 'Ground Floor', '1', 100, '08:00:00', '16:00:00', 'LECTURE_HALL'),
(2, 'Ground Floor', '2', NULL, NULL, NULL, 'LECTURE_HALL'),
(3, 'First Floor', '101', NULL, NULL, NULL, 'LECTURE_HALL'),
(4, 'First Floor', '102', NULL, NULL, NULL, 'LAB'),
(5, 'Second Floor', '201', NULL, NULL, NULL, 'LAB'),
(6, 'Second Floor', '202', NULL, NULL, NULL, 'LAB');

-- --------------------------------------------------------

--
-- Table structure for table `consists`
--

CREATE TABLE `consists` (
  `YEAR_NUMBER` int(11) NOT NULL,
  `PROGRAMME_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `consists`
--

INSERT INTO `consists` (`YEAR_NUMBER`, `PROGRAMME_ID`) VALUES
(1, 1),
(1, 2),
(2, 1),
(2, 2),
(3, 1);

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
(7, 9, 1, 1, 'ODD', 'ACCOUNTING ', 'ACCOUNTS', 4, 0, 1),
(8, 10, 2, 1, 'ODD', 'HINDI', 'HINDI', 1, 0, 1),
(9, 7, 1, 1, 'ODD', 'FINANCIAL MARKETING', 'F.M.', 4, 0, 1),
(10, 8, 2, 1, 'ODD', 'MARATHI', 'MARATHI', 1, 0, 1),
(11, 12, 2, 1, 'EVEN', 'HINDI', 'HINDI', 1, 0, 1),
(12, 11, 2, 1, 'EVEN', 'MARATHI', 'MARATHI', 1, 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `department`
--

CREATE TABLE `department` (
  `DEPARTMENT_ID` int(11) NOT NULL,
  `USERNAME` varchar(31) DEFAULT NULL,
  `LONG_NAME` varchar(63) NOT NULL,
  `SHORT_NAME` varchar(31) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `department`
--

INSERT INTO `department` (`DEPARTMENT_ID`, `USERNAME`, `LONG_NAME`, `SHORT_NAME`) VALUES
(1, NULL, 'INFORMATION TECHNOLOGY', 'I.T.'),
(2, NULL, 'BANKING AND INSURANCE', 'B.N.I.'),
(3, NULL, 'MANAGEMENT STUDIES', 'B.M.S.');

-- --------------------------------------------------------

--
-- Table structure for table `division`
--

CREATE TABLE `division` (
  `DIVISION_ID` int(11) NOT NULL,
  `YEAR_NUMBER` int(11) DEFAULT NULL,
  `PROGRAMME_ID` int(11) DEFAULT NULL,
  `NAME` char(1) NOT NULL,
  `STUDENT_COUNT` int(11) DEFAULT NULL
) ;

--
-- Dumping data for table `division`
--

INSERT INTO `division` (`DIVISION_ID`, `YEAR_NUMBER`, `PROGRAMME_ID`, `NAME`, `STUDENT_COUNT`) VALUES
(1, 1, 1, 'A', NULL),
(2, 2, 1, 'A', NULL),
(3, 3, 1, 'A', NULL),
(4, 1, 2, 'A', NULL),
(5, 2, 2, 'A', NULL),
(23, 3, 1, 'B', NULL),
(49, 1, 1, 'B', NULL),
(50, 2, 1, 'B', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `opted_by`
--

CREATE TABLE `opted_by` (
  `COURSE_ID` int(11) NOT NULL,
  `DIVISION_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ;

--
-- Dumping data for table `programme`
--

INSERT INTO `programme` (`PROGRAMME_ID`, `DEPARTMENT_ID`, `LONG_NAME`, `SHORT_NAME`, `DIVISION_COUNT`) VALUES
(1, 1, 'BACHELOR OF SCIENCE IN INFORMATION TECHNOLOGY', 'B.SC.I.T.', 6),
(2, 1, 'MASTERS OF SCIENCE IN INFORMATION TECHNOLOGY', 'M.SC.I.T.', 2);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher`
--

INSERT INTO `teacher` (`TEACHER_ID`, `DEPARTMENT_ID`, `FIRST_NAME`, `LAST_NAME`, `ISPARTTIME`) VALUES
(1, 3, 'BNI', 'TEACHERONE', 0),
(2, 2, 'BT', 'TEACHERTWO', 0),
(3, 1, 'POURNIMA', 'BHANGALE', 0);

-- --------------------------------------------------------

--
-- Table structure for table `teaches`
--

CREATE TABLE `teaches` (
  `TEACHER_ID` int(11) NOT NULL,
  `COURSE_ID` int(11) NOT NULL,
  `DIVISION_ID` int(11) NOT NULL,
  `LECTURE_COUNT` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `timeslot`
--

CREATE TABLE `timeslot` (
  `SLOT_ID` int(11) NOT NULL,
  `START_TIME` time NOT NULL,
  `END_TIME` time NOT NULL,
  `SLOT_TYPE` enum('BREAK','LECTURE','PRACTICAL') NOT NULL DEFAULT 'LECTURE'
) ;

--
-- Dumping data for table `timeslot`
--

INSERT INTO `timeslot` (`SLOT_ID`, `START_TIME`, `END_TIME`, `SLOT_TYPE`) VALUES
(1, '07:00:00', '08:00:00', 'LECTURE'),
(2, '08:00:00', '09:00:00', 'LECTURE');

-- --------------------------------------------------------

--
-- Table structure for table `timetable`
--

CREATE TABLE `timetable` (
  `COURSE_ID` int(11) NOT NULL,
  `DIVISION_ID` int(11) NOT NULL,
  `CLASSROOM_ID` int(11) NOT NULL,
  `SLOT_ID` int(11) NOT NULL,
  `WEEKDAY` enum('MONDAY','TUESDAY','WEDNESDAY','THURSDAY','FRIDAY','SATURDAY') DEFAULT NULL,
  `TEACHER_ID` int(11) NOT NULL,
  `ACADEMIC_YEAR` varchar(7) NOT NULL,
  `SEMESTER` enum('EVEN','ODD') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `USERNAME` varchar(31) NOT NULL,
  `PASSWORD` varchar(255) NOT NULL,
  `LOGIN_TIME` datetime NOT NULL DEFAULT current_timestamp(),
  `LOGIN_IP` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `weekday`
--

CREATE TABLE `weekday` (
  `WEEKDAY` enum('MONDAY','TUESDAY','WEDNESDAY','THURSDAY','FRIDAY','SATURDAY') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  ADD PRIMARY KEY (`DEPARTMENT_ID`),
  ADD KEY `fk_usrnm_deptTbl` (`USERNAME`);

--
-- Indexes for table `division`
--
ALTER TABLE `division`
  ADD PRIMARY KEY (`DIVISION_ID`),
  ADD UNIQUE KEY `NAME` (`NAME`,`YEAR_NUMBER`,`PROGRAMME_ID`),
  ADD KEY `fk_yrId_dvsnTbl` (`YEAR_NUMBER`),
  ADD KEY `fk_pgrmId_dvsnTbl` (`PROGRAMME_ID`);

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
  ADD PRIMARY KEY (`TEACHER_ID`,`COURSE_ID`,`DIVISION_ID`),
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
  ADD PRIMARY KEY (`COURSE_ID`,`DIVISION_ID`,`CLASSROOM_ID`,`SLOT_ID`,`TEACHER_ID`,`ACADEMIC_YEAR`,`SEMESTER`),
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
  MODIFY `CLASSROOM_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `course`
--
ALTER TABLE `course`
  MODIFY `COURSE_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `department`
--
ALTER TABLE `department`
  MODIFY `DEPARTMENT_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `division`
--
ALTER TABLE `division`
  MODIFY `DIVISION_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `programme`
--
ALTER TABLE `programme`
  MODIFY `PROGRAMME_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teacher`
--
ALTER TABLE `teacher`
  MODIFY `TEACHER_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `timeslot`
--
ALTER TABLE `timeslot`
  MODIFY `SLOT_ID` int(11) NOT NULL AUTO_INCREMENT;

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
-- Constraints for table `department`
--
ALTER TABLE `department`
  ADD CONSTRAINT `fk_usrnm_deptTbl` FOREIGN KEY (`USERNAME`) REFERENCES `user` (`USERNAME`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `division`
--
ALTER TABLE `division`
  ADD CONSTRAINT `fk_pgrmId_dvsnTbl` FOREIGN KEY (`PROGRAMME_ID`) REFERENCES `programme` (`PROGRAMME_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_yrId_dvsnTbl` FOREIGN KEY (`YEAR_NUMBER`) REFERENCES `year` (`YEAR_NUMBER`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `opted_by`
--
ALTER TABLE `opted_by`
  ADD CONSTRAINT `fk_crseId_optdByTbl` FOREIGN KEY (`COURSE_ID`) REFERENCES `course` (`COURSE_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dvsnId_optdByTbl` FOREIGN KEY (`DIVISION_ID`) REFERENCES `division` (`DIVISION_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

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
