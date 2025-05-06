--
-- Script run when an upgrade of Dolibarr is done. Whatever is the Dolibarr version.
--

--
-- llx_younitedpay
--
CREATE TABLE `llx_younitedpay` (
  `rowid` int(10) UNSIGNED NOT NULL,
  `datec` timestamp NOT NULL DEFAULT current_timestamp(),
  `tms` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `amount` decimal(10,2) NOT NULL,
  `maturity` int(10) UNSIGNED NOT NULL,
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `objecttype` enum('propal','commande','facture') NOT NULL,
  `fk_object` int(11) UNSIGNED NOT NULL,
  `fk_paiement` int(11) DEFAULT NULL,
  `token` text DEFAULT NULL,
  `payment_id` varchar(128) NOT NULL,
  `payment_status` varchar(16) NOT NULL,
  `payment_updatedat` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `llx_younitedpay`
  ADD PRIMARY KEY (`rowid`),
  ADD KEY `fk_paiement` (`fk_paiement`),
  ADD KEY `objecttype` (`objecttype`,`fk_object`),
  ADD KEY `payment_id` (`payment_id`);

ALTER TABLE `llx_younitedpay`
  MODIFY `rowid` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
