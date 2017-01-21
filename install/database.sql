-- phpMyAdmin SQL Dump
-- version 4.0.3
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 14, 2013 at 02:02 PM
-- Server version: 5.6.12
-- PHP Version: 5.3.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `kodirepo`
--
CREATE DATABASE IF NOT EXISTS `kodirepo` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `kodirepo`;

-- --------------------------------------------------------

--
-- Table structure for table `addon`
--

CREATE TABLE IF NOT EXISTS `addon` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `provider_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `version` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `news` text COLLATE utf8_unicode_ci,
  `created` date DEFAULT NULL,
  `updated` date DEFAULT NULL,
  `forum` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `website` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `source` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `license` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `donate` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `rating_total` float DEFAULT NULL,
  `downloads` int(11) DEFAULT NULL,
  `extension_point` tinytext DEFAULT NULL,
  `content_types` tinytext DEFAULT NULL,
  `broken` tinytext DEFAULT NULL,
  `deleted` tinyint(3) unsigned DEFAULT '0' NOT NULL,
  `repository_id` tinytext DEFAULT NULL,
  `platforms` tinytext DEFAULT '',
  `languages` tinytext DEFAULT '',
  `icon` tinytext DEFAULT NULL,
  `fanart` tinytext DEFAULT NULL,
  `screenshots` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY keyaddontype ( `extension_point` ( 60 ) , `content_types` ( 100 ) ),
  KEY keylanguages ( `languages` ( 60 ) ),
  KEY keyplatforms ( `platforms` ( 60 ) ),
  KEY keyauthor ( `provider_name` ( 100 ) )
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------