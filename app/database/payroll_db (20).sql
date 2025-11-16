-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 26, 2025 at 09:08 AM
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
-- Database: `payroll_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `hr_employee_id` varchar(50) NOT NULL,
  `hr_first_name` varchar(100) NOT NULL,
  `hr_middle_name` varchar(100) DEFAULT NULL,
  `hr_last_name` varchar(100) NOT NULL,
  `hr_email` varchar(100) NOT NULL,
  `hr_position` varchar(100) NOT NULL DEFAULT 'Human Resources',
  `hr_rfid_number` varchar(50) DEFAULT NULL,
  `hr_dob` date DEFAULT NULL,
  `hr_place_of_birth` varchar(100) DEFAULT NULL,
  `hr_sex` varchar(10) DEFAULT NULL,
  `hr_civil_status` varchar(20) DEFAULT NULL,
  `hr_contact_number` varchar(15) DEFAULT NULL,
  `hr_citizenship` varchar(50) DEFAULT NULL,
  `hr_blood_type` varchar(5) DEFAULT NULL,
  `hr_address` varchar(255) DEFAULT NULL,
  `hr_base_salary` decimal(10,2) DEFAULT NULL,
  `hr_sss_number` varchar(20) DEFAULT NULL,
  `hr_pagibig_number` varchar(20) DEFAULT NULL,
  `hr_philhealth_number` varchar(20) DEFAULT NULL,
  `hr_photo_path` varchar(255) DEFAULT NULL,
  `hr_password` varchar(255) NOT NULL,
  `hr_created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `hr_updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `hr_employee_id`, `hr_first_name`, `hr_middle_name`, `hr_last_name`, `hr_email`, `hr_position`, `hr_rfid_number`, `hr_dob`, `hr_place_of_birth`, `hr_sex`, `hr_civil_status`, `hr_contact_number`, `hr_citizenship`, `hr_blood_type`, `hr_address`, `hr_base_salary`, `hr_sss_number`, `hr_pagibig_number`, `hr_philhealth_number`, `hr_photo_path`, `hr_password`, `hr_created_at`, `hr_updated_at`, `deleted_at`) VALUES
(5, '', 'Roy B. Nabesis', NULL, '', 'admin@example.com', 'Human Resources', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'upload/me-68d16ab0dfb1a3.65533688.jpg', '$argon2id$v=19$m=65536,t=4,p=3$L3RVLzl2UGY2cldjMDlaYw$MBNaSNBK6za6l5R6HpA9dpWA9G5RNcHL7edcKLmguyc', '2025-05-24 14:55:09', '2025-10-26 08:07:19', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `manager_id` int(11) DEFAULT NULL,
  `date` date NOT NULL,
  `morning_in` time DEFAULT NULL,
  `morning_out` time DEFAULT NULL,
  `afternoon_in` time DEFAULT NULL,
  `afternoon_out` time DEFAULT NULL,
  `status` enum('Present','Absent','Late','On Leave') DEFAULT 'Present',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `employee_id`, `manager_id`, `date`, `morning_in`, `morning_out`, `afternoon_in`, `afternoon_out`, `status`, `created_at`, `updated_at`) VALUES
(87, 118, NULL, '2025-05-25', NULL, NULL, NULL, NULL, 'Present', '2025-05-25 10:15:43', '2025-05-25 10:40:03'),
(88, 116, NULL, '2025-05-25', NULL, NULL, NULL, NULL, 'Present', '2025-05-25 10:16:37', '2025-05-25 10:51:13'),
(89, 117, NULL, '2025-05-25', NULL, NULL, NULL, NULL, 'Present', '2025-05-25 10:16:53', '2025-05-25 10:45:52'),
(90, 115, NULL, '2025-05-25', NULL, NULL, NULL, NULL, 'Present', '2025-05-25 10:16:57', '2025-05-25 10:45:38'),
(91, 116, NULL, '2025-05-26', NULL, NULL, NULL, NULL, 'Present', '2025-05-26 07:58:37', '2025-05-26 07:58:37'),
(93, 118, NULL, '2025-05-28', NULL, NULL, NULL, NULL, 'Present', '2025-05-27 16:02:05', '2025-05-27 17:54:42'),
(94, 115, NULL, '2025-05-28', NULL, NULL, NULL, NULL, 'Present', '2025-05-27 16:02:08', '2025-05-27 17:55:55'),
(95, 117, NULL, '2025-05-28', NULL, NULL, NULL, NULL, 'Present', '2025-05-27 16:02:11', '2025-05-27 17:55:44'),
(96, 116, NULL, '2025-05-28', NULL, NULL, NULL, NULL, 'Present', '2025-05-27 16:02:15', '2025-05-27 17:55:24'),
(97, 124, NULL, '2025-05-30', NULL, NULL, NULL, NULL, 'Present', '2025-05-30 01:19:47', '2025-05-30 01:19:47'),
(98, 118, NULL, '2025-05-30', NULL, NULL, NULL, NULL, 'Present', '2025-05-30 01:21:43', '2025-05-30 01:21:43'),
(99, 117, NULL, '2025-05-30', NULL, NULL, NULL, NULL, 'Present', '2025-05-30 01:41:31', '2025-05-30 01:41:31'),
(107, 115, NULL, '2025-06-14', '21:50:07', '21:50:48', '21:55:30', '22:17:28', 'Present', '2025-06-14 13:50:07', '2025-06-14 14:17:28'),
(108, 117, NULL, '2025-06-14', '21:50:10', '21:51:40', '21:55:44', NULL, 'Present', '2025-06-14 13:50:10', '2025-06-14 13:55:44'),
(109, 116, NULL, '2025-06-14', '21:50:39', '21:52:04', '21:59:57', NULL, 'Present', '2025-06-14 13:50:39', '2025-06-14 13:59:57'),
(110, 115, NULL, '2025-06-15', '21:18:02', '22:18:19', '22:18:25', '22:18:32', 'Present', '2025-06-15 13:18:02', '2025-06-15 14:18:32'),
(111, 116, NULL, '2025-06-15', '22:27:01', '22:43:30', '23:29:00', '23:29:00', 'Present', '2025-06-15 14:27:01', '2025-06-15 15:29:00'),
(112, 115, NULL, '2025-06-16', '00:17:07', NULL, NULL, NULL, 'Present', '2025-06-15 16:17:07', '2025-06-15 16:17:07'),
(114, 115, NULL, '2025-07-08', '06:01:45', '11:01:49', '15:27:44', '15:28:08', 'Present', '2025-07-08 07:01:45', '2025-07-08 07:28:08'),
(115, 115, NULL, '2025-07-15', '21:07:05', '21:17:18', '21:28:12', '22:06:58', 'Present', '2025-07-15 13:07:05', '2025-07-15 14:06:58'),
(121, 116, NULL, '2025-07-15', '22:47:30', '22:49:27', '22:49:39', '22:53:48', 'Present', '2025-07-15 14:47:30', '2025-07-15 14:53:48'),
(122, 118, NULL, '2025-07-15', '22:56:11', '22:57:00', '23:19:43', '23:19:49', 'Present', '2025-07-15 14:56:11', '2025-07-15 15:19:49'),
(123, 117, NULL, '2025-07-15', '22:56:17', '22:57:16', '23:46:29', '23:46:34', 'Present', '2025-07-15 14:56:17', '2025-07-15 15:46:34'),
(124, 118, NULL, '2025-07-17', '22:57:10', '22:57:14', '22:57:15', '22:57:18', 'Present', '2025-07-17 14:57:10', '2025-07-17 14:57:18'),
(125, 118, NULL, '2025-07-23', '14:37:06', '14:37:29', NULL, NULL, 'Present', '2025-07-23 06:37:06', '2025-07-23 06:37:29'),
(126, 180, NULL, '2025-08-27', NULL, NULL, '15:58:19', '15:58:33', 'Present', '2025-08-27 07:58:19', '2025-08-27 07:58:33'),
(127, 117, NULL, '2025-09-26', NULL, NULL, '14:34:30', NULL, 'Present', '2025-09-26 06:34:30', '2025-09-26 06:34:30'),
(129, NULL, 4, '2025-09-27', NULL, NULL, '20:40:18', '20:42:08', 'Present', '2025-09-27 12:40:18', '2025-09-27 12:42:08');

-- --------------------------------------------------------

--
-- Table structure for table `benefit_rates`
--

CREATE TABLE `benefit_rates` (
  `id` int(11) NOT NULL,
  `sss_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `pagibig_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `philhealth_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `status` enum('active','inactive') NOT NULL DEFAULT 'inactive',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `benefit_rates`
--

