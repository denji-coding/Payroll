-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 25, 2025 at 05:29 AM
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
(107, 115, '2025-06-14', '21:50:07', '21:50:48', '21:55:30', '22:17:28', 'Present', '2025-06-14 13:50:07', '2025-06-14 14:17:28'),
(108, 117, '2025-06-14', '21:50:10', '21:51:40', '21:55:44', NULL, 'Present', '2025-06-14 13:50:10', '2025-06-14 13:55:44'),
(109, 116, '2025-06-14', '21:50:39', '21:52:04', '21:59:57', NULL, 'Present', '2025-06-14 13:50:39', '2025-06-14 13:59:57'),
(110, 115, '2025-06-15', '21:18:02', '22:18:19', '22:18:25', '22:18:32', 'Present', '2025-06-15 13:18:02', '2025-06-15 14:18:32'),
(111, 116, '2025-06-15', '22:27:01', '22:43:30', '23:29:00', '23:29:00', 'Present', '2025-06-15 14:27:01', '2025-06-15 15:29:00'),
(112, 115, '2025-06-16', '00:17:07', NULL, NULL, NULL, 'Present', '2025-06-15 16:17:07', '2025-06-15 16:17:07'),
(114, 115, '2025-07-08', '06:01:45', '11:01:49', '15:27:44', '15:28:08', 'Present', '2025-07-08 07:01:45', '2025-07-08 07:28:08'),
(115, 115, '2025-07-15', '21:07:05', '21:17:18', '21:28:12', '22:06:58', 'Present', '2025-07-15 13:07:05', '2025-07-15 14:06:58'),
(121, 116, '2025-07-15', '22:47:30', '22:49:27', '22:49:39', '22:53:48', 'Present', '2025-07-15 14:47:30', '2025-07-15 14:53:48'),
(122, 118, '2025-07-15', '22:56:11', '22:57:00', '23:19:43', '23:19:49', 'Present', '2025-07-15 14:56:11', '2025-07-15 15:19:49'),
(123, 117, '2025-07-15', '22:56:17', '22:57:16', '23:46:29', '23:46:34', 'Present', '2025-07-15 14:56:17', '2025-07-15 15:46:34'),
(124, 118, '2025-07-17', '22:57:10', '22:57:14', '22:57:15', '22:57:18', 'Present', '2025-07-17 14:57:10', '2025-07-17 14:57:18'),
(125, 118, '2025-07-23', '14:37:06', '14:37:29', NULL, NULL, 'Present', '2025-07-23 06:37:06', '2025-07-23 06:37:29');

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
(41, 5.00, 5.00, 5.00, 'active', '2025-07-17 13:54:24');

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
(115, 'EMP-420415', '3749540708', 'roy', 'basillote', 'nabesis', '2000-11-08', 'Davao city', 'Male', 'Single', '09923962962', 'roynabesis.main@gmail.com', 'Filipino', 'O+', 'Human Resources', 'Purok 7 - San miguel, San isidro, Bunawan, Davao city', 'upload/Roy Nabesis.jpg', 600.00, '359358793535', '562346235235', '235235235333', '2025-05-25 09:42:16', '2025-06-25 17:36:11', 4, NULL, 1),
(116, 'EMP-399188', '3750206196', 'ronn charles', 'o', 'domingo', '2003-08-18', 'davao city', 'Male', 'Married', '09911344678', 'ronncharlesd8@gmail.com', 'Filipino', 'AB+', 'Driver', 'purok 18, Upper New Visayas Panabo City', 'upload/Ronn Charles Domingo.jpg', 600.00, '768936793986', '734892735893', '839486786354', '2025-05-25 09:45:08', '2025-06-05 15:28:56', 3, NULL, 1),
(117, 'EMP-853677', '3749150996', 'ken jazver', 'v', 'galanido', '2002-12-08', 'panabo city', 'Male', 'Single', '09562876824', 'terraken08@gmail.com', 'Filipino', 'O+', 'Manager', 'sharon, new visayas, panabo city', 'upload/Ken Jazver Galanido.jpg', 600.00, '873460367222', '757835913333', '725987523522', '2025-05-25 09:47:50', '2025-06-05 14:47:55', 4, NULL, 1),
(118, 'EMP-517859', '3749734948', 'giovan kier', 'v', 'cardenio', '2002-05-13', 'medical mission group hospital', 'Male', 'Single', '09260829512', 'kiercardenio@gmail.com', 'arabo', 'B+', 'Staff', 'purok everlasting, brgy. gredu, panabo city', 'upload/Giovan Kier Cardenio.jpg', 600.00, '828924729858', '456346345654', '235246726256', '2025-05-25 09:50:52', '2025-06-06 10:09:44', 2, NULL, 1),
(122, 'EMP-661929', '4563465646', 'Kres-Ann', 'Bodiongan', 'Oclarit', '1998-11-09', 'Tagana-an', 'Female', 'Single', '09913692194', 'koclarit@dnsc.edu.ph', 'Filipino', 'O+', 'Staff', 'Panabo City', 'upload/man (1).png', 1200.00, '111111111111', '647836458589', '535424354659', '2025-05-29 07:18:49', '2025-06-06 08:25:32', 3, NULL, 1),
(124, 'EMP-699453', '4182319604', 'test', 'test', 'test', '2025-06-07', 'Tagana-an', 'Male', 'Separated', '09544356534', 'jhdfsjfhds@gmail.com', 'Filipino', 'O-', 'Staff', 'Panabo City', NULL, 600.00, '424234234234', '523532532534', '122423423511', '2025-05-30 01:15:26', '2025-07-12 13:00:54', 4, NULL, 1),
(125, 'EMP-153970', '234234', 'test 2', 'test 2', 'test 2', '2025-06-01', 'adsa', 'Female', 'Widowed', '09847236523', 'dasdjk@gmail.com', 'Filipino', 'B+', 'Human Resources', 'sfasdffasf', NULL, 600.00, '542353451111', '234523523111', '236345634111', '2025-06-01 12:32:59', '2025-07-12 13:00:57', 3, NULL, 1),
(129, 'EMP-178957', '4213423', 'asdasd', 'dads', 'asdsad', '2025-06-04', 'sfsfsdf', 'Male', 'Married', '09872783648', 'ronitaborbonnabesis@gmail.com', 'dadas', 'O-', 'Human Resources', 'fgasdgsadg', NULL, 100.00, '412412412431', '123124124124', '412412321321', '2025-06-04 11:29:10', '2025-07-20 10:41:18', 2, NULL, 1),
(130, 'EMP-134637', '424242342323', 'asdfds', 'asdfadsf', 'sfsdf', '2025-06-04', 'fasdfas', 'Male', 'Divorced', '09975289589', 'fasdfsda@gmail.com', 'fasdfasdf', 'O+', 'Driver', 'asdfasdfasdfsadf', NULL, 12.00, '241414134255', '151231234124', '512512512421', '2025-06-04 13:53:34', '2025-06-08 12:00:07', 4, '2025-06-08 20:00:07', 1),
(138, 'EMP-377199', '4242134234', 'giovan kier', 'v', 'cardenio', '2025-06-06', 'asasdfsadf', 'Male', 'Married', '09675463534', 'solicar.junel.4@gmail.com', 'Filipino', 'O+', 'Staff', 'knhjkhh', NULL, 600.00, '674563868770', '097896456536', '987875456756', '2025-06-06 08:57:47', '2025-06-06 10:45:13', 3, '2025-06-06 18:45:13', 1),
(139, 'EMP-306215', '5455455', 'jkggkgffg', 'hkgjhg', 'jhjkh', '2025-06-06', 'kjklj', 'Female', 'Married', '09546546546', 'fdfgdg@gmail.com', 'ftghffhj', 'O-', 'Manager', 'jhgvjhghg', NULL, 55.00, '404898046546', '098709880545', '094898086505', '2025-06-06 13:12:47', '2025-07-15 16:46:34', 3, NULL, 0),
(140, 'EMP-444488', '24234', 'gdfgdgd', NULL, 'dfgdfgd', '2025-06-25', 'gdfgdfg', 'Male', 'Divorced', '09574563456', 'dgfdfg@gmail.com', 'gsdgdsfg', 'A+', 'Manager', 'gsdfgdsfg', NULL, 123.00, '463636345636', '254673735625', '624625656214', '2025-06-25 15:30:29', '2025-07-15 16:10:11', NULL, '2025-07-16 00:10:11', 0),
(141, 'EMP-436584', '09067456', 'huhuhu', 'hahaha', 'hehehe', '2025-06-25', 'fasfsf', 'Male', 'Divorced', '09777111111', 'aaaa@gmai.com', 'aaaa', 'A+', 'Manager', 'aaaaaa', 'upload/emp_68668555b16416.40350052.jpg', 123.00, '313123133333', '141241211111', '141412311111', '2025-06-25 15:42:41', '2025-07-08 12:58:36', 4, NULL, 1),
(142, 'EMP-952007', '4234', 'dasd', 'afsd', 'asda', '2025-07-04', 'asdas', 'Male', '', '', 'adsd11@gmail.com', 'das', 'A+', 'Manager', 'asd', NULL, 312.00, '124124124242', '536357224624', '462543252534', '2025-06-25 17:53:31', '2025-06-25 17:55:39', 3, '2025-06-26 01:55:39', 0),
(143, 'EMP-465183', '45435', 'fasfs', 'dafaf', 'daasd', '2025-06-25', 'asdas', 'Male', 'Single', '09258483578', 'fsdfsdf@gmail.com', 'dasd', 'A+', 'Human Resources', 'dasd', NULL, 213.00, '124213425252', '123515345345', '125145345646', '2025-06-25 17:56:16', '2025-06-25 18:12:03', 4, '2025-06-26 02:12:03', 1),
(145, 'EMP-554279', '242434', 'jinwoo', 'sdfsdf', 'sung', '2025-06-26', 'fsdf', 'Female', 'Separated', '09656456456', 'fsdf@gmail.com', 'adsd', 'A+', 'Manager', 'dasasd', 'upload/emp_686d1add9c2944.57728013.jpg', 123.00, '124124154124', '523255535234', '414121232141', '2025-06-25 18:04:22', '2025-07-08 13:28:11', 4, NULL, 1),
(146, 'EMP-484666', '41413123', 'adasd', 'adad', 'aasd', '2025-07-03', 'davao city', 'Male', 'Single', '09564565465', 'dadsdas@gmail.com', 'dasd', 'A-', 'Human Resources', 'dasd', 'upload/file_00000000975c61f6be519c4594672620_conversation_id=67f90712-5470-8012-aafb-9c1d56f8c6d5&message_id=8f8214ef-bf24-4cea-9efc-407b3ff768cf.png', 100.00, '123412412421', '541312341252', '214521431243', '2025-07-03 12:47:36', '2025-07-20 10:17:40', 4, NULL, 1),
(147, 'EMP-856735', '13123', 'dadad', 'fdwdaddasd', 'adada', '2025-07-17', 'dasdasd', 'Female', 'Married', '09234522142', 'sgsdgsdfg@gmail.com', 'Filipino', 'O-', 'Human Resources', 'dada', NULL, 123.00, '124155234523', '523531623462', '234626234564', '2025-07-03 12:48:47', '2025-07-20 12:42:16', 4, '2025-07-03 23:06:50', -1),
(148, 'EMP-686128', '63563554', 'fgafdasfsa', 'adasdasd', 'gasgasgg', '2025-07-18', 'sadfasd', 'Male', 'Single', '09578257852', 'dafgadflj@gmail.com', 'Filipino', 'A+', 'Manager', 'dasd', NULL, 123.00, '141241251251', '262562462346', '254234524523', '2025-07-03 12:51:28', '2025-07-03 15:06:38', 3, '2025-07-03 23:06:38', 0),
(149, 'EMP-778888', '4342', 'fsadf', 'dadfaf', 'sgf', '2025-07-24', 'fsdfs', 'Male', 'Separated', '09746264286', 'jkiahg@gmail.com', 'sagsdfgs', 'A+', 'Manager', 'fasfsf', NULL, 442.00, '123412412412', '562234673856', '757456754673', '2025-07-03 13:28:40', '2025-07-06 07:07:55', 4, '2025-07-06 15:07:55', 0),
(151, 'EMP-167763', '7635636', 'eeqwe', 'fasfga', 'asgasdfg', '2025-07-20', 'sdgeqweq', 'Male', 'Married', '09846126523', 'mongoallstar01@gmail.com', 'afasf', 'A+', 'Staff', 'fsdfasfg', NULL, 200.00, '536236723623', '723462462511', '262366262623', '2025-07-20 10:50:37', '2025-07-23 15:29:49', 2, NULL, 1),
(153, 'EMP-320662', '55325', 'asgasga', 'fgasgasg', 'asgasdgsg', '2025-07-20', 'gasdg', 'Male', 'Separated', '09091782165', 'hhsdfgjg@gmail.com', 'Filipino', 'A+', 'Human Resources', 'gsdggsadg', NULL, 323.00, '412152163463', '171561312545', '552521512525', '2025-07-20 11:28:31', '2025-07-20 12:39:31', 4, NULL, 0),
(154, 'EMP-594968', '41241', 'fasdfsadf', 'fasfsadf', 'asfasfdas', '2025-07-28', 'fasfsad', 'Male', 'Separated', '09057682736', 'fasdfsdf@gmail.com', 'gasdgasgsd', 'A+', 'Manager', 'fasfsadf', NULL, 12312.00, '412412421421', '512522345236', '456727262162', '2025-07-20 11:30:32', '2025-07-20 12:39:34', 4, NULL, 0),
(158, 'EMP-238778', '24123412', 'asdfasfsadf', 'safasfsda', 'fasfsadf', '2025-07-15', 'fasfsaf', 'Male', 'Separated', '09085478257', 'fsajkha@gmail.com', 'dadafa', 'A+', 'Manager', 'fgasgasg', NULL, 123.00, '154154246547', '267256345324', '623623646246', '2025-07-20 11:42:54', '2025-07-20 12:43:11', 4, NULL, -1),
(159, 'EMP-257174', '4124214', 'asfasfsa', 'adfasdas', 'afasfasf', '2025-07-30', 'fasdfasdf', 'Male', 'Married', '09954782342', 'afgafg@gmail.com', 'gsfasfas', 'A+', 'Staff', 'fsadfasdfs', NULL, 31312.00, '141242142132', '451241241241', '515253453345', '2025-07-20 11:44:25', '2025-07-23 15:42:58', 3, NULL, 0),
(160, 'EMP-341898', '5235234124', 'jhe5ttw', 'asdgh', 'adgadfgg', '2025-07-24', 'hhdfhdfhg', 'Female', 'Divorced', '09095782637', 'gsdjgh@gmail.com', 'fasfas', 'A+', 'Manager', 'dasdfasf', NULL, 31234.00, '125435346346', '324562345726', '645634654645', '2025-07-20 11:46:32', '2025-07-20 11:51:40', 3, '2025-07-20 19:51:40', 0),
(162, 'EMP-605829', '1234123', 'safsadf', 'asfasf', 'fasdfsadf', '2025-07-20', 'asfgsag', 'Male', 'Married', '09941662164', 'asgasdg@gmail.com', 'kjjfsafas', 'A+', 'Staff', 'fasfsadf', NULL, 3213.00, '141241241254', '525251414124', '125412514124', '2025-07-20 11:50:39', '2025-07-20 11:51:14', 3, '2025-07-20 19:51:14', 0),
(163, 'EMP-375954', '54234', 'dasdsad', 'qdad', 'asdasdas', '2025-07-24', 'dasdasd', 'Male', 'Single', '09974523758', 'ufjahj@gmail.com', 'fasadasdasa', 'A+', 'Human Resources', 'dfadasd', NULL, 1231.00, '151545234625', '547262545252', '352452352352', '2025-07-20 12:50:52', '2025-07-20 12:55:09', 4, '2025-07-20 20:55:09', 0),
(167, 'EMP-117011', '123123', 'adasd', 'dadsad', 'addaasd', '2025-07-14', 'dadasd', 'Male', 'Separated', '09984678682', 'dadas@gmail.com', 'asdas', 'A+', 'Human Resources', 'adasda', 'upload/emp_687ce7ff7b2836.18115541.png', 123.00, '412412415254', '243523452345', '452352524525', '2025-07-20 12:58:39', '2025-07-23 16:02:24', 4, NULL, -1);

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
(114, 146, 105);

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
(103, 118, 2, 'Sick Leave', '2025-07-01', '2025-07-10', 10, '', 'upload/med_cert/med_688080f6180282.11263295.jpg', 'Approved', '2025-07-23 06:28:06', '2025-07-23 06:33:03');

