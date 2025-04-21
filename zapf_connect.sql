-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 21, 2025 at 11:15 AM
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
-- Database: `zapf_connect`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action_type` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `announcement_id` int(11) NOT NULL,
  `creator_email` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `importance` varchar(20) DEFAULT 'normal',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`announcement_id`, `creator_email`, `title`, `content`, `importance`, `created_at`, `expires_at`) VALUES
(1, 'nana@zapf.com', 'Upcoming Events', 'There is an upcomming holiday', 'high', '2025-04-19 07:27:13', '2025-04-21 09:23:00'),
(2, 'nana@zapf.com', 'Delay in paydays', 'We are apologysing for delays in pay', 'normal', '2025-04-19 13:08:34', '2025-05-02 15:07:00');

-- --------------------------------------------------------

--
-- Table structure for table `announcement_tracking`
--

CREATE TABLE `announcement_tracking` (
  `id` int(11) NOT NULL,
  `announcement_id` int(11) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `viewed_at` timestamp NULL DEFAULT NULL,
  `response` text DEFAULT NULL,
  `responded_at` timestamp NULL DEFAULT NULL,
  `reminder_sent` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcement_tracking`
--

INSERT INTO `announcement_tracking` (`id`, `announcement_id`, `user_email`, `viewed_at`, `response`, `responded_at`, `reminder_sent`) VALUES
(1, 1, 'admin@zapf.com', '2025-04-19 08:19:37', '', '2025-04-19 08:28:05', 0),
(2, 1, 'nana@zapf.com', '2025-04-19 07:27:13', NULL, NULL, 0),
(3, 2, 'munyabmasuka@gmail.com', NULL, NULL, NULL, 0),
(4, 2, 'nana@zapf.com', '2025-04-19 13:08:34', NULL, NULL, 0),
(5, 2, 'admin@zapf.com', NULL, NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE `groups` (
  `group_id` int(11) NOT NULL,
  `group_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `group_members`
--

CREATE TABLE `group_members` (
  `id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `meetings`
--

CREATE TABLE `meetings` (
  `meeting_id` int(11) NOT NULL,
  `creator_email` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `meeting_date` datetime NOT NULL,
  `duration` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) DEFAULT 'active',
  `creator_reminder_sent` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `meetings`
--

INSERT INTO `meetings` (`meeting_id`, `creator_email`, `title`, `description`, `location`, `meeting_date`, `duration`, `created_at`, `status`, `creator_reminder_sent`) VALUES
(1, 'admin@zapf.com', 'Currency Conversions', 'We want to change the rate at which we convert USD to ZIG', 'online', '2025-04-20 09:28:00', 60, '2025-04-19 07:29:50', 'active', 0);

-- --------------------------------------------------------

--
-- Table structure for table `meeting_participants`
--

CREATE TABLE `meeting_participants` (
  `id` int(11) NOT NULL,
  `meeting_id` int(11) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `response` varchar(20) DEFAULT 'pending',
  `viewed_at` timestamp NULL DEFAULT NULL,
  `responded_at` timestamp NULL DEFAULT NULL,
  `reminder_sent` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `meeting_participants`
--

INSERT INTO `meeting_participants` (`id`, `meeting_id`, `user_email`, `response`, `viewed_at`, `responded_at`, `reminder_sent`) VALUES
(1, 1, 'admin@zapf.com', 'pending', '2025-04-19 07:30:25', NULL, 0),
(2, 1, 'nana@zapf.com', 'accepted', '2025-04-19 07:31:44', '2025-04-19 07:31:55', 0),
(3, 1, 'munyanmasuka@gmail.com', 'pending', NULL, NULL, 0),
(4, 1, 'anesu@zapf.com', 'pending', NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL,
  `sender_email` varchar(100) NOT NULL,
  `recipient_email` varchar(100) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message_text` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `is_important` tinyint(1) DEFAULT 0,
  `priority` enum('normal','important','urgent') DEFAULT 'normal',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`message_id`, `sender_email`, `recipient_email`, `subject`, `message_text`, `is_read`, `is_important`, `priority`, `created_at`, `read_at`) VALUES
(1, 'admin', 'mrmasukaa@gmail.com', 'mari', 'ipi', 1, 0, 'normal', '2025-04-07 14:50:11', '2025-04-08 06:55:10'),
(2, 'munyaradzi masuka', 'nana@zapf.com', 'cash sales', 'we are selling the car', 1, 0, 'normal', '2025-04-07 15:06:12', '2025-04-08 06:42:01'),
(3, 'munyaradzi masuka', 'mrmasukaa@gmail.com', 'Pension fund', 'Well money is needed', 1, 0, 'normal', '2025-04-07 15:17:32', '2025-04-08 06:55:08'),
(4, 'mrmasukaa@gmail.com', 'nana@zapf.com', 'loadn repayment', 'please pay loan', 1, 1, 'normal', '2025-04-07 15:24:38', '2025-04-08 06:41:50'),
(5, 'mrmasukaa@gmail.com', 'admin@zapf.com', 'requesting access controls', 'I need access controls to retract a message', 1, 1, 'urgent', '2025-04-08 04:36:56', '2025-04-08 07:17:05'),
(6, 'munyaradzi masuka', 'admin@zapf.com', 'password reset', 'I want a password reset', 1, 0, 'normal', '2025-04-08 04:43:36', '2025-04-08 07:57:30'),
(7, 'admin', 'admin@zapf.com', 'Pension fund', 'how far', 1, 0, 'normal', '2025-04-08 05:43:02', '2025-04-08 07:08:51'),
(8, 'admin', 'admin@zapf.com', 'finance payment', 'how far', 1, 0, 'important', '2025-04-08 05:51:15', '2025-04-08 07:54:18'),
(9, 'admin', 'mrmasukaa@gmail.com', 'test', 'does it work', 1, 0, 'normal', '2025-04-08 05:55:04', '2025-04-08 06:55:07'),
(10, 'admin', 'mrmasukaa@gmail.com', 'finance payment', 'normal', 1, 0, 'normal', '2025-04-08 06:03:22', '2025-04-08 06:54:57'),
(11, 'admin', 'mrmasukaa@gmail.com', 'finance payment', 'pay up', 1, 0, 'normal', '2025-04-08 06:35:21', '2025-04-08 06:54:56'),
(12, 'admin', 'mrmasukaa@gmail.com', 'finance payment', 'payy us', 1, 0, 'normal', '2025-04-08 06:38:06', '2025-04-08 06:54:26'),
(13, 'nana@zapf.com', 'mrmasukaa@gmail.com', 'Pension fund', 'hie your pension is 2000', 1, 0, 'normal', '2025-04-08 06:42:36', '2025-04-08 06:54:27'),
(14, 'admin', 'mrmasukaa@gmail.com', 'Access granted', 'I have granted you 3 days access', 1, 0, 'normal', '2025-04-08 06:58:20', '2025-04-08 07:00:10'),
(15, 'nana@zapf.com', 'admin@zapf.com', 'Testing', 'can messages be viewed now', 1, 1, 'normal', '2025-04-08 07:04:37', '2025-04-08 07:06:40'),
(16, 'admin@zapf.com', 'nana@zapf.com', 'Re: Testing', 'Hie Nannettee, you can succussfully test\n\n\n--------- Original Message ---------\nFrom: Nannette <nana@zapf.com>\nDate: 8/4/2025, 09:04:37\nSubject: Testing\n\n> can messages be viewed now', 1, 0, 'normal', '2025-04-08 07:46:52', '2025-04-08 07:58:07'),
(17, 'nana@zapf.com', 'admin@zapf.com', 'sound check', 'hie, is sound there', 1, 0, 'normal', '2025-04-08 07:55:32', '2025-04-08 07:56:01'),
(18, 'admin@zapf.com', 'nana@zapf.com', 'Re: sound check', 'no sound\n\n\n--------- Original Message ---------\nFrom: Nannette <nana@zapf.com>\nDate: 8/4/2025, 09:55:32\nSubject: sound check\n\n> hie, is sound there', 1, 0, 'normal', '2025-04-08 07:56:14', '2025-04-08 07:58:10'),
(19, 'nana@zapf.com', 'admin@zapf.com', 'No sound', 'sound not found', 1, 0, 'urgent', '2025-04-08 07:59:37', '2025-04-08 08:01:00'),
(20, 'admin@zapf.com', 'nana@zapf.com', 'Re: No sound', '\nWHY\n\n--------- Original Message ---------\nFrom: Nannette <nana@zapf.com>\nDate: 8/4/2025, 09:59:37\nSubject: No sound\n\n> sound not found', 1, 0, 'urgent', '2025-04-08 08:01:21', '2025-04-08 08:02:41'),
(21, 'admin@zapf.com', 'nana@zapf.com', 'IMWE', 'CHECK', 1, 0, 'normal', '2025-04-08 08:07:49', '2025-04-08 08:08:12'),
(22, 'nana@zapf.com', 'admin@zapf.com', 'MADII', 'BBVBVBVB', 1, 0, 'normal', '2025-04-08 08:09:22', '2025-04-08 08:11:38'),
(23, 'nana@zapf.com', 'admin@zapf.com', 'Re: IMWE', 'BHO HER\n\n\n--------- Original Message ---------\nFrom: Administrator <admin@zapf.com>\nDate: 4/8/2025, 10:07:49 AM\nSubject: IMWE\n\n> CHECK', 1, 0, 'normal', '2025-04-08 08:11:06', '2025-04-08 08:11:31'),
(24, 'nana@zapf.com', 'admin@zapf.com', 'Testing', 'i test you', 1, 0, 'normal', '2025-04-10 12:50:19', '2025-04-18 06:35:00'),
(25, 'nana@zapf.com', 'admin@zapf.com', 'Pension fund', '5000', 1, 0, 'normal', '2025-04-10 12:51:22', '2025-04-18 06:40:19'),
(26, 'admin@zapf.com', 'nana@zapf.com', 'Re: Testing', 'PABHOO\n\n\n--------- Original Message ---------\nFrom: Nannette <nana@zapf.com>\nDate: 4/10/2025, 2:50:19 PM\nSubject: Testing\n\n> i test you', 1, 0, 'normal', '2025-04-18 06:35:54', '2025-04-18 06:38:59'),
(27, 'nana@zapf.com', 'admin@zapf.com', 'Re: Testing', '\n\n\n--------- Original Message ---------\nFrom: Administrator <admin@zapf.com>\nDate: 4/18/2025, 8:35:54 AM\nSubject: Re: Testing\n\n> PABHOO\n> \n> \n> --------- Original Message ---------\n> From: Nannette <nana@zapf.com>\n> Date: 4/10/2025, 2:50:19 PM\n> Subject: Testing\n> \n> > i test you', 1, 0, 'normal', '2025-04-18 06:39:13', '2025-04-18 08:02:58'),
(28, 'nana@zapf.com', 'admin@zapf.com', 'restest', 'hie admin', 1, 0, 'normal', '2025-04-18 06:40:50', '2025-04-18 08:02:53'),
(29, 'admin@zapf.com', 'nana@zapf.com', '123 testing', 'apo', 1, 0, 'normal', '2025-04-18 09:58:55', '2025-04-18 09:59:21'),
(30, 'nana@zapf.com', 'admin@zapf.com', 'IMWE', 'test', 1, 0, 'normal', '2025-04-18 09:59:46', '2025-04-19 06:06:16'),
(31, 'nana@zapf.com', 'admin@zapf.com', 'Advanced test', 'TESTING JS', 1, 0, 'normal', '2025-04-19 06:04:34', '2025-04-19 06:05:49'),
(32, 'admin@zapf.com', 'nana@zapf.com', 'Re: Advanced test', 'PABHOO MALO\n\n\n--------- Original Message ---------\nFrom: Nannette <nana@zapf.com>\nDate: 4/19/2025, 8:04:34 AM\nSubject: Advanced test\n\n> TESTING JS', 1, 0, 'normal', '2025-04-19 06:06:03', '2025-04-19 06:07:21'),
(33, 'admin@zapf.com', 'nana@zapf.com', 'Re: Advanced test 2', 'FUTI', 0, 0, 'normal', '2025-04-19 06:09:55', NULL),
(34, 'nana@zapf.com', 'admin@zapf.com', 'check', '123', 1, 0, 'normal', '2025-04-19 06:11:16', '2025-04-19 06:12:03'),
(35, 'admin@zapf.com', 'nana@zapf.com', 'Re: check', 'bho test\n\n\n--------- Original Message ---------\nFrom: Nannette <nana@zapf.com>\nDate: 4/19/2025, 8:11:16 AM\nSubject: check\n\n> 123', 1, 0, 'normal', '2025-04-19 06:12:24', '2025-04-19 06:12:55'),
(36, 'nana@zapf.com', 'admin@zapf.com', 'sound check', 'check sound', 1, 0, 'normal', '2025-04-19 06:15:09', '2025-04-19 06:16:21'),
(37, 'admin@zapf.com', 'nana@zapf.com', 'Re: sound check', '\nsafe\n\n--------- Original Message ---------\nFrom: Nannette <nana@zapf.com>\nDate: 4/19/2025, 8:15:09 AM\nSubject: sound check\n\n> check sound', 1, 0, 'normal', '2025-04-19 06:16:38', '2025-04-19 06:17:03'),
(38, 'nana@zapf.com', 'admin@zapf.com', 'Re: sound check', 'shuwabhere\n\n\n--------- Original Message ---------\nFrom: Administrator <admin@zapf.com>\nDate: 19/4/2025, 08:16:38\nSubject: Re: sound check\n\n> \n> safe\n> \n> --------- Original Message ---------\n> From: Nannette <nana@zapf.com>\n> Date: 4/19/2025, 8:15:09 AM\n> Subject: sound check\n> \n> > check sound', 0, 0, 'normal', '2025-04-19 06:17:19', NULL),
(39, 'nana@zapf.com', 'admin@zapf.com', 'Re: sound check', 'I have granted you 3 days access', 1, 0, 'normal', '2025-04-19 06:22:10', '2025-04-19 06:22:57'),
(40, 'nana@zapf.com', 'admin@zapf.com', 'Re: sound check', 'info', 1, 0, 'normal', '2025-04-19 06:22:26', '2025-04-19 06:22:59'),
(41, 'nana@zapf.com', 'admin@zapf.com', 'Re: sound check', 'hello', 1, 0, 'normal', '2025-04-19 06:36:36', '2025-04-19 06:37:24'),
(42, 'admin@zapf.com', 'nana@zapf.com', 'Re: sound check', 'oky\n\n\n--------- Original Message ---------\nFrom: Nannette <nana@zapf.com>\nDate: 4/19/2025, 8:36:36 AM\nSubject: Re: sound check\n\n> hello', 1, 0, 'normal', '2025-04-19 06:37:40', '2025-04-19 06:46:28'),
(43, 'admin@zapf.com', 'nana@zapf.com', 'Re: sound check', 'bho\n\n\n--------- Original Message ---------\nFrom: Nannette <nana@zapf.com>\nDate: 4/19/2025, 8:36:36 AM\nSubject: Re: sound check\n\n> hello', 0, 0, 'normal', '2025-04-19 06:54:06', NULL),
(44, 'nana@zapf.com', 'admin@zapf.com', 'fast', 'fast one', 1, 0, 'normal', '2025-04-19 09:36:53', '2025-04-19 09:37:28'),
(45, 'admin@zapf.com', 'nana@zapf.com', 'Re: fast', 'not fast enough\n\n\n--------- Original Message ---------\nFrom: Nannette <nana@zapf.com>\nDate: 4/19/2025, 11:36:53 AM\nSubject: fast\n\n> fast one', 1, 0, 'normal', '2025-04-19 09:37:41', '2025-04-19 09:38:05'),
(46, 'admin@zapf.com', 'nana@zapf.com', 'more ', 'hie', 1, 0, 'normal', '2025-04-19 09:44:13', '2025-04-19 09:44:33'),
(47, 'nana@zapf.com', 'admin@zapf.com', 'Re: more ', 'faster\n\n\n--------- Original Message ---------\nFrom: Administrator <admin@zapf.com>\nDate: 4/19/2025, 11:44:13 AM\nSubject: more \n\n> hie', 1, 0, 'normal', '2025-04-19 09:45:23', '2025-04-19 13:03:26'),
(48, 'admin@zapf.com', 'nana@zapf.com', 'text', 'speed', 1, 0, 'normal', '2025-04-19 11:07:11', '2025-04-19 11:08:25'),
(49, 'admin@zapf.com', 'nana@zapf.com', 'fasterr', 'hello', 0, 0, 'normal', '2025-04-19 11:12:10', NULL),
(50, 'admin@zapf.com', 'nana@zapf.com', 'more ', 'test', 1, 0, 'normal', '2025-04-19 13:05:29', '2025-04-19 13:51:13'),
(51, 'admin@zapf.com', 'nana@zapf.com', 'speed check', 'how far', 1, 0, 'normal', '2025-04-19 13:20:13', '2025-04-19 13:20:47'),
(52, 'nana@zapf.com', 'admin@zapf.com', 'Re: speed check', 'too slow\n\n\n--------- Original Message ---------\nFrom: Administrator <admin@zapf.com>\nDate: 4/19/2025, 3:20:13 PM\nSubject: speed check\n\n> how far', 0, 0, 'normal', '2025-04-19 13:21:20', NULL),
(53, 'admin@zapf.com', 'nana@zapf.com', 'cash sales', '1000000', 1, 0, 'normal', '2025-04-19 13:22:17', '2025-04-19 13:51:09');

-- --------------------------------------------------------

--
-- Table structure for table `message_status`
--

CREATE TABLE `message_status` (
  `status_id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `status` enum('sent','delivered','read') NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `message_status`
--

INSERT INTO `message_status` (`status_id`, `message_id`, `status`, `updated_at`) VALUES
(1, 1, 'sent', '2025-04-07 14:50:11'),
(2, 2, 'sent', '2025-04-07 15:06:12'),
(3, 3, 'sent', '2025-04-07 15:17:32'),
(4, 4, 'sent', '2025-04-07 15:24:38'),
(5, 5, 'sent', '2025-04-08 04:36:56'),
(6, 6, 'sent', '2025-04-08 04:43:36'),
(7, 7, 'sent', '2025-04-08 05:43:02'),
(8, 8, 'sent', '2025-04-08 05:51:15'),
(9, 9, 'sent', '2025-04-08 05:55:04'),
(10, 10, 'sent', '2025-04-08 06:03:22'),
(11, 11, 'sent', '2025-04-08 06:35:21'),
(12, 12, 'sent', '2025-04-08 06:38:06'),
(13, 4, 'read', '2025-04-08 06:41:50'),
(14, 2, 'read', '2025-04-08 06:42:01'),
(15, 13, 'sent', '2025-04-08 06:42:36'),
(16, 12, 'read', '2025-04-08 06:54:26'),
(17, 13, 'read', '2025-04-08 06:54:27'),
(18, 11, 'read', '2025-04-08 06:54:56'),
(19, 10, 'read', '2025-04-08 06:54:57'),
(20, 9, 'read', '2025-04-08 06:55:07'),
(21, 3, 'read', '2025-04-08 06:55:08'),
(22, 1, 'read', '2025-04-08 06:55:10'),
(23, 14, 'sent', '2025-04-08 06:58:20'),
(24, 14, 'read', '2025-04-08 07:00:10'),
(25, 15, 'sent', '2025-04-08 07:04:37'),
(26, 15, 'read', '2025-04-08 07:06:40'),
(27, 7, 'read', '2025-04-08 07:08:51'),
(28, 5, 'read', '2025-04-08 07:17:05'),
(29, 16, 'sent', '2025-04-08 07:46:52'),
(30, 8, 'read', '2025-04-08 07:54:18'),
(31, 17, 'sent', '2025-04-08 07:55:32'),
(32, 17, 'read', '2025-04-08 07:56:01'),
(33, 18, 'sent', '2025-04-08 07:56:14'),
(34, 6, 'read', '2025-04-08 07:57:30'),
(35, 16, 'read', '2025-04-08 07:58:07'),
(36, 18, 'read', '2025-04-08 07:58:10'),
(37, 19, 'sent', '2025-04-08 07:59:37'),
(38, 19, 'read', '2025-04-08 08:01:00'),
(39, 20, 'sent', '2025-04-08 08:01:21'),
(40, 20, 'read', '2025-04-08 08:02:41'),
(41, 21, 'sent', '2025-04-08 08:07:49'),
(42, 21, 'read', '2025-04-08 08:08:12'),
(43, 22, 'sent', '2025-04-08 08:09:22'),
(44, 23, 'sent', '2025-04-08 08:11:06'),
(45, 23, 'read', '2025-04-08 08:11:31'),
(46, 22, 'read', '2025-04-08 08:11:38'),
(47, 24, 'sent', '2025-04-10 12:50:19'),
(48, 25, 'sent', '2025-04-10 12:51:22'),
(49, 24, 'read', '2025-04-18 06:35:00'),
(50, 26, 'sent', '2025-04-18 06:35:54'),
(51, 26, 'read', '2025-04-18 06:38:59'),
(52, 27, 'sent', '2025-04-18 06:39:13'),
(53, 25, 'read', '2025-04-18 06:40:19'),
(54, 28, 'sent', '2025-04-18 06:40:50'),
(55, 28, 'read', '2025-04-18 08:02:53'),
(56, 27, 'read', '2025-04-18 08:02:58'),
(57, 29, 'sent', '2025-04-18 09:58:55'),
(58, 29, 'read', '2025-04-18 09:59:21'),
(59, 30, 'sent', '2025-04-18 09:59:46'),
(60, 31, 'sent', '2025-04-19 06:04:34'),
(61, 31, 'read', '2025-04-19 06:05:49'),
(62, 32, 'sent', '2025-04-19 06:06:03'),
(63, 30, 'read', '2025-04-19 06:06:16'),
(64, 32, 'read', '2025-04-19 06:07:21'),
(65, 33, 'sent', '2025-04-19 06:09:55'),
(66, 34, 'sent', '2025-04-19 06:11:16'),
(67, 34, 'read', '2025-04-19 06:12:03'),
(68, 35, 'sent', '2025-04-19 06:12:24'),
(69, 35, 'read', '2025-04-19 06:12:55'),
(70, 36, 'sent', '2025-04-19 06:15:09'),
(71, 36, 'read', '2025-04-19 06:16:21'),
(72, 37, 'sent', '2025-04-19 06:16:38'),
(73, 37, 'read', '2025-04-19 06:17:03'),
(74, 38, 'sent', '2025-04-19 06:17:19'),
(75, 39, 'sent', '2025-04-19 06:22:10'),
(76, 40, 'sent', '2025-04-19 06:22:26'),
(77, 39, 'read', '2025-04-19 06:22:57'),
(78, 40, 'read', '2025-04-19 06:22:59'),
(79, 41, 'sent', '2025-04-19 06:36:36'),
(80, 41, 'read', '2025-04-19 06:37:24'),
(81, 42, 'sent', '2025-04-19 06:37:40'),
(82, 42, 'read', '2025-04-19 06:46:28'),
(83, 43, 'sent', '2025-04-19 06:54:06'),
(84, 44, 'sent', '2025-04-19 09:36:53'),
(85, 44, 'read', '2025-04-19 09:37:28'),
(86, 45, 'sent', '2025-04-19 09:37:41'),
(87, 45, 'read', '2025-04-19 09:38:05'),
(88, 46, 'sent', '2025-04-19 09:44:13'),
(89, 46, 'read', '2025-04-19 09:44:33'),
(90, 47, 'sent', '2025-04-19 09:45:23'),
(91, 48, 'sent', '2025-04-19 11:07:11'),
(92, 48, 'read', '2025-04-19 11:08:25'),
(93, 49, 'sent', '2025-04-19 11:12:10'),
(94, 47, 'read', '2025-04-19 13:03:26'),
(95, 50, 'sent', '2025-04-19 13:05:29'),
(96, 51, 'sent', '2025-04-19 13:20:13'),
(97, 51, 'read', '2025-04-19 13:20:47'),
(98, 52, 'sent', '2025-04-19 13:21:20'),
(99, 53, 'sent', '2025-04-19 13:22:17'),
(100, 53, 'read', '2025-04-19 13:51:09'),
(101, 50, 'read', '2025-04-19 13:51:13');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_email` varchar(100) NOT NULL,
  `type` varchar(20) NOT NULL,
  `title` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `sender_email` varchar(100) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reminders`
--

CREATE TABLE `reminders` (
  `reminder_id` int(11) NOT NULL,
  `user_email` varchar(100) NOT NULL,
  `title` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `due_date` datetime NOT NULL,
  `reference_type` varchar(20) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `notification_sent` tinyint(1) NOT NULL DEFAULT 0,
  `is_complete` tinyint(1) NOT NULL DEFAULT 0,
  `completed_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('admin','member') DEFAULT 'member',
  `organization` varchar(100) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `department` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `full_name`, `role`, `organization`, `position`, `profile_image`, `phone_number`, `is_active`, `last_login`, `created_at`, `updated_at`, `department`) VALUES
(1, 'mrmasukaa@gmail.com', 'mrmasukaa@gmail.com', 'munya123', 'Munyaradzi Masuka', 'member', 'ZAPF', NULL, NULL, NULL, 1, '2025-04-08 08:59:35', '2025-04-06 14:44:23', '2025-04-08 06:59:35', 'IT'),
(2, 'admin@zapf.com', 'admin@zapf.com', 'admin123', 'Administrator', 'admin', 'ZAPF', 'System Administrator', NULL, NULL, 1, '2025-04-19 11:42:40', '2025-04-06 15:18:33', '2025-04-19 09:42:40', 'Administration'),
(3, 'anesu@zapf.com', 'anesu@zapf.com', '$2y$10$i2mtmp4LzuYt.L6XWxakVOlvOq6WzXQFBROnZ.3kuxWWd/vXAhmXq', 'Anesu Zita', 'member', NULL, NULL, NULL, NULL, 0, NULL, '2025-04-06 16:45:50', '2025-04-08 06:57:12', 'Accounting'),
(4, 'nana@zapf.com', 'nana@zapf.com', 'nana123', 'Nannette', '', 'ZAPF', NULL, NULL, NULL, 1, '2025-04-21 07:58:17', '2025-04-06 16:56:53', '2025-04-21 05:58:17', 'Accounting');

-- --------------------------------------------------------

--
-- Table structure for table `user_settings`
--

CREATE TABLE `user_settings` (
  `setting_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `notification_email` tinyint(1) DEFAULT 1,
  `notification_sms` tinyint(1) DEFAULT 0,
  `notification_web` tinyint(1) DEFAULT 1,
  `theme` varchar(20) DEFAULT 'light',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_status`
--

CREATE TABLE `user_status` (
  `user_email` varchar(100) NOT NULL,
  `is_online` tinyint(1) DEFAULT 0,
  `last_active` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_status`
--

INSERT INTO `user_status` (`user_email`, `is_online`, `last_active`) VALUES
('admin', 1, '2025-04-08 06:59:21'),
('admin@zapf.com', 1, '2025-04-21 09:14:35'),
('mrmasukaa@gmail.com', 1, '2025-04-08 07:05:15'),
('Munyaradzi Masuka', 1, '2025-04-08 06:45:48'),
('nana@zapf.com', 1, '2025-04-21 09:15:34');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`announcement_id`);

--
-- Indexes for table `announcement_tracking`
--
ALTER TABLE `announcement_tracking`
  ADD PRIMARY KEY (`id`),
  ADD KEY `announcement_id` (`announcement_id`);

--
-- Indexes for table `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`group_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `group_members`
--
ALTER TABLE `group_members`
  ADD PRIMARY KEY (`id`),
  ADD KEY `group_id` (`group_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `meetings`
--
ALTER TABLE `meetings`
  ADD PRIMARY KEY (`meeting_id`);

--
-- Indexes for table `meeting_participants`
--
ALTER TABLE `meeting_participants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `meeting_id` (`meeting_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`);

--
-- Indexes for table `message_status`
--
ALTER TABLE `message_status`
  ADD PRIMARY KEY (`status_id`),
  ADD KEY `message_id` (`message_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_email` (`user_email`),
  ADD KEY `is_read` (`is_read`);

--
-- Indexes for table `reminders`
--
ALTER TABLE `reminders`
  ADD PRIMARY KEY (`reminder_id`),
  ADD KEY `user_email` (`user_email`),
  ADD KEY `due_date` (`due_date`),
  ADD KEY `notification_sent` (`notification_sent`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_settings`
--
ALTER TABLE `user_settings`
  ADD PRIMARY KEY (`setting_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `user_status`
--
ALTER TABLE `user_status`
  ADD PRIMARY KEY (`user_email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `announcement_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `announcement_tracking`
--
ALTER TABLE `announcement_tracking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `groups`
--
ALTER TABLE `groups`
  MODIFY `group_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `group_members`
--
ALTER TABLE `group_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `meetings`
--
ALTER TABLE `meetings`
  MODIFY `meeting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `meeting_participants`
--
ALTER TABLE `meeting_participants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `message_status`
--
ALTER TABLE `message_status`
  MODIFY `status_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reminders`
--
ALTER TABLE `reminders`
  MODIFY `reminder_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user_settings`
--
ALTER TABLE `user_settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `announcement_tracking`
--
ALTER TABLE `announcement_tracking`
  ADD CONSTRAINT `announcement_tracking_ibfk_1` FOREIGN KEY (`announcement_id`) REFERENCES `announcements` (`announcement_id`) ON DELETE CASCADE;

--
-- Constraints for table `groups`
--
ALTER TABLE `groups`
  ADD CONSTRAINT `groups_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `group_members`
--
ALTER TABLE `group_members`
  ADD CONSTRAINT `group_members_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`group_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `group_members_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `meeting_participants`
--
ALTER TABLE `meeting_participants`
  ADD CONSTRAINT `meeting_participants_ibfk_1` FOREIGN KEY (`meeting_id`) REFERENCES `meetings` (`meeting_id`) ON DELETE CASCADE;

--
-- Constraints for table `message_status`
--
ALTER TABLE `message_status`
  ADD CONSTRAINT `message_status_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `messages` (`message_id`);

--
-- Constraints for table `user_settings`
--
ALTER TABLE `user_settings`
  ADD CONSTRAINT `user_settings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
