CREATE TABLE `pas_user_activity` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` enum('create','update','delete','fetch') NOT NULL,
  `action_via` enum('web','cron') NOT NULL DEFAULT 'web',
  `url` varchar(1000) DEFAULT NULL,
  `method` varchar(20) DEFAULT NULL,
  `old_data` text DEFAULT NULL,
  `new_data` text DEFAULT NULL,
  `ref_ids` varchar(500) DEFAULT NULL,
  `ip_address` varchar(50) NOT NULL,
  `session_id` varchar(150) NOT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `pas_user_activity`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `pas_user_activity`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `pas_enrollment` ADD `date_of_birth` VARBINARY(100) NULL DEFAULT NULL AFTER `status`, ADD `social_security_number` VARBINARY(100) NULL DEFAULT NULL AFTER `date_of_birth`;

INSERT INTO `pas_roles_access` (`id`, `role_id`, `access_level`, `feature`, `parent_menu`, `can_view`, `can_download`, `can_add`) VALUES
(NULL, 1, 'account-manager', 'STUDENT_PII_ACCESS', 'STUDENT_PII', 1, NULL, NULL),
(NULL, 2, 'account-support', 'STUDENT_PII_ACCESS', 'STUDENT_PII', 1, NULL, NULL),
(NULL, 3, 'registration-account-partner', 'STUDENT_PII_ACCESS', 'STUDENT_PII', 1, NULL, NULL),
(NULL, 4, NULL, 'STUDENT_PII_ACCESS', 'STUDENT_PII', 1, NULL, NULL),
(NULL, 5, NULL, 'STUDENT_PII_ACCESS', 'STUDENT_PII', 1, NULL, NULL),
(NULL, 6, NULL, 'STUDENT_PII_ACCESS', 'STUDENT_PII', 1, NULL, NULL);

