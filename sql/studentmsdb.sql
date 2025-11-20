-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 20, 2025 at 08:30 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `studentmsdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `achievement_approvals`
--

CREATE TABLE `achievement_approvals` (
  `id` int(11) NOT NULL,
  `achievement_id` int(11) NOT NULL,
  `approved_by` varchar(100) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `achievement_approvals`
--

INSERT INTO `achievement_approvals` (`id`, `achievement_id`, `approved_by`, `approved_at`, `notes`) VALUES
(31, 33, '1', '2025-11-20 12:13:25', NULL),
(32, 32, '1', '2025-11-20 12:13:26', NULL),
(33, 34, '1', '2025-11-20 12:15:57', NULL),
(34, 37, '1', '2025-11-20 12:32:41', NULL),
(35, 36, '1', '2025-11-20 12:32:42', NULL),
(36, 35, '1', '2025-11-20 12:32:43', NULL),
(38, 39, '1', '2025-11-20 12:44:14', NULL),
(41, 42, '1', '2025-11-20 12:56:34', 'please provide a proff'),
(42, 43, '1', '2025-11-20 12:59:59', 'please provide a proff'),
(43, 44, '1', '2025-11-20 13:31:43', 'please provide a proof of achievement and this is non academic!');

-- --------------------------------------------------------

--
-- Table structure for table `skills`
--

CREATE TABLE `skills` (
  `id` int(11) NOT NULL,
  `name` varchar(191) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `skills`
--

INSERT INTO `skills` (`id`, `name`, `category`, `created_at`) VALUES
(1, 'Soccer', 'Academic', '2025-09-24 10:55:00'),
(2, 'Basketball', 'Non-Academic', '2025-09-24 11:52:42'),
(3, 'Volley Ball', 'Academic', '2025-09-24 12:19:10'),
(4, 'Badminton', 'Non-Academic', '2025-09-24 13:30:53'),
(5, 'Sipak Takraw', 'Non-Academic', '2025-09-24 17:28:37'),
(6, 'Best in Art', 'Academic', '2025-09-29 13:27:40'),
(7, 'Best in Quiz Bee', 'Academic', '2025-09-29 14:51:50'),
(8, 'Best in Project Leadership', 'Academic', '2025-10-19 16:29:12'),
(9, 'Volleyball', 'Non-Academic', '2025-11-14 19:00:46'),
(10, 'Muse', 'Non-Academic', '2025-11-17 19:25:50'),
(11, 'MOBA', 'Non-Academic', '2025-11-17 19:27:13'),
(12, 'Mobile Legends', 'Non-Academic', '2025-11-19 11:18:02'),
(13, 'Best in Presentation', 'Academic', '2025-11-20 11:53:59');

-- --------------------------------------------------------

--
-- Table structure for table `student_achievements`
--

CREATE TABLE `student_achievements` (
  `id` int(11) NOT NULL,
  `StuID` varchar(100) NOT NULL,
  `level` varchar(50) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `proof_image` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `rejection_reason` text DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `points` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_achievements`
--

INSERT INTO `student_achievements` (`id`, `StuID`, `level`, `category`, `proof_image`, `status`, `rejection_reason`, `is_read`, `approved_by`, `approved_at`, `points`, `created_at`) VALUES
(32, '222 - 08410', 'School', 'Non-Academic', '1763611964_Screenshot__5_.png', 'approved', NULL, 0, 1, '2025-11-20 12:13:26', 10, '2025-11-20 12:12:44'),
(33, '111 - 11111', 'City', 'Non-Academic', '1763611994_Screenshot__228_.png', 'approved', NULL, 0, 1, '2025-11-20 12:13:25', 30, '2025-11-20 12:13:14'),
(34, '222 - 08412', 'Regional', 'Non-Academic', '1763612150_Screenshot__234_.png', 'approved', NULL, 0, 1, '2025-11-20 12:15:57', 50, '2025-11-20 12:15:50'),
(35, '222 - 08412', 'School', 'Academic', '1763613076_Screenshot__5_.png', 'approved', NULL, 0, 1, '2025-11-20 12:32:43', 10, '2025-11-20 12:31:16'),
(36, '111 - 11111', 'City', 'Academic', '1763613117_Screenshot__228_.png', 'approved', NULL, 0, 1, '2025-11-20 12:32:42', 30, '2025-11-20 12:31:57'),
(37, '222 - 08410', 'Provincial', 'Academic', '1763613152_Screenshot__235_.png', 'approved', NULL, 0, 1, '2025-11-20 12:32:41', 40, '2025-11-20 12:32:32'),
(39, '222 - 08410', 'International', 'Non-Academic', '1763613844_Screenshot__234_.png', 'approved', NULL, 0, 1, '2025-11-20 12:44:14', 100, '2025-11-20 12:44:04'),
(42, '222 - 08410', 'Hobby', 'Non-Academic', '', 'rejected', 'please provide a proff', 0, 1, '2025-11-20 12:56:34', 1, '2025-11-20 12:56:17'),
(43, '222 - 08410', 'School', 'Non-Academic', '', 'rejected', 'please provide a proff', 0, 1, '2025-11-20 12:59:59', 10, '2025-11-20 12:59:41'),
(44, '222 - 08410', 'Regional', 'Academic', '', 'rejected', 'please provide a proof of achievement and this is non academic!', 0, 1, '2025-11-20 13:31:43', 50, '2025-11-20 13:31:02');

