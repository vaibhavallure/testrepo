<?php
$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE `{$this->getTable('service_identifier')}`
	ALTER `identifier_id` DROP DEFAULT;
ALTER TABLE `{$this->getTable('service_identifier')}`
	CHANGE COLUMN `identifier_id` `identifier_id` VARCHAR(36) NOT NULL FIRST,
	ADD PRIMARY KEY (`identifier_id`),
	ADD INDEX `item_id` (`item_id`);
");
$installer->endSetup();