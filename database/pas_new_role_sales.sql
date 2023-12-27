INSERT INTO `pas_roles` (`id`, `role_name`, `description`, `role_type`, `status`) VALUES (NULL, 'Sales Team', 'Sales Team', 'user', '1');

INSERT INTO `pas_roles_access` (`id`, `role_id`, `access_level`, `feature`, `parent_menu`, `can_view`, `can_download`, `can_add`) VALUES
(NULL, 7, NULL, 'STATS_ACCESS', 'DASHBOARD', 1, 0, 0),
(NULL, 7, NULL, 'HOME_DASHBOARD_ACCESS', 'DASHBOARD', 1, 1, 1),
(NULL, 7, NULL, 'MYCAA_TRAINING_PLAN_CREATOR_ACCESS', 'PAS_ADMIN', 1, 1, 1),
(NULL, 7, NULL, 'TRAINING_PLAN_CREATOR_ACCESS', 'PAS_ADMIN', 1, 1, 1)
(NULL, 7, NULL, 'MY_PROFILE_ACCESS', 'PARTNER_PROFILE', 1, 0, 1);

ALTER TABLE `pas_affiliate` ADD `state` VARCHAR(100) NULL DEFAULT NULL AFTER `city`;

