<?php
$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE `{$this->getTable('sales_flat_order')}`
	ADD COLUMN `" . Teamwork_Realtimeavailability_Model_Resource::$twDefaultLocation . "` CHAR(36) NULL DEFAULT NULL AFTER `gift_message_id`;
");

$installer->endSetup();