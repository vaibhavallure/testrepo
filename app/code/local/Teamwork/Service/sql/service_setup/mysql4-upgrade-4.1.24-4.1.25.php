<?php
$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE `{$this->getTable('service_attribute_set')}`
	CHANGE COLUMN `code` `code` VARCHAR(50) NULL DEFAULT NULL AFTER `internal_id`,
	ADD COLUMN `description` VARCHAR(50) NULL DEFAULT NULL AFTER `code`,
	CHANGE COLUMN `name` `alias` VARCHAR(50) NULL DEFAULT NULL AFTER `description`;
");
$installer->endSetup();