# ************************************************************
# Sequel Pro SQL dump
# Version 4541
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: 127.0.0.1 (MySQL 5.7.26-0ubuntu0.18.04.1)
# Database: tccore_dev
# Generation Time: 2019-07-26 09:37:48 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

DROP TABLE IF EXISTS `telescope_entries`;
DROP TABLE IF EXISTS `telescope_entries_tags`;
DROP TABLE IF EXISTS `telescope_monitoring`;

# Dump of table addresses
# ------------------------------------------------------------

DROP TABLE IF EXISTS `addresses`;

CREATE TABLE `addresses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `name` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `address` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `postal` varchar(7) COLLATE utf8_unicode_ci NOT NULL,
  `city` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `country` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `addresses`;

# Dump of table answer_parent_questions
# ------------------------------------------------------------

DROP TABLE IF EXISTS `answer_parent_questions`;

CREATE TABLE `answer_parent_questions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `answer_id` int(10) unsigned NOT NULL,
  `group_question_id` int(10) unsigned NOT NULL,
  `level` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_answer_parent_questions_answers1_idx` (`answer_id`),
  KEY `fk_answer_parent_questions_group_questions1_idx` (`group_question_id`),
  CONSTRAINT `fk_answer_parent_questions_answers1` FOREIGN KEY (`answer_id`) REFERENCES `answers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_answer_parent_questions_group_questions1` FOREIGN KEY (`group_question_id`) REFERENCES `group_questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `answer_parent_questions`;

LOCK TABLES `answer_parent_questions` WRITE;
/*!40000 ALTER TABLE `answer_parent_questions` DISABLE KEYS */;

INSERT INTO `answer_parent_questions` (`id`, `created_at`, `updated_at`, `deleted_at`, `answer_id`, `group_question_id`, `level`)
VALUES
	(1,'2019-02-27 14:38:24','2019-02-27 14:38:24',NULL,2,14,1),
	(2,'2019-02-27 14:39:38','2019-02-27 14:39:38',NULL,8,14,1);

/*!40000 ALTER TABLE `answer_parent_questions` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table answer_ratings
# ------------------------------------------------------------

DROP TABLE IF EXISTS `answer_ratings`;

CREATE TABLE `answer_ratings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `answer_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `test_take_id` int(10) unsigned NOT NULL,
  `type` enum('SYSTEM','STUDENT','TEACHER') COLLATE utf8_unicode_ci NOT NULL,
  `rating` decimal(11,1) unsigned DEFAULT NULL,
  `advise` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `fk_answer_ratings_answers1_idx` (`answer_id`),
  KEY `fk_answer_ratings_users1_idx` (`user_id`),
  KEY `fk_answer_ratings_test_takes1_idx` (`test_take_id`),
  CONSTRAINT `fk_answer_ratings_answers1` FOREIGN KEY (`answer_id`) REFERENCES `answers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_answer_ratings_test_takes1` FOREIGN KEY (`test_take_id`) REFERENCES `test_takes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_answer_ratings_users1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `answer_ratings`;

LOCK TABLES `answer_ratings` WRITE;
/*!40000 ALTER TABLE `answer_ratings` DISABLE KEYS */;

INSERT INTO `answer_ratings` (`id`, `created_at`, `updated_at`, `deleted_at`, `answer_id`, `user_id`, `test_take_id`, `type`, `rating`, `advise`)
VALUES
	(1,'2019-02-27 14:40:38','2019-02-27 14:40:38',NULL,4,NULL,2,'SYSTEM',4.0,NULL),
	(2,'2019-02-27 14:40:38','2019-02-27 14:40:38',NULL,5,NULL,2,'SYSTEM',5.0,NULL),
	(3,'2019-02-27 14:40:38','2019-02-27 14:40:38',NULL,6,NULL,2,'SYSTEM',5.0,NULL),
	(4,'2019-02-27 14:40:38','2019-02-27 14:40:38',NULL,10,NULL,2,'SYSTEM',4.0,NULL),
	(5,'2019-02-27 14:40:38','2019-02-27 14:40:38',NULL,11,NULL,2,'SYSTEM',5.0,NULL),
	(6,'2019-02-27 14:40:38','2019-02-27 14:40:38',NULL,12,NULL,2,'SYSTEM',1.0,NULL),
	(7,'2019-02-27 14:42:46','2019-02-27 14:42:46',NULL,7,1486,2,'TEACHER',1.0,NULL),
	(8,'2019-02-27 14:42:48','2019-02-27 14:42:48',NULL,1,1486,2,'TEACHER',5.0,NULL),
	(9,'2019-02-27 14:42:53','2019-02-27 14:42:53',NULL,8,1486,2,'TEACHER',1.0,NULL),
	(10,'2019-02-27 14:42:55','2019-02-27 14:42:55',NULL,2,1486,2,'TEACHER',5.0,NULL),
	(11,'2019-02-27 14:42:58','2019-02-27 14:42:58',NULL,9,1486,2,'TEACHER',0.0,NULL),
	(12,'2019-02-27 14:43:00','2019-02-27 14:43:00',NULL,3,1486,2,'TEACHER',5.0,NULL);

/*!40000 ALTER TABLE `answer_ratings` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table answers
# ------------------------------------------------------------

DROP TABLE IF EXISTS `answers`;

CREATE TABLE `answers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `test_participant_id` int(10) unsigned NOT NULL,
  `question_id` int(10) unsigned NOT NULL,
  `json` longtext COLLATE utf8_unicode_ci,
  `note` longblob,
  `order` int(10) unsigned NOT NULL,
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  `done` tinyint(1) NOT NULL DEFAULT '0',
  `final_rating` decimal(11,1) unsigned DEFAULT NULL,
  `ignore_for_rating` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_test_participants_has_questions` (`test_participant_id`,`question_id`),
  KEY `fk_test_participants_has_questions_test_participants1_idx` (`test_participant_id`),
  KEY `fk_test_participants_has_questions_questions1_idx` (`question_id`),
  CONSTRAINT `fk_test_participants_has_questions_questions1` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_test_participants_has_questions_test_participants1` FOREIGN KEY (`test_participant_id`) REFERENCES `test_participants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `answers`;

LOCK TABLES `answers` WRITE;
/*!40000 ALTER TABLE `answers` DISABLE KEYS */;

INSERT INTO `answers` (`id`, `created_at`, `updated_at`, `deleted_at`, `test_participant_id`, `question_id`, `json`, `note`, `order`, `time`, `done`, `final_rating`, `ignore_for_rating`)
VALUES
	(1,'2019-02-27 14:38:24','2019-02-27 14:44:11',NULL,3,10,'{\"value\":\"test\"}',NULL,1,6,1,5.0,0),
	(2,'2019-02-27 14:38:24','2019-02-27 14:44:11',NULL,3,15,'{\"0\":\"test\"}',NULL,2,1,1,5.0,0),
	(3,'2019-02-27 14:38:24','2019-02-27 14:44:11',NULL,3,11,'{\"value\":\"open kort antwoord\"}',NULL,3,6,1,5.0,0),
	(4,'2019-02-27 14:38:24','2019-02-27 14:44:11',NULL,3,16,'{\"2\":\"0\",\"1\":\"1\",\"3\":\"0\"}',NULL,4,2,1,4.0,0),
	(5,'2019-02-27 14:38:24','2019-02-27 14:44:11',NULL,3,17,'{\"4\":\"3\",\"2\":\"1\"}',NULL,5,3,1,5.0,0),
	(6,'2019-02-27 14:38:24','2019-02-27 14:44:11',NULL,3,18,'{\"3\":\"2\",\"4\":\"3\",\"1\":\"0\",\"2\":\"1\"}',NULL,6,5,1,5.0,0),
	(7,'2019-02-27 14:39:38','2019-02-27 14:44:11',NULL,4,10,'{\"value\":\"asdasd\"}',NULL,1,1,1,1.0,0),
	(8,'2019-02-27 14:39:38','2019-02-27 14:44:11',NULL,4,15,'{\"0\":\"sddsd\"}',NULL,2,1,1,1.0,0),
	(9,'2019-02-27 14:39:38','2019-02-27 14:44:11',NULL,4,11,'{\"value\":\"sadasdsad\"}',NULL,3,1,1,0.0,0),
	(10,'2019-02-27 14:39:38','2019-02-27 14:44:11',NULL,4,16,'{\"2\":\"0\",\"1\":\"1\",\"3\":\"0\"}',NULL,4,2,1,4.0,0),
	(11,'2019-02-27 14:39:38','2019-02-27 14:44:11',NULL,4,17,'{\"2\":\"1\",\"4\":\"3\"}',NULL,5,4,1,5.0,0),
	(12,'2019-02-27 14:39:38','2019-02-27 14:44:11',NULL,4,18,'{\"4\":\"3\",\"2\":\"0\",\"1\":\"1\",\"3\":\"2\"}',NULL,6,3,1,1.0,0);

/*!40000 ALTER TABLE `answers` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table attachments
# ------------------------------------------------------------

DROP TABLE IF EXISTS `attachments`;

CREATE TABLE `attachments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `type` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `text` mediumtext COLLATE utf8_unicode_ci,
  `link` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `file_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `file_size` int(10) unsigned DEFAULT NULL,
  `file_mime_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `file_extension` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `json` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `attachments`;

LOCK TABLES `attachments` WRITE;
/*!40000 ALTER TABLE `attachments` DISABLE KEYS */;

INSERT INTO `attachments` (`id`, `created_at`, `updated_at`, `deleted_at`, `type`, `title`, `description`, `text`, `link`, `file_name`, `file_size`, `file_mime_type`, `file_extension`, `json`)
VALUES
	(1,'2019-01-04 11:18:22','2019-01-04 11:18:22',NULL,'file','testmp3.mp3',NULL,NULL,NULL,'1546597100',108974,'audio/mpeg','','{\"pausable\":\"1\",\"play_once\":\"0\",\"timeout\":\"\"}');

/*!40000 ALTER TABLE `attachments` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table attainments
# ------------------------------------------------------------

DROP TABLE IF EXISTS `attainments`;

CREATE TABLE `attainments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `base_subject_id` int(10) unsigned NOT NULL,
  `education_level_id` int(10) unsigned NOT NULL,
  `attainment_id` int(10) unsigned DEFAULT NULL,
  `code` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `subcode` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `status` enum('ACTIVE','REPLACED','OLD') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'ACTIVE',
  PRIMARY KEY (`id`),
  KEY `fk_attainments_base_subjects1_idx` (`base_subject_id`),
  KEY `fk_attainments_education_levels1_idx` (`education_level_id`),
  KEY `fk_attainments_attainments1_idx` (`attainment_id`),
  CONSTRAINT `fk_attainments_attainments1` FOREIGN KEY (`attainment_id`) REFERENCES `attainments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_attainments_base_subjects1` FOREIGN KEY (`base_subject_id`) REFERENCES `base_subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_attainments_education_levels1` FOREIGN KEY (`education_level_id`) REFERENCES `education_levels` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `attainments`;

# Dump of table average_ratings
# ------------------------------------------------------------

DROP TABLE IF EXISTS `average_ratings`;

CREATE TABLE `average_ratings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `rating` decimal(8,4) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `school_class_id` int(10) unsigned NOT NULL,
  `subject_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_average_ratings_users1_idx` (`user_id`),
  KEY `fk_average_ratings_school_classes1_idx` (`school_class_id`),
  KEY `fk_average_ratings_subjects1_idx` (`subject_id`),
  CONSTRAINT `fk_average_ratings_school_classes1` FOREIGN KEY (`school_class_id`) REFERENCES `school_classes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_average_ratings_subjects1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_average_ratings_users1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `average_ratings`;

# Dump of table base_subjects
# ------------------------------------------------------------

DROP TABLE IF EXISTS `base_subjects`;

CREATE TABLE `base_subjects` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `name` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `base_subjects`;

LOCK TABLES `base_subjects` WRITE;
/*!40000 ALTER TABLE `base_subjects` DISABLE KEYS */;

INSERT INTO `base_subjects` (`id`, `created_at`, `updated_at`, `deleted_at`, `name`)
VALUES
	(1,'2015-05-08 15:56:57','2015-05-08 15:56:57',NULL,'Nederlands'),
	(2,'2015-11-05 14:32:37','2015-11-05 14:32:37',NULL,'Fries'),
	(3,'2015-11-05 14:32:40','2015-11-05 14:32:40',NULL,'Grieks'),
	(4,'2015-11-05 14:32:41','2015-11-05 14:32:41',NULL,'Latijn'),
	(5,'2015-11-05 14:32:43','2015-11-05 14:32:43',NULL,'Wiskunde A'),
	(6,'2015-11-05 14:32:45','2015-11-05 14:32:45',NULL,'Wiskunde B'),
	(7,'2015-11-05 14:32:49','2015-11-05 14:32:49',NULL,'Wiskunde C'),
	(8,'2015-11-05 14:32:51','2015-11-05 14:32:51',NULL,'Wiskunde D'),
	(9,'2015-11-05 14:32:53','2015-11-05 14:32:53',NULL,'Natuurkunde'),
	(10,'2015-11-05 14:32:55','2015-11-05 14:32:55',NULL,'Scheikunde'),
	(11,'2015-11-05 14:32:57','2015-11-05 14:32:57',NULL,'Biologie'),
	(12,'2015-11-05 14:32:59','2015-11-05 14:32:59',NULL,'ANW'),
	(13,'2015-11-05 14:33:01','2015-11-05 14:33:01',NULL,'NLT'),
	(14,'2015-11-05 14:33:03','2015-11-05 14:33:03',NULL,'Informatica'),
	(15,'2015-11-05 14:33:05','2015-11-05 14:33:05',NULL,'Geschiedenis'),
	(16,'2015-11-05 14:33:07','2015-11-05 14:33:07',NULL,'Aardrijkskunde'),
	(17,'2015-11-05 14:33:08','2015-11-05 14:33:08',NULL,'Economie'),
	(18,'2015-11-05 14:33:10','2015-11-05 14:33:10',NULL,'Maatschappijwetenschappen'),
	(19,'2015-11-05 14:33:12','2015-11-05 14:33:12',NULL,'Maatschappijleer'),
	(20,'2015-11-05 14:33:14','2015-11-05 14:33:14',NULL,'Management en organisatie'),
	(21,'2015-11-05 14:33:15','2015-11-05 14:33:15',NULL,'Filosofie'),
	(22,'2015-11-05 14:33:17','2015-11-05 14:33:17',NULL,'Engels'),
	(23,'2015-11-05 14:33:19','2015-11-05 14:33:19',NULL,'Frans'),
	(24,'2015-11-05 14:33:21','2015-11-05 14:33:21',NULL,'Duits'),
	(25,'2015-11-05 14:33:22','2015-11-05 14:33:22',NULL,'Spaans'),
	(26,'2015-11-05 14:33:23','2015-11-05 14:33:23',NULL,'Wiskunde'),
	(27,'2015-11-05 14:33:25','2015-11-05 14:33:25',NULL,'NASK1'),
	(28,'2015-11-05 14:33:26','2015-11-05 14:33:26',NULL,'NASK2'),
	(29,'2016-09-01 14:07:30','2016-09-01 14:07:30',NULL,'ML2');

/*!40000 ALTER TABLE `base_subjects` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table completion_question_answer_links
# ------------------------------------------------------------

DROP TABLE IF EXISTS `completion_question_answer_links`;

CREATE TABLE `completion_question_answer_links` (
  `completion_question_id` int(10) unsigned NOT NULL,
  `completion_question_answer_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`completion_question_id`,`completion_question_answer_id`),
  KEY `fk_completion_question_answer_links_completion_questions1_idx` (`completion_question_id`),
  KEY `fk_completion_question_answer_links_completion_question_ans_idx` (`completion_question_answer_id`),
  CONSTRAINT `fk_completion_question_answer_links_completion_question_answe1` FOREIGN KEY (`completion_question_answer_id`) REFERENCES `completion_question_answers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_completion_question_answer_links_completion_questions1` FOREIGN KEY (`completion_question_id`) REFERENCES `completion_questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `completion_question_answer_links`;

LOCK TABLES `completion_question_answer_links` WRITE;
/*!40000 ALTER TABLE `completion_question_answer_links` DISABLE KEYS */;

INSERT INTO `completion_question_answer_links` (`completion_question_id`, `completion_question_answer_id`, `created_at`, `updated_at`, `deleted_at`)
VALUES
	(13,1,'2019-01-04 11:40:09','2019-01-04 11:40:09',NULL),
	(13,2,'2019-01-04 11:40:09','2019-01-04 11:40:09',NULL),
	(13,3,'2019-01-04 11:40:09','2019-01-04 11:40:09',NULL),
	(13,4,'2019-01-04 11:40:09','2019-01-04 11:40:09',NULL),
	(13,5,'2019-01-04 11:40:09','2019-01-04 11:40:09',NULL),
	(13,6,'2019-01-04 11:40:09','2019-01-04 11:40:09',NULL),
	(13,7,'2019-01-04 11:40:09','2019-01-04 11:40:09',NULL),
	(13,8,'2019-01-04 11:40:10','2019-01-04 11:40:10',NULL),
	(13,9,'2019-01-04 11:40:10','2019-01-04 11:40:10',NULL),
	(13,10,'2019-01-04 11:40:10','2019-01-04 11:40:10',NULL),
	(13,11,'2019-01-04 11:40:10','2019-01-04 11:40:10',NULL),
	(15,12,'2019-01-21 07:57:43','2019-01-21 07:57:43',NULL);

/*!40000 ALTER TABLE `completion_question_answer_links` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table completion_question_answers
# ------------------------------------------------------------

DROP TABLE IF EXISTS `completion_question_answers`;

CREATE TABLE `completion_question_answers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `tag` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `answer` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `correct` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `completion_question_answers`;

LOCK TABLES `completion_question_answers` WRITE;
/*!40000 ALTER TABLE `completion_question_answers` DISABLE KEYS */;

INSERT INTO `completion_question_answers` (`id`, `created_at`, `updated_at`, `deleted_at`, `tag`, `answer`, `correct`)
VALUES
	(1,'2019-01-04 11:40:09','2019-01-04 11:40:09',NULL,'1','gat1',1),
	(2,'2019-01-04 11:40:09','2019-01-04 11:40:09',NULL,'2','gat2',1),
	(3,'2019-01-04 11:40:09','2019-01-04 11:40:09',NULL,'3','gat3',1),
	(4,'2019-01-04 11:40:09','2019-01-04 11:40:09',NULL,'4','gat4',1),
	(5,'2019-01-04 11:40:09','2019-01-04 11:40:09',NULL,'5','gat5',1),
	(6,'2019-01-04 11:40:09','2019-01-04 11:40:09',NULL,'6','gat6',1),
	(7,'2019-01-04 11:40:09','2019-01-04 11:40:09',NULL,'7','gat7',1),
	(8,'2019-01-04 11:40:10','2019-01-04 11:40:10',NULL,'8','gat8',1),
	(9,'2019-01-04 11:40:10','2019-01-04 11:40:10',NULL,'9','gat9',1),
	(10,'2019-01-04 11:40:10','2019-01-04 11:40:10',NULL,'10','gat10',1),
	(11,'2019-01-04 11:40:10','2019-01-04 11:40:10',NULL,'11','gat11',1),
	(12,'2019-01-21 07:57:43','2019-01-21 07:57:43',NULL,'1','aa',1);

/*!40000 ALTER TABLE `completion_question_answers` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table completion_question_answers_5_1_2018
# ------------------------------------------------------------

DROP TABLE IF EXISTS `completion_question_answers_5_1_2018`;

CREATE TABLE `completion_question_answers_5_1_2018` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `tag` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `answer` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `correct` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `completion_question_answers_5_1_2018`;

LOCK TABLES `completion_question_answers_5_1_2018` WRITE;
/*!40000 ALTER TABLE `completion_question_answers_5_1_2018` DISABLE KEYS */;

INSERT INTO `completion_question_answers_5_1_2018` (`id`, `created_at`, `updated_at`, `deleted_at`, `tag`, `answer`, `correct`)
VALUES
	(1,'2015-02-10 12:06:37','2015-02-10 12:06:37',NULL,'1','consectetur',0),
	(2,'2015-02-10 12:06:37','2015-02-10 12:06:37',NULL,'2','eleifend',0),
	(3,'2015-02-10 12:06:37','2015-02-10 12:06:37',NULL,'3','malesuada',0),
	(4,'2015-02-10 12:17:04','2015-02-10 12:44:39',NULL,'1','dsfgdsfg',0),
	(5,'2015-02-10 12:44:23','2015-02-10 12:44:39','2015-02-10 12:44:39','2','dsfgdsfg',0),
	(6,'2015-02-10 12:45:34','2015-02-10 12:59:16',NULL,'1','vraag',0),
	(7,'2015-02-10 12:59:03','2015-02-10 12:59:03',NULL,'2','andere',0),
	(8,'2015-02-10 13:04:18','2015-02-10 13:04:18',NULL,'1','sdfg',0),
	(9,'2015-02-10 13:04:18','2015-02-10 13:04:18',NULL,'2','dfsgdsfg',0),
	(10,'2015-02-10 17:14:14','2015-02-10 17:14:14',NULL,'1','gekke ',0),
	(11,'2015-02-10 17:14:14','2015-02-10 17:14:14',NULL,'2','woorden',0),
	(12,'2015-02-11 11:06:57','2015-02-11 11:06:57',NULL,'1','',0),
	(13,'2015-02-12 08:07:09','2015-02-12 08:07:09',NULL,'1','star',0),
	(14,'2015-02-12 08:07:09','2015-02-12 08:07:09',NULL,'2','are',0),
	(15,'2015-02-12 08:07:09','2015-02-12 08:07:09',NULL,'3','reaction',0),
	(16,'2015-02-12 08:07:09','2015-02-12 08:07:09',NULL,'4','',0),
	(17,'2015-02-12 08:07:09','2015-02-12 08:07:09',NULL,'5',' attraction',0),
	(18,'2015-02-12 14:28:26','2015-02-12 14:28:26',NULL,'1','gsdfg',0),
	(19,'2015-02-12 14:42:43','2015-02-12 14:42:43',NULL,'1','asdfsadf',0),
	(20,'2015-02-12 19:35:13','2015-02-12 19:35:13',NULL,'1','boat',0),
	(21,'2015-02-12 19:35:13','2015-02-12 19:35:13',NULL,'2','down',0),
	(22,'2015-02-12 19:35:13','2015-02-12 19:35:13',NULL,'3','dream',0),
	(23,'2015-02-14 09:46:24','2015-02-14 09:46:24',NULL,'1','aanvul',0),
	(24,'2015-02-14 09:46:24','2015-02-14 09:46:24',NULL,'2','woorden',0),
	(25,'2015-02-14 09:46:24','2015-02-14 09:46:24',NULL,'3','worden',0),
	(26,'2015-02-14 09:59:56','2015-02-14 09:59:56',NULL,'1','aanvul-vraag',0),
	(27,'2015-02-14 15:43:28','2015-02-14 15:43:28',NULL,'1','vlieg',0),
	(28,'2015-02-14 15:43:28','2015-02-14 15:43:28',NULL,'2','gemaakt',0),
	(29,'2015-02-14 15:44:26','2015-02-14 15:44:26',NULL,'1','denneboom',0),
	(30,'2015-02-14 15:44:26','2015-02-14 15:44:26',NULL,'2','denneboom',0),
	(31,'2015-02-14 15:44:26','2015-02-14 15:44:26',NULL,'3','wonderschoon',0),
	(32,'2015-02-16 06:50:39','2015-02-16 06:50:39',NULL,'1','woorden',0),
	(33,'2015-02-17 09:20:32','2015-02-17 09:20:32',NULL,'1','Piet ',0),
	(34,'2015-02-17 09:20:32','2015-02-17 09:20:32',NULL,'2','Piet',0),
	(35,'2015-02-19 17:17:16','2015-02-19 17:17:16',NULL,'1','woorden',0),
	(36,'2015-02-19 17:17:16','2015-02-19 17:17:16',NULL,'2','',0),
	(37,'2015-03-03 12:46:59','2015-03-03 12:46:59',NULL,'1','strottenhoofd',0),
	(38,'2015-03-03 12:46:59','2015-03-03 12:46:59',NULL,'2','longen',0),
	(39,'2015-03-03 12:46:59','2015-03-03 12:46:59',NULL,'3','Slokdarm',0),
	(40,'2015-03-03 12:46:59','2015-03-03 12:46:59',NULL,'4','Slokdarm',0),
	(41,'2015-03-03 12:46:59','2015-03-03 12:46:59',NULL,'5','Buikvlies',0),
	(42,'2015-03-03 12:46:59','2015-03-03 12:46:59',NULL,'6','Lever',0),
	(43,'2015-03-03 12:46:59','2015-03-03 12:46:59',NULL,'7','Mild',0),
	(44,'2015-03-03 12:46:59','2015-03-03 12:46:59',NULL,'8','Dikke darm',0),
	(45,'2015-03-03 12:46:59','2015-03-03 12:46:59',NULL,'9','Dunne darm',0),
	(46,'2015-03-03 12:47:33','2015-03-03 12:47:33',NULL,'1','TEst',0),
	(47,'2015-03-03 12:58:12','2015-03-03 12:58:12',NULL,'1','koolhydraten',0),
	(48,'2015-03-03 12:58:12','2015-03-03 12:58:12',NULL,'2','',0),
	(49,'2015-03-03 12:58:12','2015-03-03 12:58:12',NULL,'3','Joule',0),
	(50,'2015-03-03 12:58:12','2015-03-03 12:58:12',NULL,'4','cytoplasma',0),
	(51,'2015-03-03 12:58:12','2015-03-03 12:58:12',NULL,'5','ijzer ',0),
	(52,'2015-03-03 12:58:12','2015-03-03 12:58:12',NULL,'6','vegetariër',0),
	(53,'2015-03-03 13:41:51','2015-03-03 13:41:51',NULL,'1','1',0),
	(54,'2015-03-03 13:41:51','2015-03-03 13:41:51',NULL,'2','2',0),
	(55,'2015-03-03 13:41:51','2015-03-03 13:41:51',NULL,'3','3',0),
	(56,'2015-03-03 13:41:51','2015-03-03 13:41:51',NULL,'4','4',0),
	(57,'2015-03-03 15:17:43','2015-03-03 15:17:43',NULL,'1','strottenhoofd',0),
	(58,'2015-03-03 15:17:43','2015-03-03 15:17:43',NULL,'2','longen',0),
	(59,'2015-03-03 15:17:43','2015-03-03 15:17:43',NULL,'3','Slokdarm',0),
	(60,'2015-03-03 15:17:43','2015-03-03 15:17:43',NULL,'4','Slokdarm',0),
	(61,'2015-03-03 15:17:43','2015-03-03 15:17:43',NULL,'5','Buikvlies',0),
	(62,'2015-03-03 15:17:43','2015-03-03 15:17:43',NULL,'6','Lever',0),
	(63,'2015-03-03 15:17:43','2015-03-03 15:17:43',NULL,'7','Mild',0),
	(64,'2015-03-03 15:17:43','2015-03-03 15:17:43',NULL,'8','Dikke darm',0),
	(65,'2015-03-03 15:17:43','2015-03-03 15:17:43',NULL,'9','Dunne darm',0),
	(66,'2015-03-04 08:01:11','2015-03-04 08:01:11',NULL,'1','vraag',0),
	(67,'2015-03-04 08:01:11','2015-03-04 08:01:11',NULL,'2','dikgedrukte',0),
	(68,'2015-03-04 14:15:27','2015-03-04 14:15:27',NULL,'1','strottenhoofd',0),
	(69,'2015-03-04 14:15:27','2015-03-04 14:15:27',NULL,'2','longen',0),
	(70,'2015-03-04 14:15:27','2015-03-04 14:15:27',NULL,'3','Slokdarm',0),
	(71,'2015-03-04 14:15:27','2015-03-04 14:15:27',NULL,'4','Slokdarm',0),
	(72,'2015-03-04 14:15:27','2015-03-04 14:15:27',NULL,'5','Buikvlies',0),
	(73,'2015-03-04 14:15:27','2015-03-04 14:15:27',NULL,'6','Lever',0),
	(74,'2015-03-04 14:15:27','2015-03-04 14:15:27',NULL,'7','Mild',0),
	(75,'2015-03-04 14:15:27','2015-03-04 14:15:27',NULL,'8','Dikke darm',0),
	(76,'2015-03-04 14:15:27','2015-03-04 14:15:27',NULL,'9','Dunne darm',0),
	(77,'2015-03-04 14:24:05','2015-03-04 14:24:05',NULL,'1','koolhydraten',0),
	(78,'2015-03-04 14:24:05','2015-03-04 14:24:05',NULL,'2','',0),
	(79,'2015-03-04 14:24:05','2015-03-04 14:24:05',NULL,'3','Joule',0),
	(80,'2015-03-04 14:24:05','2015-03-04 14:24:05',NULL,'4','cytoplasma',0),
	(81,'2015-03-04 14:24:05','2015-03-04 14:24:05',NULL,'5','ijzer ',0),
	(82,'2015-03-04 14:24:05','2015-03-04 14:24:05',NULL,'6','vegetariër',0),
	(83,'2015-03-04 15:55:24','2015-03-04 15:55:24',NULL,'1','1',0),
	(84,'2015-03-04 15:55:24','2015-03-04 15:55:24',NULL,'2','2',0),
	(85,'2015-03-04 15:55:24','2015-03-04 15:55:24',NULL,'3','3',0),
	(86,'2015-03-04 15:55:24','2015-03-04 15:55:24',NULL,'4','4',0),
	(87,'2015-03-04 15:55:24','2015-03-04 15:55:24',NULL,'1','strottenhoofd',0),
	(88,'2015-03-04 15:55:24','2015-03-04 15:55:24',NULL,'2','longen',0),
	(89,'2015-03-04 15:55:24','2015-03-04 15:55:24',NULL,'3','Slokdarm',0),
	(90,'2015-03-04 15:55:24','2015-03-04 15:55:24',NULL,'4','Slokdarm',0),
	(91,'2015-03-04 15:55:24','2015-03-04 15:55:24',NULL,'5','Buikvlies',0),
	(92,'2015-03-04 15:55:24','2015-03-04 15:55:24',NULL,'6','Lever',0),
	(93,'2015-03-04 15:55:24','2015-03-04 15:55:24',NULL,'7','Mild',0),
	(94,'2015-03-04 15:55:24','2015-03-04 15:55:24',NULL,'8','Dikke darm',0),
	(95,'2015-03-04 15:55:24','2015-03-04 15:55:24',NULL,'9','Dunne darm',0),
	(96,'2015-03-05 16:06:15','2015-03-05 16:06:15',NULL,'1','gatentekst',0),
	(97,'2015-03-05 16:06:57','2015-03-05 16:06:57',NULL,'1','gatentekst',0),
	(98,'2015-03-05 16:06:57','2015-03-05 16:06:57',NULL,'2','twee',0),
	(99,'2015-03-05 20:42:53','2015-03-05 20:42:53',NULL,'1','koolhydraten',0),
	(100,'2015-03-05 20:42:53','2015-03-05 20:42:53',NULL,'2','',0),
	(101,'2015-03-05 20:42:53','2015-03-05 20:42:53',NULL,'3','Joule',0),
	(102,'2015-03-05 20:42:53','2015-03-05 20:42:53',NULL,'4','cytoplasma',0),
	(103,'2015-03-05 20:42:53','2015-03-05 20:42:53',NULL,'5','ijzer ',0),
	(104,'2015-03-05 20:42:53','2015-03-05 20:42:53',NULL,'6','vegetariër',0),
	(105,'2015-03-06 12:02:37','2015-03-06 12:02:37',NULL,'1','woorden',0),
	(106,'2015-03-06 12:02:37','2015-03-06 12:02:37',NULL,'2','ingevuld',0),
	(107,'2015-03-06 18:17:02','2015-03-06 18:17:02',NULL,'1','eens',0),
	(108,'2015-03-06 18:17:30','2015-03-06 18:17:51',NULL,'1','kabouter',0),
	(109,'2015-03-06 18:17:30','2015-03-06 18:17:51',NULL,'2','paddestoel',0),
	(110,'2015-03-06 18:19:24','2015-03-06 18:19:24',NULL,'1','test',0),
	(111,'2015-03-07 09:17:35','2015-03-07 09:17:35',NULL,'1','broer',0),
	(112,'2015-03-07 09:17:35','2015-03-07 09:17:35',NULL,'2','middelste broer',0),
	(113,'2015-03-07 09:17:35','2015-03-07 09:17:35',NULL,'3','verreweg de jongste broer',0),
	(114,'2015-03-07 09:20:14','2015-03-07 09:20:14',NULL,'1','goed/slecht',0),
	(115,'2015-03-07 09:20:14','2015-03-07 09:20:14',NULL,'2','niet/wel',0),
	(116,'2015-03-10 11:39:07','2015-03-10 11:39:19',NULL,'1','aan',0),
	(117,'2015-03-10 11:39:07','2015-03-10 11:39:07',NULL,'2','woorden',0),
	(118,'2015-03-10 11:44:09','2015-03-10 11:44:09',NULL,'1','diebla',0),
	(119,'2015-03-10 12:57:02','2015-03-10 12:57:02',NULL,'1','Lisse',0),
	(120,'2015-03-10 12:57:02','2015-03-10 12:57:02',NULL,'2','2000',0),
	(121,'2015-03-10 12:57:02','2015-03-10 12:57:02',NULL,'3','schoolband',0),
	(122,'2015-03-10 12:57:02','2015-03-10 12:57:02',NULL,'4','directeur',0),
	(123,'2015-03-10 12:57:02','2015-03-10 12:57:02',NULL,'5','mavo',0),
	(124,'2015-03-10 13:56:45','2015-03-10 13:56:45',NULL,'1','dsfg',0),
	(125,'2015-03-10 16:20:22','2015-03-10 16:20:22',NULL,'1','Lisse',0),
	(126,'2015-03-10 16:20:22','2015-03-10 16:20:22',NULL,'2','2000',0),
	(127,'2015-03-10 16:20:22','2015-03-10 16:20:22',NULL,'3','schoolband',0),
	(128,'2015-03-10 16:20:22','2015-03-10 16:20:22',NULL,'4','directeur',0),
	(129,'2015-03-10 16:20:22','2015-03-10 16:20:22',NULL,'5','mavo',0),
	(130,'2015-03-10 16:25:33','2015-03-10 16:25:33',NULL,'1','dinsdag ',0),
	(131,'2015-03-10 16:25:33','2015-03-10 16:25:33',NULL,'2','Opstelten ',0),
	(132,'2015-03-10 16:25:33','2015-03-10 16:25:33',NULL,'3','Fred ',0),
	(133,'2015-03-10 16:25:33','2015-03-10 16:25:33',NULL,'4','Teeven ',0),
	(134,'2015-03-12 22:12:02','2015-03-12 22:12:02',NULL,'1','sdfg',0),
	(135,'2015-03-17 11:42:38','2015-03-17 11:42:38',NULL,'1','gek',0),
	(136,'2015-03-17 11:49:01','2015-03-17 11:49:01',NULL,'1','gek',0),
	(137,'2015-03-17 11:55:03','2015-03-17 11:55:03',NULL,'1','een',0),
	(138,'2015-03-17 11:55:03','2015-03-17 11:55:03',NULL,'2','vraag',0),
	(139,'2015-03-17 11:55:03','2015-03-17 11:55:03',NULL,'3','soort',0),
	(140,'2015-03-17 11:55:03','2015-03-17 11:55:03',NULL,'4','gaten',0),
	(141,'2015-03-17 11:55:03','2015-03-17 11:55:03',NULL,'5','',0),
	(142,'2015-03-17 12:21:52','2015-03-17 12:21:52',NULL,'1','gek',0),
	(143,'2015-03-17 13:22:21','2015-03-17 13:22:21',NULL,'1','woorden',0),
	(144,'2015-03-17 13:22:21','2015-03-17 13:22:21',NULL,'2','ingevuld',0),
	(145,'2015-03-17 15:58:54','2015-03-17 15:58:54',NULL,'1','half',0),
	(146,'2015-03-17 16:03:07','2015-03-17 16:03:07',NULL,'1','formidabel',0),
	(147,'2015-03-17 16:03:28','2015-03-17 16:09:11',NULL,'1','Ik ',0),
	(148,'2015-03-17 16:03:28','2015-03-17 16:09:11',NULL,'2','leuk ',0),
	(149,'2015-03-17 16:09:11','2015-03-17 16:09:11',NULL,'3','bedacht',0),
	(150,'2015-03-17 16:35:49','2015-03-17 16:35:49',NULL,'1','snaarinstrument',0),
	(151,'2015-03-17 16:36:47','2015-03-17 16:36:47',NULL,'1','73,3',0),
	(152,'2015-03-17 16:37:12','2015-03-17 16:37:12',NULL,'1','16,7',0),
	(153,'2015-03-17 16:38:00','2015-03-17 16:38:00',NULL,'1','10,4',0),
	(154,'2015-03-18 19:46:41','2015-03-18 19:46:41',NULL,'1','',0),
	(155,'2015-03-18 19:46:41','2015-03-18 19:46:41',NULL,'2','',0),
	(156,'2015-03-18 19:46:41','2015-03-18 19:46:41',NULL,'3','mobile',0),
	(157,'2015-03-19 13:24:29','2015-03-19 13:24:29',NULL,'1','gaten ',0),
	(158,'2015-03-19 13:24:29','2015-03-19 13:24:29',NULL,'2','Ja ',0),
	(159,'2015-03-19 13:24:29','2015-03-19 13:24:29',NULL,'3','zeker',0),
	(160,'2015-03-19 13:24:59','2015-03-19 13:24:59',NULL,'1','correct',0),
	(161,'2015-03-20 12:30:53','2015-03-20 12:30:53',NULL,'1','Hans',0),
	(162,'2015-03-25 11:35:40','2015-03-25 11:35:40',NULL,'1','gek',0),
	(163,'2015-03-25 21:27:03','2015-03-25 21:27:03',NULL,'1','gek',0),
	(164,'2015-03-26 16:07:14','2015-03-26 16:07:14',NULL,'1','Ik ',0),
	(165,'2015-03-26 16:07:14','2015-03-26 16:07:14',NULL,'2','leuk ',0),
	(166,'2015-03-26 16:07:14','2015-03-26 16:07:14',NULL,'3','bedacht',0),
	(167,'2015-03-26 16:07:14','2015-03-26 16:07:14',NULL,'1','73,3',0),
	(168,'2015-03-26 16:07:14','2015-03-26 16:07:14',NULL,'1','16,7',0),
	(169,'2015-03-26 16:07:14','2015-03-26 16:07:14',NULL,'1','10,4',0),
	(170,'2015-04-01 07:39:56','2015-04-01 07:39:56',NULL,'1','mensen ',0),
	(171,'2015-04-01 07:39:56','2015-04-01 07:39:56',NULL,'2','onderzoek ',0),
	(172,'2015-04-01 07:39:56','2015-04-01 07:39:56',NULL,'3','causale ',0),
	(173,'2015-04-07 12:11:45','2015-04-07 12:11:45',NULL,'1','dinsdag ',0),
	(174,'2015-04-07 12:11:45','2015-04-07 12:11:45',NULL,'2','Opstelten ',0),
	(175,'2015-04-07 12:11:45','2015-04-07 12:11:45',NULL,'3','Fred ',0),
	(176,'2015-04-07 12:11:45','2015-04-07 12:11:45',NULL,'4','Teeven ',0),
	(177,'2015-04-07 12:11:45','2015-04-07 12:11:45',NULL,'1','broer',0),
	(178,'2015-04-07 12:11:45','2015-04-07 12:11:45',NULL,'2','middelste broer',0),
	(179,'2015-04-07 12:11:45','2015-04-07 12:11:45',NULL,'3','verreweg de jongste broer',0),
	(180,'2015-04-07 12:11:45','2015-04-07 12:11:45',NULL,'1','goed/slecht',0),
	(181,'2015-04-07 12:11:45','2015-04-07 12:11:45',NULL,'2','niet/wel',0),
	(182,'2015-05-09 09:24:38','2015-05-09 09:24:38',NULL,'1','eerste',1),
	(183,'2015-05-09 09:24:38','2015-05-09 09:24:38',NULL,'2','vierde',1),
	(184,'2015-05-09 09:24:38','2015-05-09 09:24:38',NULL,'3','zevende',1),
	(185,'2015-05-09 09:24:38','2015-05-09 09:24:38',NULL,'4','twaalfde',1),
	(186,'2015-05-09 09:24:38','2015-05-09 09:24:38',NULL,'5','dertiende',1),
	(187,'2015-05-09 09:24:38','2015-05-09 09:24:38',NULL,'6','eenentwintigste',1),
	(188,'2015-05-09 09:24:38','2015-05-09 09:24:38',NULL,'7','vierentwintigste',1),
	(189,'2015-05-09 09:28:50','2015-05-09 09:28:50',NULL,'1','vierde',1),
	(190,'2015-05-09 09:28:50','2015-05-09 09:28:50',NULL,'2','vijfde',1),
	(191,'2015-05-09 10:30:56','2015-05-09 10:30:56',NULL,'1','Amsterdam',1),
	(192,'2015-05-09 10:30:56','2015-05-09 10:30:56',NULL,'1','Rotterdam',0),
	(193,'2015-05-09 10:30:56','2015-05-09 10:30:56',NULL,'1','Den Haag',0),
	(194,'2015-05-09 10:30:56','2015-05-09 10:30:56',NULL,'1','Zutphen',0),
	(195,'2015-05-09 10:30:56','2015-05-09 10:30:56',NULL,'2','Brussel',1),
	(196,'2015-05-09 10:30:56','2015-05-09 10:30:56',NULL,'2','Luik',0),
	(197,'2015-05-09 10:30:56','2015-05-09 10:30:56',NULL,'2','Duinkerken',0),
	(198,'2015-05-09 10:30:56','2015-05-09 10:30:56',NULL,'2','Antwerpen',0),
	(199,'2015-05-09 10:30:57','2015-05-09 10:30:57',NULL,'2','Ieper',0),
	(200,'2015-05-09 10:30:57','2015-05-09 10:30:57',NULL,'3','Berlijn',1),
	(201,'2015-05-09 10:30:57','2015-05-09 10:30:57',NULL,'3','Keulen',0),
	(202,'2015-05-09 10:30:57','2015-05-09 10:30:57',NULL,'3','Bonn',0),
	(203,'2015-05-09 10:30:57','2015-05-09 10:30:57',NULL,'3','Kall',0),
	(204,'2015-05-09 10:30:57','2015-05-09 10:30:57',NULL,'3','Euskirchen',0),
	(205,'2015-05-09 10:30:57','2015-05-09 10:30:57',NULL,'3','Neurenberg',0),
	(206,'2015-05-09 10:30:57','2015-05-09 10:30:57',NULL,'3','Auschwitz',0),
	(207,'2015-05-09 10:30:57','2015-05-09 10:30:57',NULL,'3','Hamburg',0),
	(208,'2015-05-09 10:30:57','2015-05-09 10:30:57',NULL,'3','Sötenich',0),
	(209,'2015-05-09 10:30:57','2015-05-09 10:30:57',NULL,'3','Freiburg',0),
	(210,'2015-05-09 10:30:57','2015-05-09 10:30:57',NULL,'4','Ankara',1),
	(211,'2015-05-09 10:30:57','2015-05-09 10:30:57',NULL,'4','Istanbul',0),
	(212,'2015-05-09 10:30:57','2015-05-09 10:30:57',NULL,'5','Teheran',1),
	(213,'2015-05-09 10:30:57','2015-05-09 10:30:57',NULL,'5','Isfahan',0),
	(214,'2015-05-12 19:17:11','2015-05-12 19:17:11',NULL,'1','tweede',1),
	(215,'2015-05-12 19:22:13','2015-05-12 19:22:13',NULL,'1','tweede',1),
	(216,'2015-05-12 19:22:13','2015-05-12 19:22:13',NULL,'2','achtste',1),
	(217,'2015-05-12 19:22:13','2015-05-12 19:22:13',NULL,'3','tiende',1),
	(218,'2015-05-12 19:22:13','2015-05-12 19:22:13',NULL,'4','dertiende',1),
	(219,'2015-05-12 19:22:13','2015-05-12 19:22:13',NULL,'5','vijftiende',1),
	(220,'2015-05-12 19:22:13','2015-05-12 19:22:13',NULL,'6','zeventiende',1),
	(221,'2015-05-12 19:22:13','2015-05-12 19:22:13',NULL,'7','negentiende',1),
	(222,'2015-05-12 19:22:13','2015-05-12 19:22:13',NULL,'8','éénentwintigste',1),
	(223,'2015-05-12 19:22:13','2015-05-12 19:22:13',NULL,'9','tweeentwintigste',1),
	(224,'2015-05-12 19:22:13','2015-05-12 19:22:13',NULL,'10','drieentwintigste',1),
	(225,'2015-05-12 19:22:13','2015-05-12 19:22:13',NULL,'11','31',1),
	(226,'2015-05-12 19:22:13','2015-05-12 19:22:13',NULL,'12','33',1),
	(227,'2015-05-12 19:22:13','2015-05-12 19:22:13',NULL,'13','35',1),
	(228,'2015-05-12 19:22:13','2015-05-12 19:22:13',NULL,'14','37',1),
	(229,'2015-05-12 19:22:13','2015-05-12 19:22:13',NULL,'15','39',1),
	(230,'2015-05-12 19:22:13','2015-05-12 19:22:13',NULL,'16','41',1),
	(231,'2015-05-12 19:22:13','2015-05-12 19:22:13',NULL,'17','43',1),
	(232,'2015-05-12 19:22:13','2015-05-12 19:22:13',NULL,'18','45',1),
	(233,'2015-05-12 19:22:13','2015-05-12 19:22:13',NULL,'19','47',1),
	(234,'2015-05-12 19:22:13','2015-05-12 19:22:13',NULL,'20','49',1),
	(235,'2015-05-12 19:23:50','2015-05-12 19:23:50',NULL,'1','Rutte',1),
	(236,'2015-05-12 20:03:02','2015-05-12 20:04:13','2015-05-12 20:04:13','1','anus',1),
	(237,'2015-05-12 20:03:02','2015-05-12 20:04:13','2015-05-12 20:04:13','1','mond',0),
	(238,'2015-05-12 20:03:03','2015-05-12 20:04:13','2015-05-12 20:04:13','2','Dikke',1),
	(239,'2015-05-12 20:03:03','2015-05-12 20:04:13','2015-05-12 20:04:13','2','Dunne',0),
	(240,'2015-05-12 20:04:13','2015-05-12 20:04:13',NULL,'1','Dikke',1),
	(241,'2015-05-12 20:04:13','2015-05-12 20:04:13',NULL,'1','Dunne',0),
	(242,'2015-05-12 20:04:13','2015-05-12 20:04:13',NULL,'2','anus',1),
	(243,'2015-05-12 20:04:13','2015-05-12 20:04:13',NULL,'2','mond',0),
	(244,'2015-05-12 20:04:13','2015-05-12 20:04:13',NULL,'3','besmetten',1),
	(245,'2015-05-12 20:04:13','2015-05-12 20:04:13',NULL,'3','belasten',0),
	(246,'2015-05-12 20:06:50','2015-05-12 20:06:50',NULL,'1','vies',1),
	(247,'2015-05-12 20:06:50','2015-05-12 20:06:50',NULL,'1','schoon',0),
	(248,'2015-05-12 20:06:50','2015-05-12 20:06:50',NULL,'2','vind',1),
	(249,'2015-05-12 20:06:50','2015-05-12 20:06:50',NULL,'2','zingt',0),
	(250,'2015-05-12 20:07:48','2015-05-12 20:07:48',NULL,'1','drie',1),
	(251,'2015-05-12 20:07:48','2015-05-12 20:07:48',NULL,'1','vijf',0),
	(252,'2015-05-18 13:38:14','2015-05-18 13:38:14',NULL,'1','Amsterdam',1),
	(253,'2015-05-18 13:38:14','2015-05-18 13:38:14',NULL,'1','Rotterdam',0),
	(254,'2015-05-18 13:38:14','2015-05-18 13:38:14',NULL,'1','Delft',0),
	(255,'2015-05-18 13:38:14','2015-05-18 13:38:14',NULL,'1','Den Haag',0),
	(256,'2015-05-18 13:38:14','2015-05-18 13:38:14',NULL,'2','Berlijn',1),
	(257,'2015-05-18 13:38:14','2015-05-18 13:38:14',NULL,'2','Warschau',0),
	(258,'2015-05-18 13:38:14','2015-05-18 13:38:14',NULL,'2','Praag',0),
	(259,'2015-05-19 14:54:59','2015-05-19 14:54:59',NULL,'1','gatentekst',1),
	(260,'2015-05-19 14:57:07','2015-05-19 14:57:07',NULL,'1','selectievraag',1),
	(261,'2015-05-19 14:57:07','2015-05-19 14:57:07',NULL,'1','tekenvraag',0),
	(262,'2015-05-23 19:37:59','2015-05-23 19:37:59',NULL,'1','testen',1),
	(263,'2015-05-25 20:45:00','2015-05-25 20:45:00',NULL,'1','activa',1),
	(264,'2015-05-25 20:45:00','2015-05-25 20:45:00',NULL,'1','passiva',0),
	(265,'2015-05-25 20:45:00','2015-05-25 20:45:00',NULL,'2','passiva',1),
	(266,'2015-05-25 20:45:00','2015-05-25 20:45:00',NULL,'2','activa',0),
	(267,'2015-05-25 20:45:00','2015-05-25 20:45:00',NULL,'3','Debet',1),
	(268,'2015-05-25 20:45:00','2015-05-25 20:45:00',NULL,'3','Krediet',0),
	(269,'2015-05-25 20:45:00','2015-05-25 20:45:00',NULL,'4','Rood',1),
	(270,'2015-05-25 20:45:00','2015-05-25 20:45:00',NULL,'4','Zwart',0),
	(271,'2015-05-25 20:45:00','2015-05-25 20:45:00',NULL,'5','ingaande',1),
	(272,'2015-05-25 20:45:00','2015-05-25 20:45:00',NULL,'5','uitgaande',0),
	(273,'2015-05-25 20:45:00','2015-05-25 20:45:00',NULL,'6','Krediet',1),
	(274,'2015-05-25 20:45:00','2015-05-25 20:45:00',NULL,'6','Debet',0),
	(275,'2015-05-25 20:45:00','2015-05-25 20:45:00',NULL,'7','Zwart',1),
	(276,'2015-05-25 20:45:01','2015-05-25 20:45:01',NULL,'7','Rood',0),
	(277,'2015-05-25 20:45:01','2015-05-25 20:45:01',NULL,'8','uitgaande',1),
	(278,'2015-05-25 20:45:01','2015-05-25 20:45:01',NULL,'8','ingaande',0),
	(279,'2015-05-25 20:45:01','2015-05-25 20:45:01',NULL,'1','activa',1),
	(280,'2015-05-25 20:45:01','2015-05-25 20:45:01',NULL,'1','passiva',0),
	(281,'2015-05-25 20:45:01','2015-05-25 20:45:01',NULL,'2','passiva',1),
	(282,'2015-05-25 20:45:01','2015-05-25 20:45:01',NULL,'2','activa',0),
	(283,'2015-05-25 20:45:01','2015-05-25 20:45:01',NULL,'3','Debet',1),
	(284,'2015-05-25 20:45:01','2015-05-25 20:45:01',NULL,'3','Krediet',0),
	(285,'2015-05-25 20:45:01','2015-05-25 20:45:01',NULL,'4','Rood',1),
	(286,'2015-05-25 20:45:01','2015-05-25 20:45:01',NULL,'4','Zwart',0),
	(287,'2015-05-25 20:45:01','2015-05-25 20:45:01',NULL,'5','ingaande',1),
	(288,'2015-05-25 20:45:01','2015-05-25 20:45:01',NULL,'5','uitgaande',0),
	(289,'2015-05-25 20:45:01','2015-05-25 20:45:01',NULL,'6','Krediet',1),
	(290,'2015-05-25 20:45:01','2015-05-25 20:45:01',NULL,'6','Debet',0),
	(291,'2015-05-25 20:45:01','2015-05-25 20:45:01',NULL,'7','Zwart',1),
	(292,'2015-05-25 20:45:01','2015-05-25 20:45:01',NULL,'7','Rood',0),
	(293,'2015-05-25 20:45:01','2015-05-25 20:45:01',NULL,'8','uitgaande',1),
	(294,'2015-05-25 20:45:01','2015-05-25 20:45:01',NULL,'8','ingaande',0),
	(295,'2015-05-25 20:55:46','2015-05-25 20:55:46',NULL,'1','gatentekst',1),
	(296,'2015-05-25 20:55:46','2015-05-25 20:55:46',NULL,'2','behoren',1),
	(297,'2015-06-01 20:51:33','2015-06-01 20:51:33',NULL,'1','heeft',1),
	(298,'2015-06-01 21:07:04','2015-06-01 21:07:04',NULL,'1','wel',1),
	(299,'2015-06-01 21:07:04','2015-06-01 21:07:04',NULL,'1','niet',0),
	(300,'2015-06-01 21:07:04','2015-06-01 21:07:04',NULL,'2','niet',1),
	(301,'2015-06-01 21:07:04','2015-06-01 21:07:04',NULL,'2','wel',0),
	(302,'2015-06-01 21:09:57','2015-06-01 21:09:57',NULL,'1','niet ',1),
	(303,'2015-06-01 21:09:57','2015-06-01 21:09:57',NULL,'1','wel',0),
	(304,'2015-06-03 15:20:18','2015-06-03 15:20:18',NULL,'1','Transport',1),
	(305,'2015-06-03 15:20:18','2015-06-03 15:20:18',NULL,'2','parlementaire',1),
	(306,'2015-06-03 15:20:18','2015-06-03 15:20:18',NULL,'3','laat',1),
	(307,'2015-06-03 15:20:18','2015-06-03 15:20:18',NULL,'4','spoor',1),
	(308,'2015-06-03 15:20:18','2015-06-03 15:20:18',NULL,'5','inspecteren',1),
	(309,'2015-06-03 15:28:31','2015-06-03 15:28:31',NULL,'1','undefined',1),
	(310,'2015-06-03 15:28:31','2015-06-03 15:28:31',NULL,'2','1,2',1),
	(311,'2015-06-03 15:28:31','2015-06-03 15:28:31',NULL,'3','null',1),
	(312,'2015-06-03 15:31:42','2015-06-03 15:34:17','2015-06-03 15:34:17','1','hhhh',1),
	(313,'2015-06-03 15:32:04','2015-06-03 15:32:04',NULL,'1','1',1),
	(314,'2015-06-03 15:32:05','2015-06-03 15:32:05',NULL,'1','2',0),
	(315,'2015-06-03 15:32:05','2015-06-03 15:32:05',NULL,'1','3',0),
	(316,'2015-06-03 15:32:05','2015-06-03 15:32:05',NULL,'1','4',0),
	(317,'2015-06-03 15:32:05','2015-06-03 15:32:05',NULL,'1','5',0),
	(318,'2015-06-03 15:32:05','2015-06-03 15:32:05',NULL,'1','6',0),
	(319,'2015-06-03 15:32:05','2015-06-03 15:32:05',NULL,'1','7',0),
	(320,'2015-06-03 15:32:05','2015-06-03 15:32:05',NULL,'1','8',0),
	(321,'2015-06-03 15:32:05','2015-06-03 15:32:05',NULL,'1','9',0),
	(322,'2015-06-03 15:32:05','2015-06-03 15:32:05',NULL,'1','10',0),
	(323,'2015-06-03 15:34:17','2015-06-03 15:46:07','2015-06-03 15:46:07','1','1',1),
	(324,'2015-06-03 15:34:17','2015-06-03 15:46:07','2015-06-03 15:46:07','1','2',0),
	(325,'2015-06-03 15:34:17','2015-06-03 15:46:07','2015-06-03 15:46:07','2','hhhh',1),
	(326,'2015-06-03 15:34:30','2015-06-03 15:34:30',NULL,'1','1',1),
	(327,'2015-06-03 15:34:30','2015-06-03 15:34:30',NULL,'1','2',0),
	(328,'2015-06-03 15:34:30','2015-06-03 15:34:30',NULL,'2','2',1),
	(329,'2015-06-03 15:34:31','2015-06-03 15:34:31',NULL,'2','3',0),
	(330,'2015-06-03 15:34:31','2015-06-03 15:34:31',NULL,'3','4',1),
	(331,'2015-06-03 15:34:31','2015-06-03 15:34:31',NULL,'3','5',0),
	(332,'2015-06-03 15:46:07','2015-06-03 15:52:04','2015-06-03 15:52:04','1','1',1),
	(333,'2015-06-03 15:46:07','2015-06-03 15:52:04','2015-06-03 15:52:04','1','2',0),
	(334,'2015-06-03 15:46:07','2015-06-03 15:52:04','2015-06-03 15:52:04','2','hhhh',1),
	(335,'2015-06-03 15:49:18','2015-06-03 15:49:18',NULL,'1','stenengooier',1),
	(336,'2015-06-03 15:49:18','2015-06-03 15:49:18',NULL,'1','marokkaan',0),
	(337,'2015-06-03 15:49:18','2015-06-03 15:49:18',NULL,'2','jongeren',1),
	(338,'2015-06-03 15:49:18','2015-06-03 15:49:18',NULL,'2','marokkanen',0),
	(339,'2015-06-03 15:49:18','2015-06-03 15:49:18',NULL,'3','harder',1),
	(340,'2015-06-03 15:49:18','2015-06-03 15:49:18',NULL,'3','zachter',0),
	(341,'2015-06-03 15:49:18','2015-06-03 15:49:18',NULL,'4','jongens',1),
	(342,'2015-06-03 15:49:18','2015-06-03 15:49:18',NULL,'4','marokkanen',0),
	(343,'2015-06-03 15:49:18','2015-06-03 15:49:18',NULL,'5','ingooiden',1),
	(344,'2015-06-03 15:49:18','2015-06-03 15:49:18',NULL,'5','zeemden',0),
	(345,'2015-06-03 15:49:18','2015-06-03 15:49:18',NULL,'6','niet',1),
	(346,'2015-06-03 15:49:18','2015-06-03 15:49:18',NULL,'6','best wel',0),
	(347,'2015-06-03 15:49:18','2015-06-03 15:49:18',NULL,'7','doelwit',1),
	(348,'2015-06-03 15:49:18','2015-06-03 15:49:18',NULL,'7','versiert',0),
	(349,'2015-06-03 15:49:18','2015-06-03 15:49:18',NULL,'8','vernielingen',1),
	(350,'2015-06-03 15:49:18','2015-06-03 15:49:18',NULL,'8','versieringen',0),
	(351,'2015-06-03 15:49:18','2015-06-03 15:49:18',NULL,'9','ingooien',1),
	(352,'2015-06-03 15:49:19','2015-06-03 15:49:19',NULL,'9','zemen',0),
	(353,'2015-06-03 15:52:05','2015-06-03 16:26:17','2015-06-03 16:26:17','1','1',1),
	(354,'2015-06-03 15:52:05','2015-06-03 16:26:17','2015-06-03 16:26:17','1','2',0),
	(355,'2015-06-03 15:52:05','2015-06-03 16:26:17','2015-06-03 16:26:17','2','hhhh',1),
	(356,'2015-06-03 16:06:13','2015-06-03 16:07:28','2015-06-03 16:07:28','1','1',1),
	(357,'2015-06-03 16:06:13','2015-06-03 16:07:28','2015-06-03 16:07:28','1','2',0),
	(358,'2015-06-03 16:06:14','2015-06-03 16:07:28','2015-06-03 16:07:28','1','3',0),
	(359,'2015-06-03 16:07:28','2015-06-03 16:07:28',NULL,'1','1',1),
	(360,'2015-06-03 16:07:28','2015-06-03 16:07:28',NULL,'1','2',0),
	(361,'2015-06-03 16:07:28','2015-06-03 16:07:28',NULL,'1','3',0),
	(362,'2015-06-03 16:07:28','2015-06-03 16:07:28',NULL,'2','34',1),
	(363,'2015-06-03 16:07:28','2015-06-03 16:07:28',NULL,'2','2',0),
	(364,'2015-06-03 16:07:28','2015-06-03 16:07:28',NULL,'2','3',0),
	(365,'2015-06-03 16:07:28','2015-06-03 16:07:28',NULL,'2','2',0),
	(366,'2015-06-03 16:07:28','2015-06-03 16:07:28',NULL,'2','34',0),
	(367,'2015-06-03 16:07:28','2015-06-03 16:07:28',NULL,'2','34',0),
	(368,'2015-06-03 16:07:28','2015-06-03 16:07:28',NULL,'2','46',0),
	(369,'2015-06-03 16:07:28','2015-06-03 16:07:28',NULL,'2','546',0),
	(370,'2015-06-03 16:07:28','2015-06-03 16:07:28',NULL,'2','345',0),
	(371,'2015-06-03 16:07:28','2015-06-03 16:07:28',NULL,'2','345',0),
	(372,'2015-06-03 16:26:17','2015-06-03 16:26:17',NULL,'1','test',1),
	(373,'2015-06-03 16:26:18','2015-06-03 16:26:18',NULL,'1','test1',0),
	(374,'2015-06-03 16:26:18','2015-06-03 16:26:18',NULL,'2','hhhh',1),
	(375,'2015-06-11 11:19:30','2015-06-11 11:19:30',NULL,'1','China',1),
	(376,'2015-06-11 11:19:30','2015-06-11 11:19:30',NULL,'2','2',1),
	(377,'2015-06-11 11:29:24','2015-06-11 11:29:52','2015-06-11 11:29:52','1','long',1),
	(378,'2015-06-11 11:29:24','2015-06-11 11:29:52','2015-06-11 11:29:52','1','short',0),
	(379,'2015-06-11 11:29:24','2015-06-11 11:29:52','2015-06-11 11:29:52','2','end',1),
	(380,'2015-06-11 11:29:24','2015-06-11 11:29:52','2015-06-11 11:29:52','2','beginning',0),
	(381,'2015-06-11 11:29:24','2015-06-11 11:29:52','2015-06-11 11:29:52','2','middle',0),
	(382,'2015-06-11 11:29:24','2015-06-11 11:29:52','2015-06-11 11:29:52','2','start',0),
	(383,'2015-06-11 11:29:52','2015-06-11 11:29:52',NULL,'1','long',1),
	(384,'2015-06-11 11:29:52','2015-06-11 11:29:52',NULL,'1','short',0),
	(385,'2015-06-11 11:29:52','2015-06-11 11:29:52',NULL,'2','end',1),
	(386,'2015-06-11 11:29:52','2015-06-11 11:29:52',NULL,'2','beginning',0),
	(387,'2015-06-11 11:29:52','2015-06-11 11:29:52',NULL,'2','middle',0),
	(388,'2015-06-11 11:29:52','2015-06-11 11:29:52',NULL,'2','start',0),
	(389,'2015-07-23 11:08:15','2015-07-23 11:19:52','2015-07-23 11:19:52','1','mug',1),
	(390,'2015-07-23 11:19:52','2015-07-23 11:21:39','2015-07-23 11:21:39','1','mug',1),
	(391,'2015-07-23 11:19:52','2015-07-23 11:21:39','2015-07-23 11:21:39','2','insect',1),
	(392,'2015-07-23 11:19:52','2015-07-23 11:21:39','2015-07-23 11:21:39','3','mug',1),
	(393,'2015-07-23 11:19:52','2015-07-23 11:21:39','2015-07-23 11:21:39','4','mug',1),
	(394,'2015-07-23 11:19:52','2015-07-23 11:21:39','2015-07-23 11:21:39','5','mug',1),
	(395,'2015-07-23 11:21:39','2015-07-23 11:21:39',NULL,'1','mug',1),
	(396,'2015-07-23 11:21:39','2015-07-23 11:21:39',NULL,'2','mug',1),
	(397,'2015-07-23 11:21:39','2015-07-23 11:21:39',NULL,'3','insect',1),
	(398,'2015-07-23 11:21:39','2015-07-23 11:21:39',NULL,'4','mug',1),
	(399,'2015-07-23 11:21:39','2015-07-23 11:21:39',NULL,'5','mug',1),
	(400,'2015-07-23 11:21:39','2015-07-23 11:21:39',NULL,'6','mug',1),
	(401,'2015-07-23 11:21:39','2015-07-23 11:21:39',NULL,'7','mug',1),
	(402,'2015-07-23 11:21:39','2015-07-23 11:21:39',NULL,'8','mug',1),
	(403,'2015-07-23 11:21:40','2015-07-23 11:21:40',NULL,'9','mug',1),
	(404,'2015-07-23 11:21:40','2015-07-23 11:21:40',NULL,'10','gezoem',1),
	(405,'2015-07-23 12:02:42','2015-07-23 12:02:42',NULL,'1','Nijmegen',1),
	(406,'2015-07-23 12:02:42','2015-07-23 12:02:42',NULL,'1','Gouda',0),
	(407,'2015-07-23 12:02:42','2015-07-23 12:02:42',NULL,'1','New York',0),
	(408,'2015-07-23 12:02:42','2015-07-23 12:02:42',NULL,'2','Wandelbond',1),
	(409,'2015-07-23 12:02:42','2015-07-23 12:02:42',NULL,'2','Fietsbond',0),
	(410,'2015-07-23 12:02:42','2015-07-23 12:02:42',NULL,'2','Harloopvereniging',0),
	(411,'2015-07-23 12:02:42','2015-07-23 12:02:42',NULL,'3','heuvelachtig',1),
	(412,'2015-07-23 12:02:42','2015-07-23 12:02:42',NULL,'3','bergactig',0),
	(413,'2015-07-23 12:02:42','2015-07-23 12:02:42',NULL,'3','vlak',0),
	(414,'2015-07-23 12:02:42','2015-07-23 12:02:42',NULL,'3','druk',0),
	(415,'2015-07-23 12:02:42','2015-07-23 12:02:42',NULL,'4','Waal',1),
	(416,'2015-07-23 12:02:42','2015-07-23 12:02:42',NULL,'4','Maas',0),
	(417,'2015-07-23 12:02:42','2015-07-23 12:02:42',NULL,'4','Rijn',0),
	(418,'2015-07-23 12:02:42','2015-07-23 12:02:42',NULL,'4','Schie',0),
	(419,'2015-07-23 14:43:23','2015-07-23 14:43:23',NULL,'1','Ja',1),
	(420,'2015-07-23 14:43:23','2015-07-23 14:43:23',NULL,'2','Piet',1),
	(421,'2015-07-23 14:53:52','2015-07-23 14:53:52',NULL,'1','Ja',1),
	(422,'2015-07-23 14:53:52','2015-07-23 14:53:52',NULL,'1','Nee',0),
	(423,'2015-07-23 14:53:52','2015-07-23 14:53:52',NULL,'1','Koe',0),
	(424,'2015-07-29 08:07:56','2015-07-29 08:07:56',NULL,'1','ede',1),
	(425,'2015-07-30 11:44:55','2015-07-30 11:44:55',NULL,'1','Consumentenbond',1),
	(426,'2015-07-30 11:44:55','2015-07-30 11:44:55',NULL,'2','waarde',1),
	(427,'2015-07-30 11:44:56','2015-07-30 11:44:56',NULL,'3','installatie',1),
	(428,'2015-07-30 11:44:56','2015-07-30 11:44:56',NULL,'4','10',1),
	(429,'2015-07-31 13:56:24','2015-07-31 13:56:24',NULL,'1','privejets',1),
	(430,'2015-07-31 13:56:24','2015-07-31 13:56:24',NULL,'2','app',1),
	(431,'2015-08-06 08:29:26','2015-08-06 08:29:26',NULL,'1','Piet',1),
	(432,'2015-08-06 08:39:04','2015-08-06 08:39:04',NULL,'1','Ja',1),
	(433,'2015-08-06 08:39:04','2015-08-06 08:39:04',NULL,'1','Nee',0),
	(434,'2015-08-06 08:39:04','2015-08-06 08:39:04',NULL,'1','Weet niet',0),
	(435,'2015-08-06 08:52:58','2015-08-06 08:52:58',NULL,'1','Ja',1),
	(436,'2015-08-06 10:17:20','2015-08-06 10:17:20',NULL,'1','wrewr',1),
	(437,'2015-08-06 10:26:12','2015-08-06 10:32:19','2015-08-06 10:32:19','1','sa',1),
	(438,'2015-08-06 10:26:12','2015-08-06 10:32:19','2015-08-06 10:32:19','1','sa',0),
	(439,'2015-08-06 10:32:19','2015-08-06 10:32:59','2015-08-06 10:32:59','1','sa',1),
	(440,'2015-08-06 10:32:19','2015-08-06 10:32:59','2015-08-06 10:32:59','1','sa',0),
	(441,'2015-08-06 10:32:59','2015-08-06 10:32:59',NULL,'1','sa',1),
	(442,'2015-08-06 10:32:59','2015-08-06 10:32:59',NULL,'1','sa',0),
	(443,'2015-08-06 14:11:15','2015-08-06 14:11:15',NULL,'1','hak',1),
	(444,'2015-08-17 12:22:07','2015-08-17 12:22:07',NULL,'1','willen',1),
	(445,'2015-08-17 12:22:07','2015-08-17 12:22:07',NULL,'2','online',1),
	(446,'2015-08-19 10:12:37','2015-08-19 10:12:37',NULL,'1','48',1),
	(447,'2015-08-19 10:12:37','2015-08-19 10:12:37',NULL,'2','een derde (31 procent)',1),
	(448,'2015-08-19 10:12:37','2015-08-19 10:12:37',NULL,'3','half miljard',1),
	(449,'2015-08-19 10:12:37','2015-08-19 10:12:37',NULL,'4','1,4 miljoen',1),
	(450,'2015-08-19 14:06:29','2015-08-19 14:06:29',NULL,'1','Turkse',1),
	(451,'2015-08-19 14:06:29','2015-08-19 14:06:29',NULL,'1','Nederlandse',0),
	(452,'2015-08-19 14:06:30','2015-08-19 14:06:30',NULL,'1','Syrische',0),
	(453,'2015-08-19 14:06:30','2015-08-19 14:06:30',NULL,'1','Amerikaanse',0),
	(454,'2015-08-19 14:06:30','2015-08-19 14:06:30',NULL,'2','Turkije',1),
	(455,'2015-08-19 14:06:30','2015-08-19 14:06:30',NULL,'2','Verenigde Staten',0),
	(456,'2015-08-19 14:06:30','2015-08-19 14:06:30',NULL,'2','Nederland',0),
	(457,'2015-08-19 14:06:30','2015-08-19 14:06:30',NULL,'2','Syrië',0),
	(458,'2015-08-19 14:06:30','2015-08-19 14:06:30',NULL,'3','Turkse',1),
	(459,'2015-08-19 14:06:30','2015-08-19 14:06:30',NULL,'3','Nederlandse',0),
	(460,'2015-08-19 14:06:30','2015-08-19 14:06:30',NULL,'3','Amerikaanse',0),
	(461,'2015-08-19 14:06:30','2015-08-19 14:06:30',NULL,'3','Syrische',0),
	(462,'2015-08-28 06:37:23','2015-08-28 06:37:23',NULL,'1','werken',1),
	(463,'2015-08-28 06:40:58','2015-08-28 06:40:58',NULL,'1','Gaten',1),
	(464,'2015-08-28 06:45:58','2015-08-28 06:45:58',NULL,'1','juiste',1),
	(465,'2015-08-28 06:45:58','2015-08-28 06:45:58',NULL,'1','foute',0),
	(466,'2015-08-28 06:45:58','2015-08-28 06:45:58',NULL,'1','verkeerde',0),
	(467,'2015-09-09 11:08:03','2015-09-09 11:08:03',NULL,'1','Probleem',1),
	(468,'2015-09-09 11:08:03','2015-09-09 11:08:03',NULL,'2','new line',1),
	(469,'2015-09-09 12:07:42','2015-09-09 12:07:42',NULL,'1','iPad',1),
	(470,'2015-09-09 12:07:42','2015-09-09 12:07:42',NULL,'1','Commodore 64',0),
	(471,'2015-09-09 12:07:42','2015-09-09 12:07:42',NULL,'1','Super Nintendo',0),
	(472,'2015-09-09 12:07:42','2015-09-09 12:07:42',NULL,'2','werkt',1),
	(473,'2015-09-09 12:07:42','2015-09-09 12:07:42',NULL,'2','fietst',0),
	(474,'2015-09-10 12:54:59','2015-09-10 12:54:59',NULL,'1','software',1),
	(475,'2015-09-10 12:54:59','2015-09-10 12:54:59',NULL,'2','browser',1),
	(476,'2015-09-10 12:54:59','2015-09-10 12:54:59',NULL,'3','tijdstempel',1),
	(477,'2015-09-10 14:13:31','2015-09-10 14:13:31',NULL,'1','start',1),
	(478,'2015-09-15 11:54:35','2015-09-15 11:54:35',NULL,'1','zwijgen',1),
	(479,'2015-09-15 11:54:35','2015-09-15 11:54:35',NULL,'2','zwijgende',1),
	(480,'2015-09-15 11:54:35','2015-09-15 11:54:35',NULL,'3','mishandeling',1),
	(481,'2015-09-15 11:54:35','2015-09-15 11:54:35',NULL,'4','ogen',1),
	(482,'2015-09-15 11:54:35','2015-09-15 11:54:35',NULL,'5','reden',1),
	(483,'2015-09-15 11:54:38','2015-09-15 11:54:38',NULL,'1','zwijgen',1),
	(484,'2015-09-15 11:54:38','2015-09-15 11:54:38',NULL,'2','zwijgende',1),
	(485,'2015-09-15 11:54:38','2015-09-15 11:54:38',NULL,'3','mishandeling',1),
	(486,'2015-09-15 11:54:38','2015-09-15 11:54:38',NULL,'4','ogen',1),
	(487,'2015-09-15 11:54:38','2015-09-15 11:54:38',NULL,'5','reden',1),
	(488,'2015-09-15 12:03:40','2015-09-15 12:03:40',NULL,'1','Palestijnse',1),
	(489,'2015-09-15 12:03:40','2015-09-15 12:03:40',NULL,'1','Jordaanse',0),
	(490,'2015-09-15 12:03:40','2015-09-15 12:03:40',NULL,'2','Israelische',1),
	(491,'2015-09-15 12:03:40','2015-09-15 12:03:40',NULL,'2','Turkse',0),
	(492,'2015-09-15 12:37:41','2015-09-15 12:37:41',NULL,'1','weinig',1),
	(493,'2015-09-15 12:37:41','2015-09-15 12:37:41',NULL,'1','veel',0),
	(494,'2015-09-15 12:37:41','2015-09-15 12:37:41',NULL,'2','Fransman',1),
	(495,'2015-09-15 12:37:41','2015-09-15 12:37:41',NULL,'2','Nederlander',0),
	(496,'2015-09-15 12:37:41','2015-09-15 12:37:41',NULL,'2','Duitser',0),
	(497,'2015-09-15 12:37:41','2015-09-15 12:37:41',NULL,'2','Brit',0),
	(498,'2015-09-15 12:37:41','2015-09-15 12:37:41',NULL,'3','mensen',1),
	(499,'2015-09-15 12:37:41','2015-09-15 12:37:41',NULL,'3','dieren',0),
	(500,'2015-09-15 14:51:52','2015-09-15 14:51:52',NULL,'1','test',1),
	(501,'2015-09-15 14:53:15','2015-09-15 14:53:50','2015-09-15 14:53:50','1','<p>klopt ',1),
	(502,'2015-09-15 14:53:15','2015-09-15 14:53:50','2015-09-15 14:53:50','1','klopt',0),
	(503,'2015-09-15 14:53:16','2015-09-15 14:53:16',NULL,'1','<p>klopt ',1),
	(504,'2015-09-15 14:53:16','2015-09-15 14:53:16',NULL,'1','klopt',0),
	(505,'2015-09-15 14:53:50','2015-09-15 14:53:50',NULL,'1','</p><p>klopt ',1),
	(506,'2015-09-15 14:53:50','2015-09-15 14:53:50',NULL,'1','test ',0),
	(507,'2015-09-15 14:53:50','2015-09-15 14:53:50',NULL,'1',' klopt',0),
	(508,'2015-09-17 14:09:35','2015-09-17 14:09:35',NULL,'1','Koolstofdioxide',1),
	(509,'2015-09-17 14:09:35','2015-09-17 14:09:35',NULL,'2','Zuurstof',1),
	(510,'2015-09-24 13:46:15','2015-09-24 13:46:15',NULL,'1','koningin',1),
	(511,'2015-09-24 13:46:15','2015-09-24 13:46:15',NULL,'2','koningin',1),
	(512,'2015-09-24 13:46:15','2015-09-24 13:46:15',NULL,'3','agenda',1),
	(513,'2015-09-30 14:36:37','2015-12-21 12:14:39','2015-12-21 12:14:39','1','JA',1),
	(514,'2015-09-30 14:36:37','2015-12-21 12:14:39','2015-12-21 12:14:39','1','NEE',0),
	(515,'2015-09-30 14:50:09','2015-12-21 12:15:01','2015-12-21 12:15:01','1','WEL',1),
	(516,'2015-09-30 14:50:09','2015-12-21 12:15:01','2015-12-21 12:15:01','1','GEEN',0),
	(517,'2015-09-30 14:50:09','2015-12-21 12:15:01','2015-12-21 12:15:01','2','WEL',1),
	(518,'2015-09-30 14:50:10','2015-12-21 12:15:01','2015-12-21 12:15:01','2','GEEN',0),
	(519,'2015-09-30 14:50:10','2015-12-21 12:15:01','2015-12-21 12:15:01','3','WEL',1),
	(520,'2015-09-30 14:50:10','2015-12-21 12:15:01','2015-12-21 12:15:01','3','GEEN',0),
	(521,'2015-09-30 14:50:10','2015-12-21 12:15:01','2015-12-21 12:15:01','4','WEL',1),
	(522,'2015-09-30 14:50:10','2015-12-21 12:15:01','2015-12-21 12:15:01','4','GEEN',0),
	(523,'2015-09-30 14:50:10','2015-12-21 12:15:01','2015-12-21 12:15:01','5','WEL',1),
	(524,'2015-09-30 14:50:10','2015-12-21 12:15:01','2015-12-21 12:15:01','5','GEEN',0),
	(525,'2015-09-30 15:16:15','2015-12-21 12:15:16','2015-12-21 12:15:16','1','een',1),
	(526,'2015-09-30 15:16:15','2015-12-21 12:15:16','2015-12-21 12:15:16','1','geen',0),
	(527,'2015-09-30 15:16:15','2015-12-21 12:15:16','2015-12-21 12:15:16','2','geen',1),
	(528,'2015-09-30 15:16:15','2015-12-21 12:15:16','2015-12-21 12:15:16','2','een',0),
	(529,'2015-09-30 15:16:15','2015-12-21 12:15:16','2015-12-21 12:15:16','3','geen',1),
	(530,'2015-09-30 15:16:15','2015-12-21 12:15:16','2015-12-21 12:15:16','3','een',0),
	(531,'2015-09-30 15:16:15','2015-12-21 12:15:16','2015-12-21 12:15:16','4','een',1),
	(532,'2015-09-30 15:16:15','2015-12-21 12:15:16','2015-12-21 12:15:16','4','geen',0),
	(533,'2015-09-30 15:16:15','2015-12-21 12:15:16','2015-12-21 12:15:16','5','geen',1),
	(534,'2015-09-30 15:16:15','2015-12-21 12:15:16','2015-12-21 12:15:16','5','een',0),
	(535,'2015-09-30 15:16:15','2015-12-21 12:15:16','2015-12-21 12:15:16','6','een',1),
	(536,'2015-09-30 15:16:16','2015-12-21 12:15:16','2015-12-21 12:15:16','6','geen',0),
	(537,'2015-09-30 15:16:16','2015-12-21 12:15:16','2015-12-21 12:15:16','7','een',1),
	(538,'2015-09-30 15:16:16','2015-12-21 12:15:16','2015-12-21 12:15:16','7','geen',0),
	(539,'2015-09-30 15:16:16','2015-12-21 12:15:16','2015-12-21 12:15:16','8','een',1),
	(540,'2015-09-30 15:16:16','2015-12-21 12:15:16','2015-12-21 12:15:16','8','geen',0),
	(541,'2015-09-30 15:51:14','2015-12-21 12:29:19','2015-12-21 12:29:19','1','NEE',1),
	(542,'2015-09-30 15:51:14','2015-12-21 12:29:19','2015-12-21 12:29:19','1','JA',0),
	(543,'2015-09-30 15:51:14','2015-12-21 12:29:19','2015-12-21 12:29:19','2','JA',1),
	(544,'2015-09-30 15:51:14','2015-12-21 12:29:19','2015-12-21 12:29:19','2','NEE',0),
	(545,'2015-09-30 16:32:21','2015-09-30 16:32:21',NULL,'1','probleemstelling',1),
	(546,'2015-09-30 16:32:21','2015-09-30 16:32:21',NULL,'1','onderzoeksvraag',0),
	(547,'2015-09-30 16:34:24','2015-12-21 12:32:04','2015-12-21 12:32:04','1','6',1),
	(548,'2015-09-30 16:34:24','2015-12-21 12:32:04','2015-12-21 12:32:04','1','1',0),
	(549,'2015-09-30 16:34:24','2015-12-21 12:32:04','2015-12-21 12:32:04','1','2',0),
	(550,'2015-09-30 16:34:24','2015-12-21 12:32:04','2015-12-21 12:32:04','1','3',0),
	(551,'2015-09-30 16:34:24','2015-12-21 12:32:04','2015-12-21 12:32:04','1','4',0),
	(552,'2015-09-30 16:34:24','2015-12-21 12:32:04','2015-12-21 12:32:04','1','5',0),
	(553,'2015-09-30 16:34:24','2015-12-21 12:32:04','2015-12-21 12:32:04','1','7',0),
	(554,'2015-09-30 16:34:24','2015-12-21 12:32:04','2015-12-21 12:32:04','1','8',0),
	(555,'2015-09-30 16:34:25','2015-12-21 12:32:04','2015-12-21 12:32:04','1','9',0),
	(556,'2015-09-30 16:34:25','2015-12-21 12:32:04','2015-12-21 12:32:04','1','10',0),
	(557,'2015-09-30 16:35:48','2015-12-21 12:32:18','2015-12-21 12:32:18','1','controlegroep',1),
	(558,'2015-09-30 16:35:48','2015-12-21 12:32:18','2015-12-21 12:32:18','1','proefgroep',0),
	(559,'2015-09-30 16:48:22','2015-12-21 12:15:30','2015-12-21 12:15:30','1','20',1),
	(560,'2015-09-30 16:48:22','2015-12-21 12:15:30','2015-12-21 12:15:30','1','40',0),
	(561,'2015-09-30 16:48:22','2015-12-21 12:15:30','2015-12-21 12:15:30','1','22',0),
	(562,'2015-09-30 16:48:22','2015-12-21 12:15:30','2015-12-21 12:15:30','1','44',0),
	(563,'2015-09-30 16:48:22','2015-12-21 12:15:30','2015-12-21 12:15:30','1','23',0),
	(564,'2015-09-30 16:48:22','2015-12-21 12:15:30','2015-12-21 12:15:30','1','46',0),
	(565,'2015-09-30 16:48:22','2015-12-21 12:15:30','2015-12-21 12:15:30','1','24',0),
	(566,'2015-09-30 16:48:22','2015-12-21 12:15:30','2015-12-21 12:15:30','1','48',0),
	(567,'2015-09-30 16:48:22','2015-12-21 12:15:30','2015-12-21 12:15:30','1','80',0),
	(568,'2015-09-30 16:48:22','2015-12-21 12:15:30','2015-12-21 12:15:30','1','10',0),
	(569,'2015-09-30 16:48:22','2015-12-21 12:15:30','2015-12-21 12:15:30','2','zijdeaapje',1),
	(570,'2015-09-30 16:48:22','2015-12-21 12:15:30','2015-12-21 12:15:30','2','varken',0),
	(571,'2015-09-30 16:48:22','2015-12-21 12:15:30','2015-12-21 12:15:30','2','konijn',0),
	(572,'2015-09-30 16:48:22','2015-12-21 12:15:30','2015-12-21 12:15:30','2','chimpansee',0),
	(573,'2015-10-01 14:55:37','2015-10-01 14:55:37',NULL,'1','kinderen',1),
	(574,'2015-10-01 14:55:37','2015-10-01 14:55:37',NULL,'1','volwassenen',0),
	(575,'2015-10-29 10:31:14','2015-10-29 10:31:14',NULL,'1','lerarenopleidingen',1),
	(576,'2015-10-29 10:31:14','2015-10-29 10:31:14',NULL,'2','lerarenopleidingen',1),
	(577,'2015-10-29 10:31:14','2015-10-29 10:31:14',NULL,'3','VVD',1),
	(578,'2015-10-29 10:31:14','2015-10-29 10:31:14',NULL,'4','VVD',1),
	(579,'2015-10-29 10:31:14','2015-10-29 10:31:14',NULL,'5','lerarenopleiding',1),
	(580,'2015-10-29 10:31:14','2015-10-29 10:31:14',NULL,'6','PvdA',1),
	(581,'2015-10-29 10:31:14','2015-10-29 10:31:14',NULL,'7','VVD',1),
	(582,'2015-10-29 10:31:14','2015-10-29 10:31:14',NULL,'8','PvdA',1),
	(583,'2015-11-16 11:41:42','2015-11-25 14:15:48','2015-11-25 14:15:48','1','JA',1),
	(584,'2015-11-16 11:41:42','2015-11-25 14:15:48','2015-11-25 14:15:48','1','NEE',0),
	(585,'2015-11-16 12:05:05','2015-11-16 12:09:36','2015-11-16 12:09:36','1','1',1),
	(586,'2015-11-16 12:05:05','2015-11-16 12:09:36','2015-11-16 12:09:36','2','2',1),
	(587,'2015-11-16 12:09:37','2015-11-16 12:09:37',NULL,'1','Wieren (algen)',1),
	(588,'2015-11-16 12:09:37','2015-11-16 12:09:37',NULL,'2','Naaktzadigen',1),
	(589,'2015-11-16 12:17:29','2015-11-16 12:17:29',NULL,'1','door longen',1),
	(590,'2015-11-16 12:17:29','2015-11-16 12:17:29',NULL,'2','door eieren met leerachtige schaal',1),
	(591,'2015-11-16 12:20:32','2015-11-16 12:20:32',NULL,'1','Haren',1),
	(592,'2015-11-16 12:20:32','2015-11-16 12:20:32',NULL,'2','Tepels OF uier OF zogende jongen',1),
	(593,'2015-11-16 15:53:52','2015-11-16 15:54:01','2015-11-16 15:54:01','1','Bij bedektzadigen: NEE - Bij naaktzadigen: JA',1),
	(594,'2015-11-16 15:53:52','2015-11-16 15:54:01','2015-11-16 15:54:01','1','Bij bedektzadigen: JA - Bij naaktzadigen: JA',0),
	(595,'2015-11-16 15:53:52','2015-11-16 15:54:01','2015-11-16 15:54:01','1','Bij bedektzadigen: JA - Bij naaktzadigen: NEE',0),
	(596,'2015-11-16 15:53:52','2015-11-16 15:54:01','2015-11-16 15:54:01','1','Bij bedektzadigen: NEE - Bij naaktzadigen: NEE',0),
	(597,'2015-11-16 15:54:01','2015-11-16 15:54:01',NULL,'1','Bij bedektzadigen: NEE - Bij naaktzadigen: JA',1),
	(598,'2015-11-16 15:54:01','2015-11-16 15:54:01',NULL,'1','Bij bedektzadigen: JA - Bij naaktzadigen: JA',0),
	(599,'2015-11-16 15:54:01','2015-11-16 15:54:01',NULL,'1','Bij bedektzadigen: JA - Bij naaktzadigen: NEE',0),
	(600,'2015-11-16 15:54:02','2015-11-16 15:54:02',NULL,'1','Bij bedektzadigen: NEE - Bij naaktzadigen: NEE',0),
	(601,'2015-11-16 15:56:42','2015-11-16 15:56:42',NULL,'1','Tot de zoogdieren',1),
	(602,'2015-11-16 15:56:42','2015-11-16 15:56:42',NULL,'1','Tot de amfibieën',0),
	(603,'2015-11-16 15:56:42','2015-11-16 15:56:42',NULL,'1','Tot de reptielen',0),
	(604,'2015-11-16 15:56:42','2015-11-16 15:56:42',NULL,'1','Tot de vissen',0),
	(605,'2015-11-16 15:59:15','2015-11-16 15:59:15',NULL,'1','Tot de duizendpootachtigen',1),
	(606,'2015-11-16 15:59:15','2015-11-16 15:59:15',NULL,'1','Tot de insecten',0),
	(607,'2015-11-16 15:59:15','2015-11-16 15:59:15',NULL,'1','Tot de kreeftachtigen',0),
	(608,'2015-11-16 15:59:15','2015-11-16 15:59:15',NULL,'1','Tot de spinachtigen',0),
	(609,'2015-11-16 16:04:35','2015-11-16 16:04:35',NULL,'1','Voortplanting is levendbarend en lichaamstemperatuur is contant',1),
	(610,'2015-11-16 16:04:35','2015-11-16 16:04:35',NULL,'1','Voortplanting is levendbarend en lichaamstemperatuur is niet constant',0),
	(611,'2015-11-16 16:04:35','2015-11-16 16:04:35',NULL,'1','Voortplanting is door eieren en lichaamstemperatuur is constant',0),
	(612,'2015-11-16 16:04:36','2015-11-16 16:04:36',NULL,'1','Voortplanting is door eieren en lichaamstemperatuur is niet constant',0),
	(613,'2015-11-16 16:28:10','2015-11-16 16:28:10',NULL,'1','Sponzen',1),
	(614,'2015-11-16 16:28:10','2015-11-16 16:28:10',NULL,'1','Eencelligen',0),
	(615,'2015-11-16 16:28:10','2015-11-16 16:28:10',NULL,'1','Holtedieren',0),
	(616,'2015-11-16 16:28:11','2015-11-16 16:28:11',NULL,'1','Wormen',0),
	(617,'2015-11-16 16:28:11','2015-11-16 16:28:11',NULL,'1','Weekdieren',0),
	(618,'2015-11-16 16:28:11','2015-11-16 16:28:11',NULL,'1','Geleedpotigen',0),
	(619,'2015-11-16 16:28:11','2015-11-16 16:28:11',NULL,'1','Stekelhuidigen',0),
	(620,'2015-11-16 16:28:11','2015-11-16 16:28:11',NULL,'1','Gewervelden',0),
	(621,'2015-11-16 16:28:11','2015-11-16 16:28:11',NULL,'2','Geleedpotigen',1),
	(622,'2015-11-16 16:28:11','2015-11-16 16:28:11',NULL,'2','Sponzen',0),
	(623,'2015-11-16 16:28:11','2015-11-16 16:28:11',NULL,'2','Holtedieren',0),
	(624,'2015-11-16 16:28:11','2015-11-16 16:28:11',NULL,'2','Wormen',0),
	(625,'2015-11-16 16:28:11','2015-11-16 16:28:11',NULL,'2','Weekdieren',0),
	(626,'2015-11-16 16:28:11','2015-11-16 16:28:11',NULL,'2','Eencelligen',0),
	(627,'2015-11-16 16:28:11','2015-11-16 16:28:11',NULL,'2','Stekelhuidigen',0),
	(628,'2015-11-16 16:28:11','2015-11-16 16:28:11',NULL,'2','Gewervelden',0),
	(629,'2015-11-16 16:28:11','2015-11-16 16:28:11',NULL,'3','Gewervelden',1),
	(630,'2015-11-16 16:28:11','2015-11-16 16:28:11',NULL,'3','Sponzen',0),
	(631,'2015-11-16 16:28:11','2015-11-16 16:28:11',NULL,'3','Holtedieren',0),
	(632,'2015-11-16 16:28:11','2015-11-16 16:28:11',NULL,'3','Wormen',0),
	(633,'2015-11-16 16:28:11','2015-11-16 16:28:11',NULL,'3','Weekdieren',0),
	(634,'2015-11-16 16:28:11','2015-11-16 16:28:11',NULL,'3','Geleedpotigen',0),
	(635,'2015-11-16 16:28:11','2015-11-16 16:28:11',NULL,'3','Stekelhuidigen',0),
	(636,'2015-11-16 16:28:11','2015-11-16 16:28:11',NULL,'3','Eencelligen',0),
	(637,'2015-11-16 16:28:12','2015-11-16 16:28:12',NULL,'4','Stekelhuidigen',1),
	(638,'2015-11-16 16:28:12','2015-11-16 16:28:12',NULL,'4','Sponzen',0),
	(639,'2015-11-16 16:28:12','2015-11-16 16:28:12',NULL,'4','Holtedieren',0),
	(640,'2015-11-16 16:28:12','2015-11-16 16:28:12',NULL,'4','Wormen',0),
	(641,'2015-11-16 16:28:12','2015-11-16 16:28:12',NULL,'4','Weekdieren',0),
	(642,'2015-11-16 16:28:12','2015-11-16 16:28:12',NULL,'4','Geleedpotigen',0),
	(643,'2015-11-16 16:28:12','2015-11-16 16:28:12',NULL,'4','Eencelligen',0),
	(644,'2015-11-16 16:28:12','2015-11-16 16:28:12',NULL,'4','Gewervelden',0),
	(645,'2015-11-16 16:28:12','2015-11-16 16:28:12',NULL,'5','Weekdieren',1),
	(646,'2015-11-16 16:28:12','2015-11-16 16:28:12',NULL,'5','Sponzen',0),
	(647,'2015-11-16 16:28:12','2015-11-16 16:28:12',NULL,'5','Holtedieren',0),
	(648,'2015-11-16 16:28:12','2015-11-16 16:28:12',NULL,'5','Wormen',0),
	(649,'2015-11-16 16:28:12','2015-11-16 16:28:12',NULL,'5','Eencelligen',0),
	(650,'2015-11-16 16:28:12','2015-11-16 16:28:12',NULL,'5','Geleedpotigen',0),
	(651,'2015-11-16 16:28:12','2015-11-16 16:28:12',NULL,'5','Stekelhuidigen',0),
	(652,'2015-11-16 16:28:12','2015-11-16 16:28:12',NULL,'5','Gewervelden',0),
	(653,'2015-11-16 16:28:12','2015-11-16 16:28:12',NULL,'6','Eencelligen',1),
	(654,'2015-11-16 16:28:12','2015-11-16 16:28:12',NULL,'6','Sponzen',0),
	(655,'2015-11-16 16:28:12','2015-11-16 16:28:12',NULL,'6','Holtedieren',0),
	(656,'2015-11-16 16:28:12','2015-11-16 16:28:12',NULL,'6','Wormen',0),
	(657,'2015-11-16 16:28:12','2015-11-16 16:28:12',NULL,'6','Weekdieren',0),
	(658,'2015-11-16 16:28:13','2015-11-16 16:28:13',NULL,'6','Geleedpotigen',0),
	(659,'2015-11-16 16:28:13','2015-11-16 16:28:13',NULL,'6','Stekelhuidigen',0),
	(660,'2015-11-16 16:28:13','2015-11-16 16:28:13',NULL,'6','Gewervelden',0),
	(661,'2015-11-16 16:28:13','2015-11-16 16:28:13',NULL,'7','Holtedieren',1),
	(662,'2015-11-16 16:28:13','2015-11-16 16:28:13',NULL,'7','Sponzen',0),
	(663,'2015-11-16 16:28:13','2015-11-16 16:28:13',NULL,'7','Eencelligen',0),
	(664,'2015-11-16 16:28:13','2015-11-16 16:28:13',NULL,'7','Wormen',0),
	(665,'2015-11-16 16:28:13','2015-11-16 16:28:13',NULL,'7','Weekdieren',0),
	(666,'2015-11-16 16:28:13','2015-11-16 16:28:13',NULL,'7','Geleedpotigen',0),
	(667,'2015-11-16 16:28:13','2015-11-16 16:28:13',NULL,'7','Stekelhuidigen',0),
	(668,'2015-11-16 16:28:13','2015-11-16 16:28:13',NULL,'7','Gewervelden',0),
	(669,'2015-11-17 18:12:07','2015-11-17 18:12:07',NULL,'1','studenten',1),
	(670,'2015-11-17 18:12:08','2015-11-17 18:12:08',NULL,'2','twee',1),
	(671,'2015-11-17 18:12:08','2015-11-17 18:12:08',NULL,'3','Eerste',1),
	(672,'2015-11-17 18:12:08','2015-11-17 18:12:08',NULL,'4','armere',1),
	(673,'2015-11-18 19:21:41','2015-11-18 19:23:10','2015-11-18 19:23:10','1','Tot vak 2',1),
	(674,'2015-11-18 19:21:41','2015-11-18 19:23:10','2015-11-18 19:23:10','1','Tot vak 1',0),
	(675,'2015-11-18 19:21:41','2015-11-18 19:23:10','2015-11-18 19:23:10','1','Tot vak 3',0),
	(676,'2015-11-18 19:21:41','2015-11-18 19:23:10','2015-11-18 19:23:10','1','Tot vak 4',0),
	(677,'2015-11-18 19:21:41','2015-11-18 19:23:11','2015-11-18 19:23:11','1','Tot vak 5',0),
	(678,'2015-11-18 19:21:41','2015-11-18 19:23:11','2015-11-18 19:23:11','2','Tot vak 3',1),
	(679,'2015-11-18 19:21:41','2015-11-18 19:23:11','2015-11-18 19:23:11','2','Tot vak 2',0),
	(680,'2015-11-18 19:21:41','2015-11-18 19:23:11','2015-11-18 19:23:11','2','Tot vak 1',0),
	(681,'2015-11-18 19:21:41','2015-11-18 19:23:11','2015-11-18 19:23:11','2','Tot vak 4',0),
	(682,'2015-11-18 19:21:41','2015-11-18 19:23:11','2015-11-18 19:23:11','2','Tot vak 5',0),
	(683,'2015-11-18 19:21:42','2015-11-18 19:23:11','2015-11-18 19:23:11','3','Tot vak 1',1),
	(684,'2015-11-18 19:21:42','2015-11-18 19:23:11','2015-11-18 19:23:11','3','Tot vak 2',0),
	(685,'2015-11-18 19:21:42','2015-11-18 19:23:11','2015-11-18 19:23:11','3','Tot vak 3',0),
	(686,'2015-11-18 19:21:42','2015-11-18 19:23:11','2015-11-18 19:23:11','3','Tot vak 4',0),
	(687,'2015-11-18 19:21:42','2015-11-18 19:23:11','2015-11-18 19:23:11','3','Tot vak 5',0),
	(688,'2015-11-18 19:23:11','2015-12-21 10:18:42','2015-12-21 10:18:42','1','Tot vak 2',1),
	(689,'2015-11-18 19:23:11','2015-12-21 10:18:42','2015-12-21 10:18:42','1','Tot vak 1',0),
	(690,'2015-11-18 19:23:11','2015-12-21 10:18:42','2015-12-21 10:18:42','1','Tot vak 3',0),
	(691,'2015-11-18 19:23:11','2015-12-21 10:18:42','2015-12-21 10:18:42','1','Tot vak 4',0),
	(692,'2015-11-18 19:23:11','2015-12-21 10:18:42','2015-12-21 10:18:42','1','Tot vak 5',0),
	(693,'2015-11-18 19:23:11','2015-12-21 10:18:42','2015-12-21 10:18:42','2','Tot vak 3',1),
	(694,'2015-11-18 19:23:11','2015-12-21 10:18:42','2015-12-21 10:18:42','2','Tot vak 2',0),
	(695,'2015-11-18 19:23:11','2015-12-21 10:18:42','2015-12-21 10:18:42','2','Tot vak 1',0),
	(696,'2015-11-18 19:23:11','2015-12-21 10:18:42','2015-12-21 10:18:42','2','Tot vak 4',0),
	(697,'2015-11-18 19:23:11','2015-12-21 10:18:42','2015-12-21 10:18:42','2','Tot vak 5',0),
	(698,'2015-11-18 19:23:11','2015-12-21 10:18:42','2015-12-21 10:18:42','3','Tot vak 1',1),
	(699,'2015-11-18 19:23:11','2015-12-21 10:18:42','2015-12-21 10:18:42','3','Tot vak 2',0),
	(700,'2015-11-18 19:23:11','2015-12-21 10:18:42','2015-12-21 10:18:42','3','Tot vak 3',0),
	(701,'2015-11-18 19:23:11','2015-12-21 10:18:42','2015-12-21 10:18:42','3','Tot vak 4',0),
	(702,'2015-11-18 19:23:11','2015-12-21 10:18:42','2015-12-21 10:18:42','3','Tot vak 5',0),
	(703,'2015-11-18 19:28:47','2015-11-18 19:28:47',NULL,'1','Met 1.',1),
	(704,'2015-11-18 19:28:47','2015-11-18 19:28:47',NULL,'1','Met 2.',0),
	(705,'2015-11-18 19:28:47','2015-11-18 19:28:47',NULL,'1','Met 3.',0),
	(706,'2015-11-18 19:28:47','2015-11-18 19:28:47',NULL,'1','Met 4.',0),
	(707,'2015-11-18 19:28:47','2015-11-18 19:28:47',NULL,'1','Met 5.',0),
	(708,'2015-11-18 19:28:48','2015-11-18 19:28:48',NULL,'2','Met 5.',1),
	(709,'2015-11-18 19:28:48','2015-11-18 19:28:48',NULL,'2','Met 2.',0),
	(710,'2015-11-18 19:28:48','2015-11-18 19:28:48',NULL,'2','Met 3.',0),
	(711,'2015-11-18 19:28:48','2015-11-18 19:28:48',NULL,'2','Met 4.',0),
	(712,'2015-11-18 19:28:48','2015-11-18 19:28:48',NULL,'2','Met 1.',0),
	(713,'2015-11-25 14:15:48','2015-11-25 14:15:48',NULL,'1','NEE',1),
	(714,'2015-11-25 14:15:48','2015-11-25 14:15:48',NULL,'1','JA',0),
	(715,'2015-12-09 11:22:42','2015-12-09 11:22:42',NULL,'1','klimaatbronnen',1),
	(716,'2015-12-09 11:22:42','2015-12-09 11:22:42',NULL,'1','klimaatfactoren',0),
	(717,'2015-12-14 10:47:29','2015-12-14 10:47:29',NULL,'1','Amsterdam',1),
	(718,'2015-12-14 10:47:29','2015-12-14 10:47:29',NULL,'2','VVD',1),
	(719,'2015-12-14 10:47:30','2015-12-14 10:47:30',NULL,'3','PvdA',1),
	(720,'2015-12-14 10:47:30','2015-12-14 10:47:30',NULL,'4','basisbeurs',1),
	(721,'2015-12-14 10:47:30','2015-12-14 10:47:30',NULL,'5','Amsterdam',1),
	(722,'2015-12-14 10:47:30','2015-12-14 10:47:30',NULL,'6','Amsterdam',1),
	(723,'2015-12-14 10:47:30','2015-12-14 10:47:30',NULL,'7','D66',1),
	(724,'2015-12-14 10:47:30','2015-12-14 10:47:30',NULL,'8','economische',1),
	(725,'2015-12-18 15:42:46','2015-12-18 15:42:46',NULL,'1','JUIST',1),
	(726,'2015-12-18 15:42:46','2015-12-18 15:42:46',NULL,'1','ONJUIST',0),
	(727,'2015-12-18 15:42:46','2015-12-18 15:42:46',NULL,'2','ONJUIST',1),
	(728,'2015-12-18 15:42:46','2015-12-18 15:42:46',NULL,'2','JUIST',0),
	(729,'2015-12-18 15:42:46','2015-12-18 15:42:46',NULL,'3','ONJUIST',1),
	(730,'2015-12-18 15:42:46','2015-12-18 15:42:46',NULL,'3','JUIST',0),
	(731,'2015-12-18 15:42:46','2015-12-18 15:42:46',NULL,'4','ONJUIST',1),
	(732,'2015-12-18 15:42:46','2015-12-18 15:42:46',NULL,'4','JUIST',0),
	(733,'2015-12-18 15:42:46','2015-12-18 15:42:46',NULL,'5','ONJUIST',1),
	(734,'2015-12-18 15:42:46','2015-12-18 15:42:46',NULL,'5','JUIST',0),
	(735,'2015-12-18 15:42:46','2015-12-18 15:42:46',NULL,'6','JUIST',1),
	(736,'2015-12-18 15:42:46','2015-12-18 15:42:46',NULL,'6','ONJUIST',0),
	(737,'2015-12-18 15:42:46','2015-12-18 15:42:46',NULL,'7','JUIST',1),
	(738,'2015-12-18 15:42:46','2015-12-18 15:42:46',NULL,'7','ONJUIST',0),
	(739,'2015-12-18 15:42:46','2015-12-18 15:42:46',NULL,'8','ONJUIST',1),
	(740,'2015-12-18 15:42:46','2015-12-18 15:42:46',NULL,'8','JUIST',0),
	(741,'2015-12-21 10:06:56','2015-12-21 10:06:56',NULL,'1','rode bloedcellen',1),
	(742,'2015-12-21 10:06:56','2015-12-21 10:06:56',NULL,'2','bloedplasma',1),
	(743,'2015-12-21 10:06:56','2015-12-21 10:06:56',NULL,'3','witte bloedcellen',1),
	(744,'2015-12-21 10:06:56','2015-12-21 10:06:56',NULL,'4','witte bloedcellen',1),
	(745,'2015-12-21 10:06:56','2015-12-21 10:06:56',NULL,'5','rode bloedcellen',1),
	(746,'2015-12-21 10:06:56','2015-12-21 10:06:56',NULL,'6','bloedplasma',1),
	(747,'2015-12-21 10:06:56','2015-12-21 10:06:56',NULL,'7','hemoglobine',1),
	(748,'2015-12-21 10:06:56','2015-12-21 10:06:56',NULL,'8','trombose',1),
	(749,'2015-12-21 10:06:56','2015-12-21 10:06:56',NULL,'9','bloedplaatjes',1),
	(750,'2015-12-21 10:11:17','2015-12-21 10:11:17',NULL,'1','bloedplasma',1),
	(751,'2015-12-21 10:11:18','2015-12-21 10:11:18',NULL,'2','een rode bloedcel',1),
	(752,'2015-12-21 10:18:42','2015-12-21 10:18:42',NULL,'1','Tot vak 2',1),
	(753,'2015-12-21 10:18:42','2015-12-21 10:18:42',NULL,'1','Tot vak 1',0),
	(754,'2015-12-21 10:18:42','2015-12-21 10:18:42',NULL,'1','Tot vak 3',0),
	(755,'2015-12-21 10:18:42','2015-12-21 10:18:42',NULL,'1','Tot vak 4',0),
	(756,'2015-12-21 10:18:43','2015-12-21 10:18:43',NULL,'1','Tot vak 5',0),
	(757,'2015-12-21 10:18:43','2015-12-21 10:18:43',NULL,'2','Tot vak 3',1),
	(758,'2015-12-21 10:18:43','2015-12-21 10:18:43',NULL,'2','Tot vak 2',0),
	(759,'2015-12-21 10:18:43','2015-12-21 10:18:43',NULL,'2','Tot vak 1',0),
	(760,'2015-12-21 10:18:43','2015-12-21 10:18:43',NULL,'2','Tot vak 4',0),
	(761,'2015-12-21 10:18:43','2015-12-21 10:18:43',NULL,'2','Tot vak 5',0),
	(762,'2015-12-21 10:18:43','2015-12-21 10:18:43',NULL,'3','Tot vak 1',1),
	(763,'2015-12-21 10:18:43','2015-12-21 10:18:43',NULL,'3','Tot vak 2',0),
	(764,'2015-12-21 10:18:43','2015-12-21 10:18:43',NULL,'3','Tot vak 3',0),
	(765,'2015-12-21 10:18:43','2015-12-21 10:18:43',NULL,'3','Tot vak 4',0),
	(766,'2015-12-21 10:18:43','2015-12-21 10:18:43',NULL,'3','Tot vak 5',0),
	(767,'2015-12-21 10:18:54','2015-12-21 10:18:54',NULL,'1','1',1),
	(768,'2015-12-21 10:21:14','2015-12-21 10:21:14',NULL,'1','bloedplasma',1),
	(769,'2015-12-21 10:21:14','2015-12-21 10:21:14',NULL,'2','een rode bloedcel',1),
	(770,'2015-12-21 10:31:43','2015-12-21 10:31:43',NULL,'1','kleine',1),
	(771,'2015-12-21 10:31:43','2015-12-21 10:31:43',NULL,'1','grote',0),
	(772,'2015-12-21 10:38:07','2015-12-21 10:38:07',NULL,'1','JUIST',1),
	(773,'2015-12-21 10:38:08','2015-12-21 10:38:08',NULL,'1','ONJUIST',0),
	(774,'2015-12-21 10:38:08','2015-12-21 10:38:08',NULL,'2','ONJUIST',1),
	(775,'2015-12-21 10:38:08','2015-12-21 10:38:08',NULL,'2','JUIST',0),
	(776,'2015-12-21 10:38:08','2015-12-21 10:38:08',NULL,'3','JUIST',1),
	(777,'2015-12-21 10:38:08','2015-12-21 10:38:08',NULL,'3','ONJUIST',0),
	(778,'2015-12-21 10:38:08','2015-12-21 10:38:08',NULL,'4','ONJUIST',1),
	(779,'2015-12-21 10:38:08','2015-12-21 10:38:08',NULL,'4','JUIST',0),
	(780,'2015-12-21 10:38:08','2015-12-21 10:38:08',NULL,'5','ONJUIST',1),
	(781,'2015-12-21 10:38:08','2015-12-21 10:38:08',NULL,'5','JUIST',0),
	(782,'2015-12-21 11:13:19','2015-12-21 11:14:41','2015-12-21 11:14:41','1','van het hart af',1),
	(783,'2015-12-21 11:13:19','2015-12-21 11:14:41','2015-12-21 11:14:41','1','naar het hart toe',0),
	(784,'2015-12-21 11:13:19','2015-12-21 11:14:41','2015-12-21 11:14:41','2','hoog',1),
	(785,'2015-12-21 11:13:19','2015-12-21 11:14:41','2015-12-21 11:14:41','2','laag',0),
	(786,'2015-12-21 11:13:19','2015-12-21 11:14:41','2015-12-21 11:14:41','3','dikker',1),
	(787,'2015-12-21 11:13:19','2015-12-21 11:14:41','2015-12-21 11:14:41','3','dunner',0),
	(788,'2015-12-21 11:13:19','2015-12-21 11:14:41','2015-12-21 11:14:41','4','elastisch',1),
	(789,'2015-12-21 11:13:19','2015-12-21 11:14:41','2015-12-21 11:14:41','4','minder elastischer',0),
	(790,'2015-12-21 11:13:19','2015-12-21 11:14:41','2015-12-21 11:14:41','5','diep',1),
	(791,'2015-12-21 11:13:20','2015-12-21 11:14:41','2015-12-21 11:14:41','5','aan de oppervlakte',0),
	(792,'2015-12-21 11:13:20','2015-12-21 11:14:41','2015-12-21 11:14:41','6','naar het hart toe',1),
	(793,'2015-12-21 11:13:20','2015-12-21 11:14:41','2015-12-21 11:14:41','6','van het hart af',0),
	(794,'2015-12-21 11:13:20','2015-12-21 11:14:41','2015-12-21 11:14:41','7','laag',1),
	(795,'2015-12-21 11:13:20','2015-12-21 11:14:41','2015-12-21 11:14:41','7','hoog',0),
	(796,'2015-12-21 11:13:20','2015-12-21 11:14:41','2015-12-21 11:14:41','8','dunner',1),
	(797,'2015-12-21 11:13:20','2015-12-21 11:14:41','2015-12-21 11:14:41','8','dikker',0),
	(798,'2015-12-21 11:13:20','2015-12-21 11:14:41','2015-12-21 11:14:41','9','minder elastisch',1),
	(799,'2015-12-21 11:13:20','2015-12-21 11:14:41','2015-12-21 11:14:41','9','elastisch',0),
	(800,'2015-12-21 11:13:20','2015-12-21 11:14:41','2015-12-21 11:14:41','10','aan de oppervlakte',1),
	(801,'2015-12-21 11:13:20','2015-12-21 11:14:41','2015-12-21 11:14:41','10','diep in het lichaam',0),
	(802,'2015-12-21 11:14:41','2015-12-21 11:14:41',NULL,'1','van het hart af',1),
	(803,'2015-12-21 11:14:41','2015-12-21 11:14:41',NULL,'1','naar het hart toe',0),
	(804,'2015-12-21 11:14:41','2015-12-21 11:14:41',NULL,'2','hoog',1),
	(805,'2015-12-21 11:14:41','2015-12-21 11:14:41',NULL,'2','laag',0),
	(806,'2015-12-21 11:14:41','2015-12-21 11:14:41',NULL,'3','dikker',1),
	(807,'2015-12-21 11:14:41','2015-12-21 11:14:41',NULL,'3','dunner',0),
	(808,'2015-12-21 11:14:41','2015-12-21 11:14:41',NULL,'4','elastisch',1),
	(809,'2015-12-21 11:14:41','2015-12-21 11:14:41',NULL,'4','minder elastischer',0),
	(810,'2015-12-21 11:14:41','2015-12-21 11:14:41',NULL,'5','diep',1),
	(811,'2015-12-21 11:14:41','2015-12-21 11:14:41',NULL,'5','aan de oppervlakte',0),
	(812,'2015-12-21 11:14:41','2015-12-21 11:14:41',NULL,'6','naar het hart toe',1),
	(813,'2015-12-21 11:14:41','2015-12-21 11:14:41',NULL,'6','van het hart af',0),
	(814,'2015-12-21 11:14:41','2015-12-21 11:14:41',NULL,'7','laag',1),
	(815,'2015-12-21 11:14:41','2015-12-21 11:14:41',NULL,'7','hoog',0),
	(816,'2015-12-21 11:14:41','2015-12-21 11:14:41',NULL,'8','dunner',1),
	(817,'2015-12-21 11:14:41','2015-12-21 11:14:41',NULL,'8','dikker',0),
	(818,'2015-12-21 11:14:41','2015-12-21 11:14:41',NULL,'9','minder elastisch',1),
	(819,'2015-12-21 11:14:41','2015-12-21 11:14:41',NULL,'9','elastisch',0),
	(820,'2015-12-21 11:14:42','2015-12-21 11:14:42',NULL,'10','aan de oppervlakte',1),
	(821,'2015-12-21 11:14:42','2015-12-21 11:14:42',NULL,'10','diep in het lichaam',0),
	(822,'2015-12-21 11:21:47','2015-12-21 11:21:47',NULL,'1','boezems',1),
	(823,'2015-12-21 11:21:47','2015-12-21 11:21:47',NULL,'2','boezems',1),
	(824,'2015-12-21 11:21:47','2015-12-21 11:21:47',NULL,'3','kamers',1),
	(825,'2015-12-21 11:21:47','2015-12-21 11:21:47',NULL,'4','open',1),
	(826,'2015-12-21 11:21:47','2015-12-21 11:21:47',NULL,'5','gesloten',1),
	(827,'2015-12-21 11:21:47','2015-12-21 11:21:47',NULL,'6','kamers',1),
	(828,'2015-12-21 11:21:47','2015-12-21 11:21:47',NULL,'7','dicht',1),
	(829,'2015-12-21 11:21:48','2015-12-21 11:21:48',NULL,'8','(bloed)druk',1),
	(830,'2015-12-21 11:21:48','2015-12-21 11:21:48',NULL,'9','open',1),
	(831,'2015-12-21 11:21:48','2015-12-21 11:21:48',NULL,'10','slagaders (aorta en longslagader)',1),
	(832,'2015-12-21 11:21:48','2015-12-21 11:21:48',NULL,'11','aders (onderste en bovenste holle aders en longaders)',1),
	(833,'2015-12-21 11:21:48','2015-12-21 11:21:48',NULL,'12','boezems',1),
	(834,'2015-12-21 11:21:48','2015-12-21 11:21:48',NULL,'13','kamers',1),
	(835,'2015-12-21 11:21:48','2015-12-21 11:21:48',NULL,'14','open',1),
	(836,'2015-12-21 11:21:48','2015-12-21 11:21:48',NULL,'15','dicht',1),
	(837,'2015-12-21 11:27:07','2015-12-21 11:27:13','2015-12-21 11:27:13','1','De grote',1),
	(838,'2015-12-21 11:27:07','2015-12-21 11:27:13','2015-12-21 11:27:13','1','De kleine',0),
	(839,'2015-12-21 11:27:13','2015-12-21 11:27:13',NULL,'1','De grote',1),
	(840,'2015-12-21 11:27:13','2015-12-21 11:27:13',NULL,'1','De kleine',0),
	(841,'2015-12-21 11:28:37','2015-12-21 11:28:37',NULL,'1','Zuurstofrijk bloed',1),
	(842,'2015-12-21 11:28:37','2015-12-21 11:28:37',NULL,'1','Zuurstofarm bloed',0),
	(843,'2015-12-21 11:30:48','2015-12-21 11:30:48',NULL,'1','bloedvat 3',1),
	(844,'2015-12-21 11:30:48','2015-12-21 11:30:48',NULL,'1','bloedvat 7',0),
	(845,'2015-12-21 11:31:47','2015-12-21 11:31:47',NULL,'1','Van het hart weg.',1),
	(846,'2015-12-21 11:31:47','2015-12-21 11:31:47',NULL,'1','Naar het hart toe.',0),
	(847,'2015-12-21 12:14:40','2015-12-21 12:14:40',NULL,'1','JA',1),
	(848,'2015-12-21 12:14:40','2015-12-21 12:14:40',NULL,'1','NEE',0),
	(849,'2015-12-21 12:15:01','2015-12-21 12:15:01',NULL,'1','WEL',1),
	(850,'2015-12-21 12:15:01','2015-12-21 12:15:01',NULL,'1','GEEN',0),
	(851,'2015-12-21 12:15:01','2015-12-21 12:15:01',NULL,'2','WEL',1),
	(852,'2015-12-21 12:15:01','2015-12-21 12:15:01',NULL,'2','GEEN',0),
	(853,'2015-12-21 12:15:01','2015-12-21 12:15:01',NULL,'3','WEL',1),
	(854,'2015-12-21 12:15:01','2015-12-21 12:15:01',NULL,'3','GEEN',0),
	(855,'2015-12-21 12:15:01','2015-12-21 12:15:01',NULL,'4','WEL',1),
	(856,'2015-12-21 12:15:01','2015-12-21 12:15:01',NULL,'4','GEEN',0),
	(857,'2015-12-21 12:15:01','2015-12-21 12:15:01',NULL,'5','WEL',1),
	(858,'2015-12-21 12:15:01','2015-12-21 12:15:01',NULL,'5','GEEN',0),
	(859,'2015-12-21 12:15:16','2015-12-21 12:15:16',NULL,'1','een',1),
	(860,'2015-12-21 12:15:16','2015-12-21 12:15:16',NULL,'1','geen',0),
	(861,'2015-12-21 12:15:16','2015-12-21 12:15:16',NULL,'2','geen',1),
	(862,'2015-12-21 12:15:16','2015-12-21 12:15:16',NULL,'2','een',0),
	(863,'2015-12-21 12:15:16','2015-12-21 12:15:16',NULL,'3','geen',1),
	(864,'2015-12-21 12:15:16','2015-12-21 12:15:16',NULL,'3','een',0),
	(865,'2015-12-21 12:15:16','2015-12-21 12:15:16',NULL,'4','een',1),
	(866,'2015-12-21 12:15:16','2015-12-21 12:15:16',NULL,'4','geen',0),
	(867,'2015-12-21 12:15:16','2015-12-21 12:15:16',NULL,'5','geen',1),
	(868,'2015-12-21 12:15:16','2015-12-21 12:15:16',NULL,'5','een',0),
	(869,'2015-12-21 12:15:16','2015-12-21 12:15:16',NULL,'6','een',1),
	(870,'2015-12-21 12:15:16','2015-12-21 12:15:16',NULL,'6','geen',0),
	(871,'2015-12-21 12:15:16','2015-12-21 12:15:16',NULL,'7','een',1),
	(872,'2015-12-21 12:15:16','2015-12-21 12:15:16',NULL,'7','geen',0),
	(873,'2015-12-21 12:15:16','2015-12-21 12:15:16',NULL,'8','een',1),
	(874,'2015-12-21 12:15:16','2015-12-21 12:15:16',NULL,'8','geen',0),
	(875,'2015-12-21 12:15:30','2015-12-21 12:15:30',NULL,'1','20',1),
	(876,'2015-12-21 12:15:30','2015-12-21 12:15:30',NULL,'1','40',0),
	(877,'2015-12-21 12:15:30','2015-12-21 12:15:30',NULL,'1','22',0),
	(878,'2015-12-21 12:15:30','2015-12-21 12:15:30',NULL,'1','44',0),
	(879,'2015-12-21 12:15:30','2015-12-21 12:15:30',NULL,'1','23',0),
	(880,'2015-12-21 12:15:30','2015-12-21 12:15:30',NULL,'1','46',0),
	(881,'2015-12-21 12:15:30','2015-12-21 12:15:30',NULL,'1','24',0),
	(882,'2015-12-21 12:15:30','2015-12-21 12:15:30',NULL,'1','48',0),
	(883,'2015-12-21 12:15:30','2015-12-21 12:15:30',NULL,'1','80',0),
	(884,'2015-12-21 12:15:30','2015-12-21 12:15:30',NULL,'1','10',0),
	(885,'2015-12-21 12:15:30','2015-12-21 12:15:30',NULL,'2','zijdeaapje',1),
	(886,'2015-12-21 12:15:30','2015-12-21 12:15:30',NULL,'2','varken',0),
	(887,'2015-12-21 12:15:30','2015-12-21 12:15:30',NULL,'2','konijn',0),
	(888,'2015-12-21 12:15:30','2015-12-21 12:15:30',NULL,'2','chimpansee',0),
	(889,'2015-12-21 12:29:19','2015-12-21 12:29:19',NULL,'1','NEE',1),
	(890,'2015-12-21 12:29:19','2015-12-21 12:29:19',NULL,'1','JA',0),
	(891,'2015-12-21 12:29:19','2015-12-21 12:29:19',NULL,'2','JA',1),
	(892,'2015-12-21 12:29:19','2015-12-21 12:29:19',NULL,'2','NEE',0),
	(893,'2015-12-21 12:32:04','2015-12-21 12:32:04',NULL,'1','6',1),
	(894,'2015-12-21 12:32:04','2015-12-21 12:32:04',NULL,'1','1',0),
	(895,'2015-12-21 12:32:04','2015-12-21 12:32:04',NULL,'1','2',0),
	(896,'2015-12-21 12:32:04','2015-12-21 12:32:04',NULL,'1','3',0),
	(897,'2015-12-21 12:32:04','2015-12-21 12:32:04',NULL,'1','4',0),
	(898,'2015-12-21 12:32:04','2015-12-21 12:32:04',NULL,'1','5',0),
	(899,'2015-12-21 12:32:04','2015-12-21 12:32:04',NULL,'1','7',0),
	(900,'2015-12-21 12:32:04','2015-12-21 12:32:04',NULL,'1','8',0),
	(901,'2015-12-21 12:32:04','2015-12-21 12:32:04',NULL,'1','9',0),
	(902,'2015-12-21 12:32:04','2015-12-21 12:32:04',NULL,'1','10',0),
	(903,'2015-12-21 12:32:18','2015-12-21 12:32:18',NULL,'1','controlegroep',1),
	(904,'2015-12-21 12:32:18','2015-12-21 12:32:18',NULL,'1','proefgroep',0),
	(905,'2016-01-20 13:40:14','2016-01-20 13:40:14',NULL,'1','hongersnood',1),
	(906,'2016-01-20 13:40:14','2016-01-20 13:40:14',NULL,'2','Rode Kruis',1),
	(907,'2016-01-20 13:40:14','2016-01-20 13:40:14',NULL,'3','de\r\nwinterkou',1),
	(908,'2016-01-20 13:40:14','2016-01-20 13:40:14',NULL,'4','voedseltekorten',1),
	(909,'2016-01-20 14:11:26','2016-01-20 14:11:26',NULL,'1','prostaatkanker',1),
	(910,'2016-01-20 14:11:26','2016-01-20 14:11:26',NULL,'1','Bassie te pakken',0),
	(911,'2016-01-20 14:11:26','2016-01-20 14:11:26',NULL,'1','Adriaan verkracht',0),
	(912,'2016-01-20 14:11:26','2016-01-20 14:11:26',NULL,'2','prostaatkanker',1),
	(913,'2016-01-20 14:11:26','2016-01-20 14:11:26',NULL,'2','Bassie te pakken',0),
	(914,'2016-01-20 14:11:26','2016-01-20 14:11:26',NULL,'2','Adriaan verkracht',0),
	(915,'2016-01-20 14:11:26','2016-01-20 14:11:26',NULL,'3','ziekte',1),
	(916,'2016-01-20 14:11:26','2016-01-20 14:11:26',NULL,'3','misdaad',0),
	(917,'2016-01-20 14:11:26','2016-01-20 14:11:26',NULL,'3','verkrachting',0),
	(918,'2016-01-20 14:11:26','2016-01-20 14:11:26',NULL,'4','prostaatkanker',1),
	(919,'2016-01-20 14:11:26','2016-01-20 14:11:26',NULL,'4','Bassie heeft opgegeten',0),
	(920,'2016-01-20 14:11:26','2016-01-20 14:11:26',NULL,'4','Adriaan heeft gepenetreert',0),
	(921,'2016-01-20 14:11:26','2016-01-20 14:11:26',NULL,'5','de botten',1),
	(922,'2016-01-20 14:11:26','2016-01-20 14:11:26',NULL,'5','B12',0),
	(923,'2016-01-20 14:11:26','2016-01-20 14:11:26',NULL,'5','Vlaardingen',0),
	(924,'2016-01-26 15:35:58','2016-01-26 15:35:58',NULL,'1','in de kleine bloedsomloop',1),
	(925,'2016-01-26 15:35:58','2016-01-26 15:35:58',NULL,'1','in de grote bloedsomloop',0),
	(926,'2016-01-26 15:38:34','2016-01-26 15:38:34',NULL,'1','halvemaanvormige kleppen',1),
	(927,'2016-01-26 15:38:34','2016-01-26 15:38:34',NULL,'2','haarvaten',1),
	(928,'2016-01-26 15:38:34','2016-01-26 15:38:34',NULL,'3','poortader',1),
	(929,'2016-01-26 15:41:55','2016-01-26 15:41:55',NULL,'1','urine',1),
	(930,'2016-01-26 15:46:57','2016-01-26 15:46:57',NULL,'1','nee',1),
	(931,'2016-01-26 15:46:57','2016-01-26 15:46:57',NULL,'1','ja',0),
	(932,'2016-01-26 15:56:01','2016-01-26 15:56:01',NULL,'1','nee',1),
	(933,'2016-01-26 15:56:01','2016-01-26 15:56:01',NULL,'1','ja',0),
	(934,'2016-01-26 15:56:01','2016-01-26 15:56:01',NULL,'2','ja',1),
	(935,'2016-01-26 15:56:01','2016-01-26 15:56:01',NULL,'2','nee',0),
	(936,'2016-01-26 16:01:53','2016-01-26 16:01:53',NULL,'1','7',1),
	(937,'2016-01-26 16:01:53','2016-01-26 16:01:53',NULL,'1','1',0),
	(938,'2016-01-26 16:01:53','2016-01-26 16:01:53',NULL,'1','2',0),
	(939,'2016-01-26 16:01:53','2016-01-26 16:01:53',NULL,'1','3',0),
	(940,'2016-01-26 16:01:53','2016-01-26 16:01:53',NULL,'1','4',0),
	(941,'2016-01-26 16:01:53','2016-01-26 16:01:53',NULL,'1','5',0),
	(942,'2016-01-26 16:01:53','2016-01-26 16:01:53',NULL,'1','6',0),
	(943,'2016-01-26 16:01:53','2016-01-26 16:01:53',NULL,'1','8',0),
	(944,'2016-01-26 16:01:53','2016-01-26 16:01:53',NULL,'1','9',0),
	(945,'2016-01-26 16:01:53','2016-01-26 16:01:53',NULL,'2','9',1),
	(946,'2016-01-26 16:01:53','2016-01-26 16:01:53',NULL,'2','8',0),
	(947,'2016-01-26 16:01:53','2016-01-26 16:01:53',NULL,'2','7',0),
	(948,'2016-01-26 16:01:53','2016-01-26 16:01:53',NULL,'2','6',0),
	(949,'2016-01-26 16:01:53','2016-01-26 16:01:53',NULL,'2','5',0),
	(950,'2016-01-26 16:01:53','2016-01-26 16:01:53',NULL,'2','4',0),
	(951,'2016-01-26 16:01:53','2016-01-26 16:01:53',NULL,'2','3',0),
	(952,'2016-01-26 16:01:53','2016-01-26 16:01:53',NULL,'2','2',0),
	(953,'2016-01-26 16:01:53','2016-01-26 16:01:53',NULL,'2','1',0),
	(954,'2016-01-26 16:01:53','2016-01-26 16:01:53',NULL,'3','weg',1),
	(955,'2016-01-26 16:01:53','2016-01-26 16:01:53',NULL,'3','toe',0),
	(956,'2016-01-26 16:01:53','2016-01-26 16:01:53',NULL,'4','4',1),
	(957,'2016-01-26 16:01:54','2016-01-26 16:01:54',NULL,'4','8',0),
	(958,'2016-01-26 16:06:41','2016-01-26 16:06:41',NULL,'1','kleine bloedsomloop',1),
	(959,'2016-01-26 16:06:41','2016-01-26 16:06:41',NULL,'1','grote bloedsomloop',0),
	(960,'2016-01-26 16:06:41','2016-01-26 16:06:41',NULL,'2','zuurstofarm bloed',1),
	(961,'2016-01-26 16:06:41','2016-01-26 16:06:41',NULL,'2','zuurstofrijk bloed',0),
	(962,'2016-01-26 16:06:41','2016-01-26 16:06:41',NULL,'3','4',1),
	(963,'2016-01-26 16:06:41','2016-01-26 16:06:41',NULL,'3','1',0),
	(964,'2016-01-26 16:06:41','2016-01-26 16:06:41',NULL,'3','2',0),
	(965,'2016-01-26 16:06:41','2016-01-26 16:06:41',NULL,'3','3',0),
	(966,'2016-01-26 16:06:41','2016-01-26 16:06:41',NULL,'3','5',0),
	(967,'2016-01-26 16:06:41','2016-01-26 16:06:41',NULL,'3','6',0),
	(968,'2016-01-26 16:06:41','2016-01-26 16:06:41',NULL,'3','7',0),
	(969,'2016-01-26 16:06:41','2016-01-26 16:06:41',NULL,'3','8',0),
	(970,'2016-01-26 16:06:41','2016-01-26 16:06:41',NULL,'3','9',0),
	(971,'2016-03-01 14:45:53','2016-03-01 14:46:00','2016-03-01 14:46:00','1','zonder hoorns',1),
	(972,'2016-03-01 14:45:53','2016-03-01 14:46:00','2016-03-01 14:46:00','1','met hoorns',0),
	(973,'2016-03-01 14:46:00','2016-03-01 14:46:00',NULL,'1','zonder hoorns',1),
	(974,'2016-03-01 14:46:00','2016-03-01 14:46:00',NULL,'1','met hoorns',0),
	(975,'2016-03-01 15:47:30','2016-03-01 15:47:30',NULL,'1','homozygoot',1),
	(976,'2016-03-01 15:47:30','2016-03-01 15:47:30',NULL,'1','heterozygoot',0),
	(977,'2016-03-01 18:20:50','2016-03-01 18:20:50',NULL,'1','is niet te zeggen',1),
	(978,'2016-03-01 18:20:50','2016-03-01 18:20:50',NULL,'1','linkshandig',0),
	(979,'2016-03-01 18:20:50','2016-03-01 18:20:50',NULL,'1','rechtshandig',0),
	(980,'2016-03-18 09:00:07','2016-03-18 09:00:07',NULL,'1','Hubble ruimtetelescoop',1),
	(981,'2016-03-18 09:00:07','2016-03-18 09:00:07',NULL,'1','James Webb ruimtetelescoop',0),
	(982,'2016-03-18 09:00:07','2016-03-18 09:00:07',NULL,'1','Bubble ruimtetelescoop',0),
	(983,'2016-03-18 09:00:07','2016-03-18 09:00:07',NULL,'1','Spinnen Web ruimtetelescoop',0),
	(984,'2016-03-18 09:00:07','2016-03-18 09:00:07',NULL,'2','zon',1),
	(985,'2016-03-18 09:00:07','2016-03-18 09:00:07',NULL,'2','aarde',0),
	(986,'2016-03-18 09:00:07','2016-03-18 09:00:07',NULL,'3','Goudvis',1),
	(987,'2016-03-18 09:00:07','2016-03-18 09:00:07',NULL,'3','Zilvervis',0),
	(988,'2016-03-18 09:00:07','2016-03-18 09:00:07',NULL,'4','Hubble',1),
	(989,'2016-03-18 09:00:07','2016-03-18 09:00:07',NULL,'4','Bubble',0),
	(990,'2016-03-18 09:00:07','2016-03-18 09:00:07',NULL,'4','Spinnen Web',0),
	(991,'2016-03-18 09:00:07','2016-03-18 09:00:07',NULL,'4','James Webb',0),
	(992,'2016-03-18 09:07:08','2016-03-18 09:07:08',NULL,'1','Porsche',1),
	(993,'2016-03-18 09:07:08','2016-03-18 09:07:08',NULL,'2','auto',1),
	(994,'2016-03-18 09:07:08','2016-03-18 09:07:08',NULL,'3','Porsche',1),
	(995,'2016-03-21 07:43:58','2016-03-21 07:43:58',NULL,'1','ontsluiting',1),
	(996,'2016-03-21 07:43:58','2016-03-21 07:43:58',NULL,'1','uitdrijving',0),
	(997,'2016-03-21 08:13:20','2016-03-21 08:13:20',NULL,'1','5',1),
	(998,'2016-03-21 08:13:20','2016-03-21 08:13:20',NULL,'1','1',0),
	(999,'2016-03-21 08:13:20','2016-03-21 08:13:20',NULL,'1','2',0),
	(1000,'2016-03-21 08:13:20','2016-03-21 08:13:20',NULL,'1','3',0),
	(1001,'2016-03-21 08:13:20','2016-03-21 08:13:20',NULL,'1','4',0),
	(1002,'2016-03-21 10:24:03','2016-03-21 10:24:03',NULL,'1','primair',1),
	(1003,'2016-03-21 10:24:03','2016-03-21 10:24:03',NULL,'1','secundair',0),
	(1004,'2016-03-21 10:24:03','2016-03-21 10:24:03',NULL,'2','secundair',1),
	(1005,'2016-03-21 10:24:03','2016-03-21 10:24:03',NULL,'2','primair',0),
	(1006,'2016-03-21 10:27:31','2016-03-21 10:27:31',NULL,'1','orgasme',1),
	(1007,'2016-03-21 10:27:31','2016-03-21 10:27:31',NULL,'1','masturbatie',0),
	(1008,'2016-03-21 10:27:31','2016-03-21 10:27:31',NULL,'2','eicellen',1),
	(1009,'2016-03-21 10:27:31','2016-03-21 10:27:31',NULL,'2','zaadcellen',0),
	(1010,'2016-03-21 10:27:31','2016-03-21 10:27:31',NULL,'3','van ongeveer 3 dagen vóór de ovulatie tot 1 dag na de ovulatie',1),
	(1011,'2016-03-21 10:27:31','2016-03-21 10:27:31',NULL,'3','van ongeveer 3 dagen vóór de menstruatie tot 1 dag na de menstruatie',0),
	(1012,'2016-03-21 10:32:04','2016-03-21 10:32:04',NULL,'1','in het baarmoederslijmvlies',1),
	(1013,'2016-03-21 10:32:04','2016-03-21 10:32:04',NULL,'1','in het slijmvlies van de vagina',0),
	(1014,'2016-03-21 10:32:04','2016-03-21 10:32:04',NULL,'2','van het bloed van het embryo naar het bloed van de moeder',1),
	(1015,'2016-03-21 10:32:04','2016-03-21 10:32:04',NULL,'2','van het bloed van de moeder naar het bloed van het embryo',0),
	(1016,'2016-03-21 10:41:21','2016-03-21 10:41:21',NULL,'1','niet',1),
	(1017,'2016-03-21 10:41:21','2016-03-21 10:41:21',NULL,'1','wel',0),
	(1018,'2016-03-21 10:41:21','2016-03-21 10:41:21',NULL,'2','groter',1),
	(1019,'2016-03-21 10:41:21','2016-03-21 10:41:21',NULL,'2','kleiner',0),
	(1020,'2016-03-24 08:46:09','2016-03-24 08:46:09',NULL,'1','Paleozoïcum',1),
	(1021,'2016-03-24 08:46:10','2016-03-24 08:46:10',NULL,'1','Mesozoïcum',0),
	(1022,'2016-03-24 08:46:10','2016-03-24 08:46:10',NULL,'1','Neozoïcum',0),
	(1023,'2016-03-24 09:03:35','2016-03-24 09:03:35',NULL,'1','4',1),
	(1024,'2016-03-24 09:03:35','2016-03-24 09:03:35',NULL,'1','1',0),
	(1025,'2016-03-24 09:03:35','2016-03-24 09:03:35',NULL,'1','2',0),
	(1026,'2016-03-24 09:03:35','2016-03-24 09:03:35',NULL,'1','3',0),
	(1027,'2016-03-24 09:03:35','2016-03-24 09:03:35',NULL,'1','5',0),
	(1028,'2016-03-24 09:03:35','2016-03-24 09:03:35',NULL,'1','6',0),
	(1029,'2016-03-24 09:03:35','2016-03-24 09:03:35',NULL,'1','7',0),
	(1030,'2016-03-24 09:03:35','2016-03-24 09:03:35',NULL,'1','8',0),
	(1031,'2016-03-24 09:06:31','2016-03-24 09:06:31',NULL,'1','4',1),
	(1032,'2016-03-24 09:06:31','2016-03-24 09:06:31',NULL,'1','1',0),
	(1033,'2016-03-24 09:06:31','2016-03-24 09:06:31',NULL,'1','2',0),
	(1034,'2016-03-24 09:06:32','2016-03-24 09:06:32',NULL,'1','3',0),
	(1035,'2016-03-24 09:06:32','2016-03-24 09:06:32',NULL,'1','5',0),
	(1036,'2016-03-24 09:06:32','2016-03-24 09:06:32',NULL,'1','8',0),
	(1037,'2016-03-24 09:07:50','2016-03-24 09:07:50',NULL,'1','1',1),
	(1038,'2016-03-24 09:07:50','2016-03-24 09:07:50',NULL,'1','3',0),
	(1039,'2016-03-24 09:07:50','2016-03-24 09:07:50',NULL,'1','4',0),
	(1040,'2016-03-24 09:07:50','2016-03-24 09:07:50',NULL,'1','5',0),
	(1041,'2016-03-24 09:07:50','2016-03-24 09:07:50',NULL,'1','6',0),
	(1042,'2016-03-24 09:07:50','2016-03-24 09:07:50',NULL,'1','7',0),
	(1043,'2016-03-24 09:07:50','2016-03-24 09:07:50',NULL,'1','8',0),
	(1044,'2016-03-24 09:07:50','2016-03-24 09:07:50',NULL,'1','42',0),
	(1045,'2016-03-24 09:08:38','2016-03-24 09:08:38',NULL,'1','3',1),
	(1046,'2016-03-24 09:08:38','2016-03-24 09:08:38',NULL,'1','7',0),
	(1047,'2016-03-24 09:14:55','2016-03-24 09:14:55',NULL,'1','ja',1),
	(1048,'2016-03-24 09:14:55','2016-03-24 09:14:55',NULL,'1','nee',0),
	(1049,'2016-03-24 09:14:55','2016-03-24 09:14:55',NULL,'2','ja',1),
	(1050,'2016-03-24 09:14:55','2016-03-24 09:14:55',NULL,'2','nee',0),
	(1051,'2016-03-24 09:20:17','2016-03-24 09:20:17',NULL,'1','Anja',1),
	(1052,'2016-03-24 09:20:17','2016-03-24 09:20:17',NULL,'1','Eric',0),
	(1053,'2016-03-24 09:20:17','2016-03-24 09:20:17',NULL,'1','Anja en Eric',0),
	(1054,'2016-03-24 09:20:17','2016-03-24 09:20:17',NULL,'2','Anja en Eric',1),
	(1055,'2016-03-24 09:20:17','2016-03-24 09:20:17',NULL,'2','Anja',0),
	(1056,'2016-03-24 09:20:17','2016-03-24 09:20:17',NULL,'2','Eric',0),
	(1057,'2016-03-25 10:52:18','2016-03-25 10:52:18',NULL,'1','erwy',1),
	(1058,'2016-03-25 10:52:50','2016-03-25 10:52:50',NULL,'1','tyedu',1),
	(1059,'2016-03-25 10:52:50','2016-03-25 10:52:50',NULL,'1','45',0),
	(1060,'2016-04-01 13:08:27','2016-04-01 13:09:46','2016-04-01 13:09:46','1','life',1),
	(1061,'2016-04-01 13:08:27','2016-04-01 13:09:46','2016-04-01 13:09:46','1','live',0),
	(1062,'2016-04-01 13:08:27','2016-04-01 13:09:46','2016-04-01 13:09:46','2','life',1),
	(1063,'2016-04-01 13:08:27','2016-04-01 13:09:46','2016-04-01 13:09:46','2','live',0),
	(1064,'2016-04-01 13:08:27','2016-04-01 13:09:46','2016-04-01 13:09:46','3','life',1),
	(1065,'2016-04-01 13:08:27','2016-04-01 13:09:46','2016-04-01 13:09:46','3','live',0),
	(1066,'2016-04-01 13:08:27','2016-04-01 13:09:46','2016-04-01 13:09:46','4','life',1),
	(1067,'2016-04-01 13:08:27','2016-04-01 13:09:46','2016-04-01 13:09:46','4','live',0),
	(1068,'2016-04-01 13:08:27','2016-04-01 13:09:46','2016-04-01 13:09:46','5','lifes',1),
	(1069,'2016-04-01 13:08:27','2016-04-01 13:09:46','2016-04-01 13:09:46','5','lives',0),
	(1070,'2016-04-01 13:09:46','2016-04-01 13:09:46',NULL,'1','life',1),
	(1071,'2016-04-01 13:09:46','2016-04-01 13:09:46',NULL,'1','live',0),
	(1072,'2016-04-01 13:09:46','2016-04-01 13:09:46',NULL,'2','life',1),
	(1073,'2016-04-01 13:09:46','2016-04-01 13:09:46',NULL,'2','live',0),
	(1074,'2016-04-01 13:09:47','2016-04-01 13:09:47',NULL,'3','life',1),
	(1075,'2016-04-01 13:09:47','2016-04-01 13:09:47',NULL,'3','live',0),
	(1076,'2016-04-01 13:09:47','2016-04-01 13:09:47',NULL,'4','live',1),
	(1077,'2016-04-01 13:09:47','2016-04-01 13:09:47',NULL,'4','life',0),
	(1078,'2016-04-01 13:09:47','2016-04-01 13:09:47',NULL,'5','lives',1),
	(1079,'2016-04-01 13:09:47','2016-04-01 13:09:47',NULL,'5','lifes',0),
	(1080,'2016-04-26 09:07:31','2016-04-26 09:07:31',NULL,'1','gatentekst',1),
	(1081,'2016-04-26 09:07:32','2016-04-26 09:07:32',NULL,'1','gatentekst',1),
	(1082,'2016-04-26 09:12:47','2016-04-26 09:12:47',NULL,'1','selectievraag',1),
	(1083,'2016-04-26 09:12:47','2016-04-26 09:12:47',NULL,'1','gatentekst',0),
	(1084,'2016-04-26 09:12:47','2016-04-26 09:12:47',NULL,'1','meerkeuze',0),
	(1085,'2016-05-18 21:27:46','2016-05-18 21:39:44','2016-05-18 21:39:44','1','JUIST',1),
	(1086,'2016-05-18 21:27:46','2016-05-18 21:39:44','2016-05-18 21:39:44','1','ONJUIST',0),
	(1087,'2016-05-18 21:27:46','2016-05-18 21:39:44','2016-05-18 21:39:44','2','ONJUIST',1),
	(1088,'2016-05-18 21:27:46','2016-05-18 21:39:44','2016-05-18 21:39:44','2','JUIST',0),
	(1089,'2016-05-18 21:27:46','2016-05-18 21:39:44','2016-05-18 21:39:44','3','JUIST',1),
	(1090,'2016-05-18 21:27:46','2016-05-18 21:39:44','2016-05-18 21:39:44','3','ONJUIST',0),
	(1091,'2016-05-18 21:27:46','2016-05-18 21:39:44','2016-05-18 21:39:44','4','JUIST',1),
	(1092,'2016-05-18 21:27:46','2016-05-18 21:39:44','2016-05-18 21:39:44','4','ONJUIST',0),
	(1093,'2016-05-18 21:27:46','2016-05-18 21:39:44','2016-05-18 21:39:44','5','ONJUIST',1),
	(1094,'2016-05-18 21:27:46','2016-05-18 21:39:44','2016-05-18 21:39:44','5','JUIST',0),
	(1095,'2016-05-18 21:30:00','2016-05-18 21:39:26','2016-05-18 21:39:26','1','JUIST',1),
	(1096,'2016-05-18 21:30:00','2016-05-18 21:39:26','2016-05-18 21:39:26','1','ONJUIST',0),
	(1097,'2016-05-18 21:30:00','2016-05-18 21:39:26','2016-05-18 21:39:26','2','ONJUIST',1),
	(1098,'2016-05-18 21:30:00','2016-05-18 21:39:26','2016-05-18 21:39:26','2','JUIST',0),
	(1099,'2016-05-18 21:30:00','2016-05-18 21:39:26','2016-05-18 21:39:26','3','JUIST',1),
	(1100,'2016-05-18 21:30:00','2016-05-18 21:39:26','2016-05-18 21:39:26','3','ONJUIST',0),
	(1101,'2016-05-18 21:30:00','2016-05-18 21:39:26','2016-05-18 21:39:26','4','JUIST',1),
	(1102,'2016-05-18 21:30:00','2016-05-18 21:39:26','2016-05-18 21:39:26','4','ONJUIST',0),
	(1103,'2016-05-18 21:30:00','2016-05-18 21:39:26','2016-05-18 21:39:26','5','ONJUIST',1),
	(1104,'2016-05-18 21:30:00','2016-05-18 21:39:26','2016-05-18 21:39:26','5','JUIST',0),
	(1105,'2016-05-18 21:32:05','2016-05-18 21:39:53','2016-05-18 21:39:53','1','ONJUIST',1),
	(1106,'2016-05-18 21:32:05','2016-05-18 21:39:53','2016-05-18 21:39:53','1','JUIST',0),
	(1107,'2016-05-18 21:32:05','2016-05-18 21:39:53','2016-05-18 21:39:53','2','ONJUIST',1),
	(1108,'2016-05-18 21:32:05','2016-05-18 21:39:53','2016-05-18 21:39:53','2','JUIST',0),
	(1109,'2016-05-18 21:34:47','2016-05-18 21:40:12','2016-05-18 21:40:12','1','JUIST',1),
	(1110,'2016-05-18 21:34:47','2016-05-18 21:40:12','2016-05-18 21:40:12','1','ONJUIST',0),
	(1111,'2016-05-18 21:34:47','2016-05-18 21:40:12','2016-05-18 21:40:12','2','ONJUIST',1),
	(1112,'2016-05-18 21:34:47','2016-05-18 21:40:12','2016-05-18 21:40:12','2','JUIST',0),
	(1113,'2016-05-18 21:34:47','2016-05-18 21:40:12','2016-05-18 21:40:12','3','ONJUIST',1),
	(1114,'2016-05-18 21:34:47','2016-05-18 21:40:12','2016-05-18 21:40:12','3','JUIST',0),
	(1115,'2016-05-18 21:34:47','2016-05-18 21:40:12','2016-05-18 21:40:12','4','JUIST',1),
	(1116,'2016-05-18 21:34:47','2016-05-18 21:40:12','2016-05-18 21:40:12','4','ONJUIST',0),
	(1117,'2016-05-18 21:39:26','2016-05-18 21:39:26',NULL,'1','JUIST',1),
	(1118,'2016-05-18 21:39:26','2016-05-18 21:39:26',NULL,'1','ONJUIST',0),
	(1119,'2016-05-18 21:39:26','2016-05-18 21:39:26',NULL,'2','ONJUIST',1),
	(1120,'2016-05-18 21:39:26','2016-05-18 21:39:26',NULL,'2','JUIST',0),
	(1121,'2016-05-18 21:39:26','2016-05-18 21:39:26',NULL,'3','JUIST',1),
	(1122,'2016-05-18 21:39:26','2016-05-18 21:39:26',NULL,'3','ONJUIST',0),
	(1123,'2016-05-18 21:39:26','2016-05-18 21:39:26',NULL,'4','JUIST',1),
	(1124,'2016-05-18 21:39:26','2016-05-18 21:39:26',NULL,'4','ONJUIST',0),
	(1125,'2016-05-18 21:39:27','2016-05-18 21:39:27',NULL,'5','ONJUIST',1),
	(1126,'2016-05-18 21:39:27','2016-05-18 21:39:27',NULL,'5','JUIST',0),
	(1127,'2016-05-18 21:39:44','2016-05-18 21:47:51','2016-05-18 21:47:51','1','JUIST',1),
	(1128,'2016-05-18 21:39:44','2016-05-18 21:47:51','2016-05-18 21:47:51','1','ONJUIST',0),
	(1129,'2016-05-18 21:39:44','2016-05-18 21:47:51','2016-05-18 21:47:51','2','ONJUIST',1),
	(1130,'2016-05-18 21:39:44','2016-05-18 21:47:51','2016-05-18 21:47:51','2','JUIST',0),
	(1131,'2016-05-18 21:39:44','2016-05-18 21:47:51','2016-05-18 21:47:51','3','JUIST',1),
	(1132,'2016-05-18 21:39:45','2016-05-18 21:47:51','2016-05-18 21:47:51','3','ONJUIST',0),
	(1133,'2016-05-18 21:39:45','2016-05-18 21:47:51','2016-05-18 21:47:51','4','JUIST',1),
	(1134,'2016-05-18 21:39:45','2016-05-18 21:47:51','2016-05-18 21:47:51','4','ONJUIST',0),
	(1135,'2016-05-18 21:39:45','2016-05-18 21:47:51','2016-05-18 21:47:51','5','ONJUIST',1),
	(1136,'2016-05-18 21:39:45','2016-05-18 21:47:51','2016-05-18 21:47:51','5','JUIST',0),
	(1137,'2016-05-18 21:39:53','2016-05-18 21:39:53',NULL,'1','ONJUIST',1),
	(1138,'2016-05-18 21:39:53','2016-05-18 21:39:53',NULL,'1','JUIST',0),
	(1139,'2016-05-18 21:39:53','2016-05-18 21:39:53',NULL,'2','ONJUIST',1),
	(1140,'2016-05-18 21:39:53','2016-05-18 21:39:53',NULL,'2','JUIST',0),
	(1141,'2016-05-18 21:40:12','2016-05-18 21:40:12',NULL,'1','JUIST',1),
	(1142,'2016-05-18 21:40:12','2016-05-18 21:40:12',NULL,'1','ONJUIST',0),
	(1143,'2016-05-18 21:40:12','2016-05-18 21:40:12',NULL,'2','ONJUIST',1),
	(1144,'2016-05-18 21:40:12','2016-05-18 21:40:12',NULL,'2','JUIST',0),
	(1145,'2016-05-18 21:40:12','2016-05-18 21:40:12',NULL,'3','ONJUIST',1),
	(1146,'2016-05-18 21:40:12','2016-05-18 21:40:12',NULL,'3','JUIST',0),
	(1147,'2016-05-18 21:40:12','2016-05-18 21:40:12',NULL,'4','JUIST',1),
	(1148,'2016-05-18 21:40:12','2016-05-18 21:40:12',NULL,'4','ONJUIST',0),
	(1149,'2016-05-18 21:43:11','2016-05-18 21:43:11',NULL,'1','JUIST',1),
	(1150,'2016-05-18 21:43:11','2016-05-18 21:43:11',NULL,'1','ONJUIST',0),
	(1151,'2016-05-18 21:43:11','2016-05-18 21:43:11',NULL,'2','JUIST',1),
	(1152,'2016-05-18 21:43:11','2016-05-18 21:43:11',NULL,'2','ONJUIST',0),
	(1153,'2016-05-18 21:43:11','2016-05-18 21:43:11',NULL,'3','JUIST',1),
	(1154,'2016-05-18 21:43:11','2016-05-18 21:43:11',NULL,'3','ONJUIST',0),
	(1155,'2016-05-18 21:43:11','2016-05-18 21:43:11',NULL,'4','JUIST',1),
	(1156,'2016-05-18 21:43:11','2016-05-18 21:43:11',NULL,'4','ONJUIST',0),
	(1157,'2016-05-18 21:43:11','2016-05-18 21:43:11',NULL,'5','ONJUIST',1),
	(1158,'2016-05-18 21:43:11','2016-05-18 21:43:11',NULL,'5','JUIST',0),
	(1159,'2016-05-18 21:44:55','2016-05-18 21:44:55',NULL,'1','ONJUIST',1),
	(1160,'2016-05-18 21:44:55','2016-05-18 21:44:55',NULL,'1','JUIST',0),
	(1161,'2016-05-18 21:44:55','2016-05-18 21:44:55',NULL,'2','ONJUIST',1),
	(1162,'2016-05-18 21:44:55','2016-05-18 21:44:55',NULL,'2','JUIST',0),
	(1163,'2016-05-18 21:47:19','2016-05-18 21:47:41','2016-05-18 21:47:41','1','JUIST',1),
	(1164,'2016-05-18 21:47:19','2016-05-18 21:47:41','2016-05-18 21:47:41','1','ONJUIST',0),
	(1165,'2016-05-18 21:47:20','2016-05-18 21:47:41','2016-05-18 21:47:41','2','JUIST',1),
	(1166,'2016-05-18 21:47:20','2016-05-18 21:47:41','2016-05-18 21:47:41','2','ONJUIST',0),
	(1167,'2016-05-18 21:47:20','2016-05-18 21:47:41','2016-05-18 21:47:41','3','JUIST',1),
	(1168,'2016-05-18 21:47:20','2016-05-18 21:47:41','2016-05-18 21:47:41','3','ONJUIST',0),
	(1169,'2016-05-18 21:47:41','2016-05-18 21:47:41',NULL,'1','JUIST',1),
	(1170,'2016-05-18 21:47:41','2016-05-18 21:47:41',NULL,'1','ONJUIST',0),
	(1171,'2016-05-18 21:47:41','2016-05-18 21:47:41',NULL,'2','JUIST',1),
	(1172,'2016-05-18 21:47:41','2016-05-18 21:47:41',NULL,'2','ONJUIST',0),
	(1173,'2016-05-18 21:47:41','2016-05-18 21:47:41',NULL,'3','JUIST',1),
	(1174,'2016-05-18 21:47:41','2016-05-18 21:47:41',NULL,'3','ONJUIST',0),
	(1175,'2016-05-18 21:47:51','2016-05-18 21:48:05','2016-05-18 21:48:05','1','JUIST',1),
	(1176,'2016-05-18 21:47:51','2016-05-18 21:48:05','2016-05-18 21:48:05','1','ONJUIST',0),
	(1177,'2016-05-18 21:47:51','2016-05-18 21:48:05','2016-05-18 21:48:05','2','ONJUIST',1),
	(1178,'2016-05-18 21:47:51','2016-05-18 21:48:05','2016-05-18 21:48:05','2','JUIST',0),
	(1179,'2016-05-18 21:47:51','2016-05-18 21:48:05','2016-05-18 21:48:05','3','JUIST',1),
	(1180,'2016-05-18 21:47:51','2016-05-18 21:48:05','2016-05-18 21:48:05','3','ONJUIST',0),
	(1181,'2016-05-18 21:47:51','2016-05-18 21:48:05','2016-05-18 21:48:05','4','JUIST',1),
	(1182,'2016-05-18 21:47:51','2016-05-18 21:48:05','2016-05-18 21:48:05','4','ONJUIST',0),
	(1183,'2016-05-18 21:47:51','2016-05-18 21:48:05','2016-05-18 21:48:05','5','ONJUIST',1),
	(1184,'2016-05-18 21:47:51','2016-05-18 21:48:05','2016-05-18 21:48:05','5','JUIST',0),
	(1185,'2016-05-18 21:48:05','2016-05-18 21:48:05',NULL,'1','JUIST',1),
	(1186,'2016-05-18 21:48:06','2016-05-18 21:48:06',NULL,'1','ONJUIST',0),
	(1187,'2016-05-18 21:48:06','2016-05-18 21:48:06',NULL,'2','ONJUIST',1),
	(1188,'2016-05-18 21:48:06','2016-05-18 21:48:06',NULL,'2','JUIST',0),
	(1189,'2016-05-18 21:48:06','2016-05-18 21:48:06',NULL,'3','JUIST',1),
	(1190,'2016-05-18 21:48:06','2016-05-18 21:48:06',NULL,'3','ONJUIST',0),
	(1191,'2016-05-18 21:48:06','2016-05-18 21:48:06',NULL,'4','JUIST',1),
	(1192,'2016-05-18 21:48:06','2016-05-18 21:48:06',NULL,'4','ONJUIST',0),
	(1193,'2016-05-18 21:48:06','2016-05-18 21:48:06',NULL,'5','ONJUIST',1),
	(1194,'2016-05-18 21:48:06','2016-05-18 21:48:06',NULL,'5','JUIST',0),
	(1195,'2016-05-18 21:51:27','2016-05-18 21:51:27',NULL,'1','ONJUIST',1),
	(1196,'2016-05-18 21:51:27','2016-05-18 21:51:27',NULL,'1','JUIST',0),
	(1197,'2016-05-18 21:51:27','2016-05-18 21:51:27',NULL,'2','ONJUIST',1),
	(1198,'2016-05-18 21:51:27','2016-05-18 21:51:27',NULL,'2','JUIST',0),
	(1199,'2016-05-18 21:51:27','2016-05-18 21:51:27',NULL,'3','ONJUIST',1),
	(1200,'2016-05-18 21:51:27','2016-05-18 21:51:27',NULL,'3','JUIST',0),
	(1201,'2016-05-18 21:51:27','2016-05-18 21:51:27',NULL,'4','JUIST',1),
	(1202,'2016-05-18 21:51:27','2016-05-18 21:51:27',NULL,'4','ONJUIST',0),
	(1203,'2016-05-18 21:51:27','2016-05-18 21:51:27',NULL,'5','JUIST',1),
	(1204,'2016-05-18 21:51:28','2016-05-18 21:51:28',NULL,'5','ONJUIST',0),
	(1205,'2016-05-18 21:59:18','2016-05-18 21:59:18',NULL,'1','ONJUIST',1),
	(1206,'2016-05-18 21:59:18','2016-05-18 21:59:18',NULL,'1','JUIST',0),
	(1207,'2016-05-18 21:59:18','2016-05-18 21:59:18',NULL,'2','JUIST',1),
	(1208,'2016-05-18 21:59:18','2016-05-18 21:59:18',NULL,'2','ONJUIST',0),
	(1209,'2016-05-18 21:59:18','2016-05-18 21:59:18',NULL,'3','JUIST',1),
	(1210,'2016-05-18 21:59:18','2016-05-18 21:59:18',NULL,'3','ONJUIST',0),
	(1211,'2016-05-18 21:59:18','2016-05-18 21:59:18',NULL,'4','JUIST',1),
	(1212,'2016-05-18 21:59:18','2016-05-18 21:59:18',NULL,'4','ONJUIST',0),
	(1213,'2016-05-18 22:01:24','2016-05-18 22:24:43','2016-05-18 22:24:43','1','JUIST',1),
	(1214,'2016-05-18 22:01:24','2016-05-18 22:24:43','2016-05-18 22:24:43','1','ONJUIST',0),
	(1215,'2016-05-18 22:01:24','2016-05-18 22:24:43','2016-05-18 22:24:43','2','ONJUIST',1),
	(1216,'2016-05-18 22:01:24','2016-05-18 22:24:43','2016-05-18 22:24:43','2','JUIST',0),
	(1217,'2016-05-18 22:24:43','2016-05-18 22:24:43',NULL,'1','JUIST',1),
	(1218,'2016-05-18 22:24:44','2016-05-18 22:24:44',NULL,'1','ONJUIST',0),
	(1219,'2016-05-18 22:24:44','2016-05-18 22:24:44',NULL,'2','ONJUIST',1),
	(1220,'2016-05-18 22:24:44','2016-05-18 22:24:44',NULL,'2','JUIST',0),
	(1221,'2016-05-22 19:54:43','2016-05-22 19:55:04','2016-05-22 19:55:04','1','JUIST',1),
	(1222,'2016-05-22 19:54:43','2016-05-22 19:55:04','2016-05-22 19:55:04','1','ONJUIST',0),
	(1223,'2016-05-22 19:54:43','2016-05-22 19:55:04','2016-05-22 19:55:04','2','JUIST',1),
	(1224,'2016-05-22 19:54:44','2016-05-22 19:55:04','2016-05-22 19:55:04','2','ONJUIST',0),
	(1225,'2016-05-22 19:54:44','2016-05-22 19:55:04','2016-05-22 19:55:04','3','ONJUIST',1),
	(1226,'2016-05-22 19:54:44','2016-05-22 19:55:04','2016-05-22 19:55:04','3','JUIST',0),
	(1227,'2016-05-22 19:54:44','2016-05-22 19:55:04','2016-05-22 19:55:04','4','ONJUIST',1),
	(1228,'2016-05-22 19:54:44','2016-05-22 19:55:04','2016-05-22 19:55:04','4','JUIST',0),
	(1229,'2016-05-22 19:54:44','2016-05-22 19:55:04','2016-05-22 19:55:04','5','ONJUIST',1),
	(1230,'2016-05-22 19:54:44','2016-05-22 19:55:04','2016-05-22 19:55:04','5','JUIST',0),
	(1231,'2016-05-22 19:55:04','2016-05-22 19:55:04',NULL,'1','JUIST',1),
	(1232,'2016-05-22 19:55:04','2016-05-22 19:55:04',NULL,'1','ONJUIST',0),
	(1233,'2016-05-22 19:55:04','2016-05-22 19:55:04',NULL,'2','JUIST',1),
	(1234,'2016-05-22 19:55:04','2016-05-22 19:55:04',NULL,'2','ONJUIST',0),
	(1235,'2016-05-22 19:55:04','2016-05-22 19:55:04',NULL,'3','ONJUIST',1),
	(1236,'2016-05-22 19:55:04','2016-05-22 19:55:04',NULL,'3','JUIST',0),
	(1237,'2016-05-22 19:55:04','2016-05-22 19:55:04',NULL,'4','ONJUIST',1),
	(1238,'2016-05-22 19:55:04','2016-05-22 19:55:04',NULL,'4','JUIST',0),
	(1239,'2016-05-22 19:55:04','2016-05-22 19:55:04',NULL,'5','ONJUIST',1),
	(1240,'2016-05-22 19:55:05','2016-05-22 19:55:05',NULL,'5','JUIST',0),
	(1241,'2016-05-22 21:33:08','2016-05-22 21:33:08',NULL,'1','Tij, Getijden, iets anders wat door docent is goedgekeurd',1),
	(1242,'2016-05-22 21:33:08','2016-05-22 21:33:08',NULL,'2','getij',1),
	(1243,'2016-05-22 21:33:08','2016-05-22 21:33:08',NULL,'3','zwaartekracht',1),
	(1244,'2016-05-22 21:33:08','2016-05-22 21:33:08',NULL,'4','Maan',1),
	(1245,'2016-05-22 21:33:08','2016-05-22 21:33:08',NULL,'5','Zon',1),
	(1246,'2016-05-22 22:30:13','2016-05-22 22:38:36','2016-05-22 22:38:36','1','&lt; 0,1 Mzon ',1),
	(1247,'2016-05-22 22:30:13','2016-05-22 22:38:36','2016-05-22 22:38:36','1',' &lt; 0,4 Mzon ',0),
	(1248,'2016-05-22 22:30:13','2016-05-22 22:38:36','2016-05-22 22:38:36','1',' &lt; 4 Mzon ',0),
	(1249,'2016-05-22 22:30:13','2016-05-22 22:38:36','2016-05-22 22:38:36','1',' &lt; 8 Mzon ',0),
	(1250,'2016-05-22 22:30:13','2016-05-22 22:38:36','2016-05-22 22:38:36','1',' &gt; 8 Mzon',0),
	(1251,'2016-05-22 22:38:36','2016-05-22 22:46:55','2016-05-22 22:46:55','1','minder dan 3000',1),
	(1252,'2016-05-22 22:38:36','2016-05-22 22:46:55','2016-05-22 22:46:55','1','plusminus 3500',0),
	(1253,'2016-05-22 22:38:36','2016-05-22 22:46:55','2016-05-22 22:46:55','1','plusminus 5500',0),
	(1254,'2016-05-22 22:38:36','2016-05-22 22:46:55','2016-05-22 22:46:55','1','plusminus 9000',0),
	(1255,'2016-05-22 22:38:36','2016-05-22 22:46:55','2016-05-22 22:46:55','1','plusminus 30.000',0),
	(1256,'2016-05-22 22:38:36','2016-05-22 22:46:55','2016-05-22 22:46:55','2','Bruin/Rood',1),
	(1257,'2016-05-22 22:38:36','2016-05-22 22:46:55','2016-05-22 22:46:55','2','Oranje/Rood',0),
	(1258,'2016-05-22 22:38:36','2016-05-22 22:46:55','2016-05-22 22:46:55','2','Geel',0),
	(1259,'2016-05-22 22:38:36','2016-05-22 22:46:55','2016-05-22 22:46:55','2','Wit',0),
	(1260,'2016-05-22 22:38:36','2016-05-22 22:46:55','2016-05-22 22:46:55','2','Blauw',0),
	(1261,'2016-05-22 22:38:36','2016-05-22 22:46:55','2016-05-22 22:46:55','3','plusminus 3500',1),
	(1262,'2016-05-22 22:38:36','2016-05-22 22:46:55','2016-05-22 22:46:55','3','plusminus 5500',0),
	(1263,'2016-05-22 22:38:36','2016-05-22 22:46:55','2016-05-22 22:46:55','3','plusminus 9000',0),
	(1264,'2016-05-22 22:38:36','2016-05-22 22:46:55','2016-05-22 22:46:55','3','plusminus 30.000',0),
	(1265,'2016-05-22 22:38:37','2016-05-22 22:46:55','2016-05-22 22:46:55','3','minder dan 3000',0),
	(1266,'2016-05-22 22:38:37','2016-05-22 22:46:55','2016-05-22 22:46:55','4','Oranje/Rood',1),
	(1267,'2016-05-22 22:38:37','2016-05-22 22:46:55','2016-05-22 22:46:55','4','Geel',0),
	(1268,'2016-05-22 22:38:37','2016-05-22 22:46:55','2016-05-22 22:46:55','4','Wit',0),
	(1269,'2016-05-22 22:38:37','2016-05-22 22:46:55','2016-05-22 22:46:55','4','Blauw',0),
	(1270,'2016-05-22 22:38:37','2016-05-22 22:46:55','2016-05-22 22:46:55','4','Bruin/Rood',0),
	(1271,'2016-05-22 22:38:37','2016-05-22 22:46:55','2016-05-22 22:46:55','5','plusminus 5500',1),
	(1272,'2016-05-22 22:38:37','2016-05-22 22:46:55','2016-05-22 22:46:55','5','plusminus 9000',0),
	(1273,'2016-05-22 22:38:37','2016-05-22 22:46:55','2016-05-22 22:46:55','5','plusminus 30.000',0),
	(1274,'2016-05-22 22:38:37','2016-05-22 22:46:55','2016-05-22 22:46:55','5','minder dan 3000',0),
	(1275,'2016-05-22 22:38:37','2016-05-22 22:46:55','2016-05-22 22:46:55','5','plusminus 3500',0),
	(1276,'2016-05-22 22:38:37','2016-05-22 22:46:55','2016-05-22 22:46:55','6','Geel',1),
	(1277,'2016-05-22 22:38:37','2016-05-22 22:46:55','2016-05-22 22:46:55','6','Wit',0),
	(1278,'2016-05-22 22:38:37','2016-05-22 22:46:55','2016-05-22 22:46:55','6','Blauw',0),
	(1279,'2016-05-22 22:38:37','2016-05-22 22:46:55','2016-05-22 22:46:55','6','Bruin/Rood',0),
	(1280,'2016-05-22 22:38:37','2016-05-22 22:46:55','2016-05-22 22:46:55','6','Oranje/Rood',0),
	(1281,'2016-05-22 22:38:37','2016-05-22 22:46:55','2016-05-22 22:46:55','7','plusminus 9000',1),
	(1282,'2016-05-22 22:38:37','2016-05-22 22:46:55','2016-05-22 22:46:55','7','plusminus 30.000',0),
	(1283,'2016-05-22 22:38:37','2016-05-22 22:46:55','2016-05-22 22:46:55','7','minder dan 3000',0),
	(1284,'2016-05-22 22:38:37','2016-05-22 22:46:55','2016-05-22 22:46:55','7','plusminus 3500',0),
	(1285,'2016-05-22 22:38:38','2016-05-22 22:46:55','2016-05-22 22:46:55','7','plusminus 5500',0),
	(1286,'2016-05-22 22:38:38','2016-05-22 22:46:55','2016-05-22 22:46:55','8','Wit',1),
	(1287,'2016-05-22 22:38:38','2016-05-22 22:46:55','2016-05-22 22:46:55','8','Blauw',0),
	(1288,'2016-05-22 22:38:38','2016-05-22 22:46:55','2016-05-22 22:46:55','8','Bruin/Rood',0),
	(1289,'2016-05-22 22:38:38','2016-05-22 22:46:55','2016-05-22 22:46:55','8','Oranje/Rood',0),
	(1290,'2016-05-22 22:38:38','2016-05-22 22:46:55','2016-05-22 22:46:55','8','Geel',0),
	(1291,'2016-05-22 22:38:38','2016-05-22 22:46:55','2016-05-22 22:46:55','9','plusminus 30.000',1),
	(1292,'2016-05-22 22:38:38','2016-05-22 22:46:55','2016-05-22 22:46:55','9','minder dan 3000',0),
	(1293,'2016-05-22 22:38:38','2016-05-22 22:46:55','2016-05-22 22:46:55','9','plusminus 3500',0),
	(1294,'2016-05-22 22:38:38','2016-05-22 22:46:55','2016-05-22 22:46:55','9','plusminus 5500',0),
	(1295,'2016-05-22 22:38:38','2016-05-22 22:46:55','2016-05-22 22:46:55','9','plusminus 9000',0),
	(1296,'2016-05-22 22:38:38','2016-05-22 22:46:55','2016-05-22 22:46:55','10','Blauw',1),
	(1297,'2016-05-22 22:38:38','2016-05-22 22:46:55','2016-05-22 22:46:55','10','Bruin/Rood',0),
	(1298,'2016-05-22 22:38:38','2016-05-22 22:46:55','2016-05-22 22:46:55','10','Oranje/Rood',0),
	(1299,'2016-05-22 22:38:38','2016-05-22 22:46:55','2016-05-22 22:46:55','10','Geel',0),
	(1300,'2016-05-22 22:38:38','2016-05-22 22:46:55','2016-05-22 22:46:55','10','Wit',0),
	(1301,'2016-05-22 22:46:55','2016-05-22 22:50:45','2016-05-22 22:50:45','1','Bruine dwerg',1),
	(1302,'2016-05-22 22:46:55','2016-05-22 22:50:45','2016-05-22 22:50:45','1','Witte dwerg',0),
	(1303,'2016-05-22 22:46:55','2016-05-22 22:50:45','2016-05-22 22:50:45','1','Neutronenster',0),
	(1304,'2016-05-22 22:46:55','2016-05-22 22:50:45','2016-05-22 22:50:45','1','Zwart gat',0),
	(1305,'2016-05-22 22:46:55','2016-05-22 22:50:45','2016-05-22 22:50:45','2','minder dan 3000',1),
	(1306,'2016-05-22 22:46:55','2016-05-22 22:50:45','2016-05-22 22:50:45','2','plusminus 3500',0),
	(1307,'2016-05-22 22:46:55','2016-05-22 22:50:45','2016-05-22 22:50:45','2','plusminus 5500',0),
	(1308,'2016-05-22 22:46:55','2016-05-22 22:50:45','2016-05-22 22:50:45','2','plusminus 9000',0),
	(1309,'2016-05-22 22:46:55','2016-05-22 22:50:45','2016-05-22 22:50:45','2','plusminus 30.000',0),
	(1310,'2016-05-22 22:46:55','2016-05-22 22:50:45','2016-05-22 22:50:45','3','Bruin/Rood<br>',1),
	(1311,'2016-05-22 22:46:55','2016-05-22 22:50:45','2016-05-22 22:50:45','3','Oranje/Rood',0),
	(1312,'2016-05-22 22:46:55','2016-05-22 22:50:45','2016-05-22 22:50:45','3','Geel',0),
	(1313,'2016-05-22 22:46:55','2016-05-22 22:50:45','2016-05-22 22:50:45','3','Wit',0),
	(1314,'2016-05-22 22:46:56','2016-05-22 22:50:45','2016-05-22 22:50:45','3','Blauw',0),
	(1315,'2016-05-22 22:46:56','2016-05-22 22:50:45','2016-05-22 22:50:45','4','nee',1),
	(1316,'2016-05-22 22:46:56','2016-05-22 22:50:45','2016-05-22 22:50:45','4','H --&gt; He',0),
	(1317,'2016-05-22 22:46:56','2016-05-22 22:50:45','2016-05-22 22:50:45','4','H --&gt; --&gt; C',0),
	(1318,'2016-05-22 22:46:56','2016-05-22 22:50:45','2016-05-22 22:50:45','4','H --&gt; --&gt; Fe',0),
	(1319,'2016-05-22 22:46:56','2016-05-22 22:50:45','2016-05-22 22:50:45','5','1 biljoen',1),
	(1320,'2016-05-22 22:46:56','2016-05-22 22:50:45','2016-05-22 22:50:45','5','100 miljard',0),
	(1321,'2016-05-22 22:46:56','2016-05-22 22:50:45','2016-05-22 22:50:45','5','10 miljard',0),
	(1322,'2016-05-22 22:46:56','2016-05-22 22:50:45','2016-05-22 22:50:45','5','100 miljoen',0),
	(1323,'2016-05-22 22:46:56','2016-05-22 22:50:45','2016-05-22 22:50:45','5','10 miljoen',0),
	(1324,'2016-05-22 22:46:56','2016-05-22 22:50:45','2016-05-22 22:50:45','6','geen',1),
	(1325,'2016-05-22 22:46:56','2016-05-22 22:50:45','2016-05-22 22:50:45','6','Rode Reus',0),
	(1326,'2016-05-22 22:46:56','2016-05-22 22:50:45','2016-05-22 22:50:45','6','Super Reus',0),
	(1327,'2016-05-22 22:46:56','2016-05-22 22:50:45','2016-05-22 22:50:45','7','plusminus 3500',1),
	(1328,'2016-05-22 22:46:56','2016-05-22 22:50:45','2016-05-22 22:50:45','7','plusminus 5500',0),
	(1329,'2016-05-22 22:46:56','2016-05-22 22:50:45','2016-05-22 22:50:45','7','plusminus 9000',0),
	(1330,'2016-05-22 22:46:56','2016-05-22 22:50:45','2016-05-22 22:50:45','7','plusminus 30.000',0),
	(1331,'2016-05-22 22:46:56','2016-05-22 22:50:45','2016-05-22 22:50:45','7','minder dan 3000',0),
	(1332,'2016-05-22 22:46:56','2016-05-22 22:50:45','2016-05-22 22:50:45','8','Oranje/Rood\r\n		</p>\r\n		<p>',1),
	(1333,'2016-05-22 22:46:56','2016-05-22 22:50:45','2016-05-22 22:50:45','8','Geel',0),
	(1334,'2016-05-22 22:46:56','2016-05-22 22:50:45','2016-05-22 22:50:45','8','Wit',0),
	(1335,'2016-05-22 22:46:56','2016-05-22 22:50:45','2016-05-22 22:50:45','8','Blauw',0),
	(1336,'2016-05-22 22:46:56','2016-05-22 22:50:45','2016-05-22 22:50:45','8','Bruin/Rood',0),
	(1337,'2016-05-22 22:46:57','2016-05-22 22:50:45','2016-05-22 22:50:45','9','H --&gt; He',1),
	(1338,'2016-05-22 22:46:57','2016-05-22 22:50:45','2016-05-22 22:50:45','9','H --&gt; --&gt; C',0),
	(1339,'2016-05-22 22:46:57','2016-05-22 22:50:45','2016-05-22 22:50:45','9','H --&gt; --&gt; Fe',0),
	(1340,'2016-05-22 22:46:57','2016-05-22 22:50:45','2016-05-22 22:50:45','9','nee',0),
	(1341,'2016-05-22 22:46:57','2016-05-22 22:50:45','2016-05-22 22:50:45','10','100 miljard',1),
	(1342,'2016-05-22 22:46:57','2016-05-22 22:50:45','2016-05-22 22:50:45','10','10 miljard',0),
	(1343,'2016-05-22 22:46:57','2016-05-22 22:50:45','2016-05-22 22:50:45','10','100 miljoen',0),
	(1344,'2016-05-22 22:46:57','2016-05-22 22:50:45','2016-05-22 22:50:45','10','10 miljoen',0),
	(1345,'2016-05-22 22:46:57','2016-05-22 22:50:45','2016-05-22 22:50:45','10','1 biljoen',0),
	(1346,'2016-05-22 22:46:57','2016-05-22 22:50:45','2016-05-22 22:50:45','11','geen',1),
	(1347,'2016-05-22 22:46:57','2016-05-22 22:50:45','2016-05-22 22:50:45','11','Rode Reus',0),
	(1348,'2016-05-22 22:46:57','2016-05-22 22:50:45','2016-05-22 22:50:45','11','Super Reus',0),
	(1349,'2016-05-22 22:46:57','2016-05-22 22:50:45','2016-05-22 22:50:45','12','plusminus 5500',1),
	(1350,'2016-05-22 22:46:57','2016-05-22 22:50:45','2016-05-22 22:50:45','12','plusminus 9000',0),
	(1351,'2016-05-22 22:46:57','2016-05-22 22:50:45','2016-05-22 22:50:45','12','plusminus 30.000',0),
	(1352,'2016-05-22 22:46:57','2016-05-22 22:50:45','2016-05-22 22:50:45','12','minder dan 3000',0),
	(1353,'2016-05-22 22:46:57','2016-05-22 22:50:45','2016-05-22 22:50:45','12','plusminus 3500',0),
	(1354,'2016-05-22 22:46:57','2016-05-22 22:50:45','2016-05-22 22:50:45','13','Geel',1),
	(1355,'2016-05-22 22:46:57','2016-05-22 22:50:45','2016-05-22 22:50:45','13','Wit',0),
	(1356,'2016-05-22 22:46:57','2016-05-22 22:50:45','2016-05-22 22:50:45','13','Blauw\r\n		</p>\r\n		<p>',0),
	(1357,'2016-05-22 22:46:57','2016-05-22 22:50:45','2016-05-22 22:50:45','13','Bruin/Rood',0),
	(1358,'2016-05-22 22:46:57','2016-05-22 22:50:45','2016-05-22 22:50:45','13','Oranje/Rood',0),
	(1359,'2016-05-22 22:46:57','2016-05-22 22:50:45','2016-05-22 22:50:45','14','H --&gt; --&gt; C',1),
	(1360,'2016-05-22 22:46:58','2016-05-22 22:50:45','2016-05-22 22:50:45','14','H --&gt; --&gt; Fe',0),
	(1361,'2016-05-22 22:46:58','2016-05-22 22:50:45','2016-05-22 22:50:45','14','nee',0),
	(1362,'2016-05-22 22:46:58','2016-05-22 22:50:45','2016-05-22 22:50:45','14','H --&gt; He',0),
	(1363,'2016-05-22 22:46:58','2016-05-22 22:50:45','2016-05-22 22:50:45','15','10 miljard',1),
	(1364,'2016-05-22 22:46:58','2016-05-22 22:50:45','2016-05-22 22:50:45','15','100 miljoen',0),
	(1365,'2016-05-22 22:46:58','2016-05-22 22:50:45','2016-05-22 22:50:45','15','10 miljoen',0),
	(1366,'2016-05-22 22:46:58','2016-05-22 22:50:45','2016-05-22 22:50:45','15','1 biljoen',0),
	(1367,'2016-05-22 22:46:58','2016-05-22 22:50:45','2016-05-22 22:50:45','15','100 miljard',0),
	(1368,'2016-05-22 22:46:58','2016-05-22 22:50:45','2016-05-22 22:50:45','16','Rode Reus',1),
	(1369,'2016-05-22 22:46:58','2016-05-22 22:50:45','2016-05-22 22:50:45','16','Super Reus',0),
	(1370,'2016-05-22 22:46:58','2016-05-22 22:50:45','2016-05-22 22:50:45','16','geen',0),
	(1371,'2016-05-22 22:46:58','2016-05-22 22:50:45','2016-05-22 22:50:45','17','plusminus 9000',1),
	(1372,'2016-05-22 22:46:58','2016-05-22 22:50:45','2016-05-22 22:50:45','17','plusminus 30.000',0),
	(1373,'2016-05-22 22:46:58','2016-05-22 22:50:45','2016-05-22 22:50:45','17','minder dan 3000',0),
	(1374,'2016-05-22 22:46:58','2016-05-22 22:50:45','2016-05-22 22:50:45','17','plusminus 3500',0),
	(1375,'2016-05-22 22:46:58','2016-05-22 22:50:45','2016-05-22 22:50:45','17','plusminus 5500',0),
	(1376,'2016-05-22 22:46:58','2016-05-22 22:50:45','2016-05-22 22:50:45','18','Wit',1),
	(1377,'2016-05-22 22:46:58','2016-05-22 22:50:45','2016-05-22 22:50:45','18','Blauw',0),
	(1378,'2016-05-22 22:46:59','2016-05-22 22:50:45','2016-05-22 22:50:45','18','Bruin/Rood\r\n		</p>\r\n		<p>',0),
	(1379,'2016-05-22 22:46:59','2016-05-22 22:50:45','2016-05-22 22:50:45','18','Oranje/Rood',0),
	(1380,'2016-05-22 22:46:59','2016-05-22 22:50:45','2016-05-22 22:50:45','18','Geel',0),
	(1381,'2016-05-22 22:46:59','2016-05-22 22:50:45','2016-05-22 22:50:45','19','H --&gt; --&gt; Fe',1),
	(1382,'2016-05-22 22:46:59','2016-05-22 22:50:45','2016-05-22 22:50:45','19','nee',0),
	(1383,'2016-05-22 22:46:59','2016-05-22 22:50:45','2016-05-22 22:50:45','19','H --&gt; He',0),
	(1384,'2016-05-22 22:46:59','2016-05-22 22:50:45','2016-05-22 22:50:45','19','H --&gt; --&gt; C',0),
	(1385,'2016-05-22 22:46:59','2016-05-22 22:50:45','2016-05-22 22:50:45','20','100 miljoen',1),
	(1386,'2016-05-22 22:46:59','2016-05-22 22:50:45','2016-05-22 22:50:45','20','10 miljoen',0),
	(1387,'2016-05-22 22:46:59','2016-05-22 22:50:45','2016-05-22 22:50:45','20','1 biljoen',0),
	(1388,'2016-05-22 22:46:59','2016-05-22 22:50:45','2016-05-22 22:50:45','20','100 miljard',0),
	(1389,'2016-05-22 22:46:59','2016-05-22 22:50:45','2016-05-22 22:50:45','20','10 miljard',0),
	(1390,'2016-05-22 22:46:59','2016-05-22 22:50:45','2016-05-22 22:50:45','21','Super Reus',1),
	(1391,'2016-05-22 22:46:59','2016-05-22 22:50:45','2016-05-22 22:50:45','21','geen',0),
	(1392,'2016-05-22 22:46:59','2016-05-22 22:50:45','2016-05-22 22:50:45','21','Rode Reus',0),
	(1393,'2016-05-22 22:46:59','2016-05-22 22:50:45','2016-05-22 22:50:45','22','plusminus 30.000',1),
	(1394,'2016-05-22 22:46:59','2016-05-22 22:50:45','2016-05-22 22:50:45','22','minder dan 3000',0),
	(1395,'2016-05-22 22:46:59','2016-05-22 22:50:45','2016-05-22 22:50:45','22','plusminus 3500',0),
	(1396,'2016-05-22 22:46:59','2016-05-22 22:50:45','2016-05-22 22:50:45','22','plusminus 5500',0),
	(1397,'2016-05-22 22:46:59','2016-05-22 22:50:45','2016-05-22 22:50:45','22','plusminus 9000',0),
	(1398,'2016-05-22 22:46:59','2016-05-22 22:50:45','2016-05-22 22:50:45','23','Blauw',1),
	(1399,'2016-05-22 22:46:59','2016-05-22 22:50:45','2016-05-22 22:50:45','23','Bruin/Rood\r\n		</p>\r\n		<p>',0),
	(1400,'2016-05-22 22:47:00','2016-05-22 22:50:45','2016-05-22 22:50:45','23','Oranje/Rood',0),
	(1401,'2016-05-22 22:47:00','2016-05-22 22:50:45','2016-05-22 22:50:45','23','Geel',0),
	(1402,'2016-05-22 22:47:00','2016-05-22 22:50:45','2016-05-22 22:50:45','23','Wit',0),
	(1403,'2016-05-22 22:47:00','2016-05-22 22:50:45','2016-05-22 22:50:45','24','H --&gt; --&gt; Fe',1),
	(1404,'2016-05-22 22:47:00','2016-05-22 22:50:45','2016-05-22 22:50:45','24','nee',0),
	(1405,'2016-05-22 22:47:00','2016-05-22 22:50:45','2016-05-22 22:50:45','24','H --&gt; He',0),
	(1406,'2016-05-22 22:47:00','2016-05-22 22:50:45','2016-05-22 22:50:45','24','H --&gt; --&gt; C',0),
	(1407,'2016-05-22 22:47:00','2016-05-22 22:50:45','2016-05-22 22:50:45','25','10 miljoen',1),
	(1408,'2016-05-22 22:47:00','2016-05-22 22:50:45','2016-05-22 22:50:45','25','1 biljoen',0),
	(1409,'2016-05-22 22:47:00','2016-05-22 22:50:45','2016-05-22 22:50:45','25','100 miljard',0),
	(1410,'2016-05-22 22:47:00','2016-05-22 22:50:45','2016-05-22 22:50:45','25','10 miljard',0),
	(1411,'2016-05-22 22:47:00','2016-05-22 22:50:45','2016-05-22 22:50:45','25','100 miljoen',0),
	(1412,'2016-05-22 22:47:00','2016-05-22 22:50:45','2016-05-22 22:50:45','26','Super Reus',1),
	(1413,'2016-05-22 22:47:00','2016-05-22 22:50:45','2016-05-22 22:50:45','26','geen',0),
	(1414,'2016-05-22 22:47:00','2016-05-22 22:50:45','2016-05-22 22:50:45','26','Rode Reus',0),
	(1415,'2016-05-22 22:50:45','2016-05-22 22:53:26','2016-05-22 22:53:26','1','Bruine dwerg',1),
	(1416,'2016-05-22 22:50:45','2016-05-22 22:53:26','2016-05-22 22:53:26','1','Witte dwerg',0),
	(1417,'2016-05-22 22:50:46','2016-05-22 22:53:26','2016-05-22 22:53:26','1','Neutronenster',0),
	(1418,'2016-05-22 22:50:46','2016-05-22 22:53:26','2016-05-22 22:53:26','1','Zwart gat',0),
	(1419,'2016-05-22 22:50:46','2016-05-22 22:53:26','2016-05-22 22:53:26','2','minder dan 3000',1),
	(1420,'2016-05-22 22:50:46','2016-05-22 22:53:26','2016-05-22 22:53:26','2','plusminus 3500',0),
	(1421,'2016-05-22 22:50:46','2016-05-22 22:53:26','2016-05-22 22:53:26','2','plusminus 5500',0),
	(1422,'2016-05-22 22:50:46','2016-05-22 22:53:26','2016-05-22 22:53:26','2','plusminus 9000',0),
	(1423,'2016-05-22 22:50:46','2016-05-22 22:53:26','2016-05-22 22:53:26','2','plusminus 30.000',0),
	(1424,'2016-05-22 22:50:46','2016-05-22 22:53:26','2016-05-22 22:53:26','3','Bruin/Rood<br>',1),
	(1425,'2016-05-22 22:50:46','2016-05-22 22:53:26','2016-05-22 22:53:26','3','Oranje/Rood',0),
	(1426,'2016-05-22 22:50:46','2016-05-22 22:53:26','2016-05-22 22:53:26','3','Geel',0),
	(1427,'2016-05-22 22:50:46','2016-05-22 22:53:26','2016-05-22 22:53:26','3','Wit',0),
	(1428,'2016-05-22 22:50:46','2016-05-22 22:53:26','2016-05-22 22:53:26','3','Blauw',0),
	(1429,'2016-05-22 22:50:46','2016-05-22 22:53:26','2016-05-22 22:53:26','4','nee',1),
	(1430,'2016-05-22 22:50:46','2016-05-22 22:53:26','2016-05-22 22:53:26','4','van H tot He',0),
	(1431,'2016-05-22 22:50:46','2016-05-22 22:53:26','2016-05-22 22:53:26','4','H tot C',0),
	(1432,'2016-05-22 22:50:46','2016-05-22 22:53:26','2016-05-22 22:53:26','4','H tot Fe',0),
	(1433,'2016-05-22 22:50:46','2016-05-22 22:53:26','2016-05-22 22:53:26','5','1 biljoen',1),
	(1434,'2016-05-22 22:50:46','2016-05-22 22:53:26','2016-05-22 22:53:26','5','100 miljard',0),
	(1435,'2016-05-22 22:50:46','2016-05-22 22:53:26','2016-05-22 22:53:26','5','10 miljard',0),
	(1436,'2016-05-22 22:50:46','2016-05-22 22:53:26','2016-05-22 22:53:26','5','100 miljoen',0),
	(1437,'2016-05-22 22:50:46','2016-05-22 22:53:26','2016-05-22 22:53:26','5','10 miljoen',0),
	(1438,'2016-05-22 22:50:46','2016-05-22 22:53:26','2016-05-22 22:53:26','6','geen',1),
	(1439,'2016-05-22 22:50:46','2016-05-22 22:53:26','2016-05-22 22:53:26','6','Rode Reus',0),
	(1440,'2016-05-22 22:50:46','2016-05-22 22:53:26','2016-05-22 22:53:26','6','Super Reus',0),
	(1441,'2016-05-22 22:50:47','2016-05-22 22:53:26','2016-05-22 22:53:26','7','plusminus 3500',1),
	(1442,'2016-05-22 22:50:47','2016-05-22 22:53:26','2016-05-22 22:53:26','7','plusminus 5500',0),
	(1443,'2016-05-22 22:50:47','2016-05-22 22:53:26','2016-05-22 22:53:26','7','plusminus 9000',0),
	(1444,'2016-05-22 22:50:47','2016-05-22 22:53:26','2016-05-22 22:53:26','7','plusminus 30.000',0),
	(1445,'2016-05-22 22:50:47','2016-05-22 22:53:26','2016-05-22 22:53:26','7','minder dan 3000',0),
	(1446,'2016-05-22 22:50:47','2016-05-22 22:53:26','2016-05-22 22:53:26','8','Oranje/Rood\r\n		</p>\r\n		<p>',1),
	(1447,'2016-05-22 22:50:47','2016-05-22 22:53:26','2016-05-22 22:53:26','8','Geel',0),
	(1448,'2016-05-22 22:50:47','2016-05-22 22:53:26','2016-05-22 22:53:26','8','Wit',0),
	(1449,'2016-05-22 22:50:47','2016-05-22 22:53:26','2016-05-22 22:53:26','8','Blauw',0),
	(1450,'2016-05-22 22:50:47','2016-05-22 22:53:26','2016-05-22 22:53:26','8','Bruin/Rood',0),
	(1451,'2016-05-22 22:50:47','2016-05-22 22:53:26','2016-05-22 22:53:26','9','H tot He',1),
	(1452,'2016-05-22 22:50:47','2016-05-22 22:53:26','2016-05-22 22:53:26','9','H tot C',0),
	(1453,'2016-05-22 22:50:47','2016-05-22 22:53:26','2016-05-22 22:53:26','9','H tot Fe',0),
	(1454,'2016-05-22 22:50:47','2016-05-22 22:53:26','2016-05-22 22:53:26','9','nee',0),
	(1455,'2016-05-22 22:50:47','2016-05-22 22:53:26','2016-05-22 22:53:26','10','100 miljard',1),
	(1456,'2016-05-22 22:50:47','2016-05-22 22:53:26','2016-05-22 22:53:26','10','10 miljard',0),
	(1457,'2016-05-22 22:50:47','2016-05-22 22:53:26','2016-05-22 22:53:26','10','100 miljoen',0),
	(1458,'2016-05-22 22:50:47','2016-05-22 22:53:26','2016-05-22 22:53:26','10','10 miljoen',0),
	(1459,'2016-05-22 22:50:47','2016-05-22 22:53:26','2016-05-22 22:53:26','10','1 biljoen',0),
	(1460,'2016-05-22 22:50:47','2016-05-22 22:53:26','2016-05-22 22:53:26','11','geen',1),
	(1461,'2016-05-22 22:50:47','2016-05-22 22:53:26','2016-05-22 22:53:26','11','Rode Reus',0),
	(1462,'2016-05-22 22:50:48','2016-05-22 22:53:26','2016-05-22 22:53:26','11','Super Reus',0),
	(1463,'2016-05-22 22:50:48','2016-05-22 22:53:26','2016-05-22 22:53:26','12','plusminus 5500',1),
	(1464,'2016-05-22 22:50:48','2016-05-22 22:53:26','2016-05-22 22:53:26','12','plusminus 9000',0),
	(1465,'2016-05-22 22:50:48','2016-05-22 22:53:26','2016-05-22 22:53:26','12','plusminus 30.000',0),
	(1466,'2016-05-22 22:50:48','2016-05-22 22:53:26','2016-05-22 22:53:26','12','minder dan 3000',0),
	(1467,'2016-05-22 22:50:48','2016-05-22 22:53:26','2016-05-22 22:53:26','12','plusminus 3500',0),
	(1468,'2016-05-22 22:50:48','2016-05-22 22:53:26','2016-05-22 22:53:26','13','Geel',1),
	(1469,'2016-05-22 22:50:48','2016-05-22 22:53:26','2016-05-22 22:53:26','13','Wit',0),
	(1470,'2016-05-22 22:50:48','2016-05-22 22:53:26','2016-05-22 22:53:26','13','Blauw\r\n		</p>\r\n		<p>',0),
	(1471,'2016-05-22 22:50:48','2016-05-22 22:53:26','2016-05-22 22:53:26','13','Bruin/Rood',0),
	(1472,'2016-05-22 22:50:48','2016-05-22 22:53:26','2016-05-22 22:53:26','13','Oranje/Rood',0),
	(1473,'2016-05-22 22:50:48','2016-05-22 22:53:26','2016-05-22 22:53:26','14','H tot C',1),
	(1474,'2016-05-22 22:50:48','2016-05-22 22:53:26','2016-05-22 22:53:26','14','H tot Fe',0),
	(1475,'2016-05-22 22:50:48','2016-05-22 22:53:26','2016-05-22 22:53:26','14','nee',0),
	(1476,'2016-05-22 22:50:48','2016-05-22 22:53:26','2016-05-22 22:53:26','14','H tot He',0),
	(1477,'2016-05-22 22:50:48','2016-05-22 22:53:26','2016-05-22 22:53:26','15','10 miljard',1),
	(1478,'2016-05-22 22:50:48','2016-05-22 22:53:26','2016-05-22 22:53:26','15','100 miljoen',0),
	(1479,'2016-05-22 22:50:48','2016-05-22 22:53:26','2016-05-22 22:53:26','15','10 miljoen',0),
	(1480,'2016-05-22 22:50:48','2016-05-22 22:53:26','2016-05-22 22:53:26','15','1 biljoen',0),
	(1481,'2016-05-22 22:50:48','2016-05-22 22:53:26','2016-05-22 22:53:26','15','100 miljard',0),
	(1482,'2016-05-22 22:50:48','2016-05-22 22:53:26','2016-05-22 22:53:26','16','Rode Reus',1),
	(1483,'2016-05-22 22:50:48','2016-05-22 22:53:26','2016-05-22 22:53:26','16','Super Reus',0),
	(1484,'2016-05-22 22:50:49','2016-05-22 22:53:26','2016-05-22 22:53:26','16','geen',0),
	(1485,'2016-05-22 22:50:49','2016-05-22 22:53:26','2016-05-22 22:53:26','17','plusminus 9000',1),
	(1486,'2016-05-22 22:50:49','2016-05-22 22:53:26','2016-05-22 22:53:26','17','plusminus 30.000',0),
	(1487,'2016-05-22 22:50:49','2016-05-22 22:53:26','2016-05-22 22:53:26','17','minder dan 3000',0),
	(1488,'2016-05-22 22:50:49','2016-05-22 22:53:26','2016-05-22 22:53:26','17','plusminus 3500',0),
	(1489,'2016-05-22 22:50:49','2016-05-22 22:53:26','2016-05-22 22:53:26','17','plusminus 5500',0),
	(1490,'2016-05-22 22:50:49','2016-05-22 22:53:26','2016-05-22 22:53:26','18','Wit',1),
	(1491,'2016-05-22 22:50:49','2016-05-22 22:53:26','2016-05-22 22:53:26','18','Blauw',0),
	(1492,'2016-05-22 22:50:49','2016-05-22 22:53:26','2016-05-22 22:53:26','18','Bruin/Rood\r\n		</p>\r\n		<p>',0),
	(1493,'2016-05-22 22:50:49','2016-05-22 22:53:26','2016-05-22 22:53:26','18','Oranje/Rood',0),
	(1494,'2016-05-22 22:50:49','2016-05-22 22:53:26','2016-05-22 22:53:26','18','Geel',0),
	(1495,'2016-05-22 22:50:49','2016-05-22 22:53:26','2016-05-22 22:53:26','19','H tot Fe',1),
	(1496,'2016-05-22 22:50:49','2016-05-22 22:53:26','2016-05-22 22:53:26','19','nee',0),
	(1497,'2016-05-22 22:50:49','2016-05-22 22:53:26','2016-05-22 22:53:26','19','H tot He',0),
	(1498,'2016-05-22 22:50:49','2016-05-22 22:53:26','2016-05-22 22:53:26','19','H tot C',0),
	(1499,'2016-05-22 22:50:49','2016-05-22 22:53:26','2016-05-22 22:53:26','20','100 miljoen',1),
	(1500,'2016-05-22 22:50:49','2016-05-22 22:53:26','2016-05-22 22:53:26','20','10 miljoen',0),
	(1501,'2016-05-22 22:50:49','2016-05-22 22:53:26','2016-05-22 22:53:26','20','1 biljoen',0),
	(1502,'2016-05-22 22:50:49','2016-05-22 22:53:26','2016-05-22 22:53:26','20','100 miljard',0),
	(1503,'2016-05-22 22:50:49','2016-05-22 22:53:26','2016-05-22 22:53:26','20','10 miljard',0),
	(1504,'2016-05-22 22:50:49','2016-05-22 22:53:26','2016-05-22 22:53:26','21','Super Reus',1),
	(1505,'2016-05-22 22:50:49','2016-05-22 22:53:26','2016-05-22 22:53:26','21','geen',0),
	(1506,'2016-05-22 22:50:50','2016-05-22 22:53:26','2016-05-22 22:53:26','21','Rode Reus',0),
	(1507,'2016-05-22 22:50:50','2016-05-22 22:53:26','2016-05-22 22:53:26','22','plusminus 30.000',1),
	(1508,'2016-05-22 22:50:50','2016-05-22 22:53:26','2016-05-22 22:53:26','22','minder dan 3000',0),
	(1509,'2016-05-22 22:50:50','2016-05-22 22:53:26','2016-05-22 22:53:26','22','plusminus 3500',0),
	(1510,'2016-05-22 22:50:50','2016-05-22 22:53:26','2016-05-22 22:53:26','22','plusminus 5500',0),
	(1511,'2016-05-22 22:50:50','2016-05-22 22:53:26','2016-05-22 22:53:26','22','plusminus 9000',0),
	(1512,'2016-05-22 22:50:50','2016-05-22 22:53:26','2016-05-22 22:53:26','23','Blauw',1),
	(1513,'2016-05-22 22:50:50','2016-05-22 22:53:26','2016-05-22 22:53:26','23','Bruin/Rood\r\n		</p>\r\n		<p>',0),
	(1514,'2016-05-22 22:50:50','2016-05-22 22:53:26','2016-05-22 22:53:26','23','Oranje/Rood',0),
	(1515,'2016-05-22 22:50:50','2016-05-22 22:53:26','2016-05-22 22:53:26','23','Geel',0),
	(1516,'2016-05-22 22:50:50','2016-05-22 22:53:26','2016-05-22 22:53:26','23','Wit',0),
	(1517,'2016-05-22 22:50:50','2016-05-22 22:53:26','2016-05-22 22:53:26','24','H tot Fe',1),
	(1518,'2016-05-22 22:50:50','2016-05-22 22:53:26','2016-05-22 22:53:26','24','nee',0),
	(1519,'2016-05-22 22:50:50','2016-05-22 22:53:26','2016-05-22 22:53:26','24','H tot He',0),
	(1520,'2016-05-22 22:50:50','2016-05-22 22:53:26','2016-05-22 22:53:26','24','H tot C',0),
	(1521,'2016-05-22 22:50:50','2016-05-22 22:53:26','2016-05-22 22:53:26','25','10 miljoen',1),
	(1522,'2016-05-22 22:50:50','2016-05-22 22:53:26','2016-05-22 22:53:26','25','1 biljoen',0),
	(1523,'2016-05-22 22:50:50','2016-05-22 22:53:26','2016-05-22 22:53:26','25','100 miljard',0),
	(1524,'2016-05-22 22:50:50','2016-05-22 22:53:26','2016-05-22 22:53:26','25','10 miljard',0),
	(1525,'2016-05-22 22:50:51','2016-05-22 22:53:26','2016-05-22 22:53:26','25','100 miljoen',0),
	(1526,'2016-05-22 22:50:51','2016-05-22 22:53:26','2016-05-22 22:53:26','26','Super Reus',1),
	(1527,'2016-05-22 22:50:51','2016-05-22 22:53:26','2016-05-22 22:53:26','26','geen',0),
	(1528,'2016-05-22 22:50:51','2016-05-22 22:53:26','2016-05-22 22:53:26','26','Rode Reus',0),
	(1529,'2016-05-22 22:53:26','2016-05-22 22:55:30','2016-05-22 22:55:30','1','Bruine dwerg',1),
	(1530,'2016-05-22 22:53:26','2016-05-22 22:55:30','2016-05-22 22:55:30','1','Witte dwerg',0),
	(1531,'2016-05-22 22:53:26','2016-05-22 22:55:30','2016-05-22 22:55:30','1','Neutronenster',0),
	(1532,'2016-05-22 22:53:26','2016-05-22 22:55:30','2016-05-22 22:55:30','1','Zwart gat',0),
	(1533,'2016-05-22 22:53:26','2016-05-22 22:55:30','2016-05-22 22:55:30','2','minder dan 3000',1),
	(1534,'2016-05-22 22:53:26','2016-05-22 22:55:30','2016-05-22 22:55:30','2','plusminus 3500',0),
	(1535,'2016-05-22 22:53:26','2016-05-22 22:55:30','2016-05-22 22:55:30','2','plusminus 5500',0),
	(1536,'2016-05-22 22:53:26','2016-05-22 22:55:30','2016-05-22 22:55:30','2','plusminus 9000',0),
	(1537,'2016-05-22 22:53:26','2016-05-22 22:55:30','2016-05-22 22:55:30','2','plusminus 30.000',0),
	(1538,'2016-05-22 22:53:26','2016-05-22 22:55:30','2016-05-22 22:55:30','3','Bruin/Rood<br>',1),
	(1539,'2016-05-22 22:53:26','2016-05-22 22:55:30','2016-05-22 22:55:30','3','Oranje/Rood',0),
	(1540,'2016-05-22 22:53:27','2016-05-22 22:55:30','2016-05-22 22:55:30','3','Geel',0),
	(1541,'2016-05-22 22:53:27','2016-05-22 22:55:30','2016-05-22 22:55:30','3','Wit',0),
	(1542,'2016-05-22 22:53:27','2016-05-22 22:55:30','2016-05-22 22:55:30','3','Blauw',0),
	(1543,'2016-05-22 22:53:27','2016-05-22 22:55:30','2016-05-22 22:55:30','4','nee',1),
	(1544,'2016-05-22 22:53:27','2016-05-22 22:55:30','2016-05-22 22:55:30','4','van H tot He',0),
	(1545,'2016-05-22 22:53:27','2016-05-22 22:55:30','2016-05-22 22:55:30','4','H tot C',0),
	(1546,'2016-05-22 22:53:27','2016-05-22 22:55:30','2016-05-22 22:55:30','4','H tot Fe',0),
	(1547,'2016-05-22 22:53:27','2016-05-22 22:55:30','2016-05-22 22:55:30','5','1 biljoen',1),
	(1548,'2016-05-22 22:53:27','2016-05-22 22:55:30','2016-05-22 22:55:30','5','100 miljard',0),
	(1549,'2016-05-22 22:53:27','2016-05-22 22:55:30','2016-05-22 22:55:30','5','10 miljard',0),
	(1550,'2016-05-22 22:53:27','2016-05-22 22:55:30','2016-05-22 22:55:30','5','100 miljoen',0),
	(1551,'2016-05-22 22:53:27','2016-05-22 22:55:30','2016-05-22 22:55:30','5','10 miljoen',0),
	(1552,'2016-05-22 22:53:27','2016-05-22 22:55:30','2016-05-22 22:55:30','6','geen',1),
	(1553,'2016-05-22 22:53:27','2016-05-22 22:55:30','2016-05-22 22:55:30','6','Rode Reus',0),
	(1554,'2016-05-22 22:53:27','2016-05-22 22:55:30','2016-05-22 22:55:30','6','Super Reus',0),
	(1555,'2016-05-22 22:53:27','2016-05-22 22:55:30','2016-05-22 22:55:30','7','Bruine dwerg',1),
	(1556,'2016-05-22 22:53:27','2016-05-22 22:55:30','2016-05-22 22:55:30','7','Witte dwerg',0),
	(1557,'2016-05-22 22:53:27','2016-05-22 22:55:30','2016-05-22 22:55:30','7','Neutronenster',0),
	(1558,'2016-05-22 22:53:27','2016-05-22 22:55:30','2016-05-22 22:55:30','7',' Zwart gat',0),
	(1559,'2016-05-22 22:53:27','2016-05-22 22:55:30','2016-05-22 22:55:30','8','plusminus 3500',1),
	(1560,'2016-05-22 22:53:27','2016-05-22 22:55:30','2016-05-22 22:55:30','8','plusminus 5500',0),
	(1561,'2016-05-22 22:53:27','2016-05-22 22:55:30','2016-05-22 22:55:30','8','plusminus 9000',0),
	(1562,'2016-05-22 22:53:27','2016-05-22 22:55:30','2016-05-22 22:55:30','8','plusminus 30.000',0),
	(1563,'2016-05-22 22:53:28','2016-05-22 22:55:30','2016-05-22 22:55:30','8','minder dan 3000',0),
	(1564,'2016-05-22 22:53:28','2016-05-22 22:55:30','2016-05-22 22:55:30','9','Oranje/Rood\r\n		</p>\r\n		<p>',1),
	(1565,'2016-05-22 22:53:28','2016-05-22 22:55:30','2016-05-22 22:55:30','9','Geel',0),
	(1566,'2016-05-22 22:53:28','2016-05-22 22:55:30','2016-05-22 22:55:30','9','Wit',0),
	(1567,'2016-05-22 22:53:28','2016-05-22 22:55:30','2016-05-22 22:55:30','9','Blauw',0),
	(1568,'2016-05-22 22:53:28','2016-05-22 22:55:30','2016-05-22 22:55:30','9','Bruin/Rood',0),
	(1569,'2016-05-22 22:53:28','2016-05-22 22:55:30','2016-05-22 22:55:30','10','H tot He',1),
	(1570,'2016-05-22 22:53:28','2016-05-22 22:55:30','2016-05-22 22:55:30','10','H tot C',0),
	(1571,'2016-05-22 22:53:28','2016-05-22 22:55:30','2016-05-22 22:55:30','10','H tot Fe',0),
	(1572,'2016-05-22 22:53:28','2016-05-22 22:55:30','2016-05-22 22:55:30','10','nee',0),
	(1573,'2016-05-22 22:53:28','2016-05-22 22:55:30','2016-05-22 22:55:30','11','100 miljard',1),
	(1574,'2016-05-22 22:53:28','2016-05-22 22:55:30','2016-05-22 22:55:30','11','10 miljard',0),
	(1575,'2016-05-22 22:53:28','2016-05-22 22:55:30','2016-05-22 22:55:30','11','100 miljoen',0),
	(1576,'2016-05-22 22:53:28','2016-05-22 22:55:30','2016-05-22 22:55:30','11','10 miljoen',0),
	(1577,'2016-05-22 22:53:28','2016-05-22 22:55:30','2016-05-22 22:55:30','11','1 biljoen',0),
	(1578,'2016-05-22 22:53:29','2016-05-22 22:55:30','2016-05-22 22:55:30','12','geen',1),
	(1579,'2016-05-22 22:53:29','2016-05-22 22:55:30','2016-05-22 22:55:30','12','Rode Reus',0),
	(1580,'2016-05-22 22:53:29','2016-05-22 22:55:30','2016-05-22 22:55:30','12','Super Reus',0),
	(1581,'2016-05-22 22:53:29','2016-05-22 22:55:30','2016-05-22 22:55:30','13','Witte dwerg',1),
	(1582,'2016-05-22 22:53:29','2016-05-22 22:55:30','2016-05-22 22:55:30','13','Neutronenster',0),
	(1583,'2016-05-22 22:53:29','2016-05-22 22:55:30','2016-05-22 22:55:30','13','Zwart gat',0),
	(1584,'2016-05-22 22:53:29','2016-05-22 22:55:30','2016-05-22 22:55:30','13','Bruine dwerg',0),
	(1585,'2016-05-22 22:53:29','2016-05-22 22:55:30','2016-05-22 22:55:30','14','plusminus 5500',1),
	(1586,'2016-05-22 22:53:29','2016-05-22 22:55:30','2016-05-22 22:55:30','14','plusminus 9000',0),
	(1587,'2016-05-22 22:53:29','2016-05-22 22:55:30','2016-05-22 22:55:30','14','plusminus 30.000',0),
	(1588,'2016-05-22 22:53:29','2016-05-22 22:55:30','2016-05-22 22:55:30','14','minder dan 3000',0),
	(1589,'2016-05-22 22:53:29','2016-05-22 22:55:30','2016-05-22 22:55:30','14','plusminus 3500',0),
	(1590,'2016-05-22 22:53:29','2016-05-22 22:55:30','2016-05-22 22:55:30','15','Geel',1),
	(1591,'2016-05-22 22:53:29','2016-05-22 22:55:30','2016-05-22 22:55:30','15','Wit',0),
	(1592,'2016-05-22 22:53:29','2016-05-22 22:55:30','2016-05-22 22:55:30','15','Blauw\r\n		</p>\r\n		<p>',0),
	(1593,'2016-05-22 22:53:29','2016-05-22 22:55:30','2016-05-22 22:55:30','15','Bruin/Rood',0),
	(1594,'2016-05-22 22:53:29','2016-05-22 22:55:30','2016-05-22 22:55:30','15','Oranje/Rood',0),
	(1595,'2016-05-22 22:53:29','2016-05-22 22:55:30','2016-05-22 22:55:30','16','H tot C',1),
	(1596,'2016-05-22 22:53:29','2016-05-22 22:55:30','2016-05-22 22:55:30','16','H tot Fe',0),
	(1597,'2016-05-22 22:53:29','2016-05-22 22:55:30','2016-05-22 22:55:30','16','nee',0),
	(1598,'2016-05-22 22:53:30','2016-05-22 22:55:30','2016-05-22 22:55:30','16','H tot He',0),
	(1599,'2016-05-22 22:53:30','2016-05-22 22:55:30','2016-05-22 22:55:30','17','10 miljard',1),
	(1600,'2016-05-22 22:53:30','2016-05-22 22:55:30','2016-05-22 22:55:30','17','100 miljoen',0),
	(1601,'2016-05-22 22:53:30','2016-05-22 22:55:30','2016-05-22 22:55:30','17','10 miljoen',0),
	(1602,'2016-05-22 22:53:30','2016-05-22 22:55:30','2016-05-22 22:55:30','17','1 biljoen',0),
	(1603,'2016-05-22 22:53:30','2016-05-22 22:55:30','2016-05-22 22:55:30','17','100 miljard',0),
	(1604,'2016-05-22 22:53:30','2016-05-22 22:55:30','2016-05-22 22:55:30','18','Rode Reus',1),
	(1605,'2016-05-22 22:53:30','2016-05-22 22:55:30','2016-05-22 22:55:30','18','Super Reus',0),
	(1606,'2016-05-22 22:53:30','2016-05-22 22:55:30','2016-05-22 22:55:30','18','geen',0),
	(1607,'2016-05-22 22:53:30','2016-05-22 22:55:30','2016-05-22 22:55:30','19','Witte dwerg',1),
	(1608,'2016-05-22 22:53:30','2016-05-22 22:55:30','2016-05-22 22:55:30','19','Neutronenster',0),
	(1609,'2016-05-22 22:53:30','2016-05-22 22:55:30','2016-05-22 22:55:30','19','Zwart gat',0),
	(1610,'2016-05-22 22:53:30','2016-05-22 22:55:30','2016-05-22 22:55:30','19','Bruine dwerg',0),
	(1611,'2016-05-22 22:53:30','2016-05-22 22:55:30','2016-05-22 22:55:30','20','plusminus 9000',1),
	(1612,'2016-05-22 22:53:30','2016-05-22 22:55:30','2016-05-22 22:55:30','20','plusminus 30.000',0),
	(1613,'2016-05-22 22:53:30','2016-05-22 22:55:30','2016-05-22 22:55:30','20','minder dan 3000',0),
	(1614,'2016-05-22 22:53:30','2016-05-22 22:55:30','2016-05-22 22:55:30','20','plusminus 3500',0),
	(1615,'2016-05-22 22:53:30','2016-05-22 22:55:30','2016-05-22 22:55:30','20','plusminus 5500',0),
	(1616,'2016-05-22 22:53:30','2016-05-22 22:55:30','2016-05-22 22:55:30','21','Wit',1),
	(1617,'2016-05-22 22:53:30','2016-05-22 22:55:30','2016-05-22 22:55:30','21','Blauw',0),
	(1618,'2016-05-22 22:53:30','2016-05-22 22:55:30','2016-05-22 22:55:30','21','Bruin/Rood\r\n		</p>\r\n		<p>',0),
	(1619,'2016-05-22 22:53:30','2016-05-22 22:55:30','2016-05-22 22:55:30','21','Oranje/Rood',0),
	(1620,'2016-05-22 22:53:31','2016-05-22 22:55:30','2016-05-22 22:55:30','21','Geel',0),
	(1621,'2016-05-22 22:53:31','2016-05-22 22:55:30','2016-05-22 22:55:30','22','H tot Fe',1),
	(1622,'2016-05-22 22:53:31','2016-05-22 22:55:30','2016-05-22 22:55:30','22','nee',0),
	(1623,'2016-05-22 22:53:31','2016-05-22 22:55:30','2016-05-22 22:55:30','22','H tot He',0),
	(1624,'2016-05-22 22:53:31','2016-05-22 22:55:30','2016-05-22 22:55:30','22','H tot C',0),
	(1625,'2016-05-22 22:53:31','2016-05-22 22:55:30','2016-05-22 22:55:30','23','100 miljoen',1),
	(1626,'2016-05-22 22:53:31','2016-05-22 22:55:30','2016-05-22 22:55:30','23','10 miljoen',0),
	(1627,'2016-05-22 22:53:31','2016-05-22 22:55:30','2016-05-22 22:55:30','23','1 biljoen',0),
	(1628,'2016-05-22 22:53:31','2016-05-22 22:55:30','2016-05-22 22:55:30','23','100 miljard',0),
	(1629,'2016-05-22 22:53:31','2016-05-22 22:55:30','2016-05-22 22:55:30','23','10 miljard',0),
	(1630,'2016-05-22 22:53:31','2016-05-22 22:55:30','2016-05-22 22:55:30','24','Super Reus',1),
	(1631,'2016-05-22 22:53:31','2016-05-22 22:55:30','2016-05-22 22:55:30','24','geen',0),
	(1632,'2016-05-22 22:53:31','2016-05-22 22:55:30','2016-05-22 22:55:30','24','Rode Reus',0),
	(1633,'2016-05-22 22:53:31','2016-05-22 22:55:30','2016-05-22 22:55:30','25','Neutronenster',1),
	(1634,'2016-05-22 22:53:31','2016-05-22 22:55:30','2016-05-22 22:55:30','25','Zwart gat',0),
	(1635,'2016-05-22 22:53:31','2016-05-22 22:55:30','2016-05-22 22:55:30','25','Bruine dwerg',0),
	(1636,'2016-05-22 22:53:31','2016-05-22 22:55:30','2016-05-22 22:55:30','25','Witte dwerg',0),
	(1637,'2016-05-22 22:53:31','2016-05-22 22:55:30','2016-05-22 22:55:30','26','plusminus 30.000',1),
	(1638,'2016-05-22 22:53:31','2016-05-22 22:55:30','2016-05-22 22:55:30','26','minder dan 3000',0),
	(1639,'2016-05-22 22:53:31','2016-05-22 22:55:30','2016-05-22 22:55:30','26','plusminus 3500',0),
	(1640,'2016-05-22 22:53:31','2016-05-22 22:55:30','2016-05-22 22:55:30','26','plusminus 5500',0),
	(1641,'2016-05-22 22:53:32','2016-05-22 22:55:30','2016-05-22 22:55:30','26','plusminus 9000',0),
	(1642,'2016-05-22 22:53:32','2016-05-22 22:55:30','2016-05-22 22:55:30','27','Blauw',1),
	(1643,'2016-05-22 22:53:32','2016-05-22 22:55:30','2016-05-22 22:55:30','27','Bruin/Rood\r\n		</p>\r\n		<p>',0),
	(1644,'2016-05-22 22:53:32','2016-05-22 22:55:30','2016-05-22 22:55:30','27','Oranje/Rood',0),
	(1645,'2016-05-22 22:53:32','2016-05-22 22:55:30','2016-05-22 22:55:30','27','Geel',0),
	(1646,'2016-05-22 22:53:32','2016-05-22 22:55:30','2016-05-22 22:55:30','27','Wit',0),
	(1647,'2016-05-22 22:53:32','2016-05-22 22:55:30','2016-05-22 22:55:30','28','H tot Fe',1),
	(1648,'2016-05-22 22:53:32','2016-05-22 22:55:30','2016-05-22 22:55:30','28','nee',0),
	(1649,'2016-05-22 22:53:32','2016-05-22 22:55:30','2016-05-22 22:55:30','28','H tot He',0),
	(1650,'2016-05-22 22:53:32','2016-05-22 22:55:30','2016-05-22 22:55:30','28','H tot C',0),
	(1651,'2016-05-22 22:53:32','2016-05-22 22:55:30','2016-05-22 22:55:30','29','10 miljoen',1),
	(1652,'2016-05-22 22:53:32','2016-05-22 22:55:30','2016-05-22 22:55:30','29','1 biljoen',0),
	(1653,'2016-05-22 22:53:32','2016-05-22 22:55:30','2016-05-22 22:55:30','29','100 miljard',0),
	(1654,'2016-05-22 22:53:32','2016-05-22 22:55:30','2016-05-22 22:55:30','29','10 miljard',0),
	(1655,'2016-05-22 22:53:32','2016-05-22 22:55:30','2016-05-22 22:55:30','29','100 miljoen',0),
	(1656,'2016-05-22 22:53:32','2016-05-22 22:55:30','2016-05-22 22:55:30','30','Super Reus',1),
	(1657,'2016-05-22 22:53:32','2016-05-22 22:55:30','2016-05-22 22:55:30','30','geen',0),
	(1658,'2016-05-22 22:53:32','2016-05-22 22:55:30','2016-05-22 22:55:30','30','Rode Reus',0),
	(1659,'2016-05-22 22:53:32','2016-05-22 22:55:30','2016-05-22 22:55:30','31','Zwart gat',1),
	(1660,'2016-05-22 22:53:33','2016-05-22 22:55:30','2016-05-22 22:55:30','31','Bruine dwerg',0),
	(1661,'2016-05-22 22:53:33','2016-05-22 22:55:30','2016-05-22 22:55:30','31','Witte dwerg',0),
	(1662,'2016-05-22 22:53:33','2016-05-22 22:55:30','2016-05-22 22:55:30','31','Neutronenster',0),
	(1663,'2016-05-22 22:55:30','2016-05-22 22:56:26','2016-05-22 22:56:26','1','Bruine dwerg',1),
	(1664,'2016-05-22 22:55:30','2016-05-22 22:56:26','2016-05-22 22:56:26','1','Witte dwerg',0),
	(1665,'2016-05-22 22:55:30','2016-05-22 22:56:26','2016-05-22 22:56:26','1','Neutronenster',0),
	(1666,'2016-05-22 22:55:30','2016-05-22 22:56:26','2016-05-22 22:56:26','1','Zwart gat',0),
	(1667,'2016-05-22 22:55:31','2016-05-22 22:56:26','2016-05-22 22:56:26','2','minder dan 3000',1),
	(1668,'2016-05-22 22:55:31','2016-05-22 22:56:26','2016-05-22 22:56:26','2','plusminus 3500',0),
	(1669,'2016-05-22 22:55:31','2016-05-22 22:56:26','2016-05-22 22:56:26','2','plusminus 5500',0),
	(1670,'2016-05-22 22:55:31','2016-05-22 22:56:26','2016-05-22 22:56:26','2','plusminus 9000',0),
	(1671,'2016-05-22 22:55:31','2016-05-22 22:56:26','2016-05-22 22:56:26','2','plusminus 30.000',0),
	(1672,'2016-05-22 22:55:31','2016-05-22 22:56:26','2016-05-22 22:56:26','3','Bruin/Rood',1),
	(1673,'2016-05-22 22:55:31','2016-05-22 22:56:26','2016-05-22 22:56:26','3','Oranje/Rood',0),
	(1674,'2016-05-22 22:55:31','2016-05-22 22:56:26','2016-05-22 22:56:26','3','Geel',0),
	(1675,'2016-05-22 22:55:31','2016-05-22 22:56:26','2016-05-22 22:56:26','3','Wit',0),
	(1676,'2016-05-22 22:55:31','2016-05-22 22:56:26','2016-05-22 22:56:26','3','Blauw',0),
	(1677,'2016-05-22 22:55:31','2016-05-22 22:56:26','2016-05-22 22:56:26','4','nee',1),
	(1678,'2016-05-22 22:55:31','2016-05-22 22:56:26','2016-05-22 22:56:26','4','van H tot He',0),
	(1679,'2016-05-22 22:55:31','2016-05-22 22:56:26','2016-05-22 22:56:26','4','H tot C',0),
	(1680,'2016-05-22 22:55:31','2016-05-22 22:56:26','2016-05-22 22:56:26','4','H tot Fe',0),
	(1681,'2016-05-22 22:55:31','2016-05-22 22:56:26','2016-05-22 22:56:26','5','1 biljoen',1),
	(1682,'2016-05-22 22:55:31','2016-05-22 22:56:26','2016-05-22 22:56:26','5','100 miljard',0),
	(1683,'2016-05-22 22:55:31','2016-05-22 22:56:26','2016-05-22 22:56:26','5','10 miljard',0),
	(1684,'2016-05-22 22:55:31','2016-05-22 22:56:26','2016-05-22 22:56:26','5','100 miljoen',0),
	(1685,'2016-05-22 22:55:31','2016-05-22 22:56:26','2016-05-22 22:56:26','5','10 miljoen',0),
	(1686,'2016-05-22 22:55:32','2016-05-22 22:56:26','2016-05-22 22:56:26','6','geen',1),
	(1687,'2016-05-22 22:55:32','2016-05-22 22:56:26','2016-05-22 22:56:26','6','Rode Reus',0),
	(1688,'2016-05-22 22:55:32','2016-05-22 22:56:26','2016-05-22 22:56:26','6','Super Reus',0),
	(1689,'2016-05-22 22:55:32','2016-05-22 22:56:26','2016-05-22 22:56:26','7','Bruine dwerg',1),
	(1690,'2016-05-22 22:55:32','2016-05-22 22:56:26','2016-05-22 22:56:26','7','Witte dwerg',0),
	(1691,'2016-05-22 22:55:32','2016-05-22 22:56:26','2016-05-22 22:56:26','7','Neutronenster',0),
	(1692,'2016-05-22 22:55:32','2016-05-22 22:56:26','2016-05-22 22:56:26','7',' Zwart gat',0),
	(1693,'2016-05-22 22:55:32','2016-05-22 22:56:26','2016-05-22 22:56:26','8','plusminus 3500',1),
	(1694,'2016-05-22 22:55:32','2016-05-22 22:56:26','2016-05-22 22:56:26','8','plusminus 5500',0),
	(1695,'2016-05-22 22:55:32','2016-05-22 22:56:26','2016-05-22 22:56:26','8','plusminus 9000',0),
	(1696,'2016-05-22 22:55:32','2016-05-22 22:56:26','2016-05-22 22:56:26','8','plusminus 30.000',0),
	(1697,'2016-05-22 22:55:32','2016-05-22 22:56:26','2016-05-22 22:56:26','8','minder dan 3000',0),
	(1698,'2016-05-22 22:55:32','2016-05-22 22:56:26','2016-05-22 22:56:26','9','Oranje/Rood',1),
	(1699,'2016-05-22 22:55:32','2016-05-22 22:56:26','2016-05-22 22:56:26','9','Geel',0),
	(1700,'2016-05-22 22:55:32','2016-05-22 22:56:26','2016-05-22 22:56:26','9','Wit',0),
	(1701,'2016-05-22 22:55:32','2016-05-22 22:56:26','2016-05-22 22:56:26','9','Blauw',0),
	(1702,'2016-05-22 22:55:32','2016-05-22 22:56:26','2016-05-22 22:56:26','9','Bruin/Rood',0),
	(1703,'2016-05-22 22:55:32','2016-05-22 22:56:26','2016-05-22 22:56:26','10','H tot He',1),
	(1704,'2016-05-22 22:55:32','2016-05-22 22:56:26','2016-05-22 22:56:26','10','H tot C',0),
	(1705,'2016-05-22 22:55:33','2016-05-22 22:56:26','2016-05-22 22:56:26','10','H tot Fe',0),
	(1706,'2016-05-22 22:55:33','2016-05-22 22:56:26','2016-05-22 22:56:26','10','nee',0),
	(1707,'2016-05-22 22:55:33','2016-05-22 22:56:26','2016-05-22 22:56:26','11','100 miljard',1),
	(1708,'2016-05-22 22:55:33','2016-05-22 22:56:26','2016-05-22 22:56:26','11','10 miljard',0),
	(1709,'2016-05-22 22:55:33','2016-05-22 22:56:26','2016-05-22 22:56:26','11','100 miljoen',0),
	(1710,'2016-05-22 22:55:33','2016-05-22 22:56:26','2016-05-22 22:56:26','11','10 miljoen',0),
	(1711,'2016-05-22 22:55:33','2016-05-22 22:56:26','2016-05-22 22:56:26','11','1 biljoen',0),
	(1712,'2016-05-22 22:55:33','2016-05-22 22:56:26','2016-05-22 22:56:26','12','geen',1),
	(1713,'2016-05-22 22:55:33','2016-05-22 22:56:26','2016-05-22 22:56:26','12','Rode Reus',0),
	(1714,'2016-05-22 22:55:33','2016-05-22 22:56:26','2016-05-22 22:56:26','12','Super Reus',0),
	(1715,'2016-05-22 22:55:33','2016-05-22 22:56:26','2016-05-22 22:56:26','13','Witte dwerg',1),
	(1716,'2016-05-22 22:55:33','2016-05-22 22:56:26','2016-05-22 22:56:26','13','Neutronenster',0),
	(1717,'2016-05-22 22:55:33','2016-05-22 22:56:26','2016-05-22 22:56:26','13','Zwart gat',0),
	(1718,'2016-05-22 22:55:33','2016-05-22 22:56:26','2016-05-22 22:56:26','13','Bruine dwerg',0),
	(1719,'2016-05-22 22:55:33','2016-05-22 22:56:26','2016-05-22 22:56:26','14','plusminus 5500',1),
	(1720,'2016-05-22 22:55:33','2016-05-22 22:56:26','2016-05-22 22:56:26','14','plusminus 9000',0),
	(1721,'2016-05-22 22:55:33','2016-05-22 22:56:26','2016-05-22 22:56:26','14','plusminus 30.000',0),
	(1722,'2016-05-22 22:55:33','2016-05-22 22:56:26','2016-05-22 22:56:26','14','minder dan 3000',0),
	(1723,'2016-05-22 22:55:34','2016-05-22 22:56:26','2016-05-22 22:56:26','14','plusminus 3500',0),
	(1724,'2016-05-22 22:55:34','2016-05-22 22:56:26','2016-05-22 22:56:26','15','Geel',1),
	(1725,'2016-05-22 22:55:34','2016-05-22 22:56:26','2016-05-22 22:56:26','15','Wit',0),
	(1726,'2016-05-22 22:55:34','2016-05-22 22:56:26','2016-05-22 22:56:26','15','Blauw',0),
	(1727,'2016-05-22 22:55:34','2016-05-22 22:56:26','2016-05-22 22:56:26','15','Bruin/Rood',0),
	(1728,'2016-05-22 22:55:34','2016-05-22 22:56:26','2016-05-22 22:56:26','15','Oranje/Rood',0),
	(1729,'2016-05-22 22:55:34','2016-05-22 22:56:26','2016-05-22 22:56:26','16','H tot C',1),
	(1730,'2016-05-22 22:55:34','2016-05-22 22:56:26','2016-05-22 22:56:26','16','H tot Fe',0),
	(1731,'2016-05-22 22:55:34','2016-05-22 22:56:26','2016-05-22 22:56:26','16','nee',0),
	(1732,'2016-05-22 22:55:34','2016-05-22 22:56:26','2016-05-22 22:56:26','16','H tot He',0),
	(1733,'2016-05-22 22:55:34','2016-05-22 22:56:26','2016-05-22 22:56:26','17','10 miljard',1),
	(1734,'2016-05-22 22:55:34','2016-05-22 22:56:26','2016-05-22 22:56:26','17','100 miljoen',0),
	(1735,'2016-05-22 22:55:34','2016-05-22 22:56:26','2016-05-22 22:56:26','17','10 miljoen',0),
	(1736,'2016-05-22 22:55:34','2016-05-22 22:56:26','2016-05-22 22:56:26','17','1 biljoen',0),
	(1737,'2016-05-22 22:55:34','2016-05-22 22:56:26','2016-05-22 22:56:26','17','100 miljard',0),
	(1738,'2016-05-22 22:55:34','2016-05-22 22:56:26','2016-05-22 22:56:26','18','Rode Reus',1),
	(1739,'2016-05-22 22:55:34','2016-05-22 22:56:26','2016-05-22 22:56:26','18','Super Reus',0),
	(1740,'2016-05-22 22:55:34','2016-05-22 22:56:26','2016-05-22 22:56:26','18','geen',0),
	(1741,'2016-05-22 22:55:34','2016-05-22 22:56:26','2016-05-22 22:56:26','19','Witte dwerg',1),
	(1742,'2016-05-22 22:55:34','2016-05-22 22:56:26','2016-05-22 22:56:26','19','Neutronenster',0),
	(1743,'2016-05-22 22:55:35','2016-05-22 22:56:26','2016-05-22 22:56:26','19','Zwart gat',0),
	(1744,'2016-05-22 22:55:35','2016-05-22 22:56:26','2016-05-22 22:56:26','19','Bruine dwerg',0),
	(1745,'2016-05-22 22:55:35','2016-05-22 22:56:26','2016-05-22 22:56:26','20','plusminus 9000',1),
	(1746,'2016-05-22 22:55:35','2016-05-22 22:56:26','2016-05-22 22:56:26','20','plusminus 30.000',0),
	(1747,'2016-05-22 22:55:35','2016-05-22 22:56:26','2016-05-22 22:56:26','20','minder dan 3000',0),
	(1748,'2016-05-22 22:55:35','2016-05-22 22:56:26','2016-05-22 22:56:26','20','plusminus 3500',0),
	(1749,'2016-05-22 22:55:35','2016-05-22 22:56:26','2016-05-22 22:56:26','20','plusminus 5500',0),
	(1750,'2016-05-22 22:55:35','2016-05-22 22:56:26','2016-05-22 22:56:26','21','Wit',1),
	(1751,'2016-05-22 22:55:35','2016-05-22 22:56:26','2016-05-22 22:56:26','21','Blauw',0),
	(1752,'2016-05-22 22:55:35','2016-05-22 22:56:26','2016-05-22 22:56:26','21','Bruin/Rood',0),
	(1753,'2016-05-22 22:55:35','2016-05-22 22:56:26','2016-05-22 22:56:26','21','Oranje/Rood',0),
	(1754,'2016-05-22 22:55:35','2016-05-22 22:56:26','2016-05-22 22:56:26','21','Geel',0),
	(1755,'2016-05-22 22:55:35','2016-05-22 22:56:26','2016-05-22 22:56:26','22','H tot Fe',1),
	(1756,'2016-05-22 22:55:35','2016-05-22 22:56:26','2016-05-22 22:56:26','22','nee',0),
	(1757,'2016-05-22 22:55:35','2016-05-22 22:56:26','2016-05-22 22:56:26','22','H tot He',0),
	(1758,'2016-05-22 22:55:36','2016-05-22 22:56:26','2016-05-22 22:56:26','22','H tot C',0),
	(1759,'2016-05-22 22:55:36','2016-05-22 22:56:26','2016-05-22 22:56:26','23','100 miljoen',1),
	(1760,'2016-05-22 22:55:36','2016-05-22 22:56:26','2016-05-22 22:56:26','23','10 miljoen',0),
	(1761,'2016-05-22 22:55:36','2016-05-22 22:56:26','2016-05-22 22:56:26','23','1 biljoen',0),
	(1762,'2016-05-22 22:55:36','2016-05-22 22:56:26','2016-05-22 22:56:26','23','100 miljard',0),
	(1763,'2016-05-22 22:55:36','2016-05-22 22:56:26','2016-05-22 22:56:26','23','10 miljard',0),
	(1764,'2016-05-22 22:55:36','2016-05-22 22:56:26','2016-05-22 22:56:26','24','Super Reus',1),
	(1765,'2016-05-22 22:55:36','2016-05-22 22:56:26','2016-05-22 22:56:26','24','geen',0),
	(1766,'2016-05-22 22:55:36','2016-05-22 22:56:26','2016-05-22 22:56:26','24','Rode Reus',0),
	(1767,'2016-05-22 22:55:36','2016-05-22 22:56:26','2016-05-22 22:56:26','25','Neutronenster',1),
	(1768,'2016-05-22 22:55:36','2016-05-22 22:56:26','2016-05-22 22:56:26','25','Zwart gat',0),
	(1769,'2016-05-22 22:55:36','2016-05-22 22:56:26','2016-05-22 22:56:26','25','Bruine dwerg',0),
	(1770,'2016-05-22 22:55:36','2016-05-22 22:56:26','2016-05-22 22:56:26','25','Witte dwerg',0),
	(1771,'2016-05-22 22:55:36','2016-05-22 22:56:26','2016-05-22 22:56:26','26','plusminus 30.000',1),
	(1772,'2016-05-22 22:55:36','2016-05-22 22:56:26','2016-05-22 22:56:26','26','minder dan 3000',0),
	(1773,'2016-05-22 22:55:36','2016-05-22 22:56:26','2016-05-22 22:56:26','26','plusminus 3500',0),
	(1774,'2016-05-22 22:55:37','2016-05-22 22:56:26','2016-05-22 22:56:26','26','plusminus 5500',0),
	(1775,'2016-05-22 22:55:37','2016-05-22 22:56:26','2016-05-22 22:56:26','26','plusminus 9000',0),
	(1776,'2016-05-22 22:55:37','2016-05-22 22:56:26','2016-05-22 22:56:26','27','Blauw',1),
	(1777,'2016-05-22 22:55:37','2016-05-22 22:56:26','2016-05-22 22:56:26','27','Bruin/Rood',0),
	(1778,'2016-05-22 22:55:37','2016-05-22 22:56:26','2016-05-22 22:56:26','27','Oranje/Rood',0),
	(1779,'2016-05-22 22:55:37','2016-05-22 22:56:26','2016-05-22 22:56:26','27','Geel',0),
	(1780,'2016-05-22 22:55:37','2016-05-22 22:56:26','2016-05-22 22:56:26','27','Wit',0),
	(1781,'2016-05-22 22:55:37','2016-05-22 22:56:26','2016-05-22 22:56:26','28','H tot Fe',1),
	(1782,'2016-05-22 22:55:37','2016-05-22 22:56:26','2016-05-22 22:56:26','28','nee',0),
	(1783,'2016-05-22 22:55:37','2016-05-22 22:56:26','2016-05-22 22:56:26','28','H tot He',0),
	(1784,'2016-05-22 22:55:37','2016-05-22 22:56:26','2016-05-22 22:56:26','28','H tot C',0),
	(1785,'2016-05-22 22:55:37','2016-05-22 22:56:26','2016-05-22 22:56:26','29','10 miljoen',1),
	(1786,'2016-05-22 22:55:37','2016-05-22 22:56:26','2016-05-22 22:56:26','29','1 biljoen',0),
	(1787,'2016-05-22 22:55:37','2016-05-22 22:56:26','2016-05-22 22:56:26','29','100 miljard',0),
	(1788,'2016-05-22 22:55:37','2016-05-22 22:56:26','2016-05-22 22:56:26','29','10 miljard',0),
	(1789,'2016-05-22 22:55:37','2016-05-22 22:56:26','2016-05-22 22:56:26','29','100 miljoen',0),
	(1790,'2016-05-22 22:55:37','2016-05-22 22:56:26','2016-05-22 22:56:26','30','Super Reus',1),
	(1791,'2016-05-22 22:55:38','2016-05-22 22:56:26','2016-05-22 22:56:26','30','geen',0),
	(1792,'2016-05-22 22:55:38','2016-05-22 22:56:26','2016-05-22 22:56:26','30','Rode Reus',0),
	(1793,'2016-05-22 22:55:38','2016-05-22 22:56:26','2016-05-22 22:56:26','31','Zwart gat',1),
	(1794,'2016-05-22 22:55:38','2016-05-22 22:56:26','2016-05-22 22:56:26','31','Bruine dwerg',0),
	(1795,'2016-05-22 22:55:38','2016-05-22 22:56:26','2016-05-22 22:56:26','31','Witte dwerg',0),
	(1796,'2016-05-22 22:55:38','2016-05-22 22:56:26','2016-05-22 22:56:26','31','Neutronenster',0),
	(1797,'2016-05-22 22:56:27','2016-05-22 22:56:27',NULL,'1','Bruine dwerg',1),
	(1798,'2016-05-22 22:56:27','2016-05-22 22:56:27',NULL,'1','Witte dwerg',0),
	(1799,'2016-05-22 22:56:27','2016-05-22 22:56:27',NULL,'1','Neutronenster',0),
	(1800,'2016-05-22 22:56:27','2016-05-22 22:56:27',NULL,'1','Zwart gat',0),
	(1801,'2016-05-22 22:56:27','2016-05-22 22:56:27',NULL,'2','minder dan 3000',1),
	(1802,'2016-05-22 22:56:27','2016-05-22 22:56:27',NULL,'2','plusminus 3500',0),
	(1803,'2016-05-22 22:56:27','2016-05-22 22:56:27',NULL,'2','plusminus 5500',0),
	(1804,'2016-05-22 22:56:27','2016-05-22 22:56:27',NULL,'2','plusminus 9000',0),
	(1805,'2016-05-22 22:56:27','2016-05-22 22:56:27',NULL,'2','plusminus 30.000',0),
	(1806,'2016-05-22 22:56:27','2016-05-22 22:56:27',NULL,'3','Bruin/Rood',1),
	(1807,'2016-05-22 22:56:27','2016-05-22 22:56:27',NULL,'3','Oranje/Rood',0),
	(1808,'2016-05-22 22:56:27','2016-05-22 22:56:27',NULL,'3','Geel',0),
	(1809,'2016-05-22 22:56:27','2016-05-22 22:56:27',NULL,'3','Wit',0),
	(1810,'2016-05-22 22:56:27','2016-05-22 22:56:27',NULL,'3','Blauw',0),
	(1811,'2016-05-22 22:56:27','2016-05-22 22:56:27',NULL,'4','nee',1),
	(1812,'2016-05-22 22:56:27','2016-05-22 22:56:27',NULL,'4','van H tot He',0),
	(1813,'2016-05-22 22:56:27','2016-05-22 22:56:27',NULL,'4','H tot C',0),
	(1814,'2016-05-22 22:56:27','2016-05-22 22:56:27',NULL,'4','H tot Fe',0),
	(1815,'2016-05-22 22:56:27','2016-05-22 22:56:27',NULL,'5','1 biljoen',1),
	(1816,'2016-05-22 22:56:27','2016-05-22 22:56:27',NULL,'5','100 miljard',0),
	(1817,'2016-05-22 22:56:27','2016-05-22 22:56:27',NULL,'5','10 miljard',0),
	(1818,'2016-05-22 22:56:27','2016-05-22 22:56:27',NULL,'5','100 miljoen',0),
	(1819,'2016-05-22 22:56:28','2016-05-22 22:56:28',NULL,'5','10 miljoen',0),
	(1820,'2016-05-22 22:56:28','2016-05-22 22:56:28',NULL,'6','geen',1),
	(1821,'2016-05-22 22:56:28','2016-05-22 22:56:28',NULL,'6','Rode Reus',0),
	(1822,'2016-05-22 22:56:28','2016-05-22 22:56:28',NULL,'6','Super Reus',0),
	(1823,'2016-05-22 22:56:28','2016-05-22 22:56:28',NULL,'7','Bruine dwerg',1),
	(1824,'2016-05-22 22:56:28','2016-05-22 22:56:28',NULL,'7','Witte dwerg',0),
	(1825,'2016-05-22 22:56:28','2016-05-22 22:56:28',NULL,'7','Neutronenster',0),
	(1826,'2016-05-22 22:56:28','2016-05-22 22:56:28',NULL,'7',' Zwart gat',0),
	(1827,'2016-05-22 22:56:28','2016-05-22 22:56:28',NULL,'8','plusminus 3500',1),
	(1828,'2016-05-22 22:56:28','2016-05-22 22:56:28',NULL,'8','plusminus 5500',0),
	(1829,'2016-05-22 22:56:28','2016-05-22 22:56:28',NULL,'8','plusminus 9000',0),
	(1830,'2016-05-22 22:56:28','2016-05-22 22:56:28',NULL,'8','plusminus 30.000',0),
	(1831,'2016-05-22 22:56:28','2016-05-22 22:56:28',NULL,'8','minder dan 3000',0),
	(1832,'2016-05-22 22:56:28','2016-05-22 22:56:28',NULL,'9','Oranje/Rood',1),
	(1833,'2016-05-22 22:56:28','2016-05-22 22:56:28',NULL,'9','Geel',0),
	(1834,'2016-05-22 22:56:28','2016-05-22 22:56:28',NULL,'9','Wit',0),
	(1835,'2016-05-22 22:56:28','2016-05-22 22:56:28',NULL,'9','Blauw',0),
	(1836,'2016-05-22 22:56:28','2016-05-22 22:56:28',NULL,'9','Bruin/Rood',0),
	(1837,'2016-05-22 22:56:28','2016-05-22 22:56:28',NULL,'10','H tot He',1),
	(1838,'2016-05-22 22:56:28','2016-05-22 22:56:28',NULL,'10','H tot C',0),
	(1839,'2016-05-22 22:56:28','2016-05-22 22:56:28',NULL,'10','H tot Fe',0),
	(1840,'2016-05-22 22:56:28','2016-05-22 22:56:28',NULL,'10','nee',0),
	(1841,'2016-05-22 22:56:28','2016-05-22 22:56:28',NULL,'11','100 miljard',1),
	(1842,'2016-05-22 22:56:29','2016-05-22 22:56:29',NULL,'11','10 miljard',0),
	(1843,'2016-05-22 22:56:29','2016-05-22 22:56:29',NULL,'11','100 miljoen',0),
	(1844,'2016-05-22 22:56:29','2016-05-22 22:56:29',NULL,'11','10 miljoen',0),
	(1845,'2016-05-22 22:56:29','2016-05-22 22:56:29',NULL,'11','1 biljoen',0),
	(1846,'2016-05-22 22:56:29','2016-05-22 22:56:29',NULL,'12','geen',1),
	(1847,'2016-05-22 22:56:29','2016-05-22 22:56:29',NULL,'12','Rode Reus',0),
	(1848,'2016-05-22 22:56:29','2016-05-22 22:56:29',NULL,'12','Super Reus',0),
	(1849,'2016-05-22 22:56:29','2016-05-22 22:56:29',NULL,'13','Witte dwerg',1),
	(1850,'2016-05-22 22:56:29','2016-05-22 22:56:29',NULL,'13','Neutronenster',0),
	(1851,'2016-05-22 22:56:29','2016-05-22 22:56:29',NULL,'13','Zwart gat',0),
	(1852,'2016-05-22 22:56:29','2016-05-22 22:56:29',NULL,'13','Bruine dwerg',0),
	(1853,'2016-05-22 22:56:29','2016-05-22 22:56:29',NULL,'14','plusminus 5500',1),
	(1854,'2016-05-22 22:56:29','2016-05-22 22:56:29',NULL,'14','plusminus 9000',0),
	(1855,'2016-05-22 22:56:29','2016-05-22 22:56:29',NULL,'14','plusminus 30.000',0),
	(1856,'2016-05-22 22:56:29','2016-05-22 22:56:29',NULL,'14','minder dan 3000',0),
	(1857,'2016-05-22 22:56:29','2016-05-22 22:56:29',NULL,'14','plusminus 3500',0),
	(1858,'2016-05-22 22:56:29','2016-05-22 22:56:29',NULL,'15','Geel',1),
	(1859,'2016-05-22 22:56:29','2016-05-22 22:56:29',NULL,'15','Wit',0),
	(1860,'2016-05-22 22:56:29','2016-05-22 22:56:29',NULL,'15','Blauw',0),
	(1861,'2016-05-22 22:56:29','2016-05-22 22:56:29',NULL,'15','Bruin/Rood',0),
	(1862,'2016-05-22 22:56:29','2016-05-22 22:56:29',NULL,'15','Oranje/Rood',0),
	(1863,'2016-05-22 22:56:30','2016-05-22 22:56:30',NULL,'16','H tot C',1),
	(1864,'2016-05-22 22:56:30','2016-05-22 22:56:30',NULL,'16','H tot Fe',0),
	(1865,'2016-05-22 22:56:30','2016-05-22 22:56:30',NULL,'16','nee',0),
	(1866,'2016-05-22 22:56:30','2016-05-22 22:56:30',NULL,'16','H tot He',0),
	(1867,'2016-05-22 22:56:30','2016-05-22 22:56:30',NULL,'17','10 miljard',1),
	(1868,'2016-05-22 22:56:30','2016-05-22 22:56:30',NULL,'17','100 miljoen',0),
	(1869,'2016-05-22 22:56:30','2016-05-22 22:56:30',NULL,'17','10 miljoen',0),
	(1870,'2016-05-22 22:56:30','2016-05-22 22:56:30',NULL,'17','1 biljoen',0),
	(1871,'2016-05-22 22:56:30','2016-05-22 22:56:30',NULL,'17','100 miljard',0),
	(1872,'2016-05-22 22:56:30','2016-05-22 22:56:30',NULL,'18','Rode Reus',1),
	(1873,'2016-05-22 22:56:30','2016-05-22 22:56:30',NULL,'18','Super Reus',0),
	(1874,'2016-05-22 22:56:30','2016-05-22 22:56:30',NULL,'18','geen',0),
	(1875,'2016-05-22 22:56:30','2016-05-22 22:56:30',NULL,'19','Witte dwerg',1),
	(1876,'2016-05-22 22:56:30','2016-05-22 22:56:30',NULL,'19','Neutronenster',0),
	(1877,'2016-05-22 22:56:30','2016-05-22 22:56:30',NULL,'19','Zwart gat',0),
	(1878,'2016-05-22 22:56:30','2016-05-22 22:56:30',NULL,'19','Bruine dwerg',0),
	(1879,'2016-05-22 22:56:30','2016-05-22 22:56:30',NULL,'20','plusminus 9000',1),
	(1880,'2016-05-22 22:56:30','2016-05-22 22:56:30',NULL,'20','plusminus 30.000',0),
	(1881,'2016-05-22 22:56:30','2016-05-22 22:56:30',NULL,'20','minder dan 3000',0),
	(1882,'2016-05-22 22:56:30','2016-05-22 22:56:30',NULL,'20','plusminus 3500',0),
	(1883,'2016-05-22 22:56:30','2016-05-22 22:56:30',NULL,'20','plusminus 5500',0),
	(1884,'2016-05-22 22:56:31','2016-05-22 22:56:31',NULL,'21','Wit',1),
	(1885,'2016-05-22 22:56:31','2016-05-22 22:56:31',NULL,'21','Blauw',0),
	(1886,'2016-05-22 22:56:31','2016-05-22 22:56:31',NULL,'21','Bruin/Rood',0),
	(1887,'2016-05-22 22:56:31','2016-05-22 22:56:31',NULL,'21','Oranje/Rood',0),
	(1888,'2016-05-22 22:56:31','2016-05-22 22:56:31',NULL,'21','Geel',0),
	(1889,'2016-05-22 22:56:31','2016-05-22 22:56:31',NULL,'22','H tot Fe',1),
	(1890,'2016-05-22 22:56:31','2016-05-22 22:56:31',NULL,'22','nee',0),
	(1891,'2016-05-22 22:56:31','2016-05-22 22:56:31',NULL,'22','H tot He',0),
	(1892,'2016-05-22 22:56:31','2016-05-22 22:56:31',NULL,'22','H tot C',0),
	(1893,'2016-05-22 22:56:31','2016-05-22 22:56:31',NULL,'23','100 miljoen',1),
	(1894,'2016-05-22 22:56:31','2016-05-22 22:56:31',NULL,'23','10 miljoen',0),
	(1895,'2016-05-22 22:56:31','2016-05-22 22:56:31',NULL,'23','1 biljoen',0),
	(1896,'2016-05-22 22:56:31','2016-05-22 22:56:31',NULL,'23','100 miljard',0),
	(1897,'2016-05-22 22:56:31','2016-05-22 22:56:31',NULL,'23','10 miljard',0),
	(1898,'2016-05-22 22:56:31','2016-05-22 22:56:31',NULL,'24','Super Reus',1),
	(1899,'2016-05-22 22:56:31','2016-05-22 22:56:31',NULL,'24','geen',0),
	(1900,'2016-05-22 22:56:31','2016-05-22 22:56:31',NULL,'24','Rode Reus',0),
	(1901,'2016-05-22 22:56:31','2016-05-22 22:56:31',NULL,'25','Neutronenster',1),
	(1902,'2016-05-22 22:56:31','2016-05-22 22:56:31',NULL,'25','Zwart gat',0),
	(1903,'2016-05-22 22:56:31','2016-05-22 22:56:31',NULL,'25','Bruine dwerg',0),
	(1904,'2016-05-22 22:56:31','2016-05-22 22:56:31',NULL,'25','Witte dwerg',0),
	(1905,'2016-05-22 22:56:31','2016-05-22 22:56:31',NULL,'26','plusminus 30.000',1),
	(1906,'2016-05-22 22:56:32','2016-05-22 22:56:32',NULL,'26','minder dan 3000',0),
	(1907,'2016-05-22 22:56:32','2016-05-22 22:56:32',NULL,'26','plusminus 3500',0),
	(1908,'2016-05-22 22:56:32','2016-05-22 22:56:32',NULL,'26','plusminus 5500',0),
	(1909,'2016-05-22 22:56:32','2016-05-22 22:56:32',NULL,'26','plusminus 9000',0),
	(1910,'2016-05-22 22:56:32','2016-05-22 22:56:32',NULL,'27','Blauw',1),
	(1911,'2016-05-22 22:56:32','2016-05-22 22:56:32',NULL,'27','Bruin/Rood',0),
	(1912,'2016-05-22 22:56:32','2016-05-22 22:56:32',NULL,'27','Oranje/Rood',0),
	(1913,'2016-05-22 22:56:32','2016-05-22 22:56:32',NULL,'27','Geel',0),
	(1914,'2016-05-22 22:56:32','2016-05-22 22:56:32',NULL,'27','Wit',0),
	(1915,'2016-05-22 22:56:32','2016-05-22 22:56:32',NULL,'28','H tot Fe',1),
	(1916,'2016-05-22 22:56:32','2016-05-22 22:56:32',NULL,'28','nee',0),
	(1917,'2016-05-22 22:56:32','2016-05-22 22:56:32',NULL,'28','H tot He',0),
	(1918,'2016-05-22 22:56:32','2016-05-22 22:56:32',NULL,'28','H tot C',0),
	(1919,'2016-05-22 22:56:32','2016-05-22 22:56:32',NULL,'29','10 miljoen',1),
	(1920,'2016-05-22 22:56:32','2016-05-22 22:56:32',NULL,'29','1 biljoen',0),
	(1921,'2016-05-22 22:56:32','2016-05-22 22:56:32',NULL,'29','100 miljard',0),
	(1922,'2016-05-22 22:56:32','2016-05-22 22:56:32',NULL,'29','10 miljard',0),
	(1923,'2016-05-22 22:56:32','2016-05-22 22:56:32',NULL,'29','100 miljoen',0),
	(1924,'2016-05-22 22:56:32','2016-05-22 22:56:32',NULL,'30','Super Reus',1),
	(1925,'2016-05-22 22:56:33','2016-05-22 22:56:33',NULL,'30','geen',0),
	(1926,'2016-05-22 22:56:33','2016-05-22 22:56:33',NULL,'30','Rode Reus',0),
	(1927,'2016-05-22 22:56:33','2016-05-22 22:56:33',NULL,'31','Zwart gat',1),
	(1928,'2016-05-22 22:56:33','2016-05-22 22:56:33',NULL,'31','Bruine dwerg',0),
	(1929,'2016-05-22 22:56:33','2016-05-22 22:56:33',NULL,'31','Witte dwerg',0),
	(1930,'2016-05-22 22:56:33','2016-05-22 22:56:33',NULL,'31','Neutronenster',0),
	(1931,'2016-05-23 07:03:02','2016-05-23 07:03:02',NULL,'1','JUIST',1),
	(1932,'2016-05-23 07:03:02','2016-05-23 07:03:02',NULL,'1','ONJUIST',0),
	(1933,'2016-05-23 07:03:03','2016-05-23 07:03:03',NULL,'2','ONJUIST',1),
	(1934,'2016-05-23 07:03:03','2016-05-23 07:03:03',NULL,'2','JUIST',0),
	(1935,'2016-05-23 07:03:03','2016-05-23 07:03:03',NULL,'3','JUIST',1),
	(1936,'2016-05-23 07:03:03','2016-05-23 07:03:03',NULL,'3','ONJUIST',0),
	(1937,'2016-05-23 07:03:03','2016-05-23 07:03:03',NULL,'4','ONJUIST',1),
	(1938,'2016-05-23 07:03:03','2016-05-23 07:03:03',NULL,'4','JUIST',0),
	(1939,'2016-05-23 07:03:03','2016-05-23 07:03:03',NULL,'5','JUIST',1),
	(1940,'2016-05-23 07:03:03','2016-05-23 07:03:03',NULL,'5','ONJUIST',0),
	(1941,'2016-05-23 07:03:03','2016-05-23 07:03:03',NULL,'6','ONJUIST',1),
	(1942,'2016-05-23 07:03:03','2016-05-23 07:03:03',NULL,'6','JUIST',0),
	(1943,'2016-05-23 07:03:03','2016-05-23 07:03:03',NULL,'7','JUIST',1),
	(1944,'2016-05-23 07:03:03','2016-05-23 07:03:03',NULL,'7','ONJUIST',0),
	(1945,'2016-05-23 07:03:03','2016-05-23 07:03:03',NULL,'8','ONJUIST',1),
	(1946,'2016-05-23 07:03:03','2016-05-23 07:03:03',NULL,'8','JUIST',0),
	(1947,'2016-05-23 07:03:03','2016-05-23 07:03:03',NULL,'9','ONJUIST',1),
	(1948,'2016-05-23 07:03:03','2016-05-23 07:03:03',NULL,'9','JUIST',0),
	(1949,'2016-05-23 07:03:03','2016-05-23 07:03:03',NULL,'10','JUIST',1),
	(1950,'2016-05-23 07:03:03','2016-05-23 07:03:03',NULL,'10','ONJUIST',0),
	(1951,'2016-05-23 08:42:33','2016-05-23 08:42:33',NULL,'1','bewegingszenuwcel',1),
	(1952,'2016-05-23 08:42:33','2016-05-23 08:42:33',NULL,'1','gevoelszenuwcel',0),
	(1953,'2016-05-23 08:42:33','2016-05-23 08:42:33',NULL,'1','schakelcel',0),
	(1954,'2016-05-23 08:56:50','2016-05-23 08:58:33','2016-05-23 08:58:33','1','ONJUIST',1),
	(1955,'2016-05-23 08:56:50','2016-05-23 08:58:33','2016-05-23 08:58:33','1','JUIST',0),
	(1956,'2016-05-23 08:56:50','2016-05-23 08:58:33','2016-05-23 08:58:33','2','JUIST',1),
	(1957,'2016-05-23 08:56:50','2016-05-23 08:58:33','2016-05-23 08:58:33','2','ONJUIST',0),
	(1958,'2016-05-23 08:56:50','2016-05-23 08:58:33','2016-05-23 08:58:33','3','JUIST',1),
	(1959,'2016-05-23 08:56:51','2016-05-23 08:58:33','2016-05-23 08:58:33','3','ONJUIST',0),
	(1960,'2016-05-23 08:56:51','2016-05-23 08:58:33','2016-05-23 08:58:33','4','JUIST',1),
	(1961,'2016-05-23 08:56:51','2016-05-23 08:58:33','2016-05-23 08:58:33','4','ONJUIST',0),
	(1962,'2016-05-23 08:56:51','2016-05-23 08:58:33','2016-05-23 08:58:33','5','ONJUIST',1),
	(1963,'2016-05-23 08:56:51','2016-05-23 08:58:33','2016-05-23 08:58:33','5','JUIST',0),
	(1964,'2016-05-23 08:58:33','2016-05-23 08:58:33',NULL,'1','ONJUIST',1),
	(1965,'2016-05-23 08:58:33','2016-05-23 08:58:33',NULL,'1','JUIST',0),
	(1966,'2016-05-23 08:58:33','2016-05-23 08:58:33',NULL,'2','JUIST',1),
	(1967,'2016-05-23 08:58:33','2016-05-23 08:58:33',NULL,'2','ONJUIST',0),
	(1968,'2016-05-23 08:58:33','2016-05-23 08:58:33',NULL,'3','JUIST',1),
	(1969,'2016-05-23 08:58:33','2016-05-23 08:58:33',NULL,'3','ONJUIST',0),
	(1970,'2016-05-23 08:58:33','2016-05-23 08:58:33',NULL,'4','JUIST',1),
	(1971,'2016-05-23 08:58:34','2016-05-23 08:58:34',NULL,'4','ONJUIST',0),
	(1972,'2016-05-23 08:58:34','2016-05-23 08:58:34',NULL,'5','ONJUIST',1),
	(1973,'2016-05-23 08:58:34','2016-05-23 08:58:34',NULL,'5','JUIST',0),
	(1974,'2016-05-23 09:00:35','2016-05-23 09:09:13','2016-05-23 09:09:13','1','ONJUIST',1),
	(1975,'2016-05-23 09:00:35','2016-05-23 09:09:13','2016-05-23 09:09:13','1','JUIST',0),
	(1976,'2016-05-23 09:09:13','2016-05-23 09:14:28','2016-05-23 09:14:28','1','ONJUIST',1),
	(1977,'2016-05-23 09:09:13','2016-05-23 09:14:28','2016-05-23 09:14:28','1','JUIST',0),
	(1978,'2016-05-23 09:09:13','2016-05-23 09:14:28','2016-05-23 09:14:28','2','JUIST',1),
	(1979,'2016-05-23 09:09:13','2016-05-23 09:14:28','2016-05-23 09:14:28','2','ONJUIST',0),
	(1980,'2016-05-23 09:09:13','2016-05-23 09:14:28','2016-05-23 09:14:28','3','JUIST',1),
	(1981,'2016-05-23 09:09:13','2016-05-23 09:14:28','2016-05-23 09:14:28','3','ONJUIST',0),
	(1982,'2016-05-23 09:09:13','2016-05-23 09:14:28','2016-05-23 09:14:28','4','ONJUIST',1),
	(1983,'2016-05-23 09:09:13','2016-05-23 09:14:28','2016-05-23 09:14:28','4','JUIST',0),
	(1984,'2016-05-23 09:14:28','2016-05-23 09:14:28',NULL,'1','ONJUIST',1),
	(1985,'2016-05-23 09:14:28','2016-05-23 09:14:28',NULL,'1','JUIST',0),
	(1986,'2016-05-23 09:14:28','2016-05-23 09:14:28',NULL,'2','JUIST',1),
	(1987,'2016-05-23 09:14:28','2016-05-23 09:14:28',NULL,'2','ONJUIST',0),
	(1988,'2016-05-23 09:14:28','2016-05-23 09:14:28',NULL,'3','JUIST',1),
	(1989,'2016-05-23 09:14:28','2016-05-23 09:14:28',NULL,'3','ONJUIST',0),
	(1990,'2016-05-23 09:14:28','2016-05-23 09:14:28',NULL,'4','ONJUIST',1),
	(1991,'2016-05-23 09:14:28','2016-05-23 09:14:28',NULL,'4','JUIST',0),
	(1992,'2016-05-23 09:54:00','2016-05-23 09:54:00',NULL,'1','stijgt',1),
	(1993,'2016-05-23 09:54:00','2016-05-23 09:54:00',NULL,'1','daalt',0),
	(1994,'2016-05-23 09:54:00','2016-05-23 09:54:00',NULL,'2','glycogeen',1),
	(1995,'2016-05-23 09:54:00','2016-05-23 09:54:00',NULL,'2','glucose',0),
	(1996,'2016-05-23 09:54:00','2016-05-23 09:54:00',NULL,'3','glucose',1),
	(1997,'2016-05-23 09:54:00','2016-05-23 09:54:00',NULL,'3','glycogeen',0),
	(1998,'2016-06-24 14:09:10','2016-06-27 21:24:54','2016-06-27 21:24:54','1','hoogpercentielstrategie',1),
	(1999,'2016-06-24 14:09:11','2016-06-27 21:24:54','2016-06-27 21:24:54','1','laagpercentielstrategie',0),
	(2000,'2016-06-24 14:09:11','2016-06-27 21:24:54','2016-06-27 21:24:54','1','gemiddelde strategie',0),
	(2001,'2016-06-24 14:09:11','2016-06-27 21:24:54','2016-06-27 21:24:54','1','verstelbaarheidsstrategie',0),
	(2002,'2016-06-24 14:09:11','2016-06-27 21:24:54','2016-06-27 21:24:54','1','variantenstrategie',0),
	(2003,'2016-06-24 14:09:11','2016-06-27 21:24:54','2016-06-27 21:24:54','2','laagpercentielstrategie',1),
	(2004,'2016-06-24 14:09:11','2016-06-27 21:24:54','2016-06-27 21:24:54','2','hoogpercentielstrategie',0),
	(2005,'2016-06-24 14:09:11','2016-06-27 21:24:54','2016-06-27 21:24:54','2','gemiddelde strategie',0),
	(2006,'2016-06-24 14:09:11','2016-06-27 21:24:54','2016-06-27 21:24:54','2','verstelbaarheidsstrategie',0),
	(2007,'2016-06-24 14:09:11','2016-06-27 21:24:54','2016-06-27 21:24:54','2','variantenstrategie',0),
	(2008,'2016-06-24 14:09:11','2016-06-27 21:24:54','2016-06-27 21:24:54','3','gemiddelde strategie',1),
	(2009,'2016-06-24 14:09:11','2016-06-27 21:24:54','2016-06-27 21:24:54','3','hoogpercentielstrategie',0),
	(2010,'2016-06-24 14:09:11','2016-06-27 21:24:54','2016-06-27 21:24:54','3','laagpercentielstrategie',0),
	(2011,'2016-06-24 14:09:11','2016-06-27 21:24:54','2016-06-27 21:24:54','3','verstelbaarheidsstrategie',0),
	(2012,'2016-06-24 14:09:11','2016-06-27 21:24:54','2016-06-27 21:24:54','3','variantenstrategie',0),
	(2013,'2016-06-24 14:09:11','2016-06-27 21:24:54','2016-06-27 21:24:54','4','laagpercentielstrategie',1),
	(2014,'2016-06-24 14:09:11','2016-06-27 21:24:54','2016-06-27 21:24:54','4','hoogpercentielstrategie',0),
	(2015,'2016-06-24 14:09:11','2016-06-27 21:24:54','2016-06-27 21:24:54','4','gemiddelde strategie',0),
	(2016,'2016-06-24 14:09:11','2016-06-27 21:24:54','2016-06-27 21:24:54','4','verstelbaarheidsstrategie',0),
	(2017,'2016-06-24 14:09:11','2016-06-27 21:24:54','2016-06-27 21:24:54','4','variantenstrategie',0),
	(2018,'2016-06-24 14:09:11','2016-06-27 21:24:54','2016-06-27 21:24:54','5','laagpercentielstrategie',1),
	(2019,'2016-06-24 14:09:11','2016-06-27 21:24:54','2016-06-27 21:24:54','5','hoogpercentielstrategie',0),
	(2020,'2016-06-24 14:09:12','2016-06-27 21:24:54','2016-06-27 21:24:54','5','gemiddelde strategie',0),
	(2021,'2016-06-24 14:09:12','2016-06-27 21:24:54','2016-06-27 21:24:54','5','verstelbaarheidsstrategie',0),
	(2022,'2016-06-24 14:09:12','2016-06-27 21:24:54','2016-06-27 21:24:54','5','variantenstrategie',0),
	(2023,'2016-06-24 14:47:51','2016-06-24 14:47:51',NULL,'1','stoommachine',1),
	(2024,'2016-06-24 14:47:51','2016-06-24 14:47:51',NULL,'2','vuur',1),
	(2025,'2016-06-24 14:47:51','2016-06-24 14:47:51',NULL,'3','brandstof',1),
	(2026,'2016-06-24 14:49:36','2016-06-24 14:49:36',NULL,'1','kolen',1),
	(2027,'2016-06-24 14:49:36','2016-06-24 14:49:36',NULL,'2','olie of gas',1),
	(2028,'2016-06-24 14:49:36','2016-06-24 14:49:36',NULL,'3','gas of olie',1),
	(2029,'2016-06-24 14:56:36','2016-06-24 14:56:36',NULL,'1','4,19',1),
	(2030,'2016-06-27 21:24:54','2016-06-27 21:24:54',NULL,'1','hoogpercentielstrategie',1),
	(2031,'2016-06-27 21:24:54','2016-06-27 21:24:54',NULL,'1','laagpercentielstrategie',0),
	(2032,'2016-06-27 21:24:54','2016-06-27 21:24:54',NULL,'1','gemiddelde strategie',0),
	(2033,'2016-06-27 21:24:54','2016-06-27 21:24:54',NULL,'1','verstelbaarheidsstrategie',0),
	(2034,'2016-06-27 21:24:54','2016-06-27 21:24:54',NULL,'1','variantenstrategie',0),
	(2035,'2016-06-27 21:24:54','2016-06-27 21:24:54',NULL,'2','laagpercentielstrategie',1),
	(2036,'2016-06-27 21:24:54','2016-06-27 21:24:54',NULL,'2','hoogpercentielstrategie',0),
	(2037,'2016-06-27 21:24:54','2016-06-27 21:24:54',NULL,'2','gemiddelde strategie',0),
	(2038,'2016-06-27 21:24:54','2016-06-27 21:24:54',NULL,'2','verstelbaarheidsstrategie',0),
	(2039,'2016-06-27 21:24:54','2016-06-27 21:24:54',NULL,'2','variantenstrategie',0),
	(2040,'2016-06-27 21:24:54','2016-06-27 21:24:54',NULL,'3','gemiddelde strategie',1),
	(2041,'2016-06-27 21:24:54','2016-06-27 21:24:54',NULL,'3','hoogpercentielstrategie',0),
	(2042,'2016-06-27 21:24:54','2016-06-27 21:24:54',NULL,'3','laagpercentielstrategie',0),
	(2043,'2016-06-27 21:24:55','2016-06-27 21:24:55',NULL,'3','verstelbaarheidsstrategie',0),
	(2044,'2016-06-27 21:24:55','2016-06-27 21:24:55',NULL,'3','variantenstrategie',0),
	(2045,'2016-06-27 21:24:55','2016-06-27 21:24:55',NULL,'4','laagpercentielstrategie',1),
	(2046,'2016-06-27 21:24:55','2016-06-27 21:24:55',NULL,'4','hoogpercentielstrategie',0),
	(2047,'2016-06-27 21:24:55','2016-06-27 21:24:55',NULL,'4','gemiddelde strategie',0),
	(2048,'2016-06-27 21:24:55','2016-06-27 21:24:55',NULL,'4','verstelbaarheidsstrategie',0),
	(2049,'2016-06-27 21:24:55','2016-06-27 21:24:55',NULL,'4','variantenstrategie',0),
	(2050,'2016-06-27 21:24:55','2016-06-27 21:24:55',NULL,'5','laagpercentielstrategie',1),
	(2051,'2016-06-27 21:24:55','2016-06-27 21:24:55',NULL,'5','hoogpercentielstrategie',0),
	(2052,'2016-06-27 21:24:55','2016-06-27 21:24:55',NULL,'5','gemiddelde strategie',0),
	(2053,'2016-06-27 21:24:55','2016-06-27 21:24:55',NULL,'5','verstelbaarheidsstrategie',0),
	(2054,'2016-06-27 21:24:55','2016-06-27 21:24:55',NULL,'5','variantenstrategie',0),
	(2055,'2016-06-29 09:51:11','2016-06-29 09:51:11',NULL,'1','simpel',1),
	(2056,'2016-06-29 09:51:12','2016-06-29 09:51:12',NULL,'1','gh',0),
	(2057,'2016-06-29 09:51:12','2016-06-29 09:51:12',NULL,'2','statistieken',1),
	(2058,'2016-06-29 09:51:12','2016-06-29 09:51:12',NULL,'2','hh',0),
	(2059,'2016-06-29 09:52:11','2016-06-29 09:52:11',NULL,'1','maak',1),
	(2060,'2016-07-14 11:30:48','2016-07-14 11:30:48',NULL,'1','test',1),
	(2061,'2016-07-14 11:30:48','2016-07-14 11:30:48',NULL,'1','a',0),
	(2062,'2016-07-14 11:30:48','2016-07-14 11:30:48',NULL,'1','b',0),
	(2063,'2016-07-14 11:30:48','2016-07-14 11:30:48',NULL,'1','c',0),
	(2064,'2016-07-14 11:30:48','2016-07-14 11:30:48',NULL,'1','d',0),
	(2065,'2016-07-14 11:30:48','2016-07-14 11:30:48',NULL,'1','e',0),
	(2066,'2016-07-14 11:30:48','2016-07-14 11:30:48',NULL,'1','f',0),
	(2067,'2016-07-14 11:30:48','2016-07-14 11:30:48',NULL,'1','g',0),
	(2068,'2016-07-14 11:30:48','2016-07-14 11:30:48',NULL,'1','h',0),
	(2069,'2016-07-14 11:30:48','2016-07-14 11:30:48',NULL,'1','i',0),
	(2070,'2016-08-18 15:05:47','2016-08-18 15:05:47',NULL,'1','test',1),
	(2071,'2016-09-29 14:32:42','2016-09-29 14:32:42',NULL,'1','persoonsvorm',1),
	(2072,'2016-09-29 14:32:42','2016-09-29 14:32:42',NULL,'1','infinitief',0),
	(2073,'2016-09-29 14:32:42','2016-09-29 14:32:42',NULL,'1','voltooid deelwoord',0),
	(2074,'2017-09-29 12:56:11','2017-09-29 12:56:11',NULL,'1','weer',1),
	(2075,'2017-09-29 12:56:11','2017-09-29 12:56:11',NULL,'2','bos',1),
	(2076,'2017-09-29 13:07:29','2017-09-29 13:07:29',NULL,'1','trap',1),
	(2077,'2017-09-29 13:07:29','2017-09-29 13:07:29',NULL,'1','dakpan',0),
	(2078,'2017-09-29 13:07:29','2017-09-29 13:07:29',NULL,'1','computer',0),
	(2079,'2017-09-29 13:07:29','2017-09-29 13:07:29',NULL,'2','baard',1),
	(2080,'2017-09-29 13:07:30','2017-09-29 13:07:30',NULL,'2','hoofddeksel',0),
	(2081,'2017-09-29 13:07:30','2017-09-29 13:07:30',NULL,'2','arm',0),
	(2082,'2017-11-01 08:40:34','2017-11-01 08:40:34',NULL,'1','Rotterdam',1),
	(2083,'2017-11-01 08:40:34','2017-11-01 08:40:34',NULL,'2','tram',1),
	(2084,'2017-11-01 08:40:34','2017-11-01 08:40:34',NULL,'3','kg',1),
	(2085,'2017-11-01 08:45:24','2017-11-01 08:45:24',NULL,'1','hard',1),
	(2086,'2017-11-01 08:45:24','2017-11-01 08:45:24',NULL,'2','zacht',1),
	(2087,'2017-11-01 08:45:25','2017-11-01 08:45:25',NULL,'3',' roet',1),
	(2088,'2017-11-01 08:45:25','2017-11-01 08:45:25',NULL,'4','goed',1),
	(2089,'2017-11-01 08:50:14','2017-11-01 08:50:14',NULL,'1','car',1),
	(2090,'2017-11-01 08:50:14','2017-11-01 08:50:14',NULL,'2','car',1),
	(2091,'2017-11-01 08:50:39','2017-11-01 08:50:39',NULL,'1','kip',1),
	(2092,'2017-11-01 08:50:39','2017-11-01 08:50:39',NULL,'1','boomstam',0),
	(2093,'2017-11-01 08:50:39','2017-11-01 08:50:39',NULL,'1','vos',0),
	(2094,'2017-11-01 08:50:39','2017-11-01 08:50:39',NULL,'1','taart',0),
	(2095,'2017-11-01 09:11:32','2017-11-23 09:46:37','2017-11-23 09:46:37','1','Lewis Hamilton',1),
	(2096,'2017-11-01 09:11:32','2017-11-23 09:46:37','2017-11-23 09:46:37','1','Max Verstappen',0),
	(2097,'2017-11-01 09:11:32','2017-11-23 09:46:37','2017-11-23 09:46:37','1','Jarno Trulli',0),
	(2098,'2017-11-01 09:11:32','2017-11-23 09:46:37','2017-11-23 09:46:37','2','Lewis Hamilton',1),
	(2099,'2017-11-01 09:11:32','2017-11-23 09:46:37','2017-11-23 09:46:37','2','Max Verstappen',0),
	(2100,'2017-11-01 09:11:32','2017-11-23 09:46:37','2017-11-23 09:46:37','2','Jarno Trulli',0),
	(2101,'2017-11-01 09:11:32','2017-11-23 09:46:37','2017-11-23 09:46:37','3','Ayrton Senna',1),
	(2102,'2017-11-01 09:11:32','2017-11-23 09:46:37','2017-11-23 09:46:37','3','Arnold Schwarzenegger',0),
	(2103,'2017-11-01 09:11:33','2017-11-23 09:46:37','2017-11-23 09:46:37','3','Genghis Khan',0),
	(2104,'2017-11-01 09:11:33','2017-11-23 09:46:37','2017-11-23 09:46:37','3','Adolf Hitler',0),
	(2105,'2017-11-01 09:11:33','2017-11-23 09:46:37','2017-11-23 09:46:37','4','four',1),
	(2106,'2017-11-01 09:11:33','2017-11-23 09:46:37','2017-11-23 09:46:37','4','three',0),
	(2107,'2017-11-01 09:11:33','2017-11-23 09:46:37','2017-11-23 09:46:37','4','five',0),
	(2108,'2017-11-01 09:11:33','2017-11-23 09:46:37','2017-11-23 09:46:37','4','eight',0),
	(2109,'2017-11-17 15:05:07','2017-11-17 15:05:07',NULL,'1','vorm',1),
	(2110,'2017-11-17 15:05:07','2017-11-17 15:05:07',NULL,'2','geel',1),
	(2111,'2017-11-17 15:05:07','2017-11-17 15:05:07',NULL,'3','bruin',1),
	(2112,'2017-11-17 15:14:18','2017-11-17 15:14:18',NULL,'1','BMW',1),
	(2113,'2017-11-17 15:14:18','2017-11-17 15:14:18',NULL,'1','Fiat',0),
	(2114,'2017-11-17 15:14:19','2017-11-17 15:14:19',NULL,'1','Mercedes',0),
	(2115,'2017-11-17 15:14:19','2017-11-17 15:14:19',NULL,'1','Jaguar',0),
	(2116,'2017-11-17 15:14:19','2017-11-17 15:14:19',NULL,'1','Chevrolet Matiz',0),
	(2117,'2017-11-19 20:53:14','2017-11-19 20:53:14',NULL,'1','JUIST',1),
	(2118,'2017-11-19 20:53:15','2017-11-19 20:53:15',NULL,'1','ONJUIST',0),
	(2119,'2017-11-19 20:53:15','2017-11-19 20:53:15',NULL,'2','JUIST',1),
	(2120,'2017-11-19 20:53:15','2017-11-19 20:53:15',NULL,'2','ONJUIST',0),
	(2121,'2017-11-19 20:53:15','2017-11-19 20:53:15',NULL,'3','ONJUIST',1),
	(2122,'2017-11-19 20:53:15','2017-11-19 20:53:15',NULL,'3','JUIST',0),
	(2123,'2017-11-22 13:09:14','2017-11-22 13:09:14',NULL,'1','moi',1),
	(2124,'2017-11-22 13:09:14','2017-11-22 13:09:14',NULL,'2','présenter',1),
	(2125,'2017-11-22 13:11:56','2017-11-22 13:11:56',NULL,'1','parler',1),
	(2126,'2017-11-22 13:11:56','2017-11-22 13:11:56',NULL,'1','voire',0),
	(2127,'2017-11-22 13:11:56','2017-11-22 13:11:56',NULL,'1','aller',0),
	(2128,'2017-11-22 13:11:56','2017-11-22 13:11:56',NULL,'1','boire',0),
	(2129,'2017-11-22 13:11:56','2017-11-22 13:11:56',NULL,'2','Monsieur',1),
	(2130,'2017-11-22 13:11:56','2017-11-22 13:11:56',NULL,'2','Sir',0),
	(2131,'2017-11-22 13:11:56','2017-11-22 13:11:56',NULL,'2','Mister',0),
	(2132,'2017-11-23 09:46:37','2017-11-23 09:50:31','2017-11-23 09:50:31','1','Lewis Hamilton',1),
	(2133,'2017-11-23 09:46:38','2017-11-23 09:50:31','2017-11-23 09:50:31','1','Max Verstappen',0),
	(2134,'2017-11-23 09:46:38','2017-11-23 09:50:31','2017-11-23 09:50:31','1','Jarno Trulli',0),
	(2135,'2017-11-23 09:46:38','2017-11-23 09:50:31','2017-11-23 09:50:31','2','Lewis Hamilton',1),
	(2136,'2017-11-23 09:46:38','2017-11-23 09:50:31','2017-11-23 09:50:31','2','Max Verstappen',0),
	(2137,'2017-11-23 09:46:38','2017-11-23 09:50:31','2017-11-23 09:50:31','2','Jarno Trulli',0),
	(2138,'2017-11-23 09:46:38','2017-11-23 09:50:31','2017-11-23 09:50:31','3','Ayrton Senna',1),
	(2139,'2017-11-23 09:46:39','2017-11-23 09:50:31','2017-11-23 09:50:31','3','Arnold Schwarzenegger',0),
	(2140,'2017-11-23 09:46:39','2017-11-23 09:50:31','2017-11-23 09:50:31','3','Genghis Khan',0),
	(2141,'2017-11-23 09:46:39','2017-11-23 09:50:31','2017-11-23 09:50:31','3','Adolf Hitler',0),
	(2142,'2017-11-23 09:46:39','2017-11-23 09:50:31','2017-11-23 09:50:31','4','four',1),
	(2143,'2017-11-23 09:46:39','2017-11-23 09:50:31','2017-11-23 09:50:31','4','three',0),
	(2144,'2017-11-23 09:46:40','2017-11-23 09:50:31','2017-11-23 09:50:31','4','five',0),
	(2145,'2017-11-23 09:46:40','2017-11-23 09:50:31','2017-11-23 09:50:31','4','eight',0),
	(2146,'2017-11-23 09:50:31','2017-11-23 09:50:31',NULL,'1','Lewis Hamilton',1),
	(2147,'2017-11-23 09:50:31','2017-11-23 09:50:31',NULL,'1','Max Verstappen',0),
	(2148,'2017-11-23 09:50:32','2017-11-23 09:50:32',NULL,'1','Jarno Trulli',0),
	(2149,'2017-11-23 09:50:32','2017-11-23 09:50:32',NULL,'2','Lewis Hamilton',1),
	(2150,'2017-11-23 09:50:32','2017-11-23 09:50:32',NULL,'2','Max Verstappen',0),
	(2151,'2017-11-23 09:50:32','2017-11-23 09:50:32',NULL,'2','Jarno Trulli',0),
	(2152,'2017-11-23 09:50:32','2017-11-23 09:50:32',NULL,'3','Ayrton Senna',1),
	(2153,'2017-11-23 09:50:32','2017-11-23 09:50:32',NULL,'3','Arnold Schwarzenegger',0),
	(2154,'2017-11-23 09:50:32','2017-11-23 09:50:32',NULL,'3','Genghis Khan',0),
	(2155,'2017-11-23 09:50:32','2017-11-23 09:50:32',NULL,'3','Adolf Hitler',0),
	(2156,'2017-11-23 09:50:32','2017-11-23 09:50:32',NULL,'4','four',1),
	(2157,'2017-11-23 09:50:32','2017-11-23 09:50:32',NULL,'4','three',0),
	(2158,'2017-11-23 09:50:32','2017-11-23 09:50:32',NULL,'4','five',0),
	(2159,'2017-11-23 09:50:32','2017-11-23 09:50:32',NULL,'4','eight',0),
	(2160,'2017-11-30 16:51:32','2017-11-30 16:53:00','2017-11-30 16:53:00','1','tomaat',1),
	(2161,'2017-11-30 16:51:33','2017-11-30 16:53:00','2017-11-30 16:53:00','2','geel',1),
	(2162,'2017-11-30 16:53:00','2017-11-30 16:53:00',NULL,'1','tomaatje',1),
	(2163,'2017-11-30 16:53:00','2017-11-30 16:53:00',NULL,'2','gelig',1),
	(2164,'2017-11-30 17:50:22','2017-11-30 17:50:22',NULL,'1','groen',1),
	(2165,'2017-11-30 17:50:22','2017-11-30 17:50:22',NULL,'1','rood',0),
	(2166,'2017-11-30 17:50:22','2017-11-30 17:50:22',NULL,'1','paars',0),
	(2167,'2017-11-30 17:50:22','2017-11-30 17:50:22',NULL,'2','roze',1),
	(2168,'2017-11-30 17:50:22','2017-11-30 17:50:22',NULL,'2','paars',0),
	(2169,'2017-11-30 17:50:22','2017-11-30 17:50:22',NULL,'2','goud',0),
	(2170,'2017-11-30 17:50:22','2017-11-30 17:50:22',NULL,'3','grijs',1),
	(2171,'2017-11-30 17:50:22','2017-11-30 17:50:22',NULL,'3','wit',0),
	(2172,'2017-11-30 17:54:01','2017-11-30 17:54:17','2017-11-30 17:54:17','1','mooi',1),
	(2173,'2017-11-30 17:54:02','2017-11-30 17:54:17','2017-11-30 17:54:17','1','lelijk',0),
	(2174,'2017-11-30 17:54:02','2017-11-30 17:54:17','2017-11-30 17:54:17','1','luid',0),
	(2175,'2017-11-30 17:54:17','2017-11-30 17:54:17',NULL,'1','mooi',1),
	(2176,'2017-11-30 17:54:17','2017-11-30 17:54:17',NULL,'1','lelijk',0),
	(2177,'2017-11-30 17:54:17','2017-11-30 17:54:17',NULL,'1','luidddd',0),
	(2178,'2017-11-30 18:43:24','2017-11-30 18:43:24',NULL,'1','geel',1),
	(2179,'2017-11-30 18:43:25','2017-11-30 18:43:25',NULL,'2','groen',1),
	(2180,'2017-12-19 12:20:52','2017-12-19 12:20:52',NULL,'1','-2',1),
	(2181,'2017-12-19 12:20:52','2017-12-19 12:20:52',NULL,'2','-3',1),
	(2182,'2017-12-19 12:20:52','2017-12-19 12:20:52',NULL,'3','0',1),
	(2183,'2017-12-19 12:20:52','2017-12-19 12:20:52',NULL,'4','1',1),
	(2184,'2017-12-19 12:33:41','2017-12-19 12:33:41',NULL,'1','1',1),
	(2185,'2017-12-19 12:37:53','2017-12-19 12:37:53',NULL,'1','-2',1),
	(2186,'2017-12-19 12:37:53','2017-12-19 12:37:53',NULL,'2','-3',1),
	(2187,'2017-12-19 12:37:53','2017-12-19 12:37:53',NULL,'3','0',1),
	(2188,'2017-12-19 12:37:53','2017-12-19 12:37:53',NULL,'4','1',1),
	(2189,'2017-12-19 12:42:12','2017-12-21 07:26:04','2017-12-21 07:26:04','1','5',1),
	(2190,'2017-12-19 12:42:12','2017-12-21 07:26:04','2017-12-21 07:26:04','2','2',1),
	(2191,'2017-12-19 12:42:12','2017-12-21 07:26:04','2017-12-21 07:26:04','3','2.5',1),
	(2192,'2017-12-19 12:44:27','2017-12-21 07:26:18','2017-12-21 07:26:18','1','1',1),
	(2193,'2017-12-19 12:44:27','2017-12-21 07:26:18','2017-12-21 07:26:18','2','2.5',1),
	(2194,'2017-12-19 12:56:31','2017-12-19 12:56:31',NULL,'1','3000',1),
	(2195,'2017-12-19 13:00:42','2017-12-19 13:00:42',NULL,'1','0',1),
	(2196,'2017-12-19 13:00:42','2017-12-19 13:00:42',NULL,'2','3000',1),
	(2197,'2017-12-19 13:00:42','2017-12-19 13:00:42',NULL,'3','5',1),
	(2198,'2017-12-19 13:00:42','2017-12-19 13:00:42',NULL,'4','2000',1),
	(2199,'2017-12-19 13:03:18','2017-12-19 13:03:18',NULL,'1','-1000',1),
	(2200,'2017-12-19 13:03:18','2017-12-19 13:03:18',NULL,'2','5',1),
	(2201,'2017-12-19 13:03:18','2017-12-19 13:03:18',NULL,'3','-200',1),
	(2202,'2017-12-19 13:05:50','2017-12-19 13:05:50',NULL,'1','3000',1),
	(2203,'2017-12-19 13:07:11','2017-12-19 13:07:11',NULL,'1','0',1),
	(2204,'2017-12-19 13:07:11','2017-12-19 13:07:11',NULL,'2','3000',1),
	(2205,'2017-12-19 13:07:11','2017-12-19 13:07:11',NULL,'3','5',1),
	(2206,'2017-12-19 13:07:11','2017-12-19 13:07:11',NULL,'4','2000',1),
	(2207,'2017-12-19 13:08:41','2017-12-19 13:08:41',NULL,'1','-1000',1),
	(2208,'2017-12-19 13:08:41','2017-12-19 13:08:41',NULL,'2','5',1),
	(2209,'2017-12-19 13:08:41','2017-12-19 13:08:41',NULL,'3','-2000',1),
	(2210,'2017-12-19 13:11:12','2017-12-19 13:11:12',NULL,'1','3000',1),
	(2211,'2017-12-19 13:11:12','2017-12-19 13:11:12',NULL,'2','200',1),
	(2212,'2017-12-19 13:28:50','2017-12-19 13:53:56','2017-12-19 13:53:56','1','4',1),
	(2213,'2017-12-19 13:28:50','2017-12-19 13:53:56','2017-12-19 13:53:56','2','2',1),
	(2214,'2017-12-19 13:46:48','2017-12-19 13:46:48',NULL,'1','-4',1),
	(2215,'2017-12-19 13:46:48','2017-12-19 13:46:48',NULL,'2','3',1),
	(2216,'2017-12-19 13:47:18','2017-12-19 13:47:18',NULL,'1','-4',1),
	(2217,'2017-12-19 13:47:18','2017-12-19 13:47:18',NULL,'2','3',1),
	(2218,'2017-12-19 13:51:31','2017-12-21 07:25:32','2017-12-21 07:25:32','1','4',1),
	(2219,'2017-12-19 13:51:31','2017-12-21 07:25:32','2017-12-21 07:25:32','2','2.5',1),
	(2220,'2017-12-19 13:53:56','2017-12-19 13:53:56',NULL,'1','0',1),
	(2221,'2017-12-19 13:53:56','2017-12-19 13:53:56',NULL,'2','2',1),
	(2222,'2017-12-21 07:25:32','2017-12-21 07:25:32',NULL,'1','4',1),
	(2223,'2017-12-21 07:25:32','2017-12-21 07:25:32',NULL,'2','-2,5',1),
	(2224,'2017-12-21 07:26:04','2017-12-21 07:26:04',NULL,'1','5',1),
	(2225,'2017-12-21 07:26:04','2017-12-21 07:26:04',NULL,'2','2',1),
	(2226,'2017-12-21 07:26:04','2017-12-21 07:26:04',NULL,'3','2,5',1),
	(2227,'2017-12-21 07:26:18','2017-12-21 07:26:18',NULL,'1','1',1),
	(2228,'2017-12-21 07:26:18','2017-12-21 07:26:18',NULL,'2','2,5',1),
	(2229,'2018-01-02 14:08:13','2018-01-02 14:08:13',NULL,'1','a',1),
	(2230,'2018-01-02 14:08:13','2018-01-02 14:08:13',NULL,'2','b',1),
	(2231,'2018-01-02 14:14:37','2018-01-02 14:14:37',NULL,'1','goed',1),
	(2232,'2018-01-02 14:14:37','2018-01-02 14:14:37',NULL,'1','fout',0),
	(2233,'2018-01-02 14:15:19','2018-01-02 14:15:19',NULL,'1','a',1),
	(2234,'2018-01-02 14:15:19','2018-01-02 14:15:19',NULL,'2','b',1),
	(2235,'2018-01-02 14:15:19','2018-01-02 14:15:19',NULL,'3','c',1),
	(2236,'2018-01-02 14:16:15','2018-01-02 14:16:15',NULL,'1','goed',1),
	(2237,'2018-01-02 14:16:15','2018-01-02 14:16:15',NULL,'1','fout',0),
	(2238,'2018-01-02 14:16:15','2018-01-02 14:16:15',NULL,'1','wat',0),
	(2239,'2018-01-02 16:35:23','2018-01-02 16:35:23',NULL,'1','gatentekst',1),
	(2240,'2018-01-02 16:35:23','2018-01-02 16:35:23',NULL,'2','gaten',1),
	(2241,'2018-01-02 16:35:23','2018-01-02 16:35:23',NULL,'3','getest',1),
	(2242,'2018-01-02 16:39:18','2018-01-02 16:39:18',NULL,'1','selectievraag',1),
	(2243,'2018-01-02 16:39:18','2018-01-02 16:39:18',NULL,'1','open vraag',0),
	(2244,'2018-01-02 16:39:18','2018-01-02 16:39:18',NULL,'1','multiple choice',0),
	(2245,'2018-01-02 16:39:18','2018-01-02 16:39:18',NULL,'2','testen',1),
	(2246,'2018-01-02 16:39:18','2018-01-02 16:39:18',NULL,'2','eten',0),
	(2247,'2018-01-02 16:39:18','2018-01-02 16:39:18',NULL,'2','drinken',0),
	(2248,'2018-01-02 16:44:06','2018-01-02 16:44:06',NULL,'1','gatentekst',1),
	(2249,'2018-01-02 16:44:06','2018-01-02 16:44:06',NULL,'2','gaten',1),
	(2250,'2018-01-02 16:44:06','2018-01-02 16:44:06',NULL,'3','getest',1),
	(2251,'2018-01-02 16:44:06','2018-01-02 16:44:06',NULL,'4','toegevoegd',1),
	(2252,'2018-01-02 16:54:12','2018-01-02 16:54:12',NULL,'1','selectievraag',1),
	(2253,'2018-01-02 16:54:13','2018-01-02 16:54:13',NULL,'1','open vraag',0),
	(2254,'2018-01-02 16:54:13','2018-01-02 16:54:13',NULL,'1','multiple choice',0),
	(2255,'2018-01-02 16:54:13','2018-01-02 16:54:13',NULL,'2','testen',1),
	(2256,'2018-01-02 16:54:13','2018-01-02 16:54:13',NULL,'2','eten',0),
	(2257,'2018-01-02 16:54:13','2018-01-02 16:54:13',NULL,'2','drinken',0),
	(2258,'2018-01-02 16:54:13','2018-01-02 16:54:13',NULL,'3','hout',1),
	(2259,'2018-01-02 16:54:13','2018-01-02 16:54:13',NULL,'3','staal',0),
	(2260,'2018-01-02 16:54:13','2018-01-02 16:54:13',NULL,'3','banaan',0),
	(2261,'2018-01-03 14:01:24','2018-01-03 14:01:24',NULL,'1','gedaagd',1),
	(2262,'2018-01-03 14:01:24','2018-01-03 14:01:24',NULL,'2','weinig',1),
	(2263,'2018-01-03 14:01:24','2018-01-03 14:01:24',NULL,'3','liedje',1),
	(2264,'2018-01-03 14:15:59','2018-01-03 14:15:59',NULL,'1','Spotify',1),
	(2265,'2018-01-03 14:15:59','2018-01-03 14:15:59',NULL,'1','Apple',0),
	(2266,'2018-01-03 14:15:59','2018-01-03 14:15:59',NULL,'1','EU',0),
	(2267,'2018-01-03 14:15:59','2018-01-03 14:15:59',NULL,'2','uitgever',1),
	(2268,'2018-01-03 14:15:59','2018-01-03 14:15:59',NULL,'2','maffiabaas',0),
	(2269,'2018-01-03 14:15:59','2018-01-03 14:15:59',NULL,'2','uitbuiter',0),
	(2270,'2018-01-03 14:15:59','2018-01-03 14:15:59',NULL,'3','betaald',1),
	(2271,'2018-01-03 14:15:59','2018-01-03 14:15:59',NULL,'3','afgedragen',0),
	(2272,'2018-01-03 14:15:59','2018-01-03 14:15:59',NULL,'3','beschermgeld',0),
	(2273,'2018-01-03 14:15:59','2018-01-03 14:15:59',NULL,'4','uitgever',1),
	(2274,'2018-01-03 14:15:59','2018-01-03 14:15:59',NULL,'4','hypocriete teringlijers',0),
	(2275,'2018-01-03 14:15:59','2018-01-03 14:15:59',NULL,'4','fossiele bedrijven die hun bestaansrecht enkel te danken hebben aan het gelobby bij overheden om iedere vooruitgang tegen te gaan om hun cashcow te beschermen',0),
	(2276,'2018-01-03 16:56:20','2018-01-03 16:56:20',NULL,'1','Spotify',1),
	(2277,'2018-01-03 16:56:20','2018-01-03 16:56:20',NULL,'1','Apple',0),
	(2278,'2018-01-03 16:56:20','2018-01-03 16:56:20',NULL,'1','EU',0),
	(2279,'2018-01-03 16:56:20','2018-01-03 16:56:20',NULL,'2','uitgever',1),
	(2280,'2018-01-03 16:56:20','2018-01-03 16:56:20',NULL,'2','maffiabaas',0),
	(2281,'2018-01-03 16:56:20','2018-01-03 16:56:20',NULL,'2','uitbuiter',0),
	(2282,'2018-01-03 16:56:20','2018-01-03 16:56:20',NULL,'3','betaald',1),
	(2283,'2018-01-03 16:56:20','2018-01-03 16:56:20',NULL,'3','afgedragen',0),
	(2284,'2018-01-03 16:56:20','2018-01-03 16:56:20',NULL,'3','beschermgeld',0),
	(2285,'2018-01-03 16:56:20','2018-01-03 16:56:20',NULL,'4','uitgever',1),
	(2286,'2018-01-03 16:56:20','2018-01-03 16:56:20',NULL,'4','hypocriete teringlijers',0),
	(2287,'2018-01-03 16:56:20','2018-01-03 16:56:20',NULL,'4','fossiele bedrijven die hun bestaansrecht enkel te danken hebben aan het gelobby bij overheden om iedere vooruitgang tegen te gaan om hun cashcow te beschermen',0),
	(2288,'2018-01-03 17:11:13','2018-01-03 17:11:13',NULL,'1','Spotify',1),
	(2289,'2018-01-03 17:11:13','2018-01-03 17:11:13',NULL,'1','Apple',0),
	(2290,'2018-01-03 17:11:13','2018-01-03 17:11:13',NULL,'1','EU',0),
	(2291,'2018-01-03 17:11:13','2018-01-03 17:11:13',NULL,'2','uitgever',1),
	(2292,'2018-01-03 17:11:13','2018-01-03 17:11:13',NULL,'2','maffiabaas',0),
	(2293,'2018-01-03 17:11:13','2018-01-03 17:11:13',NULL,'2','uitbuiter',0),
	(2294,'2018-01-03 17:11:13','2018-01-03 17:11:13',NULL,'3','betaald',1),
	(2295,'2018-01-03 17:11:13','2018-01-03 17:11:13',NULL,'3','afgedragen',0),
	(2296,'2018-01-03 17:11:14','2018-01-03 17:11:14',NULL,'3','beschermgeld',0),
	(2297,'2018-01-03 17:11:14','2018-01-03 17:11:14',NULL,'4','ontvangen',1),
	(2298,'2018-01-03 17:11:14','2018-01-03 17:11:14',NULL,'4','weggeven',0),
	(2299,'2018-01-03 17:11:14','2018-01-03 17:11:14',NULL,'5','uitgever',1),
	(2300,'2018-01-03 17:11:14','2018-01-03 17:11:14',NULL,'5','hypocriete teringlijers',0),
	(2301,'2018-01-03 17:11:14','2018-01-03 17:11:14',NULL,'5','fossiele bedrijven die hun bestaansrecht enkel te danken hebben aan het gelobby bij overheden om iedere vooruitgang tegen te gaan om hun cashcow te beschermen',0);

/*!40000 ALTER TABLE `completion_question_answers_5_1_2018` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table completion_questions
# ------------------------------------------------------------

DROP TABLE IF EXISTS `completion_questions`;

CREATE TABLE `completion_questions` (
  `id` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `subtype` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `rating_method` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_completion_questions_questions1_idx` (`id`),
  CONSTRAINT `fk_completion_questions_questions1` FOREIGN KEY (`id`) REFERENCES `questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `completion_questions`;

LOCK TABLES `completion_questions` WRITE;
/*!40000 ALTER TABLE `completion_questions` DISABLE KEYS */;

INSERT INTO `completion_questions` (`id`, `created_at`, `updated_at`, `deleted_at`, `subtype`, `rating_method`)
VALUES
	(13,'2019-01-04 11:40:09','2019-01-04 11:40:09',NULL,'completion',NULL),
	(15,'2019-01-21 07:57:43','2019-01-21 07:57:43',NULL,'completion',NULL);

/*!40000 ALTER TABLE `completion_questions` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table contacts
# ------------------------------------------------------------

DROP TABLE IF EXISTS `contacts`;

CREATE TABLE `contacts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `name` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `address` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `postal` varchar(7) COLLATE utf8_unicode_ci NOT NULL,
  `city` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `country` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `mobile` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `note` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `contacts`;

# Dump of table database_questions
# ------------------------------------------------------------

DROP TABLE IF EXISTS `database_questions`;

CREATE TABLE `database_questions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table discussing_parent_questions
# ------------------------------------------------------------

DROP TABLE IF EXISTS `discussing_parent_questions`;

CREATE TABLE `discussing_parent_questions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `test_take_id` int(10) unsigned NOT NULL,
  `group_question_id` int(10) unsigned NOT NULL,
  `level` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_discussing_parent_questions_test_takes1_idx` (`test_take_id`),
  KEY `fk_discussing_parent_questions_group_questions1_idx` (`group_question_id`),
  CONSTRAINT `fk_discussing_parent_questions_group_questions1` FOREIGN KEY (`group_question_id`) REFERENCES `group_questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_discussing_parent_questions_test_takes1` FOREIGN KEY (`test_take_id`) REFERENCES `test_takes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `discussing_parent_questions`;

# Dump of table drawing_questions
# ------------------------------------------------------------

DROP TABLE IF EXISTS `drawing_questions`;

CREATE TABLE `drawing_questions` (
  `id` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `answer` longblob,
  `grid` int(10) unsigned DEFAULT NULL,
  `bg_name` varchar(255) DEFAULT NULL,
  `bg_size` int(10) unsigned DEFAULT NULL,
  `bg_mime_type` varchar(255) DEFAULT NULL,
  `bg_extension` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_drawing_questions_questions1_idx` (`id`),
  CONSTRAINT `fk_drawing_questions_questions1` FOREIGN KEY (`id`) REFERENCES `questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

TRUNCATE TABLE `drawing_questions`;

# Dump of table education_levels
# ------------------------------------------------------------

DROP TABLE IF EXISTS `education_levels`;

CREATE TABLE `education_levels` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `name` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `max_years` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `education_levels`;

LOCK TABLES `education_levels` WRITE;
/*!40000 ALTER TABLE `education_levels` DISABLE KEYS */;

INSERT INTO `education_levels` (`id`, `created_at`, `updated_at`, `deleted_at`, `name`, `max_years`)
VALUES
	(1,'2015-06-01 22:34:37','2015-06-01 22:34:31',NULL,'VWO',6),
	(2,'2015-06-01 22:34:37','2015-06-01 22:34:31',NULL,'Gymnasium',6),
	(3,'2015-06-01 22:34:37','2015-06-01 22:34:31',NULL,'Havo',6),
	(4,'2015-06-01 22:34:37','2015-06-01 22:34:31',NULL,'Mavo / Vmbo tl',5),
	(5,'2015-06-01 22:34:37','2015-06-01 22:34:31',NULL,'Vmbo gl',4),
	(6,'2015-06-01 22:34:37','2015-06-01 22:34:31',NULL,'Vmbo kb',4),
	(7,'2015-06-01 22:34:37','2015-06-01 22:34:31',NULL,'Vmbo bb',4),
	(8,'2015-06-01 22:34:37','2015-06-01 22:34:31',NULL,'Lwoo',4),
	(9,'2015-06-01 22:34:37','2015-06-01 22:34:31',NULL,'Atheneum',4),
	(10,'2015-09-29 14:00:50','2015-09-29 14:00:50',NULL,'Mavo/Havo',2),
	(11,'2015-09-29 14:01:19','2015-09-29 14:01:19',NULL,'Havo/VWO',2),
	(12,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,'t/h',4),
	(13,'2018-06-12 00:00:00','2018-06-12 00:00:00',NULL,'h/v',6);

/*!40000 ALTER TABLE `education_levels` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table failed_jobs
# ------------------------------------------------------------

DROP TABLE IF EXISTS `failed_jobs`;

CREATE TABLE `failed_jobs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `connection` text COLLATE utf8_unicode_ci NOT NULL,
  `queue` text COLLATE utf8_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `failed_jobs`;

# Dump of table grading_scales
# ------------------------------------------------------------

DROP TABLE IF EXISTS `grading_scales`;

CREATE TABLE `grading_scales` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `name` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `system_name` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `grading_scales`;

LOCK TABLES `grading_scales` WRITE;
/*!40000 ALTER TABLE `grading_scales` DISABLE KEYS */;

INSERT INTO `grading_scales` (`id`, `created_at`, `updated_at`, `deleted_at`, `name`, `system_name`)
VALUES
	(1,'2015-12-10 11:31:39','2015-12-10 11:31:39',NULL,'Nederlands','OneToTen');

/*!40000 ALTER TABLE `grading_scales` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table group_question_questions
# ------------------------------------------------------------

DROP TABLE IF EXISTS `group_question_questions`;

CREATE TABLE `group_question_questions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `group_question_id` int(10) unsigned NOT NULL,
  `question_id` int(10) unsigned NOT NULL,
  `order` int(10) unsigned NOT NULL,
  `maintain_position` tinyint(1) NOT NULL,
  `discuss` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_group_question_questions_group_questions1_idx` (`group_question_id`),
  KEY `fk_group_question_questions_questions1_idx` (`question_id`),
  CONSTRAINT `fk_group_question_questions_group_questions1` FOREIGN KEY (`group_question_id`) REFERENCES `group_questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_group_question_questions_questions1` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `group_question_questions`;

LOCK TABLES `group_question_questions` WRITE;
/*!40000 ALTER TABLE `group_question_questions` DISABLE KEYS */;

INSERT INTO `group_question_questions` (`id`, `created_at`, `updated_at`, `deleted_at`, `group_question_id`, `question_id`, `order`, `maintain_position`, `discuss`)
VALUES
	(1,'2019-01-21 07:57:43','2019-01-21 07:57:43',NULL,14,15,1,0,1);

/*!40000 ALTER TABLE `group_question_questions` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table group_questions
# ------------------------------------------------------------

DROP TABLE IF EXISTS `group_questions`;

CREATE TABLE `group_questions` (
  `id` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `shuffle` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_group_questions_questions1_idx` (`id`),
  CONSTRAINT `fk_group_questions_questions1` FOREIGN KEY (`id`) REFERENCES `questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `group_questions`;

LOCK TABLES `group_questions` WRITE;
/*!40000 ALTER TABLE `group_questions` DISABLE KEYS */;

INSERT INTO `group_questions` (`id`, `created_at`, `updated_at`, `deleted_at`, `name`, `shuffle`)
VALUES
	(14,'2019-01-21 07:57:29','2019-01-21 07:57:29',NULL,'groepvraag',0);

/*!40000 ALTER TABLE `group_questions` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table invigilators
# ------------------------------------------------------------

DROP TABLE IF EXISTS `invigilators`;

CREATE TABLE `invigilators` (
  `test_take_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`test_take_id`,`user_id`),
  KEY `fk_test_takes_has_users_users2_idx` (`user_id`),
  KEY `fk_test_takes_has_users_test_takes2_idx` (`test_take_id`),
  CONSTRAINT `fk_test_takes_has_users_test_takes2` FOREIGN KEY (`test_take_id`) REFERENCES `test_takes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_test_takes_has_users_user1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `invigilators`;

LOCK TABLES `invigilators` WRITE;
/*!40000 ALTER TABLE `invigilators` DISABLE KEYS */;

INSERT INTO `invigilators` (`test_take_id`, `user_id`, `created_at`, `updated_at`, `deleted_at`)
VALUES
	(1,1500,'2019-02-27 11:03:41','2019-02-27 11:03:41',NULL),
	(2,1486,'2019-02-27 14:37:17','2019-02-27 14:37:17',NULL),
	(3,1486,'2019-05-24 13:52:42','2019-05-24 13:52:42',NULL);

/*!40000 ALTER TABLE `invigilators` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table jobs
# ------------------------------------------------------------

DROP TABLE IF EXISTS `jobs`;

CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8_unicode_ci NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_reserved_at_index` (`queue`,`reserved_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `jobs`;

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;


/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table licenses
# ------------------------------------------------------------

DROP TABLE IF EXISTS `licenses`;

CREATE TABLE `licenses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `school_location_id` int(10) unsigned NOT NULL,
  `start` date NOT NULL,
  `end` date DEFAULT NULL,
  `amount` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_licenses_school_locations1_idx` (`school_location_id`),
  CONSTRAINT `fk_licenses_school_locations1` FOREIGN KEY (`school_location_id`) REFERENCES `school_locations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `licenses`;

LOCK TABLES `licenses` WRITE;
/*!40000 ALTER TABLE `licenses` DISABLE KEYS */;

INSERT INTO `licenses` (`id`, `created_at`, `updated_at`, `deleted_at`, `school_location_id`, `start`, `end`, `amount`)
VALUES
	(1,'2018-12-21 15:53:51','2018-12-21 15:53:51',NULL,1,'2018-12-21','2021-12-21',1000);

/*!40000 ALTER TABLE `licenses` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table logs
# ------------------------------------------------------------

DROP TABLE IF EXISTS `logs`;

CREATE TABLE `logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `uri` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `uri_full` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `method` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `request` text COLLATE utf8_unicode_ci NOT NULL,
  `response` longtext COLLATE utf8_unicode_ci,
  `headers` text COLLATE utf8_unicode_ci NOT NULL,
  `code` int(11) NOT NULL DEFAULT '-1',
  `ip` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `duration` double(8,2) NOT NULL,
  `user_id` varchar(36) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `success` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `logs`;

# Dump of table matching_question_answer_links
# ------------------------------------------------------------

DROP TABLE IF EXISTS `matching_question_answer_links`;

CREATE TABLE `matching_question_answer_links` (
  `matching_question_id` int(10) unsigned NOT NULL,
  `matching_question_answer_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `order` int(10) unsigned NOT NULL,
  PRIMARY KEY (`matching_question_id`,`matching_question_answer_id`),
  KEY `fk_matching_question_answer_links_matching_questions1_idx` (`matching_question_id`),
  KEY `fk_matching_question_answer_links_matching_question_answers_idx` (`matching_question_answer_id`),
  CONSTRAINT `fk_matching_question_answer_links_matching_question_answers1` FOREIGN KEY (`matching_question_answer_id`) REFERENCES `matching_question_answers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_matching_question_answer_links_matching_questions1` FOREIGN KEY (`matching_question_id`) REFERENCES `matching_questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `matching_question_answer_links`;

LOCK TABLES `matching_question_answer_links` WRITE;
/*!40000 ALTER TABLE `matching_question_answer_links` DISABLE KEYS */;

INSERT INTO `matching_question_answer_links` (`matching_question_id`, `matching_question_answer_id`, `created_at`, `updated_at`, `deleted_at`, `order`)
VALUES
	(17,1,'2019-02-25 11:26:39','2019-02-25 11:26:39',NULL,1),
	(17,2,'2019-02-25 11:26:39','2019-02-25 11:26:39',NULL,1),
	(17,3,'2019-02-25 11:26:39','2019-02-25 11:26:39',NULL,1),
	(17,4,'2019-02-25 11:26:40','2019-02-25 11:26:40',NULL,1);

/*!40000 ALTER TABLE `matching_question_answer_links` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table matching_question_answers
# ------------------------------------------------------------

DROP TABLE IF EXISTS `matching_question_answers`;

CREATE TABLE `matching_question_answers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `correct_answer_id` int(10) unsigned DEFAULT NULL,
  `answer` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` enum('LEFT','RIGHT') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_matching_question_answers_matching_question_answers1_idx` (`correct_answer_id`),
  CONSTRAINT `fk_matching_question_answers_matching_question_answers1` FOREIGN KEY (`correct_answer_id`) REFERENCES `matching_question_answers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `matching_question_answers`;

LOCK TABLES `matching_question_answers` WRITE;
/*!40000 ALTER TABLE `matching_question_answers` DISABLE KEYS */;

INSERT INTO `matching_question_answers` (`id`, `created_at`, `updated_at`, `deleted_at`, `correct_answer_id`, `answer`, `type`)
VALUES
	(1,'2019-02-25 11:26:39','2019-02-25 11:26:39',NULL,NULL,'Fruit','LEFT'),
	(2,'2019-02-25 11:26:39','2019-02-25 11:26:39',NULL,1,'Appel','RIGHT'),
	(3,'2019-02-25 11:26:39','2019-02-25 11:26:39',NULL,NULL,'Groente','LEFT'),
	(4,'2019-02-25 11:26:40','2019-02-25 11:26:40',NULL,3,'Wortel','RIGHT');

/*!40000 ALTER TABLE `matching_question_answers` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table matching_questions
# ------------------------------------------------------------

DROP TABLE IF EXISTS `matching_questions`;

CREATE TABLE `matching_questions` (
  `id` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `subtype` varchar(45) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `fk_matching_questions_questions1_idx` (`id`),
  CONSTRAINT `fk_matching_questions_questions1` FOREIGN KEY (`id`) REFERENCES `questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `matching_questions`;

LOCK TABLES `matching_questions` WRITE;
/*!40000 ALTER TABLE `matching_questions` DISABLE KEYS */;

INSERT INTO `matching_questions` (`id`, `created_at`, `updated_at`, `deleted_at`, `subtype`)
VALUES
	(17,'2019-02-25 11:26:39','2019-02-25 11:26:39',NULL,'Matching');

/*!40000 ALTER TABLE `matching_questions` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table mentors
# ------------------------------------------------------------

DROP TABLE IF EXISTS `mentors`;

CREATE TABLE `mentors` (
  `school_class_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`school_class_id`,`user_id`),
  KEY `fk_mentors_school_classes1_idx` (`school_class_id`),
  KEY `fk_mentors_users1_idx` (`user_id`),
  CONSTRAINT `fk_mentors_school_classes1` FOREIGN KEY (`school_class_id`) REFERENCES `school_classes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_mentors_users1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `mentors`;

LOCK TABLES `mentors` WRITE;
/*!40000 ALTER TABLE `mentors` DISABLE KEYS */;

INSERT INTO `mentors` (`school_class_id`, `user_id`, `created_at`, `updated_at`, `deleted_at`)
VALUES
	(1,1486,'2018-12-21 17:03:38','2018-12-21 17:03:42','2018-12-21 17:03:42'),
	(1,1496,'2018-12-21 17:03:46','2018-12-21 17:03:46',NULL);

/*!40000 ALTER TABLE `mentors` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table message_receivers
# ------------------------------------------------------------

DROP TABLE IF EXISTS `message_receivers`;

CREATE TABLE `message_receivers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `message_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `type` enum('TO','CC','BCC') COLLATE utf8_unicode_ci NOT NULL,
  `read` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_message_receivers_messages1_idx` (`message_id`),
  KEY `fk_message_receivers_users1_idx` (`user_id`),
  CONSTRAINT `fk_message_receivers_messages1` FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `message_receivers`;

# Dump of table messages
# ------------------------------------------------------------

DROP TABLE IF EXISTS `messages`;

CREATE TABLE `messages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `message` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `fk_messages_users1_idx` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `messages`;

# Dump of table migrations
# ------------------------------------------------------------

DROP TABLE IF EXISTS `migrations`;

CREATE TABLE `migrations` (
  `migration` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `migrations`;


/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;

INSERT INTO `migrations` (`migration`, `batch`)
VALUES
	('2014_10_12_100000_create_password_resets_table',1),
	('2015_03_11_081125_create_answer_ratings_table',1),
	('2015_03_11_081125_create_answers_table',1),
	('2015_03_11_081125_create_attachments_table',1),
	('2015_03_11_081125_create_completion_question_answers_table',1),
	('2015_03_11_081125_create_completion_questions_table',1),
	('2015_03_11_081125_create_database_questions_table',1),
	('2015_03_11_081125_create_drawing_questions_table',1),
	('2015_03_11_081125_create_education_levels_table',1),
	('2015_03_11_081125_create_invigilators_table',1),
	('2015_03_11_081125_create_licenses_table',1),
	('2015_03_11_081125_create_matching_question_answers_table',1),
	('2015_03_11_081125_create_matching_questions_table',1),
	('2015_03_11_081125_create_multiple_choice_question_answers_table',1),
	('2015_03_11_081125_create_multiple_choice_questions_table',1),
	('2015_03_11_081125_create_open_questions_table',1),
	('2015_03_11_081125_create_periods_table',1),
	('2015_03_11_081125_create_question_groups_table',1),
	('2015_03_11_081125_create_questions_table',1),
	('2015_03_11_081125_create_ranking_question_answers_table',1),
	('2015_03_11_081125_create_ranking_questions_table',1),
	('2015_03_11_081125_create_roles_table',1),
	('2015_03_11_081125_create_sales_organizations_table',1),
	('2015_03_11_081125_create_school_classes_table',1),
	('2015_03_11_081125_create_school_location_ips_table',1),
	('2015_03_11_081125_create_school_locations_table',1),
	('2015_03_11_081125_create_school_years_table',1),
	('2015_03_11_081125_create_schools_table',1),
	('2015_03_11_081125_create_sections_table',1),
	('2015_03_11_081125_create_students_table',1),
	('2015_03_11_081125_create_subjects_table',1),
	('2015_03_11_081125_create_tag_relations_table',1),
	('2015_03_11_081125_create_tags_table',1),
	('2015_03_11_081125_create_teachers_table',1),
	('2015_03_11_081125_create_test_kinds_table',1),
	('2015_03_11_081125_create_test_participants_table',1),
	('2015_03_11_081125_create_test_rating_participants_table',1),
	('2015_03_11_081125_create_test_ratings_table',1),
	('2015_03_11_081125_create_test_take_event_types_table',1),
	('2015_03_11_081125_create_test_take_events_table',1),
	('2015_03_11_081125_create_test_take_statuses_table',1),
	('2015_03_11_081125_create_test_takes_table',1),
	('2015_03_11_081125_create_tests_table',1),
	('2015_03_11_081125_create_umbrella_organizations_table',1),
	('2015_03_11_081125_create_user_roles_table',1),
	('2015_03_11_081125_create_users_table',1),
	('2015_03_11_081128_add_foreign_keys_to_answer_ratings_table',1),
	('2015_03_11_081128_add_foreign_keys_to_answers_table',1),
	('2015_03_11_081128_add_foreign_keys_to_attachments_table',1),
	('2015_03_11_081128_add_foreign_keys_to_completion_question_answers_table',1),
	('2015_03_11_081128_add_foreign_keys_to_completion_questions_table',1),
	('2015_03_11_081128_add_foreign_keys_to_drawing_questions_table',1),
	('2015_03_11_081128_add_foreign_keys_to_invigilators_table',1),
	('2015_03_11_081128_add_foreign_keys_to_licenses_table',1),
	('2015_03_11_081128_add_foreign_keys_to_matching_question_answers_table',1),
	('2015_03_11_081128_add_foreign_keys_to_matching_questions_table',1),
	('2015_03_11_081128_add_foreign_keys_to_multiple_choice_question_answers_table',1),
	('2015_03_11_081128_add_foreign_keys_to_multiple_choice_questions_table',1),
	('2015_03_11_081128_add_foreign_keys_to_open_questions_table',1),
	('2015_03_11_081128_add_foreign_keys_to_periods_table',1),
	('2015_03_11_081128_add_foreign_keys_to_question_groups_table',1),
	('2015_03_11_081128_add_foreign_keys_to_questions_table',1),
	('2015_03_11_081128_add_foreign_keys_to_ranking_question_answers_table',1),
	('2015_03_11_081128_add_foreign_keys_to_ranking_questions_table',1),
	('2015_03_11_081128_add_foreign_keys_to_school_classes_table',1),
	('2015_03_11_081128_add_foreign_keys_to_school_location_ips_table',1),
	('2015_03_11_081128_add_foreign_keys_to_school_locations_table',1),
	('2015_03_11_081128_add_foreign_keys_to_schools_table',1),
	('2015_03_11_081128_add_foreign_keys_to_students_table',1),
	('2015_03_11_081128_add_foreign_keys_to_subjects_table',1),
	('2015_03_11_081128_add_foreign_keys_to_tag_relations_table',1),
	('2015_03_11_081128_add_foreign_keys_to_teachers_table',1),
	('2015_03_11_081128_add_foreign_keys_to_test_participants_table',1),
	('2015_03_11_081128_add_foreign_keys_to_test_rating_participants_table',1),
	('2015_03_11_081128_add_foreign_keys_to_test_ratings_table',1),
	('2015_03_11_081128_add_foreign_keys_to_test_take_events_table',1),
	('2015_03_11_081128_add_foreign_keys_to_test_takes_table',1),
	('2015_03_11_081128_add_foreign_keys_to_tests_table',1),
	('2015_03_11_081128_add_foreign_keys_to_user_roles_table',1),
	('2015_03_11_081128_add_foreign_keys_to_users_table',1),
	('2015_03_11_081129_fixing_laravels_shortcomings1',1),
	('2015_03_16_142954_update_drawing_questions_add_grid_field',2),
	('2015_04_09_132611_change_core_architecture',3),
	('2015_04_30_103345_phase_b_part1_changes',3),
	('2015_05_21_134521_phase_b_part3_changes',4),
	('2015_06_01_113249_phase_b_part4_changes',5),
	('2015_06_01_113250_phase_b_part5_changes',6),
	('2015_06_01_113251_phase_b_part6_changes',7),
	('2015_06_01_113252_phase_b_part7_changes',7),
	('2015_06_01_113253_phase_b_part8_changes',8),
	('2015_06_01_113254_phase_b_part9_changes',9),
	('2015_06_01_113255_phase_b_part10_changes',9),
	('2015_06_01_113256_phase_b_part11_changes',10),
	('2015_06_01_113257_phase_b_part12_changes',10),
	('2015_06_01_113258_phase_b_part13_changes',11),
	('2015_06_01_113259_phase_b_part14_changes',11),
	('2015_06_01_113300_phase_b_part15_changes',11),
	('2015_08_27_143024_create_jobs_table',12),
	('2015_06_01_113301_phase_b_part16_changes',13),
	('2015_06_01_113302_phase_b_part17_changes',14),
	('2015_06_01_113303_phase_b_part18_changes',15),
	('2015_09_08_140317_phase_c_part01_changes',16),
	('2015_10_30_093146_create_failed_jobs_table',17),
	('2015_11_02_095418_add_tests_discussed_counter',18),
	('2015_11_17_154142_extend_attainments',19),
	('2016_07_28_124905_add_discussed_user_id_on_test_take',20),
	('2018_12_20_160256_create_logs_table',21),
	('2019_07_10_114633_jobs_queue_reserved_reserved_at_index_for_laravel_5_3',22);

/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;



# Dump of table multiple_choice_question_answer_links
# ------------------------------------------------------------

DROP TABLE IF EXISTS `multiple_choice_question_answer_links`;

CREATE TABLE `multiple_choice_question_answer_links` (
  `multiple_choice_question_id` int(10) unsigned NOT NULL,
  `multiple_choice_question_answer_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `order` int(10) unsigned NOT NULL,
  PRIMARY KEY (`multiple_choice_question_id`,`multiple_choice_question_answer_id`),
  KEY `fk_multiple_choice_question_answer_links_multiple_choice_qu_idx1` (`multiple_choice_question_id`),
  CONSTRAINT `fk_multiple_choice_question_answer_links_multiple_choice_ques2` FOREIGN KEY (`multiple_choice_question_id`) REFERENCES `multiple_choice_questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `multiple_choice_question_answer_links`;

LOCK TABLES `multiple_choice_question_answer_links` WRITE;
/*!40000 ALTER TABLE `multiple_choice_question_answer_links` DISABLE KEYS */;

INSERT INTO `multiple_choice_question_answer_links` (`multiple_choice_question_id`, `multiple_choice_question_answer_id`, `created_at`, `updated_at`, `deleted_at`, `order`)
VALUES
	(16,1,'2019-02-25 11:25:22','2019-02-25 11:25:22',NULL,1),
	(16,2,'2019-02-25 11:25:22','2019-02-25 11:25:22',NULL,2),
	(16,3,'2019-02-25 11:25:22','2019-02-25 11:25:22',NULL,3),
	(20,4,'2019-02-27 11:02:49','2019-02-27 11:02:49',NULL,1),
	(20,5,'2019-02-27 11:02:49','2019-02-27 11:02:49',NULL,2);

/*!40000 ALTER TABLE `multiple_choice_question_answer_links` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table multiple_choice_question_answers
# ------------------------------------------------------------

DROP TABLE IF EXISTS `multiple_choice_question_answers`;

CREATE TABLE `multiple_choice_question_answers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `answer` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `score` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `multiple_choice_question_answers`;

LOCK TABLES `multiple_choice_question_answers` WRITE;
/*!40000 ALTER TABLE `multiple_choice_question_answers` DISABLE KEYS */;

INSERT INTO `multiple_choice_question_answers` (`id`, `created_at`, `updated_at`, `deleted_at`, `answer`, `score`)
VALUES
	(1,'2019-02-25 11:25:22','2019-02-25 11:25:22',NULL,'Dit is correct',4),
	(2,'2019-02-25 11:25:22','2019-02-25 11:25:22',NULL,'Niet correct 1',0),
	(3,'2019-02-25 11:25:22','2019-02-25 11:25:22',NULL,'Niet correct 2',0),
	(4,'2019-02-27 11:02:49','2019-02-27 11:02:49',NULL,'juist',2),
	(5,'2019-02-27 11:02:49','2019-02-27 11:02:49',NULL,'onjuist',0);

/*!40000 ALTER TABLE `multiple_choice_question_answers` ENABLE KEYS */;
UNLOCK TABLES;



# Dump of table multiple_choice_questions
# ------------------------------------------------------------

DROP TABLE IF EXISTS `multiple_choice_questions`;

CREATE TABLE `multiple_choice_questions` (
  `id` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `subtype` varchar(45) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `selectable_answers` int(10) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `fk_multiple_choice_questions_questions1_idx` (`id`),
  CONSTRAINT `fk_multiple_choice_questions_questions1` FOREIGN KEY (`id`) REFERENCES `questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `multiple_choice_questions`;

LOCK TABLES `multiple_choice_questions` WRITE;
/*!40000 ALTER TABLE `multiple_choice_questions` DISABLE KEYS */;

INSERT INTO `multiple_choice_questions` (`id`, `created_at`, `updated_at`, `deleted_at`, `subtype`, `selectable_answers`)
VALUES
	(16,'2019-02-25 11:25:21','2019-02-25 11:25:21',NULL,'MultipleChoice',1),
	(20,'2019-02-27 11:02:49','2019-02-27 11:02:49',NULL,'MultipleChoice',1);

/*!40000 ALTER TABLE `multiple_choice_questions` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table open_questions
# ------------------------------------------------------------

DROP TABLE IF EXISTS `open_questions`;

CREATE TABLE `open_questions` (
  `id` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `subtype` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `answer` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `fk_open_questions_questions1_idx` (`id`),
  CONSTRAINT `fk_open_questions_questions1` FOREIGN KEY (`id`) REFERENCES `questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `open_questions`;

LOCK TABLES `open_questions` WRITE;
/*!40000 ALTER TABLE `open_questions` DISABLE KEYS */;

INSERT INTO `open_questions` (`id`, `created_at`, `updated_at`, `deleted_at`, `subtype`, `answer`)
VALUES
	(10,'2019-01-04 11:18:22','2019-01-04 11:18:22',NULL,'short','<p>JA</p>\r\n'),
	(11,'2019-01-04 11:38:45','2019-01-04 11:38:45',NULL,'short','<p>Antwoord open kort</p>\r\n'),
	(12,'2019-01-04 11:39:11','2019-01-04 11:39:11',NULL,'medium','<p>Antwoord open lang</p>\r\n'),
	(19,'2019-02-27 11:01:30','2019-02-27 11:01:30',NULL,'short','<p>dit is correct</p>\r\n');

/*!40000 ALTER TABLE `open_questions` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table p_value_attainments
# ------------------------------------------------------------

DROP TABLE IF EXISTS `p_value_attainments`;

CREATE TABLE `p_value_attainments` (
  `p_value_id` int(10) unsigned NOT NULL,
  `attainment_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`p_value_id`,`attainment_id`),
  KEY `fk_p_value_attainments_p_values1_idx` (`p_value_id`),
  KEY `fk_p_value_attainments_attainments1_idx` (`attainment_id`),
  CONSTRAINT `fk_p_value_attainments_attainments1` FOREIGN KEY (`attainment_id`) REFERENCES `attainments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_p_value_attainments_p_values1` FOREIGN KEY (`p_value_id`) REFERENCES `p_values` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `p_value_attainments`;

# Dump of table p_value_users
# ------------------------------------------------------------

DROP TABLE IF EXISTS `p_value_users`;

CREATE TABLE `p_value_users` (
  `p_value_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`p_value_id`,`user_id`),
  KEY `fk_p_value_users_p_values1_idx` (`p_value_id`),
  KEY `fk_p_value_users_users1_idx` (`user_id`),
  CONSTRAINT `fk_p_value_users_p_values1` FOREIGN KEY (`p_value_id`) REFERENCES `p_values` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_p_value_users_users1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `p_value_users`;

# Dump of table p_values
# ------------------------------------------------------------

DROP TABLE IF EXISTS `p_values`;

CREATE TABLE `p_values` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `score` decimal(11,1) unsigned NOT NULL,
  `max_score` decimal(11,1) unsigned NOT NULL,
  `answer_id` int(10) unsigned NOT NULL,
  `test_participant_id` int(10) unsigned NOT NULL,
  `question_id` int(10) unsigned NOT NULL,
  `period_id` int(10) unsigned NOT NULL,
  `school_class_id` int(10) unsigned NOT NULL,
  `education_level_id` int(10) unsigned NOT NULL,
  `education_level_year` int(10) unsigned NOT NULL,
  `subject_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_p_values_answers1_idx` (`answer_id`),
  KEY `fk_p_values_test_participants1_idx` (`test_participant_id`),
  KEY `fk_p_values_questions1_idx` (`question_id`),
  KEY `fk_p_values_periods1_idx` (`period_id`),
  KEY `fk_p_values_school_classes1_idx` (`school_class_id`),
  KEY `fk_p_values_education_levels1_idx` (`education_level_id`),
  KEY `fk_p_values_subjects1_idx` (`subject_id`),
  CONSTRAINT `fk_p_values_answers1` FOREIGN KEY (`answer_id`) REFERENCES `answers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_p_values_education_levels1` FOREIGN KEY (`education_level_id`) REFERENCES `education_levels` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_p_values_periods1` FOREIGN KEY (`period_id`) REFERENCES `periods` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_p_values_questions1` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_p_values_school_classes1` FOREIGN KEY (`school_class_id`) REFERENCES `school_classes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_p_values_subjects1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_p_values_test_participants1` FOREIGN KEY (`test_participant_id`) REFERENCES `test_participants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `p_values`;

# Dump of table password_resets
# ------------------------------------------------------------

DROP TABLE IF EXISTS `password_resets`;

CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  KEY `password_resets_email_index` (`email`),
  KEY `password_resets_token_index` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `password_resets`;

# Dump of table periods
# ------------------------------------------------------------

DROP TABLE IF EXISTS `periods`;

CREATE TABLE `periods` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `school_year_id` int(10) unsigned NOT NULL,
  `name` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_periods_school_years1_idx` (`school_year_id`),
  CONSTRAINT `fk_periods_school_years1` FOREIGN KEY (`school_year_id`) REFERENCES `school_years` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `periods`;

LOCK TABLES `periods` WRITE;
/*!40000 ALTER TABLE `periods` DISABLE KEYS */;

INSERT INTO `periods` (`id`, `created_at`, `updated_at`, `deleted_at`, `school_year_id`, `name`, `start_date`, `end_date`)
VALUES
	(1,'2018-12-21 16:00:57','2018-12-21 16:00:57',NULL,1,'2018','2018-12-21','2019-08-31'),
	(2,'2019-02-25 14:39:08','2019-02-25 14:39:08',NULL,2,'2018','2018-08-31','2019-08-30'),
	(3,'2019-07-26 11:14:14','2019-07-26 11:14:14',NULL,3,'Periode 2019','2019-07-01','2020-07-02');

/*!40000 ALTER TABLE `periods` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table question_attachments
# ------------------------------------------------------------

DROP TABLE IF EXISTS `question_attachments`;

CREATE TABLE `question_attachments` (
  `question_id` int(10) unsigned NOT NULL,
  `attachment_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`question_id`,`attachment_id`),
  KEY `fk_question_attachments_questions1_idx` (`question_id`),
  KEY `fk_question_attachments_attachments1_idx` (`attachment_id`),
  CONSTRAINT `fk_question_attachments_attachments1` FOREIGN KEY (`attachment_id`) REFERENCES `attachments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_question_attachments_questions1` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `question_attachments`;

LOCK TABLES `question_attachments` WRITE;
/*!40000 ALTER TABLE `question_attachments` DISABLE KEYS */;

INSERT INTO `question_attachments` (`question_id`, `attachment_id`, `created_at`, `updated_at`, `deleted_at`)
VALUES
	(10,1,'2019-01-04 11:18:22','2019-01-04 11:18:22',NULL);

/*!40000 ALTER TABLE `question_attachments` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table question_attainments
# ------------------------------------------------------------

DROP TABLE IF EXISTS `question_attainments`;

CREATE TABLE `question_attainments` (
  `attainment_id` int(10) unsigned NOT NULL,
  `question_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`attainment_id`,`question_id`),
  KEY `fk_question_attainments_attainments1_idx` (`attainment_id`),
  KEY `fk_question_attainments_questions1_idx` (`question_id`),
  CONSTRAINT `fk_question_attainments_attainments1` FOREIGN KEY (`attainment_id`) REFERENCES `attainments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_question_attainments_questions1` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `question_attainments`;

# Dump of table question_authors
# ------------------------------------------------------------

DROP TABLE IF EXISTS `question_authors`;

CREATE TABLE `question_authors` (
  `user_id` int(10) unsigned NOT NULL,
  `question_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`,`question_id`),
  KEY `fk_question_authors_users1_idx` (`user_id`),
  KEY `fk_question_authors_questions1_idx` (`question_id`),
  CONSTRAINT `fk_question_authors_questions1` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_question_authors_users1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `question_authors`;

LOCK TABLES `question_authors` WRITE;
/*!40000 ALTER TABLE `question_authors` DISABLE KEYS */;

INSERT INTO `question_authors` (`user_id`, `question_id`, `created_at`, `updated_at`, `deleted_at`)
VALUES
	(1486,10,'2019-01-04 11:18:22','2019-01-04 11:18:22',NULL),
	(1486,11,'2019-01-04 11:38:45','2019-01-04 11:38:45',NULL),
	(1486,12,'2019-01-04 11:39:11','2019-01-04 11:39:11',NULL),
	(1486,13,'2019-01-04 11:40:09','2019-01-04 11:40:09',NULL),
	(1486,14,'2019-01-21 07:57:29','2019-01-21 07:57:29',NULL),
	(1486,15,'2019-01-21 07:57:43','2019-01-21 07:57:43',NULL),
	(1486,16,'2019-02-25 11:25:21','2019-02-25 11:25:21',NULL),
	(1486,17,'2019-02-25 11:26:39','2019-02-25 11:26:39',NULL),
	(1486,18,'2019-02-25 11:27:38','2019-02-25 11:27:38',NULL),
	(1500,19,'2019-02-27 11:01:30','2019-02-27 11:01:30',NULL),
	(1500,20,'2019-02-27 11:02:49','2019-02-27 11:02:49',NULL);

/*!40000 ALTER TABLE `question_authors` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table question_groups
# ------------------------------------------------------------

DROP TABLE IF EXISTS `question_groups`;

CREATE TABLE `question_groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `test_id` int(10) unsigned NOT NULL,
  `database_question_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `text` text COLLATE utf8_unicode_ci,
  `order` int(10) unsigned NOT NULL,
  `shuffle` tinyint(1) NOT NULL,
  `maintain_position` tinyint(1) NOT NULL,
  `add_to_database` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_question_groups_tests1_idx` (`test_id`),
  KEY `fk_question_groups_database_questions1_idx` (`database_question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `question_groups`;

# Dump of table questions
# ------------------------------------------------------------

DROP TABLE IF EXISTS `questions`;

CREATE TABLE `questions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `subject_id` int(10) unsigned NOT NULL,
  `education_level_id` int(10) unsigned NOT NULL,
  `type` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `question` longtext COLLATE utf8_unicode_ci,
  `education_level_year` int(10) unsigned NOT NULL,
  `score` int(10) unsigned DEFAULT NULL,
  `decimal_score` tinyint(1) DEFAULT NULL,
  `note_type` enum('NONE','TEXT','DRAWING') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'NONE',
  `rtti` enum('R','T1','T2','I') COLLATE utf8_unicode_ci DEFAULT NULL,
  `add_to_database` tinyint(1) NOT NULL,
  `is_subquestion` tinyint(1) NOT NULL,
  `derived_question_id` int(10) unsigned DEFAULT NULL,
  `is_open_source_content` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_questions_subjects1_idx` (`subject_id`),
  KEY `fk_questions_education_levels1_idx` (`education_level_id`),
  KEY `fk_questions_questions1_idx` (`derived_question_id`),
  CONSTRAINT `fk_questions_education_levels1` FOREIGN KEY (`education_level_id`) REFERENCES `education_levels` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_questions_questions1` FOREIGN KEY (`derived_question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_questions_subjects1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `questions`;

LOCK TABLES `questions` WRITE;
/*!40000 ALTER TABLE `questions` DISABLE KEYS */;

INSERT INTO `questions` (`id`, `created_at`, `updated_at`, `deleted_at`, `subject_id`, `education_level_id`, `type`, `question`, `education_level_year`, `score`, `decimal_score`, `note_type`, `rtti`, `add_to_database`, `is_subquestion`, `derived_question_id`, `is_open_source_content`)
VALUES
	(10,'2019-01-04 11:18:22','2019-01-04 11:18:22',NULL,1,1,'OpenQuestion','<p>TEST</p>\r\n',1,5,0,'NONE','',1,0,NULL,1),
	(11,'2019-01-04 11:38:45','2019-01-04 11:38:45',NULL,1,1,'OpenQuestion','<p>Open kort</p>\r\n',1,5,0,'NONE','',1,0,NULL,1),
	(12,'2019-01-04 11:39:11','2019-01-04 11:39:11',NULL,1,1,'OpenQuestion','<p>Open lang</p>\r\n',1,5,0,'NONE','',1,0,NULL,1),
	(13,'2019-01-04 11:40:09','2019-01-04 11:40:09',NULL,1,1,'CompletionQuestion','<p>Gatentekstvraag [1], [2], [3], [4], [5], [6], [7], [8], [9]. [10], [11]</p>\r\n',1,5,0,'NONE','',1,0,NULL,1),
	(14,'2019-01-21 07:57:29','2019-01-21 07:57:29',NULL,1,1,'GroupQuestion','',1,NULL,NULL,'NONE',NULL,1,0,NULL,NULL),
	(15,'2019-01-21 07:57:43','2019-01-21 07:57:43',NULL,1,1,'CompletionQuestion','<p>aaa [1]</p>\r\n',1,5,0,'NONE','',1,1,NULL,1),
	(16,'2019-02-25 11:25:21','2019-02-25 11:25:21',NULL,1,1,'MultipleChoiceQuestion','<p>Wat is correct?</p>\r\n',1,4,0,'NONE','',1,0,NULL,1),
	(17,'2019-02-25 11:26:39','2019-02-25 11:26:39',NULL,1,1,'MatchingQuestion','<p>Zet de juiste combinatie bij elkaar</p>\r\n',1,5,0,'NONE','',1,0,NULL,1),
	(18,'2019-02-25 11:27:38','2019-02-25 11:27:38',NULL,1,1,'RankingQuestion','<p>Zet in de juiste volgorde</p>\r\n',1,5,0,'NONE','',1,0,NULL,1),
	(19,'2019-02-27 11:01:30','2019-02-27 11:02:00',NULL,2,1,'OpenQuestion','<p>Wat is correct?</p>\r\n',1,2,0,'NONE','',0,0,NULL,0),
	(20,'2019-02-27 11:02:49','2019-02-27 11:02:49',NULL,2,1,'MultipleChoiceQuestion','<p>Welke antwoord is juist?</p>\r\n',1,2,0,'NONE','',1,0,NULL,0);

/*!40000 ALTER TABLE `questions` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table ranking_question_answer_links
# ------------------------------------------------------------

DROP TABLE IF EXISTS `ranking_question_answer_links`;

CREATE TABLE `ranking_question_answer_links` (
  `ranking_question_id` int(10) unsigned NOT NULL,
  `ranking_question_answer_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `order` int(10) unsigned NOT NULL,
  `correct_order` int(10) unsigned NOT NULL,
  PRIMARY KEY (`ranking_question_id`,`ranking_question_answer_id`),
  KEY `fk_ranking_question_answer_links_ranking_questions1_idx` (`ranking_question_id`),
  KEY `fk_ranking_question_answer_links_ranking_question_answers1_idx` (`ranking_question_answer_id`),
  CONSTRAINT `fk_ranking_question_answer_links_ranking_question_answers1` FOREIGN KEY (`ranking_question_answer_id`) REFERENCES `ranking_question_answers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ranking_question_answer_links_ranking_questions1` FOREIGN KEY (`ranking_question_id`) REFERENCES `ranking_questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `ranking_question_answer_links`;

LOCK TABLES `ranking_question_answer_links` WRITE;
/*!40000 ALTER TABLE `ranking_question_answer_links` DISABLE KEYS */;

INSERT INTO `ranking_question_answer_links` (`ranking_question_id`, `ranking_question_answer_id`, `created_at`, `updated_at`, `deleted_at`, `order`, `correct_order`)
VALUES
	(18,1,'2019-02-25 11:27:38','2019-02-25 11:27:38',NULL,1,1),
	(18,2,'2019-02-25 11:27:38','2019-02-25 11:27:38',NULL,2,2),
	(18,3,'2019-02-25 11:27:39','2019-02-25 11:27:39',NULL,3,3),
	(18,4,'2019-02-25 11:27:39','2019-02-25 11:27:39',NULL,4,4);

/*!40000 ALTER TABLE `ranking_question_answer_links` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table ranking_question_answers
# ------------------------------------------------------------

DROP TABLE IF EXISTS `ranking_question_answers`;

CREATE TABLE `ranking_question_answers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `answer` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `ranking_question_answers`;

LOCK TABLES `ranking_question_answers` WRITE;
/*!40000 ALTER TABLE `ranking_question_answers` DISABLE KEYS */;

INSERT INTO `ranking_question_answers` (`id`, `created_at`, `updated_at`, `deleted_at`, `answer`)
VALUES
	(1,'2019-02-25 11:27:38','2019-02-25 11:27:38',NULL,'1'),
	(2,'2019-02-25 11:27:38','2019-02-25 11:27:38',NULL,'2'),
	(3,'2019-02-25 11:27:39','2019-02-25 11:27:39',NULL,'3'),
	(4,'2019-02-25 11:27:39','2019-02-25 11:27:39',NULL,'4');

/*!40000 ALTER TABLE `ranking_question_answers` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table ranking_questions
# ------------------------------------------------------------

DROP TABLE IF EXISTS `ranking_questions`;

CREATE TABLE `ranking_questions` (
  `id` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `random_order` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_ranking_questions_questions1_idx` (`id`),
  CONSTRAINT `fk_ranking_questions_questions1` FOREIGN KEY (`id`) REFERENCES `questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `ranking_questions`;

LOCK TABLES `ranking_questions` WRITE;
/*!40000 ALTER TABLE `ranking_questions` DISABLE KEYS */;

INSERT INTO `ranking_questions` (`id`, `created_at`, `updated_at`, `deleted_at`, `random_order`)
VALUES
	(18,'2019-02-25 11:27:38','2019-02-25 11:27:38',NULL,NULL);

/*!40000 ALTER TABLE `ranking_questions` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table ratings
# ------------------------------------------------------------

DROP TABLE IF EXISTS `ratings`;

CREATE TABLE `ratings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `rating` decimal(8,4) unsigned NOT NULL,
  `score` decimal(11,1) unsigned NOT NULL,
  `max_score` decimal(11,1) unsigned NOT NULL,
  `weight` int(10) unsigned NOT NULL,
  `test_participant_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `period_id` int(10) unsigned NOT NULL,
  `school_class_id` int(10) unsigned NOT NULL,
  `education_level_id` int(10) unsigned NOT NULL,
  `education_level_year` int(10) unsigned NOT NULL,
  `subject_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_ratings_test_participants1_idx` (`test_participant_id`),
  KEY `fk_ratings_users1_idx` (`user_id`),
  KEY `fk_ratings_periods1_idx` (`period_id`),
  KEY `fk_ratings_school_classes1_idx` (`school_class_id`),
  KEY `fk_ratings_education_levels1_idx` (`education_level_id`),
  KEY `fk_ratings_subjects1_idx` (`subject_id`),
  CONSTRAINT `fk_ratings_education_levels1` FOREIGN KEY (`education_level_id`) REFERENCES `education_levels` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ratings_periods1` FOREIGN KEY (`period_id`) REFERENCES `periods` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ratings_school_classes1` FOREIGN KEY (`school_class_id`) REFERENCES `school_classes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ratings_subjects1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ratings_test_participants1` FOREIGN KEY (`test_participant_id`) REFERENCES `test_participants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ratings_users1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `ratings`;

# Dump of table roles
# ------------------------------------------------------------

DROP TABLE IF EXISTS `roles`;

CREATE TABLE `roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `name` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `roles`;

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;

INSERT INTO `roles` (`id`, `created_at`, `updated_at`, `deleted_at`, `name`)
VALUES
	(1,'2015-06-01 22:22:41','2015-06-01 22:22:57',NULL,'Teacher'),
	(2,'2015-06-01 22:23:02','2015-06-01 22:22:58',NULL,'Invigilator'),
	(3,'2015-06-01 22:23:01','2015-06-01 22:23:01',NULL,'Student'),
	(4,'2015-07-23 14:31:29','2015-07-23 14:31:29',NULL,'Administrator'),
	(5,'2015-07-23 14:31:31','2015-07-23 14:31:31',NULL,'Account manager'),
	(6,'2015-07-23 14:31:34','2015-07-23 14:31:34',NULL,'School manager'),
	(7,'2015-07-23 14:31:36','2015-07-23 14:31:36',NULL,'School management'),
	(8,'2015-07-23 14:31:37','2015-07-23 14:31:37',NULL,'Mentor'),
	(9,'2015-08-27 14:40:47','2015-08-27 14:40:47',NULL,'Parent');

/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table sales_organizations
# ------------------------------------------------------------

DROP TABLE IF EXISTS `sales_organizations`;

CREATE TABLE `sales_organizations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `name` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `sales_organizations`;

LOCK TABLES `sales_organizations` WRITE;
/*!40000 ALTER TABLE `sales_organizations` DISABLE KEYS */;

INSERT INTO `sales_organizations` (`id`, `created_at`, `updated_at`, `deleted_at`, `name`)
VALUES
	(1,'2015-09-15 15:06:33','2015-09-15 15:06:33',NULL,'The Teach & Learn Company');

/*!40000 ALTER TABLE `sales_organizations` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table school_addresses
# ------------------------------------------------------------

DROP TABLE IF EXISTS `school_addresses`;

CREATE TABLE `school_addresses` (
  `address_id` int(10) unsigned NOT NULL,
  `school_id` int(10) unsigned NOT NULL,
  `type` enum('MAIN','INVOICE','OTHER') COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`address_id`,`school_id`,`type`),
  KEY `fk_school_addresses_addresses1_idx` (`address_id`),
  KEY `fk_school_addresses_schools1_idx` (`school_id`),
  CONSTRAINT `fk_school_addresses_addresses1` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_school_addresses_schools1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `school_addresses`;

# Dump of table school_classes
# ------------------------------------------------------------

DROP TABLE IF EXISTS `school_classes`;

CREATE TABLE `school_classes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `school_location_id` int(10) unsigned NOT NULL,
  `education_level_id` int(10) unsigned NOT NULL,
  `school_year_id` int(10) unsigned NOT NULL,
  `name` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `education_level_year` int(10) unsigned DEFAULT NULL,
  `is_main_school_class` tinyint(1) DEFAULT NULL,
  `do_not_overwrite_from_interface` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `fk_classes_school_locations1_idx` (`school_location_id`),
  KEY `fk_classes_education_levels1_idx` (`education_level_id`),
  KEY `fk_classes_school_years1_idx` (`school_year_id`),
  CONSTRAINT `fk_classes_education_levels1` FOREIGN KEY (`education_level_id`) REFERENCES `education_levels` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_classes_school_locations1` FOREIGN KEY (`school_location_id`) REFERENCES `school_locations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_classes_school_years1` FOREIGN KEY (`school_year_id`) REFERENCES `school_years` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `school_classes`;

LOCK TABLES `school_classes` WRITE;
/*!40000 ALTER TABLE `school_classes` DISABLE KEYS */;

INSERT INTO `school_classes` (`id`, `created_at`, `updated_at`, `deleted_at`, `school_location_id`, `education_level_id`, `school_year_id`, `name`, `education_level_year`, `is_main_school_class`, `do_not_overwrite_from_interface`)
VALUES
	(1,'2018-12-21 16:01:36','2018-12-21 16:01:36',NULL,1,1,1,'Klas1',1,1,1),
	(2,'2019-02-25 14:40:56','2019-02-25 14:40:56',NULL,2,1,2,'Klas1',1,1,0),
	(3,'2019-07-26 11:27:17','2019-07-26 11:27:17',NULL,3,1,3,'Biologie',1,0,1);

/*!40000 ALTER TABLE `school_classes` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table school_contacts
# ------------------------------------------------------------

DROP TABLE IF EXISTS `school_contacts`;

CREATE TABLE `school_contacts` (
  `school_id` int(10) unsigned NOT NULL,
  `contact_id` int(10) unsigned NOT NULL,
  `type` enum('FINANCE','TECHNICAL','IMPLEMENTATION','OTHER') COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`school_id`,`contact_id`,`type`),
  KEY `fk_school_contacts_schools1_idx` (`school_id`),
  KEY `fk_school_contacts_contacts1_idx` (`contact_id`),
  CONSTRAINT `fk_school_contacts_contacts1` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_school_contacts_schools1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `school_contacts`;

# Dump of table school_location_addresses
# ------------------------------------------------------------

DROP TABLE IF EXISTS `school_location_addresses`;

CREATE TABLE `school_location_addresses` (
  `address_id` int(10) unsigned NOT NULL,
  `school_location_id` int(10) unsigned NOT NULL,
  `type` enum('MAIN','INVOICE','VISIT','OTHER') COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`address_id`,`school_location_id`,`type`),
  KEY `fk_school_location_addresses_addresses1_idx` (`address_id`),
  KEY `fk_school_location_addresses_school_locations1_idx` (`school_location_id`),
  CONSTRAINT `fk_school_location_addresses_addresses1` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_school_location_addresses_school_locations1` FOREIGN KEY (`school_location_id`) REFERENCES `school_locations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `school_location_addresses`;

# Dump of table school_location_contacts
# ------------------------------------------------------------

DROP TABLE IF EXISTS `school_location_contacts`;

CREATE TABLE `school_location_contacts` (
  `school_location_id` int(10) unsigned NOT NULL,
  `contact_id` int(10) unsigned NOT NULL,
  `type` enum('FINANCE','TECHNICAL','IMPLEMENTATION','OTHER') COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`school_location_id`,`contact_id`,`type`),
  KEY `fk_school_location_contacts_school_locations1_idx` (`school_location_id`),
  KEY `fk_school_location_contacts_contacts1_idx` (`contact_id`),
  CONSTRAINT `fk_school_location_contacts_contacts1` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_school_location_contacts_school_locations1` FOREIGN KEY (`school_location_id`) REFERENCES `school_locations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `school_location_contacts`;

# Dump of table school_location_education_levels
# ------------------------------------------------------------

DROP TABLE IF EXISTS `school_location_education_levels`;

CREATE TABLE `school_location_education_levels` (
  `school_location_id` int(10) unsigned NOT NULL,
  `education_level_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`school_location_id`,`education_level_id`),
  KEY `fk_school_location_education_levels_school_locations1_idx` (`school_location_id`),
  KEY `fk_school_location_education_levels_education_levels1_idx` (`education_level_id`),
  CONSTRAINT `fk_school_location_education_levels_education_levels1` FOREIGN KEY (`education_level_id`) REFERENCES `education_levels` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_school_location_education_levels_school_locations1` FOREIGN KEY (`school_location_id`) REFERENCES `school_locations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `school_location_education_levels`;

LOCK TABLES `school_location_education_levels` WRITE;
/*!40000 ALTER TABLE `school_location_education_levels` DISABLE KEYS */;

INSERT INTO `school_location_education_levels` (`school_location_id`, `education_level_id`, `created_at`, `updated_at`, `deleted_at`)
VALUES
	(1,1,'2018-12-21 15:51:39','2018-12-21 15:51:39',NULL),
	(1,3,'2018-12-21 15:51:39','2018-12-21 15:51:39',NULL),
	(1,4,'2018-12-21 15:51:39','2018-12-21 15:51:39',NULL),
	(1,12,'2018-12-21 15:51:39','2018-12-21 15:51:39',NULL),
	(2,1,'2019-02-25 14:36:39','2019-02-25 14:36:39',NULL),
	(2,2,'2019-02-25 14:36:39','2019-02-25 14:36:39',NULL),
	(2,3,'2019-02-25 14:36:39','2019-02-25 14:36:39',NULL),
	(2,4,'2019-02-25 14:36:39','2019-02-25 14:36:39',NULL),
	(2,12,'2019-02-25 14:36:39','2019-02-25 14:36:39',NULL),
	(3,1,'2019-07-26 10:06:22','2019-07-26 10:06:22',NULL);

/*!40000 ALTER TABLE `school_location_education_levels` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table school_location_ips
# ------------------------------------------------------------

DROP TABLE IF EXISTS `school_location_ips`;

CREATE TABLE `school_location_ips` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `school_location_id` int(10) unsigned NOT NULL,
  `ip` varbinary(16) NOT NULL,
  `netmask` int(3) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_school_location_ips_school_locations1_idx` (`school_location_id`),
  CONSTRAINT `fk_school_location_ips_school_locations1` FOREIGN KEY (`school_location_id`) REFERENCES `school_locations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `school_location_ips`;

# Dump of table school_location_school_years
# ------------------------------------------------------------

DROP TABLE IF EXISTS `school_location_school_years`;

CREATE TABLE `school_location_school_years` (
  `school_location_id` int(10) unsigned NOT NULL,
  `school_year_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`school_location_id`,`school_year_id`),
  KEY `fk_school_location_school_years_school_locations1_idx` (`school_location_id`),
  KEY `fk_school_location_school_years_school_years1_idx` (`school_year_id`),
  CONSTRAINT `fk_school_location_school_years_school_locations1` FOREIGN KEY (`school_location_id`) REFERENCES `school_locations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_school_location_school_years_sections1` FOREIGN KEY (`school_year_id`) REFERENCES `school_years` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `school_location_school_years`;

LOCK TABLES `school_location_school_years` WRITE;
/*!40000 ALTER TABLE `school_location_school_years` DISABLE KEYS */;

INSERT INTO `school_location_school_years` (`school_location_id`, `school_year_id`, `created_at`, `updated_at`, `deleted_at`)
VALUES
	(1,1,'2018-12-21 16:00:41','2018-12-21 16:00:41',NULL),
	(2,2,'2019-02-25 14:38:47','2019-02-25 14:38:47',NULL),
	(3,3,'2019-07-26 11:13:50','2019-07-26 11:13:50',NULL);

/*!40000 ALTER TABLE `school_location_school_years` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table school_location_sections
# ------------------------------------------------------------

DROP TABLE IF EXISTS `school_location_sections`;

CREATE TABLE `school_location_sections` (
  `school_location_id` int(10) unsigned NOT NULL,
  `section_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`school_location_id`,`section_id`),
  KEY `fk_school_location_sections_school_locations1_idx` (`school_location_id`),
  KEY `fk_school_location_sections_sections1_idx` (`section_id`),
  CONSTRAINT `fk_school_location_sections_school_locations1` FOREIGN KEY (`school_location_id`) REFERENCES `school_locations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_school_location_sections_sections1` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `school_location_sections`;

LOCK TABLES `school_location_sections` WRITE;
/*!40000 ALTER TABLE `school_location_sections` DISABLE KEYS */;

INSERT INTO `school_location_sections` (`school_location_id`, `section_id`, `created_at`, `updated_at`, `deleted_at`)
VALUES
	(1,1,'2018-12-21 16:01:08','2018-12-21 16:01:08',NULL),
	(2,2,'2019-02-25 14:40:09','2019-02-25 14:40:09',NULL),
	(3,3,'2019-07-26 11:31:16','2019-07-26 11:31:16',NULL);

/*!40000 ALTER TABLE `school_location_sections` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table school_locations
# ------------------------------------------------------------

DROP TABLE IF EXISTS `school_locations`;

CREATE TABLE `school_locations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `number_of_teachers` int(10) unsigned NOT NULL,
  `number_of_students` int(10) unsigned NOT NULL,
  `school_id` int(10) unsigned DEFAULT NULL,
  `grading_scale_id` int(10) unsigned NOT NULL,
  `customer_code` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `main_address` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `main_postal` varchar(7) COLLATE utf8_unicode_ci NOT NULL,
  `main_city` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `main_country` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `invoice_address` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `invoice_postal` varchar(7) COLLATE utf8_unicode_ci NOT NULL,
  `invoice_city` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `invoice_country` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `visit_address` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `visit_postal` varchar(7) COLLATE utf8_unicode_ci NOT NULL,
  `visit_city` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `visit_country` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `count_active_licenses` int(10) unsigned NOT NULL DEFAULT '0',
  `count_active_teachers` int(10) unsigned NOT NULL DEFAULT '0',
  `count_expired_licenses` int(10) unsigned NOT NULL DEFAULT '0',
  `count_licenses` int(10) unsigned NOT NULL DEFAULT '0',
  `count_questions` int(10) unsigned NOT NULL DEFAULT '0',
  `count_students` int(10) unsigned NOT NULL DEFAULT '0',
  `count_teachers` int(10) unsigned NOT NULL DEFAULT '0',
  `count_tests` int(10) unsigned NOT NULL DEFAULT '0',
  `count_tests_taken` int(10) unsigned NOT NULL DEFAULT '0',
  `activated` tinyint(1) NOT NULL DEFAULT '0',
  `is_rtti_school_location` tinyint(1) DEFAULT '0',
  `external_main_code` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `external_sub_code` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_open_source_content_creator` tinyint(1) DEFAULT '0',
  `is_allowed_to_view_open_source_content` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_school_location_school_idx` (`school_id`),
  KEY `fk_school_locations_users1_idx` (`user_id`),
  KEY `fk_school_locations_grading_scales1_idx` (`grading_scale_id`),
  CONSTRAINT `fk_school_location_school` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_school_locations_grading_scales1` FOREIGN KEY (`grading_scale_id`) REFERENCES `grading_scales` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_school_locations_users1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `school_locations`;

LOCK TABLES `school_locations` WRITE;
/*!40000 ALTER TABLE `school_locations` DISABLE KEYS */;

INSERT INTO `school_locations` (`id`, `created_at`, `updated_at`, `deleted_at`, `user_id`, `number_of_teachers`, `number_of_students`, `school_id`, `grading_scale_id`, `customer_code`, `name`, `main_address`, `main_postal`, `main_city`, `main_country`, `invoice_address`, `invoice_postal`, `invoice_city`, `invoice_country`, `visit_address`, `visit_postal`, `visit_city`, `visit_country`, `count_active_licenses`, `count_active_teachers`, `count_expired_licenses`, `count_licenses`, `count_questions`, `count_students`, `count_teachers`, `count_tests`, `count_tests_taken`, `activated`, `is_rtti_school_location`, `external_main_code`, `external_sub_code`, `is_open_source_content_creator`, `is_allowed_to_view_open_source_content`)
VALUES
	(1,'2018-12-21 15:51:39','2019-02-25 14:38:18',NULL,520,100,1000,1,1,'OSSL1','Open source schoolocatie1','1','1','1','1','2','2','2','2','3','3','3','3',0,0,0,0,0,0,0,0,0,1,0,'8888','00',1,0),
	(2,'2019-02-25 14:36:39','2019-02-25 14:36:39',NULL,521,100,1000,NULL,1,'RS','RTTI School','1','1','1','1','2','2','2','2','3','3','3','3',0,0,0,0,0,0,0,0,0,1,1,'9999','00',0,1),
	(3,'2019-07-26 10:06:22','2019-07-26 10:06:22',NULL,520,10,100,NULL,1,'standaard testschool','Standaard school','-','-','-','-','-','-','-','-','-','-','-','-',0,0,0,0,0,0,0,0,0,0,0,'','',0,0);

/*!40000 ALTER TABLE `school_locations` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table school_years
# ------------------------------------------------------------

DROP TABLE IF EXISTS `school_years`;

CREATE TABLE `school_years` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `year` int(4) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `school_years`;

LOCK TABLES `school_years` WRITE;
/*!40000 ALTER TABLE `school_years` DISABLE KEYS */;

INSERT INTO `school_years` (`id`, `created_at`, `updated_at`, `deleted_at`, `year`)
VALUES
	(1,'2018-12-21 16:00:40','2018-12-21 16:00:40',NULL,2018),
	(2,'2019-02-25 14:38:47','2019-02-25 14:38:47',NULL,2018),
	(3,'2019-07-26 11:13:50','2019-07-26 11:13:50',NULL,2019);

/*!40000 ALTER TABLE `school_years` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table schools
# ------------------------------------------------------------

DROP TABLE IF EXISTS `schools`;

CREATE TABLE `schools` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `umbrella_organization_id` int(10) unsigned DEFAULT NULL,
  `customer_code` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `main_address` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `main_postal` varchar(7) COLLATE utf8_unicode_ci NOT NULL,
  `main_city` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `main_country` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `invoice_address` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `invoice_postal` varchar(7) COLLATE utf8_unicode_ci NOT NULL,
  `invoice_city` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `invoice_country` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `count_active_licenses` int(10) unsigned NOT NULL DEFAULT '0',
  `count_active_teachers` int(10) unsigned NOT NULL DEFAULT '0',
  `count_expired_licenses` int(10) unsigned NOT NULL DEFAULT '0',
  `count_licenses` int(10) unsigned NOT NULL DEFAULT '0',
  `count_questions` int(10) unsigned NOT NULL DEFAULT '0',
  `count_students` int(10) unsigned NOT NULL DEFAULT '0',
  `count_teachers` int(10) unsigned NOT NULL DEFAULT '0',
  `count_tests` int(10) unsigned NOT NULL DEFAULT '0',
  `count_tests_taken` int(10) unsigned NOT NULL DEFAULT '0',
  `external_main_code` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_school_umbrella_organizations1_idx` (`umbrella_organization_id`),
  KEY `fk_schools_users1_idx` (`user_id`),
  CONSTRAINT `fk_school_umbrella_organizations1` FOREIGN KEY (`umbrella_organization_id`) REFERENCES `umbrella_organizations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_schools_users1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `schools`;

LOCK TABLES `schools` WRITE;
/*!40000 ALTER TABLE `schools` DISABLE KEYS */;

INSERT INTO `schools` (`id`, `created_at`, `updated_at`, `deleted_at`, `user_id`, `umbrella_organization_id`, `customer_code`, `name`, `main_address`, `main_postal`, `main_city`, `main_country`, `invoice_address`, `invoice_postal`, `invoice_city`, `invoice_country`, `count_active_licenses`, `count_active_teachers`, `count_expired_licenses`, `count_licenses`, `count_questions`, `count_students`, `count_teachers`, `count_tests`, `count_tests_taken`, `external_main_code`)
VALUES
	(1,'2019-01-04 11:34:22','2019-01-04 11:34:22',NULL,521,1,'NSG','Nieuwe scholengemeenschap','2','2','2','2','3','3','3','3',0,0,0,0,0,0,0,0,0,'8888');

/*!40000 ALTER TABLE `schools` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table sections
# ------------------------------------------------------------

DROP TABLE IF EXISTS `sections`;

CREATE TABLE `sections` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `name` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `sections`;

LOCK TABLES `sections` WRITE;
/*!40000 ALTER TABLE `sections` DISABLE KEYS */;

INSERT INTO `sections` (`id`, `created_at`, `updated_at`, `deleted_at`, `name`)
VALUES
	(1,'2018-12-21 16:01:08','2018-12-21 16:01:08',NULL,'Nederlands'),
	(2,'2019-02-25 14:40:09','2019-02-25 14:40:09',NULL,'Nederlands'),
	(3,'2019-07-26 11:31:16','2019-07-26 11:31:16',NULL,'Biologie');

/*!40000 ALTER TABLE `sections` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table student_parents
# ------------------------------------------------------------

DROP TABLE IF EXISTS `student_parents`;

CREATE TABLE `student_parents` (
  `parent_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`parent_id`,`user_id`),
  KEY `fk_student_parents_parents1_idx` (`parent_id`),
  KEY `fk_student_parents_users1_idx` (`user_id`),
  CONSTRAINT `fk_student_parents_parents1` FOREIGN KEY (`parent_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_student_parents_users1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `student_parents`;

# Dump of table students
# ------------------------------------------------------------

DROP TABLE IF EXISTS `students`;

CREATE TABLE `students` (
  `user_id` int(10) unsigned NOT NULL,
  `class_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `studentscol` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`user_id`,`class_id`),
  KEY `fk_users_has_school_classes_users1_idx` (`user_id`),
  KEY `fk_users_has_school_classes_school_classes1_idx` (`class_id`),
  CONSTRAINT `fk_users_has_school_classes_school_classes1` FOREIGN KEY (`class_id`) REFERENCES `school_classes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_users_has_school_classes_users1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `students`;

LOCK TABLES `students` WRITE;
/*!40000 ALTER TABLE `students` DISABLE KEYS */;

INSERT INTO `students` (`user_id`, `class_id`, `created_at`, `updated_at`, `deleted_at`, `studentscol`)
VALUES
	(1483,1,'2018-12-21 16:02:21','2018-12-21 16:02:21',NULL,NULL),
	(1484,1,'2018-12-21 16:53:03','2018-12-21 16:53:03',NULL,NULL),
	(1485,1,'2018-12-21 16:54:12','2018-12-21 16:54:12',NULL,NULL),
	(1498,2,'2019-02-25 14:41:28','2019-02-25 14:41:28',NULL,NULL),
	(1499,2,'2019-02-25 14:42:34','2019-02-25 14:42:34',NULL,NULL);

/*!40000 ALTER TABLE `students` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table subjects
# ------------------------------------------------------------

DROP TABLE IF EXISTS `subjects`;

CREATE TABLE `subjects` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `section_id` int(10) unsigned NOT NULL,
  `base_subject_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `abbreviation` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_subjects_sections1_idx` (`section_id`),
  KEY `fk_subjects_base_subject1_idx` (`base_subject_id`),
  CONSTRAINT `fk_subjects_base_subject1` FOREIGN KEY (`base_subject_id`) REFERENCES `base_subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_subjects_sections1` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `subjects`;

LOCK TABLES `subjects` WRITE;
/*!40000 ALTER TABLE `subjects` DISABLE KEYS */;

INSERT INTO `subjects` (`id`, `created_at`, `updated_at`, `deleted_at`, `section_id`, `base_subject_id`, `name`, `abbreviation`)
VALUES
	(1,'2018-12-21 16:01:19','2018-12-21 16:01:19',NULL,1,1,'Nederlands','NED'),
	(2,'2019-02-25 14:40:18','2019-02-25 14:40:18',NULL,2,1,'Nederlands','NL'),
	(3,'2019-07-26 11:31:49','2019-07-26 11:32:54','2019-07-26 11:32:54',3,1,'Biologie vak','bio'),
	(4,'2019-07-26 11:34:07','2019-07-26 11:34:07',NULL,3,11,'Vak biologie','bio');

/*!40000 ALTER TABLE `subjects` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table tag_relations
# ------------------------------------------------------------

DROP TABLE IF EXISTS `tag_relations`;

CREATE TABLE `tag_relations` (
  `tag_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `tag_relation_id` int(10) unsigned NOT NULL,
  `tag_relation_type` varchar(45) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`tag_relation_id`,`tag_relation_type`,`tag_id`),
  KEY `fk_tag_relations_tags1_idx` (`tag_id`),
  CONSTRAINT `fk_tag_relations_tags1` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `tag_relations`;

# Dump of table tags
# ------------------------------------------------------------

DROP TABLE IF EXISTS `tags`;

CREATE TABLE `tags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `name` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `tags`;

# Dump of table teachers
# ------------------------------------------------------------

DROP TABLE IF EXISTS `teachers`;

CREATE TABLE `teachers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `class_id` int(10) unsigned NOT NULL,
  `subject_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_has_school_classes_with_subject` (`user_id`,`class_id`,`subject_id`),
  KEY `fk_users_has_school_classes_users2_idx` (`user_id`),
  KEY `fk_users_has_school_classes_school_classes2_idx` (`class_id`),
  KEY `fk_teachers_subjects1_idx` (`subject_id`),
  CONSTRAINT `fk_teachers_subjects1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_users_has_school_classes_school_classes2` FOREIGN KEY (`class_id`) REFERENCES `school_classes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_users_has_school_classes_users2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `teachers`;

LOCK TABLES `teachers` WRITE;
/*!40000 ALTER TABLE `teachers` DISABLE KEYS */;

INSERT INTO `teachers` (`id`, `created_at`, `updated_at`, `deleted_at`, `user_id`, `class_id`, `subject_id`)
VALUES
	(1,'2018-12-21 17:01:16','2018-12-21 17:01:16',NULL,1486,1,1),
	(2,'2019-02-25 14:43:56','2019-02-25 14:43:56',NULL,1500,2,2),
	(3,'2019-02-25 14:44:01','2019-02-25 14:44:01',NULL,1501,2,2),
	(4,'2019-07-26 11:36:46','2019-07-26 11:36:46',NULL,1503,3,4);

/*!40000 ALTER TABLE `teachers` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table test_kinds
# ------------------------------------------------------------

DROP TABLE IF EXISTS `test_kinds`;

CREATE TABLE `test_kinds` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `name` varchar(45) NOT NULL,
  `has_weight` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

TRUNCATE TABLE `test_kinds`;

LOCK TABLES `test_kinds` WRITE;
/*!40000 ALTER TABLE `test_kinds` DISABLE KEYS */;

INSERT INTO `test_kinds` (`id`, `created_at`, `updated_at`, `deleted_at`, `name`, `has_weight`)
VALUES
	(1,'2015-06-01 22:44:18','2015-06-01 22:44:21',NULL,'Oefentoets',0),
	(2,'2015-06-01 22:44:20','2015-06-01 22:44:22',NULL,'Formatief',0),
	(3,'2015-06-01 22:44:19','2015-06-01 22:44:23',NULL,'Summatief',1);

/*!40000 ALTER TABLE `test_kinds` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table test_participants
# ------------------------------------------------------------

DROP TABLE IF EXISTS `test_participants`;

CREATE TABLE `test_participants` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `heartbeat_at` timestamp NULL DEFAULT NULL,
  `test_take_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `test_take_status_id` int(10) unsigned NOT NULL,
  `school_class_id` int(10) unsigned NOT NULL,
  `answer_id` int(10) unsigned DEFAULT NULL,
  `invigilator_note` text COLLATE utf8_unicode_ci,
  `rating` decimal(4,2) unsigned DEFAULT NULL,
  `retake_rating` decimal(4,2) unsigned DEFAULT NULL,
  `ip_address` varbinary(16) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_test_takes_has_users` (`test_take_id`,`user_id`),
  KEY `fk_test_takes_has_users_test_takes1_idx` (`test_take_id`),
  KEY `fk_test_takes_has_users_users1_idx` (`user_id`),
  KEY `fk_test_participants_test_take_statuses1_idx` (`test_take_status_id`),
  KEY `fk_test_participants_school_classes1_idx` (`school_class_id`),
  KEY `fk_test_participants_answers1_idx` (`answer_id`),
  CONSTRAINT `fk_test_participants_answers1` FOREIGN KEY (`answer_id`) REFERENCES `answers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_test_participants_school_classes1` FOREIGN KEY (`school_class_id`) REFERENCES `school_classes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_test_participants_test_take_statuses1` FOREIGN KEY (`test_take_status_id`) REFERENCES `test_take_statuses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_test_takes_has_users_test_takes1` FOREIGN KEY (`test_take_id`) REFERENCES `test_takes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_test_takes_has_users_users1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `test_participants`;

LOCK TABLES `test_participants` WRITE;
/*!40000 ALTER TABLE `test_participants` DISABLE KEYS */;

INSERT INTO `test_participants` (`id`, `created_at`, `updated_at`, `deleted_at`, `heartbeat_at`, `test_take_id`, `user_id`, `test_take_status_id`, `school_class_id`, `answer_id`, `invigilator_note`, `rating`, `retake_rating`, `ip_address`)
VALUES
	(1,'2019-02-27 11:03:41','2019-02-27 11:03:41',NULL,NULL,1,1498,1,2,NULL,NULL,NULL,NULL,NULL),
	(2,'2019-02-27 11:03:41','2019-02-27 11:03:41',NULL,NULL,1,1499,1,2,NULL,NULL,NULL,NULL,NULL),
	(3,'2019-02-27 14:37:17','2019-02-27 14:44:11',NULL,'2019-02-27 14:39:06',2,1483,4,1,6,NULL,10.00,NULL,X'5C409ADD'),
	(4,'2019-02-27 14:37:17','2019-02-27 14:44:11',NULL,'2019-02-27 14:41:56',2,1484,7,1,12,NULL,3.70,NULL,X'5C409ADD'),
	(5,'2019-02-27 14:37:17','2019-02-27 14:37:26',NULL,NULL,2,1485,2,1,NULL,NULL,NULL,NULL,NULL),
	(6,'2019-05-24 13:52:42','2019-05-24 14:09:54',NULL,NULL,3,1483,2,1,NULL,NULL,NULL,NULL,NULL),
	(7,'2019-05-24 13:52:42','2019-05-24 14:09:54',NULL,NULL,3,1484,2,1,NULL,NULL,NULL,NULL,NULL),
	(8,'2019-05-24 13:52:42','2019-05-24 14:09:54',NULL,NULL,3,1485,2,1,NULL,NULL,NULL,NULL,NULL);

/*!40000 ALTER TABLE `test_participants` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table test_questions
# ------------------------------------------------------------

DROP TABLE IF EXISTS `test_questions`;

CREATE TABLE `test_questions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `test_id` int(10) unsigned NOT NULL,
  `question_id` int(10) unsigned NOT NULL,
  `order` int(10) unsigned NOT NULL,
  `maintain_position` tinyint(1) NOT NULL,
  `discuss` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_test_questions_tests1_idx` (`test_id`),
  KEY `fk_test_questions_questions1_idx` (`question_id`),
  CONSTRAINT `fk_test_questions_questions1` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_test_questions_tests1` FOREIGN KEY (`test_id`) REFERENCES `tests` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `test_questions`;

LOCK TABLES `test_questions` WRITE;
/*!40000 ALTER TABLE `test_questions` DISABLE KEYS */;

INSERT INTO `test_questions` (`id`, `created_at`, `updated_at`, `deleted_at`, `test_id`, `question_id`, `order`, `maintain_position`, `discuss`)
VALUES
	(1,'2019-01-04 11:18:22','2019-01-04 11:18:22',NULL,3,10,1,0,1),
	(2,'2019-01-04 11:38:45','2019-01-04 11:38:45',NULL,1,11,1,0,1),
	(3,'2019-01-04 11:39:11','2019-01-04 11:39:11',NULL,1,12,2,0,1),
	(4,'2019-01-04 11:40:09','2019-01-04 11:40:09',NULL,1,13,3,0,1),
	(5,'2019-01-21 07:57:29','2019-01-21 07:57:29',NULL,3,14,2,0,0),
	(6,'2019-02-18 13:46:47','2019-02-18 13:46:47',NULL,3,11,3,0,1),
	(7,'2019-02-25 11:25:21','2019-02-25 11:25:21',NULL,3,16,4,0,1),
	(8,'2019-02-25 11:26:39','2019-02-25 11:26:39',NULL,3,17,5,0,1),
	(9,'2019-02-25 11:27:38','2019-02-25 11:27:38',NULL,3,18,6,0,1),
	(10,'2019-02-27 11:01:30','2019-02-27 11:01:30',NULL,5,19,1,0,1),
	(11,'2019-02-27 11:02:49','2019-02-27 11:02:49',NULL,5,20,2,0,1),
	(12,'2019-02-27 11:03:41','2019-02-27 11:03:41',NULL,6,19,1,0,1),
	(13,'2019-02-27 11:03:41','2019-02-27 11:03:41',NULL,6,20,2,0,1),
	(14,'2019-02-27 14:37:17','2019-02-27 14:37:17',NULL,7,10,1,0,1),
	(15,'2019-02-27 14:37:17','2019-02-27 14:37:17',NULL,7,14,2,0,0),
	(16,'2019-02-27 14:37:17','2019-02-27 14:37:17',NULL,7,11,3,0,1),
	(17,'2019-02-27 14:37:17','2019-02-27 14:37:17',NULL,7,16,4,0,1),
	(18,'2019-02-27 14:37:17','2019-02-27 14:37:17',NULL,7,17,5,0,1),
	(19,'2019-02-27 14:37:17','2019-02-27 14:37:17',NULL,7,18,6,0,1);

/*!40000 ALTER TABLE `test_questions` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table test_rating_participants
# ------------------------------------------------------------

DROP TABLE IF EXISTS `test_rating_participants`;

CREATE TABLE `test_rating_participants` (
  `test_participant_id` int(10) unsigned NOT NULL,
  `test_rating_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `test_rating_participants`;

# Dump of table test_take_event_types
# ------------------------------------------------------------

DROP TABLE IF EXISTS `test_take_event_types`;

CREATE TABLE `test_take_event_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `name` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `requires_confirming` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `test_take_event_types`;

LOCK TABLES `test_take_event_types` WRITE;
/*!40000 ALTER TABLE `test_take_event_types` DISABLE KEYS */;

INSERT INTO `test_take_event_types` (`id`, `created_at`, `updated_at`, `deleted_at`, `name`, `requires_confirming`)
VALUES
	(1,'2015-06-01 22:42:12','2015-06-01 22:42:20',NULL,'Start',0),
	(2,'2015-06-01 22:42:13','2015-06-01 22:42:21',NULL,'Stop',0),
	(3,'2015-06-01 22:42:14','2015-06-01 22:42:21',NULL,'Lost focus',1),
	(4,'2015-06-01 22:42:15','2015-06-01 22:42:22',NULL,'Screenshot',1),
	(5,'2015-06-01 22:42:16','2015-06-01 22:42:23',NULL,'Started late',1),
	(6,'2015-06-01 22:42:16','2015-06-01 22:42:24',NULL,'Start discussion',0),
	(7,'2015-06-01 22:42:19','2015-06-01 22:42:25',NULL,'End discussion',0),
	(8,'2015-07-23 14:36:35','2015-07-23 14:36:35',NULL,'Continue',0),
	(9,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,'Application closed',1);

/*!40000 ALTER TABLE `test_take_event_types` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table test_take_events
# ------------------------------------------------------------

DROP TABLE IF EXISTS `test_take_events`;

CREATE TABLE `test_take_events` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `test_take_id` int(10) unsigned NOT NULL,
  `test_participant_id` int(10) unsigned DEFAULT NULL,
  `test_take_event_type_id` int(10) unsigned NOT NULL,
  `confirmed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_test_take_events_test_take_event_types1_idx` (`test_take_event_type_id`),
  KEY `fk_test_take_events_test_takes1_idx` (`test_take_id`),
  KEY `fk_test_take_events_test_participants1_idx` (`test_participant_id`),
  CONSTRAINT `fk_test_take_events_test_participants1` FOREIGN KEY (`test_participant_id`) REFERENCES `test_participants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_test_take_events_test_take_event_types1` FOREIGN KEY (`test_take_event_type_id`) REFERENCES `test_take_event_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_test_take_events_test_takes1` FOREIGN KEY (`test_take_id`) REFERENCES `test_takes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `test_take_events`;

LOCK TABLES `test_take_events` WRITE;
/*!40000 ALTER TABLE `test_take_events` DISABLE KEYS */;

INSERT INTO `test_take_events` (`id`, `created_at`, `updated_at`, `deleted_at`, `test_take_id`, `test_participant_id`, `test_take_event_type_id`, `confirmed`)
VALUES
	(1,'2019-02-27 14:37:26','2019-02-27 14:37:26',NULL,2,NULL,1,0),
	(2,'2019-02-27 14:38:24','2019-02-27 14:38:24',NULL,2,3,1,0),
	(3,'2019-02-27 14:39:07','2019-02-27 14:39:07',NULL,2,3,2,0),
	(4,'2019-02-27 14:39:38','2019-02-27 14:39:38',NULL,2,4,1,0),
	(5,'2019-02-27 14:40:00','2019-02-27 14:40:00',NULL,2,4,2,0),
	(6,'2019-02-27 14:40:25','2019-02-27 14:40:25',NULL,2,NULL,2,0),
	(7,'2019-02-27 14:40:38','2019-02-27 14:40:38',NULL,2,NULL,6,0),
	(8,'2019-05-24 14:09:54','2019-05-24 14:09:54',NULL,3,NULL,1,0),
	(9,'2019-05-24 14:10:57','2019-05-24 14:10:57',NULL,3,NULL,2,0),
	(10,'2019-05-24 14:11:17','2019-05-24 14:11:17',NULL,3,NULL,6,0);

/*!40000 ALTER TABLE `test_take_events` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table test_take_statuses
# ------------------------------------------------------------

DROP TABLE IF EXISTS `test_take_statuses`;

CREATE TABLE `test_take_statuses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `name` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_individual_status` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `test_take_statuses`;

LOCK TABLES `test_take_statuses` WRITE;
/*!40000 ALTER TABLE `test_take_statuses` DISABLE KEYS */;

INSERT INTO `test_take_statuses` (`id`, `created_at`, `updated_at`, `deleted_at`, `name`, `is_individual_status`)
VALUES
	(1,'2015-06-01 22:42:35','2015-06-01 22:42:43',NULL,'Planned',0),
	(2,'2015-06-01 22:42:36','2015-06-01 22:42:44',NULL,'Test not taken',1),
	(3,'2015-06-01 22:42:37','2015-06-01 22:42:45',NULL,'Taking test',0),
	(4,'2015-06-01 22:42:38','2015-06-01 22:42:45',NULL,'Handed in',1),
	(5,'2015-06-01 22:42:39','2015-06-01 22:42:46',NULL,'Taken away',1),
	(6,'2015-06-01 22:42:40','2015-06-01 22:42:47',NULL,'Taken',0),
	(7,'2015-06-01 22:42:40','2015-06-01 22:42:48',NULL,'Discussing',0),
	(8,'2015-06-01 22:42:42','2015-06-01 22:42:49',NULL,'Discussed',0),
	(9,'2015-06-01 22:48:33','2015-06-01 22:48:33',NULL,'Rated',0);

/*!40000 ALTER TABLE `test_take_statuses` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table test_takes
# ------------------------------------------------------------

DROP TABLE IF EXISTS `test_takes`;

CREATE TABLE `test_takes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `test_id` int(10) unsigned NOT NULL,
  `test_take_status_id` int(10) unsigned NOT NULL,
  `period_id` int(10) unsigned NOT NULL,
  `is_discussed` tinyint(1) NOT NULL DEFAULT '0',
  `discussed_user_id` int(10) unsigned DEFAULT NULL,
  `discussing_question_id` int(10) unsigned DEFAULT NULL,
  `retake` tinyint(1) DEFAULT NULL,
  `retake_test_take_id` int(10) unsigned DEFAULT NULL,
  `time_start` datetime DEFAULT NULL,
  `time_end` datetime DEFAULT NULL,
  `location` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `weight` int(10) unsigned DEFAULT NULL,
  `note` text COLLATE utf8_unicode_ci,
  `invigilator_note` text COLLATE utf8_unicode_ci,
  `discussion_type` enum('ALL','OPEN_ONLY') COLLATE utf8_unicode_ci DEFAULT NULL,
  `show_results` datetime DEFAULT NULL,
  `ppp` decimal(6,4) unsigned DEFAULT NULL,
  `epp` decimal(6,4) unsigned DEFAULT NULL,
  `wanted_average` decimal(6,4) unsigned DEFAULT NULL,
  `n_term` decimal(6,4) DEFAULT NULL,
  `pass_mark` decimal(8,4) unsigned DEFAULT NULL,
  `is_rtti_test_take` tinyint(1) DEFAULT '0',
  `exported_to_rtti` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_test_takes_tests1_idx` (`test_id`),
  KEY `fk_test_takes_test_take_statuses1_idx` (`test_take_status_id`),
  KEY `fk_test_takes_periods1_idx` (`period_id`),
  KEY `fk_test_takes_users1_idx` (`user_id`),
  KEY `fk_test_takes_test_takes1_idx` (`retake_test_take_id`),
  KEY `fk_test_takes_questions1_idx` (`discussing_question_id`),
  KEY `fk_test_takes_users2_idx` (`discussed_user_id`),
  CONSTRAINT `fk_test_takes_periods1` FOREIGN KEY (`period_id`) REFERENCES `periods` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_test_takes_questions1` FOREIGN KEY (`discussing_question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_test_takes_test_take_statuses1` FOREIGN KEY (`test_take_status_id`) REFERENCES `test_take_statuses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_test_takes_test_takes1` FOREIGN KEY (`retake_test_take_id`) REFERENCES `test_takes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_test_takes_tests1` FOREIGN KEY (`test_id`) REFERENCES `tests` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_test_takes_users1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_test_takes_users2` FOREIGN KEY (`discussed_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `test_takes`;

LOCK TABLES `test_takes` WRITE;
/*!40000 ALTER TABLE `test_takes` DISABLE KEYS */;

INSERT INTO `test_takes` (`id`, `created_at`, `updated_at`, `deleted_at`, `user_id`, `test_id`, `test_take_status_id`, `period_id`, `is_discussed`, `discussed_user_id`, `discussing_question_id`, `retake`, `retake_test_take_id`, `time_start`, `time_end`, `location`, `weight`, `note`, `invigilator_note`, `discussion_type`, `show_results`, `ppp`, `epp`, `wanted_average`, `n_term`, `pass_mark`, `is_rtti_test_take`, `exported_to_rtti`)
VALUES
	(1,'2019-02-27 11:03:41','2019-02-27 11:03:41',NULL,1500,6,1,2,0,NULL,NULL,0,NULL,'2019-02-27 00:00:00',NULL,NULL,5,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL),
	(2,'2019-02-27 14:37:17','2019-02-27 14:44:15',NULL,1486,7,9,1,0,NULL,NULL,0,NULL,'2019-02-27 00:00:00',NULL,NULL,5,NULL,'','ALL','2019-02-27 15:01:00',NULL,NULL,NULL,1.0000,70.0000,0,NULL),
	(3,'2019-05-24 13:52:42','2019-05-24 14:11:17',NULL,1486,7,7,1,0,NULL,NULL,0,NULL,'2019-05-24 00:00:00',NULL,NULL,5,NULL,'','ALL',NULL,NULL,NULL,NULL,NULL,NULL,0,NULL);

/*!40000 ALTER TABLE `test_takes` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table tests
# ------------------------------------------------------------

DROP TABLE IF EXISTS `tests`;

CREATE TABLE `tests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `subject_id` int(10) unsigned NOT NULL,
  `education_level_id` int(10) unsigned NOT NULL,
  `period_id` int(10) unsigned NOT NULL,
  `author_id` int(10) unsigned NOT NULL,
  `test_kind_id` int(10) unsigned NOT NULL,
  `system_test_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `abbreviation` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `education_level_year` int(10) unsigned NOT NULL,
  `status` int(11) NOT NULL DEFAULT '0',
  `introduction` text COLLATE utf8_unicode_ci,
  `shuffle` tinyint(1) NOT NULL,
  `is_system_test` tinyint(1) NOT NULL,
  `question_count` int(10) unsigned NOT NULL DEFAULT '0',
  `is_open_source_content` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_tests_subjects1_idx` (`subject_id`),
  KEY `fk_tests_education_levels1_idx` (`education_level_id`),
  KEY `fk_tests_periods1_idx` (`period_id`),
  KEY `fk_tests_users1_idx` (`author_id`),
  KEY `fk_tests_test_kind1_idx` (`test_kind_id`),
  KEY `fk_tests_tests1_idx` (`system_test_id`),
  CONSTRAINT `fk_tests_education_levels1` FOREIGN KEY (`education_level_id`) REFERENCES `education_levels` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_tests_periods1` FOREIGN KEY (`period_id`) REFERENCES `periods` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_tests_subjects1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_tests_test_kind1` FOREIGN KEY (`test_kind_id`) REFERENCES `test_kinds` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_tests_tests1` FOREIGN KEY (`system_test_id`) REFERENCES `tests` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_tests_users1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `tests`;

LOCK TABLES `tests` WRITE;
/*!40000 ALTER TABLE `tests` DISABLE KEYS */;

INSERT INTO `tests` (`id`, `created_at`, `updated_at`, `deleted_at`, `subject_id`, `education_level_id`, `period_id`, `author_id`, `test_kind_id`, `system_test_id`, `name`, `abbreviation`, `education_level_year`, `status`, `introduction`, `shuffle`, `is_system_test`, `question_count`, `is_open_source_content`)
VALUES
	(1,'2018-12-21 17:04:44','2019-01-04 11:40:09',NULL,1,1,1,1486,3,NULL,'toets1','T1',1,0,'',0,0,3,1),
	(2,'2018-12-21 17:07:03','2018-12-21 17:07:03',NULL,1,1,1,1486,3,NULL,'Toets2','T2',1,0,'',0,0,0,1),
	(3,'2019-01-04 10:29:05','2019-02-27 14:37:17',NULL,1,1,1,1486,3,7,'Toets met geluid','TMG',1,0,'',0,0,6,1),
	(4,'2019-02-27 10:47:26','2019-02-27 10:50:28','2019-02-27 10:50:28',2,1,2,1500,1,NULL,'test123','test',1,0,'',0,0,0,NULL),
	(5,'2019-02-27 11:00:40','2019-02-27 11:03:41',NULL,2,1,2,1500,3,6,'Test2','T2',1,0,'',0,0,2,NULL),
	(6,'2019-02-27 11:03:41','2019-02-27 11:03:41',NULL,2,1,2,1500,3,NULL,'Test2','T2',1,0,'',0,1,2,NULL),
	(7,'2019-02-27 14:37:17','2019-02-27 14:37:26',NULL,1,1,1,1486,3,NULL,'Toets met geluid','TMG',1,1,'',0,1,6,1);

/*!40000 ALTER TABLE `tests` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table umbrella_organization_addresses
# ------------------------------------------------------------

DROP TABLE IF EXISTS `umbrella_organization_addresses`;

CREATE TABLE `umbrella_organization_addresses` (
  `address_id` int(10) unsigned NOT NULL,
  `umbrella_organization_id` int(10) unsigned NOT NULL,
  `type` enum('MAIN','INVOICE','OTHER') COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`address_id`,`umbrella_organization_id`,`type`),
  KEY `fk_umbrella_organization_addresses_addresses1_idx` (`address_id`),
  KEY `fk_umbrella_organization_addresses_umbrella_organizations1_idx` (`umbrella_organization_id`),
  CONSTRAINT `fk_umbrella_organization_addresses_addresses1` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_umbrella_organization_addresses_umbrella_organizations1` FOREIGN KEY (`umbrella_organization_id`) REFERENCES `umbrella_organizations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `umbrella_organization_addresses`;

# Dump of table umbrella_organization_contacts
# ------------------------------------------------------------

DROP TABLE IF EXISTS `umbrella_organization_contacts`;

CREATE TABLE `umbrella_organization_contacts` (
  `umbrella_organization_id` int(10) unsigned NOT NULL,
  `contact_id` int(10) unsigned NOT NULL,
  `type` enum('FINANCE','TECHNICAL','IMPLEMENTATION','OTHER') COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`umbrella_organization_id`,`contact_id`,`type`),
  KEY `fk_umbrella_organization_contacts_umbrella_organizations1_idx` (`umbrella_organization_id`),
  KEY `fk_umbrella_organization_contacts_contacts1_idx` (`contact_id`),
  CONSTRAINT `fk_umbrella_organization_contacts_contacts1` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_umbrella_organization_contacts_umbrella_organizations1` FOREIGN KEY (`umbrella_organization_id`) REFERENCES `umbrella_organizations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `umbrella_organization_contacts`;

# Dump of table umbrella_organizations
# ------------------------------------------------------------

DROP TABLE IF EXISTS `umbrella_organizations`;

CREATE TABLE `umbrella_organizations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `customer_code` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `main_address` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `main_postal` varchar(7) COLLATE utf8_unicode_ci NOT NULL,
  `main_city` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `main_country` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `invoice_address` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `invoice_postal` varchar(7) COLLATE utf8_unicode_ci NOT NULL,
  `invoice_city` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `invoice_country` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `count_active_licenses` int(10) unsigned NOT NULL DEFAULT '0',
  `count_active_teachers` int(10) unsigned NOT NULL DEFAULT '0',
  `count_expired_licenses` int(10) unsigned NOT NULL DEFAULT '0',
  `count_licenses` int(10) unsigned NOT NULL DEFAULT '0',
  `count_questions` int(10) unsigned NOT NULL DEFAULT '0',
  `count_students` int(10) unsigned NOT NULL DEFAULT '0',
  `count_teachers` int(10) unsigned NOT NULL DEFAULT '0',
  `count_tests` int(10) unsigned NOT NULL DEFAULT '0',
  `count_tests_taken` int(10) unsigned NOT NULL DEFAULT '0',
  `external_main_code` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_umbrella_organizations_users1_idx` (`user_id`),
  CONSTRAINT `fk_umbrella_organizations_users1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `umbrella_organizations`;

LOCK TABLES `umbrella_organizations` WRITE;
/*!40000 ALTER TABLE `umbrella_organizations` DISABLE KEYS */;

INSERT INTO `umbrella_organizations` (`id`, `created_at`, `updated_at`, `deleted_at`, `user_id`, `customer_code`, `name`, `main_address`, `main_postal`, `main_city`, `main_country`, `invoice_address`, `invoice_postal`, `invoice_city`, `invoice_country`, `count_active_licenses`, `count_active_teachers`, `count_expired_licenses`, `count_licenses`, `count_questions`, `count_students`, `count_teachers`, `count_tests`, `count_tests_taken`, `external_main_code`)
VALUES
	(1,'2019-01-04 11:33:47','2019-01-04 11:33:47',NULL,521,'NK','Nieuwe koepel','1','1','1','1','2','2','2','2',0,0,0,0,0,0,0,0,0,'8888');

/*!40000 ALTER TABLE `umbrella_organizations` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table user_roles
# ------------------------------------------------------------

DROP TABLE IF EXISTS `user_roles`;

CREATE TABLE `user_roles` (
  `user_id` int(10) unsigned NOT NULL,
  `role_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `fk_user_has_roles_users1_idx` (`user_id`),
  KEY `fk_user_has_roles_roles1_idx` (`role_id`),
  CONSTRAINT `fk_user_has_roles_roles1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_user_has_roles_users1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `user_roles`;

LOCK TABLES `user_roles` WRITE;
/*!40000 ALTER TABLE `user_roles` DISABLE KEYS */;

INSERT INTO `user_roles` (`user_id`, `role_id`, `created_at`, `updated_at`, `deleted_at`)
VALUES
	(519,4,'2015-07-23 16:01:11','2015-07-23 16:01:11',NULL),
	(520,5,'2015-07-24 16:07:38','2015-07-24 16:07:38',NULL),
	(521,5,'2015-08-06 16:36:54','2015-08-06 16:36:54',NULL),
	(755,5,'2017-11-28 16:20:10','2017-11-28 16:20:10',NULL),
	(1481,6,'2018-12-21 15:53:35','2018-12-21 15:53:35',NULL),
	(1482,6,'2018-12-21 15:59:31','2018-12-21 15:59:31',NULL),
	(1483,3,'2018-12-21 16:02:21','2018-12-21 16:02:21',NULL),
	(1484,3,'2018-12-21 16:53:03','2018-12-21 16:53:03',NULL),
	(1485,3,'2018-12-21 16:54:11','2018-12-21 16:54:11',NULL),
	(1486,1,'2018-12-21 16:55:42','2018-12-21 16:55:42',NULL),
	(1496,1,'2018-12-21 16:55:42','2018-12-21 16:55:42',NULL),
	(1497,6,'2019-02-25 14:38:02','2019-02-25 14:38:02',NULL),
	(1498,3,'2019-02-25 14:41:28','2019-02-25 14:41:28',NULL),
	(1499,3,'2019-02-25 14:42:34','2019-02-25 14:42:34',NULL),
	(1500,1,'2019-02-25 14:43:31','2019-02-25 14:43:31',NULL),
	(1501,1,'2019-02-25 14:43:48','2019-02-25 14:43:48',NULL),
	(1502,6,'2019-07-26 10:09:04','2019-07-26 10:09:04',NULL),
	(1503,1,'2019-07-26 10:15:18','2019-07-26 10:15:18',NULL);

/*!40000 ALTER TABLE `user_roles` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table users
# ------------------------------------------------------------

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `sales_organization_id` int(10) unsigned DEFAULT NULL,
  `school_id` int(10) unsigned DEFAULT NULL,
  `school_location_id` int(10) unsigned DEFAULT NULL,
  `username` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `session_hash` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name_first` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name_suffix` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `abbreviation` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `external_id` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `api_key` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `gender` enum('Male','Female','Other') COLLATE utf8_unicode_ci DEFAULT NULL,
  `time_dispensation` tinyint(1) NOT NULL DEFAULT '0',
  `send_welcome_email` tinyint(1) NOT NULL DEFAULT '0',
  `note` text COLLATE utf8_unicode_ci,
  `profile_image_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `profile_image_size` int(10) unsigned DEFAULT NULL,
  `profile_image_mime_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `profile_image_extension` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `count_accounts` int(10) unsigned NOT NULL DEFAULT '0',
  `count_active_licenses` int(10) unsigned NOT NULL DEFAULT '0',
  `count_expired_licenses` int(10) unsigned NOT NULL DEFAULT '0',
  `count_last_test_taken` date DEFAULT NULL,
  `count_licenses` int(10) unsigned NOT NULL DEFAULT '0',
  `count_questions` int(10) unsigned NOT NULL DEFAULT '0',
  `count_students` int(10) unsigned NOT NULL DEFAULT '0',
  `count_teachers` int(10) unsigned NOT NULL DEFAULT '0',
  `count_tests` int(10) unsigned NOT NULL DEFAULT '0',
  `count_tests_taken` int(10) unsigned NOT NULL DEFAULT '0',
  `count_tests_discussed` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_api_key_unique` (`api_key`),
  KEY `fk_users_sales_organizations1_idx` (`sales_organization_id`),
  KEY `fk_users_schools1_idx` (`school_id`),
  KEY `fk_users_school_locations1_idx` (`school_location_id`),
  CONSTRAINT `fk_users_sales_organizations1` FOREIGN KEY (`sales_organization_id`) REFERENCES `sales_organizations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_users_school_locations1` FOREIGN KEY (`school_location_id`) REFERENCES `school_locations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_users_schools1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `users`;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;

INSERT INTO `users` (`id`, `created_at`, `updated_at`, `deleted_at`, `sales_organization_id`, `school_id`, `school_location_id`, `username`, `password`, `remember_token`, `session_hash`, `name_first`, `name_suffix`, `name`, `abbreviation`, `external_id`, `api_key`, `gender`, `time_dispensation`, `send_welcome_email`, `note`, `profile_image_name`, `profile_image_size`, `profile_image_mime_type`, `profile_image_extension`, `count_accounts`, `count_active_licenses`, `count_expired_licenses`, `count_last_test_taken`, `count_licenses`, `count_questions`, `count_students`, `count_teachers`, `count_tests`, `count_tests_taken`, `count_tests_discussed`)
VALUES
	(519,'2015-07-23 14:30:00','2019-07-26 11:35:32',NULL,NULL,NULL,NULL,'testadmin@teachandlearncompany.com','$2y$10$FKp172JecX7LiDClZADGHOsW.CisLKvad0TpzvYFCNUlQZ6LfLzD.',NULL,'BB0gDXoJ8ILol81umBkLd9cun0o5hKJRkimzQv7PavpiteUHRwEq9yMfQ1W9BGe6PjthQ4UpZ13wT3swzvgam519','','','Administrator Teach & Learn Company',NULL,NULL,'ZmxVbR60IkEWBdHwwLATrVVL2uSahMXwQTtekLXI',NULL,0,0,NULL,NULL,NULL,NULL,NULL,0,0,0,NULL,0,0,0,0,0,0,0),
	(520,'2015-07-24 16:07:38','2018-11-27 13:46:17',NULL,1,NULL,NULL,'a@teachandlearncompany.com','$2y$10$FKp172JecX7LiDClZADGHOsW.CisLKvad0TpzvYFCNUlQZ6LfLzD.',NULL,'h5yRDQcR5ILbrpbVd2xmVliqr1S5MZC2CFsmHhdem1MF7kHXVSauOr01SNX8GVNvjP2snaTUeQE11geom9Az9DFbYRVbAu6w246I','Alex','','Karlas',NULL,NULL,'i351h0XUS2VHsPIz8qteTCWlBHLHl2XkQNjBV8g9',NULL,0,0,'Om te testen in de testportal met leerlingen van het Fioretti\r\n\r\n22102015 test RAH',NULL,NULL,NULL,NULL,26,3725,123,NULL,3984,0,718,47,0,0,0),
	(521,'2015-08-06 16:36:54','2018-10-24 10:57:56',NULL,1,NULL,NULL,'c@teachandlearncompany.com','$2y$10$FKp172JecX7LiDClZADGHOsW.CisLKvad0TpzvYFCNUlQZ6LfLzD.',NULL,'izwDeCJQqGpWvwlPXPWnio8XVwas1z8OxcaQ9KZ1D20nuKuIQgWUzNutR6MN7xDSvwxtDeP3OQ0RVkK8TamAUdPmUpddEX0KURWR','Carlo','','Schoep',NULL,NULL,'hN2Y9ybBl3P26vjIRytxfmfKREMAyPLeMAaPTG3S',NULL,0,0,'Test',NULL,NULL,NULL,NULL,11,5121,120,NULL,5541,0,11,8,0,0,0),
	(755,'2017-11-28 16:20:10','2018-12-21 15:57:41',NULL,1,NULL,NULL,'accountmanager@test-correct.nl','$2y$10$FKp172JecX7LiDClZADGHOsW.CisLKvad0TpzvYFCNUlQZ6LfLzD.',NULL,'WcfpGkcXPu1N8u5uulJF9FcTq1pkMOGUWlmltAkGptbzgLlslTRMcxZqDLD1tx7Xe18ZdXJgwhjZZBUu5PJ1eTprUBa1nuLtlraV','.','','Accountmanager testomgeving',NULL,NULL,'XdoNN4b8zLf2SFI3Ne40ISvGG55ImA2dK6GDCk8s',NULL,0,0,'',NULL,NULL,NULL,NULL,1,0,0,NULL,20,0,15,3,0,0,0),
	(1481,'2018-12-21 15:53:35','2018-12-21 15:53:35',NULL,NULL,NULL,1,'opensourceschoollocatie1@test-correct.nl','$2y$10$FKp172JecX7LiDClZADGHOsW.CisLKvad0TpzvYFCNUlQZ6LfLzD.',NULL,NULL,'Open source','','Schoollocatie1',NULL,NULL,'v2Qh8Qh2zgvODimIyr8BA7AqMsc0khgcWJ1yIxFL',NULL,0,0,'',NULL,NULL,NULL,NULL,0,0,0,NULL,0,0,0,0,0,0,0),
	(1482,'2018-12-21 15:59:31','2019-02-25 14:39:42',NULL,NULL,NULL,1,'opensourceschoollocatie1schoolbeheerder@test-correct.nl','$2y$10$FKp172JecX7LiDClZADGHOsW.CisLKvad0TpzvYFCNUlQZ6LfLzD.',NULL,'EfQVChme4aIqn5LGqSoj5Kv2SU4NR9RV8IGERuSTiBTSOnmIy3dgDXYrseFhTp6Ess6ssvAyoTLXYyObJwkghk1anhAEuWy3Ifbs','opensourceschoollocatie1','','schoolbeheerder',NULL,NULL,'EftrNpmaGtJuY0XnPcUx6W4LrNB8ZYowQYpehVa6',NULL,0,0,'',NULL,NULL,NULL,NULL,0,0,0,NULL,0,0,0,0,0,0,0),
	(1483,'2018-12-21 16:02:21','2019-02-27 14:38:16',NULL,NULL,NULL,1,'s1@test-correct.nl','$2y$10$FKp172JecX7LiDClZADGHOsW.CisLKvad0TpzvYFCNUlQZ6LfLzD.',NULL,'pHmzbyVwHzbTNNeracmpnE9EWXclZ64lzPZj6zjcumeJNijWmvk2TH6uthhHNojyf2kvPEj7ed9TLwf2SpO2HMJshR1SZaeMJjCu','student','','1',NULL,'1','1sdNrld4Gfkwc54VZQdUjU5AiqKMGeqThgqg3yQ6','Male',0,0,'',NULL,NULL,NULL,NULL,0,0,0,NULL,0,0,0,0,0,0,0),
	(1484,'2018-12-21 16:53:03','2019-02-27 14:39:30',NULL,NULL,NULL,1,'s2@test-correct.nl','$2y$10$FKp172JecX7LiDClZADGHOsW.CisLKvad0TpzvYFCNUlQZ6LfLzD.',NULL,'I9u8TiWUInyaarFDNyccY6oRexYayl5jIoVZ9pswedygaMHFkivzWmuJGNGPCF6A9uWTM3u2l6U2mJOKL40kwZOpvoFvI6Z5VTOh','student','','2',NULL,'2','K54DwOPmGgPtl7dH3n0WbLXWSgAaAskFE7m5OMr6','Male',0,0,'',NULL,NULL,NULL,NULL,0,0,0,NULL,0,0,0,0,0,0,0),
	(1485,'2018-12-21 16:54:11','2018-12-21 16:54:11',NULL,NULL,NULL,1,'s3@test-correct.nl','$2y$10$FKp172JecX7LiDClZADGHOsW.CisLKvad0TpzvYFCNUlQZ6LfLzD.',NULL,NULL,'student','','3',NULL,'3','d94bDt1OT51mF4GPEgJOjqDqhKmw2ezrm5Mowscz','Male',0,0,'',NULL,NULL,NULL,NULL,0,0,0,NULL,0,0,0,0,0,0,0),
	(1486,'2018-12-21 16:55:42','2019-05-24 14:09:41',NULL,NULL,NULL,1,'d1@test-correct.nl','$2y$10$FKp172JecX7LiDClZADGHOsW.CisLKvad0TpzvYFCNUlQZ6LfLzD.',NULL,'RO7yhzwDpby0GlYo74vdht3qRmLEJ2d5suAFz6F2im4GTdj9bs07zkfxomnCMQZUKApAqkkbNaX7fEVfbcycT1486','Docent','','1','d1','d1','zCmOADVkbdJEcvZKdcgtVFYiuhnRBrunw9ZJLPUN',NULL,0,0,'',NULL,NULL,NULL,NULL,0,0,0,NULL,0,0,0,0,0,0,0),
	(1496,'2018-12-21 16:54:11','2018-12-21 16:54:11',NULL,NULL,NULL,1,'d2@test-correct.nl','$2y$10$FKp172JecX7LiDClZADGHOsW.CisLKvad0TpzvYFCNUlQZ6LfLzD.',NULL,NULL,'Docent','','2','d1','1','d94bDt1OT51mF4GPEgJOjqDqhKmw2ezrm5Mowsch','Male',0,0,'',NULL,NULL,NULL,NULL,0,0,0,NULL,0,0,0,0,0,0,0),
	(1497,'2019-02-25 14:38:02','2019-07-22 13:37:38',NULL,NULL,NULL,2,'rtti-schoolbeheerder@test-correct.nl','$2y$10$FKp172JecX7LiDClZADGHOsW.CisLKvad0TpzvYFCNUlQZ6LfLzD.',NULL,'rJ863O06QH8Y3pniPGSp1trMn1JFk2SiiNA33RTEOT75guXDYshEK6LclkOJLdwb14QcUTciip80ya7Pj74PV1497','RTTI','','Schoolbeheerder',NULL,NULL,'Gr9KZClqwxAf7ajQcNv36qFULN4SU4CLSgeNRNzN',NULL,0,0,'',NULL,NULL,NULL,NULL,0,0,0,NULL,0,0,0,0,0,0,0),
	(1498,'2019-02-25 14:41:28','2019-07-22 13:38:09',NULL,NULL,NULL,2,'rtti-s1@test-correct.nl','$2y$10$FKp172JecX7LiDClZADGHOsW.CisLKvad0TpzvYFCNUlQZ6LfLzD.',NULL,'MUHw4e7XkL9peFCq8E38jXV0iPqWo2ah4FvJxf8CHIkkHk9OJ8nkGabS1IEKvWjmslIAFIU9j0IBGIKwvvW1H1498','Leerling','','1',NULL,'1111','Rm9Jb9hVx4S4dhwgxifXh2LxxBL06nm7XQPvrJkG','Male',0,0,'',NULL,NULL,NULL,NULL,0,0,0,NULL,0,0,0,0,0,0,0),
	(1499,'2019-02-25 14:42:34','2019-02-25 14:45:04',NULL,NULL,NULL,2,'rtti-s2@test-correct.nl','$2y$10$FKp172JecX7LiDClZADGHOsW.CisLKvad0TpzvYFCNUlQZ6LfLzD.',NULL,NULL,'Leerling','','2',NULL,'1112','Zu9UbXcGdB93et9z3K8x30Jjjj3bVuZWYjXn9Z0c','Male',0,0,'',NULL,NULL,NULL,NULL,0,0,0,NULL,0,0,0,0,0,0,0),
	(1500,'2019-02-25 14:43:31','2019-07-22 13:37:57',NULL,NULL,NULL,2,'rtti-d1@test-correct.nl','$2y$10$FKp172JecX7LiDClZADGHOsW.CisLKvad0TpzvYFCNUlQZ6LfLzD.',NULL,'BZxzY9AFUzd0U0E8joKt8dNF8No8JaaCXMxcOfw7QuyWEsdtSmv1hufNdvOms3Sq9eoQVEo6GqdVG1HvVRxnJ1500','docent','','1','d1','11234','TS3ANvYP9XuOyIzDgrl2OmfwoY8DehN2OxUQXIk6',NULL,0,0,'',NULL,NULL,NULL,NULL,0,0,0,NULL,0,0,0,0,0,0,0),
	(1501,'2019-02-25 14:43:48','2019-02-25 14:46:24',NULL,NULL,NULL,2,'rtti-d2@test-correct.nl','$2y$10$FKp172JecX7LiDClZADGHOsW.CisLKvad0TpzvYFCNUlQZ6LfLzD.',NULL,'4PCtxWw1Qkp006LmGDHlh6agLETAaspn1cw8k8vPpPbnrrEzhH4yGYQB1sFLB1luwr4NITvVLtI2X8FXAF9Jb8LvNvWUrQ0Lj6Uh','docent','','2','d2','1234','5s3TvwxH4ZNz4oYlWaxz7k0kogvAfXEwYc69IJLE',NULL,0,0,'',NULL,NULL,NULL,NULL,0,0,0,NULL,0,0,0,0,0,0,0),
	(1502,'2019-07-26 10:09:04','2019-07-26 11:36:27',NULL,NULL,NULL,3,'standaardschoolbeheerder@test-correct.nl','$2y$10$x2g3R0gDAomaQQ/QgdDc2ODYsX/YNP.oR4j1hEjVW.2WpTCWbFgTu',NULL,'kD3aHY6SROFMMfJUqtrB6wX5TPJOZ5k5CB3mUyfwDjpLfSD1M2KVjhZ9iXLNBCriXnAInFaPp8b6sLH99ZMjy1502',' ','','schoolbeheerder standaard testschool',NULL,NULL,'dC01onRohYz5GJRZE78vPqMZeR42NLG3LIjfB7W5',NULL,0,0,'',NULL,NULL,NULL,NULL,0,0,0,NULL,0,0,0,0,0,0,0),
	(1503,'2019-07-26 10:15:18','2019-07-26 11:25:57',NULL,NULL,NULL,3,'d3@test-correct.nl','$2y$10$P1z625XVtnWtGFQXLcpjWOKFg8ql7uOwNo1rZWQL.NVPJF7jaPPym',NULL,'nU3FJtydS0W677b7CTJoi5PlxnmGUSs7HCheKgmZSSfnNJK3blrIE4sBiOMT1aAT8zQb0SBkwa60MDpeZry0u1503',' ','','docent standaardschool','D3','','pTNwqUO9EwKw5ODSeJTGH4wOeIEYvhhIa1KmwXlq',NULL,0,0,'',NULL,NULL,NULL,NULL,0,0,0,NULL,0,0,0,0,0,0,0);

/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

# Dump of table managers
# ------------------------------------------------------------

DROP TABLE IF EXISTS `managers`;

CREATE TABLE `managers` (
                            `school_class_id` int(10) unsigned NOT NULL,
                            `user_id` int(10) unsigned NOT NULL,
                            `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
                            `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
                            `deleted_at` timestamp NULL DEFAULT NULL,
                            PRIMARY KEY (`school_class_id`,`user_id`),
                            KEY `fk_managers_school_classes1_idx` (`school_class_id`),
                            KEY `fk_managers_users1_idx` (`user_id`),
                            CONSTRAINT `fk_managers_school_classes1` FOREIGN KEY (`school_class_id`) REFERENCES `school_classes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                            CONSTRAINT `fk_managers_users1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE TABLE `managers`;

LOCK TABLES `managers` WRITE;
/*!40000 ALTER TABLE `managers` DISABLE KEYS */;

INSERT INTO `managers` (`school_class_id`, `user_id`, `created_at`, `updated_at`, `deleted_at`)
VALUES
(42,529,'2015-11-03 16:11:58','2015-11-18 11:39:10','2015-11-18 11:39:10'),
(42,610,'2015-12-03 15:33:15','2016-01-06 18:33:27',NULL),
(42,612,'2015-11-05 15:32:40','2016-01-21 11:19:01',NULL),
(42,617,'2015-11-05 15:32:16','2015-11-05 15:32:16',NULL),
(42,629,'2016-01-06 18:33:37','2016-01-06 18:33:37',NULL),
(45,555,'2015-11-10 14:25:21','2015-11-10 14:52:40','2015-11-10 14:52:40'),
(45,610,'2015-11-10 14:54:00','2015-11-10 15:48:15','2015-11-10 15:48:15'),
(45,615,'2015-11-10 14:55:59','2015-11-10 14:56:46','2015-11-10 14:56:46'),
(45,653,'2015-11-10 14:20:42','2015-11-10 14:52:43','2015-11-10 14:52:43'),
(45,655,'2015-11-10 15:47:58','2015-11-10 15:47:58',NULL),
(65,786,'2018-03-26 15:17:10','2018-03-26 15:17:10',NULL);

/*!40000 ALTER TABLE `managers` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
