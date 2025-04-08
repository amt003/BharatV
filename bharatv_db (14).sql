-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 08, 2025 at 05:52 AM
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
-- Database: `bharatv_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `candidate_applications`
--

CREATE TABLE `candidate_applications` (
  `application_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `party_id` int(11) DEFAULT NULL,
  `election_id` int(11) NOT NULL,
  `ward_id` int(11) NOT NULL,
  `application_form` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `application_party_approval` enum('pending','approved','rejected') DEFAULT 'pending',
  `application_ro_approval` enum('pending','approved','rejected') DEFAULT 'pending',
  `independent_party_symbol` varchar(100) DEFAULT NULL,
  `independent_party_name` varchar(100) DEFAULT NULL,
  `application_type` enum('party','independent') NOT NULL DEFAULT 'party'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `candidate_applications`
--

INSERT INTO `candidate_applications` (`application_id`, `id`, `party_id`, `election_id`, `ward_id`, `application_form`, `created_at`, `application_party_approval`, `application_ro_approval`, `independent_party_symbol`, `independent_party_name`, `application_type`) VALUES
(3, 5, 4, 21, 2, '{\"full_name\":\"James Joy\",\"age\":\"33\",\"phone\":\"7658423187\",\"education\":\"B.COM\",\"address\":\"Shanthibavanam House\\r\\nThidanadu\",\"occupation\":\"Bank Employee\",\"political_experience\":\"NIL\",\"profile_photo\":\"67cd883cf2c0b_profile2.jpeg\",\"aadhar_proof\":\"67cd883cf2f1e_13559_QmGlQD_Feasibility Study for BharatV  Election Voting System .pdf\",\"application_type\":\"party\",\"independent_party_name\":null,\"independent_party_symbol\":null}', '2025-03-09 12:23:24', 'approved', 'approved', NULL, NULL, 'party'),
(5, 3, 3, 21, 2, '{\"full_name\":\"John Joseph \",\"age\":\"30\",\"phone\":\"8960785402\",\"education\":\"B.SC\",\"address\":\"Kallarikal House \\r\\nThidanadu\",\"occupation\":\"Chemist\",\"political_experience\":\"NIL\",\"profile_photo\":\"67d11c68e1c7f_freepik__the-style-is-candid-image-photography-with-natural__67095.jpeg\",\"aadhar_proof\":\"67d11c68e1e00_13559_QmGlQD_Feasibility Study for BharatV  Election Voting System .pdf\",\"application_type\":\"party\",\"independent_party_name\":null,\"independent_party_symbol\":null}', '2025-03-12 05:32:24', 'approved', 'approved', NULL, NULL, 'party'),
(6, 4, 3, 22, 2, '{\"full_name\":\"Rohith R Nair\",\"age\":\"32\",\"phone\":\"9875364729\",\"education\":\"B.PHARM\",\"address\":\"Mannani House\\r\\nThidanadu\",\"occupation\":\"Pharmacist\",\"political_experience\":\"2 years as \",\"profile_photo\":\"67dfec5c04135_67dd362b2d82e-profile4.jpeg\",\"aadhar_proof\":\"67dfec5c04353_67a18f19719e9_6325658e1e1eb27f505ebd20208f3f49.pdf\",\"application_type\":\"party\",\"independent_party_name\":null,\"independent_party_symbol\":null}', '2025-03-23 11:11:24', 'approved', 'approved', NULL, NULL, 'party'),
(16, 6, NULL, 22, 2, '{\"full_name\":\"Alen Kuriakose\",\"age\":\"31\",\"phone\":\"8764392345\",\"education\":\"BA .LLB\",\"address\":\"Nedumthakadiyel house\\r\\nThidanadu\",\"occupation\":\"Lawyer\",\"political_experience\":\"NIL\",\"profile_photo\":\"67e01208492dc_picture.jpg.jpg\",\"aadhar_proof\":\"67e01208494ab_Election_Report (10).pdf\",\"application_type\":\"independent\",\"independent_party_name\":\"HDC\",\"independent_party_symbol\":\"diagram.png\"}', '2025-03-23 13:52:08', 'approved', 'rejected', 'diagram.png', 'HDC', 'independent'),
(19, 5, 2, 22, 2, '{\"full_name\":\"James Joy\",\"age\":\"32\",\"phone\":\"7658423187\",\"education\":\"B.COM\",\"address\":\"Shanthibhavanam House\\r\\nThidanadu\",\"occupation\":\"Bank Employee\",\"political_experience\":\"NIL\",\"profile_photo\":\"67e0f7a0233e3_profile2.jpeg\",\"aadhar_proof\":\"67e0f7a0234fb_Election_Report (14).pdf\",\"application_type\":\"party\",\"independent_party_name\":null,\"independent_party_symbol\":null}', '2025-03-24 06:11:44', 'rejected', 'pending', NULL, NULL, 'party'),
(20, 1, 4, 25, 1, '{\"full_name\":\"Abey Mathew\",\"age\":\"29\",\"phone\":\"8606005740\",\"education\":\"B.COM\",\"address\":\"Thakadiel House\\r\\nAmparanirappel\",\"occupation\":\"Bank Employee\",\"political_experience\":\"NIL\",\"profile_photo\":\"67f493c751dae_profile3.jpg\",\"aadhar_proof\":\"67f493c752648_Election_Report (12).pdf\",\"application_type\":\"party\",\"independent_party_name\":null,\"independent_party_symbol\":null}', '2025-04-08 03:11:03', 'approved', 'pending', NULL, NULL, 'party');

