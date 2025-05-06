--
-- Script run when an upgrade of Dolibarr is done. Whatever is the Dolibarr version.
--

--
-- llx_younitedpay_webhook_log
--
CREATE TABLE `llx_younitedpay_webhook_log` (
  `rowid` int(11) NOT NULL,
  `datec` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_id` varchar(128) NOT NULL,
  `data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `llx_younitedpay_webhook_log`
  ADD PRIMARY KEY (`rowid`),
  ADD KEY `payment_id` (`payment_id`);

ALTER TABLE `llx_younitedpay_webhook_log`
  MODIFY `rowid` int(11) NOT NULL AUTO_INCREMENT;
