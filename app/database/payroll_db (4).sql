-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 14, 2025 at 03:09 PM
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
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `email`, `password`, `created_at`) VALUES
(5, 'Roy B. Nabesis', 'admin@example.com', '$2y$10$7GjKIarUNyWnIXoZduxjOOSdBhRvQDGf1gJWh2TJXeKGrwCesCPKu', '2025-05-24 14:55:09');

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
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

INSERT INTO `attendance` (`id`, `employee_id`, `date`, `morning_in`, `morning_out`, `afternoon_in`, `afternoon_out`, `status`, `created_at`, `updated_at`) VALUES
(87, 118, '2025-05-25', NULL, NULL, NULL, NULL, 'Present', '2025-05-25 10:15:43', '2025-05-25 10:40:03'),
(88, 116, '2025-05-25', NULL, NULL, NULL, NULL, 'Present', '2025-05-25 10:16:37', '2025-05-25 10:51:13'),
(89, 117, '2025-05-25', NULL, NULL, NULL, NULL, 'Present', '2025-05-25 10:16:53', '2025-05-25 10:45:52'),
(90, 115, '2025-05-25', NULL, NULL, NULL, NULL, 'Present', '2025-05-25 10:16:57', '2025-05-25 10:45:38'),
(91, 116, '2025-05-26', NULL, NULL, NULL, NULL, 'Present', '2025-05-26 07:58:37', '2025-05-26 07:58:37'),
(93, 118, '2025-05-28', NULL, NULL, NULL, NULL, 'Present', '2025-05-27 16:02:05', '2025-05-27 17:54:42'),
(94, 115, '2025-05-28', NULL, NULL, NULL, NULL, 'Present', '2025-05-27 16:02:08', '2025-05-27 17:55:55'),
(95, 117, '2025-05-28', NULL, NULL, NULL, NULL, 'Present', '2025-05-27 16:02:11', '2025-05-27 17:55:44'),
(96, 116, '2025-05-28', NULL, NULL, NULL, NULL, 'Present', '2025-05-27 16:02:15', '2025-05-27 17:55:24'),
(97, 124, '2025-05-30', NULL, NULL, NULL, NULL, 'Present', '2025-05-30 01:19:47', '2025-05-30 01:19:47'),
(98, 118, '2025-05-30', NULL, NULL, NULL, NULL, 'Present', '2025-05-30 01:21:43', '2025-05-30 01:21:43'),
(99, 117, '2025-05-30', NULL, NULL, NULL, NULL, 'Present', '2025-05-30 01:41:31', '2025-05-30 01:41:31'),
(100, 128, '2025-06-04', NULL, NULL, NULL, NULL, 'Present', '2025-06-04 10:46:16', '2025-06-04 13:50:13');

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
  `approved_by_manager` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `employee_no`, `rfid_number`, `first_name`, `middle_name`, `last_name`, `dob`, `place_of_birth`, `sex`, `civil_status`, `contact_number`, `email`, `citizenship`, `blood_type`, `position`, `address`, `photo_path`, `base_salary`, `sss_number`, `pagibig_number`, `philhealth_number`, `created_at`, `updated_at`, `branch_manager`, `deleted_at`, `approved_by_manager`) VALUES
