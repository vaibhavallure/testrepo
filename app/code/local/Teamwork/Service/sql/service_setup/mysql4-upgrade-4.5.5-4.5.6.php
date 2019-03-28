<?php

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE `{$this->getTable('service_weborder')}`
	ADD COLUMN `CustomerId` CHAR(36) NULL DEFAULT NULL AFTER `Status`,
	ADD COLUMN `BillAddressId` CHAR(36) NULL DEFAULT NULL AFTER `EComCustomerId`,
	ADD COLUMN `ShipAddressId` CHAR(36) NULL DEFAULT NULL AFTER `BillState`;
");
$installer->endSetup();