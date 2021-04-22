/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

CREATE DATABASE IF NOT EXISTS `tools` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `tools`;

CREATE TABLE IF NOT EXISTS `addon` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `active` int(1) unsigned NOT NULL DEFAULT '1',
  `slug` varchar(255) NOT NULL,
  `curVersion` varchar(255) NOT NULL,
  `lastUpdate` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=MyISAM AUTO_INCREMENT=332 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `addon_tag` (
  `addon` int(11) NOT NULL,
  `tag` int(11) NOT NULL,
  PRIMARY KEY (`addon`,`tag`),
  KEY `tag` (`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `comment` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user` int(11) unsigned NOT NULL DEFAULT '0',
  `ip` varchar(50) NOT NULL DEFAULT '',
  `foreign_id` int(10) unsigned NOT NULL DEFAULT '0',
  `table` varchar(255) NOT NULL DEFAULT 'addon',
  `tstamp` int(11) NOT NULL,
  `comment` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user`),
  KEY `bug` (`table`)
) ENGINE=MyISAM AUTO_INCREMENT=119 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `description` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `addon` int(10) unsigned NOT NULL COMMENT 'addon:id',
  `author` int(10) unsigned NOT NULL COMMENT 'user:id',
  `description` text NOT NULL,
  `lang` enum('en','fr','de') NOT NULL DEFAULT 'en',
  `date` int(10) unsigned NOT NULL,
  `ip` varchar(255) NOT NULL,
  `active` int(1) unsigned NOT NULL DEFAULT '1',
  `reviewed` int(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  FULLTEXT KEY `description` (`description`)
) ENGINE=MyISAM AUTO_INCREMENT=463 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `download` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'user:id',
  `version` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'version:id',
  `addon` int(11) DEFAULT NULL,
  `useragent` varchar(250) DEFAULT NULL,
  `datetime` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user` (`user`,`version`),
  KEY `addon` (`addon`)
) ENGINE=MyISAM AUTO_INCREMENT=430151 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `endorsement` (
  `addon` int(11) NOT NULL,
  `user` int(11) NOT NULL,
  `endorsed` int(11) DEFAULT NULL,
  PRIMARY KEY (`addon`,`user`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `password` (
  `aid` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) DEFAULT NULL,
  `created` int(11) DEFAULT NULL,
  `hash` char(32) DEFAULT NULL,
  `used` bit(1) DEFAULT b'0',
  PRIMARY KEY (`aid`),
  KEY `user_hash` (`user`,`hash`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `signature` (
  `last_used` int(11) NOT NULL DEFAULT '0',
  `last_updated` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `career` int(2) unsigned NOT NULL DEFAULT '0',
  `level` int(2) unsigned NOT NULL DEFAULT '1',
  `renown` int(3) unsigned NOT NULL DEFAULT '0',
  `id` int(11) unsigned NOT NULL,
  `called` int(10) unsigned NOT NULL DEFAULT '0',
  `played` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `last_used` (`last_used`),
  KEY `last_updated` (`last_updated`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `tag` (
  `aid` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`aid`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=400 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `tag_log` (
  `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `ip` varchar(50) DEFAULT NULL,
  `user` int(11) DEFAULT NULL,
  `addon` int(11) DEFAULT NULL,
  `tags` mediumtext
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `display` varchar(255) NOT NULL,
  `login` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `pass` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `admin` int(1) unsigned NOT NULL DEFAULT '0',
  `banned` int(1) unsigned NOT NULL DEFAULT '0',
  `points` int(11) NOT NULL,
  `ip_optIn` varchar(255) NOT NULL,
  `time_optIn` int(11) NOT NULL,
  `ip_register` varchar(255) NOT NULL,
  `time_register` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `slug` (`slug`),
  FULLTEXT KEY `display` (`display`)
) ENGINE=MyISAM AUTO_INCREMENT=632 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `version` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `main` int(10) unsigned NOT NULL COMMENT 'main-version',
  `sub` int(10) unsigned NOT NULL COMMENT 'sub-version',
  `bug` int(10) unsigned NOT NULL COMMENT 'bugfix-version',
  `addon` int(10) unsigned NOT NULL COMMENT 'addon:id',
  `author` int(10) unsigned NOT NULL COMMENT 'user:id',
  `status` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '0=alpha;1=beta;2=stable',
  `use` int(2) NOT NULL DEFAULT '0' COMMENT '0=unknown,1=working,2=broken',
  `tstamp` int(10) unsigned NOT NULL,
  `reviewed` int(1) unsigned NOT NULL DEFAULT '0',
  `change` text NOT NULL,
  `ip` varchar(255) DEFAULT NULL,
  `disabled` int(1) DEFAULT '0',
  `data` longblob,
  PRIMARY KEY (`id`),
  UNIQUE KEY `version` (`main`,`sub`,`bug`,`addon`),
  KEY `use` (`use`),
  FULLTEXT KEY `change` (`change`)
) ENGINE=MyISAM AUTO_INCREMENT=594 DEFAULT CHARSET=utf8;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
