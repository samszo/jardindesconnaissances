-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Schema flux_biolographes
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema flux_biolographes
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `flux_biolographes` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ;
-- -----------------------------------------------------
-- Schema flux_proverbes
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema flux_proverbes
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `flux_proverbes` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ;
USE `flux_biolographes` ;

-- -----------------------------------------------------
-- Table `flux_biolographes`.`flux_acti`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flux_biolographes`.`flux_acti` ;

CREATE TABLE IF NOT EXISTS `flux_biolographes`.`flux_acti` (
  `acti_id` INT(11) NOT NULL AUTO_INCREMENT,
  `code` TEXT CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `desc` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  PRIMARY KEY (`acti_id`))
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `flux_biolographes`.`flux_actiuti`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flux_biolographes`.`flux_actiuti` ;

CREATE TABLE IF NOT EXISTS `flux_biolographes`.`flux_actiuti` (
  `acti_id` INT(10) NOT NULL,
  `uti_id` INT(10) NOT NULL,
  `date` DATETIME NOT NULL,
  PRIMARY KEY (`acti_id`, `uti_id`))
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `flux_biolographes`.`flux_doc`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flux_biolographes`.`flux_doc` ;

CREATE TABLE IF NOT EXISTS `flux_biolographes`.`flux_doc` (
  `doc_id` INT(11) NOT NULL AUTO_INCREMENT,
  `url` TEXT CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `titre` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `lft` INT(11) NOT NULL,
  `rgt` INT(11) NOT NULL,
  `tronc` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `poids` INT(11) NOT NULL DEFAULT '1',
  `maj` DATETIME NOT NULL,
  `pubDate` DATETIME NOT NULL,
  `note` TEXT CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `total_posts` INT(11) NOT NULL,
  `top_tags` VARCHAR(2000) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `type` INT(11) NOT NULL,
  `data` BLOB NOT NULL,
  `score` DECIMAL(28,14) NOT NULL,
  `parent` INT(11) NOT NULL,
  `niveau` INT(11) NOT NULL,
  PRIMARY KEY (`doc_id`),
  INDEX `type` (`type` ASC),
  INDEX `score` (`score` ASC),
  FULLTEXT INDEX `titre_note` (`titre` ASC, `note` ASC))
ENGINE = MyISAM
AUTO_INCREMENT = 3
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci
ROW_FORMAT = DYNAMIC;


-- -----------------------------------------------------
-- Table `flux_biolographes`.`flux_docdoc`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flux_biolographes`.`flux_docdoc` ;

CREATE TABLE IF NOT EXISTS `flux_biolographes`.`flux_docdoc` (
  `doc_id_src` INT(11) NOT NULL,
  `doc_id_dst` INT(11) NOT NULL,
  `poids` INT(11) NOT NULL,
  `maj` DATETIME NOT NULL)
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `flux_biolographes`.`flux_doctypes`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flux_biolographes`.`flux_doctypes` ;

CREATE TABLE IF NOT EXISTS `flux_biolographes`.`flux_doctypes` (
  `id_type` BIGINT(21) NOT NULL AUTO_INCREMENT,
  `titre` TEXT CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `descriptif` TEXT CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `extension` VARCHAR(10) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `mime_type` VARCHAR(100) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `inclus` ENUM('non','image','embed') CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL DEFAULT 'non',
  `upload` ENUM('oui','non') CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL DEFAULT 'oui',
  `maj` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_type`),
  UNIQUE INDEX `extension` (`extension` ASC),
  INDEX `inclus` (`inclus` ASC))
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `flux_biolographes`.`flux_exi`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flux_biolographes`.`flux_exi` ;

CREATE TABLE IF NOT EXISTS `flux_biolographes`.`flux_exi` (
  `exi_id` INT(11) NOT NULL AUTO_INCREMENT,
  `nom` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `prenom` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `lft` INT(11) NOT NULL,
  `rgt` INT(11) NOT NULL,
  `parent` INT(11) NOT NULL,
  `niveau` INT(11) NOT NULL,
  `maj` DATETIME NOT NULL,
  `poids` INT(11) NOT NULL,
  `data` TEXT CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `nait` DATE NOT NULL,
  `mort` DATE NOT NULL,
  PRIMARY KEY (`exi_id`))
ENGINE = MyISAM
AUTO_INCREMENT = 50
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci
ROW_FORMAT = DYNAMIC;


-- -----------------------------------------------------
-- Table `flux_biolographes`.`flux_exidoc`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flux_biolographes`.`flux_exidoc` ;

