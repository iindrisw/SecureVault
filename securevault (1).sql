-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 04, 2026 at 11:08 AM
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
-- Database: `securevault`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` enum('register','login','logout','upload','download','share','revoke_share','delete','view','preview') NOT NULL,
  `file_id` int(11) DEFAULT NULL,
  `target_user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `file_id`, `target_user_id`, `ip_address`, `user_agent`, `details`, `created_at`) VALUES
(1, 1, 'register', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'New user registered: indri', '2026-05-28 00:39:04'),
(2, 1, 'login', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 00:39:18'),
(3, 1, 'upload', 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'Uploaded: Kelompok 6 - How Does Digital Transformation Improve Organizational Resilience.pdf (1472545 bytes)', '2026-05-28 01:01:33'),
(4, 1, 'download', 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'Downloaded: Kelompok 6 - How Does Digital Transformation Improve Organizational Resilience.pdf', '2026-05-28 01:01:47'),
(5, 1, 'download', 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'Downloaded: Kelompok 6 - How Does Digital Transformation Improve Organizational Resilience.pdf', '2026-05-28 01:02:41'),
(6, 1, 'logout', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 01:04:40'),
(7, 2, 'register', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'New user registered: hilal', '2026-05-28 01:05:14'),
(8, 1, 'login', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 01:05:26'),
(9, 1, 'download', 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'Downloaded: Kelompok 6 - How Does Digital Transformation Improve Organizational Resilience.pdf', '2026-05-28 01:05:44'),
(10, 1, 'share', 1, 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 01:05:44'),
(11, 1, 'logout', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 01:05:55'),
(12, 2, 'login', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 01:06:06'),
(13, 2, 'download', 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'Downloaded: Kelompok 6 - How Does Digital Transformation Improve Organizational Resilience.pdf', '2026-05-28 01:06:19'),
(14, 1, 'login', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 07:43:00'),
(15, 3, 'register', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'New user registered: adam', '2026-05-29 11:20:46'),
(16, 3, 'login', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 11:21:02'),
(17, 3, 'upload', 2, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'Uploaded: Adam Fadli - 2488010028 - UAS Pemrograman Web.pdf (125267 bytes)', '2026-05-29 11:33:01'),
(18, 3, 'login', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-30 02:58:03'),
(19, 3, 'logout', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-30 03:13:15'),
(20, 1, 'login', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-30 03:14:23'),
(21, 1, 'download', 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'Downloaded: Kelompok 6 - How Does Digital Transformation Improve Organizational Resilience.pdf', '2026-05-30 03:15:04'),
(22, 1, 'share', 1, 3, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-30 03:15:04'),
(23, 3, 'login', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-31 11:32:36'),
(24, 3, 'download', 2, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'Downloaded: Adam Fadli - 2488010028 - UAS Pemrograman Web.pdf', '2026-05-31 11:33:25'),
(25, 3, 'login', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-31 14:29:27'),
(26, 1, 'login', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-06-03 15:54:44'),
(27, 1, 'logout', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-06-03 16:28:15'),
(29, 5, 'register', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0', 'New user registered: edward', '2026-06-03 16:31:01'),
(30, 5, 'login', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0', NULL, '2026-06-03 16:32:01'),
(31, 5, 'upload', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0', 'Uploaded: Laporan Tugas Kelompok_IMK_Prototype RINAI.pdf (639545 bytes)', '2026-06-03 16:41:28'),
(32, 5, 'download', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0', 'Downloaded: Laporan Tugas Kelompok_IMK_Prototype RINAI.pdf', '2026-06-03 16:42:52'),
(33, 5, 'download', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0', 'Downloaded: Laporan Tugas Kelompok_IMK_Prototype RINAI.pdf', '2026-06-03 16:48:14'),
(34, 5, 'download', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0', 'Downloaded: Laporan Tugas Kelompok_IMK_Prototype RINAI.pdf', '2026-06-03 16:48:48'),
(35, 5, 'share', NULL, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0', NULL, '2026-06-03 16:48:48'),
(36, 5, 'logout', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0', NULL, '2026-06-03 16:49:18'),
(37, 1, 'login', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0', NULL, '2026-06-03 16:49:34'),
(38, 1, 'download', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0', 'Downloaded: Laporan Tugas Kelompok_IMK_Prototype RINAI.pdf', '2026-06-03 16:50:20'),
(39, 5, 'login', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-06-03 21:50:47'),
(40, 5, 'revoke_share', NULL, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-06-03 21:51:39'),
(41, 5, 'logout', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-06-03 21:52:03'),
(42, 1, 'login', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-06-03 21:52:33'),
(43, 1, 'logout', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-06-03 21:54:21'),
(44, 5, 'login', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-06-03 21:54:31'),
(45, 5, 'delete', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'Deleted: Laporan Tugas Kelompok_IMK_Prototype RINAI.pdf', '2026-06-03 21:55:08'),
(46, 5, 'logout', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-06-03 21:58:13'),
(47, 1, 'login', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-06-03 22:02:02'),
(48, 1, 'upload', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'Uploaded: kr1.drawio.png (39909 bytes)', '2026-06-03 22:08:10'),
(49, 1, 'delete', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'Deleted: kr1.drawio.png', '2026-06-03 22:12:15'),
(50, 1, 'upload', 5, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'Uploaded: BARU.drawio (13981 bytes)', '2026-06-03 22:12:35'),
(51, 1, 'download', 5, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'Downloaded: BARU.drawio', '2026-06-03 22:13:43'),
(52, 1, 'download', 5, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'Downloaded: BARU.drawio', '2026-06-03 22:14:23'),
(53, 1, 'logout', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-06-03 22:18:25'),
(54, 1, 'login', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-06-04 00:49:30'),
(55, 1, 'delete', 5, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'Deleted: BARU.drawio', '2026-06-04 00:49:46'),
(56, 1, 'upload', 6, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'Uploaded: BARU (1).drawio (13981 bytes)', '2026-06-04 01:03:07'),
(57, 1, 'download', 6, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'Downloaded: BARU (1).drawio', '2026-06-04 01:03:23'),
(58, 1, 'share', 6, 5, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-06-04 01:03:23'),
(59, 1, 'delete', 6, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'Deleted: BARU (1).drawio', '2026-06-04 01:03:45'),
(60, 1, 'logout', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-06-04 01:03:59'),
(61, 5, 'login', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-06-04 01:04:16'),
(62, 1, 'login', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-06-04 02:34:44'),
(63, 1, 'logout', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-06-04 02:56:51'),
(64, 3, 'login', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-06-04 02:57:15'),
(65, 3, 'logout', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-06-04 02:58:03'),
(66, 6, 'register', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'New user registered: user', '2026-06-04 02:58:46'),
(67, 6, 'login', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-06-04 02:58:57'),
(68, 6, 'upload', 7, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'Uploaded: Laporan Tugas Kelompok_IMK_Prototype RINAI (1).pdf (639545 bytes)', '2026-06-04 02:59:17'),
(69, 6, 'logout', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-06-04 02:59:27'),
(70, 3, 'login', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-06-04 02:59:37'),
(71, 3, 'logout', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-06-04 02:59:51'),
(72, 6, 'login', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-06-04 03:00:02'),
(73, 6, 'logout', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-06-04 03:00:05'),
(74, 3, 'login', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-06-04 03:00:15'),
(75, 3, 'logout', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-06-04 03:00:26'),
(76, 6, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'Blocked user attempted to login.', '2026-06-04 03:00:38'),
(77, 3, 'login', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-06-04 03:00:50'),
(78, 3, 'logout', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-06-04 03:05:41'),
(79, 7, 'register', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'New user registered: ummu', '2026-06-04 03:24:52'),
(80, 7, 'login', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-06-04 03:26:03'),
(81, 7, 'upload', 8, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'Uploaded: securevault asyik.docx (1984199 bytes)', '2026-06-04 03:27:22'),
(82, 7, 'download', 8, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'Downloaded: securevault asyik.docx', '2026-06-04 03:29:13'),
(83, 7, 'download', 8, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'Downloaded: securevault asyik.docx', '2026-06-04 03:29:59'),
(84, 7, 'download', 8, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'Downloaded: securevault asyik.docx', '2026-06-04 03:30:34'),
(85, 7, 'share', 8, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-06-04 03:30:35'),
(86, 7, 'logout', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-06-04 03:30:38'),
(87, 1, 'login', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-06-04 03:30:53'),
(88, 1, 'delete', 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'Deleted: Kelompok 6 - How Does Digital Transformation Improve Organizational Resilience.pdf', '2026-06-04 03:32:50'),
(89, 1, 'logout', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-06-04 03:33:31'),
(90, 3, 'login', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-06-04 03:33:46'),
(91, 3, 'logout', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-06-04 03:34:47'),
(92, 1, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'Blocked user attempted to login.', '2026-06-04 03:34:59');

-- --------------------------------------------------------

--
-- Table structure for table `files`
--

CREATE TABLE `files` (
  `id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `filename_original` varchar(255) NOT NULL,
  `filename_stored` varchar(255) NOT NULL,
  `file_size` bigint(20) NOT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `file_hash` varchar(64) NOT NULL,
  `original_hash` varchar(64) NOT NULL,
  `session_key_enc` text NOT NULL,
  `session_key_iv` varchar(64) NOT NULL,
  `aes_tag` varchar(64) NOT NULL,
  `is_deleted` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `files`
--

INSERT INTO `files` (`id`, `owner_id`, `filename_original`, `filename_stored`, `file_size`, `mime_type`, `file_hash`, `original_hash`, `session_key_enc`, `session_key_iv`, `aes_tag`, `is_deleted`, `created_at`, `updated_at`) VALUES
(1, 1, 'Kelompok 6 - How Does Digital Transformation Improve Organizational Resilience.pdf', '98723105-8ee9-41ba-8d24-b9440cd98576.enc', 1472545, 'application/pdf', 'd262929aefdd5b34c61bd552c535892c48cf408b6d3400eb60a665cdcebab3fa', 'b26f765145b892beda664e16ea5ed8f34dfb9e5d67375b07c983bd7d15ee481a', '', '', '', 1, '2026-05-28 01:01:33', '2026-06-04 03:32:50'),
(2, 3, 'Adam Fadli - 2488010028 - UAS Pemrograman Web.pdf', 'cc43de35-71ba-4ff1-9f15-3083d8533610.enc', 125267, 'application/pdf', '081a9d5de3d412cd322bc6ba1726480d678cc3df05273c893e6a2f51e93e26fb', '881d7eb13a05536b5d17502144f1b084103be9930fc576aea968583aff9bb844', 'APmrpwagNgr7hLjmQ24tt4OzaX8lJnHp934dXN379krc1VlpBnz3aokNvY/D8Ffz6HN9oPAgzHjwGxmoTWIU36ZLnENaL1A9HKvqMGlfHS/hghD9a5lutij2eaBWtwngcCgdwOBdm8FdOiBzv8/VMqqikzekerizaE424h/eMzY+lCJWVQDUTNoJU2Jz9ioaJFWNjB8G492PrR+LFxVhCJx0CVi1FIxdGhm96DtBWpa2g4DDR1bkgCjvCwL88PbPSh6JDAdkFeujtCK3OeOg5M8YzEsp0hDpzk9jU6MsL+9tEUBSnde9R4b3YilMl9pBZ/GDdIMsDMr8sxhEkJLawQ==', 'fe6d25cc2f7811fa099dd3a8', '2c5e950921c984187e33f7a84d1920d0', 0, '2026-05-29 11:33:01', '2026-05-29 11:33:01'),
(5, 1, 'BARU.drawio', '59e5ac16-211a-43e0-b160-b7fbb8c88a35.enc', 13981, 'application/octet-stream', '3d116cdcddc5ba2f65a99b04fc422df2c6a20b0e51c00113af83bfb74f1509ac', 'a961823cc1f47d4ee72a348c181879dd120d99543436423a0a2a101d8d58d406', '', '', '', 1, '2026-06-03 22:12:35', '2026-06-04 00:49:46'),
(6, 1, 'BARU (1).drawio', 'e6802b99-c730-42fd-a53b-c81df0af3271.enc', 13981, 'application/octet-stream', '6ed3c8f203004d9a7b1c6118538b7d2b32488cc91f0e4f790228c0523f8c15e7', 'a961823cc1f47d4ee72a348c181879dd120d99543436423a0a2a101d8d58d406', '', '', '', 1, '2026-06-04 01:03:07', '2026-06-04 01:03:45'),
(7, 6, 'Laporan Tugas Kelompok_IMK_Prototype RINAI (1).pdf', 'e170f7d8-8e01-4181-a385-e919eb1952e7.enc', 639545, 'application/pdf', '3b4ba2fba4aae58696e31b62201a3be18563c6559920f93a422aa03d81152b18', 'a6b3183270524dcb87017c05b4c64eb06af9876d3b7422ea390325d8584b2587', 'YPtp/3LI19l3EXervMaBIPjHm9TWZp7IjDaCzf8xoPqszi2nF2gdHdIvaGMfMVuBJGB4hqeKUMWO+YIE9hJhn64tpGgyXVhSccjqq+5nQw0l+A0M1qBQPp91soIl0d3PD3jJkiOOkGEfc1yIVHf4BSYC9Y/8weTzQjBzQ+mcdwBvErKZoLgdLmP+I+8+comi+LAG0Z5tMYltlZdFW5GzJrumjVIBFkgHsqgqWZjctntVXoK+jK6skl4z3WD4a6xziMK2T5LgGzB8C49INiFB7KQWifnOlCQy/QR54VX38JvG4wbL1IZF77UBEefAgYMKCDsiQuSQSZBVJVgXsaSqDQ==', 'b3c8419dfb96a86d849ca842', '708aea422d7734c383b384ccfdfa43eb', 1, '2026-06-04 02:59:17', '2026-06-04 02:59:45'),
(8, 7, 'securevault asyik.docx', '95719cc0-98fd-48e4-9cc4-87576c4d0772.enc', 1984199, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'dbb7eb44e3482ceaac8d3296524625acdefd589d978ae7de2c6c1cfc5e6cb288', '1ce2a3aced87f0cd3b947c14c3fcb3116077fc79875d81ae19d960259529c4e9', 'R7ZOqPOPMrjRD/wx5O3G9VN0oYUekNgemuKC6uUA4SfO65fv9BJf5khMhAj0IbIIid4WFG9bOetlEDI3+g0yiBSUqhC5fgsw1KhJb8Bf27t/3wjMp+ueE1AG8osUhIW4OCOpocglcNY//UTxxIycc2SA77VqVcZmtur5Vlq1Yws8rR+kOTr3bdSwYtwje7pJ8h+DWPA9HeMwNlc19aCFDt5tPRQCBtxBuc3wfv/sS6NZ8ZESEIX87XF+ZCQV0bHU7p9Lgd/Xe9VhtnTKtcS7HDfObIEwqfOTTqd073Q4TnQpjwGPaAfo1RA6mUBDyEuK/rk6wpSU+nd8v9LscNk9RQ==', 'a6cb67d406f52feef4394f64', '9ebec9cd9ffd5849acc5bc3ff8f12d97', 0, '2026-06-04 03:27:22', '2026-06-04 03:27:22');

-- --------------------------------------------------------

--
-- Table structure for table `file_shares`
--

CREATE TABLE `file_shares` (
  `id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `recipient_id` int(11) NOT NULL,
  `session_key_enc` text NOT NULL,
  `permission` enum('read') DEFAULT 'read',
  `shared_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_revoked` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `file_shares`
--

INSERT INTO `file_shares` (`id`, `file_id`, `owner_id`, `recipient_id`, `session_key_enc`, `permission`, `shared_at`, `is_revoked`) VALUES
(1, 1, 1, 2, 'mRdmzolZC7Y77s0JR8cL/I7gjXMGoLnWGzQamtXbrArHQmHRNFjqqyTFDcJ5cebU797ZKNF6rCl5K62I+Z9tJsE0nPo6zl1CW3kplFAm/eJyVTrH1++k/bEOsP7GPxvMm3qXgUpP46tbwIKTLfbeLexT061aqgSGQqT9MvrNF903QwcH9gbF1FmwwNlyUAPcYoyDoqJMEil5tO2rnGYCIk8zAvTVos/KL/V/GWJLlvXakvrnxuJtfmCrfC3I78IJLWy9Xr6O7w2EMrTGhOyyh+nUGDSI+KCks7C45w3QPcT3rk5icVxbB/C5lqcHbRYmXVsuGgujUq2c55x+to6sNA==', 'read', '2026-05-28 01:05:44', 1),
(2, 1, 1, 3, 'LSmNwjvCp8wQVupQb2BMXr6OxwA8sMVRMvDkLqneGk9ZbLJLJvJSIZCiHyfcUNBtxa3Wp6lNm7862hDKpgYAKiOk2ohPdROi5c3ZGaoWPN0l18vFfjVsMMdXrEVgc+IwNPFz8joLQGmBAPSnQOt0iwFG+aNCxnSfAn5Rk3IzQJGXYltqI+sPAC3QJ50rmLMZH++OMtQcyOmsCGdZ1r0AUE/+DuxEZ0qjmAj2ewn1NaccFNKJNuMkQKbYEZdq7UdlL63y8TJ2IQd2mngqNa9hRxGcRPoUCROI1nrKJIWLwQmmjpJxvsJO4cuBb0G1hoEZueuYZN/yJ5PqAWW2NPvf/A==', 'read', '2026-05-30 03:15:04', 1),
(4, 6, 1, 5, 'zx9ReNZ8Q64TqvAss4HZ85tviv+JAJXrUOpHQiJAX0IKaOHyLe+HOlBqXzz14ASFapTGdr7aKC+aKpUd7Te5PuXf9Ru5rtx0uIO0zcgosB2HOpIFz9B7oDasKn4/B+P4rY7CuigsbvkzNxlsDFMOc69Lx6yrttATINH3qV/pdAut7FtUnIyFbE9cVurzdXB0SQxnEqo+07Ip+I3lZRjAsvccz+FmKSJpTpMk7vXwUspbT9YtmRyTipKWFVQIHwgFgq9Gs/So5UPToP6YKN8nXSvgTMq1knaxPBinE9eL1LOqkAkLWlTnBJrwjIE0NsVkj1kmJDlIQmJWFQ4dETTJEQ==', 'read', '2026-06-04 01:03:23', 1),
(5, 8, 7, 1, 'X8d3e8Sgft7YyFpXvn2MsrJHd1xMCAXb8LoGP/A0Efs3dElHGbdTH+TcdSlgCJ6SqZpCPFETIzGs1CO0041cXR7NU3mRrajGPGog+tbCa0wKnUfDXlhHcLAgjJGYoodhfu8tb6QpMiB0vbKebkegozB2lzMn9vkPZvPWmabTfMJKQ5jdmWj7m5kl5QpGAsDaPVDtOVDRSrZFaTzbSeMuoEFYwIhix6NuvxCxq5aB0NySaWIOe+eWA08SwM4J5oZYy0tfoytfGJlhCl1F30Wmvoyv2r2/B7on4xGlMou/k+0tg0tS41EbnL/qi8iTN7wq09+DqP3CToOPZi9uRZSHzw==', 'read', '2026-06-04 03:30:35', 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `password_hash` varchar(255) NOT NULL,
  `public_key` text NOT NULL,
  `private_key_enc` text NOT NULL,
  `private_key_iv` varchar(64) NOT NULL,
  `private_key_salt` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `role`, `password_hash`, `public_key`, `private_key_enc`, `private_key_iv`, `private_key_salt`, `created_at`, `last_login`, `is_active`) VALUES
(1, 'indri', 'indri@mail.com', 'user', '$2y$12$zJhD3Pn2xUJb7d4SPYKdxeWCgUg8MTROjU0PodfXvm0168Cxkke32', '-----BEGIN PUBLIC KEY-----\r\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA3u4jf9NGQ5tZ4ikQHCgO\r\nXszIhjMyYDev6imj4inVoIC7olzVwRVohz2N6MGcGlm5rvel16SWS05q2Y5PPU2T\r\nA+AzV3FatCZKYODkBZD3j/57vRzYOPHD5fEHGRdTVdvbXzIMf5N38EUGiTqSrivY\r\n8ITqZCobiZNhX1K18sHVToUdG6OYuSm5LegKgTEwbneTc+sXaEdePqX35+L+ryLd\r\nM44o2lakFgmixtwep+Mcu1jFvLud8sDkBRJAbbY4cmBuOreemarWcc11uYXvhu9P\r\n17NyqEx7wk9ftps8T6/A6nlOl4BYJ9q3x7j02/bsWytbaIdnO12FdrnSVQdygs0l\r\nQwIDAQAB\r\n-----END PUBLIC KEY-----', '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', '0a82e2ead03672788b192fe17def827c', '58766385872371f059e4928c3702b9ca', '2026-05-28 00:39:04', '2026-06-04 03:30:53', 0),
(2, 'hilal', 'hilal@mail.com', 'user', '$2y$12$dyNqbS.gKUAg7tRsrPt/GextW3zYI7r8VMdXyzK.ltKGZu0QNkprm', '-----BEGIN PUBLIC KEY-----\r\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAs8cZIPwTSper91zGtOFu\r\nplXaymSgXztx8XYIpAZx5lKQ1vPnM5gcQ16w9DvUPGdFY/UehSMoiFTWvx3pQ6S8\r\n1qzZQHaN/ZGy0IBOyhL9Adbe3s+DieLbyKA+B1s8GXfPF30EfWxLfd45S/KouRlg\r\nOlT8T0JFuRCZp5+3o9Paars1ZucucEfJpOxHD2gprkmJS2ECpG8Gb62Rd1eUHipT\r\nPvfgLWUF9v5yX/4UgZZIXfQuqevbeg/p/5OxWMxjz796FmzdXD8h/91meZS52lj1\r\n84CrW4oE6k9S5JXTYnYZZtR/zUC8om7qwXo/vDBSS9wCeA31B+WeJOKm2KMofTGr\r\nhQIDAQAB\r\n-----END PUBLIC KEY-----', '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', '313eee7a6e7103e7a4fe9f1a71e1ed32', '1813614c9dd433ce010c603552e3a805', '2026-05-28 01:05:14', '2026-05-28 01:06:06', 1),
(3, 'adam', 'adam@gmail.com', 'admin', '$2y$12$TuCLeMU8E.isWlDSlJAtLOW/E.jLkCUfhEy4nhQB3x0NKMwdxnwf6', '-----BEGIN PUBLIC KEY-----\r\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEArij6bRV4PUObMv6MPYkA\r\nSNViq51ArykwjTF6ATsl4NvQCRk1f45Pqr6Et3eckbB3RWgAVjG0tuq8l290nbB/\r\nT6aELjunasSYuicIzhOgHDlgiN6+UbWNd4+FgTTU7zAqid/c81ik9FvYWE3SPide\r\nEUq1XndlD1Mfk/jcS6M3RN+gFI6X7inAlxizgusSWOreQix2iiy4WfNsrFDUVXj2\r\nqKBL0fPbYKrlx3VnbK4InKlUnpu/qyDxtGyb7JAvIgMQszxnqGm2Q/94Hbrww5eB\r\nmebd329ndQgE51xU/gToxxwVSUMrXc8c4/mLrWZW0Z1Dk9K22xVOhM8RCsoTo8g9\r\ncwIDAQAB\r\n-----END PUBLIC KEY-----', '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', '9ae8ba6d83ade5ef6caf378771f76a0c', 'f2ef14995d42fa3041c26e8cd7612c1d', '2026-05-29 11:20:46', '2026-06-04 03:33:46', 1),
(5, 'edward', 'edward@gmail.com', 'user', '$2y$12$5BYf84l31nZdrfEggRWH2.k3Wxsat4I6NZhDJNdMFCFz9YPNIdpW.', '-----BEGIN PUBLIC KEY-----\r\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA8EEkgJIm84pSorDfWKqG\r\nOOI/0edtCWtZdZdJCanNV18O4nIdU5aFu5YZA2shDpDW+RA99Z18yXa5WZwUUoJq\r\nVfFXVeyVb5yTlUGPR4AAiV2t9Uh2JXZJxdoOVPYh+r25f6T6jxm0kiAryGIL3alu\r\nzfcgD7OMrkqYUDBgxVTkt9e6aEhUqpwVUAAmbWSb8ytP7WvUTnOec96ZuISAos09\r\n22m0WQDkNAxCRoQh14QITjhflodyQhfG+XJKK7FHGhe9JqEgKsSBYRDuRv0tOq+o\r\nkZzd7k87vGCoFcL0ZjMpqwylXOk2LB3ZGuh//FCpwSwucHLo0XIHsBoRSYBlWspn\r\nwwIDAQAB\r\n-----END PUBLIC KEY-----', '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', 'ca366243fbf4be545766b74ae8c8552e', '209a7efc60c236d4b571606574eef22d', '2026-06-03 16:31:01', '2026-06-04 01:04:16', 1),
(6, 'user', 'min@gmail.com', 'user', '$2y$12$rd/poMnfXgzL/g78jgTSZuOJjB1hWajZRHsEDxiYktGxhLps1MMO2', '-----BEGIN PUBLIC KEY-----\r\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAncRhDyDuDg8Uy2VYDlgt\r\nVvmtWE3YiMhACPcO/6zwznDW1VM22NBkYQu6bB2K+h7Gs06ZEJBRm3gFfNiMaroj\r\ng5A+0v0R5zeKUgbjVUoG47K7hDkBxR0rqwD11rICqG4a4e/Xb1zLFtehD9b/MUn2\r\ngT1UiWpQc9yPoFSlReDgMGnX+QHbFOyD6IL58N309KRXZWSMwptlmZht9CwvEAN+\r\ngS7OBBX4siJdGqyLbDlbllWjtqJ9pI45X1Vk00TRPCHhQtBDXVbpbS+bQMheBUER\r\n1bB30PpAZpJ/0dAlkFpPXQpuyLCaOMxiKBwSb1P2cQLg7zWg48FvE+3DF78sNi0M\r\nnQIDAQAB\r\n-----END PUBLIC KEY-----', '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', '89a891f50e9daa4ae8ca72ffe016d8fe', 'ea35bb4b7a42c474561249314a03cc99', '2026-06-04 02:58:46', '2026-06-04 03:00:02', 0),
(7, 'ummu', 'ummu@gmail.com', 'user', '$2y$12$aNFYHv8zJMUh1i7quwb.8uDUGPhtt.Qth21muGWq4qEzpKBxjuxaq', '-----BEGIN PUBLIC KEY-----\r\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAnPZWFu9bqa+abFMPcqJC\r\npixWa+qil8VInMv7wgEdcM3VABB6ZdAP5CklPEbAyWmm3WrYBrIfcZRk/aF22HzU\r\nRFM8fTYO1pNpe+vXYm40IMZcSlaGIctIftpjxg1vxTbwrpqwzLstLuXM6xCswU74\r\nap4MrQ1iE07J52qruzNg4yCW0KivjRIdQq0hmHtuytSxsDQ1ZBqFo8iK1dVQS/0e\r\nGABmR4pLTxP9FZCgUb7RqNL0YHijCDMS3Pyy+JAQ8TbLIrn52+6vwafrqSOD6Zug\r\nKKLNtNrXp9KpKnYhgeTSXLwTAAcXbU5MbFBm+uwIoBiOWFTpsRLMqGAQd9rgg7bL\r\nLwIDAQAB\r\n-----END PUBLIC KEY-----', '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', '3cc2ff32f9a241aaddea6b5a06ba2aa9', 'a15b30186c9ce196b80fa46e146f56a3', '2026-06-04 03:24:52', '2026-06-04 03:26:03', 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `session_token_hash` char(64) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_logs_user` (`user_id`),
  ADD KEY `idx_logs_file` (`file_id`),
  ADD KEY `idx_logs_created` (`created_at`);

--
-- Indexes for table `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `filename_stored` (`filename_stored`),
  ADD KEY `idx_files_owner` (`owner_id`);

--
-- Indexes for table `file_shares`
--
ALTER TABLE `file_shares`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_share` (`file_id`,`recipient_id`),
  ADD KEY `owner_id` (`owner_id`),
  ADD KEY `idx_shares_file` (`file_id`),
  ADD KEY `idx_shares_recipient` (`recipient_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`session_token_hash`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_sessions_cleanup` (`expires_at`,`is_active`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- AUTO_INCREMENT for table `files`
--
ALTER TABLE `files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `file_shares`
--
ALTER TABLE `file_shares`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `activity_logs_ibfk_2` FOREIGN KEY (`file_id`) REFERENCES `files` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `files`
--
ALTER TABLE `files`
  ADD CONSTRAINT `files_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `file_shares`
--
ALTER TABLE `file_shares`
  ADD CONSTRAINT `file_shares_ibfk_1` FOREIGN KEY (`file_id`) REFERENCES `files` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `file_shares_ibfk_2` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `file_shares_ibfk_3` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
