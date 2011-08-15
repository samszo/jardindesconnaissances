-- phpMyAdmin SQL Dump
-- version 3.3.9
-- http://www.phpmyadmin.net
--
-- Serveur: localhost
-- Généré le : Lun 15 Août 2011 à 09:12
-- Version du serveur: 5.5.8
-- Version de PHP: 5.3.5

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
-- Structure de la table `flux_acti`
--

DROP TABLE IF EXISTS `flux_acti`;
CREATE TABLE IF NOT EXISTS `flux_acti` (
  `acti_id` int(11) NOT NULL AUTO_INCREMENT,
  `code` text COLLATE utf8_unicode_ci NOT NULL,
  `desc` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`acti_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `flux_actiuti`
--

DROP TABLE IF EXISTS `flux_actiuti`;
CREATE TABLE IF NOT EXISTS `flux_actiuti` (
  `acti_id` int(10) NOT NULL,
  `uti_id` int(10) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`acti_id`,`uti_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `flux_doc`
--

DROP TABLE IF EXISTS `flux_doc`;
CREATE TABLE IF NOT EXISTS `flux_doc` (
  `doc_id` int(11) NOT NULL AUTO_INCREMENT,
  `url` text COLLATE utf8_unicode_ci NOT NULL,
  `titre` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `branche` int(11) NOT NULL,
  `tronc` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `poids` int(11) NOT NULL,
  `maj` datetime NOT NULL,
  `pubDate` datetime NOT NULL,
  `note` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`doc_id`),
  FULLTEXT KEY `url` (`url`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC AUTO_INCREMENT=8195 ;

-- --------------------------------------------------------

--
-- Structure de la table `flux_exi`
--

DROP TABLE IF EXISTS `flux_exi`;
CREATE TABLE IF NOT EXISTS `flux_exi` (
  `exi_id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `url` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `mail` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `mdp` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `mdp_sel` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `role` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`exi_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `flux_exidoc`
--

DROP TABLE IF EXISTS `flux_exidoc`;
CREATE TABLE IF NOT EXISTS `flux_exidoc` (
  `exi_id` int(11) NOT NULL,
  `doc_id` int(11) NOT NULL,
  PRIMARY KEY (`exi_id`,`doc_id`),
  KEY `exi_id` (`exi_id`),
  KEY `doc_id` (`doc_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `flux_exitag`
--

DROP TABLE IF EXISTS `flux_exitag`;
CREATE TABLE IF NOT EXISTS `flux_exitag` (
  `exi_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  PRIMARY KEY (`exi_id`,`tag_id`),
  KEY `exi_id` (`exi_id`),
  KEY `tag_id` (`tag_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `flux_ieml`
--

DROP TABLE IF EXISTS `flux_ieml`;
CREATE TABLE IF NOT EXISTS `flux_ieml` (
  `ieml_id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `desc` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `niveau` int(11) NOT NULL,
  `parent` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `maj` date NOT NULL,
  PRIMARY KEY (`ieml_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Cette table est utilisée pour stocker l’ontologie IEML.' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `flux_tag`
--

DROP TABLE IF EXISTS `flux_tag`;
CREATE TABLE IF NOT EXISTS `flux_tag` (
  `tag_id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(225) COLLATE utf8_unicode_ci NOT NULL,
  `desc` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `niveau` int(11) NOT NULL,
  `parent` char(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`tag_id`),
  KEY `code` (`code`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=24335 ;

-- --------------------------------------------------------

--
-- Structure de la table `flux_tagdoc`
--

DROP TABLE IF EXISTS `flux_tagdoc`;
CREATE TABLE IF NOT EXISTS `flux_tagdoc` (
  `tag_id` int(11) NOT NULL,
  `doc_id` int(11) NOT NULL,
  PRIMARY KEY (`tag_id`,`doc_id`),
  KEY `tag_id` (`tag_id`),
  KEY `doc_id` (`doc_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `flux_tagtag`
--

DROP TABLE IF EXISTS `flux_tagtag`;
CREATE TABLE IF NOT EXISTS `flux_tagtag` (
  `tag_id_src` int(11) NOT NULL,
  `tag_id_dst` int(11) NOT NULL,
  PRIMARY KEY (`tag_id_src`,`tag_id_dst`),
  KEY `tag_id_src` (`tag_id_src`),
  KEY `tag_id_dst` (`tag_id_dst`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `flux_trad`
--

DROP TABLE IF EXISTS `flux_trad`;
CREATE TABLE IF NOT EXISTS `flux_trad` (
  `trad_id` int(11) NOT NULL AUTO_INCREMENT,
  `ieml_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  `trad_date` date NOT NULL,
  `trad_post` tinyint(1) NOT NULL,
  PRIMARY KEY (`trad_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `flux_tradpartage`
--

DROP TABLE IF EXISTS `flux_tradpartage`;
CREATE TABLE IF NOT EXISTS `flux_tradpartage` (
  `trad_id` int(11) NOT NULL,
  `uti_id` int(11) NOT NULL,
  PRIMARY KEY (`trad_id`,`uti_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `flux_uti`
--

DROP TABLE IF EXISTS `flux_uti`;
CREATE TABLE IF NOT EXISTS `flux_uti` (
  `uti_id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(225) COLLATE utf8_unicode_ci NOT NULL,
  `maj` varchar(225) COLLATE utf8_unicode_ci NOT NULL,
  `flux` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`uti_id`),
  KEY `login` (`login`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=55847 ;

-- --------------------------------------------------------

--
-- Structure de la table `flux_utidoc`
--

DROP TABLE IF EXISTS `flux_utidoc`;
CREATE TABLE IF NOT EXISTS `flux_utidoc` (
  `uti_id` int(11) NOT NULL,
  `doc_id` int(11) NOT NULL,
  `maj` datetime NOT NULL,
  `poids` int(11) NOT NULL,
  PRIMARY KEY (`uti_id`,`doc_id`),
  KEY `uti_id` (`uti_id`),
  KEY `doc_id` (`doc_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=FIXED;

-- --------------------------------------------------------

--
-- Structure de la table `flux_utiieml`
--

DROP TABLE IF EXISTS `flux_utiieml`;
CREATE TABLE IF NOT EXISTS `flux_utiieml` (
  `uti_id` int(11) NOT NULL,
  `ieml_id` int(11) NOT NULL,
  PRIMARY KEY (`uti_id`,`ieml_id`),
  KEY `uti_id` (`uti_id`),
  KEY `ieml_id` (`ieml_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `flux_utitag`
--

DROP TABLE IF EXISTS `flux_utitag`;
CREATE TABLE IF NOT EXISTS `flux_utitag` (
  `uti_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  `poids` int(11) NOT NULL,
  PRIMARY KEY (`uti_id`,`tag_id`),
  KEY `uti_id` (`uti_id`),
  KEY `tag_id` (`tag_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `flux_utitagdoc`
--

DROP TABLE IF EXISTS `flux_utitagdoc`;
CREATE TABLE IF NOT EXISTS `flux_utitagdoc` (
  `uti_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  `doc_id` int(11) NOT NULL,
  `maj` datetime NOT NULL,
  PRIMARY KEY (`uti_id`,`tag_id`,`doc_id`,`maj`),
  KEY `uti_id` (`uti_id`),
  KEY `tag_id` (`tag_id`),
  KEY `doc_id` (`doc_id`),
  KEY `maj` (`maj`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `flux_utitagrelated`
--

DROP TABLE IF EXISTS `flux_utitagrelated`;
CREATE TABLE IF NOT EXISTS `flux_utitagrelated` (
  `uti_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  `poids` int(11) NOT NULL,
  PRIMARY KEY (`uti_id`,`tag_id`),
  KEY `uti_id` (`uti_id`),
  KEY `tag_id` (`tag_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `flux_utiuti`
--

DROP TABLE IF EXISTS `flux_utiuti`;
CREATE TABLE IF NOT EXISTS `flux_utiuti` (
  `uti_id_src` int(11) NOT NULL,
  `uti_id_dst` int(11) NOT NULL,
  `network` int(11) NOT NULL,
  `fan` int(11) NOT NULL,
  `post` int(11) NOT NULL,
  PRIMARY KEY (`uti_id_src`,`uti_id_dst`),
  KEY `uti_id_src` (`uti_id_src`),
  KEY `uti_id_dst` (`uti_id_dst`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
