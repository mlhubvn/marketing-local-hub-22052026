-- MySQL dump 10.13  Distrib 8.4.9, for Linux (x86_64)
--
-- Host: localhost    Database: default
-- ------------------------------------------------------
-- Server version	8.4.9

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admin_roles`
--

DROP TABLE IF EXISTS `admin_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `permissions` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admin_roles_name_unique` (`name`),
  UNIQUE KEY `admin_roles_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_roles`
--

LOCK TABLES `admin_roles` WRITE;
/*!40000 ALTER TABLE `admin_roles` DISABLE KEYS */;
/*!40000 ALTER TABLE `admin_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `affiliate_commissions`
--

DROP TABLE IF EXISTS `affiliate_commissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `affiliate_commissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_secure` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `affiliate_user_id` bigint unsigned NOT NULL,
  `referred_user_id` bigint unsigned DEFAULT NULL,
  `payment_history_id` bigint unsigned DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `commission_rate` decimal(8,2) NOT NULL DEFAULT '0.00',
  `commission` decimal(12,2) NOT NULL DEFAULT '0.00',
  `status` tinyint unsigned NOT NULL DEFAULT '0',
  `meta` json DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `affiliate_commissions_id_secure_unique` (`id_secure`),
  KEY `affiliate_commissions_referred_user_id_foreign` (`referred_user_id`),
  KEY `affiliate_commissions_payment_history_id_foreign` (`payment_history_id`),
  KEY `affiliate_commissions_affiliate_user_id_status_index` (`affiliate_user_id`,`status`),
  CONSTRAINT `affiliate_commissions_affiliate_user_id_foreign` FOREIGN KEY (`affiliate_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `affiliate_commissions_payment_history_id_foreign` FOREIGN KEY (`payment_history_id`) REFERENCES `payment_history` (`id`) ON DELETE SET NULL,
  CONSTRAINT `affiliate_commissions_referred_user_id_foreign` FOREIGN KEY (`referred_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `affiliate_commissions`
--

LOCK TABLES `affiliate_commissions` WRITE;
/*!40000 ALTER TABLE `affiliate_commissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `affiliate_commissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `affiliate_profiles`
--

DROP TABLE IF EXISTS `affiliate_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `affiliate_profiles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `clicks` int unsigned NOT NULL DEFAULT '0',
  `conversions` int unsigned NOT NULL DEFAULT '0',
  `total_approved` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total_withdrawal` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total_balance` decimal(12,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `affiliate_profiles_user_id_unique` (`user_id`),
  CONSTRAINT `affiliate_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147123472 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `affiliate_profiles`
--

LOCK TABLES `affiliate_profiles` WRITE;
/*!40000 ALTER TABLE `affiliate_profiles` DISABLE KEYS */;
INSERT INTO `affiliate_profiles` VALUES (147123468,147123468,0,0,0.00,0.00,0.00,'2026-06-06 15:58:01','2026-06-06 15:58:01'),(147123469,147123469,0,0,0.00,0.00,0.00,'2026-06-06 17:14:44','2026-06-06 17:14:44'),(147123470,147123470,0,0,0.00,0.00,0.00,'2026-06-07 11:15:14','2026-06-07 11:15:14'),(147123471,147123471,0,0,0.00,0.00,0.00,'2026-06-07 11:45:13','2026-06-07 11:45:13');
/*!40000 ALTER TABLE `affiliate_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `affiliate_withdrawals`
--

DROP TABLE IF EXISTS `affiliate_withdrawals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `affiliate_withdrawals` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_secure` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `affiliate_user_id` bigint unsigned NOT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `payment_method` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_details` text COLLATE utf8mb4_unicode_ci,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `status` tinyint unsigned NOT NULL DEFAULT '0',
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `affiliate_withdrawals_id_secure_unique` (`id_secure`),
  KEY `affiliate_withdrawals_affiliate_user_id_status_index` (`affiliate_user_id`,`status`),
  CONSTRAINT `affiliate_withdrawals_affiliate_user_id_foreign` FOREIGN KEY (`affiliate_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `affiliate_withdrawals`
--

LOCK TABLES `affiliate_withdrawals` WRITE;
/*!40000 ALTER TABLE `affiliate_withdrawals` DISABLE KEYS */;
/*!40000 ALTER TABLE `affiliate_withdrawals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ai_content_plans`
--

DROP TABLE IF EXISTS `ai_content_plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_content_plans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `owner_user_id` bigint unsigned NOT NULL,
  `requested_by_user_id` bigint unsigned NOT NULL,
  `team_id` bigint unsigned DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `brief` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date NOT NULL,
  `days` smallint unsigned NOT NULL DEFAULT '14',
  `source` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ai',
  `overview` text COLLATE utf8mb4_unicode_ci,
  `items` json NOT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ai_content_plans_owner_user_id_requested_by_user_id_index` (`owner_user_id`,`requested_by_user_id`),
  KEY `ai_content_plans_team_id_index` (`team_id`),
  KEY `ai_content_plans_start_date_index` (`start_date`)
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ai_content_plans`
--

LOCK TABLES `ai_content_plans` WRITE;
/*!40000 ALTER TABLE `ai_content_plans` DISABLE KEYS */;
/*!40000 ALTER TABLE `ai_content_plans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ai_image_jobs`
--

DROP TABLE IF EXISTS `ai_image_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_image_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `owner_user_id` bigint unsigned NOT NULL,
  `requested_by_user_id` bigint unsigned NOT NULL,
  `team_id` bigint unsigned DEFAULT NULL,
  `file_id` bigint unsigned DEFAULT NULL,
  `provider` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'generated',
  `style` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ratio` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prompt` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ai_image_jobs_owner_user_id_requested_by_user_id_index` (`owner_user_id`,`requested_by_user_id`),
  KEY `ai_image_jobs_team_id_index` (`team_id`),
  KEY `ai_image_jobs_file_id_index` (`file_id`),
  KEY `ai_image_jobs_status_index` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ai_image_jobs`
--

LOCK TABLES `ai_image_jobs` WRITE;
/*!40000 ALTER TABLE `ai_image_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `ai_image_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ai_prompt_histories`
--

DROP TABLE IF EXISTS `ai_prompt_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_prompt_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `owner_user_id` bigint unsigned NOT NULL,
  `requested_by_user_id` bigint unsigned NOT NULL,
  `team_id` bigint unsigned DEFAULT NULL,
  `module` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `language` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tone` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prompt` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `input_payload` json DEFAULT NULL,
  `output_payload` json DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ai_prompt_histories_owner_user_id_requested_by_user_id_index` (`owner_user_id`,`requested_by_user_id`),
  KEY `ai_prompt_histories_team_id_index` (`team_id`),
  KEY `ai_prompt_histories_module_index` (`module`)
) ENGINE=InnoDB AUTO_INCREMENT=147123469 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ai_prompt_histories`
--

LOCK TABLES `ai_prompt_histories` WRITE;
/*!40000 ALTER TABLE `ai_prompt_histories` DISABLE KEYS */;
INSERT INTO `ai_prompt_histories` VALUES (147123468,147123471,147123471,NULL,'content_writer','Booking Reminder - Quán nhậu Minh Dung','vi','friendly','Business: Quán nhậu Minh Dung\nContent type: Booking Reminder\nGoal: Nhắc khách thời gian đã book trước khoảng 2 tiếng\nOffer: Giảm 5% nếu đến trước 30 phút\nTarget customer: Khách đã đặt bàn trước','{\"goal\": \"Nhắc khách thời gian đã book trước khoảng 2 tiếng\", \"tone\": \"friendly\", \"offer\": \"Giảm 5% nếu đến trước 30 phút\", \"language\": \"vi\", \"source_id\": \"\", \"business_id\": \"147123468\", \"campaign_id\": \"\", \"source_type\": \"\", \"content_type\": \"booking_reminder\", \"extra_details\": \"\", \"target_customer\": \"Khách đã đặt bàn trước\"}','{\"title\": \"Booking Reminder - Quán nhậu Minh Dung\", \"source\": \"fallback\", \"hashtags\": [], \"short_version\": \"Hi! This is a friendly reminder about your upcoming appointment with Quán nhậu Minh Dung. We look forward to seeing you soon.\", \"cta_suggestions\": [\"Confirm your appointment\", \"Contact us today\", \"Learn more\"], \"fallback_reason\": \"Your project has been denied access. Please contact support.\", \"friendly_version\": \"Hi! This is a friendly reminder about your upcoming appointment with Quán nhậu Minh Dung. We look forward to seeing you soon. We would love to hear from you.\", \"generated_content\": \"Hi! This is a friendly reminder about your upcoming appointment with Quán nhậu Minh Dung. We look forward to seeing you soon.\", \"professional_version\": \"Hi! This is a friendly reminder about your upcoming appointment with Quán nhậu Minh Dung. We look forward to seeing you soon. Please contact us if you have any questions.\"}','{\"source\": \"fallback\", \"business_id\": 147123468, \"content_type\": \"booking_reminder\"}','2026-06-07 12:10:51','2026-06-07 12:10:51');
/*!40000 ALTER TABLE `ai_prompt_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ai_publishing_prompts`
--

DROP TABLE IF EXISTS `ai_publishing_prompts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_publishing_prompts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `owner_user_id` bigint unsigned NOT NULL,
  `title` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prompt_text` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `metadata` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ai_publishing_prompts_owner_user_id_foreign` (`owner_user_id`),
  CONSTRAINT `ai_publishing_prompts_owner_user_id_foreign` FOREIGN KEY (`owner_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ai_publishing_prompts`
--

LOCK TABLES `ai_publishing_prompts` WRITE;
/*!40000 ALTER TABLE `ai_publishing_prompts` DISABLE KEYS */;
/*!40000 ALTER TABLE `ai_publishing_prompts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ai_publishing_runs`
--

DROP TABLE IF EXISTS `ai_publishing_runs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_publishing_runs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `owner_user_id` bigint unsigned NOT NULL,
  `team_id` bigint unsigned DEFAULT NULL,
  `workspace_owner_user_id` bigint unsigned DEFAULT NULL,
  `name` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `campaign_id` bigint unsigned DEFAULT NULL,
  `label_ids` json DEFAULT NULL,
  `account_ids` json DEFAULT NULL,
  `prompt_ids` json DEFAULT NULL,
  `schedule_config` json DEFAULT NULL,
  `generation_config` json DEFAULT NULL,
  `stats` json DEFAULT NULL,
  `status` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `last_processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ai_publishing_runs_owner_user_id_foreign` (`owner_user_id`),
  KEY `ai_publishing_runs_team_id_foreign` (`team_id`),
  KEY `ai_publishing_runs_status_index` (`status`),
  CONSTRAINT `ai_publishing_runs_owner_user_id_foreign` FOREIGN KEY (`owner_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ai_publishing_runs_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ai_publishing_runs`
--

LOCK TABLES `ai_publishing_runs` WRITE;
/*!40000 ALTER TABLE `ai_publishing_runs` DISABLE KEYS */;
/*!40000 ALTER TABLE `ai_publishing_runs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ai_studio_user_settings`
--

DROP TABLE IF EXISTS `ai_studio_user_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_studio_user_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `settings` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ai_studio_user_settings_user_id_unique` (`user_id`),
  CONSTRAINT `ai_studio_user_settings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ai_studio_user_settings`
--

LOCK TABLES `ai_studio_user_settings` WRITE;
/*!40000 ALTER TABLE `ai_studio_user_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `ai_studio_user_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ai_studio_workspace_settings`
--

DROP TABLE IF EXISTS `ai_studio_workspace_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_studio_workspace_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `owner_user_id` bigint unsigned NOT NULL,
  `team_id` bigint unsigned DEFAULT NULL,
  `settings` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ai_studio_workspace_settings_owner_user_id_team_id_unique` (`owner_user_id`,`team_id`),
  KEY `ai_studio_workspace_settings_team_id_foreign` (`team_id`),
  CONSTRAINT `ai_studio_workspace_settings_owner_user_id_foreign` FOREIGN KEY (`owner_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ai_studio_workspace_settings_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ai_studio_workspace_settings`
--

LOCK TABLES `ai_studio_workspace_settings` WRITE;
/*!40000 ALTER TABLE `ai_studio_workspace_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `ai_studio_workspace_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ai_template_categories`
--

DROP TABLE IF EXISTS `ai_template_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_template_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_secure` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `desc` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icon` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'primary',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `changed` bigint unsigned DEFAULT NULL,
  `created` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ai_template_categories_name_unique` (`name`),
  UNIQUE KEY `ai_template_categories_id_secure_unique` (`id_secure`),
  KEY `ai_template_categories_status_changed_index` (`status`,`changed`)
) ENGINE=InnoDB AUTO_INCREMENT=147123502 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ai_template_categories`
--

LOCK TABLES `ai_template_categories` WRITE;
/*!40000 ALTER TABLE `ai_template_categories` DISABLE KEYS */;
INSERT INTO `ai_template_categories` VALUES (147123468,'65a1059f85282','Gợi ý',NULL,'fa-light fa-stars','dark',1,1780736277,0),(147123469,'65a1059f85951','Facebook',NULL,'fa-brands fa-square-facebook','warning',1,1780736277,0),(147123470,'65a1059f85fda','Instagram',NULL,'fa-brands fa-instagram','info',1,1780736277,0),(147123471,'65a1059f86883','X (Twitter)',NULL,'fa-brands fa-x-twitter','success',1,1780736277,0),(147123472,'65a1059f87128','LinkedIn',NULL,'fa-brands fa-linkedin','danger',1,1780736277,0),(147123473,'65a1059f876a9','Pinterest',NULL,'fa-brands fa-pinterest-p','primary',1,1780736277,0),(147123474,'65a1059f87ad9','Google Business Profile',NULL,'fa-brands fa-google','dark',1,1780736277,0),(147123475,'65a1059f87f18','TikTok',NULL,'fa-brands fa-tiktok','success',1,1780736277,0),(147123476,'65a1059f8833e','Youtube',NULL,'fa-brands fa-youtube','primary',1,1780736277,0),(147123477,'65a1059f88814','Viết lại',NULL,'fa-light fa-file-signature','danger',1,1780736277,0),(147123478,'65a1059f88c93','Chỉnh sửa',NULL,'fa-light fa-pencil-alt','warning',1,1780736277,0),(147123479,'65a1059f890bc','Giải thích & Mở rộng',NULL,'fa-light fa-expand','dark',1,1780736277,0),(147123480,'65a1059f894c3','Tóm tắt',NULL,'fa-light fa-filter','success',1,1780736277,0),(147123481,'65a1059f898c7','Khung tâm lý',NULL,'fa-light fa-head-side-brain','info',1,1780736277,0),(147123482,'65a1059f89cc3','Khung sáng tạo nội dung',NULL,'fa-light fa-lightbulb-on','primary',1,1780736277,0),(147123483,'65a1059f8a570','Quảng cáo',NULL,'fa-light fa-ad','danger',1,1780736277,0),(147123484,'65a1059f8aa4a','Doanh nghiệp',NULL,'fa-light fa-building','warning',1,1780736277,0),(147123485,'65a1059f8aeb0','Kêu gọi hành động (CTA)',NULL,'fa-light fa-mouse-pointer','success',1,1780736277,0),(147123486,'65a1059f8b318','Giáo dục',NULL,'fa-light fa-graduation-cap','info',1,1780736277,0),(147123487,'65a1059f8b77c','Vui vẻ',NULL,'fa-light fa-smile-beam','primary',1,1780736277,0),(147123488,'65a1059f8bc48','Tương tác',NULL,'fa-light fa-retweet','success',1,1780736277,0),(147123489,'65a1059f8c08b','Truyền cảm hứng',NULL,'fa-light fa-house-leave','danger',1,1780736277,0),(147123490,'65a1059f8c491','Ngày lễ',NULL,'fa-light fa-umbrella-beach','success',1,1780736277,0),(147123491,'65a1059f8c8b7','Hộ kinh doanh nhỏ',NULL,'fa-light fa-suitcase','warning',1,1780736277,0),(147123492,'65a1059f8ccb3','Huấn luyện kinh doanh',NULL,'fa-light fa-user-chart','warning',1,1780736277,0),(147123493,'65a1059f8d0cb','Huấn luyện lối sống',NULL,'fa-light fa-chalkboard-teacher','primary',1,1780736277,0),(147123494,'65a1059f8d49c','Môi giới bất động sản',NULL,'fa-light fa-house-day','info',1,1780736277,0),(147123495,'65a1059f8d874','Tổ chức phi lợi nhuận',NULL,'fa-light fa-home-heart','warning',1,1780736277,0),(147123496,'65a1059f8dc3f','Doanh nhân',NULL,'fa-light fa-user-tie','primary',1,1780736277,0),(147123497,'65a1059f8e044','Agency marketing',NULL,'fa-light fa-bullseye-arrow','success',1,1780736277,0),(147123498,'65a1059f8e81b','Tác giả',NULL,'fa-light fa-book-reader','success',1,1780736277,0),(147123499,'legacyacctcat33seed01','Công ty kế toán',NULL,'fa-light fa-calculator','info',1,1780736277,0),(147123500,'65a1059f8f113','Nhà hàng',NULL,'fa-light fa-utensils','info',1,1780736277,0),(147123501,'65a1059f8f4fa','Huấn luyện viên fitness',NULL,'fa-light fa-dumbbell','warning',1,1780736277,0);
/*!40000 ALTER TABLE `ai_template_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ai_templates`
--

DROP TABLE IF EXISTS `ai_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_secure` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cate_id` bigint unsigned DEFAULT NULL,
  `content` text COLLATE utf8mb4_unicode_ci,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `changed` bigint unsigned DEFAULT NULL,
  `created` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ai_templates_id_secure_unique` (`id_secure`),
  KEY `ai_templates_cate_id_foreign` (`cate_id`),
  KEY `ai_templates_status_changed_index` (`status`,`changed`),
  CONSTRAINT `ai_templates_cate_id_foreign` FOREIGN KEY (`cate_id`) REFERENCES `ai_template_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=147124527 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ai_templates`
--

LOCK TABLES `ai_templates` WRITE;
/*!40000 ALTER TABLE `ai_templates` DISABLE KEYS */;
INSERT INTO `ai_templates` VALUES (147123468,'65a10d7bd0fab',147123468,'Tóm tắt nội dung sau:',1,1780151790,1780151790),(147123469,'65a10d7bd1578',147123468,'Tạo quảng cáo khuyến mãi cho:',1,1780151790,1780151790),(147123470,'65a10d7bd1a36',147123468,'Tạo bài đăng mạng xã hội cho:',1,1780151790,1780151790),(147123471,'65a10d7bd2226',147123468,'Viết chú thích Instagram dí dỏm về:',1,1780151790,1780151790),(147123472,'65a10d7bd29d8',147123468,'Viết lại và cải thiện nội dung sau:',1,1780151790,1780151790),(147123473,'65a10d7bd2e17',147123469,'Viết bài Facebook hấp dẫn về doanh nghiệp mô tả bên dưới:',1,1780151790,1780151790),(147123474,'65a10d7bd31fb',147123469,'Viết bài đăng Facebook về lợi ích của {{ chủ đề }}.',1,1780151790,1780151790),(147123475,'65a10d7bd35d4',147123469,'Viết bài Facebook về {{ chủ đề }}.',1,1780151790,1780151790),(147123476,'65a10d7bd39d6',147123469,'Gợi ý câu hỏi thú vị để đăng lên nhóm Facebook về {{ chủ đề }}.',1,1780151790,1780151790),(147123477,'65a10d7bd3dcf',147123469,'Viết lại nội dung sau thành bài Facebook bắt mắt.',1,1780151790,1780151790),(147123478,'65a10d7bd428f',147123469,'Gợi ý câu hỏi cho khảo sát Facebook về {{ chủ đề }}.',1,1780151790,1780151790),(147123479,'65a10d7bd47be',147123469,'Viết câu chuyện truyền cảm hứng về cách thay đổi cuộc sống tốt hơn nhờ {{ sản phẩm/dịch vụ }}.',1,1780151790,1780151790),(147123480,'65a10d7bd4ce6',147123469,'Chia sẻ hậu trường tại {{ công ty/dự án }} và công sức đằng sau đó.',1,1780151790,1780151790),(147123481,'65a10d7bd5189',147123469,'Tạo bài Facebook làm nổi bật điểm đặc biệt của {{ sản phẩm/dịch vụ }}.',1,1780151790,1780151790),(147123482,'65a10d7bd56a3',147123469,'Viết bài đăng Facebook giúp đối tượng hiểu tầm quan trọng của {{ chủ đề }}.',1,1780151790,1780151790),(147123483,'65a10d7bd5b98',147123469,'Gợi ý bài Facebook truyền cảm hứng để người theo dõi hành động về {{ vấn đề }}.',1,1780151790,1780151790),(147123484,'65a10d7bd60be',147123469,'Xây dựng bài Facebook kích thích tư duy, mở cuộc trò chuyện về {{ chủ đề }}.',1,1780151790,1780151790),(147123485,'65a10d7bd65fd',147123469,'Thiết kế câu đố Facebook thử thách kiến thức người theo dõi về {{ chủ đề }}.',1,1780151790,1780151790),(147123486,'65a10d7bd6b0a',147123469,'Chia sẻ trải nghiệm cá nhân đã tác động đến cuộc sống bạn về {{ chủ đề }}.',1,1780151790,1780151790),(147123487,'65a10d7bd6fa9',147123469,'Viết bài Facebook khuyến khích người theo dõi chia sẻ ý kiến về {{ chủ đề }}.',1,1780151790,1780151790),(147123488,'65a10d7bd7483',147123469,'Tạo bài đăng Facebook cho thấy xã hội hôm nay chịu ảnh hưởng thế nào từ {{ sản phẩm/dịch vụ }}.',1,1780151790,1780151790),(147123489,'65a10d7bd7968',147123469,'Xây dựng bài Facebook xin phản hồi cải thiện về {{ công ty/dự án }}.',1,1780151790,1780151790),(147123490,'65a10d7bd7dec',147123469,'Chia sẻ ảnh chế vui hoặc dễ đồng cảm về {{ chủ đề }}.',1,1780151790,1780151790),(147123491,'65a10d7bd8354',147123469,'Viết bài Facebook kể câu chuyện thành công của người đã dùng {{ sản phẩm/dịch vụ }}.',1,1780151790,1780151790),(147123492,'65a10d7bd8856',147123469,'Tạo khảo sát/câu đố Facebook giúp người theo dõi suy nghĩ sâu về {{ chủ đề }}.',1,1780151790,1780151790),(147123493,'65a10d7bd8d13',147123470,'Khuyến khích {{ khách hàng lý tưởng }} chia sẻ trải nghiệm tích cực với {{ sản phẩm/dịch vụ }} của tôi bằng bài Instagram độc đáo, hấp dẫn.',1,1780151790,1780151790),(147123494,'65a10d7bd9241',147123470,'Xây dựng niềm tin với {{ khách hàng lý tưởng }} qua bài Instagram thể hiện chuyên môn và sự chuyên nghiệp của {{ công ty/thương hiệu }}.',1,1780151790,1780151790),(147123495,'65a10d7bd9740',147123470,'Thuyết phục {{ khách hàng lý tưởng }} thực hiện {{ hành động mong muốn }} bằng ưu đãi độc quyền trên Instagram, tạo cảm giác gấp và đặc biệt.',1,1780151790,1780151790),(147123496,'65a10d7bd9c0d',147123470,'Cho thấy {{ sản phẩm/dịch vụ }} đáp ứng nhu cầu và nỗi đau của {{ khách hàng lý tưởng }} qua bài Instagram gần gũi, hấp dẫn.',1,1780151790,1780151790),(147123497,'65a10d7bda0be',147123470,'Thuyết phục {{ khách hàng lý tưởng }} chọn {{ sản phẩm/dịch vụ }} thay vì đối thủ bằng bằng chứng rõ ràng trên Instagram.',1,1780151790,1780151790),(147123498,'65a10d7bda569',147123470,'Hướng dẫn từng bước sử dụng {{ sản phẩm/dịch vụ }} và khuyến khích {{ khách hàng lý tưởng }} mua hàng qua bài Instagram có hướng dẫn rõ ràng.',1,1780151790,1780151790),(147123499,'65a10d7bdab20',147123470,'Thu hút {{ khách hàng lý tưởng }} bằng thông điệp chân thật trên Instagram, dẫn đến {{ hành động mong muốn }} với lời kêu gọi hành động mạnh và hình ảnh ấn tượng.',1,1780151790,1780151790),(147123500,'65a10d7bdaf53',147123470,'Cung cấp thông tin hữu ích về {{ chủ đề }} cho {{ khách hàng lý tưởng }} và thuyết phục {{ hành động mong muốn }} qua bài Instagram rõ ràng.',1,1780151790,1780151790),(147123501,'65a10d7bdb39e',147123470,'Thu hút {{ khách hàng lý tưởng }} bằng bài Instagram sáng tạo giới thiệu điểm mạnh và lợi ích {{ sản phẩm/dịch vụ }}.',1,1780151790,1780151790),(147123502,'65a10d7bdb7a5',147123470,'Viết chú thích Instagram dí dỏm về {{ chủ đề }}.',1,1780151790,1780151790),(147123503,'65a10d7bdbc75',147123470,'Gợi ý chú thích Instagram cho ảnh {{ mô tả ảnh }}.',1,1780151790,1780151790),(147123504,'65a10d7bdc1bb',147123470,'Gợi ý 10 ý tưởng video Reels Instagram lan truyền về {{ chủ đề }}.',1,1780151790,1780151790),(147123505,'65a10d7bdc6c0',147123470,'Viết câu chuyện hoặc trải nghiệm cá nhân liên quan {{ chủ đề }}.',1,1780151790,1780151790),(147123506,'65a10d7bdcb77',147123470,'Mời người theo dõi chia sẻ trải nghiệm hoặc ý kiến trong bình luận về {{ chủ đề }}.',1,1780151790,1780151790),(147123507,'65a10d7bdd070',147123470,'Tạo khảo sát hoặc câu đố Instagram về {{ chủ đề }}.',1,1780151790,1780151790),(147123508,'65a10d7bdd58c',147123470,'Chia sẻ câu nói hoặc thông điệp truyền cảm hứng về {{ chủ đề }} trên Instagram.',1,1780151790,1780151790),(147123509,'65a10d7bdda1c',147123470,'Chia sẻ mẹo và lời khuyên về {{ chủ đề }} trên Instagram.',1,1780151790,1780151790),(147123510,'65a10d7bdde36',147123470,'Chia sẻ danh sách tài khoản, sách, chương trình phát thanh hoặc tài nguyên yêu thích về {{ chủ đề }}.',1,1780151790,1780151790),(147123511,'65a10d7bde2ff',147123470,'Tạo thử thách hoặc cuộc thi Instagram theo chủ đề {{ chủ đề }}.',1,1780151790,1780151790),(147123512,'65a10d7bde813',147123471,'Viết chuỗi bài đăng Twitter về {{ chủ đề }}.',1,1780151790,1780151790),(147123513,'65a10d7bdece7',147123471,'Tạo một bài đăng Twitter hài hước.',1,1780151790,1780151790),(147123514,'65a10d7bdf144',147123471,'Gợi ý 10 ý tưởng bài đăng Twitter về {{ chủ đề }}.',1,1780151790,1780151790),(147123515,'65a10d7bdf60d',147123471,'Tạo 10 bài đăng Twitter dựa trên thông tin sau:',1,1780151790,1780151790),(147123516,'65a10d7bdfc2d',147123471,'Viết lại nội dung sau thành bài đăng Twitter:',1,1780151790,1780151790),(147123517,'65a10d7be011d',147123471,'Viết bài đăng Twitter về {{ chủ đề }} với giọng điệu {{ tính từ }}.',1,1780151790,1780151790),(147123518,'65a10d7be05d9',147123471,'Gợi ý chuỗi bài đăng Twitter hậu trường {{ công ty/thương hiệu }}, thuyết phục {{ khách hàng lý tưởng }} {{ hành động mong muốn }} bằng sự chân thật.',1,1780151790,1780151790),(147123519,'65a10d7be0ac6',147123471,'Gợi ý chuỗi bài đăng Twitter hướng dẫn từng bước dùng {{ sản phẩm/dịch vụ }}, thu hút khách tiềm năng chất lượng.',1,1780151790,1780151790),(147123520,'65a10d7be1037',147123471,'Gợi ý chuỗi bài đăng Twitter cho thấy {{ sản phẩm/dịch vụ }} giải quyết nỗi đau của {{ khách hàng lý tưởng }} một cách gần gũi.',1,1780151790,1780151790),(147123521,'65a10d7be149c',147123471,'Gợi ý chuỗi bài đăng Twitter làm nổi bật điểm bán hàng độc đáo của {{ sản phẩm/dịch vụ }}, tạo cảm giác gấp và ưu đãi độc quyền.',1,1780151790,1780151790),(147123522,'65a10d7be1947',147123471,'Gợi ý chuỗi bài đăng Twitter so sánh {{ sản phẩm/dịch vụ }} với đối thủ, thuyết phục {{ khách hàng lý tưởng }} chọn bạn.',1,1780151790,1780151790),(147123523,'65a10d7be1d74',147123471,'Gợi ý chuỗi bài đăng Twitter thu hút {{ khách hàng lý tưởng }} bằng thông điệp chân thật, lời kêu gọi hành động mạnh và hình ảnh ấn tượng để {{ hành động mong muốn }}.',1,1780151790,1780151790),(147123524,'65a10d7be2237',147123471,'Gợi ý chuỗi bài đăng Twitter xây dựng niềm tin với {{ khách hàng lý tưởng }} qua câu chuyện thành công của khách đã dùng {{ sản phẩm/dịch vụ }}.',1,1780151790,1780151790),(147123525,'65a10d7be2794',147123471,'Gợi ý chuỗi bài đăng Twitter góc nhìn độc đáo về {{ chủ đề }}, thuyết phục {{ khách hàng lý tưởng }} {{ hành động mong muốn }} trên {{ trang web/sản phẩm }}.',1,1780151790,1780151790),(147123526,'65a10d7be2be9',147123471,'Gợi ý chuỗi bài đăng Twitter cung cấp thông tin hữu ích về {{ chủ đề }} cho {{ khách hàng lý tưởng }}, lời kêu gọi hành động mạnh thu hút khách chất lượng.',1,1780151790,1780151790),(147123527,'65a10d7be30a7',147123471,'Gợi ý chuỗi bài đăng Twitter xử lý lo ngại của {{ khách hàng lý tưởng }} về {{ sản phẩm/dịch vụ }}, thuyết phục {{ hành động mong muốn }} với cảm giác gấp.',1,1780151790,1780151790),(147123528,'65a10d7be34b7',147123471,'Gợi ý chuỗi bài đăng Twitter thể hiện giá trị {{ sản phẩm/dịch vụ }} cho {{ khách hàng lý tưởng }}, thông điệp rõ để {{ hành động mong muốn }}.',1,1780151790,1780151790),(147123529,'65a10d7be39c5',147123471,'Gợi ý chuỗi bài đăng Twitter giới thiệu {{ sản phẩm/dịch vụ }} sáng tạo, vui vẻ, kèm ưu đãi mạnh thu hút khách chất lượng.',1,1780151790,1780151790),(147123530,'65a10d7be3ed0',147123471,'Gợi ý chuỗi bài đăng Twitter kể câu chuyện {{ sản phẩm/dịch vụ }} giúp {{ khách hàng lý tưởng }} đạt {{ mục tiêu }}.',1,1780151790,1780151790),(147123531,'65a10d7be4324',147123471,'Gợi ý chuỗi bài đăng Twitter lan truyền thu hút khách chất lượng cho {{ sản phẩm/dịch vụ }}, lời kêu gọi hành động mạnh và hình ảnh ấn tượng.',1,1780151790,1780151790),(147123532,'65a10d7be47b7',147123471,'Gợi ý chuỗi bài đăng Twitter lan truyền giới thiệu {{ sản phẩm/dịch vụ }} cho {{ khách hàng lý tưởng }} sáng tạo, hấp dẫn.',1,1780151790,1780151790),(147123533,'65a10d7be4c73',147123471,'Chia sẻ câu chuyện hoặc trải nghiệm cá nhân về {{ chủ đề }}.',1,1780151790,1780151790),(147123534,'65a10d7be51a9',147123471,'Khuyến khích người theo dõi chia sẻ suy nghĩ bằng câu hỏi về {{ chủ đề }}.',1,1780151790,1780151790),(147123535,'65a10d7be56cf',147123471,'Dùng thẻ gắn phù hợp để tham gia cuộc trò chuyện lớn về {{ chủ đề }}.',1,1780151790,1780151790),(147123536,'65a10d7be5b45',147123471,'Chia sẻ câu nói về {{ chủ đề }} và giải thích vì sao nó chạm đến bạn.',1,1780151790,1780151790),(147123537,'65a10d7be5ff1',147123471,'Chia sẻ mẹo hoặc lời khuyên hữu ích về {{ chủ đề }}.',1,1780151790,1780151790),(147123538,'65a10d7be653e',147123471,'Tạo danh sách tài nguyên về {{ chủ đề }} và chia sẻ với người theo dõi.',1,1780151790,1780151790),(147123539,'65a10d7be6a23',147123471,'Đặt tình huống giả định về {{ chủ đề }} và hỏi người theo dõi họ sẽ làm gì.',1,1780151790,1780151790),(147123540,'65a10d7be6ed6',147123471,'Chia sẻ mục tiêu hoặc thử thách cá nhân về {{ chủ đề }}, mời người theo dõi làm tương tự.',1,1780151790,1780151790),(147123541,'65a10d7be7389',147123471,'Chia sẻ sự kiện hoặc nhân vật lịch sử liên quan {{ chủ đề }} và ý nghĩa của nó.',1,1780151790,1780151790),(147123542,'65a10d7be7857',147123471,'Chia sẻ câu chuyện về {{ chủ đề }} và mời người theo dõi kể câu của họ.',1,1780151790,1780151790),(147123543,'65a10d7be7c79',147123471,'Chia sẻ lời khuyên về {{ chủ đề }} mà bạn ước mình biết sớm hơn.',1,1780151790,1780151790),(147123544,'65a10d7be8113',147123471,'Chia sẻ bài học từ sai lầm hoặc thất bại liên quan {{ chủ đề }}.',1,1780151790,1780151790),(147123545,'65a10d7be855d',147123471,'Chia sẻ quan điểm cá nhân về xu hướng hoặc tranh luận phổ biến về {{ chủ đề }}.',1,1780151790,1780151790),(147123546,'65a10d7be89c8',147123471,'Dùng hài hước hoặc châm biếm để nêu quan điểm về {{ chủ đề }}.',1,1780151790,1780151790),(147123547,'65a10d7be8e96',147123471,'Chia sẻ câu chuyện trưởng thành về {{ chủ đề }}, mời người theo dõi chia sẻ hành trình của họ.',1,1780151790,1780151790),(147123548,'65a10d7be9304',147123471,'Chia sẻ điều thú vị về {{ chủ đề }} mà nhiều người có thể chưa biết.',1,1780151790,1780151790),(147123549,'65a10d7be973b',147123472,'Viết bài LinkedIn về tầm quan trọng của kết nối mạng lưới và xây dựng quan hệ nghề nghiệp.',1,1780151790,1780151790),(147123550,'65a10d7be9baa',147123472,'Tạo bài LinkedIn về lợi ích học hỏi liên tục và phát triển nghề nghiệp.',1,1780151790,1780151790),(147123551,'65a10d7bea007',147123472,'Tạo bài LinkedIn về tầm quan trọng xây dựng thương hiệu cá nhân mạnh.',1,1780151790,1780151790),(147123552,'65a10d7bea41e',147123472,'Tạo bài LinkedIn về việc cập nhật xu hướng và tin tức ngành.',1,1780151790,1780151790),(147123553,'65a10d7bea838',147123472,'Tạo bài LinkedIn về đặt mục tiêu sự nghiệp và lập kế hoạch đạt được.',1,1780151790,1780151790),(147123554,'65a10d7beacde',147123472,'Tạo bài LinkedIn về tầm quan trọng xây dựng thương hiệu cá nhân mạnh.',1,1780151790,1780151790),(147123555,'65a10d7beb191',147123472,'Tạo bài LinkedIn về việc cập nhật xu hướng và tin tức ngành.',1,1780151790,1780151790),(147123556,'65a10d7beb65c',147123472,'Tạo bài LinkedIn về đặt mục tiêu sự nghiệp và lập kế hoạch đạt được.',1,1780151790,1780151790),(147123557,'65a10d7bebb3f',147123472,'Viết bài LinkedIn về lợi ích chấp nhận rủi ro có tính toán trong sự nghiệp.',1,1780151790,1780151790),(147123558,'65a10d7bebf73',147123472,'Tạo bài LinkedIn về sự chân thật và minh bạch trong giao tiếp nghề nghiệp.',1,1780151790,1780151790),(147123559,'65a10d7bec36d',147123472,'Tạo bài LinkedIn quảng bá bài viết chuyên mục về {{ chủ đề }}.',1,1780151790,1780151790),(147123560,'65a10d7bec731',147123472,'Tạo bài LinkedIn quảng bá lợi ích {{ sản phẩm/dịch vụ }}, khuyến khích {{ hành động }}.',1,1780151790,1780151790),(147123561,'65a10d7becb13',147123472,'Gợi ý câu hỏi cho câu đố LinkedIn về {{ chủ đề }}.',1,1780151790,1780151790),(147123562,'65a10d7bececd',147123472,'Viết bài LinkedIn về sức mạnh của cố vấn và tìm người cố vấn nghề nghiệp.',1,1780151790,1780151790),(147123563,'65a10d7bed2b2',147123472,'Tạo bài LinkedIn về lợi ích tình nguyện và đóng góp cho cộng đồng.',1,1780151790,1780151790),(147123564,'65a10d7bedae3',147123472,'Tạo bài LinkedIn về xây dựng mạng lưới nghề nghiệp đa dạng.',1,1780151790,1780151790),(147123565,'65a10d7bedee3',147123472,'Viết bài LinkedIn về lợi ích phát triển kỹ năng giao tiếp tại nơi làm việc.',1,1780151790,1780151790),(147123566,'65a10d7bee309',147123472,'Tạo bài LinkedIn về cân bằng công việc–cuộc sống và chăm sóc bản thân.',1,1780151790,1780151790),(147123567,'65a10d7bee6bd',147123472,'Tạo bài LinkedIn về lợi ích tham dự hội nghị và sự kiện ngành.',1,1780151790,1780151790),(147123568,'65a10d7beeb10',147123472,'Viết bài LinkedIn về nuôi dưỡng tư duy phát triển.',1,1780151790,1780151790),(147123569,'65a10d7beef89',147123472,'Tạo bài LinkedIn quảng bá tập chương trình phát thanh hoặc phỏng vấn về {{ chủ đề }}.',1,1780151790,1780151790),(147123570,'65a10d7bef3fe',147123472,'Tạo bài LinkedIn về lợi ích thuê ngoài một số việc để tăng năng suất.',1,1780151790,1780151790),(147123571,'65a10d7bef86a',147123472,'Gợi ý câu hỏi khảo sát/câu đố LinkedIn về thách thức thường gặp trong {{ ngành/nghề nghiệp }}.',1,1780151790,1780151790),(147123572,'65a10d7befcef',147123472,'Viết bài LinkedIn về tầm quan trọng phát triển kỹ năng lãnh đạo.',1,1780151790,1780151790),(147123573,'65a10d7bf0187',147123472,'Tạo bài LinkedIn về lợi ích xây dựng quan hệ liên phòng ban trong tổ chức.',1,1780151790,1780151790),(147123574,'65a10d7bf055f',147123472,'Tạo bài LinkedIn về giữ thái độ và tư duy tích cực tại nơi làm việc.',1,1780151790,1780151790),(147123575,'65a10d7bf08e5',147123472,'Viết bài LinkedIn về tìm sự cân bằng công việc phù hợp giá trị và ưu tiên của bạn.',1,1780151790,1780151790),(147123576,'65a10d7bf0d5a',147123472,'Tạo bài LinkedIn về sứ mệnh cá nhân và đồng bộ mục tiêu sự nghiệp với sứ mệnh đó.',1,1780151790,1780151790),(147123577,'65a10d7bf117b',147123472,'Tạo bài LinkedIn về sứ mệnh cá nhân và đồng bộ mục tiêu sự nghiệp với sứ mệnh đó.',1,1780151790,1780151790),(147123578,'65a10d7bf15a0',147123472,'Viết bài LinkedIn về xây dựng sự kiên cường và khả năng thích ứng trong sự nghiệp.',1,1780151790,1780151790),(147123579,'65a10d7bf19f9',147123472,'Tạo bài LinkedIn quảng bá hội thảo trực tuyến hoặc sự kiện trực tuyến về {{ chủ đề }}.',1,1780151790,1780151790),(147123580,'65a10d7bf1e60',147123472,'Tạo bài LinkedIn về lợi ích kết nối mạng lưới ngoài ngành hoặc nghề của bạn.',1,1780151790,1780151790),(147123581,'65a10d7bf22f2',147123472,'Viết bài LinkedIn về cho và nhận phản hồi hiệu quả tại nơi làm việc.',1,1780151790,1780151790),(147123582,'65a10d7bf2812',147123473,'Viết mô tả Pinterest cho hình ảnh về {{ chủ đề }}.',1,1780151790,1780151790),(147123583,'65a10d7bf2ce9',147123473,'Tạo mô tả Pinterest cho {{ sản phẩm/dịch vụ }} dẫn người xem đến {{ trang web }}.',1,1780151790,1780151790),(147123584,'65a10d7bf31eb',147123473,'Gợi ý 10 tiêu đề ghim cho bài về {{ chủ đề/sản phẩm/dịch vụ }}.',1,1780151790,1780151790),(147123585,'65a10d7bf3609',147123473,'Viết bài Pinterest quảng bá {{ sản phẩm/dịch vụ }}, khuyến khích {{ hành động }}.',1,1780151790,1780151790),(147123586,'65a10d7c0053a',147123473,'Viết bài Pinterest truyền cảm hứng về {{ chủ đề }}.',1,1780151790,1780151790),(147123587,'65a10d7c009aa',147123473,'Tạo bài Pinterest chia sẻ mẹo về {{ chủ đề }}.',1,1780151790,1780151790),(147123588,'65a10d7c00f3a',147123473,'Viết hướng dẫn từng bước sử dụng {{ sản phẩm/dịch vụ }}.',1,1780151790,1780151790),(147123589,'65a10d7c01742',147123473,'Chia sẻ câu chuyện cá nhân liên quan {{ chủ đề/sản phẩm/dịch vụ }}.',1,1780151790,1780151790),(147123590,'65a10d7c028bc',147123473,'Tổng hợp 10 hàng đầu tài nguyên về {{ chủ đề }} và chia sẻ trên Pinterest.',1,1780151790,1780151790),(147123591,'65a10d7c038b2',147123473,'Viết hướng dẫn tự làm liên quan {{ chủ đề/sản phẩm/dịch vụ }}.',1,1780151790,1780151790),(147123592,'65a10d7c04d16',147123473,'Chia sẻ hậu trường quá trình tạo ra {{ sản phẩm/dịch vụ }}.',1,1780151790,1780151790),(147123593,'65a10d7c05653',147123473,'Viết bài Pinterest chia sẻ lợi ích của {{ chủ đề/sản phẩm/dịch vụ }}.',1,1780151790,1780151790),(147123594,'65a10d7c05b8e',147123473,'Tạo bài Pinterest bác bỏ hiểu lầm về {{ chủ đề/sản phẩm/dịch vụ }}.',1,1780151790,1780151790),(147123595,'65a10d7c060a0',147123473,'Viết bài Pinterest về lịch sử và sự phát triển của {{ sản phẩm/dịch vụ }}.',1,1780151790,1780151790),(147123596,'65a10d7c0658f',147123473,'Chia sẻ bộ sưu tập câu nói truyền cảm hứng về {{ chủ đề }}.',1,1780151790,1780151790),(147123597,'65a10d7c06aba',147123473,'Viết bài Pinterest liệt kê cách dùng {{ sản phẩm/dịch vụ }} vui và sáng tạo.',1,1780151790,1780151790),(147123598,'65a10d7c06fb5',147123473,'Tạo bài Pinterest 10 hàng đầu xu hướng {{ chủ đề/sản phẩm/dịch vụ }} và cách áp dụng vào đời sống.',1,1780151790,1780151790),(147123599,'65a10d7c07404',147123473,'Viết bài Pinterest liệt kê sự thật bất ngờ về {{ sản phẩm/dịch vụ }}.',1,1780151790,1780151790),(147123600,'65a10d7c078dc',147123473,'Chia sẻ bài Pinterest 10 hàng đầu người có ảnh hưởng và chuyên gia trong {{ chủ đề/sản phẩm/dịch vụ }} và lý do nên theo dõi.',1,1780151790,1780151790),(147123601,'65a10d7c07d33',147123473,'Viết bài Pinterest 10 hàng đầu sai lầm cần tránh khi dùng {{ sản phẩm/dịch vụ }}.',1,1780151790,1780151790),(147123602,'65a10d7c0820e',147123474,'Viết bài về {{ sản phẩm hoặc dịch vụ }} thuyết phục {{ đối tượng mục tiêu }} {{ hành động }}.',1,1780151790,1780151790),(147123603,'65a10d7c08681',147123474,'Tạo bài hấp dẫn về {{ chủ đề/sự kiện/sản phẩm/dịch vụ }} thu hút lượt truy cập về {{ trang web }}.',1,1780151790,1780151790),(147123604,'65a10d7c08bc4',147123474,'Tạo bài chúc mừng về {{ thành tựu }}.',1,1780151790,1780151790),(147123605,'65a10d7c08ffb',147123474,'Viết bài về lợi ích ủng hộ doanh nghiệp địa phương.',1,1780151790,1780151790),(147123606,'65a10d7c09419',147123474,'Chia sẻ câu chuyện thành công hoặc nhận xét khách hàng làm nổi bật giá trị {{ sản phẩm/dịch vụ }}.',1,1780151790,1780151790),(147123607,'65a10d7c098fd',147123474,'Làm nổi bật điểm đặc biệt của {{ sản phẩm/dịch vụ }} và lợi thế so với đối thủ.',1,1780151790,1780151790),(147123608,'65a10d7c09e29',147123474,'Viết bài chia sẻ mẹo hữu ích liên quan {{ ngành/ngách }}.',1,1780151790,1780151790),(147123609,'65a10d7c0a2e9',147123474,'Tạo chú thích video hậu trường quy trình tạo {{ sản phẩm hoặc dịch vụ }}.',1,1780151790,1780151790),(147123610,'65a10d7c0a7c7',147123474,'Viết bài thông báo {{ sự kiện/ra mắt sản phẩm/tin công ty }}.',1,1780151790,1780151790),(147123611,'65a10d7c0b027',147123474,'Chia sẻ bài quảng bá {{ hợp tác/liên kết }}.',1,1780151790,1780151790),(147123612,'65a10d7c0b512',147123474,'Tạo bài mạng xã hội chúc mừng {{ giải thưởng/sự công nhận }}.',1,1780151790,1780151790),(147123613,'65a10d7c0b9b3',147123474,'Chia sẻ bài công bố {{ giảm giá/ưu đãi }}.',1,1780151790,1780151790),(147123614,'65a10d7c0bd7c',147123474,'Viết bài giải đáp {{ thắc mắc thường gặp }} về {{ sản phẩm hoặc dịch vụ }}.',1,1780151790,1780151790),(147123615,'65a10d7c0c1ff',147123475,'Gợi ý 10 ý tưởng bài TikTok về {{ chủ đề }}.',1,1780151790,1780151790),(147123616,'65a10d7c0c64c',147123475,'Gợi ý 10 ý tưởng bài TikTok cho {{ loại hình kinh doanh }}.',1,1780151790,1780151790),(147123617,'65a10d7c0ca27',147123475,'Viết chú thích TikTok cho video về {{ chủ đề }}.',1,1780151790,1780151790),(147123618,'65a10d7c0cde7',147123475,'Viết kịch bản video TikTok về {{ chủ đề }}.',1,1780151790,1780151790),(147123619,'65a10d7c0d1d7',147123475,'Gợi ý 10 ý tưởng thử thách TikTok.',1,1780151790,1780151790),(147123620,'65a10d7c0d5bb',147123475,'Viết mô tả video TikTok về {{ chủ đề }}.',1,1780151790,1780151790),(147123621,'65a10d7c0da3c',147123475,'Gợi ý danh sách thẻ gắn cho video TikTok về {{ chủ đề }}.',1,1780151790,1780151790),(147123622,'65a10d7c0ddfa',147123475,'Viết câu đùa hài cho video TikTok về {{ chủ đề }}.',1,1780151790,1780151790),(147123623,'65a10d7c0e2e8',147123475,'Tạo đoạn mở đầu cuốn hút cho video TikTok về {{ chủ đề }}.',1,1780151790,1780151790),(147123624,'65a10d7c0e7c6',147123476,'Viết vài tiêu đề Youtube về {{ chủ đề }}.',1,1780151790,1780151790),(147123625,'65a10d7c0ec2d',147123476,'Gợi ý tiêu đề video Youtube độc đáo về {{ chủ đề }}.',1,1780151790,1780151790),(147123626,'65a10d7c0f0b8',147123476,'Liệt kê ý tưởng video Youtube về {{ chủ đề }} cho {{ đối tượng mục tiêu }}.',1,1780151790,1780151790),(147123627,'65a10d7c0f4ef',147123476,'Viết dàn ý kịch bản video có tiêu đề {{ tiêu đề video }}.',1,1780151790,1780151790),(147123628,'65a10d7c0f8c2',147123476,'Phác thảo cấu trúc video Youtube về {{ chủ đề }}.',1,1780151790,1780151790),(147123629,'65a10d7c0fc9c',147123476,'Tạo kịch bản video Youtube về {{ chủ đề }}, giọng {{ giọng điệu }}, cho {{ đối tượng mục tiêu }}.',1,1780151790,1780151790),(147123630,'65a10d7c1006b',147123476,'Viết phần mở đầu kịch bản cho video {{ tiêu đề video }}.',1,1780151790,1780151790),(147123631,'65a10d7c10431',147123476,'Viết câu mở đầu thu hút kịch bản cho video {{ tiêu đề video }}.',1,1780151790,1780151790),(147123632,'65a10d7c1081d',147123476,'Viết mô tả video cho kịch bản sau:',1,1780151790,1780151790),(147123633,'65a10d7c10bf6',147123476,'Viết phần kết cho video Youtube về {{ chủ đề }}.',1,1780151790,1780151790),(147123634,'65a10d7c10fda',147123476,'Viết đoạn quảng bá tài trợ cho video Youtube về {{ sản phẩm }}.',1,1780151790,1780151790),(147123635,'65a10d7c12163',147123476,'Viết lời kêu gọi hành động cho video Youtube khuyến khích đăng ký kênh.',1,1780151790,1780151790),(147123636,'65a10d7c12600',147123476,'Gợi ý ý tưởng video Youtube hậu trường {{ công ty/thương hiệu }}, thuyết phục {{ khách hàng lý tưởng }} {{ hành động mong muốn }} chân thật, gần gũi.',1,1780151790,1780151790),(147123637,'65a10d7c12aa3',147123476,'Tạo video Youtube hướng dẫn từng bước dùng {{ sản phẩm/dịch vụ }}, thuyết phục {{ khách hàng lý tưởng }} mua hàng.',1,1780151790,1780151790),(147123638,'65a10d7c12e68',147123476,'Gợi ý video Youtube cho thấy {{ sản phẩm/dịch vụ }} giải quyết nỗi đau của {{ khách hàng lý tưởng }} gần gũi, hấp dẫn.',1,1780151790,1780151790),(147123639,'65a10d7c132f7',147123476,'Tạo video Youtube nêu điểm bán hàng độc đáo {{ sản phẩm/dịch vụ }}, thuyết phục {{ khách hàng lý tưởng }} mua với ưu đãi độc quyền.',1,1780151790,1780151790),(147123640,'65a10d7c13715',147123476,'Tạo video Youtube so sánh {{ sản phẩm/dịch vụ }} với đối thủ, thuyết phục {{ khách hàng lý tưởng }} chọn chúng tôi.',1,1780151790,1780151790),(147123641,'65a10d7c13ab0',147123476,'Tạo video Youtube thu hút {{ khách hàng lý tưởng }} bằng thông điệp chân thật, lời kêu gọi hành động mạnh để {{ hành động mong muốn }}.',1,1780151790,1780151790),(147123642,'65a10d7c13e81',147123476,'Gợi ý video Youtube kể câu chuyện thành công khách đã dùng {{ sản phẩm/dịch vụ }}, thuyết phục {{ khách hàng lý tưởng }} mua.',1,1780151790,1780151790),(147123643,'65a10d7c142b0',147123476,'Gợi ý video Youtube góc nhìn độc đáo về {{ chủ đề }}, thuyết phục {{ khách hàng lý tưởng }} {{ hành động mong muốn }} trên {{ trang web/sản phẩm }}.',1,1780151790,1780151790),(147123644,'65a10d7c1469f',147123476,'Gợi ý video Youtube cung cấp thông tin hữu ích về {{ chủ đề }} cho {{ khách hàng lý tưởng }}, dẫn {{ hành động mong muốn }} trên {{ trang web/sản phẩm }}.',1,1780151790,1780151790),(147123645,'65a10d7c14a70',147123476,'Gợi ý video Youtube xử lý lo ngại của {{ khách hàng lý tưởng }} về {{ sản phẩm/dịch vụ }}, thuyết phục {{ hành động mong muốn }}.',1,1780151790,1780151790),(147123646,'65a10d7c14e50',147123476,'Gợi ý video Youtube thể hiện giá trị {{ sản phẩm/dịch vụ }}, ưu đãi mạnh và lời kêu gọi hành động rõ cho {{ khách hàng lý tưởng }} {{ hành động mong muốn }}.',1,1780151790,1780151790),(147123647,'65a10d7c15231',147123476,'Gợi ý video Youtube vui, sáng tạo giới thiệu {{ sản phẩm/dịch vụ }}, thuyết phục {{ khách hàng lý tưởng }} mua hàng.',1,1780151790,1780151790),(147123648,'65a10d7c1562b',147123476,'Kể câu chuyện {{ sản phẩm/dịch vụ }} trên Youtube, cho thấy đã giúp {{ khách hàng lý tưởng }} đạt {{ mục tiêu }}.',1,1780151790,1780151790),(147123649,'65a10d7c159ea',147123476,'Gợi ý video Youtube lan truyền thuyết phục {{ khách hàng lý tưởng }} {{ hành động mong muốn }} trên {{ trang web/sản phẩm }}, lời kêu gọi hành động mạnh.',1,1780151790,1780151790),(147123650,'65a10d7c15dfc',147123476,'Gợi ý video Youtube giải trí, giới thiệu {{ sản phẩm/dịch vụ }} cho {{ khách hàng lý tưởng }} sáng tạo.',1,1780151790,1780151790),(147123651,'65a10d7c161cb',147123476,'Soạn kịch bản quảng cáo Youtube nêu điểm bán hàng độc đáo {{ sản phẩm/dịch vụ }}, ưu đãi độc quyền thuyết phục {{ khách hàng lý tưởng }} mua.',1,1780151790,1780151790),(147123652,'65a10d7c165bd',147123476,'Viết kịch bản quảng cáo Youtube thu hút {{ khách hàng lý tưởng }}, lời kêu gọi hành động mạnh để {{ hành động mong muốn }}.',1,1780151790,1780151790),(147123653,'65a10d7c1697e',147123476,'Soạn kịch bản quảng cáo Youtube xây niềm tin qua thành công và nhận xét khách đã dùng {{ sản phẩm/dịch vụ }}.',1,1780151790,1780151790),(147123654,'65a10d7c16d92',147123476,'Viết kịch bản quảng cáo Youtube giáo dục {{ khách hàng lý tưởng }} về {{ chủ đề }}, dẫn {{ hành động mong muốn }} trên {{ trang web/sản phẩm }}.',1,1780151790,1780151790),(147123655,'65a10d7c171af',147123476,'Tạo kịch bản quảng cáo Youtube nói thẳng nhu cầu {{ khách hàng lý tưởng }}, ưu đãi mạnh, {{ hành động mong muốn }}.',1,1780151790,1780151790),(147123656,'65a10d7c17575',147123476,'Viết kịch bản quảng cáo Youtube cung cấp thông tin hữu ích cho {{ khách hàng lý tưởng }}, {{ hành động mong muốn }} trên {{ trang web/sản phẩm }}.',1,1780151790,1780151790),(147123657,'65a10d7c1793b',147123476,'Soạn kịch bản quảng cáo Youtube góc nhìn độc đáo về {{ chủ đề }}, thuyết phục {{ khách hàng lý tưởng }} {{ hành động mong muốn }}.',1,1780151790,1780151790),(147123658,'65a10d7c17d05',147123476,'Soạn kịch bản quảng cáo Youtube giải quyết nỗi đau {{ khách hàng lý tưởng }} bằng {{ sản phẩm/dịch vụ }}.',1,1780151790,1780151790),(147123659,'65a10d7c180f0',147123476,'Tạo kịch bản quảng cáo Youtube giải thích tính năng và lợi ích {{ sản phẩm/dịch vụ }}, thuyết phục {{ khách hàng lý tưởng }} mua gấp.',1,1780151790,1780151790),(147123660,'65a10d7c184e0',147123476,'Viết kịch bản quảng cáo Youtube kể câu chuyện {{ sản phẩm/dịch vụ }} giúp người giống {{ khách hàng lý tưởng }} đạt {{ mục tiêu }}.',1,1780151790,1780151790),(147123661,'65a10d7c188b3',147123476,'Soạn kịch bản quảng cáo Youtube thể hiện giá trị {{ sản phẩm/dịch vụ }}, ưu đãi và lời kêu gọi hành động rõ cho {{ hành động mong muốn }}.',1,1780151790,1780151790),(147123662,'65a10d7c18d0d',147123476,'Soạn kịch bản quảng cáo Youtube xử lý lo ngại về {{ sản phẩm/dịch vụ }}, thuyết phục {{ hành động mong muốn }}.',1,1780151790,1780151790),(147123663,'65a10d7c1909d',147123476,'Tạo kịch bản quảng cáo Youtube câu mở đầu thu hút mạnh, thuyết phục {{ khách hàng lý tưởng }} {{ hành động mong muốn }} bằng bằng chứng.',1,1780151790,1780151790),(147123664,'65a10d7c194ca',147123476,'Viết kịch bản quảng cáo Youtube giới thiệu {{ sản phẩm/dịch vụ }}, bằng chứng từ khách hàng thuyết phục {{ khách hàng lý tưởng }} mua.',1,1780151790,1780151790),(147123665,'65a10d7c19888',147123476,'Soạn kịch bản quảng cáo Youtube giới thiệu {{ sản phẩm/dịch vụ }} cho {{ khách hàng lý tưởng }}, lời kêu gọi hành động và hình ảnh ấn tượng.',1,1780151790,1780151790),(147123666,'65a10d7c19c83',147123477,'Viết lại và cải thiện nội dung sau:',1,1780151790,1780151790),(147123667,'65a10d7c1a05b',147123477,'Viết lại nội dung sau từ góc nhìn {{ cá nhân/nghề nghiệp }}:',1,1780151790,1780151790),(147123668,'65a10d7c1a483',147123477,'Diễn đạt lại nội dung sau với giọng {{ tính từ }}:',1,1780151790,1780151790),(147123669,'65a10d7c1a845',147123477,'Viết lại nội dung sau để giải thích cho học sinh lớp 5 hiểu:',1,1780151790,1780151790),(147123670,'65a10d7c1ac0d',147123477,'Diễn đạt lại đoạn văn sau cho dễ đọc hơn:',1,1780151790,1780151790),(147123671,'65a10d7c1aff7',147123477,'Viết lại đoạn văn sau bằng từ vựng tinh tế hơn:',1,1780151790,1780151790),(147123672,'65a10d7c1b3cf',147123477,'Viết lại danh sách sau thành đoạn văn:',1,1780151790,1780151790),(147123673,'65a10d7c1b791',147123477,'Viết lại đoạn văn sau thành danh sách:',1,1780151790,1780151790),(147123674,'65a10d7c1bbbd',147123477,'Tạo bài mạng xã hội mở rộng, giải thích sâu {{ chủ đề }} kèm từ khóa sau:',1,1780151790,1780151790),(147123675,'65a10d7c1c137',147123477,'Viết lại câu sau với cấu trúc ngữ pháp khác:',1,1780151790,1780151790),(147123676,'65a10d7c1c5eb',147123477,'Viết lại đoạn văn theo thể loại {{ thể loại }} (ví dụ: thơ, báo chí, học thuật):',1,1780151790,1780151790),(147123677,'65a10d7c1cb35',147123477,'Viết lại đoạn văn sau ở thể chủ động:',1,1780151790,1780151790),(147123678,'65a10d7c1cf8d',147123477,'Viết lại đoạn văn sau với nhiều chi tiết cảm giác hơn:',1,1780151790,1780151790),(147123679,'65a10d7c1d3a3',147123477,'Viết lại câu sau để loại bỏ trùng lặp:',1,1780151790,1780151790),(147123680,'65a10d7c1d75b',147123477,'Viết lại đoạn văn sau bằng câu ngắn hơn:',1,1780151790,1780151790),(147123681,'65a10d7c1dbc3',147123477,'Viết lại đoạn văn sau để nhấn mạnh điểm khác:',1,1780151790,1780151790),(147123682,'65a10d7c1e058',147123477,'Viết lại đoạn văn sau bằng ngôn ngữ ẩn dụ:',1,1780151790,1780151790),(147123683,'65a10d7c1e530',147123477,'Viết lại đoạn văn sau hướng tới {{ đối tượng }}:',1,1780151790,1780151790),(147123684,'65a10d7c1ea20',147123477,'Viết lại đoạn văn sau bằng ngôn ngữ chính xác hơn:',1,1780151790,1780151790),(147123685,'65a10d7c1ee69',147123477,'Viết lại đoạn văn sau để làm nổi bật góc nhìn khác:',1,1780151790,1780151790),(147123686,'65a10d7c1f269',147123478,'Cải thiện bài mạng xã hội sau, làm cho {{ tính từ }} hơn:',1,1780151790,1780151790),(147123687,'65a10d7c1f69c',147123478,'Chỉnh sửa bài mạng xã hội sau, tập trung vào {{ góc nhìn }}:',1,1780151790,1780151790),(147123688,'65a10d7c1fb72',147123478,'Chỉnh sửa bài mạng xã hội sau cho mới mẻ hơn:',1,1780151790,1780151790),(147123689,'65a10d7c1ff5e',147123478,'Chỉnh sửa bài mạng xã hội sau với giọng điệu {{ tính từ }}:',1,1780151790,1780151790),(147123690,'65a10d7c2038c',147123478,'Rút gọn bài mạng xã hội sau mà không mất ý:',1,1780151790,1780151790),(147123691,'65a10d7c2082a',147123478,'Thêm lời kêu gọi hành động vào bài mạng xã hội sau:',1,1780151790,1780151790),(147123692,'65a10d7c20d1d',147123478,'Làm bài mạng xã hội sau hấp dẫn hơn bằng câu hỏi hoặc khảo sát:',1,1780151790,1780151790),(147123693,'65a10d7c21230',147123478,'Chỉnh định dạng bài mạng xã hội sau cho dễ đọc hơn:',1,1780151790,1780151790),(147123694,'65a10d7c21686',147123478,'Chỉnh sửa bài mạng xã hội sau nhắm đúng {{ đối tượng cụ thể }}:',1,1780151790,1780151790),(147123695,'65a10d7c21af1',147123478,'Thêm thẻ gắn phù hợp vào bài mạng xã hội sau để tăng hiển thị:',1,1780151790,1780151790),(147123696,'65a10d7c2201e',147123478,'Loại bỏ thông tin thừa trong bài mạng xã hội sau:',1,1780151790,1780151790),(147123697,'65a10d7c224b4',147123478,'Gắn chủ đề hot hoặc sự kiện hiện tại vào bài mạng xã hội sau:',1,1780151790,1780151790),(147123698,'65a10d7c2291b',147123478,'Thêm dấu ấn cá nhân vào bài mạng xã hội sau cho gần gũi hơn:',1,1780151790,1780151790),(147123699,'65a10d7c22d4b',147123478,'Chỉnh sửa bài mạng xã hội sau cho phù hợp {{ giá trị }}:',1,1780151790,1780151790),(147123700,'65a10d7c23201',147123478,'Điều chỉnh độ dài bài mạng xã hội sau cho vừa {{ giới hạn ký tự }}:',1,1780151790,1780151790),(147123701,'65a10d7c236ee',147123478,'Thêm biểu tượng cảm xúc vào bài mạng xã hội sau:',1,1780151790,1780151790),(147123702,'65a10d7c23bac',147123478,'Làm bài mạng xã hội sau nổi bật bằng câu khẳng định mạnh:',1,1780151790,1780151790),(147123703,'65a10d7c243e7',147123478,'Biến bài mạng xã hội sau thành câu chuyện:',1,1780151790,1780151790),(147123704,'65a10d7c248b9',147123481,'Viết câu tiếp theo: 1',0,1780151790,1780151790),(147123705,'65a10d7c24d95',147123479,'Tạo bài mạng xã hội mở rộng ý tưởng sau:',1,1780151790,1780151790),(147123706,'65a10d7c2523b',147123479,'Hoàn thành câu sau:',1,1780151790,1780151790),(147123707,'65a10d7c2575b',147123479,'Phân tích chi tiết {{ chủ đề }}.',1,1780151790,1780151790),(147123708,'65a10d7c25b55',147123479,'Cho biết thêm về {{ chủ đề }}, tập trung {{ góc nhìn/chủ đề phụ }}.',1,1780151790,1780151790),(147123709,'65a10d7c25f46',147123479,'Kể về lịch sử {{ chủ đề }}.',1,1780151790,1780151790),(147123710,'65a10d7c26328',147123479,'Viết bài mạng xã hội về các sự thật sau:',1,1780151790,1780151790),(147123711,'65a10d7c2676f',147123479,'Hoàn thành đoạn: Tôi rất vui giới thiệu {{ tên sản phẩm }} mới giúp bạn {{ lợi ích }}.',1,1780151790,1780151790),(147123712,'65a10d7c26b6e',147123479,'Giải thích ý tưởng sau trong bài mạng xã hội, kèm ví dụ cụ thể:',1,1780151790,1780151790),(147123713,'65a10d7c26f91',147123479,'Tạo bài mạng xã hội mở rộng hàm ý của phát biểu sau:',1,1780151790,1780151790),(147123714,'65a10d7c2734a',147123479,'Tạo bài mạng xã hội mở rộng luận điểm sau và đưa ra phản biện:',1,1780151790,1780151790),(147123715,'65a10d7c27701',147123479,'Chia sẻ suy nghĩ về {{ chủ đề }} và mối liên hệ với {{ chủ đề liên quan }} trong bài mạng xã hội.',1,1780151790,1780151790),(147123716,'65a10d7c27abb',147123479,'Tạo bài mạng xã hội mở rộng tầm quan trọng của {{ khái niệm/ý tưởng }}.',1,1780151790,1780151790),(147123717,'65a10d7c27e78',147123479,'Tạo bài mạng xã hội mở rộng các cách giải quyết {{ vấn đề }}.',1,1780151790,1780151790),(147123718,'65a10d7c2823e',147123479,'Giải thích trong bài mạng xã hội các kết quả có thể của {{ sự kiện/tình huống }}.',1,1780151790,1780151790),(147123719,'65a10d7c2863e',147123479,'Tạo bài mạng xã hội mở rộng câu trích dẫn sau và liên hệ với {{ chủ đề }}:',1,1780151790,1780151790),(147123720,'65a10d7c28a2d',147123479,'Tạo bài mạng xã hội mở rộng phát biểu sau và đưa bằng chứng ủng hộ:',1,1780151790,1780151790),(147123721,'65a10d7c28df2',147123479,'Giải thích trong bài mạng xã hội sự liên quan của {{ chủ đề }} với xã hội hôm nay.',1,1780151790,1780151790),(147123722,'65a10d7c291b6',147123479,'Giải thích trong bài mạng xã hội các lý thuyết liên quan {{ khái niệm/ý tưởng }}.',1,1780151790,1780151790),(147123723,'65a10d7c29607',147123479,'Tạo bài mạng xã hội mở rộng phép ẩn dụ sau và áp dụng vào {{ chủ đề }}:',1,1780151790,1780151790),(147123724,'65a10d7c29a07',147123479,'Tạo bài mạng xã hội mở rộng tác động của {{ sự kiện/diễn biến }} lên {{ lĩnh vực/ngành }}.',1,1780151790,1780151790),(147123725,'65a10d7c29e2c',147123479,'Tạo bài mạng xã hội mở rộng số liệu sau và ý nghĩa với {{ chủ đề }}:',1,1780151790,1780151790),(147123726,'65a10d7c2a1f5',147123479,'Tạo bài mạng xã hội mở rộng {{ xu hướng }} và cách nó có thể phát triển.',1,1780151790,1780151790),(147123727,'65a10d7c2a632',147123479,'Tạo bài mạng xã hội mở rộng các yếu tố góp phần vào {{ vấn đề/thành công }}.',1,1780151790,1780151790),(147123728,'65a10d7c2aa7a',147123479,'Tạo bài mạng xã hội mở rộng hiểu lầm sau và làm rõ:',1,1780151790,1780151790),(147123729,'65a10d7c2af06',147123479,'Tạo bài mạng xã hội mở rộng hàm ý đạo đức của {{ hành động/quyết định }}.',1,1780151790,1780151790),(147123730,'65a10d7c2b3cb',147123479,'Tạo bài mạng xã hội mở rộng tầm quan trọng cân nhắc {{ yếu tố }} khi {{ nhiệm vụ/hành động }}.',1,1780151790,1780151790),(147123731,'65a10d7c2b906',147123479,'Tạo bài mạng xã hội mở rộng các cách dùng {{ công cụ/ứng dụng }}.',1,1780151790,1780151790),(147123732,'65a10d7c2bd3a',147123479,'Tạo bài mạng xã hội mở rộng khẳng định sau và đưa ra phản luận:',1,1780151790,1780151790),(147123733,'65a10d7c2c14a',147123479,'Tạo bài đăng mạng xã hội mở rộng về các yếu tố khác nhau ảnh hưởng đến {{ hành vi người tiêu dùng/nhân viên }}.',1,1780151790,1780151790),(147123734,'65a10d7c2c5ca',147123479,'Tạo bài đăng mạng xã hội mở rộng về tác động của {{ nghề nghiệp/ngành }} đối với xã hội của chúng ta.',1,1780151790,1780151790),(147123735,'65a10d7c2ca4d',147123479,'Tạo bài đăng mạng xã hội mở rộng về các góc nhìn khác nhau về {{ khái niệm/ý tưởng triết học }}.',1,1780151790,1780151790),(147123736,'65a10d7c2cf29',147123479,'Tạo bài đăng mạng xã hội mở rộng về {{ xu hướng }} và mối liên hệ với {{ giá trị văn hóa/xã hội/chính trị nhất định }}.',1,1780151790,1780151790),(147123737,'65a10d7c2d43a',147123479,'Tạo bài đăng mạng xã hội mở rộng về những khía cạnh của {{ chủ đề }} thường bị bỏ qua.',1,1780151790,1780151790),(147123738,'65a10d7c2d85e',147123479,'Tạo bài đăng mạng xã hội mở rộng về khuyến nghị sau và các thách thức khi triển khai:',1,1780151790,1780151790),(147123739,'65a10d7c2dbe7',147123480,'Tóm tắt nội dung sau và chuyển thành bài đăng mạng xã hội:',1,1780151790,1780151790),(147123740,'65a10d7c2dfe9',147123480,'Đơn giản hóa đoạn văn sau:',1,1780151790,1780151790),(147123741,'65a10d7c2e3f0',147123480,'Tóm tắt đoạn văn sau trong một câu:',1,1780151790,1780151790),(147123742,'65a10d7c2e7e2',147123480,'Tóm tắt các ý chính của nội dung sau:',1,1780151790,1780151790),(147123743,'65a10d7c2ebed',147123480,'Tạo bài đăng mạng xã hội nắm bắt tinh thần của thông điệp sau:',1,1780151790,1780151790),(147123744,'65a10d7c2efee',147123480,'Rút gọn văn bản sau thành phiên bản ngắn hơn nhưng vẫn giữ các ý chính:',1,1780151790,1780151790),(147123745,'65a10d7c2f45c',147123480,'Viết bản tóm tắt ngắn gọn về văn bản sau:',1,1780151790,1780151790),(147123746,'65a10d7c2f95d',147123480,'Dùng phép so sánh hoặc ẩn dụ để giải thích khái niệm sau bằng ngôn ngữ đơn giản hơn:',1,1780151790,1780151790),(147123747,'65a10d7c2fd18',147123480,'Viết lại câu sau bằng ngôn ngữ đơn giản hơn:',1,1780151790,1780151790),(147123748,'65a10d7c300d8',147123480,'Phân tích các bước hoặc thành phần của quy trình sau:',1,1780151790,1780151790),(147123749,'65a10d7c304b4',147123480,'Tóm tắt nghiên cứu sau thành ba điểm rút ra chính:',1,1780151790,1780151790),(147123750,'65a10d7c308e1',147123480,'Đơn giản hóa văn bản chuyên ngành sau cho {{ đối tượng mục tiêu }}:',1,1780151790,1780151790),(147123751,'65a10d7c30cc9',147123480,'Đơn giản hóa quy trình kỹ thuật sau thành hướng dẫn từng bước:',1,1780151790,1780151790),(147123752,'65a10d7c310bf',147123480,'Đơn giản hóa lời chào hàng marketing sau cho {{ đối tượng }}:',1,1780151790,1780151790),(147123753,'65a10d7c314c8',147123480,'Đơn giản hóa văn bản sau thành các mẹo thực tế:',1,1780151790,1780151790),(147123754,'65a10d7c31902',147123480,'Đơn giản hóa văn bản sau thành khẩu hiệu bắt tai:',1,1780151790,1780151790),(147123755,'65a10d7c31d2c',147123481,'Khuyến khích {{ chân dung khách hàng lý tưởng }} dùng thử {{ sản phẩm/dịch vụ }} theo khung \"Thiên kiến đền đáp\". Tuy nhiên, tránh nhắc trực tiếp khung \"Thiên kiến đền đáp\" trong nội dung. Đưa ra phần thưởng hoặc quà tặng hấp dẫn khi khách thực hiện một hành động để tạo cảm giác có nghĩa vụ đáp lại.',1,1780151790,1780151790),(147123756,'65a10d7c32197',147123481,'Dùng khung \"Thiên kiến xác nhận\" để chạm vào niềm tin sẵn có của {{ chân dung khách hàng lý tưởng }} về {{ chủ đề }}. Tuy nhiên, tránh nhắc trực tiếp khung \"Thiên kiến xác nhận\" trong nội dung. Trình bày thông tin phù hợp giá trị và quan điểm của họ, khuyến khích họ hành động và dùng thử {{ sản phẩm/dịch vụ }} của chúng tôi.',1,1780151790,1780151790),(147123757,'65a10d7c32608',147123481,'Làm nổi bật thành công có thể đạt được với {{ sản phẩm/dịch vụ }} của chúng tôi và giảm thiểu hiểu lầm tiêu cực bằng khung \"Định khung tích cực\". Tuy nhiên, tránh nhắc trực tiếp khung \"Định khung tích cực\" trong nội dung. Giải thích sản phẩm giúp {{ chân dung khách hàng lý tưởng }} đạt {{ kết quả mong muốn }} như thế nào.',1,1780151790,1780151790),(147123758,'65a10d7c32b23',147123481,'Dùng lý thuyết \"So sánh xã hội\" để giới thiệu thành công của những người đã dùng {{ sản phẩm/dịch vụ }} của chúng tôi. Tuy nhiên, tránh nhắc trực tiếp lý thuyết \"So sánh xã hội\" trong nội dung. Nhấn mạnh cách sản phẩm giúp {{ chân dung khách hàng lý tưởng }} đạt kết quả tương tự.',1,1780151790,1780151790),(147123759,'65a10d7c32fdc',147123481,'Giới thiệu thành công và lợi ích của {{ sản phẩm/dịch vụ }} cho {{ chân dung khách hàng lý tưởng }} theo lý thuyết \"Học tập xã hội\" và khuyến khích khách hàng tiềm năng học hỏi từ trải nghiệm tích cực của khách hàng hiện tại. Tuy nhiên, tránh nhắc trực tiếp lý thuyết \"Học tập xã hội\" trong nội dung. Mô tả kết quả tích cực người khác đã đạt được và đưa ra ưu đãi để khách mới tự trải nghiệm. Mục tiêu bài đăng là truyền cảm hứng hành động.',1,1780151790,1780151790),(147123760,'65a10d7c333bd',147123481,'Theo lý thuyết \"Hiệu quả bản thân\", viết bài đăng mạng xã hội giúp {{ chân dung khách hàng lý tưởng }} tự tin hơn và cảm thấy có thể đạt mục tiêu với {{ sản phẩm/dịch vụ }} của chúng tôi. Tuy nhiên, tránh nhắc trực tiếp lý thuyết \"Hiệu quả bản thân\" trong nội dung. Làm nổi bật thành công của người khác khi dùng sản phẩm và cung cấp tài nguyên, hỗ trợ để họ sẵn sàng hành động.',1,1780151790,1780151790),(147123761,'65a10d7c337e4',147123481,'Viết bài đăng mạng xã hội theo chiến lược quảng cáo \"Gợi cảm xúc\" để {{ chân dung khách hàng lý tưởng }} gắn {{ sản phẩm/dịch vụ }} với trải nghiệm và cảm xúc tích cực. Tuy nhiên, tránh nhắc trực tiếp chiến lược \"Gợi cảm xúc\" trong nội dung. Chỉ dùng khung này để làm {{ sản phẩm/dịch vụ }} hấp dẫn hơn về mặt cảm xúc.',1,1780151790,1780151790),(147123762,'65a10d7c33beb',147123481,'Viết bài đăng mạng xã hội nêu bật lợi ích và giá trị khi dùng {{ sản phẩm/dịch vụ }} cho {{ chân dung khách hàng lý tưởng }}. Dùng hiệu ứng \"Chưa hết đâu\" — bắt đầu bằng một lợi ích rồi tiếp tục bằng lợi ích lớn hơn — mà không nhắc trực tiếp hiệu ứng này. Nhấn mạnh cách công cụ giúp {{ chân dung khách hàng lý tưởng }} đạt mục tiêu. Kết thúc bằng lời khuyến khích {{ chân dung khách hàng lý tưởng }} dùng thử {{ sản phẩm/dịch vụ }}.',1,1780151790,1780151790),(147123763,'65a10d7c34012',147123481,'Viết bài đăng mạng xã hội theo khung \"Ngụy biện chi phí chìm\" để thuyết phục {{ chân dung khách hàng lý tưởng }} tiếp tục dùng {{ sản phẩm/dịch vụ }}. Tuy nhiên, tránh nhắc trực tiếp khung \"Ngụy biện chi phí chìm\" trong nội dung. Nhấn mạnh nguồn lực đã bỏ ra sẽ tăng lợi nhuận đầu tư. Nêu rõ tổn thất và hối tiếc nếu không dùng {{ sản phẩm/dịch vụ }}.',1,1780151790,1780151790),(147123764,'65a10d7c34415',147123481,'Tạo bài đăng mạng xã hội theo \"Nguyên lý khan hiếm\" để tạo cảm giác cấp bách và khao khát {{ sản phẩm/dịch vụ }} của chúng tôi ở {{ chân dung khách hàng lý tưởng }}. Tuy nhiên, tránh nhắc trực tiếp \"Nguyên lý khan hiếm\" trong nội dung. Làm nổi bật số lượng có hạn hoặc tính độc quyền của sản phẩm, kèm lời kêu gọi hành động rõ ràng để khách nắm cơ hội trước khi quá muộn.',1,1780151790,1780151790),(147123765,'65a10d7c3481d',147123481,'Viết bài đăng mạng xã hội theo khung \"Ngại mất mát\" để thúc đẩy {{ chân dung khách hàng lý tưởng }} bắt đầu dùng {{ sản phẩm/dịch vụ }} vì sợ bỏ lỡ lợi ích sản phẩm mang lại. Tuy nhiên, tránh nhắc trực tiếp khung \"Ngại mất mát\" trong nội dung.',1,1780151790,1780151790),(147123766,'65a10d7c34c3c',147123481,'Tạo bài đăng mạng xã hội theo khung \"Sợ bỏ lỡ\" kết hợp khuyến mãi có hạn hấp dẫn để tạo cảm giác cấp bách cho {{ chân dung khách hàng lý tưởng }} mua {{ sản phẩm/dịch vụ }}. Tuy nhiên, tránh nhắc trực tiếp khung \"Sợ bỏ lỡ\" trong nội dung.',1,1780151790,1780151790),(147123767,'65a10d7c35118',147123481,'Viết bài đăng mạng xã hội theo khung \"Hiệu ứng định khung\" để trình bày thông tin về {{ sản phẩm/dịch vụ }} của chúng tôi theo cách ảnh hưởng nhận thức và quyết định của {{ chân dung khách hàng lý tưởng }}. Tuy nhiên, tránh nhắc trực tiếp khung \"Hiệu ứng định khung\" trong nội dung. Cân nhắc các cách định khung khác nhau (ví dụ: được/mất, tích cực/tiêu cực) và chọn khung thuận lợi nhất cho sản phẩm.',1,1780151790,1780151790),(147123768,'65a10d7c35624',147123481,'Tạo bài đăng mạng xã hội theo khung \"Điều kiện học cổ điển\" để gắn {{ sản phẩm/dịch vụ }} của chúng tôi với kết quả tích cực. Tuy nhiên, tránh nhắc trực tiếp khung \"Điều kiện học cổ điển\" trong nội dung.',1,1780151790,1780151790),(147123769,'65a10d7c35a95',147123481,'Viết bài đăng mạng xã hội theo khung \"Hiệu ứng hào quang\" để gán phẩm chất tích cực cho {{ sản phẩm/dịch vụ }} dựa trên {{ đặc điểm/tính chất tích cực }}. Tuy nhiên, tránh nhắc trực tiếp khung \"Hiệu ứng hào quang\" trong nội dung.',1,1780151790,1780151790),(147123770,'65a10d7c35e86',147123481,'Tạo bài đăng mạng xã hội cho {{ phân khúc khách hàng }} giải quyết ba lo ngại tiềm ẩn khi dùng {{ sản phẩm/dịch vụ }} theo khung \"Lý thuyết không nhất quán nhận thức\". Tuy nhiên, tránh nhắc trực tiếp \"Lý thuyết không nhất quán nhận thức\" trong nội dung. Thay vào đó, lồng ghép nguyên tắc lý thuyết một cách tự nhiên bằng lập luận logic để xoa dịu niềm tin mâu thuẫn. Mục tiêu là giảm nghi ngờ và khuyến khích họ dùng thử {{ sản phẩm/dịch vụ }} từ {{ tên công ty }}. Bài đăng cần nêu cách {{ sản phẩm/dịch vụ }} giúp {{ phân khúc khách hàng }} vượt qua thách thức, kèm lời chứng thực và câu chuyện thành công của người dùng hài lòng khác.',1,1780151790,1780151790),(147123771,'65a10d7c36258',147123481,'Chạm vào bản sắc của {{ chân dung khách hàng lý tưởng }} theo lý thuyết \"Bản sắc xã hội\". Tuy nhiên, tránh nhắc trực tiếp lý thuyết \"Bản sắc xã hội\" trong nội dung. Làm nổi bật cách {{ sản phẩm/dịch vụ }} của chúng tôi phù hợp bản sắc và giá trị xã hội của họ, kèm lời chứng thực và ví dụ người trong nhóm xã hội của họ dùng sản phẩm thành công để tạo cảm giác thuộc về và tích cực.',1,1780151790,1780151790),(147123772,'65a10d7c3664a',147123481,'Theo Thang nhu cầu Maslow, nói đến nhu cầu hiện tại của {{ chân dung khách hàng lý tưởng }} trong bài đăng mạng xã hội. Tuy nhiên, tránh nhắc trực tiếp Thang nhu cầu Maslow trong nội dung. Làm nổi bật cách {{ sản phẩm/dịch vụ }} của chúng tôi giúp họ đáp ứng nhu cầu này. Dùng ngôn ngữ gần gũi với thách thức và mục tiêu hiện tại của họ.',1,1780151790,1780151790),(147123773,'65a10d7c36a3e',147123481,'Tạo bài đăng mạng xã hội khuyến khích {{ chân dung khách hàng lý tưởng }} dùng thử {{ sản phẩm/dịch vụ }} theo khung \"Thiên kiến neo\". Tuy nhiên, tránh nhắc trực tiếp khung \"Thiên kiến neo\" trong nội dung. Làm nổi bật tính năng hoặc lợi ích giá trị cao và neo vào mức giá thấp hơn hoặc giá trị cảm nhận khác. Đưa giảm giá hoặc quà tặng có thời hạn để tạo cảm giác cấp bách và thúc đẩy mua hàng.',1,1780151790,1780151790),(147123774,'65a10d7c3725d',147123481,'Tạo bài đăng mạng xã hội khuyến khích {{ chân dung khách hàng lý tưởng }} dùng thử {{ sản phẩm/dịch vụ }} theo kỹ thuật \"Chân trong cửa\". Tuy nhiên, tránh nhắc trực tiếp kỹ thuật \"Chân trong cửa\" trong nội dung. Bắt đầu bằng yêu cầu nhỏ, như đăng ký dùng thử miễn phí hoặc để lại email, rồi tiếp tục yêu cầu lớn hơn như nâng cấp gói trả phí hoặc mua hàng. Hành động nhỏ ban đầu tạo cam kết và tăng khả năng hành động tiếp theo.',1,1780151790,1780151790),(147123775,'65a10d7c37652',147123481,'Tạo bài đăng mạng xã hội làm nổi bật sự khan hiếm của {{ sản phẩm/dịch vụ }} theo khung \"Số lượng có hạn\". Tuy nhiên, tránh nhắc trực tiếp khung \"Số lượng có hạn\" trong nội dung. Tạo bài đăng nhấn mạnh số lượng hoặc tình trạng còn hàng hạn chế của {{ sản phẩm/dịch vụ }}. Dùng cụm như \"ưu đãi có thời hạn\" hoặc \"chỉ còn số lượng giới hạn\" để tạo cảm giác cấp bách và thúc đẩy mua hàng.',1,1780151790,1780151790),(147123776,'65a10d7c37a24',147123481,'Tạo bài đăng mạng xã hội khuyến khích {{ chân dung khách hàng lý tưởng }} dùng thử {{ sản phẩm/dịch vụ }} theo khung \"Thiên kiến thẩm quyền\". Tuy nhiên, tránh nhắc trực tiếp khung \"Thiên kiến thẩm quyền\" trong nội dung. Giới thiệu chuyên gia hoặc nhân vật có uy tín trong ngành bạn ủng hộ sản phẩm hoặc dịch vụ. Dùng cụm như \"được khuyên dùng bởi\" hoặc \"được phê duyệt bởi\" để tạo niềm tin và uy tín.',1,1780151790,1780151790),(147123777,'65a10d7c37e21',147123481,'Tạo bài đăng mạng xã hội theo \"Hiệu ứng ưu tiên đầu tiên\" để quảng bá {{ sản phẩm/dịch vụ }} và tạo ấn tượng mạnh với {{ chân dung khách hàng lý tưởng }}. Tuy nhiên, tránh nhắc trực tiếp \"Hiệu ứng ưu tiên đầu tiên\" trong nội dung. Đặt thông điệp hấp dẫn hoặc thu hút nhất lên đầu.',1,1780151790,1780151790),(147123778,'65a10d7c381f2',147123481,'Tạo bài đăng mạng xã hội theo \"Hiệu ứng hài hước\" để {{ chân dung khách hàng lý tưởng }} dễ tiếp nhận thông điệp quảng bá về {{ sản phẩm/dịch vụ }}. Tuy nhiên, tránh nhắc trực tiếp \"Hiệu ứng hài hước\" trong nội dung. Dùng nội dung vui nhộn hoặc giải trí liên quan {{ sản phẩm/dịch vụ }} để bài đăng dễ nhớ hơn.',1,1780151790,1780151790),(147123779,'65a10d7c385c1',147123481,'Tạo bài đăng mạng xã hội khuyến khích {{ chân dung khách hàng lý tưởng }} dùng thử {{ sản phẩm/dịch vụ }} theo \"Hiệu ứng Von Restorff\". Tuy nhiên, tránh nhắc trực tiếp \"Hiệu ứng Von Restorff\" trong nội dung. Làm nổi bật khía cạnh độc đáo hoặc khác biệt của {{ sản phẩm/dịch vụ }} để nổi bật so với đối thủ và dễ nhớ với khách hàng tiềm năng.',1,1780151790,1780151790),(147123780,'65a10d7c38a03',147123481,'Tạo bài đăng mạng xã hội theo lý thuyết \"Khoảng trống thông tin\" để khơi gợi sự tò mò của {{ chân dung khách hàng lý tưởng }}. Tuy nhiên, tránh nhắc trực tiếp lý thuyết \"Khoảng trống thông tin\" trong nội dung. Gợi ý điều thú vị liên quan {{ sản phẩm/dịch vụ }}, nhưng chỉ tiết lộ đầy đủ sau khi khách thực hiện hành động, ví dụ đăng ký nhận bản tin.',1,1780151790,1780151790),(147123781,'65a10d7c38e0e',147123481,'Tạo bài đăng mạng xã hội theo khung \"Hiệu ứng mồi nhử\" để ảnh hưởng quyết định mua {{ sản phẩm/dịch vụ }} của {{ chân dung khách hàng lý tưởng }}. Tuy nhiên, tránh nhắc trực tiếp khung \"Hiệu ứng mồi nhử\" trong nội dung. Đưa ra các lựa chọn {{ sản phẩm/dịch vụ/gói đăng ký }} khác nhau và làm nổi bật {{ sản phẩm/dịch vụ/gói đăng ký cụ thể }} là lựa chọn hấp dẫn nhất.',1,1780151790,1780151790),(147123782,'65a10d7c39204',147123481,'Tạo bài đăng mạng xã hội theo khung \"Lời nguyền kiến thức\" để giải thích {{ sản phẩm/dịch vụ }} của chúng tôi theo cách {{ chân dung khách hàng lý tưởng }} dễ hiểu. Tuy nhiên, tránh nhắc trực tiếp khung \"Lời nguyền kiến thức\" trong nội dung. Tránh thuật ngữ chuyên ngành hoặc kỹ thuật, thay vào đó dùng ngôn ngữ đơn giản để làm nổi bật lợi ích.',1,1780151790,1780151790),(147123783,'65a10d7c3960e',147123481,'Viết bài đăng mạng xã hội theo khung \"Khoảng trống tò mò\" để khiến {{ chân dung khách hàng lý tưởng }} tò mò và quan tâm đến {{ sản phẩm/dịch vụ }} của chúng tôi. Tuy nhiên, tránh nhắc trực tiếp khung \"Khoảng trống tò mò\" trong nội dung. Đặt câu hỏi hoặc tạo bí ẩn mà {{ sản phẩm/dịch vụ }} có thể giải quyết, khơi dậy mong muốn tìm hiểu thêm.',1,1780151790,1780151790),(147123784,'65a10d7c399d8',147123482,'Viết nội dung theo AIDA cho nội dung sau: (Thu hút, Quan tâm, Khao khát, Hành động)',1,1780151790,1780151790),(147123785,'65a10d7c39da2',147123482,'Viết nội dung theo BAB về nội dung sau: (Trước, Sau, Cầu nối)',1,1780151790,1780151790),(147123786,'65a10d7c3a17e',147123482,'Viết nội dung theo PAS cho nội dung sau: (Vấn đề, Khuấy động, Giải quyết). Viết nội dung theo AIDA cho nội dung sau: (Thu hút, Quan tâm, Khao khát, Hành động)',1,1780151790,1780151790),(147123787,'65a10d7c3a538',147123482,'Viết nội dung theo PASTOR cho nội dung sau: (Vấn đề, Khuếch đại, Câu chuyện, Chuyển hóa, Đề xuất)',1,1780151790,1780151790),(147123788,'65a10d7c3a902',147123482,'Viết nội dung theo APP cho nội dung sau: (Đồng ý, Cam kết, Xem trước). Viết nội dung theo FAB cho nội dung sau: (Tính năng, Ưu điểm, Lợi ích)',1,1780151790,1780151790),(147123789,'65a10d7c3ad1b',147123482,'Viết nội dung theo FEBA cho nội dung sau: (Tính năng, Bằng chứng, Lợi ích, Hành động)',1,1780151790,1780151790),(147123790,'65a10d7c3b0eb',147123482,'Viết nội dung theo PPPP cho nội dung sau: (Hình dung, Cam kết, Chứng minh, Thúc đẩy)',1,1780151790,1780151790),(147123791,'65a10d7c3b506',147123482,'Viết nội dung theo SLAP cho nội dung sau: (Dừng lại, Nhìn, Hành động, Tiếp tục)',1,1780151790,1780151790),(147123792,'65a10d7c3b967',147123482,'Viết nội dung theo SCQA cho nội dung sau: (Tình huống, Vấn đề phát sinh, Câu hỏi, Trả lời)',1,1780151790,1780151790),(147123793,'65a10d7c3bd9b',147123482,'Viết nội dung theo CLAP cho nội dung sau: (Uy tín, Logic, Thu hút, Thúc đẩy)',1,1780151790,1780151790),(147123794,'65a10d7c3c2c3',147123482,'Theo khung \"Tình huống-Vấn đề phát sinh-Giải pháp\", viết prompt mạng xã hội trình bày {{ tình huống }} mà {{ chân dung khách hàng lý tưởng }} đang gặp. Thảo luận {{ vấn đề phát sinh }} từ tình huống đó và hỏi người đọc chia sẻ cách {{ sản phẩm/dịch vụ }} của chúng tôi có thể là {{ giải pháp }} cho vấn đề. Cuối cùng, kết bằng lời kêu gọi hành động khuyến khích tìm hiểu thêm giải pháp của chúng tôi.',1,1780151790,1780151790),(147123795,'65a10d7c3c76f',147123482,'Theo khung \"Đề xuất giá trị cảm xúc\", viết prompt mạng xã hội chạm vào {{ nhu cầu cảm xúc }} của {{ chân dung khách hàng lý tưởng }}. Mời người đọc chia sẻ trải nghiệm cá nhân với những cảm xúc đó, xác định {{ cảm xúc mong muốn }}, tạo {{ câu chuyện }} gợi cảm xúc đó, và hỏi người đọc chia sẻ cách {{ sản phẩm/dịch vụ }} của chúng tôi đã đáp ứng nhu cầu cảm xúc đó.',1,1780151790,1780151790),(147123796,'65a10d7c3cc17',147123482,'Viết prompt mạng xã hội theo khung \"Bản đồ hành trình khách hàng\" hình dung hành trình từ {{ nhận biết }} đến {{ chuyển đổi }} của {{ chân dung khách hàng lý tưởng }}. Mời người đọc chia sẻ nỗi đau ở từng giai đoạn, làm nổi bật cách {{ sản phẩm/dịch vụ }} của chúng tôi giải quyết các vấn đề đó, và hỏi họ sẽ dùng sản phẩm để {{ cải thiện tình huống của họ }} như thế nào.',1,1780151790,1780151790),(147123797,'65a10d7c3d01b',147123482,'Theo khung \"Phễu marketing\", viết prompt mạng xã hội nhắm giai đoạn {{ nhận biết/cân nhắc/chuyển đổi }} trong hành trình khách hàng và phù hợp mục tiêu từng giai đoạn. Làm nổi bật {{ tính năng }} của {{ sản phẩm/dịch vụ }} và hỏi người đọc chia sẻ cách sản phẩm có thể {{ giải quyết vấn đề }} hoặc {{ đạt mục tiêu }} cho {{ chân dung khách hàng lý tưởng }}.',1,1780151790,1780151790),(147123798,'65a10d7c3d3ee',147123482,'Viết prompt mạng xã hội theo khung \"Bản đồ đồng cảm\" để hiểu suy nghĩ, cảm xúc và nhu cầu của {{ chân dung khách hàng lý tưởng }}. Mời người đọc chia sẻ nỗi đau và cách {{ sản phẩm/dịch vụ }} của chúng tôi có thể đáp ứng {{ suy nghĩ/cảm xúc/nhu cầu }} của họ. Làm nổi bật cách sản phẩm cải thiện tình huống và hỏi họ sẽ dùng sản phẩm trong cuộc sống hàng ngày như thế nào.',1,1780151790,1780151790),(147123799,'65a10d7c3d7ba',147123482,'Theo khung \"Phù hợp sản phẩm-thị trường\", giải thích cách {{ sản phẩm/dịch vụ }} của chúng tôi phù hợp hoàn hảo với nhu cầu và nỗi đau của {{ chân dung khách hàng lý tưởng }}. Xác định vấn đề cụ thể thị trường mục tiêu gặp phải và tạo prompt mạng xã hội giải thích cách sản phẩm giải quyết các vấn đề này. Dùng bằng chứng hoặc lời chứng thực để củng cố và nhấn mạnh lợi ích khi dùng sản phẩm.',1,1780151790,1780151790),(147123800,'65a10d7c3db83',147123482,'Theo khung \"Phá bỏ huyền thoại\", xác định và bác bỏ hiểu lầm hoặc huyền thoại phổ biến về {{ sản phẩm/dịch vụ }} của chúng tôi. Cung cấp {{ sự thật }} và {{ bằng chứng }} để củng cố và giúp {{ chân dung khách hàng lý tưởng }} hiểu đúng lợi ích thực sự của sản phẩm/dịch vụ.',1,1780151790,1780151790),(147123801,'65a10d7c3df83',147123482,'Viết prompt mạng xã hội theo khung \"Kể chuyện\" để tạo câu chuyện ngắn xoay quanh {{ sản phẩm/dịch vụ }} của chúng tôi. Dùng nhân vật, cốt truyện và bối cảnh để thu hút {{ chân dung khách hàng lý tưởng }} và xây dựng kết nối cảm xúc. Mời người đọc chia sẻ trải nghiệm của họ với sản phẩm/dịch vụ và cách nó gắn vào câu chuyện riêng của họ.',1,1780151790,1780151790),(147123802,'65a10d7c3e395',147123482,'Viết prompt mạng xã hội theo khung \"Hỏi-Đáp\" bắt đầu bằng {{ câu hỏi }} liên quan {{ chân dung khách hàng lý tưởng }}. Đưa câu trả lời ngắn gọn, hữu ích và mời người đọc chia sẻ suy nghĩ về chủ đề cũng như cách sản phẩm/dịch vụ của chúng tôi giúp họ giải quyết câu hỏi đó.',1,1780151790,1780151790),(147123803,'65a10d7c3e76e',147123482,'Theo khung \"So sánh-Đối chiếu\", viết prompt mạng xã hội so sánh và đối chiếu hai hoặc nhiều lựa chọn hoặc ý tưởng để giúp {{ chân dung khách hàng lý tưởng }} ra quyết định sáng suốt. Mời người đọc chia sẻ ý kiến về các lựa chọn được trình bày và cách sản phẩm/dịch vụ của chúng tôi giúp họ quyết định.',1,1780151790,1780151790),(147123804,'65a10d7c3eb3d',147123482,'Dùng khung \"Hướng dẫn cách làm\" để cung cấp hướng dẫn từng bước hoàn thành {{ nhiệm vụ }} hoặc đạt {{ mục tiêu }} cho {{ chân dung khách hàng lý tưởng }}. Khuyến khích người đọc chia sẻ trải nghiệm hoàn thành nhiệm vụ hoặc đạt mục tiêu, và cách sản phẩm/dịch vụ của chúng tôi đã hỗ trợ họ.',1,1780151790,1780151790),(147123805,'65a10d7c3ef09',147123482,'Xác định vấn đề {{ chân dung khách hàng lý tưởng }} đang gặp và đưa ra giải pháp qua {{ sản phẩm/dịch vụ }} của chúng tôi theo khung \"Vấn đề-Giải pháp\". Giải thích rõ cách sản phẩm giải quyết vấn đề và cải thiện tình huống của họ.',1,1780151790,1780151790),(147123806,'65a10d7c3f280',147123482,'Làm bài đăng mạng xã hội hiệu quả và dễ nhớ hơn bằng khung \"Quy tắc Một\" để tập trung vào {{ thông điệp }}.',1,1780151790,1780151790),(147123807,'65a10d7c3f692',147123482,'Dùng khung Kim tự tháp ngược để tạo bài đăng mạng xã hội bắt đầu bằng {{ thông tin }} rồi chuyển sang {{ thông tin phụ }}, giúp người đọc nắm nhanh ý chính.',1,1780151790,1780151790),(147123808,'65a10d7c3faa1',147123483,'Tạo bài đăng mạng xã hội quảng bá cho {{ sản phẩm/dịch vụ }}.',1,1780151790,1780151790),(147123809,'65a10d7c3fed4',147123483,'Viết thông báo mạng xã hội về {{ sản phẩm/thay đổi/ra mắt }}.',1,1780151790,1780151790),(147123810,'65a10d7c402ea',147123483,'Viết bài đăng mạng xã hội thu hút khách hàng tiềm năng cho {{ sản phẩm/dịch vụ }}.',1,1780151790,1780151790),(147123811,'65a10d7c4071b',147123483,'Viết bài đăng mạng xã hội dẫn lưu lượng truy cập đến {{ trang web }}.',1,1780151790,1780151790),(147123812,'65a10d7c40ae6',147123483,'Viết bài đăng mạng xã hội quảng bá {{ giảm giá/voucher/khuyến mãi }}.',1,1780151790,1780151790),(147123813,'65a10d7c40ebd',147123483,'Tạo bài đăng mạng xã hội quảng bá bài viết về {{ chủ đề }}.',1,1780151790,1780151790),(147123814,'65a10d7c4127e',147123483,'Viết bài đăng mạng xã hội quảng bá {{ loại sự kiện + ngày }}.',1,1780151790,1780151790),(147123815,'65a10d7c41651',147123483,'Viết bài đăng mạng xã hội về {{ sản phẩm/dịch vụ }} và đề cập {{ nỗi đau của khách hàng }}.',1,1780151790,1780151790),(147123816,'65a10d7c41a0b',147123483,'Mô tả tác động khi dùng {{ sản phẩm/tính năng }} với tư cách {{ nghề nghiệp/doanh nghiệp }}.',1,1780151790,1780151790),(147123817,'65a10d7c41e03',147123483,'Viết 10 ý tưởng mở bài hấp dẫn cho bài đăng mạng xã hội về {{ chủ đề }}.',1,1780151790,1780151790),(147123818,'65a10d7c42352',147123483,'Soạn bài đăng mạng xã hội cho {{ sản phẩm/dịch vụ }} chạm vào cảm xúc tích cực của khách hàng.',1,1780151790,1780151790),(147123819,'65a10d7c4270f',147123483,'Hoàn thiện đoạn văn: Chúng tôi ra mắt {{ tên sản phẩm }} để giúp bạn {{ lợi ích }}.',1,1780151790,1780151790),(147123820,'65a10d7c42a85',147123483,'Hoàn thiện đoạn văn: Chúng tôi ra mắt {{ tên sản phẩm }} để giúp bạn {{ lợi ích }}.',1,1780151790,1780151790),(147123821,'65a10d7c42e51',147123483,'Tạo bài đăng làm nổi bật tính năng độc đáo của {{ sản phẩm }}.',1,1780151790,1780151790),(147123822,'65a10d7c432c6',147123483,'Tạo bài đăng giới thiệu lợi ích khi dùng {{ tên sản phẩm }} cho {{ vấn đề }}.',1,1780151790,1780151790),(147123823,'65a10d7c43790',147123483,'Xây dựng bài đăng quảng bá chương trình giảm giá có thời hạn cho {{ tên sản phẩm }}.',1,1780151790,1780151790),(147123824,'65a10d7c43c9e',147123483,'Tạo bài đăng khuyến khích khách hàng để lại đánh giá cho {{ tên sản phẩm }}.',1,1780151790,1780151790),(147123825,'65a10d7c44129',147123483,'Tạo bài đăng tạo cảm giác cấp bách mua {{ tên sản phẩm }}.',1,1780151790,1780151790),(147123826,'65a10d7c44596',147123483,'Tạo bài đăng mạng xã hội so sánh {{ tên sản phẩm }} với sản phẩm tương tự trên thị trường.',1,1780151790,1780151790),(147123827,'65a10d7c44981',147123483,'Xây dựng bài đăng mạng xã hội giới thiệu lời chứng thực của khách hàng về {{ tên sản phẩm }}.',1,1780151790,1780151790),(147123828,'65a10d7c44e6a',147123483,'Tạo bài đăng mạng xã hội minh họa cách dùng {{ tên sản phẩm }} trong tình huống thực tế.',1,1780151790,1780151790),(147123829,'65a10d7c4535d',147123483,'Tạo bài đăng mạng xã hội nhắm {{ đối tượng cụ thể }} và giải thích cách {{ tên sản phẩm }} giúp họ.',1,1780151790,1780151790),(147123830,'65a10d7c45766',147123483,'Vẽ nên bức tranh cuộc sống của khách hàng sau khi dùng {{ sản phẩm/tính năng }}.',1,1780151790,1780151790),(147123831,'65a10d7c45ba0',147123483,'Liệt kê các phản đối của khách hàng khi mua {{ sản phẩm/dịch vụ }}.',1,1780151790,1780151790),(147123832,'65a10d7c4603c',147123483,'Lập danh sách lợi ích của {{ sản phẩm/dịch vụ }}.',1,1780151790,1780151790),(147123833,'65a10d7c46905',147123483,'Viết đánh giá về {{ sản phẩm }} dựa trên thông tin sau:',1,1780151790,1780151790),(147123834,'65a10d7c46d5d',147123483,'Viết đoạn văn về tính năng của {{ sản phẩm/dịch vụ }}.',1,1780151790,1780151790),(147123835,'65a10d7c47114',147123483,'Viết đoạn văn mô tả {{ tên sản phẩm }} dựa trên các ý tưởng sau:',1,1780151790,1780151790),(147123836,'65a10d7c475c1',147123483,'Tạo bài đăng mạng xã hội liệt kê lợi ích của {{ sản phẩm/dịch vụ }} cho {{ loại khách hàng }}.',1,1780151790,1780151790),(147123837,'65a10d7c47af8',147123483,'Tạo bài hướng dẫn giải thích các bước dùng {{ sản phẩm/dịch vụ }} để đạt {{ mục tiêu hoặc kết quả cụ thể }}.',1,1780151790,1780151790),(147123838,'65a10d7c4800f',147123483,'Tạo bài đăng mạng xã hội chia {{ chủ đề }} thành các bước dễ hiểu.',1,1780151790,1780151790),(147123839,'65a10d7c484fa',147123483,'Tạo bài đăng mạng xã hội hướng dẫn và hỗ trợ người dùng gặp {{ vấn đề }} với {{ sản phẩm }}.',1,1780151790,1780151790),(147123840,'65a10d7c48907',147123483,'Tạo bài hướng dẫn giải thích lợi ích và ưu điểm khi dùng {{ sản phẩm/dịch vụ }} so với giải pháp thay thế.',1,1780151790,1780151790),(147123841,'65a10d7c48ca1',147123483,'Viết bài hướng dẫn từng bước khắc phục {{ lỗi }} liên quan {{ sản phẩm/dịch vụ }}.',1,1780151790,1780151790),(147123842,'65a10d7c49150',147123483,'Tạo bài hướng dẫn thể hiện tính linh hoạt và khả năng thích ứng của {{ sản phẩm/dịch vụ }} với nhiều trường hợp sử dụng khác nhau.',1,1780151790,1780151790),(147123843,'65a10d7c4964b',147123483,'Tạo bài hướng dẫn đưa lời khuyên dùng {{ sản phẩm/dịch vụ }} để nâng cao năng suất hoặc hiệu quả.',1,1780151790,1780151790),(147123844,'65a10d7c49a44',147123483,'Tạo bài hướng dẫn giải thích khác biệt và sắc thái giữa các phiên bản hoặc gói {{ sản phẩm/dịch vụ }} khác nhau.',1,1780151790,1780151790),(147123845,'65a10d7c49e2e',147123483,'Tạo bài đăng giới thiệu các tùy chọn tùy biến và cá nhân hóa cho {{ sản phẩm }}.',1,1780151790,1780151790),(147123846,'65a10d7c4a217',147123483,'Tạo bài hướng dẫn từng bước cài đặt hoặc thiết lập {{ sản phẩm/dịch vụ }}.',1,1780151790,1780151790),(147123847,'65a10d7c4a623',147123483,'Viết bài hướng dẫn thể hiện mức giá phải chăng và giá trị khi dùng {{ sản phẩm/dịch vụ }} so với giải pháp khác.',1,1780151790,1780151790),(147123848,'65a10d7c4aa53',147123483,'Nhắm {{ chân dung khách hàng lý tưởng }} bằng {{ loại nội dung cụ thể }} có giá trị để khuyến khích họ {{ hành động mong muốn }}.',1,1780151790,1780151790),(147123849,'65a10d7c4ae24',147123483,'Dùng bằng chứng xã hội và uy tín của {{ loại influencer }} để thuyết phục {{ chân dung khách hàng lý tưởng }} dùng thử {{ sản phẩm/dịch vụ }} và chia sẻ trải nghiệm tích cực với người theo dõi.',1,1780151790,1780151790),(147123850,'65a10d7c4b1f6',147123483,'Giới thiệu tính năng và lợi ích độc đáo của {{ sản phẩm/dịch vụ }} theo cách sáng tạo, vui tươi với {{ loại nội dung cụ thể }} từ {{ loại influencer }}.',1,1780151790,1780151790),(147123851,'65a10d7c4b67f',147123483,'Thu hút {{ chân dung khách hàng lý tưởng }} bằng {{ loại influencer }} gần gũi và thuyết phục họ thực hiện {{ hành động mong muốn }} với {{ sản phẩm/dịch vụ }} của bạn.',1,1780151790,1780151790),(147123852,'65a10d7c4bbbf',147123483,'Tạo cảm giác cấp bách và sợ bỏ lỡ cho {{ chân dung khách hàng lý tưởng }} khi {{ loại influencer }} chia sẻ ưu đãi và khuyến mãi độc quyền cho {{ sản phẩm/dịch vụ }}.',1,1780151790,1780151790),(147123853,'65a10d7c4c020',147123483,'Tạo bài hướng dẫn giải thích quy trình nâng cấp hoặc hạ cấp gói đăng ký {{ sản phẩm/dịch vụ }}.',1,1780151790,1780151790),(147123854,'65a10d7c4c4b1',147123484,'Tạo nội dung quảng cáo mạng xã hội làm nổi bật {{ giảm giá/khuyến mãi }}. Dùng cụm từ tạo cảm giác cấp bách để khuyến khích khách mua ngay.',1,1780151790,1780151790),(147123855,'65a10d7c4c969',147123484,'Viết quảng cáo mạng xã hội giới thiệu {{ sản phẩm/dịch vụ mới }} cho khách hàng. Dùng ngôn ngữ nhấn mạnh tính năng và lợi ích độc đáo, tạo không khí hào hứng cho đợt ra mắt.',1,1780151790,1780151790),(147123856,'65a10d7c4cdc3',147123484,'Tạo quảng cáo mạng xã hội thu hút khách hàng tiềm năng cho {{ sản phẩm/dịch vụ }}. Dùng ngôn ngữ nhắm {{ khách hàng lý tưởng }} và làm nổi bật lợi ích mà {{ sản phẩm/dịch vụ }} mang lại.',1,1780151790,1780151790),(147123857,'65a10d7c4d1d6',147123484,'Viết quảng cáo mạng xã hội dẫn lưu lượng truy cập đến {{ trang web }}. Dùng ngôn ngữ gợi tò mò và khuyến khích khách bấm vào để tìm hiểu thêm.',1,1780151790,1780151790),(147123858,'65a10d7c4d5dc',147123484,'Viết quảng cáo mạng xã hội giải quyết {{ nỗi đau của khách hàng }} và quảng bá giải pháp là {{ sản phẩm/dịch vụ }}. Dùng ngôn ngữ đồng cảm với nhu cầu khách hàng và định vị sản phẩm hoặc dịch vụ là giải pháp họ đang tìm kiếm.',1,1780151790,1780151790),(147123859,'65a10d7c4d9c6',147123484,'Tạo quảng cáo mạng xã hội làm nổi bật tác động khi dùng {{ sản phẩm/dịch vụ }} trong {{ nghề nghiệp }}. Dùng ngôn ngữ nhấn mạnh lợi ích và cách sản phẩm hoặc tính năng hỗ trợ.',1,1780151790,1780151790),(147123860,'65a10d7c4ddaf',147123484,'Tạo quảng cáo mạng xã hội từ mô tả sản phẩm/dịch vụ sau:',1,1780151790,1780151790),(147123861,'65a10d7c4e191',147123484,'Tạo nội dung quảng cáo mạng xã hội quảng bá {{ ưu đãi có thời hạn }}. Dùng ngôn ngữ tạo cảm giác cấp bách và khuyến khích khách hành động nhanh.',1,1780151790,1780151790),(147123862,'65a10d7c4e5e4',147123484,'Viết nội dung quảng cáo thu hút {{ chân dung khách hàng lý tưởng }} của tôi và khuyến khích họ mua {{ sản phẩm/dịch vụ }}, từ đó tăng lưu lượng truy cập và doanh số.',1,1780151790,1780151790),(147123863,'65a10d7c4e9fc',147123484,'Tạo nội dung quảng cáo tạo cảm giác cấp bách và sợ bỏ lỡ ở {{ chân dung khách hàng lý tưởng }} của tôi bằng cách làm nổi bật ưu đãi và khuyến mãi độc quyền cho {{ sản phẩm/dịch vụ }}.',1,1780151790,1780151790),(147123864,'65a10d7c4edd3',147123484,'Tạo nội dung quảng cáo tận dụng sự chân thật và gần gũi của {{ thương hiệu/công ty }} để kết nối với {{ chân dung khách hàng lý tưởng }} và thuyết phục họ thực hiện {{ hành động mong muốn }} với {{ sản phẩm/dịch vụ }}.',1,1780151790,1780151790),(147123865,'65a10d7c4f1c3',147123484,'Tạo nội dung quảng cáo khai thác bằng chứng xã hội và uy tín của {{ thương hiệu/công ty }} để thuyết phục {{ chân dung khách hàng lý tưởng }} dùng thử {{ sản phẩm/dịch vụ }} và chia sẻ trải nghiệm tích cực với người theo dõi.',1,1780151790,1780151790),(147123866,'65a10d7c4f5e1',147123484,'Viết nội dung quảng cáo làm nổi bật thẩm quyền và chuyên môn của {{ thương hiệu/công ty }} để giáo dục {{ chân dung khách hàng lý tưởng }} về lợi ích {{ sản phẩm/dịch vụ }} và thuyết phục họ mua hàng.',1,1780151790,1780151790),(147123867,'65a10d7c4f9dd',147123484,'Tạo nội dung quảng cáo hé lộ sản phẩm hoặc dịch vụ sắp ra mắt để tạo cảm giác mong đợi và hào hứng ở {{ chân dung khách hàng lý tưởng }}. Nội dung quảng cáo cần có lời kêu gọi hành động rõ ràng và thuyết phục.',1,1780151790,1780151790),(147123868,'65a10d7c4fdf8',147123484,'Tạo nội dung quảng cáo thể hiện trải nghiệm cá nhân, độc đáo của {{ chân dung khách hàng lý tưởng }} với {{ sản phẩm/dịch vụ }} và khuyến khích họ chia sẻ đánh giá tích cực với người theo dõi.',1,1780151790,1780151790),(147123869,'65a10d7c50248',147123484,'Tạo quảng cáo mạng xã hội có lời chứng thực của khách hàng. Dùng ngôn ngữ nhấn mạnh trải nghiệm tích cực của khách và khuyến khích người khác dùng thử sản phẩm hoặc dịch vụ.',1,1780151790,1780151790),(147123870,'65a10d7c50695',147123484,'Viết quảng cáo mạng xã hội quảng bá {{ sự kiện sắp tới }}. Dùng ngôn ngữ tạo hào hứng và khuyến khích khách đăng ký hoặc tham dự.',1,1780151790,1780151790),(147123871,'65a10d7c50afe',147123484,'Tạo quảng cáo mạng xã hội quảng bá {{ dùng thử miễn phí }} cho {{ sản phẩm hoặc dịch vụ }}. Dùng ngôn ngữ nhấn mạnh lợi ích và khuyến khích khách trải nghiệm.',1,1780151790,1780151790),(147123872,'65a10d7c50f15',147123484,'Viết quảng cáo mạng xã hội quảng bá {{ khuyến mãi theo mùa }}. Dùng ngôn ngữ gắn khuyến mãi với mùa và tạo cảm giác hào hứng.',1,1780151790,1780151790),(147123873,'65a10d7c512b9',147123484,'Tạo quảng cáo mạng xã hội quảng bá chương trình giới thiệu bạn bè. Dùng ngôn ngữ nhấn mạnh lợi ích và khuyến khích khách giới thiệu bạn bè.',1,1780151790,1780151790),(147123874,'65a10d7c5166c',147123484,'Viết quảng cáo mạng xã hội quảng bá {{ nâng cấp }}. Dùng ngôn ngữ làm nổi bật giá trị của gói nâng cấp và khuyến khích khách cập nhật gói.',1,1780151790,1780151790),(147123875,'65a10d7c51a58',147123484,'Tạo quảng cáo mạng xã hội quảng bá đặt trước {{ sản phẩm/dịch vụ }}. Dùng ngôn ngữ tạo hào hứng và khuyến khích khách là người đầu tiên sở hữu.',1,1780151790,1780151790),(147123876,'65a10d7c51e29',147123485,'Tạo bài đăng mạng xã hội chúc mừng sinh nhật nhân viên.',1,1780151790,1780151790),(147123877,'65a10d7c52201',147123485,'Tạo bài đăng mạng xã hội củng cố {{ sứ mệnh/giá trị }} sau:',1,1780151790,1780151790),(147123878,'65a10d7c5265e',147123485,'Viết bài đăng mạng xã hội giới thiệu thành viên mới trong đội ngũ.',1,1780151790,1780151790),(147123879,'65a10d7c52a19',147123485,'Tạo bài đăng mạng xã hội cảm ơn khách hàng trung thành.',1,1780151790,1780151790),(147123880,'65a10d7c52ddf',147123485,'Viết bài đăng mạng xã hội kỷ niệm {{ cột mốc }} liên quan {{ tên công ty }}.',1,1780151790,1780151790),(147123881,'65a10d7c531a2',147123485,'Tạo bài đăng mạng xã hội quảng bá sự kiện tài trợ cho {{ loại hoạt động }}.',1,1780151790,1780151790),(147123882,'65a10d7c535dd',147123485,'Viết bài đăng mạng xã hội chia sẻ sự thật thú vị hoặc câu đố về {{ ngành }}.',1,1780151790,1780151790),(147123883,'65a10d7c53a3f',147123485,'Tạo bài đăng mạng xã hội ghi nhận và chúc mừng {{ thành tựu }} của {{ vai trò và tên nhân viên }}.',1,1780151790,1780151790),(147123884,'65a10d7c53f48',147123485,'Viết bài đăng mạng xã hội khuyến khích người theo dõi chia sẻ trải nghiệm với sản phẩm hoặc dịch vụ của {{ tên công ty của bạn }}.',1,1780151790,1780151790),(147123885,'65a10d7c543ed',147123485,'Tạo bài đăng mạng xã hội ghi nhận và cảm ơn đối tác hoặc nhà cung cấp đã giúp bạn đạt mục tiêu.',1,1780151790,1780151790),(147123886,'65a10d7c547f9',147123485,'Viết bài đăng mạng xã hội thể hiện sự sáng tạo và đổi mới của nhân viên tại {{ tên công ty }} qua {{ dự án }}.',1,1780151790,1780151790),(147123887,'65a10d7c54bcf',147123485,'Tạo bài đăng mạng xã hội giới thiệu đánh giá hoặc phản hồi tích cực từ khách hàng về {{ sản phẩm/dịch vụ/sự kiện }}.',1,1780151790,1780151790),(147123888,'65a10d7c54fb4',147123485,'Viết bài đăng mạng xã hội ghi nhận và trân trọng sự chăm chỉ, cống hiến của nhân viên tại {{ tên công ty }}.',1,1780151790,1780151790),(147123889,'65a10d7c553ce',147123485,'Viết bài đăng mạng xã hội nói về {{ thành viên nhóm }} và làm nổi bật đóng góp của họ cho công ty trong {{ phòng ban }}.',1,1780151790,1780151790),(147123890,'65a10d7c564ef',147123485,'Tạo chú thích cho video mạng xã hội đưa người theo dõi vào hậu trường quá trình tạo {{ sự kiện/sản phẩm }}.',1,1780151790,1780151790),(147123891,'65a10d7c5748a',147123485,'Viết bài đăng mạng xã hội thông báo buổi hỏi đáp trực tiếp với thành viên chuyên về {{ vai trò }}, nơi người theo dõi có thể đặt câu hỏi về vai trò của họ.',1,1780151790,1780151790),(147123892,'65a10d7c58537',147123485,'Tạo bài đăng mạng xã hội về một ngày làm việc của {{ nghề nghiệp }}, thể hiện thói quen và trách nhiệm hàng ngày.',1,1780151790,1780151790),(147123893,'65a10d7c5961a',147123485,'Tạo bài đăng mạng xã hội thể hiện cam kết bền vững và thân thiện môi trường của công ty trong hoạt động và sản phẩm.',1,1780151790,1780151790),(147123894,'65a10d7c59b85',147123485,'Viết bài đăng mạng xã hội thể hiện cam kết phát triển và đào tạo nhân viên của công ty.',1,1780151790,1780151790),(147123895,'65a10d7c5a01d',147123486,'Viết lời kêu gọi hành động cho {{ sản phẩm }} để người đọc {{ hành động }}.',1,1780151790,1780151790),(147123896,'65a10d7c5a4a1',147123486,'Viết lời kêu gọi hành động cho {{ sản phẩm/dịch vụ }}.',1,1780151790,1780151790),(147123897,'65a10d7c5a945',147123486,'Viết lời kêu gọi hành động cho nội dung sau:',1,1780151790,1780151790),(147123898,'65a10d7c5ad5c',147123486,'Viết lời kêu gọi hành động quảng bá {{ sản phẩm/dịch vụ }} cho {{ loại khách hàng }}.',1,1780151790,1780151790),(147123899,'65a10d7c5b11a',147123486,'Tạo lời kêu gọi hành động cho {{ sản phẩm/dịch vụ }} và bao gồm cụm từ sau:',1,1780151790,1780151790),(147123900,'65a10d7c5b52a',147123486,'Đưa ra 10 lời kêu gọi hành động mạnh mẽ về {{ sản phẩm/dịch vụ }}.',1,1780151790,1780151790),(147123901,'65a10d7c5b95d',147123487,'Viết bài đăng mạng xã hội dựa trên thống kê về cà phê {{ chủ đề }}.',1,1780151790,1780151790),(147123902,'65a10d7c5bdc1',147123487,'Viết bài đăng mạng xã hội thảo luận huyền thoại về {{ chủ đề }}.',1,1780151790,1780151790),(147123903,'65a10d7c5c225',147123487,'Tạo bài đăng mạng xã hội nói về những điều không nên làm với {{ chủ đề }}.',1,1780151790,1780151790),(147123904,'65a10d7c5c6f7',147123487,'Tạo bài đăng mạng xã hội về mẹo/lợi ích của {{ chủ đề }}.',1,1780151790,1780151790),(147123905,'65a10d7c5cc0a',147123487,'Viết bài đánh giá mạng xã hội về {{ sản phẩm }}.',1,1780151790,1780151790),(147123906,'65a10d7c5d02e',147123487,'Tạo bài đăng mạng xã hội về lịch sử của {{ chủ đề }}.',1,1780151790,1780151790),(147123907,'65a10d7c5d468',147123487,'Liệt kê thách thức đi kèm {{ nghề nghiệp }} để đăng trên {{ nền tảng mạng xã hội }} cho {{ đối tượng }}.',1,1780151790,1780151790),(147123908,'65a10d7c5d8b0',147123487,'Viết bài đăng mạng xã hội so sánh {{ chủ đề }} và {{ chủ đề }}.',1,1780151790,1780151790),(147123909,'65a10d7c5dccd',147123487,'Tạo danh sách ưu và nhược điểm của {{ chủ đề }}.',1,1780151790,1780151790),(147123910,'65a10d7c5e119',147123487,'Mời đối tượng chia sẻ suy nghĩ hoặc trải nghiệm liên quan {{ chủ đề }}, và khuyến khích thảo luận trong phần bình luận.',1,1780151790,1780151790),(147123911,'65a10d7c5e582',147123487,'Chia sẻ sự thật bất ngờ hoặc ít ai biết về {{ chủ đề }}.',1,1780151790,1780151790),(147123912,'65a10d7c5ea7f',147123487,'Giải thích vì sao {{ sản phẩm }} ngày càng quan trọng trong {{ lĩnh vực }}, và cách nó giúp cải thiện {{ kết quả }}.',1,1780151790,1780151790),(147123913,'65a10d7c5eee4',147123487,'Thảo luận lợi ích và hạn chế tiềm năng của {{ phương pháp/công cụ }}, cân nhắc ưu nhược điểm.',1,1780151790,1780151790),(147123914,'65a10d7c5f32d',147123487,'Đưa ví dụ thực tế hoặc case study về cách {{ phương pháp/công cụ }} được áp dụng trong {{ ngành }} và tác động đến {{ nhóm đối tượng }}.',1,1780151790,1780151790),(147123915,'65a10d7c5f6de',147123487,'Giải thích rõ ràng, ngắn gọn cách một {{ sản phẩm }} hoạt động, dùng ngôn ngữ đơn giản và ví dụ minh họa.',1,1780151790,1780151790),(147123916,'65a10d7c5fb79',147123487,'Giải đáp hiểu lầm hoặc câu hỏi phổ biến liên quan {{ chủ đề }}, và làm rõ.',1,1780151790,1780151790),(147123917,'65a10d7c5ff9f',147123487,'Dùng định dạng \"Câu hỏi thường gặp\" để trả lời câu hỏi phổ biến về {{ chủ đề }}, kèm giải thích hoặc tóm tắt ngắn gọn.',1,1780151790,1780151790),(147123918,'65a10d7c60450',147123487,'Đặt câu hỏi gợi suy nghĩ hoặc thách thức liên quan {{ chủ đề }}, và khuyến khích đối tượng chia sẻ suy nghĩ hoặc quan điểm.',1,1780151790,1780151790),(147123919,'65a10d7c608cf',147123487,'Đưa lời khuyên hoặc hướng dẫn cách {{ hành động }}, đồng thời giải quyết thách thức hoặc lo ngại phổ biến.',1,1780151790,1780151790),(147123920,'65a10d7c60fa1',147123487,'Giải quyết huyền thoại hoặc hiểu lầm phổ biến liên quan {{ chủ đề }}, kèm bằng chứng hoặc ví dụ để bác bỏ.',1,1780151790,1780151790),(147123921,'65a10d7c61455',147123487,'Giải thích vì sao {{ hiểu lầm }} gây hại hoặc dẫn lối, và cách nó ảnh hưởng nhận thức hoặc quyết định liên quan {{ hành động }}.',1,1780151790,1780151790),(147123922,'65a10d7c6187e',147123487,'Tạo bài đăng mạng xã hội đưa ra luận điểm phản bác cho phát biểu sau:',1,1780151790,1780151790),(147123923,'65a10d7c61c72',147123487,'Viết bài đăng mạng xã hội so sánh {{ chủ đề }} và {{ chủ đề }}.',1,1780151790,1780151790),(147123924,'65a10d7c62102',147123487,'So sánh và đối chiếu {{ chủ đề }} và {{ chủ đề }}.',1,1780151790,1780151790),(147123925,'65a10d7c6254e',147123487,'So sánh ưu và nhược điểm của {{ sản phẩm/dịch vụ/phương pháp }}.',1,1780151790,1780151790),(147123926,'65a10d7c62913',147123487,'Tạo bài đăng mạng xã hội đưa ra ba luận điểm ủng hộ cho phát biểu sau:',1,1780151790,1780151790),(147123927,'65a10d7c62c9d',147123487,'Tạo bài đăng mạng xã hội đưa ra luận điểm phản bác cho bài viết về {{ chủ đề }}.',1,1780151790,1780151790),(147123928,'65a10d7c630b2',147123487,'Phân tích phê phán {{ chủ đề }}.',1,1780151790,1780151790),(147123929,'65a10d7c63535',147123487,'Tạo bài đăng mạng xã hội như câu trả lời hay cho {{ phản đối của khách hàng }}.',1,1780151790,1780151790),(147123930,'65a10d7c639f4',147123488,'Viết câu đùa hài hước về {{ chủ đề }}.',1,1780151790,1780151790),(147123931,'65a10d7c63e57',147123488,'Nêu một sự thật thú vị về {{ chủ đề }} và tạo bài đăng mạng xã hội về nó.',1,1780151790,1780151790),(147123932,'65a10d7c642c1',147123488,'Viết phép so sánh để giải thích {{ chủ đề }}.',1,1780151790,1780151790),(147123933,'65a10d7c64766',147123488,'Viết bài đăng mạng xã hội hài hước về {{ chủ đề }}.',1,1780151790,1780151790),(147123934,'65a10d7c64b3a',147123488,'Viết câu đố về {{ chủ đề }}.',1,1780151790,1780151790),(147123935,'65a10d7c64f65',147123488,'Tạo thử thách mạng xã hội khuyến khích mọi người {{ hoạt động }}.',1,1780151790,1780151790),(147123936,'65a10d7c6533b',147123488,'Nghĩ ra các câu chơi chữ dí dỏm về {{ chủ đề }}.',1,1780151790,1780151790),(147123937,'65a10d7c6579e',147123488,'Tạo bài đăng mạng xã hội gợi ý {{ sách/phim/podcast/sản phẩm/dịch vụ }} cho {{ nhu cầu }}.',1,1780151790,1780151790),(147123938,'65a10d7c65bb5',147123488,'Viết bài đăng mạng xã hội hài hước chọc vào {{ quan niệm sai lầm }}.',1,1780151790,1780151790),(147123939,'65a10d7c660bb',147123488,'Tạo bài đăng mạng xã hội kể một giai thoại hoặc câu chuyện hài hước liên quan {{ ngành }}.',1,1780151790,1780151790),(147123940,'65a10d7c66603',147123488,'Tạo bài đăng mạng xã hội làm nổi bật câu chơi chữ hài hước liên quan {{ sản phẩm/dịch vụ/ngành }}.',1,1780151790,1780151790),(147123941,'65a10d7c66a55',147123488,'Tạo loạt bài đăng mạng xã hội với câu đùa hoặc meme hài hước về thách thức hoặc điều thú vị khi làm việc từ xa.',1,1780151790,1780151790),(147123942,'65a10d7c66e69',147123488,'Viết bài đăng mạng xã hội xin lời khuyên hoặc ý kiến về {{ chủ đề }}.',1,1780151790,1780151790),(147123943,'65a10d7c6727f',147123488,'Tạo bài đăng mạng xã hội mời người theo dõi chia sẻ meme yêu thích.',1,1780151790,1780151790),(147123944,'65a10d7c676d7',147123488,'Viết bài đăng mạng xã hội mời người theo dõi chia sẻ câu đùa hoặc meme yêu thích về {{ chủ đề }}.',1,1780151790,1780151790),(147123945,'65a10d7c67ad1',147123488,'Tạo bài đăng mạng xã hội mời người theo dõi chia sẻ câu nói yêu thích về {{ chủ đề }}.',1,1780151790,1780151790),(147123946,'65a10d7c67f64',147123488,'Tạo bài đăng mạng xã hội mời người theo dõi chia sẻ ý kiến về {{ chủ đề }}.',1,1780151790,1780151790),(147123947,'65a10d7c68484',147123489,'Khởi động cuộc tranh luận trên mạng xã hội về {{ chủ đề }}.',1,1780151790,1780151790),(147123948,'65a10d7c6893b',147123489,'Viết bài đăng mạng xã hội đặt câu hỏi về {{ chủ đề }}',1,1780151790,1780151790),(147123949,'65a10d7c68d38',147123489,'Tạo bài đăng mạng xã hội xin gợi ý {{ phim/sách/podcast }}.',1,1780151790,1780151790),(147123950,'65a10d7c690ed',147123489,'Liệt kê 10 ý tưởng cuộc thi trên mạng xã hội.',1,1780151790,1780151790),(147123951,'65a10d7c694b4',147123489,'Tạo bài đăng tặng quà trên mạng xã hội cho {{ sản phẩm/dịch vụ }}.',1,1780151790,1780151790),(147123952,'65a10d7c69912',147123489,'Tạo khảo sát/trắc nghiệm mạng xã hội về {{ chủ đề }}.',1,1780151790,1780151790),(147123953,'65a10d7c69d8d',147123489,'Tạo bài đăng mạng xã hội thông báo buổi hỏi đáp trực tiếp về {{ chủ đề }}.',1,1780151790,1780151790),(147123954,'65a10d7c6a229',147123489,'Tạo 10 lựa chọn khảo sát cho trắc nghiệm về {{ chủ đề }}.',1,1780151790,1780151790),(147123955,'65a10d7c6a6a0',147123489,'Tạo trắc nghiệm gợi ý sản phẩm giúp khách hàng tìm {{ sản phẩm }} phù hợp với {{ nhu cầu của họ }}. Dùng câu hỏi thu hẹp lựa chọn và làm nổi bật lợi ích từng sản phẩm.',1,1780151790,1780151790),(147123956,'65a10d7c6abdc',147123489,'Tạo trắc nghiệm kiến thức về {{ chủ đề }}. Dùng câu hỏi thách thức khách hàng suy nghĩ và tìm hiểu thêm về chủ đề.',1,1780151790,1780151790),(147123957,'65a10d7c6b03d',147123489,'Tạo trắc nghiệm đố vui về {{ ngành }}. Dùng câu hỏi kiểm tra kiến thức khách hàng và tạo trải nghiệm vui, tương tác.',1,1780151790,1780151790),(147123958,'65a10d7c6b40f',147123489,'Tạo trắc nghiệm định hướng nghề nghiệp giúp khách hàng xác định con đường sự nghiệp lý tưởng. Dùng câu hỏi giới thiệu các vai trò và ngành khác nhau, kèm tài nguyên hữu ích để khám phá thêm.',1,1780151790,1780151790),(147123959,'65a10d7c6b7f3',147123489,'Tạo trắc nghiệm du lịch giúp khách hàng tìm điểm đến lý tưởng. Dùng câu hỏi thể hiện sở thích du lịch khác nhau và cung cấp mẹo, tài nguyên hữu ích.',1,1780151790,1780151790),(147123960,'65a10d7c6bc46',147123489,'Tạo trắc nghiệm ẩm thực giúp khách hàng xác định khẩu vị và khám phá công thức, nguyên liệu mới. Dùng câu hỏi giới thiệu các ẩm thực và hương vị khác nhau.',1,1780151790,1780151790),(147123961,'65a10d7c6c0a5',147123489,'Tạo trắc nghiệm thời trang giúp khách hàng xác định phong cách cá nhân và tìm trang phục phù hợp. Dùng câu hỏi giới thiệu các phong cách thời trang khác nhau, kèm tài nguyên và lời khuyên hữu ích.',1,1780151790,1780151790),(147123962,'65a10d7c6c493',147123489,'Tạo trắc nghiệm giáo dục giúp mọi người xác định phong cách học và tìm tài nguyên, mẹo học tập hữu ích. Dùng câu hỏi giới thiệu kỹ thuật học khác nhau và đưa mẹo, tài nguyên có thể áp dụng ngay.',1,1780151790,1780151790),(147123963,'65a10d7c6c87d',147123489,'Tạo trắc nghiệm âm nhạc giúp khách hàng xác định thể loại và nghệ sĩ yêu thích. Dùng câu hỏi giới thiệu các phong cách âm nhạc khác nhau, kèm tài nguyên và đố vui.',1,1780151790,1780151790),(147123964,'65a10d7c6cd01',147123489,'Tạo trắc nghiệm phim và truyền hình giúp khách hàng xác định phim và chương trình yêu thích. Dùng câu hỏi giới thiệu các thể loại khác nhau, kèm tài nguyên và đố vui.',1,1780151790,1780151790),(147123965,'65a10d7c6d1bd',147123489,'Tạo cuộc thi ảnh khuyến khích khách hàng chia sẻ ảnh khi dùng {{ sản phẩm }}. Dùng hashtag riêng để theo dõi bài dự thi và trao giải cho ảnh hay nhất.',1,1780151790,1780151790),(147123966,'65a10d7c6e33b',147123489,'Tạo cuộc thi chú thích khuyến khích khách hàng viết chú thích hài hước hoặc sáng tạo cho ảnh liên quan {{ chủ đề }}. Trao giải cho chú thích hay nhất.',1,1780151790,1780151790),(147123967,'65a10d7c6f3ba',147123489,'Tạo cuộc xổ số khuyến khích khách hàng tham gia để có cơ hội trúng {{ giải thưởng }}.',1,1780151790,1780151790),(147123968,'65a10d7c6f79c',147123489,'Tạo cuộc thi đố vui về {{ chủ đề }}. Dùng loạt câu hỏi thách thức kiến thức khách hàng và trao giải cho điểm cao nhất.',1,1780151790,1780151790),(147123969,'65a10d7c6fc2b',147123489,'Tạo cuộc thi nội dung do người dùng tạo khuyến khích khách hàng sáng tạo và chia sẻ nội dung liên quan {{ chủ đề }}. Dùng hashtag riêng để theo dõi bài dự thi và trao giải cho nội dung hay nhất.',1,1780151790,1780151790),(147123970,'65a10d7c70137',147123489,'Tạo cuộc thi sáng tác khuyến khích khách hàng viết truyện ngắn, thơ hoặc tác phẩm sáng tạo khác liên quan {{ chủ đề }}. Dùng hashtag riêng để theo dõi bài dự thi và trao giải cho tác phẩm hay nhất.',1,1780151790,1780151790),(147123971,'65a10d7c705df',147123489,'Tạo cuộc thi giải nghĩa emoji khuyến khích khách hàng giải thích loạt emoji liên quan {{ chủ đề }}. Dùng hashtag riêng để theo dõi bài dự thi và trao giải cho cách giải thích sáng tạo nhất.',1,1780151790,1780151790),(147123972,'65a10d7c70a28',147123489,'Tạo cuộc thi thiết kế lại logo khuyến khích khách hàng thiết kế lại logo thương hiệu. Dùng hashtag riêng để theo dõi bài dự thi và trao giải cho thiết kế hay nhất.',1,1780151790,1780151790),(147123973,'65a10d7c70e13',147123489,'Tạo cuộc thi \"Đặt tên sản phẩm\" khuyến khích khách hàng nghĩ tên sáng tạo cho {{ sản phẩm/dịch vụ }}.',1,1780151790,1780151790),(147123974,'65a10d7c71266',147123490,'Viết thông điệp truyền cảm hứng trên mạng xã hội cho {{ đối tượng mục tiêu }}.',1,1780151790,1780151790),(147123975,'65a10d7c71650',147123490,'Tạo bài đăng mạng xã hội chia sẻ câu nói truyền cảm hứng về {{ chủ đề }}.',1,1780151790,1780151790),(147123976,'65a10d7c71ae7',147123490,'Tạo bài đăng chia sẻ câu chuyện thành công truyền cảm hứng về {{ chủ đề }}.',1,1780151790,1780151790),(147123977,'65a10d7c71f95',147123490,'Viết bài đăng mạng xã hội truyền cảm hứng về cách vượt qua {{ thử thách }}.',1,1780151790,1780151790),(147123978,'65a10d7c724b2',147123490,'Tạo bài đăng mạng xã hội chia sẻ thông điệp truyền động lực cho người theo dõi là doanh nhân hoặc chủ doanh nghiệp.',1,1780151790,1780151790),(147123979,'65a10d7c7296b',147123490,'Chia sẻ câu chuyện về người vượt qua nghịch cảnh và thành công trong lĩnh vực của họ.',1,1780151790,1780151790),(147123980,'65a10d7c72dcd',147123490,'Viết bài truyền cảm hứng về việc chấp nhận rủi ro và bước ra khỏi vùng an toàn.',1,1780151790,1780151790),(147123981,'65a10d7c73248',147123490,'Tạo bài đăng mạng xã hội khuyến khích người theo dõi không bỏ cuộc với ước mơ của mình.',1,1780151790,1780151790),(147123982,'65a10d7c73645',147123490,'Tạo bài đăng chia sẻ mẹo giữ động lực và tập trung trong thời điểm khó khăn.',1,1780151790,1780151790),(147123983,'65a10d7c73ae9',147123490,'Viết thông điệp truyền cảm hứng về sức mạnh của kiên trì và quyết tâm khi theo đuổi mục tiêu.',1,1780151790,1780151790),(147123984,'65a10d7c74012',147123490,'Chia sẻ câu chuyện cá nhân về việc vượt qua trở ngại và cách điều đó giúp bạn mạnh mẽ hơn.',1,1780151790,1780151790),(147123985,'65a10d7c74459',147123490,'Tạo bài đăng về tầm quan trọng của chăm sóc bản thân khi theo đuổi mục tiêu.',1,1780151790,1780151790),(147123986,'65a10d7c74856',147123490,'Tạo bài đăng mạng xã hội về lợi ích của suy nghĩ tích cực và tư duy phát triển.',1,1780151790,1780151790),(147123987,'65a10d7c74c34',147123490,'Viết thông điệp truyền cảm hứng về sức mạnh của cộng đồng và sự hỗ trợ mà cộng đồng mang lại.',1,1780151790,1780151790),(147123988,'65a10d7c7508e',147123490,'Chia sẻ câu chuyện về người tạo tác động tích cực cho thế giới và truyền cảm hứng cho người khác làm điều tương tự.',1,1780151790,1780151790),(147123989,'65a10d7c75461',147123490,'Tạo bài đăng mạng xã hội về giá trị của sự chăm chỉ và kiên trì trên con đường thành công.',1,1780151790,1780151790),(147123990,'65a10d7c75842',147123490,'Viết thông điệp truyền cảm hứng về tầm quan trọng của việc đặt mục tiêu và nỗ lực theo đuổi.',1,1780151790,1780151790),(147123991,'65a10d7c75c4e',147123490,'Tạo bài đăng chia sẻ mẹo giữ tập trung và năng suất khi làm việc tại nhà.',1,1780151790,1780151790),(147123992,'65a10d7c760cd',147123490,'Chia sẻ câu chuyện truyền động lực về người theo đuổi đam mê và tìm thấy thành công.',1,1780151790,1780151790),(147123993,'65a10d7c76496',147123490,'Tạo bài đăng mạng xã hội về lợi ích của việc đón nhận thử thách mới và học kỹ năng mới.',1,1780151790,1780151790),(147123994,'65a10d7c76908',147123490,'Viết thông điệp truyền cảm hứng về sức mạnh của lòng biết ơn và trân trọng những điều nhỏ trong cuộc sống.',1,1780151790,1780151790),(147123995,'65a10d7c76dca',147123490,'Tạo bài đăng chia sẻ trải nghiệm cá nhân vượt qua tình huống khó khăn và bài học rút ra.',1,1780151790,1780151790),(147123996,'65a10d7c772f6',147123490,'Chia sẻ câu chuyện về người biến thất bại thành cơ hội phát triển và thành công.',1,1780151790,1780151790),(147123997,'65a10d7c7779b',147123490,'Tạo bài đăng mạng xã hội về tầm quan trọng của tự phản tư và tự hoàn thiện.',1,1780151790,1780151790),(147123998,'65a10d7c77ff6',147123490,'Tạo bài đăng về tầm quan trọng của việc hành động và tiến bộ mỗi ngày hướng tới mục tiêu của bạn.',1,1780151790,1780151790),(147123999,'65a10d7c783af',147123490,'Chia sẻ câu chuyện truyền cảm hứng về một người kiên trì không bỏ cuộc và cuối cùng đã hiện thực hóa ước mơ.',1,1780151790,1780151790),(147124000,'65a10d7c787bf',147123490,'Tạo bài đăng mạng xã hội về tầm quan trọng của việc cân bằng cuộc sống và theo đuổi đam mê.',1,1780151790,1780151790),(147124001,'65a10d7c78c22',147123490,'Viết lời nhắn truyền cảm hứng về lợi ích của việc học hỏi từ thất bại và biến nó thành bước đệm tới thành công.',1,1780151790,1780151790),(147124002,'65a10d7c790be',147123490,'Tạo bài đăng chia sẻ mẹo duy trì tư duy tích cực và vượt qua suy nghĩ tiêu cực về bản thân.',1,1780151790,1780151790),(147124003,'65a10d7c794ea',147123490,'Chia sẻ câu chuyện về một người đã tạo nên thay đổi tích cực bằng cách giúp đỡ người khác và truyền cảm hứng.',1,1780151790,1780151790),(147124004,'65a10d7c798bb',147123490,'Tạo bài đăng mạng xã hội về sức mạnh của việc hình dung và biến ước mơ thành hiện thực.',1,1780151790,1780151790),(147124005,'65a10d7c79cb5',147123490,'Viết lời nhắn truyền cảm hứng về tầm quan trọng của việc ở gần những người tích cực và luôn ủng hộ bạn.',1,1780151790,1780151790),(147124006,'65a10d7c7a123',147123490,'Tạo bài đăng về lợi ích của việc nghỉ ngơi và chăm sóc bản thân để tránh kiệt sức.',1,1780151790,1780151790),(147124007,'65a10d7c7a59e',147123490,'Chia sẻ câu chuyện truyền cảm hứng về một người dám thách thức chuẩn mực xã hội và tạo ảnh hưởng tích cực lớn tới cộng đồng.',1,1780151790,1780151790),(147124008,'65a10d7c7aa2b',147123491,'Tạo bài đăng mạng xã hội vui vẻ chào mừng {{ ngày lễ }}.',1,1780151790,1780151790),(147124009,'65a10d7c7af64',147123491,'Viết bài đăng mạng xã hội về các truyền thống nổi tiếng của {{ ngày lễ }}.',1,1780151790,1780151790),(147124010,'65a10d7c7b3e0',147123491,'Soạn bài đăng mạng xã hội kể về nguồn gốc và cách {{ ngày lễ }} ra đời.',1,1780151790,1780151790),(147124011,'65a10d7c7b83d',147123491,'Chia sẻ danh sách phim về {{ ngày lễ }}.',1,1780151790,1780151790),(147124012,'65a10d7c7bcbe',147123491,'Tạo bài đăng mạng xã hội mô tả một bộ phim kinh điển về {{ ngày lễ }} mà không tiết lộ tên, mời khán giả đoán.',1,1780151790,1780151790),(147124013,'65a10d7c7c187',147123491,'Viết bài đăng mạng xã hội về những món không thể thiếu trong dịp {{ ngày lễ }}.',1,1780151790,1780151790),(147124014,'65a10d7c7c6c7',147123491,'Viết chú thích mạng xã hội cho ảnh trang trí văn phòng theo chủ đề {{ ngày lễ }}.',1,1780151790,1780151790),(147124015,'65a10d7c7cc93',147123491,'Viết câu chuyện ấm áp cho mùa {{ ngày lễ }}.',1,1780151790,1780151790),(147124016,'65a10d7c7d122',147123491,'Tạo bài đăng mạng xã hội quảng bá chương trình khuyến mãi dịp {{ ngày lễ }}.',1,1780151790,1780151790),(147124017,'65a10d7c7d96d',147123491,'Viết bài đăng về các công thức nấu ăn cho dịp {{ ngày lễ }}.',1,1780151790,1780151790),(147124018,'65a10d7c7ddfc',147123491,'Tạo bài đăng mạng xã hội về những sự thật ít người biết về {{ ngày lễ }}.',1,1780151790,1780151790),(147124019,'65a10d7c7e23d',147123491,'Gợi ý ý tưởng bài đăng mạng xã hội về {{ ngày lễ }}.',1,1780151790,1780151790),(147124020,'65a10d7c7e639',147123491,'Tạo cuộc thi mạng xã hội theo chủ đề {{ ngày lễ }}.',1,1780151790,1780151790),(147124021,'65a10d7c7eaa1',147123491,'Tạo bài đăng mạng xã hội làm nổi bật lịch sử và ý nghĩa văn hóa của {{ ngày lễ }}.',1,1780151790,1780151790),(147124022,'65a10d7c7eefa',147123491,'Tạo loạt bài đăng mạng xã hội giới thiệu các truyền thống và phong tục liên quan đến {{ ngày lễ }} trên khắp thế giới.',1,1780151790,1780151790),(147124023,'65a10d7c7f3ad',147123491,'Viết bài đăng mạng xã hội giới thiệu món ăn hoặc công thức độc đáo thường được dùng trong dịp {{ ngày lễ }}.',1,1780151790,1780151790),(147124024,'65a10d7c7f83d',147123491,'Tạo bài đăng mạng xã hội khuyến khích người theo dõi chia sẻ cách họ đón {{ ngày lễ }} và tag thương hiệu để có cơ hội được giới thiệu.',1,1780151790,1780151790),(147124025,'65a10d7c7fd4c',147123491,'Tạo bài đăng mạng xã hội giới thiệu các ý tưởng thủ công và DIY liên quan đến {{ ngày lễ }}.',1,1780151790,1780151790),(147124026,'65a10d7c8015d',147123491,'Viết bài đăng mạng xã hội giới thiệu cách thương hiệu đón {{ ngày lễ }} và tặng mã giảm giá đặc biệt.',1,1780151790,1780151790),(147124027,'65a10d7c80564',147123491,'Tạo bài đăng mạng xã hội gợi ý các bài hát nên nghe trong dịp {{ ngày lễ }}.',1,1780151790,1780151790),(147124028,'65a10d7c8095c',147123491,'Tạo loạt bài đăng mạng xã hội giới thiệu các điểm du lịch phù hợp cho dịp {{ ngày lễ }}.',1,1780151790,1780151790),(147124029,'65a10d7c80e26',147123491,'Tạo bài đăng mạng xã hội khuyến khích người theo dõi chia sẻ kỷ niệm hoặc ảnh {{ ngày lễ }} yêu thích bằng hashtag thương hiệu.',1,1780151790,1780151790),(147124030,'65a10d7c81299',147123491,'Tạo bài đăng mạng xã hội giới thiệu các bộ phim liên quan đến {{ ngày lễ }}.',1,1780151790,1780151790),(147124031,'65a10d7c81712',147123491,'Viết bài đăng mạng xã hội gợi ý mẹo tổ chức tiệc hoặc buổi sum họp dịp {{ ngày lễ }}.',1,1780151790,1780151790),(147124032,'65a10d7c81c0c',147123491,'Tạo bài đăng mạng xã hội giới thiệu hoạt động thiện nguyện hoặc cơ hội tình nguyện theo chủ đề {{ ngày lễ }}.',1,1780151790,1780151790),(147124033,'65a10d7c81ff4',147123491,'Tạo loạt bài đăng mạng xã hội giới thiệu sách và tác phẩm văn học liên quan đến {{ ngày lễ }}.',1,1780151790,1780151790),(147124034,'65a10d7c82438',147123491,'Viết bài đăng mạng xã hội giới thiệu cách các quốc gia và nền văn hóa khác nhau đón {{ ngày lễ }}.',1,1780151790,1780151790),(147124035,'65a10d7c82837',147123491,'Tạo bài đăng mạng xã hội khuyến khích người theo dõi trang trí nhà hoặc không gian làm việc cho {{ ngày lễ }} và chia sẻ ảnh bằng hashtag thương hiệu.',1,1780151790,1780151790),(147124036,'65a10d7c82c20',147123491,'Tạo bài đăng mạng xã hội chia sẻ công thức đồ uống theo chủ đề {{ ngày lễ }} để người theo dõi thử.',1,1780151790,1780151790),(147124037,'65a10d7c83c7c',147123491,'Viết bài đăng mạng xã hội giới thiệu những cách sáng tạo để đón {{ ngày lễ }}.',1,1780151790,1780151790),(147124038,'65a10d7c84d00',147123491,'Tạo bài đăng mạng xã hội khuyến khích người theo dõi chia sẻ cuốn sách theo chủ đề {{ ngày lễ }} yêu thích.',1,1780151790,1780151790),(147124039,'65a10d7c8516e',147123491,'Tạo loạt bài đăng mạng xã hội giới thiệu các trò chơi và hoạt động liên quan đến {{ ngày lễ }}.',1,1780151790,1780151790),(147124040,'65a10d7c85647',147123491,'Viết bài đăng mạng xã hội giới thiệu các cách lan tỏa yêu thương hoặc tạo tác động tích cực trong dịp {{ ngày lễ }}.',1,1780151790,1780151790),(147124041,'65a10d7c85ad5',147123491,'Tạo bài đăng mạng xã hội chia sẻ câu đố vui theo chủ đề {{ ngày lễ }}.',1,1780151790,1780151790),(147124042,'65a10d7c86b17',147123491,'Viết bài đăng mạng xã hội khuyến khích người theo dõi chia sẻ kỷ niệm {{ ngày lễ }} yêu thích.',1,1780151790,1780151790),(147124043,'65a10d7c87a8d',147123492,'Tạo bài đăng làm nổi bật sứ mệnh công ty sau:',1,1780151790,1780151790),(147124044,'65a10d7c87e67',147123492,'Viết bài đăng giới thiệu {{ sản phẩm hoặc dịch vụ }}.',1,1780151790,1780151790),(147124045,'65a10d7c88291',147123492,'Viết bài đăng chia sẻ mẹo hoặc lời khuyên liên quan đến {{ chủ đề }}.',1,1780151790,1780151790),(147124046,'65a10d7c88656',147123492,'Tạo bài đăng cảm ơn khách hàng đã đồng hành và ủng hộ doanh nghiệp.',1,1780151790,1780151790),(147124047,'65a10d7c889e9',147123492,'Viết bài đăng thông báo chương trình khuyến mãi hoặc giảm giá đặc biệt.',1,1780151790,1780151790),(147124048,'65a10d7c88e06',147123492,'Tạo bài đăng chúc mừng {{ cột mốc/kỷ niệm }}.',1,1780151790,1780151790),(147124049,'65a10d7c8922a',147123492,'Tạo chú thích cho ảnh chụp tại văn phòng.',1,1780151790,1780151790),(147124050,'65a10d7c89688',147123492,'Tạo bài đăng chào mừng thành viên mới {{ tên }} thuộc {{ phòng ban }}.',1,1780151790,1780151790),(147124051,'65a10d7c89b1c',147123492,'Viết bài đăng hỏi ý kiến hoặc phản hồi của khán giả về {{ chủ đề }}.',1,1780151790,1780151790),(147124052,'65a10d7c8a028',147123492,'Tạo chú thích cho hình ảnh giới thiệu đánh giá hoặc lời chứng thực từ khách hàng.',1,1780151790,1780151790),(147124053,'65a10d7c8a420',147123492,'Viết bài đăng làm nổi bật {{ giải thưởng/công nhận ngành }}.',1,1780151790,1780151790),(147124054,'65a10d7c8a877',147123492,'Tạo bài đăng chia sẻ sự thật thú vị hoặc câu đố về {{ ngành }} của bạn.',1,1780151790,1780151790),(147124055,'65a10d7c8ad22',147123492,'Viết chú thích cho ảnh gợi ý sản phẩm sắp ra mắt dựa trên mô tả sau:',1,1780151790,1780151790),(147124056,'65a10d7c8b168',147123492,'Tạo bài đăng thể hiện cam kết bền vững và thân thiện môi trường của công ty.',1,1780151790,1780151790),(147124057,'65a10d7c8b5d4',147123492,'Viết bài đăng giới thiệu văn hóa làm việc tại {{ tên công ty }}. Dùng mô tả sau để tạo bài đăng:',1,1780151790,1780151790),(147124058,'65a10d7c8ba66',147123492,'Tạo bài đăng chia sẻ các mục tiêu công ty sau:',1,1780151790,1780151790),(147124059,'65a10d7c8bf8e',147123492,'Viết bài đăng làm nổi bật lý do {{ sản phẩm/dịch vụ }} là phần quan trọng trong doanh nghiệp.',1,1780151790,1780151790),(147124060,'65a10d7c8c43a',147123492,'Tạo bài đăng giới thiệu các sự thật thú vị về {{ tên công ty }} sau:',1,1780151790,1780151790),(147124061,'65a10d7c8c84d',147123492,'Viết bài đăng thông báo bài blog mới trên website về {{ chủ đề }}.',1,1780151790,1780151790),(147124062,'65a10d7c8cc99',147123492,'Tạo bài đăng chia sẻ câu nói hoặc lời nhắn truyền cảm hứng liên quan đến {{ chủ đề }}.',1,1780151790,1780151790),(147124063,'65a10d7c8d12a',147123492,'Viết bài đăng so sánh {{ sản phẩm }} với {{ sản phẩm đối thủ }}.',1,1780151790,1780151790),(147124064,'65a10d7c8d59c',147123492,'Viết bài đăng quảng bá webinar sắp diễn ra về {{ chủ đề }}.',1,1780151790,1780151790),(147124065,'65a10d7c8da95',147123492,'Tạo bài đăng chia sẻ tên hài hước tiềm năng cho {{ sản phẩm }}.',1,1780151790,1780151790),(147124066,'65a10d7c8df19',147123492,'Tạo bài đăng hỏi khán giả góp ý cách cải thiện {{ sản phẩm }}.',1,1780151790,1780151790),(147124067,'65a10d7c8e352',147123492,'Viết bài đăng tặng mã giảm giá hoặc coupon cho {{ sản phẩm }}.',1,1780151790,1780151790),(147124068,'65a10d7c8e75f',147123492,'Viết bài đăng quảng bá chương trình giới thiệu khách hàng của công ty dựa trên thông tin sau:',1,1780151790,1780151790),(147124069,'65a10d7c8ead6',147123492,'Tạo chú thích cho ảnh hoặc video do khách hàng gửi. Dùng mô tả ảnh sau khi tạo bài đăng:',1,1780151790,1780151790),(147124070,'65a10d7c8ef12',147123492,'Tạo câu đố hoặc trivia mạng xã hội liên quan đến {{ ngành }}.',1,1780151790,1780151790),(147124071,'65a10d7c8f32f',147123492,'Viết bài đăng chia sẻ câu nói truyền cảm hứng yêu thích của thành viên trong đội. Đây là câu nói:',1,1780151790,1780151790),(147124072,'65a10d7c8f796',147123492,'Viết bài đăng quảng bá chương trình giảm giá trong thời gian có hạn.',1,1780151790,1780151790),(147124073,'65a10d7c8fbf7',147123492,'Viết bài đăng tạo khảo sát hỏi khách hàng nên đăng nội dung gì tiếp theo. Liệt kê 3 ý tưởng bài đăng liên quan đến {{ ngành }} để người theo dõi lựa chọn.',1,1780151790,1780151790),(147124074,'65a10d7c90094',147123492,'Tạo bài đăng làm nổi bật cam kết chăm sóc khách hàng và hỗ trợ của công ty.',1,1780151790,1780151790),(147124075,'65a10d7c905a8',147123493,'Tạo bài đăng tặng tư vấn miễn phí cho doanh nhân muốn mở rộng quy mô kinh doanh.',1,1780151790,1780151790),(147124076,'65a10d7c90a6e',147123493,'Viết bài đăng giới thiệu top 5 công cụ năng suất cho chủ doanh nghiệp nhỏ.',1,1780151790,1780151790),(147124077,'65a10d7c90eb1',147123493,'Viết bài đăng chia sẻ mẹo đặt mục tiêu kinh doanh khả thi.',1,1780151790,1780151790),(147124078,'65a10d7c9133c',147123493,'Tạo bài đăng thông báo chương trình coaching nhóm mới dành cho chủ doanh nghiệp nhỏ.',1,1780151790,1780151790),(147124079,'65a10d7c91811',147123493,'Viết bài đăng gợi ý cách cân bằng công việc và cuộc sống cá nhân khi là chủ doanh nghiệp.',1,1780151790,1780151790),(147124080,'65a10d7c91bb2',147123493,'Tạo bài đăng chia sẻ câu chuyện cá nhân về hành trình vượt qua thử thách với tư cách doanh nhân.',1,1780151790,1780151790),(147124081,'65a10d7c91fde',147123493,'Viết bài đăng phác thảo các phẩm chất then chốt của chủ doanh nghiệp thành công.',1,1780151790,1780151790),(147124082,'65a10d7c92418',147123493,'Tạo bài đăng thông báo ưu đãi đặc biệt cho dịch vụ coaching.',1,1780151790,1780151790),(147124083,'65a10d7c92881',147123493,'Viết bài đăng chia sẻ mẹo quản lý thời gian hiệu quả khi là chủ doanh nghiệp.',1,1780151790,1780151790),(147124084,'65a10d7c92c70',147123493,'Tạo bài đăng chia sẻ câu nói truyền cảm hứng yêu thích dành cho doanh nhân.',1,1780151790,1780151790),(147124085,'65a10d7c930fc',147123493,'Viết bài đăng gợi ý cách quản lý dòng tiền trong doanh nghiệp nhỏ.',1,1780151790,1780151790),(147124086,'65a10d7c935d2',147123493,'Tạo bài đăng thông báo webinar về cách xây dựng doanh nghiệp thành công từ con số không.',1,1780151790,1780151790),(147124087,'65a10d7c93a64',147123493,'Viết bài đăng gợi ý cách xây dựng hiện diện trực tuyến mạnh mẽ khi là chủ doanh nghiệp.',1,1780151790,1780151790),(147124088,'65a10d7c93eef',147123493,'Tạo bài đăng chia sẻ câu chuyện cá nhân về cách bạn xây dựng doanh nghiệp thành công.',1,1780151790,1780151790),(147124089,'65a10d7c943a0',147123493,'Viết bài đăng phác thảo lợi ích chính khi làm việc với coach kinh doanh.',1,1780151790,1780151790),(147124090,'65a10d7c947f6',147123493,'Viết bài đăng gợi ý cách xây dựng đội ngũ mạnh khi là chủ doanh nghiệp.',1,1780151790,1780151790),(147124091,'65a10d7c94c5d',147123493,'Viết bài đăng chia sẻ mẹo xây dựng kế hoạch marketing thành công.',1,1780151790,1780151790),(147124092,'65a10d7c9511d',147123493,'Tạo bài đăng thông báo giảm giá đặc biệt cho dịch vụ coaching.',1,1780151790,1780151790),(147124093,'65a10d7c9563b',147123493,'Viết bài đăng gợi ý cách vượt qua hội chứng kẻ mạo danh khi là doanh nhân.',1,1780151790,1780151790),(147124094,'65a10d7c95a8a',147123493,'Tạo bài đăng chia sẻ câu chuyện truyền cảm hứng về một chủ doanh nghiệp thành công.',1,1780151790,1780151790),(147124095,'65a10d7c95e9b',147123493,'Viết bài đăng phác thảo các bước then chốt để lập kế hoạch kinh doanh thành công.',1,1780151790,1780151790),(147124096,'65a10d7c96298',147123493,'Tạo bài đăng thông báo loạt podcast mới về khởi nghiệp.',1,1780151790,1780151790),(147124097,'65a10d7c966ad',147123493,'Viết bài đăng gợi ý cách đặt mục tiêu hiệu quả khi là chủ doanh nghiệp.',1,1780151790,1780151790),(147124098,'65a10d7c96b7e',147123493,'Tạo bài đăng chia sẻ câu chuyện cá nhân về cách bạn vượt qua thất bại trong kinh doanh.',1,1780151790,1780151790),(147124099,'65a10d7c9702f',147123493,'Viết bài đăng phác thảo các phẩm chất then chốt của coach kinh doanh thành công.',1,1780151790,1780151790),(147124100,'65a10d7c97554',147123493,'Tạo bài đăng thông báo khóa học trực tuyến mới về chiến lược kinh doanh.',1,1780151790,1780151790),(147124101,'65a10d7c97964',147123493,'Viết bài đăng gợi ý cách rèn luyện sự kiên cường khi là doanh nhân.',1,1780151790,1780151790),(147124102,'65a10d7c97d61',147123493,'Viết bài đăng chia sẻ mẹo giao tiếp hiệu quả trong doanh nghiệp nhỏ.',1,1780151790,1780151790),(147124103,'65a10d7c98150',147123493,'Tạo bài đăng thông báo nhóm mastermind mới dành cho doanh nhân.',1,1780151790,1780151790),(147124104,'65a10d7c98555',147123493,'Viết bài đăng gợi ý cách quản lý căng thẳng khi là chủ doanh nghiệp.',1,1780151790,1780151790),(147124105,'65a10d7c98918',147123493,'Viết bài đăng phác thảo các bước then chốt để xây dựng thương hiệu thành công.',1,1780151790,1780151790),(147124106,'65a10d7c98d02',147123493,'Tạo bài đăng thông báo workshop mới về lãnh đạo dành cho chủ doanh nghiệp nhỏ.',1,1780151790,1780151790),(147124107,'65a10d7c990ee',147123493,'Viết bài đăng gợi ý cách vượt qua trì hoãn khi là doanh nhân.',1,1780151790,1780151790),(147124108,'65a10d7c99474',147123493,'Viết bài đăng gợi ý cách giữ gìn sự ngăn nắp khi là chủ doanh nghiệp.',1,1780151790,1780151790),(147124109,'65a10d7c9988f',147123493,'Tạo bài đăng thông báo chương trình coaching mới dành cho nữ doanh nhân.',1,1780151790,1780151790),(147124110,'65a10d7c99d0b',147123493,'Viết bài đăng gợi ý cách xây dựng chiến lược bán hàng thành công.',1,1780151790,1780151790),(147124111,'65a10d7c9a0e6',147123493,'Tạo bài đăng chia sẻ câu chuyện cá nhân về cách bạn vượt qua khó khăn trong kinh doanh.',1,1780151790,1780151790),(147124112,'65a10d7c9a51b',147123493,'Viết bài đăng phác thảo lợi ích chính khi có coach kinh doanh đồng hành.',1,1780151790,1780151790),(147124113,'65a10d7c9a962',147123493,'Tạo bài đăng thông báo cuốn sách mới về thành công trong kinh doanh.',1,1780151790,1780151790),(147124114,'65a10d7c9ade4',147123493,'Viết bài đăng gợi ý cách networking hiệu quả khi là chủ doanh nghiệp.',1,1780151790,1780151790),(147124115,'65a10d7c9b304',147123493,'Viết bài đăng gợi ý cách xây dựng chiến lược chăm sóc khách hàng thành công.',1,1780151790,1780151790),(147124116,'65a10d7c9b77f',147123493,'Tạo bài đăng thông báo workshop mới về quản lý thời gian dành cho doanh nhân.',1,1780151790,1780151790),(147124117,'65a10d7c9bbac',147123493,'Viết bài đăng gợi ý cách xây dựng đội ngũ bán hàng thành công.',1,1780151790,1780151790),(147124118,'65a10d7c9bfb0',147123493,'Tạo bài đăng chia sẻ cách xử lý tình huống khách hàng khó tính.',1,1780151790,1780151790),(147124119,'65a10d7c9c33e',147123493,'Viết bài đăng phác thảo các bước then chốt để tạo chiến dịch marketing thành công.',1,1780151790,1780151790),(147124120,'65a10d7c9c707',147123493,'Viết bài đăng gợi ý cách xây dựng văn hóa doanh nghiệp mạnh.',1,1780151790,1780151790),(147124121,'65a10d7c9cb45',147123493,'Tạo bài đăng chia sẻ câu chuyện truyền cảm hứng về một doanh nhân thành công.',1,1780151790,1780151790),(147124122,'65a10d7c9cfbc',147123493,'Viết bài đăng gợi ý cách xây dựng doanh nghiệp thương mại điện tử thành công.',1,1780151790,1780151790),(147124123,'65a10d7c9d48c',147123493,'Tạo bài đăng thông báo masterclass mới về phát triển kinh doanh.',1,1780151790,1780151790),(147124124,'65a10d7c9d915',147123493,'Viết bài đăng gợi ý cách xây dựng cộng đồng trực tuyến thành công.',1,1780151790,1780151790),(147124125,'65a10d7c9ddde',147123493,'Tạo bài đăng chia sẻ câu chuyện cá nhân về cách bạn xử lý mối quan hệ đối tác kinh doanh đầy thử thách.',1,1780151790,1780151790),(147124126,'65a10d7c9e25b',147123493,'Viết bài đăng phác thảo lợi ích chính khi đầu tư phát triển bản thân với tư cách chủ doanh nghiệp.',1,1780151790,1780151790),(147124127,'65a10d7c9e68d',147123493,'Viết bài đăng gợi ý cách xây dựng chiến lược mạng xã hội thành công.',1,1780151790,1780151790),(147124128,'65a10d7c9ea65',147123493,'Viết bài đăng gợi ý cách xây dựng chiến dịch email marketing thành công.',1,1780151790,1780151790),(147124129,'65a10d7c9f23a',147123493,'Tạo bài đăng thông báo chương trình retreat mới dành cho chủ doanh nghiệp.',1,1780151790,1780151790),(147124130,'65a10d7c9f679',147123493,'Viết bài đăng gợi ý cách xây dựng chiến lược giữ chân khách hàng thành công.',1,1780151790,1780151790),(147124131,'65a10d7c9fa9a',147123494,'Tạo bài đăng chia sẻ mẹo hàng đầu để cân bằng công việc và cuộc sống.',1,1780151790,1780151790),(147124132,'65a10d7c9fefb',147123494,'Viết bài về lợi ích của chánh niệm và cách cải thiện cuộc sống hàng ngày.',1,1780151790,1780151790),(147124133,'65a10d7ca0394',147123494,'Tạo bài đăng giới thiệu công thức ăn healthy và giải thích vì sao tốt cho sức khỏe.',1,1780151790,1780151790),(147124134,'65a10d7ca08a3',147123494,'Viết bài về cách đưa chăm sóc bản thân vào thói quen hàng ngày.',1,1780151790,1780151790),(147124135,'65a10d7ca0d87',147123494,'Tạo bài đăng chia sẻ cách giữ vận động trong những tháng mùa đông.',1,1780151790,1780151790),(147124136,'65a10d7ca119d',147123494,'Viết bài về tầm quan trọng của việc đặt mục tiêu và biến chúng thành hiện thực.',1,1780151790,1780151790),(147124137,'65a10d7ca15a9',147123494,'Viết bài về lợi ích của thói quen buổi sáng và cách xây dựng thói quen phù hợp với bạn.',1,1780151790,1780151790),(147124138,'65a10d7ca19e5',147123494,'Tạo bài đăng chia sẻ mẹo hàng đầu để duy trì động lực và đạt mục tiêu.',1,1780151790,1780151790),(147124139,'65a10d7ca1e68',147123494,'Viết bài về lợi ích của thiền định và cách bắt đầu.',1,1780151790,1780151790),(147124140,'65a10d7ca2277',147123494,'Tạo bài đăng chia sẻ ý tưởng món ăn vặt healthy yêu thích.',1,1780151790,1780151790),(147124141,'65a10d7ca2731',147123494,'Viết bài về tầm quan trọng của việc yêu bản thân và cách thực hành.',1,1780151790,1780151790),(147124142,'65a10d7ca2c1b',147123494,'Tạo bài đăng giới thiệu bài tập nhanh, dễ thực hiện dành cho người bận rộn.',1,1780151790,1780151790),(147124143,'65a10d7ca30b0',147123494,'Viết bài về lợi ích của lòng biết ơn và cách đưa vào cuộc sống hàng ngày.',1,1780151790,1780151790),(147124144,'65a10d7ca356f',147123494,'Tạo bài đăng chia sẻ mẹo yêu thích để giữ tập trung và năng suất.',1,1780151790,1780151790),(147124145,'65a10d7ca39d1',147123494,'Viết bài về tầm quan trọng của giấc ngủ và cách cải thiện thói quen ngủ.',1,1780151790,1780151790),(147124146,'65a10d7ca3e34',147123494,'Tạo bài đăng chia sẻ cách yêu thích để vận động khi đi du lịch.',1,1780151790,1780151790),(147124147,'65a10d7ca42de',147123494,'Viết bài về lợi ích của viết nhật ký và cách bắt đầu.',1,1780151790,1780151790),(147124148,'65a10d7ca4766',147123494,'Tạo bài đăng giới thiệu ý tưởng meal prep healthy yêu thích.',1,1780151790,1780151790),(147124149,'65a10d7ca4c57',147123494,'Viết bài về lợi ích của detox kỹ thuật số và cách thực hiện.',1,1780151790,1780151790),(147124150,'65a10d7ca50b3',147123494,'Tạo bài đăng chia sẻ cách yêu thích để vận động vào mùa hè.',1,1780151790,1780151790),(147124151,'65a10d7ca5565',147123494,'Viết bài về tầm quan trọng của tự phản tư và cách thực hành.',1,1780151790,1780151790),(147124152,'65a10d7ca5a40',147123494,'Tạo bài đăng giới thiệu bài giãn cơ yêu thích để thư giãn.',1,1780151790,1780151790),(147124153,'65a10d7ca5f63',147123494,'Viết bài về lợi ích của tư duy tích cực và cách rèn luyện.',1,1780151790,1780151790),(147124154,'65a10d7ca6313',147123494,'Tạo bài đăng chia sẻ cách yêu thích để vận động khi làm việc tại nhà.',1,1780151790,1780151790),(147124155,'65a10d7ca66fc',147123494,'Viết bài về tầm quan trọng của việc đặt ranh giới và cách thực hiện.',1,1780151790,1780151790),(147124156,'65a10d7ca6b4d',147123494,'Tạo bài đăng giới thiệu công thức sinh tố healthy yêu thích.',1,1780151790,1780151790),(147124157,'65a10d7ca7099',147123494,'Viết bài về lợi ích của thói quen biết ơn và cách bắt đầu.',1,1780151790,1780151790),(147124158,'65a10d7ca7575',147123494,'Tạo bài đăng chia sẻ mẹo hàng đầu để duy trì cân bằng công việc và sức khỏe.',1,1780151790,1780151790),(147124159,'65a10d7ca7ac4',147123494,'Viết bài về tầm quan trọng của tự nhận thức và cách rèn luyện.',1,1780151790,1780151790),(147124160,'65a10d7ca7ed7',147123494,'Viết bài về lợi ích của thiên nhiên và cách đưa vào cuộc sống hàng ngày.',1,1780151790,1780151790),(147124161,'65a10d7ca82dd',147123494,'Tạo bài đăng chia sẻ công thức món tráng miệng healthy.',1,1780151790,1780151790),(147124162,'65a10d7ca870c',147123494,'Viết bài về tầm quan trọng của việc chấp nhận bản thân và cách thực hành.',1,1780151790,1780151790),(147124163,'65a10d7ca8af6',147123494,'Tạo bài đăng giới thiệu bài tập nhanh, dễ thực hiện dành cho người mới bắt đầu.',1,1780151790,1780151790),(147124164,'65a10d7ca8fe7',147123494,'Viết bài về lợi ích của chánh niệm tại nơi làm việc.',1,1780151790,1780151790),(147124165,'65a10d7ca94ef',147123494,'Tạo bài đăng chia sẻ cách yêu thích để vận động vào mùa thu.',1,1780151790,1780151790),(147124166,'65a10d7ca9976',147123494,'Viết bài về tầm quan trọng của chăm sóc bản thân đối với sức khỏe tinh thần.',1,1780151790,1780151790),(147124167,'65a10d7ca9e0b',147123494,'Tạo bài đăng giới thiệu bài tập thở yêu thích để thư giãn.',1,1780151790,1780151790),(147124168,'65a10d7caa331',147123494,'Viết bài về lợi ích của tự phản tư trong hành trình phát triển bản thân.',1,1780151790,1780151790),(147124169,'65a10d7caa7c0',147123494,'Tạo bài đăng chia sẻ mẹo hàng đầu để giữ ngăn nắp và năng suất.',1,1780151790,1780151790),(147124170,'65a10d7caaca4',147123494,'Viết bài về tầm quan trọng của các mối quan hệ lành mạnh và cách nuôi dưỡng.',1,1780151790,1780151790),(147124171,'65a10d7cab16a',147123494,'Tạo bài đăng giới thiệu công thức món ăn vặt healthy tiện mang theo.',1,1780151790,1780151790),(147124172,'65a10d7cab69a',147123494,'Viết bài về lợi ích của tự nói tích cực và cách thực hành.',1,1780151790,1780151790),(147124173,'65a10d7cabc23',147123494,'Tạo bài đăng chia sẻ cách yêu thích để vận động vào mùa xuân.',1,1780151790,1780151790),(147124174,'65a10d7cac0bd',147123494,'Viết bài về tầm quan trọng của lòng tự thương và cách thực hành.',1,1780151790,1780151790),(147124175,'65a10d7cac5dc',147123494,'Tạo bài đăng chia sẻ cách yêu thích để xả stress sau một ngày dài.',1,1780151790,1780151790),(147124176,'65a10d7caca46',147123494,'Viết bài về tầm quan trọng của tư duy tích cực và cách rèn luyện.',1,1780151790,1780151790),(147124177,'65a10d7cace6a',147123494,'Tạo bài đăng giới thiệu công thức bữa sáng healthy và giải thích vì sao quan trọng.',1,1780151790,1780151790),(147124178,'65a10d7cad249',147123494,'Viết bài về lợi ích của việc tập luyện đều đặn và cách bắt đầu.',1,1780151790,1780151790),(147124179,'65a10d7cad660',147123494,'Tạo bài đăng chia sẻ mẹo hàng đầu để chăm sóc bản thân trong thời gian bận rộn.',1,1780151790,1780151790),(147124180,'65a10d7cadabd',147123494,'Viết bài về tầm quan trọng của quản lý thời gian và cách cải thiện kỹ năng.',1,1780151790,1780151790),(147124181,'65a10d7cadf2b',147123494,'Viết bài về lợi ích của thói quen biết ơn hàng ngày và cách biến thành thói quen.',1,1780151790,1780151790),(147124182,'65a10d7cae395',147123494,'Tạo bài đăng chia sẻ ý tưởng món ăn vặt healthy cho trẻ em.',1,1780151790,1780151790),(147124183,'65a10d7cae7fa',147123494,'Viết bài về tầm quan trọng của kỷ luật bản thân và cách rèn luyện.',1,1780151790,1780151790),(147124184,'65a10d7caec89',147123494,'Viết bài về lợi ích của chế độ ăn healthy và cách thay đổi bền vững.',1,1780151790,1780151790),(147124185,'65a10d7caf151',147123494,'Tạo bài đăng chia sẻ mẹo hàng đầu để giữ động lực khi tập luyện.',1,1780151790,1780151790),(147124186,'65a10d7caf580',147123494,'Viết bài về tầm quan trọng của khẳng định tích cực và cách sử dụng.',1,1780151790,1780151790),(147124187,'65a10d7caf9c8',147123494,'Tạo bài đăng giới thiệu bài tập thở yêu thích để giảm căng thẳng.',1,1780151790,1780151790),(147124188,'65a10d7cafe4d',147123494,'Viết bài về lợi ích của việc dành thời gian ở thiên nhiên đối với sức khỏe tinh thần.',1,1780151790,1780151790),(147124189,'65a10d7cb0323',147123494,'Tạo bài đăng chia sẻ cách yêu thích để vận động trong mùa lễ hội.',1,1780151790,1780151790),(147124190,'65a10d7cb07a1',147123494,'Viết bài về tầm quan trọng của chăm sóc bản thân đối với sức khỏe và hạnh phúc tổng thể.',1,1780151790,1780151790),(147124191,'65a10d7cb0b77',147123494,'Tạo bài đăng giới thiệu công thức bữa tối healthy phù hợp để meal prep.',1,1780151790,1780151790),(147124192,'65a10d7cb0f3d',147123494,'Viết bài về lợi ích của thói quen chăm sóc bản thân đều đặn và cách xây dựng.',1,1780151790,1780151790),(147124193,'65a10d7cb1304',147123495,'Tạo bài đăng giới thiệu {{ tin đăng bất động sản }}. Nhớ làm nổi bật {{ điểm nổi bật }}.',1,1780151790,1780151790),(147124194,'65a10d7cb16b8',147123495,'Viết bài đăng làm nổi bật lợi ích khi sống tại {{ khu vực/lân cận }}.',1,1780151790,1780151790),(147124195,'65a10d7cb1a71',147123495,'Tạo bài đăng chia sẻ mẹo dành cho người mua nhà lần đầu.',1,1780151790,1780151790),(147124196,'65a10d7cb1e34',147123495,'Viết bài đăng về xu hướng bất động sản mới nhất tại {{ thị trường địa phương }}.',1,1780151790,1780151790),(147124197,'65a10d7cb2221',147123495,'Tạo chú thích cho video tham quan ảo {{ loại bất động sản }}.',1,1780151790,1780151790),(147124198,'65a10d7cb25e0',147123495,'Viết bài đăng về tầm quan trọng của việc staging nhà trước khi bán.',1,1780151790,1780151790),(147124199,'65a10d7cb29be',147123495,'Viết bài đăng về lợi ích khi sử dụng môi giới bất động sản để mua hoặc bán nhà.',1,1780151790,1780151790),(147124200,'65a10d7cb2d85',147123495,'Tạo bài đăng về {{ giao dịch thành công gần đây }}.',1,1780151790,1780151790),(147124201,'65a10d7cb314a',147123495,'Viết bài đăng chia sẻ mẹo giúp người bán nhà chuẩn bị bất động sản trước khi rao bán.',1,1780151790,1780151790),(147124202,'65a10d7cb3501',147123495,'Viết bài đăng chia sẻ mẹo giúp người bán nhà chuẩn bị bất động sản trước khi rao bán.',1,1780151790,1780151790),(147124203,'65a10d7cb38d8',147123495,'Tạo bài đăng giới thiệu những khu vực sống tốt nhất tại {{ khu vực }}.',1,1780151790,1780151790),(147124204,'65a10d7cb3ca4',147123495,'Viết bài đăng về tầm quan trọng của ngoại thất ấn tượng khi bán nhà.',1,1780151790,1780151790),(147124205,'65a10d7cb4068',147123495,'Tạo chú thích cho bài đăng giới thiệu lời chứng thực từ khách hàng hài lòng sau:',1,1780151790,1780151790),(147124206,'65a10d7cb442a',147123495,'Viết bài đăng về lãi suất thế chấp mới nhất tại {{ khu vực }} và tác động tới người mua nhà.',1,1780151790,1780151790),(147124207,'65a10d7cb47f7',147123495,'Tạo bài đăng giới thiệu tiện ích của căn hộ hoặc khu nhà cao cấp.',1,1780151790,1780151790),(147124208,'65a10d7cb4baf',147123495,'Viết bài đăng về sự khác biệt giữa mua nhà xây mới và nhà đã qua sử dụng.',1,1780151790,1780151790),(147124209,'65a10d7cb4f78',147123495,'Tạo bài đăng giới thiệu các trường học tốt nhất tại {{ khu vực }}.',1,1780151790,1780151790),(147124210,'65a10d7cb5333',147123495,'Viết bài đăng về cách đàm phán giao dịch bất động sản.',1,1780151790,1780151790),(147124211,'65a10d7cb5695',147123495,'Tạo bài đăng giới thiệu {{ bất động sản/phong cách kiến trúc độc đáo }}.',1,1780151790,1780151790),(147124212,'65a10d7cb5a64',147123495,'Viết bài đăng về quy trình mua hoặc bán nhà tại {{ thị trường địa phương }}.',1,1780151790,1780151790),(147124213,'65a10d7cb5e2e',147123495,'Viết bài đăng về cách chọn môi giới bất động sản phù hợp nhu cầu.',1,1780151790,1780151790),(147124214,'65a10d7cb61fa',147123495,'Tạo bài đăng giới thiệu xu hướng cải tạo nhà hàng đầu giúp tăng giá trị bất động sản.',1,1780151790,1780151790),(147124215,'65a10d7cb65ce',147123495,'Viết bài đăng về ưu và nhược điểm của thuê nhà so với mua nhà.',1,1780151790,1780151790),(147124216,'65a10d7cb6a18',147123495,'Viết bài đăng về những sai lầm cần tránh khi mua hoặc bán nhà.',1,1780151790,1780151790),(147124217,'65a10d7cb6e2a',147123495,'Tạo bài đăng giới thiệu căn nhà với câu chuyện nền sau:',1,1780151790,1780151790),(147124218,'65a10d7cb7204',147123495,'Tạo bài đăng so sánh bất động sản ở trung tâm thành phố và ngoại ô.',1,1780151790,1780151790),(147124219,'65a10d7cb760a',147123495,'Viết bài đăng về tầm quan trọng của kiểm tra nhà đối với người mua.',1,1780151790,1780151790),(147124220,'65a10d7cb79f2',147123495,'Tạo bài đăng giới thiệu không gian ngoài trời tốt nhất tại {{ khu vực }} dành cho người thích tiếp khách.',1,1780151790,1780151790),(147124221,'65a10d7cb7e8c',147123495,'Viết bài đăng về cách marketing hiệu quả cho bất động sản cao cấp.',1,1780151790,1780151790),(147124222,'65a10d7cb8244',147123495,'Tạo bài đăng giới thiệu các căn nhà thân thiện môi trường tốt nhất tại {{ khu vực }}.',1,1780151790,1780151790),(147124223,'65a10d7cb8613',147123495,'Viết bài đăng về tầm quan trọng của vị trí khi mua hoặc bán nhà.',1,1780151790,1780151790),(147124224,'65a10d7cb89d7',147123495,'Tạo chú thích cho bài đăng trước và sau cải tạo nhà.',1,1780151790,1780151790),(147124225,'65a10d7cb8da3',147123495,'Viết bài đăng về lợi ích khi làm việc với môi giới chuyên về {{ phân khúc thị trường chuyên biệt }}.',1,1780151790,1780151790),(147124226,'65a10d7cb9160',147123495,'Tạo bài đăng giới thiệu các căn nhà thân thiện với thú cưng tốt nhất tại {{ khu vực }}.',1,1780151790,1780151790),(147124227,'65a10d7cb9534',147123495,'Viết bài đăng về cách đàm phán thành công khi có nhiều offer cho một căn nhà.',1,1780151790,1780151790),(147124228,'65a10d7cb995a',147123495,'Viết bài đăng về lợi ích của việc được duyệt trước khoản vay mua nhà.',1,1780151790,1780151790),(147124229,'65a10d7cb9da1',147123495,'Tạo bài đăng giới thiệu các khu vực tốt nhất cho gia đình trẻ tại {{ khu vực }}.',1,1780151790,1780151790),(147124230,'65a10d7cba189',147123495,'Viết bài đăng về cách xử lý cuộc đua giá khi mua nhà.',1,1780151790,1780151790),(147124231,'65a10d7cba553',147123495,'Tạo chú thích cho bài đăng giới thiệu bất động sản có view tuyệt đẹp.',1,1780151790,1780151790),(147124232,'65a10d7cba947',147123495,'Viết bài đăng về lợi ích của staging ảo khi bán nhà.',1,1780151790,1780151790),(147124233,'65a10d7cbad3a',147123495,'Chia sẻ bài đăng về xu hướng bất động sản nóng nhất tại {{ khu vực }}.',1,1780151790,1780151790),(147124234,'65a10d7cbb122',147123495,'Viết bài đăng về lý do đầu tư bất động sản là quyết định tài chính thông minh.',1,1780151790,1780151790),(147124235,'65a10d7cbb512',147123495,'Tạo bài đăng mạng xã hội làm nổi bật lợi ích khi sống tại {{ khu vực }}.',1,1780151790,1780151790),(147124236,'65a10d7cbb8d9',147123495,'Tạo bài đăng mạng xã hội về các yếu tố quan trọng nhất khi bán bất động sản.',1,1780151790,1780151790),(147124237,'65a10d7cbbcfd',147123495,'Tạo bài đăng mạng xã hội về các yếu tố quan trọng nhất khi đầu tư bất động sản.',1,1780151790,1780151790),(147124238,'65a10d7cbc104',147123495,'Tạo bài đăng mạng xã hội về xu hướng bất động sản đáng theo dõi trong năm tới.',1,1780151790,1780151790),(147124239,'65a10d7cbc4f5',147123495,'Tạo bài đăng mạng xã hội về lợi ích dùng mạng xã hội để marketing bất động sản.',1,1780151790,1780151790),(147124240,'65a10d7cbc8a6',147123495,'Tạo bài đăng mạng xã hội về cách staging bất động sản cho tham quan ảo.',1,1780151790,1780151790),(147124241,'65a10d7cbcc8b',147123495,'Tạo bài đăng mạng xã hội về những hiểu lầm và quan niệm sai phổ biến về bất động sản.',1,1780151790,1780151790),(147124242,'65a10d7cbd090',147123495,'Tạo bài đăng mạng xã hội về các yếu tố quan trọng nhất khi chọn khoản vay mua nhà.',1,1780151790,1780151790),(147124243,'65a10d7cbd465',147123495,'Tạo bài đăng mạng xã hội về xu hướng thiết kế nhà hàng đầu trong năm.',1,1780151790,1780151790),(147124244,'65a10d7cbd872',147123496,'Viết bài đăng mạng xã hội cảm ơn tình nguyện viên đã đóng góp cho tổ chức phi lợi nhuận.',1,1780151790,1780151790),(147124245,'65a10d7cbdc3a',147123496,'Viết chú thích cho bài đăng mạng xã hội với câu nói sau từ người được tổ chức phi lợi nhuận hỗ trợ tích cực:',1,1780151790,1780151790),(147124246,'65a10d7cbe332',147123496,'Tạo bài đăng quảng bá sự kiện gây quỹ sắp diễn ra cho {{ hoạt động/mục tiêu }}.',1,1780151790,1780151790),(147124247,'65a10d7cbe754',147123496,'Viết bài đăng chia sẻ mẹo giúp mọi người tham gia và ủng hộ tổ chức phi lợi nhuận.',1,1780151790,1780151790),(147124248,'65a10d7cbeb7e',147123496,'Tạo bài đăng giới thiệu {{ thành tựu }}.',1,1780151790,1780151790),(147124249,'65a10d7cbef2b',147123496,'Viết bài đăng làm nổi bật tầm quan trọng của {{ chương trình }}.',1,1780151790,1780151790),(147124250,'65a10d7cbf2f5',147123496,'Tạo chú thích cho ảnh giới thiệu đội ngũ tổ chức phi lợi nhuận và niềm đam mê với sứ mệnh.',1,1780151790,1780151790),(147124251,'65a10d7cbf6ad',147123496,'Tạo bài đăng kêu gọi quyên góp hoặc ủng hộ cho {{ chiến dịch cụ thể }}.',1,1780151790,1780151790),(147124252,'65a10d7cbfa6a',147123496,'Tạo bài đăng giới thiệu tác động của tình nguyện viên đối với thành công tổ chức phi lợi nhuận.',1,1780151790,1780151790),(147124253,'65a10d7cbfe33',147123496,'Viết chú thích cho video montage về hoạt động của tổ chức phi lợi nhuận.',1,1780151790,1780151790),(147124254,'65a10d7cc01f7',147123496,'Tạo bài đăng làm nổi bật tầm quan trọng của sự tham gia và ủng hộ cộng đồng dành cho tổ chức phi lợi nhuận.',1,1780151790,1780151790),(147124255,'65a10d7cc05ad',147123496,'Viết bài đăng giới thiệu tác động của nhà tài trợ hoặc đối tác doanh nghiệp đối với tổ chức phi lợi nhuận.',1,1780151790,1780151790),(147124256,'65a10d7cc0982',147123496,'Tạo bài đăng quảng bá cơ hội tình nguyện sắp diễn ra cho {{ hoạt động/mục tiêu }}.',1,1780151790,1780151790),(147124257,'65a10d7cc0d69',147123496,'Tạo bài đăng vinh danh công việc của đội ngũ và tình nguyện viên tổ chức phi lợi nhuận.',1,1780151790,1780151790),(147124258,'65a10d7cc113e',147123496,'Viết bài đăng chia sẻ mẹo giúp mọi người tạo tác động tích cực trong cộng đồng của mình.',1,1780151790,1780151790),(147124259,'65a10d7cc152b',147123496,'Tạo bài đăng làm nổi bật vai trò của hoạt động vận động trong sứ mệnh tổ chức phi lợi nhuận.',1,1780151790,1780151790),(147124260,'65a10d7cc1935',147123496,'Viết bài đăng giới thiệu tác động của hoạt động tình nguyện đối với {{ nhóm đối tượng }}.',1,1780151790,1780151790),(147124261,'65a10d7cc1d37',147123496,'Tạo bài đăng trả lời 3 câu hỏi thường gặp về tổ chức phi lợi nhuận.',1,1780151790,1780151790),(147124262,'65a10d7cc211f',147123496,'Tạo bài đăng quảng bá chiến dịch gây quỹ mới cho {{ hoạt động/mục tiêu }}.',1,1780151790,1780151790),(147124263,'65a10d7cc253f',147123496,'Tạo bài đăng làm nổi bật tầm quan trọng của việc đồng hành cùng cộng đồng.',1,1780151790,1780151790),(147124264,'65a10d7cc28e5',147123496,'Viết bài đăng làm nổi bật tác động của tình nguyện viên đối với sứ mệnh của tổ chức bạn.',1,1780151790,1780151790),(147124265,'65a10d7cc2cf8',147123496,'Viết bài đăng làm nổi bật tầm quan trọng của minh bạch và trách nhiệm giải trình trong quản lý tổ chức phi lợi nhuận.',1,1780151790,1780151790),(147124266,'65a10d7cc30f7',147123496,'Tạo ý tưởng nội dung cho bài đăng mạng xã hội nhằm nâng cao nhận thức về {{ mục tiêu xã hội }}.',1,1780151790,1780151790),(147124267,'65a10d7cc3500',147123496,'Tạo bài đăng gửi lời tri ân đến các nhà tài trợ, tình nguyện viên và những người ủng hộ tổ chức phi lợi nhuận của bạn.',1,1780151790,1780151790),(147124268,'65a10d7cc38f9',147123496,'Viết bài đăng chia sẻ mẹo giúp mọi người hỗ trợ tổ chức phi lợi nhuận của bạn mà không cần đóng góp tài chính.',1,1780151790,1780151790),(147124269,'65a10d7cc3cf7',147123496,'Tạo bài đăng quảng bá quan hệ đối tác hoặc sáng kiến hợp tác với {{ tên đối tác }}.',1,1780151790,1780151790),(147124270,'65a10d7cc40d1',147123496,'Viết bài đăng làm nổi bật tầm quan trọng của sự đồng cảm và lòng trắc ẩn.',1,1780151790,1780151790),(147124271,'65a10d7cc44de',147123496,'Tạo chú thích cho bài đăng \"throwback\" về một thời điểm trong lịch sử tổ chức và hành trình phát triển đến nay.',1,1780151790,1780151790),(147124272,'65a10d7cc48d3',147123496,'Tạo bài đăng truyền thông điệp hy vọng và cảm hứng đến người theo dõi, khuyến khích họ tham gia sứ mệnh của tổ chức phi lợi nhuận.',1,1780151790,1780151790),(147124273,'65a10d7cc4cb4',147123497,'Tạo bài đăng giới thiệu {{ sản phẩm/dịch vụ }}.',1,1780151790,1780151790),(147124274,'65a10d7cc5098',147123497,'Viết bài đăng kỷ niệm hành trình kinh doanh và bày tỏ lòng biết ơn với đội ngũ.',1,1780151790,1780151790),(147124275,'65a10d7cc5477',147123497,'Tạo bài đăng vinh danh {{ thành viên đội ngũ }} và chuyên môn của họ trong vai trò {{ vai trò }}.',1,1780151790,1780151790),(147124276,'65a10d7cc5859',147123497,'Viết chú thích cho video hậu trường hoạt động kinh doanh của bạn.',1,1780151790,1780151790),(147124277,'65a10d7cc5bc9',147123497,'Tạo bài đăng kỷ niệm {{ thành tựu/cột mốc }}.',1,1780151790,1780151790),(147124278,'65a10d7cc5fa4',147123497,'Viết bài đăng chia sẻ mẹo hoặc lời khuyên liên quan đến {{ chủ đề }}.',1,1780151790,1780151790),(147124279,'65a10d7cc638c',147123497,'Tạo chú thích cho hình ảnh kèm lời chứng thực của khách hàng sau đây:',1,1780151790,1780151790),(147124280,'65a10d7cc67b0',147123497,'Tạo bài đăng giới thiệu các giá trị cốt lõi của doanh nghiệp sau:',1,1780151790,1780151790),(147124281,'65a10d7cc6b6b',147123497,'Viết bài đăng quảng bá {{ sự kiện sắp tới }}.',1,1780151790,1780151790),(147124282,'65a10d7cc6f3e',147123497,'Viết chú thích cho hình ảnh gợi ý sơ bộ về {{ sản phẩm }}.',1,1780151790,1780151790),(147124283,'65a10d7cc7329',147123497,'Tạo chú thích cho bài đăng chia sẻ bài báo hoặc blog liên quan về {{ chủ đề }}.',1,1780151790,1780151790),(147124284,'65a10d7cc771c',147123497,'Viết bài đăng hỏi ý kiến đối tượng mục tiêu về phản hồi hoặc gợi ý cho {{ sản phẩm/dịch vụ }}.',1,1780151790,1780151790),(147124285,'65a10d7cc7b07',147123497,'Tạo bài đăng về những khó khăn khi làm doanh nhân.',1,1780151790,1780151790),(147124286,'65a10d7cc7e75',147123497,'Viết bài đăng mạng xã hội xoay quanh câu nói kinh doanh sau:',1,1780151790,1780151790),(147124287,'65a10d7cc8263',147123497,'Tạo bài đăng chia sẻ điểm bán hàng độc đáo sau:',1,1780151790,1780151790),(147124288,'65a10d7cc8648',147123497,'Viết bài đăng giới thiệu chương trình khuyến mãi đặc biệt với {{ mức giảm giá }}.',1,1780151790,1780151790),(147124289,'65a10d7cc8a12',147123497,'Viết chú thích cho video một ngày làm việc của {{ nghề nghiệp }}.',1,1780151790,1780151790),(147124290,'65a10d7cc8dde',147123497,'Viết bài đăng mạng xã hội giải quyết rào cản hoặc nghi ngại mà {{ chân dung khách hàng lý tưởng }} có thể gặp khi dùng {{ sản phẩm/dịch vụ }}.',1,1780151790,1780151790),(147124291,'65a10d7cc91ce',147123497,'Tạo bài đăng chia sẻ thông điệp truyền cảm hứng về {{ chủ đề }}.',1,1780151790,1780151790),(147124292,'65a10d7cc95ae',147123497,'Viết bài đăng quảng bá bài blog mới về {{ chủ đề }}.',1,1780151790,1780151790),(147124293,'65a10d7cc99db',147123497,'Tạo bài đăng thể hiện cam kết của công ty đối với {{ giá trị }}.',1,1780151790,1780151790),(147124294,'65a10d7cc9dbd',147123497,'Tạo bài đăng gợi ý sách phù hợp cho {{ mục tiêu }}.',1,1780151790,1780151790),(147124295,'65a10d7cca1a9',147123497,'Viết bài đăng mời đối tượng mục tiêu chia sẻ câu chuyện thành công của họ.',1,1780151790,1780151790),(147124296,'65a10d7cca570',147123497,'Viết bài đăng chia sẻ xu hướng đáng chú ý trong {{ ngành }}.',1,1780151790,1780151790),(147124297,'65a10d7cca933',147123497,'Viết bài đăng chia sẻ cách vượt qua thử thách nghề nghiệp trong {{ lĩnh vực }}.',1,1780151790,1780151790),(147124298,'65a10d7ccad0c',147123497,'Tạo bài đăng chia sẻ bài học bạn rút ra khi làm doanh nhân.',1,1780151790,1780151790),(147124299,'65a10d7ccb115',147123498,'Tạo bài đăng làm nổi bật 3 sai lầm marketing mà hộ kinh doanh nhỏ thường mắc phải.',1,1780151790,1780151790),(147124300,'65a10d7ccb4fd',147123498,'Viết chú thích cho ảnh giới thiệu đội ngũ tại văn phòng.',1,1780151790,1780151790),(147124301,'65a10d7ccb8cb',147123498,'Tạo bài đăng thông báo {{ dịch vụ mới }} mà agency đang cung cấp cho {{ nhu cầu }}.',1,1780151790,1780151790),(147124302,'65a10d7ccbc9f',147123498,'Viết bài đăng làm nổi bật tầm quan trọng của phản hồi khách hàng trong việc xây dựng chiến lược marketing hiệu quả.',1,1780151790,1780151790),(147124303,'65a10d7ccc0a1',147123498,'Chia sẻ bài đăng về vai trò của storytelling trong marketing hiệu quả.',1,1780151790,1780151790),(147124304,'65a10d7ccc45a',147123498,'Viết bài đăng chia sẻ lợi ích của việc dùng phân tích mạng xã hội để định hướng chiến lược marketing.',1,1780151790,1780151790),(147124305,'65a10d7ccc81a',147123498,'Chia sẻ bài đăng làm nổi bật tầm quan trọng của tối ưu mobile trong thiết kế website.',1,1780151790,1780151790),(147124306,'65a10d7cccbd7',147123498,'Tạo bài đăng giới thiệu {{ dịch vụ }} của agency.',1,1780151790,1780151790),(147124307,'65a10d7cccf9f',147123498,'Viết bài đăng chia sẻ mẹo tạo nội dung video marketing hiệu quả.',1,1780151790,1780151790),(147124308,'65a10d7ccd35f',147123498,'Viết bài đăng quảng bá {{ sự kiện sắp tới }}.',1,1780151790,1780151790),(147124309,'65a10d7ccd72d',147123498,'Tạo bài đăng chia sẻ 10 mẹo thành công trong marketing.',1,1780151790,1780151790),(147124310,'65a10d7ccdaf2',147123498,'Tạo bài đăng quảng bá chương trình giới thiệu khách hàng của agency.',1,1780151790,1780151790),(147124311,'65a10d7ccdeb7',147123498,'Tạo bài đăng thể hiện tác động của influencer marketing đến tăng trưởng doanh nghiệp.',1,1780151790,1780151790),(147124312,'65a10d7cce274',147123498,'Viết chú thích cho bài đăng thông báo buổi hỏi đáp trực tiếp với thành viên agency về {{ chủ đề }}.',1,1780151790,1780151790),(147124313,'65a10d7cce637',147123498,'Tạo bài đăng giới thiệu lợi ích của phân khúc email trong email marketing hiệu quả.',1,1780151790,1780151790),(147124314,'65a10d7ccea02',147123498,'Tạo bài đăng chia sẻ một sự thật thú vị về {{ chủ đề }}.',1,1780151790,1780151790),(147124315,'65a10d7ccedb3',147123498,'Tạo bài đăng bày tỏ lòng tri ân với khách hàng.',1,1780151790,1780151790),(147124316,'65a10d7ccf1a2',147123498,'Viết bài đăng chia sẻ mẹo quản lý tài khoản mạng xã hội khi làm {{ nghề nghiệp }}.',1,1780151790,1780151790),(147124317,'65a10d7ccf564',147123498,'Tạo bài đăng chia sẻ mẹo email marketing hiệu quả.',1,1780151790,1780151790),(147124318,'65a10d7ccf930',147123498,'Tạo bài đăng chia sẻ mẹo quảng cáo mạng xã hội thành công.',1,1780151790,1780151790),(147124319,'65a10d7ccfd06',147123498,'Tạo bài đăng về những điều nên và không nên làm trong marketing.',1,1780151790,1780151790),(147124320,'65a10d7cd00bb',147123498,'Viết bài đăng mạng xã hội về các huyền thoại sai lầm trong marketing.',1,1780151790,1780151790),(147124321,'65a10d7cd04c6',147123498,'Viết bài đăng chia sẻ mẹo SEO thành công.',1,1780151790,1780151790),(147124322,'65a10d7cd088b',147123498,'Viết bài đăng giới thiệu công cụ marketing cho {{ mục tiêu }}.',1,1780151790,1780151790),(147124323,'65a10d7cd0c62',147123498,'Tạo bài đăng chia sẻ mẹo influencer marketing thành công.',1,1780151790,1780151790),(147124324,'65a10d7cd1077',147123498,'Viết bài đăng chia sẻ mẹo tạo landing page hiệu quả để thu hút khách hàng tiềm năng.',1,1780151790,1780151790),(147124325,'65a10d7cd143a',147123498,'Viết bài đăng chia sẻ mẹo sáng tạo nội dung thành công.',1,1780151790,1780151790),(147124326,'65a10d7cd180c',147123498,'Viết bài đăng chia sẻ mẹo tương tác khách hàng hiệu quả.',1,1780151790,1780151790),(147124327,'65a10d7cd1be1',147123498,'Tạo bài đăng về xu hướng marketing năm {{ năm }}.',1,1780151790,1780151790),(147124328,'65a10d7cd1f93',147123498,'Viết bài đăng chia sẻ mẹo giữ chân khách hàng thành công.',1,1780151790,1780151790),(147124329,'65a10d7cd237d',147123498,'Tạo bài đăng về cách vượt qua thử thách trong marketing.',1,1780151790,1780151790),(147124330,'65a10d7cd2742',147123498,'Viết bài đăng chia sẻ mẹo mobile marketing thành công.',1,1780151790,1780151790),(147124331,'65a10d7cd2adf',147123498,'Viết bài đăng chia sẻ mẹo quảng cáo PPC thành công.',1,1780151790,1780151790),(147124332,'65a10d7cd2eac',147123498,'Tạo một câu đùa vui về marketing.',1,1780151790,1780151790),(147124333,'65a10d7cd327d',147123498,'Viết bài đăng chia sẻ mẹo thu hút khách hàng tiềm năng thành công.',1,1780151790,1780151790),(147124334,'65a10d7cd3659',147123498,'Tạo bài đăng quảng bá bài viết chuyên ngành mà agency vừa xuất bản.',1,1780151790,1780151790),(147124335,'65a10d7cd3a37',147123498,'Tạo bài đăng quảng bá cuốn sách mới nhất kèm lời kêu gọi mua hàng.',1,1780151790,1780151790),(147124336,'65a10d7cd3e2b',147123498,'Tạo bài đăng chia sẻ mẹo viết lách cho tác giả mới.',1,1780151790,1780151790),(147124337,'65a10d7cd41f8',147123498,'Viết bài đăng hỏi đối tượng mục tiêu gợi ý sách hay.',1,1780151790,1780151790),(147124338,'65a10d7cd45c2',147123498,'Tạo chú thích cho bài đăng trích dẫn từ cuốn sách mới nhất và mời người theo dõi chia sẻ câu trích dẫn yêu thích. Đây là câu trích dẫn:',1,1780151790,1780151790),(147124339,'65a10d7cd499f',147123498,'Viết chú thích cho ảnh gợi ý bìa sách sắp ra mắt.',1,1780151790,1780151790),(147124340,'65a10d7cd4d64',147123498,'Viết bài đăng khuyến khích đối tượng mục tiêu chia sẻ mục tiêu viết lách trong năm.',1,1780151790,1780151790),(147124341,'65a10d7cd5126',147123498,'Viết bài đăng khuyến khích đối tượng mục tiêu chia sẻ bìa sách yêu thích và lý do họ thích.',1,1780151790,1780151790),(147124342,'65a10d7cd54b3',147123498,'Tạo bài đăng chia sẻ tài nguyên hoặc công cụ hỗ trợ viết.',1,1780151790,1780151790),(147124343,'65a10d7cd5883',147123498,'Viết bài đăng khuyến khích người theo dõi chia sẻ blog hoặc podcast viết lách yêu thích.',1,1780151790,1780151790),(147124344,'65a10d7cd5c41',147123498,'Tạo chú thích cho hình ảnh trích đoạn hoặc teaser sách.',1,1780151790,1780151790),(147124345,'65a10d7cd6001',147123498,'Viết bài đăng chia sẻ những câu nói vui về sách.',1,1780151790,1780151790),(147124346,'65a10d7cd63c6',147123498,'Tạo bài đăng hỏi đối tượng mục tiêu thể loại sách yêu thích.',1,1780151790,1780151790),(147124347,'65a10d7cd678e',147123498,'Viết bài đăng chia sẻ câu nói hoặc thần chú truyền cảm hứng về viết lách.',1,1780151790,1780151790),(147124348,'65a10d7cd6b89',147123498,'Tạo bài đăng giới thiệu chương trình tặng sách hoặc cuộc thi.',1,1780151790,1780151790),(147124349,'65a10d7cd6fa3',147123498,'Viết bài đăng chia sẻ gợi ý hoặc bài tập viết.',1,1780151790,1780151790),(147124350,'65a10d7cd73a2',147123498,'Tạo bài đăng thể hiện tầm quan trọng của beta reader và cách tìm họ.',1,1780151790,1780151790),(147124351,'65a10d7cd77f3',147123498,'Viết bài đăng chia sẻ nhân vật hoặc cuốn sách văn học yêu thích.',1,1780151790,1780151790),(147124352,'65a10d7cd7bf7',147123498,'Tạo bài đăng đưa ra lời khuyên viết lách cho tác giả mới.',1,1780151790,1780151790),(147124353,'65a10d7cd7fe2',147123498,'Tạo bài đăng thông báo buổi ký tặng sách sắp diễn ra tại {{ ngày và địa điểm }}.',1,1780151790,1780151790),(147124354,'65a10d7cd8378',147123498,'Viết bài đăng khuyến khích đối tượng mục tiêu chia sẻ hiệu sách yêu thích.',1,1780151790,1780151790),(147124355,'65a10d7cd8745',147123498,'Tạo bài đăng chia sẻ thử thách viết lách và cách bạn vượt qua.',1,1780151790,1780151790),(147124356,'65a10d7cd8b1d',147123498,'Tạo bài đăng chia sẻ gợi ý viết yêu thích để vượt qua bế tắc sáng tạo.',1,1780151790,1780151790),(147124357,'65a10d7cd8ee0',147123498,'Viết bài đăng khuyến khích đối tượng mục tiêu chia sẻ ứng dụng hoặc phần mềm viết yêu thích.',1,1780151790,1780151790),(147124358,'65a10d7cd92a5',147123498,'Tạo bài đăng đánh giá sách hoặc tài nguyên liên quan đến viết lách.',1,1780151790,1780151790),(147124359,'65a10d7cd96a6',147123498,'Viết bài đăng chia sẻ bài tập viết để phát triển nhân vật.',1,1780151790,1780151790),(147124360,'65a10d7cd9a89',147123498,'Viết bài đăng khuyến khích đối tượng mục tiêu chia sẻ món ăn vặt hoặc đồ uống yêu thích khi viết.',1,1780151790,1780151790),(147124361,'65a10d7cda1b1',147123498,'Tạo bài đăng chia sẻ podcast hoặc kênh YouTube về viết lách yêu thích.',1,1780151790,1780151790),(147124362,'65a10d7cda57e',147123498,'Tạo bài đăng chia sẻ gợi ý viết để phát triển cốt truyện.',1,1780151790,1780151790),(147124363,'65a10d7cda958',147123498,'Tạo bài đăng làm nổi bật tầm quan trọng của biên tập và hiệu đính.',1,1780151790,1780151790),(147124364,'65a10d7cdad25',147123498,'Viết bài đăng chia sẻ gợi ý viết yêu thích để khơi nguồn ý tưởng.',1,1780151790,1780151790),(147124365,'65a10d7cdb0fc',147123498,'Tạo bài đăng với câu đùa hoặc trích dẫn hài hước về viết lách.',1,1780151790,1780151790),(147124366,'65a10d7cdb4f8',147123498,'Tạo bài đăng làm nổi bật {{ thành tựu }}.',1,1780151790,1780151790),(147124367,'65a10d7cdb997',147123498,'Viết bài đăng giới thiệu trò chơi hoặc thử thách viết lách cho người theo dõi.',1,1780151790,1780151790),(147124368,'65a10d7cdbd9d',147123498,'Tạo bài đăng chia sẻ gợi ý viết yêu thích để xây dựng xung đột trong truyện.',1,1780151790,1780151790),(147124369,'65a10d7cdc16a',147123498,'Viết bài đăng khuyến khích đối tượng mục tiêu chia sẻ blog hoặc website về viết lách yêu thích.',1,1780151790,1780151790),(147124370,'65a10d7cdc573',147123498,'Viết bài đăng chia sẻ gợi ý viết yêu thích để phát triển đối thoại.',1,1780151790,1780151790),(147124371,'65a10d7cdc972',147123499,'Viết bài đăng về lợi ích khi thuê công ty kế toán quản lý tài chính.',1,1780151790,1780151790),(147124372,'65a10d7cdcd3c',147123499,'Viết bài đăng giải thích các thay đổi thuế trong năm hiện tại.',1,1780151790,1780151790),(147124373,'65a10d7cdd129',147123499,'Tạo chuỗi bài đăng về kế hoạch thuế cho hộ kinh doanh nhỏ.',1,1780151790,1780151790),(147124374,'65a10d7cdd4f2',147123499,'Viết bài đăng về tầm quan trọng của việc lưu giữ hồ sơ tài chính.',1,1780151790,1780151790),(147124375,'65a10d7cdd8cd',147123499,'Viết bài đăng về vai trò của công nghệ trong kế toán.',1,1780151790,1780151790),(147124376,'65a10d7cddca7',147123499,'Tạo chú thích cho video hướng dẫn sử dụng phần mềm kế toán.',1,1780151790,1780151790),(147124377,'65a10d7cde082',147123499,'Viết bài đăng về cách chuẩn bị cho cuộc thanh tra thuế.',1,1780151790,1780151790),(147124378,'65a10d7cde454',147123499,'Tạo bài quiz kiến thức kế toán cơ bản dành cho chủ hộ kinh doanh nhỏ.',1,1780151790,1780151790),(147124379,'65a10d7cde91e',147123499,'Viết bài đăng về cách giảm thiểu nghĩa vụ thuế cho hộ kinh doanh nhỏ.',1,1780151790,1780151790),(147124380,'65a10d7cdecf4',147123499,'Tạo chuỗi bài đăng về thực hành ghi sổ kế toán tốt nhất.',1,1780151790,1780151790),(147124381,'65a10d7cdf0d1',147123499,'Viết bài đăng về sự khác biệt giữa kế toán tiền mặt và kế toán dồn tích.',1,1780151790,1780151790),(147124382,'65a10d7cdf518',147123499,'Viết bài đăng về tầm quan trọng của lập ngân sách.',1,1780151790,1780151790),(147124383,'65a10d7cdf936',147123499,'Tạo bài đăng mạng xã hội hướng dẫn lập ngân sách kinh doanh.',1,1780151790,1780151790),(147124384,'65a10d7cdfd2e',147123499,'Viết bài đăng về lợi ích khi thuê đội ngũ kế toán thuê ngoài.',1,1780151790,1780151790),(147124385,'65a10d7ce011b',147123499,'Tạo chuỗi bài đăng về cách quản lý dòng tiền cho hộ kinh doanh nhỏ.',1,1780151790,1780151790),(147124386,'65a10d7ce0508',147123499,'Viết bài đăng về các khoản khấu trừ thuế hàng đầu cho hộ kinh doanh nhỏ.',1,1780151790,1780151790),(147124387,'65a10d7ce08ff',147123499,'Tạo video hướng dẫn chuẩn bị cho mùa khai thuế.',1,1780151790,1780151790),(147124388,'65a10d7ce0cbb',147123499,'Viết bài đăng về vai trò của bộ phận kế toán trong doanh nghiệp.',1,1780151790,1780151790),(147124389,'65a10d7ce1084',147123499,'Viết bài đăng về các tác động thuế khi khởi nghiệp.',1,1780151790,1780151790),(147124390,'65a10d7ce143d',147123499,'Tạo video hướng dẫn đối chiếu sao kê ngân hàng.',1,1780151790,1780151790),(147124391,'65a10d7ce180e',147123499,'Viết bài đăng về lợi ích của phần mềm kế toán trên nền tảng đám mây.',1,1780151790,1780151790),(147124392,'65a10d7ce1bbf',147123499,'Tạo chuỗi bài đăng về tuân thủ thuế cho hộ kinh doanh nhỏ.',1,1780151790,1780151790),(147124393,'65a10d7ce1fb5',147123499,'Viết bài đăng về cách lập kế hoạch tài chính cho nghỉ hưu.',1,1780151790,1780151790),(147124394,'65a10d7ce239a',147123499,'Viết bài đăng về cách tránh các sai lầm kế toán phổ biến.',1,1780151790,1780151790),(147124395,'65a10d7ce27a7',147123499,'Tạo video hướng dẫn quản lý công nợ phải trả và phải thu.',1,1780151790,1780151790),(147124396,'65a10d7ce2b69',147123499,'Viết bài đăng về tầm quan trọng của báo cáo tài chính chính xác.',1,1780151790,1780151790),(147124397,'65a10d7ce2f78',147123499,'Viết bài đăng về lợi ích khi có đội ngũ kế toán chuyên trách.',1,1780151790,1780151790),(147124398,'65a10d7ce3331',147123499,'Tạo infographic về các khoản khấu trừ thuế cho kinh doanh tại nhà.',1,1780151790,1780151790),(147124399,'65a10d7ce3703',147123499,'Viết bài đăng về cách chọn phần mềm kế toán phù hợp cho doanh nghiệp.',1,1780151790,1780151790),(147124400,'65a10d7ce3b24',147123499,'Tạo chuỗi bài đăng về kế hoạch thuế cho cá nhân.',1,1780151790,1780151790),(147124401,'65a10d7ce3eea',147123499,'Viết bài đăng về sự khác biệt giữa CPA và kế toán viên.',1,1780151790,1780151790),(147124402,'65a10d7ce42b1',147123499,'Viết bài đăng về lợi ích của tự động hóa trong kế toán.',1,1780151790,1780151790),(147124403,'65a10d7ce46e9',147123499,'Viết bài đăng về tầm quan trọng của việc theo dõi hàng tồn kho.',1,1780151790,1780151790),(147124404,'65a10d7ce4abd',147123499,'Tạo chuỗi bài đăng hướng dẫn đọc báo cáo tài chính.',1,1780151790,1780151790),(147124405,'65a10d7ce4e76',147123499,'Viết bài đăng về cách lập hệ thống tài khoản kế toán.',1,1780151790,1780151790),(147124406,'65a10d7ce5245',147123499,'Tạo infographic về các khoản tín dụng thuế cho hộ kinh doanh nhỏ.',1,1780151790,1780151790),(147124407,'65a10d7ce55f2',147123499,'Tạo chuỗi bài đăng về kế toán cho tổ chức phi lợi nhuận.',1,1780151790,1780151790),(147124408,'65a10d7ce597e',147123499,'Tạo chuỗi bài đăng hướng dẫn đọc và phân tích các chỉ số tài chính.',1,1780151790,1780151790),(147124409,'65a10d7ce5d33',147123499,'Viết bài đăng về cách chọn phương pháp kế toán phù hợp cho doanh nghiệp.',1,1780151790,1780151790),(147124410,'65a10d7ce6107',147123499,'Tạo công cụ tương tác tính nghĩa vụ thuế theo các mức thu nhập khác nhau.',1,1780151790,1780151790),(147124411,'65a10d7ce64b3',147123499,'Viết bài đăng về tầm quan trọng của hiểu biết tài chính đối với doanh nhân.',1,1780151790,1780151790),(147124412,'65a10d7ce685d',147123499,'Viết bài đăng về cách lập kế hoạch tài chính cho doanh nghiệp mới.',1,1780151790,1780151790),(147124413,'65a10d7ce6c71',147123499,'Viết bài đăng về lợi ích khi có đội ngũ kế toán làm việc từ xa.',1,1780151790,1780151790),(147124414,'65a10d7ce7071',147123499,'Viết bài đăng về cách giảm thiểu nghĩa vụ thuế cho freelancer và lao động tự do.',1,1780151790,1780151790),(147124415,'65a10d7ce7469',147123499,'Viết bài đăng về tầm quan trọng của ghi chép tài chính chính xác cho hộ kinh doanh nhỏ.',1,1780151790,1780151790),(147124416,'65a10d7ce786b',147123499,'Tạo chuỗi bài đăng hướng dẫn lập và quản lý ngân sách cho tổ chức phi lợi nhuận.',1,1780151790,1780151790),(147124417,'65a10d7ce7c9f',147123499,'Viết bài đăng về lợi ích khi thuê ngoài công việc ghi sổ và kế toán.',1,1780151790,1780151790),(147124418,'65a10d7ce80bf',147123499,'Viết bài đăng về sự khác biệt giữa ghi sổ một bên và ghi sổ kép.',1,1780151790,1780151790),(147124419,'65a10d7ce84a6',147123499,'Viết bài đăng về các tác động thuế khi thuê cộng tác viên độc lập.',1,1780151790,1780151790),(147124420,'65a10d7ce88ca',147123499,'Tạo chuỗi bài đăng hướng dẫn tính và theo dõi khấu hao tài sản cố định.',1,1780151790,1780151790),(147124421,'65a10d7ce8c95',147123499,'Viết bài đăng về cách chuẩn bị cho cuộc thanh tra thuế.',1,1780151790,1780151790),(147124422,'65a10d7ce90a6',147123499,'Viết bài đăng về vai trò của kế toán trong định giá doanh nghiệp.',1,1780151790,1780151790),(147124423,'65a10d7ce94db',147123499,'Tạo video hướng dẫn tính điểm hòa vốn cho hộ kinh doanh nhỏ.',1,1780151790,1780151790),(147124424,'65a10d7ce98a6',147123499,'Viết bài đăng về lợi ích khi có cố vấn tài chính cho kế hoạch nghỉ hưu.',1,1780151790,1780151790),(147124425,'65a10d7ce9c89',147123499,'Tạo chuỗi bài đăng hướng dẫn quản lý công nợ phải trả và phải thu cho hộ kinh doanh nhỏ.',1,1780151790,1780151790),(147124426,'65a10d7cea096',147123499,'Viết bài đăng về các tác động thuế khi bán doanh nghiệp.',1,1780151790,1780151790),(147124427,'65a10d7cea453',147123499,'Viết bài đăng về sự khác biệt giữa khai thuế và lập kế hoạch thuế.',1,1780151790,1780151790),(147124428,'65a10d7cea81e',147123500,'Viết bài đăng mời người theo dõi thử các món theo mùa mới trên menu.',1,1780151790,1780151790),(147124429,'65a10d7ceabd4',147123500,'Tạo chú thích cho ảnh giới thiệu khu vực ăn uống ngoài trời của nhà hàng.',1,1780151790,1780151790),(147124430,'65a10d7ceafe7',147123500,'Tạo chú thích cho hình ảnh kèm đánh giá của khách hàng và cảm ơn lời khen.',1,1780151790,1780151790),(147124431,'65a10d7ceb39c',147123500,'Viết bài đăng khuyến khích người theo dõi ghé nhà hàng cho {{ sự kiện }}.',1,1780151790,1780151790),(147124432,'65a10d7ceb786',147123500,'Viết bài đăng hỏi người theo dõi món ăn yêu thích tại nhà hàng và lý do.',1,1780151790,1780151790),(147124433,'65a10d7cebbb9',147123500,'Tạo bài đăng chúc mừng kỷ niệm làm việc của thành viên đội ngũ.',1,1780151790,1780151790),(147124434,'65a10d7cebfd3',147123500,'Tạo bài đăng làm nổi bật nỗ lực thân thiện môi trường của nhà hàng, như ủ phân hoặc giảm rác thải.',1,1780151790,1780151790),(147124435,'65a10d7cec3a7',147123500,'Viết bài đăng giới thiệu dịch vụ phòng ăn riêng của nhà hàng cho sự kiện và lễ kỷ niệm.',1,1780151790,1780151790),(147124436,'65a10d7cec788',147123500,'Tạo bài đăng giới thiệu {{ công thức từ menu }} mà người theo dõi có thể tự làm tại nhà.',1,1780151790,1780151790),(147124437,'65a10d7cecb47',147123500,'Viết bài đăng mời người theo dõi bình chọn món tiếp theo sẽ được giới thiệu trên menu.',1,1780151790,1780151790),(147124438,'65a10d7cecf2d',147123500,'Tạo bài đăng giới thiệu {{ ưu đãi happy hour của nhà hàng }}.',1,1780151790,1780151790),(147124439,'65a10d7ced2ea',147123500,'Viết bài đăng kể về lịch sử và ý nghĩa của {{ món ăn từ menu }}.',1,1780151790,1780151790),(147124440,'65a10d7ced6ab',147123500,'Viết bài đăng quảng bá sự kiện từ thiện hoặc gây quỹ do nhà hàng tổ chức.',1,1780151790,1780151790),(147124441,'65a10d7ceda60',147123500,'Tạo bài đăng giới thiệu món đặc biệt được tạo riêng cho ngày lễ hoặc dịp đặc biệt.',1,1780151790,1780151790),(147124442,'65a10d7cede3e',147123500,'Viết bài đăng khuyến khích người theo dõi chia sẻ ảnh món ăn yêu thích tại nhà hàng bằng hashtag cụ thể.',1,1780151790,1780151790),(147124443,'65a10d7cee1f5',147123500,'Tạo bài đăng giới thiệu menu gia đình hoặc món ăn chia sẻ tại nhà hàng.',1,1780151790,1780151790),(147124444,'65a10d7cee5ba',147123500,'Viết bài đăng giới thiệu khách hàng trung thành đã gắn bó với nhà hàng nhiều năm.',1,1780151790,1780151790),(147124445,'65a10d7cee943',147123500,'Tạo bài đăng quảng bá dịch vụ catering của nhà hàng cho sự kiện.',1,1780151790,1780151790),(147124446,'65a10d7ceed10',147123500,'Viết bài đăng giới thiệu lớp học pha cocktail hoặc nấu ăn của nhà hàng.',1,1780151790,1780151790),(147124447,'65a10d7cef0c9',147123500,'Viết bài đăng làm nổi bật các lựa chọn món chay hoặc thuần chay trên menu.',1,1780151790,1780151790),(147124448,'65a10d7cef48a',147123500,'Viết bài đăng quảng bá ưu đãi cho người theo dõi khi chia sẻ bài hoặc tag bạn bè.',1,1780151790,1780151790),(147124449,'65a10d7cef850',147123500,'Tạo bài đăng quảng bá menu brunch và cocktail mimosa của nhà hàng.',1,1780151790,1780151790),(147124450,'65a10d7cefc81',147123500,'Viết bài đăng chia sẻ công thức cocktail hoặc đồ uống phổ biến tại nhà hàng.',1,1780151790,1780151790),(147124451,'65a10d7cf0052',147123500,'Tạo chú thích cho hình ảnh trang trí hoặc không gian theo mùa của nhà hàng.',1,1780151790,1780151790),(147124452,'65a10d7cf0422',147123500,'Viết bài đăng quảng bá dịch vụ giao hàng hoặc mang về của nhà hàng.',1,1780151790,1780151790),(147124453,'65a10d7cf07db',147123500,'Viết bài đăng giới thiệu ưu đãi đồ uống trong giờ happy hour.',1,1780151790,1780151790),(147124454,'65a10d7cf0ba7',147123500,'Tạo bài đăng quảng bá giveaway hoặc cuộc thi cho người theo dõi tương tác. Viết bài đăng chia sẻ mẹo nấu ăn và thưởng thức.',1,1780151790,1780151790),(147124455,'65a10d7cf0f70',147123500,'Tạo bài đăng thể hiện nỗ lực giảm lãng phí thực phẩm hoặc quyên góp thực phẩm cho tổ chức địa phương.',1,1780151790,1780151790),(147124456,'65a10d7cf132b',147123500,'Tạo bài đăng giới thiệu món không gluten, không sữa hoặc phù hợp với người dị ứng.',1,1780151790,1780151790),(147124457,'65a10d7cf16de',147123500,'Viết bài đăng giới thiệu món ăn lý tưởng cho buổi hẹn hò lãng mạn.',1,1780151790,1780151790),(147124458,'65a10d7cf1abf',147123500,'Viết bài đăng mời người theo dõi bình chọn cocktail đặc biệt tiếp theo tại quầy bar.',1,1780151790,1780151790),(147124459,'65a10d7cf1e9b',147123500,'Viết bài đăng giới thiệu món ăn phù hợp cho bữa tối gia đình hoặc sum họp.',1,1780151790,1780151790),(147124460,'65a10d7cf2258',147123501,'Tạo bài đăng giới thiệu công thức meal prep healthy dễ làm tại nhà.',1,1780151790,1780151790),(147124461,'65a10d7cf2610',147123501,'Viết lời nhắn truyền động lực cho những ai đang gặp khó khăn khi bắt đầu hành trình fitness.',1,1780151790,1780151790),(147124462,'65a10d7cf29c9',147123501,'Tạo bài đăng giới thiệu dụng cụ tập luyện yêu thích và lý do bạn thích chúng.',1,1780151790,1780151790),(147124463,'65a10d7cf2d83',147123501,'Viết bài đăng về lợi ích của việc giãn cơ trước và sau khi tập.',1,1780151790,1780151790),(147124464,'65a10d7cf314a',147123501,'Tạo bài đăng giới thiệu bài tập yêu thích để tăng sức mạnh chân.',1,1780151790,1780151790),(147124465,'65a10d7cf357f',147123501,'Viết bài đăng về cách duy trì vận động khi đi du lịch.',1,1780151790,1780151790),(147124466,'65a10d7cf3974',147123501,'Tạo bài đăng giới thiệu món ăn vặt healthy yêu thích khi di chuyển.',1,1780151790,1780151790),(147124467,'65a10d7cf3d6a',147123501,'Viết bài đăng về cách tăng cơ cho người mới bắt đầu.',1,1780151790,1780151790),(147124468,'65a10d7cf4154',147123501,'Tạo bài đăng giới thiệu công thức sinh tố healthy yêu thích.',1,1780151790,1780151790),(147124469,'65a10d7d002be',147123501,'Viết lời nhắn truyền động lực về tầm quan trọng của việc chăm sóc bản thân.',1,1780151790,1780151790),(147124470,'65a10d7d00677',147123501,'Tạo bài đăng giới thiệu câu nói hoặc thần chú fitness yêu thích.',1,1780151790,1780151790),(147124471,'65a10d7d00a35',147123501,'Viết bài đăng về cách duy trì động lực trong hành trình fitness dài hạn.',1,1780151790,1780151790),(147124472,'65a10d7d00df8',147123501,'Tạo bài đăng giới thiệu lịch tập full-body yêu thích.',1,1780151790,1780151790),(147124473,'65a10d7d011e3',147123501,'Viết bài đăng về lợi ích của việc tập luyện theo nhóm hoặc cùng bạn tập.',1,1780151790,1780151790),(147124474,'65a10d7d015ad',147123501,'Tạo bài đăng giới thiệu tư thế yoga yêu thích và lợi ích của nó.',1,1780151790,1780151790),(147124475,'65a10d7d01990',147123501,'Viết bài đăng về lợi ích của thiền định cho tinh thần và cơ thể khỏe mạnh.',1,1780151790,1780151790),(147124476,'65a10d7d01d6f',147123501,'Tạo bài đăng giới thiệu bài tập yêu thích để tăng sức mạnh cánh tay.',1,1780151790,1780151790),(147124477,'65a10d7d02156',147123501,'Viết bài đăng về lợi ích của việc nghỉ ngơi trong lịch tập fitness.',1,1780151790,1780151790),(147124478,'65a10d7d02875',147123501,'Tạo bài đăng giới thiệu công thức bữa sáng healthy yêu thích.',1,1780151790,1780151790),(147124479,'65a10d7d02c80',147123501,'Viết lời nhắn truyền động lực về tầm quan trọng của việc đặt và đạt mục tiêu fitness.',1,1780151790,1780151790),(147124480,'65a10d7d03061',147123501,'Tạo bài đăng giới thiệu bài tập yêu thích để tăng sức mạnh cơ core.',1,1780151790,1780151790),(147124481,'65a10d7d03740',147123501,'Viết bài đăng về lợi ích của việc tập luyện ngoài trời cho sức khỏe tinh thần.',1,1780151790,1780151790),(147124482,'65a10d7d03b55',147123501,'Tạo bài đăng giới thiệu công thức món tráng miệng healthy yêu thích.',1,1780151790,1780151790),(147124483,'65a10d7d03f97',147123501,'Viết bài đăng về lợi ích khi có huấn luyện viên cá nhân để đạt mục tiêu fitness.',1,1780151790,1780151790),(147124484,'65a10d7d0439d',147123501,'Tạo bài đăng giới thiệu bài tập yêu thích để tăng sức mạnh lưng.',1,1780151790,1780151790),(147124485,'65a10d7d04795',147123501,'Viết bài đăng về cách vượt qua rào cản phổ biến khi bắt đầu tập luyện.',1,1780151790,1780151790),(147124486,'65a10d7d04b68',147123501,'Tạo bài đăng giới thiệu công thức món ăn vặt healthy để phục hồi sau tập.',1,1780151790,1780151790),(147124487,'65a10d7d04f36',147123501,'Viết bài đăng về lợi ích của việc tích hợp tập kháng lực vào lịch tập.',1,1780151790,1780151790),(147124488,'65a10d7d052fb',147123501,'Tạo bài đăng giới thiệu bài tập yêu thích để tăng sức bền.',1,1780151790,1780151790),(147124489,'65a10d7d056bb',147123501,'Viết lời nhắn truyền động lực về tầm quan trọng của kỷ luật bản thân để đạt mục tiêu fitness.',1,1780151790,1780151790),(147124490,'65a10d7d05ac9',147123501,'Tạo bài đăng giới thiệu công thức bữa trưa healthy yêu thích.',1,1780151790,1780151790),(147124491,'65a10d7d05e83',147123501,'Viết bài đăng về lợi ích của thiết bị theo dõi fitness đeo được.',1,1780151790,1780151790),(147124492,'65a10d7d0623c',147123501,'Tạo bài đăng giới thiệu bài tập yêu thích để cải thiện thăng bằng và ổn định.',1,1780151790,1780151790),(147124493,'65a10d7d06612',147123501,'Viết bài đăng về tầm quan trọng của việc bổ sung nước cho fitness và sức khỏe tổng thể.',1,1780151790,1780151790),(147124494,'65a10d7d069c2',147123501,'Tạo bài đăng giới thiệu công thức bữa tối healthy yêu thích.',1,1780151790,1780151790),(147124495,'65a10d7d06d88',147123501,'Viết bài đăng về lợi ích của tập HIIT (High-Intensity Interval Training) để giảm mỡ.',1,1780151790,1780151790),(147124496,'65a10d7d0797c',147123501,'Tạo bài đăng giới thiệu bài tập yêu thích để tăng độ linh hoạt.',1,1780151790,1780151790),(147124497,'65a10d7d07fe2',147123501,'Viết lời nhắn truyền động lực về tầm quan trọng của sự kiên trì để đạt mục tiêu fitness.',1,1780151790,1780151790),(147124498,'65a10d7d08b5e',147123470,'Tạo bài đăng về cách duy trì vận động khi làm công việc văn phòng ít vận động.',0,1780151790,1780151790),(147124499,'65a10d7d091c8',147123501,'Viết bài đăng về lợi ích của việc dùng mạng xã hội để theo dõi và chia sẻ tiến trình fitness.',1,1780151790,1780151790),(147124500,'65a10d7d0996d',147123501,'Tạo bài đăng giới thiệu công thức món ăn vặt healthy trước khi tập.',1,1780151790,1780151790),(147124501,'65a10d7d09fa8',147123501,'Viết bài đăng về lợi ích của việc tích hợp bài tập plyometric vào lịch tập.',1,1780151790,1780151790),(147124502,'65a10d7d0a6bf',147123501,'Tạo bài đăng giới thiệu bài tập yêu thích để tăng sức bùng nổ.',1,1780151790,1780151790),(147124503,'65a10d7d0aa8b',147123501,'Viết bài đăng về lợi ích của chế độ ăn cân bằng cho sức khỏe và fitness tổng thể.',1,1780151790,1780151790),(147124504,'65a10d7d0ae86',147123501,'Tạo bài đăng giới thiệu công thức smoothie bowl healthy yêu thích.',1,1780151790,1780151790),(147124505,'65a10d7d0b265',147123501,'Viết lời nhắn truyền động lực về tầm quan trọng của tư duy tích cực để đạt mục tiêu fitness.',1,1780151790,1780151790),(147124506,'65a10d7d0b678',147123501,'Tạo bài đăng giới thiệu bài tập yêu thích để tăng sức bền chân.',1,1780151790,1780151790),(147124507,'65a10d7d0ba45',147123501,'Tạo bài đăng giới thiệu bài tập yêu thích để tăng sức mạnh phần thân trên.',1,1780151790,1780151790),(147124508,'65a10d7d0be43',147123501,'Viết bài đăng về lợi ích của foam rolling để phục hồi và phòng ngừa chấn thương.',1,1780151790,1780151790),(147124509,'65a10d7d0c245',147123501,'Tạo bài đăng giới thiệu công thức món ăn vặt healthy trước buổi tập.',1,1780151790,1780151790),(147124510,'65a10d7d0c635',147123501,'Viết bài đăng về lợi ích của việc tích hợp bài tập thăng bằng vào lịch tập.',1,1780151790,1780151790),(147124511,'65a10d7d0caaf',147123501,'Tạo bài đăng giới thiệu bài tập yêu thích để tăng sức mạnh bùng nổ.',1,1780151790,1780151790),(147124512,'65a10d7d0cf46',147123501,'Viết lời nhắn truyền động lực về tầm quan trọng của kiên nhẫn và bền bỉ để đạt mục tiêu fitness.',1,1780151790,1780151790),(147124513,'65a10d7d0d350',147123501,'Tạo bài đăng giới thiệu công thức sinh tố healthy để phục hồi sau tập.',1,1780151790,1780151790),(147124514,'65a10d7d0d7b3',147123501,'Viết bài đăng về lợi ích của tập circuit cho thể lực tổng thể.',1,1780151790,1780151790),(147124515,'65a10d7d0dbf9',147123501,'Tạo bài đăng giới thiệu bài tập yêu thích để tăng ổn định cơ core.',1,1780151790,1780151790),(147124516,'65a10d7d0e0a5',147123501,'Viết bài đăng về tầm quan trọng của việc đặt mục tiêu fitness thực tế và khả thi.',1,1780151790,1780151790),(147124517,'65a10d7d0e49e',147123501,'Tạo bài đăng giới thiệu công thức meal prep healthy cho tuần bận rộn.',1,1780151790,1780151790),(147124518,'65a10d7d0e8f6',147123501,'Viết bài đăng về lợi ích của việc dùng con lăn foam để giải phóng cơ myofascial.',1,1780151790,1780151790),(147124519,'65a10d7d0ed64',147123501,'Tạo bài đăng giới thiệu bài tập yêu thích để tăng sức mạnh ngực.',1,1780151790,1780151790),(147124520,'65a10d7d0f1a8',147123501,'Viết lời nhắn truyền động lực về tầm quan trọng của niềm tin vào bản thân để đạt mục tiêu fitness.',1,1780151790,1780151790),(147124521,'65a10d7d0f656',147123501,'Tạo bài đăng giới thiệu công thức món ăn vặt healthy cho năng lượng giữa ngày.',1,1780151790,1780151790),(147124522,'65a10d7d0fa89',147123501,'Viết bài đăng về lợi ích của việc tích hợp dây kháng lực vào lịch tập.',1,1780151790,1780151790),(147124523,'65a10d7d0fee3',147123501,'Tạo bài đăng giới thiệu bài tập yêu thích để tăng sức mạnh chân.',1,1780151790,1780151790),(147124524,'65a10d7d10369',147123501,'Viết bài đăng về tầm quan trọng của tư thế và kỹ thuật đúng để tránh chấn thương khi tập.',1,1780151790,1780151790),(147124525,'65a10d7d107aa',147123501,'Tạo bài đăng giới thiệu công thức sinh tố healthy trước khi tập.',1,1780151790,1780151790),(147124526,'65a10d7d10c01',147123501,'Viết bài đăng về lợi ích của việc tích hợp yoga vào lịch tập fitness.',1,1780151790,1780151790);
/*!40000 ALTER TABLE `ai_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ai_usage_logs`
--

DROP TABLE IF EXISTS `ai_usage_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_usage_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `provider` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `capability` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'success',
  `feature` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `route_name` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prompt_tokens` int unsigned DEFAULT NULL,
  `completion_tokens` int unsigned DEFAULT NULL,
  `total_tokens` int unsigned DEFAULT NULL,
  `estimated_cost` decimal(12,6) DEFAULT NULL,
  `latency_ms` int unsigned DEFAULT NULL,
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ai_usage_logs_provider_capability_index` (`provider`,`capability`),
  KEY `ai_usage_logs_status_created_at_index` (`status`,`created_at`),
  KEY `ai_usage_logs_user_id_created_at_index` (`user_id`,`created_at`),
  CONSTRAINT `ai_usage_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=147123469 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ai_usage_logs`
--

LOCK TABLES `ai_usage_logs` WRITE;
/*!40000 ALTER TABLE `ai_usage_logs` DISABLE KEYS */;
INSERT INTO `ai_usage_logs` VALUES (147123468,147123471,'gemini','content','gemini-2.5-flash','error','localboost.content-writer','default-livewire.update',NULL,NULL,NULL,NULL,810,'Your project has been denied access. Please contact support.','[]','2026-06-07 12:09:27','2026-06-07 12:09:27');
/*!40000 ALTER TABLE `ai_usage_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ai_video_jobs`
--

DROP TABLE IF EXISTS `ai_video_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_video_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `owner_user_id` bigint unsigned NOT NULL,
  `requested_by_user_id` bigint unsigned DEFAULT NULL,
  `team_id` bigint unsigned DEFAULT NULL,
  `file_id` bigint unsigned DEFAULT NULL,
  `external_video_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'queued',
  `progress` int unsigned NOT NULL DEFAULT '0',
  `duration` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `format` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prompt` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `metadata` json DEFAULT NULL,
  `last_polled_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `failed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ai_video_jobs_external_video_id_unique` (`external_video_id`),
  KEY `ai_video_jobs_requested_by_user_id_foreign` (`requested_by_user_id`),
  KEY `ai_video_jobs_team_id_foreign` (`team_id`),
  KEY `ai_video_jobs_file_id_foreign` (`file_id`),
  KEY `ai_video_jobs_owner_user_id_requested_by_user_id_index` (`owner_user_id`,`requested_by_user_id`),
  KEY `ai_video_jobs_status_created_at_index` (`status`,`created_at`),
  CONSTRAINT `ai_video_jobs_file_id_foreign` FOREIGN KEY (`file_id`) REFERENCES `files` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ai_video_jobs_owner_user_id_foreign` FOREIGN KEY (`owner_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ai_video_jobs_requested_by_user_id_foreign` FOREIGN KEY (`requested_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ai_video_jobs_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ai_video_jobs`
--

LOCK TABLES `ai_video_jobs` WRITE;
/*!40000 ALTER TABLE `ai_video_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `ai_video_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `causer_user_id` bigint unsigned DEFAULT NULL,
  `event` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject_id` bigint unsigned DEFAULT NULL,
  `route_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `area` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'admin',
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `audit_logs_subject_type_subject_id_index` (`subject_type`,`subject_id`),
  KEY `audit_logs_causer_user_id_created_at_index` (`causer_user_id`,`created_at`),
  KEY `audit_logs_event_created_at_index` (`event`,`created_at`),
  CONSTRAINT `audit_logs_causer_user_id_foreign` FOREIGN KEY (`causer_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=147123471 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES (147123468,147123469,'auth.register','Registered a new account and default team.','Modules\\AdminUser\\Models\\User',147123469,'auth.social.callback','user','42.118.13.184','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','{\"plan\": null, \"team\": \"Nhóm của Khoa MLHUB\", \"username\": \"khoa-mlhub\"}','2026-06-06 17:14:44','2026-06-06 17:14:44'),(147123469,147123470,'auth.register','Registered a new account and default team.','Modules\\AdminUser\\Models\\User',147123470,'auth.social.callback','user','14.191.241.142','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','{\"plan\": null, \"team\": \"Giang Nguyen (Helen)\'s Team\", \"username\": \"giang-nguyen-helen\"}','2026-06-07 11:15:14','2026-06-07 11:15:14'),(147123470,147123471,'auth.register','Registered a new account and default team.','Modules\\AdminUser\\Models\\User',147123471,'auth.social.callback','user','14.191.241.142','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','{\"plan\": null, \"team\": \"Nhóm của Hoàng Linh Nguyễn\", \"username\": \"hoang-linh-nguyen\"}','2026-06-07 11:45:13','2026-06-07 11:45:13');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blog_categories`
--

DROP TABLE IF EXISTS `blog_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `blog_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_secure` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_translations` json DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `description_translations` json DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#0f766e',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `changed` bigint unsigned NOT NULL DEFAULT '0',
  `created` bigint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `blog_categories_id_secure_unique` (`id_secure`),
  UNIQUE KEY `blog_categories_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blog_categories`
--

LOCK TABLES `blog_categories` WRITE;
/*!40000 ALTER TABLE `blog_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `blog_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blog_rss_imports`
--

DROP TABLE IF EXISTS `blog_rss_imports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `blog_rss_imports` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `blog_rss_source_id` bigint unsigned NOT NULL,
  `blog_id` bigint unsigned DEFAULT NULL,
  `external_guid` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `external_url` text COLLATE utf8mb4_unicode_ci,
  `content_hash` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source_published_at` bigint unsigned DEFAULT NULL,
  `changed` bigint unsigned NOT NULL DEFAULT '0',
  `created` bigint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `blog_rss_imports_blog_rss_source_id_content_hash_unique` (`blog_rss_source_id`,`content_hash`),
  KEY `blog_rss_imports_blog_id_foreign` (`blog_id`),
  CONSTRAINT `blog_rss_imports_blog_id_foreign` FOREIGN KEY (`blog_id`) REFERENCES `blogs` (`id`) ON DELETE SET NULL,
  CONSTRAINT `blog_rss_imports_blog_rss_source_id_foreign` FOREIGN KEY (`blog_rss_source_id`) REFERENCES `blog_rss_sources` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blog_rss_imports`
--

LOCK TABLES `blog_rss_imports` WRITE;
/*!40000 ALTER TABLE `blog_rss_imports` DISABLE KEYS */;
/*!40000 ALTER TABLE `blog_rss_imports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blog_rss_sources`
--

DROP TABLE IF EXISTS `blog_rss_sources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `blog_rss_sources` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_secure` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `feed_url` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `blog_category_id` bigint unsigned DEFAULT NULL,
  `tag_ids` json DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `auto_publish` tinyint(1) NOT NULL DEFAULT '1',
  `ai_improve` tinyint(1) NOT NULL DEFAULT '0',
  `ai_auto_translate` tinyint(1) NOT NULL DEFAULT '0',
  `ai_prompt` text COLLATE utf8mb4_unicode_ci,
  `sync_interval_minutes` int unsigned NOT NULL DEFAULT '60',
  `max_items_per_run` int unsigned NOT NULL DEFAULT '5',
  `last_checked_at` bigint unsigned DEFAULT NULL,
  `last_imported_at` bigint unsigned DEFAULT NULL,
  `last_error` text COLLATE utf8mb4_unicode_ci,
  `changed` bigint unsigned NOT NULL DEFAULT '0',
  `created` bigint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `blog_rss_sources_id_secure_unique` (`id_secure`),
  KEY `blog_rss_sources_blog_category_id_foreign` (`blog_category_id`),
  CONSTRAINT `blog_rss_sources_blog_category_id_foreign` FOREIGN KEY (`blog_category_id`) REFERENCES `blog_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blog_rss_sources`
--

LOCK TABLES `blog_rss_sources` WRITE;
/*!40000 ALTER TABLE `blog_rss_sources` DISABLE KEYS */;
/*!40000 ALTER TABLE `blog_rss_sources` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blog_tag_maps`
--

DROP TABLE IF EXISTS `blog_tag_maps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `blog_tag_maps` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `blog_id` bigint unsigned NOT NULL,
  `blog_tag_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `blog_tag_maps_blog_id_blog_tag_id_unique` (`blog_id`,`blog_tag_id`),
  KEY `blog_tag_maps_blog_tag_id_foreign` (`blog_tag_id`),
  CONSTRAINT `blog_tag_maps_blog_id_foreign` FOREIGN KEY (`blog_id`) REFERENCES `blogs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `blog_tag_maps_blog_tag_id_foreign` FOREIGN KEY (`blog_tag_id`) REFERENCES `blog_tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blog_tag_maps`
--

LOCK TABLES `blog_tag_maps` WRITE;
/*!40000 ALTER TABLE `blog_tag_maps` DISABLE KEYS */;
/*!40000 ALTER TABLE `blog_tag_maps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blog_tags`
--

DROP TABLE IF EXISTS `blog_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `blog_tags` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_secure` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_translations` json DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `description_translations` json DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#0f766e',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `changed` bigint unsigned NOT NULL DEFAULT '0',
  `created` bigint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `blog_tags_id_secure_unique` (`id_secure`),
  UNIQUE KEY `blog_tags_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blog_tags`
--

LOCK TABLES `blog_tags` WRITE;
/*!40000 ALTER TABLE `blog_tags` DISABLE KEYS */;
/*!40000 ALTER TABLE `blog_tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blogs`
--

DROP TABLE IF EXISTS `blogs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `blogs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_secure` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `blog_category_id` bigint unsigned DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title_translations` json DEFAULT NULL,
  `excerpt` text COLLATE utf8mb4_unicode_ci,
  `excerpt_translations` json DEFAULT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `content_translations` json DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `meta_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` text COLLATE utf8mb4_unicode_ci,
  `canonical_url` varchar(2000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `og_image` text COLLATE utf8mb4_unicode_ci,
  `thumbnail` text COLLATE utf8mb4_unicode_ci,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `published_at` bigint unsigned DEFAULT NULL,
  `changed` bigint unsigned NOT NULL DEFAULT '0',
  `created` bigint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `blogs_id_secure_unique` (`id_secure`),
  UNIQUE KEY `blogs_slug_unique` (`slug`),
  KEY `blogs_blog_category_id_foreign` (`blog_category_id`),
  CONSTRAINT `blogs_blog_category_id_foreign` FOREIGN KEY (`blog_category_id`) REFERENCES `blog_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blogs`
--

LOCK TABLES `blogs` WRITE;
/*!40000 ALTER TABLE `blogs` DISABLE KEYS */;
/*!40000 ALTER TABLE `blogs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
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
-- Table structure for table `coupons`
--

DROP TABLE IF EXISTS `coupons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `coupons` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_secure` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` tinyint unsigned NOT NULL DEFAULT '1',
  `discount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `start_date` bigint unsigned DEFAULT NULL,
  `end_date` bigint DEFAULT NULL,
  `plans` json DEFAULT NULL,
  `usage_limit` int NOT NULL DEFAULT '-1',
  `usage_count` int unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `changed` bigint unsigned DEFAULT NULL,
  `created` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `coupons_code_unique` (`code`),
  UNIQUE KEY `coupons_id_secure_unique` (`id_secure`),
  KEY `coupons_status_created_index` (`status`,`created`),
  KEY `coupons_code_index` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coupons`
--

LOCK TABLES `coupons` WRITE;
/*!40000 ALTER TABLE `coupons` DISABLE KEYS */;
/*!40000 ALTER TABLE `coupons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `credit_packs`
--

DROP TABLE IF EXISTS `credit_packs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `credit_packs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `credits` int unsigned NOT NULL DEFAULT '0',
  `price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `currency` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'USD',
  `currency_symbol` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '$',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `featured` tinyint(1) NOT NULL DEFAULT '0',
  `sort` int unsigned NOT NULL DEFAULT '100',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `credit_packs_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `credit_packs`
--

LOCK TABLES `credit_packs` WRITE;
/*!40000 ALTER TABLE `credit_packs` DISABLE KEYS */;
/*!40000 ALTER TABLE `credit_packs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `credit_topup_ledgers`
--

DROP TABLE IF EXISTS `credit_topup_ledgers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `credit_topup_ledgers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `credit_pack_id` bigint unsigned DEFAULT NULL,
  `payment_history_id` bigint unsigned DEFAULT NULL,
  `type` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` int NOT NULL DEFAULT '0',
  `remaining` int NOT NULL DEFAULT '0',
  `expires_at` timestamp NULL DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `credit_topup_ledgers_credit_pack_id_foreign` (`credit_pack_id`),
  KEY `credit_topup_ledgers_payment_history_id_foreign` (`payment_history_id`),
  KEY `credit_topup_ledgers_user_id_type_index` (`user_id`,`type`),
  KEY `credit_topup_ledgers_user_id_remaining_index` (`user_id`,`remaining`),
  KEY `credit_topup_ledgers_expires_at_index` (`expires_at`),
  CONSTRAINT `credit_topup_ledgers_credit_pack_id_foreign` FOREIGN KEY (`credit_pack_id`) REFERENCES `credit_packs` (`id`) ON DELETE SET NULL,
  CONSTRAINT `credit_topup_ledgers_payment_history_id_foreign` FOREIGN KEY (`payment_history_id`) REFERENCES `payment_history` (`id`) ON DELETE SET NULL,
  CONSTRAINT `credit_topup_ledgers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `credit_topup_ledgers`
--

LOCK TABLES `credit_topup_ledgers` WRITE;
/*!40000 ALTER TABLE `credit_topup_ledgers` DISABLE KEYS */;
/*!40000 ALTER TABLE `credit_topup_ledgers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `credit_usage_logs`
--

DROP TABLE IF EXISTS `credit_usage_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `credit_usage_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `plan_id` bigint unsigned DEFAULT NULL,
  `action_key` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `feature` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` int unsigned NOT NULL DEFAULT '0',
  `unit_cost` int unsigned NOT NULL DEFAULT '0',
  `quantity` int unsigned NOT NULL DEFAULT '1',
  `credits_before` int DEFAULT NULL,
  `credits_after` int DEFAULT NULL,
  `is_unlimited` tinyint(1) NOT NULL DEFAULT '0',
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `credit_usage_logs_user_id_created_at_index` (`user_id`,`created_at`),
  KEY `credit_usage_logs_plan_id_created_at_index` (`plan_id`,`created_at`),
  KEY `credit_usage_logs_action_key_created_at_index` (`action_key`,`created_at`),
  CONSTRAINT `credit_usage_logs_plan_id_foreign` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE SET NULL,
  CONSTRAINT `credit_usage_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `credit_usage_logs`
--

LOCK TABLES `credit_usage_logs` WRITE;
/*!40000 ALTER TABLE `credit_usage_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `credit_usage_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `custom_domains`
--

DROP TABLE IF EXISTS `custom_domains`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `custom_domains` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `owner_user_id` bigint unsigned NOT NULL,
  `team_id` bigint unsigned DEFAULT NULL,
  `domain` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `verification_token` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `settings` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `custom_domains_domain_unique` (`domain`),
  UNIQUE KEY `custom_domains_verification_token_unique` (`verification_token`),
  KEY `custom_domains_owner_user_id_index` (`owner_user_id`),
  KEY `custom_domains_team_id_index` (`team_id`),
  KEY `custom_domains_status_index` (`status`),
  KEY `custom_domains_is_default_index` (`is_default`)
) ENGINE=InnoDB AUTO_INCREMENT=147123469 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `custom_domains`
--

LOCK TABLES `custom_domains` WRITE;
/*!40000 ALTER TABLE `custom_domains` DISABLE KEYS */;
INSERT INTO `custom_domains` VALUES (147123468,147123470,147123469,'quannhauminhdung.vn','pending',0,'qr-verify-m4e9xtra70iaewoqefbatcxr',NULL,NULL,'2026-06-07 13:03:46','2026-06-07 13:03:46');
/*!40000 ALTER TABLE `custom_domains` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `faqs`
--

DROP TABLE IF EXISTS `faqs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `faqs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_secure` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title_translations` json DEFAULT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `content_translations` json DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `changed` bigint unsigned NOT NULL DEFAULT '0',
  `created` bigint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `faqs_slug_unique` (`slug`),
  KEY `faqs_id_secure_index` (`id_secure`),
  KEY `faqs_status_index` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `faqs`
--

LOCK TABLES `faqs` WRITE;
/*!40000 ALTER TABLE `faqs` DISABLE KEYS */;
/*!40000 ALTER TABLE `faqs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `files`
--

DROP TABLE IF EXISTS `files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `files` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_secure` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner_user_id` bigint unsigned NOT NULL,
  `team_id` bigint unsigned DEFAULT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  `disk` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'public',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` varchar(2048) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mime_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `extension` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'other',
  `size_bytes` bigint unsigned NOT NULL DEFAULT '0',
  `is_folder` tinyint(1) NOT NULL DEFAULT '0',
  `is_image` tinyint(1) NOT NULL DEFAULT '0',
  `width` int unsigned DEFAULT NULL,
  `height` int unsigned DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `files_id_secure_unique` (`id_secure`),
  KEY `files_parent_id_foreign` (`parent_id`),
  KEY `files_owner_user_id_parent_id_index` (`owner_user_id`,`parent_id`),
  KEY `files_team_id_parent_id_index` (`team_id`,`parent_id`),
  KEY `files_is_folder_category_index` (`is_folder`,`category`),
  KEY `files_team_id_index` (`team_id`),
  CONSTRAINT `files_owner_user_id_foreign` FOREIGN KEY (`owner_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `files_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `files` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147123469 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `files`
--

LOCK TABLES `files` WRITE;
/*!40000 ALTER TABLE `files` DISABLE KEYS */;
INSERT INTO `files` VALUES (147123468,'wmXjozsEJmMwKDhz1vDykrFrUqGQwKuz',147123471,147123470,NULL,'public','z7727040031897_dd4ee118b04754ab1a1f172abfa51db7.jpg','files/user-147123471/2026/06/z7727040031897-dd4ee118b04754ab1a1f172abfa51db7-nqbyvc.jpg','image/jpeg','jpg','image',259738,0,1,1024,1024,NULL,'2026-06-07 12:00:46','2026-06-07 12:00:46');
/*!40000 ALTER TABLE `files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
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
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `language_translations`
--

DROP TABLE IF EXISTS `language_translations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `language_translations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `language_code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` longtext COLLATE utf8mb4_unicode_ci,
  `is_custom` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `language_translations_language_code_key_unique` (`language_code`,`key`),
  KEY `language_translations_language_code_index` (`language_code`)
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `language_translations`
--

LOCK TABLES `language_translations` WRITE;
/*!40000 ALTER TABLE `language_translations` DISABLE KEYS */;
/*!40000 ALTER TABLE `language_translations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `languages`
--

DROP TABLE IF EXISTS `languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `languages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `native_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direction` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ltr',
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `auto_translate` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `languages_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=147123470 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `languages`
--

LOCK TABLES `languages` WRITE;
/*!40000 ALTER TABLE `languages` DISABLE KEYS */;
INSERT INTO `languages` VALUES (147123468,'Tiếng Việt','Tiếng Việt','vi','vn','ltr',1,1,1,0,'2026-06-06 15:57:57','2026-06-06 15:57:57'),(147123469,'English','English','en','us','ltr',0,1,1,10,'2026-06-06 15:57:57','2026-06-06 15:57:57');
/*!40000 ALTER TABLE `languages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lb_booking_services`
--

DROP TABLE IF EXISTS `lb_booking_services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lb_booking_services` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `business_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `duration_minutes` smallint unsigned NOT NULL DEFAULT '60',
  `price` decimal(10,2) DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `available_days` json DEFAULT NULL,
  `time_slots` json DEFAULT NULL,
  `use_business_hours` tinyint(1) NOT NULL DEFAULT '1',
  `slot_interval` smallint unsigned NOT NULL DEFAULT '30',
  `buffer_before` smallint unsigned NOT NULL DEFAULT '0',
  `buffer_after` smallint unsigned NOT NULL DEFAULT '0',
  `service_hours` json DEFAULT NULL,
  `max_bookings_per_slot` smallint unsigned NOT NULL DEFAULT '1',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lb_booking_services_user_id_foreign` (`user_id`),
  KEY `lb_booking_services_business_id_foreign` (`business_id`),
  CONSTRAINT `lb_booking_services_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `lb_businesses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lb_booking_services_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147123469 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lb_booking_services`
--

LOCK TABLES `lb_booking_services` WRITE;
/*!40000 ALTER TABLE `lb_booking_services` DISABLE KEYS */;
INSERT INTO `lb_booking_services` VALUES (147123468,147123471,147123468,'Đặt bàn hẹn hò dưới ánh nến',60,500000.00,'Bàn hẹn hò lung linh lấp lánh huyền ảo bên bờ biển dưới ánh nến lãng mạn','[\"mon\", \"tue\", \"wed\", \"thu\", \"fri\", \"sat\"]','[\"09:00\", \"10:00\", \"11:00\", \"14:00\", \"15:00\", \"16:00\"]',1,30,0,0,NULL,1,1,'2026-06-07 12:03:59','2026-06-07 12:03:59');
/*!40000 ALTER TABLE `lb_booking_services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lb_bookings`
--

DROP TABLE IF EXISTS `lb_bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lb_bookings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `campaign_id` bigint unsigned DEFAULT NULL,
  `service_id` bigint unsigned DEFAULT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `booking_date` date NOT NULL,
  `booking_time` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lb_bookings_campaign_id_foreign` (`campaign_id`),
  KEY `lb_bookings_service_id_foreign` (`service_id`),
  KEY `lb_bookings_user_id_status_booking_date_index` (`user_id`,`status`,`booking_date`),
  CONSTRAINT `lb_bookings_campaign_id_foreign` FOREIGN KEY (`campaign_id`) REFERENCES `lb_campaigns` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lb_bookings_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `lb_booking_services` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lb_bookings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147123472 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lb_bookings`
--

LOCK TABLES `lb_bookings` WRITE;
/*!40000 ALTER TABLE `lb_bookings` DISABLE KEYS */;
INSERT INTO `lb_bookings` VALUES (147123468,147123471,147123469,147123468,'pending','2026-06-08','12:00','Hoàng Linh Nguyễn','0835788256','thihoanglinhnguyen16082004@gmail.com','Đặt bàn hẹn hò dưới ánh nến','2026-06-07 12:06:21','2026-06-07 12:06:21'),(147123469,147123471,147123469,147123468,'pending','2026-06-08','17:00','Nguyễn Hoàng linh','0835788256','hlinhwork123@gmail.com','Đặt bàn hẹn hò dưới ánh nến','2026-06-07 12:08:10','2026-06-07 12:08:10'),(147123470,147123471,147123469,147123468,'pending','2026-06-08','16:00','Như','961121159','tdqn2004@gmail.com','Đặt bàn hẹn hò dưới ánh nến','2026-06-07 12:08:32','2026-06-07 12:08:32'),(147123471,147123471,147123469,147123468,'pending','2026-06-08','09:00','Giang Nguyen','0858340270','ntqgiang268@gmail.com','Đặt bàn hẹn hò dưới ánh nến','2026-06-07 12:27:10','2026-06-07 12:27:10');
/*!40000 ALTER TABLE `lb_bookings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lb_businesses`
--

DROP TABLE IF EXISTS `lb_businesses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lb_businesses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'other',
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `google_maps_url` text COLLATE utf8mb4_unicode_ci,
  `social_links` json DEFAULT NULL,
  `opening_hours` json DEFAULT NULL,
  `qr_design` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lb_businesses_user_id_type_index` (`user_id`,`type`),
  CONSTRAINT `lb_businesses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147123470 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lb_businesses`
--

LOCK TABLES `lb_businesses` WRITE;
/*!40000 ALTER TABLE `lb_businesses` DISABLE KEYS */;
INSERT INTO `lb_businesses` VALUES (147123468,147123471,'Quán nhậu Minh Dung','Restaurant','0835788256','thihoanglinhnguyen16082004@gmail.com','','','https://maps.app.goo.gl/7rV5Q1Nwxc3wngBt9',NULL,'{\"fri\": {\"is_closed\": false, \"open_time\": \"09:00\", \"close_time\": \"18:00\"}, \"mon\": {\"is_closed\": false, \"open_time\": \"09:00\", \"close_time\": \"18:00\"}, \"sat\": {\"is_closed\": false, \"open_time\": \"10:00\", \"close_time\": \"15:00\"}, \"sun\": {\"is_closed\": true, \"open_time\": \"09:00\", \"close_time\": \"18:00\"}, \"thu\": {\"is_closed\": false, \"open_time\": \"09:00\", \"close_time\": \"18:00\"}, \"tue\": {\"is_closed\": false, \"open_time\": \"09:00\", \"close_time\": \"18:00\"}, \"wed\": {\"is_closed\": false, \"open_time\": \"09:00\", \"close_time\": \"18:00\"}}',NULL,'2026-06-07 11:46:45','2026-06-07 11:46:45'),(147123469,147123470,'Camellia Homestay & Villa','Spa','0236 3999 118','ntqgiang268@gmail.com','','','',NULL,'{\"fri\": {\"is_closed\": false, \"open_time\": \"09:00\", \"close_time\": \"18:00\"}, \"mon\": {\"is_closed\": false, \"open_time\": \"09:00\", \"close_time\": \"18:00\"}, \"sat\": {\"is_closed\": false, \"open_time\": \"10:00\", \"close_time\": \"15:00\"}, \"sun\": {\"is_closed\": true, \"open_time\": \"09:00\", \"close_time\": \"18:00\"}, \"thu\": {\"is_closed\": false, \"open_time\": \"09:00\", \"close_time\": \"18:00\"}, \"tue\": {\"is_closed\": false, \"open_time\": \"09:00\", \"close_time\": \"18:00\"}, \"wed\": {\"is_closed\": false, \"open_time\": \"09:00\", \"close_time\": \"18:00\"}}',NULL,'2026-06-07 13:15:18','2026-06-07 13:15:18');
/*!40000 ALTER TABLE `lb_businesses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lb_campaigns`
--

DROP TABLE IF EXISTS `lb_campaigns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lb_campaigns` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `business_id` bigint unsigned NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `destination_url` text COLLATE utf8mb4_unicode_ci,
  `settings` json DEFAULT NULL,
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lb_campaigns_slug_unique` (`slug`),
  KEY `lb_campaigns_business_id_foreign` (`business_id`),
  KEY `lb_campaigns_user_id_type_status_index` (`user_id`,`type`,`status`),
  CONSTRAINT `lb_campaigns_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `lb_businesses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lb_campaigns_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147123470 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lb_campaigns`
--

LOCK TABLES `lb_campaigns` WRITE;
/*!40000 ALTER TABLE `lb_campaigns` DISABLE KEYS */;
INSERT INTO `lb_campaigns` VALUES (147123468,147123471,147123468,'get-more-google-reviews','Get More Google Reviews','review','active',NULL,'{\"design\": {\"logo_url\": \"\", \"show_faq\": true, \"template\": \"review_clean_request\", \"show_logo\": true, \"card_style\": \"soft\", \"font_style\": \"modern\", \"logo_shape\": \"circle\", \"show_terms\": true, \"cover_image\": \"\", \"accent_color\": \"#ccfbf1\", \"button_style\": \"pill\", \"layout_style\": \"split\", \"primary_color\": \"#0f766e\", \"show_benefits\": true, \"background_type\": \"gradient\", \"background_color\": \"#f4fbf8\", \"show_social_links\": true, \"show_business_info\": true}, \"generate_qr_code\": true, \"landing_template\": \"review_clean_request\", \"google_review_url\": \"https://maps.app.goo.gl/7rV5Q1Nwxc3wngBt9\", \"thank_you_message\": \"Cảm ơn bạn vì đã rì viu chân thật\", \"create_public_page\": true, \"positive_threshold\": 4, \"facebook_review_url\": \"\", \"preferred_destination\": \"google\", \"negative_feedback_message\": \"Đồ ăn ngon hay dở, phục vụ tận tình hay chưa cứ nói để quán cải thiện nhen <3\"}','2026-06-07 11:49:56','2026-06-07 11:49:56','2026-06-07 11:59:08'),(147123469,147123471,147123468,'book-ban','Book bàn','booking','active',NULL,'{\"design\": {\"logo_url\": \"\", \"show_faq\": true, \"template\": \"booking_spa\", \"show_logo\": true, \"card_style\": \"soft\", \"font_style\": \"modern\", \"logo_shape\": \"circle\", \"show_terms\": true, \"cover_image\": \"\", \"accent_color\": \"#ccfbf1\", \"button_style\": \"pill\", \"layout_style\": \"split\", \"primary_color\": \"#0f766e\", \"show_benefits\": true, \"background_type\": \"gradient\", \"background_color\": \"#f4fbf8\", \"show_social_links\": true, \"show_business_info\": true}, \"headline\": \"Book an appointment\", \"generate_qr_code\": true, \"landing_template\": \"booking_spa\", \"create_public_page\": true}','2026-06-07 12:04:20','2026-06-07 12:04:20','2026-06-07 12:04:20');
/*!40000 ALTER TABLE `lb_campaigns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lb_coupon_redemptions`
--

DROP TABLE IF EXISTS `lb_coupon_redemptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lb_coupon_redemptions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `campaign_id` bigint unsigned NOT NULL,
  `code` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'claimed',
  `used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lb_coupon_redemptions_code_unique` (`code`),
  KEY `lb_coupon_redemptions_user_id_foreign` (`user_id`),
  KEY `lb_coupon_redemptions_campaign_id_status_index` (`campaign_id`,`status`),
  CONSTRAINT `lb_coupon_redemptions_campaign_id_foreign` FOREIGN KEY (`campaign_id`) REFERENCES `lb_campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lb_coupon_redemptions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lb_coupon_redemptions`
--

LOCK TABLES `lb_coupon_redemptions` WRITE;
/*!40000 ALTER TABLE `lb_coupon_redemptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `lb_coupon_redemptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lb_crm_automation_jobs`
--

DROP TABLE IF EXISTS `lb_crm_automation_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lb_crm_automation_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint unsigned DEFAULT NULL,
  `automation_id` bigint unsigned DEFAULT NULL,
  `customer_id` bigint unsigned DEFAULT NULL,
  `event_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` json DEFAULT NULL,
  `status` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `attempts` int unsigned NOT NULL DEFAULT '0',
  `run_at` timestamp NULL DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lb_crm_automation_jobs_automation_id_foreign` (`automation_id`),
  KEY `lb_crm_automation_jobs_customer_id_foreign` (`customer_id`),
  KEY `lb_crm_automation_jobs_team_id_index` (`team_id`),
  KEY `lb_crm_automation_jobs_event_name_index` (`event_name`),
  KEY `lb_crm_automation_jobs_status_index` (`status`),
  KEY `lb_crm_automation_jobs_run_at_index` (`run_at`),
  CONSTRAINT `lb_crm_automation_jobs_automation_id_foreign` FOREIGN KEY (`automation_id`) REFERENCES `lb_crm_automations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lb_crm_automation_jobs_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `lb_customers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lb_crm_automation_jobs`
--

LOCK TABLES `lb_crm_automation_jobs` WRITE;
/*!40000 ALTER TABLE `lb_crm_automation_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `lb_crm_automation_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lb_crm_automation_logs`
--

DROP TABLE IF EXISTS `lb_crm_automation_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lb_crm_automation_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint unsigned DEFAULT NULL,
  `automation_id` bigint unsigned NOT NULL,
  `customer_id` bigint unsigned DEFAULT NULL,
  `related_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `related_id` bigint unsigned DEFAULT NULL,
  `status` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'success',
  `message` text COLLATE utf8mb4_unicode_ci,
  `payload` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lb_crm_automation_logs_automation_id_foreign` (`automation_id`),
  KEY `lb_crm_automation_logs_customer_id_foreign` (`customer_id`),
  KEY `lb_crm_automation_logs_team_id_index` (`team_id`),
  KEY `lb_crm_automation_logs_status_index` (`status`),
  CONSTRAINT `lb_crm_automation_logs_automation_id_foreign` FOREIGN KEY (`automation_id`) REFERENCES `lb_crm_automations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lb_crm_automation_logs_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `lb_customers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lb_crm_automation_logs`
--

LOCK TABLES `lb_crm_automation_logs` WRITE;
/*!40000 ALTER TABLE `lb_crm_automation_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `lb_crm_automation_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lb_crm_automations`
--

DROP TABLE IF EXISTS `lb_crm_automations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lb_crm_automations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint unsigned DEFAULT NULL,
  `business_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `trigger_event` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `condition_json` json DEFAULT NULL,
  `action_json` json NOT NULL,
  `delay_type` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'immediate',
  `delay_value` int unsigned DEFAULT NULL,
  `delay_unit` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lb_crm_automations_business_id_foreign` (`business_id`),
  KEY `lb_crm_automations_created_by_foreign` (`created_by`),
  KEY `lb_crm_automations_team_id_index` (`team_id`),
  KEY `lb_crm_automations_trigger_event_index` (`trigger_event`),
  KEY `lb_crm_automations_status_index` (`status`),
  CONSTRAINT `lb_crm_automations_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `lb_businesses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lb_crm_automations_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lb_crm_automations`
--

LOCK TABLES `lb_crm_automations` WRITE;
/*!40000 ALTER TABLE `lb_crm_automations` DISABLE KEYS */;
/*!40000 ALTER TABLE `lb_crm_automations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lb_customer_activities`
--

DROP TABLE IF EXISTS `lb_customer_activities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lb_customer_activities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint unsigned DEFAULT NULL,
  `business_id` bigint unsigned DEFAULT NULL,
  `customer_id` bigint unsigned NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `related_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `related_id` bigint unsigned DEFAULT NULL,
  `source_module` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `occurred_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lb_customer_activities_business_id_foreign` (`business_id`),
  KEY `lb_customer_activities_customer_id_foreign` (`customer_id`),
  KEY `lb_customer_activities_created_by_foreign` (`created_by`),
  KEY `lb_customer_activities_team_id_index` (`team_id`),
  KEY `lb_customer_activities_type_index` (`type`),
  KEY `lb_customer_activities_related_type_index` (`related_type`),
  KEY `lb_customer_activities_related_id_index` (`related_id`),
  KEY `lb_customer_activities_source_module_index` (`source_module`),
  KEY `lb_customer_activities_occurred_at_index` (`occurred_at`),
  CONSTRAINT `lb_customer_activities_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `lb_businesses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lb_customer_activities_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lb_customer_activities_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `lb_customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147123481 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lb_customer_activities`
--

LOCK TABLES `lb_customer_activities` WRITE;
/*!40000 ALTER TABLE `lb_customer_activities` DISABLE KEYS */;
INSERT INTO `lb_customer_activities` VALUES (147123468,NULL,147123468,147123468,'customer_created','Customer created',NULL,NULL,NULL,'AppCustomers','fa-light fa-timeline','#0f766e','[]',147123471,'2026-06-07 11:50:31','2026-06-07 11:50:31','2026-06-07 11:50:31'),(147123469,147123471,147123468,147123468,'review_rating_submitted','Review rating submitted',NULL,'Modules\\AppReviewBooster\\Models\\ReviewFeedback',147123468,'AppReviewBooster','fa-light fa-star','#0f766e','{\"related_id\": 147123468, \"business_id\": 147123468, \"campaign_id\": 147123468, \"related_type\": \"Modules\\\\AppReviewBooster\\\\Models\\\\ReviewFeedback\", \"campaign_type\": \"review\", \"review.rating\": 5, \"feedback_message\": \"Quá ngon\"}',147123471,'2026-06-07 11:50:31','2026-06-07 11:50:31','2026-06-07 11:50:31'),(147123470,NULL,147123468,147123469,'customer_created','Customer created',NULL,NULL,NULL,'AppCustomers','fa-light fa-timeline','#0f766e','[]',147123471,'2026-06-07 11:51:29','2026-06-07 11:51:29','2026-06-07 11:51:29'),(147123471,147123471,147123468,147123469,'low_score_feedback_submitted','Low-score review submitted',NULL,'Modules\\AppReviewBooster\\Models\\ReviewFeedback',147123469,'AppReviewBooster','fa-light fa-message-lines','#0f766e','{\"related_id\": 147123469, \"business_id\": 147123468, \"campaign_id\": 147123468, \"related_type\": \"Modules\\\\AppReviewBooster\\\\Models\\\\ReviewFeedback\", \"campaign_type\": \"review\", \"review.rating\": 1, \"feedback_message\": \"njdfnjnf\"}',147123471,'2026-06-07 11:51:29','2026-06-07 11:51:29','2026-06-07 11:51:29'),(147123472,NULL,147123468,147123470,'customer_created','Khách hàng đã được tạo',NULL,NULL,NULL,'AppCustomers','fa-light fa-timeline','#0f766e','[]',NULL,'2026-06-07 11:55:55','2026-06-07 11:55:55','2026-06-07 11:55:55'),(147123473,147123471,147123468,147123470,'review_rating_submitted','Đã gửi đánh giá xếp hạng',NULL,'Modules\\AppReviewBooster\\Models\\ReviewFeedback',147123470,'AppReviewBooster','fa-light fa-star','#0f766e','{\"related_id\": 147123470, \"business_id\": 147123468, \"campaign_id\": 147123468, \"related_type\": \"Modules\\\\AppReviewBooster\\\\Models\\\\ReviewFeedback\", \"campaign_type\": \"review\", \"review.rating\": 5, \"feedback_message\": \"Ok ngon tuyệt\"}',NULL,'2026-06-07 11:55:55','2026-06-07 11:55:55','2026-06-07 11:55:55'),(147123474,147123471,147123468,147123470,'review_rating_submitted','Đã gửi đánh giá xếp hạng',NULL,'Modules\\AppReviewBooster\\Models\\ReviewFeedback',147123471,'AppReviewBooster','fa-light fa-star','#0f766e','{\"related_id\": 147123471, \"business_id\": 147123468, \"campaign_id\": 147123468, \"related_type\": \"Modules\\\\AppReviewBooster\\\\Models\\\\ReviewFeedback\", \"campaign_type\": \"review\", \"review.rating\": 5, \"feedback_message\": \"Ok ngon tuyệt\"}',NULL,'2026-06-07 11:56:55','2026-06-07 11:56:55','2026-06-07 11:56:55'),(147123475,NULL,147123468,147123471,'customer_created','Customer created',NULL,NULL,NULL,'AppCustomers','fa-light fa-timeline','#0f766e','[]',147123471,'2026-06-07 12:06:21','2026-06-07 12:06:21','2026-06-07 12:06:21'),(147123476,147123471,147123468,147123471,'booking_submitted','Booking submitted',NULL,'Modules\\AppBookingPages\\Models\\Booking',147123468,'AppBookingPages','fa-light fa-calendar-check','#0f766e','{\"related_id\": 147123468, \"business_id\": 147123468, \"campaign_id\": 147123469, \"related_type\": \"Modules\\\\AppBookingPages\\\\Models\\\\Booking\", \"campaign_type\": \"booking\", \"booking.status\": \"pending\"}',147123471,'2026-06-07 12:06:21','2026-06-07 12:06:21','2026-06-07 12:06:21'),(147123477,147123471,147123468,147123470,'booking_submitted','Đã gửi yêu cầu đặt chỗ',NULL,'Modules\\AppBookingPages\\Models\\Booking',147123469,'AppBookingPages','fa-light fa-calendar-check','#0f766e','{\"related_id\": 147123469, \"business_id\": 147123468, \"campaign_id\": 147123469, \"related_type\": \"Modules\\\\AppBookingPages\\\\Models\\\\Booking\", \"campaign_type\": \"booking\", \"booking.status\": \"pending\"}',NULL,'2026-06-07 12:08:10','2026-06-07 12:08:10','2026-06-07 12:08:10'),(147123478,NULL,147123468,147123472,'customer_created','Khách hàng đã được tạo',NULL,NULL,NULL,'AppCustomers','fa-light fa-timeline','#0f766e','[]',NULL,'2026-06-07 12:08:32','2026-06-07 12:08:32','2026-06-07 12:08:32'),(147123479,147123471,147123468,147123472,'booking_submitted','Đã gửi yêu cầu đặt chỗ',NULL,'Modules\\AppBookingPages\\Models\\Booking',147123470,'AppBookingPages','fa-light fa-calendar-check','#0f766e','{\"related_id\": 147123470, \"business_id\": 147123468, \"campaign_id\": 147123469, \"related_type\": \"Modules\\\\AppBookingPages\\\\Models\\\\Booking\", \"campaign_type\": \"booking\", \"booking.status\": \"pending\"}',NULL,'2026-06-07 12:08:32','2026-06-07 12:08:32','2026-06-07 12:08:32'),(147123480,147123471,147123468,147123468,'booking_submitted','Booking submitted',NULL,'Modules\\AppBookingPages\\Models\\Booking',147123471,'AppBookingPages','fa-light fa-calendar-check','#0f766e','{\"related_id\": 147123471, \"business_id\": 147123468, \"campaign_id\": 147123469, \"related_type\": \"Modules\\\\AppBookingPages\\\\Models\\\\Booking\", \"campaign_type\": \"booking\", \"booking.status\": \"pending\"}',147123470,'2026-06-07 12:27:10','2026-06-07 12:27:10','2026-06-07 12:27:10');
/*!40000 ALTER TABLE `lb_customer_activities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lb_customer_merge_logs`
--

DROP TABLE IF EXISTS `lb_customer_merge_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lb_customer_merge_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint unsigned DEFAULT NULL,
  `primary_customer_id` bigint unsigned NOT NULL,
  `merged_customer_id` bigint unsigned NOT NULL,
  `merged_by` bigint unsigned DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lb_customer_merge_logs_primary_customer_id_foreign` (`primary_customer_id`),
  KEY `lb_customer_merge_logs_merged_by_foreign` (`merged_by`),
  KEY `lb_customer_merge_logs_team_id_index` (`team_id`),
  CONSTRAINT `lb_customer_merge_logs_merged_by_foreign` FOREIGN KEY (`merged_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lb_customer_merge_logs_primary_customer_id_foreign` FOREIGN KEY (`primary_customer_id`) REFERENCES `lb_customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lb_customer_merge_logs`
--

LOCK TABLES `lb_customer_merge_logs` WRITE;
/*!40000 ALTER TABLE `lb_customer_merge_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `lb_customer_merge_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lb_customer_notes`
--

DROP TABLE IF EXISTS `lb_customer_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lb_customer_notes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint unsigned DEFAULT NULL,
  `business_id` bigint unsigned DEFAULT NULL,
  `customer_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `visibility` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'team',
  `pinned` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lb_customer_notes_business_id_foreign` (`business_id`),
  KEY `lb_customer_notes_customer_id_foreign` (`customer_id`),
  KEY `lb_customer_notes_user_id_foreign` (`user_id`),
  KEY `lb_customer_notes_team_id_index` (`team_id`),
  CONSTRAINT `lb_customer_notes_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `lb_businesses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lb_customer_notes_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `lb_customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lb_customer_notes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lb_customer_notes`
--

LOCK TABLES `lb_customer_notes` WRITE;
/*!40000 ALTER TABLE `lb_customer_notes` DISABLE KEYS */;
/*!40000 ALTER TABLE `lb_customer_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lb_customer_score_logs`
--

DROP TABLE IF EXISTS `lb_customer_score_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lb_customer_score_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint unsigned DEFAULT NULL,
  `customer_id` bigint unsigned NOT NULL,
  `old_score` int NOT NULL DEFAULT '0',
  `new_score` int NOT NULL DEFAULT '0',
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `related_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `related_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lb_customer_score_logs_customer_id_foreign` (`customer_id`),
  KEY `lb_customer_score_logs_team_id_index` (`team_id`),
  CONSTRAINT `lb_customer_score_logs_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `lb_customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147123476 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lb_customer_score_logs`
--

LOCK TABLES `lb_customer_score_logs` WRITE;
/*!40000 ALTER TABLE `lb_customer_score_logs` DISABLE KEYS */;
INSERT INTO `lb_customer_score_logs` VALUES (147123468,147123471,147123468,0,8,'Review rating submitted','Modules\\AppReviewBooster\\Models\\ReviewFeedback',147123468,'2026-06-07 11:50:31','2026-06-07 11:50:31'),(147123469,147123471,147123469,0,0,'Low-score review submitted','Modules\\AppReviewBooster\\Models\\ReviewFeedback',147123469,'2026-06-07 11:51:29','2026-06-07 11:51:29'),(147123470,147123471,147123470,0,8,'Đã gửi đánh giá xếp hạng','Modules\\AppReviewBooster\\Models\\ReviewFeedback',147123470,'2026-06-07 11:55:55','2026-06-07 11:55:55'),(147123471,147123471,147123470,8,16,'Đã gửi đánh giá xếp hạng','Modules\\AppReviewBooster\\Models\\ReviewFeedback',147123471,'2026-06-07 11:56:55','2026-06-07 11:56:55'),(147123472,147123471,147123471,0,3,'Booking submitted','Modules\\AppBookingPages\\Models\\Booking',147123468,'2026-06-07 12:06:21','2026-06-07 12:06:21'),(147123473,147123471,147123470,16,19,'Đã gửi yêu cầu đặt chỗ','Modules\\AppBookingPages\\Models\\Booking',147123469,'2026-06-07 12:08:10','2026-06-07 12:08:10'),(147123474,147123471,147123472,0,3,'Đã gửi yêu cầu đặt chỗ','Modules\\AppBookingPages\\Models\\Booking',147123470,'2026-06-07 12:08:32','2026-06-07 12:08:32'),(147123475,147123471,147123468,8,11,'Booking submitted','Modules\\AppBookingPages\\Models\\Booking',147123471,'2026-06-07 12:27:10','2026-06-07 12:27:10');
/*!40000 ALTER TABLE `lb_customer_score_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lb_customer_segments`
--

DROP TABLE IF EXISTS `lb_customer_segments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lb_customer_segments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint unsigned DEFAULT NULL,
  `business_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `filters` json DEFAULT NULL,
  `is_dynamic` tinyint(1) NOT NULL DEFAULT '1',
  `color` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lb_customer_segments_business_id_foreign` (`business_id`),
  KEY `lb_customer_segments_created_by_foreign` (`created_by`),
  KEY `lb_customer_segments_team_id_index` (`team_id`),
  CONSTRAINT `lb_customer_segments_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `lb_businesses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lb_customer_segments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lb_customer_segments`
--

LOCK TABLES `lb_customer_segments` WRITE;
/*!40000 ALTER TABLE `lb_customer_segments` DISABLE KEYS */;
/*!40000 ALTER TABLE `lb_customer_segments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lb_customer_tag_maps`
--

DROP TABLE IF EXISTS `lb_customer_tag_maps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lb_customer_tag_maps` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint unsigned DEFAULT NULL,
  `customer_id` bigint unsigned NOT NULL,
  `tag_id` bigint unsigned NOT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lb_customer_tag_maps_customer_id_tag_id_unique` (`customer_id`,`tag_id`),
  KEY `lb_customer_tag_maps_tag_id_foreign` (`tag_id`),
  KEY `lb_customer_tag_maps_created_by_foreign` (`created_by`),
  KEY `lb_customer_tag_maps_team_id_index` (`team_id`),
  CONSTRAINT `lb_customer_tag_maps_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lb_customer_tag_maps_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `lb_customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lb_customer_tag_maps_tag_id_foreign` FOREIGN KEY (`tag_id`) REFERENCES `lb_customer_tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lb_customer_tag_maps`
--

LOCK TABLES `lb_customer_tag_maps` WRITE;
/*!40000 ALTER TABLE `lb_customer_tag_maps` DISABLE KEYS */;
/*!40000 ALTER TABLE `lb_customer_tag_maps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lb_customer_tags`
--

DROP TABLE IF EXISTS `lb_customer_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lb_customer_tags` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#0f766e',
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_system` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lb_customer_tags_team_id_slug_unique` (`team_id`,`slug`),
  KEY `lb_customer_tags_team_id_index` (`team_id`)
) ENGINE=InnoDB AUTO_INCREMENT=147123489 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lb_customer_tags`
--

LOCK TABLES `lb_customer_tags` WRITE;
/*!40000 ALTER TABLE `lb_customer_tags` DISABLE KEYS */;
INSERT INTO `lb_customer_tags` VALUES (147123468,147123470,'VIP','vip','#f59e0b',NULL,1,'2026-06-07 11:16:01','2026-06-07 11:16:01'),(147123469,147123470,'New Customer','new-customer','#2563eb',NULL,1,'2026-06-07 11:16:01','2026-06-07 11:16:01'),(147123470,147123470,'Returning Customer','returning-customer','#0f766e',NULL,1,'2026-06-07 11:16:01','2026-06-07 11:16:01'),(147123471,147123470,'Needs Follow-up','needs-follow-up','#dc2626',NULL,1,'2026-06-07 11:16:01','2026-06-07 11:16:01'),(147123472,147123470,'Coupon Claimed','coupon-claimed','#7c3aed',NULL,1,'2026-06-07 11:16:01','2026-06-07 11:16:01'),(147123473,147123470,'Low-score Feedback','low-score-feedback','#ef4444',NULL,1,'2026-06-07 11:16:01','2026-06-07 11:16:01'),(147123474,147123470,'Loyal Customer','loyal-customer','#16a34a',NULL,1,'2026-06-07 11:16:01','2026-06-07 11:16:01'),(147123475,147123470,'Referral Customer','referral-customer','#0891b2',NULL,1,'2026-06-07 11:16:01','2026-06-07 11:16:01'),(147123476,147123470,'Inactive','inactive','#64748b',NULL,1,'2026-06-07 11:16:01','2026-06-07 11:16:01'),(147123477,147123470,'Coupon Used','coupon-used','#0f766e',NULL,1,'2026-06-07 11:21:28','2026-06-07 11:21:28'),(147123478,147123470,'High Rating','high-rating','#22c55e',NULL,1,'2026-06-07 11:21:28','2026-06-07 11:21:28'),(147123479,147123470,'Booking Customer','booking-customer','#0891b2',NULL,1,'2026-06-07 11:21:28','2026-06-07 11:21:28'),(147123480,147123469,'VIP','vip','#f59e0b',NULL,1,'2026-06-07 14:23:26','2026-06-07 14:23:26'),(147123481,147123469,'New Customer','new-customer','#2563eb',NULL,1,'2026-06-07 14:23:26','2026-06-07 14:23:26'),(147123482,147123469,'Returning Customer','returning-customer','#0f766e',NULL,1,'2026-06-07 14:23:26','2026-06-07 14:23:26'),(147123483,147123469,'Needs Follow-up','needs-follow-up','#dc2626',NULL,1,'2026-06-07 14:23:26','2026-06-07 14:23:26'),(147123484,147123469,'Coupon Claimed','coupon-claimed','#7c3aed',NULL,1,'2026-06-07 14:23:26','2026-06-07 14:23:26'),(147123485,147123469,'Low-score Feedback','low-score-feedback','#ef4444',NULL,1,'2026-06-07 14:23:26','2026-06-07 14:23:26'),(147123486,147123469,'Loyal Customer','loyal-customer','#16a34a',NULL,1,'2026-06-07 14:23:26','2026-06-07 14:23:26'),(147123487,147123469,'Referral Customer','referral-customer','#0891b2',NULL,1,'2026-06-07 14:23:26','2026-06-07 14:23:26'),(147123488,147123469,'Inactive','inactive','#64748b',NULL,1,'2026-06-07 14:23:26','2026-06-07 14:23:26');
/*!40000 ALTER TABLE `lb_customer_tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lb_customer_tasks`
--

DROP TABLE IF EXISTS `lb_customer_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lb_customer_tasks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint unsigned DEFAULT NULL,
  `business_id` bigint unsigned DEFAULT NULL,
  `customer_id` bigint unsigned NOT NULL,
  `assigned_to` bigint unsigned DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `type` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'follow_up',
  `priority` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `status` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `due_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `related_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `related_id` bigint unsigned DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lb_customer_tasks_business_id_foreign` (`business_id`),
  KEY `lb_customer_tasks_customer_id_foreign` (`customer_id`),
  KEY `lb_customer_tasks_assigned_to_foreign` (`assigned_to`),
  KEY `lb_customer_tasks_created_by_foreign` (`created_by`),
  KEY `lb_customer_tasks_team_id_index` (`team_id`),
  KEY `lb_customer_tasks_priority_index` (`priority`),
  KEY `lb_customer_tasks_status_index` (`status`),
  KEY `lb_customer_tasks_due_at_index` (`due_at`),
  CONSTRAINT `lb_customer_tasks_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lb_customer_tasks_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `lb_businesses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lb_customer_tasks_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lb_customer_tasks_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `lb_customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lb_customer_tasks`
--

LOCK TABLES `lb_customer_tasks` WRITE;
/*!40000 ALTER TABLE `lb_customer_tasks` DISABLE KEYS */;
/*!40000 ALTER TABLE `lb_customer_tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lb_customers`
--

DROP TABLE IF EXISTS `lb_customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lb_customers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `team_id` bigint unsigned DEFAULT NULL,
  `business_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `source_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source_id` bigint unsigned DEFAULT NULL,
  `tags` json DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `metadata` json DEFAULT NULL,
  `first_seen_at` timestamp NULL DEFAULT NULL,
  `last_activity_at` timestamp NULL DEFAULT NULL,
  `last_contacted_at` timestamp NULL DEFAULT NULL,
  `score` int unsigned NOT NULL DEFAULT '0',
  `lifetime_value` decimal(12,2) DEFAULT NULL,
  `total_reviews` int unsigned NOT NULL DEFAULT '0',
  `total_feedback` int unsigned NOT NULL DEFAULT '0',
  `total_coupon_used` int unsigned NOT NULL DEFAULT '0',
  `total_coupon_claims` int unsigned NOT NULL DEFAULT '0',
  `total_bookings` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `total_loyalty_stamps` int unsigned NOT NULL DEFAULT '0',
  `total_referrals` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `lb_customers_business_id_foreign` (`business_id`),
  KEY `lb_customers_user_id_business_id_index` (`user_id`,`business_id`),
  KEY `lb_customers_team_id_index` (`team_id`),
  KEY `lb_customers_status_index` (`status`),
  KEY `lb_customers_source_type_index` (`source_type`),
  KEY `lb_customers_source_id_index` (`source_id`),
  KEY `lb_customers_last_activity_at_index` (`last_activity_at`),
  CONSTRAINT `lb_customers_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `lb_businesses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lb_customers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147123473 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lb_customers`
--

LOCK TABLES `lb_customers` WRITE;
/*!40000 ALTER TABLE `lb_customers` DISABLE KEYS */;
INSERT INTO `lb_customers` VALUES (147123468,147123471,147123471,147123468,'Giang Nguyen','0858340270','ntqgiang268@gmail.com',NULL,'active','AppReviewBooster',147123468,'[\"landing-page\", \"review\"]',NULL,'{\"last_source\": \"AppBookingPages\", \"last_related_id\": 147123471, \"last_related_type\": \"Modules\\\\AppBookingPages\\\\Models\\\\Booking\", \"last_landing_page_id\": 147123469, \"last_landing_page_type\": \"booking\"}','2026-06-07 11:50:31','2026-06-07 12:27:10',NULL,11,NULL,1,0,0,0,1,'2026-06-07 11:50:31','2026-06-07 12:27:10',0,0),(147123469,147123471,147123471,147123468,'G',NULL,'giangkwon@gmail.com',NULL,'active','AppReviewBooster',147123469,'[\"landing-page\", \"review\"]',NULL,'{\"last_source\": \"AppReviewBooster\", \"last_related_id\": 147123469, \"last_related_type\": \"Modules\\\\AppReviewBooster\\\\Models\\\\ReviewFeedback\", \"last_landing_page_id\": 147123468, \"last_landing_page_type\": \"review\"}','2026-06-07 11:51:29','2026-06-07 11:51:29',NULL,0,NULL,1,0,0,0,0,'2026-06-07 11:51:29','2026-06-07 11:51:29',0,0),(147123470,147123471,147123471,147123468,'Nguyễn Hoàng linh','0835788256','hlinhwork123@gmail.com',NULL,'active','AppReviewBooster',147123470,'[\"landing-page\", \"review\"]',NULL,'{\"last_source\": \"AppBookingPages\", \"last_related_id\": 147123469, \"last_related_type\": \"Modules\\\\AppBookingPages\\\\Models\\\\Booking\", \"last_landing_page_id\": 147123469, \"last_landing_page_type\": \"booking\"}','2026-06-07 11:55:55','2026-06-07 12:08:10',NULL,19,NULL,2,0,0,0,1,'2026-06-07 11:55:55','2026-06-07 12:08:10',0,0),(147123471,147123471,147123471,147123468,'Hoàng Linh Nguyễn','0835788256','thihoanglinhnguyen16082004@gmail.com',NULL,'active','AppBookingPages',147123468,'[\"landing-page\", \"booking\"]',NULL,'{\"last_source\": \"AppBookingPages\", \"last_related_id\": 147123468, \"last_related_type\": \"Modules\\\\AppBookingPages\\\\Models\\\\Booking\", \"last_landing_page_id\": 147123469, \"last_landing_page_type\": \"booking\"}','2026-06-07 12:06:21','2026-06-07 12:06:21',NULL,3,NULL,0,0,0,0,1,'2026-06-07 12:06:21','2026-06-07 12:06:21',0,0),(147123472,147123471,147123471,147123468,'Như','961121159','tdqn2004@gmail.com',NULL,'active','AppBookingPages',147123470,'[\"landing-page\", \"booking\"]',NULL,'{\"last_source\": \"AppBookingPages\", \"last_related_id\": 147123470, \"last_related_type\": \"Modules\\\\AppBookingPages\\\\Models\\\\Booking\", \"last_landing_page_id\": 147123469, \"last_landing_page_type\": \"booking\"}','2026-06-07 12:08:32','2026-06-07 12:08:32',NULL,3,NULL,0,0,0,0,1,'2026-06-07 12:08:32','2026-06-07 12:08:32',0,0);
/*!40000 ALTER TABLE `lb_customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lb_email_automation_logs`
--

DROP TABLE IF EXISTS `lb_email_automation_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lb_email_automation_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `automation_id` bigint unsigned DEFAULT NULL,
  `email_template_id` bigint unsigned DEFAULT NULL,
  `business_id` bigint unsigned DEFAULT NULL,
  `customer_id` bigint unsigned DEFAULT NULL,
  `trigger_event` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `related_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `related_id` bigint unsigned DEFAULT NULL,
  `recipient_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipient_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` longtext COLLATE utf8mb4_unicode_ci,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'queued',
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `queued_at` timestamp NULL DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `opened_at` timestamp NULL DEFAULT NULL,
  `clicked_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lb_email_automation_logs_automation_id_foreign` (`automation_id`),
  KEY `lb_email_automation_logs_email_template_id_foreign` (`email_template_id`),
  KEY `lb_email_automation_logs_business_id_foreign` (`business_id`),
  KEY `lb_email_automation_logs_customer_id_foreign` (`customer_id`),
  KEY `lb_email_automation_logs_user_id_status_created_at_index` (`user_id`,`status`,`created_at`),
  KEY `lb_email_automation_logs_related_type_related_id_index` (`related_type`,`related_id`),
  CONSTRAINT `lb_email_automation_logs_automation_id_foreign` FOREIGN KEY (`automation_id`) REFERENCES `lb_email_automations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lb_email_automation_logs_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `lb_businesses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lb_email_automation_logs_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `lb_customers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lb_email_automation_logs_email_template_id_foreign` FOREIGN KEY (`email_template_id`) REFERENCES `lb_email_templates` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lb_email_automation_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147123469 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lb_email_automation_logs`
--

LOCK TABLES `lb_email_automation_logs` WRITE;
/*!40000 ALTER TABLE `lb_email_automation_logs` DISABLE KEYS */;
INSERT INTO `lb_email_automation_logs` VALUES (147123468,147123471,147123469,147123469,NULL,NULL,'booking.submitted','Modules\\AppBookingPages\\Models\\Booking',147123471,'ntqgiang268@gmail.com','Giang Nguyen','Your booking is confirmed','Hi Giang Nguyen,<br />\n<br />\nYour appointment with  is confirmed for 2026-06-08 00:00:00 at 09:00.<br />\n<br />\nSee you soon.','sent',NULL,'2026-06-07 12:27:10','2026-06-07 12:27:16',NULL,NULL,'2026-06-07 12:27:10','2026-06-07 12:27:16');
/*!40000 ALTER TABLE `lb_email_automation_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lb_email_automations`
--

DROP TABLE IF EXISTS `lb_email_automations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lb_email_automations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `business_id` bigint unsigned DEFAULT NULL,
  `email_template_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `trigger_event` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `delay_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'immediate',
  `delay_value` int unsigned NOT NULL DEFAULT '0',
  `delay_unit` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'minutes',
  `condition_json` json DEFAULT NULL,
  `action_json` json DEFAULT NULL,
  `send_to` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'customer',
  `custom_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lb_email_automations_business_id_foreign` (`business_id`),
  KEY `lb_email_automations_email_template_id_foreign` (`email_template_id`),
  KEY `lb_email_automations_created_by_foreign` (`created_by`),
  KEY `lb_email_automations_user_id_trigger_event_status_index` (`user_id`,`trigger_event`,`status`),
  CONSTRAINT `lb_email_automations_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `lb_businesses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lb_email_automations_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lb_email_automations_email_template_id_foreign` FOREIGN KEY (`email_template_id`) REFERENCES `lb_email_templates` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lb_email_automations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147123470 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lb_email_automations`
--

LOCK TABLES `lb_email_automations` WRITE;
/*!40000 ALTER TABLE `lb_email_automations` DISABLE KEYS */;
INSERT INTO `lb_email_automations` VALUES (147123468,147123471,147123468,147123470,'Nhắc lịch đặt bàn','booking.confirmed','active','after',1,'days','{\"rules\": [{\"field\": \"customer.email\", \"value\": true, \"operator\": \"exists\"}, {\"field\": \"booking.status\", \"value\": \"confirmed\", \"operator\": \"=\"}]}','{\"type\": \"send_email\"}','customer',NULL,147123471,'2026-06-07 12:15:53','2026-06-07 12:15:58'),(147123469,147123471,147123468,147123469,'Confirm','booking.submitted','active','immediate',0,'minutes','{\"rules\": [{\"field\": \"customer.email\", \"value\": true, \"operator\": \"exists\"}]}','{\"type\": \"send_email\"}','customer',NULL,147123471,'2026-06-07 12:26:41','2026-06-07 12:26:41');
/*!40000 ALTER TABLE `lb_email_automations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lb_email_templates`
--

DROP TABLE IF EXISTS `lb_email_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lb_email_templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `business_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `preheader` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `body` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `language` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en',
  `is_system` tinyint(1) NOT NULL DEFAULT '0',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lb_email_templates_business_id_foreign` (`business_id`),
  KEY `lb_email_templates_user_id_business_id_status_index` (`user_id`,`business_id`,`status`),
  CONSTRAINT `lb_email_templates_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `lb_businesses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lb_email_templates_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147123480 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lb_email_templates`
--

LOCK TABLES `lb_email_templates` WRITE;
/*!40000 ALTER TABLE `lb_email_templates` DISABLE KEYS */;
INSERT INTO `lb_email_templates` VALUES (147123468,NULL,NULL,'Booking Request Received','booking','Your booking request has been received',NULL,'Hi {customer_name},\n\nThank you for booking {booking_service} with {business_name}.\nWe received your request for {booking_date} at {booking_time}.\n\nOur team will confirm your appointment soon.','en',1,'active','2026-06-06 15:58:01','2026-06-06 15:58:01'),(147123469,NULL,NULL,'Booking Confirmed','booking','Your booking is confirmed',NULL,'Hi {customer_name},\n\nYour appointment with {business_name} is confirmed for {booking_date} at {booking_time}.\n\nSee you soon.','en',1,'active','2026-06-06 15:58:01','2026-06-06 15:58:01'),(147123470,NULL,NULL,'Booking Reminder','booking','Reminder: your appointment is coming up',NULL,'Hi {customer_name},\n\nThis is a reminder for your appointment with {business_name} on {booking_date} at {booking_time}.','en',1,'active','2026-06-06 15:58:01','2026-06-06 15:58:01'),(147123471,NULL,NULL,'Booking Cancelled','booking','Your booking was cancelled',NULL,'Hi {customer_name},\n\nYour booking with {business_name} was cancelled. Contact us if you want to reschedule.','en',1,'active','2026-06-06 15:58:01','2026-06-06 15:58:01'),(147123472,NULL,NULL,'Booking Completed Review Request','review','How was your visit?',NULL,'Hi {customer_name},\n\nThanks for visiting {business_name}. We would appreciate your feedback:\n{public_page_url}','en',1,'active','2026-06-06 15:58:01','2026-06-06 15:58:01'),(147123473,NULL,NULL,'Coupon Claimed','coupon','Your coupon code is ready',NULL,'Hi {customer_name},\n\nHere is your coupon for {business_name}: {coupon_code}\n\nUse it before {coupon_expiry}.','en',1,'active','2026-06-06 15:58:01','2026-06-06 15:58:01'),(147123474,NULL,NULL,'Coupon Used Thank You','coupon','Thanks for using your coupon',NULL,'Hi {customer_name},\n\nThanks for visiting {business_name}. We hope to see you again soon.','en',1,'active','2026-06-06 15:58:01','2026-06-06 15:58:01'),(147123475,NULL,NULL,'Lead Received','lead','We received your request',NULL,'Hi {customer_name},\n\nThanks for contacting {business_name}. Our team will follow up soon.','en',1,'active','2026-06-06 15:58:01','2026-06-06 15:58:01'),(147123476,NULL,NULL,'Lead Follow-up','lead','Following up on your request',NULL,'Hi {customer_name},\n\nJust checking in after your request to {business_name}. Reply to this email if you have any questions.','en',1,'active','2026-06-06 15:58:01','2026-06-06 15:58:01'),(147123477,NULL,NULL,'Feedback Received','feedback','Thank you for your feedback',NULL,'Hi {customer_name},\n\nThank you for sharing your feedback with {business_name}. We appreciate it.','en',1,'active','2026-06-06 15:58:01','2026-06-06 15:58:01'),(147123478,NULL,NULL,'Low-score Recovery','feedback','We want to make this right',NULL,'Hi {customer_name},\n\nWe are sorry your experience with {business_name} was not perfect. Please reply and tell us how we can make it right.','en',1,'active','2026-06-06 15:58:01','2026-06-06 15:58:01'),(147123479,NULL,NULL,'Positive Review Thank You','review','Thank you for your review',NULL,'Hi {customer_name},\n\nThank you for supporting {business_name}. Your feedback means a lot to us.','en',1,'active','2026-06-06 15:58:01','2026-06-06 15:58:01');
/*!40000 ALTER TABLE `lb_email_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lb_feedback_responses`
--

DROP TABLE IF EXISTS `lb_feedback_responses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lb_feedback_responses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `campaign_id` bigint unsigned NOT NULL,
  `rating` tinyint unsigned DEFAULT NULL,
  `customer_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'new',
  `resolved_at` timestamp NULL DEFAULT NULL,
  `payload` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lb_feedback_responses_user_id_foreign` (`user_id`),
  KEY `lb_feedback_responses_campaign_id_rating_index` (`campaign_id`,`rating`),
  CONSTRAINT `lb_feedback_responses_campaign_id_foreign` FOREIGN KEY (`campaign_id`) REFERENCES `lb_campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lb_feedback_responses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lb_feedback_responses`
--

LOCK TABLES `lb_feedback_responses` WRITE;
/*!40000 ALTER TABLE `lb_feedback_responses` DISABLE KEYS */;
/*!40000 ALTER TABLE `lb_feedback_responses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lb_google_auto_reply_logs`
--

DROP TABLE IF EXISTS `lb_google_auto_reply_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lb_google_auto_reply_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint unsigned NOT NULL,
  `rule_id` bigint unsigned DEFAULT NULL,
  `review_id` bigint unsigned DEFAULT NULL,
  `action` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `generated_reply` text COLLATE utf8mb4_unicode_ci,
  `publish_status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lb_google_auto_reply_logs_rule_id_foreign` (`rule_id`),
  KEY `lb_google_auto_reply_logs_review_id_foreign` (`review_id`),
  KEY `lb_google_auto_reply_logs_team_id_publish_status_index` (`team_id`,`publish_status`),
  CONSTRAINT `lb_google_auto_reply_logs_review_id_foreign` FOREIGN KEY (`review_id`) REFERENCES `lb_google_reviews` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lb_google_auto_reply_logs_rule_id_foreign` FOREIGN KEY (`rule_id`) REFERENCES `lb_google_auto_reply_rules` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lb_google_auto_reply_logs_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lb_google_auto_reply_logs`
--

LOCK TABLES `lb_google_auto_reply_logs` WRITE;
/*!40000 ALTER TABLE `lb_google_auto_reply_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `lb_google_auto_reply_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lb_google_auto_reply_rules`
--

DROP TABLE IF EXISTS `lb_google_auto_reply_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lb_google_auto_reply_rules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint unsigned NOT NULL,
  `business_id` bigint unsigned DEFAULT NULL,
  `google_business_location_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rating_condition` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'positive',
  `text_condition` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'any',
  `keyword` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reply_mode` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `template_reply` text COLLATE utf8mb4_unicode_ci,
  `tone` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'professional',
  `language` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'same',
  `delay_minutes` int unsigned NOT NULL DEFAULT '0',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lb_google_auto_reply_rules_business_id_foreign` (`business_id`),
  KEY `lb_google_auto_reply_rules_google_business_location_id_foreign` (`google_business_location_id`),
  KEY `lb_google_auto_reply_rules_team_id_status_index` (`team_id`,`status`),
  CONSTRAINT `lb_google_auto_reply_rules_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `lb_businesses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lb_google_auto_reply_rules_google_business_location_id_foreign` FOREIGN KEY (`google_business_location_id`) REFERENCES `lb_google_business_locations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lb_google_auto_reply_rules_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lb_google_auto_reply_rules`
--

LOCK TABLES `lb_google_auto_reply_rules` WRITE;
/*!40000 ALTER TABLE `lb_google_auto_reply_rules` DISABLE KEYS */;
/*!40000 ALTER TABLE `lb_google_auto_reply_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lb_google_business_connections`
--

DROP TABLE IF EXISTS `lb_google_business_connections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lb_google_business_connections` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `google_account_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `access_token` text COLLATE utf8mb4_unicode_ci,
  `refresh_token` text COLLATE utf8mb4_unicode_ci,
  `expires_at` timestamp NULL DEFAULT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'connected',
  `auto_sync` tinyint(1) NOT NULL DEFAULT '1',
  `sync_interval` smallint unsigned NOT NULL DEFAULT '15',
  `scopes` json DEFAULT NULL,
  `last_synced_at` timestamp NULL DEFAULT NULL,
  `last_error` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lb_google_business_connections_user_id_foreign` (`user_id`),
  KEY `lb_google_business_connections_team_id_status_index` (`team_id`,`status`),
  CONSTRAINT `lb_google_business_connections_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lb_google_business_connections_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147123470 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lb_google_business_connections`
--

LOCK TABLES `lb_google_business_connections` WRITE;
/*!40000 ALTER TABLE `lb_google_business_connections` DISABLE KEYS */;
INSERT INTO `lb_google_business_connections` VALUES (147123468,147123470,147123470,'thihoanglinhnguyen16082004@gmail.com','eyJpdiI6IjQ5VnFKTnhGNVpnUnN2emVMUzJNWUE9PSIsInZhbHVlIjoiWko5djZvQ0ZEeEswVnhXbUI1ZXlMb1BQOGFVeFpJcUdRU3hYU2dxN29HWjFGMStnRTdLNUxISHhlbGpwbWQ0YUpnMVJjd1FjVmlQRzBYUXR0U0FSM0R2ZGNUeEhXRnljS28weGxVOEpwNDlaLzhIS1VCN0trRkxSZ2JIeTlOU3ZwdllJck9wNEhYMVZSZ1NGd21LK3I5Qm1VMCtsTzlyRDdyclVrci9tT0dCbTBwNWQ5eTU1TUhZNWszUjJMakhSd3B3b3JKSk53U2pSUEI2aVNqanBNS2lTaU1jMm82dHR5R3lCdnRUSzhQRFh5YUZvMnNwdUFKa3kvOTd4NStYNElKUEVwUVV2cVJvYlRyRlBzbWl3UThNcE1WU3pJU25rbTYrOFVmeTlwUktVeVFta0c2RjJia3pJalBOVm5TYXNhb2lSVXoza3kxVm16dWRYS1M2Ulp3PT0iLCJtYWMiOiI3YWQwNzg4ZGExY2IyNjZkOGY3NTQ4OTUzZmY2M2I5NGYxY2Q2ODg0M2FmY2FhYWY5NzRjMjI4YzgzMjU4ODgwIiwidGFnIjoiIn0=','eyJpdiI6InNxQTlBejBtZld4S3dxcnBSWGt3VWc9PSIsInZhbHVlIjoibUViMjVTWFRKVC80b1cyeFV2aXVBR3k5QllRQ0wzNnhxbHZZVGorUk80anpIK2F6NTJRcTR3dUFyS3UrbGxKblk1S3pnOURBdVRjcGltU25pSnhYem5QVUhwUEcya1FENUR4bFNQOHhiZDRkMzYwd01RZEZBbkFlWGJXQ2FGTmt6dXI3MkhZVS9oRHFvWVIrZ2NVYWZBPT0iLCJtYWMiOiJmNmI3MWI0NmJiMjY5YmY1ZGI0ZTBlNjMxYjJmZmFlMGY4ZjNjMTc4NTdjMGFhMWFmOWVjYTczMjMyMDUzOTgzIiwidGFnIjoiIn0=','2026-06-07 12:43:00','connected',1,15,'[\"openid\", \"https://www.googleapis.com/auth/userinfo.email\", \"https://www.googleapis.com/auth/userinfo.profile\", \"https://www.googleapis.com/auth/business.manage\"]',NULL,NULL,'2026-06-07 11:43:01','2026-06-07 11:43:01'),(147123469,147123471,147123471,'thihoanglinhnguyen16082004@gmail.com','eyJpdiI6IlJjVUlpRWRCUE1RVDZCcC8zbk5ub3c9PSIsInZhbHVlIjoiS3p1ZmdKQnZ1MG0vK2ZwZi9aWGNpM0ZsL0FVbzhZclZaalA0QmUwUEhwa1h5TE1qNXJUWWpwb21sQ24vbER3aDdJd2NXWUVrL1RIbzk0dU13RTY3WmwyYmFPR3FVa3NSSHNOakhVdGxzcE14OXhSQkE1ZWU4UFhHWG5VazBvd21oc3NxVU5jRG5LMW5PbXRydWhhUFUwcW5lcnF2bVBiMks2UlJpSDZNdWRYUi9vK0hUY2ZvNXVSUCtMMWFpUXR4dkN0bGswTS9rY3RGRGRqMlNTdzdOczZnUEhPQmYrMDVoY2hCc3VuaUMxd0ZNMVFnUkpYNjlrMUo3S0tZbkFHYlViUUZUcVd0dytRL1VhOW9rL3dTcFJYRUFGQkhRNlZLTEs3Wk1rZHdWbzd6QzMxTnVsS0JlMy9rQzE4NDE1T2I0S0FjRDhUZC9SMksyZUJnSUNjNDZBPT0iLCJtYWMiOiI3ZGQzNTQ5M2JkNDlhMTgxZmJhZjU1ODhhY2YyYmY2MGIzZjgwMmNjYTQ3YTE1YmEzODcyMjYzZDE5NzA5YTVlIiwidGFnIjoiIn0=','eyJpdiI6Iityd2Jsb2JOd3FYdVVuTHBHSFpDeGc9PSIsInZhbHVlIjoidGRSb2NzUkRSanA4TC84cERScXQySjhTblJHbXNUdGIwMk5JTG9pMTRFdlRtTlphRzR1eVhObE9PTGZ3OU8zS0YrTy9HRjhGWlJjNlBvaU1ScmFRb1BsN2dTVFJmOTFxTUZET0NxbjJYajVLY3JWZUVYdmM0ZkM5RmtiRFdWSUtvUkdlOXNFdjhLK09UMWk2L2JTalhRPT0iLCJtYWMiOiJhM2I1ZjY5OTg2Y2UyMTVmNTdiYTZkN2EwOGEzMGIyNDRkZTdhOGI4YzI4OGFjYjRkYjQ1NmM0YzE1ZTg2ZmM4IiwidGFnIjoiIn0=','2026-06-07 13:13:09','connected',1,15,'[\"https://www.googleapis.com/auth/userinfo.profile\", \"openid\", \"https://www.googleapis.com/auth/business.manage\", \"https://www.googleapis.com/auth/userinfo.email\"]',NULL,NULL,'2026-06-07 12:13:10','2026-06-07 12:13:10');
/*!40000 ALTER TABLE `lb_google_business_connections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lb_google_business_locations`
--

DROP TABLE IF EXISTS `lb_google_business_locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lb_google_business_locations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint unsigned NOT NULL,
  `connection_id` bigint unsigned NOT NULL,
  `business_id` bigint unsigned DEFAULT NULL,
  `google_account_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `google_location_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `review_url` text COLLATE utf8mb4_unicode_ci,
  `opening_hours` json DEFAULT NULL,
  `sync_business_info` tinyint(1) NOT NULL DEFAULT '1',
  `sync_hours` tinyint(1) NOT NULL DEFAULT '1',
  `sync_reviews` tinyint(1) NOT NULL DEFAULT '1',
  `sync_insights` tinyint(1) NOT NULL DEFAULT '0',
  `auto_reply_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `is_managed` tinyint(1) NOT NULL DEFAULT '0',
  `managed_at` timestamp NULL DEFAULT NULL,
  `last_synced_at` timestamp NULL DEFAULT NULL,
  `last_reviews_synced_at` timestamp NULL DEFAULT NULL,
  `last_info_synced_at` timestamp NULL DEFAULT NULL,
  `last_hours_synced_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `gb_location_connection_unique` (`connection_id`,`google_location_id`),
  KEY `lb_google_business_locations_business_id_foreign` (`business_id`),
  KEY `lb_google_business_locations_team_id_business_id_index` (`team_id`,`business_id`),
  CONSTRAINT `lb_google_business_locations_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `lb_businesses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lb_google_business_locations_connection_id_foreign` FOREIGN KEY (`connection_id`) REFERENCES `lb_google_business_connections` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lb_google_business_locations_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lb_google_business_locations`
--

LOCK TABLES `lb_google_business_locations` WRITE;
/*!40000 ALTER TABLE `lb_google_business_locations` DISABLE KEYS */;
/*!40000 ALTER TABLE `lb_google_business_locations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lb_google_business_post_logs`
--

DROP TABLE IF EXISTS `lb_google_business_post_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lb_google_business_post_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint unsigned NOT NULL,
  `post_id` bigint unsigned DEFAULT NULL,
  `action` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `request_payload` json DEFAULT NULL,
  `response_body` json DEFAULT NULL,
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lb_google_business_post_logs_post_id_foreign` (`post_id`),
  KEY `lb_google_business_post_logs_team_id_status_index` (`team_id`,`status`),
  CONSTRAINT `lb_google_business_post_logs_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `lb_google_business_posts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lb_google_business_post_logs_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lb_google_business_post_logs`
--

LOCK TABLES `lb_google_business_post_logs` WRITE;
/*!40000 ALTER TABLE `lb_google_business_post_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `lb_google_business_post_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lb_google_business_posts`
--

DROP TABLE IF EXISTS `lb_google_business_posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lb_google_business_posts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint unsigned NOT NULL,
  `google_business_location_id` bigint unsigned NOT NULL,
  `business_id` bigint unsigned DEFAULT NULL,
  `campaign_id` bigint unsigned DEFAULT NULL,
  `landing_page_id` bigint unsigned DEFAULT NULL,
  `google_post_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `google_post_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'standard',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `summary` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `cta_type` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cta_url` text COLLATE utf8mb4_unicode_ci,
  `media_url` text COLLATE utf8mb4_unicode_ci,
  `coupon_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `terms` text COLLATE utf8mb4_unicode_ci,
  `start_at` timestamp NULL DEFAULT NULL,
  `end_at` timestamp NULL DEFAULT NULL,
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `status` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `search_url` text COLLATE utf8mb4_unicode_ci,
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lb_google_business_posts_business_id_foreign` (`business_id`),
  KEY `lb_google_business_posts_campaign_id_foreign` (`campaign_id`),
  KEY `lb_google_business_posts_landing_page_id_foreign` (`landing_page_id`),
  KEY `lb_google_business_posts_team_id_status_index` (`team_id`,`status`),
  KEY `gb_posts_location_status_index` (`google_business_location_id`,`status`),
  KEY `lb_google_business_posts_scheduled_at_index` (`scheduled_at`),
  CONSTRAINT `lb_google_business_posts_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `lb_businesses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lb_google_business_posts_campaign_id_foreign` FOREIGN KEY (`campaign_id`) REFERENCES `lb_campaigns` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lb_google_business_posts_google_business_location_id_foreign` FOREIGN KEY (`google_business_location_id`) REFERENCES `lb_google_business_locations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lb_google_business_posts_landing_page_id_foreign` FOREIGN KEY (`landing_page_id`) REFERENCES `lb_landing_pages` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lb_google_business_posts_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lb_google_business_posts`
--

LOCK TABLES `lb_google_business_posts` WRITE;
/*!40000 ALTER TABLE `lb_google_business_posts` DISABLE KEYS */;
/*!40000 ALTER TABLE `lb_google_business_posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lb_google_reviews`
--

DROP TABLE IF EXISTS `lb_google_reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lb_google_reviews` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint unsigned NOT NULL,
  `google_business_location_id` bigint unsigned NOT NULL,
  `business_id` bigint unsigned DEFAULT NULL,
  `google_review_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reviewer_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rating` tinyint unsigned NOT NULL DEFAULT '0',
  `comment` text COLLATE utf8mb4_unicode_ci,
  `reply` text COLLATE utf8mb4_unicode_ci,
  `local_reply` text COLLATE utf8mb4_unicode_ci,
  `reply_status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none',
  `auto_reply_status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none',
  `replied_at` timestamp NULL DEFAULT NULL,
  `review_created_at` timestamp NULL DEFAULT NULL,
  `review_updated_at` timestamp NULL DEFAULT NULL,
  `last_synced_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `gb_review_location_unique` (`google_business_location_id`,`google_review_id`),
  KEY `lb_google_reviews_business_id_foreign` (`business_id`),
  KEY `lb_google_reviews_team_id_business_id_rating_index` (`team_id`,`business_id`,`rating`),
  CONSTRAINT `lb_google_reviews_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `lb_businesses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lb_google_reviews_google_business_location_id_foreign` FOREIGN KEY (`google_business_location_id`) REFERENCES `lb_google_business_locations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lb_google_reviews_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lb_google_reviews`
--

LOCK TABLES `lb_google_reviews` WRITE;
/*!40000 ALTER TABLE `lb_google_reviews` DISABLE KEYS */;
/*!40000 ALTER TABLE `lb_google_reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lb_landing_pages`
--

DROP TABLE IF EXISTS `lb_landing_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lb_landing_pages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `business_id` bigint unsigned DEFAULT NULL,
  `campaign_id` bigint unsigned DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'lead',
  `template` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'local_campaign',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'published',
  `content` json DEFAULT NULL,
  `settings` json DEFAULT NULL,
  `visits_count` int unsigned NOT NULL DEFAULT '0',
  `conversions_count` int unsigned NOT NULL DEFAULT '0',
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lb_landing_pages_slug_unique` (`slug`),
  KEY `lb_landing_pages_user_id_status_index` (`user_id`,`status`),
  KEY `lb_landing_pages_business_id_type_index` (`business_id`,`type`),
  KEY `lb_landing_pages_campaign_id_index` (`campaign_id`),
  CONSTRAINT `lb_landing_pages_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `lb_businesses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lb_landing_pages_campaign_id_foreign` FOREIGN KEY (`campaign_id`) REFERENCES `lb_campaigns` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lb_landing_pages_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147123470 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lb_landing_pages`
--

LOCK TABLES `lb_landing_pages` WRITE;
/*!40000 ALTER TABLE `lb_landing_pages` DISABLE KEYS */;
INSERT INTO `lb_landing_pages` VALUES (147123468,147123471,147123468,147123468,'get-more-google-reviews','Get More Google Reviews','review','review_clean_request','published','{\"cta\": \"Continue\", \"benefits\": [], \"headline\": \"Nhận mọi lời khen chê\", \"description\": \"Cảm ơn bạn vì đã rì viu chân thật\", \"subheadline\": \"Cảm ơn vì đã đến. Để lại ý kiến của bạn để quán cải thiện thêm nhé\", \"thank_you_message\": \"Cảm ơn bạn vì đã rì viu chân thật\", \"landing_page_blocks\": []}','{\"price\": \"\", \"terms\": \"\", \"blocks\": [], \"design\": {\"logo_url\": \"https://mlhub.vn/storage/app/public/files/user-147123471/2026/06/z7727040031897-dd4ee118b04754ab1a1f172abfa51db7-nqbyvc.jpg\", \"show_faq\": true, \"template\": \"review_clean_request\", \"show_logo\": true, \"card_style\": \"soft\", \"font_style\": \"modern\", \"logo_shape\": \"circle\", \"show_terms\": true, \"cover_image\": \"\", \"button_style\": \"pill\", \"primary_color\": \"#16a34a\", \"show_benefits\": true, \"background_type\": \"gradient\", \"background_color\": \"#0f766e\", \"show_social_links\": true, \"show_business_info\": true}, \"expiry\": \"\", \"service\": \"\", \"discount\": \"\", \"duration\": \"\", \"review_url\": \"https://maps.app.goo.gl/7rV5Q1Nwxc3wngBt9\", \"coupon_title\": \"\", \"available_slots\": []}',2,5,'2026-06-07 12:01:09','2026-06-07 11:49:56','2026-06-07 12:01:09'),(147123469,147123471,147123468,147123469,'book-ban','Book bàn','booking','booking_tour_booking','published','{\"cta\": \"Request booking\", \"benefits\": [\"Búc lẹ\"], \"headline\": \"Búc bàn ở đây\", \"description\": \"\", \"subheadline\": \"Búc lẹ kẻo hết\", \"thank_you_message\": \"Cảm ơn vì đã búc\", \"landing_page_blocks\": []}','{\"price\": \"500000\", \"terms\": \"\", \"blocks\": [], \"design\": {\"logo_url\": \"\", \"show_faq\": true, \"template\": \"booking_tour_booking\", \"show_logo\": true, \"card_style\": \"soft\", \"font_style\": \"modern\", \"logo_shape\": \"square\", \"show_terms\": true, \"cover_image\": \"\", \"button_style\": \"pill\", \"primary_color\": \"#c2410c\", \"show_benefits\": true, \"background_type\": \"gradient\", \"background_color\": \"#fff7ed\", \"show_social_links\": true, \"show_business_info\": true}, \"expiry\": \"\", \"service\": \"Appointment\", \"discount\": \"\", \"duration\": \"\", \"review_url\": \"\", \"coupon_title\": \"\", \"available_slots\": [\"09:00\", \"10:00\", \"14:00\", \"15:00\"]}',17,4,'2026-06-07 13:02:21','2026-06-07 12:04:20','2026-06-07 13:02:21');
/*!40000 ALTER TABLE `lb_landing_pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lb_lead_submissions`
--

DROP TABLE IF EXISTS `lb_lead_submissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lb_lead_submissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `campaign_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `payload` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lb_lead_submissions_campaign_id_foreign` (`campaign_id`),
  KEY `lb_lead_submissions_user_id_campaign_id_index` (`user_id`,`campaign_id`),
  CONSTRAINT `lb_lead_submissions_campaign_id_foreign` FOREIGN KEY (`campaign_id`) REFERENCES `lb_campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lb_lead_submissions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lb_lead_submissions`
--

LOCK TABLES `lb_lead_submissions` WRITE;
/*!40000 ALTER TABLE `lb_lead_submissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `lb_lead_submissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lb_locations`
--

DROP TABLE IF EXISTS `lb_locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lb_locations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `business_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `google_maps_url` text COLLATE utf8mb4_unicode_ci,
  `opening_hours` json DEFAULT NULL,
  `qr_design` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lb_locations_business_id_foreign` (`business_id`),
  KEY `lb_locations_user_id_business_id_index` (`user_id`,`business_id`),
  CONSTRAINT `lb_locations_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `lb_businesses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lb_locations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lb_locations`
--

LOCK TABLES `lb_locations` WRITE;
/*!40000 ALTER TABLE `lb_locations` DISABLE KEYS */;
/*!40000 ALTER TABLE `lb_locations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lb_loyalty_cards`
--

DROP TABLE IF EXISTS `lb_loyalty_cards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lb_loyalty_cards` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `team_id` bigint unsigned DEFAULT NULL,
  `business_id` bigint unsigned NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `required_stamps` int unsigned NOT NULL DEFAULT '10',
  `stamp_method` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'qr_scan',
  `customer_identifier` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'phone',
  `reward_title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reward_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'free_item',
  `reward_value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expiry_days` int unsigned DEFAULT NULL,
  `stamp_cooldown_minutes` int unsigned NOT NULL DEFAULT '1440',
  `max_stamps_per_day` int unsigned NOT NULL DEFAULT '1',
  `settings` json DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lb_loyalty_cards_slug_unique` (`slug`),
  KEY `lb_loyalty_cards_user_id_foreign` (`user_id`),
  KEY `lb_loyalty_cards_team_id_foreign` (`team_id`),
  KEY `lb_loyalty_cards_business_id_foreign` (`business_id`),
  CONSTRAINT `lb_loyalty_cards_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `lb_businesses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lb_loyalty_cards_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lb_loyalty_cards_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lb_loyalty_cards`
--

LOCK TABLES `lb_loyalty_cards` WRITE;
/*!40000 ALTER TABLE `lb_loyalty_cards` DISABLE KEYS */;
/*!40000 ALTER TABLE `lb_loyalty_cards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lb_loyalty_customers`
--

DROP TABLE IF EXISTS `lb_loyalty_customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lb_loyalty_customers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `card_id` bigint unsigned NOT NULL,
  `customer_id` bigint unsigned NOT NULL,
  `stamps_count` int unsigned NOT NULL DEFAULT '0',
  `completed_count` int unsigned NOT NULL DEFAULT '0',
  `last_stamp_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lb_loyalty_customers_card_id_customer_id_unique` (`card_id`,`customer_id`),
  KEY `lb_loyalty_customers_customer_id_foreign` (`customer_id`),
  CONSTRAINT `lb_loyalty_customers_card_id_foreign` FOREIGN KEY (`card_id`) REFERENCES `lb_loyalty_cards` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lb_loyalty_customers_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `lb_customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lb_loyalty_customers`
--

LOCK TABLES `lb_loyalty_customers` WRITE;
/*!40000 ALTER TABLE `lb_loyalty_customers` DISABLE KEYS */;
/*!40000 ALTER TABLE `lb_loyalty_customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lb_loyalty_rewards`
--

DROP TABLE IF EXISTS `lb_loyalty_rewards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lb_loyalty_rewards` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `card_id` bigint unsigned NOT NULL,
  `customer_id` bigint unsigned NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'available',
  `expires_at` timestamp NULL DEFAULT NULL,
  `used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lb_loyalty_rewards_code_unique` (`code`),
  KEY `lb_loyalty_rewards_card_id_foreign` (`card_id`),
  KEY `lb_loyalty_rewards_customer_id_foreign` (`customer_id`),
  CONSTRAINT `lb_loyalty_rewards_card_id_foreign` FOREIGN KEY (`card_id`) REFERENCES `lb_loyalty_cards` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lb_loyalty_rewards_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `lb_customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lb_loyalty_rewards`
--

LOCK TABLES `lb_loyalty_rewards` WRITE;
/*!40000 ALTER TABLE `lb_loyalty_rewards` DISABLE KEYS */;
/*!40000 ALTER TABLE `lb_loyalty_rewards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lb_loyalty_stamps`
--

DROP TABLE IF EXISTS `lb_loyalty_stamps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lb_loyalty_stamps` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `card_id` bigint unsigned NOT NULL,
  `customer_id` bigint unsigned NOT NULL,
  `source` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'qr_scan',
  `staff_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lb_loyalty_stamps_card_id_foreign` (`card_id`),
  KEY `lb_loyalty_stamps_customer_id_foreign` (`customer_id`),
  KEY `lb_loyalty_stamps_staff_id_foreign` (`staff_id`),
  CONSTRAINT `lb_loyalty_stamps_card_id_foreign` FOREIGN KEY (`card_id`) REFERENCES `lb_loyalty_cards` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lb_loyalty_stamps_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `lb_customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lb_loyalty_stamps_staff_id_foreign` FOREIGN KEY (`staff_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lb_loyalty_stamps`
--

LOCK TABLES `lb_loyalty_stamps` WRITE;
/*!40000 ALTER TABLE `lb_loyalty_stamps` DISABLE KEYS */;
/*!40000 ALTER TABLE `lb_loyalty_stamps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lb_marketing_templates`
--

DROP TABLE IF EXISTS `lb_marketing_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lb_marketing_templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `team_id` bigint unsigned DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'landing_page',
  `category` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `goal` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'lead',
  `description` text COLLATE utf8mb4_unicode_ci,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fa-light fa-grid-2',
  `preview_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` json DEFAULT NULL,
  `is_system` tinyint(1) NOT NULL DEFAULT '0',
  `source` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'custom',
  `visibility` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'private',
  `marketplace_status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none',
  `submitted_at` timestamp NULL DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `approved_by` bigint unsigned DEFAULT NULL,
  `featured` tinyint(1) NOT NULL DEFAULT '0',
  `rating_count` int unsigned NOT NULL DEFAULT '0',
  `rating_sum` int unsigned NOT NULL DEFAULT '0',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `version` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1.0.0',
  `usage_count` int unsigned NOT NULL DEFAULT '0',
  `settings` json DEFAULT NULL,
  `design` json DEFAULT NULL,
  `builder_schema` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lb_marketing_templates_team_id_foreign` (`team_id`),
  KEY `lb_marketing_templates_created_by_foreign` (`created_by`),
  KEY `lb_marketing_templates_approved_by_foreign` (`approved_by`),
  KEY `lb_marketing_templates_user_id_type_index` (`user_id`,`type`),
  KEY `lb_marketing_templates_slug_index` (`slug`),
  KEY `lb_marketing_templates_category_index` (`category`),
  KEY `lb_marketing_templates_goal_index` (`goal`),
  KEY `lb_marketing_templates_source_index` (`source`),
  KEY `lb_marketing_templates_visibility_index` (`visibility`),
  KEY `lb_marketing_templates_marketplace_status_index` (`marketplace_status`),
  KEY `lb_marketing_templates_featured_index` (`featured`),
  KEY `lb_marketing_templates_status_index` (`status`),
  CONSTRAINT `lb_marketing_templates_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lb_marketing_templates_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lb_marketing_templates_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lb_marketing_templates_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=147123556 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lb_marketing_templates`
--

LOCK TABLES `lb_marketing_templates` WRITE;
/*!40000 ALTER TABLE `lb_marketing_templates` DISABLE KEYS */;
INSERT INTO `lb_marketing_templates` VALUES (147123468,NULL,NULL,NULL,'Get More Google Reviews','get-more-google-reviews','campaign','general','review','Route 4-5 star customers to Google and collect low-score feedback privately.','fa-light fa-star',NULL,'{\"cta\": \"Share your experience\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"How was your visit?\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Route 4-5 star customers to Google and collect low-score feedback privately.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Google Review Request\", \"email_content\": {\"body\": \"How was your visit?\\n\\nRoute 4-5 star customers to Google and collect low-score feedback privately.\", \"subject\": \"Google Review Request\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: review. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"How was your visit? Share your experience\"}, \"thank_you_message\": \"Thanks for your feedback.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Share your experience\", \"headline\": \"How was your visit?\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Share your experience\", \"thank_you_message\": \"Thanks for your feedback.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"review\", \"campaign_type\": \"review\", \"target_module\": \"review\", \"tracking_goal\": \"review\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": true, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": true}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#d09100\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": true, \"landing_page_blocks\": true}','2026-06-07 11:24:43','2026-06-07 11:24:43'),(147123469,NULL,NULL,NULL,'Facebook Review Request','facebook-review-request','campaign','general','review','Ask happy customers for a Facebook review after a visit.','fa-light fa-thumbs-up',NULL,'{\"cta\": \"Leave a Review\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Enjoyed your experience?\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Ask happy customers for a Facebook review after a visit.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Facebook Review Request\", \"email_content\": {\"body\": \"Enjoyed your experience?\\n\\nAsk happy customers for a Facebook review after a visit.\", \"subject\": \"Facebook Review Request\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: review. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Enjoyed your experience? Leave a Review\"}, \"thank_you_message\": \"Thank you for supporting us.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Leave a Review\", \"headline\": \"Enjoyed your experience?\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Leave a Review\", \"thank_you_message\": \"Thank you for supporting us.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"review\", \"campaign_type\": \"review\", \"target_module\": \"review\", \"tracking_goal\": \"review\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": true, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": true}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#d09100\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": true, \"landing_page_blocks\": true}','2026-06-07 11:24:43','2026-06-07 11:24:43'),(147123470,NULL,NULL,NULL,'Low-Score Recovery Form','low-score-recovery-form','form','general','feedback','Capture private customer concerns before they become public reviews.','fa-light fa-shield-heart',NULL,'{\"cta\": \"Send Feedback\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Tell us what went wrong\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Capture private customer concerns before they become public reviews.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Private Feedback\", \"email_content\": {\"body\": \"Tell us what went wrong\\n\\nCapture private customer concerns before they become public reviews.\", \"subject\": \"Private Feedback\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: feedback. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Tell us what went wrong Send Feedback\"}, \"thank_you_message\": \"We received your feedback.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Send Feedback\", \"headline\": \"Tell us what went wrong\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Send Feedback\", \"thank_you_message\": \"We received your feedback.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"feedback\", \"campaign_type\": \"feedback\", \"target_module\": \"feedback\", \"tracking_goal\": \"feedback\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": false, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": false}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#0891b2\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": false, \"landing_page_blocks\": false}','2026-06-07 11:24:43','2026-06-07 11:24:43'),(147123471,NULL,NULL,NULL,'Post-Visit Rating Funnel','post-visit-rating-funnel','landing_page','general','review','A public page that routes ratings into reviews or private recovery.','fa-light fa-ranking-star',NULL,'{\"cta\": \"Continue\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Rate your recent visit\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"A public page that routes ratings into reviews or private recovery.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Post-Visit Rating Funnel\", \"email_content\": {\"body\": \"Rate your recent visit\\n\\nA public page that routes ratings into reviews or private recovery.\", \"subject\": \"Post-Visit Rating Funnel\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: review. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Rate your recent visit Continue\"}, \"thank_you_message\": \"Thanks for sharing your rating.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Continue\", \"headline\": \"Rate your recent visit\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Continue\", \"thank_you_message\": \"Thanks for sharing your rating.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"review\", \"campaign_type\": \"review\", \"target_module\": \"review\", \"tracking_goal\": \"review\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": false, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": true}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#d09100\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": false, \"landing_page_blocks\": true}','2026-06-07 11:24:43','2026-06-07 11:24:43'),(147123472,NULL,NULL,NULL,'Weekend Booking Boost','weekend-booking-boost','campaign','spa','booking','Promote available weekend appointment slots.','fa-light fa-calendar-check',NULL,'{\"cta\": \"Book Now\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Book your weekend appointment\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Promote available weekend appointment slots.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Weekend Booking Boost\", \"email_content\": {\"body\": \"Book your weekend appointment\\n\\nPromote available weekend appointment slots.\", \"subject\": \"Weekend Booking Boost\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: booking. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Book your weekend appointment Book Now\"}, \"thank_you_message\": \"Your request has been sent.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Book Now\", \"headline\": \"Book your weekend appointment\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Book Now\", \"thank_you_message\": \"Your request has been sent.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"booking\", \"campaign_type\": \"booking\", \"target_module\": \"booking\", \"tracking_goal\": \"booking\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": true, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": true}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#0f766e\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": true, \"landing_page_blocks\": true}','2026-06-07 11:24:43','2026-06-07 11:24:43'),(147123473,NULL,NULL,NULL,'Free Consultation Booking','free-consultation-booking','campaign','clinic','booking','Collect consultation appointment requests from a public booking page.','fa-light fa-user-doctor',NULL,'{\"cta\": \"Request Time\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Book a free consultation\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Collect consultation appointment requests from a public booking page.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Free Consultation\", \"email_content\": {\"body\": \"Book a free consultation\\n\\nCollect consultation appointment requests from a public booking page.\", \"subject\": \"Free Consultation\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: booking. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Book a free consultation Request Time\"}, \"thank_you_message\": \"We will confirm your appointment soon.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Request Time\", \"headline\": \"Book a free consultation\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Request Time\", \"thank_you_message\": \"We will confirm your appointment soon.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"booking\", \"campaign_type\": \"booking\", \"target_module\": \"booking\", \"tracking_goal\": \"booking\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": true, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": true}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#0f766e\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": true, \"landing_page_blocks\": true}','2026-06-07 11:24:43','2026-06-07 11:24:43'),(147123474,NULL,NULL,NULL,'Limited Slots Campaign','limited-slots-campaign','campaign','salon','booking','Create urgency around limited service slots.','fa-light fa-clock',NULL,'{\"cta\": \"Reserve My Slot\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Only a few appointment slots left\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Create urgency around limited service slots.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Limited Slots\", \"email_content\": {\"body\": \"Only a few appointment slots left\\n\\nCreate urgency around limited service slots.\", \"subject\": \"Limited Slots\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: booking. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Only a few appointment slots left Reserve My Slot\"}, \"thank_you_message\": \"Your slot request has been received.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Reserve My Slot\", \"headline\": \"Only a few appointment slots left\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Reserve My Slot\", \"thank_you_message\": \"Your slot request has been received.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"booking\", \"campaign_type\": \"booking\", \"target_module\": \"booking\", \"tracking_goal\": \"booking\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": true, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": true}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#0f766e\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": true, \"landing_page_blocks\": true}','2026-06-07 11:24:43','2026-06-07 11:24:43'),(147123475,NULL,NULL,NULL,'First-Time Appointment','first-time-appointment','landing_page','general','booking','Help new customers request their first appointment.','fa-light fa-calendar-plus',NULL,'{\"cta\": \"Get Started\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Book your first visit\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Help new customers request their first appointment.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"First-Time Appointment\", \"email_content\": {\"body\": \"Book your first visit\\n\\nHelp new customers request their first appointment.\", \"subject\": \"First-Time Appointment\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: booking. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Book your first visit Get Started\"}, \"thank_you_message\": \"We will contact you soon.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Get Started\", \"headline\": \"Book your first visit\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Get Started\", \"thank_you_message\": \"We will contact you soon.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"booking\", \"campaign_type\": \"booking\", \"target_module\": \"booking\", \"tracking_goal\": \"booking\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": false, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": true}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#0f766e\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": false, \"landing_page_blocks\": true}','2026-06-07 11:24:43','2026-06-07 11:24:43'),(147123476,NULL,NULL,NULL,'20% Off Next Visit','20-off-next-visit','campaign','general','coupon','Let customers claim a limited-time coupon and return sooner.','fa-light fa-ticket',NULL,'{\"cta\": \"Claim Coupon\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Get 20% off your next visit\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Let customers claim a limited-time coupon and return sooner.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"20% Off Next Visit\", \"email_content\": {\"body\": \"Get 20% off your next visit\\n\\nLet customers claim a limited-time coupon and return sooner.\", \"subject\": \"20% Off Next Visit\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: coupon. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Get 20% off your next visit Claim Coupon\"}, \"thank_you_message\": \"Your coupon code is ready.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Claim Coupon\", \"headline\": \"Get 20% off your next visit\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Claim Coupon\", \"thank_you_message\": \"Your coupon code is ready.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"coupon\", \"campaign_type\": \"coupon\", \"target_module\": \"coupon\", \"tracking_goal\": \"coupon\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": true, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": true}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#84a900\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": true, \"landing_page_blocks\": true}','2026-06-07 11:24:43','2026-06-07 11:24:43'),(147123477,NULL,NULL,NULL,'Buy 1 Get 1 Trial','buy-one-get-one-trial','campaign','retail','coupon','Promote a simple BOGO offer with redemption tracking.','fa-light fa-bags-shopping',NULL,'{\"cta\": \"Claim Offer\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Bring a friend and save\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Promote a simple BOGO offer with redemption tracking.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Buy 1 Get 1 Trial\", \"email_content\": {\"body\": \"Bring a friend and save\\n\\nPromote a simple BOGO offer with redemption tracking.\", \"subject\": \"Buy 1 Get 1 Trial\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: coupon. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Bring a friend and save Claim Offer\"}, \"thank_you_message\": \"Show this offer at checkout.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Claim Offer\", \"headline\": \"Bring a friend and save\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Claim Offer\", \"thank_you_message\": \"Show this offer at checkout.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"coupon\", \"campaign_type\": \"coupon\", \"target_module\": \"coupon\", \"tracking_goal\": \"coupon\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": true, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": true}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#84a900\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": true, \"landing_page_blocks\": true}','2026-06-07 11:24:43','2026-06-07 11:24:43'),(147123478,NULL,NULL,NULL,'Birthday Offer','birthday-offer','landing_page','restaurant','coupon','Collect birthday coupon claims and customer contacts.','fa-light fa-cake-candles',NULL,'{\"cta\": \"Get My Birthday Offer\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Celebrate with a special offer\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Collect birthday coupon claims and customer contacts.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Birthday Offer\", \"email_content\": {\"body\": \"Celebrate with a special offer\\n\\nCollect birthday coupon claims and customer contacts.\", \"subject\": \"Birthday Offer\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: coupon. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Celebrate with a special offer Get My Birthday Offer\"}, \"thank_you_message\": \"Your birthday offer is ready.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Get My Birthday Offer\", \"headline\": \"Celebrate with a special offer\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Get My Birthday Offer\", \"thank_you_message\": \"Your birthday offer is ready.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"coupon\", \"campaign_type\": \"coupon\", \"target_module\": \"coupon\", \"tracking_goal\": \"coupon\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": false, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": true}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#84a900\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": false, \"landing_page_blocks\": true}','2026-06-07 11:24:43','2026-06-07 11:24:43'),(147123479,NULL,NULL,NULL,'Come Back Coupon','come-back-coupon','campaign','general','retention','Win back past customers with a return visit incentive.','fa-light fa-rotate-left',NULL,'{\"cta\": \"Claim Comeback Offer\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"We would love to see you again\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Win back past customers with a return visit incentive.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Come Back Offer\", \"email_content\": {\"body\": \"We would love to see you again\\n\\nWin back past customers with a return visit incentive.\", \"subject\": \"Come Back Offer\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: retention. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"We would love to see you again Claim Comeback Offer\"}, \"thank_you_message\": \"Your return offer has been saved.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Claim Comeback Offer\", \"headline\": \"We would love to see you again\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Claim Comeback Offer\", \"thank_you_message\": \"Your return offer has been saved.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"coupon\", \"campaign_type\": \"coupon\", \"target_module\": \"coupon\", \"tracking_goal\": \"coupon_claim\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": true, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": true}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#84a900\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": true, \"landing_page_blocks\": true}','2026-06-07 11:24:43','2026-06-07 11:24:43'),(147123480,NULL,NULL,NULL,'Post-Visit Feedback','post-visit-feedback','campaign','general','feedback','Ask customers for private feedback after a service.','fa-light fa-comment-dots',NULL,'{\"cta\": \"Send Feedback\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"How did we do?\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Ask customers for private feedback after a service.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Post-Visit Feedback\", \"email_content\": {\"body\": \"How did we do?\\n\\nAsk customers for private feedback after a service.\", \"subject\": \"Post-Visit Feedback\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: feedback. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"How did we do? Send Feedback\"}, \"thank_you_message\": \"Thanks for helping us improve.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Send Feedback\", \"headline\": \"How did we do?\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Send Feedback\", \"thank_you_message\": \"Thanks for helping us improve.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"feedback\", \"campaign_type\": \"feedback\", \"target_module\": \"feedback\", \"tracking_goal\": \"feedback\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": true, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": true}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#0891b2\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": true, \"landing_page_blocks\": true}','2026-06-07 11:24:43','2026-06-07 11:24:43'),(147123481,NULL,NULL,NULL,'Service Quality Survey','service-quality-survey','form','clinic','feedback','Collect structured feedback on service quality.','fa-light fa-list-check',NULL,'{\"cta\": \"Submit Survey\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Tell us about your service experience\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Collect structured feedback on service quality.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Service Quality Survey\", \"email_content\": {\"body\": \"Tell us about your service experience\\n\\nCollect structured feedback on service quality.\", \"subject\": \"Service Quality Survey\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: feedback. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Tell us about your service experience Submit Survey\"}, \"thank_you_message\": \"Your response has been recorded.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Submit Survey\", \"headline\": \"Tell us about your service experience\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Submit Survey\", \"thank_you_message\": \"Your response has been recorded.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"feedback\", \"campaign_type\": \"feedback\", \"target_module\": \"feedback\", \"tracking_goal\": \"feedback\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": false, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": false}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#0891b2\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": false, \"landing_page_blocks\": false}','2026-06-07 11:24:43','2026-06-07 11:24:43'),(147123482,NULL,NULL,NULL,'Complaint Recovery Form','complaint-recovery-form','form','general','feedback','Capture issue details and assign recovery follow-up.','fa-light fa-life-ring',NULL,'{\"cta\": \"Send Details\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Let us make it right\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Capture issue details and assign recovery follow-up.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Complaint Recovery\", \"email_content\": {\"body\": \"Let us make it right\\n\\nCapture issue details and assign recovery follow-up.\", \"subject\": \"Complaint Recovery\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: feedback. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Let us make it right Send Details\"}, \"thank_you_message\": \"Our team will review your request.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Send Details\", \"headline\": \"Let us make it right\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Send Details\", \"thank_you_message\": \"Our team will review your request.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"feedback\", \"campaign_type\": \"feedback\", \"target_module\": \"feedback\", \"tracking_goal\": \"feedback\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": false, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": false}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#0891b2\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": false, \"landing_page_blocks\": false}','2026-06-07 11:24:43','2026-06-07 11:24:43'),(147123483,NULL,NULL,NULL,'Quick Satisfaction Check','quick-satisfaction-check','landing_page','general','feedback','A short satisfaction page for QR follow-up.','fa-light fa-face-smile',NULL,'{\"cta\": \"Submit Rating\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Quick question about your visit\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"A short satisfaction page for QR follow-up.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Satisfaction Check\", \"email_content\": {\"body\": \"Quick question about your visit\\n\\nA short satisfaction page for QR follow-up.\", \"subject\": \"Satisfaction Check\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: feedback. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Quick question about your visit Submit Rating\"}, \"thank_you_message\": \"Thank you for your rating.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Submit Rating\", \"headline\": \"Quick question about your visit\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Submit Rating\", \"thank_you_message\": \"Thank you for your rating.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"feedback\", \"campaign_type\": \"feedback\", \"target_module\": \"feedback\", \"tracking_goal\": \"feedback\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": false, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": true}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#0891b2\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": false, \"landing_page_blocks\": true}','2026-06-07 11:24:43','2026-06-07 11:24:43'),(147123484,NULL,NULL,NULL,'Free Consultation Lead Form','free-consultation-lead-form','campaign','clinic','lead','Collect consultation leads with name, phone, email and service interest.','fa-light fa-address-card',NULL,'{\"cta\": \"Request Consultation\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Request a free consultation\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Collect consultation leads with name, phone, email and service interest.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Free Consultation Lead Form\", \"email_content\": {\"body\": \"Request a free consultation\\n\\nCollect consultation leads with name, phone, email and service interest.\", \"subject\": \"Free Consultation Lead Form\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: lead. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Request a free consultation Request Consultation\"}, \"thank_you_message\": \"We have received your request.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Request Consultation\", \"headline\": \"Request a free consultation\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Request Consultation\", \"thank_you_message\": \"We have received your request.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"lead\", \"campaign_type\": \"lead\", \"target_module\": \"lead\", \"tracking_goal\": \"lead\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": true, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": true}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#2563eb\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": true, \"landing_page_blocks\": true}','2026-06-07 11:24:43','2026-06-07 11:24:43'),(147123485,NULL,NULL,NULL,'Quote Request Form','quote-request-form','form','general','lead','Capture project or service quote requests.','fa-light fa-file-invoice-dollar',NULL,'{\"cta\": \"Get Quote\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Request a local service quote\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Capture project or service quote requests.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Quote Request\", \"email_content\": {\"body\": \"Request a local service quote\\n\\nCapture project or service quote requests.\", \"subject\": \"Quote Request\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: lead. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Request a local service quote Get Quote\"}, \"thank_you_message\": \"Your quote request has been sent.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Get Quote\", \"headline\": \"Request a local service quote\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Get Quote\", \"thank_you_message\": \"Your quote request has been sent.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"lead\", \"campaign_type\": \"lead\", \"target_module\": \"lead\", \"tracking_goal\": \"lead\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": false, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": false}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#2563eb\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": false, \"landing_page_blocks\": false}','2026-06-07 11:24:43','2026-06-07 11:24:43'),(147123486,NULL,NULL,NULL,'New Customer Inquiry','new-customer-inquiry','landing_page','general','lead','A simple lead capture page for first-time customers.','fa-light fa-user-plus',NULL,'{\"cta\": \"Contact Me\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Tell us what you need\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"A simple lead capture page for first-time customers.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"New Customer Inquiry\", \"email_content\": {\"body\": \"Tell us what you need\\n\\nA simple lead capture page for first-time customers.\", \"subject\": \"New Customer Inquiry\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: lead. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Tell us what you need Contact Me\"}, \"thank_you_message\": \"We will follow up shortly.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Contact Me\", \"headline\": \"Tell us what you need\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Contact Me\", \"thank_you_message\": \"We will follow up shortly.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"lead\", \"campaign_type\": \"lead\", \"target_module\": \"lead\", \"tracking_goal\": \"lead\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": false, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": true}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#2563eb\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": false, \"landing_page_blocks\": true}','2026-06-07 11:24:43','2026-06-07 11:24:43'),(147123487,NULL,NULL,NULL,'Service Interest Form','service-interest-form','form','gym','lead','Ask prospects which service they are interested in.','fa-light fa-clipboard-question',NULL,'{\"cta\": \"Send Request\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Which service are you interested in?\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Ask prospects which service they are interested in.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Service Interest\", \"email_content\": {\"body\": \"Which service are you interested in?\\n\\nAsk prospects which service they are interested in.\", \"subject\": \"Service Interest\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: lead. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Which service are you interested in? Send Request\"}, \"thank_you_message\": \"Thanks, we will reach out soon.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Send Request\", \"headline\": \"Which service are you interested in?\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Send Request\", \"thank_you_message\": \"Thanks, we will reach out soon.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"lead\", \"campaign_type\": \"lead\", \"target_module\": \"lead\", \"tracking_goal\": \"lead\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": false, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": false}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#2563eb\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": false, \"landing_page_blocks\": false}','2026-06-07 11:24:43','2026-06-07 11:24:43'),(147123488,NULL,NULL,NULL,'Review Request Message','review-request-message','content','general','review','AI prompt for a friendly review request message.','fa-light fa-message-star',NULL,'{\"cta\": \"Generate Message\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Ask {customer_name} to review {business_name}\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"AI prompt for a friendly review request message.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": false}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Review Request Message\", \"email_content\": {\"body\": \"Ask {customer_name} to review {business_name}\\n\\nAI prompt for a friendly review request message.\", \"subject\": \"Review Request Message\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: review. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Ask {customer_name} to review {business_name} Generate Message\"}, \"thank_you_message\": \"Message generated.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Generate Message\", \"headline\": \"Ask {customer_name} to review {business_name}\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Generate Message\", \"thank_you_message\": \"Message generated.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"content\", \"campaign_type\": \"content\", \"target_module\": \"content\", \"tracking_goal\": \"review\", \"default_status\": \"published\", \"creates_qr_code\": false, \"creates_campaign\": false, \"creates_tracking\": false, \"requires_business\": true, \"creates_landing_page\": false}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#d09100\"}','{\"form_builder\": false, \"prompt_variables\": true, \"campaign_settings\": false, \"landing_page_blocks\": false}','2026-06-07 11:24:43','2026-06-07 11:24:43'),(147123489,NULL,NULL,NULL,'Booking Reminder','booking-reminder','content','general','booking','AI prompt for appointment reminder copy.','fa-light fa-bell',NULL,'{\"cta\": \"Generate Reminder\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Remind {customer_name} about {service_name}\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"AI prompt for appointment reminder copy.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": false}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Booking Reminder\", \"email_content\": {\"body\": \"Remind {customer_name} about {service_name}\\n\\nAI prompt for appointment reminder copy.\", \"subject\": \"Booking Reminder\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: booking. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Remind {customer_name} about {service_name} Generate Reminder\"}, \"thank_you_message\": \"Reminder generated.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Generate Reminder\", \"headline\": \"Remind {customer_name} about {service_name}\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Generate Reminder\", \"thank_you_message\": \"Reminder generated.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"content\", \"campaign_type\": \"content\", \"target_module\": \"content\", \"tracking_goal\": \"booking\", \"default_status\": \"published\", \"creates_qr_code\": false, \"creates_campaign\": false, \"creates_tracking\": false, \"requires_business\": true, \"creates_landing_page\": false}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#0f766e\"}','{\"form_builder\": false, \"prompt_variables\": true, \"campaign_settings\": false, \"landing_page_blocks\": false}','2026-06-07 11:24:43','2026-06-07 11:24:43'),(147123490,NULL,NULL,NULL,'Lead Follow-up','lead-follow-up','content','general','lead','AI prompt for follow-up messages after a lead submission.','fa-light fa-paper-plane',NULL,'{\"cta\": \"Generate Follow-up\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Follow up with a new lead for {service_name}\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"AI prompt for follow-up messages after a lead submission.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": false}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Lead Follow-up\", \"email_content\": {\"body\": \"Follow up with a new lead for {service_name}\\n\\nAI prompt for follow-up messages after a lead submission.\", \"subject\": \"Lead Follow-up\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: lead. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Follow up with a new lead for {service_name} Generate Follow-up\"}, \"thank_you_message\": \"Follow-up generated.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Generate Follow-up\", \"headline\": \"Follow up with a new lead for {service_name}\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Generate Follow-up\", \"thank_you_message\": \"Follow-up generated.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"content\", \"campaign_type\": \"content\", \"target_module\": \"content\", \"tracking_goal\": \"lead\", \"default_status\": \"published\", \"creates_qr_code\": false, \"creates_campaign\": false, \"creates_tracking\": false, \"requires_business\": true, \"creates_landing_page\": false}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#2563eb\"}','{\"form_builder\": false, \"prompt_variables\": true, \"campaign_settings\": false, \"landing_page_blocks\": false}','2026-06-07 11:24:43','2026-06-07 11:24:43'),(147123491,NULL,NULL,NULL,'Win-back Message','win-back-message','content','general','retention','AI prompt for bringing previous customers back.','fa-light fa-reply-clock',NULL,'{\"cta\": \"Generate Copy\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Invite {customer_name} back with {offer}\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"AI prompt for bringing previous customers back.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": false}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Win-back Message\", \"email_content\": {\"body\": \"Invite {customer_name} back with {offer}\\n\\nAI prompt for bringing previous customers back.\", \"subject\": \"Win-back Message\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: retention. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Invite {customer_name} back with {offer} Generate Copy\"}, \"thank_you_message\": \"Win-back copy generated.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Generate Copy\", \"headline\": \"Invite {customer_name} back with {offer}\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Generate Copy\", \"thank_you_message\": \"Win-back copy generated.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"content\", \"campaign_type\": \"content\", \"target_module\": \"content\", \"tracking_goal\": \"coupon_claim\", \"default_status\": \"published\", \"creates_qr_code\": false, \"creates_campaign\": false, \"creates_tracking\": false, \"requires_business\": true, \"creates_landing_page\": false}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#84a900\"}','{\"form_builder\": false, \"prompt_variables\": true, \"campaign_settings\": false, \"landing_page_blocks\": false}','2026-06-07 11:24:43','2026-06-07 11:24:43'),(147123492,NULL,NULL,NULL,'Review Request Email','review-request-email','email','general','review','Email template that asks recent customers for a public review.','fa-light fa-envelope-open-text',NULL,'{\"cta\": \"Leave a Review\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Thanks for visiting {business_name}\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Email template that asks recent customers for a public review.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Review Request Email\", \"email_content\": {\"body\": \"Thanks for visiting {business_name}\\n\\nEmail template that asks recent customers for a public review.\", \"subject\": \"Review Request Email\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: review. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Thanks for visiting {business_name} Leave a Review\"}, \"thank_you_message\": \"Thanks for supporting us.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Leave a Review\", \"headline\": \"Thanks for visiting {business_name}\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Leave a Review\", \"thank_you_message\": \"Thanks for supporting us.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"review\", \"campaign_type\": \"review\", \"target_module\": \"review\", \"tracking_goal\": \"review\", \"default_status\": \"published\", \"creates_qr_code\": false, \"creates_campaign\": false, \"creates_tracking\": false, \"requires_business\": true, \"creates_landing_page\": false}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#d09100\"}','{\"form_builder\": false, \"prompt_variables\": true, \"campaign_settings\": false, \"landing_page_blocks\": false}','2026-06-07 11:24:43','2026-06-07 11:24:43'),(147123493,NULL,NULL,NULL,'Coupon Follow-up Email','coupon-follow-up-email','email','retail','coupon','Follow up with customers who claimed a coupon but have not returned.','fa-light fa-envelope-circle-check',NULL,'{\"cta\": \"Use My Offer\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Your offer is still waiting\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Follow up with customers who claimed a coupon but have not returned.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Coupon Follow-up Email\", \"email_content\": {\"body\": \"Your offer is still waiting\\n\\nFollow up with customers who claimed a coupon but have not returned.\", \"subject\": \"Coupon Follow-up Email\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: coupon. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Your offer is still waiting Use My Offer\"}, \"thank_you_message\": \"Your offer has been saved.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Use My Offer\", \"headline\": \"Your offer is still waiting\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Use My Offer\", \"thank_you_message\": \"Your offer has been saved.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"coupon\", \"campaign_type\": \"coupon\", \"target_module\": \"coupon\", \"tracking_goal\": \"coupon\", \"default_status\": \"published\", \"creates_qr_code\": false, \"creates_campaign\": false, \"creates_tracking\": false, \"requires_business\": true, \"creates_landing_page\": false}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#84a900\"}','{\"form_builder\": false, \"prompt_variables\": true, \"campaign_settings\": false, \"landing_page_blocks\": false}','2026-06-07 11:24:43','2026-06-07 11:24:43'),(147123494,NULL,NULL,NULL,'Booking Confirmation WhatsApp','booking-confirmation-whatsapp','whatsapp','spa','booking','Short WhatsApp confirmation for appointment requests.','fa-brands fa-whatsapp',NULL,'{\"cta\": \"Confirm Booking\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Your booking request is received\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Short WhatsApp confirmation for appointment requests.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Booking WhatsApp\", \"email_content\": {\"body\": \"Your booking request is received\\n\\nShort WhatsApp confirmation for appointment requests.\", \"subject\": \"Booking WhatsApp\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: booking. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Your booking request is received Confirm Booking\"}, \"thank_you_message\": \"We will confirm your time soon.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Confirm Booking\", \"headline\": \"Your booking request is received\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Confirm Booking\", \"thank_you_message\": \"We will confirm your time soon.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"booking\", \"campaign_type\": \"booking\", \"target_module\": \"booking\", \"tracking_goal\": \"booking\", \"default_status\": \"published\", \"creates_qr_code\": false, \"creates_campaign\": false, \"creates_tracking\": false, \"requires_business\": true, \"creates_landing_page\": false}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#0f766e\"}','{\"form_builder\": false, \"prompt_variables\": true, \"campaign_settings\": false, \"landing_page_blocks\": false}','2026-06-07 11:24:43','2026-06-07 11:24:43'),(147123495,NULL,NULL,NULL,'Post-Visit WhatsApp Review','post-visit-whatsapp-review','whatsapp','restaurant','review','WhatsApp message that sends happy customers to a review page.','fa-brands fa-whatsapp',NULL,'{\"cta\": \"Share Feedback\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"How was your visit today?\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"WhatsApp message that sends happy customers to a review page.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"WhatsApp Review Request\", \"email_content\": {\"body\": \"How was your visit today?\\n\\nWhatsApp message that sends happy customers to a review page.\", \"subject\": \"WhatsApp Review Request\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: review. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"How was your visit today? Share Feedback\"}, \"thank_you_message\": \"Thanks for your feedback.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Share Feedback\", \"headline\": \"How was your visit today?\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Share Feedback\", \"thank_you_message\": \"Thanks for your feedback.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"review\", \"campaign_type\": \"review\", \"target_module\": \"review\", \"tracking_goal\": \"review\", \"default_status\": \"published\", \"creates_qr_code\": false, \"creates_campaign\": false, \"creates_tracking\": false, \"requires_business\": true, \"creates_landing_page\": false}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#d09100\"}','{\"form_builder\": false, \"prompt_variables\": true, \"campaign_settings\": false, \"landing_page_blocks\": false}','2026-06-07 11:24:43','2026-06-07 11:24:43'),(147123496,NULL,NULL,NULL,'Lead Nurture Automation','lead-nurture-automation','automation','general','lead','Automation template for new lead follow-up across email and WhatsApp.','fa-light fa-diagram-project',NULL,'{\"cta\": \"Start Follow-up\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"New lead follow-up sequence\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Automation template for new lead follow-up across email and WhatsApp.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Lead Nurture Automation\", \"email_content\": {\"body\": \"New lead follow-up sequence\\n\\nAutomation template for new lead follow-up across email and WhatsApp.\", \"subject\": \"Lead Nurture Automation\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: lead. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"New lead follow-up sequence Start Follow-up\"}, \"thank_you_message\": \"Lead workflow is ready.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Start Follow-up\", \"headline\": \"New lead follow-up sequence\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Start Follow-up\", \"thank_you_message\": \"Lead workflow is ready.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"lead\", \"campaign_type\": \"lead\", \"target_module\": \"lead\", \"tracking_goal\": \"lead\", \"default_status\": \"published\", \"creates_qr_code\": false, \"creates_campaign\": false, \"creates_tracking\": false, \"requires_business\": true, \"creates_landing_page\": false}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#2563eb\"}','{\"form_builder\": false, \"prompt_variables\": false, \"campaign_settings\": false, \"landing_page_blocks\": false}','2026-06-07 11:24:43','2026-06-07 11:24:43'),(147123497,NULL,NULL,NULL,'Low Rating Recovery Automation','low-rating-recovery-automation','automation','general','feedback','Automation template that opens a recovery task after low feedback.','fa-light fa-arrows-spin',NULL,'{\"cta\": \"Create Task\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"A customer needs recovery follow-up\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Automation template that opens a recovery task after low feedback.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Low Rating Recovery\", \"email_content\": {\"body\": \"A customer needs recovery follow-up\\n\\nAutomation template that opens a recovery task after low feedback.\", \"subject\": \"Low Rating Recovery\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: feedback. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"A customer needs recovery follow-up Create Task\"}, \"thank_you_message\": \"Recovery workflow is ready.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Create Task\", \"headline\": \"A customer needs recovery follow-up\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Create Task\", \"thank_you_message\": \"Recovery workflow is ready.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"feedback\", \"campaign_type\": \"feedback\", \"target_module\": \"feedback\", \"tracking_goal\": \"feedback\", \"default_status\": \"published\", \"creates_qr_code\": false, \"creates_campaign\": false, \"creates_tracking\": false, \"requires_business\": true, \"creates_landing_page\": false}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#0891b2\"}','{\"form_builder\": false, \"prompt_variables\": false, \"campaign_settings\": false, \"landing_page_blocks\": false}','2026-06-07 11:24:43','2026-06-07 11:24:43'),(147123498,NULL,NULL,NULL,'Restaurant Lunch Coupon','restaurant-lunch-coupon','form','restaurant','lead','Capture qualified Restaurant leads with a focused form.','fa-light fa-utensils',NULL,'{\"cta\": \"Send Request\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Request Lunch Coupon\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Capture qualified Restaurant leads with a focused form.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Restaurant Lunch Coupon\", \"email_content\": {\"body\": \"Request Lunch Coupon\\n\\nCapture qualified Restaurant leads with a focused form.\", \"subject\": \"Restaurant Lunch Coupon\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: lead. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Request Lunch Coupon Send Request\"}, \"thank_you_message\": \"We received your request.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Send Request\", \"headline\": \"Request Lunch Coupon\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Send Request\", \"thank_you_message\": \"We received your request.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"lead\", \"campaign_type\": \"lead\", \"target_module\": \"lead\", \"tracking_goal\": \"lead\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": false, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": false}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#2563eb\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": false, \"landing_page_blocks\": false}','2026-06-07 11:24:43','2026-06-07 11:24:43'),(147123499,NULL,NULL,NULL,'Dental Review Booster','dental-review-booster','campaign','dentist','review','Dental pack template for post-appointment review requests.','fa-light fa-tooth',NULL,'{\"cta\": \"Leave a Review\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"How was your appointment?\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Dental pack template for post-appointment review requests.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Dental Review Booster\", \"email_content\": {\"body\": \"How was your appointment?\\n\\nDental pack template for post-appointment review requests.\", \"subject\": \"Dental Review Booster\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: review. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"How was your appointment? Leave a Review\"}, \"thank_you_message\": \"Thank you for trusting our team.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Leave a Review\", \"headline\": \"How was your appointment?\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Leave a Review\", \"thank_you_message\": \"Thank you for trusting our team.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"review\", \"campaign_type\": \"review\", \"target_module\": \"review\", \"tracking_goal\": \"review\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": true, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": true}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#d09100\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": true, \"landing_page_blocks\": true}','2026-06-07 11:24:43','2026-06-07 11:24:43'),(147123500,NULL,NULL,NULL,'Gym Trial Lead Form','gym-trial-lead-form','form','gym','lead','Gym pack form for free trial or consultation leads.','fa-light fa-dumbbell',NULL,'{\"cta\": \"Request Trial\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Start your free trial\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Gym pack form for free trial or consultation leads.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Free Gym Trial\", \"email_content\": {\"body\": \"Start your free trial\\n\\nGym pack form for free trial or consultation leads.\", \"subject\": \"Free Gym Trial\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: lead. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Start your free trial Request Trial\"}, \"thank_you_message\": \"We will contact you shortly.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Request Trial\", \"headline\": \"Start your free trial\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Request Trial\", \"thank_you_message\": \"We will contact you shortly.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"lead\", \"campaign_type\": \"lead\", \"target_module\": \"lead\", \"tracking_goal\": \"lead\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": false, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": false}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#2563eb\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": false, \"landing_page_blocks\": false}','2026-06-07 11:24:43','2026-06-07 11:24:43'),(147123501,NULL,NULL,NULL,'Real Estate Consultation','real-estate-consultation','landing_page','real_estate','lead','Real estate pack page for buyer or seller consultation leads.','fa-light fa-house-building',NULL,'{\"cta\": \"Book Consultation\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Plan your next property move\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Real estate pack page for buyer or seller consultation leads.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Real Estate Consultation\", \"email_content\": {\"body\": \"Plan your next property move\\n\\nReal estate pack page for buyer or seller consultation leads.\", \"subject\": \"Real Estate Consultation\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: lead. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Plan your next property move Book Consultation\"}, \"thank_you_message\": \"We will follow up with next steps.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Book Consultation\", \"headline\": \"Plan your next property move\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Book Consultation\", \"thank_you_message\": \"We will follow up with next steps.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"lead\", \"campaign_type\": \"lead\", \"target_module\": \"lead\", \"tracking_goal\": \"lead\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": false, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": true}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#2563eb\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": false, \"landing_page_blocks\": true}','2026-06-07 11:24:43','2026-06-07 11:24:43'),(147123502,NULL,NULL,NULL,'Restaurant Table Feedback','restaurant-table-feedback','campaign','restaurant','review','Ask recent Restaurant customers for a review and route feedback privately.','fa-light fa-utensils',NULL,'{\"cta\": \"Leave a Review\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"How was your experience?\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Ask recent Restaurant customers for a review and route feedback privately.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Restaurant Table Feedback\", \"email_content\": {\"body\": \"How was your experience?\\n\\nAsk recent Restaurant customers for a review and route feedback privately.\", \"subject\": \"Restaurant Table Feedback\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: review. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"How was your experience? Leave a Review\"}, \"thank_you_message\": \"Thanks for sharing your experience.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Leave a Review\", \"headline\": \"How was your experience?\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Leave a Review\", \"thank_you_message\": \"Thanks for sharing your experience.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"review\", \"campaign_type\": \"review\", \"target_module\": \"review\", \"tracking_goal\": \"review\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": true, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": true}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#d09100\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": true, \"landing_page_blocks\": true}','2026-06-07 11:24:43','2026-06-07 11:24:43'),(147123503,NULL,NULL,NULL,'Restaurant Birthday Reward','restaurant-birthday-reward','campaign','restaurant','coupon','Promote a limited Restaurant offer with QR and claim tracking.','fa-light fa-utensils',NULL,'{\"cta\": \"Claim Offer\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Claim your limited-time offer\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Promote a limited Restaurant offer with QR and claim tracking.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Restaurant Birthday Reward\", \"email_content\": {\"body\": \"Claim your limited-time offer\\n\\nPromote a limited Restaurant offer with QR and claim tracking.\", \"subject\": \"Restaurant Birthday Reward\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: coupon. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Claim your limited-time offer Claim Offer\"}, \"thank_you_message\": \"Your offer is ready.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Claim Offer\", \"headline\": \"Claim your limited-time offer\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Claim Offer\", \"thank_you_message\": \"Your offer is ready.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"coupon\", \"campaign_type\": \"coupon\", \"target_module\": \"coupon\", \"tracking_goal\": \"coupon\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": true, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": true}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#84a900\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": true, \"landing_page_blocks\": true}','2026-06-07 11:24:43','2026-06-07 11:24:43'),(147123504,NULL,NULL,NULL,'Restaurant Reservation Boost','restaurant-reservation-boost','landing_page','restaurant','booking','Create a booking-focused landing page for Restaurant customers.','fa-light fa-utensils',NULL,'{\"cta\": \"Book Now\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Book your next visit\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Create a booking-focused landing page for Restaurant customers.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Restaurant Reservation Boost\", \"email_content\": {\"body\": \"Book your next visit\\n\\nCreate a booking-focused landing page for Restaurant customers.\", \"subject\": \"Restaurant Reservation Boost\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: booking. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Book your next visit Book Now\"}, \"thank_you_message\": \"We will confirm your request soon.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Book Now\", \"headline\": \"Book your next visit\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Book Now\", \"thank_you_message\": \"We will confirm your request soon.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"booking\", \"campaign_type\": \"booking\", \"target_module\": \"booking\", \"tracking_goal\": \"booking\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": false, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": true}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#0f766e\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": false, \"landing_page_blocks\": true}','2026-06-07 11:24:43','2026-06-07 11:24:43'),(147123505,NULL,NULL,NULL,'Restaurant Referral Dessert','restaurant-referral-dessert','email','restaurant','referral','Email template for referral and word-of-mouth campaigns.','fa-light fa-utensils',NULL,'{\"cta\": \"Send Referral\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Invite a friend to {business_name}\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Email template for referral and word-of-mouth campaigns.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Restaurant Referral Dessert\", \"email_content\": {\"body\": \"Invite a friend to {business_name}\\n\\nEmail template for referral and word-of-mouth campaigns.\", \"subject\": \"Restaurant Referral Dessert\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: referral. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Invite a friend to {business_name} Send Referral\"}, \"thank_you_message\": \"Referral message generated.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Send Referral\", \"headline\": \"Invite a friend to {business_name}\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Send Referral\", \"thank_you_message\": \"Referral message generated.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"lead\", \"campaign_type\": \"lead\", \"target_module\": \"lead\", \"tracking_goal\": \"referral\", \"default_status\": \"published\", \"creates_qr_code\": false, \"creates_campaign\": false, \"creates_tracking\": false, \"requires_business\": true, \"creates_landing_page\": false}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#2563eb\"}','{\"form_builder\": false, \"prompt_variables\": true, \"campaign_settings\": false, \"landing_page_blocks\": false}','2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123506,NULL,NULL,NULL,'Spa Massage Booking','spa-massage-booking','form','spa','lead','Capture qualified Spa leads with a focused form.','fa-light fa-spa',NULL,'{\"cta\": \"Send Request\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Request Massage Booking\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Capture qualified Spa leads with a focused form.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Spa Massage Booking\", \"email_content\": {\"body\": \"Request Massage Booking\\n\\nCapture qualified Spa leads with a focused form.\", \"subject\": \"Spa Massage Booking\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: lead. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Request Massage Booking Send Request\"}, \"thank_you_message\": \"We received your request.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Send Request\", \"headline\": \"Request Massage Booking\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Send Request\", \"thank_you_message\": \"We received your request.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"lead\", \"campaign_type\": \"lead\", \"target_module\": \"lead\", \"tracking_goal\": \"lead\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": false, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": false}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#2563eb\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": false, \"landing_page_blocks\": false}','2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123507,NULL,NULL,NULL,'Spa Post-Service Review','spa-post-service-review','campaign','spa','review','Ask recent Spa customers for a review and route feedback privately.','fa-light fa-spa',NULL,'{\"cta\": \"Leave a Review\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"How was your experience?\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Ask recent Spa customers for a review and route feedback privately.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Spa Post-Service Review\", \"email_content\": {\"body\": \"How was your experience?\\n\\nAsk recent Spa customers for a review and route feedback privately.\", \"subject\": \"Spa Post-Service Review\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: review. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"How was your experience? Leave a Review\"}, \"thank_you_message\": \"Thanks for sharing your experience.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Leave a Review\", \"headline\": \"How was your experience?\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Leave a Review\", \"thank_you_message\": \"Thanks for sharing your experience.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"review\", \"campaign_type\": \"review\", \"target_module\": \"review\", \"tracking_goal\": \"review\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": true, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": true}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#d09100\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": true, \"landing_page_blocks\": true}','2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123508,NULL,NULL,NULL,'Spa Weekend Coupon','spa-weekend-coupon','campaign','spa','coupon','Promote a limited Spa offer with QR and claim tracking.','fa-light fa-spa',NULL,'{\"cta\": \"Claim Offer\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Claim your limited-time offer\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Promote a limited Spa offer with QR and claim tracking.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Spa Weekend Coupon\", \"email_content\": {\"body\": \"Claim your limited-time offer\\n\\nPromote a limited Spa offer with QR and claim tracking.\", \"subject\": \"Spa Weekend Coupon\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: coupon. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Claim your limited-time offer Claim Offer\"}, \"thank_you_message\": \"Your offer is ready.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Claim Offer\", \"headline\": \"Claim your limited-time offer\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Claim Offer\", \"thank_you_message\": \"Your offer is ready.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"coupon\", \"campaign_type\": \"coupon\", \"target_module\": \"coupon\", \"tracking_goal\": \"coupon\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": true, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": true}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#84a900\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": true, \"landing_page_blocks\": true}','2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123509,NULL,NULL,NULL,'Spa Relaxation Lead Form','spa-relaxation-lead-form','landing_page','spa','booking','Create a booking-focused landing page for Spa customers.','fa-light fa-spa',NULL,'{\"cta\": \"Book Now\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Book your next visit\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Create a booking-focused landing page for Spa customers.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Spa Relaxation Lead Form\", \"email_content\": {\"body\": \"Book your next visit\\n\\nCreate a booking-focused landing page for Spa customers.\", \"subject\": \"Spa Relaxation Lead Form\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: booking. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Book your next visit Book Now\"}, \"thank_you_message\": \"We will confirm your request soon.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Book Now\", \"headline\": \"Book your next visit\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Book Now\", \"thank_you_message\": \"We will confirm your request soon.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"booking\", \"campaign_type\": \"booking\", \"target_module\": \"booking\", \"tracking_goal\": \"booking\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": false, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": true}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#0f766e\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": false, \"landing_page_blocks\": true}','2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123510,NULL,NULL,NULL,'Spa Come Back Offer','spa-come-back-offer','email','spa','referral','Email template for referral and word-of-mouth campaigns.','fa-light fa-spa',NULL,'{\"cta\": \"Send Referral\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Invite a friend to {business_name}\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Email template for referral and word-of-mouth campaigns.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Spa Come Back Offer\", \"email_content\": {\"body\": \"Invite a friend to {business_name}\\n\\nEmail template for referral and word-of-mouth campaigns.\", \"subject\": \"Spa Come Back Offer\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: referral. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Invite a friend to {business_name} Send Referral\"}, \"thank_you_message\": \"Referral message generated.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Send Referral\", \"headline\": \"Invite a friend to {business_name}\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Send Referral\", \"thank_you_message\": \"Referral message generated.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"lead\", \"campaign_type\": \"lead\", \"target_module\": \"lead\", \"tracking_goal\": \"referral\", \"default_status\": \"published\", \"creates_qr_code\": false, \"creates_campaign\": false, \"creates_tracking\": false, \"requires_business\": true, \"creates_landing_page\": false}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#2563eb\"}','{\"form_builder\": false, \"prompt_variables\": true, \"campaign_settings\": false, \"landing_page_blocks\": false}','2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123511,NULL,NULL,NULL,'Salon Color Appointment','salon-color-appointment','form','salon','lead','Capture qualified Salon leads with a focused form.','fa-light fa-scissors',NULL,'{\"cta\": \"Send Request\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Request Color Appointment\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Capture qualified Salon leads with a focused form.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Salon Color Appointment\", \"email_content\": {\"body\": \"Request Color Appointment\\n\\nCapture qualified Salon leads with a focused form.\", \"subject\": \"Salon Color Appointment\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: lead. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Request Color Appointment Send Request\"}, \"thank_you_message\": \"We received your request.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Send Request\", \"headline\": \"Request Color Appointment\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Send Request\", \"thank_you_message\": \"We received your request.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"lead\", \"campaign_type\": \"lead\", \"target_module\": \"lead\", \"tracking_goal\": \"lead\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": false, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": false}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#2563eb\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": false, \"landing_page_blocks\": false}','2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123512,NULL,NULL,NULL,'Salon Stylist Review','salon-stylist-review','campaign','salon','review','Ask recent Salon customers for a review and route feedback privately.','fa-light fa-scissors',NULL,'{\"cta\": \"Leave a Review\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"How was your experience?\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Ask recent Salon customers for a review and route feedback privately.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Salon Stylist Review\", \"email_content\": {\"body\": \"How was your experience?\\n\\nAsk recent Salon customers for a review and route feedback privately.\", \"subject\": \"Salon Stylist Review\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: review. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"How was your experience? Leave a Review\"}, \"thank_you_message\": \"Thanks for sharing your experience.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Leave a Review\", \"headline\": \"How was your experience?\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Leave a Review\", \"thank_you_message\": \"Thanks for sharing your experience.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"review\", \"campaign_type\": \"review\", \"target_module\": \"review\", \"tracking_goal\": \"review\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": true, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": true}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#d09100\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": true, \"landing_page_blocks\": true}','2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123513,NULL,NULL,NULL,'Salon First Visit Coupon','salon-first-visit-coupon','campaign','salon','coupon','Promote a limited Salon offer with QR and claim tracking.','fa-light fa-scissors',NULL,'{\"cta\": \"Claim Offer\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Claim your limited-time offer\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Promote a limited Salon offer with QR and claim tracking.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Salon First Visit Coupon\", \"email_content\": {\"body\": \"Claim your limited-time offer\\n\\nPromote a limited Salon offer with QR and claim tracking.\", \"subject\": \"Salon First Visit Coupon\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: coupon. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Claim your limited-time offer Claim Offer\"}, \"thank_you_message\": \"Your offer is ready.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Claim Offer\", \"headline\": \"Claim your limited-time offer\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Claim Offer\", \"thank_you_message\": \"Your offer is ready.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"coupon\", \"campaign_type\": \"coupon\", \"target_module\": \"coupon\", \"tracking_goal\": \"coupon\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": true, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": true}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#84a900\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": true, \"landing_page_blocks\": true}','2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123514,NULL,NULL,NULL,'Salon Service Feedback','salon-service-feedback','landing_page','salon','booking','Create a booking-focused landing page for Salon customers.','fa-light fa-scissors',NULL,'{\"cta\": \"Book Now\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Book your next visit\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Create a booking-focused landing page for Salon customers.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Salon Service Feedback\", \"email_content\": {\"body\": \"Book your next visit\\n\\nCreate a booking-focused landing page for Salon customers.\", \"subject\": \"Salon Service Feedback\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: booking. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Book your next visit Book Now\"}, \"thank_you_message\": \"We will confirm your request soon.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Book Now\", \"headline\": \"Book your next visit\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Book Now\", \"thank_you_message\": \"We will confirm your request soon.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"booking\", \"campaign_type\": \"booking\", \"target_module\": \"booking\", \"tracking_goal\": \"booking\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": false, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": true}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#0f766e\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": false, \"landing_page_blocks\": true}','2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123515,NULL,NULL,NULL,'Salon Referral Blowout','salon-referral-blowout','email','salon','referral','Email template for referral and word-of-mouth campaigns.','fa-light fa-scissors',NULL,'{\"cta\": \"Send Referral\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Invite a friend to {business_name}\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Email template for referral and word-of-mouth campaigns.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Salon Referral Blowout\", \"email_content\": {\"body\": \"Invite a friend to {business_name}\\n\\nEmail template for referral and word-of-mouth campaigns.\", \"subject\": \"Salon Referral Blowout\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: referral. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Invite a friend to {business_name} Send Referral\"}, \"thank_you_message\": \"Referral message generated.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Send Referral\", \"headline\": \"Invite a friend to {business_name}\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Send Referral\", \"thank_you_message\": \"Referral message generated.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"lead\", \"campaign_type\": \"lead\", \"target_module\": \"lead\", \"tracking_goal\": \"referral\", \"default_status\": \"published\", \"creates_qr_code\": false, \"creates_campaign\": false, \"creates_tracking\": false, \"requires_business\": true, \"creates_landing_page\": false}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#2563eb\"}','{\"form_builder\": false, \"prompt_variables\": true, \"campaign_settings\": false, \"landing_page_blocks\": false}','2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123516,NULL,NULL,NULL,'Clinic Consultation Lead','clinic-consultation-lead','form','clinic','lead','Capture qualified Clinic leads with a focused form.','fa-light fa-user-doctor',NULL,'{\"cta\": \"Send Request\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Request Consultation Lead\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Capture qualified Clinic leads with a focused form.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Clinic Consultation Lead\", \"email_content\": {\"body\": \"Request Consultation Lead\\n\\nCapture qualified Clinic leads with a focused form.\", \"subject\": \"Clinic Consultation Lead\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: lead. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Request Consultation Lead Send Request\"}, \"thank_you_message\": \"We received your request.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Send Request\", \"headline\": \"Request Consultation Lead\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Send Request\", \"thank_you_message\": \"We received your request.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"lead\", \"campaign_type\": \"lead\", \"target_module\": \"lead\", \"tracking_goal\": \"lead\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": false, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": false}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#2563eb\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": false, \"landing_page_blocks\": false}','2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123517,NULL,NULL,NULL,'Clinic Appointment Reminder','clinic-appointment-reminder','campaign','clinic','review','Ask recent Clinic customers for a review and route feedback privately.','fa-light fa-user-doctor',NULL,'{\"cta\": \"Leave a Review\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"How was your experience?\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Ask recent Clinic customers for a review and route feedback privately.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Clinic Appointment Reminder\", \"email_content\": {\"body\": \"How was your experience?\\n\\nAsk recent Clinic customers for a review and route feedback privately.\", \"subject\": \"Clinic Appointment Reminder\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: review. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"How was your experience? Leave a Review\"}, \"thank_you_message\": \"Thanks for sharing your experience.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Leave a Review\", \"headline\": \"How was your experience?\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Leave a Review\", \"thank_you_message\": \"Thanks for sharing your experience.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"review\", \"campaign_type\": \"review\", \"target_module\": \"review\", \"tracking_goal\": \"review\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": true, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": true}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#d09100\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": true, \"landing_page_blocks\": true}','2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123518,NULL,NULL,NULL,'Clinic Patient Feedback','clinic-patient-feedback','campaign','clinic','coupon','Promote a limited Clinic offer with QR and claim tracking.','fa-light fa-user-doctor',NULL,'{\"cta\": \"Claim Offer\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Claim your limited-time offer\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Promote a limited Clinic offer with QR and claim tracking.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Clinic Patient Feedback\", \"email_content\": {\"body\": \"Claim your limited-time offer\\n\\nPromote a limited Clinic offer with QR and claim tracking.\", \"subject\": \"Clinic Patient Feedback\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: coupon. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Claim your limited-time offer Claim Offer\"}, \"thank_you_message\": \"Your offer is ready.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Claim Offer\", \"headline\": \"Claim your limited-time offer\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Claim Offer\", \"thank_you_message\": \"Your offer is ready.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"coupon\", \"campaign_type\": \"coupon\", \"target_module\": \"coupon\", \"tracking_goal\": \"coupon\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": true, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": true}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#84a900\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": true, \"landing_page_blocks\": true}','2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123519,NULL,NULL,NULL,'Clinic Review Booster','clinic-review-booster','landing_page','clinic','booking','Create a booking-focused landing page for Clinic customers.','fa-light fa-user-doctor',NULL,'{\"cta\": \"Book Now\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Book your next visit\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Create a booking-focused landing page for Clinic customers.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Clinic Review Booster\", \"email_content\": {\"body\": \"Book your next visit\\n\\nCreate a booking-focused landing page for Clinic customers.\", \"subject\": \"Clinic Review Booster\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: booking. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Book your next visit Book Now\"}, \"thank_you_message\": \"We will confirm your request soon.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Book Now\", \"headline\": \"Book your next visit\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Book Now\", \"thank_you_message\": \"We will confirm your request soon.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"booking\", \"campaign_type\": \"booking\", \"target_module\": \"booking\", \"tracking_goal\": \"booking\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": false, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": true}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#0f766e\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": false, \"landing_page_blocks\": true}','2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123520,NULL,NULL,NULL,'Clinic Follow-up Automation','clinic-follow-up-automation','email','clinic','referral','Email template for referral and word-of-mouth campaigns.','fa-light fa-user-doctor',NULL,'{\"cta\": \"Send Referral\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Invite a friend to {business_name}\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Email template for referral and word-of-mouth campaigns.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Clinic Follow-up Automation\", \"email_content\": {\"body\": \"Invite a friend to {business_name}\\n\\nEmail template for referral and word-of-mouth campaigns.\", \"subject\": \"Clinic Follow-up Automation\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: referral. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Invite a friend to {business_name} Send Referral\"}, \"thank_you_message\": \"Referral message generated.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Send Referral\", \"headline\": \"Invite a friend to {business_name}\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Send Referral\", \"thank_you_message\": \"Referral message generated.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"lead\", \"campaign_type\": \"lead\", \"target_module\": \"lead\", \"tracking_goal\": \"referral\", \"default_status\": \"published\", \"creates_qr_code\": false, \"creates_campaign\": false, \"creates_tracking\": false, \"requires_business\": true, \"creates_landing_page\": false}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#2563eb\"}','{\"form_builder\": false, \"prompt_variables\": true, \"campaign_settings\": false, \"landing_page_blocks\": false}','2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123521,NULL,NULL,NULL,'Dental Cleaning Reminder','dentist-cleaning-reminder','form','dentist','lead','Capture qualified Dental leads with a focused form.','fa-light fa-tooth',NULL,'{\"cta\": \"Send Request\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Request Cleaning Reminder\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Capture qualified Dental leads with a focused form.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Dental Cleaning Reminder\", \"email_content\": {\"body\": \"Request Cleaning Reminder\\n\\nCapture qualified Dental leads with a focused form.\", \"subject\": \"Dental Cleaning Reminder\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: lead. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Request Cleaning Reminder Send Request\"}, \"thank_you_message\": \"We received your request.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Send Request\", \"headline\": \"Request Cleaning Reminder\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Send Request\", \"thank_you_message\": \"We received your request.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"lead\", \"campaign_type\": \"lead\", \"target_module\": \"lead\", \"tracking_goal\": \"lead\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": false, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": false}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#2563eb\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": false, \"landing_page_blocks\": false}','2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123522,NULL,NULL,NULL,'Dental Patient Review','dentist-patient-review','campaign','dentist','review','Ask recent Dental customers for a review and route feedback privately.','fa-light fa-tooth',NULL,'{\"cta\": \"Leave a Review\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"How was your experience?\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Ask recent Dental customers for a review and route feedback privately.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Dental Patient Review\", \"email_content\": {\"body\": \"How was your experience?\\n\\nAsk recent Dental customers for a review and route feedback privately.\", \"subject\": \"Dental Patient Review\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: review. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"How was your experience? Leave a Review\"}, \"thank_you_message\": \"Thanks for sharing your experience.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Leave a Review\", \"headline\": \"How was your experience?\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Leave a Review\", \"thank_you_message\": \"Thanks for sharing your experience.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"review\", \"campaign_type\": \"review\", \"target_module\": \"review\", \"tracking_goal\": \"review\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": true, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": true}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#d09100\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": true, \"landing_page_blocks\": true}','2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123523,NULL,NULL,NULL,'Dental Whitening Coupon','dentist-whitening-coupon','campaign','dentist','coupon','Promote a limited Dental offer with QR and claim tracking.','fa-light fa-tooth',NULL,'{\"cta\": \"Claim Offer\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Claim your limited-time offer\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Promote a limited Dental offer with QR and claim tracking.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Dental Whitening Coupon\", \"email_content\": {\"body\": \"Claim your limited-time offer\\n\\nPromote a limited Dental offer with QR and claim tracking.\", \"subject\": \"Dental Whitening Coupon\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: coupon. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Claim your limited-time offer Claim Offer\"}, \"thank_you_message\": \"Your offer is ready.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Claim Offer\", \"headline\": \"Claim your limited-time offer\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Claim Offer\", \"thank_you_message\": \"Your offer is ready.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"coupon\", \"campaign_type\": \"coupon\", \"target_module\": \"coupon\", \"tracking_goal\": \"coupon\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": true, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": true}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#84a900\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": true, \"landing_page_blocks\": true}','2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123524,NULL,NULL,NULL,'Dental Referral Campaign','dentist-referral-campaign','landing_page','dentist','booking','Create a booking-focused landing page for Dental customers.','fa-light fa-tooth',NULL,'{\"cta\": \"Book Now\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Book your next visit\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Create a booking-focused landing page for Dental customers.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Dental Referral Campaign\", \"email_content\": {\"body\": \"Book your next visit\\n\\nCreate a booking-focused landing page for Dental customers.\", \"subject\": \"Dental Referral Campaign\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: booking. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Book your next visit Book Now\"}, \"thank_you_message\": \"We will confirm your request soon.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Book Now\", \"headline\": \"Book your next visit\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Book Now\", \"thank_you_message\": \"We will confirm your request soon.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"booking\", \"campaign_type\": \"booking\", \"target_module\": \"booking\", \"tracking_goal\": \"booking\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": false, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": true}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#0f766e\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": false, \"landing_page_blocks\": true}','2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123525,NULL,NULL,NULL,'Dental Post-Visit Feedback','dentist-post-visit-feedback','email','dentist','referral','Email template for referral and word-of-mouth campaigns.','fa-light fa-tooth',NULL,'{\"cta\": \"Send Referral\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Invite a friend to {business_name}\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Email template for referral and word-of-mouth campaigns.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Dental Post-Visit Feedback\", \"email_content\": {\"body\": \"Invite a friend to {business_name}\\n\\nEmail template for referral and word-of-mouth campaigns.\", \"subject\": \"Dental Post-Visit Feedback\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: referral. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Invite a friend to {business_name} Send Referral\"}, \"thank_you_message\": \"Referral message generated.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Send Referral\", \"headline\": \"Invite a friend to {business_name}\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Send Referral\", \"thank_you_message\": \"Referral message generated.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"lead\", \"campaign_type\": \"lead\", \"target_module\": \"lead\", \"tracking_goal\": \"referral\", \"default_status\": \"published\", \"creates_qr_code\": false, \"creates_campaign\": false, \"creates_tracking\": false, \"requires_business\": true, \"creates_landing_page\": false}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#2563eb\"}','{\"form_builder\": false, \"prompt_variables\": true, \"campaign_settings\": false, \"landing_page_blocks\": false}','2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123526,NULL,NULL,NULL,'Gym Trial Pass Lead','gym-trial-pass-lead','form','gym','lead','Capture qualified Gym leads with a focused form.','fa-light fa-dumbbell',NULL,'{\"cta\": \"Send Request\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Request Trial Pass Lead\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Capture qualified Gym leads with a focused form.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Gym Trial Pass Lead\", \"email_content\": {\"body\": \"Request Trial Pass Lead\\n\\nCapture qualified Gym leads with a focused form.\", \"subject\": \"Gym Trial Pass Lead\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: lead. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Request Trial Pass Lead Send Request\"}, \"thank_you_message\": \"We received your request.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Send Request\", \"headline\": \"Request Trial Pass Lead\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Send Request\", \"thank_you_message\": \"We received your request.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"lead\", \"campaign_type\": \"lead\", \"target_module\": \"lead\", \"tracking_goal\": \"lead\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": false, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": false}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#2563eb\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": false, \"landing_page_blocks\": false}','2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123527,NULL,NULL,NULL,'Gym Member Review','gym-member-review','campaign','gym','review','Ask recent Gym customers for a review and route feedback privately.','fa-light fa-dumbbell',NULL,'{\"cta\": \"Leave a Review\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"How was your experience?\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Ask recent Gym customers for a review and route feedback privately.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Gym Member Review\", \"email_content\": {\"body\": \"How was your experience?\\n\\nAsk recent Gym customers for a review and route feedback privately.\", \"subject\": \"Gym Member Review\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: review. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"How was your experience? Leave a Review\"}, \"thank_you_message\": \"Thanks for sharing your experience.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Leave a Review\", \"headline\": \"How was your experience?\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Leave a Review\", \"thank_you_message\": \"Thanks for sharing your experience.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"review\", \"campaign_type\": \"review\", \"target_module\": \"review\", \"tracking_goal\": \"review\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": true, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": true}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#d09100\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": true, \"landing_page_blocks\": true}','2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123528,NULL,NULL,NULL,'Gym Class Booking','gym-class-booking','campaign','gym','coupon','Promote a limited Gym offer with QR and claim tracking.','fa-light fa-dumbbell',NULL,'{\"cta\": \"Claim Offer\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Claim your limited-time offer\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Promote a limited Gym offer with QR and claim tracking.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Gym Class Booking\", \"email_content\": {\"body\": \"Claim your limited-time offer\\n\\nPromote a limited Gym offer with QR and claim tracking.\", \"subject\": \"Gym Class Booking\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: coupon. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Claim your limited-time offer Claim Offer\"}, \"thank_you_message\": \"Your offer is ready.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Claim Offer\", \"headline\": \"Claim your limited-time offer\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Claim Offer\", \"thank_you_message\": \"Your offer is ready.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"coupon\", \"campaign_type\": \"coupon\", \"target_module\": \"coupon\", \"tracking_goal\": \"coupon\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": true, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": true}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#84a900\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": true, \"landing_page_blocks\": true}','2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123529,NULL,NULL,NULL,'Gym Come Back Offer','gym-come-back-offer','landing_page','gym','booking','Create a booking-focused landing page for Gym customers.','fa-light fa-dumbbell',NULL,'{\"cta\": \"Book Now\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Book your next visit\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Create a booking-focused landing page for Gym customers.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Gym Come Back Offer\", \"email_content\": {\"body\": \"Book your next visit\\n\\nCreate a booking-focused landing page for Gym customers.\", \"subject\": \"Gym Come Back Offer\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: booking. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Book your next visit Book Now\"}, \"thank_you_message\": \"We will confirm your request soon.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Book Now\", \"headline\": \"Book your next visit\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Book Now\", \"thank_you_message\": \"We will confirm your request soon.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"booking\", \"campaign_type\": \"booking\", \"target_module\": \"booking\", \"tracking_goal\": \"booking\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": false, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": true}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#0f766e\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": false, \"landing_page_blocks\": true}','2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123530,NULL,NULL,NULL,'Gym Referral Challenge','gym-referral-challenge','email','gym','referral','Email template for referral and word-of-mouth campaigns.','fa-light fa-dumbbell',NULL,'{\"cta\": \"Send Referral\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Invite a friend to {business_name}\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Email template for referral and word-of-mouth campaigns.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Gym Referral Challenge\", \"email_content\": {\"body\": \"Invite a friend to {business_name}\\n\\nEmail template for referral and word-of-mouth campaigns.\", \"subject\": \"Gym Referral Challenge\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: referral. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Invite a friend to {business_name} Send Referral\"}, \"thank_you_message\": \"Referral message generated.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Send Referral\", \"headline\": \"Invite a friend to {business_name}\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Send Referral\", \"thank_you_message\": \"Referral message generated.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"lead\", \"campaign_type\": \"lead\", \"target_module\": \"lead\", \"tracking_goal\": \"referral\", \"default_status\": \"published\", \"creates_qr_code\": false, \"creates_campaign\": false, \"creates_tracking\": false, \"requires_business\": true, \"creates_landing_page\": false}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#2563eb\"}','{\"form_builder\": false, \"prompt_variables\": true, \"campaign_settings\": false, \"landing_page_blocks\": false}','2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123531,NULL,NULL,NULL,'Retail Weekend Sale','retail-weekend-sale','form','retail','lead','Capture qualified Retail leads with a focused form.','fa-light fa-bags-shopping',NULL,'{\"cta\": \"Send Request\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Request Weekend Sale\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Capture qualified Retail leads with a focused form.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Retail Weekend Sale\", \"email_content\": {\"body\": \"Request Weekend Sale\\n\\nCapture qualified Retail leads with a focused form.\", \"subject\": \"Retail Weekend Sale\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: lead. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Request Weekend Sale Send Request\"}, \"thank_you_message\": \"We received your request.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Send Request\", \"headline\": \"Request Weekend Sale\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Send Request\", \"thank_you_message\": \"We received your request.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"lead\", \"campaign_type\": \"lead\", \"target_module\": \"lead\", \"tracking_goal\": \"lead\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": false, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": false}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#2563eb\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": false, \"landing_page_blocks\": false}','2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123532,NULL,NULL,NULL,'Retail Loyalty Coupon','retail-loyalty-coupon','campaign','retail','review','Ask recent Retail customers for a review and route feedback privately.','fa-light fa-bags-shopping',NULL,'{\"cta\": \"Leave a Review\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"How was your experience?\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Ask recent Retail customers for a review and route feedback privately.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Retail Loyalty Coupon\", \"email_content\": {\"body\": \"How was your experience?\\n\\nAsk recent Retail customers for a review and route feedback privately.\", \"subject\": \"Retail Loyalty Coupon\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: review. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"How was your experience? Leave a Review\"}, \"thank_you_message\": \"Thanks for sharing your experience.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Leave a Review\", \"headline\": \"How was your experience?\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Leave a Review\", \"thank_you_message\": \"Thanks for sharing your experience.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"review\", \"campaign_type\": \"review\", \"target_module\": \"review\", \"tracking_goal\": \"review\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": true, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": true}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#d09100\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": true, \"landing_page_blocks\": true}','2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123533,NULL,NULL,NULL,'Retail Product Feedback','retail-product-feedback','campaign','retail','coupon','Promote a limited Retail offer with QR and claim tracking.','fa-light fa-bags-shopping',NULL,'{\"cta\": \"Claim Offer\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Claim your limited-time offer\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Promote a limited Retail offer with QR and claim tracking.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Retail Product Feedback\", \"email_content\": {\"body\": \"Claim your limited-time offer\\n\\nPromote a limited Retail offer with QR and claim tracking.\", \"subject\": \"Retail Product Feedback\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: coupon. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Claim your limited-time offer Claim Offer\"}, \"thank_you_message\": \"Your offer is ready.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Claim Offer\", \"headline\": \"Claim your limited-time offer\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Claim Offer\", \"thank_you_message\": \"Your offer is ready.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"coupon\", \"campaign_type\": \"coupon\", \"target_module\": \"coupon\", \"tracking_goal\": \"coupon\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": true, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": true}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#84a900\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": true, \"landing_page_blocks\": true}','2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123534,NULL,NULL,NULL,'Retail Birthday Reward','retail-birthday-reward','landing_page','retail','booking','Create a booking-focused landing page for Retail customers.','fa-light fa-bags-shopping',NULL,'{\"cta\": \"Book Now\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Book your next visit\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Create a booking-focused landing page for Retail customers.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Retail Birthday Reward\", \"email_content\": {\"body\": \"Book your next visit\\n\\nCreate a booking-focused landing page for Retail customers.\", \"subject\": \"Retail Birthday Reward\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: booking. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Book your next visit Book Now\"}, \"thank_you_message\": \"We will confirm your request soon.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Book Now\", \"headline\": \"Book your next visit\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Book Now\", \"thank_you_message\": \"We will confirm your request soon.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"booking\", \"campaign_type\": \"booking\", \"target_module\": \"booking\", \"tracking_goal\": \"booking\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": false, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": true}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#0f766e\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": false, \"landing_page_blocks\": true}','2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123535,NULL,NULL,NULL,'Retail Referral Offer','retail-referral-offer','email','retail','referral','Email template for referral and word-of-mouth campaigns.','fa-light fa-bags-shopping',NULL,'{\"cta\": \"Send Referral\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Invite a friend to {business_name}\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Email template for referral and word-of-mouth campaigns.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Retail Referral Offer\", \"email_content\": {\"body\": \"Invite a friend to {business_name}\\n\\nEmail template for referral and word-of-mouth campaigns.\", \"subject\": \"Retail Referral Offer\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: referral. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Invite a friend to {business_name} Send Referral\"}, \"thank_you_message\": \"Referral message generated.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Send Referral\", \"headline\": \"Invite a friend to {business_name}\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Send Referral\", \"thank_you_message\": \"Referral message generated.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"lead\", \"campaign_type\": \"lead\", \"target_module\": \"lead\", \"tracking_goal\": \"referral\", \"default_status\": \"published\", \"creates_qr_code\": false, \"creates_campaign\": false, \"creates_tracking\": false, \"requires_business\": true, \"creates_landing_page\": false}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#2563eb\"}','{\"form_builder\": false, \"prompt_variables\": true, \"campaign_settings\": false, \"landing_page_blocks\": false}','2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123536,NULL,NULL,NULL,'Agency Free Audit Lead','agency-free-audit-lead','form','agency','lead','Capture qualified Agency leads with a focused form.','fa-light fa-bullhorn',NULL,'{\"cta\": \"Send Request\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Request Free Audit Lead\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Capture qualified Agency leads with a focused form.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Agency Free Audit Lead\", \"email_content\": {\"body\": \"Request Free Audit Lead\\n\\nCapture qualified Agency leads with a focused form.\", \"subject\": \"Agency Free Audit Lead\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: lead. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Request Free Audit Lead Send Request\"}, \"thank_you_message\": \"We received your request.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Send Request\", \"headline\": \"Request Free Audit Lead\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Send Request\", \"thank_you_message\": \"We received your request.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"lead\", \"campaign_type\": \"lead\", \"target_module\": \"lead\", \"tracking_goal\": \"lead\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": false, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": false}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#2563eb\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": false, \"landing_page_blocks\": false}','2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123537,NULL,NULL,NULL,'Agency Client Review','agency-client-review','campaign','agency','review','Ask recent Agency customers for a review and route feedback privately.','fa-light fa-bullhorn',NULL,'{\"cta\": \"Leave a Review\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"How was your experience?\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Ask recent Agency customers for a review and route feedback privately.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Agency Client Review\", \"email_content\": {\"body\": \"How was your experience?\\n\\nAsk recent Agency customers for a review and route feedback privately.\", \"subject\": \"Agency Client Review\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: review. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"How was your experience? Leave a Review\"}, \"thank_you_message\": \"Thanks for sharing your experience.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Leave a Review\", \"headline\": \"How was your experience?\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Leave a Review\", \"thank_you_message\": \"Thanks for sharing your experience.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"review\", \"campaign_type\": \"review\", \"target_module\": \"review\", \"tracking_goal\": \"review\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": true, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": true}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#d09100\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": true, \"landing_page_blocks\": true}','2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123538,NULL,NULL,NULL,'Agency Consultation Booking','agency-consultation-booking','campaign','agency','coupon','Promote a limited Agency offer with QR and claim tracking.','fa-light fa-bullhorn',NULL,'{\"cta\": \"Claim Offer\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Claim your limited-time offer\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Promote a limited Agency offer with QR and claim tracking.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Agency Consultation Booking\", \"email_content\": {\"body\": \"Claim your limited-time offer\\n\\nPromote a limited Agency offer with QR and claim tracking.\", \"subject\": \"Agency Consultation Booking\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: coupon. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Claim your limited-time offer Claim Offer\"}, \"thank_you_message\": \"Your offer is ready.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Claim Offer\", \"headline\": \"Claim your limited-time offer\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Claim Offer\", \"thank_you_message\": \"Your offer is ready.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"coupon\", \"campaign_type\": \"coupon\", \"target_module\": \"coupon\", \"tracking_goal\": \"coupon\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": true, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": true}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#84a900\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": true, \"landing_page_blocks\": true}','2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123539,NULL,NULL,NULL,'Agency Proposal Follow-up','agency-proposal-follow-up','landing_page','agency','booking','Create a booking-focused landing page for Agency customers.','fa-light fa-bullhorn',NULL,'{\"cta\": \"Book Now\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Book your next visit\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Create a booking-focused landing page for Agency customers.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Agency Proposal Follow-up\", \"email_content\": {\"body\": \"Book your next visit\\n\\nCreate a booking-focused landing page for Agency customers.\", \"subject\": \"Agency Proposal Follow-up\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: booking. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Book your next visit Book Now\"}, \"thank_you_message\": \"We will confirm your request soon.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Book Now\", \"headline\": \"Book your next visit\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Book Now\", \"thank_you_message\": \"We will confirm your request soon.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"booking\", \"campaign_type\": \"booking\", \"target_module\": \"booking\", \"tracking_goal\": \"booking\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": false, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": true}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#0f766e\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": false, \"landing_page_blocks\": true}','2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123540,NULL,NULL,NULL,'Agency Referral Intro','agency-referral-intro','email','agency','referral','Email template for referral and word-of-mouth campaigns.','fa-light fa-bullhorn',NULL,'{\"cta\": \"Send Referral\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Invite a friend to {business_name}\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Email template for referral and word-of-mouth campaigns.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Agency Referral Intro\", \"email_content\": {\"body\": \"Invite a friend to {business_name}\\n\\nEmail template for referral and word-of-mouth campaigns.\", \"subject\": \"Agency Referral Intro\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: referral. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Invite a friend to {business_name} Send Referral\"}, \"thank_you_message\": \"Referral message generated.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Send Referral\", \"headline\": \"Invite a friend to {business_name}\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Send Referral\", \"thank_you_message\": \"Referral message generated.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"lead\", \"campaign_type\": \"lead\", \"target_module\": \"lead\", \"tracking_goal\": \"referral\", \"default_status\": \"published\", \"creates_qr_code\": false, \"creates_campaign\": false, \"creates_tracking\": false, \"requires_business\": true, \"creates_landing_page\": false}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#2563eb\"}','{\"form_builder\": false, \"prompt_variables\": true, \"campaign_settings\": false, \"landing_page_blocks\": false}','2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123541,NULL,NULL,NULL,'Real Estate Seller Consultation','real_estate-seller-consultation','form','real_estate','lead','Capture qualified Real Estate leads with a focused form.','fa-light fa-house-building',NULL,'{\"cta\": \"Send Request\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Request Seller Consultation\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Capture qualified Real Estate leads with a focused form.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Real Estate Seller Consultation\", \"email_content\": {\"body\": \"Request Seller Consultation\\n\\nCapture qualified Real Estate leads with a focused form.\", \"subject\": \"Real Estate Seller Consultation\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: lead. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Request Seller Consultation Send Request\"}, \"thank_you_message\": \"We received your request.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Send Request\", \"headline\": \"Request Seller Consultation\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Send Request\", \"thank_you_message\": \"We received your request.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"lead\", \"campaign_type\": \"lead\", \"target_module\": \"lead\", \"tracking_goal\": \"lead\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": false, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": false}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#2563eb\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": false, \"landing_page_blocks\": false}','2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123542,NULL,NULL,NULL,'Real Estate Buyer Lead Form','real_estate-buyer-lead-form','campaign','real_estate','review','Ask recent Real Estate customers for a review and route feedback privately.','fa-light fa-house-building',NULL,'{\"cta\": \"Leave a Review\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"How was your experience?\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Ask recent Real Estate customers for a review and route feedback privately.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Real Estate Buyer Lead Form\", \"email_content\": {\"body\": \"How was your experience?\\n\\nAsk recent Real Estate customers for a review and route feedback privately.\", \"subject\": \"Real Estate Buyer Lead Form\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: review. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"How was your experience? Leave a Review\"}, \"thank_you_message\": \"Thanks for sharing your experience.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Leave a Review\", \"headline\": \"How was your experience?\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Leave a Review\", \"thank_you_message\": \"Thanks for sharing your experience.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"review\", \"campaign_type\": \"review\", \"target_module\": \"review\", \"tracking_goal\": \"review\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": true, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": true}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#d09100\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": true, \"landing_page_blocks\": true}','2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123543,NULL,NULL,NULL,'Real Estate Open House Booking','real_estate-open-house-booking','campaign','real_estate','coupon','Promote a limited Real Estate offer with QR and claim tracking.','fa-light fa-house-building',NULL,'{\"cta\": \"Claim Offer\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Claim your limited-time offer\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Promote a limited Real Estate offer with QR and claim tracking.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Real Estate Open House Booking\", \"email_content\": {\"body\": \"Claim your limited-time offer\\n\\nPromote a limited Real Estate offer with QR and claim tracking.\", \"subject\": \"Real Estate Open House Booking\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: coupon. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Claim your limited-time offer Claim Offer\"}, \"thank_you_message\": \"Your offer is ready.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Claim Offer\", \"headline\": \"Claim your limited-time offer\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Claim Offer\", \"thank_you_message\": \"Your offer is ready.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"coupon\", \"campaign_type\": \"coupon\", \"target_module\": \"coupon\", \"tracking_goal\": \"coupon\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": true, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": true}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#84a900\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": true, \"landing_page_blocks\": true}','2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123544,NULL,NULL,NULL,'Real Estate Client Review','real_estate-client-review','landing_page','real_estate','booking','Create a booking-focused landing page for Real Estate customers.','fa-light fa-house-building',NULL,'{\"cta\": \"Book Now\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Book your next visit\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Create a booking-focused landing page for Real Estate customers.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Real Estate Client Review\", \"email_content\": {\"body\": \"Book your next visit\\n\\nCreate a booking-focused landing page for Real Estate customers.\", \"subject\": \"Real Estate Client Review\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: booking. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Book your next visit Book Now\"}, \"thank_you_message\": \"We will confirm your request soon.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Book Now\", \"headline\": \"Book your next visit\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Book Now\", \"thank_you_message\": \"We will confirm your request soon.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"booking\", \"campaign_type\": \"booking\", \"target_module\": \"booking\", \"tracking_goal\": \"booking\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": false, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": true}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#0f766e\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": false, \"landing_page_blocks\": true}','2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123545,NULL,NULL,NULL,'Real Estate Referral Campaign','real_estate-referral-campaign','email','real_estate','referral','Email template for referral and word-of-mouth campaigns.','fa-light fa-house-building',NULL,'{\"cta\": \"Send Referral\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Invite a friend to {business_name}\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Email template for referral and word-of-mouth campaigns.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Real Estate Referral Campaign\", \"email_content\": {\"body\": \"Invite a friend to {business_name}\\n\\nEmail template for referral and word-of-mouth campaigns.\", \"subject\": \"Real Estate Referral Campaign\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: referral. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Invite a friend to {business_name} Send Referral\"}, \"thank_you_message\": \"Referral message generated.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Send Referral\", \"headline\": \"Invite a friend to {business_name}\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Send Referral\", \"thank_you_message\": \"Referral message generated.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"lead\", \"campaign_type\": \"lead\", \"target_module\": \"lead\", \"tracking_goal\": \"referral\", \"default_status\": \"published\", \"creates_qr_code\": false, \"creates_campaign\": false, \"creates_tracking\": false, \"requires_business\": true, \"creates_landing_page\": false}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#2563eb\"}','{\"form_builder\": false, \"prompt_variables\": true, \"campaign_settings\": false, \"landing_page_blocks\": false}','2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123546,NULL,NULL,NULL,'Auto Service Oil Change Coupon','auto_service-oil-change-coupon','form','auto_service','lead','Capture qualified Auto Service leads with a focused form.','fa-light fa-car-wrench',NULL,'{\"cta\": \"Send Request\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Request Oil Change Coupon\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Capture qualified Auto Service leads with a focused form.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Auto Service Oil Change Coupon\", \"email_content\": {\"body\": \"Request Oil Change Coupon\\n\\nCapture qualified Auto Service leads with a focused form.\", \"subject\": \"Auto Service Oil Change Coupon\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: lead. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Request Oil Change Coupon Send Request\"}, \"thank_you_message\": \"We received your request.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Send Request\", \"headline\": \"Request Oil Change Coupon\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Send Request\", \"thank_you_message\": \"We received your request.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"lead\", \"campaign_type\": \"lead\", \"target_module\": \"lead\", \"tracking_goal\": \"lead\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": false, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": false}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#2563eb\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": false, \"landing_page_blocks\": false}','2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123547,NULL,NULL,NULL,'Auto Service Repair Review','auto_service-repair-review','campaign','auto_service','review','Ask recent Auto Service customers for a review and route feedback privately.','fa-light fa-car-wrench',NULL,'{\"cta\": \"Leave a Review\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"How was your experience?\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Ask recent Auto Service customers for a review and route feedback privately.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Auto Service Repair Review\", \"email_content\": {\"body\": \"How was your experience?\\n\\nAsk recent Auto Service customers for a review and route feedback privately.\", \"subject\": \"Auto Service Repair Review\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: review. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"How was your experience? Leave a Review\"}, \"thank_you_message\": \"Thanks for sharing your experience.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Leave a Review\", \"headline\": \"How was your experience?\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Leave a Review\", \"thank_you_message\": \"Thanks for sharing your experience.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"review\", \"campaign_type\": \"review\", \"target_module\": \"review\", \"tracking_goal\": \"review\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": true, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": true}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#d09100\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": true, \"landing_page_blocks\": true}','2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123548,NULL,NULL,NULL,'Auto Service Service Reminder','auto_service-service-reminder','campaign','auto_service','coupon','Promote a limited Auto Service offer with QR and claim tracking.','fa-light fa-car-wrench',NULL,'{\"cta\": \"Claim Offer\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Claim your limited-time offer\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Promote a limited Auto Service offer with QR and claim tracking.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Auto Service Service Reminder\", \"email_content\": {\"body\": \"Claim your limited-time offer\\n\\nPromote a limited Auto Service offer with QR and claim tracking.\", \"subject\": \"Auto Service Service Reminder\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: coupon. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Claim your limited-time offer Claim Offer\"}, \"thank_you_message\": \"Your offer is ready.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Claim Offer\", \"headline\": \"Claim your limited-time offer\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Claim Offer\", \"thank_you_message\": \"Your offer is ready.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"coupon\", \"campaign_type\": \"coupon\", \"target_module\": \"coupon\", \"tracking_goal\": \"coupon\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": true, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": true}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#84a900\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": true, \"landing_page_blocks\": true}','2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123549,NULL,NULL,NULL,'Auto Service Inspection Booking','auto_service-inspection-booking','landing_page','auto_service','booking','Create a booking-focused landing page for Auto Service customers.','fa-light fa-car-wrench',NULL,'{\"cta\": \"Book Now\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Book your next visit\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Create a booking-focused landing page for Auto Service customers.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Auto Service Inspection Booking\", \"email_content\": {\"body\": \"Book your next visit\\n\\nCreate a booking-focused landing page for Auto Service customers.\", \"subject\": \"Auto Service Inspection Booking\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: booking. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Book your next visit Book Now\"}, \"thank_you_message\": \"We will confirm your request soon.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Book Now\", \"headline\": \"Book your next visit\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Book Now\", \"thank_you_message\": \"We will confirm your request soon.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"booking\", \"campaign_type\": \"booking\", \"target_module\": \"booking\", \"tracking_goal\": \"booking\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": false, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": true}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#0f766e\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": false, \"landing_page_blocks\": true}','2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123550,NULL,NULL,NULL,'Auto Service Referral Tune-up','auto_service-referral-tune-up','email','auto_service','referral','Email template for referral and word-of-mouth campaigns.','fa-light fa-car-wrench',NULL,'{\"cta\": \"Send Referral\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Invite a friend to {business_name}\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Email template for referral and word-of-mouth campaigns.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Auto Service Referral Tune-up\", \"email_content\": {\"body\": \"Invite a friend to {business_name}\\n\\nEmail template for referral and word-of-mouth campaigns.\", \"subject\": \"Auto Service Referral Tune-up\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: referral. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Invite a friend to {business_name} Send Referral\"}, \"thank_you_message\": \"Referral message generated.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Send Referral\", \"headline\": \"Invite a friend to {business_name}\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Send Referral\", \"thank_you_message\": \"Referral message generated.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"lead\", \"campaign_type\": \"lead\", \"target_module\": \"lead\", \"tracking_goal\": \"referral\", \"default_status\": \"published\", \"creates_qr_code\": false, \"creates_campaign\": false, \"creates_tracking\": false, \"requires_business\": true, \"creates_landing_page\": false}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#2563eb\"}','{\"form_builder\": false, \"prompt_variables\": true, \"campaign_settings\": false, \"landing_page_blocks\": false}','2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123551,NULL,NULL,NULL,'Local Service Quote Request','local_service-quote-request','form','local_service','lead','Capture qualified Local Service leads with a focused form.','fa-light fa-screwdriver-wrench',NULL,'{\"cta\": \"Send Request\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Request Quote Request\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Capture qualified Local Service leads with a focused form.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Local Service Quote Request\", \"email_content\": {\"body\": \"Request Quote Request\\n\\nCapture qualified Local Service leads with a focused form.\", \"subject\": \"Local Service Quote Request\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: lead. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Request Quote Request Send Request\"}, \"thank_you_message\": \"We received your request.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Send Request\", \"headline\": \"Request Quote Request\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Send Request\", \"thank_you_message\": \"We received your request.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"lead\", \"campaign_type\": \"lead\", \"target_module\": \"lead\", \"tracking_goal\": \"lead\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": false, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": false}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#2563eb\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": false, \"landing_page_blocks\": false}','2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123552,NULL,NULL,NULL,'Local Service Job Review','local_service-job-review','campaign','local_service','review','Ask recent Local Service customers for a review and route feedback privately.','fa-light fa-screwdriver-wrench',NULL,'{\"cta\": \"Leave a Review\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"How was your experience?\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Ask recent Local Service customers for a review and route feedback privately.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Local Service Job Review\", \"email_content\": {\"body\": \"How was your experience?\\n\\nAsk recent Local Service customers for a review and route feedback privately.\", \"subject\": \"Local Service Job Review\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: review. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"How was your experience? Leave a Review\"}, \"thank_you_message\": \"Thanks for sharing your experience.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Leave a Review\", \"headline\": \"How was your experience?\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Leave a Review\", \"thank_you_message\": \"Thanks for sharing your experience.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"review\", \"campaign_type\": \"review\", \"target_module\": \"review\", \"tracking_goal\": \"review\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": true, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": true}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#d09100\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": true, \"landing_page_blocks\": true}','2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123553,NULL,NULL,NULL,'Local Service Seasonal Coupon','local_service-seasonal-coupon','campaign','local_service','coupon','Promote a limited Local Service offer with QR and claim tracking.','fa-light fa-screwdriver-wrench',NULL,'{\"cta\": \"Claim Offer\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Claim your limited-time offer\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Promote a limited Local Service offer with QR and claim tracking.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Local Service Seasonal Coupon\", \"email_content\": {\"body\": \"Claim your limited-time offer\\n\\nPromote a limited Local Service offer with QR and claim tracking.\", \"subject\": \"Local Service Seasonal Coupon\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: coupon. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Claim your limited-time offer Claim Offer\"}, \"thank_you_message\": \"Your offer is ready.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Claim Offer\", \"headline\": \"Claim your limited-time offer\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Claim Offer\", \"thank_you_message\": \"Your offer is ready.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"coupon\", \"campaign_type\": \"coupon\", \"target_module\": \"coupon\", \"tracking_goal\": \"coupon\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": true, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": true}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#84a900\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": true, \"landing_page_blocks\": true}','2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123554,NULL,NULL,NULL,'Local Service Booking Page','local_service-booking-page','landing_page','local_service','booking','Create a booking-focused landing page for Local Service customers.','fa-light fa-screwdriver-wrench',NULL,'{\"cta\": \"Book Now\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Book your next visit\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Create a booking-focused landing page for Local Service customers.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Local Service Booking Page\", \"email_content\": {\"body\": \"Book your next visit\\n\\nCreate a booking-focused landing page for Local Service customers.\", \"subject\": \"Local Service Booking Page\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: booking. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Book your next visit Book Now\"}, \"thank_you_message\": \"We will confirm your request soon.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Book Now\", \"headline\": \"Book your next visit\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Book Now\", \"thank_you_message\": \"We will confirm your request soon.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"booking\", \"campaign_type\": \"booking\", \"target_module\": \"booking\", \"tracking_goal\": \"booking\", \"default_status\": \"published\", \"creates_qr_code\": true, \"creates_campaign\": false, \"creates_tracking\": true, \"requires_business\": true, \"creates_landing_page\": true}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#0f766e\"}','{\"form_builder\": true, \"prompt_variables\": false, \"campaign_settings\": false, \"landing_page_blocks\": true}','2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123555,NULL,NULL,NULL,'Local Service Referral Offer','local_service-referral-offer','email','local_service','referral','Email template for referral and word-of-mouth campaigns.','fa-light fa-screwdriver-wrench',NULL,'{\"cta\": \"Send Referral\", \"qr_text\": \"Scan to open this local campaign.\", \"benefits\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"], \"headline\": \"Invite a friend to {business_name}\", \"variables\": [\"business_name\", \"customer_name\", \"offer\", \"service_name\", \"discount_value\", \"expiry_date\"], \"description\": \"Email template for referral and word-of-mouth campaigns.\", \"form_fields\": [{\"name\": \"name\", \"type\": \"text\", \"label\": \"Your name\", \"required\": true}, {\"name\": \"phone\", \"type\": \"phone\", \"label\": \"Phone number\", \"required\": true}, {\"name\": \"email\", \"type\": \"email\", \"label\": \"Email address\", \"required\": false}], \"campaign_name\": \"Local Service Referral Offer\", \"email_content\": {\"body\": \"Invite a friend to {business_name}\\n\\nEmail template for referral and word-of-mouth campaigns.\", \"subject\": \"Local Service Referral Offer\"}, \"prompt_template\": \"Write a concise local marketing message for {business_name}. Goal: referral. Offer: {offer}.\", \"whatsapp_content\": {\"message\": \"Invite a friend to {business_name} Send Referral\"}, \"thank_you_message\": \"Referral message generated.\", \"landing_page_blocks\": [{\"type\": \"hero\", \"settings\": {\"cta\": \"Send Referral\", \"headline\": \"Invite a friend to {business_name}\"}}, {\"type\": \"benefits\", \"settings\": {\"items\": [\"Fast setup\", \"Mobile-first page\", \"Track every result\"]}}, {\"type\": \"form\", \"settings\": {\"submit_button\": \"Send Referral\", \"thank_you_message\": \"Referral message generated.\"}}]}',1,'system','public','none',NULL,NULL,NULL,0,0,0,'active','1.0.0',0,'{\"page_type\": \"lead\", \"campaign_type\": \"lead\", \"target_module\": \"lead\", \"tracking_goal\": \"referral\", \"default_status\": \"published\", \"creates_qr_code\": false, \"creates_campaign\": false, \"creates_tracking\": false, \"requires_business\": true, \"creates_landing_page\": false}','{\"theme\": \"clean\", \"layout\": \"mobile_first\", \"accent_color\": \"#2563eb\"}','{\"form_builder\": false, \"prompt_variables\": true, \"campaign_settings\": false, \"landing_page_blocks\": false}','2026-06-07 11:24:44','2026-06-07 11:24:44');
/*!40000 ALTER TABLE `lb_marketing_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lb_qr_scans`
--

DROP TABLE IF EXISTS `lb_qr_scans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lb_qr_scans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `campaign_id` bigint unsigned NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `device` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lb_qr_scans_campaign_id_created_at_index` (`campaign_id`,`created_at`),
  KEY `lb_qr_scans_user_campaign_index` (`user_id`,`campaign_id`),
  CONSTRAINT `lb_qr_scans_campaign_id_foreign` FOREIGN KEY (`campaign_id`) REFERENCES `lb_campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lb_qr_scans_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147123498 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lb_qr_scans`
--

LOCK TABLES `lb_qr_scans` WRITE;
/*!40000 ALTER TABLE `lb_qr_scans` DISABLE KEYS */;
INSERT INTO `lb_qr_scans` VALUES (147123468,147123471,147123468,'14.191.241.142','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','desktop',NULL,NULL,'2026-06-07 11:50:01'),(147123469,147123471,147123468,'14.191.241.142','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','desktop',NULL,NULL,'2026-06-07 11:50:56'),(147123470,147123471,147123468,'14.191.241.142','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','desktop',NULL,NULL,'2026-06-07 11:51:29'),(147123471,147123471,147123468,'14.191.246.183','Mozilla/5.0 (iPhone; CPU iPhone OS 18_3_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.3.1 Mobile/15E148 Safari/604.1','mobile',NULL,NULL,'2026-06-07 11:55:20'),(147123472,147123471,147123468,'14.191.241.142','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','desktop',NULL,NULL,'2026-06-07 11:58:25'),(147123473,147123471,147123468,'14.191.241.142','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','desktop',NULL,NULL,'2026-06-07 11:58:44'),(147123474,147123471,147123469,'14.191.241.142','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','desktop',NULL,NULL,'2026-06-07 12:05:56'),(147123475,147123471,147123469,'14.191.241.142','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','desktop',NULL,NULL,'2026-06-07 12:06:22'),(147123476,147123471,147123469,'173.252.79.116','facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)','desktop',NULL,NULL,'2026-06-07 12:07:06'),(147123477,147123471,147123469,'173.252.69.8','Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5 Mobile/15E148 Safari/604.1','mobile',NULL,NULL,'2026-06-07 12:07:11'),(147123478,147123471,147123469,'16.146.86.147','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36','desktop',NULL,NULL,'2026-06-07 12:07:12'),(147123479,147123471,147123469,'173.252.69.24','facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)','desktop',NULL,NULL,'2026-06-07 12:07:13'),(147123480,147123471,147123469,'69.171.231.115','facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)','desktop',NULL,NULL,'2026-06-07 12:07:14'),(147123481,147123471,147123469,'173.252.69.32','facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)','desktop',NULL,NULL,'2026-06-07 12:07:14'),(147123482,147123471,147123469,'173.252.69.15','facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)','desktop',NULL,NULL,'2026-06-07 12:07:14'),(147123483,147123471,147123469,'14.191.246.183','Mozilla/5.0 (iPhone; CPU iPhone OS 18_3_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.3.1 Mobile/15E148 Safari/604.1','mobile',NULL,NULL,'2026-06-07 12:07:22'),(147123484,147123471,147123469,'116.99.173.89','Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 [FBAN/FBIOS;FBAV/563.0.0.27.106;FBBV/980221516;FBDV/iPhone12,1;FBMD/iPhone;FBSN/iOS;FBSV/18.6.2;FBSS/2;FBCR/;FBID/phone;FBLC/vi_VN;FBOP/80]','mobile',NULL,NULL,'2026-06-07 12:07:34'),(147123485,147123471,147123469,'116.99.173.89','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_1) AppleWebKit/601.2.4 (KHTML, like Gecko) Version/9.0.1 Safari/601.2.4 facebookexternalhit/1.1 Facebot Twitterbot/1.0','desktop',NULL,NULL,'2026-06-07 12:07:38'),(147123486,147123471,147123469,'69.171.249.8','facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)','desktop',NULL,NULL,'2026-06-07 12:07:39'),(147123487,147123471,147123469,'116.99.173.89','Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1','mobile',NULL,NULL,'2026-06-07 12:07:42'),(147123488,147123471,147123469,'69.171.234.10','facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)','desktop',NULL,NULL,'2026-06-07 12:07:46'),(147123489,147123471,147123469,'173.252.87.12','facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)','desktop',NULL,NULL,'2026-06-07 12:07:46'),(147123490,147123471,147123469,'173.252.87.112','facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)','desktop',NULL,NULL,'2026-06-07 12:07:52'),(147123491,147123471,147123469,'14.191.246.183','Mozilla/5.0 (iPhone; CPU iPhone OS 18_3_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.3.1 Mobile/15E148 Safari/604.1','mobile',NULL,NULL,'2026-06-07 12:08:10'),(147123492,147123471,147123469,'116.99.173.89','Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1','mobile',NULL,NULL,'2026-06-07 12:08:32'),(147123493,147123471,147123469,'14.191.241.142','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','desktop',NULL,NULL,'2026-06-07 12:26:52'),(147123494,147123471,147123469,'14.191.241.142','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','desktop',NULL,NULL,'2026-06-07 12:27:10'),(147123495,147123471,147123469,'14.191.241.142','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','desktop',NULL,NULL,'2026-06-07 13:01:59'),(147123496,147123471,147123469,'14.191.241.142','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','desktop',NULL,NULL,'2026-06-07 13:02:31'),(147123497,147123471,147123469,'14.191.241.142','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','desktop',NULL,NULL,'2026-06-07 13:02:43');
/*!40000 ALTER TABLE `lb_qr_scans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lb_referral_campaigns`
--

DROP TABLE IF EXISTS `lb_referral_campaigns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lb_referral_campaigns` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `team_id` bigint unsigned DEFAULT NULL,
  `business_id` bigint unsigned NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reward_title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reward_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'coupon',
  `reward_value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `required_referrals` int unsigned NOT NULL DEFAULT '1',
  `target_action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'lead',
  `expiry_days` int unsigned DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `settings` json DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lb_referral_campaigns_slug_unique` (`slug`),
  KEY `lb_referral_campaigns_user_id_foreign` (`user_id`),
  KEY `lb_referral_campaigns_team_id_foreign` (`team_id`),
  KEY `lb_referral_campaigns_business_id_foreign` (`business_id`),
  CONSTRAINT `lb_referral_campaigns_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `lb_businesses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lb_referral_campaigns_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lb_referral_campaigns_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lb_referral_campaigns`
--

LOCK TABLES `lb_referral_campaigns` WRITE;
/*!40000 ALTER TABLE `lb_referral_campaigns` DISABLE KEYS */;
/*!40000 ALTER TABLE `lb_referral_campaigns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lb_referral_links`
--

DROP TABLE IF EXISTS `lb_referral_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lb_referral_links` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `campaign_id` bigint unsigned NOT NULL,
  `customer_id` bigint unsigned NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `clicks_count` int unsigned NOT NULL DEFAULT '0',
  `conversions_count` int unsigned NOT NULL DEFAULT '0',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lb_referral_links_campaign_id_customer_id_unique` (`campaign_id`,`customer_id`),
  UNIQUE KEY `lb_referral_links_code_unique` (`code`),
  KEY `lb_referral_links_customer_id_foreign` (`customer_id`),
  CONSTRAINT `lb_referral_links_campaign_id_foreign` FOREIGN KEY (`campaign_id`) REFERENCES `lb_referral_campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lb_referral_links_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `lb_customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lb_referral_links`
--

LOCK TABLES `lb_referral_links` WRITE;
/*!40000 ALTER TABLE `lb_referral_links` DISABLE KEYS */;
/*!40000 ALTER TABLE `lb_referral_links` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lb_referral_rewards`
--

DROP TABLE IF EXISTS `lb_referral_rewards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lb_referral_rewards` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `campaign_id` bigint unsigned NOT NULL,
  `customer_id` bigint unsigned NOT NULL,
  `referral_id` bigint unsigned DEFAULT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'available',
  `expires_at` timestamp NULL DEFAULT NULL,
  `used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lb_referral_rewards_code_unique` (`code`),
  KEY `lb_referral_rewards_campaign_id_foreign` (`campaign_id`),
  KEY `lb_referral_rewards_customer_id_foreign` (`customer_id`),
  KEY `lb_referral_rewards_referral_id_foreign` (`referral_id`),
  CONSTRAINT `lb_referral_rewards_campaign_id_foreign` FOREIGN KEY (`campaign_id`) REFERENCES `lb_referral_campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lb_referral_rewards_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `lb_customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lb_referral_rewards_referral_id_foreign` FOREIGN KEY (`referral_id`) REFERENCES `lb_referrals` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lb_referral_rewards`
--

LOCK TABLES `lb_referral_rewards` WRITE;
/*!40000 ALTER TABLE `lb_referral_rewards` DISABLE KEYS */;
/*!40000 ALTER TABLE `lb_referral_rewards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lb_referrals`
--

DROP TABLE IF EXISTS `lb_referrals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lb_referrals` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `campaign_id` bigint unsigned NOT NULL,
  `referral_link_id` bigint unsigned NOT NULL,
  `referrer_customer_id` bigint unsigned NOT NULL,
  `referred_customer_id` bigint unsigned NOT NULL,
  `target_action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'lead',
  `target_id` bigint unsigned DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'converted',
  `converted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lb_referrals_campaign_id_foreign` (`campaign_id`),
  KEY `lb_referrals_referral_link_id_foreign` (`referral_link_id`),
  KEY `lb_referrals_referrer_customer_id_foreign` (`referrer_customer_id`),
  KEY `lb_referrals_referred_customer_id_foreign` (`referred_customer_id`),
  CONSTRAINT `lb_referrals_campaign_id_foreign` FOREIGN KEY (`campaign_id`) REFERENCES `lb_referral_campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lb_referrals_referral_link_id_foreign` FOREIGN KEY (`referral_link_id`) REFERENCES `lb_referral_links` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lb_referrals_referred_customer_id_foreign` FOREIGN KEY (`referred_customer_id`) REFERENCES `lb_customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lb_referrals_referrer_customer_id_foreign` FOREIGN KEY (`referrer_customer_id`) REFERENCES `lb_customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lb_referrals`
--

LOCK TABLES `lb_referrals` WRITE;
/*!40000 ALTER TABLE `lb_referrals` DISABLE KEYS */;
/*!40000 ALTER TABLE `lb_referrals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lb_review_feedbacks`
--

DROP TABLE IF EXISTS `lb_review_feedbacks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lb_review_feedbacks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `campaign_id` bigint unsigned NOT NULL,
  `rating` tinyint unsigned NOT NULL,
  `customer_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(24) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'new',
  `ai_reply_history_id` bigint unsigned DEFAULT NULL,
  `replied_at` timestamp NULL DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lb_review_feedbacks_user_id_foreign` (`user_id`),
  KEY `lb_review_feedbacks_campaign_id_rating_index` (`campaign_id`,`rating`),
  KEY `lb_review_feedbacks_ai_reply_history_id_foreign` (`ai_reply_history_id`),
  KEY `lb_review_feedbacks_status_index` (`status`),
  CONSTRAINT `lb_review_feedbacks_ai_reply_history_id_foreign` FOREIGN KEY (`ai_reply_history_id`) REFERENCES `ai_prompt_histories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lb_review_feedbacks_campaign_id_foreign` FOREIGN KEY (`campaign_id`) REFERENCES `lb_campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lb_review_feedbacks_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147123473 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lb_review_feedbacks`
--

LOCK TABLES `lb_review_feedbacks` WRITE;
/*!40000 ALTER TABLE `lb_review_feedbacks` DISABLE KEYS */;
INSERT INTO `lb_review_feedbacks` VALUES (147123468,147123471,147123468,5,'HLinh',NULL,'ntqgiang268@gmail.com','Quá ngon','new',NULL,NULL,NULL,'2026-06-07 11:50:31','2026-06-07 11:50:31'),(147123469,147123471,147123468,1,'G',NULL,'giangkwon@gmail.com','njdfnjnf','new',NULL,NULL,NULL,'2026-06-07 11:51:29','2026-06-07 11:51:29'),(147123470,147123471,147123468,5,'Nguyễn Hoàng linh',NULL,'hlinhwork123@gmail.com','Ok ngon tuyệt','new',NULL,NULL,NULL,'2026-06-07 11:55:55','2026-06-07 11:55:55'),(147123471,147123471,147123468,5,'Nguyễn Hoàng linh',NULL,'hlinhwork123@gmail.com','Ok ngon tuyệt','new',NULL,NULL,NULL,'2026-06-07 11:56:55','2026-06-07 11:56:55'),(147123472,147123471,147123468,3,NULL,NULL,NULL,NULL,'new',NULL,NULL,NULL,'2026-06-07 11:58:44','2026-06-07 11:58:44');
/*!40000 ALTER TABLE `lb_review_feedbacks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lb_template_imports`
--

DROP TABLE IF EXISTS `lb_template_imports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lb_template_imports` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint unsigned DEFAULT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `imported_count` int unsigned NOT NULL DEFAULT '0',
  `failed_count` int unsigned NOT NULL DEFAULT '0',
  `error_log` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lb_template_imports_team_id_foreign` (`team_id`),
  KEY `lb_template_imports_status_index` (`status`),
  CONSTRAINT `lb_template_imports_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lb_template_imports`
--

LOCK TABLES `lb_template_imports` WRITE;
/*!40000 ALTER TABLE `lb_template_imports` DISABLE KEYS */;
/*!40000 ALTER TABLE `lb_template_imports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lb_template_pack_items`
--

DROP TABLE IF EXISTS `lb_template_pack_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lb_template_pack_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pack_id` bigint unsigned NOT NULL,
  `template_id` bigint unsigned NOT NULL,
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lb_template_pack_items_pack_id_template_id_unique` (`pack_id`,`template_id`),
  KEY `lb_template_pack_items_template_id_foreign` (`template_id`),
  CONSTRAINT `lb_template_pack_items_pack_id_foreign` FOREIGN KEY (`pack_id`) REFERENCES `lb_template_packs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lb_template_pack_items_template_id_foreign` FOREIGN KEY (`template_id`) REFERENCES `lb_marketing_templates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147123503 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lb_template_pack_items`
--

LOCK TABLES `lb_template_pack_items` WRITE;
/*!40000 ALTER TABLE `lb_template_pack_items` DISABLE KEYS */;
INSERT INTO `lb_template_pack_items` VALUES (147123468,147123468,147123478,1,'2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123469,147123468,147123495,2,'2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123470,147123468,147123498,3,'2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123471,147123468,147123502,4,'2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123472,147123468,147123503,5,'2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123473,147123468,147123504,6,'2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123474,147123468,147123505,7,'2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123475,147123469,147123472,1,'2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123476,147123469,147123494,2,'2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123477,147123469,147123506,3,'2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123478,147123469,147123507,4,'2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123479,147123469,147123508,5,'2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123480,147123469,147123509,6,'2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123481,147123469,147123510,7,'2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123482,147123470,147123473,1,'2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123483,147123470,147123481,2,'2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123484,147123470,147123484,3,'2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123485,147123470,147123516,4,'2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123486,147123470,147123517,5,'2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123487,147123470,147123518,6,'2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123488,147123470,147123519,7,'2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123489,147123470,147123520,8,'2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123490,147123471,147123499,1,'2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123491,147123471,147123521,2,'2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123492,147123471,147123522,3,'2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123493,147123471,147123523,4,'2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123494,147123471,147123524,5,'2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123495,147123471,147123525,6,'2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123496,147123472,147123487,1,'2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123497,147123472,147123500,2,'2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123498,147123472,147123526,3,'2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123499,147123472,147123527,4,'2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123500,147123472,147123528,5,'2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123501,147123472,147123529,6,'2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123502,147123472,147123530,7,'2026-06-07 11:24:44','2026-06-07 11:24:44');
/*!40000 ALTER TABLE `lb_template_pack_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lb_template_packs`
--

DROP TABLE IF EXISTS `lb_template_packs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lb_template_packs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `description` text COLLATE utf8mb4_unicode_ci,
  `preview_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'system',
  `visibility` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'public',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `version` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1.0.0',
  `install_count` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lb_template_packs_slug_unique` (`slug`),
  KEY `lb_template_packs_team_id_foreign` (`team_id`),
  KEY `lb_template_packs_category_index` (`category`),
  KEY `lb_template_packs_source_index` (`source`),
  KEY `lb_template_packs_visibility_index` (`visibility`),
  KEY `lb_template_packs_status_index` (`status`),
  CONSTRAINT `lb_template_packs_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=147123473 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lb_template_packs`
--

LOCK TABLES `lb_template_packs` WRITE;
/*!40000 ALTER TABLE `lb_template_packs` DISABLE KEYS */;
INSERT INTO `lb_template_packs` VALUES (147123468,NULL,'Restaurant Review Pack','restaurant-review-pack','restaurant','Review, coupon, WhatsApp, and feedback templates for restaurants.',NULL,'system','public','active','1.0.0',0,'2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123469,NULL,'Spa Growth Pack','spa-growth-pack','spa','Booking, coupon, WhatsApp, and retention templates for spas.',NULL,'system','public','active','1.0.0',0,'2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123470,NULL,'Clinic Booking Pack','clinic-booking-pack','clinic','Consultation, appointment, feedback, and follow-up templates for clinics.',NULL,'system','public','active','1.0.0',0,'2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123471,NULL,'Dental Review Pack','dental-review-pack','dentist','Review booster and post-appointment templates for dental practices.',NULL,'system','public','active','1.0.0',0,'2026-06-07 11:24:44','2026-06-07 11:24:44'),(147123472,NULL,'Gym Lead Pack','gym-lead-pack','gym','Lead forms, trial offers, and nurture templates for gyms.',NULL,'system','public','active','1.0.0',0,'2026-06-07 11:24:44','2026-06-07 11:24:44');
/*!40000 ALTER TABLE `lb_template_packs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lb_template_ratings`
--

DROP TABLE IF EXISTS `lb_template_ratings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lb_template_ratings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `template_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `rating` tinyint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lb_template_ratings_template_id_user_id_unique` (`template_id`,`user_id`),
  KEY `lb_template_ratings_user_id_foreign` (`user_id`),
  CONSTRAINT `lb_template_ratings_template_id_foreign` FOREIGN KEY (`template_id`) REFERENCES `lb_marketing_templates` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lb_template_ratings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lb_template_ratings`
--

LOCK TABLES `lb_template_ratings` WRITE;
/*!40000 ALTER TABLE `lb_template_ratings` DISABLE KEYS */;
/*!40000 ALTER TABLE `lb_template_ratings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lb_template_usages`
--

DROP TABLE IF EXISTS `lb_template_usages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lb_template_usages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint unsigned DEFAULT NULL,
  `template_id` bigint unsigned NOT NULL,
  `business_id` bigint unsigned DEFAULT NULL,
  `created_object_type` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_object_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lb_template_usages_team_id_foreign` (`team_id`),
  KEY `lb_template_usages_business_id_foreign` (`business_id`),
  KEY `lb_template_usages_user_id_foreign` (`user_id`),
  KEY `lb_template_usages_template_id_created_object_type_index` (`template_id`,`created_object_type`),
  CONSTRAINT `lb_template_usages_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `lb_businesses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lb_template_usages_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lb_template_usages_template_id_foreign` FOREIGN KEY (`template_id`) REFERENCES `lb_marketing_templates` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lb_template_usages_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lb_template_usages`
--

LOCK TABLES `lb_template_usages` WRITE;
/*!40000 ALTER TABLE `lb_template_usages` DISABLE KEYS */;
/*!40000 ALTER TABLE `lb_template_usages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lb_webhook_automation_logs`
--

DROP TABLE IF EXISTS `lb_webhook_automation_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lb_webhook_automation_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `automation_id` bigint unsigned DEFAULT NULL,
  `business_id` bigint unsigned DEFAULT NULL,
  `customer_id` bigint unsigned DEFAULT NULL,
  `trigger_event` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `related_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `related_id` bigint unsigned DEFAULT NULL,
  `action_type` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'webhook',
  `webhook_url` varchar(2048) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `method` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'POST',
  `request_headers` json DEFAULT NULL,
  `request_payload` json DEFAULT NULL,
  `response_status` smallint unsigned DEFAULT NULL,
  `response_body` longtext COLLATE utf8mb4_unicode_ci,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'queued',
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `queued_at` timestamp NULL DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lb_webhook_automation_logs_automation_id_foreign` (`automation_id`),
  KEY `lb_webhook_automation_logs_business_id_foreign` (`business_id`),
  KEY `lb_webhook_automation_logs_customer_id_foreign` (`customer_id`),
  KEY `lb_webhook_automation_logs_user_id_status_created_at_index` (`user_id`,`status`,`created_at`),
  KEY `lb_webhook_automation_logs_related_type_related_id_index` (`related_type`,`related_id`),
  CONSTRAINT `lb_webhook_automation_logs_automation_id_foreign` FOREIGN KEY (`automation_id`) REFERENCES `lb_webhook_automations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lb_webhook_automation_logs_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `lb_businesses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lb_webhook_automation_logs_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `lb_customers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lb_webhook_automation_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lb_webhook_automation_logs`
--

LOCK TABLES `lb_webhook_automation_logs` WRITE;
/*!40000 ALTER TABLE `lb_webhook_automation_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `lb_webhook_automation_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lb_webhook_automations`
--

DROP TABLE IF EXISTS `lb_webhook_automations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lb_webhook_automations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `business_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `trigger_event` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `delay_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'immediate',
  `delay_value` int unsigned NOT NULL DEFAULT '0',
  `delay_unit` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'minutes',
  `condition_json` json DEFAULT NULL,
  `webhook_url` varchar(2048) COLLATE utf8mb4_unicode_ci NOT NULL,
  `method` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'POST',
  `headers_json` json DEFAULT NULL,
  `secret_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `retry_on_failure` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lb_webhook_automations_business_id_foreign` (`business_id`),
  KEY `lb_webhook_automations_created_by_foreign` (`created_by`),
  KEY `lb_webhook_automations_user_id_trigger_event_status_index` (`user_id`,`trigger_event`,`status`),
  CONSTRAINT `lb_webhook_automations_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `lb_businesses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lb_webhook_automations_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lb_webhook_automations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lb_webhook_automations`
--

LOCK TABLES `lb_webhook_automations` WRITE;
/*!40000 ALTER TABLE `lb_webhook_automations` DISABLE KEYS */;
/*!40000 ALTER TABLE `lb_webhook_automations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lb_whatsapp_notification_logs`
--

DROP TABLE IF EXISTS `lb_whatsapp_notification_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lb_whatsapp_notification_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `automation_id` bigint unsigned DEFAULT NULL,
  `whatsapp_template_id` bigint unsigned DEFAULT NULL,
  `business_id` bigint unsigned DEFAULT NULL,
  `customer_id` bigint unsigned DEFAULT NULL,
  `trigger_event` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `related_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `related_id` bigint unsigned DEFAULT NULL,
  `recipient_phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipient_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'text',
  `template_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `template_language` varchar(12) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `body` longtext COLLATE utf8mb4_unicode_ci,
  `provider_message_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'queued',
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `queued_at` timestamp NULL DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lb_whatsapp_notification_logs_automation_id_foreign` (`automation_id`),
  KEY `lb_whatsapp_notification_logs_whatsapp_template_id_foreign` (`whatsapp_template_id`),
  KEY `lb_whatsapp_notification_logs_business_id_foreign` (`business_id`),
  KEY `lb_whatsapp_notification_logs_customer_id_foreign` (`customer_id`),
  KEY `lb_whatsapp_notification_logs_user_id_status_created_at_index` (`user_id`,`status`,`created_at`),
  KEY `lb_whatsapp_notification_logs_related_type_related_id_index` (`related_type`,`related_id`),
  CONSTRAINT `lb_whatsapp_notification_logs_automation_id_foreign` FOREIGN KEY (`automation_id`) REFERENCES `lb_whatsapp_notifications` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lb_whatsapp_notification_logs_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `lb_businesses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lb_whatsapp_notification_logs_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `lb_customers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lb_whatsapp_notification_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lb_whatsapp_notification_logs_whatsapp_template_id_foreign` FOREIGN KEY (`whatsapp_template_id`) REFERENCES `lb_whatsapp_templates` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lb_whatsapp_notification_logs`
--

LOCK TABLES `lb_whatsapp_notification_logs` WRITE;
/*!40000 ALTER TABLE `lb_whatsapp_notification_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `lb_whatsapp_notification_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lb_whatsapp_notifications`
--

DROP TABLE IF EXISTS `lb_whatsapp_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lb_whatsapp_notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `business_id` bigint unsigned DEFAULT NULL,
  `whatsapp_template_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `trigger_event` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `delay_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'immediate',
  `delay_value` int unsigned NOT NULL DEFAULT '0',
  `delay_unit` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'minutes',
  `condition_json` json DEFAULT NULL,
  `action_json` json DEFAULT NULL,
  `send_to` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'customer',
  `custom_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_number_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `access_token` text COLLATE utf8mb4_unicode_ci,
  `template_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `template_language` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en_US',
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lb_whatsapp_notifications_business_id_foreign` (`business_id`),
  KEY `lb_whatsapp_notifications_whatsapp_template_id_foreign` (`whatsapp_template_id`),
  KEY `lb_whatsapp_notifications_created_by_foreign` (`created_by`),
  KEY `lb_whatsapp_notifications_user_id_trigger_event_status_index` (`user_id`,`trigger_event`,`status`),
  CONSTRAINT `lb_whatsapp_notifications_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `lb_businesses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lb_whatsapp_notifications_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lb_whatsapp_notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lb_whatsapp_notifications_whatsapp_template_id_foreign` FOREIGN KEY (`whatsapp_template_id`) REFERENCES `lb_whatsapp_templates` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lb_whatsapp_notifications`
--

LOCK TABLES `lb_whatsapp_notifications` WRITE;
/*!40000 ALTER TABLE `lb_whatsapp_notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `lb_whatsapp_notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lb_whatsapp_templates`
--

DROP TABLE IF EXISTS `lb_whatsapp_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lb_whatsapp_templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `business_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `template_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `language` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en_US',
  `body` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_system` tinyint(1) NOT NULL DEFAULT '0',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lb_whatsapp_templates_business_id_foreign` (`business_id`),
  KEY `lb_whatsapp_templates_user_id_business_id_status_index` (`user_id`,`business_id`,`status`),
  CONSTRAINT `lb_whatsapp_templates_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `lb_businesses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lb_whatsapp_templates_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147123478 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lb_whatsapp_templates`
--

LOCK TABLES `lb_whatsapp_templates` WRITE;
/*!40000 ALTER TABLE `lb_whatsapp_templates` DISABLE KEYS */;
INSERT INTO `lb_whatsapp_templates` VALUES (147123468,NULL,NULL,'Booking Request Received','booking',NULL,'en_US','Hi {customer_name}, your booking request for {booking_service} with {business_name} on {booking_date} at {booking_time} has been received.',1,'active','2026-06-07 12:34:52','2026-06-07 12:34:52'),(147123469,NULL,NULL,'New Booking Alert','booking',NULL,'en_US','New booking from {customer_name}. Service: {booking_service}. Date: {booking_date} {booking_time}. Phone: {customer_phone}.',1,'active','2026-06-07 12:34:52','2026-06-07 12:34:52'),(147123470,NULL,NULL,'Booking Reminder','booking',NULL,'en_US','Hi {customer_name}, reminder for your appointment with {business_name} on {booking_date} at {booking_time}.',1,'active','2026-06-07 12:34:52','2026-06-07 12:34:52'),(147123471,NULL,NULL,'Booking Completed Review Request','review',NULL,'en_US','Hi {customer_name}, thanks for visiting {business_name}. Please share your feedback here: {public_page_url}',1,'active','2026-06-07 12:34:52','2026-06-07 12:34:52'),(147123472,NULL,NULL,'Coupon Claimed','coupon',NULL,'en_US','Hi {customer_name}, here is your coupon code: {coupon_code}. Show this code at checkout before {coupon_expiry}.',1,'active','2026-06-07 12:34:52','2026-06-07 12:34:52'),(147123473,NULL,NULL,'Lead Owner Alert','lead',NULL,'en_US','New lead from {customer_name}. Phone: {customer_phone}. Message: {feedback_message}',1,'active','2026-06-07 12:34:52','2026-06-07 12:34:52'),(147123474,NULL,NULL,'Lead Thank You','lead',NULL,'en_US','Hi {customer_name}, thanks for contacting {business_name}. Our team will follow up soon.',1,'active','2026-06-07 12:34:52','2026-06-07 12:34:52'),(147123475,NULL,NULL,'Low-score Feedback Alert','feedback',NULL,'en_US','New low-score feedback received from {customer_name}. Rating: {review_rating}. Message: {feedback_message}',1,'active','2026-06-07 12:34:52','2026-06-07 12:34:52'),(147123476,NULL,NULL,'Feedback Thank You','feedback',NULL,'en_US','Hi {customer_name}, thank you for sharing your feedback with {business_name}.',1,'active','2026-06-07 12:34:52','2026-06-07 12:34:52'),(147123477,NULL,NULL,'Stamp Submitted Thank You','loyalty',NULL,'en_US','Hi {customer_name}, your stamp was added to {stamp_card_name}. Progress: {stamp_count}/{required_stamps}. Reward: {reward_title}.',1,'active','2026-06-07 12:34:52','2026-06-07 12:34:52');
/*!40000 ALTER TABLE `lb_whatsapp_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `marketplace_packages`
--

DROP TABLE IF EXISTS `marketplace_packages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `marketplace_packages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_secure` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `package_key` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `module_name` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `version` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source_type` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'zip',
  `product_id` bigint unsigned DEFAULT NULL,
  `purchase_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_slug` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `license_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `licensed_domain` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `install_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `providers` json DEFAULT NULL,
  `meta` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `installed_at` timestamp NULL DEFAULT NULL,
  `last_synced_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `marketplace_packages_id_secure_unique` (`id_secure`),
  UNIQUE KEY `marketplace_packages_package_key_unique` (`package_key`),
  KEY `marketplace_packages_module_name_index` (`module_name`),
  KEY `marketplace_packages_product_id_index` (`product_id`),
  KEY `marketplace_packages_purchase_code_index` (`purchase_code`),
  KEY `marketplace_packages_is_active_index` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=147123474 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `marketplace_packages`
--

LOCK TABLES `marketplace_packages` WRITE;
/*!40000 ALTER TABLE `marketplace_packages` DISABLE KEYS */;
INSERT INTO `marketplace_packages` VALUES (147123468,'ABPYSt4InVH4UAeZBZ47C8vhELFMLBp2','localboost-ai-review-booster-booking-coupons-feedback-lead-generation-saas','MLHUB','MLHUB','','1.0.1','purchase',10252026,'d80177d1-4974-4e46-a7f3-564da3bc83f7','localboost-ai-review-booster-booking-coupons-feedback-lead-generation-saas','Extended License','mlhub.vn','/var/www/html/./','[]','{\"is_main\": 1, \"payload\": {\"slug\": \"localboost-ai-review-booster-booking-coupons-feedback-lead-generation-saas\", \"domain\": \"mlhub.vn\", \"status\": 1, \"license\": \"Extended License\", \"message\": \"Addon installation verified.\", \"version\": \"1.0.1\", \"product_id\": 10252026, \"module_name\": null, \"install_path\": \"./\"}, \"installer_managed\": true, \"marketplace_version\": \"1.0.1\"}',1,'2026-06-06 15:58:01','2026-06-06 15:58:01','2026-06-06 15:58:01','2026-06-06 15:58:01'),(147123469,'R0ub7eRy4Id3r8BSmW2eHhpzmwmXysAp','appemailautomation','AppEmailAutomation','Smart Email Automation – Booking, Coupon, Lead, Review & Feedback Follow-up for LocalBoost AI','','1.0.0','purchase',11052026,'b20ef8aa-3a28-481c-bd3f-12520968b836','smart-email-automation-booking-coupon-lead-review-feedback-follow-up-for-localboost-ai','Extended License','mlhub.vn','/var/www/html/modules/AppEmailAutomation','[\"Modules\\\\AppEmailAutomation\\\\Providers\\\\AppEmailAutomationServiceProvider\"]','{\"alias\": \"appemailautomation\", \"domain\": \"mlhub.vn\", \"bundle_modules\": [\"AppEmailAutomation\"], \"requested_module_name\": \"AppEmailAutomation\"}',1,'2026-06-06 15:58:01','2026-06-06 15:58:01','2026-06-06 15:58:01','2026-06-06 15:58:01'),(147123470,'ZToVHDY0R4PcxsOZULAQ51OR2hwg1s9A','appwhatsappnotification','AppWhatsAppNotification','WhatsApp Automation – Booking, Coupon, Lead & Review Notifications for LocalBoost AI','','1.0.1','purchase',12052026,'d1c41405-b528-4d94-8f4f-e9656d60744e','whatsapp-automation-booking-coupon-lead-review-notifications-for-localboost-ai','Extended License','mlhub.vn','/var/www/html/modules/AppWhatsAppNotification','[\"Modules\\\\AppWhatsAppNotification\\\\Providers\\\\AppWhatsAppNotificationServiceProvider\"]','{\"alias\": \"appwhatsappnotification\", \"domain\": \"mlhub.vn\", \"bundle_modules\": [\"AppWhatsAppNotification\"], \"requested_module_name\": \"AppWhatsAppNotification\"}',1,'2026-06-06 15:58:01','2026-06-06 15:58:01','2026-06-06 15:58:01','2026-06-06 15:58:01'),(147123471,'YXv8zZFsIVOdRleOepNAt0flD6hbS2i9','appwebhookautomation','AppWebhookAutomation','Zapier & Webhook Automation – Connect Leads, Bookings, Coupons & Feedback for LocalBoost AI','','1.0.0','purchase',13052026,'a593cf93-2277-41cb-8d73-4bf22886a441','zapier-webhook-automation-connect-leads-bookings-coupons-feedback-for-localboost-ai','Extended License','mlhub.vn','/var/www/html/modules/AppWebhookAutomation','[\"Modules\\\\AppWebhookAutomation\\\\Providers\\\\AppWebhookAutomationServiceProvider\"]','{\"alias\": \"appwebhookautomation\", \"domain\": \"mlhub.vn\", \"bundle_modules\": [\"AppWebhookAutomation\"], \"requested_module_name\": \"AppWebhookAutomation\"}',1,'2026-06-06 15:58:01','2026-06-06 15:58:01','2026-06-06 15:58:01','2026-06-06 15:58:01'),(147123472,'GNPOj9rueNy2poPmTJ0tp4X5uZg1mSof','appgooglebusiness','AppGoogleBusiness','Google Business – Locations, Reviews, AI Replies, Auto Reply & Posts for LocalBoost AI','Google Business Profile integration addon for LocalBoost AI.','1.0.0','purchase',52112026,'632966be-be4b-4fed-8ef9-7634fac56bac','google-business-locations-reviews-ai-replies-auto-reply-posts-for-localboost-ai','Extended License','mlhub.vn','/var/www/html/modules/AppGoogleBusiness','[\"Modules\\\\AppGoogleBusiness\\\\Providers\\\\AppGoogleBusinessServiceProvider\"]','{\"alias\": \"appgooglebusiness\", \"domain\": \"mlhub.vn\", \"product_id\": 52112026, \"bundle_modules\": [\"AppGoogleBusiness\"], \"requested_module_name\": \"AppGoogleBusiness\"}',1,'2026-06-06 15:58:01','2026-06-06 15:58:01','2026-06-06 15:58:01','2026-06-06 16:03:30'),(147123473,'X5xPPHrcTKVQZlWzTRrQZ1m5MCxQ8ios','appadvancedcustomercrm','AppAdvancedCustomerCrm','Advanced CRM – Timeline, Tags, Segments, Notes, Tasks & Customer Automation for LocalBoost AI','','1.0.0','purchase',99052026,'76e54c2f-d67b-418a-89e5-f0e77921567c','advanced-crm-timeline-tags-segments-notes-tasks-customer-automation-for-localboost-ai','Extended License','mlhub.vn','/var/www/html/modules/AppAdvancedCustomerCrm','[\"Modules\\\\AppAdvancedCustomerCrm\\\\Providers\\\\AppAdvancedCustomerCrmServiceProvider\"]','{\"alias\": \"appadvancedcustomercrm\", \"domain\": \"mlhub.vn\", \"product_id\": 99052026, \"bundle_modules\": [\"AppAdvancedCustomerCrm\"], \"requested_module_name\": \"AppAdvancedCustomerCrm\"}',1,'2026-06-06 15:58:01','2026-06-06 15:58:01','2026-06-06 15:58:01','2026-06-06 16:03:30');
/*!40000 ALTER TABLE `marketplace_packages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2026_04_18_110000_create_database',1),(2,'2026_05_11_040000_create_email_automation_tables',1),(3,'2026_05_11_040000_create_whatsapp_notification_tables',1),(4,'2026_05_11_040100_add_email_automation_permissions_to_plans',1),(5,'2026_05_11_040100_add_whatsapp_notification_permissions_to_plans',1),(6,'2026_05_11_050000_create_google_business_tables',1),(7,'2026_05_11_050100_add_google_business_permissions_to_plans',1),(8,'2026_05_11_050200_extend_google_business_for_channels_auto_reply',1),(9,'2026_05_11_050300_add_managed_state_to_google_business_locations',1),(10,'2026_05_11_051100_add_template_reply_to_google_auto_reply_rules',1),(11,'2026_05_11_052000_create_google_business_posts_tables',1),(12,'2026_05_11_052100_add_scheduled_at_to_google_business_posts',1),(13,'2026_05_11_060000_create_webhook_automation_tables',1),(14,'2026_05_11_060100_add_webhook_automation_permissions_to_plans',1),(15,'2026_05_11_230000_create_loyalty_stamp_card_tables',1),(16,'2026_05_11_230100_add_loyalty_permissions_to_plans',1),(17,'2026_05_12_010000_upgrade_marketing_templates_to_template_engine',1),(18,'2026_05_12_011000_create_template_engine_pack_tables',1),(19,'2026_05_12_012000_add_template_sharing_marketplace_workflow',1),(20,'2026_05_12_020000_add_anti_abuse_limits_to_loyalty_cards',1),(21,'2026_05_12_030000_add_design_settings_to_loyalty_cards',1),(22,'2026_05_12_040000_create_referral_campaign_tables',1),(23,'2026_05_12_041000_add_referral_permissions_to_plans',1),(24,'2026_05_12_041100_add_loyalty_customer_counters',1),(25,'2026_05_12_050000_create_advanced_customer_crm_tables',1),(26,'2026_05_12_050100_add_advanced_crm_permissions_to_plans',1),(27,'2026_05_12_051000_create_crm_automation_and_merge_tables',1),(28,'2026_05_12_052000_create_crm_automation_jobs_table',1),(29,'2026_06_02_120000_add_lb_qr_scans_user_campaign_index',1),(30,'2026_06_04_120000_mlhub_brand_logo_svg_defaults',1),(31,'2026_06_05_120000_mlhub_site_asset_svg_defaults',1),(32,'2026_06_06_120000_ensure_blog_rss_tables',2),(33,'2026_06_08_120000_update_plan_descriptions_mlhub_local_growth',3);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notification_manual`
--

DROP TABLE IF EXISTS `notification_manual`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notification_manual` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_secure` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'news',
  `is_global` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notification_manual_created_by_index` (`created_by`),
  KEY `notification_manual_id_secure_index` (`id_secure`)
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notification_manual`
--

LOCK TABLES `notification_manual` WRITE;
/*!40000 ALTER TABLE `notification_manual` DISABLE KEYS */;
/*!40000 ALTER TABLE `notification_manual` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notification_manual_states`
--

DROP TABLE IF EXISTS `notification_manual_states`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notification_manual_states` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `notification_manual_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `archived_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `notif_manual_user_unique` (`notification_manual_id`,`user_id`),
  KEY `notification_manual_states_user_id_archived_at_index` (`user_id`,`archived_at`)
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notification_manual_states`
--

LOCK TABLES `notification_manual_states` WRITE;
/*!40000 ALTER TABLE `notification_manual_states` DISABLE KEYS */;
/*!40000 ALTER TABLE `notification_manual_states` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_secure` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint unsigned NOT NULL,
  `source` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'auto',
  `mid` bigint unsigned DEFAULT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'news',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_user_id_index` (`user_id`),
  KEY `notifications_mid_index` (`mid`),
  KEY `notifications_user_id_read_at_index` (`user_id`,`read_at`),
  KEY `notifications_id_secure_index` (`id_secure`)
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `options`
--

DROP TABLE IF EXISTS `options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `options` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `options_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=147123614 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `options`
--

LOCK TABLES `options` WRITE;
/*!40000 ALTER TABLE `options` DISABLE KEYS */;
INSERT INTO `options` VALUES (147123468,'website_logo_brand_dark','img/logo-brand-dark.svg',NULL,NULL),(147123469,'website_logo_brand_light','img/logo-brand-light.svg',NULL,NULL),(147123470,'website_favicon','img/favicon.svg',NULL,NULL),(147123471,'website_logo_dark','img/logo-dark.svg',NULL,NULL),(147123472,'website_logo_light','img/logo-light.svg',NULL,NULL),(147123473,'default_locale','vi',NULL,NULL),(147123474,'app_timezone','Asia/Ho_Chi_Minh',NULL,NULL),(147123475,'website_title','MLHUB',NULL,NULL),(147123476,'website_description','Nền tảng Marketing Automation hỗ trợ tăng đánh giá, đặt lịch, mã ưu đãi, phản hồi & tạo khách hàng tiềm năng.',NULL,NULL),(147123477,'website_keyword','MLHUB, Marketing Automation, đánh giá, đặt lịch, mã ưu đãi, phản hồi, khách hàng tiềm năng, hộ kinh doanh',NULL,NULL),(147123478,'contact_company_name','MLHUB',NULL,NULL),(147123479,'contact_email','admin@mlhub.vn',NULL,NULL),(147123480,'frontend_theme','mlhubfrontend',NULL,NULL),(147123481,'backend_theme','mlhubbackend',NULL,NULL),(147123482,'theme_settings.guest.mlhubfrontend','{\"accent_color\":\"#ff5f5f\",\"body_bg_color\":\"#fffbf8\",\"surface_bg_color\":\"#ffffff\",\"header_bg_color\":\"#ffffff\",\"header_text_color\":\"#2d1810\",\"link_color\":\"#ff5f5f\",\"link_hover_color\":\"#ff8c42\",\"border_color\":\"#ffe5dc\",\"muted_text_color\":\"#8a7068\",\"success_color\":\"#059669\",\"warning_color\":\"#ffb347\",\"danger_color\":\"#dc2626\",\"font_family\":\"manrope\",\"page_max_width\":\"86rem\",\"default_appearance\":\"light\",\"supports_dark_mode\":\"1\",\"allow_user_appearance_toggle\":\"1\",\"section_spacing\":\"5rem\",\"card_radius\":\"18\",\"input_radius\":\"14\",\"button_radius\":\"14\"}',NULL,NULL),(147123483,'theme_settings.app.mlhubbackend','{\"accent_color\":\"#ff5f5f\",\"sidebar_bg_color\":\"#fffbf8\",\"header_bg_color\":\"#ffffff\",\"header_active_color\":\"#ff8c42\",\"link_color\":\"#ff5f5f\",\"link_hover_color\":\"#ff8c42\",\"border_color\":\"#ffe5dc\",\"muted_text_color\":\"#8a7068\",\"sidebar_text_color\":\"#2d1810\",\"header_text_color\":\"#2d1810\",\"success_color\":\"#059669\",\"warning_color\":\"#ffb347\",\"danger_color\":\"#dc2626\",\"dark_accent_color\":\"#ff8a8a\",\"dark_sidebar_bg_color\":\"#1f110c\",\"dark_header_bg_color\":\"#251610\",\"dark_header_active_color\":\"#ff8c42\",\"dark_link_color\":\"#ff7b7b\",\"dark_link_hover_color\":\"#ff8c42\",\"dark_border_color\":\"#4a3028\",\"dark_muted_text_color\":\"#b89a90\",\"dark_sidebar_text_color\":\"#ffe5dc\",\"dark_header_text_color\":\"#fffbf8\",\"dark_success_color\":\"#34d399\",\"dark_warning_color\":\"#ffb347\",\"dark_danger_color\":\"#f87171\",\"font_family\":\"manrope\",\"layout_width\":\"full\",\"page_max_width\":\"90rem\",\"supports_dark_mode\":\"1\",\"allow_user_appearance_toggle\":\"1\",\"default_appearance\":\"light\",\"density\":\"comfortable\",\"section_spacing\":\"1.5rem\",\"preview_mode\":\"desktop\",\"card_radius\":\"14\",\"input_radius\":\"10\",\"button_radius\":\"12\",\"button_style\":\"solid\",\"button_shadow\":\"soft\"}',NULL,NULL),(147123484,'installer_completed_at','2026-06-06T15:58:00+07:00',NULL,NULL),(147123485,'system_cron_secure_key','v3xwuBjKqsICFFT6',NULL,NULL),(147123486,'format_date','d/m/Y',NULL,NULL),(147123487,'format_datetime','d/m/Y H:i',NULL,NULL),(147123488,'format_number_style','vi_VN',NULL,NULL),(147123489,'format_decimal_separator',',',NULL,NULL),(147123490,'format_thousands_separator','.',NULL,NULL),(147123491,'default_currency','VND',NULL,NULL),(147123492,'format_money_decimals','0',NULL,NULL),(147123493,'contact_company_website','https://mlhub.vn',NULL,NULL),(147123494,'contact_phone_number','0899789225',NULL,NULL),(147123495,'contact_working_hours','Thứ 2 - Thứ 6: 09:00 - 18:00',NULL,NULL),(147123496,'contact_location','01 Nguyễn Văn Linh, Đà Nẵng',NULL,NULL),(147123497,'app_navigation','sidebar',NULL,NULL),(147123498,'payment_notify_success_status','1',NULL,NULL),(147123499,'payment_notify_success_subject','Đã xác nhận thanh toán cho :plan',NULL,NULL),(147123500,'payment_notify_success_message','Giao dịch thanh toán của bạn với số tiền :amount :currency qua :gateway đã được xác nhận.\n\nID giao dịch: :transaction_id.\nKế hoạch hiện tại: :plan.',NULL,NULL),(147123501,'payment_notify_failed_status','1',NULL,NULL),(147123502,'payment_notify_failed_subject','Thanh toán không thành công cho :plan',NULL,NULL),(147123503,'payment_notify_failed_message','Chúng tôi không thể xác nhận thanh toán của bạn qua :gateway.\n\nLý do: :message.\nKế hoạch: :plan.',NULL,NULL),(147123504,'payment_notify_review_status','1',NULL,NULL),(147123505,'payment_notify_review_subject','Đã nhận được thanh toán và đang chờ xem xét',NULL,NULL),(147123506,'payment_notify_review_message','Chúng tôi đã nhận được yêu cầu thanh toán của bạn cho :plan và hiện đang chờ xem xét.\n\nCổng thanh toán: :gateway.\nTrạng thái: :status.\nTin nhắn: :message.',NULL,NULL),(147123507,'payment_notify_cancel_status','1',NULL,NULL),(147123508,'payment_notify_cancel_subject','Đã hủy thanh toán hoặc đăng ký',NULL,NULL),(147123509,'payment_notify_cancel_message','Giao dịch thanh toán/đăng ký của bạn cho gói :plan đã bị hủy.\n\nCổng thanh toán: :gateway.\nTrạng thái: :status.\nTin nhắn: :message.',NULL,NULL),(147123510,'payment_manual_status','1',NULL,NULL),(147123511,'payment_manual_prefix','PAYQR-',NULL,NULL),(147123512,'payment_manual_info','<div>Tên ngân hàng</div><div>Số tài khoản</div><div>Số tiền cần chuyển</div><div>Nội dung chuyển khoản</div>',NULL,NULL),(147123513,'privacy_policy_title','Chính sách quyền riêng tư',NULL,NULL),(147123514,'privacy_policy_content','Nội dung đang cập nhật...',NULL,NULL),(147123515,'terms_of_use_title','Điều khoản sử dụng',NULL,NULL),(147123516,'terms_of_use_content','Nội dung đang cập nhật...',NULL,NULL),(147123517,'social_pages_title','Các trang xã hội',NULL,NULL),(147123518,'social_pages_intro','Theo dõi MLHUB trên mạng xã hội.',NULL,NULL),(147123519,'social_facebook_url','https://mlhub.vn/#Facebook',NULL,NULL),(147123520,'social_instagram_url','https://mlhub.vn/#Instagram',NULL,NULL),(147123521,'social_linkedin_url','https://mlhub.vn/#LinkedIn',NULL,NULL),(147123522,'social_x_url','https://mlhub.vn/#X',NULL,NULL),(147123523,'social_youtube_url','https://mlhub.vn/#YouTube',NULL,NULL),(147123524,'social_tiktok_url','https://mlhub.vn/#TikTok',NULL,NULL),(147123525,'social_telegram_url','https://mlhub.vn/#Telegram',NULL,NULL),(147123526,'social_whatsapp_url','https://mlhub.vn/#WhatsApp',NULL,NULL),(147123527,'captcha_type','disable',NULL,NULL),(147123528,'auth_landing_page_status','1',NULL,NULL),(147123529,'auth_signup_page_status','1',NULL,NULL),(147123530,'auth_activation_email_new_user_status','1',NULL,NULL),(147123531,'auth_welcome_email_new_user_status','1',NULL,NULL),(147123532,'auth_two_factor_authentication_status','1',NULL,NULL),(147123533,'google_analytics_status','1',NULL,NULL),(147123534,'google_analytics_measurement_id','G-ZY1P6YJLME',NULL,NULL),(147123535,'google_analytics_track_guest','1',NULL,NULL),(147123536,'google_analytics_track_app','1',NULL,NULL),(147123537,'mail_protocol','smtp',NULL,NULL),(147123538,'mail_sender_email','hotro@mlhub.vn',NULL,NULL),(147123539,'mail_sender_name','MLHUB.vn',NULL,NULL),(147123540,'smtp_server','smtp.emailit.com',NULL,NULL),(147123541,'smtp_username','emailit',NULL,NULL),(147123542,'smtp_port','587',NULL,NULL),(147123543,'smtp_encryption','tls',NULL,NULL),(147123544,'mail_timeout','30',NULL,NULL),(147123545,'mail_ehlo_domain','mlhub.vn',NULL,NULL),(147123546,'smtp_password','secret_LHzlnBLwXz8ZGV0JztVkvn9mf4VFKE14',NULL,NULL),(147123547,'license_purchase_code','d80177d1-4974-4e46-a7f3-564da3bc83f7',NULL,NULL),(147123548,'license_status','verified',NULL,NULL),(147123549,'license_product_id','10252026',NULL,NULL),(147123550,'license_version','1.0.1',NULL,NULL),(147123551,'license_install_path','./',NULL,NULL),(147123552,'license_verified_at','2026-06-06T15:58:01+07:00',NULL,NULL),(147123553,'license_meta','{\"status\":1,\"message\":\"Addon installation verified.\",\"module_name\":null,\"slug\":\"localboost-ai-review-booster-booking-coupons-feedback-lead-generation-saas\",\"version\":\"1.0.1\",\"license\":\"Extended License\",\"domain\":\"mlhub.vn\",\"product_id\":10252026,\"install_path\":\".\\/\"}',NULL,NULL),(147123554,'integration_google_business_profile_status','1',NULL,NULL),(147123555,'integration_google_business_profile_client_id','266306087862-6cq30ofj92o1tg1809246bi1rh4edmm9.apps.googleusercontent.com',NULL,NULL),(147123556,'integration_google_business_profile_client_secret','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',NULL,NULL),(147123557,'auth_user_change_email_status','0',NULL,NULL),(147123558,'auth_user_change_username_status','0',NULL,NULL),(147123559,'auth_facebook_login_status','0',NULL,NULL),(147123560,'auth_facebook_login_app_id','',NULL,NULL),(147123561,'auth_facebook_login_app_secret','',NULL,NULL),(147123562,'auth_facebook_login_app_version','v22.0',NULL,NULL),(147123563,'auth_google_login_status','1',NULL,NULL),(147123564,'auth_google_login_client_id','266306087862-0cf2mkerddr0dic2fgj1j25jpb269gg2.apps.googleusercontent.com',NULL,NULL),(147123565,'auth_google_login_client_secret','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',NULL,NULL),(147123566,'auth_x_login_status','0',NULL,NULL),(147123567,'auth_x_login_client_id','',NULL,NULL),(147123568,'auth_x_login_client_secret','',NULL,NULL),(147123569,'ai_status','1',NULL,NULL),(147123570,'ai_default_provider','openai',NULL,NULL),(147123571,'ai_default_language','vi',NULL,NULL),(147123572,'ai_default_tone_of_voice','professional',NULL,NULL),(147123573,'ai_default_creativity','experimental',NULL,NULL),(147123574,'ai_maximum_input_length','100',NULL,NULL),(147123575,'ai_maximum_output_length','2000',NULL,NULL),(147123576,'ai_text_model','gpt-5.4',NULL,NULL),(147123577,'ai_embedding_model','text-embedding-3-small',NULL,NULL),(147123578,'ai_openai_api_key','',NULL,NULL),(147123579,'ai_openai_url','https://api.openai.com/v1',NULL,NULL),(147123580,'ai_anthropic_api_key','',NULL,NULL),(147123581,'ai_gemini_api_key','AQ.Ab8RN6ItdimY52trdm4HAuDrMOFmhONxv42S4yozlLMm-ivfoQ',NULL,NULL),(147123582,'ai_groq_api_key','',NULL,NULL),(147123583,'ai_ollama_api_key','',NULL,NULL),(147123584,'ai_ollama_url','http://localhost:11434',NULL,NULL),(147123585,'ai_xai_api_key','',NULL,NULL),(147123586,'ai_openrouter_api_key','',NULL,NULL),(147123587,'ai_replicate_api_key','',NULL,NULL),(147123588,'ai_replicate_model_slug','zylim0702/qr_code_controlnet',NULL,NULL),(147123589,'ai_replicate_version_override','',NULL,NULL),(147123590,'ai_replicate_template_preset','zylim_qr_code_controlnet',NULL,NULL),(147123591,'ai_replicate_input_template','{\n    \"url\": \"{{qr_value}}\",\n    \"prompt\": \"{{prompt}}\",\n    \"qr_conditioning_scale\": 1.35,\n    \"num_outputs\": 1,\n    \"image_resolution\": 768,\n    \"scheduler\": \"DDIM\",\n    \"num_inference_steps\": 28,\n    \"guidance_scale\": 9,\n    \"eta\": 0,\n    \"negative_prompt\": \"{{negative_prompt}}\",\n    \"guess_mode\": false,\n    \"disable_safety_check\": false\n}',NULL,NULL),(147123592,'ai_content_status','1',NULL,NULL),(147123593,'ai_content_provider','gemini',NULL,NULL),(147123594,'ai_content_model','gemini-2.5-flash',NULL,NULL),(147123595,'ai_text_status','1',NULL,NULL),(147123596,'ai_text_provider','gemini',NULL,NULL),(147123597,'ai_text_generation_model','gemini-2.5-flash',NULL,NULL),(147123598,'ai_image_status','1',NULL,NULL),(147123599,'ai_image_provider','gemini',NULL,NULL),(147123600,'ai_image_model','gemini-2.5-flash-image',NULL,NULL),(147123601,'ai_qr_status','1',NULL,NULL),(147123602,'ai_video_status','0',NULL,NULL),(147123603,'ai_video_provider','openai',NULL,NULL),(147123604,'ai_video_model','sora-2',NULL,NULL),(147123605,'ai_chat_status','1',NULL,NULL),(147123606,'ai_chat_provider','gemini',NULL,NULL),(147123607,'ai_chat_model','gemini-2.5-flash',NULL,NULL),(147123608,'ai_voice_status','0',NULL,NULL),(147123609,'ai_voice_provider','openai',NULL,NULL),(147123610,'ai_voice_model','gpt-realtime',NULL,NULL),(147123611,'ai_code_status','1',NULL,NULL),(147123612,'ai_code_provider','gemini',NULL,NULL),(147123613,'ai_code_model','gemini-2.5-flash',NULL,NULL);
/*!40000 ALTER TABLE `options` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
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
-- Table structure for table `payment_history`
--

DROP TABLE IF EXISTS `payment_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payment_history` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_secure` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uid` bigint unsigned DEFAULT NULL,
  `plan_id` bigint unsigned DEFAULT NULL,
  `from` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `currency` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'USD',
  `by` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `status` tinyint unsigned NOT NULL DEFAULT '1',
  `changed` bigint unsigned DEFAULT NULL,
  `created` bigint unsigned DEFAULT NULL,
  `meta` json DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_history_id_secure_unique` (`id_secure`),
  UNIQUE KEY `payment_history_transaction_id_unique` (`transaction_id`),
  KEY `payment_history_uid_foreign` (`uid`),
  KEY `payment_history_plan_id_foreign` (`plan_id`),
  KEY `payment_history_status_created_index` (`status`,`created`),
  KEY `payment_history_from_index` (`from`),
  CONSTRAINT `payment_history_plan_id_foreign` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payment_history_uid_foreign` FOREIGN KEY (`uid`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_history`
--

LOCK TABLES `payment_history` WRITE;
/*!40000 ALTER TABLE `payment_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `payment_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_manual`
--

DROP TABLE IF EXISTS `payment_manual`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payment_manual` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_secure` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uid` bigint unsigned DEFAULT NULL,
  `plan_id` bigint unsigned DEFAULT NULL,
  `payment_id` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_info` varchar(2000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `currency` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'USD',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `status` tinyint unsigned NOT NULL DEFAULT '0',
  `created` bigint unsigned DEFAULT NULL,
  `changed` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payment_manual_uid_foreign` (`uid`),
  KEY `payment_manual_plan_id_foreign` (`plan_id`),
  KEY `payment_manual_status_created_index` (`status`,`created`),
  KEY `payment_manual_id_secure_index` (`id_secure`),
  KEY `payment_manual_payment_id_index` (`payment_id`),
  CONSTRAINT `payment_manual_plan_id_foreign` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payment_manual_uid_foreign` FOREIGN KEY (`uid`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_manual`
--

LOCK TABLES `payment_manual` WRITE;
/*!40000 ALTER TABLE `payment_manual` DISABLE KEYS */;
/*!40000 ALTER TABLE `payment_manual` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_subscriptions`
--

DROP TABLE IF EXISTS `payment_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payment_subscriptions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_secure` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uid` bigint unsigned DEFAULT NULL,
  `plan_id` bigint unsigned DEFAULT NULL,
  `type` tinyint unsigned DEFAULT NULL,
  `service` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subscription_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `currency` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint unsigned NOT NULL DEFAULT '1',
  `changed` bigint unsigned DEFAULT NULL,
  `created` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payment_subscriptions_uid_foreign` (`uid`),
  KEY `payment_subscriptions_plan_id_foreign` (`plan_id`),
  KEY `payment_subscriptions_status_created_index` (`status`,`created`),
  KEY `payment_subscriptions_source_index` (`source`),
  KEY `payment_subscriptions_id_secure_index` (`id_secure`),
  KEY `payment_subscriptions_subscription_id_index` (`subscription_id`),
  KEY `payment_subscriptions_customer_id_index` (`customer_id`),
  CONSTRAINT `payment_subscriptions_plan_id_foreign` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payment_subscriptions_uid_foreign` FOREIGN KEY (`uid`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_subscriptions`
--

LOCK TABLES `payment_subscriptions` WRITE;
/*!40000 ALTER TABLE `payment_subscriptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `payment_subscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `plans`
--

DROP TABLE IF EXISTS `plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `plans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `featured` tinyint(1) NOT NULL DEFAULT '0',
  `currency` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'USD',
  `price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `type` tinyint unsigned NOT NULL DEFAULT '1',
  `free_plan` tinyint(1) NOT NULL DEFAULT '0',
  `default_signup_plan` tinyint(1) NOT NULL DEFAULT '0',
  `trial_day` int unsigned NOT NULL DEFAULT '0',
  `position` int unsigned NOT NULL DEFAULT '0',
  `desc` text COLLATE utf8mb4_unicode_ci,
  `permissions` json DEFAULT NULL,
  `monthly_price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `yearly_price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `description` text COLLATE utf8mb4_unicode_ci,
  `features` json DEFAULT NULL,
  `limits` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admin_plans_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=147123477 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `plans`
--

LOCK TABLES `plans` WRITE;
/*!40000 ALTER TABLE `plans` DISABLE KEYS */;
INSERT INTO `plans` VALUES (147123468,'Starter — Monthly','starter-monthly','1',0,'VND',490000.00,1,0,0,7,10,'For new local businesses starting campaign pages, QR codes, and review growth.','{\"files\": true, \"teams\": true, \"groups\": false, \"support\": true, \"channels\": true, \"affiliate\": false, \"ai_studio\": true, \"watermark\": false, \"automation\": false, \"bulk_posts\": false, \"localboost\": true, \"publishing\": true, \"advanced_crm\": false, \"file_dropbox\": false, \"image_editor\": true, \"max_channels\": 6, \"max_qr_codes\": 25, \"ai_publishing\": false, \"credits_usage\": true, \"customer_tags\": 0, \"facebook_page\": true, \"file_onedrive\": false, \"max_campaigns\": 10, \"max_templates\": 10, \"rss_schedules\": false, \"webhook_retry\": false, \"customer_tasks\": 0, \"max_businesses\": 3, \"ai_studio_image\": false, \"crm_automations\": 0, \"google_business\": false, \"loyalty_rewards\": false, \"remove_branding\": false, \"automation_delay\": false, \"email_automation\": false, \"emails_per_month\": 0, \"label_publishing\": false, \"max_file_size_mb\": 128, \"max_team_members\": 2, \"customer_segments\": 0, \"file_google_drive\": false, \"instagram_profile\": true, \"max_landing_pages\": 10, \"max_loyalty_cards\": 0, \"qr_custom_domains\": false, \"channel_count_mode\": \"entire_social_network\", \"google_review_sync\": false, \"max_custom_domains\": 0, \"webhook_automation\": false, \"webhooks_per_month\": 0, \"whatsapp_cloud_api\": false, \"ai_studio_repurpose\": false, \"campaign_publishing\": true, \"credits_usage_limit\": 300, \"google_review_reply\": false, \"loyalty_stamp_cards\": false, \"max_email_templates\": 0, \"max_posts_per_month\": 120, \"max_storage_size_mb\": 2048, \"search_media_online\": true, \"loyalty_staff_redeem\": false, \"automation_conditions\": false, \"google_business_posts\": false, \"max_email_automations\": 0, \"max_loyalty_customers\": 0, \"whatsapp_notification\": false, \"channel_facebook_pages\": true, \"max_bulk_posts_per_csv\": 0, \"max_referral_campaigns\": 0, \"max_whatsapp_templates\": 0, \"webhook_custom_headers\": false, \"max_webhook_automations\": 0, \"google_business_insights\": false, \"ai_studio_content_planner\": true, \"channel_instagram_profiles\": true, \"max_whatsapp_notifications\": 0, \"whatsapp_template_messages\": false, \"ai_studio_caption_generator\": true, \"crm_activity_retention_days\": 90, \"whatsapp_messages_per_month\": 0, \"ai_publishing_generate_image\": 3, \"ai_publishing_generate_images\": false, \"max_google_business_locations\": 0, \"ai_publishing_generate_content\": 1, \"max_google_business_connections\": 0, \"ai_publishing_user_schedule_time\": false, \"max_ai_publishing_posts_per_month\": 0, \"credit_cost_ai_studio_review_reply\": 1, \"credit_cost_ai_studio_plan_calendar\": 1, \"credit_cost_ai_studio_generate_image\": 3, \"credit_cost_ai_studio_generate_captions\": 1, \"credit_cost_ai_studio_repurpose_content\": 1}',0.00,0.00,0,NULL,NULL,NULL,'2026-06-06 15:57:57','2026-06-06 15:57:57'),(147123469,'Growth — Monthly','growth-monthly','1',1,'VND',990000.00,1,0,0,10,20,'For growing local businesses running recurring campaigns, bookings, and reports.','{\"files\": true, \"teams\": true, \"groups\": true, \"support\": true, \"channels\": true, \"affiliate\": true, \"ai_studio\": true, \"watermark\": true, \"automation\": false, \"bulk_posts\": true, \"localboost\": true, \"publishing\": true, \"advanced_crm\": true, \"file_dropbox\": false, \"image_editor\": true, \"max_channels\": 20, \"max_qr_codes\": 150, \"ai_publishing\": true, \"credits_usage\": true, \"customer_tags\": 25, \"facebook_page\": true, \"file_onedrive\": false, \"max_campaigns\": 50, \"max_templates\": 50, \"rss_schedules\": true, \"webhook_retry\": true, \"customer_tasks\": 100, \"max_businesses\": 10, \"ai_studio_image\": true, \"crm_automations\": 5, \"google_business\": true, \"loyalty_rewards\": true, \"remove_branding\": true, \"automation_delay\": true, \"email_automation\": true, \"emails_per_month\": 3000, \"label_publishing\": true, \"max_file_size_mb\": 512, \"max_team_members\": 5, \"customer_segments\": 10, \"file_google_drive\": true, \"instagram_profile\": true, \"max_landing_pages\": 50, \"max_loyalty_cards\": 5, \"qr_custom_domains\": true, \"channel_count_mode\": \"entire_social_network\", \"google_review_sync\": true, \"max_custom_domains\": 3, \"webhook_automation\": true, \"webhooks_per_month\": 5000, \"whatsapp_cloud_api\": true, \"ai_studio_repurpose\": true, \"campaign_publishing\": true, \"credits_usage_limit\": 2000, \"google_review_reply\": true, \"loyalty_stamp_cards\": false, \"max_email_templates\": 10, \"max_posts_per_month\": 600, \"max_storage_size_mb\": 10240, \"search_media_online\": true, \"loyalty_staff_redeem\": false, \"automation_conditions\": false, \"google_business_posts\": false, \"max_email_automations\": 5, \"max_loyalty_customers\": 500, \"whatsapp_notification\": true, \"channel_facebook_pages\": true, \"max_bulk_posts_per_csv\": 500, \"max_referral_campaigns\": 3, \"max_whatsapp_templates\": 20, \"webhook_custom_headers\": true, \"max_webhook_automations\": 10, \"google_business_insights\": true, \"ai_studio_content_planner\": true, \"channel_instagram_profiles\": true, \"max_whatsapp_notifications\": 10, \"whatsapp_template_messages\": true, \"ai_studio_caption_generator\": true, \"crm_activity_retention_days\": 365, \"whatsapp_messages_per_month\": 1000, \"ai_publishing_generate_image\": 3, \"ai_publishing_generate_images\": true, \"max_google_business_locations\": 5, \"ai_publishing_generate_content\": 1, \"max_google_business_connections\": 2, \"ai_publishing_user_schedule_time\": true, \"max_ai_publishing_posts_per_month\": 80, \"credit_cost_ai_studio_review_reply\": 1, \"credit_cost_ai_studio_plan_calendar\": 1, \"credit_cost_ai_studio_generate_image\": 3, \"credit_cost_ai_studio_generate_captions\": 1, \"credit_cost_ai_studio_repurpose_content\": 1}',0.00,0.00,0,NULL,NULL,NULL,'2026-06-06 15:57:57','2026-06-06 15:57:57'),(147123470,'Professional — Monthly','agency-monthly','1',0,'VND',1990000.00,1,0,0,14,30,'For teams managing multiple brands, campaigns, and full marketing pipelines.','{\"files\": true, \"teams\": true, \"groups\": true, \"support\": true, \"channels\": true, \"affiliate\": true, \"ai_studio\": true, \"watermark\": true, \"automation\": false, \"bulk_posts\": true, \"localboost\": true, \"publishing\": true, \"advanced_crm\": true, \"file_dropbox\": true, \"image_editor\": true, \"max_channels\": -1, \"max_qr_codes\": -1, \"ai_publishing\": true, \"credits_usage\": true, \"customer_tags\": -1, \"facebook_page\": true, \"file_onedrive\": true, \"max_campaigns\": -1, \"max_templates\": -1, \"rss_schedules\": true, \"webhook_retry\": true, \"customer_tasks\": -1, \"max_businesses\": -1, \"ai_studio_image\": true, \"crm_automations\": -1, \"google_business\": true, \"loyalty_rewards\": true, \"remove_branding\": true, \"automation_delay\": true, \"email_automation\": true, \"emails_per_month\": -1, \"label_publishing\": true, \"max_file_size_mb\": 2048, \"max_team_members\": 15, \"customer_segments\": -1, \"file_google_drive\": true, \"instagram_profile\": true, \"max_landing_pages\": -1, \"max_loyalty_cards\": -1, \"qr_custom_domains\": true, \"channel_count_mode\": \"entire_social_network\", \"google_review_sync\": true, \"max_custom_domains\": -1, \"webhook_automation\": true, \"webhooks_per_month\": -1, \"whatsapp_cloud_api\": true, \"ai_studio_repurpose\": true, \"campaign_publishing\": true, \"credits_usage_limit\": -1, \"google_review_reply\": true, \"loyalty_stamp_cards\": true, \"max_email_templates\": -1, \"max_posts_per_month\": -1, \"max_storage_size_mb\": 51200, \"search_media_online\": true, \"loyalty_staff_redeem\": true, \"automation_conditions\": true, \"google_business_posts\": true, \"max_email_automations\": -1, \"max_loyalty_customers\": -1, \"whatsapp_notification\": true, \"channel_facebook_pages\": true, \"max_bulk_posts_per_csv\": -1, \"max_referral_campaigns\": -1, \"max_whatsapp_templates\": -1, \"webhook_custom_headers\": true, \"max_webhook_automations\": -1, \"google_business_insights\": true, \"ai_studio_content_planner\": true, \"channel_instagram_profiles\": true, \"max_whatsapp_notifications\": -1, \"whatsapp_template_messages\": true, \"ai_studio_caption_generator\": true, \"crm_activity_retention_days\": -1, \"whatsapp_messages_per_month\": -1, \"ai_publishing_generate_image\": 3, \"ai_publishing_generate_images\": true, \"max_google_business_locations\": -1, \"ai_publishing_generate_content\": 1, \"max_google_business_connections\": -1, \"ai_publishing_user_schedule_time\": true, \"max_ai_publishing_posts_per_month\": -1, \"credit_cost_ai_studio_review_reply\": 1, \"credit_cost_ai_studio_plan_calendar\": 1, \"credit_cost_ai_studio_generate_image\": 3, \"credit_cost_ai_studio_generate_captions\": 1, \"credit_cost_ai_studio_repurpose_content\": 1}',0.00,0.00,0,NULL,NULL,NULL,'2026-06-06 15:57:57','2026-06-06 15:57:57'),(147123471,'Starter — Yearly','starter-yearly','1',0,'VND',4900000.00,2,0,0,14,10,'Annual plan for local businesses that want stable costs and steady marketing growth.','{\"files\": true, \"teams\": true, \"groups\": false, \"support\": true, \"channels\": true, \"affiliate\": false, \"ai_studio\": true, \"watermark\": false, \"automation\": false, \"bulk_posts\": false, \"localboost\": true, \"publishing\": true, \"advanced_crm\": false, \"file_dropbox\": false, \"image_editor\": true, \"max_channels\": 6, \"max_qr_codes\": 25, \"ai_publishing\": false, \"credits_usage\": true, \"customer_tags\": 0, \"facebook_page\": true, \"file_onedrive\": false, \"max_campaigns\": 10, \"max_templates\": 10, \"rss_schedules\": false, \"webhook_retry\": false, \"customer_tasks\": 0, \"max_businesses\": 3, \"ai_studio_image\": false, \"crm_automations\": 0, \"google_business\": false, \"loyalty_rewards\": false, \"remove_branding\": false, \"automation_delay\": false, \"email_automation\": false, \"emails_per_month\": 0, \"label_publishing\": false, \"max_file_size_mb\": 128, \"max_team_members\": 2, \"customer_segments\": 0, \"file_google_drive\": false, \"instagram_profile\": true, \"max_landing_pages\": 10, \"max_loyalty_cards\": 0, \"qr_custom_domains\": false, \"channel_count_mode\": \"entire_social_network\", \"google_review_sync\": false, \"max_custom_domains\": 0, \"webhook_automation\": false, \"webhooks_per_month\": 0, \"whatsapp_cloud_api\": false, \"ai_studio_repurpose\": false, \"campaign_publishing\": true, \"credits_usage_limit\": 300, \"google_review_reply\": false, \"loyalty_stamp_cards\": false, \"max_email_templates\": 0, \"max_posts_per_month\": 120, \"max_storage_size_mb\": 2048, \"search_media_online\": true, \"loyalty_staff_redeem\": false, \"automation_conditions\": false, \"google_business_posts\": false, \"max_email_automations\": 0, \"max_loyalty_customers\": 0, \"whatsapp_notification\": false, \"channel_facebook_pages\": true, \"max_bulk_posts_per_csv\": 0, \"max_referral_campaigns\": 0, \"max_whatsapp_templates\": 0, \"webhook_custom_headers\": false, \"max_webhook_automations\": 0, \"google_business_insights\": false, \"ai_studio_content_planner\": true, \"channel_instagram_profiles\": true, \"max_whatsapp_notifications\": 0, \"whatsapp_template_messages\": false, \"ai_studio_caption_generator\": true, \"crm_activity_retention_days\": 90, \"whatsapp_messages_per_month\": 0, \"ai_publishing_generate_image\": 3, \"ai_publishing_generate_images\": false, \"max_google_business_locations\": 0, \"ai_publishing_generate_content\": 1, \"max_google_business_connections\": 0, \"ai_publishing_user_schedule_time\": false, \"max_ai_publishing_posts_per_month\": 0, \"credit_cost_ai_studio_review_reply\": 1, \"credit_cost_ai_studio_plan_calendar\": 1, \"credit_cost_ai_studio_generate_image\": 3, \"credit_cost_ai_studio_generate_captions\": 1, \"credit_cost_ai_studio_repurpose_content\": 1}',0.00,0.00,0,NULL,NULL,NULL,'2026-06-06 15:57:57','2026-06-06 15:57:57'),(147123472,'Growth — Yearly','growth-yearly','1',1,'VND',9900000.00,2,0,0,21,20,'Yearly plan for teams scaling campaigns, automation, and AI-driven local workflows.','{\"files\": true, \"teams\": true, \"groups\": true, \"support\": true, \"channels\": true, \"affiliate\": true, \"ai_studio\": true, \"watermark\": true, \"automation\": false, \"bulk_posts\": true, \"localboost\": true, \"publishing\": true, \"advanced_crm\": true, \"file_dropbox\": false, \"image_editor\": true, \"max_channels\": 20, \"max_qr_codes\": 150, \"ai_publishing\": true, \"credits_usage\": true, \"customer_tags\": 25, \"facebook_page\": true, \"file_onedrive\": false, \"max_campaigns\": 50, \"max_templates\": 50, \"rss_schedules\": true, \"webhook_retry\": true, \"customer_tasks\": 100, \"max_businesses\": 10, \"ai_studio_image\": true, \"crm_automations\": 5, \"google_business\": true, \"loyalty_rewards\": true, \"remove_branding\": true, \"automation_delay\": true, \"email_automation\": true, \"emails_per_month\": 3000, \"label_publishing\": true, \"max_file_size_mb\": 512, \"max_team_members\": 5, \"customer_segments\": 10, \"file_google_drive\": true, \"instagram_profile\": true, \"max_landing_pages\": 50, \"max_loyalty_cards\": 5, \"qr_custom_domains\": true, \"channel_count_mode\": \"entire_social_network\", \"google_review_sync\": true, \"max_custom_domains\": 3, \"webhook_automation\": true, \"webhooks_per_month\": 5000, \"whatsapp_cloud_api\": true, \"ai_studio_repurpose\": true, \"campaign_publishing\": true, \"credits_usage_limit\": 2000, \"google_review_reply\": true, \"loyalty_stamp_cards\": false, \"max_email_templates\": 10, \"max_posts_per_month\": 600, \"max_storage_size_mb\": 10240, \"search_media_online\": true, \"loyalty_staff_redeem\": false, \"automation_conditions\": false, \"google_business_posts\": false, \"max_email_automations\": 5, \"max_loyalty_customers\": 500, \"whatsapp_notification\": true, \"channel_facebook_pages\": true, \"max_bulk_posts_per_csv\": 500, \"max_referral_campaigns\": 3, \"max_whatsapp_templates\": 20, \"webhook_custom_headers\": true, \"max_webhook_automations\": 10, \"google_business_insights\": true, \"ai_studio_content_planner\": true, \"channel_instagram_profiles\": true, \"max_whatsapp_notifications\": 10, \"whatsapp_template_messages\": true, \"ai_studio_caption_generator\": true, \"crm_activity_retention_days\": 365, \"whatsapp_messages_per_month\": 1000, \"ai_publishing_generate_image\": 3, \"ai_publishing_generate_images\": true, \"max_google_business_locations\": 5, \"ai_publishing_generate_content\": 1, \"max_google_business_connections\": 2, \"ai_publishing_user_schedule_time\": true, \"max_ai_publishing_posts_per_month\": 80, \"credit_cost_ai_studio_review_reply\": 1, \"credit_cost_ai_studio_plan_calendar\": 1, \"credit_cost_ai_studio_generate_image\": 3, \"credit_cost_ai_studio_generate_captions\": 1, \"credit_cost_ai_studio_repurpose_content\": 1}',0.00,0.00,0,NULL,NULL,NULL,'2026-06-06 15:57:57','2026-06-06 15:57:57'),(147123473,'Professional — Yearly','agency-yearly','1',0,'VND',19900000.00,2,0,0,30,30,'Full-year plan for agencies running many workspaces and large-scale marketing ops.','{\"files\": true, \"teams\": true, \"groups\": true, \"support\": true, \"channels\": true, \"affiliate\": true, \"ai_studio\": true, \"watermark\": true, \"automation\": false, \"bulk_posts\": true, \"localboost\": true, \"publishing\": true, \"advanced_crm\": true, \"file_dropbox\": true, \"image_editor\": true, \"max_channels\": -1, \"max_qr_codes\": -1, \"ai_publishing\": true, \"credits_usage\": true, \"customer_tags\": -1, \"facebook_page\": true, \"file_onedrive\": true, \"max_campaigns\": -1, \"max_templates\": -1, \"rss_schedules\": true, \"webhook_retry\": true, \"customer_tasks\": -1, \"max_businesses\": -1, \"ai_studio_image\": true, \"crm_automations\": -1, \"google_business\": true, \"loyalty_rewards\": true, \"remove_branding\": true, \"automation_delay\": true, \"email_automation\": true, \"emails_per_month\": -1, \"label_publishing\": true, \"max_file_size_mb\": 2048, \"max_team_members\": 15, \"customer_segments\": -1, \"file_google_drive\": true, \"instagram_profile\": true, \"max_landing_pages\": -1, \"max_loyalty_cards\": -1, \"qr_custom_domains\": true, \"channel_count_mode\": \"entire_social_network\", \"google_review_sync\": true, \"max_custom_domains\": -1, \"webhook_automation\": true, \"webhooks_per_month\": -1, \"whatsapp_cloud_api\": true, \"ai_studio_repurpose\": true, \"campaign_publishing\": true, \"credits_usage_limit\": -1, \"google_review_reply\": true, \"loyalty_stamp_cards\": true, \"max_email_templates\": -1, \"max_posts_per_month\": -1, \"max_storage_size_mb\": 51200, \"search_media_online\": true, \"loyalty_staff_redeem\": true, \"automation_conditions\": true, \"google_business_posts\": true, \"max_email_automations\": -1, \"max_loyalty_customers\": -1, \"whatsapp_notification\": true, \"channel_facebook_pages\": true, \"max_bulk_posts_per_csv\": -1, \"max_referral_campaigns\": -1, \"max_whatsapp_templates\": -1, \"webhook_custom_headers\": true, \"max_webhook_automations\": -1, \"google_business_insights\": true, \"ai_studio_content_planner\": true, \"channel_instagram_profiles\": true, \"max_whatsapp_notifications\": -1, \"whatsapp_template_messages\": true, \"ai_studio_caption_generator\": true, \"crm_activity_retention_days\": -1, \"whatsapp_messages_per_month\": -1, \"ai_publishing_generate_image\": 3, \"ai_publishing_generate_images\": true, \"max_google_business_locations\": -1, \"ai_publishing_generate_content\": 1, \"max_google_business_connections\": -1, \"ai_publishing_user_schedule_time\": true, \"max_ai_publishing_posts_per_month\": -1, \"credit_cost_ai_studio_review_reply\": 1, \"credit_cost_ai_studio_plan_calendar\": 1, \"credit_cost_ai_studio_generate_image\": 3, \"credit_cost_ai_studio_generate_captions\": 1, \"credit_cost_ai_studio_repurpose_content\": 1}',0.00,0.00,0,NULL,NULL,NULL,'2026-06-06 15:57:57','2026-06-06 15:57:57'),(147123474,'Starter — Lifetime','starter-lifetime','1',0,'VND',7900000.00,3,0,0,30,10,'One-time payment for small shops building a long-term MLHUB local growth base.','{\"files\": true, \"teams\": true, \"groups\": false, \"support\": true, \"channels\": true, \"affiliate\": false, \"ai_studio\": true, \"watermark\": false, \"automation\": false, \"bulk_posts\": false, \"localboost\": true, \"publishing\": true, \"advanced_crm\": false, \"file_dropbox\": false, \"image_editor\": true, \"max_channels\": 6, \"max_qr_codes\": 25, \"ai_publishing\": false, \"credits_usage\": true, \"customer_tags\": 0, \"facebook_page\": true, \"file_onedrive\": false, \"max_campaigns\": 10, \"max_templates\": 10, \"rss_schedules\": false, \"webhook_retry\": false, \"customer_tasks\": 0, \"max_businesses\": 3, \"ai_studio_image\": false, \"crm_automations\": 0, \"google_business\": false, \"loyalty_rewards\": false, \"remove_branding\": false, \"automation_delay\": false, \"email_automation\": false, \"emails_per_month\": 0, \"label_publishing\": false, \"max_file_size_mb\": 128, \"max_team_members\": 2, \"customer_segments\": 0, \"file_google_drive\": false, \"instagram_profile\": true, \"max_landing_pages\": 10, \"max_loyalty_cards\": 0, \"qr_custom_domains\": false, \"channel_count_mode\": \"entire_social_network\", \"google_review_sync\": false, \"max_custom_domains\": 0, \"webhook_automation\": false, \"webhooks_per_month\": 0, \"whatsapp_cloud_api\": false, \"ai_studio_repurpose\": false, \"campaign_publishing\": true, \"credits_usage_limit\": 300, \"google_review_reply\": false, \"loyalty_stamp_cards\": false, \"max_email_templates\": 0, \"max_posts_per_month\": 120, \"max_storage_size_mb\": 2048, \"search_media_online\": true, \"loyalty_staff_redeem\": false, \"automation_conditions\": false, \"google_business_posts\": false, \"max_email_automations\": 0, \"max_loyalty_customers\": 0, \"whatsapp_notification\": false, \"channel_facebook_pages\": true, \"max_bulk_posts_per_csv\": 0, \"max_referral_campaigns\": 0, \"max_whatsapp_templates\": 0, \"webhook_custom_headers\": false, \"max_webhook_automations\": 0, \"google_business_insights\": false, \"ai_studio_content_planner\": true, \"channel_instagram_profiles\": true, \"max_whatsapp_notifications\": 0, \"whatsapp_template_messages\": false, \"ai_studio_caption_generator\": true, \"crm_activity_retention_days\": 90, \"whatsapp_messages_per_month\": 0, \"ai_publishing_generate_image\": 3, \"ai_publishing_generate_images\": false, \"max_google_business_locations\": 0, \"ai_publishing_generate_content\": 1, \"max_google_business_connections\": 0, \"ai_publishing_user_schedule_time\": false, \"max_ai_publishing_posts_per_month\": 0, \"credit_cost_ai_studio_review_reply\": 1, \"credit_cost_ai_studio_plan_calendar\": 1, \"credit_cost_ai_studio_generate_image\": 3, \"credit_cost_ai_studio_generate_captions\": 1, \"credit_cost_ai_studio_repurpose_content\": 1}',0.00,0.00,0,NULL,NULL,NULL,'2026-06-06 15:57:57','2026-06-06 15:57:57'),(147123475,'Growth — Lifetime','growth-lifetime','1',1,'VND',14900000.00,3,0,0,45,20,'Lifetime access for active businesses needing AI, automation, and higher volume.','{\"files\": true, \"teams\": true, \"groups\": true, \"support\": true, \"channels\": true, \"affiliate\": true, \"ai_studio\": true, \"watermark\": true, \"automation\": false, \"bulk_posts\": true, \"localboost\": true, \"publishing\": true, \"advanced_crm\": true, \"file_dropbox\": false, \"image_editor\": true, \"max_channels\": 20, \"max_qr_codes\": 150, \"ai_publishing\": true, \"credits_usage\": true, \"customer_tags\": 25, \"facebook_page\": true, \"file_onedrive\": false, \"max_campaigns\": 50, \"max_templates\": 50, \"rss_schedules\": true, \"webhook_retry\": true, \"customer_tasks\": 100, \"max_businesses\": 10, \"ai_studio_image\": true, \"crm_automations\": 5, \"google_business\": true, \"loyalty_rewards\": true, \"remove_branding\": true, \"automation_delay\": true, \"email_automation\": true, \"emails_per_month\": 3000, \"label_publishing\": true, \"max_file_size_mb\": 512, \"max_team_members\": 5, \"customer_segments\": 10, \"file_google_drive\": true, \"instagram_profile\": true, \"max_landing_pages\": 50, \"max_loyalty_cards\": 5, \"qr_custom_domains\": true, \"channel_count_mode\": \"entire_social_network\", \"google_review_sync\": true, \"max_custom_domains\": 3, \"webhook_automation\": true, \"webhooks_per_month\": 5000, \"whatsapp_cloud_api\": true, \"ai_studio_repurpose\": true, \"campaign_publishing\": true, \"credits_usage_limit\": 2000, \"google_review_reply\": true, \"loyalty_stamp_cards\": false, \"max_email_templates\": 10, \"max_posts_per_month\": 600, \"max_storage_size_mb\": 10240, \"search_media_online\": true, \"loyalty_staff_redeem\": false, \"automation_conditions\": false, \"google_business_posts\": false, \"max_email_automations\": 5, \"max_loyalty_customers\": 500, \"whatsapp_notification\": true, \"channel_facebook_pages\": true, \"max_bulk_posts_per_csv\": 500, \"max_referral_campaigns\": 3, \"max_whatsapp_templates\": 20, \"webhook_custom_headers\": true, \"max_webhook_automations\": 10, \"google_business_insights\": true, \"ai_studio_content_planner\": true, \"channel_instagram_profiles\": true, \"max_whatsapp_notifications\": 10, \"whatsapp_template_messages\": true, \"ai_studio_caption_generator\": true, \"crm_activity_retention_days\": 365, \"whatsapp_messages_per_month\": 1000, \"ai_publishing_generate_image\": 3, \"ai_publishing_generate_images\": true, \"max_google_business_locations\": 5, \"ai_publishing_generate_content\": 1, \"max_google_business_connections\": 2, \"ai_publishing_user_schedule_time\": true, \"max_ai_publishing_posts_per_month\": 80, \"credit_cost_ai_studio_review_reply\": 1, \"credit_cost_ai_studio_plan_calendar\": 1, \"credit_cost_ai_studio_generate_image\": 3, \"credit_cost_ai_studio_generate_captions\": 1, \"credit_cost_ai_studio_repurpose_content\": 1}',0.00,0.00,0,NULL,NULL,NULL,'2026-06-06 15:57:57','2026-06-06 15:57:57'),(147123476,'Professional — Lifetime','agency-lifetime','1',0,'VND',29900000.00,3,0,0,60,30,'Lifetime plan for operators managing many clients, assets, and automations.','{\"files\": true, \"teams\": true, \"groups\": true, \"support\": true, \"channels\": true, \"affiliate\": true, \"ai_studio\": true, \"watermark\": true, \"automation\": false, \"bulk_posts\": true, \"localboost\": true, \"publishing\": true, \"advanced_crm\": true, \"file_dropbox\": true, \"image_editor\": true, \"max_channels\": -1, \"max_qr_codes\": -1, \"ai_publishing\": true, \"credits_usage\": true, \"customer_tags\": -1, \"facebook_page\": true, \"file_onedrive\": true, \"max_campaigns\": -1, \"max_templates\": -1, \"rss_schedules\": true, \"webhook_retry\": true, \"customer_tasks\": -1, \"max_businesses\": -1, \"ai_studio_image\": true, \"crm_automations\": -1, \"google_business\": true, \"loyalty_rewards\": true, \"remove_branding\": true, \"automation_delay\": true, \"email_automation\": true, \"emails_per_month\": -1, \"label_publishing\": true, \"max_file_size_mb\": 2048, \"max_team_members\": 15, \"customer_segments\": -1, \"file_google_drive\": true, \"instagram_profile\": true, \"max_landing_pages\": -1, \"max_loyalty_cards\": -1, \"qr_custom_domains\": true, \"channel_count_mode\": \"entire_social_network\", \"google_review_sync\": true, \"max_custom_domains\": -1, \"webhook_automation\": true, \"webhooks_per_month\": -1, \"whatsapp_cloud_api\": true, \"ai_studio_repurpose\": true, \"campaign_publishing\": true, \"credits_usage_limit\": -1, \"google_review_reply\": true, \"loyalty_stamp_cards\": true, \"max_email_templates\": -1, \"max_posts_per_month\": -1, \"max_storage_size_mb\": 51200, \"search_media_online\": true, \"loyalty_staff_redeem\": true, \"automation_conditions\": true, \"google_business_posts\": true, \"max_email_automations\": -1, \"max_loyalty_customers\": -1, \"whatsapp_notification\": true, \"channel_facebook_pages\": true, \"max_bulk_posts_per_csv\": -1, \"max_referral_campaigns\": -1, \"max_whatsapp_templates\": -1, \"webhook_custom_headers\": true, \"max_webhook_automations\": -1, \"google_business_insights\": true, \"ai_studio_content_planner\": true, \"channel_instagram_profiles\": true, \"max_whatsapp_notifications\": -1, \"whatsapp_template_messages\": true, \"ai_studio_caption_generator\": true, \"crm_activity_retention_days\": -1, \"whatsapp_messages_per_month\": -1, \"ai_publishing_generate_image\": 3, \"ai_publishing_generate_images\": true, \"max_google_business_locations\": -1, \"ai_publishing_generate_content\": 1, \"max_google_business_connections\": -1, \"ai_publishing_user_schedule_time\": true, \"max_ai_publishing_posts_per_month\": -1, \"credit_cost_ai_studio_review_reply\": 1, \"credit_cost_ai_studio_plan_calendar\": 1, \"credit_cost_ai_studio_generate_image\": 3, \"credit_cost_ai_studio_generate_captions\": 1, \"credit_cost_ai_studio_repurpose_content\": 1}',0.00,0.00,0,NULL,NULL,NULL,'2026-06-06 15:57:57','2026-06-06 15:57:57');
/*!40000 ALTER TABLE `plans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `posts`
--

DROP TABLE IF EXISTS `posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `posts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_secure` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `team_id` bigint unsigned DEFAULT NULL,
  `campaign` bigint unsigned DEFAULT NULL,
  `labels` json DEFAULT NULL,
  `account_id` bigint unsigned DEFAULT NULL,
  `social_network` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `module` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `function` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `api_type` tinyint unsigned DEFAULT '1',
  `type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `method` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'basic',
  `query_id` bigint unsigned DEFAULT NULL,
  `data` longtext COLLATE utf8mb4_unicode_ci,
  `time_post` int DEFAULT NULL,
  `delay` int NOT NULL DEFAULT '0',
  `repost_frequency` int NOT NULL DEFAULT '0',
  `repost_until` int DEFAULT NULL,
  `result` longtext COLLATE utf8mb4_unicode_ci,
  `tmp` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `custom_data_1` text COLLATE utf8mb4_unicode_ci,
  `custom_data_2` text COLLATE utf8mb4_unicode_ci,
  `custom_data_3` text COLLATE utf8mb4_unicode_ci,
  `status` int DEFAULT NULL,
  `changed` int DEFAULT NULL,
  `created` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `posts_id_secure_unique` (`id_secure`),
  KEY `posts_user_id_foreign` (`user_id`),
  KEY `posts_account_id_foreign` (`account_id`),
  KEY `posts_social_network_index` (`social_network`),
  KEY `posts_function_index` (`function`),
  KEY `posts_time_post_index` (`time_post`),
  KEY `posts_status_index` (`status`),
  KEY `posts_team_id_index` (`team_id`),
  CONSTRAINT `posts_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `social_accounts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `posts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `posts`
--

LOCK TABLES `posts` WRITE;
/*!40000 ALTER TABLE `posts` DISABLE KEYS */;
/*!40000 ALTER TABLE `posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rss_schedule_histories`
--

DROP TABLE IF EXISTS `rss_schedule_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rss_schedule_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `schedule_id` bigint unsigned NOT NULL,
  `account_id` bigint unsigned NOT NULL,
  `publishing_post_id` bigint unsigned DEFAULT NULL,
  `external_guid` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `external_url` varchar(2000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content_hash` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `queued_at` int unsigned DEFAULT NULL,
  `published_at` int unsigned DEFAULT NULL,
  `changed` int unsigned DEFAULT NULL,
  `created` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rss_schedule_histories_unique_item` (`schedule_id`,`account_id`,`content_hash`),
  KEY `rss_schedule_histories_account_id_foreign` (`account_id`),
  KEY `rss_schedule_histories_publishing_post_id_foreign` (`publishing_post_id`),
  CONSTRAINT `rss_schedule_histories_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `social_accounts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rss_schedule_histories_publishing_post_id_foreign` FOREIGN KEY (`publishing_post_id`) REFERENCES `posts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `rss_schedule_histories_schedule_id_foreign` FOREIGN KEY (`schedule_id`) REFERENCES `rss_schedules` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rss_schedule_histories`
--

LOCK TABLES `rss_schedule_histories` WRITE;
/*!40000 ALTER TABLE `rss_schedule_histories` DISABLE KEYS */;
/*!40000 ALTER TABLE `rss_schedule_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rss_schedules`
--

DROP TABLE IF EXISTS `rss_schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rss_schedules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_secure` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `team_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `feed_url` varchar(2000) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `account_ids` json NOT NULL,
  `settings` json DEFAULT NULL,
  `time_posts` json NOT NULL,
  `weekdays` json NOT NULL,
  `start_at` int unsigned DEFAULT NULL,
  `end_at` int unsigned DEFAULT NULL,
  `last_checked_at` int unsigned DEFAULT NULL,
  `last_queued_at` int unsigned DEFAULT NULL,
  `next_run_at` int unsigned DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `changed` int unsigned DEFAULT NULL,
  `created` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rss_schedules_id_secure_unique` (`id_secure`),
  KEY `rss_schedules_user_id_status_index` (`user_id`,`status`),
  KEY `rss_schedules_team_id_status_index` (`team_id`,`status`),
  KEY `rss_schedules_next_run_at_status_index` (`next_run_at`,`status`),
  CONSTRAINT `rss_schedules_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE SET NULL,
  CONSTRAINT `rss_schedules_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rss_schedules`
--

LOCK TABLES `rss_schedules` WRITE;
/*!40000 ALTER TABLE `rss_schedules` DISABLE KEYS */;
/*!40000 ALTER TABLE `rss_schedules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `social_accounts`
--

DROP TABLE IF EXISTS `social_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `social_accounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `provider_key` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `capability_key` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `display_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `external_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'manual',
  `profile_url` text COLLATE utf8mb4_unicode_ci,
  `avatar_url` text COLLATE utf8mb4_unicode_ci,
  `avatar_disk` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar_path` text COLLATE utf8mb4_unicode_ci,
  `reconnect_url` text COLLATE utf8mb4_unicode_ci,
  `access_token` text COLLATE utf8mb4_unicode_ci,
  `refresh_token` text COLLATE utf8mb4_unicode_ci,
  `scopes` text COLLATE utf8mb4_unicode_ci,
  `auth_data` json DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by_user_id` bigint unsigned DEFAULT NULL,
  `connected_at` timestamp NULL DEFAULT NULL,
  `last_synced_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `social_accounts_created_by_user_id_foreign` (`created_by_user_id`),
  KEY `social_accounts_provider_key_index` (`provider_key`),
  KEY `social_accounts_username_index` (`username`),
  KEY `social_accounts_external_id_index` (`external_id`),
  KEY `social_accounts_is_active_index` (`is_active`),
  KEY `social_accounts_capability_key_index` (`capability_key`),
  CONSTRAINT `social_accounts_created_by_user_id_foreign` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `social_accounts`
--

LOCK TABLES `social_accounts` WRITE;
/*!40000 ALTER TABLE `social_accounts` DISABLE KEYS */;
/*!40000 ALTER TABLE `social_accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `support_categories`
--

DROP TABLE IF EXISTS `support_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `support_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_secure` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fa-light',
  `color` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#2563eb',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `changed` int unsigned DEFAULT NULL,
  `created` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `support_categories_id_secure_unique` (`id_secure`)
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `support_categories`
--

LOCK TABLES `support_categories` WRITE;
/*!40000 ALTER TABLE `support_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `support_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `support_comments`
--

DROP TABLE IF EXISTS `support_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `support_comments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_secure` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ticket_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `changed` int unsigned DEFAULT NULL,
  `created` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `support_comments_id_secure_unique` (`id_secure`),
  KEY `support_comments_ticket_id_foreign` (`ticket_id`),
  KEY `support_comments_user_id_foreign` (`user_id`),
  CONSTRAINT `support_comments_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `support_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `support_comments`
--

LOCK TABLES `support_comments` WRITE;
/*!40000 ALTER TABLE `support_comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `support_comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `support_labels`
--

DROP TABLE IF EXISTS `support_labels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `support_labels` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_secure` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fa-light',
  `color` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#475569',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `changed` int unsigned DEFAULT NULL,
  `created` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `support_labels_id_secure_unique` (`id_secure`)
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `support_labels`
--

LOCK TABLES `support_labels` WRITE;
/*!40000 ALTER TABLE `support_labels` DISABLE KEYS */;
/*!40000 ALTER TABLE `support_labels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `support_map_labels`
--

DROP TABLE IF EXISTS `support_map_labels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `support_map_labels` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` bigint unsigned NOT NULL,
  `label_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `support_map_labels_ticket_id_label_id_unique` (`ticket_id`,`label_id`),
  KEY `support_map_labels_label_id_foreign` (`label_id`),
  CONSTRAINT `support_map_labels_label_id_foreign` FOREIGN KEY (`label_id`) REFERENCES `support_labels` (`id`) ON DELETE CASCADE,
  CONSTRAINT `support_map_labels_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `support_map_labels`
--

LOCK TABLES `support_map_labels` WRITE;
/*!40000 ALTER TABLE `support_map_labels` DISABLE KEYS */;
/*!40000 ALTER TABLE `support_map_labels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `support_tickets`
--

DROP TABLE IF EXISTS `support_tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `support_tickets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_secure` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uid` bigint unsigned NOT NULL,
  `open_by` bigint unsigned NOT NULL,
  `team_id` bigint unsigned DEFAULT NULL,
  `cate_id` bigint unsigned DEFAULT NULL,
  `type_id` bigint unsigned DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint unsigned NOT NULL DEFAULT '1',
  `pin` tinyint(1) NOT NULL DEFAULT '0',
  `user_read` tinyint(1) NOT NULL DEFAULT '0',
  `admin_read` tinyint(1) NOT NULL DEFAULT '1',
  `changed` int unsigned DEFAULT NULL,
  `created` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `support_tickets_id_secure_unique` (`id_secure`),
  KEY `support_tickets_uid_foreign` (`uid`),
  KEY `support_tickets_open_by_foreign` (`open_by`),
  KEY `support_tickets_cate_id_foreign` (`cate_id`),
  KEY `support_tickets_type_id_foreign` (`type_id`),
  KEY `support_tickets_team_id_index` (`team_id`),
  CONSTRAINT `support_tickets_cate_id_foreign` FOREIGN KEY (`cate_id`) REFERENCES `support_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `support_tickets_open_by_foreign` FOREIGN KEY (`open_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `support_tickets_type_id_foreign` FOREIGN KEY (`type_id`) REFERENCES `support_types` (`id`) ON DELETE SET NULL,
  CONSTRAINT `support_tickets_uid_foreign` FOREIGN KEY (`uid`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `support_tickets`
--

LOCK TABLES `support_tickets` WRITE;
/*!40000 ALTER TABLE `support_tickets` DISABLE KEYS */;
/*!40000 ALTER TABLE `support_tickets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `support_types`
--

DROP TABLE IF EXISTS `support_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `support_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_secure` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fa-light',
  `color` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#2563eb',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `changed` int unsigned DEFAULT NULL,
  `created` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `support_types_id_secure_unique` (`id_secure`)
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `support_types`
--

LOCK TABLES `support_types` WRITE;
/*!40000 ALTER TABLE `support_types` DISABLE KEYS */;
/*!40000 ALTER TABLE `support_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `team_activity_logs`
--

DROP TABLE IF EXISTS `team_activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `team_activity_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint unsigned DEFAULT NULL,
  `owner_user_id` bigint unsigned DEFAULT NULL,
  `actor_user_id` bigint unsigned DEFAULT NULL,
  `subject_type` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject_id` bigint unsigned DEFAULT NULL,
  `action` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `team_activity_logs_subject_type_subject_id_index` (`subject_type`,`subject_id`),
  KEY `team_activity_logs_team_id_index` (`team_id`),
  KEY `team_activity_logs_owner_user_id_index` (`owner_user_id`),
  KEY `team_activity_logs_actor_user_id_index` (`actor_user_id`),
  CONSTRAINT `team_activity_logs_actor_user_id_foreign` FOREIGN KEY (`actor_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `team_activity_logs_owner_user_id_foreign` FOREIGN KEY (`owner_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `team_activity_logs_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=147123469 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `team_activity_logs`
--

LOCK TABLES `team_activity_logs` WRITE;
/*!40000 ALTER TABLE `team_activity_logs` DISABLE KEYS */;
INSERT INTO `team_activity_logs` VALUES (147123468,147123469,147123470,147123471,'Modules\\AppCustomDomain\\Models\\AppCustomDomain',147123468,'domain.created','{\"scope\": \"shared\", \"domain\": \"quannhauminhdung.vn\"}','2026-06-07 13:03:46','2026-06-07 13:03:46');
/*!40000 ALTER TABLE `team_activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `team_conversation_participants`
--

DROP TABLE IF EXISTS `team_conversation_participants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `team_conversation_participants` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `conversation_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `role` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'member',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `team_conversation_participants_conversation_id_user_id_unique` (`conversation_id`,`user_id`),
  KEY `team_conversation_participants_user_id_foreign` (`user_id`),
  CONSTRAINT `team_conversation_participants_conversation_id_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `team_conversations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `team_conversation_participants_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147123470 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `team_conversation_participants`
--

LOCK TABLES `team_conversation_participants` WRITE;
/*!40000 ALTER TABLE `team_conversation_participants` DISABLE KEYS */;
INSERT INTO `team_conversation_participants` VALUES (147123468,147123468,147123470,'owner','2026-06-07 12:54:05','2026-06-07 12:54:05'),(147123469,147123468,147123471,'member','2026-06-07 12:54:05','2026-06-07 12:54:05');
/*!40000 ALTER TABLE `team_conversation_participants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `team_conversations`
--

DROP TABLE IF EXISTS `team_conversations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `team_conversations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint unsigned NOT NULL,
  `created_by_user_id` bigint unsigned DEFAULT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'room',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `last_message_at` timestamp NULL DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `team_conversations_created_by_user_id_foreign` (`created_by_user_id`),
  KEY `team_conversations_team_id_last_message_at_index` (`team_id`,`last_message_at`),
  KEY `team_conversations_team_id_index` (`team_id`),
  CONSTRAINT `team_conversations_created_by_user_id_foreign` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=147123469 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `team_conversations`
--

LOCK TABLES `team_conversations` WRITE;
/*!40000 ALTER TABLE `team_conversations` DISABLE KEYS */;
INSERT INTO `team_conversations` VALUES (147123468,147123469,147123471,'room','2222','Content planning','2026-06-07 12:54:20',NULL,'2026-06-07 12:54:05','2026-06-07 12:54:20');
/*!40000 ALTER TABLE `team_conversations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `team_invitations`
--

DROP TABLE IF EXISTS `team_invitations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `team_invitations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint unsigned NOT NULL,
  `invited_by_user_id` bigint unsigned DEFAULT NULL,
  `accepted_by_user_id` bigint unsigned DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `invite_code` varchar(24) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'member',
  `permissions` json DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `message` text COLLATE utf8mb4_unicode_ci,
  `expires_at` timestamp NULL DEFAULT NULL,
  `accepted_at` timestamp NULL DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `team_invitations_invite_code_unique` (`invite_code`),
  KEY `team_invitations_invited_by_user_id_foreign` (`invited_by_user_id`),
  KEY `team_invitations_accepted_by_user_id_foreign` (`accepted_by_user_id`),
  KEY `team_invitations_team_id_status_index` (`team_id`,`status`),
  KEY `team_invitations_team_id_index` (`team_id`),
  CONSTRAINT `team_invitations_accepted_by_user_id_foreign` FOREIGN KEY (`accepted_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `team_invitations_invited_by_user_id_foreign` FOREIGN KEY (`invited_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=147123469 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `team_invitations`
--

LOCK TABLES `team_invitations` WRITE;
/*!40000 ALTER TABLE `team_invitations` DISABLE KEYS */;
INSERT INTO `team_invitations` VALUES (147123468,147123469,147123470,147123471,'thihoanglinhnguyen16082004@gmail.com','V9SARG8G','member','[\"chat.participate\", \"team.manage\", \"member.manage\", \"chat.manage\"]','accepted','Mời anh vào team em','2026-06-14 12:53:10','2026-06-07 12:53:47','{\"delivery\": \"email\", \"managed_account_ids\": []}','2026-06-07 12:53:10','2026-06-07 12:53:47');
/*!40000 ALTER TABLE `team_invitations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `team_messages`
--

DROP TABLE IF EXISTS `team_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `team_messages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `conversation_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `body` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attachments` json DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `team_messages_user_id_foreign` (`user_id`),
  KEY `team_messages_conversation_id_created_at_index` (`conversation_id`,`created_at`),
  CONSTRAINT `team_messages_conversation_id_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `team_conversations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `team_messages_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=147123470 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `team_messages`
--

LOCK TABLES `team_messages` WRITE;
/*!40000 ALTER TABLE `team_messages` DISABLE KEYS */;
INSERT INTO `team_messages` VALUES (147123468,147123468,147123471,'hi',NULL,'2026-06-07 12:54:05',NULL,'2026-06-07 12:54:05','2026-06-07 12:54:05'),(147123469,147123468,147123470,'hiiii',NULL,'2026-06-07 12:54:20',NULL,'2026-06-07 12:54:20','2026-06-07 12:54:20');
/*!40000 ALTER TABLE `team_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `team_post_comments`
--

DROP TABLE IF EXISTS `team_post_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `team_post_comments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint unsigned NOT NULL,
  `post_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `message` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `team_post_comments_post_id_foreign` (`post_id`),
  KEY `team_post_comments_user_id_foreign` (`user_id`),
  KEY `team_post_comments_team_id_post_id_index` (`team_id`,`post_id`),
  KEY `team_post_comments_team_id_index` (`team_id`),
  CONSTRAINT `team_post_comments_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `team_post_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `team_post_comments`
--

LOCK TABLES `team_post_comments` WRITE;
/*!40000 ALTER TABLE `team_post_comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `team_post_comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `team_post_reviews`
--

DROP TABLE IF EXISTS `team_post_reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `team_post_reviews` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint unsigned NOT NULL,
  `post_id` bigint unsigned NOT NULL,
  `submitted_by_user_id` bigint unsigned DEFAULT NULL,
  `decided_by_user_id` bigint unsigned DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `decision_note` text COLLATE utf8mb4_unicode_ci,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `decided_at` timestamp NULL DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `team_post_reviews_submitted_by_user_id_foreign` (`submitted_by_user_id`),
  KEY `team_post_reviews_decided_by_user_id_foreign` (`decided_by_user_id`),
  KEY `team_post_reviews_team_id_status_index` (`team_id`,`status`),
  KEY `team_post_reviews_post_id_status_index` (`post_id`,`status`),
  KEY `team_post_reviews_team_id_index` (`team_id`),
  CONSTRAINT `team_post_reviews_decided_by_user_id_foreign` FOREIGN KEY (`decided_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `team_post_reviews_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `team_post_reviews_submitted_by_user_id_foreign` FOREIGN KEY (`submitted_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=147123468 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `team_post_reviews`
--

LOCK TABLES `team_post_reviews` WRITE;
/*!40000 ALTER TABLE `team_post_reviews` DISABLE KEYS */;
/*!40000 ALTER TABLE `team_post_reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `team_user`
--

DROP TABLE IF EXISTS `team_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `team_user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `role` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'member',
  `permissions` json DEFAULT NULL,
  `managed_account_ids` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `team_user_team_id_user_id_unique` (`team_id`,`user_id`),
  KEY `team_user_user_id_index` (`user_id`),
  KEY `team_user_team_id_index` (`team_id`),
  CONSTRAINT `team_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147123473 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `team_user`
--

LOCK TABLES `team_user` WRITE;
/*!40000 ALTER TABLE `team_user` DISABLE KEYS */;
INSERT INTO `team_user` VALUES (147123468,147123468,147123468,'owner','[\"team.manage\", \"member.manage\", \"post.approve\", \"post.publish\", \"chat.manage\"]',NULL,'2026-06-06 15:58:01','2026-06-06 15:58:01'),(147123469,147123469,147123469,'owner','[\"team.manage\", \"member.manage\", \"post.approve\", \"post.publish\", \"chat.manage\"]',NULL,'2026-06-06 17:14:44','2026-06-06 17:14:44'),(147123470,147123470,147123470,'owner','[\"team.manage\", \"member.manage\", \"post.approve\", \"post.publish\", \"chat.manage\"]',NULL,'2026-06-07 11:15:14','2026-06-07 11:15:14'),(147123471,147123471,147123471,'owner','[\"team.manage\", \"member.manage\", \"post.approve\", \"post.publish\", \"chat.manage\"]',NULL,'2026-06-07 11:45:13','2026-06-07 11:45:13'),(147123472,147123470,147123471,'member','[\"chat.participate\", \"team.manage\", \"member.manage\", \"chat.manage\"]','[]','2026-06-07 12:53:47','2026-06-07 12:53:47');
/*!40000 ALTER TABLE `team_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `teams`
--

DROP TABLE IF EXISTS `teams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `teams` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `enabled_modules` json DEFAULT NULL,
  `owner_user_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `teams_slug_unique` (`slug`),
  UNIQUE KEY `teams_owner_user_id_unique` (`owner_user_id`),
  CONSTRAINT `teams_owner_user_id_foreign` FOREIGN KEY (`owner_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=147123472 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teams`
--

LOCK TABLES `teams` WRITE;
/*!40000 ALTER TABLE `teams` DISABLE KEYS */;
INSERT INTO `teams` VALUES (147123468,'Nhóm của MLHUB Admin','mlhub-team','Nhóm mặc định của MLHUB Admin',NULL,147123468,'2026-06-06 15:58:01','2026-06-06 15:58:01'),(147123469,'Nhóm của Khoa MLHUB','khoa-mlhub-team','Nhóm mặc định của Khoa MLHUB',NULL,147123469,'2026-06-06 17:14:44','2026-06-06 17:14:44'),(147123470,'Giang Nguyen (Helen)\'s Team','giang-nguyen-helen-team','Default team for Giang Nguyen (Helen)',NULL,147123470,'2026-06-07 11:15:14','2026-06-07 11:15:14'),(147123471,'Nhóm của Hoàng Linh Nguyễn','hoang-linh-nguyen-team','Nhóm mặc định của Hoàng Linh Nguyễn',NULL,147123471,'2026-06-07 11:45:13','2026-06-07 11:45:13');
/*!40000 ALTER TABLE `teams` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar_disk` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `locale` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `timezone` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role_id` bigint unsigned DEFAULT NULL,
  `is_super_admin` tinyint(1) NOT NULL DEFAULT '0',
  `plan_id` bigint unsigned DEFAULT NULL,
  `next_plan_id` bigint unsigned DEFAULT NULL,
  `plan_started_at` timestamp NULL DEFAULT NULL,
  `plan_expires_at` timestamp NULL DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `two_factor_secret` text COLLATE utf8mb4_unicode_ci,
  `two_factor_recovery_codes` text COLLATE utf8mb4_unicode_ci,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referral_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referred_by_user_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_username_unique` (`username`),
  UNIQUE KEY `users_referral_code_unique` (`referral_code`),
  KEY `users_role_id_foreign` (`role_id`),
  KEY `users_plan_id_foreign` (`plan_id`),
  KEY `users_referred_by_user_id_foreign` (`referred_by_user_id`),
  KEY `users_next_plan_id_foreign` (`next_plan_id`),
  CONSTRAINT `users_next_plan_id_foreign` FOREIGN KEY (`next_plan_id`) REFERENCES `plans` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_plan_id_foreign` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_referred_by_user_id_foreign` FOREIGN KEY (`referred_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `admin_roles` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=147123472 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (147123468,'MLHUB Admin','mlhub','admin@mlhub.vn',NULL,NULL,'vi','Asia/Ho_Chi_Minh',NULL,1,147123476,NULL,'2026-06-06 15:58:01','2026-08-05 15:58:01','2026-06-06 15:58:01','$2y$12$OMHUv1UttKX214QIOF9O5ek3KRUxqQDZfEmDUH7MsReQZFUZtTnli',NULL,NULL,NULL,'0WnLQlGZnbDJluHezOiAx8IHDAy7vPMy3TZTwteWq3itERx55BUmBZNi1hDC','MLHUBMBWI',NULL,'2026-06-06 15:58:01','2026-06-06 15:58:01'),(147123469,'Khoa MLHUB','khoa-mlhub','khoa.mlhub@gmail.com',NULL,NULL,NULL,'Asia/Ho_Chi_Minh',NULL,0,NULL,NULL,NULL,NULL,'2026-06-06 17:14:45','$2y$12$70EuueFIudMYCaFGZmFDCOgdJl9/FtHqbf/qE4engp3V6fONoXN5C',NULL,NULL,NULL,'rKpjpQcw53cJJ0195l37y684lDkkKN0LkOfvNLsP2c3pgu4UFXC2u4kiuYEs','KHOAMLHUC4CE',NULL,'2026-06-06 17:14:44','2026-06-06 18:32:25'),(147123470,'Giang Nguyen (Helen)','giang-nguyen-helen','ntqgiang268@gmail.com',NULL,NULL,NULL,'Asia/Ho_Chi_Minh',NULL,0,NULL,NULL,NULL,NULL,'2026-06-07 11:15:14','$2y$12$uf2oHxKwwMqj2R5lG1oY9u9PvIxUCYJGDUfOdlZaGnvuVE8LjKrHi',NULL,NULL,NULL,'ZpS13q2fk7QNucYoJw5mJf35XY7ejacq3KiPZFEQK3iXxxPFH0pBudmDCm9B','GIANGNGUANCD',NULL,'2026-06-07 11:15:14','2026-06-07 11:15:14'),(147123471,'Hoàng Linh Nguyễn','hoang-linh-nguyen','thihoanglinhnguyen16082004@gmail.com',NULL,NULL,'en','Asia/Ho_Chi_Minh',NULL,0,NULL,NULL,NULL,NULL,'2026-06-07 11:45:13','$2y$12$OvUOA5RTStE3fEs5ta/4yOaZ9b2N/zFYp/xbGMnDLJXzwWku271vK',NULL,NULL,NULL,'RTNvbcINtvIx3yDgYvtx08hgvHKhPiOWhF665sVMkx7PGbYIQ8UxJK9RPXmC','HOANGLINQQQM',NULL,'2026-06-07 11:45:13','2026-06-07 11:45:28');
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

-- Dump completed on 2026-06-08  7:09:26
