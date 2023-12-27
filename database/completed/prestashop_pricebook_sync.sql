ALTER TABLE `pas_price_book_program_map` ADD `id` INT NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id`);

ALTER TABLE `pas_price_book_program_map` ADD `created_at` DATETIME NULL DEFAULT NULL AFTER `program_list_price`, ADD `updated_at` DATETIME NULL DEFAULT NULL AFTER `created_at`;

UPDATE `pas_price_book_program_map` SET `created_at` = '2022-10-12 17:27:40', `updated_at` = '2022-10-12 17:27:40' WHERE 1;

ALTER TABLE `pas_program` CHANGE `is_copy` `is_copy` TINYINT(1) NOT NULL DEFAULT '1';


ALTER TABLE `pas_program` ADD `price_book_counter` TINYINT(2) NOT NULL DEFAULT '0' AFTER `is_copy`;