-- --------------------------------------------------------

--
-- Table structure for table `contesting_candidates`
--

CREATE TABLE `contesting_candidates` (
  `contesting_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `party_id` int(11) DEFAULT NULL,
  `ward_id` int(11) NOT NULL,
  `election_id` int(11) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `application_type` enum('party','independent') DEFAULT 'party',
  `independent_party_name` varchar(100) DEFAULT NULL,
  `independent_party_symbol` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contesting_candidates`
--

INSERT INTO `contesting_candidates` (`contesting_id`, `id`, `party_id`, `ward_id`, `election_id`, `added_at`, `application_type`, `independent_party_name`, `independent_party_symbol`) VALUES
(7, 5, 4, 2, 21, '2025-03-09 13:14:43', 'party', NULL, NULL),
(8, 3, 3, 2, 21, '2025-03-12 06:45:38', 'party', NULL, NULL),
(9, 4, 3, 2, 22, '2025-03-23 13:59:06', 'party', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `elections`
--

CREATE TABLE `elections` (
  `election_id` int(11) NOT NULL,
  `Election_title` varchar(100) NOT NULL,
  `Description` text DEFAULT NULL,
  `ward_ids` text NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('scheduled','ongoing','completed') NOT NULL DEFAULT 'scheduled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `result_status` enum('pending','published') NOT NULL DEFAULT 'pending',
  `results_published_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `elections`
--

INSERT INTO `elections` (`election_id`, `Election_title`, `Description`, `ward_ids`, `start_date`, `end_date`, `status`, `created_at`, `result_status`, `results_published_date`) VALUES
(21, 'Ward Election ', 'Thidanadu Grama Panchayth Ward Elections-Thidanadu Ward ', '2', '2025-03-12', '2025-03-13', 'completed', '2025-03-08 08:14:56', 'published', '2025-04-01 10:19:55'),
(22, 'ThidanaduWard Election', 'Thidanadu Grama Panchayth- Thidanadu Ward Election', '2', '2025-04-05', '2025-04-13', 'ongoing', '2025-03-22 13:24:01', 'pending', NULL),
(25, 'Ward Election', 'Thidanadu Grama Panchayth-Amparanirappel Ward Election', '1', '2025-04-30', '2025-04-30', 'scheduled', '2025-04-08 02:43:11', 'pending', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `parties`
--

CREATE TABLE `parties` (
  `party_id` int(11) NOT NULL,
  `party_name` varchar(100) NOT NULL,
  `party_symbol` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parties`
--

INSERT INTO `parties` (`party_id`, `party_name`, `party_symbol`, `password`, `email`) VALUES
(1, 'Independent Candidate', '', '', ''),
(2, 'IAC', 'congress_symbol.jpg', '$2y$10$IEuiE9adiyhPR42rSPaPi.cTom7IYBr7EwyQbnlbsfcVgr6z0U3m6', 'iac@gmail.com'),
(3, 'FCU', 'bjp_symbol.jpg', '$2y$10$EGDBhUSr8VvArkSHu1q9ROIIiYMpcQ/s6RkW.SF1nISmW1qPCB55e', 'fuc@gmail.com'),
(4, 'JUC', 'cpi_symbol.png', '$2y$10$.cw2t0gQ27ztHrGVb4qaUOqO2pW1w2/qvDtCP6PlBBDny94IDkwh6', 'juc@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `results`
--

CREATE TABLE `results` (
  `result_id` int(11) NOT NULL,
  `election_id` int(11) NOT NULL,
  `ward_id` int(11) NOT NULL,
  `contesting_id` int(11) NOT NULL,
  `votes_received` int(11) NOT NULL,
  `is_winner` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `results`
--

INSERT INTO `results` (`result_id`, `election_id`, `ward_id`, `contesting_id`, `votes_received`, `is_winner`) VALUES
(1, 21, 2, 7, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('voter','candidate','admin') NOT NULL,
  `aadhaar_number` varchar(12) NOT NULL,
  `aadhaar_file` varchar(255) NOT NULL,
  `voter_id_proof` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `phone` varchar(15) NOT NULL,
  `ward_id` int(11) NOT NULL,
  `dob` date NOT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `approved_by_admin` tinyint(1) DEFAULT 0,
  `profile_photo` varchar(255) DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `aadhaar_number`, `aadhaar_file`, `voter_id_proof`, `address`, `phone`, `ward_id`, `dob`, `email_verified`, `created_at`, `updated_at`, `approved_by_admin`, `profile_photo`, `rejection_reason`) VALUES
(1, 'Abey Mathew', 'abeymathew2003@gmail.com', '$2y$10$ERpyhGWLjYxtkxzWMWffEOTMCgoTHBS3FjC4oNdaRstxlD/Yzs5va', 'candidate', '811134560928', 'uploads/67b5d4f0a6fe8_2a00e97639e178dab3df20e6c39e2cf1.pdf', '67dea768be371_voter_id_fd2f679e2cfc38dd7a683cbb45d38a12.pdf', 'Thakadiel House\r\nAmparanirappel', '8606005740', 1, '1994-10-23', 1, '2025-02-19 12:57:02', '2025-04-08 03:22:21', 1, '67bec6426a0c9-hero.jpeg.jpeg', NULL),
(2, 'Alphons Mathew', 'alphonsmathew203@gmail.com', '$2y$10$u1b7MpKYCU4OTtQAcZTFHefuMbzdLvLeNvpnHathBajkc7Uxs6mhO', 'voter', '907365762812', 'uploads/67b5dd3a19420_d9a672eeec6e2430d30329d44f9be601.pdf', '67dea6e9d14c5_voter_id_4640ebe79b9e503571b1d86b42e40c42.pdf', 'Mulakal House \r\nPoovathodu', '9447573196', 3, '2001-10-23', 1, '2025-02-19 13:32:17', '2025-04-05 16:36:16', 1, '67de52dda9aa2-profile3.jpg', NULL),
(3, 'John Joseph John', 'johnjosephjohn748@gmail.com', '$2y$10$QMlMINK6qlrwpATSFMo0uewBYPovRjP.SYV0Mo/YBy0Ymet3VDnf2', 'voter', '975860387421', 'uploads/67b5ff6b91af7_6325658e1e1eb27f505ebd20208f3f49.pdf', '67dea7945d2b4_voter_id_edb4405ff9119d418b770a95ef872ddd.pdf', 'Kallarikal House  \r\nThidanadu', '8960785402', 2, '1995-08-09', 1, '2025-02-19 15:58:05', '2025-04-03 17:25:48', 1, '67d11b707e399-hero.jpeg.jpeg', NULL),
(4, 'Rohith R Nair', 'rohithrnair2027@mca.ajce.in', '$2y$10$/HcSyFfN5ouHjYOOJiz8P.W6wXJp3xYpDioD7TJaG2o7Sn46imXEW', 'candidate', '876984672456', 'uploads/67b6f8df9f011_2a00e97639e178dab3df20e6c39e2cf1.pdf', '67dea7225d592_voter_id_9f36f90addb3d9bc03f4d138524a42c7.pdf', 'Mannani House\r\nThidanadu', '9875364729', 2, '1994-02-23', 1, '2025-02-20 09:43:10', '2025-03-22 12:03:46', 1, '67de531b59b18-profile4.jpeg', NULL),
(5, 'James Joy', 'abeymathew2027@mca.ajce.in', '$2y$10$ASnIziqBy5fvjaxtBgTss.YJRbvTO2HXhxHhvHVfbJgKy4zd8387m', 'voter', '789065462438', 'uploads/67bd0b6ab9f58_d9a672eeec6e2430d30329d44f9be601.pdf', '67dea7d4ea36c_voter_id_8f40e054d61752f01ecd2fc9d635a5ed.pdf', 'Shantibhavanam House\r\nThidanadu', '7658423187', 2, '1993-03-21', 1, '2025-02-25 00:15:32', '2025-04-06 11:44:09', 1, '67bf2206e0c35-profile2.jpeg', NULL),
(6, 'Alen Kuriakose', 'alenkuriakose2027@mca.ajce.in', '$2y$10$tDbxWfEcrlc7NlNCxOnuI.Wfgp0liicgYL1zURv1OEEpvAQgRZ6pS', 'voter', '911273638497', 'uploads/67d13ecb52bcd_cbba3bc3a3635ae2354e205d32bc414a.pdf', '67dea63ced7af_voter_id_6325658e1e1eb27f505ebd20208f3f49.pdf', 'Nedumthakadiyel House\r\nThidanadu', '8458936475', 2, '1994-12-09', 1, '2025-03-12 07:59:56', '2025-04-01 14:01:39', 1, '67ebaa950d870-profile4.jpg', NULL),
(7, 'Melbin Sabu', 'melbinsabu2027@mca.ajce.in', '$2y$10$dCplPnnvA/6dxVdj/I.hFOek1uMzzho8/TyvdKJmfL7IBbCD2P0tC', 'voter', '819273947582', 'uploads/67da84c2bd54b_3e3e14e8201a42e68e2f65e3b7d002af.pdf', '67dea7f625189_voter_id_3e3e14e8201a42e68e2f65e3b7d002af.pdf', 'Moozhayil House\r\nAmparanirappel', '9878493648', 1, '2003-04-23', 1, '2025-03-19 08:48:41', '2025-03-25 13:19:33', 1, '67e008979c98e-profile4.jpg', NULL),
(8, 'Rehan S Nair', 'rehansnair@gmail.com', '$2y$10$PPx.O2z1CR/KHOmSHYJb7OL4j1n2h1ORfFSNVEzY9VFpaegAjtxX2', 'candidate', '873947123043', 'uploads/67e3b856871e8_968faf9f411b6be9f9df379b789709a8.pdf', 'uploads/election reprot 19.pdf', 'Neduvayil House\r\nAmparanirappel', '8746592381', 1, '1994-03-24', 1, '2025-03-26 08:20:41', '2025-04-02 08:40:46', 1, '67ecf768e7bc6-profile6.jpeg', NULL),
(11, 'admin', 'bharatv2k25@gmail.com', '$2y$10$Bj/K2Im0LIK256kLw91uSuXF2cRhYOpxE5JJEUVedtv1pRNLH8LMO', 'admin', '', '', '', '', '', 1, '0000-00-00', 1, '2025-03-27 10:54:53', '2025-04-01 14:07:52', 0, NULL, NULL),
(12, 'Dennis Jacob', 'dennisjacob2027@mca.ajce.in', '$2y$10$7IH7WEcCC/e2eFK3sYzBjeafyIuYWx5uqvLjWN4g5FnMTHGdVOEku', 'candidate', '981112345212', 'uploads/67ee2d4bc2b70_87d384c59939937d995b9a6df9f8dde7.pdf', 'uploads/67ee2d4bc2e31_voter_id_6325658e1e1eb27f505ebd20208f3f49.pdf', 'Punnathanathu House\r\nAmparanirappel', '8756302931', 1, '1993-11-28', 1, '2025-04-03 06:42:56', '2025-04-04 14:57:54', 1, NULL, NULL),
(15, 'Anet Mathew', 'amt93489@gmail.com', '$2y$10$LFPpUzZmCSF7O6UY5VdvJOniycRcEdmfu/DQF5g991HyMIolcu9TW', 'candidate', '789203746819', 'uploads/67f27d8d2480d_7d588a0d2dd4911b86514884d497983d.pdf', 'uploads/67f27d8d26d99_voter_id_0bad8a16d26e8e14c641614d1f953379.pdf', 'Thakadiel House\r\nAmparanirappel', '8281192739', 1, '1993-03-23', 1, '2025-04-06 13:12:07', '2025-04-06 13:26:37', -1, NULL, 'documents not original');

-- --------------------------------------------------------

--
-- Table structure for table `votes`
--

CREATE TABLE `votes` (
  `vote_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `contesting_id` int(11) NOT NULL,
  `casted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `election_id` int(11) NOT NULL,
  `ward_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `votes`
--

INSERT INTO `votes` (`vote_id`, `id`, `contesting_id`, `casted_at`, `election_id`, `ward_id`) VALUES
(1, 4, 7, '2025-03-12 09:11:34', 21, 2),
(22, 5, 9, '2025-04-06 12:46:40', 22, 2);

-- --------------------------------------------------------

--
-- Table structure for table `wards`
--

CREATE TABLE `wards` (
  `ward_id` int(11) NOT NULL,
  `ward_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wards`
--

INSERT INTO `wards` (`ward_id`, `ward_name`) VALUES
(1, 'Amparanirappel'),
(4, 'Chemalamattom'),
(3, 'Poovathodu'),
(2, 'Thidanadu');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `candidate_applications`
--
ALTER TABLE `candidate_applications`
  ADD PRIMARY KEY (`application_id`),
  ADD KEY `id` (`id`),
  ADD KEY `party_id` (`party_id`),
  ADD KEY `election_id` (`election_id`),
  ADD KEY `ward_id` (`ward_id`);

--
-- Indexes for table `contesting_candidates`
--
ALTER TABLE `contesting_candidates`
  ADD PRIMARY KEY (`contesting_id`),
  ADD KEY `id` (`id`),
  ADD KEY `ward_id` (`ward_id`),
  ADD KEY `election_id` (`election_id`),
  ADD KEY `party_id` (`party_id`);

--
-- Indexes for table `elections`
--
ALTER TABLE `elections`
  ADD PRIMARY KEY (`election_id`);

--
-- Indexes for table `parties`
--
ALTER TABLE `parties`
  ADD PRIMARY KEY (`party_id`),
  ADD UNIQUE KEY `party_name` (`party_name`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `results`
--
ALTER TABLE `results`
  ADD PRIMARY KEY (`result_id`),
  ADD KEY `election_id` (`election_id`),
  ADD KEY `ward_id` (`ward_id`),
  ADD KEY `contesting_id` (`contesting_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `aadhaar_number` (`aadhaar_number`),
  ADD KEY `ward_id` (`ward_id`);

--
-- Indexes for table `votes`
--
ALTER TABLE `votes`
  ADD PRIMARY KEY (`vote_id`),
  ADD UNIQUE KEY `unique_vote` (`id`,`contesting_id`),
  ADD KEY `contesting_id` (`contesting_id`),
  ADD KEY `fk_votes_election` (`election_id`),
  ADD KEY `ward_id` (`ward_id`);

--
-- Indexes for table `wards`
--
ALTER TABLE `wards`
  ADD PRIMARY KEY (`ward_id`),
  ADD UNIQUE KEY `ward_name` (`ward_name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `candidate_applications`
--
ALTER TABLE `candidate_applications`
  MODIFY `application_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `contesting_candidates`
--
ALTER TABLE `contesting_candidates`
  MODIFY `contesting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `elections`
--
ALTER TABLE `elections`
  MODIFY `election_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `parties`
--
ALTER TABLE `parties`
  MODIFY `party_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `results`
--
ALTER TABLE `results`
  MODIFY `result_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `votes`
--
ALTER TABLE `votes`
  MODIFY `vote_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `wards`
--
ALTER TABLE `wards`
  MODIFY `ward_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `candidate_applications`
--
ALTER TABLE `candidate_applications`
  ADD CONSTRAINT `candidate_applications_ibfk_1` FOREIGN KEY (`id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `candidate_applications_ibfk_2` FOREIGN KEY (`party_id`) REFERENCES `parties` (`party_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `candidate_applications_ibfk_3` FOREIGN KEY (`election_id`) REFERENCES `elections` (`election_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `candidate_applications_ibfk_4` FOREIGN KEY (`ward_id`) REFERENCES `wards` (`ward_id`) ON DELETE CASCADE;

--
-- Constraints for table `contesting_candidates`
--
ALTER TABLE `contesting_candidates`
  ADD CONSTRAINT `contesting_candidates_ibfk_1` FOREIGN KEY (`id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `contesting_candidates_ibfk_2` FOREIGN KEY (`party_id`) REFERENCES `parties` (`party_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `contesting_candidates_ibfk_3` FOREIGN KEY (`ward_id`) REFERENCES `wards` (`ward_id`),
  ADD CONSTRAINT `contesting_candidates_ibfk_4` FOREIGN KEY (`election_id`) REFERENCES `elections` (`election_id`),
  ADD CONSTRAINT `contesting_candidates_ibfk_5` FOREIGN KEY (`party_id`) REFERENCES `parties` (`party_id`);

--
-- Constraints for table `results`
--
ALTER TABLE `results`
  ADD CONSTRAINT `results_ibfk_1` FOREIGN KEY (`election_id`) REFERENCES `elections` (`election_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `results_ibfk_2` FOREIGN KEY (`ward_id`) REFERENCES `wards` (`ward_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `results_ibfk_3` FOREIGN KEY (`contesting_id`) REFERENCES `contesting_candidates` (`contesting_id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`ward_id`) REFERENCES `wards` (`ward_id`);

--
-- Constraints for table `votes`
--
ALTER TABLE `votes`
  ADD CONSTRAINT `fk_votes_election` FOREIGN KEY (`election_id`) REFERENCES `elections` (`election_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `votes_ibfk_1` FOREIGN KEY (`id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `votes_ibfk_2` FOREIGN KEY (`contesting_id`) REFERENCES `contesting_candidates` (`contesting_id`),
  ADD CONSTRAINT `votes_ibfk_3` FOREIGN KEY (`ward_id`) REFERENCES `wards` (`ward_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
