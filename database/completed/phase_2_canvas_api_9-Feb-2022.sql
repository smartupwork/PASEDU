ALTER TABLE `pas_user_activity` CHANGE `old_data` `old_data` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE `new_data` `new_data` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;

ALTER TABLE `pas_partner` ADD `canvas_sub_account_id` BIGINT NULL DEFAULT NULL AFTER `zoho_id`;

CREATE TABLE `student_activity_progress` (
  `id` int(11) NOT NULL,
  `activity_type` enum('activity-progress','activity-log') NOT NULL,
  `report_type` enum('generate-report','schedule-report') NOT NULL,
  `partner_id` int(11) NOT NULL,
  `enrollment_id` int(11) DEFAULT NULL,
  `schedule_interval` enum('bi-week','one-month','six-month','one-time') DEFAULT NULL,
  `scheduled_at` date DEFAULT NULL,
  `is_recurring` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL,
  `created_by` int(11) NOT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `student_activity_progress`
  ADD PRIMARY KEY (`id`),
  ADD KEY `partner_id` (`partner_id`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `student_id` (`enrollment_id`);

ALTER TABLE `student_activity_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


CREATE TABLE `pas_canvas_course` (
  `id` bigint(20) NOT NULL,
  `pas_sub_account_id` bigint(20) DEFAULT NULL,
  `canvas_course_id` bigint(20) NOT NULL,
  `account_id` bigint(20) NOT NULL,
  `root_account_id` bigint(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `work_status` varchar(50) NOT NULL,
  `uuid` varchar(100) NOT NULL,
  `start_at` datetime DEFAULT NULL,
  `end_at` datetime(1) DEFAULT NULL,
  `course_code` varchar(50) NOT NULL,
  `license` varchar(20) DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT NULL,
  `time_zone` varchar(150) DEFAULT NULL,
  `migration_detail` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `pas_canvas_course`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pas_sub_account_id` (`pas_sub_account_id`);

ALTER TABLE `pas_canvas_course`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;


CREATE TABLE `pas_canvas_sub_account` (
  `id` bigint(20) NOT NULL,
  `parent_account_id` bigint(20) NOT NULL,
  `sub_account_id` bigint(20) NOT NULL,
  `root_account_id` bigint(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `work_status` varchar(50) NOT NULL,
  `uuid` varchar(100) NOT NULL,
  `default_time_zone` varchar(150) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `pas_canvas_sub_account`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `pas_canvas_sub_account`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

ALTER TABLE `pas_partner` ADD `mkt_colors1` VARCHAR(15) NULL DEFAULT NULL AFTER `status`, ADD `mkt_colors2` VARCHAR(15) NULL DEFAULT NULL AFTER `mkt_colors1`, ADD `mkt_colors3` VARCHAR(15) NULL DEFAULT NULL AFTER `mkt_colors2`, ADD `mkt_colors4` VARCHAR(15) NULL DEFAULT NULL AFTER `mkt_colors3`, ADD `mkt_colors5` VARCHAR(15) NULL DEFAULT NULL AFTER `mkt_colors4`, ADD `mkt_colors6` VARCHAR(15) NULL DEFAULT NULL AFTER `mkt_colors5`, ADD `mkt_colors7` VARCHAR(15) NULL DEFAULT NULL AFTER `mkt_colors6`, ADD `mkt_colors8` VARCHAR(15) NULL DEFAULT NULL AFTER `mkt_colors7`, ADD `mkt_colors9` VARCHAR(15) NULL DEFAULT NULL AFTER `mkt_colors8`, ADD `mkt_colors10` VARCHAR(15) NULL DEFAULT NULL AFTER `mkt_colors9`;


CREATE TABLE `pas_canvas_user` (
  `id` bigint(20) NOT NULL,
  `canvas_user_id` bigint(20) NOT NULL,
  `name` varchar(200) NOT NULL,
  `role` varchar(100) DEFAULT NULL,
  `sortable_name` varchar(200) NOT NULL,
  `short_name` varchar(100) NOT NULL,
  `sis_user_id` varchar(50) NOT NULL,
  `integration_id` varchar(20) DEFAULT NULL,
  `sis_import_id` varchar(100) DEFAULT NULL,
  `login_id` varchar(200) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `pas_canvas_user`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `pas_canvas_user`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;


CREATE TABLE `pas_canvas_user_enrollment` (
  `id` bigint(20) NOT NULL,
  `canvas_enrollment_id` bigint(20) DEFAULT NULL,
  `user_id` bigint(20) NOT NULL,
  `course_id` bigint(20) NOT NULL,
  `course_section_id` bigint(20) NOT NULL,
  `enroll_start_date` date DEFAULT NULL,
  `enroll_end_date` date DEFAULT NULL,
  `total_activity_sec` varchar(50) DEFAULT NULL,
  `today_activity_sec` varchar(50) DEFAULT NULL,
  `last_activity_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `pas_canvas_user_enrollment`
  ADD PRIMARY KEY (`id`);

CREATE TABLE `email_queue` (
  `id` int(11) NOT NULL,
  `partner_id` int(11) DEFAULT NULL,
  `enrollment_id` int(11) DEFAULT NULL,
  `from_email` varchar(128) DEFAULT NULL,
  `to_email` varchar(200) DEFAULT NULL,
  `cc_email` text DEFAULT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `attachments` text DEFAULT NULL,
  `is_sent` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `email_queue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`partner_id`);

ALTER TABLE `email_queue`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `pas_email_logs` ADD `attachments` TEXT NULL DEFAULT NULL AFTER `message`;

ALTER TABLE `student_activity_progress` ADD `exists_into_canvas` TINYINT(1) NULL DEFAULT NULL AFTER `is_recurring`;

ALTER TABLE `student_activity_progress` CHANGE `exists_into_canvas` `canvas_student_id` BIGINT NULL DEFAULT NULL;

ALTER TABLE `pas_canvas_user_enrollment` ADD `login_time` DATETIME NULL DEFAULT NULL AFTER `last_activity_at`, ADD `logout_time` DATETIME NULL DEFAULT NULL AFTER `login_time`, ADD `ip_address` VARCHAR(200) NULL DEFAULT NULL AFTER `logout_time`;


ALTER TABLE `student_activity_progress` ADD `fetch_report_type` ENUM('all','date-range') NOT NULL AFTER `scheduled_at`, ADD `fetch_start_date` DATE NULL DEFAULT NULL AFTER `fetch_report_type`, ADD `fetch_end_date` DATE NULL DEFAULT NULL AFTER `fetch_start_date`;


ALTER TABLE `pas_enrollment` ADD `date_of_birth` VARBINARY(100) NULL DEFAULT NULL AFTER `status`, ADD `social_security_number` VARBINARY(100) NULL DEFAULT NULL AFTER `date_of_birth`;


CREATE TABLE `pas_partner_inquiry` (
  `id` int(11) NOT NULL,
  `request_type` varchar(222) DEFAULT NULL,
  `request_reason` varchar(222) DEFAULT NULL,
  `message` text,
  `added_by` int(11) DEFAULT NULL,
  `added_date` datetime DEFAULT NULL,
  `status` varchar(55) DEFAULT NULL,
  `partner_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `pas_partner_inquiry`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `pas_partner_inquiry`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

ALTER TABLE `student_activity_progress` CHANGE `fetch_report_type` `fetch_report_type` ENUM('all','date-range') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;

CREATE TABLE `pas_email_logs` (
  `id` int(11) NOT NULL,
  `from_email` varchar(128) DEFAULT NULL,
  `to_email` varchar(200) DEFAULT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `attachments` text DEFAULT NULL,
  `added_by` int(11) DEFAULT NULL,
  `added_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `pas_email_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `added_by` (`added_by`);

ALTER TABLE `pas_email_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


UPDATE `pas_email_templates` SET `message` = 'A Request for Student Enrollment has been submitted with following details:\r\n\r\nFirst Name - %FIRST_NAME%\r\n\r\nLast Name - %LAST_NAME%\r\n\r\nPartner name - %PARTNER_NAME%\r\n\r\nProgram - %PROGRAM_NAME%\r\n\r\nEMAIL - %EMAIL%\r\n\r\nPayment Type - %PAYMENT_TYPE%\r\n\r\nStart Date - %START_DATE%\r\n\r\nEnd Date - %END_DATE%\r\n\r\nPhone - %PHONE%\r\n\r\nRequester Name - %REQUESTER_NAME%\r\n\r\nRequester Email - %REQUESTER_EMAIL%' WHERE `pas_email_templates`.`id` = 12;


ALTER TABLE `pas_canvas_user` CHANGE `sis_user_id` `sis_user_id` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;


CREATE TABLE `listing_setting` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `partner_id` int(11) DEFAULT NULL,
  `module` varchar(100) NOT NULL,
  `menu` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


ALTER TABLE `listing_setting`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `listing_setting`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


