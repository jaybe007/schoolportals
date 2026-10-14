-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: edusphere_saas
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `academic_sessions`
--

DROP TABLE IF EXISTS `academic_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `academic_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_current` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_session_name` (`school_id`,`name`),
  CONSTRAINT `academic_sessions_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `academic_sessions`
--

LOCK TABLES `academic_sessions` WRITE;
/*!40000 ALTER TABLE `academic_sessions` DISABLE KEYS */;
INSERT INTO `academic_sessions` VALUES (1,1,'2025/2026','2025-09-08','2026-07-24',1,'2026-10-13 13:28:02','2026-10-13 13:28:02'),(2,2,'2025/2026','2025-09-15','2026-07-18',1,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(3,1,'2027/2028_39','2027-09-01','2028-07-31',0,'2026-10-13 14:06:50','2026-10-13 14:06:50'),(4,1,'2027/2028_37','2027-09-01','2028-07-31',0,'2026-10-13 14:08:37','2026-10-13 14:08:37');
/*!40000 ALTER TABLE `academic_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `academic_terms`
--

DROP TABLE IF EXISTS `academic_terms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `academic_terms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `name` enum('First Term','Second Term','Third Term') NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_current` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_term_session` (`school_id`,`session_id`,`name`),
  KEY `session_id` (`session_id`),
  CONSTRAINT `academic_terms_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `academic_terms_ibfk_2` FOREIGN KEY (`session_id`) REFERENCES `academic_sessions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `academic_terms`
--

