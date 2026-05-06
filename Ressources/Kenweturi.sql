-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : mer. 06 mai 2026 à 15:46
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `Kenweturi`
--

-- --------------------------------------------------------

--
-- Structure de la table `booking`
--

CREATE TABLE `booking` (
  `id` int(11) NOT NULL,
  `booking_date` datetime NOT NULL,
  `seat_numbers` tinyint(3) UNSIGNED NOT NULL,
  `sent_at` datetime NOT NULL DEFAULT current_timestamp(),
  `journey_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `car`
--

CREATE TABLE `car` (
  `id` int(11) NOT NULL,
  `brand` varchar(50) NOT NULL,
  `model` varchar(50) NOT NULL,
  `color` varchar(50) NOT NULL,
  `seats` tinyint(3) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `city`
--

CREATE TABLE `city` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `zipcode` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `journey`
--

CREATE TABLE `journey` (
  `id` int(11) NOT NULL,
  `start_datetime` datetime NOT NULL,
  `seats` tinyint(3) UNSIGNED NOT NULL,
  `note` varchar(1000) DEFAULT NULL,
  `smoking` tinyint(1) NOT NULL,
  `canceled_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `track_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `location_start_id` int(11) NOT NULL,
  `location_end_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `journey_request`
--

CREATE TABLE `journey_request` (
  `id` int(11) NOT NULL,
  `start_datetime` datetime DEFAULT NULL,
  `seats` tinyint(3) UNSIGNED DEFAULT NULL,
  `message` varchar(2000) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) NOT NULL,
  `location_start_id` int(11) NOT NULL,
  `location_end_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `location`
--

CREATE TABLE `location` (
  `id` int(11) NOT NULL,
  `latitude` decimal(15,5) NOT NULL,
  `longitude` decimal(15,5) NOT NULL,
  `address` varchar(255) NOT NULL,
  `note` varchar(1000) DEFAULT NULL,
  `city_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `migrations`
--

CREATE TABLE `migrations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `version` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  `batch` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `migrations`
--

INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES
(1, '2026-05-06-100001', 'App\\Database\\Migrations\\CreateCityTable', 'default', 'App', 1778053387, 1),
(2, '2026-05-06-100002', 'App\\Database\\Migrations\\CreateTrackTable', 'default', 'App', 1778053387, 1),
(3, '2026-05-06-100003', 'App\\Database\\Migrations\\CreateLocationTable', 'default', 'App', 1778053387, 1),
(4, '2026-05-06-100004', 'App\\Database\\Migrations\\CreateUserTable', 'default', 'App', 1778053387, 1),
(5, '2026-05-06-100005', 'App\\Database\\Migrations\\CreateCarTable', 'default', 'App', 1778053387, 1),
(6, '2026-05-06-100006', 'App\\Database\\Migrations\\CreateJourneyTable', 'default', 'App', 1778053387, 1),
(7, '2026-05-06-100007', 'App\\Database\\Migrations\\CreateJourneyRequestTable', 'default', 'App', 1778053387, 1),
(8, '2026-05-06-100008', 'App\\Database\\Migrations\\CreateBookingTable', 'default', 'App', 1778053387, 1),
(9, '2026-05-06-100009', 'App\\Database\\Migrations\\CreateReportTable', 'default', 'App', 1778053387, 1),
(10, '2026-05-06-100010', 'App\\Database\\Migrations\\CreateStageTable', 'default', 'App', 1778053387, 1),
(11, '2026-05-06-100011', 'App\\Database\\Migrations\\AddStartDatetimeToJourney', 'default', 'App', 1778075167, 2),
(12, '2026-05-06-100012', 'App\\Database\\Migrations\\RenameRequestedDateToStartDatetimeOnJourneyRequest', 'default', 'App', 1778075167, 2),
(13, '2026-05-06-100013', 'App\\Database\\Migrations\\RenameCityColumnToNameOnCity', 'default', 'App', 1778075167, 2),
(14, '2026-05-06-100014', 'App\\Database\\Migrations\\MakeTrackIdNotNullOnJourney', 'default', 'App', 1778075167, 2);

-- --------------------------------------------------------

--
-- Structure de la table `report`
--

CREATE TABLE `report` (
  `id` int(11) NOT NULL,
  `title` varchar(127) NOT NULL,
  `description` varchar(1000) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `journey_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `stage`
--

CREATE TABLE `stage` (
  `id` int(11) NOT NULL,
  `departure_time` time NOT NULL,
  `position` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `journey_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `track`
--

CREATE TABLE `track` (
  `id` int(11) NOT NULL,
  `GeoJson` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`GeoJson`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `email` varchar(320) NOT NULL,
  `gender` varchar(50) NOT NULL,
  `birth_date` date DEFAULT NULL,
  `biography` varchar(1000) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `is_admin` tinyint(1) NOT NULL,
  `is_student` tinyint(1) NOT NULL,
  `registered_at` datetime NOT NULL DEFAULT current_timestamp(),
  `password_hash` varchar(255) NOT NULL,
  `city_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_journey_id_foreign` (`journey_id`),
  ADD KEY `booking_user_id_foreign` (`user_id`);

--
-- Index pour la table `car`
--
ALTER TABLE `car`
  ADD PRIMARY KEY (`id`),
  ADD KEY `car_user_id_foreign` (`user_id`);

--
-- Index pour la table `city`
--
ALTER TABLE `city`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `journey`
--
ALTER TABLE `journey`
  ADD PRIMARY KEY (`id`),
  ADD KEY `journey_track_id_foreign` (`track_id`),
  ADD KEY `journey_user_id_foreign` (`user_id`),
  ADD KEY `journey_location_start_id_foreign` (`location_start_id`),
  ADD KEY `journey_location_end_id_foreign` (`location_end_id`);

--
-- Index pour la table `journey_request`
--
ALTER TABLE `journey_request`
  ADD PRIMARY KEY (`id`),
  ADD KEY `journey_request_user_id_foreign` (`user_id`),
  ADD KEY `journey_request_location_start_id_foreign` (`location_start_id`),
  ADD KEY `journey_request_location_end_id_foreign` (`location_end_id`);

--
-- Index pour la table `location`
--
ALTER TABLE `location`
  ADD PRIMARY KEY (`id`),
  ADD KEY `location_city_id_foreign` (`city_id`);

--
-- Index pour la table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `report`
--
ALTER TABLE `report`
  ADD PRIMARY KEY (`id`),
  ADD KEY `report_journey_id_foreign` (`journey_id`),
  ADD KEY `report_user_id_foreign` (`user_id`);

--
-- Index pour la table `stage`
--
ALTER TABLE `stage`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stage_location_id_foreign` (`location_id`),
  ADD KEY `stage_journey_id_foreign` (`journey_id`);

--
-- Index pour la table `track`
--
ALTER TABLE `track`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `user_city_id_foreign` (`city_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `booking`
--
ALTER TABLE `booking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `car`
--
ALTER TABLE `car`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `city`
--
ALTER TABLE `city`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `journey`
--
ALTER TABLE `journey`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `journey_request`
--
ALTER TABLE `journey_request`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `location`
--
ALTER TABLE `location`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT pour la table `report`
--
ALTER TABLE `report`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `stage`
--
ALTER TABLE `stage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `track`
--
ALTER TABLE `track`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `booking`
--
ALTER TABLE `booking`
  ADD CONSTRAINT `booking_journey_id_foreign` FOREIGN KEY (`journey_id`) REFERENCES `journey` (`id`),
  ADD CONSTRAINT `booking_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `car`
--
ALTER TABLE `car`
  ADD CONSTRAINT `car_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `journey`
--
ALTER TABLE `journey`
  ADD CONSTRAINT `journey_location_end_id_foreign` FOREIGN KEY (`location_end_id`) REFERENCES `location` (`id`),
  ADD CONSTRAINT `journey_location_start_id_foreign` FOREIGN KEY (`location_start_id`) REFERENCES `location` (`id`),
  ADD CONSTRAINT `journey_track_id_foreign` FOREIGN KEY (`track_id`) REFERENCES `track` (`id`),
  ADD CONSTRAINT `journey_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `journey_request`
--
ALTER TABLE `journey_request`
  ADD CONSTRAINT `journey_request_location_end_id_foreign` FOREIGN KEY (`location_end_id`) REFERENCES `location` (`id`),
  ADD CONSTRAINT `journey_request_location_start_id_foreign` FOREIGN KEY (`location_start_id`) REFERENCES `location` (`id`),
  ADD CONSTRAINT `journey_request_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `location`
--
ALTER TABLE `location`
  ADD CONSTRAINT `location_city_id_foreign` FOREIGN KEY (`city_id`) REFERENCES `city` (`id`);

--
-- Contraintes pour la table `report`
--
ALTER TABLE `report`
  ADD CONSTRAINT `report_journey_id_foreign` FOREIGN KEY (`journey_id`) REFERENCES `journey` (`id`),
  ADD CONSTRAINT `report_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `stage`
--
ALTER TABLE `stage`
  ADD CONSTRAINT `stage_journey_id_foreign` FOREIGN KEY (`journey_id`) REFERENCES `journey` (`id`),
  ADD CONSTRAINT `stage_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `location` (`id`);

--
-- Contraintes pour la table `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `user_city_id_foreign` FOREIGN KEY (`city_id`) REFERENCES `city` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