-- --------------------------------------------------------

--
-- Table structure for table `student_achievement_skills`
--

CREATE TABLE `student_achievement_skills` (
  `id` int(11) NOT NULL,
  `achievement_id` int(11) NOT NULL,
  `skill_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_achievement_skills`
--

INSERT INTO `student_achievement_skills` (`id`, `achievement_id`, `skill_id`) VALUES
(32, 32, 12),
(33, 33, 12),
(34, 34, 12),
(35, 35, 6),
(36, 36, 6),
(37, 37, 6),
(39, 39, 12),
(42, 42, 11),
(43, 43, 11),
(44, 44, 11);

-- --------------------------------------------------------

--
-- Table structure for table `tbladmin`
--

CREATE TABLE `tbladmin` (
  `ID` int(10) NOT NULL,
  `AdminName` varchar(120) DEFAULT NULL,
  `UserName` varchar(120) DEFAULT NULL,
  `Email` varchar(200) DEFAULT NULL,
  `Password` varchar(200) DEFAULT NULL,
  `AdminRegdate` timestamp NULL DEFAULT current_timestamp(),
  `Image` varchar(255) DEFAULT NULL,
  `reset_code` varchar(255) DEFAULT NULL,
  `reset_code_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tbladmin`
--

INSERT INTO `tbladmin` (`ID`, `AdminName`, `UserName`, `Email`, `Password`, `AdminRegdate`, `Image`, `reset_code`, `reset_code_expires`) VALUES
(1, 'Admin', 'admin', 'senilla.jayriel.mcc@gmail.com', '$2y$10$TkVBZ5IoU68nQZTYwmxDBukUG58pO4qM1XiO5Ad0G1VkxkMtDlJSm', '2025-01-01 04:36:52', '59f33a9e9f4ba0bf4e68d5c4264d8e6e1763372483.jpg', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tblmessages`
--

CREATE TABLE `tblmessages` (
  `ID` int(11) NOT NULL,
  `SenderID` int(11) NOT NULL,
  `SenderType` enum('admin','staff') NOT NULL,
  `RecipientStuID` varchar(255) NOT NULL,
  `Subject` varchar(255) NOT NULL,
  `Message` text NOT NULL,
  `IsRead` tinyint(1) NOT NULL DEFAULT 0,
  `Timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblmessages`
--

INSERT INTO `tblmessages` (`ID`, `SenderID`, `SenderType`, `RecipientStuID`, `Subject`, `Message`, `IsRead`, `Timestamp`) VALUES
(1, 1, 'staff', '222 - 08410', 'Sample Email SMS', 'Sample Email Messaging', 0, '2025-11-20 07:15:50');

-- --------------------------------------------------------

--
-- Table structure for table `tblnotice`
--

CREATE TABLE `tblnotice` (
  `ID` int(5) NOT NULL,
  `NoticeTitle` mediumtext DEFAULT NULL,
  `ClassId` int(10) DEFAULT NULL,
  `NoticeMsg` mediumtext DEFAULT NULL,
  `CreationDate` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tblnotice`
--

INSERT INTO `tblnotice` (`ID`, `NoticeTitle`, `ClassId`, `NoticeMsg`, `CreationDate`) VALUES
(7, 'Test Notice', 1, 'This is the test notice. This is the test notice. This is the test notice. This is the test notice. This is the test notice.', '2025-01-01 06:03:25'),
(8, 'Test Notice 02', 1, 'Testing notice number 2', '2025-01-04 04:12:07'),
(9, 'Sample01', 8, 'This is sample post!!', '2025-07-28 05:37:25'),
(10, 'Sample03', NULL, 'this sample 03', '2025-08-26 05:26:30'),
(11, 'Sample04', NULL, 'This is sample 04', '2025-08-26 05:27:00'),
(12, 'Sample05', NULL, 'This is sample 06', '2025-08-26 05:29:03'),
(13, 'Sample04', NULL, 'This is sample notice number 4', '2025-09-29 07:06:13'),
(14, 'Sample06', NULL, 'Idk ngano ni red na', '2025-10-02 09:54:16');

-- --------------------------------------------------------

--
-- Table structure for table `tblpage`
--

CREATE TABLE `tblpage` (
  `ID` int(10) NOT NULL,
  `PageType` varchar(200) DEFAULT NULL,
  `PageTitle` mediumtext DEFAULT NULL,
  `PageDescription` mediumtext DEFAULT NULL,
  `Email` varchar(200) DEFAULT NULL,
  `MobileNumber` bigint(10) DEFAULT NULL,
  `UpdationDate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tblpage`
--

INSERT INTO `tblpage` (`ID`, `PageType`, `PageTitle`, `PageDescription`, `Email`, `MobileNumber`, `UpdationDate`) VALUES
(1, 'aboutus', 'About Us', '<div style=\"text-align: start;\"><font color=\"#7b8898\" face=\"Mercury SSm A, Mercury SSm B, Georgia, Times, Times New Roman, Microsoft YaHei New, Microsoft Yahei, ????, ??, SimSun, STXihei, ????, serif\"><span style=\"font-size: 26px;\">Student Profiling System Developed for MCC.</span></font><br></div>', NULL, NULL, NULL),
(2, 'contactus', 'Contact Us', '<span style=\"color: rgb(8, 8, 9); font-family: &quot;Segoe UI Historic&quot;, &quot;Segoe UI&quot;, Helvetica, Arial, sans-serif; font-size: 15px;\">Mandaue City Cultural and Sports Complex, A. Soriano Ave, Mandaue City, Philippines</span>', 'mcc@mandauecitycollege.com', 322395989, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tblpublicnotice`
--

CREATE TABLE `tblpublicnotice` (
  `ID` int(5) NOT NULL,
  `NoticeTitle` varchar(200) DEFAULT NULL,
  `NoticeMessage` mediumtext DEFAULT NULL,
  `CreationDate` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tblpublicnotice`
--

INSERT INTO `tblpublicnotice` (`ID`, `NoticeTitle`, `NoticeMessage`, `CreationDate`) VALUES
(3, 'Winter vaction', 'Vacation til 15 Jan', '2025-01-04 04:14:32'),
(4, 'Summer Vacation', 'Summer vacation for this year.', '2025-08-26 05:12:01'),
(6, 'Bagyong Tino ', 'No class', '2025-11-16 06:45:25');

-- --------------------------------------------------------

--
-- Table structure for table `tblskills`
--

CREATE TABLE `tblskills` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `category` varchar(64) NOT NULL DEFAULT 'Non-Academic',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tblskills`
--

INSERT INTO `tblskills` (`id`, `name`, `category`, `created_at`) VALUES
(1, 'Basketball', 'Non-Academic', '2025-09-29 12:57:38'),
(2, 'Soccer', 'Non-Academic', '2025-09-29 12:58:04'),
(3, 'Football', 'Non-Academic', '2025-09-29 12:58:19'),
(4, 'Volleyball', 'Non-Academic', '2025-09-29 12:58:31'),
(5, 'Archery', 'Non-Academic', '2025-09-29 12:58:48'),
(6, 'Tennis', 'Non-Academic', '2025-09-29 12:59:08'),
(7, 'Rugby', 'Non-Academic', '2025-09-29 12:59:22'),
(8, 'Track & Field', 'Non-Academic', '2025-09-29 12:59:39'),
(9, 'Taekwondo', 'Non-Academic', '2025-09-29 12:59:52'),
(10, 'Baseball', 'Non-Academic', '2025-09-29 13:00:03'),
(11, 'Softball', 'Non-Academic', '2025-09-29 13:00:12'),
(12, 'Cricket', 'Non-Academic', '2025-09-29 13:00:20'),
(13, 'Gymnastics', 'Non-Academic', '2025-09-29 13:00:31'),
(14, 'Figure Skating', 'Non-Academic', '2025-09-29 13:00:40'),
(15, 'Yoga', 'Non-Academic', '2025-09-29 13:00:49'),
(16, 'Martial Arts', 'Non-Academic', '2025-09-29 13:01:01'),
(17, 'Running', 'Non-Academic', '2025-09-29 13:01:10'),
(18, 'Cycling', 'Non-Academic', '2025-09-29 13:01:18'),
(19, 'Best in Essay Writing', 'Academic', '2025-09-29 13:05:11'),
(20, 'Best in Mathematics', 'Academic', '2025-09-29 13:05:21'),
(21, 'Best in Science', 'Academic', '2025-09-29 13:05:30'),
(22, 'Best in English Grammar', 'Academic', '2025-09-29 13:05:39'),
(23, 'Best in Creative Writing', 'Academic', '2025-09-29 13:05:51'),
(24, 'Best in Reading Comprehension', 'Academic', '2025-09-29 13:06:01'),
(25, 'Best in Research', 'Academic', '2025-09-29 13:06:10'),
(26, 'Best in Public Speaking', 'Academic', '2025-09-29 13:06:20'),
(27, 'Best in Debate', 'Academic', '2025-09-29 13:06:30'),
(28, 'Best in History', 'Academic', '2025-09-29 13:06:40'),
(29, 'Best in Geography', 'Academic', '2025-09-29 13:06:50'),
(30, 'Best in Spelling', 'Academic', '2025-09-29 13:06:59'),
(31, 'Best in Vocabulary', 'Academic', '2025-09-29 13:07:08'),
(32, 'Best in Computer Skills', 'Academic', '2025-09-29 13:07:18'),
(33, 'Best in Coding', 'Academic', '2025-09-29 13:07:31'),
(34, 'Best in Art', 'Academic', '2025-09-29 13:07:44'),
(35, 'Best in Music', 'Academic', '2025-09-29 13:08:01'),
(36, 'Best in Presentation', 'Academic', '2025-09-29 13:08:15'),
(37, 'Best in Project Leadership', 'Academic', '2025-09-29 13:08:25'),
(38, 'Best in Problem Solving', 'Academic', '2025-09-29 13:08:35'),
(39, 'Best in Critical Thinking', 'Academic', '2025-09-29 13:08:44'),
(40, 'Best in Memorization', 'Academic', '2025-09-29 13:08:58'),
(41, 'Best in Quiz Bee', 'Academic', '2025-09-29 13:09:10'),
(42, 'Best in Collaboration', 'Academic', '2025-09-29 13:09:19'),
(43, 'Muse', 'Non-Academic', '2025-11-17 19:25:50'),
(44, 'MOBA', 'Non-Academic', '2025-11-17 19:27:13'),
(45, 'Mobile Legends', 'Non-Academic', '2025-11-19 11:18:02');

-- --------------------------------------------------------

--
-- Table structure for table `tblstaff`
--

CREATE TABLE `tblstaff` (
  `ID` int(11) NOT NULL,
  `StaffName` varchar(100) NOT NULL,
  `UserName` varchar(50) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Image` varchar(255) DEFAULT NULL,
  `Password` varchar(255) NOT NULL,
  `StaffRegdate` datetime DEFAULT current_timestamp(),
  `Status` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblstaff`
--

INSERT INTO `tblstaff` (`ID`, `StaffName`, `UserName`, `Email`, `Image`, `Password`, `StaffRegdate`, `Status`) VALUES
(1, 'Shiela daniot', 'shiela', 'senillajayriel@gmail.com', '9f29f3f97902866ff4f3ead5d59690c91760437710.png', '$2y$10$B3qul0SK1vX86/QjpYmnduinc.2QiG3OgnA6hfYLIkAizJOGcZVHe', '2025-09-09 06:56:58', 1),
(3, 'gabi katol', 'gabi', 'gabi@test.com', NULL, 'd1aa72f9cae9ff4a4377fc58a5ae2fe9', '2025-09-12 14:20:55', 0),
(4, 'saging hinog', 'saging', 'saging@test.com', NULL, '71eb4a6c476caef18ca1c2b5342f357a', '2025-09-12 14:21:16', 0),
(5, 'Richie Messa', 'richie', 'senilla.jayriel.mcc@gmail.com', NULL, '29353f3b5eb749ae0afb3d88b810f05c', '2025-11-15 08:36:32', 1),
(6, 'Jezrah Faith Canonio', 'jezrah', 'jezrah@gmail.com', NULL, '$2y$10$I0WwMIDQy4twjg2mZtw4kuAF95yINKiT6v4uxgCnn06FuyCz9lBPi', '2025-11-16 08:48:57', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tblstudent`
--

CREATE TABLE `tblstudent` (
  `ID` int(11) NOT NULL,
  `StuID` varchar(50) NOT NULL,
  `FamilyName` varchar(100) NOT NULL,
  `FirstName` varchar(100) NOT NULL,
  `MiddleName` varchar(100) DEFAULT NULL,
  `Program` varchar(100) NOT NULL,
  `Major` varchar(100) DEFAULT NULL,
  `LearnersReferenceNo` varchar(50) DEFAULT NULL,
  `DOB` date NOT NULL,
  `PlaceOfBirth` varchar(255) DEFAULT NULL,
  `Gender` varchar(10) NOT NULL,
  `CivilStatus` varchar(50) DEFAULT NULL,
  `Religion` varchar(100) DEFAULT NULL,
  `Height` varchar(10) DEFAULT NULL,
  `Weight` varchar(10) DEFAULT NULL,
  `Citizenship` varchar(50) DEFAULT NULL,
  `FathersName` varchar(100) DEFAULT NULL,
  `MothersMaidenName` varchar(100) DEFAULT NULL,
  `BuildingHouseNumber` varchar(255) DEFAULT NULL,
  `StreetName` varchar(255) DEFAULT NULL,
  `Barangay` varchar(255) DEFAULT NULL,
  `CityMunicipality` varchar(255) DEFAULT NULL,
  `Province` varchar(255) DEFAULT NULL,
  `PostalCode` varchar(20) DEFAULT NULL,
  `ContactNumber` varchar(15) DEFAULT NULL,
  `EmailAddress` varchar(100) DEFAULT NULL,
  `EmergencyContactPerson` varchar(100) DEFAULT NULL,
  `EmergencyRelationship` varchar(50) DEFAULT NULL,
  `EmergencyContactNumber` varchar(15) DEFAULT NULL,
  `EmergencyAddress` text DEFAULT NULL,
  `Category` set('New Freshman','Continuing/Returnee','Shiftee','Second Degree','Regular','Irregular') NOT NULL,
  `YearLevel` enum('1','2','3','4') DEFAULT NULL,
  `Password` varchar(255) NOT NULL,
  `Image` varchar(255) DEFAULT NULL,
  `Academic` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `NonAcademic` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `Status` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblstudent`
--

INSERT INTO `tblstudent` (`ID`, `StuID`, `FamilyName`, `FirstName`, `MiddleName`, `Program`, `Major`, `LearnersReferenceNo`, `DOB`, `PlaceOfBirth`, `Gender`, `CivilStatus`, `Religion`, `Height`, `Weight`, `Citizenship`, `FathersName`, `MothersMaidenName`, `BuildingHouseNumber`, `StreetName`, `Barangay`, `CityMunicipality`, `Province`, `PostalCode`, `ContactNumber`, `EmailAddress`, `EmergencyContactPerson`, `EmergencyRelationship`, `EmergencyContactNumber`, `EmergencyAddress`, `Category`, `YearLevel`, `Password`, `Image`, `Academic`, `NonAcademic`, `Status`) VALUES
(1, '222 - 08412', 'Senilla', 'Jayriel', 'Longakit', 'BSIT', 'InfoTech', '119323090042', '2004-04-09', 'idk', 'Mechanic', 'single', 'Roman Catholic', '164cm', '57kg', 'Filipino', 'Arnulfo Senilla', 'Jennifer Longakit', '587-A', 'Carnation street', 'Casili', 'Consolacion', 'Cebu', '6014', '09319106644', 'jayriel@test.com', 'jaynard senilla', 'brother', '09238263741', 'idk', 'Regular', '4', '$2y$10$m4ESgI.efyRszHdUes2XiOf9d606./wyvvN7nDoKptDxHWypKqik6', 'pfpjm.jfif', 'Best in Art', 'Mobile Legends', 1),
(2, '222 - 08410', 'Ypil', 'John Mar', 'Hortilana', 'Bachelor of Science in Information Technology (BSIT)', '', '119323090040', '2002-12-22', 'idk', 'Male', 'Single', 'Roman Catholic', '164cm', '57cm', 'Filipino', 'idk', 'idk', 'idk', 'idk', 'idk', 'Mandaue City', 'Cebu', '6004', '09319106644', 'ypil.johnmar.mcc@gmail.com', 'Denise', 'wife', '09238263740', 'idk', '', '', '$2y$10$RV2LjH1NklSikQGVOfXIk.jPsI6TRI6NX8lVrIJ4NU6b.uvTlLwki', 'pfpjm.jfif', 'Best in Art', 'Mobile Legends', 1),
(3, '112233', 'Canonio', 'Jezrah Faith', 'Conde', 'BSIT', 'InfoTech', '119323090041', '2004-05-13', 'idk', 'Female', 'Single', 'Roman Catholic', '164cm', '49kg', 'Filipino', 'EdilJr Canonio', 'Nimfa Conde', 'idk', 'idk', 'idk', 'idk', 'idk', '6004', '09319106639', 'canonio.jezrahfaith.mcc@gmail.com', 'Marissa Canonio', 'Step Mother', '09238263740', 'idk', 'Regular', '4', '$2y$10$7B0fv2araaGH2rbuhL72x.SrdIKlNwGDbjkhFlXE5ZyECsEV5GqzW', 'pfpjez.jfif', NULL, NULL, 1),
(9, '2025-001', 'Garcia', 'Juan', 'Santos', 'BSIT', 'Software Engineering', 'LRN12345', '2003-05-12', 'Manila', 'Male', 'Single', 'Catholic', '170', '65', 'Filipino', 'Pedro Garcia', 'Maria Santos', '123', 'Mabini St', 'Barangay 1', 'Quezon City', 'Metro Manila', '1100', '09171234567', 'juan.garcia@example.com', 'Ana Garcia', 'Sister', '09181234567', '123 Mabini St, QC', 'Regular', '', 'ad6a280417a0f533d8b670c61667e1a0', NULL, NULL, NULL, 1),
(10, '2025-002', 'Reyes', 'Maria', 'Lopez', 'BSBA', 'Marketing', 'LRN67890', '2002-11-23', 'Cebu', 'Female', 'Single', 'Catholic', '160', '50', 'Filipino', 'Jose Reyes', 'Elena Lopez', '456', 'Rizal Ave', 'Barangay 5', 'Cebu City', 'Cebu', '6000', '09991234567', 'maria.reyes@example.com', 'Jose Reyes', 'Father', '09981234567', '456 Rizal Ave, Cebu', 'Regular', '', 'ad6a280417a0f533d8b670c61667e1a0', NULL, NULL, NULL, 1),
(11, '2025-003', 'Cruz', 'Mark', 'Antonio', 'BSCE', 'Structural Engineering', 'LRN54321', '2001-07-19', 'Davao', 'Male', 'Married', 'Christian', '175', '70', 'Filipino', 'Andres Cruz', 'Luz Antonio', '789', 'Bonifacio St', 'Barangay 10', 'Davao City', 'Davao del Sur', '8000', '09221234567', 'mark.cruz@example.com', 'Anna Cruz', 'Wife', '09281234567', '789 Bonifacio St, Davao', 'Irregular', '', 'ad6a280417a0f533d8b670c61667e1a0', NULL, NULL, NULL, 1),
(12, '2025-004', 'Torres', 'Angela', 'Dela Cruz', 'BSN', 'Nursing', 'LRN98765', '2004-01-05', 'Baguio', 'Female', 'Single', 'Catholic', '158', '48', 'Filipino', 'Mario Torres', 'Cristina Dela Cruz', '321', 'Session Rd', 'Barangay 3', 'Baguio City', 'Benguet', '2600', '09331234567', 'angela.torres@example.com', 'Cristina Dela Cruz', 'Mother', '09381234567', '321 Session Rd, Baguio', 'Regular', '', 'ad6a280417a0f533d8b670c61667e1a0', NULL, NULL, NULL, 1),
(13, '2025-005', 'Villanueva', 'Jose', 'Martinez', 'BSA', 'Accounting', 'LRN19283', '2003-09-30', 'Iloilo', 'Male', 'Single', 'Catholic', '172', '68', 'Filipino', 'Ramon Villanueva', 'Teresa Martinez', '654', 'Lopez Jaena St', 'Barangay 8', 'Iloilo City', 'Iloilo', '5000', '09451234567', 'jose.villanueva@example.com', 'Teresa Martinez', 'Mother', '09481234567', '654 Lopez Jaena St, Iloilo', 'Regular', '4', 'ad6a280417a0f533d8b670c61667e1a0', NULL, NULL, NULL, 1),
(14, 'TEST123', 'Smith', 'John', 'A', 'BSCS', 'Software', '', '0000-00-00', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '$2y$10$HI12KNlEI6sXFFYSfwPz5O2vWo4kVHPJS9jGjbTKEgcmskAKBAVyW', NULL, NULL, NULL, 1),
(15, '1001', 'Santos', 'Juan', 'Dela Cruz', 'BSIT', 'Software Engineering', 'LRN12345', '2002-05-14', 'Manila', 'Male', 'Single', 'Catholic', '170', '65', 'Filipino', 'Pedro Santos', 'Maria Dela Cruz', '123', 'Rizal St.', 'Barangay 1', 'Quezon City', 'Metro Manila', '1100', '09171234567', 'juan.santos@email.com', 'Ana Santos', 'Mother', '09181234567', '123 Rizal St., QC', 'Regular', '', '$2y$10$MhCRDxWeewte4q8N62uGK.dB7UQZhjJYnbHkskAKC9qtGWXhNlfAe', NULL, NULL, NULL, 1),
(16, '1002', 'Reyes', 'Maria', 'Lopez', 'BSBA', 'Marketing', 'LRN54321', '2001-11-20', 'Cebu City', 'Female', 'Single', 'Catholic', '160', '50', 'Filipino', 'Jose Reyes', 'Carmen Lopez', '45', 'Mabini St.', 'Barangay Central', 'Cebu City', 'Cebu', '6000', '09271234567', 'maria.reyes@email.com', 'Jose Reyes', 'Father', '09281234567', '45 Mabini St., Cebu', 'Regular', '', '$2y$10$MhCRDxWeewte4q8N62uGK.dB7UQZhjJYnbHkskAKC9qtGWXhNlfAe', NULL, NULL, NULL, 1),
(17, '1003', 'Cruz', 'Mark', 'Antonio', 'BSCS', 'Data Science', 'LRN67890', '2003-03-08', 'Davao City', 'Male', 'Single', 'Christian', '175', '70', 'Filipino', 'Ramon Cruz', 'Luz Antonio', '67', 'Bonifacio St.', 'Barangay 2', 'Davao City', 'Davao del Sur', '8000', '09391234567', 'mark.cruz@email.com', 'Luz Antonio', 'Mother', '09381234567', '67 Bonifacio St., Davao', 'Regular', '', '$2y$10$MhCRDxWeewte4q8N62uGK.dB7UQZhjJYnbHkskAKC9qtGWXhNlfAe', NULL, NULL, NULL, 1),
(18, '1004', 'Garcia', 'Ana', 'Mendoza', 'BSED', 'English', 'LRN98765', '2000-09-15', 'Baguio City', 'Female', 'Married', 'Catholic', '158', '48', 'Filipino', 'Mario Garcia', 'Elena Mendoza', '89', 'Session Rd.', 'Barangay West', 'Baguio City', 'Benguet', '2600', '09451234567', 'ana.garcia@email.com', 'Mario Garcia', 'Father', '09481234567', '89 Session Rd., Baguio', 'Irregular', '', '$2y$10$MhCRDxWeewte4q8N62uGK.dB7UQZhjJYnbHkskAKC9qtGWXhNlfAe', NULL, NULL, NULL, 1),
(19, '1005', 'Flores', 'Carlos', 'Ramos', 'BSCE', 'Structural Engineering', 'LRN11223', '2002-01-25', 'Iloilo City', 'Male', 'Single', 'Catholic', '180', '72', 'Filipino', 'Andres Flores', 'Teresa Ramos', '321', 'Lopez Jaena St.', 'Barangay East', 'Iloilo City', 'Iloilo', '5000', '09561234567', 'carlos.flores@email.com', 'Teresa Ramos', 'Mother', '09581234567', '321 Lopez Jaena St., Iloilo', 'Regular', '4', '$2y$10$MhCRDxWeewte4q8N62uGK.dB7UQZhjJYnbHkskAKC9qtGWXhNlfAe', NULL, NULL, NULL, 1),
(20, '12345', 'Crazy', 'Rapid', 'Boots', '', NULL, NULL, '0000-00-00', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'saging@test.com', NULL, NULL, NULL, NULL, '', '1', '$2y$10$3sUTYaeAFwO0ZxbM54afr.2Bn9HeWVpubli/PPnMn8zMRHwOn/5lC', 'anonymous-user.png', NULL, NULL, 1),
(21, '222-08800', 'Canonio', 'Seg Francis', 'Kiem', '', NULL, NULL, '0000-00-00', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'canonio.segfrancis.mcc@gmail.com', NULL, NULL, NULL, NULL, '', '1', '$2y$10$PBmGXFQarxtc0i1lOhoN7Otgp1kz9v4wXSLDIAnTIDPl58FWiiHrW', 'Screenshot 2025-02-13 182718.png', NULL, NULL, 1),
(22, '1111', 'Senilla', 'Senilla', 'Sam', '', NULL, NULL, '0000-00-00', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'jayriel.senilla@mc-college.online', NULL, NULL, NULL, NULL, '', '1', '$2y$10$NeKFtteePIK5KQITC9w/KuaZHAb2FwSe2I/YMkzvUmdQ0kfU2pi0y', '1763278247_Screenshot__235_.png', NULL, NULL, 1),
(26, '111 - 11111', 'Senilla', 'Jaynard', 'Longakit', '', NULL, NULL, '0000-00-00', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'senillajayriel@gmail.com', NULL, NULL, NULL, NULL, '', NULL, '$2y$10$B1q6WTaXxw7/ZLA3t/AoKektMtEKeyaO.XTQBbLiCmZpXfpfiT8iG', NULL, 'Best in Art', 'Mobile Legends', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `achievement_approvals`
--
ALTER TABLE `achievement_approvals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `achievement_id` (`achievement_id`);

--
-- Indexes for table `skills`
--
ALTER TABLE `skills`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `student_achievements`
--
ALTER TABLE `student_achievements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `StuID` (`StuID`),
  ADD KEY `idx_student_achievements_approved_by` (`approved_by`);

--
-- Indexes for table `student_achievement_skills`
--
ALTER TABLE `student_achievement_skills`
  ADD PRIMARY KEY (`id`),
  ADD KEY `achievement_id` (`achievement_id`),
  ADD KEY `skill_id` (`skill_id`);

--
-- Indexes for table `tbladmin`
--
ALTER TABLE `tbladmin`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `tblmessages`
--
ALTER TABLE `tblmessages`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `recipient_read_idx` (`RecipientStuID`,`IsRead`);

--
-- Indexes for table `tblnotice`
--
ALTER TABLE `tblnotice`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `tblpage`
--
ALTER TABLE `tblpage`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `tblpublicnotice`
--
ALTER TABLE `tblpublicnotice`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `tblskills`
--
ALTER TABLE `tblskills`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_tblskills_name` (`name`);

--
-- Indexes for table `tblstaff`
--
ALTER TABLE `tblstaff`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `UserName` (`UserName`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Indexes for table `tblstudent`
--
ALTER TABLE `tblstudent`
  ADD PRIMARY KEY (`ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `achievement_approvals`
--
ALTER TABLE `achievement_approvals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `skills`
--
ALTER TABLE `skills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `student_achievements`
--
ALTER TABLE `student_achievements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `student_achievement_skills`
--
ALTER TABLE `student_achievement_skills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `tbladmin`
--
ALTER TABLE `tbladmin`
  MODIFY `ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tblmessages`
--
ALTER TABLE `tblmessages`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tblnotice`
--
ALTER TABLE `tblnotice`
  MODIFY `ID` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `tblpage`
--
ALTER TABLE `tblpage`
  MODIFY `ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tblpublicnotice`
--
ALTER TABLE `tblpublicnotice`
  MODIFY `ID` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tblskills`
--
ALTER TABLE `tblskills`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `tblstaff`
--
ALTER TABLE `tblstaff`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tblstudent`
--
ALTER TABLE `tblstudent`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `achievement_approvals`
--
ALTER TABLE `achievement_approvals`
  ADD CONSTRAINT `achievement_approvals_ibfk_1` FOREIGN KEY (`achievement_id`) REFERENCES `student_achievements` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_achievement_skills`
--
ALTER TABLE `student_achievement_skills`
  ADD CONSTRAINT `student_achievement_skills_ibfk_1` FOREIGN KEY (`achievement_id`) REFERENCES `student_achievements` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_achievement_skills_ibfk_2` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
