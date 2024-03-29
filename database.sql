-- phpMyAdmin SQL Dump
-- version 4.6.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 27, 2017 at 06:54 PM
-- Server version: 5.7.14
-- PHP Version: 5.6.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
-- Hard drop
SET foreign_key_checks = 0;
DROP TABLE IF EXISTS `favorite_room`;
DROP TABLE IF EXISTS `message`;
DROP TABLE IF EXISTS `request`;
DROP TABLE IF EXISTS `room`;
DROP TABLE IF EXISTS `room_members`;
DROP TABLE IF EXISTS `user`;
DROP TABLE IF EXISTS `world`;
SET foreign_key_checks = 1;

--
-- Database: `trust`
--

-- --------------------------------------------------------

--
-- Table structure for table `favorite_room`
--

CREATE TABLE `favorite_room` (
  `id` int(10) UNSIGNED NOT NULL,
  `room_key` int(10) UNSIGNED NOT NULL,
  `user_key` int(10) UNSIGNED NOT NULL,
  `created` timestamp NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `message`
--

CREATE TABLE `message` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_key` int(10) UNSIGNED NOT NULL,
  `room_key` int(10) UNSIGNED NOT NULL,
  `username` varchar(64) NOT NULL,
  `color` varchar(8) NOT NULL,
  `ip` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `report` int(10) UNSIGNED NOT NULL,
  `timestamp` timestamp NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `request`
--

CREATE TABLE `request` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_flag` bit(1) NOT NULL,
  `user_key` int(10) UNSIGNED NOT NULL,
  `ip` varchar(100) NOT NULL,
  `api_flag` bit(1) NOT NULL,
  `route_url` varchar(1000) NOT NULL,
  `full_url` varchar(1000) NOT NULL,
  `get_data` text NOT NULL,
  `post_data` text NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `room`
--

CREATE TABLE `room` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `user_key` int(10) UNSIGNED NULL,
  `receiving_user_key` int(10) UNSIGNED NULL,
  `is_pm` bit(1) NOT NULL,
  `is_base` bit(1) NOT NULL,
  `room_passcode` varchar(100) NULL,
  `user_unread` bit(1) NULL,
  `receiving_user_unread` bit(1) NULL,
  `lng` float NOT NULL,
  `lat` float NOT NULL,
  `archived` bit(1) NOT NULL,
  `last_message_time` timestamp NOT NULL,
  `created` timestamp NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `room_members`
--

CREATE TABLE `room_members` (
  `id` int(10) UNSIGNED NOT NULL,
  `room_key` int(10) UNSIGNED NOT NULL,
  `user_key` int(10) UNSIGNED NOT NULL,
  `created` timestamp NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(100) NOT NULL,
  `room_key` int(10) UNSIGNED NULL,
  `last_load` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cash` int(11) UNSIGNED NOT NULL,
  `cash_received` int(11) UNSIGNED NOT NULL,
  `time_served_minutes` int(11) UNSIGNED NOT NULL,
  `good_reputation` int(10) UNSIGNED NOT NULL,
  `bad_reputation` int(10) UNSIGNED NOT NULL,
  `jobs_led` int(10) UNSIGNED NOT NULL,
  `bails_paid_count` int(10) UNSIGNED NOT NULL,
  `bails_paid_amount` int(10) UNSIGNED NOT NULL,
  `jobs_failed` int(10) UNSIGNED NOT NULL,
  `jobs_success` int(10) UNSIGNED NOT NULL,
  `arrested` int(10) UNSIGNED NOT NULL,
  `in_jail_time_minutes` int(11) UNSIGNED NOT NULL,
  `fines_paid` int(11) UNSIGNED NOT NULL,
  `payouts_stolen` int(11) UNSIGNED NOT NULL,
  `crews_joined` int(10) UNSIGNED NOT NULL,
  `kills` int(10) UNSIGNED NOT NULL,
  `skill_thief` int(10) UNSIGNED NOT NULL,
  `skill_muscle` int(10) UNSIGNED NOT NULL,
  `skill_driver` int(10) UNSIGNED NOT NULL,
  `skill_conman` int(10) UNSIGNED NOT NULL,
  `skill_cracker` int(10) UNSIGNED NOT NULL,
  `skill_hacker` int(10) UNSIGNED NOT NULL,
  `skill_fixer` int(10) UNSIGNED NOT NULL,
  `is_dead` bit(1) NOT NULL,
  `is_in_jail` bit(1) NOT NULL,
  `jail_sentence_end_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,

  `ab_test` varchar(100) NOT NULL,
  `color` varchar(8) NOT NULL,
  `location` varchar(100) NOT NULL,
  `ip` varchar(100) NOT NULL,
  `email` varchar(250) NOT NULL,
  `api_key` varchar(250) NOT NULL,
  `password` varchar(250) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Indexes for dumped tables
--

--
-- Indexes for table `favorite_room`
--
ALTER TABLE `favorite_room`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `request`
--
ALTER TABLE `request`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `room`
--
ALTER TABLE `room`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `room_members`
--
ALTER TABLE `room_members`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `favorite_room`
--
ALTER TABLE `favorite_room`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `message`
--
ALTER TABLE `message`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `request`
--
ALTER TABLE `request`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `room`
--
ALTER TABLE `room`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `room_members`
--
ALTER TABLE `room_members`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


ALTER TABLE `user`
  ADD CONSTRAINT `user_room_cascade` FOREIGN KEY (`room_key`) REFERENCES `room` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `favorite_room`
  ADD CONSTRAINT `favorite_rooms_room_key_cascade` FOREIGN KEY (`room_key`) REFERENCES `room` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `room_members`
  ADD CONSTRAINT `room_members_room_key_cascade` FOREIGN KEY (`room_key`) REFERENCES `room` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `message`
  ADD CONSTRAINT `message_room_key_cascade` FOREIGN KEY (`room_key`) REFERENCES `room` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;