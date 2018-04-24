<?php
$installer = $this;
$installer->startSetup();
$quoteTableName = $this->getTable('sales/quote');
$gcLinkTableName = $this->getTable('teamwork_cegiftcards/giftcard_link');
$gcTransactionTableName = $this->getTable('teamwork_cegiftcards/giftcard_transaction');

$invoiceTableName = $this->getTable('sales/invoice');
$gcOrderInvoiceLinkTableName = $this->getTable('teamwork_cegiftcards/order_invoice_link');

$creditmemoTableName = $this->getTable('sales/creditmemo');
$gcOrderCreditmemoLinkTableName = $this->getTable('teamwork_cegiftcards/order_creditmemo_link');


$installer->run("

CREATE TABLE `{$gcLinkTableName}` (
`entity_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `applied_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `quote_id` INT(10) UNSIGNED NOT NULL,
  `gc_code` VARCHAR(255) NOT NULL,
  `balance` DECIMAL(12,4) NOT NULL DEFAULT 0,
  `amount` DECIMAL(12,4) NOT NULL DEFAULT 0,
  `base_amount` DECIMAL(12,4) NOT NULL DEFAULT 0,
  `position` INT(10) UNSIGNED NOT NULL,
  `order_id` INT(10) UNSIGNED NOT NULL,
  `paid` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`entity_id`),
  FOREIGN KEY (`quote_id`) REFERENCES `{$quoteTableName}` (`entity_id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `{$gcTransactionTableName}` (
`entity_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `gc_link_id` INT(10) UNSIGNED NOT NULL,
  `transaction_id` VARCHAR(255) NOT NULL,
  `amount` DECIMAL(12,4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`entity_id`),
  FOREIGN KEY (`gc_link_id`) REFERENCES `{$gcLinkTableName}` (`entity_id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `{$gcOrderInvoiceLinkTableName}` (
`entity_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `gc_link_id` INT(10) UNSIGNED NOT NULL,
  `invoice_id` VARCHAR(255) NOT NULL,
  `amount_used` DECIMAL(12,4) NOT NULL DEFAULT 0,
  `base_amount_used` DECIMAL(12,4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`entity_id`),
  FOREIGN KEY (`gc_link_id`) REFERENCES `{$gcLinkTableName}` (`entity_id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `{$gcOrderCreditmemoLinkTableName}` (
`entity_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `gc_link_id` INT(10) UNSIGNED NOT NULL,
  `creditmemo_id` VARCHAR(255) NOT NULL,
  `amount_used` DECIMAL(12,4) NOT NULL DEFAULT 0,
  `base_amount_used` DECIMAL(12,4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`entity_id`),
  FOREIGN KEY (`gc_link_id`) REFERENCES `{$gcLinkTableName}` (`entity_id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$installer->endSetup();