CREATE TABLE IF NOT EXISTS `flux_biolographes`.`flux_exidoc` (
  `exi_id` INT(11) NOT NULL,
  `doc_id` INT(11) NOT NULL,
  PRIMARY KEY (`exi_id`, `doc_id`),
  INDEX `exi_id` (`exi_id` ASC),
  INDEX `doc_id` (`doc_id` ASC))
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `flux_biolographes`.`flux_exitag`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flux_biolographes`.`flux_exitag` ;

CREATE TABLE IF NOT EXISTS `flux_biolographes`.`flux_exitag` (
  `exi_id` INT(11) NOT NULL,
  `tag_id` INT(11) NOT NULL,
  PRIMARY KEY (`exi_id`, `tag_id`),
  INDEX `exi_id` (`exi_id` ASC),
  INDEX `tag_id` (`tag_id` ASC))
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `flux_biolographes`.`flux_exitagdoc`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flux_biolographes`.`flux_exitagdoc` ;

CREATE TABLE IF NOT EXISTS `flux_biolographes`.`flux_exitagdoc` (
  `exitagdoc_id` INT(11) NOT NULL AUTO_INCREMENT,
  `exi_id` INT(11) NOT NULL,
  `tag_id` INT(11) NOT NULL,
  `doc_id` INT(11) NOT NULL,
  `maj` DATETIME NOT NULL,
  `poids` INT(11) NOT NULL,
  PRIMARY KEY (`exitagdoc_id`),
  INDEX `uti_id` (`exi_id` ASC),
  INDEX `tag_id` (`tag_id` ASC),
  INDEX `doc_id` (`doc_id` ASC),
  INDEX `maj` (`maj` ASC))
ENGINE = MyISAM
AUTO_INCREMENT = 2514
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `flux_biolographes`.`flux_geos`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flux_biolographes`.`flux_geos` ;

CREATE TABLE IF NOT EXISTS `flux_biolographes`.`flux_geos` (
  `geo_id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_instant` INT(11) NOT NULL,
  `lat` DECIMAL(12,8) NOT NULL,
  `lng` DECIMAL(12,8) NOT NULL,
  `zoom_min` INT(11) NOT NULL,
  `zoom_max` INT(11) NOT NULL,
  `adresse` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `codepostal` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `ville` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `pays` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `kml` TEXT CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `type_carte` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `maj` DATETIME NOT NULL,
  PRIMARY KEY (`geo_id`),
  INDEX `id_instant` (`id_instant` ASC))
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci
ROW_FORMAT = DYNAMIC;


-- -----------------------------------------------------
-- Table `flux_biolographes`.`flux_graine`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flux_biolographes`.`flux_graine` ;

CREATE TABLE IF NOT EXISTS `flux_biolographes`.`flux_graine` (
  `graine_id` INT(11) NOT NULL AUTO_INCREMENT,
  `titre` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `class` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `url` VARCHAR(1000) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `maj` DATETIME NOT NULL,
  PRIMARY KEY (`graine_id`))
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `flux_biolographes`.`flux_grainedoc`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flux_biolographes`.`flux_grainedoc` ;

CREATE TABLE IF NOT EXISTS `flux_biolographes`.`flux_grainedoc` (
  `graine_id` INT(11) NOT NULL,
  `doc_id` INT(11) NOT NULL,
  PRIMARY KEY (`graine_id`, `doc_id`))
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `flux_biolographes`.`flux_ieml`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flux_biolographes`.`flux_ieml` ;

