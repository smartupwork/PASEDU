ALTER TABLE `pas_users` ADD `password_expired_at` DATE NULL DEFAULT NULL AFTER `highlight_reports`;

CREATE TABLE `password_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `password` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `password_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `password_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `password_history`
  ADD CONSTRAINT `password_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `pas_users` (`id`) ON UPDATE CASCADE;

ALTER TABLE `pas_users` CHANGE `password_expired_at` `password_expired_at` DATETIME NULL DEFAULT NULL;
