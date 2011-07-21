-- phpMyAdmin SQL Dump
-- version 3.3.9.2
-- http://www.phpmyadmin.net
--
-- Serveur: localhost
-- Généré le : Jeu 21 Juillet 2011 à 15:27
-- Version du serveur: 5.5.9
-- Version de PHP: 5.2.17

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données: `flux`
--

-- --------------------------------------------------------

--
-- Structure de la table `flux_Acti`
--

DROP TABLE IF EXISTS `flux_Acti`;
CREATE TABLE `flux_Acti` (
  `acti_id` int(11) NOT NULL AUTO_INCREMENT,
  `code` text NOT NULL,
  `desc` varchar(255) NOT NULL,
  PRIMARY KEY (`acti_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `flux_ActiUti`
--

DROP TABLE IF EXISTS `flux_ActiUti`;
CREATE TABLE `flux_ActiUti` (
  `acti_id` int(10) NOT NULL,
  `uti_id` int(10) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`acti_id`,`uti_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `flux_Doc`
--

DROP TABLE IF EXISTS `flux_Doc`;
CREATE TABLE `flux_Doc` (
  `doc_id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(255) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `branche` int(11) NOT NULL,
  `tronc` varchar(255) NOT NULL,
  `poids` int(11) NOT NULL,
  `maj` datetime NOT NULL,
  `pubDate` datetime NOT NULL,
  `note` varchar(255) NOT NULL,
  PRIMARY KEY (`doc_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=7319 ;

-- --------------------------------------------------------

--
-- Structure de la table `flux_Ieml`
--

DROP TABLE IF EXISTS `flux_Ieml`;
CREATE TABLE `flux_Ieml` (
  `ieml_id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `desc` varchar(255) NOT NULL,
  `niveau` int(11) NOT NULL,
  `parent` varchar(50) NOT NULL,
  `maj` date NOT NULL,
  PRIMARY KEY (`ieml_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Cette table est utilisée pour stocker l’ontologie IEML.' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `flux_Tag`
--

DROP TABLE IF EXISTS `flux_Tag`;
CREATE TABLE `flux_Tag` (
  `tag_id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(225) NOT NULL,
  `desc` varchar(255) NOT NULL,
  `niveau` int(11) NOT NULL,
  `parent` char(255) NOT NULL,
  PRIMARY KEY (`tag_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3815 ;

-- --------------------------------------------------------

--
-- Structure de la table `flux_TagDoc`
--

DROP TABLE IF EXISTS `flux_TagDoc`;
CREATE TABLE `flux_TagDoc` (
  `tag_id` int(11) NOT NULL,
  `doc_id` int(11) NOT NULL,
  PRIMARY KEY (`tag_id`,`doc_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `flux_Trad`
--

DROP TABLE IF EXISTS `flux_Trad`;
CREATE TABLE `flux_Trad` (
  `trad_id` int(11) NOT NULL AUTO_INCREMENT,
  `ieml_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  `trad_date` date NOT NULL,
  `trad_post` tinyint(1) NOT NULL,
  PRIMARY KEY (`trad_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `flux_TradPartage`
--

DROP TABLE IF EXISTS `flux_TradPartage`;
CREATE TABLE `flux_TradPartage` (
  `trad_id` int(11) NOT NULL,
  `uti_id` int(11) NOT NULL,
  PRIMARY KEY (`trad_id`,`uti_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `flux_Uti`
--

DROP TABLE IF EXISTS `flux_Uti`;
CREATE TABLE `flux_Uti` (
  `uti_id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(225) NOT NULL,
  `maj` varchar(225) NOT NULL,
  `flux` varchar(255) NOT NULL,
  PRIMARY KEY (`uti_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2115 ;

-- --------------------------------------------------------

--
-- Structure de la table `flux_UtiDoc`
--

DROP TABLE IF EXISTS `flux_UtiDoc`;
CREATE TABLE `flux_UtiDoc` (
  `uti_id` int(11) NOT NULL,
  `doc_id` int(11) NOT NULL,
  `maj` datetime NOT NULL,
  PRIMARY KEY (`uti_id`,`doc_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

-- --------------------------------------------------------

--
-- Structure de la table `flux_UtiIeml`
--

DROP TABLE IF EXISTS `flux_UtiIeml`;
CREATE TABLE `flux_UtiIeml` (
  `uti_id` int(11) NOT NULL,
  `ieml_id` int(11) NOT NULL,
  PRIMARY KEY (`uti_id`,`ieml_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `flux_UtiTag`
--

DROP TABLE IF EXISTS `flux_UtiTag`;
CREATE TABLE `flux_UtiTag` (
  `uti_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  `poids` int(11) NOT NULL,
  PRIMARY KEY (`uti_id`,`tag_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `flux_UtiTagRelated`
--

DROP TABLE IF EXISTS `flux_UtiTagRelated`;
CREATE TABLE `flux_UtiTagRelated` (
  `uti_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  `poids` int(11) NOT NULL,
  PRIMARY KEY (`uti_id`,`tag_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `flux_UtiUti`
--

DROP TABLE IF EXISTS `flux_UtiUti`;
CREATE TABLE `flux_UtiUti` (
  `uti_id_src` int(11) NOT NULL,
  `uti_id_dst` int(11) NOT NULL,
  `network` int(11) NOT NULL,
  `fan` int(11) NOT NULL,
  `post` int(11) NOT NULL,
  PRIMARY KEY (`uti_id_src`,`uti_id_dst`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `jardin_Exis`
--

DROP TABLE IF EXISTS `jardin_Exis`;
CREATE TABLE `jardin_Exis` (
  `id_exi` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) COLLATE utf8_bin NOT NULL,
  `url` varchar(255) COLLATE utf8_bin NOT NULL,
  `mail` varchar(255) COLLATE utf8_bin NOT NULL,
  `mdp` varchar(32) COLLATE utf8_bin NOT NULL,
  `mdp_sel` varchar(32) COLLATE utf8_bin NOT NULL,
  `role` varchar(32) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id_exi`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1 ;