CREATE TABLE IF NOT EXISTS `flux_biolographes`.`flux_ieml` (
  `ieml_id` INT(11) NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(50) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `desc` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `niveau` INT(11) NOT NULL,
  `parent` VARCHAR(50) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `maj` DATE NOT NULL,
  `parse` LONGTEXT CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `binary` LONGTEXT CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `ordre` LONGTEXT CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  PRIMARY KEY (`ieml_id`))
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci
COMMENT = 'Cette table est utilisée pour stocker l’ontologie IEML.';


-- -----------------------------------------------------
-- Table `flux_biolographes`.`flux_monade`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flux_biolographes`.`flux_monade` ;

CREATE TABLE IF NOT EXISTS `flux_biolographes`.`flux_monade` (
  `monade_id` INT(11) NOT NULL AUTO_INCREMENT,
  `titre` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `maj` DATETIME NOT NULL,
  PRIMARY KEY (`monade_id`))
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `flux_biolographes`.`flux_rapport`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flux_biolographes`.`flux_rapport` ;

CREATE TABLE IF NOT EXISTS `flux_biolographes`.`flux_rapport` (
  `rapport_id` INT(11) NOT NULL AUTO_INCREMENT,
  `monade_id` INT(11) NOT NULL,
  `exitagdoc_id` INT(11) NOT NULL,
  `maj` DATETIME NOT NULL,
  `niveau` INT(11) NOT NULL,
  PRIMARY KEY (`rapport_id`))
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `flux_biolographes`.`flux_svg`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flux_biolographes`.`flux_svg` ;

CREATE TABLE IF NOT EXISTS `flux_biolographes`.`flux_svg` (
  `svg_id` INT(11) NOT NULL AUTO_INCREMENT,
  `monade_id` INT(11) NOT NULL,
  `obj_id` INT(11) NOT NULL,
  `obj_type` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `data` TEXT CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `maj` DATETIME NOT NULL,
  PRIMARY KEY (`svg_id`))
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `flux_biolographes`.`flux_tag`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flux_biolographes`.`flux_tag` ;

CREATE TABLE IF NOT EXISTS `flux_biolographes`.`flux_tag` (
  `tag_id` INT(11) NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(225) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `desc` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `niveau` INT(11) NOT NULL,
  `parent` INT(11) NOT NULL,
  `lft` INT(11) NOT NULL,
  `rgt` INT(11) NOT NULL,
  PRIMARY KEY (`tag_id`),
  INDEX `code` (`code` ASC),
  INDEX `lft_rgt` (`lft` ASC, `rgt` ASC))
ENGINE = MyISAM
AUTO_INCREMENT = 480
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `flux_biolographes`.`flux_tagdoc`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flux_biolographes`.`flux_tagdoc` ;

CREATE TABLE IF NOT EXISTS `flux_biolographes`.`flux_tagdoc` (
  `tag_id` INT(11) NOT NULL,
  `doc_id` INT(11) NOT NULL,
  `poids` INT(11) NOT NULL,
  `maj` DATETIME NOT NULL,
  PRIMARY KEY (`tag_id`, `doc_id`),
  INDEX `tag_id` (`tag_id` ASC),
  INDEX `doc_id` (`doc_id` ASC))
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `flux_biolographes`.`flux_tagtag`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flux_biolographes`.`flux_tagtag` ;

CREATE TABLE IF NOT EXISTS `flux_biolographes`.`flux_tagtag` (
  `tag_id_src` INT(11) NOT NULL,
  `tag_id_dst` INT(11) NOT NULL,
  `poids` INT(11) NOT NULL,
  `maj` DATETIME NOT NULL,
  PRIMARY KEY (`tag_id_src`, `tag_id_dst`),
  INDEX `tag_id_src` (`tag_id_src` ASC),
  INDEX `tag_id_dst` (`tag_id_dst` ASC))
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `flux_biolographes`.`flux_trad`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flux_biolographes`.`flux_trad` ;

CREATE TABLE IF NOT EXISTS `flux_biolographes`.`flux_trad` (
  `trad_id` INT(11) NOT NULL AUTO_INCREMENT,
  `trad_date` DATE NOT NULL,
  `trad_post` TINYINT(1) NOT NULL,
  `ieml_id` INT(11) NOT NULL,
  `tag_id` INT(11) NOT NULL,
  PRIMARY KEY (`trad_id`),
  INDEX `fk_flux_trad_flux_ieml_idx` (`ieml_id` ASC),
  INDEX `fk_flux_trad_flux_tag1_idx` (`tag_id` ASC))
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `flux_biolographes`.`flux_tradpartage`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flux_biolographes`.`flux_tradpartage` ;

