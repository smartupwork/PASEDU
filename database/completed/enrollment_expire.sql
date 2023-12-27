SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `zoho_webhook` (
  `id` int(11) NOT NULL,
  `module` varchar(100) DEFAULT 'SalesOrders',
  `status` enum('success','exception') DEFAULT 'success',
  `response` text,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `zoho_webhook`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `zoho_webhook`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;