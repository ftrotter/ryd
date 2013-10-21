-- phpMyAdmin SQL Dump
-- version 3.2.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Apr 26, 2010 at 06:17 AM
-- Server version: 5.1.41
-- PHP Version: 5.3.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `record`
--

-- --------------------------------------------------------

--
-- Table structure for table `invites`
--

CREATE TABLE IF NOT EXISTS `invites` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `to_email` varchar(100) NOT NULL,
  `from_email` varchar(100) NOT NULL,
  `sent` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `phones`
--

CREATE TABLE IF NOT EXISTS `phones` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `phone` varchar(30) NOT NULL,
  `enc_phone` varchar(3000) NOT NULL,
  `phone_hash` varchar(250) NOT NULL,
  `enc_priv_key` varchar(3000) NOT NULL,
  `user_id` int(10) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `active` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `recording`
--

CREATE TABLE IF NOT EXISTS `recording` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL,
  `source` varchar(10) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `name` varchar(100) NOT NULL,
  `deleted` int(11) NOT NULL,
  `locked` int(3) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `recording_comment`
--

CREATE TABLE IF NOT EXISTS `recording_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `recording_id` int(11) NOT NULL,
  `enc_text` longtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `recording_keys`
--

CREATE TABLE IF NOT EXISTS `recording_keys` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL,
  `recording_id` int(10) NOT NULL,
  `enc_key` varchar(3000) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_recording` (`user_id`,`recording_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `recording_transcription`
--

CREATE TABLE IF NOT EXISTS `recording_transcription` (
  `id` int(11) NOT NULL,
  `recording_id` int(11) NOT NULL,
  `enc_transcription` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tag`
--

CREATE TABLE IF NOT EXISTS `tag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tag_table`
--

CREATE TABLE IF NOT EXISTS `tag_table` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table` varchar(20) NOT NULL,
  `table_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  `create_by` int(11) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tokens`
--

CREATE TABLE IF NOT EXISTS `tokens` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL,
  `enc_priv_key` varchar(3000) NOT NULL,
  `signature` varchar(1000) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='for letting users use the API' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `last_login` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `eula_agree` int(11) NOT NULL DEFAULT '0',
  `splash_seen` int(11) NOT NULL DEFAULT '0',
  `motd` int(11) NOT NULL DEFAULT '0',
  `pay_status` varchar(20) NOT NULL DEFAULT '0',
  `pay_code` varchar(20) NOT NULL,
  `pay_good_til` date NOT NULL,
  `enc_priv_key` varchar(3000) NOT NULL,
  `public_key` varchar(1000) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Table structure for table `user_data`
--

CREATE TABLE IF NOT EXISTS `user_data` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL,
  `type` varchar(50) NOT NULL,
  `data` varchar(1000) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `user_recording_access`
--

CREATE TABLE IF NOT EXISTS `user_recording_access` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from_user_id` int(11) NOT NULL,
  `to_recording_id` int(11) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `active` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `user_user_access`
--

CREATE TABLE IF NOT EXISTS `user_user_access` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `from_user_id` int(10) NOT NULL,
  `to_user_id` int(10) NOT NULL,
  `signature` varchar(3000) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `active` int(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_user` (`from_user_id`,`to_user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

