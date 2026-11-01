-- SIK production seed: skema penuh + master data + 9 akun (tanpa pengajuan/audit uji)
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;

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
DROP TABLE IF EXISTS `application_approval_steps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `application_approval_steps` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `permit_application_id` bigint(20) unsigned NOT NULL,
  `stage` tinyint(3) unsigned NOT NULL,
  `approver_title` varchar(255) NOT NULL,
  `approver_user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `application_approval_steps_permit_application_id_stage_unique` (`permit_application_id`,`stage`),
  KEY `application_approval_steps_approver_user_id_index` (`approver_user_id`),
  CONSTRAINT `application_approval_steps_approver_user_id_foreign` FOREIGN KEY (`approver_user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `application_approval_steps_permit_application_id_foreign` FOREIGN KEY (`permit_application_id`) REFERENCES `permit_applications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `application_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `application_documents` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `permit_application_id` bigint(20) unsigned NOT NULL,
  `kind` varchar(12) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `path` varchar(255) NOT NULL,
  `mime` varchar(100) DEFAULT NULL,
  `size` bigint(20) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `application_documents_permit_application_id_foreign` (`permit_application_id`),
  CONSTRAINT `application_documents_permit_application_id_foreign` FOREIGN KEY (`permit_application_id`) REFERENCES `permit_applications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `application_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `application_histories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `permit_application_id` bigint(20) unsigned NOT NULL,
  `status` varchar(20) NOT NULL,
  `stage` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `actor_id` bigint(20) unsigned DEFAULT NULL,
  `actor_name` varchar(255) NOT NULL,
  `actor_role` varchar(255) NOT NULL,
  `decision` varchar(255) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `application_histories_actor_id_foreign` (`actor_id`),
  KEY `application_histories_permit_application_id_created_at_index` (`permit_application_id`,`created_at`),
  CONSTRAINT `application_histories_actor_id_foreign` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `application_histories_permit_application_id_foreign` FOREIGN KEY (`permit_application_id`) REFERENCES `permit_applications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `application_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `application_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `permit_application_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `brand` varchar(255) DEFAULT NULL,
  `model` varchar(255) DEFAULT NULL,
  `serial_number` varchar(255) DEFAULT NULL,
  `asset_number` varchar(255) DEFAULT NULL,
  `quantity` int(10) unsigned NOT NULL,
  `condition` varchar(20) NOT NULL DEFAULT 'good',
  `movement` varchar(10) NOT NULL DEFAULT 'IN',
  `frequency` varchar(10) NOT NULL DEFAULT 'One-time',
  `is_consumable` tinyint(1) NOT NULL DEFAULT 0,
  `photo_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `application_items_permit_application_id_foreign` (`permit_application_id`),
  CONSTRAINT `application_items_permit_application_id_foreign` FOREIGN KEY (`permit_application_id`) REFERENCES `permit_applications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `application_personnel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `application_personnel` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `permit_application_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `id_number` varchar(255) NOT NULL,
  `vehicle_plate` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `application_personnel_permit_application_id_foreign` (`permit_application_id`),
  CONSTRAINT `application_personnel_permit_application_id_foreign` FOREIGN KEY (`permit_application_id`) REFERENCES `permit_applications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `areas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `areas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(10) NOT NULL,
  `name` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `areas_code_unique` (`code`),
  UNIQUE KEY `areas_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `user_name` varchar(255) NOT NULL,
  `role` varchar(255) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `target` varchar(255) DEFAULT NULL,
  `detail` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `audit_logs_user_id_foreign` (`user_id`),
  KEY `audit_logs_target_index` (`target`),
  CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=295 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `departments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(10) NOT NULL,
  `name` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `departments_code_unique` (`code`),
  UNIQUE KEY `departments_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
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
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
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
DROP TABLE IF EXISTS `permit_applications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permit_applications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `number` varchar(20) NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `applicant_name` varchar(255) DEFAULT NULL,
  `contact_name` varchar(255) DEFAULT NULL,
  `job_title` varchar(255) DEFAULT NULL,
  `area_id` bigint(20) unsigned DEFAULT NULL,
  `work_type_id` bigint(20) unsigned DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'DRAFT',
  `current_stage` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `total_stages` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `correction_note` text DEFAULT NULL,
  `permit_number` varchar(18) DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permit_applications_number_unique` (`number`),
  UNIQUE KEY `permit_applications_permit_number_unique` (`permit_number`),
  KEY `permit_applications_user_id_foreign` (`user_id`),
  KEY `permit_applications_area_id_foreign` (`area_id`),
  KEY `permit_applications_work_type_id_foreign` (`work_type_id`),
  KEY `permit_applications_status_created_at_index` (`status`,`created_at`),
  KEY `permit_applications_status_index` (`status`),
  CONSTRAINT `permit_applications_area_id_foreign` FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`),
  CONSTRAINT `permit_applications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `permit_applications_work_type_id_foreign` FOREIGN KEY (`work_type_id`) REFERENCES `work_types` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'applicant',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `department_id` bigint(20) unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `notifications_read_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_username_unique` (`username`),
  KEY `users_role_index` (`role`),
  KEY `users_department_id_index` (`department_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `work_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `work_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(10) NOT NULL,
  `name` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `work_types_code_unique` (`code`),
  UNIQUE KEY `work_types_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `workflow_template_steps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `workflow_template_steps` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `workflow_template_id` bigint(20) unsigned NOT NULL,
  `stage` tinyint(3) unsigned NOT NULL,
  `approver_title` varchar(255) NOT NULL,
  `approver_user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `workflow_template_steps_workflow_template_id_stage_unique` (`workflow_template_id`,`stage`),
  KEY `workflow_template_steps_approver_user_id_foreign` (`approver_user_id`),
  CONSTRAINT `workflow_template_steps_approver_user_id_foreign` FOREIGN KEY (`approver_user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `workflow_template_steps_workflow_template_id_foreign` FOREIGN KEY (`workflow_template_id`) REFERENCES `workflow_templates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `workflow_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `workflow_templates` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `area_id` bigint(20) unsigned NOT NULL,
  `work_type_id` bigint(20) unsigned NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `workflow_templates_area_id_work_type_id_unique` (`area_id`,`work_type_id`),
  KEY `workflow_templates_work_type_id_foreign` (`work_type_id`),
  CONSTRAINT `workflow_templates_area_id_foreign` FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`),
  CONSTRAINT `workflow_templates_work_type_id_foreign` FOREIGN KEY (`work_type_id`) REFERENCES `work_types` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;



/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_01_01_000001_create_sik_master_tables',1),(5,'2026_01_01_000002_add_sik_fields_to_users_table',1),(6,'2026_01_01_000003_create_workflow_template_tables',1),(7,'2026_01_01_000004_create_permit_application_tables',1);

INSERT INTO `users` (`id`, `name`, `username`, `email`, `email_verified_at`, `password`, `role`, `remember_token`, `created_at`, `updated_at`, `department_id`, `is_active`, `notifications_read_at`) VALUES (1,'Aqil','applicant',NULL,NULL,'$2y$12$axMnXuObcXkI4fdkT654kegbrrtqqslkI3ohhml2mf.x/8JKKjXei','applicant',NULL,'2026-10-24 04:24:30','2026-10-24 20:07:47',1,1,NULL),(2,'Antonius Dwi','admin',NULL,NULL,'$2y$12$YfR.oxi9qDLaL4dWh8Erv.iWtz7Vv4yE2Ft4YvxfWfFDOecNPbKxi','admin',NULL,'2026-10-24 04:24:30','2026-10-24 20:07:48',1,1,NULL),(3,'Fadhlullah','approver',NULL,NULL,'$2y$12$3RxBy9djcHSlyW4XhJD8x.VWWnOkV2AT2IIflxeNAOHN5IX7TIPiS','approver',NULL,'2026-10-24 04:24:31','2026-10-24 20:07:48',1,1,NULL),(4,'Afrizal','approver2',NULL,NULL,'$2y$12$0ZipWKA/XdMreUBJM0b/1u.LLP4T4MtBNDkgrnoxQC0CKb3cIuzMK','approver',NULL,'2026-10-24 04:24:31','2026-10-24 20:07:48',1,1,NULL),(5,'Adhiansyah','approver3',NULL,NULL,'$2y$12$B8KVDkp6oi.riNbLuboIs.xSLWBSN12RmVmt4nPkgLkOYGELiKy1K','approver',NULL,'2026-10-24 04:24:31','2026-10-24 20:07:48',2,1,NULL),(6,'Prido','approver4',NULL,NULL,'$2y$12$LVNneRdHQPh3P5VLaZvfQ.J06k3aYZZqITHGZ6QWfQAHR1ZdXmtau','approver',NULL,'2026-10-24 04:24:32','2026-10-24 20:07:49',2,1,NULL),(7,'Nur Aini','approver5',NULL,NULL,'$2y$12$MMybwPujBeIkqrfe0Esqyutd9P1Nj8caYOfzNLs/DnaTg8hS8KjhS','approver',NULL,'2026-10-24 04:24:32','2026-10-24 20:07:49',3,1,NULL),(8,'Kusumawardhani','management',NULL,NULL,'$2y$12$aVOGIiLe79FMN9o0XL2oYODjtP1TaXW/i7.PUFBxBXPkxYnV4G8IS','management',NULL,'2026-10-24 04:24:32','2026-10-24 20:07:49',3,1,NULL),(9,'Muhammad Mustar','sysadmin',NULL,NULL,'$2y$12$Mp4kIKFUBIisUfaQbs6KfuOP51M5SGCdhYnuBYj9Qc5xjhZUwjCLO','system-admin',NULL,'2026-10-24 04:24:33','2026-10-24 20:07:49',3,1,NULL);

INSERT INTO `departments` (`id`, `code`, `name`, `is_active`, `created_at`, `updated_at`) VALUES (1,'OPS','Operasi Sisi Udara',1,'2026-10-24 04:24:29','2026-10-24 04:24:29'),(2,'TEC','Teknik Bandara',1,'2026-10-24 04:24:29','2026-10-24 04:24:29'),(3,'MGT','Manajemen',1,'2026-10-24 04:24:29','2026-10-24 04:24:29');

INSERT INTO `areas` (`id`, `code`, `name`, `is_active`, `created_at`, `updated_at`) VALUES (1,'APN','Apron',1,'2026-10-24 04:24:29','2026-10-24 04:24:29'),(2,'RWY','Runway',1,'2026-10-24 04:24:29','2026-10-24 04:24:29'),(3,'TWY','Taxiway',1,'2026-10-24 04:24:29','2026-10-24 04:24:29'),(4,'TER','Terminal',1,'2026-10-24 04:24:29','2026-10-24 04:24:29'),(5,'HGR','Hanggar',1,'2026-10-24 04:24:29','2026-10-24 04:24:29');

INSERT INTO `work_types` (`id`, `code`, `name`, `is_active`, `created_at`, `updated_at`) VALUES (1,'MNT','Pemeliharaan',1,'2026-10-24 04:24:29','2026-10-24 04:24:29'),(2,'CON','Konstruksi',1,'2026-10-24 04:24:29','2026-10-24 04:24:29'),(3,'INS','Inspeksi',1,'2026-10-24 04:24:29','2026-10-24 04:24:29'),(4,'CLN','Pembersihan',1,'2026-10-24 04:24:29','2026-10-24 04:24:29'),(5,'OTH','Lainnya',1,'2026-10-24 04:24:29','2026-10-24 04:24:29');

INSERT INTO `workflow_templates` (`id`, `area_id`, `work_type_id`, `is_active`, `created_at`, `updated_at`) VALUES (1,1,1,1,'2026-10-24 04:24:33','2026-10-26 06:12:00'),(2,3,2,1,'2026-10-24 04:24:33','2026-10-24 04:24:33'),(3,3,1,1,'2026-10-24 04:24:33','2026-10-24 04:24:33'),(4,1,3,1,'2026-10-24 04:24:34','2026-10-24 04:24:34'),(5,2,1,1,'2026-10-24 04:24:34','2026-10-24 04:24:34'),(6,4,3,1,'2026-10-24 04:24:34','2026-10-24 04:24:34'),(7,4,1,1,'2026-10-24 04:24:34','2026-10-24 04:24:34'),(8,1,4,1,'2026-10-24 04:24:34','2026-10-24 04:24:34');

INSERT INTO `workflow_template_steps` (`id`, `workflow_template_id`, `stage`, `approver_title`, `approver_user_id`, `created_at`, `updated_at`) VALUES (1,1,1,'Koordinator Operasi',4,'2026-10-24 04:24:33','2026-10-24 04:24:33'),(2,1,2,'Manajer Operasi Sisi Udara',3,'2026-10-24 04:24:33','2026-10-24 04:24:33'),(3,1,3,'Kepala Bandara',7,'2026-10-24 04:24:33','2026-10-24 04:24:33'),(4,2,1,'Koordinator Teknik',5,'2026-10-24 04:24:33','2026-10-24 04:24:33'),(5,2,2,'Manajer Teknik',6,'2026-10-24 04:24:33','2026-10-24 04:24:33'),(6,2,3,'Manajer Operasi Sisi Udara',3,'2026-10-24 04:24:33','2026-10-24 04:24:33'),(7,2,4,'Kepala Bandara',7,'2026-10-24 04:24:33','2026-10-24 04:24:33'),(8,3,1,'Koordinator Teknik',5,'2026-10-24 04:24:34','2026-10-24 04:24:34'),(9,3,2,'Manajer Operasi Sisi Udara',3,'2026-10-24 04:24:34','2026-10-24 04:24:34'),(10,3,3,'Kepala Bandara',7,'2026-10-24 04:24:34','2026-10-24 04:24:34'),(11,4,1,'Koordinator Operasi',4,'2026-10-24 04:24:34','2026-10-24 04:24:34'),(12,4,2,'Manajer Operasi Sisi Udara',3,'2026-10-24 04:24:34','2026-10-24 04:24:34'),(13,5,1,'Koordinator Teknik',5,'2026-10-24 04:24:34','2026-10-24 04:24:34'),(14,5,2,'Manajer Teknik',6,'2026-10-24 04:24:34','2026-10-24 04:24:34'),(15,5,3,'Kepala Bandara',7,'2026-10-24 04:24:34','2026-10-24 04:24:34'),(16,6,1,'Koordinator Operasi',4,'2026-10-24 04:24:34','2026-10-24 04:24:34'),(17,6,2,'Manajer Operasi Sisi Udara',3,'2026-10-24 04:24:34','2026-10-24 04:24:34'),(18,7,1,'Koordinator Teknik',5,'2026-10-24 04:24:34','2026-10-24 04:24:34'),(19,7,2,'Manajer Teknik',6,'2026-10-24 04:24:34','2026-10-24 04:24:34'),(20,7,3,'Kepala Bandara',7,'2026-10-24 04:24:34','2026-10-24 04:24:34'),(21,8,1,'Koordinator Operasi',4,'2026-10-24 04:24:34','2026-10-24 04:24:34'),(22,8,2,'Manajer Operasi Sisi Udara',3,'2026-10-24 04:24:34','2026-10-24 04:24:34');
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

SET FOREIGN_KEY_CHECKS=1;
