CREATE TABLE `we_product_lang` (
  `id_product` int(10) UNSIGNED NOT NULL,
  `id_shop` int(10) UNSIGNED NOT NULL DEFAULT '1',
  `id_lang` int(10) UNSIGNED NOT NULL DEFAULT '1',
  `zoho_id` varchar(30) NOT NULL,
  `name` varchar(255) NOT NULL,
  `sub_title` varchar(255) DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `program_type` varchar(255) DEFAULT NULL,
  `code` varchar(100) DEFAULT NULL,
  `hours` int(11) DEFAULT NULL,
  `duration_type` varchar(30) DEFAULT NULL,
  `duration_value` int(11) DEFAULT NULL,
  `level` varchar(100) DEFAULT NULL,
  `occupation` varchar(300) DEFAULT NULL,
  `feature_tag_line` text,
  `career_description` text,
  `median_salary` double DEFAULT NULL,
  `job_growth` varchar(300) DEFAULT NULL,
  `right_career` text,
  `website_short_description` text,
  `learning_objectives` text,
  `support_description` text,
  `retail_wholesale` double DEFAULT NULL,
  `description` longtext,
  `service_item_not_program` int(11) DEFAULT NULL,
  `displayed_on` varchar(30) DEFAULT NULL,
  `unite_price` double DEFAULT NULL,
  `certification_included` varchar(20) DEFAULT NULL,
  `status` varchar(30) DEFAULT NULL,
  `layout` varchar(50) NOT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `is_best_seller` tinyint(1) DEFAULT '0',
  `tag_line` varchar(255) DEFAULT NULL,
  `prerequisites` text,
  `outline` text,
  `externship_included` varchar(20) DEFAULT NULL,
  `approved_offering` varchar(30) DEFAULT NULL,
  `language` varchar(100) DEFAULT NULL,
  `ce_units` int(11) DEFAULT NULL,
  `audience` text,
  `delivery_methods_available` text,
  `certification` text,
  `certification_inclusion` longtext,
  `vendor_name` varchar(255) DEFAULT NULL,
  `vendor_id` varchar(50) DEFAULT NULL,
  `average_completion` text,
  `avg_completion_time` varchar(200) DEFAULT NULL,
  `required_materials` text,
  `technical_requirements` text,
  `accreditation` int(11) DEFAULT NULL,
  `certification_benefits` int(11) DEFAULT NULL,
  `general_features_and_benefits` text,
  `demo_url` varchar(500) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `we_product_lang`
  ADD PRIMARY KEY (`zoho_id`) USING BTREE,
  ADD KEY `id_lang` (`id_lang`),
  ADD KEY `id_shop` (`id_shop`),
  ADD KEY `id_product` (`id_product`);


ALTER TABLE `ps_product_shop` ADD `is_best_selling` TINYINT NOT NULL DEFAULT '0' AFTER `pack_stock_type`;


ALTER TABLE `ps_customer` ADD `phone` VARCHAR(20) NULL DEFAULT NULL AFTER `email`;
ALTER TABLE `ps_customer` ADD `buying_for` VARCHAR(20) NULL DEFAULT NULL AFTER `phone`;


-- ONLY FOR DEV SERVER (PHPEMAILER ADN CRM ENROLLMENT)

INSERT INTO `ps_configuration` (`id_configuration`, `id_shop_group`, `id_shop`, `name`, `value`, `date_add`, `date_upd`) VALUES
(NULL, NULL, NULL, 'DEV_ENVIRONMENT', '1', '2022-09-08 12:38:56', '2022-09-08 12:38:56'),
(NULL, NULL, NULL, 'USE_EMAIL_CUSTOM_CODE', '1', '2022-09-08 12:38:56', '2022-09-08 12:38:56');

-- ONLY FOR DEV SERVER


-- NOT USING RIGHT NOW START
-- INSERT INTO `ps_configuration` (`id_configuration`, `id_shop_group`, `id_shop`, `name`, `value`, `date_add`, `date_upd`) VALUES (NULL, NULL, NULL, 'AFFILIATES_DEFAULT_SHOP', '111', '2022-09-08 12:38:56', '2022-09-08 12:38:56');

--ALTER TABLE `ps_customer` ADD `zoho_enrollment_id` VARCHAR(100) NULL DEFAULT NULL AFTER `reset_password_validity`, ADD `zoho_contact_id` VARCHAR(100) NULL DEFAULT NULL AFTER `zoho_enrollment_id`, ADD `zoho_payment_id` VARCHAR(100) NULL DEFAULT NULL AFTER `zoho_contact_id`;

-- NOT USING RIGHT NOW END


-- IF PRODUCTION YOU NEED TO CHANGE LAYOUT PRODUCTION ID
INSERT INTO `ps_configuration` (`id_configuration`, `id_shop_group`, `id_shop`, `name`, `value`, `date_add`, `date_upd`) VALUES
(NULL, NULL, NULL, 'ZOHO_PAYMENT_LAYOUT_ID', '4837391000016642004', '2017-05-24 10:46:56', '2017-05-24 10:46:56'),
(NULL, NULL, NULL, 'ZOHO_ENROLLMENT_LAYOUT_ID', '4837391000016636094', '2017-05-24 10:46:56', '2017-05-24 10:46:56');


CREATE TABLE `zoho_enrollments` (
  `id` int NOT NULL,
  `cart_id` int NOT NULL,
  `request_type` enum('contact','enrollment','payment') NOT NULL,
  `zoho_id` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `request_data` text CHARACTER SET latin1 COLLATE latin1_swedish_ci,
  `status` enum('success','fail') DEFAULT NULL,
  `zoho_response` text,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `zoho_enrollments`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `zoho_enrollments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;








ALTER TABLE `ps_product` ADD `displayed_on` VARCHAR(150) NULL DEFAULT NULL AFTER `state`, ADD `status` VARCHAR(100) NULL DEFAULT NULL AFTER `displayed_on`;

ALTER TABLE `ps_product` ADD `is_featured` TINYINT(1) NOT NULL DEFAULT '0' AFTER `status`;

ALTER TABLE `ps_product` ADD `is_best_seller` TINYINT(1) NOT NULL DEFAULT '0' AFTER `is_featured`;


ALTER TABLE `ps_product` ADD `zoho_id` VARCHAR(100) NULL DEFAULT NULL AFTER `is_best_seller`;


ALTER TABLE `ps_linksmenutop` ADD `position` TINYINT(4) NULL DEFAULT NULL AFTER `new_window`;

ALTER TABLE `ps_linksmenutop_lang` CHANGE `link` `link` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

ALTER TABLE `ps_product` ADD `language` VARCHAR(50) NULL DEFAULT NULL AFTER `state`;

ALTER TABLE `ps_product` ADD `category` VARCHAR(500) NULL DEFAULT NULL AFTER `language`, ADD `ce_units` VARCHAR(500) NULL DEFAULT NULL AFTER `category`;