-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : ven. 03 juil. 2026 à 17:23
-- Version du serveur : 8.4.7
-- Version de PHP : 8.4.15

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `yuno_budget`
--

-- --------------------------------------------------------

--
-- Structure de la table `categorie`
--

DROP TABLE IF EXISTS `categorie`;
CREATE TABLE IF NOT EXISTS `categorie` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `type` varchar(20) NOT NULL,
  `utilisateur_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_497DD634FB88E14F` (`utilisateur_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `categorie`
--

INSERT INTO `categorie` (`id`, `nom`, `type`, `utilisateur_id`) VALUES
(1, 'Salaire & Revenus 💰', 'REVENU', 1),
(2, 'Logement & Loyer 🏠', 'DEPENSE', 1),
(3, 'Alimentation & Courses 🛒', 'DEPENSE', 1),
(4, 'Loisirs & Sorties 🎬', 'DEPENSE', 1),
(5, 'Transports & Essence 🚗', 'DEPENSE', 1);

-- --------------------------------------------------------

--
-- Structure de la table `doctrine_migration_versions`
--

DROP TABLE IF EXISTS `doctrine_migration_versions`;
CREATE TABLE IF NOT EXISTS `doctrine_migration_versions` (
  `version` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `doctrine_migration_versions`
--

INSERT INTO `doctrine_migration_versions` (`version`, `executed_at`, `execution_time`) VALUES
('DoctrineMigrations\\Version20260627161544', '2026-06-27 16:15:52', 143),
('DoctrineMigrations\\Version20260627160519', '2026-06-27 16:05:35', 203),
('DoctrineMigrations\\Version20260702160056', '2026-07-02 16:30:16', 138),
('DoctrineMigrations\\Version20260703130143', '2026-07-03 13:05:17', 160),
('DoctrineMigrations\\Version20260703152404', '2026-07-03 15:24:10', 169);

-- --------------------------------------------------------

--
-- Structure de la table `messenger_messages`
--

DROP TABLE IF EXISTS `messenger_messages`;
CREATE TABLE IF NOT EXISTS `messenger_messages` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `body` longtext NOT NULL,
  `headers` longtext NOT NULL,
  `queue_name` varchar(190) NOT NULL,
  `created_at` datetime NOT NULL,
  `available_at` datetime NOT NULL,
  `delivered_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750` (`queue_name`,`available_at`,`delivered_at`,`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `moyen_paiement`
--

DROP TABLE IF EXISTS `moyen_paiement`;
CREATE TABLE IF NOT EXISTS `moyen_paiement` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `numero_masque` varchar(50) DEFAULT NULL,
  `type` varchar(20) NOT NULL,
  `libelle_banque` varchar(100) DEFAULT NULL,
  `utilisateur_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_ED4417D2FB88E14F` (`utilisateur_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `moyen_paiement`
--

INSERT INTO `moyen_paiement` (`id`, `nom`, `numero_masque`, `type`, `libelle_banque`, `utilisateur_id`) VALUES
(1, 'Carte Principale', 'XXXX-XXXX-XXXX-4512', 'CB', 'Crédit Agricole', 1),
(2, 'Espèces Portefeuille', NULL, 'ESPECES', NULL, 1),
(3, 'Compte PayPal', 'XXXX-XXXX-XXXX-8894', 'PAYPAL', 'PayPal Europe', 1),
(4, 'Virement Bancaire', 'FR76 1234...', 'VIREMENT', 'Boursorama', 1);

-- --------------------------------------------------------

--
-- Structure de la table `transaction`
--

DROP TABLE IF EXISTS `transaction`;
CREATE TABLE IF NOT EXISTS `transaction` (
  `id` int NOT NULL AUTO_INCREMENT,
  `montant` double NOT NULL,
  `date_transaction` datetime NOT NULL,
  `libelle_transaction` varchar(255) NOT NULL,
  `utilisateur_id` int NOT NULL,
  `categorie_id` int NOT NULL,
  `moyen_paiement_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_723705D1FB88E14F` (`utilisateur_id`),
  KEY `IDX_723705D1BCF5E72D` (`categorie_id`),
  KEY `IDX_723705D19C7E259C` (`moyen_paiement_id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `transaction`
--

INSERT INTO `transaction` (`id`, `montant`, `date_transaction`, `libelle_transaction`, `utilisateur_id`, `categorie_id`, `moyen_paiement_id`) VALUES
(1, 2500, '2026-06-01 09:00:00', 'Salaire Juin', 1, 1, 4),
(2, 35, '2026-06-15 14:20:00', 'Remboursement Vinted', 1, 1, 3),
(3, -800, '2026-06-05 10:30:00', 'Loyer Mensuel', 1, 2, 1),
(4, -240.5, '2026-06-12 15:45:00', 'Courses Leclerc', 1, 3, 1),
(5, -65, '2026-06-18 20:00:00', 'Restaurant entre amis', 1, 4, 1),
(6, -50, '2026-06-22 08:00:00', 'Plein Essence', 1, 5, 2),
(7, -190, '2026-06-29 17:00:00', 'Courses Auchan', 1, 3, 1);

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

DROP TABLE IF EXISTS `utilisateur`;
CREATE TABLE IF NOT EXISTS `utilisateur` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(180) NOT NULL,
  `roles` json NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_verified` tinyint NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_IDENTIFIER_EMAIL` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`id`, `email`, `roles`, `password`, `is_verified`) VALUES
(1, 'demo@yuno.fr', '[]', '$2y$13$OY2xyuev7Mgd2dUtUf.g3.XYMhzSyjRBBVrZpvwrZOoBL5/R.RZLm', 0);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
