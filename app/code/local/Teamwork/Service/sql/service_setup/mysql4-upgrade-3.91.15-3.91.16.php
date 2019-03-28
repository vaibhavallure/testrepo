<?php
$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE `{$this->getTable('service_category')}`
    DROP INDEX `internal_id`,
    ADD UNIQUE INDEX `internal_id_channel_id` (`internal_id`, `channel_id`);

ALTER TABLE `{$this->getTable('service_style_category')}`
    CHANGE COLUMN `channel_id` `channel_id` CHAR(36) NOT NULL AFTER `style_id`,
    DROP PRIMARY KEY,
    ADD INDEX `style_id_channel_id` (`style_id`, `channel_id`);

ALTER TABLE `{$this->getTable('service_item_category')}`
    CHANGE COLUMN `channel_id` `channel_id` CHAR(36) NOT NULL AFTER `item_id`,
    DROP PRIMARY KEY,
    ADD INDEX `item_id_channel_id` (`item_id`, `channel_id`);

ALTER TABLE `{$this->getTable('service_items')}`
    ADD INDEX `request_id` (`request_id`);
");
$installer->endSetup();