(115, 'EMP-420415', '3749540708', 'roy', 'b', 'nabesis', '2000-11-08', 'Davao city', 'Male', 'Single', '09923962962', 'roynabesis.main@gmail.com', 'Filipino', 'O+', 'Human Resources', 'Purok 7 - San miguel, San isidro, Bunawan, Davao city', 'upload/Roy Nabesis.jpg', 600.00, '359358793535', '562346235235', '235235235333', '2025-05-25 09:42:16', '2025-06-04 15:49:47', 4, NULL, 1),
(116, 'EMP-399188', '3750206196', 'ronn charles', 'o', 'domingo', '2003-08-18', 'davao city', 'Male', 'Married', '09911344678', 'ronncharlesd8@gmail.com', 'Filipino', 'AB+', 'Driver', 'purok 18, Upper New Visayas Panabo City', 'upload/Ronn Charles Domingo.jpg', 600.00, '768936793986', '734892735893', '839486786354', '2025-05-25 09:45:08', '2025-06-05 15:28:56', 3, NULL, 1),
(117, 'EMP-853677', '3749150996', 'ken jazver', 'v', 'galanido', '2002-12-08', 'panabo city', 'Male', 'Single', '09562876824', 'terraken08@gmail.com', 'Filipino', 'O+', 'Manager', 'sharon, new visayas, panabo city', 'upload/Ken Jazver Galanido.jpg', 600.00, '873460367222', '757835913333', '725987523522', '2025-05-25 09:47:50', '2025-06-05 14:47:55', 4, NULL, 1),
(118, 'EMP-517859', '3749734948', 'giovan kier', 'v', 'cardenio', '2002-05-13', 'medical mission group hospital', 'Male', 'Single', '09260829512', 'kiercardenio@gmail.com', 'arabo', 'B+', 'Staff', 'purok everlasting, brgy. gredu, panabo city', 'upload/Giovan Kier Cardenio.jpg', 600.00, '828924729858', '456346345654', '235246726256', '2025-05-25 09:50:52', '2025-06-06 10:09:44', 2, NULL, 1),
(122, 'EMP-661929', '4563465646', 'Kres-Ann', 'Bodiongan', 'Oclarit', '1998-11-09', 'Tagana-an', 'Female', 'Single', '09913692194', 'koclarit@dnsc.edu.ph', 'Filipino', 'O+', 'Staff', 'Panabo City', 'upload/man (1).png', 1200.00, '111111111111', '647836458589', '535424354659', '2025-05-29 07:18:49', '2025-06-06 08:25:32', 3, NULL, 1),
(124, 'EMP-699453', '4182319604', 'test', 'test', 'test', '2025-06-07', 'Tagana-an', 'Male', 'Separated', '09544356534', 'jhdfsjfhds@gmail.com', 'Filipino', 'O-', 'Staff', 'Panabo City', NULL, 600.00, '424234234234', '523532532534', '122423423511', '2025-05-30 01:15:26', '2025-06-08 11:59:42', 4, NULL, 1),
(125, 'EMP-153970', '234234', 'test 2', 'test 2', 'test 2', '2025-06-01', 'adsa', 'Female', 'Widowed', '09847236523', 'dasdjk@gmail.com', 'Filipino', 'B+', 'Human Resources', 'sfasdffasf', NULL, 600.00, '542353451111', '234523523453', '236345634111', '2025-06-01 12:32:59', '2025-06-06 08:14:02', 3, NULL, 1),
(128, 'EMP-110318', '23523423', 'dfasdfasdf', 'asdfgafdg', 'asdfsadf', '2025-06-04', 'asdfsadf', 'Male', 'Married', '09974826387', 'fasdfsadf@GMAIL.COM', 'FAFAS', 'AB+', 'Staff', 'FASDFSADF', NULL, 43.00, '412123123213', '421341242342', '123123213124', '2025-06-04 10:43:55', '2025-06-06 11:31:03', 2, '2025-06-06 19:31:03', 0),
(129, 'EMP-178957', '4213423', 'asdasd', 'dads', 'asdsad', '2025-06-04', 'sfsfsdf', 'Male', 'Married', '09872783648', 'sdfsd@gmail.com', 'dadas', 'O-', 'Human Resources', 'fgasdgsadg', NULL, 4134.00, '412412412431', '123124124124', '412412321321', '2025-06-04 11:29:10', '2025-06-06 13:07:56', 2, NULL, 0),
(130, 'EMP-134637', '424242342323', 'asdfds', 'asdfadsf', 'sfsdf', '2025-06-04', 'fasdfas', 'Male', 'Divorced', '09975289589', 'fasdfsda@gmail.com', 'fasdfasdf', 'O+', 'Driver', 'asdfasdfasdfsadf', NULL, 12.00, '241414134255', '151231234124', '512512512421', '2025-06-04 13:53:34', '2025-06-08 12:00:07', 4, '2025-06-08 20:00:07', 1),
(137, 'EMP-176689', '425235', 'sdhafga', 'sdgfg', 'sdfgsdfg', '2025-06-06', 'fsfsdag', 'Female', 'Divorced', '09677865875', 'roynabesis123@gmail.com', 'hsadfgsdh', 'A-', 'Human Resources', 'afhdhsdh', NULL, 4234.00, '654653737374', '745674568356', '458467373737', '2025-06-06 08:44:55', '2025-06-06 09:59:56', 3, '2025-06-06 17:59:56', 1),
(138, 'EMP-377199', '4242134234', 'giovan kier', 'v', 'cardenio', '2025-06-06', 'asasdfsadf', 'Male', 'Married', '09675463534', 'solicar.junel.4@gmail.com', 'Filipino', 'O+', 'Staff', 'knhjkhh', NULL, 600.00, '674563868770', '097896456536', '987875456756', '2025-06-06 08:57:47', '2025-06-06 10:45:13', 3, '2025-06-06 18:45:13', 1),
(139, 'EMP-306215', '5455455', 'jkggkgffg', 'hkgjhg', 'jhjkh', '2025-06-06', 'kjklj', 'Female', 'Married', '09546546546', 'fdfgdg@gmail.com', 'ftghffhj', 'O-', 'Manager', 'jhgvjhghg', NULL, 55.00, '404898046546', '098709880545', '094898086505', '2025-06-06 13:12:47', '2025-06-06 13:12:47', 3, NULL, 0);

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
(71, 115, 62),
(72, 118, 63),
(73, 117, 64),
(74, 116, 65),
(77, 122, 68),
(78, 124, 69),
(79, 125, 70),
(80, 128, 71),
(85, 130, 76);

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
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leaves`
--

INSERT INTO `leaves` (`id`, `employee_id`, `manager_id`, `leave_type`, `start_date`, `end_date`, `duration`, `reason`, `status`, `created_at`, `updated_at`) VALUES
(31, 118, 3, 'Personal Leave', '2025-05-29', '2025-05-29', 1, 'asdasd', 'Rejected', '2025-05-29 01:36:35', '2025-05-29 01:36:55'),
(32, 118, 3, 'Personal Leave', '2025-05-30', '2025-05-30', 1, 'gasdffs', 'Approved', '2025-05-29 01:38:31', '2025-05-29 01:39:17'),
(34, 122, 2, 'Sick Leave', '2025-05-29', '2025-05-30', 2, 'sick', 'Rejected', '2025-05-29 07:32:12', '2025-05-29 07:33:37'),
(35, 122, 2, 'Emergency Leave', '2025-05-29', '2025-05-30', 2, 'sdfsdff', 'Approved', '2025-05-29 07:34:27', '2025-05-29 07:34:40'),
(36, 116, 3, 'Personal Leave', '2025-05-30', '2025-05-30', 1, 'Nabilar hahah', 'Rejected', '2025-05-29 13:40:48', '2025-05-29 17:06:46'),
(37, 124, 4, 'Vacation Leave', '2025-05-31', '2025-05-31', 1, 'fsdfsfd', 'Rejected', '2025-05-30 01:23:35', '2025-06-01 12:30:41'),
(38, 124, 4, 'Vacation Leave', '2025-05-31', '2025-06-02', 3, 'gsdsd', 'Approved', '2025-05-30 01:34:03', '2025-06-01 12:29:54'),
(39, 125, 3, 'Vacation Leave', '2025-06-16', '2025-06-18', 3, 'asdasd', 'Approved', '2025-06-01 12:35:25', '2025-06-05 16:11:51'),
(40, 137, 3, 'Emergency Leave', '2025-06-06', '2025-06-06', 1, 'easd', 'Approved', '2025-06-06 08:50:49', '2025-06-06 08:50:59');

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
-- Table structure for table `managers`
--

CREATE TABLE `managers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `branch` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `managers`
--