CREATE TABLE IF NOT EXISTS `flux_biolographes`.`flux_tradpartage` (
  `trad_id` INT(11) NOT NULL,
  `uti_id` INT(11) NOT NULL,
  PRIMARY KEY (`trad_id`, `uti_id`))
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `flux_biolographes`.`flux_uti`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flux_biolographes`.`flux_uti` ;

CREATE TABLE IF NOT EXISTS `flux_biolographes`.`flux_uti` (
  `uti_id` INT(11) NOT NULL AUTO_INCREMENT,
  `login` VARCHAR(2000) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `maj` VARCHAR(225) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `flux` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `mdp` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `role` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `mdp_sel` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `email` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `ip_inscription` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `date_inscription` DATETIME NOT NULL,
  PRIMARY KEY (`uti_id`),
  INDEX `login` (`login`(333) ASC))
ENGINE = MyISAM
AUTO_INCREMENT = 3
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `flux_biolographes`.`flux_utidoc`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flux_biolographes`.`flux_utidoc` ;

CREATE TABLE IF NOT EXISTS `flux_biolographes`.`flux_utidoc` (
  `uti_id` INT(11) NOT NULL,
  `doc_id` INT(11) NOT NULL,
  `maj` DATETIME NOT NULL,
  `poids` INT(11) NOT NULL,
  PRIMARY KEY (`uti_id`, `doc_id`),
  INDEX `uti_id` (`uti_id` ASC),
  INDEX `doc_id` (`doc_id` ASC))
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci
ROW_FORMAT = FIXED;


-- -----------------------------------------------------
-- Table `flux_biolographes`.`flux_utiexi`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flux_biolographes`.`flux_utiexi` ;

CREATE TABLE IF NOT EXISTS `flux_biolographes`.`flux_utiexi` (
  `utiexi_id` INT(11) NOT NULL AUTO_INCREMENT,
  `uti_id` INT(11) NOT NULL,
  `exi_id` INT(11) NOT NULL,
  PRIMARY KEY (`utiexi_id`),
  INDEX `uti_id` (`uti_id` ASC),
  INDEX `exi_id` (`exi_id` ASC))
ENGINE = MyISAM
AUTO_INCREMENT = 15
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `flux_biolographes`.`flux_utigeodoc`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flux_biolographes`.`flux_utigeodoc` ;

CREATE TABLE IF NOT EXISTS `flux_biolographes`.`flux_utigeodoc` (
  `geo_id` INT(11) NOT NULL,
  `doc_id` INT(11) NOT NULL,
  `uti_id` INT(11) NOT NULL,
  `maj` DATETIME NOT NULL,
  `utigeodoc_id` INT(11) NOT NULL AUTO_INCREMENT,
  `note` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  PRIMARY KEY (`utigeodoc_id`),
  INDEX `doc_id` (`doc_id` ASC),
  INDEX `uti_id` (`uti_id` ASC),
  INDEX `geo_id` (`geo_id` ASC))
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `flux_biolographes`.`flux_utigraine`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flux_biolographes`.`flux_utigraine` ;

CREATE TABLE IF NOT EXISTS `flux_biolographes`.`flux_utigraine` (
  `uti_id` INT(11) NOT NULL,
  `graine_id` INT(11) NOT NULL,
  PRIMARY KEY (`uti_id`, `graine_id`),
  INDEX `graine_id` (`graine_id` ASC))
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `flux_biolographes`.`flux_utiieml`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flux_biolographes`.`flux_utiieml` ;

