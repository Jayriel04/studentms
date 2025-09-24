-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 24, 2025 at 04:38 PM
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
  `YearLevel` enum('1','2','3','4') NOT NULL,
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
(1, '222- 08412', 'Senilla', 'Jayriel', 'Longakit', 'BSIT', 'InfoTech', '119323090042', '2004-04-09', 'idk', 'Mechanic', 'single', 'Roman Catholic', '164cm', '57kg', 'Filipino', 'Arnulfo Senilla', 'Jennifer Longakit', '587-A', 'Carnation street', 'Casili', 'Consolacion', 'Cebu', '6014', '09319106644', 'jayriel@test.com', 'jaynard senilla', 'brother', '09238263741', 'idk', 'Regular', '4', 'ad6a280417a0f533d8b670c61667e1a0', 'pfpjm.jfif', NULL, 'Sipak Takraw', 1),
(2, '222- 08410', 'Ypil', 'John Mar', 'Hortilana', 'BSIT', 'InfoTech', '119323090040', '2002-12-22', 'idk', 'Gay', 'single', 'Roman Catholic', '164cm', '57cm', 'Filipino', 'idk', 'idk', 'idk', 'idk', 'idk', 'idk', 'idk', '6004', '09319106644', 'ypil.johnmar.mcc@gmail.com', 'Denise', 'wife', '09238263740', 'idk', 'Regular', '4', '$2y$10$sBdrj5NJOVA1uMg8x1Mleee4cGXEvOscqq9oQpPz8zEvWU1nzH6Fe', 'pfpjm.jfif', NULL, 'Badminton, Basketball', 1),
(3, '222- 08411', 'Canonio', 'Jezrah Faith', 'Conde', 'BSIT', 'InfoTech', '119323090041', '2004-05-13', 'idk', 'Female', 'Single', 'Roman Catholic', '164cm', '49kg', 'Filipino', 'EdilJr Canonio', 'Nimfa Conde', 'idk', 'idk', 'idk', 'idk', 'idk', '6004', '09319106639', 'jezrah@test.com', 'Marissa Canonio', 'Step Mother', '09238263740', 'idk', 'Regular', '4', '', NULL, NULL, NULL, 1),
(9, '2025-001', 'Garcia', 'Juan', 'Santos', 'BSIT', 'Software Engineering', 'LRN12345', '2003-05-12', 'Manila', 'Male', 'Single', 'Catholic', '170', '65', 'Filipino', 'Pedro Garcia', 'Maria Santos', '123', 'Mabini St', 'Barangay 1', 'Quezon City', 'Metro Manila', '1100', '09171234567', 'juan.garcia@example.com', 'Ana Garcia', 'Sister', '09181234567', '123 Mabini St, QC', 'Regular', '', 'ad6a280417a0f533d8b670c61667e1a0', NULL, NULL, NULL, 1),
(10, '2025-002', 'Reyes', 'Maria', 'Lopez', 'BSBA', 'Marketing', 'LRN67890', '2002-11-23', 'Cebu', 'Female', 'Single', 'Catholic', '160', '50', 'Filipino', 'Jose Reyes', 'Elena Lopez', '456', 'Rizal Ave', 'Barangay 5', 'Cebu City', 'Cebu', '6000', '09991234567', 'maria.reyes@example.com', 'Jose Reyes', 'Father', '09981234567', '456 Rizal Ave, Cebu', 'Regular', '', 'ad6a280417a0f533d8b670c61667e1a0', NULL, NULL, NULL, 1),
(11, '2025-003', 'Cruz', 'Mark', 'Antonio', 'BSCE', 'Structural Engineering', 'LRN54321', '2001-07-19', 'Davao', 'Male', 'Married', 'Christian', '175', '70', 'Filipino', 'Andres Cruz', 'Luz Antonio', '789', 'Bonifacio St', 'Barangay 10', 'Davao City', 'Davao del Sur', '8000', '09221234567', 'mark.cruz@example.com', 'Anna Cruz', 'Wife', '09281234567', '789 Bonifacio St, Davao', 'Irregular', '', 'ad6a280417a0f533d8b670c61667e1a0', NULL, NULL, NULL, 1),
(12, '2025-004', 'Torres', 'Angela', 'Dela Cruz', 'BSN', 'Nursing', 'LRN98765', '2004-01-05', 'Baguio', 'Female', 'Single', 'Catholic', '158', '48', 'Filipino', 'Mario Torres', 'Cristina Dela Cruz', '321', 'Session Rd', 'Barangay 3', 'Baguio City', 'Benguet', '2600', '09331234567', 'angela.torres@example.com', 'Cristina Dela Cruz', 'Mother', '09381234567', '321 Session Rd, Baguio', 'Regular', '', 'ad6a280417a0f533d8b670c61667e1a0', NULL, NULL, NULL, 1),
(13, '2025-005', 'Villanueva', 'Jose', 'Martinez', 'BSA', 'Accounting', 'LRN19283', '2003-09-30', 'Iloilo', 'Male', 'Single', 'Catholic', '172', '68', 'Filipino', 'Ramon Villanueva', 'Teresa Martinez', '654', 'Lopez Jaena St', 'Barangay 8', 'Iloilo City', 'Iloilo', '5000', '09451234567', 'jose.villanueva@example.com', 'Teresa Martinez', 'Mother', '09481234567', '654 Lopez Jaena St, Iloilo', 'Regular', '4', 'ad6a280417a0f533d8b670c61667e1a0', NULL, NULL, NULL, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tblstudent`
--
ALTER TABLE `tblstudent`
  ADD PRIMARY KEY (`ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tblstudent`
--
ALTER TABLE `tblstudent`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
