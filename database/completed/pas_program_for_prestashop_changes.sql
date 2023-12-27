ALTER TABLE `pas_program` ADD `best_seller` TINYINT(1) NULL DEFAULT '0' AFTER `is_featured`, ADD `tag_line` TEXT NULL DEFAULT NULL AFTER `best_seller`, ADD `prerequisites` TEXT NULL DEFAULT NULL AFTER `tag_line`, ADD `outline` TEXT NULL DEFAULT NULL AFTER `prerequisites`, ADD `externship_included` VARCHAR(20) NULL DEFAULT NULL AFTER `outline`, ADD `approved_offering` TEXT NULL DEFAULT NULL AFTER `externship_included`;

ALTER TABLE `pas_program` CHANGE `best_seller` `is_best_seller` TINYINT(1) NULL DEFAULT '0';

ALTER TABLE `pas_program` CHANGE `hours` `hours` INT NULL DEFAULT NULL;

ALTER TABLE `pas_program` ADD `language` VARCHAR(50) NULL DEFAULT NULL AFTER `approved_offering`, ADD `ce_units` VARCHAR(20) NULL DEFAULT NULL AFTER `language`;

ALTER TABLE `pas_program` ADD `sub_title` VARCHAR(500) NULL DEFAULT NULL AFTER `name`;

ALTER TABLE `pas_program` ADD `vendor_id` VARCHAR(20) NULL DEFAULT NULL AFTER `ce_units`, ADD `average_completion` TEXT NULL DEFAULT NULL AFTER `vendor_name`, ADD `required_materials` VARCHAR(500) NULL DEFAULT NULL AFTER `average_completion`, ADD `technical_requirements` TEXT NULL DEFAULT NULL AFTER `required_materials`, ADD `accreditation` INT(255) NULL DEFAULT NULL AFTER `technical_requirements`, ADD `certification_benefits` TEXT NULL DEFAULT NULL AFTER `accreditation`, ADD `general_features_and_benefits` TEXT NULL DEFAULT NULL AFTER `certification_benefits`, ADD `demo_url` VARCHAR(500) NULL DEFAULT NULL AFTER `general_features_and_benefits`;


ALTER TABLE `pas_program` ADD `level` VARCHAR(100) NULL DEFAULT NULL AFTER `duration_value`;

ALTER TABLE `pas_program` ADD `occupation` VARCHAR(300) NULL DEFAULT NULL AFTER `level`;

ALTER TABLE `pas_program` ADD `feature_tag_line` TEXT NULL DEFAULT NULL AFTER `occupation`;


ALTER TABLE `pas_program` ADD `career_description` TEXT NULL DEFAULT NULL AFTER `feature_tag_line`, ADD `median_salary` DOUBLE NULL DEFAULT NULL AFTER `career_description`, ADD `job_growth` VARCHAR(300) NULL DEFAULT NULL AFTER `median_salary`, ADD `right_career` TEXT NULL DEFAULT NULL AFTER `job_growth`, ADD `website_short_description` TEXT NULL DEFAULT NULL AFTER `right_career`, ADD `learning_objectives` TEXT NULL DEFAULT NULL AFTER `website_short_description`, ADD `support_description` TEXT NULL DEFAULT NULL AFTER `learning_objectives`;

ALTER TABLE `pas_program` ADD `audience` TEXT NULL DEFAULT NULL AFTER `ce_units`;

ALTER TABLE `pas_partner` ADD `contacts` LONGTEXT NULL DEFAULT NULL AFTER `partner_type`;


ALTER TABLE `pas_partner` ADD `contact_title` VARCHAR(500) NULL DEFAULT NULL AFTER `status`, ADD `campus_name_if_applicable` VARCHAR(500) NULL DEFAULT NULL AFTER `contact_title`, ADD `billing_street` VARCHAR(500) NULL DEFAULT NULL AFTER `campus_name_if_applicable`, ADD `billing_address_2` VARCHAR(500) NULL DEFAULT NULL AFTER `billing_street`, ADD `billing_city` VARCHAR(100) NULL DEFAULT NULL AFTER `billing_address_2`, ADD `billing_code` VARCHAR(100) NULL DEFAULT NULL AFTER `billing_city`, ADD `billing_country` VARCHAR(100) NULL DEFAULT NULL AFTER `billing_code`, ADD `billing_state` VARCHAR(500) NULL DEFAULT NULL AFTER `billing_country`, ADD `tp_website` VARCHAR(500) NULL DEFAULT NULL AFTER `billing_state`;

ALTER TABLE `pas_program` ADD `delivery_methods_available` TEXT NULL DEFAULT NULL AFTER `audience`;

ALTER TABLE `pas_program` CHANGE `required_materials` `required_materials` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;

ALTER TABLE `pas_program` ADD `certification` TEXT NULL DEFAULT NULL AFTER `delivery_methods_available`;

ALTER TABLE `pas_program` ADD `avg_completion_time` VARCHAR(200) NULL DEFAULT NULL AFTER `average_completion`;

INSERT INTO `ps_configuration` (`id`, `partner_id`, `type`, `content`, `is_active`, `created_at`, `created_by`, `updated_at`, `updated_by`) VALUES
(NULL, 1, 'pricebook-for-affiliate', 'World Education', 1, '2022-09-08 04:02:47', 1, NULL, NULL);

-- ALTER TABLE `pas_program` ADD `ps_need_to_update` TINYINT(1) NOT NULL DEFAULT '0' AFTER `vendor_id`;

-- ALTER TABLE `pas_partner` ADD `prestashop_synced_at` DATETIME NULL DEFAULT NULL AFTER `prestashop_menu`;

ALTER TABLE `pas_program` ADD `is_copy` TINYINT(1) NOT NULL DEFAULT '0' AFTER `vendor_id`;

ALTER TABLE `pas_program` ADD `prepares_for_certification` TEXT NULL DEFAULT NULL AFTER `service_item_not_program`, ADD `mycaa_description` VARCHAR(500) NULL DEFAULT NULL AFTER `prepares_for_certification`;