-- --------------------------------------------------------

--
-- Table structure for table `leave_credits`
--

CREATE TABLE `leave_credits` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `leave_type` enum('Sick Leave','Emergency Leave','Vacation Leave','Personal Leave','Maternity/Paternity Leave') NOT NULL,
  `allowed` int(11) DEFAULT 0,
  `taken` int(11) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_credits`
--

INSERT INTO `leave_credits` (`id`, `employee_id`, `leave_type`, `allowed`, `taken`, `updated_at`) VALUES
(1, 115, 'Sick Leave', 10, 0, '2025-07-08 15:45:04'),
(2, 115, 'Emergency Leave', 5, 1, '2025-07-05 13:56:02'),
(3, 115, 'Vacation Leave', 5, 0, '2025-07-08 15:45:04'),
(4, 115, 'Personal Leave', 5, 0, '2025-07-02 15:39:31'),
(5, 115, 'Maternity/Paternity Leave', 8, 0, '2025-07-08 15:45:04'),
(6, 122, 'Sick Leave', 10, 0, '2025-07-02 15:40:25'),
(7, 122, 'Emergency Leave', 5, 0, '2025-07-02 15:40:25'),
(8, 122, 'Vacation Leave', 5, 0, '2025-07-02 15:40:25'),
(9, 122, 'Personal Leave', 5, 0, '2025-07-02 15:40:25'),
(10, 122, 'Maternity/Paternity Leave', 8, 0, '2025-07-02 15:40:25'),
(11, 118, 'Sick Leave', 10, 1, '2025-07-23 06:33:03'),
(12, 118, 'Emergency Leave', 5, 0, '2025-07-18 03:05:33'),
(13, 118, 'Vacation Leave', 5, 0, '2025-07-18 03:05:33'),
(14, 118, 'Personal Leave', 5, 0, '2025-07-18 03:05:33'),
(15, 118, 'Maternity/Paternity Leave', 8, 0, '2025-07-18 03:05:33');

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
  `m_updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `managers`