INSERT INTO `managers` (`id`, `name`, `email`, `branch`, `password`, `created_at`, `updated_at`) VALUES
(2, 'Roy B. Nabesis', 'roydonnabesis@gmail.com', 'Branch-Panabo', '$2y$10$fxZ.6r183iuUZHjDEWVPuObVM7HX5uew8X1d8g0DgykCXJkJG0Bba', '2025-05-28 14:02:00', '2025-05-28 14:17:35'),
(3, 'Ronn Charles O. Domingo', 'ronncharlesd8@gmail.com', 'Branch-Tagum-1', '$2y$10$5F2dkNXvyIsiShN.405JZ.mzkfBzNupIR.1KgMSChR7XRbpXzud62', '2025-05-28 14:18:42', '2025-05-28 14:19:38'),
(4, 'Ken Jazver V. Galanido', 'terraken08@gmail.com', 'Branch-Tagum-2', '$2y$10$nJhnkIvGWcxwy1xvFQtXbeMfL7DhyJN3O0Bmvq7uaSarswwWE1rt6', '2025-05-28 14:19:05', '2025-05-28 14:19:38');

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
  `deductions` decimal(10,2) NOT NULL DEFAULT 0.00,
  `gross_pay` decimal(10,2) NOT NULL DEFAULT 0.00,
  `net_pay` decimal(10,2) NOT NULL DEFAULT 0.00,
  `generated_by` int(11) DEFAULT NULL,
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payroll`
--

INSERT INTO `payroll` (`id`, `employee_id`, `pay_period_start`, `pay_period_end`, `payroll_frequency`, `payroll_duration`, `total_hours`, `deductions`, `gross_pay`, `net_pay`, `generated_by`, `generated_at`) VALUES
(13, 115, '2025-03-01', '2025-03-31', 'monthly', 31, 160, 950.00, 12000.00, 11050.00, NULL, '2025-05-27 07:21:47'),
(14, 115, '2025-04-01', '2025-04-30', 'monthly', 30, 160, 1200.00, 12000.00, 10800.00, NULL, '2025-05-27 07:21:47'),
(15, 115, '2025-05-01', '2025-05-31', 'monthly', 31, 160, 1000.00, 12000.00, 11000.00, NULL, '2025-05-27 07:21:47');

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `time_in` time NOT NULL,
  `time_out` time NOT NULL,
  `grace_period` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedules`
