<?php

$installer = $this;
$installer->startSetup();
$weborderItemTableName = $this->getTable('service_weborder_item');
$installer->run("
ALTER TABLE `{$this->getTable('service_weborder')}`
	ADD COLUMN `BillAddressType` VARCHAR(255) NOT NULL DEFAULT '' AFTER `BillAddressId`,
	ADD COLUMN `ShipAddressType` VARCHAR(255) NOT NULL DEFAULT '' AFTER `ShipAddressId`;
");
$installer->endSetup();