CREATE TABLE IF NOT EXISTS `flux_biolographes`.`flux_utiieml` (
  `uti_id` INT(11) NOT NULL,
  `ieml_id` INT(11) NOT NULL,
  PRIMARY KEY (`uti_id`, `ieml_id`),
  INDEX `uti_id` (`uti_id` ASC),
  INDEX `ieml_id` (`ieml_id` ASC))
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `flux_biolographes`.`flux_utimonade`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flux_biolographes`.`flux_utimonade` ;

CREATE TABLE IF NOT EXISTS `flux_biolographes`.`flux_utimonade` (
  `uti_id` INT(11) NOT NULL,
  `monade_id` INT(11) NOT NULL)
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `flux_biolographes`.`flux_utitag`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flux_biolographes`.`flux_utitag` ;

CREATE TABLE IF NOT EXISTS `flux_biolographes`.`flux_utitag` (
  `uti_id` INT(11) NOT NULL,
  `tag_id` INT(11) NOT NULL,
  `poids` INT(11) NOT NULL,
  `maj` DATETIME NOT NULL,
  PRIMARY KEY (`uti_id`, `tag_id`),
  INDEX `uti_id` (`uti_id` ASC),
  INDEX `tag_id` (`tag_id` ASC))
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `flux_biolographes`.`flux_utitagdoc`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flux_biolographes`.`flux_utitagdoc` ;

CREATE TABLE IF NOT EXISTS `flux_biolographes`.`flux_utitagdoc` (
  `utitagdoc_id` INT(11) NOT NULL AUTO_INCREMENT,
  `uti_id` INT(11) NOT NULL,
  `tag_id` INT(11) NOT NULL,
  `doc_id` INT(11) NOT NULL,
  `maj` DATETIME NOT NULL,
  `poids` INT(11) NOT NULL,
  PRIMARY KEY (`utitagdoc_id`),
  INDEX `uti_id` (`uti_id` ASC),
  INDEX `tag_id` (`tag_id` ASC),
  INDEX `doc_id` (`doc_id` ASC),
  INDEX `maj` (`maj` ASC))
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `flux_biolographes`.`flux_utitagrelated`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flux_biolographes`.`flux_utitagrelated` ;

CREATE TABLE IF NOT EXISTS `flux_biolographes`.`flux_utitagrelated` (
  `uti_id` INT(11) NOT NULL,
  `tag_id` INT(11) NOT NULL,
  `poids` INT(11) NOT NULL,
  PRIMARY KEY (`uti_id`, `tag_id`),
  INDEX `uti_id` (`uti_id` ASC),
  INDEX `tag_id` (`tag_id` ASC))
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `flux_biolographes`.`flux_utiuti`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flux_biolographes`.`flux_utiuti` ;

CREATE TABLE IF NOT EXISTS `flux_biolographes`.`flux_utiuti` (
  `uti_id_src` INT(11) NOT NULL,
  `uti_id_dst` INT(11) NOT NULL,
  `network` INT(11) NOT NULL,
  `fan` INT(11) NOT NULL,
  `post` INT(11) NOT NULL,
  PRIMARY KEY (`uti_id_src`, `uti_id_dst`),
  INDEX `uti_id_src` (`uti_id_src` ASC),
  INDEX `uti_id_dst` (`uti_id_dst` ASC))
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `flux_biolographes`.`jardin_exis`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flux_biolographes`.`jardin_exis` ;

CREATE TABLE IF NOT EXISTS `flux_biolographes`.`jardin_exis` (
  `id_exi` INT(11) NOT NULL AUTO_INCREMENT,
  `nom` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_bin' NOT NULL,
  `url` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_bin' NOT NULL,
  `mail` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_bin' NOT NULL,
  `mdp` VARCHAR(32) CHARACTER SET 'utf8' COLLATE 'utf8_bin' NOT NULL,
  `mdp_sel` VARCHAR(32) CHARACTER SET 'utf8' COLLATE 'utf8_bin' NOT NULL,
  `role` VARCHAR(32) CHARACTER SET 'utf8' COLLATE 'utf8_bin' NOT NULL,
  PRIMARY KEY (`id_exi`))
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_bin
ROW_FORMAT = DYNAMIC;

USE `flux_proverbes` ;

