-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: sitama
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
-- Table structure for table `applications`
--

DROP TABLE IF EXISTS `applications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `applications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) unsigned NOT NULL,
  `job_listing_id` bigint(20) unsigned NOT NULL,
  `status` enum('pending','accepted','rejected') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `applications_student_id_job_listing_id_unique` (`student_id`,`job_listing_id`),
  KEY `applications_job_listing_id_foreign` (`job_listing_id`),
  CONSTRAINT `applications_job_listing_id_foreign` FOREIGN KEY (`job_listing_id`) REFERENCES `job_listings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `applications_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `applications`
--

LOCK TABLES `applications` WRITE;
/*!40000 ALTER TABLE `applications` DISABLE KEYS */;
/*!40000 ALTER TABLE `applications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `assessment_components`
--

DROP TABLE IF EXISTS `assessment_components`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `assessment_components` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assessment_components`
--

LOCK TABLES `assessment_components` WRITE;
/*!40000 ALTER TABLE `assessment_components` DISABLE KEYS */;
INSERT INTO `assessment_components` VALUES (1,'Kedisiplinan','2026-04-24 07:46:35','2026-04-24 07:46:35'),(2,'Kemampuan Teknis','2026-04-24 07:46:36','2026-04-24 07:46:36'),(3,'Kerjasama','2026-04-24 07:46:36','2026-04-24 07:46:36'),(4,'Inisiatif','2026-04-24 07:46:36','2026-04-24 07:46:36');
/*!40000 ALTER TABLE `assessment_components` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('sitama-cache-5c785c036466adea360111aa28563bfd556b5fba','i:2;',1778650952),('sitama-cache-5c785c036466adea360111aa28563bfd556b5fba:timer','i:1778650952;',1778650952);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `companies`
--

DROP TABLE IF EXISTS `companies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `companies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `field` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `verification_status` enum('pending','verified','rejected') NOT NULL DEFAULT 'pending',
  `rejection_reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `companies_user_id_foreign` (`user_id`),
  CONSTRAINT `companies_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `companies`
--

LOCK TABLES `companies` WRITE;
/*!40000 ALTER TABLE `companies` DISABLE KEYS */;
INSERT INTO `companies` VALUES (1,5,'PT. Telkom Indonesia','Jl. Japati No.1, Bandung, Jawa Barat','Telekomunikasi & Teknologi Informasi','022-4521111','info@telkom.co.id','verified',NULL,'2026-04-24 07:46:37','2026-05-12 21:46:08'),(2,NULL,'PT. Gojek Indonesia','Pasaraya Blok M, Jakarta Selatan','Teknologi & Ride-Hailing','021-5010000','info@gojek.com','pending',NULL,'2026-04-24 07:46:37','2026-04-24 07:46:37');
/*!40000 ALTER TABLE `companies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detailed_assessment_components`
--

DROP TABLE IF EXISTS `detailed_assessment_components`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `detailed_assessment_components` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `assessment_component_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `detailed_assessment_components_assessment_component_id_foreign` (`assessment_component_id`),
  CONSTRAINT `detailed_assessment_components_assessment_component_id_foreign` FOREIGN KEY (`assessment_component_id`) REFERENCES `assessment_components` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detailed_assessment_components`
--

LOCK TABLES `detailed_assessment_components` WRITE;
/*!40000 ALTER TABLE `detailed_assessment_components` DISABLE KEYS */;
INSERT INTO `detailed_assessment_components` VALUES (1,1,'Kehadiran dan ketepatan waktu','2026-04-24 07:46:35','2026-04-24 07:46:35'),(2,1,'Kepatuhan terhadap peraturan perusahaan','2026-04-24 07:46:35','2026-04-24 07:46:35'),(3,1,'Tanggung jawab dalam menyelesaikan tugas','2026-04-24 07:46:35','2026-04-24 07:46:35'),(4,2,'Penguasaan bidang ilmu yang relevan','2026-04-24 07:46:36','2026-04-24 07:46:36'),(5,2,'Kemampuan menggunakan peralatan/teknologi','2026-04-24 07:46:36','2026-04-24 07:46:36'),(6,2,'Kualitas hasil kerja','2026-04-24 07:46:36','2026-04-24 07:46:36'),(7,3,'Kemampuan bekerja dalam tim','2026-04-24 07:46:36','2026-04-24 07:46:36'),(8,3,'Komunikasi dengan rekan kerja','2026-04-24 07:46:36','2026-04-24 07:46:36'),(9,3,'Kemampuan menerima arahan','2026-04-24 07:46:36','2026-04-24 07:46:36'),(10,4,'Kreativitas dalam menyelesaikan masalah','2026-04-24 07:46:36','2026-04-24 07:46:36'),(11,4,'Kemampuan bekerja mandiri','2026-04-24 07:46:36','2026-04-24 07:46:36'),(12,4,'Semangat belajar hal baru','2026-04-24 07:46:36','2026-04-24 07:46:36');
/*!40000 ALTER TABLE `detailed_assessment_components` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `guidances`
--

DROP TABLE IF EXISTS `guidances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `guidances` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `activity` text NOT NULL,
  `date` date NOT NULL,
  `name_file` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','in-progress','rejected') NOT NULL DEFAULT 'pending',
  `lecturer_note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `guidances_student_id_foreign` (`student_id`),
  CONSTRAINT `guidances_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `guidances`
--

LOCK TABLES `guidances` WRITE;
/*!40000 ALTER TABLE `guidances` DISABLE KEYS */;
INSERT INTO `guidances` VALUES (1,1,'Bimbingan Awal Magang','Konsultasi rencana kegiatan magang dan target yang akan dicapai selama periode magang berlangsung.','2024-07-05',NULL,'approved','Bagus, pastikan target terpenuhi sesuai jadwal.','2026-04-24 07:46:38','2026-04-24 07:46:38'),(2,1,'Progress Report Minggu 2','Melaporkan progres pengerjaan fitur login dan registrasi pada aplikasi yang sedang dikembangkan.','2024-07-15',NULL,'approved','Progress baik, lanjutkan ke fitur berikutnya.','2026-04-24 07:46:38','2026-04-24 07:46:38'),(3,1,'Diskusi Teknologi yang Digunakan','Membahas stack teknologi yang digunakan perusahaan dan relevansinya dengan materi perkuliahan.','2024-07-22',NULL,'in-progress',NULL,'2026-04-24 07:46:38','2026-04-24 07:46:38'),(4,1,'Review Laporan Akhir','Mengajukan draft laporan akhir magang untuk direview dan mendapatkan masukan dari dosen pembimbing.','2024-08-05',NULL,'pending',NULL,'2026-04-24 07:46:38','2026-04-24 07:46:38'),(5,2,'Bimbingan Awal Magang','Konsultasi rencana kegiatan magang dan target yang akan dicapai selama periode magang berlangsung.','2024-07-05',NULL,'approved','Bagus, pastikan target terpenuhi sesuai jadwal.','2026-04-24 07:46:38','2026-04-24 07:46:38'),(6,2,'Progress Report Minggu 2','Melaporkan progres pengerjaan fitur login dan registrasi pada aplikasi yang sedang dikembangkan.','2024-07-15',NULL,'approved','Progress baik, lanjutkan ke fitur berikutnya.','2026-04-24 07:46:38','2026-04-24 07:46:38'),(7,2,'Diskusi Teknologi yang Digunakan','Membahas stack teknologi yang digunakan perusahaan dan relevansinya dengan materi perkuliahan.','2024-07-22',NULL,'in-progress',NULL,'2026-04-24 07:46:38','2026-04-24 07:46:38'),(8,2,'Review Laporan Akhir','Mengajukan draft laporan akhir magang untuk direview dan mendapatkan masukan dari dosen pembimbing.','2024-08-05',NULL,'pending',NULL,'2026-04-24 07:46:38','2026-04-24 07:46:38'),(9,3,'Bimbingan Awal Magang','Konsultasi rencana kegiatan magang dan target yang akan dicapai selama periode magang berlangsung.','2024-07-05',NULL,'approved','Bagus, pastikan target terpenuhi sesuai jadwal.','2026-04-24 07:46:38','2026-04-24 07:46:38'),(10,3,'Progress Report Minggu 2','Melaporkan progres pengerjaan fitur login dan registrasi pada aplikasi yang sedang dikembangkan.','2024-07-15',NULL,'approved','Progress baik, lanjutkan ke fitur berikutnya.','2026-04-24 07:46:38','2026-04-24 07:46:38'),(11,3,'Diskusi Teknologi yang Digunakan','Membahas stack teknologi yang digunakan perusahaan dan relevansinya dengan materi perkuliahan.','2024-07-22',NULL,'in-progress',NULL,'2026-04-24 07:46:38','2026-04-24 07:46:38'),(12,3,'Review Laporan Akhir','Mengajukan draft laporan akhir magang untuk direview dan mendapatkan masukan dari dosen pembimbing.','2024-08-05',NULL,'pending',NULL,'2026-04-24 07:46:38','2026-04-24 07:46:38'),(13,4,'Bimbingan Awal Magang','Konsultasi rencana kegiatan magang dan target yang akan dicapai selama periode magang berlangsung.','2024-07-05',NULL,'approved','Bagus, pastikan target terpenuhi sesuai jadwal.','2026-04-24 07:46:39','2026-04-24 07:46:39'),(14,4,'Progress Report Minggu 2','Melaporkan progres pengerjaan fitur login dan registrasi pada aplikasi yang sedang dikembangkan.','2024-07-15',NULL,'approved','Progress baik, lanjutkan ke fitur berikutnya.','2026-04-24 07:46:39','2026-04-24 07:46:39'),(15,4,'Diskusi Teknologi yang Digunakan','Membahas stack teknologi yang digunakan perusahaan dan relevansinya dengan materi perkuliahan.','2024-07-22',NULL,'in-progress',NULL,'2026-04-24 07:46:39','2026-04-24 07:46:39'),(16,4,'Review Laporan Akhir','Mengajukan draft laporan akhir magang untuk direview dan mendapatkan masukan dari dosen pembimbing.','2024-08-05',NULL,'pending',NULL,'2026-04-24 07:46:39','2026-04-24 07:46:39');
/*!40000 ALTER TABLE `guidances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `internships`
--

DROP TABLE IF EXISTS `internships`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `internships` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) unsigned NOT NULL,
  `lecturer_id` bigint(20) unsigned DEFAULT NULL,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `lecturer_industry_id` bigint(20) unsigned DEFAULT NULL,
  `position` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `is_finished` tinyint(1) NOT NULL DEFAULT 0,
  `performance_notes` text DEFAULT NULL,
  `performance_notes_by` varchar(255) DEFAULT NULL,
  `performance_notes_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `internships_student_id_foreign` (`student_id`),
  KEY `internships_lecturer_id_foreign` (`lecturer_id`),
  KEY `internships_company_id_foreign` (`company_id`),
  KEY `internships_lecturer_industry_id_foreign` (`lecturer_industry_id`),
  CONSTRAINT `internships_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `internships_lecturer_id_foreign` FOREIGN KEY (`lecturer_id`) REFERENCES `lecturers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `internships_lecturer_industry_id_foreign` FOREIGN KEY (`lecturer_industry_id`) REFERENCES `lecturers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `internships_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `internships`
--

LOCK TABLES `internships` WRITE;
/*!40000 ALTER TABLE `internships` DISABLE KEYS */;
INSERT INTO `internships` VALUES (1,1,1,1,3,'Frontend Developer','2024-07-01','2024-09-30',1,NULL,NULL,NULL,'2026-04-24 07:46:38','2026-04-24 07:46:38'),(2,2,1,1,3,'Backend Engineer','2024-07-01','2024-09-30',1,NULL,NULL,NULL,'2026-04-24 07:46:38','2026-04-24 07:46:38'),(3,3,2,1,3,'UI/UX Designer','2024-07-01',NULL,0,NULL,NULL,NULL,'2026-04-24 07:46:38','2026-04-24 07:46:38'),(4,4,2,2,3,'Data Analyst','2024-07-01',NULL,0,NULL,NULL,NULL,'2026-04-24 07:46:39','2026-04-24 07:46:39');
/*!40000 ALTER TABLE `internships` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_listings`
--

DROP TABLE IF EXISTS `job_listings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_listings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `division` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `skills` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`skills`)),
  `location` varchar(255) DEFAULT NULL,
  `job_type` varchar(255) NOT NULL DEFAULT 'On-site',
  `quota` int(11) NOT NULL DEFAULT 1,
  `duration_months` int(11) NOT NULL DEFAULT 3,
  `pic_email` varchar(255) DEFAULT NULL,
  `status` enum('active','closed') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `job_listings_company_id_foreign` (`company_id`),
  CONSTRAINT `job_listings_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_listings`
--

LOCK TABLES `job_listings` WRITE;
/*!40000 ALTER TABLE `job_listings` DISABLE KEYS */;
INSERT INTO `job_listings` VALUES (1,1,'Frontend Developer','IT','Membangun UI menggunakan React dan Flutter.','[\"React\",\"Flutter\",\"JavaScript\"]','Bandung','On-site',3,3,NULL,'active','2026-05-12 21:46:08','2026-05-12 21:46:08'),(2,1,'Backend Developer','Engineering','Membangun REST API menggunakan Laravel.','[\"Laravel\",\"MySQL\",\"Docker\"]','Bandung','Hybrid',2,6,NULL,'active','2026-05-12 21:46:08','2026-05-12 21:46:08'),(3,1,'Data Analyst','Data','Menganalisis data bisnis menggunakan Python dan SQL.','[\"Python\",\"SQL\",\"Tableau\"]','Jakarta','On-site',2,3,NULL,'active','2026-05-12 21:46:08','2026-05-12 21:46:08');
/*!40000 ALTER TABLE `job_listings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lecturers`
--

DROP TABLE IF EXISTS `lecturers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lecturers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lecturers_user_id_foreign` (`user_id`),
  CONSTRAINT `lecturers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lecturers`
--

LOCK TABLES `lecturers` WRITE;
/*!40000 ALTER TABLE `lecturers` DISABLE KEYS */;
INSERT INTO `lecturers` VALUES (1,2,'2026-04-24 07:46:36','2026-04-24 07:46:36'),(2,3,'2026-04-24 07:46:37','2026-04-24 07:46:37'),(3,4,'2026-04-24 07:46:37','2026-04-24 07:46:37');
/*!40000 ALTER TABLE `lecturers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_books`
--

DROP TABLE IF EXISTS `log_books`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_books` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `activity` text NOT NULL,
  `date` date NOT NULL,
  `category` varchar(255) DEFAULT NULL,
  `lecturer_note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `log_books_student_id_foreign` (`student_id`),
  CONSTRAINT `log_books_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_books`
--

LOCK TABLES `log_books` WRITE;
/*!40000 ALTER TABLE `log_books` DISABLE KEYS */;
INSERT INTO `log_books` VALUES (1,1,'Orientasi dan Perkenalan','Mengikuti sesi orientasi karyawan baru, perkenalan dengan tim, dan penjelasan SOP perusahaan.','2024-07-01',NULL,NULL,'2026-04-24 07:46:38','2026-04-24 07:46:38'),(2,1,'Setup Lingkungan Kerja','Instalasi tools pengembangan (VS Code, Git, Node.js) dan akses ke repositori project yang akan dikerjakan.','2024-07-02',NULL,NULL,'2026-04-24 07:46:38','2026-04-24 07:46:38'),(3,1,'Mempelajari Codebase','Membaca dan memahami struktur kode yang sudah ada, standar coding, dan alur data pada aplikasi.','2024-07-03',NULL,NULL,'2026-04-24 07:46:38','2026-04-24 07:46:38'),(4,1,'Pengerjaan Fitur Login','Mulai mengimplementasikan fitur autentikasi menggunakan JWT token sesuai spesifikasi yang diberikan mentor.','2024-07-08',NULL,NULL,'2026-04-24 07:46:38','2026-04-24 07:46:38'),(5,1,'Testing dan Bug Fixing','Melakukan unit testing pada fitur yang telah selesai dibuat dan memperbaiki bug yang temukan.','2024-07-10',NULL,NULL,'2026-04-24 07:46:38','2026-05-12 22:04:27'),(6,2,'Orientasi dan Perkenalan','Mengikuti sesi orientasi karyawan baru, perkenalan dengan tim, dan penjelasan SOP perusahaan.','2024-07-01',NULL,NULL,'2026-04-24 07:46:38','2026-04-24 07:46:38'),(7,2,'Setup Lingkungan Kerja','Instalasi tools pengembangan (VS Code, Git, Node.js) dan akses ke repositori project yang akan dikerjakan.','2024-07-02',NULL,NULL,'2026-04-24 07:46:38','2026-04-24 07:46:38'),(8,2,'Mempelajari Codebase','Membaca dan memahami struktur kode yang sudah ada, standar coding, dan alur data pada aplikasi.','2024-07-03',NULL,NULL,'2026-04-24 07:46:38','2026-04-24 07:46:38'),(9,2,'Pengerjaan Fitur Login','Mulai mengimplementasikan fitur autentikasi menggunakan JWT token sesuai spesifikasi yang diberikan mentor.','2024-07-08',NULL,NULL,'2026-04-24 07:46:38','2026-04-24 07:46:38'),(10,2,'Testing dan Bug Fixing','Melakukan unit testing pada fitur yang telah selesai dibuat dan memperbaiki bug yang ditemukan.','2024-07-10',NULL,NULL,'2026-04-24 07:46:38','2026-04-24 07:46:38'),(11,3,'Orientasi dan Perkenalan','Mengikuti sesi orientasi karyawan baru, perkenalan dengan tim, dan penjelasan SOP perusahaan.','2024-07-01',NULL,NULL,'2026-04-24 07:46:38','2026-04-24 07:46:38'),(12,3,'Setup Lingkungan Kerja','Instalasi tools pengembangan (VS Code, Git, Node.js) dan akses ke repositori project yang akan dikerjakan.','2024-07-02',NULL,NULL,'2026-04-24 07:46:38','2026-04-24 07:46:38'),(13,3,'Mempelajari Codebase','Membaca dan memahami struktur kode yang sudah ada, standar coding, dan alur data pada aplikasi.','2024-07-03',NULL,NULL,'2026-04-24 07:46:38','2026-04-24 07:46:38'),(14,3,'Pengerjaan Fitur Login','Mulai mengimplementasikan fitur autentikasi menggunakan JWT token sesuai spesifikasi yang diberikan mentor.','2024-07-08',NULL,NULL,'2026-04-24 07:46:38','2026-04-24 07:46:38'),(15,3,'Testing dan Bug Fixing','Melakukan unit testing pada fitur yang telah selesai dibuat dan memperbaiki bug yang ditemukan.','2024-07-10',NULL,NULL,'2026-04-24 07:46:38','2026-04-24 07:46:38'),(16,4,'Orientasi dan Perkenalan','Mengikuti sesi orientasi karyawan baru, perkenalan dengan tim, dan penjelasan SOP perusahaan.','2024-07-01',NULL,NULL,'2026-04-24 07:46:39','2026-04-24 07:46:39'),(17,4,'Setup Lingkungan Kerja','Instalasi tools pengembangan (VS Code, Git, Node.js) dan akses ke repositori project yang akan dikerjakan.','2024-07-02',NULL,NULL,'2026-04-24 07:46:39','2026-04-24 07:46:39'),(18,4,'Mempelajari Codebase','Membaca dan memahami struktur kode yang sudah ada, standar coding, dan alur data pada aplikasi.','2024-07-03',NULL,NULL,'2026-04-24 07:46:39','2026-04-24 07:46:39'),(19,4,'Pengerjaan Fitur Login','Mulai mengimplementasikan fitur autentikasi menggunakan JWT token sesuai spesifikasi yang diberikan mentor.','2024-07-08',NULL,NULL,'2026-04-24 07:46:39','2026-04-24 07:46:39'),(20,4,'Testing dan Bug Fixing','Melakukan unit testing pada fitur yang telah selesai dibuat dan memperbaiki bug yang ditemukan.','2024-07-10',NULL,NULL,'2026-04-24 07:46:39','2026-04-24 07:46:39');
/*!40000 ALTER TABLE `log_books` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_04_24_000001_create_students_table',1),(5,'2026_04_24_000002_create_lecturers_table',1),(6,'2026_04_24_000003_create_companies_table',1),(7,'2026_04_24_000004_create_internships_table',1),(8,'2026_04_24_000005_create_guidances_table',1),(9,'2026_04_24_000006_create_log_books_table',1),(10,'2026_04_24_000007_create_assessment_components_table',1),(11,'2026_04_24_000008_create_student_scores_table',1),(12,'2026_04_24_000009_create_notifications_table',1),(13,'2026_04_24_142427_create_personal_access_tokens_table',1),(14,'2026_04_29_000001_add_fields_for_lecturer_industry',2),(15,'2026_04_29_000002_create_industry_tables',2),(16,'2026_04_29_000003_create_seminars_table',2),(17,'2026_06_02_000001_make_students_fields_nullable',3),(18,'2026_06_02_000002_make_internships_fields_nullable',3);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `message` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `category` enum('guidance','log_book','general','revisi') NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `detail_text` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_user_id_foreign` (`user_id`),
  CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (1,6,'Bimbingan Anda telah disetujui oleh dosen pembimbing.','2024-07-15','guidance',1,NULL,'2026-04-24 07:46:38','2026-04-24 07:46:38'),(2,6,'Dosen pembimbing telah menambahkan catatan pada logbook Anda.','2024-07-16','log_book',1,NULL,'2026-04-24 07:46:38','2026-05-12 21:41:25'),(3,6,'Pengingat: Segera unggah laporan akhir sebelum batas waktu.','2024-08-01','general',1,NULL,'2026-04-24 07:46:38','2026-05-12 21:41:25'),(4,7,'Bimbingan Anda telah disetujui oleh dosen pembimbing.','2024-07-15','guidance',1,NULL,'2026-04-24 07:46:38','2026-04-24 07:46:38'),(5,7,'Dosen pembimbing telah menambahkan catatan pada logbook Anda.','2024-07-16','log_book',0,NULL,'2026-04-24 07:46:38','2026-04-24 07:46:38'),(6,7,'Pengingat: Segera unggah laporan akhir sebelum batas waktu.','2024-08-01','general',0,NULL,'2026-04-24 07:46:38','2026-04-24 07:46:38'),(7,8,'Bimbingan Anda telah disetujui oleh dosen pembimbing.','2024-07-15','guidance',1,NULL,'2026-04-24 07:46:38','2026-04-24 07:46:38'),(8,8,'Dosen pembimbing telah menambahkan catatan pada logbook Anda.','2024-07-16','log_book',0,NULL,'2026-04-24 07:46:38','2026-04-24 07:46:38'),(9,8,'Pengingat: Segera unggah laporan akhir sebelum batas waktu.','2024-08-01','general',0,NULL,'2026-04-24 07:46:38','2026-04-24 07:46:38'),(10,9,'Bimbingan Anda telah disetujui oleh dosen pembimbing.','2024-07-15','guidance',1,NULL,'2026-04-24 07:46:39','2026-04-24 07:46:39'),(11,9,'Dosen pembimbing telah menambahkan catatan pada logbook Anda.','2024-07-16','log_book',0,NULL,'2026-04-24 07:46:39','2026-04-24 07:46:39'),(12,9,'Pengingat: Segera unggah laporan akhir sebelum batas waktu.','2024-08-01','general',0,NULL,'2026-04-24 07:46:39','2026-04-24 07:46:39');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
INSERT INTO `personal_access_tokens` VALUES (1,'App\\Models\\User',6,'auth_token','7841e584b13528830cbcbf06a88bdf565577de61e95b486e45462365111d2107','[\"*\"]','2026-04-24 07:47:22',NULL,'2026-04-24 07:47:16','2026-04-24 07:47:22'),(2,'App\\Models\\User',2,'auth_token','eb30fb93f69728c94cdb6369fd3b3a70b5e5e6c60d540cf3aa200340b840cbd1','[\"*\"]',NULL,NULL,'2026-04-24 07:49:56','2026-04-24 07:49:56'),(3,'App\\Models\\User',6,'auth_token','a288b831d84e797f7977f626867155c15d594fabe4ef9e6d540d56a92293f9dc','[\"*\"]',NULL,NULL,'2026-04-24 07:58:36','2026-04-24 07:58:36'),(4,'App\\Models\\User',6,'auth_token','b20b389f71dd6e125ccefbda1e21a3df19b2f17b32fdb95c17d0bc5eb9bf40f2','[\"*\"]',NULL,NULL,'2026-04-26 08:04:06','2026-04-26 08:04:06'),(5,'App\\Models\\User',1,'auth_token','82f1ac4d2b482e763264356d08fec76c64513c41c82ed85f3531542e5d656bad','[\"*\"]',NULL,NULL,'2026-04-26 08:05:51','2026-04-26 08:05:51'),(6,'App\\Models\\User',6,'auth_token','937d9d2bdb1662d6da5c855f31c0c85a20e6a3f585c899a3bc303df1f7521c5b','[\"*\"]','2026-05-12 01:06:21',NULL,'2026-05-12 01:06:19','2026-05-12 01:06:21'),(7,'App\\Models\\User',6,'auth_token','f1d504baa2334efd094262b84e224b8090c4919e8a304de21f2452766ec20b80','[\"*\"]','2026-05-12 21:41:25',NULL,'2026-05-12 21:34:36','2026-05-12 21:41:25'),(8,'App\\Models\\User',6,'auth_token','a80a579eacea70368eff960f3dbe2c9f22d57b454f25e5966628d37238558bfa','[\"*\"]','2026-05-12 22:04:28',NULL,'2026-05-12 21:48:19','2026-05-12 22:04:28'),(9,'App\\Models\\User',6,'auth_token','5ba5d45103e51a0198d24c4d50a0ebce017eb8e79fd6fb19af28df4701612249','[\"*\"]','2026-05-12 22:37:57',NULL,'2026-05-12 22:37:55','2026-05-12 22:37:57'),(10,'App\\Models\\User',6,'auth_token','4cc282ba5133a9d141416429b1dd843aa99c19c3c9fda83cf4a4571947e4047a','[\"*\"]','2026-05-12 22:39:23',NULL,'2026-05-12 22:39:21','2026-05-12 22:39:23'),(11,'App\\Models\\User',6,'auth_token','b410c50ff8ff3cc470e981911123db4a7f0bef297c5a1ded58cf8dab6c89181a','[\"*\"]','2026-05-12 22:51:00',NULL,'2026-05-12 22:41:40','2026-05-12 22:51:00');
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `seminar_registrations`
--

DROP TABLE IF EXISTS `seminar_registrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `seminar_registrations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `seminar_id` bigint(20) unsigned NOT NULL,
  `student_id` bigint(20) unsigned NOT NULL,
  `status` enum('registered','attended','completed') NOT NULL DEFAULT 'registered',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `seminar_registrations_seminar_id_student_id_unique` (`seminar_id`,`student_id`),
  KEY `seminar_registrations_student_id_foreign` (`student_id`),
  CONSTRAINT `seminar_registrations_seminar_id_foreign` FOREIGN KEY (`seminar_id`) REFERENCES `seminars` (`id`) ON DELETE CASCADE,
  CONSTRAINT `seminar_registrations_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seminar_registrations`
--

LOCK TABLES `seminar_registrations` WRITE;
/*!40000 ALTER TABLE `seminar_registrations` DISABLE KEYS */;
/*!40000 ALTER TABLE `seminar_registrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `seminars`
--

DROP TABLE IF EXISTS `seminars`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `seminars` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `program` varchar(255) NOT NULL,
  `date` date DEFAULT NULL,
  `time` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `organizer` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  `status` enum('scheduled','completed','cancelled') NOT NULL DEFAULT 'scheduled',
  `student_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `seminars_student_id_foreign` (`student_id`),
  CONSTRAINT `seminars_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seminars`
--

LOCK TABLES `seminars` WRITE;
/*!40000 ALTER TABLE `seminars` DISABLE KEYS */;
INSERT INTO `seminars` VALUES (1,'Seminar Hasil Magang & Pembelajaran','Teknik Informatika','2026-05-09','13:00 - 15:00 WIB','Aula Blok B Lantai 3','Dr. Siti Rahayu, M.T.','Seminar wajib presentasi hasil magang untuk semua mahasiswa aktif.','QR_SEMINAR_001','scheduled',NULL,'2026-04-29 03:55:47','2026-04-29 03:55:47'),(2,'Flutter Development Workshop','Teknik Informatika','2026-05-19','10:00 - 12:00 WIB','Lab Komputer Blok C','Budi Hartono, S.T., M.Eng.','Workshop pengembangan aplikasi mobile menggunakan Flutter dan Dart.','QR_SEMINAR_002','scheduled',NULL,'2026-04-29 03:55:47','2026-04-29 03:55:47'),(3,'Sharing Pengalaman Praktik Industri','Teknik Informatika','2026-05-04','14:00 - 16:00 WIB','Aula Blok A Lantai 1','Dr. Ahmad Fauzi, M.Kom.','Sharing session bersama alumni tentang pengalaman magang di industri.','QR_SEMINAR_003','scheduled',NULL,'2026-04-29 03:55:47','2026-04-29 03:55:47');
/*!40000 ALTER TABLE `seminars` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_scores`
--

DROP TABLE IF EXISTS `student_scores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_scores` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `internship_id` bigint(20) unsigned NOT NULL,
  `detailed_assessment_component_id` bigint(20) unsigned NOT NULL,
  `score` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `scores_unique` (`internship_id`,`detailed_assessment_component_id`),
  KEY `student_scores_detailed_assessment_component_id_foreign` (`detailed_assessment_component_id`),
  CONSTRAINT `student_scores_detailed_assessment_component_id_foreign` FOREIGN KEY (`detailed_assessment_component_id`) REFERENCES `detailed_assessment_components` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_scores_internship_id_foreign` FOREIGN KEY (`internship_id`) REFERENCES `internships` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_scores`
--

LOCK TABLES `student_scores` WRITE;
/*!40000 ALTER TABLE `student_scores` DISABLE KEYS */;
/*!40000 ALTER TABLE `student_scores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `students`
--

DROP TABLE IF EXISTS `students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `students` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `the_class` varchar(255) DEFAULT NULL,
  `study_program` varchar(255) DEFAULT NULL,
  `major` varchar(255) DEFAULT NULL,
  `academic_year` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `students_user_id_foreign` (`user_id`),
  CONSTRAINT `students_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `students`
--

LOCK TABLES `students` WRITE;
/*!40000 ALTER TABLE `students` DISABLE KEYS */;
INSERT INTO `students` VALUES (1,6,'TI-4A','Teknik Informatika','Informatika','2021/2022','2026-04-24 07:46:38','2026-04-24 07:46:38'),(2,7,'TI-4A','Teknik Informatika','Informatika','2021/2022','2026-04-24 07:46:38','2026-04-24 07:46:38'),(3,8,'TI-4B','Teknik Informatika','Informatika','2021/2022','2026-04-24 07:46:38','2026-04-24 07:46:38'),(4,9,'TI-4B','Teknik Informatika','Informatika','2021/2022','2026-04-24 07:46:39','2026-04-24 07:46:39');
/*!40000 ALTER TABLE `students` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','lecturer','lecturer_industry','kaprodi','industri') NOT NULL,
  `photo_profile` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_unique` (`username`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Dr. Ahmad Fauzi, M.Kom','kaprodi','kaprodi@politeknik.ac.id','$2y$12$blsokTOmawZI4H0QZhadWeexKmkxACfSh5VbGoKzEPTjj0tBIqbEG','kaprodi',NULL,NULL,'2026-04-24 07:46:36','2026-05-12 01:04:05'),(2,'Dr. Siti Rahayu, M.T.','dosen1','siti.rahayu@politeknik.ac.id','$2y$12$blsokTOmawZI4H0QZhadWeexKmkxACfSh5VbGoKzEPTjj0tBIqbEG','lecturer',NULL,NULL,'2026-04-24 07:46:36','2026-05-12 01:04:05'),(3,'Budi Hartono, S.T., M.Eng.','dosen2','budi.hartono@politeknik.ac.id','$2y$12$blsokTOmawZI4H0QZhadWeexKmkxACfSh5VbGoKzEPTjj0tBIqbEG','lecturer',NULL,NULL,'2026-04-24 07:46:37','2026-05-12 01:04:05'),(4,'Andi Wijaya, S.T.','pembimbing_industri','andi.wijaya@telkom.co.id','$2y$12$blsokTOmawZI4H0QZhadWeexKmkxACfSh5VbGoKzEPTjj0tBIqbEG','lecturer_industry',NULL,NULL,'2026-04-24 07:46:37','2026-05-12 01:04:05'),(5,'PT. Telkom Indonesia','telkom_hr','hr@telkom.co.id','$2y$12$blsokTOmawZI4H0QZhadWeexKmkxACfSh5VbGoKzEPTjj0tBIqbEG','industri',NULL,NULL,'2026-04-24 07:46:37','2026-05-12 01:04:05'),(6,'Budi Santoso','mahasiswa1','budi.santoso@student.politeknik.ac.id','$2y$12$blsokTOmawZI4H0QZhadWeexKmkxACfSh5VbGoKzEPTjj0tBIqbEG','student',NULL,NULL,'2026-04-24 07:46:38','2026-05-12 01:04:05'),(7,'Sari Dewi','mahasiswa2','sari.dewi@student.politeknik.ac.id','$2y$12$blsokTOmawZI4H0QZhadWeexKmkxACfSh5VbGoKzEPTjj0tBIqbEG','student',NULL,NULL,'2026-04-24 07:46:38','2026-05-12 01:04:05'),(8,'Rizky Pratama','mahasiswa3','rizky.pratama@student.politeknik.ac.id','$2y$12$blsokTOmawZI4H0QZhadWeexKmkxACfSh5VbGoKzEPTjj0tBIqbEG','student',NULL,NULL,'2026-04-24 07:46:38','2026-05-12 01:04:05'),(9,'Putri Ayu','mahasiswa4','putri.ayu@student.politeknik.ac.id','$2y$12$blsokTOmawZI4H0QZhadWeexKmkxACfSh5VbGoKzEPTjj0tBIqbEG','student',NULL,NULL,'2026-04-24 07:46:39','2026-05-12 01:04:05');
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

-- Dump completed on 2026-06-03 22:51:20
