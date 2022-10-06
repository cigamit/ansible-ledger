SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `changes`;
CREATE TABLE `changes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job` int(11) NOT NULL,
  `job_template_id` int(11) NOT NULL,
  `timestamp` varchar(32) DEFAULT '',
  `host` varchar(128) DEFAULT '',
  `name` varchar(512) DEFAULT '',
  `job_type` varchar(32) DEFAULT '',
  `inventory` varchar(128) DEFAULT '',
  `project` varchar(128) DEFAULT '',
  `scm_branch` varchar(128) DEFAULT '',
  `execution_environment` varchar(128) DEFAULT '',
  `actor` varchar(64) DEFAULT '',
  `limit` text DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `job` (`job`),
  KEY `job_template_id` (`job_template_id`),
  KEY `actor` (`actor`)
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

DROP TABLE IF EXISTS `reports_perms`;
CREATE TABLE `reports_perms` (
  `report` int(11) NOT NULL,
  `user` int(11) NOT NULL,
  PRIMARY KEY (`report`,`user`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `reports` (`id`, `owner`, `name`, `created`, `filters`, `columns`, `sortc`, `sortd`) VALUES
(1,	1,	'Linux Servers',	1663558186,	'YToxOntpOjA7YTozOntzOjQ6ImZhY3QiO3M6MTQ6ImFuc2libGVfc3lzdGVtIjtzOjc6ImNvbXBhcmUiO3M6MjoiZXEiO3M6NToidmFsdWUiO3M6NToiTGludXgiO319',	'YTo3OntzOjg6Ikhvc3RuYW1lIjtzOjE2OiJhbnNpYmxlX2hvc3RuYW1lIjtzOjEwOiJJUCBBZGRyZXNzIjtzOjI4OiJhbnNpYmxlX2RlZmF1bHRfaXB2NC5hZGRyZXNzIjtzOjY6IkRpc3RybyI7czoyMDoiYW5zaWJsZV9kaXN0cmlidXRpb24iO3M6MTQ6IkRpc3RybyBWZXJzaW9uIjtzOjI4OiJhbnNpYmxlX2Rpc3RyaWJ1dGlvbl92ZXJzaW9uIjtzOjE0OiJQeXRob24gVmVyc2lvbiI7czoyMjoiYW5zaWJsZV9weXRob25fdmVyc2lvbiI7czo0OiJDUFVzIjtzOjIzOiJhbnNpYmxlX3Byb2Nlc3Nvcl92Y3B1cyI7czo2OiJNZW1vcnkiO3M6MTk6ImFuc2libGVfbWVtdG90YWxfbWIiO30=',	0,	'asc'),
(2,	1,	'Windows Servers',	1664418280,	'YToxOntpOjA7YTozOntzOjQ6ImZhY3QiO3M6MTc6ImFuc2libGVfb3NfZmFtaWx5IjtzOjc6ImNvbXBhcmUiO3M6MjoiZXEiO3M6NToidmFsdWUiO3M6NzoiV2luZG93cyI7fX0=',	'YTo2OntzOjg6Ikhvc3RuYW1lIjtzOjE2OiJhbnNpYmxlX2hvc3RuYW1lIjtzOjEwOiJJUCBBZGRyZXNzIjtzOjIyOiJhbnNpYmxlX2lwX2FkZHJlc3Nlcy4wIjtzOjEwOiJPUyBWZXJzaW9uIjtzOjIwOiJhbnNpYmxlX2Rpc3RyaWJ1dGlvbiI7czoxMDoiUG93ZXJzaGVsbCI7czoyNjoiYW5zaWJsZV9wb3dlcnNoZWxsX3ZlcnNpb24iO3M6NDoiQ1BVcyI7czoyMzoiYW5zaWJsZV9wcm9jZXNzb3JfdmNwdXMiO3M6NjoiTWVtb3J5IjtzOjE5OiJhbnNpYmxlX21lbXRvdGFsX21iIjt9',	0,	'asc');

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
  `super` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `enabled` (`enabled`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


