ALTER TABLE `pas_program` ADD `product_id` INT NULL DEFAULT NULL AFTER `id`;

ALTER TABLE `pas_partner` ADD `ps_shop_id` INT NULL DEFAULT NULL AFTER `zoho_id`;

ALTER TABLE `ps_banner` CHANGE `partner_id` `partner_id` INT NULL DEFAULT NULL;

CREATE TABLE `pas_product` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `zoho_id` varchar(100) NOT NULL,
  `program_type` varchar(200) DEFAULT NULL,
  `category` varchar(150) NOT NULL,
  `code` varchar(100) NOT NULL,
  `hours` int DEFAULT NULL,
  `duration_type` varchar(100) DEFAULT NULL,
  `duration_value` float DEFAULT NULL,
  `wholesale` varchar(255) DEFAULT NULL,
  `description` text,
  `owner` varchar(255) DEFAULT NULL,
  `unite_price` float NOT NULL,
  `quantity_in_stock` int DEFAULT NULL,
  `vendor_name` varchar(200) DEFAULT NULL,
  `status` varchar(100) DEFAULT NULL,
  `certification` varchar(150) DEFAULT NULL,
  `displayed_on` varchar(255) NOT NULL,
  `service_item_not_program` tinyint(1) NOT NULL,
  `layout` varchar(200) DEFAULT NULL,
  `is_featured` tinyint NOT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `pas_product`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

ALTER TABLE `pas_product`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `pas_product` ADD `is_best_seller` TINYINT(1) NULL DEFAULT '0' AFTER `is_featured`;

ALTER TABLE `ps_product_shop` ADD `is_best_selling` TINYINT(1) NOT NULL DEFAULT '0' AFTER `pack_stock_type`;

ALTER TABLE `pas_product` ADD `level` VARCHAR(100) NULL DEFAULT NULL AFTER `certification`, ADD `language` VARCHAR(100) NULL DEFAULT NULL AFTER `level`;

ALTER TABLE `pas_product` ADD `prerequisites` TEXT NULL DEFAULT NULL AFTER `language`, ADD `outline` TEXT NULL DEFAULT NULL AFTER `prerequisites`;

ALTER TABLE `pas_product` ADD `optional_externship_included` VARCHAR(200) NULL DEFAULT NULL AFTER `outline`;

ALTER TABLE `pas_product` ADD `eligible_funding` TEXT NULL DEFAULT NULL AFTER `optional_externship_included`;
