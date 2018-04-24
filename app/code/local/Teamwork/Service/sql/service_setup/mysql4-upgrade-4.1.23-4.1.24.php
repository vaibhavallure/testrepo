<?php
$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE `{$this->getTable('service_weborder')}`
    CHANGE COLUMN `RecCreated` `ProcessingDate` DATETIME NOT NULL AFTER `EComChannelId`;

ALTER TABLE `{$installer->getTable('service_style')}`
    ADD COLUMN `url_key` VARCHAR(255) NULL DEFAULT NULL AFTER `manufacturer`;

ALTER TABLE `{$installer->getTable('service_items')}`
    ADD COLUMN `url_key` VARCHAR(255) NULL DEFAULT NULL AFTER `skukey`;
");

$installer->endSetup();