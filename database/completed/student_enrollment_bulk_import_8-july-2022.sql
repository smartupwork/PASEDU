ALTER TABLE `pas_imported_files` ADD `records_imported_warning` INT NOT NULL DEFAULT '0' AFTER `records_imported`;

ALTER TABLE `pas_imported_files` ADD `warning_rows` TEXT NULL DEFAULT NULL AFTER `processing_time`;