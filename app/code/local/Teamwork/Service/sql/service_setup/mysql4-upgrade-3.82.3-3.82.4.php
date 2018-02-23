<?php
$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE `{$this->getTable('service_style_category')}`
    ADD COLUMN `channel_id` CHAR(36) NOT NULL AFTER `category_id`,
    DROP PRIMARY KEY,
    ADD PRIMARY KEY (`style_id`, `category_id`, `channel_id`);
ALTER TABLE `{$this->getTable('service_item_category')}`
    ADD COLUMN `channel_id` CHAR(36) NOT NULL AFTER `category_id`,
    DROP COLUMN `request_id`,
    DROP PRIMARY KEY,
    ADD PRIMARY KEY (`item_id`, `category_id`, `channel_id`);
ALTER TABLE `{$this->getTable('service_media')}`
    ADD COLUMN `channel_id` CHAR(36) NOT NULL AFTER `host_id`,
    DROP INDEX `host_id_media_index_media_type`,
    ADD INDEX `host_id_media_index_media_type` (`host_id`, `media_type`, `media_index`, `channel_id`),
    DROP INDEX `host_id_media_uri_media_index`,
    ADD INDEX `host_id_media_uri_media_index` (`host_id`, `media_uri`, `media_index`, `channel_id`);
");
$installer->endSetup();