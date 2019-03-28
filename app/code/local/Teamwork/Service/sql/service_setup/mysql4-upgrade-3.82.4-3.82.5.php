<?php
$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE `{$this->getTable('service_items')}`
    ADD COLUMN `channel_id` CHAR(36) NOT NULL AFTER `item_id`,
    DROP PRIMARY KEY,
    ADD PRIMARY KEY (`item_id`, `channel_id`),
    ADD INDEX `style_id_channel_id` (`style_id`, `channel_id`),
    ADD INDEX `internal_id` (`internal_id`);

ALTER TABLE `{$this->getTable('service_style')}`
    ADD COLUMN `channel_id` CHAR(36) NOT NULL AFTER `style_id`,
    DROP PRIMARY KEY,
    ADD PRIMARY KEY (`style_id`, `channel_id`),
    ADD INDEX `channel_id_request_id` (`request_id`, `channel_id`);

ALTER TABLE `{$this->getTable('service_price')}`
    ADD COLUMN `channel_id` CHAR(36) NOT NULL AFTER `item_id`,
    DROP PRIMARY KEY,
    ADD PRIMARY KEY (`item_id`, `channel_id`, `price_level`);

ALTER TABLE `{$this->getTable('service_style_related')}`
    ADD COLUMN `channel_id` CHAR(36) NOT NULL AFTER `style_id`,
    ADD KEY (`style_id`, `channel_id`);
");
$installer->endSetup();