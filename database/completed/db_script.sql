ALTER TABLE `pas_users` ADD `last_wrong_attempted_at` DATETIME NULL DEFAULT NULL AFTER `login_code`;

CREATE TABLE `pas_wrong_login` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `attempt_time` datetime NOT NULL,
  `ip_address` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `pas_wrong_login` ADD PRIMARY KEY (`id`), ADD KEY `user_id` (`user_id`);

ALTER TABLE `pas_wrong_login` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

ALTER TABLE `pas_wrong_login` ADD CONSTRAINT `pas_wrong_login_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `pas_users` (`id`) ON UPDATE CASCADE;

INSERT INTO `pas_email_templates` (`id`, `type`, `subject`, `message`, `added_date`) VALUES
(3, 'Account Disable Attempt Wrong Password', 'Account Disable Attempt Wrong Password', 'Hi %FN%, \r\n \r\nWelcome to PAS Application. Your account has been disabled. \r\n \r\nYour login details are as follows: \r\nLogin url: %URL% \r\nThe web application will prompt you to change your Password. Your password should be between 8 and 20 characters. \r\nPlease choose a strong password that you can easily remember. \r\nThis is an exciting opportunity to discover all what you can do on this web application. If at any time you have questions, contact the Partner Help Desk.\r\n \r\nBest Regards, \r\nPAS Admin', '2021-04-09 12:17:38'),
(4, 'Account Enable Attempt Wrong Password', 'Account Enable Attempt Wrong Password', 'Hi %FN%, \r\n \r\nWelcome to PAS Application. Your account has been disabled. \r\n \r\nYour login details are as follows: \r\nLogin url: %URL% \r\nThe web application will prompt you to change your Password. Your password should be between 8 and 20 characters. \r\nPlease choose a strong password that you can easily remember. \r\nThis is an exciting opportunity to discover all what you can do on this web application. If at any time you have questions, contact the Partner Help Desk.\r\n \r\nBest Regards, \r\nPAS Admin', '2021-04-09 12:17:38');


-- 03 May 2021 Role and Partner Type Master Table Implemented

CREATE TABLE `pas_roles` (
  `id` int(11) NOT NULL,
  `role_name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `role_type` enum('partner','user') NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


INSERT INTO `pas_roles` (`id`, `role_name`, `description`, `role_type`, `status`) VALUES
(1, 'Account Manager', 'Account Manager', 'partner', 1),
(2, 'Account Support', 'Account Support', 'partner', 1),
(3, 'Registration Account', 'Registration Account', 'partner', 1),
(4, 'Manager', 'Manager', 'user', 1),
(5, 'Read-Only', 'Read-Only', 'user', 1);

ALTER TABLE `pas_roles`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `pas_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

CREATE TABLE `pas_partner_type` (
  `id` int(11) NOT NULL,
  `partner_type` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


INSERT INTO `pas_partner_type` (`id`, `partner_type`, `description`, `status`) VALUES
(1, 'Account Partner', 'Account Partner', 1),
(2, 'Registration Partner', 'Registration Partner', 1);

ALTER TABLE `pas_partner_type`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `pas_partner_type`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;


ALTER TABLE `pas_users` ADD INDEX(`roleid`);

ALTER TABLE `pas_users` ADD INDEX(`partner_type`);

ALTER TABLE `pas_users` CHANGE `roleid` `roleid` INT(1) NOT NULL DEFAULT '0', CHANGE `partner_type` `partner_type` INT(1) NULL DEFAULT '0';

UPDATE `pas_users` SET `partner_type` = NULL WHERE `pas_users`.`partner_type` = 0;

ALTER TABLE `pas_users` ADD FOREIGN KEY (`roleid`) REFERENCES `pas_roles`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `pas_users` ADD FOREIGN KEY (`partner_type`) REFERENCES `pas_partner_type`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE;


-- 05 May 2021 Maximum 3 Login Allowed Functionality Implemented

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `pas_login_activity` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `logged_in_at` datetime NOT NULL,
  `logged_out_at` datetime DEFAULT NULL,
  `last_activity_time` datetime NOT NULL,
  `ip_address` varchar(50) NOT NULL,
  `session_id` varchar(150) NOT NULL,
  `user_agent` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `pas_login_activity`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `pas_login_activity`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `pas_login_activity`
  ADD CONSTRAINT `pas_login_activity_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `pas_users` (`id`) ON UPDATE CASCADE;

-- 09 May 2021 Student CRUD Related Tables

