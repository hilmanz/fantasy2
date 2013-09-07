-- MySQL dump 10.13  Distrib 5.5.28, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: ffg
-- ------------------------------------------------------
-- Server version	5.5.28-1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `teams`
--

DROP TABLE IF EXISTS `teams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teams` (
  `id` bigint(21) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(21) DEFAULT NULL,
  `team_id` varchar(41) DEFAULT NULL,
  `team_name` varchar(140) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQUE_USER_TEAM` (`user_id`,`team_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teams`
--

LOCK TABLES `teams` WRITE;
/*!40000 ALTER TABLE `teams` DISABLE KEYS */;
INSERT INTO `teams` VALUES (2,14,'t56','Sunderland Maniacs');
/*!40000 ALTER TABLE `teams` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(21) NOT NULL AUTO_INCREMENT,
  `fb_id` varchar(32) DEFAULT NULL,
  `name` varchar(64) DEFAULT NULL,
  `email` varchar(64) DEFAULT NULL,
  `location` varchar(64) DEFAULT NULL,
  `register_date` datetime DEFAULT NULL,
  `survey_about` tinyint(3) DEFAULT '0',
  `survey_daily_email` tinyint(3) DEFAULT '0',
  `survey_daily_sms` tinyint(3) DEFAULT '0',
  `survey_has_play` tinyint(3) DEFAULT '0',
  `n_status` tinyint(3) DEFAULT '1' COMMENT '0-> disabled',
  `register_completed` tinyint(3) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQUE_FB_ID` (`fb_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (14,'100001023465395','Thorfinn Karlsefni','tkarlsefni@facebook.com','Jakarta','2013-07-09 14:48:40',5,1,1,1,1,0);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2013-07-13 19:21:11




CREATE TABLE `monthly_points` (
  `id` bigint(21) NOT NULL AUTO_INCREMENT,
  `team_id` bigint(21) DEFAULT NULL,
  `bln` int(3) DEFAULT NULL,
  `thn` int(4) DEFAULT NULL,
  `points` int(11) DEFAULT NULL,
  `rank` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQUE_MONTHLY` (`team_id`,`bln`,`thn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `weekly_points` (
  `id` bigint(21) NOT NULL AUTO_INCREMENT,
  `team_id` bigint(21) DEFAULT NULL,
  `game_id` varchar(32) DEFAULT NULL,
  `matchday` int(11) DEFAULT '0',
  `matchdate` datetime DEFAULT NULL,
  `points` bigint(21) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQUE_TEAM_GAMES` (`team_id`,`game_id`),
  KEY `team_matchday` (`team_id`,`matchday`),
  KEY `IDX_MATHCDATE` (`matchdate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `weekly_ranks` (
  `id` bigint(21) NOT NULL AUTO_INCREMENT,
  `team_id` bigint(21) DEFAULT NULL,
  `matchday` int(5) DEFAULT NULL,
  `rank` int(11) DEFAULT '0' COMMENT 'the last rank before the income is calculated.',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_team_game` (`team_id`,`matchday`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;
