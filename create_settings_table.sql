-- Run this in phpMyAdmin: select the database from your .env (DB_DATABASE) in the left sidebar, open the SQL tab, paste, Go.
-- It only ADDS things. It does not delete or change any existing table or record.

CREATE TABLE IF NOT EXISTS `settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `value` text NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `settings` (`key`, `value`, `created_at`, `updated_at`) VALUES
  ('max_borrow_days', '14', NOW(), NOW()),
  ('max_quantity_per_item', '0', NOW(), NOW());

-- Tell Laravel this migration is already done (only inserts if it is not recorded yet)
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_01_03_000001_create_settings_table', COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_01_03_000001_create_settings_table');

-- Check the result (you should see 2 rows):
SELECT * FROM `settings`;