CREATE TABLE `pas_country` (
  `id` int(11) NOT NULL,
  `country_name` varchar(150) NOT NULL,
  `iso2_code` char(2) DEFAULT NULL,
  `iso3_code` char(3) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `pas_country` (`id`, `country_name`, `iso2_code`, `iso3_code`, `status`) VALUES
(1, 'America', 'US', 'USA', 1);

CREATE TABLE `pas_program` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `zoho_id` varchar(100) NOT NULL,
  `category` varchar(150) NOT NULL,
  `code` varchar(100) NOT NULL,
  `owner` varchar(255) DEFAULT NULL,
  `unite_price` float NOT NULL,
  `quantiry_in_stock` int(11) DEFAULT NULL,
  `vendor_name` varchar(200) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `pas_state` (
  `id` int(11) NOT NULL,
  `country_id` int(11) NOT NULL,
  `state_name` varchar(150) NOT NULL,
  `iso2_code` char(2) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


INSERT INTO `pas_state` (`id`, `country_id`, `state_name`, `iso2_code`, `status`) VALUES
(1, 1, 'Alabama', 'AL', 1),
(2, 1, 'Alaska', 'AK', 1),
(3, 1, 'Arizona', 'AZ', 1),
(4, 1, 'Arkansas', 'AR', 1);


CREATE TABLE `pas_student` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(120) NOT NULL,
  `program_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `complete_date` date DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1=>Active, 2=>Complete, 3=>Refund, 4=>Expired',
  `payment_type` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1=>Myh, 2=>Self, 3=>WIOA, 4=>VOCR',
  `end_date` date NOT NULL,
  `phone` varchar(20) DEFAULT '000-000-0000',
  `street` varchar(255) DEFAULT NULL,
  `city` varchar(150) DEFAULT NULL,
  `state` int(11) DEFAULT NULL,
  `country` int(11) DEFAULT NULL,
  `zip` varchar(10) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


INSERT INTO `pas_student` (`id`, `first_name`, `last_name`, `email`, `program_id`, `start_date`, `complete_date`, `status`, `payment_type`, `end_date`, `phone`, `street`, `city`, `state`, `country`, `zip`, `created_by`, `created_at`, `updated_by`, `updated_at`) VALUES
(2, 'Robert', 'Velarde', 'robert@gmail.com', 4, '2021-06-01', '2021-06-30', 2, 3, '2021-06-25', '8437674448', '7360 Industry Dr.', 'North Charleston', 2, 1, '29418-8429', 11, NULL, NULL, NULL),
(3, 'Sandeep', 'Gautam', 'sandeep@gmail.com', 9, '2021-06-09', NULL, 4, 4, '2021-06-23', '09935723456', 'Barra 5', 'Kanpur', 3, 1, '208027', 11, NULL, NULL, NULL),
(5, 'Rajneesh', 'Gautam', 'rajneeshgautam24@gmail.com', 1, '2021-05-15', NULL, 2, 3, '2021-05-28', '+919935723456', 'Lig 139 Barra 5, 80 Feet Road', 'Kanpur', 4, 1, '208027', 11, NULL, NULL, NULL);


ALTER TABLE `pas_country`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `pas_program`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);


ALTER TABLE `pas_state`
  ADD PRIMARY KEY (`id`),
  ADD KEY `country_id` (`country_id`);

ALTER TABLE `pas_student`
  ADD PRIMARY KEY (`id`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `program_id` (`program_id`),
  ADD KEY `state` (`state`),
  ADD KEY `country` (`country`);

ALTER TABLE `pas_country`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `pas_program`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

ALTER TABLE `pas_state`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `pas_student`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

ALTER TABLE `pas_program`
  ADD CONSTRAINT `pas_program_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `pas_users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `pas_program_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `pas_users` (`id`) ON UPDATE CASCADE;

ALTER TABLE `pas_state`
  ADD CONSTRAINT `pas_state_ibfk_1` FOREIGN KEY (`country_id`) REFERENCES `pas_country` (`id`) ON UPDATE CASCADE;

ALTER TABLE `pas_student`
  ADD CONSTRAINT `pas_student_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `pas_users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `pas_student_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `pas_users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `pas_student_ibfk_3` FOREIGN KEY (`program_id`) REFERENCES `pas_program` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `pas_student_ibfk_4` FOREIGN KEY (`state`) REFERENCES `pas_state` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `pas_student_ibfk_5` FOREIGN KEY (`country`) REFERENCES `pas_country` (`id`) ON UPDATE CASCADE;


ALTER TABLE `pas_student` ADD `zoho_id` VARCHAR(100) NULL DEFAULT NULL AFTER `id`;

ALTER TABLE `pas_student` ADD `attachment` VARCHAR(200) NULL DEFAULT NULL AFTER `zip`;

ALTER TABLE `pas_student` CHANGE `payment_type` `payment_type` VARCHAR(200) NULL COMMENT '';

INSERT INTO `pas_email_templates` (`id`, `type`, `subject`, `message`, `added_date`) VALUES (6, 'Personal Detail Updated', 'Personal Detail Updated on ZOHO ', 'A Request for Student Progress report has been submitted with following details:\r\n\r\n\r\nUpdated Information -\r\n\r\nName - %FIRSTNAME% %LASTNAME%\r\n\r\nPhone - %PHONE%\r\n\r\nEmail - %EMAIL%', '2021-04-09 12:17:38');

INSERT INTO `pas_email_templates` (`id`, `type`, `subject`, `message`, `added_date`) VALUES (7, 'Personal Detail Updated For Partner', 'Personal Detail Updated on ZOHO ', 'A Request for Student Progress report has been submitted with the following details:\r\n\r\n\r\nUpdated Information -\r\n\r\n%STUDENTS%', '2021-04-09 12:17:38');

ALTER TABLE `pas_leads` ADD `city` VARCHAR(200) NULL DEFAULT NULL AFTER `phone`, ADD `state` VARCHAR(200) NULL DEFAULT NULL AFTER `city`, ADD `zip` VARCHAR(10) NULL DEFAULT NULL AFTER `state`;

ALTER TABLE `pas_student` ADD `partner_id` INT NOT NULL AFTER `id`;

UPDATE `pas_student` SET `partner_id` = '4' WHERE 1;

ALTER TABLE `pas_student` ADD INDEX(`partner_id`);

ALTER TABLE `pas_leads` ADD `partner_id` INT NOT NULL AFTER `zoho_id`, ADD INDEX (`partner_id`);

ALTER TABLE `pas_leads` ADD INDEX(`partner_id`);

ALTER TABLE `pas_leads` ADD INDEX(`partner_id`);

ALTER TABLE `pas_leads` ADD INDEX(`added_by`);

ALTER TABLE `pas_leads` ADD INDEX(`state`);

ALTER TABLE `pas_leads` ADD INDEX(`country`);

ALTER TABLE `pas_leads` CHANGE `time_zone` `time_zone` INT(11) NULL DEFAULT NULL;

ALTER TABLE `pas_leads` ADD FOREIGN KEY (`partner_id`) REFERENCES `pas_partner`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE;


ALTER TABLE `pas_partner` ADD `contact_name` INT NOT NULL AFTER `partner_name`, ADD `title` VARCHAR(255) NOT NULL AFTER `contact_name`, ADD `phone` VARCHAR(15) NOT NULL AFTER `title`, ADD `email` VARCHAR(120) NOT NULL AFTER `phone`, ADD `street` VARCHAR(255) NOT NULL AFTER `email`, ADD `city` VARCHAR(200) NOT NULL AFTER `street`, ADD `state` INT(200) NOT NULL AFTER `city`, ADD `zip_code` VARCHAR(10) NOT NULL AFTER `state`, ADD `logo` VARCHAR(150) NOT NULL AFTER `zip_code`;


ALTER TABLE `pas_partner` CHANGE `title` `title` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `phone` `phone` VARCHAR(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `street` `street` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `city` `city` VARCHAR(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `state` `state` INT(200) NULL DEFAULT NULL, CHANGE `zip_code` `zip_code` VARCHAR(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `logo` `logo` VARCHAR(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;

ALTER TABLE `pas_partner` CHANGE `contact_name` `contact_name` VARCHAR(255) NOT NULL;

ALTER TABLE `pas_program` ADD `hours` INT NOT NULL AFTER `code`, ADD `retail_wholesale` VARCHAR(255) NULL DEFAULT NULL AFTER `hours`, ADD `description` TEXT NULL DEFAULT NULL AFTER `retail_wholesale`;

ALTER TABLE `pas_program` ADD `displayed_on` VARCHAR(255) NOT NULL AFTER `status`, ADD `service_item_not_program` TINYINT(1) NOT NULL AFTER `displayed_on`;


ALTER TABLE `pas_partner` ADD `record_image` VARCHAR(255) NULL DEFAULT NULL AFTER `logo`;


ALTER TABLE `pas_partner` ADD `created_by` INT NULL DEFAULT NULL AFTER `status`, ADD `created_at` DATETIME NULL DEFAULT NULL AFTER `created_by`, ADD `updated_by` INT NULL DEFAULT NULL AFTER `created_at`, ADD `updated_at` DATETIME NULL DEFAULT NULL AFTER `updated_by`, ADD INDEX (`created_by`), ADD INDEX (`updated_by`);

ALTER TABLE `student_progress_report` ADD `request_type` TINYINT(1) NOT NULL DEFAULT '1' COMMENT '1 - Institution Request, 2- Marketing Collateral Request' AFTER `id`

CREATE TABLE `pas_marketing_collateral` (
  `id` int(11) NOT NULL,
  `progress_report_id` int(11) NOT NULL,
  `contact_name` varchar(200) NOT NULL,
  `contact_email` varchar(180) DEFAULT NULL,
  `partner_id` int(11) DEFAULT NULL,
  `is_requested_material` tinyint(1) NOT NULL DEFAULT 0,
  `event_date` date DEFAULT NULL,
  `target_audience` varchar(255) DEFAULT NULL,
  `intended_outcome` varchar(255) DEFAULT NULL,
  `branding` tinyint(1) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `project_type` tinyint(1) DEFAULT NULL,
  `program_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `additional_notes` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `pas_marketing_collateral`
  ADD PRIMARY KEY (`id`),
  ADD KEY `partner_id` (`partner_id`),
  ADD KEY `progress_report_id` (`progress_report_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `program_id` (`program_id`);

ALTER TABLE `pas_marketing_collateral`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `pas_marketing_collateral`
  ADD CONSTRAINT `pas_marketing_collateral_ibfk_1` FOREIGN KEY (`progress_report_id`) REFERENCES `student_progress_report` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `pas_marketing_collateral_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `pas_users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `pas_marketing_collateral_ibfk_3` FOREIGN KEY (`updated_by`) REFERENCES `pas_users` (`id`) ON UPDATE CASCADE;

ALTER TABLE `pas_partner` ADD `price_book_id` INT NULL DEFAULT NULL AFTER `zip_code`;

ALTER TABLE `pas_partner` ADD `price_book_zoho_id` VARCHAR(255) NULL DEFAULT NULL AFTER `price_book_id`;

ALTER TABLE `pas_partner` CHANGE `price_book_id` `price_book_id` VARCHAR(100) NULL DEFAULT NULL;


CREATE TABLE `pas_price_book` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `zoho_id` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `owner` varchar(255) DEFAULT NULL,
  `owner_id` bigint(20) NOT NULL,
  `status` varchar(100) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `pas_price_book`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

ALTER TABLE `pas_price_book`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

CREATE TABLE `pas_price_book_program_map` (
  `price_book_id` int(11) DEFAULT NULL,
  `price_book_zoho_id` varchar(100) DEFAULT NULL,
  `program_id` int(11) NOT NULL,
  `program_zoho_id` varchar(100) DEFAULT NULL,
  `program_list_price` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `pas_price_book_program_map`
  ADD UNIQUE KEY `price_book_id_2` (`price_book_id`,`program_id`),
  ADD KEY `price_book_id` (`price_book_id`),
  ADD KEY `program_id` (`program_id`);



  ALTER TABLE `pas_student` CHANGE `first_name` `first_name_old` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, CHANGE `last_name` `last_name_old` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, CHANGE `email` `email_old` VARCHAR(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, CHANGE `phone` `phone_old` VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '000-000-0000';


ALTER TABLE `pas_student` ADD `first_name` VARBINARY(100) NOT NULL AFTER `zoho_id`, ADD `last_name` VARBINARY(100) NOT NULL AFTER `first_name`, ADD `email` VARBINARY(100) NULL AFTER `last_name`, ADD `phone` VARBINARY(100) NULL DEFAULT NULL AFTER `email`;

UPDATE `pas_student` SET first_name = AES_ENCRYPT(first_name_old, 'PASAppdevPMA2021'), last_name = AES_ENCRYPT(last_name_old, 'PASAppdevPMA2021'), email = AES_ENCRYPT(email_old, 'PASAppdevPMA2021'), phone = AES_ENCRYPT(phone_old, 'PASAppdevPMA2021')

ALTER TABLE `pas_users` ADD `highlight_reports` VARCHAR(1000) NULL DEFAULT NULL AFTER `last_wrong_attempted_at`;

ALTER TABLE `pas_users_access` DROP `id`;

ALTER TABLE `pas_users_access` ADD UNIQUE( `user_id`, `feature`);


INSERT INTO `pas_roles` (`id`, `role_name`, `description`, `role_type`, `status`) VALUES (NULL, 'Marketing Manager', 'Marketing Manager', 'user', '1');

ALTER TABLE `pas_roles_access` ADD `access_level` ENUM('full-access','account-support','registration-account-partner') NULL DEFAULT NULL AFTER `role_id`;


INSERT INTO `pas_roles_access` (`id`, `role_id`, `access_level`, `feature`, `parent_menu`, `can_view`, `can_download`, `can_add`) VALUES
(NULL, '1', 'account-manager', 'HOME_DASHBOARD_ACCESS', 'DASHBOARD', '1', '1', '1'),
(NULL, '1', 'account-manager', 'STATS_ACCESS', 'DASHBOARD', '1', '1', '1'),
(NULL, '1', 'account-manager', 'MY_INSTITUTION_REQUEST_ACCESS', 'DASHBOARD', '1', '1', '1'),
(NULL, '1', 'account-manager', 'MAP_MY_STUDENTS_ACCESS', 'DASHBOARD', '1', '1', '1'),
(NULL, '1', 'account-manager', 'CATALOG_MANAGEMENT_ACCESS', 'CATALOG_MANAGEMENT', '1', '1', '1'),
(NULL, '1', 'account-manager', 'STUDENT_MANAGEMENT_ACCESS', 'STUDENT_MANAGEMENT', '1', '1', '1'),
(NULL, '1', 'account-manager', 'STUDENT_IMPORT_AUDIT_ACCESS', 'STUDENT_MANAGEMENT', '1', '1', '1'),
(NULL, '1', 'account-manager', 'LEADS_ACCESS', 'STUDENT_MANAGEMENT', '1', '1', '1'),
(NULL, '1', 'account-manager', 'NEWS_ACCESS', 'MARKETING', '1', '0', '0'),
(NULL, '1', 'account-manager', 'ANNOUNCEMENTS_ACCESS', 'MARKETING', '1', '0', '0'),
(NULL, '1', 'account-manager', 'UPDATES_ACCESS', 'MARKETING', '1', '0', '0'),
(NULL, '1', 'account-manager', 'MY_TOP_SELLING_PROGRAMS_ACCESS', 'MARKETING', '1', '0', '0'),
(NULL, '1', 'account-manager', 'REQUEST_COLLATERAL_ACCESS', 'MARKETING', '1', '1', '1'),
(NULL, '1', 'account-manager', 'MARKETING_COLLATERAL_ACCESS', 'MARKETING', '1', '1', '1'),
(NULL, '1', 'account-manager', 'MY_INSTITUTION_PROFILE_ACCESS', 'PARTNER_PROFILE', '1', '1', '1'),
(NULL, '1', 'account-manager', 'MY_PROFILE_ACCESS', 'PARTNER_PROFILE', '1', '1', '1');

UPDATE `pas_users` SET `access_level` = NULL WHERE 1;

ALTER TABLE `pas_users` CHANGE `access_level` `access_level` ENUM('full-access','account-manager','account-support','registration-account-partner-augosoft','registration-account-partner-campus-ce') NULL DEFAULT NULL;

------

ALTER TABLE `pas_roles_access` CHANGE `access_level` `access_level` ENUM('full-access','account-manager','account-support','registration-account-partner-augosoft','registration-account-partner-campus-ce','registration-account-partner') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;

UPDATE `pas_roles_access` SET `access_level` = 'registration-account-partner' WHERE `pas_roles_access`.`role_id` = 3;


ALTER TABLE `pas_roles_access` CHANGE `access_level` `access_level` ENUM('full-access','account-manager','account-support','registration-account-partner') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;


ALTER TABLE `pas_users` CHANGE `access_level` `access_level` ENUM('account-manager','account-support','registration-account-partner') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;

ALTER TABLE `pas_users` ADD FOREIGN KEY (`partner_id`) REFERENCES `pas_partner`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `pas_users` ADD `added_by` INT NULL DEFAULT NULL AFTER `partner_id`, ADD INDEX (`added_by`);

ALTER TABLE `pas_users` ADD FOREIGN KEY (`added_by`) REFERENCES `pas_users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- Create Relation START

ALTER TABLE `pas_imported_files` ADD INDEX(`added_by`);

ALTER TABLE `pas_imported_files` ADD FOREIGN KEY (`added_by`) REFERENCES `pas_users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `pas_leads` CHANGE `state` `state` INT NULL DEFAULT NULL, CHANGE `country` `country` INT NULL DEFAULT NULL;


ALTER TABLE `pas_leads` ADD FOREIGN KEY (`added_by`) REFERENCES `pas_users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE; ALTER TABLE `pas_leads` ADD FOREIGN KEY (`country`) REFERENCES `pas_country`(`id`) ON DELETE CASCADE ON UPDATE CASCADE; ALTER TABLE `pas_leads` ADD FOREIGN KEY (`state`) REFERENCES `pas_state`(`id`) ON DELETE CASCADE ON UPDATE CASCADE; ALTER TABLE `pas_leads` ADD FOREIGN KEY (`partner_id`) REFERENCES `pas_partner`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `pas_leads` CHANGE `interested_program` `interested_program` INT NULL DEFAULT NULL;

ALTER TABLE `pas_leads` ADD INDEX(`interested_program`);
ALTER TABLE `pas_leads` ADD INDEX(`time_zone`);

ALTER TABLE `pas_leads` ADD FOREIGN KEY (`time_zone`) REFERENCES `pas_timezone`(`id`) ON DELETE CASCADE ON UPDATE CASCADE; ALTER TABLE `pas_leads` ADD FOREIGN KEY (`interested_program`) REFERENCES `pas_program`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `pas_marketing` ADD INDEX(`created_by`);
ALTER TABLE `pas_marketing` ADD INDEX(`updated_by`);

ALTER TABLE `pas_marketing` ADD FOREIGN KEY (`created_by`) REFERENCES `pas_users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `pas_marketing` ADD FOREIGN KEY (`updated_by`) REFERENCES `pas_users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `pas_marketing_collateral` ADD FOREIGN KEY (`partner_id`) REFERENCES `pas_partner`(`id`) ON DELETE CASCADE ON UPDATE CASCADE; ALTER TABLE `pas_marketing_collateral` ADD FOREIGN KEY (`program_id`) REFERENCES `pas_program`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- Check partner_id is exists or not before run below script
UPDATE `pas_marketing_collateral` SET `partner_id` = '2' WHERE `pas_marketing_collateral`.`partner_id` = 1;
---------------------------- END ----------------------------



ALTER TABLE `pas_marketing_partner_map` ADD FOREIGN KEY (`marketing_id`) REFERENCES `pas_marketing`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `pas_marketing_template` ADD INDEX(`created_by`);
ALTER TABLE `pas_marketing_template` ADD INDEX(`updated_at`);


ALTER TABLE `pas_marketing_template` ADD FOREIGN KEY (`created_by`) REFERENCES `pas_users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE; ALTER TABLE `pas_marketing_template` ADD FOREIGN KEY (`updated_by`) REFERENCES `pas_users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `pas_partner` ADD FOREIGN KEY (`created_by`) REFERENCES `pas_users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE; ALTER TABLE `pas_partner` ADD FOREIGN KEY (`updated_by`) REFERENCES `pas_users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `pas_price_book` ADD FOREIGN KEY (`created_by`) REFERENCES `pas_users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE; ALTER TABLE `pas_price_book` ADD FOREIGN KEY (`updated_by`) REFERENCES `pas_users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `pas_price_book_program_map` ADD FOREIGN KEY (`price_book_id`) REFERENCES `pas_price_book`(`id`) ON DELETE CASCADE ON UPDATE CASCADE; ALTER TABLE `pas_price_book_program_map` ADD FOREIGN KEY (`program_id`) REFERENCES `pas_program`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;



-------- DELETE RECORD WHICH STUDENT NOT HAS ZOHO ID -----------
DELETE FROM `pas_student` WHERE `pas_student`.`zoho_id` IS NULL
------------------------- END ---------------------------------


---------- CHECK partner_id and program_id is exists or not ------------------
ALTER TABLE `pas_student` ADD FOREIGN KEY (`program_id`) REFERENCES `pas_program`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `pas_student` ADD FOREIGN KEY (`partner_id`) REFERENCES `pas_partner`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
-------------------------- END -------------------------


ALTER TABLE `student_progress_report` CHANGE `student_id` `student_id` INT(11) NULL DEFAULT NULL;

UPDATE `student_progress_report` SET `student_id` = NULL WHERE `student_progress_report`.`student_id` = 0;
ALTER TABLE `student_progress_report` CHANGE `requested_by` `requested_by` INT(11) NULL DEFAULT NULL;
ALTER TABLE `student_progress_report` ADD INDEX(`requested_by`);


ALTER TABLE `student_progress_report` ADD FOREIGN KEY (`partner_id`) REFERENCES `pas_partner`(`id`) ON DELETE CASCADE ON UPDATE CASCADE; ALTER TABLE `student_progress_report` ADD FOREIGN KEY (`requested_by`) REFERENCES `pas_users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE; ALTER TABLE `student_progress_report` ADD FOREIGN KEY (`updated_by`) REFERENCES `pas_users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- Create Relation END



INSERT INTO `pas_roles_access` (`id`, `role_id`, `access_level`, `feature`, `parent_menu`, `can_view`, `can_download`, `can_add`) VALUES (NULL, '4', NULL, 'PARTNER_USERS_LIST_ACCESS', 'PAS_ADMIN', '1', NULL, NULL);
INSERT INTO `pas_roles_access` (`id`, `role_id`, `access_level`, `feature`, `parent_menu`, `can_view`, `can_download`, `can_add`) VALUES (NULL, '4', NULL, 'WE_USERS_LIST_ACCESS', 'PAS_ADMIN', '1', NULL, NULL);
INSERT INTO `pas_roles_access` (`id`, `role_id`, `access_level`, `feature`, `parent_menu`, `can_view`, `can_download`, `can_add`) VALUES (NULL, '4', NULL, 'CONFIGURATION_EMAIL_ACCESS', 'PAS_ADMIN', '1', NULL, NULL);
INSERT INTO `pas_roles_access` (`id`, `role_id`, `access_level`, `feature`, `parent_menu`, `can_view`, `can_download`, `can_add`) VALUES (NULL, '4', NULL, 'SYSTEM_EMAIL_LOGS_ACCESS', 'PAS_ADMIN', '1', NULL, NULL);


ALTER TABLE `pas_student` ADD `payment_amount` DOUBLE NULL DEFAULT NULL AFTER `payment_type`;


CREATE TABLE `pas_enrollment` (
  `id` int(11) NOT NULL,
  `zoho_id` varchar(30) NOT NULL,
  `subject` varchar(1000) DEFAULT NULL,
  `status` varchar(200) DEFAULT NULL,
  `grand_total` double DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `program_name` varchar(200) DEFAULT NULL,
  `program_zoho_id` varchar(30) DEFAULT NULL,
  `completion_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `final_grade` double DEFAULT NULL,
  `username` varchar(200) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `pas_enrollment`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `zoho_id` (`zoho_id`);

ALTER TABLE `pas_enrollment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

TRUNCATE `pasapp_dev`.`pas_user_notification`;

ALTER TABLE `pas_marketing_partner_map` CHANGE `partner_id` `user_id` INT(11) NOT NULL;

ALTER TABLE `pas_marketing_partner_map` DROP FOREIGN KEY `pas_marketing_partner_map_ibfk_1`; ALTER TABLE `pas_marketing_partner_map` ADD CONSTRAINT `pas_marketing_partner_map_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `pas_users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `pasapp_dev`.`pas_marketing_partner_map` DROP INDEX `partner_id`, ADD INDEX `user_id` (`user_id`) USING BTREE;

RENAME TABLE `pasapp_dev`.`pas_marketing_partner_map` TO `pasapp_dev`.`pas_user_notification`;

ALTER TABLE `pas_user_notification` CHANGE `marketing_id` `foreign_key_id` INT(11) NOT NULL;

ALTER TABLE `pas_user_notification` ADD `relation_table` VARCHAR(100) NOT NULL AFTER `id`;

ALTER TABLE pasapp_dev.pas_user_notification DROP FOREIGN KEY pas_user_notification_ibfk_2;


INSERT INTO `pas_roles_access` (`id`, `role_id`, `access_level`, `feature`, `parent_menu`, `can_view`, `can_download`, `can_add`) VALUES (NULL, '1', 'account-manager', 'EDIT_PROFILE_ACCESS', 'EDIT_PROFILE', '1', '1', '1');

INSERT INTO `pas_roles_access` (`id`, `role_id`, `access_level`, `feature`, `parent_menu`, `can_view`, `can_download`, `can_add`) VALUES (NULL, '2', 'account-support', 'EDIT_PROFILE_ACCESS', 'EDIT_PROFILE', '1', '1', '1');

INSERT INTO `pas_roles_access` (`id`, `role_id`, `access_level`, `feature`, `parent_menu`, `can_view`, `can_download`, `can_add`) VALUES (NULL, '3', 'registration-account-partner', 'EDIT_PROFILE_ACCESS', 'EDIT_PROFILE', '1', '1', '1');

INSERT INTO `pas_roles_access` (`id`, `role_id`, `access_level`, `feature`, `parent_menu`, `can_view`, `can_download`, `can_add`) VALUES (NULL, '4', NULL, 'EDIT_PROFILE_ACCESS', 'EDIT_PROFILE', '1', '1', '1');

INSERT INTO `pas_roles_access` (`id`, `role_id`, `access_level`, `feature`, `parent_menu`, `can_view`, `can_download`, `can_add`) VALUES (NULL, '5', NULL, 'EDIT_PROFILE_ACCESS', 'EDIT_PROFILE', '1', '1', '1');

INSERT INTO `pas_roles_access` (`id`, `role_id`, `access_level`, `feature`, `parent_menu`, `can_view`, `can_download`, `can_add`) VALUES (NULL, '6', NULL, 'EDIT_PROFILE_ACCESS', 'EDIT_PROFILE', '1', '1', '1');

INSERT INTO `pas_roles_access` (`id`, `role_id`, `access_level`, `feature`, `parent_menu`, `can_view`, `can_download`, `can_add`) VALUES (NULL, '1', 'account-manager', 'STUDENT_ENROLLMENT_ACCESS', 'STUDENT_MANAGEMENT', '1', NULL, '1');

INSERT INTO `pas_roles_access` (`id`, `role_id`, `access_level`, `feature`, `parent_menu`, `can_view`, `can_download`, `can_add`) VALUES (NULL, '2', 'account-support', 'STUDENT_ENROLLMENT_ACCESS', 'STUDENT_MANAGEMENT', '0', NULL, '0');

INSERT INTO `pas_roles_access` (`id`, `role_id`, `access_level`, `feature`, `parent_menu`, `can_view`, `can_download`, `can_add`) VALUES (NULL, '3', 'registration-account-partner', 'STUDENT_ENROLLMENT_ACCESS', 'STUDENT_MANAGEMENT', '1', NULL, '0');

INSERT INTO `pas_roles_access` (`id`, `role_id`, `access_level`, `feature`, `parent_menu`, `can_view`, `can_download`, `can_add`) VALUES (NULL, '4', NULL, 'STUDENT_ENROLLMENT_ACCESS', 'STUDENT_MANAGEMENT', '1', NULL, '1');

INSERT INTO `pas_roles_access` (`id`, `role_id`, `access_level`, `feature`, `parent_menu`, `can_view`, `can_download`, `can_add`) VALUES (NULL, '5', NULL, 'STUDENT_ENROLLMENT_ACCESS', 'STUDENT_MANAGEMENT', '1', NULL, '1');

INSERT INTO `pas_roles_access` (`id`, `role_id`, `access_level`, `feature`, `parent_menu`, `can_view`, `can_download`, `can_add`) VALUES (NULL, '6', NULL, 'STUDENT_ENROLLMENT_ACCESS', 'STUDENT_MANAGEMENT', '1', NULL, '0');



ALTER TABLE `pas_enrollment` ADD `student_id` INT DEFAULT NULL AFTER `zoho_id`, ADD `student_zoho_id` VARCHAR(100) DEFAULT NULL AFTER `student_id`, ADD INDEX (`student_id`);


ALTER TABLE `pas_enrollment` ADD FOREIGN KEY (`student_id`) REFERENCES `pas_student`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `pas_enrollment` ADD FOREIGN KEY (`student_id`) REFERENCES `pas_student`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;


UPDATE `pas_email_templates` SET `message` = 'A Request for Student Progress report has been submitted with following details:\r\n\r\n\r\nRequest Type - %REQUEST_TYPE%\r\n\r\nOccurence - %OCCURENCE%\r\n\r\nRequester Information - %REQUESTER_INFORMATION%' WHERE `pas_email_templates`.`id` = 5;

ALTER TABLE `pas_enrollment` CHANGE `final_grade` `final_grade` DOUBLE NULL DEFAULT NULL;


UPDATE `pas_email_templates` SET `message` = 'A Request for Student Progress report has been submitted with following details:\n\n\nRequest Type - %REQUEST_TYPE%\n\nOccurence - %OCCURENCE%\n\nRequester Information - %REQUESTER_INFORMATION%\n\nSubject - %SUBJECT%\n\nPartner name - %PARTNER_NAME%\n\nProgram - %PROGRAM_NAME%\n\nUser name - %USER_NAME%' WHERE `pas_email_templates`.`id` = 5;


ALTER TABLE `pas_partner_selling_program_map` ADD `id` INT NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id`);

ALTER TABLE `pas_partner_selling_program_map` ADD `created_at` DATETIME NULL DEFAULT NULL AFTER `selling_count`, ADD `updated_at` DATETIME NULL DEFAULT NULL AFTER `created_at`;

ALTER TABLE `pas_schedule` ADD `stage` VARCHAR(200) NULL AFTER `deal_name`, ADD `start_date` DATE NULL AFTER `stage`, ADD `amount` DOUBLE NULL AFTER `start_date`;


-- Created At: 16 Aug 2021

ALTER TABLE `pas_program` ADD `layout` VARCHAR(200) NULL DEFAULT NULL AFTER `service_item_not_program`;

INSERT INTO `pas_users` (`id`, `user_type`, `email`, `password`, `roleid`, `firstname`, `lastname`, `last_active`, `status`, `request_time`, `reset_status`, `first_login`, `login_status`, `otp`, `photo`, `phone`, `partner`, `partner_type`, `augusoft_campus`, `access_level`, `access_feature`, `partner_id`, `added_by`, `login_code`, `last_wrong_attempted_at`, `highlight_reports`) VALUES
(NULL, 1, 'krm@xoomwebdevelopment.com', 'c7f143bd77ad147a8a093bb950cffcd5', 1, 'Test', 'User', NULL, 1, NULL, 0, 1, 0, 0, NULL, '(626) 310-3637', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL),
(NULL, 4, 'krmp@xoomwebdevelopment.com', 'c7f143bd77ad147a8a093bb950cffcd5', 4, 'Test', 'Partner', NULL, 2, NULL, 0, 1, 0, 0, NULL, '803-717-7743', NULL, 2, NULL, NULL, NULL, 1, NULL, 0, NULL, NULL);

ALTER TABLE `pas_email_logs` ADD `added_by` INT NULL DEFAULT NULL AFTER `message`, ADD INDEX (`added_by`);

INSERT INTO `pas_email_templates` (`id`, `from_email`, `from_name`, `type`, `subject`, `message`, `added_date`) VALUES (NULL, 'festus@academyofwe.com', 'World Education', 'Progress Request', 'Progress Request', 'A Request for Student Progress report has been submitted with following details:\r\n\r\n\r\nRequest Type - %REQUEST_TYPE%\r\n\r\nRequester Information - %REQUESTER_INFORMATION%\r\n\r\nSubject - %SUBJECT%\r\n\r\nPartner name - %PARTNER_NAME%\r\n\r\nProgram - %PROGRAM_NAME%\r\n\r\nUser name - %USER_NAME%', '2021-05-18 04:27:01');

-- 20-Oct-2021

ALTER TABLE `pas_student` ADD `price_paid` DOUBLE NULL AFTER `payment_amount`;

INSERT INTO `pas_email_templates` (`id`, `from_email`, `from_name`, `type`, `subject`, `message`, `added_date`) VALUES
(12, 'festus@academyofwe.com', 'World Education', 'Student Enrollment', 'Student Enrollment', 'A Request for Student Enrollment has been submitted with following details:\n\nFirst Name - %FIRST_NAME%\n\nLast Name - %LAST_NAME%\n\nPartner name - %PARTNER_NAME%\n\nProgram - %PROGRAM_NAME%\n\nEMAIL - %EMAIL%\n\nPayment Type - %PAYMENT_TYPE%\n\nStart Date - %START_DATE%\n\nEnd Date - %END_DATE%\n\nPhone - %PHONE%', '2021-05-18 14:27:01'),
(13, 'festus@academyofwe.com', 'World Education', 'Leads Entry', 'Leads Entry', 'A Request for Leads Entry has been submitted with following details:\n\nFirst Name - %FIRST_NAME%\n\nLast Name - %LAST_NAME%\n\nName of Requester - %NAME_OF_REQUESTER%\n\nPartner name - %PARTNER_NAME%\n\nProgram - %PROGRAM_NAME%\n\nEMAIL - %EMAIL%\n\nInquiry Message - %INQUIRY_MESSAGE%\n\nPhone - %PHONE%', '2021-05-18 14:27:01'),
(14, 'festus@academyofwe.com', 'World Education', 'Student Enrollment Bulk', 'Student Enrollment Bulk', 'Student Enrollment upload with %SUCCESS_RECORDS% records have been submitted on PAS for %PARTNER_NAME% institution. Please go to the CRM to work on the Student Enrollment.', '2021-05-18 14:27:01');
