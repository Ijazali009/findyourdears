-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 08, 2025 at 03:45 PM
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
-- Database: `missing_persons_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `role` enum('admin','employee') NOT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password`, `name`, `role`, `email`, `created_at`) VALUES
(1, 'admin', '$2y$10$Hldk9gPTxq2lcPsktctzr.YdcP4c7uFP5m8kTzgiR4yX0rRTdYuQ2', 'System Admin', 'admin', 'admin@example.com', '2025-05-08 04:02:57'),
(4, 'ijaz', '$2y$10$ClvJSlIeRLh28tjkwY4Sx.lVSNCTGx67cHdKJMAYWGluTxAZx6NUS', 'ijazali', 'employee', 'ijaz1233467@gmail.com', '2025-05-08 13:00:56'),
(8, 'asad', '$2y$10$UWw8S5yZNo.SQnL3Uu7D4OVW7iKJua4VdTHGga89oBes9miOEVGza', 'asadali', 'employee', 'asad@gmail.com', '2025-05-08 13:04:48'),
(10, 'asadi', '$2y$10$6LbNBTvO4S6OSf5tAJq.wuCvPuRKd3xSElcdFOLt8jwk9K6z4r4nS', '𝚊𝚓𝚞𝚞 ', 'admin', 'asadii@gmail.com', '2025-05-08 13:06:17');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`, `created_at`) VALUES
(3, 'asad', 'asad12367@gmail.com', 'dcsdcsdvsdvs fdfd', 'efwejfioj34iovj jiofj34io43jio jiovjervio', '2025-05-08 13:32:36');

-- --------------------------------------------------------

--
-- Table structure for table `found_persons`
--

CREATE TABLE `found_persons` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `contact` varchar(15) NOT NULL,
  `location` varchar(255) NOT NULL,
  `photo_url` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `found_persons`
--

INSERT INTO `found_persons` (`id`, `name`, `description`, `contact`, `location`, `photo_url`, `created_at`) VALUES
(1, 'naveed', 'sdcjsdhk', '0434544343', 'ewdedwefewfffkfl', 'uploads/1746668733_IMG_20240412_162154_250.jpg', '2025-05-08 01:45:33'),
(2, 'naveed', 'sfsdcfsd', '0434544343', 'ewdedwefewfffkfl', 'uploads/1746668796_black white Thunder logo (1).jpg', '2025-05-08 01:46:36'),
(3, 'naveed', 'sfsdcfsd', '0434544343', 'ewdedwefewfffkfl', 'uploads/1746668842_black white Thunder logo (1).jpg', '2025-05-08 01:47:22'),
(4, 'saqib', 'rergre grgtrgtr', '2345465457', 'erwere', 'uploads/1746671488_TN-FM-1-8.jpg', '2025-05-08 02:31:28');

-- --------------------------------------------------------

--
-- Table structure for table `missing_persons`
--

CREATE TABLE `missing_persons` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `contact` varchar(15) NOT NULL,
  `location` varchar(255) NOT NULL,
  `missing_date` date NOT NULL,
  `photo_url` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `missing_persons`
--

INSERT INTO `missing_persons` (`id`, `name`, `description`, `contact`, `location`, `missing_date`, `photo_url`, `created_at`) VALUES
(1, 'asad', 'jhkhjkh', '7878676567', '7897789', '2000-06-07', 'uploads/1746668338_AJ TECH-logos_transparent.png', '2025-05-08 01:38:58'),
(2, 'naqi', 'iopio', '8989089800', '23242', '2000-02-01', 'uploads/1746669699_Aj background remover.png', '2025-05-08 02:01:39'),
(3, 'sajid', '232 red edfwefwefwefweffffffff', '56456543456', 'gfnfggfnfg', '2024-02-02', 'uploads/1746670071_ACE Option1-06.png', '2025-05-08 02:07:51'),
(4, 'saba', 'ijojni ijoj jiojoj oijo', '898098098080809', '0988908', '0676-05-07', 'uploads/1746670769_thumbs_b_c_8dc060302dc81b0f520bb2bec4f02.jpg', '2025-05-08 02:19:29'),
(5, 'naseem', 'ewe 3rewrerwe', '342342342342343', 'fwefwefwe', '0032-03-31', 'uploads/1746671368_images.jpg', '2025-05-08 02:29:28');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `cnic` varchar(15) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `name`, `cnic`, `phone`, `created_at`) VALUES
(1, 'ali', '$2y$10$8YqKdR.U6S9Lvh8KitF9yuR7AfPjjU52MndQrGjKz2BTSPHuXgAvG', 'ali', '12234-4323434-3', '03184834180', '2025-05-08 03:27:30');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `found_persons`
--
ALTER TABLE `found_persons`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `missing_persons`
--
ALTER TABLE `missing_persons`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `found_persons`
--
ALTER TABLE `found_persons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `missing_persons`
--
ALTER TABLE `missing_persons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