-- -----------------------------------------------------
-- Table `flux_proverbes`.`flux_acti`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flux_proverbes`.`flux_acti` ;

CREATE TABLE IF NOT EXISTS `flux_proverbes`.`flux_acti` (
  `acti_id` INT(11) NOT NULL AUTO_INCREMENT,
  `code` TEXT CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `desc` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  PRIMARY KEY (`acti_id`))
ENGINE = MyISAM
AUTO_INCREMENT = 2
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `flux_proverbes`.`flux_doc`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flux_proverbes`.`flux_doc` ;

CREATE TABLE IF NOT EXISTS `flux_proverbes`.`flux_doc` (
  `doc_id` INT(11) NOT NULL AUTO_INCREMENT,
  `url` TEXT CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `titre` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `lft` INT(11) NOT NULL,
  `rgt` INT(11) NOT NULL,
  `tronc` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `poids` INT(11) NOT NULL DEFAULT '1',
  `maj` DATETIME NOT NULL,
  `pubDate` DATETIME NOT NULL,
  `note` TEXT CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `total_posts` INT(11) NOT NULL,
  `top_tags` VARCHAR(2000) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `type` INT(11) NOT NULL,
  `data` BLOB NOT NULL,
  `score` DECIMAL(28,14) NOT NULL,
  `parent` INT(11) NOT NULL,
  `niveau` INT(11) NOT NULL,
  PRIMARY KEY (`doc_id`),
  INDEX `type` (`type` ASC),
  INDEX `score` (`score` ASC),
  FULLTEXT INDEX `titre_note` (`titre` ASC, `note` ASC))
ENGINE = MyISAM
AUTO_INCREMENT = 124
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci
ROW_FORMAT = DYNAMIC;


-- -----------------------------------------------------
-- Table `flux_proverbes`.`flux_doctypes`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flux_proverbes`.`flux_doctypes` ;

CREATE TABLE IF NOT EXISTS `flux_proverbes`.`flux_doctypes` (
  `id_type` BIGINT(21) NOT NULL AUTO_INCREMENT,
  `titre` TEXT CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `descriptif` TEXT CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `extension` VARCHAR(10) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `mime_type` VARCHAR(100) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `inclus` ENUM('non','image','embed') CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL DEFAULT 'non',
  `upload` ENUM('oui','non') CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL DEFAULT 'oui',
  `maj` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_type`),
  UNIQUE INDEX `extension` (`extension` ASC),
  INDEX `inclus` (`inclus` ASC))
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `flux_proverbes`.`flux_exi`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flux_proverbes`.`flux_exi` ;

CREATE TABLE IF NOT EXISTS `flux_proverbes`.`flux_exi` (
  `exi_id` INT(11) NOT NULL AUTO_INCREMENT,
  `uti_id` INT(11) NOT NULL,
  `nom` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `prenom` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `lft` INT(11) NOT NULL,
  `rgt` INT(11) NOT NULL,
  `parent` INT(11) NOT NULL,
  `niveau` INT(11) NOT NULL,
  `maj` DATETIME NOT NULL,
  `poids` INT(11) NOT NULL,
  `data` TEXT CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `nait` DATE NOT NULL,
  `mort` DATE NOT NULL,
  `isni` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  PRIMARY KEY (`exi_id`))
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci
ROW_FORMAT = DYNAMIC;


-- -----------------------------------------------------
-- Table `flux_proverbes`.`flux_geos`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flux_proverbes`.`flux_geos` ;

CREATE TABLE IF NOT EXISTS `flux_proverbes`.`flux_geos` (
  `geo_id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_instant` INT(11) NOT NULL,
  `lat` DECIMAL(12,8) NOT NULL,
  `lng` DECIMAL(12,8) NOT NULL,
  `alt` DECIMAL(20,10) NOT NULL COMMENT 'en mètre',
  `geo` GEOMETRY NOT NULL,
  `zoom_min` INT(11) NOT NULL,
  `zoom_max` INT(11) NOT NULL,
  `adresse` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `codepostal` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `ville` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `pays` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `kml` TEXT CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `type_carte` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `maj` DATETIME NOT NULL,
  PRIMARY KEY (`geo_id`),
  INDEX `id_instant` (`id_instant` ASC))
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci
ROW_FORMAT = DYNAMIC;


