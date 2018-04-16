<?php

$installer = $this;
$installer->startSetup();
$weborderItemTableName = $this->getTable('service_weborder_item');
$installer->run("
DROP INDEX `ItemId` ON `$weborderItemTableName`;
ALTER TABLE `$weborderItemTableName` CHANGE COLUMN `ItemId` `ItemId` CHAR(36) DEFAULT NULL;
ALTER TABLE `$weborderItemTableName` ADD COLUMN `SecondaryId` VARCHAR(255) DEFAULT NULL AFTER `ItemId`;
");
$installer->endSetup();
