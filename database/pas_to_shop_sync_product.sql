ALTER TABLE `pas_price_book` ADD `sync_at` DATETIME NULL DEFAULT NULL AFTER `updated_at`, ADD `sync_status` TINYINT(1) NOT NULL DEFAULT '0' AFTER `sync_at`;
