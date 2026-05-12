-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 12, 2026 at 02:34 PM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tawasul db`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

DROP TABLE IF EXISTS `announcements`;
CREATE TABLE IF NOT EXISTS `announcements` (
  `announcementId` int NOT NULL AUTO_INCREMENT,
  `adminId` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `imagePath` varchar(255) DEFAULT NULL,
  `createdAt` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`announcementId`),
  KEY `adminId` (`adminId`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`announcementId`, `adminId`, `title`, `content`, `imagePath`, `createdAt`) VALUES
(7, 1, 'تحديث جدول الامتحانات النهائية للفصل الدراسي الحالي', ' انتباه لجميع أولياء الأمور بأنه قد تم إجراء تعديلات طفيفة على جدول الامتحانات النهائية لضمان عدم تضارب المواعيد. يرجى الاطلاع على الجدول الجديد ', '', '2026-05-12 13:29:16');

-- --------------------------------------------------------

--
-- Table structure for table `inquiries`
--

DROP TABLE IF EXISTS `inquiries`;
CREATE TABLE IF NOT EXISTS `inquiries` (
  `inquiryID` int NOT NULL AUTO_INCREMENT,
  `status` varchar(50) DEFAULT 'قيد الانتظار',
  `parentID` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `subject` varchar(60) NOT NULL,
  PRIMARY KEY (`inquiryID`),
  KEY `parentID` (`parentID`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `inquiries`
--

INSERT INTO `inquiries` (`inquiryID`, `status`, `parentID`, `created_at`, `subject`) VALUES
(1, 'قيد الانتظار', 2, '2026-05-11 17:23:58', 'تتتت'),
(2, 'قيد الانتظار', 2, '2026-05-11 17:24:04', 'تتتت');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
CREATE TABLE IF NOT EXISTS `messages` (
  `messageID` int NOT NULL AUTO_INCREMENT,
  `inquiryID` int NOT NULL,
  `senderID` int NOT NULL,
  `messageText` text NOT NULL,
  `timestamp` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`messageID`),
  KEY `inquiryID` (`inquiryID`),
  KEY `senderID` (`senderID`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`messageID`, `inquiryID`, `senderID`, `messageText`, `timestamp`) VALUES
(1, 1, 2, 'تتلتبغيغف', '2026-05-11 19:23:58'),
(2, 2, 2, 'ثصقثث', '2026-05-11 19:24:04');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `userID` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `userName` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL,
  PRIMARY KEY (`userID`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`userID`, `name`, `userName`, `password`, `role`) VALUES
(1, 'إدارة مدرسة ', 'admin_tawasul', 'admin123', 'admin'),
(2, 'علي محمد', 'parent_ali', 'parent123', 'parent');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