--

INSERT INTO `schedules` (`id`, `name`, `time_in`, `time_out`, `grace_period`) VALUES
(62, 'Roy B. Nabesis', '08:00:00', '16:00:00', 15),
(63, 'Giovan Kier V. Cardenio', '10:00:00', '17:00:00', 15),
(64, 'Ken Jazver V. Galanido', '07:00:00', '15:00:00', 15),
(65, 'Ronn Charles O. Domingo', '07:00:00', '15:00:00', 15),
(66, 'Fjsdjkfh S. Hdsdf', '07:00:00', '16:00:00', 1),
(68, 'Kres-ann B. Oclarit', '04:00:00', '19:00:00', 2),
(69, 'Test T. Test', '07:00:00', '16:00:00', 15),
(70, 'Test 2 T. Test 2', '07:00:00', '16:00:00', 15),
(71, 'Dfasdfasdf A. Asdfsadf', '07:00:00', '16:00:00', 11),
(76, 'Asdfds A. Sfsdf', '05:01:00', '16:00:00', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_attendance_per_day` (`employee_id`,`date`),
  ADD KEY `idx_attendance_date` (`date`),
  ADD KEY `idx_attendance_status` (`status`),
  ADD KEY `idx_attendance_empid` (`employee_id`);

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
-- Indexes for table `leaves`
--
ALTER TABLE `leaves`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_leave_employee` (`employee_id`),
  ADD KEY `fk_leave_manager` (`manager_id`);

--
-- Indexes for table `leave_rejections`
--
ALTER TABLE `leave_rejections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `leave_id` (`leave_id`),
  ADD KEY `manager_id` (`manager_id`);

--
-- Indexes for table `managers`
--
ALTER TABLE `managers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `payroll`
--
ALTER TABLE `payroll`
  ADD PRIMARY KEY (`id`),
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=140;

--
-- AUTO_INCREMENT for table `employee_schedules`
--
ALTER TABLE `employee_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;

--
-- AUTO_INCREMENT for table `leaves`
--
ALTER TABLE `leaves`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `leave_rejections`
--
ALTER TABLE `leave_rejections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `managers`
--
ALTER TABLE `managers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `payroll`
--
ALTER TABLE `payroll`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

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
-- Constraints for table `leave_rejections`
--
ALTER TABLE `leave_rejections`
  ADD CONSTRAINT `fk_rejection_leave` FOREIGN KEY (`leave_id`) REFERENCES `leaves` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `payroll`
--
ALTER TABLE `payroll`
  ADD CONSTRAINT `payroll_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payroll_ibfk_2` FOREIGN KEY (`generated_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
