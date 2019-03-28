<?php
$installer = $this;

$installer->startSetup();
$installer->run("
ALTER TABLE `{$this->getTable('service_style')}`
	ADD COLUMN `vendor_no` VARCHAR(255) NULL AFTER `order_cost`;

ALTER TABLE `{$this->getTable('service_items')}`
	ADD COLUMN `vendor_no` VARCHAR(255) NULL AFTER `order_cost`;
");
$installer->endSetup();