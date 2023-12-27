-- NOTE ZOHO WEBHOOK SCOPE NEED TO UPDATE
-- https://partner-worldeducation.net/dashboard/zoho/link

INSERT INTO `pas_email_templates` (`id`, `from_email`, `from_name`, `type`, `subject`, `message`, `added_date`) VALUES (15, 'partners@worldeducation.net', 'World Education', 'Leads Entry To Owner', 'Leads Entry', 'A lead in CRM with the following details have been assigned to you:\r\n\r\nStudent Name: - %STUDENT_NAME%\r\n\r\nProgram - %PROGRAM_NAME%\r\n\r\nInstitution: - %INSTITUTION%', '2021-05-19 00:57:01');

INSERT INTO `pas_email_templates` (`id`, `from_email`, `from_name`, `type`, `subject`, `message`, `added_date`) VALUES (16, 'partners@worldeducation.net', 'World Education', 'A lead in the system has been converted to an actual sale.', 'A lead converted to an actual sale', 'Hi, a lead in the system with the following details has been converted to an actual sale.\r\n\r\nLead Name: %STUDENT_NAME%\r\n\r\nLead Email: %STUDENT_EMAIL%\r\n\r\nProgram: %PROGRAM_NAME%\r\n\r\nInstitution: %INSTITUTION%\r\n\r\nPlease go ahead and process the enrollment\r\n\r\nThanks,\r\nPAS Admin', '2021-05-19 00:57:01');

INSERT INTO `pas_email_templates` (`id`, `from_email`, `from_name`, `type`, `subject`, `message`, `added_date`) VALUES (17, 'partners@worldeducation.net', 'World Education', 'Leads Entry To Owner', 'Enrollment has been submitted', 'Hi, an enrollment has been submitted with following details:\r\n\r\nStudent Name: - %STUDENT_NAME%\r\n\r\nStudent Email: - %STUDENT_EMAIL%\r\n\r\nProgram - %PROGRAM_NAME%\r\n\r\nInstitution: - %INSTITUTION%', '2021-05-19 00:57:01')


CREATE TABLE `pas_owner` (
  `id` int(11) NOT NULL,
  `zoho_id` varchar(50) DEFAULT NULL,
  `full_name` varchar(200) NOT NULL,
  `email` varchar(200) NOT NULL,
  `status` varchar(50) DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL,
  `role_zoho_id` varchar(50) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `pas_owner`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `pas_owner`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `pas_leads` ADD `lead_owner_zoho_id` VARCHAR(50) NULL DEFAULT NULL AFTER `partner_id`;

ALTER TABLE `pas_leads` CHANGE `lead_owner_zoho_id` `owner_zoho_id` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;
