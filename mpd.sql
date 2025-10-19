-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 19, 2025 at 06:12 PM
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
-- Database: `mpd`
--

-- --------------------------------------------------------

--
-- Table structure for table `backup_history`
--

CREATE TABLE `backup_history` (
  `backup_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `backup_file` varchar(255) NOT NULL,
  `backup_size` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cities`
--

CREATE TABLE `cities` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `state_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cities`
--

INSERT INTO `cities` (`id`, `name`, `state_id`, `created_at`) VALUES
(1, 'Mumbai', 14, '2025-10-11 08:03:58'),
(2, 'Pune', 14, '2025-10-11 08:03:58'),
(3, 'Nagpur', 14, '2025-10-11 08:03:58'),
(4, 'Nashik', 14, '2025-10-11 08:03:58'),
(5, 'Aurangabad', 14, '2025-10-11 08:03:58'),
(6, 'Bangalore', 11, '2025-10-11 08:03:58'),
(7, 'Mysore', 11, '2025-10-11 08:03:58'),
(8, 'Hubli', 11, '2025-10-11 08:03:58'),
(9, 'Mangalore', 11, '2025-10-11 08:03:58'),
(10, 'Belgaum', 11, '2025-10-11 08:03:58'),
(11, 'Chennai', 23, '2025-10-11 08:03:58'),
(12, 'Coimbatore', 23, '2025-10-11 08:03:58'),
(13, 'Madurai', 23, '2025-10-11 08:03:58'),
(14, 'Tiruchirappalli', 23, '2025-10-11 08:03:58'),
(15, 'Salem', 23, '2025-10-11 08:03:58'),
(16, 'Los Angeles', 35, '2025-10-11 08:03:58'),
(17, 'San Francisco', 35, '2025-10-11 08:03:58'),
(18, 'San Diego', 35, '2025-10-11 08:03:58'),
(19, 'San Jose', 35, '2025-10-11 08:03:58'),
(20, 'Fresno', 35, '2025-10-11 08:03:58'),
(21, 'Houston', 73, '2025-10-11 08:03:58'),
(22, 'Dallas', 73, '2025-10-11 08:03:58'),
(23, 'Austin', 73, '2025-10-11 08:03:58'),
(24, 'San Antonio', 73, '2025-10-11 08:03:58'),
(25, 'Fort Worth', 73, '2025-10-11 08:03:58'),
(26, 'Thiruvananthapuram', 12, '2025-10-11 08:27:52'),
(27, 'Kochi', 12, '2025-10-11 08:27:52'),
(28, 'Kozhikode', 12, '2025-10-11 08:27:52'),
(29, 'Thrissur', 12, '2025-10-11 08:27:52'),
(30, 'Kollam', 12, '2025-10-11 08:27:52'),
(31, 'Palakkad', 12, '2025-10-11 08:27:52'),
(32, 'Malappuram', 12, '2025-10-11 08:27:52'),
(33, 'Kannur', 12, '2025-10-11 08:27:52'),
(34, 'Hyderabad', 1, '2025-10-11 08:27:52'),
(35, 'Visakhapatnam', 1, '2025-10-11 08:27:52'),
(36, 'Vijayawada', 1, '2025-10-11 08:27:52'),
(37, 'Guntur', 1, '2025-10-11 08:27:52'),
(38, 'Nellore', 1, '2025-10-11 08:27:52'),
(39, 'Kurnool', 1, '2025-10-11 08:27:52'),
(40, 'Tirupati', 1, '2025-10-11 08:27:52'),
(41, 'Ahmedabad', 7, '2025-10-11 08:27:52'),
(42, 'Surat', 7, '2025-10-11 08:27:52'),
(43, 'Vadodara', 7, '2025-10-11 08:27:52'),
(44, 'Rajkot', 7, '2025-10-11 08:27:52'),
(45, 'Bhavnagar', 7, '2025-10-11 08:27:52'),
(46, 'Jamnagar', 7, '2025-10-11 08:27:52'),
(47, 'Junagadh', 7, '2025-10-11 08:27:52'),
(48, 'Jaipur', 21, '2025-10-11 08:27:52'),
(49, 'Jodhpur', 21, '2025-10-11 08:27:52'),
(50, 'Udaipur', 21, '2025-10-11 08:27:52'),
(51, 'Kota', 21, '2025-10-11 08:27:52'),
(52, 'Bikaner', 21, '2025-10-11 08:27:52'),
(53, 'Ajmer', 21, '2025-10-11 08:27:52'),
(54, 'Bharatpur', 21, '2025-10-11 08:27:52'),
(55, 'Lucknow', 26, '2025-10-11 08:27:52'),
(56, 'Kanpur', 26, '2025-10-11 08:27:52'),
(57, 'Agra', 26, '2025-10-11 08:27:52'),
(58, 'Varanasi', 26, '2025-10-11 08:27:52'),
(59, 'Meerut', 26, '2025-10-11 08:27:52'),
(60, 'Allahabad', 26, '2025-10-11 08:27:52'),
(61, 'Bareilly', 26, '2025-10-11 08:27:52'),
(62, 'Ghaziabad', 26, '2025-10-11 08:27:52'),
(63, 'Kolkata', 28, '2025-10-11 08:27:52'),
(64, 'Howrah', 28, '2025-10-11 08:27:52'),
(65, 'Durgapur', 28, '2025-10-11 08:27:52'),
(66, 'Asansol', 28, '2025-10-11 08:27:52'),
(67, 'Siliguri', 28, '2025-10-11 08:27:52'),
(68, 'Bardhaman', 28, '2025-10-11 08:27:52'),
(69, 'Malda', 28, '2025-10-11 08:27:52'),
(70, 'Chandigarh', 20, '2025-10-11 08:27:52'),
(71, 'Ludhiana', 20, '2025-10-11 08:27:52'),
(72, 'Amritsar', 20, '2025-10-11 08:27:52'),
(73, 'Jalandhar', 20, '2025-10-11 08:27:52'),
(74, 'Patiala', 20, '2025-10-11 08:27:52'),
(75, 'Bathinda', 20, '2025-10-11 08:27:52'),
(76, 'Mohali', 20, '2025-10-11 08:27:52'),
(77, 'Gurgaon', 8, '2025-10-11 08:27:52'),
(78, 'Faridabad', 8, '2025-10-11 08:27:52'),
(79, 'Panipat', 8, '2025-10-11 08:27:52'),
(80, 'Ambala', 8, '2025-10-11 08:27:52'),
(81, 'Yamunanagar', 8, '2025-10-11 08:27:52'),
(82, 'Rohtak', 8, '2025-10-11 08:27:52'),
(83, 'Hisar', 8, '2025-10-11 08:27:52'),
(84, 'New Delhi', 29, '2025-10-11 08:27:52'),
(85, 'Central Delhi', 29, '2025-10-11 08:27:52'),
(86, 'East Delhi', 29, '2025-10-11 08:27:52'),
(87, 'North Delhi', 29, '2025-10-11 08:27:52'),
(88, 'South Delhi', 29, '2025-10-11 08:27:52'),
(89, 'West Delhi', 29, '2025-10-11 08:27:52'),
(90, 'Bhopal', 13, '2025-10-11 08:27:52'),
(91, 'Indore', 13, '2025-10-11 08:27:52'),
(92, 'Gwalior', 13, '2025-10-11 08:27:52'),
(93, 'Jabalpur', 13, '2025-10-11 08:27:52'),
(94, 'Ujjain', 13, '2025-10-11 08:27:52'),
(95, 'Sagar', 13, '2025-10-11 08:27:52'),
(96, 'Dewas', 13, '2025-10-11 08:27:52'),
(97, 'Bhubaneswar', 19, '2025-10-11 08:27:52'),
(98, 'Cuttack', 19, '2025-10-11 08:27:52'),
(99, 'Rourkela', 19, '2025-10-11 08:27:52'),
(100, 'Berhampur', 19, '2025-10-11 08:27:52'),
(101, 'Sambalpur', 19, '2025-10-11 08:27:52'),
(102, 'Puri', 19, '2025-10-11 08:27:52'),
(103, 'Balasore', 19, '2025-10-11 08:27:52'),
(104, 'Guwahati', 3, '2025-10-11 08:27:52'),
(105, 'Silchar', 3, '2025-10-11 08:27:52'),
(106, 'Dibrugarh', 3, '2025-10-11 08:27:52'),
(107, 'Jorhat', 3, '2025-10-11 08:27:52'),
(108, 'Tezpur', 3, '2025-10-11 08:27:52'),
(109, 'Nagaon', 3, '2025-10-11 08:27:52'),
(110, 'Tinsukia', 3, '2025-10-11 08:27:52'),
(111, 'Patna', 4, '2025-10-11 08:27:52'),
(112, 'Gaya', 4, '2025-10-11 08:27:52'),
(113, 'Bhagalpur', 4, '2025-10-11 08:27:52'),
(114, 'Muzaffarpur', 4, '2025-10-11 08:27:52'),
(115, 'Darbhanga', 4, '2025-10-11 08:27:52'),
(116, 'Purnia', 4, '2025-10-11 08:27:52'),
(117, 'Arrah', 4, '2025-10-11 08:27:52'),
(118, 'Ranchi', 10, '2025-10-11 08:27:52'),
(119, 'Jamshedpur', 10, '2025-10-11 08:27:52'),
(120, 'Dhanbad', 10, '2025-10-11 08:27:52'),
(121, 'Bokaro', 10, '2025-10-11 08:27:52'),
(122, 'Deoghar', 10, '2025-10-11 08:27:52'),
(123, 'Hazaribagh', 10, '2025-10-11 08:27:52'),
(124, 'Giridih', 10, '2025-10-11 08:27:52'),
(125, 'Raipur', 5, '2025-10-11 08:27:52'),
(126, 'Bhilai', 5, '2025-10-11 08:27:52'),
(127, 'Bilaspur', 5, '2025-10-11 08:27:52'),
(128, 'Korba', 5, '2025-10-11 08:27:52'),
(129, 'Rajnandgaon', 5, '2025-10-11 08:27:52'),
(130, 'Durg', 5, '2025-10-11 08:27:52'),
(131, 'Raigarh', 5, '2025-10-11 08:27:52'),
(132, 'Shimla', 9, '2025-10-11 08:27:52'),
(133, 'Dharamshala', 9, '2025-10-11 08:27:52'),
(134, 'Manali', 9, '2025-10-11 08:27:52'),
(135, 'Solan', 9, '2025-10-11 08:27:52'),
(136, 'Mandi', 9, '2025-10-11 08:27:52'),
(137, 'Palampur', 9, '2025-10-11 08:27:52'),
(138, 'Kullu', 9, '2025-10-11 08:27:52'),
(139, 'Dehradun', 27, '2025-10-11 08:27:52'),
(140, 'Haridwar', 27, '2025-10-11 08:27:52'),
(141, 'Rishikesh', 27, '2025-10-11 08:27:52'),
(142, 'Nainital', 27, '2025-10-11 08:27:52'),
(143, 'Mussoorie', 27, '2025-10-11 08:27:52'),
(144, 'Almora', 27, '2025-10-11 08:27:52'),
(145, 'Haldwani', 27, '2025-10-11 08:27:52'),
(146, 'Panaji', 6, '2025-10-11 08:27:52'),
(147, 'Margao', 6, '2025-10-11 08:27:52'),
(148, 'Vasco da Gama', 6, '2025-10-11 08:27:52'),
(149, 'Mapusa', 6, '2025-10-11 08:27:52'),
(150, 'Ponda', 6, '2025-10-11 08:27:52'),
(151, 'Mormugao', 6, '2025-10-11 08:27:52'),
(152, 'Imphal', 15, '2025-10-11 08:27:52'),
(153, 'Thoubal', 15, '2025-10-11 08:27:52'),
(154, 'Bishnupur', 15, '2025-10-11 08:27:52'),
(155, 'Churachandpur', 15, '2025-10-11 08:27:52'),
(156, 'Ukhrul', 15, '2025-10-11 08:27:52'),
(157, 'Senapati', 15, '2025-10-11 08:27:52'),
(158, 'Shillong', 16, '2025-10-11 08:27:52'),
(159, 'Tura', 16, '2025-10-11 08:27:52'),
(160, 'Jowai', 16, '2025-10-11 08:27:52'),
(161, 'Nongstoin', 16, '2025-10-11 08:27:52'),
(162, 'Williamnagar', 16, '2025-10-11 08:27:52'),
(163, 'Baghmara', 16, '2025-10-11 08:27:52'),
(164, 'Aizawl', 17, '2025-10-11 08:27:52'),
(165, 'Lunglei', 17, '2025-10-11 08:27:52'),
(166, 'Saiha', 17, '2025-10-11 08:27:52'),
(167, 'Champhai', 17, '2025-10-11 08:27:52'),
(168, 'Kolasib', 17, '2025-10-11 08:27:52'),
(169, 'Serchhip', 17, '2025-10-11 08:27:52'),
(170, 'Kohima', 18, '2025-10-11 08:27:52'),
(171, 'Dimapur', 18, '2025-10-11 08:27:52'),
(172, 'Mokokchung', 18, '2025-10-11 08:27:52'),
(173, 'Tuensang', 18, '2025-10-11 08:27:52'),
(174, 'Wokha', 18, '2025-10-11 08:27:52'),
(175, 'Zunheboto', 18, '2025-10-11 08:27:52'),
(176, 'Gangtok', 22, '2025-10-11 08:27:52'),
(177, 'Namchi', 22, '2025-10-11 08:27:52'),
(178, 'Mangan', 22, '2025-10-11 08:27:52'),
(179, 'Gyalshing', 22, '2025-10-11 08:27:52'),
(180, 'Singtam', 22, '2025-10-11 08:27:52'),
(181, 'Rangpo', 22, '2025-10-11 08:27:52'),
(182, 'Agartala', 25, '2025-10-11 08:27:52'),
(183, 'Dharmanagar', 25, '2025-10-11 08:27:52'),
(184, 'Udaipur', 25, '2025-10-11 08:27:52'),
(185, 'Ambassa', 25, '2025-10-11 08:27:52'),
(186, 'Kailashahar', 25, '2025-10-11 08:27:52'),
(187, 'Belonia', 25, '2025-10-11 08:27:52'),
(188, 'Itanagar', 2, '2025-10-11 08:27:52'),
(189, 'Naharlagun', 2, '2025-10-11 08:27:52'),
(190, 'Pasighat', 2, '2025-10-11 08:27:52'),
(191, 'Tezpur', 2, '2025-10-11 08:27:52'),
(192, 'Bomdila', 2, '2025-10-11 08:27:52'),
(193, 'Tawang', 2, '2025-10-11 08:27:52'),
(194, 'Hyderabad', 24, '2025-10-11 08:27:52'),
(195, 'Warangal', 24, '2025-10-11 08:27:52'),
(196, 'Nizamabad', 24, '2025-10-11 08:27:52'),
(197, 'Khammam', 24, '2025-10-11 08:27:52'),
(198, 'Karimnagar', 24, '2025-10-11 08:27:52'),
(199, 'Ramagundam', 24, '2025-10-11 08:27:52'),
(200, 'Srinagar', 30, '2025-10-11 08:27:52'),
(201, 'Jammu', 30, '2025-10-11 08:27:52'),
(202, 'Anantnag', 30, '2025-10-11 08:27:52'),
(203, 'Baramulla', 30, '2025-10-11 08:27:52'),
(204, 'Sopore', 30, '2025-10-11 08:27:52'),
(205, 'Kathua', 30, '2025-10-11 08:27:52'),
(206, 'New York City', 62, '2025-10-11 08:27:52'),
(207, 'Buffalo', 62, '2025-10-11 08:27:52'),
(208, 'Rochester', 62, '2025-10-11 08:27:52'),
(209, 'Yonkers', 62, '2025-10-11 08:27:52'),
(210, 'Syracuse', 62, '2025-10-11 08:27:52'),
(211, 'Albany', 62, '2025-10-11 08:27:52'),
(212, 'New Rochelle', 62, '2025-10-11 08:27:52'),
(213, 'Miami', 39, '2025-10-11 08:27:52'),
(214, 'Tampa', 39, '2025-10-11 08:27:52'),
(215, 'Orlando', 39, '2025-10-11 08:27:52'),
(216, 'Jacksonville', 39, '2025-10-11 08:27:52'),
(217, 'St. Petersburg', 39, '2025-10-11 08:27:52'),
(218, 'Hialeah', 39, '2025-10-11 08:27:52'),
(219, 'Tallahassee', 39, '2025-10-11 08:27:52'),
(220, 'Chicago', 43, '2025-10-11 08:27:52'),
(221, 'Aurora', 43, '2025-10-11 08:27:52'),
(222, 'Rockford', 43, '2025-10-11 08:27:52'),
(223, 'Joliet', 43, '2025-10-11 08:27:52'),
(224, 'Naperville', 43, '2025-10-11 08:27:52'),
(225, 'Springfield', 43, '2025-10-11 08:27:52'),
(226, 'Peoria', 43, '2025-10-11 08:27:52'),
(227, 'Philadelphia', 68, '2025-10-11 08:27:52'),
(228, 'Pittsburgh', 68, '2025-10-11 08:27:52'),
(229, 'Allentown', 68, '2025-10-11 08:27:52'),
(230, 'Erie', 68, '2025-10-11 08:27:52'),
(231, 'Reading', 68, '2025-10-11 08:27:52'),
(232, 'Scranton', 68, '2025-10-11 08:27:52'),
(233, 'Bethlehem', 68, '2025-10-11 08:27:52'),
(234, 'Columbus', 65, '2025-10-11 08:27:52'),
(235, 'Cleveland', 65, '2025-10-11 08:27:52'),
(236, 'Cincinnati', 65, '2025-10-11 08:27:52'),
(237, 'Toledo', 65, '2025-10-11 08:27:52'),
(238, 'Akron', 65, '2025-10-11 08:27:52'),
(239, 'Dayton', 65, '2025-10-11 08:27:52'),
(240, 'Parma', 65, '2025-10-11 08:27:52'),
(241, 'Atlanta', 40, '2025-10-11 08:27:52'),
(242, 'Augusta', 40, '2025-10-11 08:27:52'),
(243, 'Columbus', 40, '2025-10-11 08:27:52'),
(244, 'Savannah', 40, '2025-10-11 08:27:52'),
(245, 'Athens', 40, '2025-10-11 08:27:52'),
(246, 'Sandy Springs', 40, '2025-10-11 08:27:52'),
(247, 'Roswell', 40, '2025-10-11 08:27:52'),
(248, 'Charlotte', 63, '2025-10-11 08:27:52'),
(249, 'Raleigh', 63, '2025-10-11 08:27:52'),
(250, 'Greensboro', 63, '2025-10-11 08:27:52'),
(251, 'Durham', 63, '2025-10-11 08:27:52'),
(252, 'Winston-Salem', 63, '2025-10-11 08:27:52'),
(253, 'Fayetteville', 63, '2025-10-11 08:27:52'),
(254, 'Cary', 63, '2025-10-11 08:27:52'),
(255, 'Detroit', 52, '2025-10-11 08:27:52'),
(256, 'Grand Rapids', 52, '2025-10-11 08:27:52'),
(257, 'Warren', 52, '2025-10-11 08:27:52'),
(258, 'Sterling Heights', 52, '2025-10-11 08:27:52'),
(259, 'Lansing', 52, '2025-10-11 08:27:52'),
(260, 'Ann Arbor', 52, '2025-10-11 08:27:52'),
(261, 'Flint', 52, '2025-10-11 08:27:52'),
(262, 'Newark', 60, '2025-10-11 08:27:52'),
(263, 'Jersey City', 60, '2025-10-11 08:27:52'),
(264, 'Paterson', 60, '2025-10-11 08:27:52'),
(265, 'Elizabeth', 60, '2025-10-11 08:27:52'),
(266, 'Edison', 60, '2025-10-11 08:27:52'),
(267, 'Woodbridge', 60, '2025-10-11 08:27:52'),
(268, 'Lakewood', 60, '2025-10-11 08:27:52'),
(269, 'Virginia Beach', 76, '2025-10-11 08:27:52'),
(270, 'Norfolk', 76, '2025-10-11 08:27:52'),
(271, 'Chesapeake', 76, '2025-10-11 08:27:52'),
(272, 'Richmond', 76, '2025-10-11 08:27:52'),
(273, 'Newport News', 76, '2025-10-11 08:27:52'),
(274, 'Alexandria', 76, '2025-10-11 08:27:52'),
(275, 'Portsmouth', 76, '2025-10-11 08:27:52');

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

CREATE TABLE `countries` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `code` varchar(3) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `countries`
--

INSERT INTO `countries` (`id`, `name`, `code`, `created_at`) VALUES
(1, 'India', 'IN', '2025-10-11 08:03:58'),
(2, 'United States', 'US', '2025-10-11 08:03:58'),
(3, 'United Kingdom', 'UK', '2025-10-11 08:03:58'),
(4, 'Canada', 'CA', '2025-10-11 08:03:58'),
(5, 'Australia', 'AU', '2025-10-11 08:03:58');

-- --------------------------------------------------------

--
-- Table structure for table `diary_entries`
--

CREATE TABLE `diary_entries` (
  `entry_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `mood` varchar(50) DEFAULT NULL,
  `tags` text DEFAULT NULL,
  `is_favorite` tinyint(1) DEFAULT 0,
  `is_archived` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `diary_entries`
--

INSERT INTO `diary_entries` (`entry_id`, `user_id`, `title`, `content`, `mood`, `tags`, `is_favorite`, `is_archived`, `created_at`, `updated_at`) VALUES
(1, 8, 'An usual day', 'Today started like most others: the hum of the ceiling fan, the soft light creeping through the curtains, and the quiet promise of a new day. I made my usual cup of chai—strong, with just the right amount of ginger—and sat by the window watching the neighborhood slowly come alive. A dog barked in the distance, someone honked twice (impatiently, as always), and the vegetable vendor’s bell rang out like clockwork.\r\n\r\nWork was steady. I spent a good chunk of time tweaking some PHP logic for a client’s site—nothing groundbreaking, but satisfying in its own way. There’s something oddly comforting about solving small bugs. Like untangling a necklace chain: frustrating at first, but rewarding once it clicks.\r\n\r\nI took a break mid-afternoon to stretch and sketch out a few color ideas for the stationery site. I’m leaning toward a muted coral paired with soft gray—it feels warm and trustworthy, like a handwritten note from someone who cares.\r\n\r\nThe evening was quiet. I didn’t do much, just scrolled through some design forums and bookmarked a few clever UI tricks. I’m always amazed at how generous people are with their ideas. It’s like a silent community of creators, each adding a brushstroke to the bigger picture.\r\n\r\nAnyway, nothing dramatic happened today. But maybe that’s the beauty of it. Not every day needs fireworks. Sometimes, it’s enough to feel the rhythm of your own routine and know you’re building something—bit by bit.', 'Calm', '#work', 0, 0, '2025-10-12 07:05:17', '2025-10-12 07:05:17'),
(2, 8, 'An usual day', 'Today started like most others: the hum of the ceiling fan, the soft light creeping through the curtains, and the quiet promise of a new day. I made my usual cup of chai—strong, with just the right amount of ginger—and sat by the window watching the neighborhood slowly come alive. A dog barked in the distance, someone honked twice (impatiently, as always), and the vegetable vendor’s bell rang out like clockwork.\r\n\r\nWork was steady. I spent a good chunk of time tweaking some PHP logic for a client’s site—nothing groundbreaking, but satisfying in its own way. There’s something oddly comforting about solving small bugs. Like untangling a necklace chain: frustrating at first, but rewarding once it clicks.\r\n\r\nI took a break mid-afternoon to stretch and sketch out a few color ideas for the stationery site. I’m leaning toward a muted coral paired with soft gray—it feels warm and trustworthy, like a handwritten note from someone who cares.\r\n\r\nThe evening was quiet. I didn’t do much, just scrolled through some design forums and bookmarked a few clever UI tricks. I’m always amazed at how generous people are with their ideas. It’s like a silent community of creators, each adding a brushstroke to the bigger picture.\r\n\r\nAnyway, nothing dramatic happened today. But maybe that’s the beauty of it. Not every day needs fireworks. Sometimes, it’s enough to feel the rhythm of your own routine and know you’re building something—bit by bit.', 'Calm', '#work', 0, 0, '2025-10-12 07:05:25', '2025-10-12 07:05:25'),
(3, 2, 'A Beautiful Day.', '## 🌞 A Beautiful Day: A Gentle Reminder to Breathe\r\n\r\nThere are days that arrive quietly, without fanfare — no grand announcements, no dramatic skies. Just a soft sunrise, a whisper of breeze, and the promise of something good. Today was one of those days.\r\n\r\nI woke up to the sound of birds, not alarms. The sun filtered through the curtains like golden lace, casting patterns on the floor that felt like nature’s own design. There was no rush, no urgency. Just time — time to stretch, sip coffee slowly, and watch the world wake up.\r\n\r\nOutside, the air was crisp but kind. Trees swayed gently, their leaves catching light like tiny mirrors. A child laughed in the distance. A dog barked, not in warning, but in joy. Even the traffic seemed to hum in harmony.\r\n\r\nI walked without destination. Just me, the sidewalk, and the rhythm of my thoughts. I noticed things I usually miss — the way ivy climbs brick walls, how puddles reflect the sky, how strangers smile when you smile first.\r\n\r\nBack home, I opened the windows wide. Let the day in. Let the light touch everything — the books, the plants, the quiet corners. I played music that felt like sunshine. I wrote a little. I read a little. I let myself be.\r\n\r\nAnd that, I think, is the magic of a beautiful day. It doesn’t demand anything. It simply offers itself — a canvas of calm, a palette of peace. It reminds us that joy isn’t always loud. Sometimes, it’s just the absence of hurry. The presence of grace\r\n\r\nSo here’s to the beautiful days. May we notice them more often. May we create them when we can. And may we never forget that sometimes, the most extraordinary moments are the simplest ones. Thank you!', 'Excited', 'beautiful', 0, 0, '2025-10-19 07:28:58', '2025-10-19 08:08:52'),
(4, 2, 'The day', 'I was super and fabulous in today\'s performance', 'Happy', '', 0, 0, '2025-10-19 12:45:44', '2025-10-19 12:45:44'),
(5, 2, 'Happy Days', 'My happy days was in college days where I enjoyed and learned so many positive things', 'Happy', '#happy', 0, 0, '2025-10-19 12:47:27', '2025-10-19 12:47:27');

-- --------------------------------------------------------

--
-- Table structure for table `login`
--

CREATE TABLE `login` (
  `login_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `login_email` varchar(150) NOT NULL,
  `login_password` varchar(255) NOT NULL,
  `login_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login`
--

INSERT INTO `login` (`login_id`, `user_id`, `login_email`, `login_password`, `login_time`) VALUES
(3, 2, 'justinsaji2027@mca.ajce.in', '$2y$10$xp2/3BqHWn6AlaKKYNgL6uJWXyRB9f/TVb0itecD3oS/1gplFpVSS', '2025-10-11 07:06:14'),
(4, 2, 'justinsaji2027@mca.ajce.in', '$2y$10$aVOFWIA5ZWlm0UkBvfQrkuSN8nnetm2U/Tf6BOef67wTocui1QvEy', '2025-10-11 08:00:57');

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `attempt_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `email` varchar(150) NOT NULL,
  `attempt_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('success','failed') NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `moods`
--

CREATE TABLE `moods` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `mood` varchar(50) NOT NULL,
  `mood_date` date NOT NULL,
  `note` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `otp` varchar(6) NOT NULL,
  `expiry` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `amount` decimal(10,2) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Completed',
  `transaction_id` varchar(255) DEFAULT NULL,
  `plan_type` enum('premium','lifetime') DEFAULT NULL,
  `currency` varchar(3) DEFAULT 'INR'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `user_id`, `created_at`, `amount`, `payment_method`, `status`, `transaction_id`, `plan_type`, `currency`) VALUES
(13, 5, '2025-10-11 09:49:16', 500.00, 'upi', 'completed', 'pay_RS7cpF0StjciXa', 'premium', 'INR'),
(14, 5, '2025-10-11 09:52:29', 2000.00, 'upi', 'completed', 'pay_RS7gEF6H4ZTMKL', 'lifetime', 'INR'),
(15, 5, '2025-10-11 09:53:23', 500.00, 'upi', 'completed', 'pay_RS7hB7LvHB65Vp', 'premium', 'INR'),
(16, 5, '2025-10-11 09:54:14', 2000.00, 'upi', 'completed', 'pay_RS7i4pM6FUIFzT', 'lifetime', 'INR'),
(17, 5, '2025-10-11 09:57:01', 500.00, 'upi', 'completed', 'pay_RS7l17gudCNx6N', 'premium', 'INR'),
(18, 5, '2025-10-11 10:09:27', 2000.00, 'upi', 'completed', 'pay_RS7y9VHrCnPs2o', 'lifetime', 'INR'),
(19, 5, '2025-10-11 10:16:15', 500.00, 'upi', 'completed', 'pay_RS85LDDkWFSSgK', 'premium', 'INR'),
(20, 5, '2025-10-11 10:18:54', 2000.00, 'upi', 'completed', 'pay_RS887lk8Yrkgpy', 'lifetime', 'INR'),
(21, 5, '2025-10-11 10:19:44', 500.00, 'upi', 'completed', 'pay_RS890nqjJcs3c6', 'premium', 'INR'),
(22, 5, '2025-10-11 10:22:51', 2000.00, 'upi', 'completed', 'pay_RS8CJc5D7NMOJh', 'lifetime', 'INR'),
(23, 5, '2025-10-11 10:37:56', 500.00, 'upi', 'completed', 'pay_RS8SEy4FJRiVA5', 'premium', 'INR'),
(24, 5, '2025-10-11 13:35:12', 2000.00, 'upi', 'completed', 'pay_RSBTV5bunr9AHF', 'lifetime', 'INR'),
(25, 8, '2025-10-12 08:56:57', 500.00, 'upi', 'completed', 'pay_RSVGbuwqDQ5Tma', 'premium', 'INR'),
(26, 8, '2025-10-12 09:03:18', 500.00, 'upi', 'completed', 'pay_RSVNPIGLFKbBbG', 'premium', 'INR'),
(27, 8, '2025-10-12 09:15:11', 2000.00, 'upi', 'completed', 'pay_RSVZxM1NhoadqK', 'lifetime', 'INR'),
(28, 8, '2025-10-12 09:15:41', 500.00, 'upi', 'completed', 'pay_RSVaTMvyTtWnXK', 'premium', 'INR'),
(29, 7, '2025-10-12 09:16:53', 500.00, 'upi', 'completed', 'pay_RSVbkPfWEtayha', 'premium', 'INR'),
(30, 2, '2025-10-12 09:24:01', 500.00, 'upi', 'completed', 'pay_RSVjI3sad0kt0P', 'premium', 'INR'),
(31, 2, '2025-10-12 10:45:58', 2000.00, 'upi', 'completed', 'pay_RSX7r2NifMQXP6', 'lifetime', 'INR'),
(32, 2, '2025-10-19 08:30:32', 500.00, 'upi', 'completed', 'pay_RVGYdNmXZQ4e57', 'premium', 'INR'),
(33, 2, '2025-10-19 08:31:36', 500.00, 'upi', 'completed', 'pay_RVGZkg1kkhqRjA', 'premium', 'INR'),
(34, 2, '2025-10-19 09:06:37', 500.00, 'upi', 'completed', 'pay_RVHAjf0lq1mhO3', 'premium', 'INR'),
(35, 2, '2025-10-19 09:12:48', 500.00, 'upi', 'completed', 'pay_RVHHH7Wv4bTf5N', 'premium', 'INR'),
(36, 2, '2025-10-19 09:26:04', 500.00, 'upi', 'completed', 'pay_RVHVHLzPN3FdQ2', 'premium', 'INR'),
(37, 2, '2025-10-19 09:34:15', 500.00, 'upi', 'completed', 'pay_RVHdvkFzTxvzZ5', 'premium', 'INR'),
(38, 2, '2025-10-19 09:34:50', 500.00, 'upi', 'completed', 'pay_RVHeY4PJUTjG6m', 'premium', 'INR'),
(39, 2, '2025-10-19 09:35:49', 500.00, 'upi', 'completed', 'pay_RVHfGuQ8KwKaYw', 'premium', 'INR'),
(40, 2, '2025-10-19 09:37:43', 500.00, 'upi', 'completed', 'pay_RVHhZxcjk0WdBY', 'premium', 'INR'),
(41, 2, '2025-10-19 09:45:58', 500.00, 'upi', 'completed', 'pay_RVHqIoXMHYc51G', 'premium', 'INR'),
(42, 2, '2025-10-19 09:47:40', 500.00, 'upi', 'completed', 'pay_RVHs67qxWKLwJQ', 'premium', 'INR'),
(43, 2, '2025-10-19 09:52:37', 500.00, 'upi', 'completed', 'pay_RVHxKlYkQpRoJe', 'premium', 'INR'),
(44, 2, '2025-10-19 09:53:31', 500.00, 'upi', 'completed', 'pay_RVHyHLecvLxVJw', 'premium', 'INR'),
(45, 2, '2025-10-19 10:00:01', 500.00, 'upi', 'completed', 'pay_RVI59nlNhpBhL6', 'premium', 'INR'),
(46, 2, '2025-10-19 10:02:14', 500.00, 'upi', 'completed', 'pay_RVI7Tf3Fgfja1S', 'premium', 'INR'),
(47, 2, '2025-10-19 10:08:10', 500.00, 'upi', 'completed', 'pay_RVIDkzTGsNcIaR', 'premium', 'INR'),
(48, 2, '2025-10-19 10:12:08', 2000.00, 'upi', 'completed', 'pay_RVIHx95YMtMfcx', 'lifetime', 'INR'),
(49, 2, '2025-10-19 10:45:17', 500.00, 'upi', 'completed', 'pay_RVIqyEZnEQi69F', 'premium', 'INR'),
(50, 2, '2025-10-19 10:51:57', 500.00, 'upi', 'completed', 'pay_RVIy10BQGvp3zc', 'premium', 'INR'),
(51, 7, '2025-10-19 10:55:57', 500.00, 'upi', 'completed', 'pay_RVJ2EzqDpdVaK7', 'premium', 'INR'),
(52, 2, '2025-10-19 11:02:40', 500.00, 'upi', 'completed', 'pay_RVJ9LmPSctemKR', 'premium', 'INR'),
(53, 2, '2025-10-19 11:03:49', 2000.00, 'upi', 'completed', 'pay_RVJAZ2WQY7nRfV', 'lifetime', 'INR'),
(54, 2, '2025-10-19 15:21:29', 500.00, 'upi', 'completed', 'pay_RVNYWJTsZ9tlat', 'premium', 'INR');

-- --------------------------------------------------------

--
-- Table structure for table `reminders`
--

CREATE TABLE `reminders` (
  `reminder_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `time` time NOT NULL,
  `days` varchar(100) NOT NULL,
  `enabled` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `email_notifications` tinyint(1) DEFAULT 1,
  `dark_mode` tinyint(1) DEFAULT 0,
  `language` varchar(50) DEFAULT 'English',
  `notification_email` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `user_id`, `email_notifications`, `dark_mode`, `language`, `notification_email`, `created_at`, `updated_at`) VALUES
(4, 2, 0, 0, 'es', NULL, '2025-10-11 17:15:02', '2025-10-11 17:15:06'),
(5, 7, 1, 1, 'en', 'justinsaji2027@mca.ajce.in', '2025-10-16 18:33:17', '2025-10-16 18:52:46');

-- --------------------------------------------------------

--
-- Table structure for table `signup`
--

CREATE TABLE `signup` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `signup_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `birthdate` date DEFAULT NULL,
  `subscription_plan` enum('basic','premium','lifetime') DEFAULT 'basic',
  `subscription_status` enum('active','expired','cancelled') DEFAULT 'active',
  `subscription_start_date` datetime DEFAULT NULL,
  `subscription_end_date` datetime DEFAULT NULL,
  `theme` varchar(50) DEFAULT 'default',
  `google_signup` tinyint(1) DEFAULT 0,
  `google_id` varchar(100) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `hobbies` text DEFAULT NULL,
  `favorite_music` text DEFAULT NULL,
  `favorite_films` text DEFAULT NULL,
  `favorite_books` text DEFAULT NULL,
  `favorite_places` text DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `language` varchar(10) DEFAULT 'en',
  `profile_public` tinyint(1) DEFAULT 0,
  `email_notifications` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `signup`
--

INSERT INTO `signup` (`user_id`, `full_name`, `email`, `password`, `signup_date`, `birthdate`, `subscription_plan`, `subscription_status`, `subscription_start_date`, `subscription_end_date`, `theme`, `google_signup`, `google_id`, `profile_picture`, `bio`, `phone`, `address`, `city`, `state`, `country`, `postal_code`, `hobbies`, `favorite_music`, `favorite_films`, `favorite_books`, `favorite_places`, `gender`, `language`, `profile_public`, `email_notifications`) VALUES
(2, 'JUSTIN SAJI INT MCA 2022-2027', 'justinsaji2027@mca.ajce.in', '$2y$10$dRQH7ih3HHlpDBJyHXV9qu7TzvaVlkHnriv6kheHq37rr2c3CLUv6', '2025-10-11 07:05:45', '1984-05-10', 'lifetime', 'active', '2025-10-12 12:45:58', NULL, 'default', 0, NULL, 'user_2_1760873998.jpg', 'Strong Believer', '7306978298', 'Mukalathu House , Madanthamon', NULL, 'Mizoram', 'India', '689711', 'Reading, Listening to Music', 'Kesariya,Pavizha Mazha', 'Kabhi Kushi Khabi Ham', 'The Alchemist', 'Shimla', NULL, 'en', 0, 0),
(4, 'Test User', 'test@example.com', '$2y$10$PAq8hNlAdvu444pJY5rOr.3a.TQWxpfZ9E9VeNNWKMaytKyYHIvXe', '2025-10-11 14:42:19', '1990-01-01', 'basic', 'active', NULL, NULL, 'default', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'en', 0, 0),
(5, 'Elizabeth Saji', 'elizabeth97@gmail.com', '$2y$10$IApDnpGHPsyN9s5bHyb3Wu6rB5WdZ6Dytwfpg9eV8cTtpVdNy47ye', '2025-10-11 14:42:19', '1997-07-13', 'lifetime', 'active', '2025-10-11 15:35:12', NULL, 'default', 0, NULL, 'user_5_1760177572.jpg', 'xxcfcgjfcfccjvchvcfcgjckjgv', '7306978298', 'Mukalathu', 'Kochi', 'Kerala', 'India', '689711', 'Reading, Listening to Music', 'Kesariya,Pavizha Mazha', 'Kabhi Kushi Khabi Ham', 'The Alchemist', 'Shimla', NULL, 'en', 0, 0),
(6, 'Admin User', 'admin@example.com', '$2y$10$KK/Yf0y6MSIaKxf6uIFIh.4aSdup/9wmUNJ8gsvJ7D8UjR23kg/TO', '2025-10-11 14:52:42', '2000-01-01', 'lifetime', 'active', NULL, NULL, 'default', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'en', 0, 0),
(7, 'JUSTIN SAJI', 'justinsaji2412@gmail.com', '$2y$10$iFQ3VwjGtkc1KMnU4nvuQuxoZYzGtg2cFTZFZ7WvQhQNJE.xyxAQm', '2025-10-11 17:51:08', '2003-12-24', 'premium', 'active', '2025-10-12 11:16:53', '2025-11-12 11:16:53', 'default', 0, NULL, 'user_7_1760637730.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'en', 0, 0),
(8, 'MS Dhoni', 'msdhoni24@gmail.com', '$2y$10$h1X3rzaM7JMhd8MivhyGk.zDBsYq1qK58x89uyBpc72MeAgVdMhKe', '2025-10-12 06:56:52', '2003-12-26', 'premium', 'active', '2025-10-12 11:15:41', '2025-11-12 11:15:41', 'default', 0, NULL, 'user_8_1760252437.jpg', 'prpbfjpb2rqpbvpjq2b2rfjwrnjfvbqwjnvnlqbrljnvqljn', '7306978298', 'Mukalathu', 'Visakhapatnam', 'Andhra Pradesh', 'India', '689711', 'Reading, Listening to Music', 'Kesariya,Pavizha Mazha', 'Kabhi Kushi Khabi Ham', 'The Alchemist', 'Shimla', NULL, 'en', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `states`
--

CREATE TABLE `states` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `country_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `states`
--

INSERT INTO `states` (`id`, `name`, `country_id`, `created_at`) VALUES
(1, 'Andhra Pradesh', 1, '2025-10-11 08:03:58'),
(2, 'Arunachal Pradesh', 1, '2025-10-11 08:03:58'),
(3, 'Assam', 1, '2025-10-11 08:03:58'),
(4, 'Bihar', 1, '2025-10-11 08:03:58'),
(5, 'Chhattisgarh', 1, '2025-10-11 08:03:58'),
(6, 'Goa', 1, '2025-10-11 08:03:58'),
(7, 'Gujarat', 1, '2025-10-11 08:03:58'),
(8, 'Haryana', 1, '2025-10-11 08:03:58'),
(9, 'Himachal Pradesh', 1, '2025-10-11 08:03:58'),
(10, 'Jharkhand', 1, '2025-10-11 08:03:58'),
(11, 'Karnataka', 1, '2025-10-11 08:03:58'),
(12, 'Kerala', 1, '2025-10-11 08:03:58'),
(13, 'Madhya Pradesh', 1, '2025-10-11 08:03:58'),
(14, 'Maharashtra', 1, '2025-10-11 08:03:58'),
(15, 'Manipur', 1, '2025-10-11 08:03:58'),
(16, 'Meghalaya', 1, '2025-10-11 08:03:58'),
(17, 'Mizoram', 1, '2025-10-11 08:03:58'),
(18, 'Nagaland', 1, '2025-10-11 08:03:58'),
(19, 'Odisha', 1, '2025-10-11 08:03:58'),
(20, 'Punjab', 1, '2025-10-11 08:03:58'),
(21, 'Rajasthan', 1, '2025-10-11 08:03:58'),
(22, 'Sikkim', 1, '2025-10-11 08:03:58'),
(23, 'Tamil Nadu', 1, '2025-10-11 08:03:58'),
(24, 'Telangana', 1, '2025-10-11 08:03:58'),
(25, 'Tripura', 1, '2025-10-11 08:03:58'),
(26, 'Uttar Pradesh', 1, '2025-10-11 08:03:58'),
(27, 'Uttarakhand', 1, '2025-10-11 08:03:58'),
(28, 'West Bengal', 1, '2025-10-11 08:03:58'),
(29, 'Delhi', 1, '2025-10-11 08:03:58'),
(30, 'Jammu and Kashmir', 1, '2025-10-11 08:03:58'),
(31, 'Alabama', 2, '2025-10-11 08:03:58'),
(32, 'Alaska', 2, '2025-10-11 08:03:58'),
(33, 'Arizona', 2, '2025-10-11 08:03:58'),
(34, 'Arkansas', 2, '2025-10-11 08:03:58'),
(35, 'California', 2, '2025-10-11 08:03:58'),
(36, 'Colorado', 2, '2025-10-11 08:03:58'),
(37, 'Connecticut', 2, '2025-10-11 08:03:58'),
(38, 'Delaware', 2, '2025-10-11 08:03:58'),
(39, 'Florida', 2, '2025-10-11 08:03:58'),
(40, 'Georgia', 2, '2025-10-11 08:03:58'),
(41, 'Hawaii', 2, '2025-10-11 08:03:58'),
(42, 'Idaho', 2, '2025-10-11 08:03:58'),
(43, 'Illinois', 2, '2025-10-11 08:03:58'),
(44, 'Indiana', 2, '2025-10-11 08:03:58'),
(45, 'Iowa', 2, '2025-10-11 08:03:58'),
(46, 'Kansas', 2, '2025-10-11 08:03:58'),
(47, 'Kentucky', 2, '2025-10-11 08:03:58'),
(48, 'Louisiana', 2, '2025-10-11 08:03:58'),
(49, 'Maine', 2, '2025-10-11 08:03:58'),
(50, 'Maryland', 2, '2025-10-11 08:03:58'),
(51, 'Massachusetts', 2, '2025-10-11 08:03:58'),
(52, 'Michigan', 2, '2025-10-11 08:03:58'),
(53, 'Minnesota', 2, '2025-10-11 08:03:58'),
(54, 'Mississippi', 2, '2025-10-11 08:03:58'),
(55, 'Missouri', 2, '2025-10-11 08:03:58'),
(56, 'Montana', 2, '2025-10-11 08:03:58'),
(57, 'Nebraska', 2, '2025-10-11 08:03:58'),
(58, 'Nevada', 2, '2025-10-11 08:03:58'),
(59, 'New Hampshire', 2, '2025-10-11 08:03:58'),
(60, 'New Jersey', 2, '2025-10-11 08:03:58'),
(61, 'New Mexico', 2, '2025-10-11 08:03:58'),
(62, 'New York', 2, '2025-10-11 08:03:58'),
(63, 'North Carolina', 2, '2025-10-11 08:03:58'),
(64, 'North Dakota', 2, '2025-10-11 08:03:58'),
(65, 'Ohio', 2, '2025-10-11 08:03:58'),
(66, 'Oklahoma', 2, '2025-10-11 08:03:58'),
(67, 'Oregon', 2, '2025-10-11 08:03:58'),
(68, 'Pennsylvania', 2, '2025-10-11 08:03:58'),
(69, 'Rhode Island', 2, '2025-10-11 08:03:58'),
(70, 'South Carolina', 2, '2025-10-11 08:03:58'),
(71, 'South Dakota', 2, '2025-10-11 08:03:58'),
(72, 'Tennessee', 2, '2025-10-11 08:03:58'),
(73, 'Texas', 2, '2025-10-11 08:03:58'),
(74, 'Utah', 2, '2025-10-11 08:03:58'),
(75, 'Vermont', 2, '2025-10-11 08:03:58'),
(76, 'Virginia', 2, '2025-10-11 08:03:58'),
(77, 'Washington', 2, '2025-10-11 08:03:58'),
(78, 'West Virginia', 2, '2025-10-11 08:03:58'),
(79, 'Wisconsin', 2, '2025-10-11 08:03:58'),
(80, 'Wyoming', 2, '2025-10-11 08:03:58');

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `testimonial_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `testimonials`
--

INSERT INTO `testimonials` (`testimonial_id`, `user_id`, `content`, `created_at`) VALUES
(1, 2, 'I like to thank you for this website', '2025-10-19 12:43:00'),
(2, 2, 'Good website', '2025-10-19 15:23:59');

-- --------------------------------------------------------

--
-- Table structure for table `themes`
--

CREATE TABLE `themes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `theme_name` varchar(50) DEFAULT 'default',
  `font_choice` varchar(50) DEFAULT 'Inter',
  `background_color` varchar(7) DEFAULT '#ffffff',
  `text_color` varchar(7) DEFAULT '#1e293b',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `themes`
--

INSERT INTO `themes` (`id`, `user_id`, `theme_name`, `font_choice`, `background_color`, `text_color`, `created_at`, `updated_at`) VALUES
(1, 6, 'default', 'Inter', '#ffffff', '#1e293b', '2025-10-11 17:01:42', '2025-10-11 17:01:42'),
(2, 5, 'default', 'Inter', '#ffffff', '#1e293b', '2025-10-11 17:01:42', '2025-10-11 17:01:42'),
(3, 2, 'default', 'Inter', '#ffffff', '#1e293b', '2025-10-11 17:01:42', '2025-10-11 17:01:42'),
(5, 4, 'default', 'Inter', '#ffffff', '#1e293b', '2025-10-11 17:01:42', '2025-10-11 17:01:42');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `birthdate` date NOT NULL,
  `subscription_plan` enum('basic','premium','lifetime') DEFAULT 'basic',
  `subscription_status` enum('active','expired','cancelled') DEFAULT 'active',
  `subscription_start_date` datetime DEFAULT NULL,
  `subscription_end_date` datetime DEFAULT NULL,
  `theme` varchar(50) DEFAULT 'default',
  `google_signup` tinyint(1) DEFAULT 0,
  `google_id` varchar(100) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `bio` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `hobbies` text DEFAULT NULL,
  `favorite_music` text DEFAULT NULL,
  `favorite_films` text DEFAULT NULL,
  `favorite_books` text DEFAULT NULL,
  `favorite_places` text DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `language` varchar(10) DEFAULT 'en',
  `profile_public` tinyint(1) DEFAULT 0,
  `email_notifications` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `view_entries_log`
--

CREATE TABLE `view_entries_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `entry_id` int(11) NOT NULL,
  `viewed_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `write_entries`
--

CREATE TABLE `write_entries` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `mood` varchar(50) DEFAULT NULL,
  `written_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `backup_history`
--
ALTER TABLE `backup_history`
  ADD PRIMARY KEY (`backup_id`),
  ADD KEY `backup_history_ibfk_1` (`user_id`);

--
-- Indexes for table `cities`
--
ALTER TABLE `cities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `state_id` (`state_id`);

--
-- Indexes for table `countries`
--
ALTER TABLE `countries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `diary_entries`
--
ALTER TABLE `diary_entries`
  ADD PRIMARY KEY (`entry_id`),
  ADD KEY `diary_entries_ibfk_1` (`user_id`);

--
-- Indexes for table `login`
--
ALTER TABLE `login`
  ADD PRIMARY KEY (`login_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`attempt_id`),
  ADD KEY `login_attempts_ibfk_1` (`user_id`);

--
-- Indexes for table `moods`
--
ALTER TABLE `moods`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_email` (`email`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `payments_ibfk_1` (`user_id`);

--
-- Indexes for table `reminders`
--
ALTER TABLE `reminders`
  ADD PRIMARY KEY (`reminder_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `settings_ibfk_1` (`user_id`);

--
-- Indexes for table `signup`
--
ALTER TABLE `signup`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `states`
--
ALTER TABLE `states`
  ADD PRIMARY KEY (`id`),
  ADD KEY `country_id` (`country_id`);

--
-- Indexes for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`testimonial_id`),
  ADD KEY `testimonials_ibfk_1` (`user_id`);

--
-- Indexes for table `themes`
--
ALTER TABLE `themes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `view_entries_log`
--
ALTER TABLE `view_entries_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `entry_id` (`entry_id`);

--
-- Indexes for table `write_entries`
--
ALTER TABLE `write_entries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `backup_history`
--
ALTER TABLE `backup_history`
  MODIFY `backup_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cities`
--
ALTER TABLE `cities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=276;

--
-- AUTO_INCREMENT for table `countries`
--
ALTER TABLE `countries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `diary_entries`
--
ALTER TABLE `diary_entries`
  MODIFY `entry_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `login`
--
ALTER TABLE `login`
  MODIFY `login_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `attempt_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `moods`
--
ALTER TABLE `moods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `reminders`
--
ALTER TABLE `reminders`
  MODIFY `reminder_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `signup`
--
ALTER TABLE `signup`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `states`
--
ALTER TABLE `states`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT for table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `testimonial_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `themes`
--
ALTER TABLE `themes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `view_entries_log`
--
ALTER TABLE `view_entries_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `write_entries`
--
ALTER TABLE `write_entries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `backup_history`
--
ALTER TABLE `backup_history`
  ADD CONSTRAINT `backup_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `signup` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `cities`
--
ALTER TABLE `cities`
  ADD CONSTRAINT `cities_ibfk_1` FOREIGN KEY (`state_id`) REFERENCES `states` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `diary_entries`
--
ALTER TABLE `diary_entries`
  ADD CONSTRAINT `diary_entries_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `signup` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `login`
--
ALTER TABLE `login`
  ADD CONSTRAINT `login_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `signup` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD CONSTRAINT `login_attempts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `signup` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `moods`
--
ALTER TABLE `moods`
  ADD CONSTRAINT `moods_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `signup` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `signup` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `reminders`
--
ALTER TABLE `reminders`
  ADD CONSTRAINT `reminders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `signup` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `settings`
--
ALTER TABLE `settings`
  ADD CONSTRAINT `settings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `signup` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `states`
--
ALTER TABLE `states`
  ADD CONSTRAINT `states_ibfk_1` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD CONSTRAINT `testimonials_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `signup` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `themes`
--
ALTER TABLE `themes`
  ADD CONSTRAINT `themes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `signup` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `view_entries_log`
--
ALTER TABLE `view_entries_log`
  ADD CONSTRAINT `view_entries_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `signup` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `view_entries_log_ibfk_2` FOREIGN KEY (`entry_id`) REFERENCES `write_entries` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `write_entries`
--
ALTER TABLE `write_entries`
  ADD CONSTRAINT `write_entries_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `signup` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
