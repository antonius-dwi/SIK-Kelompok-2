-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 23, 2026 at 07:10 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sik`
--

-- --------------------------------------------------------

--
-- Table structure for table `application_approval_steps`
--

CREATE TABLE `application_approval_steps` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `permit_application_id` bigint(20) UNSIGNED NOT NULL,
  `stage` tinyint(3) UNSIGNED NOT NULL,
  `approver_title` varchar(255) NOT NULL,
  `approver_user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `application_approval_steps`
--

INSERT INTO `application_approval_steps` (`id`, `permit_application_id`, `stage`, `approver_title`, `approver_user_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Koordinator Teknik', 5, '2026-10-23 07:03:07', '2026-10-23 07:03:07'),
(2, 1, 2, 'Manajer Teknik', 6, '2026-10-23 07:03:07', '2026-10-23 07:03:07'),
(3, 1, 3, 'Kepala Bandara', 7, '2026-10-23 07:03:07', '2026-10-23 07:03:07');

-- --------------------------------------------------------

--
-- Table structure for table `application_documents`
--

CREATE TABLE `application_documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `permit_application_id` bigint(20) UNSIGNED NOT NULL,
  `kind` varchar(12) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `path` varchar(255) NOT NULL,
  `mime` varchar(100) DEFAULT NULL,
  `size` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `application_documents`
--

INSERT INTO `application_documents` (`id`, `permit_application_id`, `kind`, `original_name`, `path`, `mime`, `size`, `created_at`, `updated_at`) VALUES
(1, 1, 'request', 'amplop hadiah.pdf', 'sik/1/documents/McuMROZWRckRiVS8Ghs3pkBR4dNLL6ksU3mk1wwF.pdf', 'application/pdf', 57520, '2026-10-23 06:52:05', '2026-10-23 06:52:05'),
(2, 1, 'attachment', 'amplop hadiah.pdf', 'sik/1/documents/UolIEc80YzWC5aECnlFjQPNcBmWSxxg9B52JbIcm.pdf', 'application/pdf', 57520, '2026-10-23 06:52:05', '2026-10-23 06:52:05');

-- --------------------------------------------------------

--
-- Table structure for table `application_histories`
--

CREATE TABLE `application_histories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `permit_application_id` bigint(20) UNSIGNED NOT NULL,
  `status` varchar(20) NOT NULL,
  `stage` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `actor_id` bigint(20) UNSIGNED DEFAULT NULL,
  `actor_name` varchar(255) NOT NULL,
  `actor_role` varchar(255) NOT NULL,
  `decision` varchar(255) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `application_histories`
--

INSERT INTO `application_histories` (`id`, `permit_application_id`, `status`, `stage`, `actor_id`, `actor_name`, `actor_role`, `decision`, `comment`, `created_at`) VALUES
(1, 1, 'DRAFT', 0, 1, 'Andi Rahman', 'Applicant', 'Simpan draf', 'Draf baru dibuat.', '2026-10-23 06:52:05'),
(2, 1, 'SUBMITTED', 0, 1, 'Andi Rahman', 'Applicant', 'Kirim pengajuan', NULL, '2026-10-23 06:52:05'),
(3, 1, 'VERIFICATION', 0, 2, 'Ratna Puspitasari', 'Admin / Verifier', 'Mulai verifikasi', 'Admin memulai pemeriksaan dokumen.', '2026-10-23 07:02:52'),
(4, 1, 'IN_APPROVAL', 1, 2, 'Ratna Puspitasari', 'Admin / Verifier', 'Teruskan ke persetujuan', 'safety', '2026-10-23 07:03:07'),
(5, 1, 'IN_APPROVAL', 1, 5, 'Budi Santoso', 'Approver', 'APPROVE', 'lanjutkan', '2026-10-23 07:04:42'),
(6, 1, 'IN_APPROVAL', 2, 6, 'Rizky Pratama', 'Approver', 'APPROVE', 'lanjutkan', '2026-10-23 07:06:11'),
(7, 1, 'APPROVED', 3, 7, 'Hendra Wijaya', 'Approver', 'APPROVE', 'ok', '2026-10-23 07:09:15'),
(8, 1, 'ISSUED', 3, 2, 'Ratna Puspitasari', 'Admin / Verifier', 'Terbitkan SIK', 'SIK SIK/AYANI/2026/001 diterbitkan pada 2026-10-24.', '2026-10-23 10:07:47');

-- --------------------------------------------------------

--
-- Table structure for table `application_items`
--

CREATE TABLE `application_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `permit_application_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `brand` varchar(255) DEFAULT NULL,
  `model` varchar(255) DEFAULT NULL,
  `serial_number` varchar(255) DEFAULT NULL,
  `asset_number` varchar(255) DEFAULT NULL,
  `quantity` int(10) UNSIGNED NOT NULL,
  `condition` varchar(20) NOT NULL DEFAULT 'good',
  `movement` varchar(10) NOT NULL DEFAULT 'IN',
  `frequency` varchar(10) NOT NULL DEFAULT 'One-time',
  `is_consumable` tinyint(1) NOT NULL DEFAULT 0,
  `photo_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `application_items`
--

INSERT INTO `application_items` (`id`, `permit_application_id`, `name`, `brand`, `model`, `serial_number`, `asset_number`, `quantity`, `condition`, `movement`, `frequency`, `is_consumable`, `photo_path`, `created_at`, `updated_at`) VALUES
(1, 1, 'Lampu', 'Philip', 't123', '2222', '4343', 2, 'good', 'IN', 'One-time', 0, NULL, '2026-10-23 06:52:05', '2026-10-23 06:52:05');

-- --------------------------------------------------------

--
-- Table structure for table `application_personnel`
--

CREATE TABLE `application_personnel` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `permit_application_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `id_number` varchar(255) NOT NULL,
  `vehicle_plate` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `application_personnel`
--

INSERT INTO `application_personnel` (`id`, `permit_application_id`, `name`, `id_number`, `vehicle_plate`, `created_at`, `updated_at`) VALUES
(1, 1, 'Rizal', '34216534365477', 'f123th', '2026-10-23 06:52:05', '2026-10-23 06:52:05');

-- --------------------------------------------------------

--
-- Table structure for table `areas`
--

CREATE TABLE `areas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(10) NOT NULL,
  `name` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `areas`
--

INSERT INTO `areas` (`id`, `code`, `name`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'APN', 'Apron', 1, '2026-10-23 05:29:26', '2026-10-23 05:29:26'),
(2, 'RWY', 'Runway', 1, '2026-10-23 05:29:26', '2026-10-23 05:29:26'),
(3, 'TWY', 'Taxiway', 1, '2026-10-23 05:29:26', '2026-10-23 05:29:26'),
(4, 'TER', 'Terminal', 1, '2026-10-23 05:29:26', '2026-10-23 05:29:26'),
(5, 'HGR', 'Hanggar', 1, '2026-10-23 05:29:26', '2026-10-23 05:29:26');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_name` varchar(255) NOT NULL,
  `role` varchar(255) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `target` varchar(255) DEFAULT NULL,
  `detail` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `user_name`, `role`, `action`, `target`, `detail`, `created_at`) VALUES
(1, 1, 'Andi Rahman', 'Applicant', 'Login', NULL, NULL, '2026-10-23 05:30:29'),
(2, 1, 'Andi Rahman', 'Applicant', 'Login', NULL, NULL, '2026-10-23 06:14:57'),
(3, 2, 'Ratna Puspitasari', 'Admin / Verifier', 'Login', NULL, NULL, '2026-10-23 06:18:31'),
(4, 3, 'Dwi Wibowo', 'Approver', 'Login', NULL, NULL, '2026-10-23 06:18:58'),
(5, 9, 'Nadia Kusuma', 'System administrator', 'Login', NULL, NULL, '2026-10-23 06:19:33'),
(6, 1, 'Andi Rahman', 'Applicant', 'Login', NULL, NULL, '2026-10-23 06:36:42'),
(7, 1, 'Andi Rahman', 'Applicant', 'Draf dibuat', 'SIK-2026-00001', NULL, '2026-10-23 06:52:05'),
(8, 1, 'Andi Rahman', 'Applicant', 'Status menjadi SUBMITTED', 'SIK-2026-00001', NULL, '2026-10-23 06:52:05'),
(9, 2, 'Ratna Puspitasari', 'Admin / Verifier', 'Login', NULL, NULL, '2026-10-23 07:02:45'),
(10, 2, 'Ratna Puspitasari', 'Admin / Verifier', 'Status menjadi VERIFICATION', 'SIK-2026-00001', 'Admin memulai pemeriksaan dokumen.', '2026-10-23 07:02:52'),
(11, 2, 'Ratna Puspitasari', 'Admin / Verifier', 'Status menjadi IN_APPROVAL', 'SIK-2026-00001', 'safety', '2026-10-23 07:03:07'),
(12, 3, 'Dwi Wibowo', 'Approver', 'Login', NULL, NULL, '2026-10-23 07:03:22'),
(13, 1, 'Andi Rahman', 'Applicant', 'Login', NULL, NULL, '2026-10-23 07:03:37'),
(14, 4, 'Agus Setiawan', 'Approver', 'Login', NULL, NULL, '2026-10-23 07:04:13'),
(15, 5, 'Budi Santoso', 'Approver', 'Login', NULL, NULL, '2026-10-23 07:04:30'),
(16, 5, 'Budi Santoso', 'Approver', 'Status menjadi IN_APPROVAL', 'SIK-2026-00001', 'lanjutkan', '2026-10-23 07:04:42'),
(17, 1, 'Andi Rahman', 'Applicant', 'Login', NULL, NULL, '2026-10-23 07:04:53'),
(18, 5, 'Budi Santoso', 'Approver', 'Login', NULL, NULL, '2026-10-23 07:05:36'),
(19, 6, 'Rizky Pratama', 'Approver', 'Login', NULL, NULL, '2026-10-23 07:06:01'),
(20, 6, 'Rizky Pratama', 'Approver', 'Status menjadi IN_APPROVAL', 'SIK-2026-00001', 'lanjutkan', '2026-10-23 07:06:11'),
(21, 1, 'Andi Rahman', 'Applicant', 'Login', NULL, NULL, '2026-10-23 07:06:21'),
(22, 2, 'Ratna Puspitasari', 'Admin / Verifier', 'Login', NULL, NULL, '2026-10-23 07:08:10'),
(23, 1, 'Andi Rahman', 'Applicant', 'Login', NULL, NULL, '2026-10-23 07:08:34'),
(24, 7, 'Hendra Wijaya', 'Approver', 'Login', NULL, NULL, '2026-10-23 07:09:07'),
(25, 7, 'Hendra Wijaya', 'Approver', 'Status menjadi APPROVED', 'SIK-2026-00001', 'ok', '2026-10-23 07:09:15'),
(26, 1, 'Andi Rahman', 'Applicant', 'Login', NULL, NULL, '2026-10-23 07:09:27'),
(27, 1, 'Andi Rahman', 'Applicant', 'Login', NULL, NULL, '2026-10-23 10:06:21'),
(28, 7, 'Hendra Wijaya', 'Approver', 'Login', NULL, NULL, '2026-10-23 10:07:04'),
(29, 2, 'Ratna Puspitasari', 'Admin / Verifier', 'Login', NULL, NULL, '2026-10-23 10:07:16'),
(30, 2, 'Ratna Puspitasari', 'Admin / Verifier', 'Status menjadi ISSUED', 'SIK-2026-00001', 'SIK SIK/AYANI/2026/001 diterbitkan pada 2026-10-24.', '2026-10-23 10:07:47'),
(31, 9, 'Nadia Kusuma', 'System administrator', 'Login', NULL, NULL, '2026-10-23 10:09:29');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(10) NOT NULL,
  `name` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `code`, `name`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'OPS', 'Operasi Sisi Udara', 1, '2026-10-23 05:29:26', '2026-10-23 05:29:26'),
(2, 'TEC', 'Teknik Bandara', 1, '2026-10-23 05:29:26', '2026-10-23 05:29:26'),
(3, 'MGT', 'Manajemen', 1, '2026-10-23 05:29:26', '2026-10-23 05:29:26');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

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
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_01_01_000001_create_sik_master_tables', 1),
(5, '2026_01_01_000002_add_sik_fields_to_users_table', 1),
(6, '2026_01_01_000003_create_workflow_template_tables', 1),
(7, '2026_01_01_000004_create_permit_application_tables', 1);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permit_applications`
--

CREATE TABLE `permit_applications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `number` varchar(20) NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `applicant_name` varchar(255) DEFAULT NULL,
  `contact_name` varchar(255) DEFAULT NULL,
  `job_title` varchar(255) DEFAULT NULL,
  `area_id` bigint(20) UNSIGNED DEFAULT NULL,
  `work_type_id` bigint(20) UNSIGNED DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'DRAFT',
  `current_stage` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `total_stages` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `correction_note` text DEFAULT NULL,
  `permit_number` varchar(18) DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permit_applications`
--

INSERT INTO `permit_applications` (`id`, `number`, `user_id`, `applicant_name`, `contact_name`, `job_title`, `area_id`, `work_type_id`, `start_date`, `end_date`, `status`, `current_stage`, `total_stages`, `correction_note`, `permit_number`, `issue_date`, `submitted_at`, `created_at`, `updated_at`) VALUES
(1, 'SIK-2026-00001', 1, 'PT Cakra', 'Andi Rahman', 'Teknisi Listrik', 2, 1, '2026-10-24', '2026-10-30', 'ISSUED', 3, 3, NULL, 'SIK/AYANI/2026/001', '2026-10-24', '2026-10-23 06:52:05', '2026-10-23 06:52:05', '2026-10-23 10:07:47');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('c4DHICEHmwrnnG5QOpHd7dCf7ON4H2dvFwz1wf45', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiTE8xcjUxRzNZbGhMVEt6MUZ4dTBSUndlcUxPQnQ0RHN4bk9WZXFwRCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NTI6Imh0dHA6Ly9qdXN0bmVzcy1kZWZsYXRvci1mcm9saWMubmdyb2stZnJlZS5kZXYvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1792759667),
('ihZXDDOV2UUdoBoF7YFgXYuhyiio5sFsQlybqLSK', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36 Edg/156.0.0.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoib3lYQWx3YllFVzc2cFkwN1MxSUl4UzVSTHpSYzJVVW9MT2R5WWRZYSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo0NjoiaHR0cDovL2p1c3RuZXNzLWRlZmxhdG9yLWZyb2xpYy5uZ3Jvay1mcmVlLmRldiI7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjUyOiJodHRwOi8vanVzdG5lc3MtZGVmbGF0b3ItZnJvbGljLm5ncm9rLWZyZWUuZGV2L2xvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1792759652),
('JmvvTax0MaDWWTnLhv4ocMZoqeu1oQHekTZv4cdD', 9, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiT2tCa2JJVmhsT3FpM0c2eWh4OEhXQzhoTHZDMG5YbFRERUM5c2RsayI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzU6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9ub3RpZmljYXRpb25zIjt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6OTt9', 1792775431),
('jStIbqmHM7it2cy5QVGrrS8686za99G4TwXtB9yL', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.143.1 Chrome/150.0.7871.250 Electron/43.7.7 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiQUJvOWRSd0hxQkdRSjdXRXJhNGJ1VkgzNGtnN04wZUhyVVYxb3RHYyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyMToiaHR0cDovLzEyNy4wLjAuMTo4MDAwIjt9czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1792758576),
('thCkUX5jB5kXZGv3BX1LDenY6lQAGI7IOhkFr7ef', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiOTB4QU94RnpUTXR4ZXNTdFdZTTZ3R216NDFzZFRtZjFKTXVjaTR5NyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NTI6Imh0dHA6Ly9qdXN0bmVzcy1kZWZsYXRvci1mcm9saWMubmdyb2stZnJlZS5kZXYvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1792759846),
('XvBz3G3Oxngz7U1Xhdk4ig7AYkirsLXOyWyhU7Fy', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiU05yV256b0hyMktmaDFiMVNHOGhkNkxlUTY4RXJLNHRiZXN4WkNESSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzU6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9ub3RpZmljYXRpb25zIjt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9', 1792764599);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'applicant',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `department_id` bigint(20) UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `notifications_read_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `username`, `email`, `email_verified_at`, `password`, `role`, `remember_token`, `created_at`, `updated_at`, `department_id`, `is_active`, `notifications_read_at`) VALUES
(1, 'Aqil', 'applicant', NULL, NULL, '$2y$12$7xO0RjWvCCJdGE.H/KC4MeQ5ecCQLQTvN9HCr4XbId7K9k6jJmFum', 'applicant', NULL, '2026-10-23 05:29:26', '2026-10-23 05:29:26', 1, 1, NULL),
(2, 'Antonius Dwi', 'admin', NULL, NULL, '$2y$12$FBDKfufjDxi69kTvLomK6uaT0O5/DGCN9kXgfkrxw8rvrVPyd7CEO', 'admin', NULL, '2026-10-23 05:29:27', '2026-10-23 05:29:27', 1, 1, NULL),
(3, 'Fadhlullah', 'approver', NULL, NULL, '$2y$12$JddviJh7B3ZxHseaG3JEy.W1RNgjz8CTJ.qL4.zc67yeGLtCtKVNW', 'approver', NULL, '2026-10-23 05:29:27', '2026-10-23 05:29:27', 1, 1, NULL),
(4, 'Afrizal', 'approver2', NULL, NULL, '$2y$12$sGIkVA.cZkwTCa2VrHXuhOcWHYwPEMvkocYTpPI2y0EYVTSqpxhwy', 'approver', NULL, '2026-10-23 05:29:27', '2026-10-23 05:29:27', 1, 1, NULL),
(5, 'Adhiansyah', 'approver3', NULL, NULL, '$2y$12$YJhI.3JySMJLC04YQnEttuch19.f/hxYS1GGE9HEDhygC0dNpFk7e', 'approver', NULL, '2026-10-23 05:29:28', '2026-10-23 05:29:28', 2, 1, NULL),
(6, 'PRIDO MUHAMMAD MUSTAR', 'approver4', NULL, NULL, '$2y$12$1tnz/i9i8BMfOiN4l6yxsOI9pbOkF7HkXCy2wd1t7PZg6SOD3/d3S', 'approver', NULL, '2026-10-23 05:29:28', '2026-10-23 05:29:28', 2, 1, NULL),
(7, 'Nur Aini', 'approver5', NULL, NULL, '$2y$12$QjoDUsdlYB.yRb6b1bORdeLi03qWRxkstbFKBIGYFJ5TiNxHYud.m', 'approver', NULL, '2026-10-23 05:29:28', '2026-10-23 05:29:28', 3, 1, NULL),
(8, 'Kusumawardhani', 'management', NULL, NULL, '$2y$12$To2CLOJp5JTzSqAWjWU1pOlIS8/09.EIEOAeutYDJautkknH5C72y', 'management', NULL, '2026-10-23 05:29:28', '2026-10-23 05:29:28', 3, 1, NULL),
(9, 'Muhammad Mustar', 'sysadmin', NULL, NULL, '$2y$12$lF4o8JTsHztugPFhXSvK0O7Raa7QuSJSyQlcf2cHDqfN/MwNP7Kuu', 'system-admin', NULL, '2026-10-23 05:29:29', '2026-10-23 05:29:29', 3, 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `workflow_templates`
--

CREATE TABLE `workflow_templates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `area_id` bigint(20) UNSIGNED NOT NULL,
  `work_type_id` bigint(20) UNSIGNED NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `workflow_templates`
--

INSERT INTO `workflow_templates` (`id`, `area_id`, `work_type_id`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, '2026-10-23 05:29:29', '2026-10-23 05:29:29'),
(2, 3, 2, 1, '2026-10-23 05:29:29', '2026-10-23 05:29:29'),
(3, 3, 1, 1, '2026-10-23 05:29:29', '2026-10-23 05:29:29'),
(4, 1, 3, 1, '2026-10-23 05:29:29', '2026-10-23 05:29:29'),
(5, 2, 1, 1, '2026-10-23 05:29:29', '2026-10-23 05:29:29'),
(6, 4, 3, 1, '2026-10-23 05:29:29', '2026-10-23 05:29:29'),
(7, 4, 1, 1, '2026-10-23 05:29:29', '2026-10-23 05:29:29'),
(8, 1, 4, 1, '2026-10-23 05:29:29', '2026-10-23 05:29:29');

-- --------------------------------------------------------

--
-- Table structure for table `workflow_template_steps`
--

CREATE TABLE `workflow_template_steps` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `workflow_template_id` bigint(20) UNSIGNED NOT NULL,
  `stage` tinyint(3) UNSIGNED NOT NULL,
  `approver_title` varchar(255) NOT NULL,
  `approver_user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `workflow_template_steps`
--

INSERT INTO `workflow_template_steps` (`id`, `workflow_template_id`, `stage`, `approver_title`, `approver_user_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Koordinator Operasi', 4, '2026-10-23 05:29:29', '2026-10-23 05:29:29'),
(2, 1, 2, 'Manajer Operasi Sisi Udara', 3, '2026-10-23 05:29:29', '2026-10-23 05:29:29'),
(3, 1, 3, 'Kepala Bandara', 7, '2026-10-23 05:29:29', '2026-10-23 05:29:29'),
(4, 2, 1, 'Koordinator Teknik', 5, '2026-10-23 05:29:29', '2026-10-23 05:29:29'),
(5, 2, 2, 'Manajer Teknik', 6, '2026-10-23 05:29:29', '2026-10-23 05:29:29'),
(6, 2, 3, 'Manajer Operasi Sisi Udara', 3, '2026-10-23 05:29:29', '2026-10-23 05:29:29'),
(7, 2, 4, 'Kepala Bandara', 7, '2026-10-23 05:29:29', '2026-10-23 05:29:29'),
(8, 3, 1, 'Koordinator Teknik', 5, '2026-10-23 05:29:29', '2026-10-23 05:29:29'),
(9, 3, 2, 'Manajer Operasi Sisi Udara', 3, '2026-10-23 05:29:29', '2026-10-23 05:29:29'),
(10, 3, 3, 'Kepala Bandara', 7, '2026-10-23 05:29:29', '2026-10-23 05:29:29'),
(11, 4, 1, 'Koordinator Operasi', 4, '2026-10-23 05:29:29', '2026-10-23 05:29:29'),
(12, 4, 2, 'Manajer Operasi Sisi Udara', 3, '2026-10-23 05:29:29', '2026-10-23 05:29:29'),
(13, 5, 1, 'Koordinator Teknik', 5, '2026-10-23 05:29:29', '2026-10-23 05:29:29'),
(14, 5, 2, 'Manajer Teknik', 6, '2026-10-23 05:29:29', '2026-10-23 05:29:29'),
(15, 5, 3, 'Kepala Bandara', 7, '2026-10-23 05:29:29', '2026-10-23 05:29:29'),
(16, 6, 1, 'Koordinator Operasi', 4, '2026-10-23 05:29:29', '2026-10-23 05:29:29'),
(17, 6, 2, 'Manajer Operasi Sisi Udara', 3, '2026-10-23 05:29:29', '2026-10-23 05:29:29'),
(18, 7, 1, 'Koordinator Teknik', 5, '2026-10-23 05:29:29', '2026-10-23 05:29:29'),
(19, 7, 2, 'Manajer Teknik', 6, '2026-10-23 05:29:29', '2026-10-23 05:29:29'),
(20, 7, 3, 'Kepala Bandara', 7, '2026-10-23 05:29:29', '2026-10-23 05:29:29'),
(21, 8, 1, 'Koordinator Operasi', 4, '2026-10-23 05:29:29', '2026-10-23 05:29:29'),
(22, 8, 2, 'Manajer Operasi Sisi Udara', 3, '2026-10-23 05:29:29', '2026-10-23 05:29:29');

-- --------------------------------------------------------

--
-- Table structure for table `work_types`
--

CREATE TABLE `work_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(10) NOT NULL,
  `name` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `work_types`
--

INSERT INTO `work_types` (`id`, `code`, `name`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'MNT', 'Pemeliharaan', 1, '2026-10-23 05:29:26', '2026-10-23 05:29:26'),
(2, 'CON', 'Konstruksi', 1, '2026-10-23 05:29:26', '2026-10-23 05:29:26'),
(3, 'INS', 'Inspeksi', 1, '2026-10-23 05:29:26', '2026-10-23 05:29:26'),
(4, 'CLN', 'Pembersihan', 1, '2026-10-23 05:29:26', '2026-10-23 05:29:26'),
(5, 'OTH', 'Lainnya', 1, '2026-10-23 05:29:26', '2026-10-23 05:29:26');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `application_approval_steps`
--
ALTER TABLE `application_approval_steps`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `application_approval_steps_permit_application_id_stage_unique` (`permit_application_id`,`stage`),
  ADD KEY `application_approval_steps_approver_user_id_index` (`approver_user_id`);

--
-- Indexes for table `application_documents`
--
ALTER TABLE `application_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `application_documents_permit_application_id_foreign` (`permit_application_id`);

--
-- Indexes for table `application_histories`
--
ALTER TABLE `application_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `application_histories_actor_id_foreign` (`actor_id`),
  ADD KEY `application_histories_permit_application_id_created_at_index` (`permit_application_id`,`created_at`);

--
-- Indexes for table `application_items`
--
ALTER TABLE `application_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `application_items_permit_application_id_foreign` (`permit_application_id`);

--
-- Indexes for table `application_personnel`
--
ALTER TABLE `application_personnel`
  ADD PRIMARY KEY (`id`),
  ADD KEY `application_personnel_permit_application_id_foreign` (`permit_application_id`);

--
-- Indexes for table `areas`
--
ALTER TABLE `areas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `areas_code_unique` (`code`),
  ADD UNIQUE KEY `areas_name_unique` (`name`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `audit_logs_user_id_foreign` (`user_id`),
  ADD KEY `audit_logs_target_index` (`target`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `departments_code_unique` (`code`),
  ADD UNIQUE KEY `departments_name_unique` (`name`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `permit_applications`
--
ALTER TABLE `permit_applications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permit_applications_number_unique` (`number`),
  ADD UNIQUE KEY `permit_applications_permit_number_unique` (`permit_number`),
  ADD KEY `permit_applications_user_id_foreign` (`user_id`),
  ADD KEY `permit_applications_area_id_foreign` (`area_id`),
  ADD KEY `permit_applications_work_type_id_foreign` (`work_type_id`),
  ADD KEY `permit_applications_status_created_at_index` (`status`,`created_at`),
  ADD KEY `permit_applications_status_index` (`status`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_username_unique` (`username`),
  ADD KEY `users_role_index` (`role`),
  ADD KEY `users_department_id_index` (`department_id`);

--
-- Indexes for table `workflow_templates`
--
ALTER TABLE `workflow_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `workflow_templates_area_id_work_type_id_unique` (`area_id`,`work_type_id`),
  ADD KEY `workflow_templates_work_type_id_foreign` (`work_type_id`);

--
-- Indexes for table `workflow_template_steps`
--
ALTER TABLE `workflow_template_steps`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `workflow_template_steps_workflow_template_id_stage_unique` (`workflow_template_id`,`stage`),
  ADD KEY `workflow_template_steps_approver_user_id_foreign` (`approver_user_id`);

--
-- Indexes for table `work_types`
--
ALTER TABLE `work_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `work_types_code_unique` (`code`),
  ADD UNIQUE KEY `work_types_name_unique` (`name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `application_approval_steps`
--
ALTER TABLE `application_approval_steps`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `application_documents`
--
ALTER TABLE `application_documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `application_histories`
--
ALTER TABLE `application_histories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `application_items`
--
ALTER TABLE `application_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `application_personnel`
--
ALTER TABLE `application_personnel`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `areas`
--
ALTER TABLE `areas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `permit_applications`
--
ALTER TABLE `permit_applications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `workflow_templates`
--
ALTER TABLE `workflow_templates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `workflow_template_steps`
--
ALTER TABLE `workflow_template_steps`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `work_types`
--
ALTER TABLE `work_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `application_approval_steps`
--
ALTER TABLE `application_approval_steps`
  ADD CONSTRAINT `application_approval_steps_approver_user_id_foreign` FOREIGN KEY (`approver_user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `application_approval_steps_permit_application_id_foreign` FOREIGN KEY (`permit_application_id`) REFERENCES `permit_applications` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `application_documents`
--
ALTER TABLE `application_documents`
  ADD CONSTRAINT `application_documents_permit_application_id_foreign` FOREIGN KEY (`permit_application_id`) REFERENCES `permit_applications` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `application_histories`
--
ALTER TABLE `application_histories`
  ADD CONSTRAINT `application_histories_actor_id_foreign` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `application_histories_permit_application_id_foreign` FOREIGN KEY (`permit_application_id`) REFERENCES `permit_applications` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `application_items`
--
ALTER TABLE `application_items`
  ADD CONSTRAINT `application_items_permit_application_id_foreign` FOREIGN KEY (`permit_application_id`) REFERENCES `permit_applications` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `application_personnel`
--
ALTER TABLE `application_personnel`
  ADD CONSTRAINT `application_personnel_permit_application_id_foreign` FOREIGN KEY (`permit_application_id`) REFERENCES `permit_applications` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `permit_applications`
--
ALTER TABLE `permit_applications`
  ADD CONSTRAINT `permit_applications_area_id_foreign` FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`),
  ADD CONSTRAINT `permit_applications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `permit_applications_work_type_id_foreign` FOREIGN KEY (`work_type_id`) REFERENCES `work_types` (`id`);

--
-- Constraints for table `workflow_templates`
--
ALTER TABLE `workflow_templates`
  ADD CONSTRAINT `workflow_templates_area_id_foreign` FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`),
  ADD CONSTRAINT `workflow_templates_work_type_id_foreign` FOREIGN KEY (`work_type_id`) REFERENCES `work_types` (`id`);

--
-- Constraints for table `workflow_template_steps`
--
ALTER TABLE `workflow_template_steps`
  ADD CONSTRAINT `workflow_template_steps_approver_user_id_foreign` FOREIGN KEY (`approver_user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `workflow_template_steps_workflow_template_id_foreign` FOREIGN KEY (`workflow_template_id`) REFERENCES `workflow_templates` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