--

INSERT INTO `managers` (`id`, `m_employee_id`, `m_photo_path`, `m_first_name`, `m_middle_name`, `m_last_name`, `m_full_name`, `m_email`, `m_branch`, `m_position`, `m_rfid_number`, `m_dob`, `m_place_of_birth`, `m_sex`, `m_civil_status`, `m_contact_number`, `m_citizenship`, `m_blood_type`, `m_address`, `m_base_salary`, `m_sss_number`, `m_pagibig_number`, `m_philhealth_number`, `m_password`, `m_created_at`, `m_updated_at`) VALUES
(2, '', '1753304576_as_if_logo.png', 'roy', 'b', 'nabesis', 'Roy B. Nabesis', 'roydonnabesis@gmail.com', '', 'Manager', '41234234', '2000-11-08', 'Asdas', 'Male', 'Single', '09525434343', 'fdfsd', 'O+', 'fsdfsdfdsf', 123.00, '542342344234', '423423423423', '312312312333', '$2y$10$fxZ.6r183iuUZHjDEWVPuObVM7HX5uew8X1d8g0DgykCXJkJG0Bba', '2025-05-28 14:02:00', '2025-07-23 22:30:33'),
(3, '', NULL, 'ronn charles', 'o', 'domingo', 'Ronn Charles O. Domingo', 'ronncharlesd8@gmail.com', 'Branch-Tagum-1', 'Manager', '234234', '2002-10-02', 'dasd', 'Male', 'Single', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$10$5F2dkNXvyIsiShN.405JZ.mzkfBzNupIR.1KgMSChR7XRbpXzud62', '2025-05-28 14:18:42', '2025-07-23 16:10:58'),
(4, '', NULL, 'ken jazver', 'v', 'galanido', 'Ken Jazver V. Galanido', 'terraken08@gmail.com', 'Branch-Tagum-2', 'Manager', '4234234', '2003-12-07', 'dasd', 'Male', 'Single', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$10$nJhnkIvGWcxwy1xvFQtXbeMfL7DhyJN3O0Bmvq7uaSarswwWE1rt6', '2025-05-28 14:19:05', '2025-07-23 16:10:58'),
(5, 'EMP-320673', '', 'dasdasdd', 'asdasd', 'dasdasd', '', 'fsdasoO@fmail.com', 'LMG Co., Ltd - Tagum', 'Manager', '3123123', '2025-07-24', 'asdasd', 'Male', 'Divorced', '09525534534', 'dasdsa', 'O+', 'dasdasd', 123123.00, '412412412412', '124121312111', '412412312312', '', '2025-07-23 14:49:55', '2025-07-24 04:38:26'),
(18, 'EMP-294326', '', 'ddsss', 'dasdasd', 'asdasd', '', 'dsfsdf@gmail.com', 'LMG Co., Ltd - Tagum', 'Manager', '534534', '2025-07-17', 'Dasdasd', 'Male', 'Separated', '09563453235', 'dasd', 'O+', 'dasdsa', 3123.00, '424235523452', '523522333123', '143562359999', '$2y$10$FQXzTugVPWJK1V2OS7Wq9ugnyA7ak5BCT/QWmGJ4GMlUsPL5Q4QeK', '2025-07-23 20:58:06', '2025-07-24 05:00:55'),
(20, 'EMP-566102', 'upload/emp_6881a999a27060.79155070.jpg', 'fsdfds', 'fsfsdf', 'ggdsfsdf', '', 'klgagj@gmail.com', 'Global Marketing Alliance - Panabo', 'Manager', '6546', '0000-00-00', 'agedg', 'Male', 'Separated', '09535876385', 'fdasds', 'O+', 'fgsdgfdsg', 312.00, '412435465464', '342545565475', '235434565464', '$2y$10$.dWH8u527rHcQKeJseTI9ObYQURQRcnWI1OZbuFWCSkdO9ViBrKP6', '2025-07-24 03:33:45', '2025-07-24 03:33:45'),
(21, 'EMP-150244', '', 'fssdf', 'asfasdf', 'sfgadsfgg', '', 'gfd@gmail.com', 'Branch-Tagum-2', 'Manager', '4234235', '2025-07-23', 'sdfsdfs', 'Male', 'Divorced', '09546356354', 'dasd', 'O+', 'dasd', 123.00, '423534687970', '858798087008', '896875676534', '$2y$10$ToHuAtnGEn0HCZtGeco9I.r9s0Elyiz6FXdwtqiE/lzDYljn5rX22', '2025-07-24 05:03:22', '2025-07-24 05:03:22');

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
(72, 151, '2025-07-01', '2025-07-18', 'monthly', 18, 0, 0.00, 0.00, 0.00, 0, 18, 0, 0.00, 0.00, 0.00, 2, '2025-07-20 10:59:59'),
(86, 115, '2025-07-01', '2025-07-18', 'monthly', 22, 16, 60.00, 60.00, 60.00, 2, 16, 4, 133.33, 1200.00, 886.67, 4, '2025-07-20 16:48:15'),
(87, 118, '2025-07-01', '2025-07-18', 'monthly', 18, 16, 60.00, 60.00, 60.00, 2, 16, 0, 0.00, 1200.00, 1020.00, 2, '2025-07-22 06:30:20');

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
(42, 72, 151, 2, '2025-07-20 19:00:00', 'payslips/payslip_EMP-167763_2025-07-01_2025-07-18.pdf'),
(56, 86, 115, 4, '2025-07-21 00:48:16', 'payslips/payslip_EMP-420415_2025-07-01_2025-07-18.pdf'),
(57, 87, 118, 2, '2025-07-22 14:30:22', 'payslips/payslip_EMP-517859_2025-07-01_2025-07-18.pdf');

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
  `grace_period` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedules`
--

INSERT INTO `schedules` (`id`, `name`, `sched_morning_in`, `sched_morning_out`, `sched_afternoon_in`, `sched_afternoon_out`, `grace_period`) VALUES
(69, 'Test T. Test', '07:00:00', '16:00:00', '00:00:00', '00:00:00', 15),
(70, 'Test 2 T. Test 2', '07:00:00', '16:00:00', '00:00:00', '00:00:00', 15),
(88, 'Kres-Ann B. Oclarit', '06:00:00', '11:00:00', '13:00:00', '17:00:00', 11),
(89, 'Giovan Kier V. Cardenio', '06:00:00', '11:00:00', '13:00:00', '17:00:00', 11),
(90, 'Huhuhu H. Hehehe', '06:00:00', '11:00:00', '13:00:00', '16:00:00', 11),
(95, 'Ken Jazver V. Galanido', '06:00:00', '11:00:00', '13:00:00', '17:00:00', 11),
(96, 'Sfdsdf S. Sfsdf', '06:00:00', '11:00:00', '13:00:00', '17:00:00', 5),
(101, 'Roy B. Nabesis', '06:00:00', '11:00:00', '13:00:00', '17:00:00', 20),
(103, 'Ronn Charles O. Domingo', '22:00:00', '22:05:00', '22:10:00', '22:15:00', 14),
(105, 'Adasd A. Aasd', '04:00:00', '11:00:00', '13:00:00', '17:00:00', 14);

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
-- Indexes for table `benefit_rates`
--
ALTER TABLE `benefit_rates`
  ADD PRIMARY KEY (`id`);

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
-- Indexes for table `leave_credits`
--
ALTER TABLE `leave_credits`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_leave` (`employee_id`,`leave_type`);

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
  ADD UNIQUE KEY `email` (`m_email`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=126;

--
-- AUTO_INCREMENT for table `benefit_rates`
--
ALTER TABLE `benefit_rates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=173;

--
-- AUTO_INCREMENT for table `employee_schedules`
--
ALTER TABLE `employee_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=115;

--
-- AUTO_INCREMENT for table `leaves`
--
ALTER TABLE `leaves`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- AUTO_INCREMENT for table `leave_credits`
--
ALTER TABLE `leave_credits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `leave_rejections`
--
ALTER TABLE `leave_rejections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `managers`
--
ALTER TABLE `managers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `payroll`
--
ALTER TABLE `payroll`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT for table `payslips`
--
ALTER TABLE `payslips`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;

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
  ADD CONSTRAINT `leave_credits_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `leave_rejections`
--
ALTER TABLE `leave_rejections`
  ADD CONSTRAINT `fk_rejection_leave` FOREIGN KEY (`leave_id`) REFERENCES `leaves` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