LOCK TABLES `academic_terms` WRITE;
/*!40000 ALTER TABLE `academic_terms` DISABLE KEYS */;
INSERT INTO `academic_terms` VALUES (1,1,1,'First Term','2025-09-08','2025-12-15',1,'2026-10-13 13:28:02','2026-10-13 13:28:02'),(2,1,1,'Second Term','2026-01-12','2026-04-10',0,'2026-10-13 13:28:02','2026-10-13 13:28:02'),(3,1,1,'Third Term','2026-05-04','2026-07-24',0,'2026-10-13 13:28:02','2026-10-13 13:28:02'),(4,2,2,'First Term','2025-09-15','2025-12-18',1,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(5,2,2,'Second Term','2026-01-15','2026-04-15',0,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(6,2,2,'Third Term','2026-05-02','2026-07-18',0,'2026-10-13 13:28:03','2026-10-13 13:28:03');
/*!40000 ALTER TABLE `academic_terms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admissions`
--

DROP TABLE IF EXISTS `admissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `application_number` varchar(50) NOT NULL,
  `applicant_first_name` varchar(100) NOT NULL,
  `applicant_last_name` varchar(100) NOT NULL,
  `applicant_gender` enum('male','female') NOT NULL,
  `date_of_birth` date NOT NULL,
  `applied_class_level_id` int(11) NOT NULL,
  `parent_name` varchar(150) NOT NULL,
  `parent_email` varchar(150) NOT NULL,
  `parent_phone` varchar(50) NOT NULL,
  `address` text DEFAULT NULL,
  `previous_school` varchar(200) DEFAULT NULL,
  `status` enum('pending','under_review','admitted','rejected','waitlisted') DEFAULT 'pending',
  `fee_paid` tinyint(1) DEFAULT 0,
  `assigned_admission_number` varchar(50) DEFAULT NULL,
  `review_notes` text DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_admission_app_num` (`school_id`,`application_number`),
  KEY `applied_class_level_id` (`applied_class_level_id`),
  CONSTRAINT `admissions_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `admissions_ibfk_2` FOREIGN KEY (`applied_class_level_id`) REFERENCES `class_levels` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admissions`
--

LOCK TABLES `admissions` WRITE;
/*!40000 ALTER TABLE `admissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `admissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `announcements`
--

DROP TABLE IF EXISTS `announcements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `announcements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `target_audience` enum('all','staff','parents','students') DEFAULT 'all',
  `is_published` tinyint(1) DEFAULT 1,
  `published_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `school_id` (`school_id`),
  CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `announcements`
--

LOCK TABLES `announcements` WRITE;
/*!40000 ALTER TABLE `announcements` DISABLE KEYS */;
INSERT INTO `announcements` VALUES (1,1,'Resumption for Academic Session 2025/2026','Welcome back all students and parents. First term academic activities commence at 7:45am prompt.','all',1,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(2,1,'Inter-House Sports Festival Announcement','Annual track and field championships scheduled for Green House, Blue House, Yellow House and Red House.','all',1,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(3,NULL,'Platform Maintenance Notice: Security & Core Updates','EduSphere SaaS will undergo routine optimization this Sunday between 2:00 AM and 3:00 AM WAT.','all',1,'2026-10-13 13:28:03','2026-10-13 13:28:03');
/*!40000 ALTER TABLE `announcements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `assessment_configs`
--

DROP TABLE IF EXISTS `assessment_configs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `assessment_configs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `term_id` int(11) NOT NULL,
  `ca1_weight` decimal(5,2) DEFAULT 20.00,
  `ca2_weight` decimal(5,2) DEFAULT 20.00,
  `exam_weight` decimal(5,2) DEFAULT 60.00,
  `pass_mark` decimal(5,2) DEFAULT 50.00,
  `is_locked` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_assess_config` (`school_id`,`session_id`,`term_id`),
  CONSTRAINT `assessment_configs_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assessment_configs`
--

LOCK TABLES `assessment_configs` WRITE;
/*!40000 ALTER TABLE `assessment_configs` DISABLE KEYS */;
INSERT INTO `assessment_configs` VALUES (1,1,1,1,20.00,20.00,60.00,50.00,0,'2026-10-13 13:28:02','2026-10-13 13:28:02'),(2,2,2,4,20.00,20.00,60.00,50.00,0,'2026-10-13 13:28:03','2026-10-13 13:28:03');
/*!40000 ALTER TABLE `assessment_configs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attendance`
--

DROP TABLE IF EXISTS `attendance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `term_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `class_arm_id` int(11) NOT NULL,
  `attendance_date` date NOT NULL,
  `status` enum('present','absent','late','excused') DEFAULT 'present',
  `remarks` varchar(255) DEFAULT NULL,
  `recorded_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_attendance_entry` (`school_id`,`student_id`,`attendance_date`),
  KEY `student_id` (`student_id`),
  KEY `class_arm_id` (`class_arm_id`),
  CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_ibfk_3` FOREIGN KEY (`class_arm_id`) REFERENCES `class_arms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance`
--

LOCK TABLES `attendance` WRITE;
/*!40000 ALTER TABLE `attendance` DISABLE KEYS */;
INSERT INTO `attendance` VALUES (1,1,1,1,1,1,'2025-10-06','present',NULL,3,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(2,1,1,1,1,1,'2025-10-07','present',NULL,3,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(3,1,1,1,1,1,'2025-10-08','present',NULL,3,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(4,1,1,1,1,1,'2025-10-09','present',NULL,3,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(5,1,1,1,1,1,'2025-10-10','present',NULL,3,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(6,1,1,1,1,1,'2025-10-13','present',NULL,3,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(7,1,1,1,1,1,'2025-10-14','present',NULL,3,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(8,1,1,1,1,1,'2025-10-15','present',NULL,3,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(9,1,1,1,1,1,'2025-10-16','present',NULL,3,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(10,1,1,1,1,1,'2025-10-17','present',NULL,3,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(11,1,1,1,2,1,'2025-10-06','present',NULL,3,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(12,1,1,1,2,1,'2025-10-07','present',NULL,3,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(13,1,1,1,2,1,'2025-10-08','present',NULL,3,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(14,1,1,1,2,1,'2025-10-09','present',NULL,3,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(15,1,1,1,2,1,'2025-10-10','present',NULL,3,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(16,1,1,1,2,1,'2025-10-13','present',NULL,3,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(17,1,1,1,2,1,'2025-10-14','present',NULL,3,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(18,1,1,1,2,1,'2025-10-15','present',NULL,3,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(19,1,1,1,2,1,'2025-10-16','present',NULL,3,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(20,1,1,1,2,1,'2025-10-17','present',NULL,3,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(21,1,1,1,3,1,'2025-10-06','present',NULL,3,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(22,1,1,1,3,1,'2025-10-07','present',NULL,3,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(23,1,1,1,3,1,'2025-10-08','present',NULL,3,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(24,1,1,1,3,1,'2025-10-09','present',NULL,3,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(25,1,1,1,3,1,'2025-10-10','present',NULL,3,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(26,1,1,1,3,1,'2025-10-13','present',NULL,3,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(27,1,1,1,3,1,'2025-10-14','present',NULL,3,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(28,1,1,1,3,1,'2025-10-15','present',NULL,3,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(29,1,1,1,3,1,'2025-10-16','present',NULL,3,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(30,1,1,1,3,1,'2025-10-17','present',NULL,3,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(31,1,1,1,4,1,'2025-10-06','present',NULL,3,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(32,1,1,1,4,1,'2025-10-07','present',NULL,3,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(33,1,1,1,4,1,'2025-10-08','present',NULL,3,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(34,1,1,1,4,1,'2025-10-09','present',NULL,3,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(35,1,1,1,4,1,'2025-10-10','present',NULL,3,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(36,1,1,1,4,1,'2025-10-13','present',NULL,3,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(37,1,1,1,4,1,'2025-10-14','present',NULL,3,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(38,1,1,1,4,1,'2025-10-15','present',NULL,3,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(39,1,1,1,4,1,'2025-10-16','present',NULL,3,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(40,1,1,1,4,1,'2025-10-17','present',NULL,3,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(41,1,1,1,5,1,'2025-10-06','present',NULL,3,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(42,1,1,1,5,1,'2025-10-07','present',NULL,3,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(43,1,1,1,5,1,'2025-10-08','present',NULL,3,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(44,1,1,1,5,1,'2025-10-09','absent',NULL,3,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(45,1,1,1,5,1,'2025-10-10','present',NULL,3,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(46,1,1,1,5,1,'2025-10-13','present',NULL,3,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(47,1,1,1,5,1,'2025-10-14','present',NULL,3,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(48,1,1,1,5,1,'2025-10-15','present',NULL,3,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(49,1,1,1,5,1,'2025-10-16','present',NULL,3,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(50,1,1,1,5,1,'2025-10-17','present',NULL,3,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(51,2,2,4,6,4,'2025-10-06','present',NULL,6,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(52,2,2,4,6,4,'2025-10-07','present',NULL,6,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(53,2,2,4,6,4,'2025-10-08','present',NULL,6,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(54,2,2,4,6,4,'2025-10-09','present',NULL,6,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(55,2,2,4,6,4,'2025-10-10','present',NULL,6,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(56,2,2,4,7,4,'2025-10-06','present',NULL,6,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(57,2,2,4,7,4,'2025-10-07','present',NULL,6,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(58,2,2,4,7,4,'2025-10-08','present',NULL,6,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(59,2,2,4,7,4,'2025-10-09','present',NULL,6,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(60,2,2,4,7,4,'2025-10-10','present',NULL,6,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(61,2,2,4,8,4,'2025-10-06','present',NULL,6,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(62,2,2,4,8,4,'2025-10-07','present',NULL,6,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(63,2,2,4,8,4,'2025-10-08','present',NULL,6,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(64,2,2,4,8,4,'2025-10-09','present',NULL,6,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(65,2,2,4,8,4,'2025-10-10','present',NULL,6,'2026-10-13 13:28:03','2026-10-13 13:28:03');
/*!40000 ALTER TABLE `attendance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(100) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_audit_school` (`school_id`),
  KEY `idx_audit_action` (`action`)
) ENGINE=InnoDB AUTO_INCREMENT=171 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES (1,NULL,1,'system_database_seeded','database',1,NULL,'{\"status\":\"seeded_successfully\"}','127.0.0.1','CLI Seeder Engine','2026-10-03 13:28:03'),(2,1,5,'academic_scores_published','exam_scores',1,NULL,'{\"term\":\"First Term\",\"session\":\"2025/2026\",\"status\":\"published\"}','102.89.44.12','Mozilla/5.0 (Windows NT 10.0; Win64; x64)','2026-10-08 13:28:03'),(3,1,8,'fee_payment_verified','payment_receipt',1,NULL,'{\"receipt\":\"REC-2025-001\",\"amount\":160000}','197.210.84.18','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)','2026-10-09 13:28:03'),(4,1,6,'student_admitted_enrolled','student',1,NULL,'{\"admission_number\":\"APX/2025/001\"}','102.89.44.12','Mozilla/5.0 (Windows NT 10.0; Win64; x64)','2026-10-10 13:28:03'),(5,1,1,'domain_verified_active','tenant_domain',1,NULL,'{\"domain\":\"apexschools.edu.ng\",\"status\":\"verified\"}','127.0.0.1','Domain Health Checker','2026-10-11 13:28:03'),(6,NULL,1,'platform_security_backup','backup',1,NULL,'{\"archive\":\"edusphere_daily_enc.tar.gz\",\"status\":\"success\"}','127.0.0.1','Automated Cron Engine','2026-10-12 13:28:03'),(7,NULL,1,'demo_quick_login','user',1,NULL,'{\"email\":\"superadmin@edusphere.example\"}','::1','Unknown','2026-10-13 13:29:11'),(8,1,4,'demo_quick_login','user',4,NULL,'{\"email\":\"admin@apexcollege.edu.ng\"}','::1','Unknown','2026-10-13 13:29:12'),(9,NULL,3,'demo_quick_login','user',3,NULL,'{\"email\":\"salesrep@edusphere.example\"}','::1','Unknown','2026-10-13 13:29:13'),(10,1,10,'demo_quick_login','user',10,NULL,'{\"email\":\"student@apexcollege.edu.ng\"}','::1','Unknown','2026-10-13 13:29:13'),(11,1,9,'demo_quick_login','user',9,NULL,'{\"email\":\"parent@apexcollege.edu.ng\"}','::1','Unknown','2026-10-13 13:29:13'),(12,1,10,'demo_quick_login','user',10,NULL,'{\"email\":\"student@apexcollege.edu.ng\"}','::1','Unknown','2026-10-13 13:29:41'),(13,1,9,'demo_quick_login','user',9,NULL,'{\"email\":\"parent@apexcollege.edu.ng\"}','::1','Unknown','2026-10-13 13:29:41'),(14,1,4,'demo_quick_login','user',4,NULL,'{\"email\":\"admin@apexcollege.edu.ng\"}','::1','Unknown','2026-10-13 13:30:08'),(15,2,11,'demo_quick_login','user',11,NULL,'{\"email\":\"admin@horizonacademy.sch.ng\"}','::1','Unknown','2026-10-13 13:30:08'),(16,1,4,'unauthorized_cross_tenant_report_card_attempt','student',6,NULL,'{\"attempted_student_id\":6,\"user_school_id\":1}','::1','Unknown','2026-10-13 13:30:08'),(17,2,11,'unauthorized_cross_tenant_report_card_attempt','student',1,NULL,'{\"attempted_student_id\":1,\"user_school_id\":2}','::1','Unknown','2026-10-13 13:30:08'),(18,1,4,'unauthorized_cross_tenant_attendance_attempt','class_arms',4,NULL,'{\"school_id\":1,\"user_id\":4}','::1','Unknown','2026-10-13 13:30:08'),(19,1,4,'unauthorized_cross_tenant_exam_attempt','class_arms',4,NULL,'{\"school_id\":1,\"user_id\":4}','::1','Unknown','2026-10-13 13:30:08'),(20,1,4,'unauthorized_cross_tenant_finance_attempt','fee_invoices',4,NULL,'{\"school_id\":1,\"user_id\":4}','::1','Unknown','2026-10-13 13:30:08'),(21,1,9,'demo_quick_login','user',9,NULL,'{\"email\":\"parent@apexcollege.edu.ng\"}','::1','Unknown','2026-10-13 13:30:08'),(22,NULL,NULL,'portal_term_invoices_generated','school_portal_fee_agreement',1,NULL,'{\"school_id\":1,\"session_id\":1,\"term_id\":1,\"created\":0,\"skipped\":5}','127.0.0.1','Unknown','2026-10-13 13:30:24'),(23,NULL,NULL,'portal_term_invoices_generated','school_portal_fee_agreement',1,NULL,'{\"school_id\":1,\"session_id\":1,\"term_id\":1,\"created\":0,\"skipped\":5}','127.0.0.1','Unknown','2026-10-13 13:30:24'),(24,NULL,NULL,'portal_payment_allocated','portal_payment',4,NULL,'{\"invoice_id\":4,\"amount\":1500,\"distributable\":1477.5,\"platform\":664.87,\"school_incentive\":591,\"sales_rep_comm\":221.63,\"reference\":\"TEST-PAY-20261013-17438\"}','127.0.0.1','Unknown','2026-10-13 13:30:24'),(25,NULL,NULL,'school_payout_requested','school_payout_request',1,NULL,'{\"school_id\":1,\"amount\":500,\"request_code\":\"POUT-SCH-20261013-4543\"}','127.0.0.1','Unknown','2026-10-13 13:30:24'),(26,NULL,NULL,'school_payout_approved_paid','school_payout_request',1,NULL,'{\"school_id\":1,\"amount\":500,\"disbursement_ref\":\"DISB-TEST-20261013-9999\",\"approver_id\":1}','127.0.0.1','Unknown','2026-10-13 13:30:24'),(27,NULL,NULL,'portal_payment_refunded','portal_payment',4,NULL,'{\"amount\":1500,\"reason\":\"Duplicate test deposit reversal\",\"authorized_by\":1}','127.0.0.1','Unknown','2026-10-13 13:30:24'),(28,NULL,NULL,'portal_fee_agreement_accepted','school_portal_fee_agreement',2,NULL,'{\"school_id\":2,\"term_id\":4,\"accepted_by\":1}','127.0.0.1','Unknown','2026-10-13 13:30:24'),(29,NULL,NULL,'portal_term_invoices_generated','school_portal_fee_agreement',2,NULL,'{\"school_id\":2,\"session_id\":2,\"term_id\":4,\"created\":3,\"skipped\":0}','127.0.0.1','Unknown','2026-10-13 13:30:24'),(30,1,4,'demo_quick_login','user',4,NULL,'{\"email\":\"admin@apexcollege.edu.ng\"}','::1','Unknown','2026-10-13 13:43:18'),(31,1,10,'demo_quick_login','user',10,NULL,'{\"email\":\"student@apexcollege.edu.ng\"}','::1','Unknown','2026-10-13 13:43:18'),(32,1,9,'demo_quick_login','user',9,NULL,'{\"email\":\"parent@apexcollege.edu.ng\"}','::1','Unknown','2026-10-13 13:43:18'),(33,1,4,'demo_quick_login','user',4,NULL,'{\"email\":\"admin@apexcollege.edu.ng\"}','::1','Unknown','2026-10-13 13:46:02'),(34,1,10,'demo_quick_login','user',10,NULL,'{\"email\":\"student@apexcollege.edu.ng\"}','::1','Unknown','2026-10-13 13:46:02'),(35,1,9,'demo_quick_login','user',9,NULL,'{\"email\":\"parent@apexcollege.edu.ng\"}','::1','Unknown','2026-10-13 13:46:02'),(36,1,4,'demo_quick_login','user',4,NULL,'{\"email\":\"admin@apexcollege.edu.ng\"}','::1','Unknown','2026-10-13 13:46:14'),(37,1,10,'demo_quick_login','user',10,NULL,'{\"email\":\"student@apexcollege.edu.ng\"}','::1','Unknown','2026-10-13 13:46:14'),(38,1,9,'demo_quick_login','user',9,NULL,'{\"email\":\"parent@apexcollege.edu.ng\"}','::1','Unknown','2026-10-13 13:46:14'),(39,1,4,'demo_quick_login','user',4,NULL,'{\"email\":\"admin@apexcollege.edu.ng\"}','::1','Unknown','2026-10-13 13:48:38'),(40,1,10,'demo_quick_login','user',10,NULL,'{\"email\":\"student@apexcollege.edu.ng\"}','::1','Unknown','2026-10-13 13:48:38'),(41,1,9,'demo_quick_login','user',9,NULL,'{\"email\":\"parent@apexcollege.edu.ng\"}','::1','Unknown','2026-10-13 13:48:38'),(42,1,4,'school_notice_created','school_notices',4,NULL,'{\"title\":\"Confidential Staff Meeting 1791899318\",\"category\":\"academic\",\"target_audience\":\"staff\"}','::1','Unknown','2026-10-13 13:48:38'),(43,1,4,'school_notice_created','school_notices',5,NULL,'{\"title\":\"Inter-House Athletic Practice 1791899318\",\"category\":\"sports\",\"target_audience\":\"students\"}','::1','Unknown','2026-10-13 13:48:39'),(44,1,4,'school_message_to_parent_sent','parent_school_messages',4,NULL,'{\"parent_id\":1,\"student_id\":1,\"subject\":\"Student Academic Commendation 1791899319\"}','::1','Unknown','2026-10-13 13:48:39'),(45,1,10,'student_fee_paid','fee_invoices',6,NULL,'{\"receipt_number\":\"REC-2026-4427\",\"amount\":5000,\"student_id\":1}','::1','Unknown','2026-10-13 13:48:39'),(46,1,4,'demo_quick_login','user',4,NULL,'{\"email\":\"admin@apexcollege.edu.ng\"}','::1','Unknown','2026-10-13 13:49:41'),(47,1,10,'demo_quick_login','user',10,NULL,'{\"email\":\"student@apexcollege.edu.ng\"}','::1','Unknown','2026-10-13 13:49:41'),(48,1,9,'demo_quick_login','user',9,NULL,'{\"email\":\"parent@apexcollege.edu.ng\"}','::1','Unknown','2026-10-13 13:49:41'),(49,1,4,'school_notice_created','school_notices',6,NULL,'{\"title\":\"Confidential Staff Meeting 1791899381\",\"category\":\"academic\",\"target_audience\":\"staff\"}','::1','Unknown','2026-10-13 13:49:41'),(50,1,4,'school_notice_created','school_notices',7,NULL,'{\"title\":\"Inter-House Athletic Practice 1791899382\",\"category\":\"sports\",\"target_audience\":\"students\"}','::1','Unknown','2026-10-13 13:49:42'),(51,1,4,'school_message_to_parent_sent','parent_school_messages',6,NULL,'{\"parent_id\":1,\"student_id\":1,\"subject\":\"Student Academic Commendation 1791899382\"}','::1','Unknown','2026-10-13 13:49:42'),(52,1,10,'student_fee_paid','fee_invoices',1,NULL,'{\"receipt_number\":\"REC-2026-9810\",\"amount\":5000,\"student_id\":1}','::1','Unknown','2026-10-13 13:49:42'),(53,1,10,'portal_payment_allocated','portal_payment',5,NULL,'{\"invoice_id\":1,\"amount\":3000,\"distributable\":2955,\"platform\":1329.75,\"school_incentive\":1182,\"sales_rep_comm\":443.25,\"reference\":\"PAY-ONLINE-STU-20261013-32731\"}','::1','Unknown','2026-10-13 13:49:42'),(54,1,4,'demo_quick_login','user',4,NULL,'{\"email\":\"admin@apexcollege.edu.ng\"}','::1','Unknown','2026-10-13 13:50:03'),(55,1,10,'demo_quick_login','user',10,NULL,'{\"email\":\"student@apexcollege.edu.ng\"}','::1','Unknown','2026-10-13 13:50:03'),(56,1,9,'demo_quick_login','user',9,NULL,'{\"email\":\"parent@apexcollege.edu.ng\"}','::1','Unknown','2026-10-13 13:50:03'),(57,1,4,'school_notice_created','school_notices',8,NULL,'{\"title\":\"Confidential Staff Meeting 1791899403\",\"category\":\"academic\",\"target_audience\":\"staff\"}','::1','Unknown','2026-10-13 13:50:03'),(58,1,4,'school_notice_created','school_notices',9,NULL,'{\"title\":\"Inter-House Athletic Practice 1791899403\",\"category\":\"sports\",\"target_audience\":\"students\"}','::1','Unknown','2026-10-13 13:50:03'),(59,1,4,'school_message_to_parent_sent','parent_school_messages',8,NULL,'{\"parent_id\":1,\"student_id\":1,\"subject\":\"Student Academic Commendation 1791899403\"}','::1','Unknown','2026-10-13 13:50:03'),(60,1,10,'student_fee_paid','fee_invoices',1,NULL,'{\"receipt_number\":\"REC-2026-1370\",\"amount\":5000,\"student_id\":1}','::1','Unknown','2026-10-13 13:50:03'),(61,1,10,'portal_payment_allocated','portal_payment',6,NULL,'{\"invoice_id\":1,\"amount\":3000,\"distributable\":2955,\"platform\":1329.75,\"school_incentive\":1182,\"sales_rep_comm\":443.25,\"reference\":\"PAY-ONLINE-STU-20261013-43503\"}','::1','Unknown','2026-10-13 13:50:03'),(62,1,4,'demo_quick_login','user',4,NULL,'{\"email\":\"admin@apexcollege.edu.ng\"}','::1','Unknown','2026-10-13 13:50:32'),(63,1,10,'demo_quick_login','user',10,NULL,'{\"email\":\"student@apexcollege.edu.ng\"}','::1','Unknown','2026-10-13 13:50:32'),(64,1,9,'demo_quick_login','user',9,NULL,'{\"email\":\"parent@apexcollege.edu.ng\"}','::1','Unknown','2026-10-13 13:50:32'),(65,1,4,'school_notice_created','school_notices',10,NULL,'{\"title\":\"Confidential Staff Meeting 1791899432\",\"category\":\"academic\",\"target_audience\":\"staff\"}','::1','Unknown','2026-10-13 13:50:32'),(66,1,4,'school_notice_created','school_notices',11,NULL,'{\"title\":\"Inter-House Athletic Practice 1791899432\",\"category\":\"sports\",\"target_audience\":\"students\"}','::1','Unknown','2026-10-13 13:50:32'),(67,1,4,'school_message_to_parent_sent','parent_school_messages',10,NULL,'{\"parent_id\":1,\"student_id\":1,\"subject\":\"Student Academic Commendation 1791899432\"}','::1','Unknown','2026-10-13 13:50:32'),(68,1,10,'student_fee_paid','fee_invoices',1,NULL,'{\"receipt_number\":\"REC-2026-8464\",\"amount\":5000,\"student_id\":1}','::1','Unknown','2026-10-13 13:50:32'),(69,1,10,'portal_payment_allocated','portal_payment',7,NULL,'{\"invoice_id\":1,\"amount\":3000,\"distributable\":2955,\"platform\":1329.75,\"school_incentive\":1182,\"sales_rep_comm\":443.25,\"reference\":\"PAY-ONLINE-STU-20261013-16340\"}','::1','Unknown','2026-10-13 13:50:32'),(70,1,4,'demo_quick_login','user',4,NULL,'{\"email\":\"admin@apexcollege.edu.ng\"}','::1','Unknown','2026-10-13 13:51:03'),(71,1,10,'demo_quick_login','user',10,NULL,'{\"email\":\"student@apexcollege.edu.ng\"}','::1','Unknown','2026-10-13 13:51:03'),(72,1,9,'demo_quick_login','user',9,NULL,'{\"email\":\"parent@apexcollege.edu.ng\"}','::1','Unknown','2026-10-13 13:51:03'),(73,1,4,'school_notice_created','school_notices',12,NULL,'{\"title\":\"Confidential Staff Meeting 1791899463\",\"category\":\"academic\",\"target_audience\":\"staff\"}','::1','Unknown','2026-10-13 13:51:03'),(74,1,4,'school_notice_created','school_notices',13,NULL,'{\"title\":\"Inter-House Athletic Practice 1791899463\",\"category\":\"sports\",\"target_audience\":\"students\"}','::1','Unknown','2026-10-13 13:51:03'),(75,1,4,'school_message_to_parent_sent','parent_school_messages',12,NULL,'{\"parent_id\":1,\"student_id\":1,\"subject\":\"Student Academic Commendation 1791899463\"}','::1','Unknown','2026-10-13 13:51:03'),(76,1,10,'student_fee_paid','fee_invoices',1,NULL,'{\"receipt_number\":\"REC-2026-2015\",\"amount\":5000,\"student_id\":1}','::1','Unknown','2026-10-13 13:51:03'),(77,1,10,'portal_payment_allocated','portal_payment',8,NULL,'{\"invoice_id\":1,\"amount\":3000,\"distributable\":2955,\"platform\":1329.75,\"school_incentive\":1182,\"sales_rep_comm\":443.25,\"reference\":\"PAY-ONLINE-STU-20261013-84778\"}','::1','Unknown','2026-10-13 13:51:03'),(78,1,10,'demo_quick_login','user',10,NULL,'{\"email\":\"student@apexcollege.edu.ng\"}','::1','Unknown','2026-10-13 13:51:17'),(79,1,9,'demo_quick_login','user',9,NULL,'{\"email\":\"parent@apexcollege.edu.ng\"}','::1','Unknown','2026-10-13 13:51:17'),(80,NULL,1,'demo_quick_login','user',1,NULL,'{\"email\":\"superadmin@edusphere.example\"}','::1','Unknown','2026-10-13 13:51:38'),(81,1,4,'demo_quick_login','user',4,NULL,'{\"email\":\"admin@apexcollege.edu.ng\"}','::1','Unknown','2026-10-13 13:51:38'),(82,NULL,3,'demo_quick_login','user',3,NULL,'{\"email\":\"salesrep@edusphere.example\"}','::1','Unknown','2026-10-13 13:51:39'),(83,1,10,'demo_quick_login','user',10,NULL,'{\"email\":\"student@apexcollege.edu.ng\"}','::1','Unknown','2026-10-13 13:51:39'),(84,1,9,'demo_quick_login','user',9,NULL,'{\"email\":\"parent@apexcollege.edu.ng\"}','::1','Unknown','2026-10-13 13:51:39'),(85,1,4,'demo_quick_login','user',4,NULL,'{\"email\":\"admin@apexcollege.edu.ng\"}','::1','Unknown','2026-10-13 13:53:59'),(86,1,10,'demo_quick_login','user',10,NULL,'{\"email\":\"student@apexcollege.edu.ng\"}','::1','Unknown','2026-10-13 13:53:59'),(87,1,9,'demo_quick_login','user',9,NULL,'{\"email\":\"parent@apexcollege.edu.ng\"}','::1','Unknown','2026-10-13 13:53:59'),(88,1,4,'school_notice_created','school_notices',14,NULL,'{\"title\":\"Confidential Staff Meeting 1791899640\",\"category\":\"academic\",\"target_audience\":\"staff\"}','::1','Unknown','2026-10-13 13:54:00'),(89,1,4,'school_notice_created','school_notices',15,NULL,'{\"title\":\"Inter-House Athletic Practice 1791899640\",\"category\":\"sports\",\"target_audience\":\"students\"}','::1','Unknown','2026-10-13 13:54:00'),(90,1,4,'school_message_to_parent_sent','parent_school_messages',14,NULL,'{\"parent_id\":1,\"student_id\":1,\"subject\":\"Student Academic Commendation 1791899640\"}','::1','Unknown','2026-10-13 13:54:00'),(91,1,10,'student_fee_paid','fee_invoices',1,NULL,'{\"receipt_number\":\"REC-2026-1962\",\"amount\":5000,\"student_id\":1}','::1','Unknown','2026-10-13 13:54:00'),(92,1,10,'portal_payment_allocated','portal_payment',9,NULL,'{\"invoice_id\":1,\"amount\":3000,\"distributable\":2955,\"platform\":1329.75,\"school_incentive\":1182,\"sales_rep_comm\":443.25,\"reference\":\"PAY-ONLINE-STU-20261013-59540\"}','::1','Unknown','2026-10-13 13:54:00'),(93,1,10,'demo_quick_login','user',10,NULL,'{\"email\":\"student@apexcollege.edu.ng\"}','::1','Unknown','2026-10-13 14:01:28'),(94,1,9,'demo_quick_login','user',9,NULL,'{\"email\":\"parent@apexcollege.edu.ng\"}','::1','Unknown','2026-10-13 14:01:29'),(95,NULL,1,'demo_quick_login','user',1,NULL,'{\"email\":\"superadmin@edusphere.example\"}','::1','Unknown','2026-10-13 14:06:49'),(96,NULL,1,'subscription_plan_created','subscription_plans',4,NULL,'{\"name\":\"Elite Campus Plan\",\"price\":850000,\"code\":\"plan_test_493\"}','::1','Unknown','2026-10-13 14:06:49'),(97,NULL,1,'tenant_custom_domain_added','tenant_domains',4,NULL,'{\"school_id\":1,\"domain\":\"portal.apexcollege-130.org.ng\"}','::1','Unknown','2026-10-13 14:06:50'),(98,1,4,'demo_quick_login','user',4,NULL,'{\"email\":\"admin@apexcollege.edu.ng\"}','::1','Unknown','2026-10-13 14:06:50'),(99,1,4,'student_enrolled_direct','student',9,NULL,'{\"admission_number\":\"SCH/2026/006\",\"name\":\"Kareem Ogunlesi_287\",\"class_arm_id\":1}','::1','Unknown','2026-10-13 14:06:50'),(100,1,4,'academic_session_created','academic_sessions',3,NULL,'{\"name\":\"2027/2028_39\",\"school_id\":1}','::1','Unknown','2026-10-13 14:06:50'),(101,1,4,'class_arm_created','class_arms',6,NULL,'{\"name\":\"Titanium_46\",\"class_level_id\":1}','::1','Unknown','2026-10-13 14:06:50'),(102,1,4,'subject_created','subjects',12,NULL,'{\"name\":\"Robotics & AI\",\"code\":\"ROB_28\"}','::1','Unknown','2026-10-13 14:06:50'),(103,1,4,'staff_onboarded','staff',8,NULL,'{\"employee_number\":\"EMP/2026/005\",\"name\":\"Engr. Daniel Alabi\",\"role\":\"teacher\"}','::1','Unknown','2026-10-13 14:06:50'),(104,1,4,'fee_invoice_created','fee_invoices',7,NULL,'{\"invoice_number\":\"INV-2026-005\",\"student_id\":9,\"total_amount\":195000}','::1','Unknown','2026-10-13 14:06:50'),(105,1,NULL,'commission_approved','sales_commission',2,NULL,'{\"approver_user_id\":2,\"commission_amount\":\"50000.00\"}','127.0.0.1','Unknown','2026-10-13 14:06:58'),(106,1,NULL,'commission_payout_recorded','sales_commission',2,NULL,'{\"payout_reference\":\"BNK-REF-99281\"}','127.0.0.1','Unknown','2026-10-13 14:06:58'),(107,1,NULL,'commission_approved','sales_commission',3,NULL,'{\"approver_user_id\":2,\"commission_amount\":\"50000.00\"}','127.0.0.1','Unknown','2026-10-13 14:07:50'),(108,1,NULL,'commission_payout_recorded','sales_commission',3,NULL,'{\"payout_reference\":\"BNK-REF-99281\"}','127.0.0.1','Unknown','2026-10-13 14:07:50'),(109,1,10,'demo_quick_login','user',10,NULL,'{\"email\":\"student@apexcollege.edu.ng\"}','::1','Unknown','2026-10-13 14:08:32'),(110,1,9,'demo_quick_login','user',9,NULL,'{\"email\":\"parent@apexcollege.edu.ng\"}','::1','Unknown','2026-10-13 14:08:32'),(111,NULL,1,'demo_quick_login','user',1,NULL,'{\"email\":\"superadmin@edusphere.example\"}','::1','Unknown','2026-10-13 14:08:33'),(112,1,4,'demo_quick_login','user',4,NULL,'{\"email\":\"admin@apexcollege.edu.ng\"}','::1','Unknown','2026-10-13 14:08:33'),(113,NULL,3,'demo_quick_login','user',3,NULL,'{\"email\":\"salesrep@edusphere.example\"}','::1','Unknown','2026-10-13 14:08:34'),(114,1,10,'demo_quick_login','user',10,NULL,'{\"email\":\"student@apexcollege.edu.ng\"}','::1','Unknown','2026-10-13 14:08:35'),(115,1,9,'demo_quick_login','user',9,NULL,'{\"email\":\"parent@apexcollege.edu.ng\"}','::1','Unknown','2026-10-13 14:08:35'),(116,1,4,'demo_quick_login','user',4,NULL,'{\"email\":\"admin@apexcollege.edu.ng\"}','::1','Unknown','2026-10-13 14:08:35'),(117,2,11,'demo_quick_login','user',11,NULL,'{\"email\":\"admin@horizonacademy.sch.ng\"}','::1','Unknown','2026-10-13 14:08:35'),(118,1,4,'unauthorized_cross_tenant_report_card_attempt','student',6,NULL,'{\"attempted_student_id\":6,\"user_school_id\":1}','::1','Unknown','2026-10-13 14:08:35'),(119,2,11,'unauthorized_cross_tenant_report_card_attempt','student',1,NULL,'{\"attempted_student_id\":1,\"user_school_id\":2}','::1','Unknown','2026-10-13 14:08:35'),(120,1,4,'unauthorized_cross_tenant_attendance_attempt','class_arms',4,NULL,'{\"school_id\":1,\"user_id\":4}','::1','Unknown','2026-10-13 14:08:35'),(121,1,4,'unauthorized_cross_tenant_exam_attempt','class_arms',4,NULL,'{\"school_id\":1,\"user_id\":4}','::1','Unknown','2026-10-13 14:08:35'),(122,1,4,'unauthorized_cross_tenant_finance_attempt','fee_invoices',4,NULL,'{\"school_id\":1,\"user_id\":4}','::1','Unknown','2026-10-13 14:08:35'),(123,1,9,'demo_quick_login','user',9,NULL,'{\"email\":\"parent@apexcollege.edu.ng\"}','::1','Unknown','2026-10-13 14:08:35'),(124,NULL,NULL,'portal_term_invoices_generated','school_portal_fee_agreement',1,NULL,'{\"school_id\":1,\"session_id\":1,\"term_id\":1,\"created\":1,\"skipped\":5}','127.0.0.1','Unknown','2026-10-13 14:08:36'),(125,NULL,NULL,'portal_term_invoices_generated','school_portal_fee_agreement',1,NULL,'{\"school_id\":1,\"session_id\":1,\"term_id\":1,\"created\":0,\"skipped\":6}','127.0.0.1','Unknown','2026-10-13 14:08:36'),(126,NULL,NULL,'portal_payment_allocated','portal_payment',10,NULL,'{\"invoice_id\":4,\"amount\":1500,\"distributable\":1477.5,\"platform\":664.87,\"school_incentive\":591,\"sales_rep_comm\":221.63,\"reference\":\"TEST-PAY-20261013-65949\"}','127.0.0.1','Unknown','2026-10-13 14:08:36'),(127,NULL,NULL,'school_payout_requested','school_payout_request',2,NULL,'{\"school_id\":1,\"amount\":500,\"request_code\":\"POUT-SCH-20261013-5621\"}','127.0.0.1','Unknown','2026-10-13 14:08:36'),(128,NULL,NULL,'school_payout_approved_paid','school_payout_request',2,NULL,'{\"school_id\":1,\"amount\":500,\"disbursement_ref\":\"DISB-TEST-20261013-9999\",\"approver_id\":1}','127.0.0.1','Unknown','2026-10-13 14:08:36'),(129,NULL,NULL,'portal_payment_refunded','portal_payment',10,NULL,'{\"amount\":1500,\"reason\":\"Duplicate test deposit reversal\",\"authorized_by\":1}','127.0.0.1','Unknown','2026-10-13 14:08:36'),(130,NULL,NULL,'portal_term_invoices_generated','school_portal_fee_agreement',2,NULL,'{\"school_id\":2,\"session_id\":2,\"term_id\":4,\"created\":0,\"skipped\":3}','127.0.0.1','Unknown','2026-10-13 14:08:36'),(131,1,4,'demo_quick_login','user',4,NULL,'{\"email\":\"admin@apexcollege.edu.ng\"}','::1','Unknown','2026-10-13 14:08:36'),(132,1,10,'demo_quick_login','user',10,NULL,'{\"email\":\"student@apexcollege.edu.ng\"}','::1','Unknown','2026-10-13 14:08:36'),(133,1,9,'demo_quick_login','user',9,NULL,'{\"email\":\"parent@apexcollege.edu.ng\"}','::1','Unknown','2026-10-13 14:08:36'),(134,1,4,'school_notice_created','school_notices',20,NULL,'{\"title\":\"Confidential Staff Meeting 1791900516\",\"category\":\"academic\",\"target_audience\":\"staff\"}','::1','Unknown','2026-10-13 14:08:36'),(135,1,4,'school_notice_created','school_notices',21,NULL,'{\"title\":\"Inter-House Athletic Practice 1791900516\",\"category\":\"sports\",\"target_audience\":\"students\"}','::1','Unknown','2026-10-13 14:08:36'),(136,1,4,'school_message_to_parent_sent','parent_school_messages',16,NULL,'{\"parent_id\":1,\"student_id\":1,\"subject\":\"Student Academic Commendation 1791900516\"}','::1','Unknown','2026-10-13 14:08:36'),(137,1,10,'student_fee_paid','fee_invoices',1,NULL,'{\"receipt_number\":\"REC-2026-1334\",\"amount\":5000,\"student_id\":1}','::1','Unknown','2026-10-13 14:08:36'),(138,1,10,'portal_payment_allocated','portal_payment',11,NULL,'{\"invoice_id\":1,\"amount\":3000,\"distributable\":2955,\"platform\":1329.75,\"school_incentive\":1182,\"sales_rep_comm\":443.25,\"reference\":\"PAY-ONLINE-STU-20261013-31815\"}','::1','Unknown','2026-10-13 14:08:37'),(139,1,NULL,'commission_approved','sales_commission',4,NULL,'{\"approver_user_id\":2,\"commission_amount\":\"50000.00\"}','127.0.0.1','Unknown','2026-10-13 14:08:37'),(140,1,NULL,'commission_payout_recorded','sales_commission',4,NULL,'{\"payout_reference\":\"BNK-REF-99281\"}','127.0.0.1','Unknown','2026-10-13 14:08:37'),(141,NULL,1,'demo_quick_login','user',1,NULL,'{\"email\":\"superadmin@edusphere.example\"}','::1','Unknown','2026-10-13 14:08:37'),(142,NULL,1,'subscription_plan_created','subscription_plans',5,NULL,'{\"name\":\"Elite Campus Plan\",\"price\":850000,\"code\":\"plan_test_717\"}','::1','Unknown','2026-10-13 14:08:37'),(143,NULL,1,'tenant_custom_domain_added','tenant_domains',5,NULL,'{\"school_id\":1,\"domain\":\"portal.apexcollege-474.org.ng\"}','::1','Unknown','2026-10-13 14:08:37'),(144,1,4,'demo_quick_login','user',4,NULL,'{\"email\":\"admin@apexcollege.edu.ng\"}','::1','Unknown','2026-10-13 14:08:37'),(145,1,4,'student_enrolled_direct','student',10,NULL,'{\"admission_number\":\"SCH/2026/007\",\"name\":\"Kareem Ogunlesi_291\",\"class_arm_id\":1}','::1','Unknown','2026-10-13 14:08:37'),(146,1,4,'academic_session_created','academic_sessions',4,NULL,'{\"name\":\"2027/2028_37\",\"school_id\":1}','::1','Unknown','2026-10-13 14:08:37'),(147,1,4,'class_arm_created','class_arms',7,NULL,'{\"name\":\"Titanium_30\",\"class_level_id\":1}','::1','Unknown','2026-10-13 14:08:37'),(148,1,4,'subject_created','subjects',13,NULL,'{\"name\":\"Robotics & AI\",\"code\":\"ROB_90\"}','::1','Unknown','2026-10-13 14:08:37'),(149,1,4,'staff_onboarded','staff',9,NULL,'{\"employee_number\":\"EMP/2026/006\",\"name\":\"Engr. Daniel Alabi\",\"role\":\"teacher\"}','::1','Unknown','2026-10-13 14:08:37'),(150,1,4,'fee_invoice_created','fee_invoices',8,NULL,'{\"invoice_number\":\"INV-2026-006\",\"student_id\":10,\"total_amount\":195000}','::1','Unknown','2026-10-13 14:08:38'),(151,NULL,1,'demo_quick_login','user',1,NULL,'{\"email\":\"superadmin@edusphere.example\"}','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36','2026-10-13 14:13:36'),(152,NULL,3,'demo_quick_login','user',3,NULL,'{\"email\":\"salesrep@edusphere.example\"}','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36','2026-10-13 14:13:58'),(153,NULL,1,'demo_quick_login','user',1,NULL,'{\"email\":\"superadmin@edusphere.example\"}','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36','2026-10-14 07:35:15'),(154,NULL,3,'demo_quick_login','user',3,NULL,'{\"email\":\"salesrep@edusphere.example\"}','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36','2026-10-14 07:35:50'),(155,1,10,'demo_quick_login','user',10,NULL,'{\"email\":\"student@apexcollege.edu.ng\"}','::1','Unknown','2026-10-14 07:52:29'),(156,1,9,'demo_quick_login','user',9,NULL,'{\"email\":\"parent@apexcollege.edu.ng\"}','::1','Unknown','2026-10-14 07:52:29'),(157,NULL,NULL,'sales_rep_self_registered','sales_representative',2,NULL,'{\"rep_code\":\"REP-LAG-5511\",\"email\":\"partner_test_1791964410@example.com\",\"initial_lead_id\":8,\"school_name\":\"Oakfield Model Academy 643\",\"ip\":\"192.168.1.105\"}','192.168.1.105','PHPUnit/EduSphereTestBrowser','2026-10-14 07:53:30'),(158,NULL,NULL,'sales_rep_self_registered','sales_representative',3,NULL,'{\"rep_code\":\"REP-LAG-3418\",\"email\":\"partner_http_1791964453@example.com\",\"initial_lead_id\":9,\"school_name\":\"St. Gregory Crest College 225\",\"ip\":\"::1\"}','::1','Unknown','2026-10-14 07:54:13'),(159,NULL,NULL,'sales_rep_self_registered','sales_representative',4,NULL,'{\"rep_code\":\"REP-LAG-8610\",\"email\":\"partner_http_1791964501@example.com\",\"initial_lead_id\":10,\"school_name\":\"St. Gregory Crest College 829\",\"ip\":\"::1\"}','::1','Unknown','2026-10-14 07:55:01'),(160,1,10,'demo_quick_login','user',10,NULL,'{\"email\":\"student@apexcollege.edu.ng\"}','::1','Unknown','2026-10-14 07:56:10'),(161,1,9,'demo_quick_login','user',9,NULL,'{\"email\":\"parent@apexcollege.edu.ng\"}','::1','Unknown','2026-10-14 07:56:10'),(162,1,10,'demo_quick_login','user',10,NULL,'{\"email\":\"student@apexcollege.edu.ng\"}','::1','Unknown','2026-10-14 07:56:39'),(163,1,9,'demo_quick_login','user',9,NULL,'{\"email\":\"parent@apexcollege.edu.ng\"}','::1','Unknown','2026-10-14 07:56:39'),(164,NULL,1,'demo_quick_login','user',1,NULL,'{\"email\":\"superadmin@edusphere.example\"}','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36','2026-10-14 08:10:31'),(165,1,4,'demo_quick_login','user',4,NULL,'{\"email\":\"admin@apexcollege.edu.ng\"}','::1','Unknown','2026-10-14 08:31:30'),(166,1,9,'demo_quick_login','user',9,NULL,'{\"email\":\"parent@apexcollege.edu.ng\"}','::1','Unknown','2026-10-14 08:31:31'),(167,1,10,'demo_quick_login','user',10,NULL,'{\"email\":\"student@apexcollege.edu.ng\"}','::1','Unknown','2026-10-14 08:31:44'),(168,1,9,'demo_quick_login','user',9,NULL,'{\"email\":\"parent@apexcollege.edu.ng\"}','::1','Unknown','2026-10-14 08:31:45'),(169,1,10,'demo_quick_login','user',10,NULL,'{\"email\":\"student@apexcollege.edu.ng\"}','::1','Unknown','2026-10-14 08:32:53'),(170,1,9,'demo_quick_login','user',9,NULL,'{\"email\":\"parent@apexcollege.edu.ng\"}','::1','Unknown','2026-10-14 08:32:53');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `class_arms`
--

DROP TABLE IF EXISTS `class_arms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `class_arms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `class_level_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `capacity` int(11) DEFAULT 35,
  `class_teacher_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_class_arm` (`school_id`,`class_level_id`,`name`),
  KEY `class_level_id` (`class_level_id`),
  CONSTRAINT `class_arms_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `class_arms_ibfk_2` FOREIGN KEY (`class_level_id`) REFERENCES `class_levels` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `class_arms`
--

LOCK TABLES `class_arms` WRITE;
/*!40000 ALTER TABLE `class_arms` DISABLE KEYS */;
INSERT INTO `class_arms` VALUES (1,1,1,'Gold',35,3,'2026-10-13 13:28:02','2026-10-13 13:28:02'),(2,1,1,'Diamond',35,NULL,'2026-10-13 13:28:02','2026-10-13 13:28:02'),(3,1,5,'Science',30,NULL,'2026-10-13 13:28:02','2026-10-13 13:28:02'),(4,2,9,'Emerald',28,6,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(5,2,9,'Ruby',28,NULL,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(6,1,1,'Titanium_46',40,NULL,'2026-10-13 14:06:50','2026-10-13 14:06:50'),(7,1,1,'Titanium_30',40,NULL,'2026-10-13 14:08:37','2026-10-13 14:08:37');
/*!40000 ALTER TABLE `class_arms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `class_levels`
--

DROP TABLE IF EXISTS `class_levels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `class_levels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `name` varchar(80) NOT NULL,
  `section` enum('nursery','primary','junior_secondary','senior_secondary') NOT NULL,
  `rank_order` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_class_level` (`school_id`,`name`),
  CONSTRAINT `class_levels_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `class_levels`
--

LOCK TABLES `class_levels` WRITE;
/*!40000 ALTER TABLE `class_levels` DISABLE KEYS */;
INSERT INTO `class_levels` VALUES (1,1,'JSS 1','junior_secondary',1,'2026-10-13 13:28:02','2026-10-13 13:28:02'),(2,1,'JSS 2','junior_secondary',2,'2026-10-13 13:28:02','2026-10-13 13:28:02'),(3,1,'JSS 3','junior_secondary',3,'2026-10-13 13:28:02','2026-10-13 13:28:02'),(4,1,'SSS 1','senior_secondary',4,'2026-10-13 13:28:02','2026-10-13 13:28:02'),(5,1,'SSS 2','senior_secondary',5,'2026-10-13 13:28:02','2026-10-13 13:28:02'),(6,1,'SSS 3','senior_secondary',6,'2026-10-13 13:28:02','2026-10-13 13:28:02'),(7,2,'Primary 1','primary',1,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(8,2,'Primary 2','primary',2,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(9,2,'Primary 3','primary',3,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(10,2,'Primary 4','primary',4,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(11,2,'Primary 5','primary',5,'2026-10-13 13:28:03','2026-10-13 13:28:03');
/*!40000 ALTER TABLE `class_levels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clinic_records`
--

DROP TABLE IF EXISTS `clinic_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clinic_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `complaint` text NOT NULL,
  `diagnosis` text DEFAULT NULL,
  `treatment` text NOT NULL,
  `administered_by` varchar(120) NOT NULL,
  `recorded_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `school_id` (`school_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `clinic_records_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `clinic_records_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clinic_records`
--

LOCK TABLES `clinic_records` WRITE;
/*!40000 ALTER TABLE `clinic_records` DISABLE KEYS */;
INSERT INTO `clinic_records` VALUES (1,1,1,'Headache and mild elevated temperature during second period.','Mild fatigue / tension headache. Temp 37.2C.','Administered oral Paracetamol syrup and 1-hour rest in clinic ward.','Nurse Patricia Adams, RN','2026-10-10 14:28:03','2026-10-13 13:28:03');
/*!40000 ALTER TABLE `clinic_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exam_scores`
--

DROP TABLE IF EXISTS `exam_scores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exam_scores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `term_id` int(11) NOT NULL,
  `class_arm_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `ca1_score` decimal(5,2) DEFAULT 0.00,
  `ca2_score` decimal(5,2) DEFAULT 0.00,
  `exam_score` decimal(5,2) DEFAULT 0.00,
  `total_score` decimal(5,2) DEFAULT 0.00,
  `grade` varchar(10) DEFAULT NULL,
  `remark` varchar(100) DEFAULT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `status` enum('draft','submitted','approved','published','locked') DEFAULT 'draft',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_score_entry` (`school_id`,`session_id`,`term_id`,`class_arm_id`,`subject_id`,`student_id`),
  KEY `student_id` (`student_id`),
  KEY `subject_id` (`subject_id`),
  CONSTRAINT `exam_scores_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exam_scores_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exam_scores_ibfk_3` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exam_scores`
--

LOCK TABLES `exam_scores` WRITE;
/*!40000 ALTER TABLE `exam_scores` DISABLE KEYS */;
INSERT INTO `exam_scores` VALUES (1,1,1,1,1,1,1,18.00,19.00,54.00,91.00,'A1','Distinction',3,'published',5,'2026-10-13 13:28:02','2026-10-13 13:28:02','2026-10-13 13:28:02'),(2,1,1,1,1,2,1,17.00,18.00,51.00,86.00,'A1','Distinction',3,'published',5,'2026-10-13 13:28:02','2026-10-13 13:28:02','2026-10-13 13:28:02'),(3,1,1,1,1,3,1,19.00,18.00,52.00,89.00,'A1','Distinction',3,'published',5,'2026-10-13 13:28:02','2026-10-13 13:28:02','2026-10-13 13:28:02'),(4,1,1,1,1,4,1,16.00,17.00,49.00,82.00,'A1','Distinction',3,'published',5,'2026-10-13 13:28:02','2026-10-13 13:28:02','2026-10-13 13:28:02'),(5,1,1,1,1,5,1,18.00,17.00,50.00,85.00,'A1','Distinction',3,'published',5,'2026-10-13 13:28:02','2026-10-13 13:28:02','2026-10-13 13:28:02'),(6,1,1,1,1,1,2,17.00,16.00,48.00,81.00,'A1','Distinction',3,'published',5,'2026-10-13 13:28:02','2026-10-13 13:28:02','2026-10-13 13:28:02'),(7,1,1,1,1,2,2,19.00,19.00,56.00,94.00,'A1','Distinction',3,'published',5,'2026-10-13 13:28:02','2026-10-13 13:28:02','2026-10-13 13:28:02'),(8,1,1,1,1,3,2,17.00,18.00,50.00,85.00,'A1','Distinction',3,'published',5,'2026-10-13 13:28:02','2026-10-13 13:28:02','2026-10-13 13:28:02'),(9,1,1,1,1,4,2,18.00,18.00,53.00,89.00,'A1','Distinction',3,'published',5,'2026-10-13 13:28:02','2026-10-13 13:28:02','2026-10-13 13:28:02'),(10,1,1,1,1,5,2,17.00,17.00,49.00,83.00,'A1','Distinction',3,'published',5,'2026-10-13 13:28:02','2026-10-13 13:28:02','2026-10-13 13:28:02'),(11,1,1,1,1,1,3,14.00,15.00,42.00,71.00,'B2','Very Good',3,'published',5,'2026-10-13 13:28:02','2026-10-13 13:28:02','2026-10-13 13:28:02'),(12,1,1,1,1,2,3,15.00,14.00,45.00,74.00,'B2','Very Good',3,'published',5,'2026-10-13 13:28:02','2026-10-13 13:28:02','2026-10-13 13:28:02'),(13,1,1,1,1,3,3,13.00,14.00,40.00,67.00,'B3','Good',3,'published',5,'2026-10-13 13:28:02','2026-10-13 13:28:02','2026-10-13 13:28:02'),(14,1,1,1,1,4,3,15.00,15.00,46.00,76.00,'A1','Distinction',3,'published',5,'2026-10-13 13:28:02','2026-10-13 13:28:02','2026-10-13 13:28:02'),(15,1,1,1,1,5,3,14.00,15.00,43.00,72.00,'B2','Very Good',3,'published',5,'2026-10-13 13:28:02','2026-10-13 13:28:02','2026-10-13 13:28:02'),(16,1,1,1,1,1,4,13.00,12.00,38.00,63.00,'C4','Credit',3,'published',5,'2026-10-13 13:28:02','2026-10-13 13:28:02','2026-10-13 13:28:02'),(17,1,1,1,1,2,4,14.00,15.00,44.00,73.00,'B2','Very Good',3,'published',5,'2026-10-13 13:28:02','2026-10-13 13:28:02','2026-10-13 13:28:02'),(18,1,1,1,1,3,4,12.00,13.00,37.00,62.00,'C4','Credit',3,'published',5,'2026-10-13 13:28:02','2026-10-13 13:28:02','2026-10-13 13:28:02'),(19,1,1,1,1,4,4,14.00,14.00,41.00,69.00,'B3','Good',3,'published',5,'2026-10-13 13:28:02','2026-10-13 13:28:02','2026-10-13 13:28:02'),(20,1,1,1,1,5,4,13.00,13.00,39.00,65.00,'B3','Good',3,'published',5,'2026-10-13 13:28:02','2026-10-13 13:28:02','2026-10-13 13:28:02'),(21,1,1,1,1,1,5,11.00,10.00,32.00,53.00,'C6','Credit',3,'published',5,'2026-10-13 13:28:02','2026-10-13 13:28:02','2026-10-13 13:28:02'),(22,1,1,1,1,2,5,12.00,13.00,35.00,60.00,'C4','Credit',3,'published',5,'2026-10-13 13:28:02','2026-10-13 13:28:02','2026-10-13 13:28:02'),(23,1,1,1,1,3,5,10.00,11.00,30.00,51.00,'C6','Credit',3,'published',5,'2026-10-13 13:28:02','2026-10-13 13:28:02','2026-10-13 13:28:02'),(24,1,1,1,1,4,5,12.00,12.00,36.00,60.00,'C4','Credit',3,'published',5,'2026-10-13 13:28:02','2026-10-13 13:28:02','2026-10-13 13:28:02'),(25,1,1,1,1,5,5,11.00,12.00,34.00,57.00,'C5','Credit',3,'published',5,'2026-10-13 13:28:02','2026-10-13 13:28:02','2026-10-13 13:28:02'),(26,2,2,4,4,9,6,18.00,19.00,52.00,89.00,'A1','Distinction',6,'published',12,'2026-10-13 13:28:03','2026-10-13 13:28:03','2026-10-13 13:28:03'),(27,2,2,4,4,9,7,16.00,17.00,48.00,81.00,'A1','Distinction',6,'published',12,'2026-10-13 13:28:03','2026-10-13 13:28:03','2026-10-13 13:28:03'),(28,2,2,4,4,9,8,14.00,15.00,42.00,71.00,'B2','Very Good',6,'published',12,'2026-10-13 13:28:03','2026-10-13 13:28:03','2026-10-13 13:28:03'),(29,2,2,4,4,8,6,18.00,19.00,52.00,89.00,'A1','Distinction',6,'published',12,'2026-10-13 13:28:03','2026-10-13 13:28:03','2026-10-13 13:28:03'),(30,2,2,4,4,8,7,16.00,17.00,48.00,81.00,'A1','Distinction',6,'published',12,'2026-10-13 13:28:03','2026-10-13 13:28:03','2026-10-13 13:28:03'),(31,2,2,4,4,8,8,14.00,15.00,42.00,71.00,'B2','Very Good',6,'published',12,'2026-10-13 13:28:03','2026-10-13 13:28:03','2026-10-13 13:28:03'),(32,2,2,4,4,11,6,18.00,19.00,52.00,89.00,'A1','Distinction',6,'published',12,'2026-10-13 13:28:03','2026-10-13 13:28:03','2026-10-13 13:28:03'),(33,2,2,4,4,11,7,16.00,17.00,48.00,81.00,'A1','Distinction',6,'published',12,'2026-10-13 13:28:03','2026-10-13 13:28:03','2026-10-13 13:28:03'),(34,2,2,4,4,11,8,14.00,15.00,42.00,71.00,'B2','Very Good',6,'published',12,'2026-10-13 13:28:03','2026-10-13 13:28:03','2026-10-13 13:28:03'),(35,2,2,4,4,7,6,18.00,19.00,52.00,89.00,'A1','Distinction',6,'published',12,'2026-10-13 13:28:03','2026-10-13 13:28:03','2026-10-13 13:28:03'),(36,2,2,4,4,7,7,16.00,17.00,48.00,81.00,'A1','Distinction',6,'published',12,'2026-10-13 13:28:03','2026-10-13 13:28:03','2026-10-13 13:28:03'),(37,2,2,4,4,7,8,14.00,15.00,42.00,71.00,'B2','Very Good',6,'published',12,'2026-10-13 13:28:03','2026-10-13 13:28:03','2026-10-13 13:28:03'),(38,2,2,4,4,10,6,18.00,19.00,52.00,89.00,'A1','Distinction',6,'published',12,'2026-10-13 13:28:03','2026-10-13 13:28:03','2026-10-13 13:28:03'),(39,2,2,4,4,10,7,16.00,17.00,48.00,81.00,'A1','Distinction',6,'published',12,'2026-10-13 13:28:03','2026-10-13 13:28:03','2026-10-13 13:28:03'),(40,2,2,4,4,10,8,14.00,15.00,42.00,71.00,'B2','Very Good',6,'published',12,'2026-10-13 13:28:03','2026-10-13 13:28:03','2026-10-13 13:28:03');
/*!40000 ALTER TABLE `exam_scores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fee_categories`
--

DROP TABLE IF EXISTS `fee_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fee_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_fee_cat_name` (`school_id`,`name`),
  CONSTRAINT `fee_categories_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fee_categories`
--

LOCK TABLES `fee_categories` WRITE;
/*!40000 ALTER TABLE `fee_categories` DISABLE KEYS */;
INSERT INTO `fee_categories` VALUES (1,1,'Tuition & Academic Instructional Fee','Core tuition fees covering coursework and faculty.','2026-10-13 13:28:03','2026-10-13 13:28:03'),(2,1,'ICT & STEM Robotics Lab Levy','Computer science, robotics and STEM laboratory maintenance.','2026-10-13 13:28:03','2026-10-13 13:28:03'),(3,1,'School Development & Facility Levy','Annual facility maintenance, power generation and infrastructure.','2026-10-13 13:28:03','2026-10-13 13:28:03'),(4,2,'Primary Tuition & Academic Instruction','Tuition for basic educational foundation.','2026-10-13 13:28:03','2026-10-13 13:28:03'),(5,2,'Learning Resources & Workbooks','Educational text and activity workbooks.','2026-10-13 13:28:03','2026-10-13 13:28:03'),(6,2,'Campus Development Levy','Infrastructure and campus maintenance.','2026-10-13 13:28:03','2026-10-13 13:28:03');
/*!40000 ALTER TABLE `fee_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fee_invoices`
--

DROP TABLE IF EXISTS `fee_invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fee_invoices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `session_id` int(11) NOT NULL,
  `term_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `class_arm_id` int(11) NOT NULL,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(12,2) DEFAULT 0.00,
  `paid_amount` decimal(12,2) DEFAULT 0.00,
  `balance_due` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` enum('unpaid','partially_paid','paid','cancelled') DEFAULT 'unpaid',
  `due_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_fee_invoice_num` (`school_id`,`invoice_number`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `fee_invoices_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fee_invoices_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fee_invoices`
--

LOCK TABLES `fee_invoices` WRITE;
/*!40000 ALTER TABLE `fee_invoices` DISABLE KEYS */;
INSERT INTO `fee_invoices` VALUES (1,1,'INV-2025-001',1,1,1,1,160000.00,0.00,160000.00,0.00,'paid','2025-09-30','2026-10-13 13:28:03','2026-10-13 14:08:37'),(2,1,'INV-2025-002',1,1,2,1,160000.00,0.00,100000.00,60000.00,'partially_paid','2025-09-30','2026-10-13 13:28:03','2026-10-13 13:28:03'),(3,1,'INV-2025-003',1,1,3,1,160000.00,0.00,0.00,160000.00,'unpaid','2025-09-30','2026-10-13 13:28:03','2026-10-13 13:28:03'),(4,2,'INV-HRZ-2025-001',2,4,6,4,130000.00,0.00,130000.00,0.00,'paid','2025-09-30','2026-10-13 13:28:03','2026-10-13 13:28:03'),(5,2,'INV-HRZ-2025-002',2,4,7,4,130000.00,0.00,65000.00,65000.00,'partially_paid','2025-09-30','2026-10-13 13:28:03','2026-10-13 13:28:03'),(6,1,'INV-TEST-STU-1791899319',1,1,1,1,25000.00,0.00,5000.00,20000.00,'partially_paid','2026-11-12','2026-10-13 13:48:39','2026-10-13 13:48:39'),(7,1,'INV-2026-005',1,1,9,1,195000.00,0.00,0.00,195000.00,'unpaid','2026-11-30','2026-10-13 14:06:50','2026-10-13 14:06:50'),(8,1,'INV-2026-006',1,1,10,1,195000.00,0.00,0.00,195000.00,'unpaid','2026-11-30','2026-10-13 14:08:38','2026-10-13 14:08:38');
/*!40000 ALTER TABLE `fee_invoices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fee_structures`
--

DROP TABLE IF EXISTS `fee_structures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fee_structures` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `term_id` int(11) NOT NULL,
  `class_level_id` int(11) NOT NULL,
  `fee_category_id` int(11) NOT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `is_mandatory` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_fee_struct` (`school_id`,`session_id`,`term_id`,`class_level_id`,`fee_category_id`),
  KEY `fee_category_id` (`fee_category_id`),
  KEY `class_level_id` (`class_level_id`),
  CONSTRAINT `fee_structures_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fee_structures_ibfk_2` FOREIGN KEY (`fee_category_id`) REFERENCES `fee_categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fee_structures_ibfk_3` FOREIGN KEY (`class_level_id`) REFERENCES `class_levels` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fee_structures`
--

LOCK TABLES `fee_structures` WRITE;
/*!40000 ALTER TABLE `fee_structures` DISABLE KEYS */;
INSERT INTO `fee_structures` VALUES (1,1,1,1,1,2,120000.00,1,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(2,1,1,1,1,3,25000.00,1,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(3,1,1,1,1,1,15000.00,1,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(4,2,2,4,9,6,95000.00,1,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(5,2,2,4,9,5,20000.00,1,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(6,2,2,4,9,4,15000.00,1,'2026-10-13 13:28:03','2026-10-13 13:28:03');
/*!40000 ALTER TABLE `fee_structures` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `financial_allocations`
--

DROP TABLE IF EXISTS `financial_allocations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `financial_allocations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payment_id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `revenue_policy_id` int(11) NOT NULL,
  `gross_amount` decimal(12,2) NOT NULL,
  `gateway_fee` decimal(12,2) NOT NULL DEFAULT 0.00,
  `distributable_amount` decimal(12,2) NOT NULL,
  `platform_amount` decimal(12,2) NOT NULL,
  `school_incentive_amount` decimal(12,2) NOT NULL,
  `sales_rep_id` int(11) DEFAULT NULL,
  `sales_rep_commission_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `commission_tier` enum('initial','recurring','none') NOT NULL DEFAULT 'initial',
  `status` enum('allocated','adjusted','reversed') NOT NULL DEFAULT 'allocated',
  `reversal_reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_id` (`payment_id`),
  KEY `school_id` (`school_id`),
  KEY `invoice_id` (`invoice_id`),
  KEY `revenue_policy_id` (`revenue_policy_id`),
  KEY `sales_rep_id` (`sales_rep_id`),
  CONSTRAINT `financial_allocations_ibfk_1` FOREIGN KEY (`payment_id`) REFERENCES `portal_payments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `financial_allocations_ibfk_2` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `financial_allocations_ibfk_3` FOREIGN KEY (`invoice_id`) REFERENCES `portal_invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `financial_allocations_ibfk_4` FOREIGN KEY (`revenue_policy_id`) REFERENCES `revenue_sharing_policies` (`id`),
  CONSTRAINT `financial_allocations_ibfk_5` FOREIGN KEY (`sales_rep_id`) REFERENCES `sales_representatives` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `financial_allocations`
--

LOCK TABLES `financial_allocations` WRITE;
/*!40000 ALTER TABLE `financial_allocations` DISABLE KEYS */;
INSERT INTO `financial_allocations` VALUES (1,1,1,1,1,3000.00,45.00,2955.00,1329.75,1182.00,1,443.25,'initial','allocated',NULL,'2026-10-13 13:28:03'),(2,2,1,2,1,3000.00,45.00,2955.00,1329.75,1182.00,1,443.25,'initial','allocated',NULL,'2026-10-13 13:28:03'),(3,3,1,3,1,1500.00,22.50,1477.50,664.87,591.00,1,221.63,'initial','allocated',NULL,'2026-10-13 13:28:03'),(4,4,1,4,1,1500.00,22.50,1477.50,664.87,591.00,1,221.63,'initial','reversed','Duplicate test deposit reversal','2026-10-13 13:30:24'),(5,5,1,1,1,3000.00,45.00,2955.00,1329.75,1182.00,1,443.25,'initial','allocated',NULL,'2026-10-13 13:49:42'),(6,6,1,1,1,3000.00,45.00,2955.00,1329.75,1182.00,1,443.25,'initial','allocated',NULL,'2026-10-13 13:50:03'),(7,7,1,1,1,3000.00,45.00,2955.00,1329.75,1182.00,1,443.25,'initial','allocated',NULL,'2026-10-13 13:50:32'),(8,8,1,1,1,3000.00,45.00,2955.00,1329.75,1182.00,1,443.25,'initial','allocated',NULL,'2026-10-13 13:51:03'),(9,9,1,1,1,3000.00,45.00,2955.00,1329.75,1182.00,1,443.25,'initial','allocated',NULL,'2026-10-13 13:54:00'),(10,10,1,4,1,1500.00,22.50,1477.50,664.87,591.00,1,221.63,'initial','reversed','Duplicate test deposit reversal','2026-10-13 14:08:36'),(11,11,1,1,1,3000.00,45.00,2955.00,1329.75,1182.00,1,443.25,'initial','allocated',NULL,'2026-10-13 14:08:37');
/*!40000 ALTER TABLE `financial_allocations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `grading_scales`
--

DROP TABLE IF EXISTS `grading_scales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `grading_scales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `grade_name` varchar(10) NOT NULL,
  `min_score` decimal(5,2) NOT NULL,
  `max_score` decimal(5,2) NOT NULL,
  `grade_point` decimal(4,2) DEFAULT 0.00,
  `remark` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_grade_scale` (`school_id`,`grade_name`),
  CONSTRAINT `grading_scales_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `grading_scales`
--

LOCK TABLES `grading_scales` WRITE;
/*!40000 ALTER TABLE `grading_scales` DISABLE KEYS */;
INSERT INTO `grading_scales` VALUES (1,1,'A1',75.00,100.00,4.00,'Distinction','2026-10-13 13:28:02','2026-10-13 13:28:02'),(2,1,'B2',70.00,74.99,3.50,'Very Good','2026-10-13 13:28:02','2026-10-13 13:28:02'),(3,1,'B3',65.00,69.99,3.00,'Good','2026-10-13 13:28:02','2026-10-13 13:28:02'),(4,1,'C4',60.00,64.99,2.50,'Credit','2026-10-13 13:28:02','2026-10-13 13:28:02'),(5,1,'C5',55.00,59.99,2.00,'Credit','2026-10-13 13:28:02','2026-10-13 13:28:02'),(6,1,'C6',50.00,54.99,1.50,'Credit','2026-10-13 13:28:02','2026-10-13 13:28:02'),(7,1,'D7',45.00,49.99,1.00,'Pass','2026-10-13 13:28:02','2026-10-13 13:28:02'),(8,1,'E8',40.00,44.99,0.50,'Pass','2026-10-13 13:28:02','2026-10-13 13:28:02'),(9,1,'F9',0.00,39.99,0.00,'Fail','2026-10-13 13:28:02','2026-10-13 13:28:02'),(10,2,'A1',75.00,100.00,4.00,'Distinction','2026-10-13 13:28:03','2026-10-13 13:28:03'),(11,2,'B2',70.00,74.99,3.50,'Very Good','2026-10-13 13:28:03','2026-10-13 13:28:03'),(12,2,'B3',65.00,69.99,3.00,'Good','2026-10-13 13:28:03','2026-10-13 13:28:03'),(13,2,'C4',60.00,64.99,2.50,'Credit','2026-10-13 13:28:03','2026-10-13 13:28:03'),(14,2,'C5',55.00,59.99,2.00,'Credit','2026-10-13 13:28:03','2026-10-13 13:28:03'),(15,2,'C6',50.00,54.99,1.50,'Credit','2026-10-13 13:28:03','2026-10-13 13:28:03'),(16,2,'D7',45.00,49.99,1.00,'Pass','2026-10-13 13:28:03','2026-10-13 13:28:03'),(17,2,'E8',40.00,44.99,0.50,'Pass','2026-10-13 13:28:03','2026-10-13 13:28:03'),(18,2,'F9',0.00,39.99,0.00,'Fail','2026-10-13 13:28:03','2026-10-13 13:28:03');
/*!40000 ALTER TABLE `grading_scales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_items`
--

DROP TABLE IF EXISTS `inventory_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventory_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `item_code` varchar(50) NOT NULL,
  `name` varchar(150) NOT NULL,
  `category` varchar(100) DEFAULT 'Stationery',
  `quantity` int(11) NOT NULL DEFAULT 0,
  `unit` varchar(30) DEFAULT 'pcs',
  `unit_price` decimal(10,2) DEFAULT 0.00,
  `minimum_threshold` int(11) DEFAULT 5,
  `status` enum('in_stock','low_stock','out_of_stock') DEFAULT 'in_stock',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_inventory_item` (`school_id`,`item_code`),
  CONSTRAINT `inventory_items_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_items`
--

LOCK TABLES `inventory_items` WRITE;
/*!40000 ALTER TABLE `inventory_items` DISABLE KEYS */;
INSERT INTO `inventory_items` VALUES (1,1,'STN-PPR-A4','Standard A4 Printing Ream Paper (80gsm)','Stationery',65,'reams',4500.00,15,'in_stock','2026-10-13 13:28:03','2026-10-13 13:28:03'),(2,1,'LAB-MIC-01','Binocular Compound Microscopes','Science Lab',12,'units',85000.00,4,'in_stock','2026-10-13 13:28:03','2026-10-13 13:28:03'),(3,1,'SPT-FB-01','FIFA Approved Leather Footballs','Sports',8,'balls',12000.00,3,'in_stock','2026-10-13 13:28:03','2026-10-13 13:28:03'),(4,2,'HRZ-ART-01','Children Art Paint Kits (Non-Toxic)','Art & Craft',40,'sets',3500.00,10,'in_stock','2026-10-13 13:28:03','2026-10-13 13:28:03'),(5,2,'HRZ-SPT-01','Mini Football Training Cones','Sports',25,'units',1200.00,5,'in_stock','2026-10-13 13:28:03','2026-10-13 13:28:03');
/*!40000 ALTER TABLE `inventory_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `library_books`
--

DROP TABLE IF EXISTS `library_books`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `library_books` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `isbn` varchar(50) DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `author` varchar(150) NOT NULL,
  `category` varchar(80) DEFAULT 'General',
  `total_copies` int(11) DEFAULT 1,
  `available_copies` int(11) DEFAULT 1,
  `shelf_location` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `school_id` (`school_id`),
  CONSTRAINT `library_books_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `library_books`
--

LOCK TABLES `library_books` WRITE;
/*!40000 ALTER TABLE `library_books` DISABLE KEYS */;
INSERT INTO `library_books` VALUES (1,1,'978-0199142835','New General Mathematics for JSS 1','M.F. Macrae et al.','Mathematics',40,38,'Shelf M-01','2026-10-13 13:28:03','2026-10-13 13:28:03'),(2,1,'978-9781754029','Essential Agricultural Science for Schools','O.A. Iwena','Agriculture',30,29,'Shelf A-03','2026-10-13 13:28:03','2026-10-13 13:28:03'),(3,1,'978-0582522718','Junior English Project for Secondary Schools','N. Grant','English',35,35,'Shelf E-02','2026-10-13 13:28:03','2026-10-13 13:28:03'),(4,2,'978-0199142839','Primary Mathematics for Nigerian Schools Bk 3','Universal Basic Education Board','Mathematics',30,29,'Shelf P-01','2026-10-13 13:28:03','2026-10-13 13:28:03'),(5,2,'978-0582522712','Active Primary English Bk 3','Longman Nigeria','English',30,30,'Shelf P-02','2026-10-13 13:28:03','2026-10-13 13:28:03');
/*!40000 ALTER TABLE `library_books` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `library_loans`
--

DROP TABLE IF EXISTS `library_loans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `library_loans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `borrower_type` enum('student','staff') DEFAULT 'student',
  `borrower_id` int(11) NOT NULL,
  `issue_date` date NOT NULL,
  `due_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  `status` enum('borrowed','returned','overdue') DEFAULT 'borrowed',
  `fine_amount` decimal(8,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `school_id` (`school_id`),
  KEY `book_id` (`book_id`),
  CONSTRAINT `library_loans_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `library_loans_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `library_books` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `library_loans`
--

LOCK TABLES `library_loans` WRITE;
/*!40000 ALTER TABLE `library_loans` DISABLE KEYS */;
/*!40000 ALTER TABLE `library_loans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `parent_school_messages`
--

DROP TABLE IF EXISTS `parent_school_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `parent_school_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `sender_type` enum('school','parent') NOT NULL,
  `sender_id` int(11) NOT NULL,
  `recipient_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` datetime DEFAULT NULL,
  `parent_message_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `student_id` (`student_id`),
  KEY `sender_id` (`sender_id`),
  KEY `recipient_id` (`recipient_id`),
  KEY `idx_msg_thread` (`parent_message_id`,`created_at`),
  KEY `idx_msg_school_parent` (`school_id`,`parent_id`,`is_read`),
  CONSTRAINT `parent_school_messages_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `parent_school_messages_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `parents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `parent_school_messages_ibfk_3` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE SET NULL,
  CONSTRAINT `parent_school_messages_ibfk_4` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `parent_school_messages_ibfk_5` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `parent_school_messages_ibfk_6` FOREIGN KEY (`parent_message_id`) REFERENCES `parent_school_messages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `parent_school_messages`
--

LOCK TABLES `parent_school_messages` WRITE;
/*!40000 ALTER TABLE `parent_school_messages` DISABLE KEYS */;
INSERT INTO `parent_school_messages` VALUES (1,1,1,1,'school',7,9,'Commendation in Mathematics & Class Engagement','Dear Mr. & Mrs. Adeyemi,\n\nI am delighted to inform you that Adeyemi performed exceptionally well during our recent continuous assessment in Advanced Algebra, securing 18.5/20. His classroom participation has been exemplary.\n\nPlease continue encouraging his study discipline at home.',1,'2026-10-13 14:28:03',NULL,'2026-10-11 13:28:03','2026-10-13 13:28:03'),(2,1,1,1,'parent',9,7,'Re: Commendation in Mathematics & Class Engagement','Thank you very much Mr. Teacher for the uplifting feedback! We will continue to supervise his evening revision schedules. Warm regards.',1,'2026-10-13 14:28:03',1,'2026-10-12 13:28:03','2026-10-13 13:28:03'),(3,1,1,1,'parent',9,5,'Late Afternoon Music Club Pickup Notice','Good day Administration,\n\nPlease be informed that Adeyemi will be attending the Wednesday afternoon orchestra rehearsal. His uncle, Mr. Tunde Adeyemi, will handle his pickup at 4:30 PM. Thank you.',1,'2026-10-13 14:29:12',NULL,'2026-10-13 09:28:03','2026-10-13 13:29:12'),(4,1,1,1,'school',4,9,'Student Academic Commendation 1791899319','Your ward demonstrated exceptional problem solving in Advanced Physics today.',1,'2026-10-13 14:48:39',NULL,'2026-10-13 13:48:39','2026-10-13 13:48:39'),(5,1,1,1,'parent',9,4,'Re: Student Academic Commendation 1791899319','Thank you very much, we are deeply grateful for the continuous guidance!',1,'2026-10-13 14:49:42',4,'2026-10-13 13:48:39','2026-10-13 13:49:42'),(6,1,1,1,'school',4,9,'Student Academic Commendation 1791899382','Your ward demonstrated exceptional problem solving in Advanced Physics today.',1,'2026-10-13 14:49:42',NULL,'2026-10-13 13:49:42','2026-10-13 13:49:42'),(7,1,1,1,'parent',9,4,'Re: Student Academic Commendation 1791899382','Thank you very much, we are deeply grateful for the continuous guidance!',1,'2026-10-13 14:50:03',6,'2026-10-13 13:49:42','2026-10-13 13:50:03'),(8,1,1,1,'school',4,9,'Student Academic Commendation 1791899403','Your ward demonstrated exceptional problem solving in Advanced Physics today.',1,'2026-10-13 14:50:03',NULL,'2026-10-13 13:50:03','2026-10-13 13:50:03'),(9,1,1,1,'parent',9,4,'Re: Student Academic Commendation 1791899403','Thank you very much, we are deeply grateful for the continuous guidance!',1,'2026-10-13 14:50:32',8,'2026-10-13 13:50:03','2026-10-13 13:50:32'),(10,1,1,1,'school',4,9,'Student Academic Commendation 1791899432','Your ward demonstrated exceptional problem solving in Advanced Physics today.',1,'2026-10-13 14:50:32',NULL,'2026-10-13 13:50:32','2026-10-13 13:50:32'),(11,1,1,1,'parent',9,4,'Re: Student Academic Commendation 1791899432','Thank you very much, we are deeply grateful for the continuous guidance!',1,'2026-10-13 14:51:03',10,'2026-10-13 13:50:32','2026-10-13 13:51:03'),(12,1,1,1,'school',4,9,'Student Academic Commendation 1791899463','Your ward demonstrated exceptional problem solving in Advanced Physics today.',1,'2026-10-13 14:51:03',NULL,'2026-10-13 13:51:03','2026-10-13 13:51:03'),(13,1,1,1,'parent',9,4,'Re: Student Academic Commendation 1791899463','Thank you very much, we are deeply grateful for the continuous guidance!',1,'2026-10-13 14:51:39',12,'2026-10-13 13:51:03','2026-10-13 13:51:39'),(14,1,1,1,'school',4,9,'Student Academic Commendation 1791899640','Your ward demonstrated exceptional problem solving in Advanced Physics today.',1,'2026-10-13 14:54:00',NULL,'2026-10-13 13:54:00','2026-10-13 13:54:00'),(15,1,1,1,'parent',9,4,'Re: Student Academic Commendation 1791899640','Thank you very much, we are deeply grateful for the continuous guidance!',1,'2026-10-13 15:08:34',14,'2026-10-13 13:54:00','2026-10-13 14:08:34'),(16,1,1,1,'school',4,9,'Student Academic Commendation 1791900516','Your ward demonstrated exceptional problem solving in Advanced Physics today.',1,'2026-10-13 15:08:36',NULL,'2026-10-13 14:08:36','2026-10-13 14:08:36'),(17,1,1,1,'parent',9,4,'Re: Student Academic Commendation 1791900516','Thank you very much, we are deeply grateful for the continuous guidance!',0,NULL,16,'2026-10-13 14:08:36','2026-10-13 14:08:36');
/*!40000 ALTER TABLE `parent_school_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `parents`
--

DROP TABLE IF EXISTS `parents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `parents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(30) DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `emergency_phone` varchar(50) DEFAULT NULL,
  `relationship_type` enum('father','mother','guardian') DEFAULT 'father',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  KEY `school_id` (`school_id`),
  CONSTRAINT `parents_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `parents_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `parents`
--

LOCK TABLES `parents` WRITE;
/*!40000 ALTER TABLE `parents` DISABLE KEYS */;
INSERT INTO `parents` VALUES (1,1,9,'Chief','Senior Logistics Director','15 Admiralty Way, Lekki Phase 1, Lagos','+2348039900112','father','2026-10-13 13:28:02','2026-10-13 13:28:02'),(2,2,15,'Alhaji','Director of Infrastructure','18 Maitama Avenue, Abuja','+2348029988780','father','2026-10-13 13:28:03','2026-10-13 13:28:03'),(3,1,17,'Mr/Mrs','Guardian',NULL,NULL,'father','2026-10-13 14:06:50','2026-10-13 14:06:50'),(4,1,19,'Mr/Mrs','Guardian',NULL,NULL,'father','2026-10-13 14:08:37','2026-10-13 14:08:37');
/*!40000 ALTER TABLE `parents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_receipts`
--

DROP TABLE IF EXISTS `payment_receipts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment_receipts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `receipt_number` varchar(50) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `amount_paid` decimal(12,2) NOT NULL,
  `payment_method` enum('paystack','bank_transfer','cash','cheque','pos') DEFAULT 'cash',
  `payment_reference` varchar(100) DEFAULT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `payment_date` date NOT NULL,
  `notes` text DEFAULT NULL,
  `verified_by` int(11) DEFAULT NULL,
  `status` enum('verified','pending','reversed') DEFAULT 'verified',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_receipt_num` (`school_id`,`receipt_number`),
  KEY `invoice_id` (`invoice_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `payment_receipts_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payment_receipts_ibfk_2` FOREIGN KEY (`invoice_id`) REFERENCES `fee_invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payment_receipts_ibfk_3` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_receipts`
--

LOCK TABLES `payment_receipts` WRITE;
/*!40000 ALTER TABLE `payment_receipts` DISABLE KEYS */;
INSERT INTO `payment_receipts` VALUES (1,1,'REC-2025-001',1,1,160000.00,'paystack','PSTK_TXN_9918231','TRX_8871625','2025-09-22',NULL,8,'verified','2026-10-13 13:28:03','2026-10-13 13:28:03'),(2,1,'REC-2025-002',2,2,100000.00,'bank_transfer','GTB_TRANSFER_5541','TRX_8871626','2025-09-25',NULL,8,'verified','2026-10-13 13:28:03','2026-10-13 13:28:03'),(3,2,'REC-HRZ-2025-001',4,6,130000.00,'bank_transfer','ZENITH_TRF_1192','TRX_HRZ_001','2025-09-20',NULL,14,'verified','2026-10-13 13:28:03','2026-10-13 13:28:03'),(4,1,'REC-2026-4427',6,1,5000.00,'paystack','PAY-STU-20261013-53226','TXN_872292','2026-10-13',NULL,10,'verified','2026-10-13 13:48:39','2026-10-13 13:48:39'),(5,1,'REC-2026-9810',1,1,5000.00,'paystack','PAY-STU-20261013-92979','TXN_642137','2026-10-13',NULL,10,'verified','2026-10-13 13:49:42','2026-10-13 13:49:42'),(6,1,'REC-2026-1370',1,1,5000.00,'paystack','PAY-STU-20261013-56058','TXN_567164','2026-10-13',NULL,10,'verified','2026-10-13 13:50:03','2026-10-13 13:50:03'),(7,1,'REC-2026-8464',1,1,5000.00,'paystack','PAY-STU-20261013-10295','TXN_616038','2026-10-13',NULL,10,'verified','2026-10-13 13:50:32','2026-10-13 13:50:32'),(8,1,'REC-2026-2015',1,1,5000.00,'paystack','PAY-STU-20261013-22747','TXN_315594','2026-10-13',NULL,10,'verified','2026-10-13 13:51:03','2026-10-13 13:51:03'),(9,1,'REC-2026-1962',1,1,5000.00,'paystack','PAY-STU-20261013-70917','TXN_418857','2026-10-13',NULL,10,'verified','2026-10-13 13:54:00','2026-10-13 13:54:00'),(10,1,'REC-2026-1334',1,1,5000.00,'paystack','PAY-STU-20261013-42196','TXN_721231','2026-10-13',NULL,10,'verified','2026-10-13 14:08:36','2026-10-13 14:08:36');
/*!40000 ALTER TABLE `payment_receipts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `platform_settings`
--

DROP TABLE IF EXISTS `platform_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `platform_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` longtext DEFAULT NULL,
  `setting_group` varchar(50) DEFAULT 'general',
  `is_public` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `platform_settings`
--

LOCK TABLES `platform_settings` WRITE;
/*!40000 ALTER TABLE `platform_settings` DISABLE KEYS */;
INSERT INTO `platform_settings` VALUES (1,'platform_name','EduSphere SaaS','general',1,'2026-10-13 13:28:02','2026-10-13 13:28:02'),(2,'platform_tagline','Next-Generation Multi-Tenant School Administration Engine','general',1,'2026-10-13 13:28:02','2026-10-13 13:28:02'),(3,'support_email','support@edusphere.example','general',1,'2026-10-13 13:28:02','2026-10-13 13:28:02'),(4,'currency_symbol','₦','finance',1,'2026-10-13 13:28:02','2026-10-13 13:28:02'),(5,'currency_code','NGN','finance',1,'2026-10-13 13:28:02','2026-10-13 13:28:02'),(6,'enable_paystack','1','finance',0,'2026-10-13 13:28:02','2026-10-13 13:28:02'),(7,'enable_sales_crm','1','sales',0,'2026-10-13 13:28:02','2026-10-13 13:28:02'),(8,'compliance_standard','Nigeria Data Protection Act (NDPA) 2023 Compliant','security',1,'2026-10-13 13:28:02','2026-10-13 13:28:02');
/*!40000 ALTER TABLE `platform_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `portal_billing_notices`
--

DROP TABLE IF EXISTS `portal_billing_notices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `portal_billing_notices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `agreement_id` int(11) NOT NULL,
  `term_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `notice_body` text NOT NULL,
  `notice_type` enum('term_announcement','invoice_notification','payment_reminder','payment_receipt','refund_notice') NOT NULL DEFAULT 'term_announcement',
  `sent_via` enum('portal_announcement','email','sms') NOT NULL DEFAULT 'portal_announcement',
  `recipient_count` int(11) NOT NULL DEFAULT 0,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `school_id` (`school_id`),
  KEY `agreement_id` (`agreement_id`),
  KEY `term_id` (`term_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `portal_billing_notices_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `portal_billing_notices_ibfk_2` FOREIGN KEY (`agreement_id`) REFERENCES `school_portal_fee_agreements` (`id`) ON DELETE CASCADE,
  CONSTRAINT `portal_billing_notices_ibfk_3` FOREIGN KEY (`term_id`) REFERENCES `academic_terms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `portal_billing_notices_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `portal_billing_notices`
--

LOCK TABLES `portal_billing_notices` WRITE;
/*!40000 ALTER TABLE `portal_billing_notices` DISABLE KEYS */;
INSERT INTO `portal_billing_notices` VALUES (1,1,1,1,'Official Notice: 2025/2026 First Term Student Portal Access Fee','Dear Parents and Guardians, please be informed that the approved digital portal access fee of ₦3,000 per pupil for the 2025/2026 First Term is now active. This provides continuous access to live continuous assessments, terminal digital report cards, lesson notes, and fee reconciliations.','term_announcement','portal_announcement',5,1,5,'2026-10-13 13:28:03');
/*!40000 ALTER TABLE `portal_billing_notices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `portal_commission_ledger`
--

DROP TABLE IF EXISTS `portal_commission_ledger`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `portal_commission_ledger` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rep_id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `allocation_id` int(11) NOT NULL,
  `term_id` int(11) NOT NULL,
  `commission_tier` enum('initial','recurring') NOT NULL DEFAULT 'initial',
  `rate_applied` decimal(5,2) NOT NULL,
  `commission_amount` decimal(12,2) NOT NULL,
  `status` enum('pending','approved','paid','reversed','disputed') NOT NULL DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `payout_reference` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `school_id` (`school_id`),
  KEY `allocation_id` (`allocation_id`),
  KEY `term_id` (`term_id`),
  KEY `approved_by` (`approved_by`),
  KEY `idx_portal_comm_rep` (`rep_id`,`status`),
  CONSTRAINT `portal_commission_ledger_ibfk_1` FOREIGN KEY (`rep_id`) REFERENCES `sales_representatives` (`id`) ON DELETE CASCADE,
  CONSTRAINT `portal_commission_ledger_ibfk_2` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `portal_commission_ledger_ibfk_3` FOREIGN KEY (`allocation_id`) REFERENCES `financial_allocations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `portal_commission_ledger_ibfk_4` FOREIGN KEY (`term_id`) REFERENCES `academic_terms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `portal_commission_ledger_ibfk_5` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `portal_commission_ledger`
--

LOCK TABLES `portal_commission_ledger` WRITE;
/*!40000 ALTER TABLE `portal_commission_ledger` DISABLE KEYS */;
INSERT INTO `portal_commission_ledger` VALUES (1,1,1,1,1,'initial',15.00,443.25,'pending',NULL,NULL,NULL,NULL,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(2,1,1,2,1,'initial',15.00,443.25,'pending',NULL,NULL,NULL,NULL,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(3,1,1,3,1,'initial',15.00,221.63,'pending',NULL,NULL,NULL,NULL,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(4,1,1,4,1,'initial',15.00,221.63,'reversed',NULL,NULL,NULL,NULL,'2026-10-13 13:30:24','2026-10-13 13:30:24'),(5,1,1,5,1,'initial',15.00,443.25,'pending',NULL,NULL,NULL,NULL,'2026-10-13 13:49:42','2026-10-13 13:49:42'),(6,1,1,6,1,'initial',15.00,443.25,'pending',NULL,NULL,NULL,NULL,'2026-10-13 13:50:03','2026-10-13 13:50:03'),(7,1,1,7,1,'initial',15.00,443.25,'pending',NULL,NULL,NULL,NULL,'2026-10-13 13:50:32','2026-10-13 13:50:32'),(8,1,1,8,1,'initial',15.00,443.25,'pending',NULL,NULL,NULL,NULL,'2026-10-13 13:51:03','2026-10-13 13:51:03'),(9,1,1,9,1,'initial',15.00,443.25,'pending',NULL,NULL,NULL,NULL,'2026-10-13 13:54:00','2026-10-13 13:54:00'),(10,1,1,10,1,'initial',15.00,221.63,'reversed',NULL,NULL,NULL,NULL,'2026-10-13 14:08:36','2026-10-13 14:08:36'),(11,1,1,11,1,'initial',15.00,443.25,'pending',NULL,NULL,NULL,NULL,'2026-10-13 14:08:37','2026-10-13 14:08:37');
/*!40000 ALTER TABLE `portal_commission_ledger` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `portal_invoices`
--

DROP TABLE IF EXISTS `portal_invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `portal_invoices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `agreement_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `term_id` int(11) NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `gross_amount` decimal(12,2) NOT NULL,
  `discount_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `waiver_reason` varchar(255) DEFAULT NULL,
  `amount_due` decimal(12,2) NOT NULL,
  `paid_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `balance_due` decimal(12,2) NOT NULL,
  `status` enum('unpaid','partially_paid','paid','waived','disputed','cancelled') NOT NULL DEFAULT 'unpaid',
  `due_date` date NOT NULL,
  `idempotency_hash` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  UNIQUE KEY `idempotency_hash` (`idempotency_hash`),
  UNIQUE KEY `uq_portal_inv_term_student` (`school_id`,`session_id`,`term_id`,`student_id`),
  KEY `student_id` (`student_id`),
  KEY `agreement_id` (`agreement_id`),
  KEY `session_id` (`session_id`),
  KEY `term_id` (`term_id`),
  KEY `idx_portal_inv_school` (`school_id`,`status`),
  KEY `idx_portal_inv_parent` (`parent_id`),
  CONSTRAINT `portal_invoices_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `portal_invoices_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `portal_invoices_ibfk_3` FOREIGN KEY (`parent_id`) REFERENCES `parents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `portal_invoices_ibfk_4` FOREIGN KEY (`agreement_id`) REFERENCES `school_portal_fee_agreements` (`id`) ON DELETE CASCADE,
  CONSTRAINT `portal_invoices_ibfk_5` FOREIGN KEY (`session_id`) REFERENCES `academic_sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `portal_invoices_ibfk_6` FOREIGN KEY (`term_id`) REFERENCES `academic_terms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `portal_invoices`
--

LOCK TABLES `portal_invoices` WRITE;
/*!40000 ALTER TABLE `portal_invoices` DISABLE KEYS */;
INSERT INTO `portal_invoices` VALUES (1,1,1,1,1,1,1,'PINV-2025-0001',3000.00,0.00,NULL,3000.00,3000.00,0.00,'paid','2025-10-31','78332a3f32bab40db427bcf3fe7f2346022e44243ea9a3f822a16fadcc59997a','2026-10-13 13:28:03','2026-10-13 14:08:37'),(2,1,2,1,1,1,1,'PINV-2025-0002',3000.00,0.00,NULL,3000.00,3000.00,0.00,'paid','2025-10-31','c9854e74f99b3c7786bd211e16f898ffaab89fc4dc04b6a7f9513c3fed889866','2026-10-13 13:28:03','2026-10-13 13:28:03'),(3,1,3,1,1,1,1,'PINV-2025-0003',3000.00,0.00,NULL,3000.00,1500.00,1500.00,'partially_paid','2025-10-31','8cab0abd2dddc6bafb0a06aecb2a9b6a6f7ff3b81ec2d89af3dec48afe312346','2026-10-13 13:28:03','2026-10-13 13:28:03'),(4,1,4,1,1,1,1,'PINV-2025-0004',3000.00,0.00,NULL,3000.00,0.00,3000.00,'unpaid','2025-10-31','711b9c2d3fd070dd56b52c79a11ab64a8d4ce4ad231c6c3fafcd55faa4bce577','2026-10-13 13:28:03','2026-10-13 14:08:36'),(5,1,5,1,1,1,1,'PINV-2025-0005',3000.00,3000.00,'Approved Academic Merit Scholarship',0.00,0.00,0.00,'waived','2025-10-31','1415a1373161ac93c2fe78f30832d5e19b62f8403015076ce1a7b4963d64e0ea','2026-10-13 13:28:03','2026-10-13 13:28:03'),(6,2,6,2,2,2,4,'PINV-2026-S2-0001',2500.00,0.00,NULL,2500.00,0.00,2500.00,'unpaid','2026-11-12','f3fdb33e2c1631a29a50bcc8df78ab3e479aa8477c63ddfcc805097b4068ba0e','2026-10-13 13:30:24','2026-10-13 13:30:24'),(7,2,7,2,2,2,4,'PINV-2026-S2-0002',2500.00,0.00,NULL,2500.00,0.00,2500.00,'unpaid','2026-11-12','9fdd57b784c2ee5fa8a024951a1777e8b3b95a6a6fd7ceb093b4c415eb520207','2026-10-13 13:30:24','2026-10-13 13:30:24'),(8,2,8,2,2,2,4,'PINV-2026-S2-0003',2500.00,0.00,NULL,2500.00,0.00,2500.00,'unpaid','2026-11-12','1c04e575f2efc7556d29ae98329adbf90417bef2085df78e8c6ff1f4e4fbd632','2026-10-13 13:30:24','2026-10-13 13:30:24'),(10,1,9,3,1,1,1,'PINV-2026-S1-0006',3000.00,0.00,NULL,3000.00,0.00,3000.00,'unpaid','2026-11-12','41563e7fc46b47e68bd7f1106c30da6b7a833f1b7e5f9b1d974d42f9a0b2f980','2026-10-13 14:08:36','2026-10-13 14:08:36');
/*!40000 ALTER TABLE `portal_invoices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `portal_payments`
--

DROP TABLE IF EXISTS `portal_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `portal_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `payment_reference` varchar(100) NOT NULL,
  `gateway` enum('paystack','bank_transfer','cash','pos') NOT NULL DEFAULT 'paystack',
  `amount` decimal(12,2) NOT NULL,
  `gateway_fee` decimal(12,2) NOT NULL DEFAULT 0.00,
  `net_amount` decimal(12,2) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'NGN',
  `payer_name` varchar(150) DEFAULT NULL,
  `payer_email` varchar(150) DEFAULT NULL,
  `status` enum('pending','verified','failed','reversed','refunded') NOT NULL DEFAULT 'verified',
  `notes` text DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `verified_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_reference` (`payment_reference`),
  KEY `invoice_id` (`invoice_id`),
  KEY `student_id` (`student_id`),
  KEY `parent_id` (`parent_id`),
  KEY `verified_by` (`verified_by`),
  KEY `idx_portal_pay_school` (`school_id`,`status`),
  CONSTRAINT `portal_payments_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `portal_payments_ibfk_2` FOREIGN KEY (`invoice_id`) REFERENCES `portal_invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `portal_payments_ibfk_3` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `portal_payments_ibfk_4` FOREIGN KEY (`parent_id`) REFERENCES `parents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `portal_payments_ibfk_5` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `portal_payments`
--

LOCK TABLES `portal_payments` WRITE;
/*!40000 ALTER TABLE `portal_payments` DISABLE KEYS */;
INSERT INTO `portal_payments` VALUES (1,1,1,1,1,'PAY-PTL-20261013-43001','paystack',3000.00,45.00,2955.00,'NGN',NULL,NULL,'verified',NULL,'2026-10-13 13:28:03',1,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(2,1,2,2,1,'PAY-PTL-20261013-63399','paystack',3000.00,45.00,2955.00,'NGN',NULL,NULL,'verified',NULL,'2026-10-13 13:28:03',1,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(3,1,3,3,1,'PAY-PTL-20261013-97501','paystack',1500.00,22.50,1477.50,'NGN',NULL,NULL,'verified',NULL,'2026-10-13 13:28:03',1,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(4,1,4,4,1,'TEST-PAY-20261013-17438','paystack',1500.00,22.50,1477.50,'NGN','Chief Emeka Okafor','parent@apexcollege.edu.ng','refunded','Duplicate test deposit reversal','2026-10-13 13:30:24',NULL,'2026-10-13 13:30:24','2026-10-13 13:30:24'),(5,1,1,1,1,'PAY-ONLINE-STU-20261013-32731','paystack',3000.00,45.00,2955.00,'NGN','Chinedu Okafor','student@apexcollege.edu.ng','verified',NULL,'2026-10-13 13:49:42',10,'2026-10-13 13:49:42','2026-10-13 13:49:42'),(6,1,1,1,1,'PAY-ONLINE-STU-20261013-43503','paystack',3000.00,45.00,2955.00,'NGN','Chinedu Okafor','student@apexcollege.edu.ng','verified',NULL,'2026-10-13 13:50:03',10,'2026-10-13 13:50:03','2026-10-13 13:50:03'),(7,1,1,1,1,'PAY-ONLINE-STU-20261013-16340','paystack',3000.00,45.00,2955.00,'NGN','Chinedu Okafor','student@apexcollege.edu.ng','verified',NULL,'2026-10-13 13:50:32',10,'2026-10-13 13:50:32','2026-10-13 13:50:32'),(8,1,1,1,1,'PAY-ONLINE-STU-20261013-84778','paystack',3000.00,45.00,2955.00,'NGN','Chinedu Okafor','student@apexcollege.edu.ng','verified',NULL,'2026-10-13 13:51:03',10,'2026-10-13 13:51:03','2026-10-13 13:51:03'),(9,1,1,1,1,'PAY-ONLINE-STU-20261013-59540','paystack',3000.00,45.00,2955.00,'NGN','Chinedu Okafor','student@apexcollege.edu.ng','verified',NULL,'2026-10-13 13:54:00',10,'2026-10-13 13:54:00','2026-10-13 13:54:00'),(10,1,4,4,1,'TEST-PAY-20261013-65949','paystack',1500.00,22.50,1477.50,'NGN','Chief Emeka Okafor','parent@apexcollege.edu.ng','refunded','Duplicate test deposit reversal','2026-10-13 14:08:36',NULL,'2026-10-13 14:08:36','2026-10-13 14:08:36'),(11,1,1,1,1,'PAY-ONLINE-STU-20261013-31815','paystack',3000.00,45.00,2955.00,'NGN','Chinedu Okafor','student@apexcollege.edu.ng','verified',NULL,'2026-10-13 14:08:37',10,'2026-10-13 14:08:37','2026-10-13 14:08:37');
/*!40000 ALTER TABLE `portal_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `portal_pricing_policies`
--

DROP TABLE IF EXISTS `portal_pricing_policies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `portal_pricing_policies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `policy_code` varchar(50) NOT NULL,
  `school_id` int(11) DEFAULT NULL,
  `session_id` int(11) DEFAULT NULL,
  `term_id` int(11) DEFAULT NULL,
  `fee_per_student` decimal(12,2) NOT NULL DEFAULT 3000.00,
  `currency` varchar(10) NOT NULL DEFAULT 'NGN',
  `fee_payer` enum('parent','school_sponsored') NOT NULL DEFAULT 'parent',
  `min_fee` decimal(12,2) DEFAULT NULL,
  `max_fee` decimal(12,2) DEFAULT NULL,
  `discount_policy` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`discount_policy`)),
  `effective_date` date NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `status` enum('draft','pending_approval','approved','active','expired','archived') NOT NULL DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `policy_code` (`policy_code`),
  KEY `school_id` (`school_id`),
  KEY `created_by` (`created_by`),
  KEY `approved_by` (`approved_by`),
  CONSTRAINT `portal_pricing_policies_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `portal_pricing_policies_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `portal_pricing_policies_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `portal_pricing_policies`
--

LOCK TABLES `portal_pricing_policies` WRITE;
/*!40000 ALTER TABLE `portal_pricing_policies` DISABLE KEYS */;
INSERT INTO `portal_pricing_policies` VALUES (1,'DEFAULT-TERM-2025',NULL,NULL,NULL,3000.00,'NGN','parent',NULL,NULL,NULL,'2025-09-01',NULL,'active','Standard Platform Default Portal Fee: ₦3,000/student/term',1,1,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(2,'HORIZON-SPECIAL-2025',2,NULL,NULL,2500.00,'NGN','parent',NULL,NULL,NULL,'2025-09-01',NULL,'active','Discounted Partner Rate for Horizon Academy: ₦2,500/student/term',1,1,'2026-10-13 13:28:03','2026-10-13 13:28:03');
/*!40000 ALTER TABLE `portal_pricing_policies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `revenue_sharing_policies`
--

DROP TABLE IF EXISTS `revenue_sharing_policies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `revenue_sharing_policies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `policy_code` varchar(50) NOT NULL,
  `school_id` int(11) DEFAULT NULL,
  `allocation_basis` enum('gross','net_after_gateway') NOT NULL DEFAULT 'net_after_gateway',
  `platform_percentage` decimal(5,2) NOT NULL DEFAULT 45.00,
  `school_incentive_percentage` decimal(5,2) NOT NULL DEFAULT 40.00,
  `sales_rep_initial_rate` decimal(5,2) NOT NULL DEFAULT 15.00,
  `sales_rep_recurring_rate` decimal(5,2) NOT NULL DEFAULT 7.50,
  `gateway_fee_bearer` enum('platform','school','parent','split') NOT NULL DEFAULT 'platform',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `policy_code` (`policy_code`),
  KEY `school_id` (`school_id`),
  KEY `created_by` (`created_by`),
  KEY `approved_by` (`approved_by`),
  CONSTRAINT `revenue_sharing_policies_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `revenue_sharing_policies_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `revenue_sharing_policies_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `revenue_sharing_policies`
--

LOCK TABLES `revenue_sharing_policies` WRITE;
/*!40000 ALTER TABLE `revenue_sharing_policies` DISABLE KEYS */;
INSERT INTO `revenue_sharing_policies` VALUES (1,'DEFAULT-REVSHARE-2025',NULL,'net_after_gateway',45.00,40.00,15.00,7.50,'platform',1,1,1,'2026-10-13 13:28:03','2026-10-13 13:28:03');
/*!40000 ALTER TABLE `revenue_sharing_policies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sales_activities`
--

DROP TABLE IF EXISTS `sales_activities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sales_activities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lead_id` int(11) NOT NULL,
  `rep_id` int(11) NOT NULL,
  `activity_type` enum('call','meeting','demo','email','note','stage_change') NOT NULL,
  `summary` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `scheduled_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `lead_id` (`lead_id`),
  KEY `rep_id` (`rep_id`),
  CONSTRAINT `sales_activities_ibfk_1` FOREIGN KEY (`lead_id`) REFERENCES `sales_leads` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sales_activities_ibfk_2` FOREIGN KEY (`rep_id`) REFERENCES `sales_representatives` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sales_activities`
--

LOCK TABLES `sales_activities` WRITE;
/*!40000 ALTER TABLE `sales_activities` DISABLE KEYS */;
INSERT INTO `sales_activities` VALUES (1,1,1,'call','Initial discovery call with Chairman','Discussed paper-based report cards bottlenecks and financial reconciliation leaks.','2026-09-13 14:28:03','2026-09-13 14:28:03','2026-10-13 13:28:03'),(2,1,1,'demo','On-site platform demonstration','Presented multi-tenant portal, result computations and Paystack school fee collection.','2026-09-23 14:28:03','2026-09-23 14:28:03','2026-10-13 13:28:03'),(3,1,1,'stage_change','Contract Executed & Onboarded','School enrolled on Premium Plan at ₦600,000/yr.','2026-10-03 14:28:03','2026-10-03 14:28:03','2026-10-13 13:28:03'),(4,2,1,'note','Contract terms under review with school board.',NULL,NULL,'2026-10-13 14:28:03','2026-10-13 13:28:03'),(5,3,1,'note','Custom annual proposal sent for 800 student tier.',NULL,NULL,'2026-10-13 14:28:03','2026-10-13 13:28:03'),(6,4,1,'note','Scheduled live presentation for the academic committee.',NULL,NULL,'2026-10-13 14:28:03','2026-10-13 13:28:03'),(7,5,1,'note','Budget verified for 2026 onboarding.',NULL,NULL,'2026-10-13 14:28:03','2026-10-13 13:28:03'),(8,6,1,'note','Follow-up call booked for Thursday 10am.',NULL,NULL,'2026-10-13 14:28:03','2026-10-13 13:28:03'),(9,7,1,'note','Lead generated from Lagos State Private School Exhibition.',NULL,NULL,'2026-10-13 14:28:03','2026-10-13 13:28:03'),(10,8,2,'stage_change','Prospect school registered during commercial sales partner onboarding',NULL,NULL,'2026-10-14 08:53:30','2026-10-14 07:53:30');
/*!40000 ALTER TABLE `sales_activities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sales_commissions`
--

DROP TABLE IF EXISTS `sales_commissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sales_commissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rep_id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `subscription_id` int(11) DEFAULT NULL,
  `transaction_amount` decimal(12,2) NOT NULL,
  `commission_rate` decimal(5,2) NOT NULL,
  `commission_amount` decimal(12,2) NOT NULL,
  `status` enum('pending','approved','paid','reversed','disputed') DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `payout_reference` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `rep_id` (`rep_id`),
  KEY `school_id` (`school_id`),
  KEY `subscription_id` (`subscription_id`),
  KEY `approved_by` (`approved_by`),
  CONSTRAINT `sales_commissions_ibfk_1` FOREIGN KEY (`rep_id`) REFERENCES `sales_representatives` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sales_commissions_ibfk_2` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sales_commissions_ibfk_3` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sales_commissions_ibfk_4` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sales_commissions`
--

LOCK TABLES `sales_commissions` WRITE;
/*!40000 ALTER TABLE `sales_commissions` DISABLE KEYS */;
INSERT INTO `sales_commissions` VALUES (1,1,1,1,600000.00,10.00,60000.00,'approved',2,'2026-10-08 13:28:03',NULL,NULL,NULL,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(2,1,1,NULL,500000.00,10.00,50000.00,'paid',2,'2026-10-13 14:06:58','2026-10-13 14:06:58','BNK-REF-99281',NULL,'2026-10-13 14:06:58','2026-10-13 14:06:58'),(3,1,1,NULL,500000.00,10.00,50000.00,'paid',2,'2026-10-13 14:07:50','2026-10-13 14:07:50','BNK-REF-99281',NULL,'2026-10-13 14:07:50','2026-10-13 14:07:50'),(4,1,1,NULL,500000.00,10.00,50000.00,'paid',2,'2026-10-13 14:08:37','2026-10-13 14:08:37','BNK-REF-99281',NULL,'2026-10-13 14:08:37','2026-10-13 14:08:37');
/*!40000 ALTER TABLE `sales_commissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sales_leads`
--

DROP TABLE IF EXISTS `sales_leads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sales_leads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rep_id` int(11) NOT NULL,
  `school_name` varchar(200) NOT NULL,
  `contact_person` varchar(150) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(50) NOT NULL,
  `address` text DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `estimated_students` int(11) DEFAULT 300,
  `lead_source` varchar(100) DEFAULT 'Direct Outreach',
  `stage` enum('new_lead','contacted','qualified','demo_scheduled','proposal_sent','negotiation','won','lost') DEFAULT 'new_lead',
  `lost_reason` varchar(255) DEFAULT NULL,
  `expected_closing_date` date DEFAULT NULL,
  `created_school_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `rep_id` (`rep_id`),
  KEY `created_school_id` (`created_school_id`),
  CONSTRAINT `sales_leads_ibfk_1` FOREIGN KEY (`rep_id`) REFERENCES `sales_representatives` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sales_leads_ibfk_2` FOREIGN KEY (`created_school_id`) REFERENCES `schools` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sales_leads`
--

LOCK TABLES `sales_leads` WRITE;
/*!40000 ALTER TABLE `sales_leads` DISABLE KEYS */;
INSERT INTO `sales_leads` VALUES (1,1,'Apex Crown College','Dr. Olawale Sanusi','admin@apexcollege.edu.ng','+2348033221101','Plot 14 Commercial Ave, Ikeja','Lagos',650,'Outbound Direct','won',NULL,NULL,1,NULL,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(2,1,'Grace High Grammar School','Alhaji Rasheed Bello','gracehigh@example.com','+2348021122331','Surulere, Lagos','Lagos',450,'Direct Outreach','negotiation',NULL,NULL,NULL,'Contract terms under review with school board.','2026-10-13 13:28:03','2026-10-13 13:28:03'),(3,1,'Royal Heritage Model College','Rev. Father Paul','royalheritage@example.com','+2348032233442','Ikorodu, Lagos','Lagos',800,'Direct Outreach','proposal_sent',NULL,NULL,NULL,'Custom annual proposal sent for 800 student tier.','2026-10-13 13:28:03','2026-10-13 13:28:03'),(4,1,'Kingsway College Victoria Island','Mrs. Amanda Lawson','kingsway@example.com','+2348053344553','Victoria Island, Lagos','Lagos',550,'Direct Outreach','demo_scheduled',NULL,NULL,NULL,'Scheduled live presentation for the academic committee.','2026-10-13 13:28:03','2026-10-13 13:28:03'),(5,1,'Anchor International School','Chief Anthony Obi','anchor@example.com','+2348074455664','Abeokuta, Ogun State','Lagos',350,'Direct Outreach','qualified',NULL,NULL,NULL,'Budget verified for 2026 onboarding.','2026-10-13 13:28:03','2026-10-13 13:28:03'),(6,1,'Bethel Christian Academy','Pastor Samuel David','bethel@example.com','+2348095566775','Magodo, Lagos','Lagos',280,'Direct Outreach','contacted',NULL,NULL,NULL,'Follow-up call booked for Thursday 10am.','2026-10-13 13:28:03','2026-10-13 13:28:03'),(7,1,'New Era Comprehensive High','Mr. Festus Ade','newera@example.com','+2348016677886','Otta, Ogun State','Lagos',400,'Direct Outreach','new_lead',NULL,NULL,NULL,'Lead generated from Lagos State Private School Exhibition.','2026-10-13 13:28:03','2026-10-13 13:28:03'),(8,2,'Oakfield Model Academy 643','Dr. Mrs. Cynthia Alabi (Proprietress)','admin@oakfield442.example','08038636694','Plot 4, Admiralty Road, Lekki Phase 1','Lagos',450,'Partner Self-Registration','new_lead',NULL,NULL,NULL,'Current Management: Paper report cards & manual registers | Notes: Proprietress wants automated SMS terminal fee receipts and online student portal | School Category: Combined','2026-10-14 07:53:30','2026-10-14 07:53:30');
/*!40000 ALTER TABLE `sales_leads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sales_partner_agreements`
--

DROP TABLE IF EXISTS `sales_partner_agreements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sales_partner_agreements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rep_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `agreement_version` varchar(20) NOT NULL DEFAULT 'v1.0',
  `signer_name` varchar(150) NOT NULL,
  `signer_email` varchar(150) NOT NULL,
  `signer_phone` varchar(50) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `terms_snapshot` text NOT NULL,
  `accepted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `rep_id` (`rep_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `sales_partner_agreements_ibfk_1` FOREIGN KEY (`rep_id`) REFERENCES `sales_representatives` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sales_partner_agreements_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sales_partner_agreements`
--

LOCK TABLES `sales_partner_agreements` WRITE;
/*!40000 ALTER TABLE `sales_partner_agreements` DISABLE KEYS */;
INSERT INTO `sales_partner_agreements` VALUES (1,2,21,'v1.0','Chinedu Eze','partner_test_1791964410@example.com','08036491320','192.168.1.105','PHPUnit/EduSphereTestBrowser','EDUSPHERE COMMERCIAL SALES PARTNER AGREEMENT (v1.0)\nSigned Digitally By: Chinedu Eze (partner_test_1791964410@example.com)\nPhone: 08036491320\nTerritory: Lagos (Island & Lekki)\nSettlement Bank: Zenith Bank | Account: 1029384756 | Name: Chinedu Eze\nTimestamp: 2026-10-14 09:53:30\nIP Address: 192.168.1.105\nUser Agent: PHPUnit/EduSphereTestBrowser\n\nCORE TERMS AGREED:\n1. Commission Schedule: 15.0% on verified initial academic term portal fees and subscription payments, and 7.5% recurring commission on subsequent terms.\n2. Governance & Anti-Collusion: All commission payouts require Sales Manager compliance verification. Representatives cannot approve their own commissions.\n3. Code of Conduct: Representation must adhere to EduSphere standards. Direct submission of authentic school prospect data required.\n4. Payout Channel: Settlements are electronically disbursed to the verified Nigerian bank account provided.','2026-10-14 07:53:30');
/*!40000 ALTER TABLE `sales_partner_agreements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sales_representatives`
--

DROP TABLE IF EXISTS `sales_representatives`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sales_representatives` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `rep_code` varchar(30) NOT NULL,
  `territory` varchar(100) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `manager_id` int(11) DEFAULT NULL,
  `commission_type` enum('percentage','fixed','tiered') DEFAULT 'percentage',
  `commission_rate` decimal(5,2) DEFAULT 10.00,
  `monthly_target` decimal(12,2) DEFAULT 500000.00,
  `status` enum('active','suspended') DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `account_number` varchar(30) DEFAULT NULL,
  `account_name` varchar(150) DEFAULT NULL,
  `agreement_accepted_at` timestamp NULL DEFAULT NULL,
  `agreement_version` varchar(20) DEFAULT 'v1.0',
  `agreement_ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  UNIQUE KEY `rep_code` (`rep_code`),
  KEY `manager_id` (`manager_id`),
  CONSTRAINT `sales_representatives_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sales_representatives_ibfk_2` FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sales_representatives`
--

LOCK TABLES `sales_representatives` WRITE;
/*!40000 ALTER TABLE `sales_representatives` DISABLE KEYS */;
INSERT INTO `sales_representatives` VALUES (1,3,'REP-LAGOS-01','Lagos & Ogun State Metro',NULL,2,'percentage',10.00,1500000.00,'active',NULL,NULL,NULL,NULL,NULL,'v1.0',NULL,'2026-10-13 13:28:02','2026-10-13 13:28:02'),(2,21,'REP-LAG-5511','Lagos (Island & Lekki)','08036491320',NULL,'percentage',15.00,500000.00,'active','Self-registered partner. Territory: Lagos (Island & Lekki)','Zenith Bank','1029384756','Chinedu Eze','2026-10-14 07:53:30','v1.0','192.168.1.105','2026-10-14 07:53:30','2026-10-14 07:53:30');
/*!40000 ALTER TABLE `sales_representatives` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `school_incentive_ledger`
--

DROP TABLE IF EXISTS `school_incentive_ledger`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `school_incentive_ledger` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `allocation_id` int(11) DEFAULT NULL,
  `term_id` int(11) DEFAULT NULL,
  `entry_type` enum('accrual','payout','adjustment','reversal') NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `running_balance` decimal(12,2) NOT NULL,
  `reference` varchar(100) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `allocation_id` (`allocation_id`),
  KEY `term_id` (`term_id`),
  KEY `idx_school_incentive_sch` (`school_id`),
  CONSTRAINT `school_incentive_ledger_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `school_incentive_ledger_ibfk_2` FOREIGN KEY (`allocation_id`) REFERENCES `financial_allocations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `school_incentive_ledger_ibfk_3` FOREIGN KEY (`term_id`) REFERENCES `academic_terms` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `school_incentive_ledger`
--

LOCK TABLES `school_incentive_ledger` WRITE;
/*!40000 ALTER TABLE `school_incentive_ledger` DISABLE KEYS */;
INSERT INTO `school_incentive_ledger` VALUES (1,1,1,1,'accrual',1182.00,1182.00,'PAY-PTL-20261013-43001','Portal fee collection incentive accrual','2026-10-13 13:28:03'),(2,1,2,1,'accrual',1182.00,2364.00,'PAY-PTL-20261013-63399','Portal fee collection incentive accrual','2026-10-13 13:28:03'),(3,1,3,1,'accrual',591.00,2955.00,'PAY-PTL-20261013-97501','Portal fee collection incentive accrual','2026-10-13 13:28:03'),(4,1,4,1,'accrual',591.00,3546.00,'TEST-PAY-20261013-17438','Portal fee payment incentive allocation','2026-10-13 13:30:24'),(5,1,NULL,NULL,'payout',-500.00,3046.00,'DISB-TEST-20261013-9999','Approved bank payout disbursement','2026-10-13 13:30:24'),(6,1,4,NULL,'reversal',-591.00,2455.00,'TEST-PAY-20261013-17438','Refund reversal: Duplicate test deposit reversal','2026-10-13 13:30:24'),(7,1,5,1,'accrual',1182.00,3637.00,'PAY-ONLINE-STU-20261013-32731','Portal fee payment incentive allocation','2026-10-13 13:49:42'),(8,1,6,1,'accrual',1182.00,4819.00,'PAY-ONLINE-STU-20261013-43503','Portal fee payment incentive allocation','2026-10-13 13:50:03'),(9,1,7,1,'accrual',1182.00,6001.00,'PAY-ONLINE-STU-20261013-16340','Portal fee payment incentive allocation','2026-10-13 13:50:32'),(10,1,8,1,'accrual',1182.00,7183.00,'PAY-ONLINE-STU-20261013-84778','Portal fee payment incentive allocation','2026-10-13 13:51:03'),(11,1,9,1,'accrual',1182.00,8365.00,'PAY-ONLINE-STU-20261013-59540','Portal fee payment incentive allocation','2026-10-13 13:54:00'),(12,1,10,1,'accrual',591.00,8956.00,'TEST-PAY-20261013-65949','Portal fee payment incentive allocation','2026-10-13 14:08:36'),(13,1,NULL,NULL,'payout',-500.00,8456.00,'DISB-TEST-20261013-9999','Approved bank payout disbursement','2026-10-13 14:08:36'),(14,1,10,NULL,'reversal',-591.00,7865.00,'TEST-PAY-20261013-65949','Refund reversal: Duplicate test deposit reversal','2026-10-13 14:08:36'),(15,1,11,1,'accrual',1182.00,9047.00,'PAY-ONLINE-STU-20261013-31815','Portal fee payment incentive allocation','2026-10-13 14:08:37');
/*!40000 ALTER TABLE `school_incentive_ledger` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `school_notices`
--

DROP TABLE IF EXISTS `school_notices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `school_notices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `category` enum('general','academic','holiday','event','sports','pta','urgent') NOT NULL DEFAULT 'general',
  `priority` enum('normal','important','urgent') NOT NULL DEFAULT 'normal',
  `target_audience` enum('all','parents','students','staff') NOT NULL DEFAULT 'all',
  `is_pinned` tinyint(1) NOT NULL DEFAULT 0,
  `published_at` datetime NOT NULL DEFAULT current_timestamp(),
  `expires_at` date DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `idx_notices_school_target` (`school_id`,`target_audience`,`is_pinned`),
  CONSTRAINT `school_notices_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `school_notices_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `school_notices`
--

LOCK TABLES `school_notices` WRITE;
/*!40000 ALTER TABLE `school_notices` DISABLE KEYS */;
INSERT INTO `school_notices` VALUES (1,1,'Resumption Guidelines & Assembly Schedule for Term 1','Dear Students and Parents,\n\nWelcome to the 2025/2026 Academic Session! All pupils are required to be in full school uniform for the morning devotion at 7:45 AM prompt. Class registers will close at 8:15 AM.\n\nLooking forward to an inspiring academic session.','academic','important','all',1,'2026-10-13 14:28:03','2026-11-30',5,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(2,1,'Annual Inter-House Athletics Championship 2026','The Apex Crown College Inter-House Sports competition will take place on Friday, November 14th at the campus sports arena. Parents and guardians are cordially invited to cheer their wards.','sports','normal','all',0,'2026-10-13 14:28:03','2026-11-15',5,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(3,1,'Mid-Term Break & Visiting Day Notice','Please be advised that mid-term break commences on Thursday, October 23rd. Boarding students may be visited between 1:00 PM and 5:00 PM on Sunday.','holiday','normal','parents',0,'2026-10-13 14:28:03','2026-10-31',5,'2026-10-13 13:28:03','2026-10-13 13:28:03');
/*!40000 ALTER TABLE `school_notices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `school_payout_requests`
--

DROP TABLE IF EXISTS `school_payout_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `school_payout_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `request_code` varchar(50) NOT NULL,
  `requested_amount` decimal(12,2) NOT NULL,
  `bank_name` varchar(100) NOT NULL,
  `account_number` varchar(30) NOT NULL,
  `account_name` varchar(150) NOT NULL,
  `status` enum('pending','approved','rejected','paid') NOT NULL DEFAULT 'pending',
  `requested_by` int(11) NOT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `disbursement_reference` varchar(100) DEFAULT NULL,
  `rejection_reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `request_code` (`request_code`),
  KEY `school_id` (`school_id`),
  KEY `requested_by` (`requested_by`),
  KEY `approved_by` (`approved_by`),
  CONSTRAINT `school_payout_requests_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `school_payout_requests_ibfk_2` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `school_payout_requests_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `school_payout_requests`
--

LOCK TABLES `school_payout_requests` WRITE;
/*!40000 ALTER TABLE `school_payout_requests` DISABLE KEYS */;
INSERT INTO `school_payout_requests` VALUES (1,1,'POUT-SCH-20261013-4543',500.00,'Zenith Bank PLC','1012345678','Apex Crown College Commercial','paid',1,1,'2026-10-13 13:30:24','2026-10-13 13:30:24','DISB-TEST-20261013-9999',NULL,'2026-10-13 13:30:24','2026-10-13 13:30:24'),(2,1,'POUT-SCH-20261013-5621',500.00,'Zenith Bank PLC','1012345678','Apex Crown College Commercial','paid',1,1,'2026-10-13 14:08:36','2026-10-13 14:08:36','DISB-TEST-20261013-9999',NULL,'2026-10-13 14:08:36','2026-10-13 14:08:36');
/*!40000 ALTER TABLE `school_payout_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `school_portal_fee_agreements`
--

DROP TABLE IF EXISTS `school_portal_fee_agreements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `school_portal_fee_agreements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `term_id` int(11) NOT NULL,
  `pricing_policy_id` int(11) NOT NULL,
  `revenue_policy_id` int(11) NOT NULL,
  `proposed_fee_per_student` decimal(12,2) NOT NULL,
  `estimated_eligible_students` int(11) NOT NULL DEFAULT 0,
  `status` enum('proposed','accepted','rejected','amendment_requested') NOT NULL DEFAULT 'proposed',
  `accepted_by` int(11) DEFAULT NULL,
  `accepted_at` timestamp NULL DEFAULT NULL,
  `school_notes` text DEFAULT NULL,
  `rejection_reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_school_term_agreement` (`school_id`,`session_id`,`term_id`),
  KEY `session_id` (`session_id`),
  KEY `term_id` (`term_id`),
  KEY `pricing_policy_id` (`pricing_policy_id`),
  KEY `revenue_policy_id` (`revenue_policy_id`),
  KEY `accepted_by` (`accepted_by`),
  CONSTRAINT `school_portal_fee_agreements_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `school_portal_fee_agreements_ibfk_2` FOREIGN KEY (`session_id`) REFERENCES `academic_sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `school_portal_fee_agreements_ibfk_3` FOREIGN KEY (`term_id`) REFERENCES `academic_terms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `school_portal_fee_agreements_ibfk_4` FOREIGN KEY (`pricing_policy_id`) REFERENCES `portal_pricing_policies` (`id`),
  CONSTRAINT `school_portal_fee_agreements_ibfk_5` FOREIGN KEY (`revenue_policy_id`) REFERENCES `revenue_sharing_policies` (`id`),
  CONSTRAINT `school_portal_fee_agreements_ibfk_6` FOREIGN KEY (`accepted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `school_portal_fee_agreements`
--

LOCK TABLES `school_portal_fee_agreements` WRITE;
/*!40000 ALTER TABLE `school_portal_fee_agreements` DISABLE KEYS */;
INSERT INTO `school_portal_fee_agreements` VALUES (1,1,1,1,1,1,3000.00,5,'accepted',5,'2026-10-03 13:28:03','Agreement reviewed and accepted by School Management for 2025/2026 First Term.',NULL,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(2,2,2,4,2,1,2500.00,3,'accepted',1,'2026-10-13 13:30:24','Auto-accepted for test',NULL,'2026-10-13 13:28:03','2026-10-13 13:30:24');
/*!40000 ALTER TABLE `school_portal_fee_agreements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `school_website_pages`
--

DROP TABLE IF EXISTS `school_website_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `school_website_pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `page_slug` varchar(50) NOT NULL,
  `title` varchar(150) NOT NULL,
  `banner_title` varchar(200) DEFAULT NULL,
  `banner_subtitle` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `sections` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`sections`)),
  `is_published` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_school_page` (`school_id`,`page_slug`),
  CONSTRAINT `school_website_pages_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `school_website_pages`
--

LOCK TABLES `school_website_pages` WRITE;
/*!40000 ALTER TABLE `school_website_pages` DISABLE KEYS */;
INSERT INTO `school_website_pages` VALUES (1,1,'home','Welcome to Apex Crown College','Inspiring Excellence, Fostering Global Leaders','Providing holistic academic education, moral integrity and 21st-century technological acumen since 2008.','At Apex Crown College, we believe in unlocking the unique potential of every student through innovative curricula, world-class laboratory facilities, and dedicated faculty.',NULL,1,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(2,1,'about','About Apex Crown College','Our Heritage of Academic Distinction','Founded with a vision to revolutionize secondary education in Nigeria.','Apex Crown College combines the Nigerian National Curriculum with Cambridge IGCSE standards, giving our students the competitive edge required for top universities worldwide.',NULL,1,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(3,1,'admissions','Admissions & Enrollment','Join Our Vibrant Academic Community','Applications for the 2025/2026 Academic Session are now open.','We welcome applications from motivated learners into JSS 1 through SSS 2. Complete our streamlined online application to begin the journey.',NULL,1,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(4,1,'academics','Academic Excellence & Curriculum','Rigorous Academic Rigor with Real-World Application','Nurturing well-rounded scholars through STEM, Humanities, and Vocational disciplines.','Our low teacher-to-student ratio ensures tailored mentorship and continuous performance tracking.',NULL,1,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(5,1,'contact','Contact Our Campus','Get in Touch with Our Administration','Our admissions counselors are available Monday through Friday from 8:00 AM to 4:00 PM.','Visit us at Plot 14 Commercial Avenue, Ikeja GRA, Lagos or call +234 802 334 4551.',NULL,1,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(6,2,'home','Welcome to Horizon International Academy','Nurturing Global Thinkers & Future Leaders','Leading early years and primary school education in Maitama, Abuja.','At Horizon International Academy, we provide a nurturing, inquiry-based learning environment where young minds blossom.',NULL,1,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(7,2,'about','About Horizon International Academy','Excellence from the Early Years','Dedicated to academic curiosity and moral development.','Founded with a deep commitment to modern, child-centered pedagogy in the heart of the Federal Capital Territory.',NULL,1,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(8,2,'admissions','Admissions at Horizon','Enroll Your Child in Primary Excellence','Now accepting applications for Primary 1 through Primary 5.','Join our caring, stimulating academic environment.',NULL,1,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(9,2,'academics','Academic Curriculum & STEM','Holistic Primary Education','Blending international standards with foundational Nigerian curricula.','Focusing on literacy, numeracy, critical thinking and creativity.',NULL,1,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(10,2,'contact','Contact Horizon Academy','Visit Our Maitama Campus','Open Monday to Friday 7:30 AM to 3:30 PM.','12 Diplomatic Drive, Maitama, Abuja. Phone: +234 802 998 8772.',NULL,1,'2026-10-13 13:28:03','2026-10-13 13:28:03');
/*!40000 ALTER TABLE `school_website_pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `schools`
--

DROP TABLE IF EXISTS `schools`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `schools` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(36) NOT NULL,
  `code` varchar(30) NOT NULL,
  `name` varchar(200) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `motto` varchar(255) DEFAULT NULL,
  `logo_url` varchar(255) DEFAULT NULL,
  `school_type` enum('primary','secondary','combined','nursery_primary') DEFAULT 'combined',
  `email` varchar(150) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT 'Nigeria',
  `website` varchar(200) DEFAULT NULL,
  `current_plan_id` int(11) DEFAULT NULL,
  `status` enum('trial','active','suspended','expired','archived') DEFAULT 'active',
  `subscription_status` enum('trial','active','past_due','expired') DEFAULT 'active',
  `subscription_expires_at` date DEFAULT NULL,
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`settings`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`),
  UNIQUE KEY `code` (`code`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_school_slug` (`slug`),
  KEY `idx_school_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schools`
--

LOCK TABLES `schools` WRITE;
/*!40000 ALTER TABLE `schools` DISABLE KEYS */;
INSERT INTO `schools` VALUES (1,'c3b8a101-1234-4567-89ab-cdef01234567','SCH-APEX-001','Apex Crown College','apex','Knowledge, Virtue and Leadership',NULL,'combined','admin@apexcollege.edu.ng','+2348023344551','Plot 14, Commercial Avenue, Ikeja GRA, Lagos','Lagos','Nigeria',NULL,2,'active','active','2027-10-01',NULL,'2026-10-13 13:28:02','2026-10-13 13:28:02'),(2,'d4c9b202-2345-5678-90bc-def012345678','SCH-HORIZON-002','Horizon International Academy','horizon','Nurturing Global Thinkers',NULL,'nursery_primary','admin@horizonacademy.sch.ng','+2348029988772','12 Diplomatic Drive, Maitama, Abuja','FCT','Nigeria',NULL,1,'active','active','2027-08-15',NULL,'2026-10-13 13:28:02','2026-10-13 13:28:02');
/*!40000 ALTER TABLE `schools` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staff`
--

DROP TABLE IF EXISTS `staff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `employee_number` varchar(50) NOT NULL,
  `designation` varchar(100) NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `qualification` varchar(150) DEFAULT NULL,
  `date_of_joining` date DEFAULT NULL,
  `salary_base` decimal(12,2) DEFAULT 0.00,
  `status` enum('active','on_leave','terminated') DEFAULT 'active',
  `phone` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  UNIQUE KEY `uq_staff_emp_num` (`school_id`,`employee_number`),
  CONSTRAINT `staff_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `staff_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staff`
--

LOCK TABLES `staff` WRITE;
/*!40000 ALTER TABLE `staff` DISABLE KEYS */;
INSERT INTO `staff` VALUES (1,1,5,'APX-EMP-001','Vice Principal & Senior Master','Administration','M.Ed Educational Management','2020-09-01',320000.00,'active','+2348033221102',NULL,'2026-10-13 13:28:02','2026-10-13 13:28:02'),(2,1,6,'APX-EMP-002','Academic Registrar','Registry','B.Sc Public Administration','2021-01-15',240000.00,'active','+2348033221103',NULL,'2026-10-13 13:28:02','2026-10-13 13:28:02'),(3,1,7,'APX-EMP-003','Senior Mathematics & Physics Tutor','Sciences','B.Sc Mathematics & Statistics','2022-03-01',220000.00,'active','+2348033221104',NULL,'2026-10-13 13:28:02','2026-10-13 13:28:02'),(4,1,8,'APX-EMP-004','Chief Bursar & Finance Manager','Bursary','B.Sc Accounting, ICAN','2019-11-01',280000.00,'active','+2348033221105',NULL,'2026-10-13 13:28:02','2026-10-13 13:28:02'),(5,2,12,'HRZ-EMP-001','Head of School / Principal','Administration','B.Ed, M.Sc Educational Leadership','2021-08-01',310000.00,'active','+2348029988772',NULL,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(6,2,13,'HRZ-EMP-002','Senior Primary 3 Form Tutor','Primary Section','B.Ed Early Childhood Education','2022-09-01',200000.00,'active','+2348029988773',NULL,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(7,2,14,'HRZ-EMP-003','Lead Bursar & Accounts Officer','Finance','HND Accounting','2020-01-10',250000.00,'active','+2348029988774',NULL,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(8,1,18,'EMP/2026/005','Robotics Instructor','Technology','B.Eng Robotics','2026-10-13',175000.00,'active',NULL,NULL,'2026-10-13 14:06:50','2026-10-13 14:06:50'),(9,1,20,'EMP/2026/006','Robotics Instructor','Technology','B.Eng Robotics','2026-10-13',175000.00,'active',NULL,NULL,'2026-10-13 14:08:37','2026-10-13 14:08:37');
/*!40000 ALTER TABLE `staff` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staff_attendance`
--

DROP TABLE IF EXISTS `staff_attendance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff_attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `attendance_date` date NOT NULL,
  `status` enum('present','absent','late','excused') DEFAULT 'present',
  `check_in_time` time DEFAULT NULL,
  `check_out_time` time DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_staff_att_entry` (`school_id`,`staff_id`,`attendance_date`),
  KEY `staff_id` (`staff_id`),
  CONSTRAINT `staff_attendance_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `staff_attendance_ibfk_2` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staff_attendance`
--

LOCK TABLES `staff_attendance` WRITE;
/*!40000 ALTER TABLE `staff_attendance` DISABLE KEYS */;
/*!40000 ALTER TABLE `staff_attendance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_term_summaries`
--

DROP TABLE IF EXISTS `student_term_summaries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_term_summaries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `term_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `class_arm_id` int(11) NOT NULL,
  `total_score` decimal(8,2) DEFAULT 0.00,
  `average_score` decimal(5,2) DEFAULT 0.00,
  `position` int(11) DEFAULT NULL,
  `total_students` int(11) DEFAULT NULL,
  `teacher_remark` text DEFAULT NULL,
  `principal_remark` text DEFAULT NULL,
  `promotion_status` enum('promoted','promoted_on_trial','not_promoted','pending') DEFAULT 'pending',
  `is_published` tinyint(1) DEFAULT 0,
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_term_summary` (`school_id`,`session_id`,`term_id`,`student_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `student_term_summaries_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_term_summaries_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_term_summaries`
--

LOCK TABLES `student_term_summaries` WRITE;
/*!40000 ALTER TABLE `student_term_summaries` DISABLE KEYS */;
INSERT INTO `student_term_summaries` VALUES (1,1,1,1,1,1,433.00,86.60,1,6,'Chinedu has maintained outstanding intellectual curiosity and exemplary discipline across all subjects.','A brilliant academic outing! Keep leading by positive example.','pending',1,'2026-10-13 13:28:03','2026-10-13 13:28:02','2026-10-13 14:08:37'),(2,1,1,1,2,1,432.00,86.40,2,6,NULL,NULL,'pending',0,NULL,'2026-10-13 13:28:02','2026-10-13 14:08:37'),(3,1,1,1,3,1,360.00,72.00,3,6,NULL,NULL,'pending',0,NULL,'2026-10-13 13:28:03','2026-10-13 14:08:37'),(4,1,1,1,4,1,332.00,66.40,4,6,NULL,NULL,'pending',0,NULL,'2026-10-13 13:28:03','2026-10-13 14:08:37'),(5,1,1,1,5,1,281.00,56.20,5,6,NULL,NULL,'pending',0,NULL,'2026-10-13 13:28:03','2026-10-13 14:08:37'),(6,2,2,4,6,4,445.00,89.00,1,3,NULL,NULL,'pending',0,NULL,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(7,2,2,4,7,4,405.00,81.00,2,3,NULL,NULL,'pending',0,NULL,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(8,2,2,4,8,4,355.00,71.00,3,3,NULL,NULL,'pending',0,NULL,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(9,1,1,1,9,1,0.00,0.00,6,6,NULL,NULL,'pending',0,NULL,'2026-10-13 14:06:58','2026-10-13 14:08:37');
/*!40000 ALTER TABLE `student_term_summaries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `students`
--

DROP TABLE IF EXISTS `students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `class_arm_id` int(11) DEFAULT NULL,
  `admission_number` varchar(50) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female') NOT NULL,
  `blood_group` varchar(10) DEFAULT NULL,
  `genotype` varchar(10) DEFAULT NULL,
  `medical_notes` text DEFAULT NULL,
  `status` enum('admitted','enrolled','graduated','withdrawn','suspended') DEFAULT 'enrolled',
  `enrolled_at` date DEFAULT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_student_adm_num` (`school_id`,`admission_number`),
  UNIQUE KEY `user_id` (`user_id`),
  KEY `parent_id` (`parent_id`),
  KEY `class_arm_id` (`class_arm_id`),
  KEY `idx_student_class` (`school_id`,`class_arm_id`),
  CONSTRAINT `students_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `students_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `students_ibfk_3` FOREIGN KEY (`parent_id`) REFERENCES `parents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `students_ibfk_4` FOREIGN KEY (`class_arm_id`) REFERENCES `class_arms` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `students`
--

LOCK TABLES `students` WRITE;
/*!40000 ALTER TABLE `students` DISABLE KEYS */;
INSERT INTO `students` VALUES (1,1,10,1,1,'APX/2025/001','Chinedu','Okafor','Chukwuemeka','2012-05-14','male','O+','AA',NULL,'enrolled','2025-09-08',NULL,'2026-10-13 13:28:02','2026-10-13 13:28:02'),(2,1,NULL,1,1,'APX/2025/002','Aisha','Bello','Khadijah','2012-07-20','female',NULL,NULL,NULL,'enrolled','2025-09-08',NULL,'2026-10-13 13:28:02','2026-10-13 13:28:02'),(3,1,NULL,1,1,'APX/2025/003','Babatunde','Adeyemi','Oluwaseun','2012-07-20','male',NULL,NULL,NULL,'enrolled','2025-09-08',NULL,'2026-10-13 13:28:02','2026-10-13 13:28:02'),(4,1,NULL,1,1,'APX/2025/004','Kemi','Adeleke','Grace','2012-07-20','female',NULL,NULL,NULL,'enrolled','2025-09-08',NULL,'2026-10-13 13:28:02','2026-10-13 13:28:02'),(5,1,NULL,1,1,'APX/2025/005','David','Eze','Somto','2012-07-20','male',NULL,NULL,NULL,'enrolled','2025-09-08',NULL,'2026-10-13 13:28:02','2026-10-13 13:28:02'),(6,2,16,2,4,'HRZ/2025/001','Zainab','Garba','Amina','2016-04-12','female','AA','O+',NULL,'enrolled','2025-09-15',NULL,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(7,2,NULL,2,4,'HRZ/2025/002','Tariq','Danjuma','Aliyu','2016-06-25','male','AA','A+',NULL,'enrolled','2025-09-15',NULL,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(8,2,NULL,2,4,'HRZ/2025/003','Maryam','Bello','Khadijah','2016-08-10','female','AS','B+',NULL,'enrolled','2025-09-15',NULL,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(9,1,NULL,3,1,'SCH/2026/006','Kareem','Ogunlesi_287','Babajide','2016-08-10','male','O+','AA',NULL,'enrolled','2026-10-13',NULL,'2026-10-13 14:06:50','2026-10-13 14:06:50'),(10,1,NULL,4,1,'SCH/2026/007','Kareem','Ogunlesi_291','Babajide','2016-08-10','male','O+','AA',NULL,'enrolled','2026-10-13',NULL,'2026-10-13 14:08:37','2026-10-13 14:08:37');
/*!40000 ALTER TABLE `students` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subjects`
--

DROP TABLE IF EXISTS `subjects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subjects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `code` varchar(30) NOT NULL,
  `name` varchar(120) NOT NULL,
  `category` enum('core','elective','vocational') DEFAULT 'core',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_subject_code` (`school_id`,`code`),
  CONSTRAINT `subjects_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subjects`
--

LOCK TABLES `subjects` WRITE;
/*!40000 ALTER TABLE `subjects` DISABLE KEYS */;
INSERT INTO `subjects` VALUES (1,1,'MTH','Mathematics','core',1,'2026-10-13 13:28:02','2026-10-13 13:28:02'),(2,1,'ENG','English Language','core',1,'2026-10-13 13:28:02','2026-10-13 13:28:02'),(3,1,'BSC','Basic Science','core',1,'2026-10-13 13:28:02','2026-10-13 13:28:02'),(4,1,'CIV','Civic Education','core',1,'2026-10-13 13:28:02','2026-10-13 13:28:02'),(5,1,'AGR','Agricultural Science','vocational',1,'2026-10-13 13:28:02','2026-10-13 13:28:02'),(6,1,'PHY','Physics','core',1,'2026-10-13 13:28:02','2026-10-13 13:28:02'),(7,2,'MTH','Mathematics','core',1,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(8,2,'ENG','English Language','core',1,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(9,2,'BSC','Basic Science','core',1,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(10,2,'SST','Social Studies','core',1,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(11,2,'FRN','French Language','elective',1,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(12,1,'ROB_28','Robotics & AI','',1,'2026-10-13 14:06:50','2026-10-13 14:06:50'),(13,1,'ROB_90','Robotics & AI','',1,'2026-10-13 14:08:37','2026-10-13 14:08:37');
/*!40000 ALTER TABLE `subjects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subscription_plans`
--

DROP TABLE IF EXISTS `subscription_plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subscription_plans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `billing_cycle` enum('monthly','annual') DEFAULT 'annual',
  `price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `student_limit` int(11) DEFAULT 200,
  `staff_limit` int(11) DEFAULT 25,
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features`)),
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subscription_plans`
--

LOCK TABLES `subscription_plans` WRITE;
/*!40000 ALTER TABLE `subscription_plans` DISABLE KEYS */;
INSERT INTO `subscription_plans` VALUES (1,'standard','Standard School Plan','Comprehensive management for growing primary & secondary schools.','annual',250000.00,300,30,'{\"academics\":true,\"attendance\":true,\"exams\":true,\"fees\":true,\"website\":true}',1,'2026-10-13 13:28:02','2026-10-13 13:28:02'),(2,'premium','Premium Enterprise Plan','Advanced enterprise features including library, clinic, payroll & online admissions.','annual',600000.00,1000,80,'{\"academics\":true,\"attendance\":true,\"exams\":true,\"fees\":true,\"website\":true,\"library\":true,\"clinic\":true,\"payroll\":true,\"custom_domain\":true}',1,'2026-10-13 13:28:02','2026-10-13 13:28:02'),(3,'group','Apex Multi-Campus Plan','Unlimited students and campuses with dedicated priority server infrastructure.','annual',1200000.00,3000,200,'{\"all_modules\":true,\"unlimited\":true}',1,'2026-10-13 13:28:02','2026-10-13 13:28:02'),(4,'plan_test_493','Elite Campus Plan','Automated test plan creation','annual',850000.00,1500,100,NULL,1,'2026-10-13 14:06:49','2026-10-13 14:06:49'),(5,'plan_test_717','Elite Campus Plan','Automated test plan creation','annual',850000.00,1500,100,NULL,1,'2026-10-13 14:08:37','2026-10-13 14:08:37');
/*!40000 ALTER TABLE `subscription_plans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subscriptions`
--

DROP TABLE IF EXISTS `subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `billing_cycle` enum('monthly','annual') DEFAULT 'annual',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `payment_status` enum('paid','pending','failed','refunded') DEFAULT 'paid',
  `payment_reference` varchar(100) DEFAULT NULL,
  `payment_gateway` enum('paystack','manual_bank_transfer') DEFAULT 'paystack',
  `notes` text DEFAULT NULL,
  `verified_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  KEY `school_id` (`school_id`),
  KEY `plan_id` (`plan_id`),
  CONSTRAINT `subscriptions_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `subscriptions_ibfk_2` FOREIGN KEY (`plan_id`) REFERENCES `subscription_plans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subscriptions`
--

LOCK TABLES `subscriptions` WRITE;
/*!40000 ALTER TABLE `subscriptions` DISABLE KEYS */;
INSERT INTO `subscriptions` VALUES (1,1,2,'SUB-INV-2025-001',600000.00,'annual','2025-10-01','2026-10-01','paid','PSTK_SUB_776152','paystack',NULL,1,'2026-10-13 13:28:03','2026-10-13 13:28:03');
/*!40000 ALTER TABLE `subscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tenant_domains`
--

DROP TABLE IF EXISTS `tenant_domains`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tenant_domains` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `domain` varchar(200) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 1,
  `is_custom` tinyint(1) DEFAULT 0,
  `is_verified` tinyint(1) DEFAULT 1,
  `verified_at` timestamp NULL DEFAULT NULL,
  `status` enum('active','pending','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `domain` (`domain`),
  KEY `school_id` (`school_id`),
  KEY `idx_tenant_domain` (`domain`),
  CONSTRAINT `tenant_domains_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tenant_domains`
--

LOCK TABLES `tenant_domains` WRITE;
/*!40000 ALTER TABLE `tenant_domains` DISABLE KEYS */;
INSERT INTO `tenant_domains` VALUES (1,1,'apex.edusphere.example',1,0,1,NULL,'active','2026-10-13 13:28:02','2026-10-13 13:28:02'),(2,1,'apexschools.edu.ng',0,1,1,NULL,'active','2026-10-13 13:28:02','2026-10-13 13:28:02'),(3,2,'horizon.edusphere.example',1,0,1,NULL,'active','2026-10-13 13:28:02','2026-10-13 13:28:02'),(4,1,'portal.apexcollege-130.org.ng',0,1,1,NULL,'active','2026-10-13 14:06:50','2026-10-13 14:06:50'),(5,1,'portal.apexcollege-474.org.ng',0,1,1,NULL,'active','2026-10-13 14:08:37','2026-10-13 14:08:37');
/*!40000 ALTER TABLE `tenant_domains` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `timetables`
--

DROP TABLE IF EXISTS `timetables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `timetables` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `class_arm_id` int(11) NOT NULL,
  `day_of_week` enum('monday','tuesday','wednesday','thursday','friday') NOT NULL,
  `period_number` int(11) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `subject_id` int(11) NOT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_timetable_slot` (`school_id`,`class_arm_id`,`day_of_week`,`period_number`),
  KEY `class_arm_id` (`class_arm_id`),
  KEY `subject_id` (`subject_id`),
  CONSTRAINT `timetables_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `timetables_ibfk_2` FOREIGN KEY (`class_arm_id`) REFERENCES `class_arms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `timetables_ibfk_3` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `timetables`
--

LOCK TABLES `timetables` WRITE;
/*!40000 ALTER TABLE `timetables` DISABLE KEYS */;
/*!40000 ALTER TABLE `timetables` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('super_admin','school_admin','principal','registrar','teacher','bursar','student','parent','sales_rep','sales_manager') NOT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `two_factor_enabled` tinyint(1) DEFAULT 0,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_email_school` (`email`,`school_id`),
  KEY `school_id` (`school_id`),
  KEY `idx_user_role` (`role`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,NULL,'Dr. Marcus Adebayo','superadmin@edusphere.example','+2348031234001','$2y$10$4uNDYoFGdKx5Tw2lSprJ2uzhAcUsWMsYtzDU4TnuQsY/fwQqB0G5K','super_admin',NULL,1,0,'2026-10-14 08:10:31','2026-10-13 13:28:02','2026-10-14 08:10:31'),(2,NULL,'Victoria Eke','salesmanager@edusphere.example','+2348031234002','$2y$10$4uNDYoFGdKx5Tw2lSprJ2uzhAcUsWMsYtzDU4TnuQsY/fwQqB0G5K','sales_manager',NULL,1,0,NULL,'2026-10-13 13:28:02','2026-10-13 13:28:02'),(3,NULL,'Tunde Bakare','salesrep@edusphere.example','+2348031234003','$2y$10$4uNDYoFGdKx5Tw2lSprJ2uzhAcUsWMsYtzDU4TnuQsY/fwQqB0G5K','sales_rep',NULL,1,0,'2026-10-14 07:35:50','2026-10-13 13:28:02','2026-10-14 07:35:50'),(4,1,'Olawale Sanusi','admin@apexcollege.edu.ng','+2348033221101','$2y$10$4uNDYoFGdKx5Tw2lSprJ2uzhAcUsWMsYtzDU4TnuQsY/fwQqB0G5K','school_admin',NULL,1,0,'2026-10-14 08:31:30','2026-10-13 13:28:02','2026-10-14 08:31:30'),(5,1,'Mrs. Folake Folorunsho','principal@apexcollege.edu.ng','+2348033221102','$2y$10$4uNDYoFGdKx5Tw2lSprJ2uzhAcUsWMsYtzDU4TnuQsY/fwQqB0G5K','principal',NULL,1,0,NULL,'2026-10-13 13:28:02','2026-10-13 13:28:02'),(6,1,'Mr. Godwin Okon','registrar@apexcollege.edu.ng','+2348033221103','$2y$10$4uNDYoFGdKx5Tw2lSprJ2uzhAcUsWMsYtzDU4TnuQsY/fwQqB0G5K','registrar',NULL,1,0,NULL,'2026-10-13 13:28:02','2026-10-13 13:28:02'),(7,1,'Mr. Chidi Nwosu','teacher@apexcollege.edu.ng','+2348033221104','$2y$10$4uNDYoFGdKx5Tw2lSprJ2uzhAcUsWMsYtzDU4TnuQsY/fwQqB0G5K','teacher',NULL,1,0,NULL,'2026-10-13 13:28:02','2026-10-13 13:28:02'),(8,1,'Mrs. Zainab Aliyu','bursar@apexcollege.edu.ng','+2348033221105','$2y$10$4uNDYoFGdKx5Tw2lSprJ2uzhAcUsWMsYtzDU4TnuQsY/fwQqB0G5K','bursar',NULL,1,0,NULL,'2026-10-13 13:28:02','2026-10-13 13:28:02'),(9,1,'Chief Emeka Okafor','parent@apexcollege.edu.ng','+2348039900112','$2y$10$4uNDYoFGdKx5Tw2lSprJ2uzhAcUsWMsYtzDU4TnuQsY/fwQqB0G5K','parent',NULL,1,0,'2026-10-14 08:32:53','2026-10-13 13:28:02','2026-10-14 08:32:53'),(10,1,'Chinedu Okafor','student@apexcollege.edu.ng','+2348039900113','$2y$10$4uNDYoFGdKx5Tw2lSprJ2uzhAcUsWMsYtzDU4TnuQsY/fwQqB0G5K','student',NULL,1,0,'2026-10-14 08:32:53','2026-10-13 13:28:02','2026-10-14 08:32:53'),(11,2,'Dr. Fatima Garba','admin@horizonacademy.sch.ng','+2348029988771','$2y$10$4uNDYoFGdKx5Tw2lSprJ2uzhAcUsWMsYtzDU4TnuQsY/fwQqB0G5K','school_admin',NULL,1,0,'2026-10-13 14:08:35','2026-10-13 13:28:03','2026-10-13 14:08:35'),(12,2,'Alhaji Ibrahim Danjuma','principal@horizonacademy.sch.ng','+2348029988772','$2y$10$4uNDYoFGdKx5Tw2lSprJ2uzhAcUsWMsYtzDU4TnuQsY/fwQqB0G5K','principal',NULL,1,0,NULL,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(13,2,'Miss Blessing Adeyemi','teacher@horizonacademy.sch.ng','+2348029988773','$2y$10$4uNDYoFGdKx5Tw2lSprJ2uzhAcUsWMsYtzDU4TnuQsY/fwQqB0G5K','teacher',NULL,1,0,NULL,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(14,2,'Mr. Usman Bello','bursar@horizonacademy.sch.ng','+2348029988774','$2y$10$4uNDYoFGdKx5Tw2lSprJ2uzhAcUsWMsYtzDU4TnuQsY/fwQqB0G5K','bursar',NULL,1,0,NULL,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(15,2,'Alhaji Musa Garba','parent@horizonacademy.sch.ng','+2348029988780','$2y$10$4uNDYoFGdKx5Tw2lSprJ2uzhAcUsWMsYtzDU4TnuQsY/fwQqB0G5K','parent',NULL,1,0,NULL,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(16,2,'Zainab Garba','student@horizonacademy.sch.ng','+2348029988781','$2y$10$4uNDYoFGdKx5Tw2lSprJ2uzhAcUsWMsYtzDU4TnuQsY/fwQqB0G5K','student',NULL,1,0,NULL,'2026-10-13 13:28:03','2026-10-13 13:28:03'),(17,1,'Alhaji Ogunlesi','ogunlesi_670@example.ng','08022233344','$2y$10$FikCGly2ZKcBM3wOMohtJ.jCYxuW1zWKYH0gvK2gysYTYOsK2nTVS','parent',NULL,1,0,NULL,'2026-10-13 14:06:50','2026-10-13 14:06:50'),(18,1,'Engr. Daniel Alabi','staff_125@apexcollege.edu.ng','08099887766','$2y$10$wNSQz91oqVosNtlbbhXhQelIg4R6OuyhrMk/F4EAVvc.LDtA5NjM6','teacher',NULL,1,0,NULL,'2026-10-13 14:06:50','2026-10-13 14:06:50'),(19,1,'Alhaji Ogunlesi','ogunlesi_930@example.ng','08022233344','$2y$10$N9h31nqL4YGfgpHJgKHF8.bDf91bvvaQzTRE3XAp5auhXFzIaFfwW','parent',NULL,1,0,NULL,'2026-10-13 14:08:37','2026-10-13 14:08:37'),(20,1,'Engr. Daniel Alabi','staff_601@apexcollege.edu.ng','08099887766','$2y$10$kpRp1hqHvSympr0We9LuD..gKskc3OJc1xnyHu1DStdf3wWfhRRmy','teacher',NULL,1,0,NULL,'2026-10-13 14:08:37','2026-10-13 14:08:37'),(21,NULL,'Chinedu Eze','partner_test_1791964410@example.com','08036491320','$2y$10$iIzQy69GMoO8iym6NQt6HesOvRKorucZ78FH77eF5VwJGZBNACXSe','sales_rep',NULL,1,0,'2026-10-14 07:53:30','2026-10-14 07:53:30','2026-10-14 07:53:30');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'edusphere_saas'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-14 11:21:28
