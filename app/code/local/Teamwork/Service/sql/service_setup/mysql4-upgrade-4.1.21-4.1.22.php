<?php
$installer = $this;
$db = Mage::getSingleton('core/resource')->getConnection('core_write');

$installer->startSetup();

$mainTable = $installer->getTable('service_media');

$installer->run("
ALTER TABLE `{$this->getTable('service_attribute_value')}`
    ADD COLUMN `request_id` CHAR(36) NOT NULL AFTER `attribute_set_id`;

TRUNCATE `{$mainTable}`;
ALTER TABLE `{$mainTable}`
    ADD COLUMN `media_id` CHAR(36) NOT NULL FIRST,
    ADD PRIMARY KEY `media_id` (`media_id`);

ALTER TABLE `{$installer->getTable('service_media_value')}`
    CHANGE COLUMN `save_params` `saved_media_name` VARCHAR(255) NOT NULL AFTER `internal_id`,
    CHANGE COLUMN `media_uri` `media_id` CHAR(36) NOT NULL FIRST,
    DROP INDEX `media_uri`, ADD INDEX `media_id` (`media_id`);
");

$installer->endSetup();