INSERT INTO `benefit_rates` (`id`, `sss_rate`, `pagibig_rate`, `philhealth_rate`, `status`, `updated_at`) VALUES
(39, 7.00, 7.00, 5.00, 'inactive', '2025-07-15 13:04:16'),
(41, 5.00, 5.00, 5.00, 'inactive', '2025-08-17 17:07:55'),
(42, 5.00, 5.00, 5.00, 'inactive', '2025-09-18 14:08:06'),
(43, 5.00, 5.00, 5.00, 'inactive', '2025-09-18 14:08:17'),
(44, 5.00, 5.00, 2.00, 'inactive', '2025-09-18 14:08:47'),
(45, 5.00, 5.00, 2.00, 'inactive', '2025-09-18 14:09:02'),
(48, 5.00, 2.00, 5.00, 'inactive', '2025-09-27 12:11:28'),
(49, 5.00, 2.00, 5.00, 'active', '2025-09-27 12:11:28');

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `name`, `address`, `created_at`, `updated_at`) VALUES
(8, 'LMG Co., Ltd', 'Tagum', '2025-10-05 14:03:05', '2025-10-05 14:03:05'),
(9, 'Global Marketing Alliance', 'Panabo', '2025-10-05 14:03:26', '2025-10-05 14:03:26'),
(10, 'Expressway Liaison Services', 'Tagum', '2025-10-05 14:03:54', '2025-10-05 14:03:54'),
(11, 'test', 'test', '2025-10-08 11:15:52', '2025-10-08 11:15:52'),
(14, 'test 1', 'test 1', '2025-10-08 13:08:40', '2025-10-08 13:09:19'),
(15, 'test 3', 'test 3', '2025-10-18 16:05:40', '2025-10-18 16:05:40');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `employee_no` varchar(20) NOT NULL,
  `rfid_number` varchar(50) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `dob` date NOT NULL,
  `place_of_birth` varchar(100) NOT NULL,
  `sex` enum('Male','Female') NOT NULL,
  `civil_status` enum('Single','Married','Separated','Divorced','Widowed') NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `citizenship` varchar(50) NOT NULL,
  `blood_type` enum('A+','A-','B+','B-','O+','O-','AB+','AB-') NOT NULL,
  `position` enum('Manager','Human Resources','Staff','Driver') DEFAULT NULL,
  `address` text NOT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `base_salary` decimal(10,2) NOT NULL,
  `sss_number` varchar(20) NOT NULL,
  `pagibig_number` varchar(20) NOT NULL,
  `philhealth_number` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `branch_manager` int(11) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `approved_by_manager` tinyint(1) DEFAULT 0,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `employee_no`, `rfid_number`, `first_name`, `middle_name`, `last_name`, `dob`, `place_of_birth`, `sex`, `civil_status`, `contact_number`, `email`, `citizenship`, `blood_type`, `position`, `address`, `photo_path`, `base_salary`, `sss_number`, `pagibig_number`, `philhealth_number`, `created_at`, `updated_at`, `branch_manager`, `deleted_at`, `approved_by_manager`, `password`) VALUES
