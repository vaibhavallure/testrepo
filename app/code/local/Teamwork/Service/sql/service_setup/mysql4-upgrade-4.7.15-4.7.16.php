<?php
$installer = $this;

$installer->startSetup();
$installer->run("
ALTER TABLE `{$this->getTable('service_weborder_payment')}`
	ADD COLUMN `IsCaptured` TINYINT NOT NULL DEFAULT '1' AFTER `TransactionId`,
	ADD COLUMN `PaymentDate` DATETIME NOT NULL AFTER `IsCaptured`;
    
ALTER TABLE `{$this->getTable('service_setting_payment')}`
	ADD COLUMN `allow_authorize_only` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `active`,
	ADD COLUMN `payment_method_id` CHAR(36) NULL DEFAULT NULL AFTER `allow_authorize_only`;
");
$installer->endSetup();