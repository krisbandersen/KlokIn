-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Vært: localhost
-- Genereringstid: 05. 11 2024 kl. 18:16:39
-- Serverversion: 10.4.28-MariaDB
-- PHP-version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `klokindb`
--

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `organization_id` int(10) UNSIGNED DEFAULT NULL,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `employee_number` int(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data dump for tabellen `employees`
--

INSERT INTO `employees` (`id`, `organization_id`, `firstname`, `lastname`, `department`, `email`, `employee_number`, `created_at`, `updated_at`) VALUES
(8, 1, 'Kris', 'Andersen', NULL, 'kriskingo2010@gmail.com', 150, '2024-09-30 17:57:29', '2024-09-30 17:57:29'),
(13, 1, 'Sebastian Høxbro', 'Pedersen', NULL, 'allaherenluder@gmail.com', 130, '2024-10-01 21:51:21', '2024-10-01 21:51:21'),
(14, 1, 'Kris Bjørnfeldt', 'Andersen', NULL, 'kris@test.dk', 109, '2024-10-08 19:53:07', '2024-10-08 19:53:07'),
(18, 1, 'Daniel', 'Bune', NULL, 'danielbune12@gmail.com', 101, '2024-10-09 08:24:53', '2024-10-09 08:24:53'),
(23, 1, 'kris', 'test', NULL, 'test@test.dk', 104, '2024-10-09 08:34:30', '2024-10-09 08:34:30'),
(26, 1, 'Hej', 'hej', NULL, 'kkg@gkf.dk', 123, '2024-10-09 13:51:25', '2024-10-09 13:51:25'),
(28, 1, 'Dorte Fink', 'Isaksen', NULL, 'krisbandersen05@gmail.com', 119, '2024-10-09 13:55:02', '2024-10-09 13:55:02'),
(30, 1, 'Kris', 'kris', NULL, 'kris@13123dksd.dk', 18, '2024-10-09 15:12:53', '2024-10-09 15:12:53'),
(33, 1, 'Emily', 'Davis', 'Finance', 'emily.davis@example.com', 1004, '2024-10-09 15:15:27', '2024-10-09 15:15:27'),
(34, 1, 'Daniel', 'Martinez', 'IT', 'daniel.martinez@example.com', 1005, '2024-10-09 15:15:27', '2024-10-09 15:15:27'),
(35, 1, 'Laura', 'Garcia', 'Support', 'laura.garcia@example.com', 1006, '2024-10-09 15:15:27', '2024-10-09 15:15:27'),
(36, 1, 'James', 'Wilson', 'Engineering', 'james.wilson@example.com', 1007, '2024-10-09 15:15:27', '2024-10-09 15:15:27'),
(37, 1, 'Sophia', 'Moore', 'Legal', 'sophia.moore@example.com', 1008, '2024-10-09 15:15:27', '2024-10-09 15:15:27'),
(38, 1, 'David', 'Taylor', 'Operations', 'david.taylor@example.com', 1009, '2024-10-09 15:15:27', '2024-10-09 15:15:27'),
(39, 1, 'Olivia', 'Anderson', 'Logistics', 'olivia.anderson@example.com', 1010, '2024-10-09 15:15:27', '2024-10-09 15:15:27'),
(40, 1, 'John', 'Doe', 'Sales', 'john.doe@example.com', 1001, '2024-10-09 15:15:27', '2024-10-09 15:15:27'),
(41, 1, 'John', 'Doe', 'Sales', 'john.doe@example.com', 1012, '2024-10-10 14:12:33', '2024-10-10 14:12:33'),
(42, 1, 'Jane', 'Smith', 'HR', 'jane.smith@example.com', 1013, '2024-10-10 14:12:33', '2024-10-10 14:12:33'),
(43, 1, 'Tom', 'Johnson', 'IT', 'tom.johnson@example.com', 1014, '2024-10-10 14:12:33', '2024-10-10 14:12:33'),
(44, 1, 'Emily', 'Davis', 'Marketing', 'emily.davis@example.com', 1015, '2024-10-10 14:12:33', '2024-10-10 14:12:33'),
(45, 1, 'Michael', 'Miller', 'Finance', 'michael.miller@example.com', 1016, '2024-10-10 14:12:33', '2024-10-10 14:12:33'),
(46, 1, 'Sarah', 'Wilson', 'Sales', 'sarah.wilson@example.com', 1017, '2024-10-10 14:12:33', '2024-10-10 14:12:33'),
(47, 1, 'David', 'Brown', 'HR', 'david.brown@example.com', 1018, '2024-10-10 14:12:33', '2024-10-10 14:12:33'),
(48, 1, 'Anna', 'Jones', 'IT', 'anna.jones@example.com', 1019, '2024-10-10 14:12:33', '2024-10-10 14:12:33'),
(49, 1, 'James', 'Garcia', 'Marketing', 'james.garcia@example.com', 1020, '2024-10-10 14:12:33', '2024-10-10 14:12:33'),
(50, 1, 'Jessica', 'Martinez', 'Finance', 'jessica.martinez@example.com', 1021, '2024-10-10 14:12:33', '2024-10-10 14:12:33'),
(51, 1, 'Chris', 'Lopez', 'Sales', 'chris.lopez@example.com', 1022, '2024-10-10 14:12:33', '2024-10-10 14:12:33'),
(52, 1, 'Lisa', 'Hernandez', 'HR', 'lisa.hernandez@example.com', 1023, '2024-10-10 14:12:33', '2024-10-10 14:12:33'),
(53, 1, 'Paul', 'Gonzalez', 'IT', 'paul.gonzalez@example.com', 1024, '2024-10-10 14:12:33', '2024-10-10 14:12:33'),
(54, 1, 'Laura', 'Rodriguez', 'Marketing', 'laura.rodriguez@example.com', 1025, '2024-10-10 14:12:33', '2024-10-10 14:12:33'),
(55, 1, 'Daniel', 'Perez', 'Finance', 'daniel.perez@example.com', 1026, '2024-10-10 14:12:33', '2024-10-10 14:12:33'),
(56, 1, 'Ashley', 'Thomas', 'Sales', 'ashley.thomas@example.com', 1027, '2024-10-10 14:12:33', '2024-10-10 14:12:33'),
(57, 1, 'Brandon', 'Harris', 'HR', 'brandon.harris@example.com', 1028, '2024-10-10 14:12:33', '2024-10-10 14:12:33'),
(58, 1, 'Sophia', 'Clark', 'IT', 'sophia.clark@example.com', 1029, '2024-10-10 14:12:33', '2024-10-10 14:12:33'),
(59, 1, 'Kevin', 'Lewis', 'Marketing', 'kevin.lewis@example.com', 1030, '2024-10-10 14:12:33', '2024-10-10 14:12:33'),
(60, 1, 'Rachel', 'Walker', 'Finance', 'rachel.walker@example.com', 1031, '2024-10-10 14:12:33', '2024-10-10 14:12:33'),
(61, 1, 'Brian', 'Hall', 'Sales', 'brian.hall@example.com', 1032, '2024-10-10 14:12:33', '2024-10-10 14:12:33'),
(62, 1, 'Olivia', 'Allen', 'HR', 'olivia.allen@example.com', 1033, '2024-10-10 14:12:33', '2024-10-10 14:12:33'),
(63, 1, 'Ethan', 'Young', 'IT', 'ethan.young@example.com', 1034, '2024-10-10 14:12:33', '2024-10-10 14:12:33'),
(64, 1, 'Mia', 'King', 'Marketing', 'mia.king@example.com', 1035, '2024-10-10 14:12:33', '2024-10-10 14:12:33'),
(65, 1, 'Nathan', 'Scott', 'Finance', 'nathan.scott@example.com', 1036, '2024-10-10 14:12:33', '2024-10-10 14:12:33'),
(66, 1, 'Grace', 'Green', 'Sales', 'grace.green@example.com', 1037, '2024-10-10 14:12:33', '2024-10-10 14:12:33'),
(67, 1, 'Zach', 'Baker', 'HR', 'zach.baker@example.com', 1038, '2024-10-10 14:12:33', '2024-10-10 14:12:33'),
(68, 1, 'Hailey', 'Adams', 'IT', 'hailey.adams@example.com', 1039, '2024-10-10 14:12:33', '2024-10-10 14:12:33'),
(69, 1, 'Ben', 'Nelson', 'Marketing', 'ben.nelson@example.com', 1040, '2024-10-10 14:12:33', '2024-10-10 14:12:33'),
(70, 1, 'Claire', 'Hill', 'Finance', 'claire.hill@example.com', 1041, '2024-10-10 14:12:33', '2024-10-10 14:12:33'),
(71, 1, 'Kyle', 'Ramirez', 'Sales', 'kyle.ramirez@example.com', 1042, '2024-10-10 14:12:33', '2024-10-10 14:12:33'),
(72, 1, 'Emma', 'Campbell', 'HR', 'emma.campbell@example.com', 1043, '2024-10-10 14:12:33', '2024-10-10 14:12:33'),
(73, 1, 'Luke', 'Mitchell', 'IT', 'luke.mitchell@example.com', 1044, '2024-10-10 14:12:33', '2024-10-10 14:12:33'),
(74, 1, 'Chloe', 'Roberts', 'Marketing', 'chloe.roberts@example.com', 1045, '2024-10-10 14:12:33', '2024-10-10 14:12:33'),
(75, 1, 'Alex', 'Carter', 'Finance', 'alex.carter@example.com', 1046, '2024-10-10 14:12:33', '2024-10-10 14:12:33'),
(76, 1, 'Julia', 'Phillips', 'Sales', 'julia.phillips@example.com', 1047, '2024-10-10 14:12:33', '2024-10-10 14:12:33'),
(77, 1, 'Isaac', 'Evans', 'HR', 'isaac.evans@example.com', 1048, '2024-10-10 14:12:33', '2024-10-10 14:12:33'),
(78, 1, 'Samantha', 'Turner', 'IT', 'samantha.turner@example.com', 1049, '2024-10-10 14:12:33', '2024-10-10 14:12:33'),
(79, 1, 'Adam', 'Torres', 'Marketing', 'adam.torres@example.com', 1050, '2024-10-10 14:12:33', '2024-10-10 14:12:33'),
(80, 1, 'Lily', 'Parker', 'Finance', 'lily.parker@example.com', 1051, '2024-10-10 14:12:33', '2024-10-10 14:12:33'),
(81, 1, 'Noah', 'Collins', 'Sales', 'noah.collins@example.com', 1052, '2024-10-10 14:12:33', '2024-10-10 14:12:33');

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `employee_tasks`
--

CREATE TABLE `employee_tasks` (
  `id` int(11) UNSIGNED NOT NULL,
  `employee_id` int(11) NOT NULL,
  `task_id` int(11) UNSIGNED NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `loginattempts`
--

CREATE TABLE `loginattempts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user` bigint(20) UNSIGNED DEFAULT NULL,
  `ip` varchar(255) DEFAULT NULL,
  `timestamp` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `organizations`
--

CREATE TABLE `organizations` (
  `id` int(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `label_name` varchar(255) DEFAULT NULL,
  `cvr` varchar(35) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `active_status` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data dump for tabellen `organizations`
--

INSERT INTO `organizations` (`id`, `name`, `label_name`, `cvr`, `address`, `contact_email`, `phone`, `created_at`, `active_status`) VALUES
(1, 'focusrengoering', 'Focus Rengøring', '25872134', 'Bredskifte Alé 13B', 'krisbandersen05@gmail.com', '61685837', '2024-09-23 07:03:17', 1);

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `requests`
--

CREATE TABLE `requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user` bigint(20) UNSIGNED DEFAULT NULL,
  `hash` varchar(255) DEFAULT NULL,
  `timestamp` int(10) UNSIGNED DEFAULT NULL,
  `type` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) UNSIGNED NOT NULL,
  `organization_id` int(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `start_time` timestamp NULL DEFAULT NULL,
  `end_time` timestamp NULL DEFAULT NULL,
  `completed` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data dump for tabellen `tasks`
--

INSERT INTO `tasks` (`id`, `organization_id`, `title`, `description`, `latitude`, `longitude`, `start_time`, `end_time`, `completed`, `created_at`, `updated_at`) VALUES
(36, 1, 'Dagmar Petersens gade 27.st', 'Her skal der rengøres godt og grundigt.\r\nDerudover skal der tages to vogne.\r\n2xSkurvogn = 1 time i alt. 2 Mennesker = 30 minutter i alt.', 56.16437354, 10.17266393, '2024-10-30 13:30:00', '2024-10-30 16:30:00', 0, '2024-10-30 21:17:00', '2024-10-30 21:17:00'),
(37, 1, 'Dollerupvej 4', 'Vask borde og døre af. Vask gulv af og husk at støvsuge gulvet.\r\n\r\nBrug i alt 5 timer.', 56.15626305, 10.18692923, '2024-11-08 06:30:00', '2024-11-08 11:30:00', 0, '2024-11-05 11:30:51', '2024-11-05 11:30:51');

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `task_assigned_employees`
--

CREATE TABLE `task_assigned_employees` (
  `task_id` int(11) UNSIGNED NOT NULL,
  `employee_id` int(11) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data dump for tabellen `task_assigned_employees`
--

INSERT INTO `task_assigned_employees` (`task_id`, `employee_id`, `assigned_at`) VALUES
(36, 8, '2024-10-30 21:17:00'),
(37, 8, '2024-11-05 11:30:51');

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `verified` tinyint(1) NOT NULL DEFAULT 0,
  `organization_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data dump for tabellen `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `verified`, `organization_id`) VALUES
(19, 'Kris Bjornfeldt Andersen', 'krisbandersen05@gmail.com', '$2y$10$eQPHyVZAMYqQ315SQpIkD.64ZWxxL/qYTdf2lg/a0vFR6lH.mPOiG', 1, 1);

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `work_sessions`
--

CREATE TABLE `work_sessions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` int(11) NOT NULL,
  `organization_id` int(20) UNSIGNED NOT NULL,
  `task_id` int(11) NOT NULL,
  `start_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `end_time` timestamp NULL DEFAULT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Begrænsninger for dumpede tabeller
--

--
-- Indeks for tabel `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_number` (`employee_number`),
  ADD UNIQUE KEY `unique_employee_number` (`organization_id`,`employee_number`);

--
-- Indeks for tabel `employee_tasks`
--
ALTER TABLE `employee_tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_employee_tasks_employee` (`employee_id`),
  ADD KEY `fk_employee_tasks_task` (`task_id`);

--
-- Indeks for tabel `loginattempts`
--
ALTER TABLE `loginattempts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indeks for tabel `organizations`
--
ALTER TABLE `organizations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cvr` (`cvr`);

--
-- Indeks for tabel `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indeks for tabel `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `organization_id` (`organization_id`);

--
-- Indeks for tabel `task_assigned_employees`
--
ALTER TABLE `task_assigned_employees`
  ADD PRIMARY KEY (`task_id`,`employee_id`),
  ADD KEY `fk_task_employee_employee` (`employee_id`);

--
-- Indeks for tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indeks for tabel `work_sessions`
--
ALTER TABLE `work_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `organization_id` (`organization_id`);

--
-- Brug ikke AUTO_INCREMENT for slettede tabeller
--

--
-- Tilføj AUTO_INCREMENT i tabel `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- Tilføj AUTO_INCREMENT i tabel `employee_tasks`
--
ALTER TABLE `employee_tasks`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `loginattempts`
--
ALTER TABLE `loginattempts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Tilføj AUTO_INCREMENT i tabel `organizations`
--
ALTER TABLE `organizations`
  MODIFY `id` int(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tilføj AUTO_INCREMENT i tabel `requests`
--
ALTER TABLE `requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- Tilføj AUTO_INCREMENT i tabel `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- Tilføj AUTO_INCREMENT i tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Tilføj AUTO_INCREMENT i tabel `work_sessions`
--
ALTER TABLE `work_sessions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Begrænsninger for dumpede tabeller
--

--
-- Begrænsninger for tabel `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_employees_organization` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`);

--
-- Begrænsninger for tabel `employee_tasks`
--
ALTER TABLE `employee_tasks`
  ADD CONSTRAINT `employee_tasks_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `employee_tasks_ibfk_2` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_employee_tasks_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`),
  ADD CONSTRAINT `fk_employee_tasks_task` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`);

--
-- Begrænsninger for tabel `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE;

--
-- Begrænsninger for tabel `task_assigned_employees`
--
ALTER TABLE `task_assigned_employees`
  ADD CONSTRAINT `fk_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_task` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_task_employee_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`),
  ADD CONSTRAINT `fk_task_employee_task` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`);

--
-- Begrænsninger for tabel `work_sessions`
--
ALTER TABLE `work_sessions`
  ADD CONSTRAINT `work_sessions_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `work_sessions_ibfk_2` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
