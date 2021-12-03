-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Dec 03, 2021 at 04:21 PM
-- Server version: 8.0.25
-- PHP Version: 7.4.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `n98211tb`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`n98211tb`@`localhost` PROCEDURE `st_names_scores_fail` ()  SELECT users.name AS 'Name', quiz_attempts.score * 100 AS 'Score percentage'
FROM quiz_attempts
INNER JOIN users
ON quiz_attempts.uid = users.uid
WHERE quiz_attempts.score < 0.4$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `quizzes`
--

CREATE TABLE `quizzes` (
  `id` int NOT NULL,
  `author_uid` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `duration` int NOT NULL,
  `available` int NOT NULL,
  `modifiable` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `quizzes`
--

INSERT INTO `quizzes` (`id`, `author_uid`, `title`, `duration`, `available`, `modifiable`) VALUES
(16, 'uid8', 'Ocean quiz', 5, 1, 1),
(26, 'su1234', 'My first quiz', 10, 1, 1),
(27, 'uid8', 'Secret quiz', 1, 0, 0);

--
-- Triggers `quizzes`
--
DELIMITER $$
CREATE TRIGGER `quiz_delete_log` AFTER DELETE ON `quizzes` FOR EACH ROW -- Logs author, not modifier
-- This is corrected in the webpage
INSERT INTO 
quiz_deletions (quiz_id, uid, date_deleted)
VALUES (OLD.id, OLD.author_uid, NOW())
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_attempts`
--

CREATE TABLE `quiz_attempts` (
  `id` int NOT NULL,
  `uid` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `quiz_id` int NOT NULL,
  `score` double NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `quiz_attempts`
--

INSERT INTO `quiz_attempts` (`id`, `uid`, `quiz_id`, `score`, `date`) VALUES
(10, 'su1234', 26, 0.33333333333333, '2021-12-03'),
(11, 'st1234', 16, 0.8, '2021-12-03'),
(12, 'st1234', 26, 1, '2021-12-03');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_deletions`
--

CREATE TABLE `quiz_deletions` (
  `quiz_id` int NOT NULL,
  `uid` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `date_deleted` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `quiz_deletions`
--

INSERT INTO `quiz_deletions` (`quiz_id`, `uid`, `date_deleted`) VALUES
(28, 'su1234', '2021-12-03 16:12:02');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_questions`
--

CREATE TABLE `quiz_questions` (
  `id` int NOT NULL,
  `quiz_id` int NOT NULL,
  `text` varchar(2047) COLLATE utf8_unicode_ci NOT NULL,
  `a` varchar(1023) COLLATE utf8_unicode_ci NOT NULL,
  `b` varchar(1023) COLLATE utf8_unicode_ci DEFAULT NULL,
  `c` varchar(1023) COLLATE utf8_unicode_ci DEFAULT NULL,
  `d` varchar(1023) COLLATE utf8_unicode_ci DEFAULT NULL,
  `answer` varchar(1) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `quiz_questions`
--

INSERT INTO `quiz_questions` (`id`, `quiz_id`, `text`, `a`, `b`, `c`, `d`, `answer`) VALUES
(7, 16, 'Do octopuses have tentacles?', 'yes', 'no', NULL, NULL, 'b'),
(8, 16, 'How many hearts do octopuses have?', '1', '2', '3', '8', 'c'),
(9, 16, 'What is the largest sea turtle in the world?', 'Loggerhead', 'Kemps ridley', 'Leatherback', 'Hawksbill', 'c'),
(23, 16, 'What is the biggest animal on earth?', 'White shark', 'African elephant', 'Blue whale', NULL, 'c'),
(24, 26, '1+1', '2', '3', NULL, '-1', 'a'),
(25, 26, 'In which base is ln?', '10', '2', 'e', NULL, 'c'),
(26, 26, 'In which base is lg?', '10', '2', 'e', NULL, 'a'),
(27, 16, 'Do you love sea animals?', 'yes', 'no', NULL, NULL, 'a'),
(28, 27, 'Can you take this quiz?', 'yes', NULL, NULL, NULL, 'a');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `uid` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `is_staff` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`uid`, `name`, `password`, `is_staff`) VALUES
('N98211TB', 'd', '$2y$10$xabQu8fMss1Q50gP4df/RuXxHorPS58TI0xW47fapTg0dleB/WcpC', 1),
('st1234', 'John Doe', '$2y$10$y5hwjTf1f0KP0ry1A6OpnO2FR21pZsiqzB/Ogh4sl4.7f7erJ/fve', 0),
('su1234', 'John Doe Lecturer', '$2y$10$YoFWNXFCwe9v5lqd27IJK.HRlbew/Xs/dCAaOyakgt5eutUpUsdjm', 1),
('uid8', 'nume', '$2y$10$M5F0JuK2HAzndRxPuYoIOu.Wy40PU3HmyJtebJf7j7t.MpzYS5qXG', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `author_uid` (`author_uid`);

--
-- Indexes for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uid` (`uid`),
  ADD KEY `quiz_id` (`quiz_id`);

--
-- Indexes for table `quiz_deletions`
--
ALTER TABLE `quiz_deletions`
  ADD PRIMARY KEY (`quiz_id`),
  ADD KEY `uid` (`uid`);

--
-- Indexes for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quiz_questions_ibfk_1` (`quiz_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`uid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `quizzes`
--
ALTER TABLE `quizzes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD CONSTRAINT `quizzes_ibfk_1` FOREIGN KEY (`author_uid`) REFERENCES `users` (`uid`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD CONSTRAINT `quiz_attempts_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `users` (`uid`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `quiz_attempts_ibfk_2` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `quiz_deletions`
--
ALTER TABLE `quiz_deletions`
  ADD CONSTRAINT `quiz_deletions_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `users` (`uid`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD CONSTRAINT `quiz_questions_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT;
COMMIT;
