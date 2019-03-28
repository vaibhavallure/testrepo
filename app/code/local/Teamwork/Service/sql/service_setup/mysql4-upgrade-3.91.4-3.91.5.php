<?php
$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE `{$this->getTable('service_weborder_item')}`
    ADD COLUMN `InternalId` INT NOT NULL DEFAULT '0' AFTER `ItemId`,
    ADD INDEX `InternalId` (`InternalId`),
    ADD INDEX `ItemId` (`ItemId`);

ALTER TABLE `{$this->getTable('service_status_items')}`
    ADD COLUMN `WebOrderItemId` CHAR(36) NULL FIRST,
    DROP PRIMARY KEY,
    ADD INDEX `PackageId` (`PackageId`);
");
$installer->endSetup();