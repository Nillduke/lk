-- Eternity Insights: consolidated database update, 24 October 2026.
-- Import into your EXISTING ei_db database using phpMyAdmin > Import.
-- WAMP MariaDB syntax; tested on MariaDB 11.3. Safe to import repeatedly.
-- Requires original reports, reports_licenses and news tables.
-- Update only, not a full backup or fresh-install dump.
-- Preserves existing content. No reportdb changes are required.
-- Local admin credentials and account records are deliberately not included.
-- MariaDB schema changes commit automatically.
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `lr_password_reset_tokens` (
 `email` varchar(255) NOT NULL,
 `token` varchar(255) NOT NULL,
 `created_at` timestamp NULL DEFAULT NULL,
 PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `lr_pages` (
 `id` bigint unsigned NOT NULL AUTO_INCREMENT,
 `slug` varchar(255) NOT NULL,
 `title` varchar(255) NOT NULL,
 `content` longtext NOT NULL,
 `status` tinyint(1) NOT NULL DEFAULT 1,
 `created_at` timestamp NULL DEFAULT NULL,
 `updated_at` timestamp NULL DEFAULT NULL,
 PRIMARY KEY (`id`),
 UNIQUE KEY `lr_pages_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `lr_bug_reports` (
 `id` bigint unsigned NOT NULL AUTO_INCREMENT,
 `user_id` int unsigned NOT NULL,
 `title` varchar(255) NOT NULL,
 `description` text NOT NULL,
 `status` varchar(255) NOT NULL DEFAULT 'open',
 `created_at` timestamp NULL DEFAULT NULL,
 `updated_at` timestamp NULL DEFAULT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `reports`
 ADD INDEX IF NOT EXISTS `lr_reports_slug_status_idx` (`slug`, `status`),
 ADD INDEX IF NOT EXISTS `lr_reports_live_date_idx` (`status`, `pub_date`),
 ADD INDEX IF NOT EXISTS `lr_reports_live_category_date_idx` (`status`, `category_id`, `pub_date`);
ALTER TABLE `reports_licenses`
 ADD INDEX IF NOT EXISTS `lr_report_license_min_idx` (`report_id`, `lic_price`);
ALTER TABLE `news`
 ADD INDEX IF NOT EXISTS `lr_news_live_date_idx` (`status`, `deleted_at`, `created_at`),
 ADD INDEX IF NOT EXISTS `lr_news_slug_live_idx` (`slug`, `status`, `deleted_at`);

-- Record completed migrations without duplicating existing history.
CREATE TABLE IF NOT EXISTS `lr_migrations` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `migration` varchar(255) NOT NULL,
 `batch` int NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SET @ei_update_batch = (SELECT COALESCE(MAX(`batch`), 0) + 1 FROM `lr_migrations`);
INSERT INTO `lr_migrations` (`migration`, `batch`)
SELECT '2026_10_24_000001_add_laravel_support_tables', @ei_update_batch
WHERE NOT EXISTS (SELECT 1 FROM `lr_migrations` WHERE `migration` = '2026_10_24_000001_add_laravel_support_tables');
INSERT INTO `lr_migrations` (`migration`, `batch`)
SELECT '2026_10_24_000002_add_public_catalogue_performance_indexes', @ei_update_batch
WHERE NOT EXISTS (SELECT 1 FROM `lr_migrations` WHERE `migration` = '2026_10_24_000002_add_public_catalogue_performance_indexes');
