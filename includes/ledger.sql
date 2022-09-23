SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `changes`;
CREATE TABLE `changes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tower` varchar(15) NOT NULL DEFAULT '',
  `host` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `job` int(11) NOT NULL,
  `playbook` varchar(256) NOT NULL,
  `play` varchar(256) NOT NULL,
  `role` varchar(64) NOT NULL,
  `task` varchar(256) NOT NULL,
  `task_action` varchar(64) NOT NULL,
  `res` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `time` (`time`),
  KEY `task_action` (`task_action`),
  KEY `playbook` (`playbook`),
  KEY `job` (`job`),
  KEY `host` (`host`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `facts`;
CREATE TABLE `facts` (
  `host` int(11) NOT NULL,
  `fact` varchar(128) NOT NULL,
  `type` varchar(32) NOT NULL,
  `data` text NULL,
  PRIMARY KEY (`host`,`fact`),
  KEY `host` (`host`),
  KEY `fact` (`fact`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hosts`;
CREATE TABLE `hosts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hostname` varchar(128) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `hostname` (`hostname`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `reports`;
CREATE TABLE `reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner` int(11) NOT NULL,
  `name` varchar(256) NOT NULL,
  `created` int(11) NOT NULL,
  `filters` text NOT NULL,
  `columns` text NOT NULL,
  `sortc` int(11) NOT NULL,
  `sortd` varchar(4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`owner`),
  KEY `created` (`created`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(32) NOT NULL,
  `access` int(10) unsigned DEFAULT NULL,
  `user` int(11) DEFAULT 0,
  `data` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `setting` varchar(64) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`setting`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `username` varchar(64) NOT NULL,
  `email` varchar(64) NOT NULL DEFAULT '',
  `password` varchar(128) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  `registered` tinyint(1) NOT NULL DEFAULT 0,
  `code` varchar(256) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `enabled` (`enabled`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
