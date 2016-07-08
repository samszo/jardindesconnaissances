-- phpMyAdmin SQL Dump
-- version 4.5.2
-- http://www.phpmyadmin.net
--
-- Client :  localhost
-- Généré le :  Mar 28 Juin 2016 à 10:40
-- Version du serveur :  10.1.9-MariaDB
-- Version de PHP :  5.6.15

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `flux_editinflu`
--

-- --------------------------------------------------------

--
-- Structure de la table `flux_acti`
--

DROP TABLE IF EXISTS `flux_acti`;
CREATE TABLE `flux_acti` (
  `acti_id` int(11) NOT NULL,
  `code` text COLLATE utf8_unicode_ci NOT NULL,
  `desc` varchar(255) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `flux_doc`
--

DROP TABLE IF EXISTS `flux_doc`;
CREATE TABLE `flux_doc` (
  `doc_id` int(11) NOT NULL,
  `url` text COLLATE utf8_unicode_ci NOT NULL,
  `titre` varchar(5000) COLLATE utf8_unicode_ci NOT NULL,
  `lft` int(11) NOT NULL,
  `rgt` int(11) NOT NULL,
  `tronc` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `poids` int(11) NOT NULL DEFAULT '1',
  `maj` datetime NOT NULL,
  `pubDate` datetime NOT NULL,
  `note` text COLLATE utf8_unicode_ci NOT NULL,
  `total_posts` int(11) NOT NULL,
  `top_tags` varchar(2000) COLLATE utf8_unicode_ci NOT NULL,
  `type` int(11) NOT NULL,
  `data` blob NOT NULL,
  `score` decimal(28,14) NOT NULL,
  `parent` int(11) NOT NULL,
  `niveau` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Structure de la table `flux_doctypes`
--

DROP TABLE IF EXISTS `flux_doctypes`;
CREATE TABLE `flux_doctypes` (
  `id_type` bigint(21) NOT NULL,
  `titre` text COLLATE utf8_unicode_ci NOT NULL,
  `descriptif` text COLLATE utf8_unicode_ci NOT NULL,
  `extension` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `mime_type` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `inclus` enum('non','image','embed') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'non',
  `upload` enum('oui','non') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'oui',
  `maj` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `flux_exi`
--

DROP TABLE IF EXISTS `flux_exi`;
CREATE TABLE `flux_exi` (
  `exi_id` int(11) NOT NULL,
  `uti_id` int(11) NOT NULL,
  `nom` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `prenom` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `lft` int(11) NOT NULL,
  `rgt` int(11) NOT NULL,
  `parent` int(11) NOT NULL,
  `niveau` int(11) NOT NULL,
  `maj` datetime NOT NULL,
  `poids` int(11) NOT NULL,
  `data` text COLLATE utf8_unicode_ci NOT NULL,
  `nait` date NOT NULL,
  `mort` date NOT NULL,
  `isni` varchar(255) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Structure de la table `flux_geos`
--

DROP TABLE IF EXISTS `flux_geos`;
CREATE TABLE `flux_geos` (
  `geo_id` int(11) NOT NULL,
  `id_instant` int(11) NOT NULL,
  `lat` decimal(12,8) NOT NULL,
  `lng` decimal(12,8) NOT NULL,
  `alt` decimal(20,10) NOT NULL COMMENT 'en mètre',
  `geo` geometry NOT NULL,
  `zoom_min` int(11) NOT NULL,
  `zoom_max` int(11) NOT NULL,
  `adresse` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `codepostal` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ville` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `pays` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `kml` text COLLATE utf8_unicode_ci NOT NULL,
  `type_carte` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `maj` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Structure de la table `flux_ieml`
--

DROP TABLE IF EXISTS `flux_ieml`;
CREATE TABLE `flux_ieml` (
  `ieml_id` int(11) NOT NULL,
  `code` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `desc` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `niveau` int(11) NOT NULL,
  `parent` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `maj` date NOT NULL,
  `parse` longtext COLLATE utf8_unicode_ci NOT NULL,
  `binary` longtext COLLATE utf8_unicode_ci NOT NULL,
  `ordre` longtext COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Cette table est utilisée pour stocker l’ontologie IEML.';

-- --------------------------------------------------------

--
-- Structure de la table `flux_monade`
--

DROP TABLE IF EXISTS `flux_monade`;
CREATE TABLE `flux_monade` (
  `monade_id` int(11) NOT NULL,
  `titre` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `maj` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `flux_rapport`
--

DROP TABLE IF EXISTS `flux_rapport`;
CREATE TABLE `flux_rapport` (
  `rapport_id` int(11) NOT NULL,
  `monade_id` int(11) NOT NULL,
  `src_id` int(11) NOT NULL,
  `src_obj` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `dst_id` int(11) NOT NULL,
  `dst_obj` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `pre_id` int(11) NOT NULL COMMENT 'id prédicat',
  `pre_obj` varchar(20) COLLATE utf8_unicode_ci NOT NULL COMMENT 'obj prédicat',
  `geo_id` int(11) NOT NULL,
  `maj` datetime NOT NULL,
  `niveau` int(11) NOT NULL,
  `valeur` text COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `flux_tag`
--

DROP TABLE IF EXISTS `flux_tag`;
CREATE TABLE `flux_tag` (
  `tag_id` int(11) NOT NULL,
  `code` varchar(225) COLLATE utf8_unicode_ci NOT NULL,
  `desc` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ns` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `niveau` int(11) NOT NULL,
  `parent` int(11) NOT NULL,
  `lft` int(11) NOT NULL,
  `rgt` int(11) NOT NULL,
  `uri` varchar(255) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `flux_uti`
--

DROP TABLE IF EXISTS `flux_uti`;
CREATE TABLE `flux_uti` (
  `uti_id` int(11) NOT NULL,
  `login` varchar(2000) COLLATE utf8_unicode_ci NOT NULL,
  `maj` varchar(225) COLLATE utf8_unicode_ci NOT NULL,
  `flux` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `mdp` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `role` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `mdp_sel` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ip_inscription` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `date_inscription` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Index pour les tables exportées
--

--
-- Index pour la table `flux_acti`
--
ALTER TABLE `flux_acti`
  ADD PRIMARY KEY (`acti_id`);

--
-- Index pour la table `flux_doc`
--
ALTER TABLE `flux_doc`
  ADD PRIMARY KEY (`doc_id`),
  ADD KEY `type` (`type`),
  ADD KEY `score` (`score`);

--
-- Index pour la table `flux_doctypes`
--
ALTER TABLE `flux_doctypes`
  ADD PRIMARY KEY (`id_type`),
  ADD UNIQUE KEY `extension` (`extension`),
  ADD KEY `inclus` (`inclus`);

--
-- Index pour la table `flux_exi`
--
ALTER TABLE `flux_exi`
  ADD PRIMARY KEY (`exi_id`);

--
-- Index pour la table `flux_geos`
--
ALTER TABLE `flux_geos`
  ADD PRIMARY KEY (`geo_id`),
  ADD KEY `id_instant` (`id_instant`);

--
-- Index pour la table `flux_ieml`
--
ALTER TABLE `flux_ieml`
  ADD PRIMARY KEY (`ieml_id`);

--
-- Index pour la table `flux_monade`
--
ALTER TABLE `flux_monade`
  ADD PRIMARY KEY (`monade_id`);

--
-- Index pour la table `flux_rapport`
--
ALTER TABLE `flux_rapport`
  ADD PRIMARY KEY (`rapport_id`);

--
-- Index pour la table `flux_tag`
--
ALTER TABLE `flux_tag`
  ADD PRIMARY KEY (`tag_id`),
  ADD KEY `code` (`code`),
  ADD KEY `lft_rgt` (`lft`,`rgt`);

--
-- Index pour la table `flux_uti`
--
ALTER TABLE `flux_uti`
  ADD PRIMARY KEY (`uti_id`),
  ADD KEY `login` (`login`(333));

--
-- AUTO_INCREMENT pour les tables exportées
--

--
-- AUTO_INCREMENT pour la table `flux_acti`
--
ALTER TABLE `flux_acti`
  MODIFY `acti_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `flux_doc`
--
ALTER TABLE `flux_doc`
  MODIFY `doc_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `flux_doctypes`
--
ALTER TABLE `flux_doctypes`
  MODIFY `id_type` bigint(21) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `flux_exi`
--
ALTER TABLE `flux_exi`
  MODIFY `exi_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `flux_geos`
--
ALTER TABLE `flux_geos`
  MODIFY `geo_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `flux_ieml`
--
ALTER TABLE `flux_ieml`
  MODIFY `ieml_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `flux_monade`
--
ALTER TABLE `flux_monade`
  MODIFY `monade_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `flux_rapport`
--
ALTER TABLE `flux_rapport`
  MODIFY `rapport_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `flux_tag`
--
ALTER TABLE `flux_tag`
  MODIFY `tag_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `flux_uti`
--
ALTER TABLE `flux_uti`
  MODIFY `uti_id` int(11) NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
