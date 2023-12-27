ALTER TABLE `pas_enrollment` ADD `contact_id` INT NULL DEFAULT NULL AFTER `partner_zoho_id`, ADD `contact_zoho_id` VARCHAR(100) NULL DEFAULT NULL AFTER `contact_id`;

CREATE TABLE `pas_contact` (
  `id` int(11) NOT NULL,
  `zoho_id` varchar(50) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `contact_title` varchar(150) DEFAULT NULL,
  `date_of_birth` varbinary(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `mobile` varchar(50) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `contact_active` varchar(100) DEFAULT NULL,
  `contact_role` varchar(100) DEFAULT NULL,
  `lead_created` varchar(100) DEFAULT NULL,
  `lead_source` varchar(100) DEFAULT NULL,
  `mailing_city` varchar(100) DEFAULT NULL,
  `mailing_country` varchar(100) DEFAULT NULL,
  `mailing_state` varchar(50) DEFAULT NULL,
  `mailing_street` varchar(100) DEFAULT NULL,
  `mailing_zip` varchar(20) DEFAULT NULL,
  `partner_id` int(11) DEFAULT NULL,
  `partner_zoho_id` varchar(100) DEFAULT NULL,
  `secondary_email` varchar(150) DEFAULT NULL,
  `social_security_number` varbinary(150) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `pas_contact`
  ADD PRIMARY KEY (`id`),
  ADD KEY `partner_id` (`partner_id`);

ALTER TABLE `pas_contact`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;