ALTER TABLE `pas_schedule` ADD `end_date` DATE NULL DEFAULT NULL AFTER `start_date`;

ALTER TABLE `pas_schedule` ADD `payment_type` VARCHAR(100) NULL DEFAULT NULL AFTER `amount`;

ALTER TABLE `pas_schedule` ADD `program_id` INT NULL DEFAULT NULL AFTER `stage`, ADD `program_zoho_id` VARCHAR(100) NULL DEFAULT NULL AFTER `program_id`;


ALTER TABLE `pas_schedule` ADD `payment_amount` DOUBLE NULL DEFAULT NULL AFTER `end_date`;


ALTER TABLE `pas_schedule` ADD `contact_zoho_id` VARCHAR(50) NULL DEFAULT NULL AFTER `partner_zoho_id`, ADD `contact_id` INT NULL DEFAULT NULL AFTER `contact_zoho_id`;

-- Above script executed on all environments

ALTER TABLE `pas_schedule` CHANGE `deal_name` `deal_name_old` VARCHAR(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `email` `email_old` VARCHAR(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `phone` `phone_old` VARCHAR(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;


ALTER TABLE `pas_schedule` ADD `deal_name` VARBINARY(100) NULL DEFAULT NULL AFTER `contact_id`, ADD `email` VARBINARY(100) NULL DEFAULT NULL AFTER `deal_name`, ADD `phone` VARBINARY(100) NULL DEFAULT NULL AFTER `email`;


ALTER TABLE `pas_contact` CHANGE `first_name` `first_name_old` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `last_name` `last_name_old` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `email` `email_old` VARCHAR(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `mobile` `mobile_old` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;

ALTER TABLE `pas_contact` CHANGE `mobile_old` `mobile` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;

ALTER TABLE `pas_contact` CHANGE `phone` `phone_old` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;


ALTER TABLE `pas_contact` ADD `first_name` VARBINARY(100) NOT NULL AFTER `zoho_id`, ADD `last_name` VARBINARY(100) NOT NULL AFTER `first_name`, ADD `email` VARBINARY(100) NOT NULL AFTER `last_name`, ADD `phone` VARBINARY(100) NULL DEFAULT NULL AFTER `email`;

ALTER TABLE `pas_contact` CHANGE `mobile` `mobile_old` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;

ALTER TABLE `pas_contact` ADD `mobile` VARBINARY(100) NULL DEFAULT NULL AFTER `phone`;

