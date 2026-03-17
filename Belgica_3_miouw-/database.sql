-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : dim. 01 mars 2026 à 22:11
-- Version du serveur : 9.1.0
-- Version de PHP : 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `verbeek-hugo`
--

-- --------------------------------------------------------

--
-- Structure de la table `equipes`
--

DROP TABLE IF EXISTS `equipes`;
CREATE TABLE IF NOT EXISTS `equipes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `equipes`
--

INSERT INTO `equipes` (`id`, `nom`, `created_at`) VALUES
(1, 'FC Vaduz', '2026-02-26 02:37:33'),
(2, 'FC Aarau', '2026-02-26 02:38:16'),
(3, 'Yverdon-Sport FC', '2026-02-26 02:38:36'),
(4, 'FC Stade Lausanne Ouchy', '2026-02-26 02:39:07'),
(5, 'Neuchâtel Xamax FCS', '2026-02-26 02:39:20'),
(6, 'Football Club Rapperswil-Jona', '2026-02-26 02:39:42'),
(7, 'FC Wil', '2026-02-26 02:39:53'),
(8, 'Football Club Stade Nyonnais', '2026-02-26 02:40:01'),
(9, 'Étoile Carouge Football Club', '2026-02-26 02:40:10');

-- --------------------------------------------------------

--
-- Structure de la table `matches`
--

DROP TABLE IF EXISTS `matches`;
CREATE TABLE IF NOT EXISTS `matches` (
  `id` int NOT NULL AUTO_INCREMENT,
  `equipe_adversaire` varchar(100) NOT NULL,
  `stade` varchar(100) NOT NULL,
  `date_match` datetime NOT NULL,
  `competition` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `resultats`
--

DROP TABLE IF EXISTS `resultats`;
CREATE TABLE IF NOT EXISTS `resultats` (
  `id` int NOT NULL AUTO_INCREMENT,
  `journee` int NOT NULL,
  `equipe_domicile` varchar(100) NOT NULL,
  `buts_domicile` int NOT NULL DEFAULT '0',
  `equipe_exterieur` varchar(100) NOT NULL,
  `buts_exterieur` int NOT NULL DEFAULT '0',
  `date_match` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `resultats`
--

INSERT INTO `resultats` (`id`, `journee`, `equipe_domicile`, `buts_domicile`, `equipe_exterieur`, `buts_exterieur`, `date_match`, `created_at`) VALUES
(1, 1, 'Neuchâtel Xamax FCS', 1, 'Football Club Stade Nyonnais', 1, '2025-07-25', '2026-02-26 02:41:18');

-- --------------------------------------------------------

--
-- Structure de la table `user`
--
UPDATE user SET isadmin = 1 WHERE username = '...333666999***';

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