-- -----------------------------------------------------
-- Table `flux_proverbes`.`flux_ieml`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flux_proverbes`.`flux_ieml` ;

CREATE TABLE IF NOT EXISTS `flux_proverbes`.`flux_ieml` (
  `ieml_id` INT(11) NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(50) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `desc` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `niveau` INT(11) NOT NULL,
  `parent` VARCHAR(50) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `maj` DATE NOT NULL,
  `parse` LONGTEXT CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `binary` LONGTEXT CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `ordre` LONGTEXT CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  PRIMARY KEY (`ieml_id`))
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci
COMMENT = 'Cette table est utilisée pour stocker l’ontologie IEML.';


-- -----------------------------------------------------
-- Table `flux_proverbes`.`flux_monade`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flux_proverbes`.`flux_monade` ;

CREATE TABLE IF NOT EXISTS `flux_proverbes`.`flux_monade` (
  `monade_id` INT(11) NOT NULL AUTO_INCREMENT,
  `titre` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `maj` DATETIME NOT NULL,
  PRIMARY KEY (`monade_id`))
ENGINE = MyISAM
AUTO_INCREMENT = 2
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `flux_proverbes`.`flux_rapport`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flux_proverbes`.`flux_rapport` ;

CREATE TABLE IF NOT EXISTS `flux_proverbes`.`flux_rapport` (
  `rapport_id` INT(11) NOT NULL AUTO_INCREMENT,
  `monade_id` INT(11) NOT NULL,
  `src_id` INT(11) NOT NULL,
  `src_obj` VARCHAR(20) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `dst_id` INT(11) NOT NULL,
  `dst_obj` VARCHAR(20) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `pre_id` INT(11) NOT NULL COMMENT 'id prédicat',
  `pre_obj` VARCHAR(20) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL COMMENT 'obj prédicat',
  `geo_id` INT(11) NOT NULL,
  `maj` DATETIME NOT NULL,
  `niveau` INT(11) NOT NULL,
  PRIMARY KEY (`rapport_id`))
ENGINE = MyISAM
AUTO_INCREMENT = 21
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `flux_proverbes`.`flux_spip`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flux_proverbes`.`flux_spip` ;

CREATE TABLE IF NOT EXISTS `flux_proverbes`.`flux_spip` (
  `id_flux_spip` INT(11) NOT NULL,
  `id_flux` INT(11) NULL DEFAULT NULL,
  `id_spip` INT(11) NULL DEFAULT NULL,
  `obj_flux` VARCHAR(45) NULL DEFAULT NULL,
  `obj_spip` VARCHAR(45) NULL DEFAULT NULL,
  `lang` VARCHAR(10) NOT NULL)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `flux_proverbes`.`flux_tag`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flux_proverbes`.`flux_tag` ;

CREATE TABLE IF NOT EXISTS `flux_proverbes`.`flux_tag` (
  `tag_id` INT(11) NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(225) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `desc` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `ns` VARCHAR(10) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `type` VARCHAR(100) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `niveau` INT(11) NOT NULL,
  `parent` INT(11) NOT NULL,
  `lft` INT(11) NOT NULL,
  `rgt` INT(11) NOT NULL,
  `uri` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  PRIMARY KEY (`tag_id`),
  INDEX `code` (`code` ASC),
  INDEX `lft_rgt` (`lft` ASC, `rgt` ASC))
ENGINE = MyISAM
AUTO_INCREMENT = 7
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `flux_proverbes`.`flux_uti`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flux_proverbes`.`flux_uti` ;

CREATE TABLE IF NOT EXISTS `flux_proverbes`.`flux_uti` (
  `uti_id` INT(11) NOT NULL AUTO_INCREMENT,
  `login` VARCHAR(2000) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `maj` VARCHAR(225) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `flux` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `mdp` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `role` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `mdp_sel` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `email` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `ip_inscription` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `date_inscription` DATETIME NOT NULL,
  PRIMARY KEY (`uti_id`),
  INDEX `login` (`login`(333) ASC))
ENGINE = MyISAM
AUTO_INCREMENT = 2
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
