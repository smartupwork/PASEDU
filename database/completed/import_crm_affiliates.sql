CREATE TABLE `pas_affiliate` (
  `id` int NOT NULL,
  `zoho_id` varchar(30) NOT NULL,
  `ps_shop_id` int DEFAULT NULL,
  `canvas_sub_account_id` bigint DEFAULT NULL,
  `affiliate_name` varchar(200) CHARACTER SET utf8mb4 NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `city` varchar(200) DEFAULT NULL,
  `zip_postal_code` varchar(10) CHARACTER SET utf8mb4 DEFAULT NULL,
  `address_1` varchar(500) DEFAULT NULL,
  `address_2` varchar(500) DEFAULT NULL,
  `hosted_site` varchar(500) CHARACTER SET utf8mb4 DEFAULT NULL,
  `affiliate_site` varchar(500) CHARACTER SET utf8mb4 DEFAULT NULL,
  `price_book_id` int DEFAULT NULL,
  `price_book_zoho_id` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 => Inactive, 1 => Active',
  `prestashop_menu` text,
  `created_by` int DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `pas_affiliate`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `zoho_id` (`zoho_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `price_book_id` (`price_book_id`);

ALTER TABLE `pas_affiliate`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;


ALTER TABLE `pas_partner` ADD `sync_ps_product` TINYINT(1) NOT NULL DEFAULT '0' AFTER `prestashop_menu`;

ALTER TABLE `pas_affiliate` ADD `sync_ps_product` TINYINT(1) NOT NULL DEFAULT '0' AFTER `prestashop_menu`;