(115, 'EMP-420415', '3749540708', 'roy', 'basillote', 'nabesis', '2000-11-08', 'Davao city', 'Male', 'Single', '09923962962', 'roynabesis.main@gmail.com', 'Filipino', 'O+', 'Human Resources', 'Purok 7 - San miguel, San isidro, Bunawan, Davao city', 'upload/Roy Nabesis.jpg', 600.00, '359358793535', '562346211111', '235235235333', '2025-05-25 09:42:16', '2025-09-12 18:28:58', 4, NULL, 1, '$2y$10$yxOukkwgROuId1csxMR2P.ydxgKv3SHm8r7cgqZ9tdA7ngbYGmLn.'),
(116, 'EMP-399188', '3750206196', 'ronn charles', 'o', 'domingo', '2003-08-18', 'davao city', 'Male', 'Married', '09911344678', 'ronncharlesd8@gmail.com', 'Filipino', 'AB+', 'Driver', 'purok 18, Upper New Visayas Panabo City', 'upload/Ronn Charles Domingo.jpg', 600.00, '768936793986', '734892735893', '839486786354', '2025-05-25 09:45:08', '2025-09-07 17:23:25', 3, NULL, 1, '$argon2id$v=19$m=65536,t=4,p=3$VGRvQ1lnbENud1NqTDJrZw$VkOEQnTJSrNEQaf1iVaOiabYJe5XXRXE1xa/ZS62Cwo'),
(117, 'EMP-853677', '3749150996', 'ken jazver', 'v', 'galanido', '2002-12-08', 'panabo city', 'Male', 'Single', '09562876824', 'terraken08@gmail.com', 'Filipino', 'O+', 'Manager', 'sharon, new visayas, panabo city', 'upload/Ken Jazver Galanido.jpg', 600.00, '873460367222', '757835913333', '725987523522', '2025-05-25 09:47:50', '2025-09-07 17:23:25', 4, NULL, 1, '$argon2id$v=19$m=65536,t=4,p=3$VGRvQ1lnbENud1NqTDJrZw$VkOEQnTJSrNEQaf1iVaOiabYJe5XXRXE1xa/ZS62Cwo'),
(118, 'EMP-517859', '3749734948', 'giovan kier', 'v', 'cardenio', '2002-05-13', 'medical mission group hospital', 'Male', 'Single', '09260829512', 'kiercardenio@gmail.com', 'Chinese', 'B+', 'Staff', 'purok everlasting, brgy. gredu, panabo city', 'upload/Giovan Kier Cardenio.jpg', 600.00, '828924729858', '456346345654', '235246726256', '2025-05-25 09:50:52', '2025-09-07 17:23:25', 2, NULL, 1, '$argon2id$v=19$m=65536,t=4,p=3$VGRvQ1lnbENud1NqTDJrZw$VkOEQnTJSrNEQaf1iVaOiabYJe5XXRXE1xa/ZS62Cwo'),
(122, 'EMP-661929', '4563465646', 'Kres-Ann', 'Bodiongan', 'Oclarit', '1998-11-09', 'Tagana-an', 'Female', 'Single', '09913692194', 'koclarit@dnsc.edu.ph', 'Filipino', 'O+', 'Staff', 'Panabo City', 'upload/man (1).png', 1200.00, '111111111111', '647836458589', '535424354659', '2025-05-29 07:18:49', '2025-09-07 17:23:25', 3, NULL, 1, '$argon2id$v=19$m=65536,t=4,p=3$VGRvQ1lnbENud1NqTDJrZw$VkOEQnTJSrNEQaf1iVaOiabYJe5XXRXE1xa/ZS62Cwo'),
(124, 'EMP-699453', '4182319604', 'test', 'test', 'test', '2025-06-07', 'Tagana-an', 'Male', 'Separated', '09544356534', 'jhdfsjfhds@gmail.com', 'Filipino', 'O-', 'Staff', 'Panabo City', NULL, 600.00, '424234234234', '523532532534', '122423423511', '2025-05-30 01:15:26', '2025-09-07 17:23:25', 4, NULL, 1, '$argon2id$v=19$m=65536,t=4,p=3$VGRvQ1lnbENud1NqTDJrZw$VkOEQnTJSrNEQaf1iVaOiabYJe5XXRXE1xa/ZS62Cwo'),
(125, 'EMP-153970', '234234', 'test 2', 'test 2', 'test 2', '2025-06-01', 'adsa', 'Female', 'Widowed', '09847236523', 'dasdjk@gmail.com', 'Filipino', 'B+', 'Human Resources', 'sfasdffasf', NULL, 600.00, '542353451111', '234523523111', '236345634111', '2025-06-01 12:32:59', '2025-09-07 17:23:25', 3, NULL, 1, '$argon2id$v=19$m=65536,t=4,p=3$VGRvQ1lnbENud1NqTDJrZw$VkOEQnTJSrNEQaf1iVaOiabYJe5XXRXE1xa/ZS62Cwo'),
(129, 'EMP-178957', '4213423', 'asdasd', 'dads', 'asdsad', '2025-06-04', 'sfsfsdf', 'Male', 'Married', '09872783648', 'ronitaborbonnabesis@gmail.com', 'dadas', 'O-', 'Human Resources', 'fgasdgsadg', NULL, 100.00, '412412412431', '123124124124', '412412321321', '2025-06-04 11:29:10', '2025-09-07 17:23:25', 2, NULL, 1, '$argon2id$v=19$m=65536,t=4,p=3$VGRvQ1lnbENud1NqTDJrZw$VkOEQnTJSrNEQaf1iVaOiabYJe5XXRXE1xa/ZS62Cwo'),
(138, 'EMP-377199', '4242134234', 'giovan kier', 'v', 'cardenio', '2025-06-06', 'asasdfsadf', 'Male', 'Married', '09675463534', 'solicar.junel.4@gmail.com', 'Filipino', 'O+', 'Staff', 'knhjkhh', NULL, 600.00, '674563868770', '097896456536', '987875456756', '2025-06-06 08:57:47', '2025-06-06 10:45:13', 3, '2025-06-06 18:45:13', 1, NULL),
(139, 'EMP-306215', '5455455', 'jkggkgffg', 'hkgjhg', 'jhjkh', '2025-06-06', 'kjklj', 'Female', 'Married', '09546546546', 'fdfgdg@gmail.com', 'ftghffhj', 'O-', 'Manager', 'jhgvjhghg', NULL, 55.00, '404898046546', '098709880545', '094898086505', '2025-06-06 13:12:47', '2025-09-07 17:23:25', 3, NULL, 0, '$argon2id$v=19$m=65536,t=4,p=3$VGRvQ1lnbENud1NqTDJrZw$VkOEQnTJSrNEQaf1iVaOiabYJe5XXRXE1xa/ZS62Cwo'),
(140, 'EMP-444488', '24234', 'gdfgdgd', NULL, 'dfgdfgd', '2025-06-25', 'gdfgdfg', 'Male', 'Divorced', '09574563456', 'dgfdfg@gmail.com', 'gsdgdsfg', 'A+', 'Manager', 'gsdfgdsfg', NULL, 123.00, '463636345636', '254673735625', '624625656214', '2025-06-25 15:30:29', '2025-09-07 17:23:25', NULL, NULL, 0, '$argon2id$v=19$m=65536,t=4,p=3$VGRvQ1lnbENud1NqTDJrZw$VkOEQnTJSrNEQaf1iVaOiabYJe5XXRXE1xa/ZS62Cwo'),
(141, 'EMP-436584', '09067456', 'huhuhu', 'hahaha', 'hehehe', '2025-06-25', 'fasfsf', 'Male', 'Divorced', '09777111111', 'aaaa@gmai.com', 'aaaa', 'A+', 'Manager', 'aaaaaa', 'upload/emp_68668555b16416.40350052.jpg', 123.00, '313123133111', '141241211111', '141412311111', '2025-06-25 15:42:41', '2025-09-07 17:23:25', 4, NULL, 1, '$argon2id$v=19$m=65536,t=4,p=3$VGRvQ1lnbENud1NqTDJrZw$VkOEQnTJSrNEQaf1iVaOiabYJe5XXRXE1xa/ZS62Cwo'),
(143, 'EMP-465183', '45435', 'fasfs', 'dafaf', 'daasd', '2025-06-25', 'asdas', 'Male', 'Single', '09258483578', 'fsdfsdf@gmail.com', 'dasd', 'A+', 'Human Resources', 'dasd', NULL, 213.00, '124213425252', '123515345345', '125145345646', '2025-06-25 17:56:16', '2025-06-25 18:12:03', 4, '2025-06-26 02:12:03', 1, NULL),
(145, 'EMP-554279', '242434', 'jinwoo', 'sdfsdf', 'sung', '2025-06-26', 'fsdf', 'Female', 'Separated', '09656456456', 'fsdf@gmail.com', 'Filipino', 'A+', 'Manager', 'dasasd', 'upload/emp_686d1add9c2944.57728013.jpg', 123.00, '124124154124', '523255535234', '414121232141', '2025-06-25 18:04:22', '2025-09-07 17:23:25', 4, NULL, 1, '$argon2id$v=19$m=65536,t=4,p=3$VGRvQ1lnbENud1NqTDJrZw$VkOEQnTJSrNEQaf1iVaOiabYJe5XXRXE1xa/ZS62Cwo'),
(146, 'EMP-484666', '41413123', 'adasd', 'adad', 'aasd', '2025-07-03', 'davao city', 'Male', 'Single', '09564565465', 'dadsdas@gmail.com', 'Filipino', 'A-', 'Human Resources', 'dasd', 'upload/file_00000000975c61f6be519c4594672620_conversation_id=67f90712-5470-8012-aafb-9c1d56f8c6d5&message_id=8f8214ef-bf24-4cea-9efc-407b3ff768cf.png', 100.00, '123412412421', '541312341252', '214521431243', '2025-07-03 12:47:36', '2025-09-07 17:23:25', 4, NULL, 1, '$argon2id$v=19$m=65536,t=4,p=3$VGRvQ1lnbENud1NqTDJrZw$VkOEQnTJSrNEQaf1iVaOiabYJe5XXRXE1xa/ZS62Cwo'),
(147, 'EMP-856735', '13123', 'dadad', 'fdwdaddasd', 'adada', '2025-07-17', 'dasdasd', 'Female', 'Married', '09234522142', 'sgsdgsdfg@gmail.com', 'Filipino', 'O-', 'Human Resources', 'dada', NULL, 123.00, '124155234523', '523531623462', '234626234564', '2025-07-03 12:48:47', '2025-07-20 12:42:16', 4, '2025-07-03 23:06:50', -1, NULL),
(151, 'EMP-167763', '7635636', 'eeqwe', 'fasfga', 'asgasdfg', '2025-07-20', 'sdgeqweq', 'Male', 'Married', '09846126523', 'mongoallstar01@gmail.com', 'Filipino', 'A+', 'Staff', 'fsdfasfg', NULL, 200.00, '536236723623', '723462462222', '262366261111', '2025-07-20 10:50:37', '2025-09-07 17:23:25', 3, NULL, 1, '$argon2id$v=19$m=65536,t=4,p=3$VGRvQ1lnbENud1NqTDJrZw$VkOEQnTJSrNEQaf1iVaOiabYJe5XXRXE1xa/ZS62Cwo'),
(153, 'EMP-320662', '55325', 'asgasga', 'fgasgasg', 'asgasdgsg', '2025-07-20', 'gasdg', 'Male', 'Separated', '09091782165', 'hhsdfgjg@gmail.com', 'Filipino', 'A+', 'Human Resources', 'gsdggsadg', NULL, 323.00, '412152163463', '171561312545', '552521512525', '2025-07-20 11:28:31', '2025-09-07 17:23:26', 4, NULL, 0, '$argon2id$v=19$m=65536,t=4,p=3$VGRvQ1lnbENud1NqTDJrZw$VkOEQnTJSrNEQaf1iVaOiabYJe5XXRXE1xa/ZS62Cwo'),
(154, 'EMP-594968', '41241', 'fasdfsadf', 'fasfsadf', 'asfasfdas', '2025-07-28', 'fasfsad', 'Male', 'Separated', '09057682736', 'fasdfsdf@gmail.com', 'gasdgasgsd', 'A+', 'Manager', 'fasfsadf', NULL, 12312.00, '412412421421', '512522345236', '456727262162', '2025-07-20 11:30:32', '2025-09-07 17:23:26', 4, NULL, 0, '$argon2id$v=19$m=65536,t=4,p=3$VGRvQ1lnbENud1NqTDJrZw$VkOEQnTJSrNEQaf1iVaOiabYJe5XXRXE1xa/ZS62Cwo'),
(158, 'EMP-238778', '24123412', 'asdfasfsadf', 'safasfsda', 'fasfsadf', '2025-07-15', 'fasfsaf', 'Male', 'Separated', '09085478257', 'fsajkha@gmail.com', 'dadafa', 'A+', 'Manager', 'fgasgasg', NULL, 123.00, '154154246547', '267256345324', '623623646246', '2025-07-20 11:42:54', '2025-09-07 17:23:26', 4, NULL, -1, '$argon2id$v=19$m=65536,t=4,p=3$VGRvQ1lnbENud1NqTDJrZw$VkOEQnTJSrNEQaf1iVaOiabYJe5XXRXE1xa/ZS62Cwo'),
(159, 'EMP-257174', '4124214', 'asfasfsa', 'adfasdas', 'afasfasf', '2025-07-30', 'fasdfasdf', 'Male', 'Married', '09954782342', 'afgafg@gmail.com', 'gsfasfas', 'A+', 'Staff', 'fsadfasdfs', NULL, 31312.00, '141242142132', '451241241241', '515253453345', '2025-07-20 11:44:25', '2025-09-07 17:23:26', 3, NULL, 0, '$argon2id$v=19$m=65536,t=4,p=3$VGRvQ1lnbENud1NqTDJrZw$VkOEQnTJSrNEQaf1iVaOiabYJe5XXRXE1xa/ZS62Cwo'),
(160, 'EMP-341898', '5235234124', 'jhe5ttw', 'asdgh', 'adgadfgg', '2025-07-24', 'hhdfhdfhg', 'Female', 'Divorced', '09095782637', 'gsdjgh@gmail.com', 'fasfas', 'A+', 'Manager', 'dasdfasf', NULL, 31234.00, '125435346346', '324562345726', '645634654645', '2025-07-20 11:46:32', '2025-09-07 17:23:26', 3, NULL, 0, '$argon2id$v=19$m=65536,t=4,p=3$VGRvQ1lnbENud1NqTDJrZw$VkOEQnTJSrNEQaf1iVaOiabYJe5XXRXE1xa/ZS62Cwo'),
(162, 'EMP-605829', '1234123', 'safsadf', 'asfasf', 'fasdfsadf', '2025-07-20', 'asfgsag', 'Male', 'Married', '09941662164', 'asgasdg@gmail.com', 'kjjfsafas', 'A+', 'Staff', 'fasfsadf', NULL, 3213.00, '141241241254', '525251414124', '125412514124', '2025-07-20 11:50:39', '2025-09-07 17:23:26', 3, NULL, 0, '$argon2id$v=19$m=65536,t=4,p=3$VGRvQ1lnbENud1NqTDJrZw$VkOEQnTJSrNEQaf1iVaOiabYJe5XXRXE1xa/ZS62Cwo'),
(163, 'EMP-375954', '54234', 'dasdsad', 'qdad', 'asdasdas', '2025-07-24', 'dasdasd', 'Male', 'Single', '09974523758', 'ufjahj@gmail.com', 'fasadasdasa', 'A+', 'Human Resources', 'dfadasd', NULL, 1231.00, '151545234625', '547262545252', '352452352352', '2025-07-20 12:50:52', '2025-09-07 17:23:26', 4, NULL, 0, '$argon2id$v=19$m=65536,t=4,p=3$VGRvQ1lnbENud1NqTDJrZw$VkOEQnTJSrNEQaf1iVaOiabYJe5XXRXE1xa/ZS62Cwo'),
(167, 'EMP-117011', '123123', 'adasd', 'dadsad', 'addaasd', '2025-07-14', 'dadasd', 'Male', 'Separated', '09984678682', 'dadas@gmail.com', 'asdas', 'A+', 'Human Resources', 'adasda', 'upload/emp_687ce7ff7b2836.18115541.png', 123.00, '412412415254', '243523452345', '452352524525', '2025-07-20 12:58:39', '2025-09-18 11:24:23', 4, NULL, 0, '$argon2id$v=19$m=65536,t=4,p=3$VGRvQ1lnbENud1NqTDJrZw$VkOEQnTJSrNEQaf1iVaOiabYJe5XXRXE1xa/ZS62Cwo'),
(173, 'EMP-109346', '421423', 'asdsad', 'sdasd', 'asdsa', '2025-08-05', 'dasd', 'Male', 'Single', '09974782637', 'hfjaio@gmail.com', 'fafasfas', 'A+', 'Staff', 'fsafasd', NULL, 31.00, '563567346345', '124213423523', '895950580967', '2025-08-07 13:19:42', '2025-09-07 17:23:26', 22, NULL, 0, '$argon2id$v=19$m=65536,t=4,p=3$VGRvQ1lnbENud1NqTDJrZw$VkOEQnTJSrNEQaf1iVaOiabYJe5XXRXE1xa/ZS62Cwo'),
(179, 'EMP-943244', '563535345', 'test', 'test', 'test', '2025-08-13', 'test', 'Male', 'Married', '09563535333', 'yonitanabesis01@gmail.com', 'Filipino', 'A+', 'Staff', 'test', NULL, 133.00, '123541354363', '522634879111', '242353645865', '2025-08-13 03:18:22', '2025-09-27 11:59:10', 31, NULL, 1, '$2y$10$oOecK.qACZnL1RGmZwjQHeq81lBdqPbpKZiUY0U0PzX386XduDQDG'),
(180, 'EMP-474954', '0967857456', 'Lauriana', 'sillote', 'Nabesis', '1964-07-04', 'Davao city', 'Female', 'Married', '09956646111', 'lauriananabesis@gmail.com', 'Filipino', 'A+', 'Human Resources', 'Purok 7 - San miguel, San isidro, Bunawan, Davao city', 'upload/emp_68cc11e760b985.73506899.png', 601.00, '079673452542', '090789674111', '078078967111', '2025-08-13 03:30:37', '2025-10-26 07:51:30', 4, NULL, 1, '$2y$10$OYorKcmKDLZX4LpZsAKxOOf57zPCPGN.DSnilUslqVDB/QxuDDiQ.'),
(181, 'EMP-965793', '7685230923', 'fasdas', 'jhjhjkh', 'jhjkjkkj', '2025-09-13', 'jfoijoasjd', 'Male', 'Single', '09034712789', 'yuankyriejuarez23@gmail.com', 'Filipino', 'A+', 'Driver', 'gjhggjhg', NULL, 786.00, '896756354267', '967534677898', '097867656563', '2025-09-13 14:07:58', '2025-09-13 14:07:58', 31, NULL, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `employee_schedules`
--

CREATE TABLE `employee_schedules` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_schedules`
--

INSERT INTO `employee_schedules` (`id`, `employee_id`, `schedule_id`) VALUES
(78, 124, 69),
(79, 125, 70),
(97, 122, 88),
(98, 118, 89),
(99, 141, 90),
(104, 117, 95),
(105, 145, 96),
(110, 115, 101),
(112, 116, 103),
(114, 146, 105),
(115, 180, 106);

-- --------------------------------------------------------

--
-- Table structure for table `hr`
--

CREATE TABLE `hr` (
  `id` int(11) NOT NULL,
  `hr_employee_id` varchar(50) NOT NULL,
  `hr_photo_path` varchar(255) DEFAULT NULL,
  `hr_first_name` varchar(100) NOT NULL,
  `hr_middle_name` varchar(100) DEFAULT NULL,
  `hr_last_name` varchar(100) NOT NULL,
  `hr_email` varchar(100) NOT NULL,
  `hr_position` varchar(100) NOT NULL DEFAULT 'Human Resources',
  `hr_rfid_number` varchar(50) DEFAULT NULL,
  `hr_dob` date DEFAULT NULL,
  `hr_place_of_birth` varchar(100) DEFAULT NULL,
  `hr_sex` varchar(10) DEFAULT NULL,
  `hr_civil_status` varchar(20) DEFAULT NULL,
  `hr_contact_number` varchar(15) DEFAULT NULL,
  `hr_citizenship` varchar(50) DEFAULT NULL,
  `hr_blood_type` varchar(5) DEFAULT NULL,
  `hr_address` varchar(255) DEFAULT NULL,
  `hr_base_salary` decimal(10,2) DEFAULT NULL,
  `hr_sss_number` varchar(20) DEFAULT NULL,
  `hr_pagibig_number` varchar(20) DEFAULT NULL,
  `hr_philhealth_number` varchar(20) DEFAULT NULL,
  `hr_password` varchar(255) NOT NULL,
  `hr_created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `hr_updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hr`
--

INSERT INTO `hr` (`id`, `hr_employee_id`, `hr_photo_path`, `hr_first_name`, `hr_middle_name`, `hr_last_name`, `hr_email`, `hr_position`, `hr_rfid_number`, `hr_dob`, `hr_place_of_birth`, `hr_sex`, `hr_civil_status`, `hr_contact_number`, `hr_citizenship`, `hr_blood_type`, `hr_address`, `hr_base_salary`, `hr_sss_number`, `hr_pagibig_number`, `hr_philhealth_number`, `hr_password`, `hr_created_at`, `hr_updated_at`, `deleted_at`) VALUES
(6, 'HR-653677', NULL, 'hfgadsd', 'asdasd', 'dasds', 'bahanadamuzakpa@gmail.com', 'Human Resources', '2786422', '2025-10-26', 'asdasd', 'Male', 'Married', '09237845637', 'Filipino', 'A-', 'dasdas', 31231.00, '312341231312', '414522565656', '234653245634', '$argon2id$v=19$m=65536,t=4,p=3$WWZJVk1KYmZpMFgyMVF0Tw$6IprSBUl5X+Lnwg378VGAn09DhhULwSvTXsxuRioNwo', '2025-10-26 02:53:26', '2025-10-26 03:21:38', NULL),
(8, 'HR-697660', NULL, 'sasfasd', 'adasda', 'asdas', 'migrantsventurecorporation@gmail.com', 'Human Resources', '12313123', '2025-10-26', 'dafadasd', 'Male', 'Married', '09647237654', 'Filipino', 'A+', 'dasdas', 123.00, '412452352356', '234523623634', '342623462345', '$argon2id$v=19$m=65536,t=4,p=3$VnVFWHpPWVUzSDZPNlhjSw$hRdfZiIVfY6ZRIl746kGh6TwFEsmrSQl/Llb0SrTMB8', '2025-10-26 02:59:14', '2025-10-26 07:41:04', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `leaves`
--

CREATE TABLE `leaves` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `manager_id` int(11) DEFAULT NULL,
  `leave_type` enum('Sick Leave','Emergency Leave','Vacation Leave','Personal Leave','Maternity/Paternity Leave') NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `duration` int(11) NOT NULL,
  `reason` text NOT NULL,
  `med_cert_path` varchar(255) DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leaves`
--

INSERT INTO `leaves` (`id`, `employee_id`, `manager_id`, `leave_type`, `start_date`, `end_date`, `duration`, `reason`, `med_cert_path`, `status`, `created_at`, `updated_at`) VALUES
(31, 118, 3, 'Personal Leave', '2025-05-29', '2025-05-29', 1, 'asdasd', NULL, 'Rejected', '2025-05-29 01:36:35', '2025-05-29 01:36:55'),
(32, 118, 3, 'Personal Leave', '2025-05-30', '2025-05-30', 1, 'gasdffs', NULL, 'Approved', '2025-05-29 01:38:31', '2025-05-29 01:39:17'),
(34, 122, 2, 'Sick Leave', '2025-05-29', '2025-05-30', 2, 'sick', NULL, 'Rejected', '2025-05-29 07:32:12', '2025-05-29 07:33:37'),
(35, 122, 2, 'Emergency Leave', '2025-05-29', '2025-05-30', 2, 'sdfsdff', NULL, 'Approved', '2025-05-29 07:34:27', '2025-05-29 07:34:40'),
(36, 116, 3, 'Personal Leave', '2025-05-30', '2025-05-30', 1, 'Nabilar hahah', NULL, 'Rejected', '2025-05-29 13:40:48', '2025-05-29 17:06:46'),
(37, 124, 4, 'Vacation Leave', '2025-05-31', '2025-05-31', 1, 'fsdfsfd', NULL, 'Rejected', '2025-05-30 01:23:35', '2025-06-01 12:30:41'),
(38, 124, 4, 'Vacation Leave', '2025-05-31', '2025-06-02', 3, 'gsdsd', NULL, 'Approved', '2025-05-30 01:34:03', '2025-06-01 12:29:54'),
(39, 125, 3, 'Vacation Leave', '2025-06-16', '2025-06-18', 3, 'asdasd', NULL, 'Approved', '2025-06-01 12:35:25', '2025-06-05 16:11:51'),
(83, 115, 4, 'Emergency Leave', '2025-06-29', '2025-07-02', 4, 'dasds', NULL, 'Approved', '2025-07-03 12:14:56', '2025-07-03 12:15:18'),
(101, 115, NULL, 'Sick Leave', '2025-07-01', '2025-07-03', 3, '', 'upload/med_cert/med_686d3d1cb67865.73669816.jpg', 'Pending', '2025-07-08 15:45:32', '2025-07-08 15:45:32'),
(102, 118, NULL, 'Sick Leave', '2025-07-01', '2025-07-03', 3, '', 'upload/med_cert/med_6879ba28d957a3.99688076.png', 'Pending', '2025-07-18 03:06:16', '2025-07-18 03:06:16'),
(103, 118, 2, 'Sick Leave', '2025-07-01', '2025-07-10', 10, '', 'upload/med_cert/med_688080f6180282.11263295.jpg', 'Approved', '2025-07-23 06:28:06', '2025-07-23 06:33:03'),
(104, 180, NULL, 'Sick Leave', '2025-10-01', '2025-10-02', 2, 'fsdfdsf', NULL, 'Pending', '2025-10-03 06:33:32', '2025-10-03 06:33:32');

-- --------------------------------------------------------

--
-- Table structure for table `leave_credits`
--

CREATE TABLE `leave_credits` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `leave_type_id` int(11) NOT NULL,
  `taken` int(11) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_credits`
--

INSERT INTO `leave_credits` (`id`, `employee_id`, `leave_type_id`, `taken`, `updated_at`) VALUES
(1, 115, 1, 0, '2025-09-13 15:37:36'),
(2, 115, 2, 1, '2025-09-13 15:37:36'),
(3, 115, 3, 0, '2025-09-13 15:37:36'),
(4, 115, 4, 0, '2025-09-13 15:37:36'),
(5, 115, 5, 0, '2025-09-13 15:37:36'),
(6, 122, 1, 0, '2025-09-13 15:37:36'),
(7, 122, 2, 0, '2025-09-13 15:37:36'),
(8, 122, 3, 0, '2025-09-13 15:37:36'),
(9, 122, 4, 0, '2025-09-13 15:37:36'),
(10, 122, 5, 0, '2025-09-13 15:37:36'),
(11, 118, 1, 1, '2025-09-13 15:37:36'),
(12, 118, 2, 0, '2025-09-13 15:37:36'),
(13, 118, 3, 0, '2025-09-13 15:37:36'),
(14, 118, 4, 0, '2025-09-13 15:37:36'),
(15, 118, 5, 0, '2025-09-13 15:37:36'),
(16, 116, 1, 0, '2025-09-13 15:37:36'),
(17, 116, 2, 0, '2025-09-13 15:37:36'),
(18, 116, 3, 0, '2025-09-13 15:37:36'),
(19, 116, 4, 0, '2025-09-13 15:37:36'),
(20, 116, 5, 0, '2025-09-13 15:37:36'),
(21, 180, 1, 0, '2025-09-13 15:37:36'),
(22, 180, 2, 0, '2025-09-13 15:37:36'),
(23, 180, 3, 0, '2025-09-13 15:37:36'),
(24, 180, 4, 0, '2025-09-13 15:37:36'),
(25, 180, 5, 0, '2025-09-13 15:37:36'),
(26, 179, 1, 0, '2025-09-13 15:37:36'),
(27, 179, 2, 0, '2025-09-13 15:37:36'),
(28, 179, 3, 0, '2025-09-13 15:37:36'),
(29, 179, 4, 0, '2025-09-13 15:37:36'),
(30, 179, 5, 0, '2025-09-13 15:37:36');

-- --------------------------------------------------------

--
-- Table structure for table `leave_rejections`
--

CREATE TABLE `leave_rejections` (
  `id` int(11) NOT NULL,
  `leave_id` int(11) NOT NULL,
  `manager_id` int(11) NOT NULL,
  `reason` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_rejections`
--

INSERT INTO `leave_rejections` (`id`, `leave_id`, `manager_id`, `reason`, `created_at`) VALUES
(5, 31, 3, 'apoigjdfgdsfg', '2025-05-29 01:36:55'),
(6, 34, 2, 'i hate you..', '2025-05-29 07:33:37'),
(7, 36, 3, 'fsfsdfasd', '2025-05-29 17:06:46'),
(9, 37, 4, 'asdasd', '2025-06-01 12:30:41');

-- --------------------------------------------------------

--
-- Table structure for table `leave_types`
--

CREATE TABLE `leave_types` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `default_allowed` int(11) NOT NULL DEFAULT 10,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_types`
--

INSERT INTO `leave_types` (`id`, `name`, `default_allowed`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Sick Leave', 5, 'Medical leave for illness', 1, '2025-09-13 15:33:32', '2025-09-13 16:05:44'),
(2, 'Emergency Leave', 5, 'Emergency situations', 1, '2025-09-13 15:33:32', '2025-09-13 15:33:32'),
(3, 'Vacation Leave', 5, 'Annual vacation leave', 1, '2025-09-13 15:33:32', '2025-09-18 13:22:09'),
(4, 'Personal Leave', 5, 'Personal matters', 1, '2025-09-13 15:33:32', '2025-09-13 15:33:32'),
(5, 'Maternity/Paternity Leave', 5, 'Parental leave', 1, '2025-09-13 15:33:32', '2025-09-13 16:20:12');

-- --------------------------------------------------------

--
-- Table structure for table `managers`
--

CREATE TABLE `managers` (
  `id` int(11) NOT NULL,
  `m_employee_id` varchar(50) NOT NULL,
  `m_photo_path` varchar(255) DEFAULT NULL,
  `m_first_name` varchar(100) NOT NULL,
  `m_middle_name` varchar(100) DEFAULT NULL,
  `m_last_name` varchar(100) NOT NULL,
  `m_full_name` varchar(100) NOT NULL,
  `m_email` varchar(100) NOT NULL,
  `m_branch` varchar(100) NOT NULL,
  `m_position` varchar(100) NOT NULL,
  `m_rfid_number` varchar(50) DEFAULT NULL,
  `m_dob` date DEFAULT NULL,
  `m_place_of_birth` varchar(100) DEFAULT NULL,
  `m_sex` varchar(10) DEFAULT NULL,
  `m_civil_status` varchar(20) DEFAULT NULL,
  `m_contact_number` varchar(15) DEFAULT NULL,
  `m_citizenship` varchar(50) DEFAULT NULL,
  `m_blood_type` varchar(5) DEFAULT NULL,
  `m_address` varchar(255) DEFAULT NULL,
  `m_base_salary` decimal(10,2) DEFAULT NULL,
  `m_sss_number` varchar(20) DEFAULT NULL,
  `m_pagibig_number` varchar(20) DEFAULT NULL,
  `m_philhealth_number` varchar(20) DEFAULT NULL,
  `m_password` varchar(255) NOT NULL,
  `m_created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `m_updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `managers`
--

INSERT INTO `managers` (`id`, `m_employee_id`, `m_photo_path`, `m_first_name`, `m_middle_name`, `m_last_name`, `m_full_name`, `m_email`, `m_branch`, `m_position`, `m_rfid_number`, `m_dob`, `m_place_of_birth`, `m_sex`, `m_civil_status`, `m_contact_number`, `m_citizenship`, `m_blood_type`, `m_address`, `m_base_salary`, `m_sss_number`, `m_pagibig_number`, `m_philhealth_number`, `m_password`, `m_created_at`, `m_updated_at`, `deleted_at`) VALUES
(2, '', '', 'roy', 'b', 'nabesis', 'Roy B. Nabesis', 'roydonnabesis@gmail.com', 'Global Marketing Alliance - Panabo', 'Manager', '41234234', '2025-08-13', 'Asdas', 'Male', 'Single', '09525434343', 'Filipino', 'O+', 'fsdfsdfdsf', 123.00, '542342344234', '423423423423', '312312311111', '$2y$10$fxZ.6r183iuUZHjDEWVPuObVM7HX5uew8X1d8g0DgykCXJkJG0Bba', '2025-05-28 14:02:00', '2025-10-08 11:58:35', NULL),
(3, '', 'upload/emp_6897f8f2992d65.15014270.jpg', 'Ronn Charles', 'C.', 'Domingo', 'Ronn Charles O. Domingo', 'ronncharlesd8@gmail.com', 'LMG Co., Ltd - Tagum', 'Manager', '234234', '2002-10-02', 'dasd', 'Male', 'Single', '09454564564', 'dfgfgdfg', 'A+', 'fsdfsd', 123.00, '435634674567', '568574634534', '347345674575', '$2y$10$lDAhZsilwoBMyeQea7nWX.9lKRstCAYEdlvCmW1D8/VqDzyB8uKIC', '2025-05-28 14:18:42', '2025-10-18 16:03:23', NULL),
(4, 'EMP-033873', '', 'ken jazver', 'v', 'galanido', 'Ken Jazver V. Galanido', 'terraken08@gmail.com', 'Expressway Liaison Services - Tagum', 'Manager', '4234234', '2003-12-07', 'dasd', 'Male', 'Single', '09843578345', 'Filipino', 'A+', 'Panabo City', 500.00, '347895634789', '907657826372', '123875638465', '$argon2id$v=19$m=65536,t=4,p=3$NHZSTDg2MS5Sa3AzL2k1cA$V+jmZJsTy3jsfjs5IkUdcud9GkWaGe6oJfYJBuS/I/E', '2025-05-28 14:19:05', '2025-10-18 16:04:06', NULL),
(5, 'EMP-320673', 'upload/emp_68892140ebad31.44344137.jpg', 'dasdasdd', 'asdasd', 'dasdasd', '', 'fsdasoO@fmail.com', 'LMG Co., Ltd - Tagum', 'Manager', '3123123', '2025-07-24', 'asdasd', 'Male', 'Divorced', '09525534534', 'dasdsa', 'O+', 'dasdasd', 123123.00, '412412412412', '124121312111', '412412312111', '', '2025-07-23 14:49:55', '2025-07-29 19:41:36', NULL),
(22, 'EMP-385357', 'upload/emp_688926a74856f8.60484205.jpg', 'popouui', 'dadasd', 'dasd', '', 'gdgdfgj@gmail.com', 'Branch-Tagum-2', 'Manager', '5345435', '2025-07-18', 'dasd', 'Female', 'Divorced', '09545353536', 'dfasdasd', 'A-', 'asdsad', 123.00, '235465756876', '987098098907', '567663535353', '$argon2id$v=19$m=65536,t=4,p=3$T2E1ZG5hTUhXMG5xMnR0YQ$GfFvgFBNmvnYymemV+fAkY8LbfnX/tdaBil9dbDk9rw', '2025-07-29 19:43:15', '2025-08-25 16:46:23', NULL),
(23, 'EMP-755007', '', 'habx', 'sdfds', 'ggdfg', '', 'fkkg@gmail.com', 'LMG Co., Ltd - Tagum', 'Manager', '24324', '2025-07-30', 'dsfbfd', 'Male', 'Widowed', '09754634522', 'dasdasd', 'B+', 'dasds', 123.00, '123123423423', '523255435345', '523542345252', '$2y$10$JiTy5yTw61ihvvk6PhJezuPf1DvRFCoORzu74XOSBEnbcVaKq18AS', '2025-07-29 19:51:20', '2025-10-08 11:54:36', NULL),
(24, 'EMP-590788', 'upload/emp_6897fec210d419.30605324.png', 'fafhjgf', 'dsad', 'fasas', 'fafhjgf dsad fasas', 'dassgf@gmail.com', 'LMG Co., Ltd - Tagum', 'Manager', '558453566', '2025-08-13', 'fasfa', 'Male', 'Single', '09574564645', 'dasdasd', 'B+', 'dasdasd', 123.00, '142315346456', '547252345252', '626546735735', '$argon2id$v=19$m=65536,t=4,p=3$RHUuZ0NwZ0JsTkRCakFwTQ$gLxRk2sm/6deMKhSDf9miLs5uMLwpPdpUXqpThS3Op0', '2025-07-30 09:51:21', '2025-08-27 06:59:27', NULL),
(27, 'EMP-007678', '', 'sfsdf', 'fsdfsd', 'sdfsdfsdf', 'sfsdf fsdfsd sdfsdfsdf', 'royskienabesis@gmail.com', 'Global Marketing Alliance - Panabo', 'Manager', '42423434', '2025-08-13', 'fsfsdfafa', 'Male', 'Married', '09623454354', 'Afghan', 'A-', 'asdsad', 123.00, '452365475696', '352134564654', '254424736858', '$2y$10$/.N0JJ3djBNBjszQ47LksO0aqQrxQDABblihMTB1/3SoOB5m2/pVu', '2025-08-13 13:25:04', '2025-10-08 11:54:18', NULL),
(30, 'EMP-864509', '', 'test2', 'test2', 'test2', 'test2 test2 test2', 'drewu382@gmail.com', 'Global Marketing Alliance - Panabo', 'Manager', '5234263', '2025-08-13', 'Davao city', 'Male', 'Separated', '09625353453', 'Filipino', 'B+', 'Purok 7 - San miguel, San isidro, Bunawan, Davao city', 213.00, '654754433333', '123436231222', '235236511111', '1234', '2025-08-17 13:26:32', '2025-10-03 06:38:50', NULL),
(31, 'EMP-185013', 'upload/emp_68e65eb2614660.32464238.png', 'test', 'ggjfhfgh', 'kukgjgh', 'dghdfgdf ggjfhfgh kukgjgh', 'uncledrew090119@gmail.com', 'Expressway Liaison Services - Tagum', 'Manager', '54234234', '2025-09-10', 'dfgdfg', 'Male', 'Separated', '09086847232', 'Albanian', 'A-', 'sdfsdfs', 123.00, '972986342398', '472987492111', '908592379598', '$argon2id$v=19$m=65536,t=4,p=3$UUZ6LmxVejlVV3kvYXVqYg$j4zszje4Pb3Tqw/wsTjRLO8zbMWD0uoQPON2bmtd/IM', '2025-09-09 16:52:17', '2025-10-19 04:23:46', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `manager_schedules`
--

CREATE TABLE `manager_schedules` (
  `id` int(11) NOT NULL,
  `manager_id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `manager_schedules`
--

INSERT INTO `manager_schedules` (`id`, `manager_id`, `schedule_id`) VALUES
(1, 30, 107),
(2, 4, 108);

-- --------------------------------------------------------

--
-- Table structure for table `hr_schedules`
--

CREATE TABLE `hr_schedules` (
  `id` int(11) NOT NULL,
  `hr_id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hr_schedules`
--

-- (no default rows)

-- --------------------------------------------------------

--
-- Table structure for table `owners`
--

CREATE TABLE `owners` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `owners`
--

INSERT INTO `owners` (`id`, `name`, `email`, `password`, `phone`, `photo_path`, `created_at`, `updated_at`) VALUES
(2, 'System Owner', 'owner@example.com', '$argon2id$v=19$m=65536,t=4,p=1$SVB3Ui5MaG5rWHN4L09Saw$zsYwpLI0ERpgTAIymXO41/3S4Mn+mm1k7QBISNmBTaM', '09171234567', 'upload/owner_profile.png', '2025-10-05 11:08:37', '2025-10-05 11:09:30');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_type` enum('employee','manager') NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(128) NOT NULL,
  `verification_code` varchar(10) DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `user_type`, `user_id`, `token`, `verification_code`, `expires_at`, `used_at`, `verified_at`, `created_at`) VALUES
(1, 'employee', 115, '4a2ae68c8e36cbc0e0c033e255129f7c5f6aa0beff261416c1ed01ebbf0a27ec', '359709', '2025-09-07 18:59:36', NULL, NULL, '2025-09-08 00:29:36'),
(2, 'employee', 115, 'aecc3da3616505ea9980b17c6473f6f3eb3e5e9a99e869cf5a012c6085dede74', '805736', '2025-09-07 19:04:59', NULL, NULL, '2025-09-08 00:34:59'),
(3, 'employee', 115, '90fc0685fe88952a6c87465bb3bbf4907c3e2e6cfedaf83f02f499d75236ab4f', '423409', '2025-09-07 19:15:09', NULL, NULL, '2025-09-08 00:45:09'),
(4, 'employee', 115, 'a6b86220c291ad3a71ddfb8c13661162b8d2eceedcab4ccaacb6124d34e9d598', '634039', '2025-09-07 19:18:08', NULL, '2025-09-08 00:49:18', '2025-09-08 00:48:08'),
(5, 'employee', 180, '596a434ef70421c5d43bb7e249e698ee08d28e6570c154ba4b8d765ab8177d9f', '657521', '2025-09-07 19:22:17', NULL, '2025-09-08 00:52:48', '2025-09-08 00:52:17'),
(6, 'employee', 180, 'd6230ec6cd5fe606ccf5adb303a44b1c7a346cfd1e4bc2b60ee92ec857dbf8a4', '651262', '2025-09-07 19:24:08', NULL, '2025-09-08 00:54:44', '2025-09-08 00:54:08'),
(7, 'employee', 180, '49503735ef51b4077d05e9145d37a3c9960dcfbbf92ef39e3d8d4b93e3fd1b79', '322074', '2025-09-07 19:28:17', '2025-09-08 01:01:15', '2025-09-08 00:58:54', '2025-09-08 00:58:17'),
(8, 'employee', 179, 'a6364605877b6d8eeb56ef8a7f8d2b8831f9a5f6994870390a0ad648b7144ae8', '469574', '2025-09-07 19:33:24', '2025-09-08 01:04:26', '2025-09-08 01:04:06', '2025-09-08 01:03:24'),
(9, 'employee', 179, '1e7813fd0862e128108ba1a612b240bf074d238e4dbb8f0a860f7697058b0c3a', '199010', '2025-09-07 19:38:57', '2025-09-08 01:09:39', '2025-09-08 01:09:22', '2025-09-08 01:08:57'),
(10, 'employee', 179, '85c83e84fec50d5ea2decc2a1303506586d536d850a666c42c36f26a474844af', '800609', '2025-09-07 19:41:08', '2025-09-08 01:11:43', '2025-09-08 01:11:29', '2025-09-08 01:11:08'),
(11, 'employee', 179, '24c330196096b3754760c5cdb85def105bf93a5b545b8df3b535e7f2c0c6c654', '276698', '2025-09-07 19:45:14', '2025-09-08 01:15:56', '2025-09-08 01:15:44', '2025-09-08 01:15:14'),
(12, 'employee', 180, 'bd96afd893ad782b832d2e3c4004dc4920042fa4bff15f6d7893ef85d9a69ad9', '817247', '2025-09-07 19:55:02', '2025-09-08 01:26:48', '2025-09-08 01:26:20', '2025-09-08 01:25:02'),
(13, 'employee', 179, '7d721b4c4113d612ff717ecdbfa1f68132c5b2a80cb12d36108bf3b91e7a2e15', '678119', '2025-09-07 20:57:11', NULL, NULL, '2025-09-08 02:27:11'),
(14, 'employee', 115, 'f219c6ec4f43a8f337db927fca3f15bbe2dd6e64748df14ab1d5b604002c1aa3', '177355', '2025-09-12 20:10:07', NULL, '2025-09-13 01:46:50', '2025-09-13 01:40:07'),
(15, 'employee', 115, '60a30e3d03410ed8c6dd76c7fd5200944f257fb7a970de5d84a7543aef7c019b', '869475', '2025-09-12 20:47:17', NULL, NULL, '2025-09-13 02:17:17'),
(16, 'employee', 115, '74aa9330de40249c869a2ea952288f2093511908247928d610deef6aa624bbc4', '904525', '2025-09-12 20:47:40', NULL, '2025-09-13 02:17:43', '2025-09-13 02:17:40'),
(17, 'employee', 115, '6fb8b028431afed7c0113e2815a4e794bcf36dfcdd0a33bc470edcc3f18975a8', '944586', '2025-09-12 20:49:22', NULL, '2025-09-13 02:26:01', '2025-09-13 02:19:22'),
(18, 'employee', 115, 'd1a39bf7e171b7a7d24d0bda49ddfbff289f4ef23263f8e87157e52a7e2b7a3f', '963908', '2025-09-12 20:56:59', NULL, '2025-09-13 02:26:59', '2025-09-13 02:26:59'),
(19, 'employee', 115, '3258c8b3df86a08415eaae54b12a8e55b9f1aa35877154b1a962a379c5b52cd7', '633329', '2025-09-12 20:57:43', '2025-09-13 02:28:58', '2025-09-13 02:28:09', '2025-09-13 02:27:43'),
(20, 'employee', 180, 'e311e6acb0784f6ce41460e119a6f14c002715b3196ddab47e1f525341a21848', '982885', '2025-09-13 10:12:29', NULL, '2025-09-13 15:43:55', '2025-09-13 15:42:29'),
(21, 'employee', 180, 'd15d5de77bf104a3b6df1868d8348c7d6babe7e2493813c3f57c29a3597a13d2', '834183', '2025-09-13 10:19:15', '2025-09-13 15:50:44', '2025-09-13 15:49:42', '2025-09-13 15:49:15'),
(22, 'employee', 180, '1e7049e28f1d27e04537f60da81f5a55332f9d2d9ede5693bacda973bbfef95b', '876493', '2025-09-13 10:23:16', NULL, '2025-09-13 15:53:45', '2025-09-13 15:53:16'),
(23, 'employee', 180, '9cabb3e867629d707d436197054de18ca38c09c18c82677b07cafa80fa7f7579', '945366', '2025-09-13 10:44:43', NULL, NULL, '2025-09-13 16:14:43'),
(24, 'employee', 180, 'c0ac30d4304301fd1b856759e7cb30b32c425f62b4c4a8aea5389f247aef3c9f', '518944', '2025-09-13 10:48:00', NULL, NULL, '2025-09-13 16:18:00'),
(25, 'employee', 180, '7797c1b8ba44612ae9544b44ec1f48c2d6a9dfb5f3a344bd37a422e06b641f74', '945958', '2025-09-13 10:49:13', NULL, NULL, '2025-09-13 16:19:13'),
(26, 'employee', 180, 'd0470b15f85cdb39f55831c2631c2a43de8feb4bd16c02263f227d411be6783d', '443034', '2025-09-13 10:53:16', NULL, NULL, '2025-09-13 16:43:16'),
(27, 'employee', 180, '6c8f3ac55a638e9b04cb185b0da505d29e62f453db8712252768c3921b74d4c2', '421800', '2025-09-13 10:54:47', '2025-09-13 16:45:52', '2025-09-13 16:45:24', '2025-09-13 16:44:47'),
(28, 'manager', 30, '9e6e04b5d6b2d7cffad7e05ea95e441b7b8853b292db00d6c38ec1ceb1d58bd6', '848712', '2025-09-18 17:09:57', '2025-09-18 22:47:25', '2025-09-18 22:41:52', '2025-09-18 22:39:57'),
(29, 'manager', 30, '99375eaf41bbe5a93358430b7a0a35ae3bd2d60186e283d905d7ef3d5279a9e8', '802458', '2025-09-18 17:17:59', '2025-09-18 22:48:44', '2025-09-18 22:48:21', '2025-09-18 22:47:59'),
(30, 'employee', 180, 'a56bc4b0bbed213cf68fdb0a0eb083f936bc58351515832fb6efe161e241be52', '408349', '2025-10-03 05:46:58', '2025-10-03 11:19:16', '2025-10-03 11:17:44', '2025-10-03 11:16:58'),
(31, 'manager', 30, 'c0c8cee5116e46f882945063f06439feef351964d91337a413695ba13acb6202', '503144', '2025-10-03 09:05:44', NULL, NULL, '2025-10-03 14:35:44');

-- --------------------------------------------------------

--
-- Table structure for table `payroll`
--

CREATE TABLE `payroll` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `pay_period_start` date NOT NULL,
  `pay_period_end` date NOT NULL,
  `payroll_frequency` enum('monthly') NOT NULL DEFAULT 'monthly',
  `payroll_duration` int(11) NOT NULL DEFAULT 0,
  `total_hours` int(11) NOT NULL DEFAULT 0,
  `sss_deduction` decimal(10,2) NOT NULL DEFAULT 0.00,
  `pagibig_deduction` decimal(10,2) NOT NULL DEFAULT 0.00,
  `philhealth_deduction` decimal(10,2) NOT NULL DEFAULT 0.00,
  `present_days` int(11) NOT NULL DEFAULT 0,
  `absent_days` int(11) NOT NULL DEFAULT 0,
  `leave_days` int(11) NOT NULL DEFAULT 0,
  `total_deductions` decimal(10,2) NOT NULL DEFAULT 0.00,
  `gross_pay` decimal(10,2) NOT NULL DEFAULT 0.00,
  `net_pay` decimal(10,2) NOT NULL DEFAULT 0.00,
  `generated_by` int(11) DEFAULT NULL,
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payroll`
--

INSERT INTO `payroll` (`id`, `employee_id`, `pay_period_start`, `pay_period_end`, `payroll_frequency`, `payroll_duration`, `total_hours`, `sss_deduction`, `pagibig_deduction`, `philhealth_deduction`, `present_days`, `absent_days`, `leave_days`, `total_deductions`, `gross_pay`, `net_pay`, `generated_by`, `generated_at`) VALUES
(101, 116, '2025-07-01', '2025-07-18', 'monthly', 18, 8, 30.00, 30.00, 30.00, 1, 17, 0, 90.00, 600.00, 510.00, 3, '2025-08-09 17:02:52'),
(102, 118, '2025-07-01', '2025-07-18', 'monthly', 28, 16, 60.00, 60.00, 60.00, 2, 16, 10, 333.33, 1200.00, 686.67, 2, '2025-08-10 07:05:53');

-- --------------------------------------------------------

--
-- Table structure for table `payslips`
--

CREATE TABLE `payslips` (
  `id` int(11) NOT NULL,
  `payroll_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `generated_by` int(11) NOT NULL,
  `date_generated` datetime DEFAULT current_timestamp(),
  `ps_pdf_file_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payslips`
--

INSERT INTO `payslips` (`id`, `payroll_id`, `employee_id`, `generated_by`, `date_generated`, `ps_pdf_file_path`) VALUES
(71, 101, 116, 3, '2025-08-10 01:02:53', 'payslips/payslip_EMP-399188_2025-07-01_2025-07-18.pdf'),
(72, 102, 118, 2, '2025-08-10 15:05:53', 'payslips/payslip_EMP-517859_2025-07-01_2025-07-18.pdf');

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `sched_morning_in` time NOT NULL,
  `sched_morning_out` time NOT NULL,
  `sched_afternoon_in` time NOT NULL,
  `sched_afternoon_out` time NOT NULL,
  `grace_period` int(11) NOT NULL,
  `type` enum('employee','manager','hr') DEFAULT 'employee',
  `record_type` enum('employee','manager','hr') DEFAULT 'employee'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedules`
--

INSERT INTO `schedules` (`id`, `name`, `sched_morning_in`, `sched_morning_out`, `sched_afternoon_in`, `sched_afternoon_out`, `grace_period`, `type`, `record_type`) VALUES
(69, 'Test T. Test', '07:00:00', '16:00:00', '00:00:00', '00:00:00', 15, 'employee', 'employee'),
(70, 'Test 2 T. Test 2', '07:00:00', '16:00:00', '00:00:00', '00:00:00', 15, 'employee', 'employee'),
(88, 'Kres-Ann B. Oclarit', '06:00:00', '11:00:00', '13:00:00', '17:00:00', 11, 'employee', 'employee'),
(89, 'Giovan Kier V. Cardenio', '19:35:00', '19:40:00', '19:45:00', '19:50:00', 11, 'employee', 'employee'),
(90, 'Huhuhu H. Hehehe', '06:00:00', '11:00:00', '13:00:00', '16:00:00', 11, 'employee', 'employee'),
(95, 'Ken Jazver V. Galanido', '06:00:00', '11:00:00', '13:00:00', '17:00:00', 11, 'employee', 'employee'),
(96, 'Sfdsdf S. Sfsdf', '06:00:00', '11:00:00', '13:00:00', '17:00:00', 5, 'employee', 'employee'),
(101, 'Roy B. Nabesis', '06:00:00', '11:00:00', '13:00:00', '17:00:00', 20, 'employee', 'employee'),
(103, 'Ronn Charles O. Domingo', '22:00:00', '22:05:00', '22:10:00', '22:15:00', 14, 'employee', 'employee'),
(105, 'Adasd A. Aasd', '04:00:00', '11:00:00', '13:00:00', '17:00:00', 14, 'employee', 'employee'),
(106, 'Lauriana S. Nabesis', '06:30:00', '11:00:00', '13:00:00', '17:00:00', 11, 'employee', 'employee'),
(107, 'Test2 T. Test2', '06:00:00', '11:30:00', '13:00:00', '16:00:00', 10, 'manager', 'manager'),
(108, 'Ken Jazver V. Galanido', '07:00:00', '11:00:00', '13:00:00', '21:00:00', 15, 'manager', 'manager');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`hr_email`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_employee_attendance` (`employee_id`,`date`),
  ADD UNIQUE KEY `unique_manager_attendance` (`manager_id`,`date`),
  ADD KEY `idx_attendance_date` (`date`),
  ADD KEY `idx_attendance_status` (`status`),
  ADD KEY `idx_attendance_empid` (`employee_id`),
  ADD KEY `idx_attendance_manager_id` (`manager_id`);

--
-- Indexes for table `benefit_rates`
--
ALTER TABLE `benefit_rates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_branches_name` (`name`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_no` (`employee_no`),
  ADD UNIQUE KEY `rfid_number` (`rfid_number`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `sss_number` (`sss_number`),
  ADD UNIQUE KEY `pagibig_number` (`pagibig_number`),
  ADD UNIQUE KEY `philhealth_number` (`philhealth_number`),
  ADD KEY `fk_branch_manager` (`branch_manager`);

--
-- Indexes for table `employee_schedules`
--
ALTER TABLE `employee_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `schedule_id` (`schedule_id`),
  ADD KEY `fk_employee_schedules_employee` (`employee_id`);

--
-- Indexes for table `hr`
--
ALTER TABLE `hr`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `leaves`
--
ALTER TABLE `leaves`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_leave_employee` (`employee_id`),
  ADD KEY `fk_leave_manager` (`manager_id`);

--
-- Indexes for table `leave_credits`
--
ALTER TABLE `leave_credits`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_leave` (`employee_id`,`leave_type_id`),
  ADD KEY `fk_leave_type` (`leave_type_id`);

--
-- Indexes for table `leave_rejections`
--
ALTER TABLE `leave_rejections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `leave_id` (`leave_id`),
  ADD KEY `manager_id` (`manager_id`);

--
-- Indexes for table `leave_types`
--
ALTER TABLE `leave_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `managers`
--
ALTER TABLE `managers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`m_email`),
  ADD KEY `idx_managers_deleted_at` (`deleted_at`);

--
-- Indexes for table `manager_schedules`
--
ALTER TABLE `manager_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `manager_id` (`manager_id`),
  ADD KEY `schedule_id` (`schedule_id`);

--
-- Indexes for table `hr_schedules`
--
ALTER TABLE `hr_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hr_id` (`hr_id`),
  ADD KEY `schedule_id` (`schedule_id`);

--
-- Indexes for table `owners`
--
ALTER TABLE `owners`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `idx_owners_email` (`email`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `user_type` (`user_type`,`user_id`),
  ADD KEY `token_2` (`token`);

--
-- Indexes for table `payroll`
--
ALTER TABLE `payroll`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `fk_generated_by_manager` (`generated_by`);

--
-- Indexes for table `payslips`
--
ALTER TABLE `payslips`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payroll_id` (`payroll_id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `generated_by` (`generated_by`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=130;

--
-- AUTO_INCREMENT for table `benefit_rates`
--
ALTER TABLE `benefit_rates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=182;

--
-- AUTO_INCREMENT for table `employee_schedules`
--
ALTER TABLE `employee_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=116;

--
-- AUTO_INCREMENT for table `hr`
--
ALTER TABLE `hr`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `leaves`
--
ALTER TABLE `leaves`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=105;

--
-- AUTO_INCREMENT for table `leave_credits`
--
ALTER TABLE `leave_credits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT for table `leave_rejections`
--
ALTER TABLE `leave_rejections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `leave_types`
--
ALTER TABLE `leave_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `managers`
--
ALTER TABLE `managers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `manager_schedules`
--
ALTER TABLE `manager_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `hr_schedules`
--
ALTER TABLE `hr_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `owners`
--
ALTER TABLE `owners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `payroll`
--
ALTER TABLE `payroll`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- AUTO_INCREMENT for table `payslips`
--
ALTER TABLE `payslips`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=109;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `fk_attendance_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `fk_branch_manager` FOREIGN KEY (`branch_manager`) REFERENCES `managers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `employee_schedules`
--
ALTER TABLE `employee_schedules`
  ADD CONSTRAINT `employee_schedules_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `employee_schedules_ibfk_2` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_employee_schedules_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `leaves`
--
ALTER TABLE `leaves`
  ADD CONSTRAINT `fk_leave_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_leave_manager` FOREIGN KEY (`manager_id`) REFERENCES `managers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `leave_credits`
--
ALTER TABLE `leave_credits`
  ADD CONSTRAINT `fk_leave_type` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `leave_credits_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `leave_credits_ibfk_2` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `leave_rejections`
--
ALTER TABLE `leave_rejections`
  ADD CONSTRAINT `fk_rejection_leave` FOREIGN KEY (`leave_id`) REFERENCES `leaves` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `manager_schedules`
--
ALTER TABLE `manager_schedules`
  ADD CONSTRAINT `fk_manager_schedules_manager` FOREIGN KEY (`manager_id`) REFERENCES `managers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_manager_schedules_schedule` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hr_schedules`
--
ALTER TABLE `hr_schedules`
  ADD CONSTRAINT `fk_hr_schedules_hr` FOREIGN KEY (`hr_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_hr_schedules_schedule` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payroll`
--
ALTER TABLE `payroll`
  ADD CONSTRAINT `fk_generated_by_manager` FOREIGN KEY (`generated_by`) REFERENCES `managers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `payroll_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payslips`
--
ALTER TABLE `payslips`
  ADD CONSTRAINT `payslips_ibfk_1` FOREIGN KEY (`payroll_id`) REFERENCES `payroll` (`id`),
  ADD CONSTRAINT `payslips_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`),
  ADD CONSTRAINT `payslips_ibfk_3` FOREIGN KEY (`generated_by`) REFERENCES `managers` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
